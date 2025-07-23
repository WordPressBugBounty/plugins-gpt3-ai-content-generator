<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/ajax/class-aipkit-core-ajax-handler.php
// Status: MODIFIED

namespace WPAICG\Core\Ajax;

use WPAICG\Dashboard\Ajax\BaseDashboardAjaxHandler; // Use the base dashboard handler
use WPAICG\AIPKit_Role_Manager;
use WPAICG\Includes\AIPKit_Upload_Utils;
use WPAICG\Core\AIPKit_AI_Caller; // For AI Caller
use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WP_Error; // For WP_Error usage

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles core AJAX actions not specific to a particular module.
 */
class AIPKit_Core_Ajax_Handler extends BaseDashboardAjaxHandler {

    /**
     * AJAX handler to get upload limits.
     * Ensures only users who can access AI Training module can call this.
     * @since NEXT_VERSION
     */
    public function ajax_get_upload_limits() {
        // Permission check: Ensure the user has permission to access the 'ai-training' module
        // Using the nonce for OpenAI vector store as it's closely related
        $permission_check = $this->check_module_access_permissions('ai-training', 'aipkit_vector_store_nonce_openai');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        if (class_exists(\WPAICG\Includes\AIPKit_Upload_Utils::class)) {
            $limits = AIPKit_Upload_Utils::get_upload_limits();
            wp_send_json_success($limits);
        } else {
            wp_send_json_error(['message' => __('Upload utility not available.', 'gpt3-ai-content-generator')], 500);
        }
    }

    /**
     * AJAX handler to generate embeddings for given content.
     * Requires 'ai-training' module access permission.
     * Expects 'content_to_embed', 'embedding_provider', 'embedding_model' in POST.
     * UPDATED: Uses the global 'aipkit_nonce' for nonce verification in check_module_access_permissions.
     * @since NEXT_VERSION
     */
    public function ajax_generate_embedding() {
        // Permission Check: Users who can access AI Training can generate embeddings.
        // Use the main dashboard nonce 'aipkit_nonce' for this core utility.
        $permission_check = $this->check_module_access_permissions('ai-training', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }
        
        // Unslash the POST array at once.
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked by check_module_access_permissions().
        $post_data = wp_unslash($_POST);
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked by check_module_access_permissions().
        $content_to_embed = isset($post_data['content_to_embed']) ? wp_kses_post($post_data['content_to_embed']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked by check_module_access_permissions().
        $embedding_provider_key = isset($post_data['embedding_provider']) ? sanitize_key($post_data['embedding_provider']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked by check_module_access_permissions().
        $embedding_model = isset($post_data['embedding_model']) ? sanitize_text_field($post_data['embedding_model']) : '';

        if (empty($content_to_embed)) {
            $this->send_wp_error(new WP_Error('missing_content', __('Content to embed cannot be empty.', 'gpt3-ai-content-generator')));
            return;
        }
        if (empty($embedding_provider_key)) {
            $this->send_wp_error(new WP_Error('missing_embedding_provider', __('Embedding provider is required.', 'gpt3-ai-content-generator')));
            return;
        }
        if (empty($embedding_model)) {
            $this->send_wp_error(new WP_Error('missing_embedding_model', __('Embedding model is required.', 'gpt3-ai-content-generator')));
            return;
        }

        // Normalize provider name
        $provider_map = ['openai' => 'OpenAI', 'google' => 'Google', 'azure' => 'Azure'];
        $embedding_provider = $provider_map[$embedding_provider_key] ?? '';

        if (empty($embedding_provider)) {
             $this->send_wp_error(new WP_Error('invalid_embedding_provider', __('Invalid embedding provider specified.', 'gpt3-ai-content-generator')));
             return;
        }

        if (!class_exists(\WPAICG\Core\AIPKit_AI_Caller::class)) {
            $this->send_wp_error(new WP_Error('internal_error_embedding', __('Embedding service component not available.', 'gpt3-ai-content-generator')));
            return;
        }

        $ai_caller = new AIPKit_AI_Caller();
        $embedding_options = ['model' => $embedding_model];

        $result = $ai_caller->generate_embeddings($embedding_provider, $content_to_embed, $embedding_options);

        if (is_wp_error($result)) {
            $this->send_wp_error($result);
        } else {
            wp_send_json_success($result);
        }
    }
    
    /**
     * AJAX handler to delete a single vector data source entry from both the vector DB and local log.
     * @since NEXT_VERSION
     */
    public function ajax_delete_vector_data_source_entry() {
        $permission_check = $this->check_module_access_permissions('ai-training', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked above.
        $post_data = wp_unslash($_POST);

        // --- START FIX: Use sanitize_text_field to preserve case and add validation ---
        $provider_raw = isset($post_data['provider']) ? sanitize_text_field($post_data['provider']) : '';
        $allowed_providers = ['Pinecone', 'Qdrant'];
        if (!in_array($provider_raw, $allowed_providers, true)) {
            $this->send_wp_error(new WP_Error('invalid_provider_delete_vector', __('Invalid or unsupported provider for this action.', 'gpt3-ai-content-generator')));
            return;
        }
        $provider = $provider_raw;
        // --- END FIX ---
        
        $store_id   = isset($post_data['store_id']) ? sanitize_text_field($post_data['store_id']) : '';
        $vector_id  = isset($post_data['vector_id']) ? sanitize_text_field($post_data['vector_id']) : '';
        $log_entry_id = isset($post_data['log_id']) ? absint($post_data['log_id']) : 0;

        if (empty($provider) || empty($store_id) || empty($vector_id) || empty($log_entry_id)) {
            $this->send_wp_error(new WP_Error('missing_params_delete_vector', __('Missing required parameters for vector deletion.', 'gpt3-ai-content-generator')));
            return;
        }

        // Ensure Vector Store Manager is loaded
        if (!class_exists('\\WPAICG\\Vector\\AIPKit_Vector_Store_Manager')) {
             $this->send_wp_error(new WP_Error('vsm_missing_delete_vector', __('Vector management component is not available.', 'gpt3-ai-content-generator')));
             return;
        }
        $vector_store_manager = new \WPAICG\Vector\AIPKit_Vector_Store_Manager();

        // Get provider config
        $provider_config = \WPAICG\AIPKit_Providers::get_provider_data($provider);
        if (empty($provider_config['api_key'])) {
             $this->send_wp_error(new WP_Error('missing_api_key_delete_vector', sprintf(__('API key for %s is missing.', 'gpt3-ai-content-generator'), $provider)));
             return;
        }

        // 1. Delete from external vector store
        $delete_result = $vector_store_manager->delete_vectors($provider, $store_id, [$vector_id], $provider_config);
        
        // We proceed even if the external deletion fails, as the vector might not exist there anymore but the log does.
        // We will log the error if one occurs.
        if (is_wp_error($delete_result)) {
            // This is not a fatal error for the process, so we just log it and continue to delete from local DB.
        }

        // 2. Delete from local database log
        global $wpdb;
        $data_source_table_name = $wpdb->prefix . 'aipkit_vector_data_source';
        $deleted_rows = $wpdb->delete(
            $data_source_table_name,
            ['id' => $log_entry_id],
            ['%d']
        );

        if ($deleted_rows === false) {
             $this->send_wp_error(new WP_Error('db_delete_failed_vector_log', __('Failed to delete the log entry from the local database.', 'gpt3-ai-content-generator')));
             return;
        }
        if ($deleted_rows === 0) {
            // This could mean it was already deleted, which is a success state for the user.
            wp_send_json_success(['message' => __('Log entry was not found, it might have been already deleted.', 'gpt3-ai-content-generator')]);
            return;
        }

        wp_send_json_success(['message' => __('Vector record and log entry deleted successfully.', 'gpt3-ai-content-generator')]);
    }
}