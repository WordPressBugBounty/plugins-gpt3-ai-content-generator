<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/ajax/class-aipkit-core-ajax-handler.php
// MODIFIED FILE

namespace WPAICG\Core\Ajax;

use WPAICG\Dashboard\Ajax\BaseDashboardAjaxHandler; // Use the base dashboard handler
use WPAICG\AIPKit_Role_Manager;
use WPAICG\Includes\AIPKit_Upload_Utils;
use WPAICG\Core\AIPKit_AI_Caller; // For AI Caller
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

        $content_to_embed = isset($_POST['content_to_embed']) ? wp_kses_post(wp_unslash($_POST['content_to_embed'])) : '';
        $embedding_provider_key = isset($_POST['embedding_provider']) ? sanitize_key($_POST['embedding_provider']) : '';
        $embedding_model = isset($_POST['embedding_model']) ? sanitize_text_field($_POST['embedding_model']) : '';

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
}