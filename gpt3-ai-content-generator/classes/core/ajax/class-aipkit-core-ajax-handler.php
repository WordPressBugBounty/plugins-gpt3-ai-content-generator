<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/ajax/class-aipkit-core-ajax-handler.php
// Status: MODIFIED

namespace WPAICG\Core\Ajax;

use WPAICG\Dashboard\Ajax\BaseDashboardAjaxHandler;
use WPAICG\Includes\AIPKit_Upload_Utils;
use WPAICG\Core\AIPKit_AI_Caller; // For AI Caller
use WPAICG\AIPKit_Providers;
use WPAICG\Vector\AIPKit_Vector_Store_Manager;
use WPAICG\Vector\PostProcessor\OpenAI\OpenAIPostProcessor;
use WPAICG\Vector\PostProcessor\Pinecone\PineconePostProcessor;
use WPAICG\Vector\PostProcessor\Qdrant\QdrantPostProcessor;
use WPAICG\Chat\Storage\LogStorage;
use WPAICG\Chat\Storage\LogCronManager;
use WPAICG\Chat\Storage\LogManager;
use WPAICG\Chat\Utils\LogConfig;
use WPAICG\Stats\AIPKit_Stats;
use WP_Error; // For WP_Error usage

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles core AJAX actions not specific to a particular module.
 */
class AIPKit_Core_Ajax_Handler extends BaseDashboardAjaxHandler
{
    private $ai_caller;
    private $vector_store_manager;
    private $openai_post_processor;
    private $pinecone_post_processor;
    private $qdrant_post_processor;

    public function __construct()
    {
        if (class_exists(\WPAICG\Core\AIPKit_AI_Caller::class)) {
            $this->ai_caller = new AIPKit_AI_Caller();
        }
        if (class_exists(\WPAICG\Vector\AIPKit_Vector_Store_Manager::class)) {
            $this->vector_store_manager = new AIPKit_Vector_Store_Manager();
        }
        if (class_exists(OpenAIPostProcessor::class)) {
            $this->openai_post_processor = new OpenAIPostProcessor();
        }
        if (class_exists(PineconePostProcessor::class)) {
            $this->pinecone_post_processor = new PineconePostProcessor();
        }
        if (class_exists(QdrantPostProcessor::class)) {
            $this->qdrant_post_processor = new QdrantPostProcessor();
        }
    }


    /**
     * AJAX handler to get upload limits.
     * Ensures only users who can access Sources or Chatbot module can call this.
     * @since NEXT_VERSION
     */
    public function ajax_get_upload_limits()
    {
        // Permission check: accept any valid nonce from AI Training UIs (global or provider-specific)
        $nonce_candidates = [
            'aipkit_nonce',
            'aipkit_vector_store_nonce_openai',
            'aipkit_vector_store_pinecone_nonce',
            'aipkit_vector_store_qdrant_nonce',
        ];
        $has_permission = false;
        $last_error = null;
        foreach ($nonce_candidates as $nonce_field) {
            $check = $this->check_any_module_access_permissions(['sources', 'chatbot'], $nonce_field);
            if (!is_wp_error($check)) { $has_permission = true; break; }
            $last_error = $check;
        }
        if (!$has_permission) {
            $this->send_wp_error($last_error ?: new WP_Error('forbidden', __('Permission denied.', 'gpt3-ai-content-generator')));
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
     * Requires 'sources' or 'chatbot' module access permission.
     * Expects 'content_to_embed', 'embedding_provider', 'embedding_model' in POST.
     * UPDATED: Uses the global 'aipkit_nonce' for nonce verification.
     * @since NEXT_VERSION
     */
    public function ajax_generate_embedding()
    {
        // Permission Check: Users who can access Sources or AI Training can generate embeddings.
        // Use the main dashboard nonce 'aipkit_nonce' for this core utility.
        $permission_check = $this->check_any_module_access_permissions(
            ['sources', 'chatbot'],
            'aipkit_nonce'
        );
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
    public function ajax_delete_vector_data_source_entry()
    {
        $permission_check = $this->check_any_module_access_permissions(
            ['sources', 'chatbot'],
            'aipkit_nonce'
        );
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked above.
        $post_data = wp_unslash($_POST);

        // --- START FIX: Use sanitize_text_field to preserve case and add validation ---
        $provider_raw = isset($post_data['provider']) ? sanitize_text_field($post_data['provider']) : '';
        $allowed_providers = ['OpenAI', 'Pinecone', 'Qdrant']; // <-- FIX: Added 'OpenAI'
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
            /* translators: %s: Provider name. */
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table delete for admin action.
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

    /**
     * AJAX handler to re-index a single vector data source entry from a WordPress post.
     * @since 2.4.2
     */
    public function ajax_reindex_vector_data_source_entry() {
        $permission_check = $this->check_any_module_access_permissions(
            ['sources', 'chatbot'],
            'aipkit_nonce'
        );
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // Sanitize all inputs
        $post_data = wp_unslash($_POST);
        $provider = isset($post_data['provider']) ? sanitize_text_field($post_data['provider']) : '';
        $store_id = isset($post_data['store_id']) ? sanitize_text_field($post_data['store_id']) : '';
        $vector_id = isset($post_data['vector_id']) ? sanitize_text_field($post_data['vector_id']) : '';
        $log_id = isset($post_data['log_id']) ? absint($post_data['log_id']) : 0;
        $post_id = isset($post_data['post_id']) ? absint($post_data['post_id']) : 0;
        $embedding_provider = isset($post_data['embedding_provider']) ? sanitize_key($post_data['embedding_provider']) : '';
        $embedding_model = isset($post_data['embedding_model']) ? sanitize_text_field($post_data['embedding_model']) : '';

        // Validate required parameters
        if (empty($provider) || empty($store_id) || empty($vector_id) || empty($log_id) || empty($post_id)) {
            $this->send_wp_error(new WP_Error('missing_params_reindex', __('Missing required parameters for re-indexing.', 'gpt3-ai-content-generator')));
            return;
        }

        // Validate dependencies
        if (!$this->vector_store_manager || !$this->openai_post_processor || !$this->pinecone_post_processor || !$this->qdrant_post_processor) {
            $this->send_wp_error(new WP_Error('vsm_missing_reindex', __('Vector processing components are not available.', 'gpt3-ai-content-generator')));
            return;
        }

        // Step 1: Delete the existing vector and log entry
        $provider_config = AIPKit_Providers::get_provider_data($provider);
        if (empty($provider_config['api_key'])) {
            /* translators: %s: Provider name. */
             $this->send_wp_error(new WP_Error('missing_api_key_reindex', sprintf(__('API key for %s is missing.', 'gpt3-ai-content-generator'), $provider)));
             return;
        }
        $delete_result = $this->vector_store_manager->delete_vectors($provider, $store_id, [$vector_id], $provider_config);
        if (is_wp_error($delete_result)) {
            // Log this but don't fail, as the vector might already be gone from the remote.
        }

        global $wpdb;
        $data_source_table_name = $wpdb->prefix . 'aipkit_vector_data_source';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table delete for admin action.
        $wpdb->delete($data_source_table_name, ['id' => $log_id], ['%d']);

        // Step 2: Re-index the post
        $reindex_result = null;
        switch ($provider) {
            case 'OpenAI':
                $reindex_result = $this->openai_post_processor->index_single_post_to_store($post_id, $store_id);
                break;
            case 'Pinecone':
                if (empty($embedding_provider) || empty($embedding_model)) {
                    $this->send_wp_error(new WP_Error('missing_embedding_config_reindex', __('Embedding provider and model are required for Pinecone re-indexing.', 'gpt3-ai-content-generator')));
                    return;
                }
                $reindex_result = $this->pinecone_post_processor->index_single_post_to_index($post_id, $store_id, $embedding_provider, $embedding_model);
                break;
            case 'Qdrant':
                if (empty($embedding_provider) || empty($embedding_model)) {
                    $this->send_wp_error(new WP_Error('missing_embedding_config_reindex', __('Embedding provider and model are required for Qdrant re-indexing.', 'gpt3-ai-content-generator')));
                    return;
                }
                $reindex_result = $this->qdrant_post_processor->index_single_post_to_collection($post_id, $store_id, $embedding_provider, $embedding_model);
                break;
            default:
                $this->send_wp_error(new WP_Error('invalid_provider_reindex', __('Invalid provider for re-indexing.', 'gpt3-ai-content-generator')));
                return;
        }

        if (isset($reindex_result['status']) && $reindex_result['status'] === 'success') {
            wp_send_json_success(['message' => __('Content successfully re-indexed.', 'gpt3-ai-content-generator')]);
        } else {
            $error_message = $reindex_result['message'] ?? __('An unknown error occurred during re-indexing.', 'gpt3-ai-content-generator');
            $this->send_wp_error(new WP_Error('reindex_failed', 'Re-indexing failed: ' . $error_message));
        }
    }

    /**
     * AJAX handler to fetch global vector sources for the Sources module.
     * @since NEXT_VERSION
     */
    public function ajax_get_global_vector_sources()
    {
        $permission_check = $this->check_module_access_permissions('sources', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked above.
        $post_data = wp_unslash($_POST);

        $provider_map = $this->get_vector_source_provider_map();
        $filters = $this->get_vector_sources_filters($post_data, $provider_map);
        [$where_sql, $params] = $this->build_vector_sources_where($filters);

        global $wpdb;
        $table_name = $wpdb->prefix . 'aipkit_vector_data_source';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table_name is safe.
        $total_logs = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}", $params));

        $logs_params = array_merge($params, [$filters['per_page'], $filters['offset']]);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table_name is safe.
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, timestamp, provider, status, message, indexed_content, post_id, post_title, file_id, batch_id, embedding_provider, embedding_model, vector_store_id, vector_store_name FROM {$table_name} WHERE {$where_sql} ORDER BY timestamp DESC LIMIT %d OFFSET %d",
                $logs_params
            ),
            ARRAY_A
        );

        $total_pages = $filters['per_page'] > 0 ? (int) ceil($total_logs / $filters['per_page']) : 0;

        wp_send_json_success([
            'logs' => $logs ?: [],
            'pagination' => [
                'total_logs' => $total_logs,
                'total_pages' => $total_pages,
                'current_page' => $filters['page'],
            ],
            'providers' => $this->get_available_vector_source_providers($provider_map),
        ]);
    }

    /**
     * Provider label map for vector sources.
     *
     * @return array<string, string>
     */
    private function get_vector_source_provider_map(): array
    {
        return [
            'openai' => 'OpenAI',
            'pinecone' => 'Pinecone',
            'qdrant' => 'Qdrant',
        ];
    }

    /**
     * Normalize filters for vector source queries.
     *
     * @param array<string, mixed> $post_data Raw POST data.
     * @param array<string, string> $provider_map Provider key => label.
     * @return array<string, mixed>
     */
    private function get_vector_sources_filters(array $post_data, array $provider_map): array
    {
        $page = isset($post_data['page']) ? max(1, absint($post_data['page'])) : 1;
        $per_page = isset($post_data['per_page']) ? absint($post_data['per_page']) : 10;
        $per_page = min(50, max(1, $per_page));

        $provider_key = isset($post_data['provider']) ? sanitize_key($post_data['provider']) : '';
        $provider_label = $provider_key && isset($provider_map[$provider_key]) ? $provider_map[$provider_key] : '';

        $status_filter = isset($post_data['status']) ? sanitize_key($post_data['status']) : '';
        $allowed_statuses = ['indexed', 'failed', 'processing', 'queued', 'skipped_already_indexed', 'success'];
        if ($status_filter && !in_array($status_filter, $allowed_statuses, true)) {
            $status_filter = '';
        }

        $type_filter = isset($post_data['type']) ? sanitize_key($post_data['type']) : '';
        $allowed_types = ['site', 'text', 'file'];
        if ($type_filter && !in_array($type_filter, $allowed_types, true)) {
            $type_filter = '';
        }

        $general_settings = get_option('aipkit_training_general_settings', []);
        $hide_user_uploads = isset($general_settings['hide_user_uploads'])
            ? (bool) $general_settings['hide_user_uploads']
            : true;

        return [
            'page' => $page,
            'per_page' => $per_page,
            'offset' => ($page - 1) * $per_page,
            'provider_label' => $provider_label,
            'status' => $status_filter,
            'type' => $type_filter,
            'search' => isset($post_data['search']) ? sanitize_text_field($post_data['search']) : '',
            'store_id' => isset($post_data['store_id']) ? sanitize_text_field($post_data['store_id']) : '',
            'hide_user_uploads' => $hide_user_uploads,
        ];
    }

    /**
     * Builds WHERE clause and params for vector source queries.
     *
     * @param array<string, mixed> $filters Normalized filters.
     * @return array{0: string, 1: array<int, mixed>}
     */
    private function build_vector_sources_where(array $filters): array
    {
        global $wpdb;

        $where_clauses = ['(post_id IS NOT NULL OR file_id IS NOT NULL OR indexed_content IS NOT NULL)'];
        $params = [];

        if (!empty($filters['provider_label'])) {
            $where_clauses[] = 'provider = %s';
            $params[] = $filters['provider_label'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'processing') {
                $where_clauses[] = '(status = %s OR status = %s)';
                $params[] = 'processing';
                $params[] = 'queued';
            } elseif ($filters['status'] === 'indexed') {
                $where_clauses[] = '(status = %s OR status = %s OR status = %s)';
                $params[] = 'indexed';
                $params[] = 'skipped_already_indexed';
                $params[] = 'success';
            } else {
                $where_clauses[] = 'status = %s';
                $params[] = $filters['status'];
            }
        }

        if (!empty($filters['type'])) {
            if ($filters['type'] === 'site') {
                $where_clauses[] = '(' . implode(' OR ', [
                    '(post_id IS NOT NULL)',
                    '(message LIKE %s)',
                    '(file_id LIKE %s)',
                    '(provider = %s AND message LIKE %s AND message LIKE %s)',
                ]) . ')';
                $params[] = '%wordpress post content submitted for indexing%';
                $params[] = 'wp_post_%';
                $params[] = 'Qdrant';
                $params[] = '%points upserted to qdrant%';
                $params[] = '%post id:%';
            } elseif ($filters['type'] === 'text') {
                $where_clauses[] = '(' . implode(' OR ', [
                    '(message LIKE %s)',
                    '(file_id LIKE %s)',
                    '(provider = %s AND message LIKE %s AND (post_id IS NULL OR post_id = 0) AND message NOT LIKE %s)',
                ]) . ')';
                $params[] = '%text content submitted for indexing%';
                $params[] = 'text_%';
                $params[] = 'Qdrant';
                $params[] = '%points upserted to qdrant%';
                $params[] = '%post id:%';
            } elseif ($filters['type'] === 'file') {
                $where_clauses[] = '(' . implode(' OR ', [
                    '(message LIKE %s)',
                    '(message LIKE %s)',
                    '(message LIKE %s)',
                    '(message LIKE %s)',
                    '(file_id LIKE %s)',
                ]) . ')';
                $params[] = '%file content submitted for indexing%';
                $params[] = '%file content embedded and upserted%';
                $params[] = '%original filename:%';
                $params[] = '%file uploaded%';
                $params[] = 'pinecone_file_%';
            }
        }

        if (!empty($filters['hide_user_uploads'])) {
            $where_clauses[] = 'NOT (' . implode(' OR ', [
                '(provider = %s AND COALESCE(vector_store_name, \'\') LIKE %s)',
                '(provider = %s AND COALESCE(file_id, \'\') LIKE %s)',
                '(provider = %s AND (COALESCE(batch_id, \'\') LIKE %s OR COALESCE(file_id, \'\') LIKE %s))',
            ]) . ')';
            $params[] = 'OpenAI';
            $params[] = 'chat_file_%';
            $params[] = 'Pinecone';
            $params[] = 'chatfile_%';
            $params[] = 'Qdrant';
            $params[] = 'qdrant_chat_file_%';
            $params[] = 'qdrant_chat_file_%';
        }

        if (!empty($filters['search'])) {
            $like = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_clauses[] = '(message LIKE %s OR post_title LIKE %s OR file_id LIKE %s OR vector_store_name LIKE %s OR indexed_content LIKE %s)';
            $params = array_merge($params, array_fill(0, 5, $like));
        }

        if (!empty($filters['store_id'])) {
            $where_clauses[] = 'vector_store_id = %s';
            $params[] = $filters['store_id'];
        }

        return [implode(' AND ', $where_clauses), $params];
    }

    /**
     * Returns available provider options based on stored vector source entries.
     *
     * @param array<string, string> $provider_map Provider key => label.
     * @return array<int, array<string, string>>
     */
    private function get_available_vector_source_providers(array $provider_map): array
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'aipkit_vector_data_source';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Distinct providers from custom table.
        $provider_labels = $wpdb->get_col("SELECT DISTINCT provider FROM {$table_name} WHERE provider <> ''");
        $provider_labels = array_filter(array_map('sanitize_text_field', (array) $provider_labels));

        if (empty($provider_labels)) {
            $provider_labels = array_values($provider_map);
        }

        $providers = [];
        foreach ($provider_map as $key => $label) {
            if (in_array($label, $provider_labels, true)) {
                $providers[] = [
                    'key' => $key,
                    'label' => $label,
                ];
            }
        }

        return $providers;
    }

    /**
     * AJAX: Return chunk logs for a given provider/store and batch_id with pagination.
     * @since NEXT_VERSION
     */
    public function ajax_get_chunk_logs_by_batch()
    {
        $permission_check = $this->check_any_module_access_permissions(['sources', 'chatbot'], 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'aipkit_vector_data_source';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- already checked
        $post = wp_unslash($_POST);
        $provider = isset($post['provider']) ? sanitize_text_field($post['provider']) : '';
        $store_id = isset($post['store_id']) ? sanitize_text_field($post['store_id']) : '';
        $batch_id = isset($post['batch_id']) ? sanitize_text_field($post['batch_id']) : '';
        $page = isset($post['page']) ? max(1, absint($post['page'])) : 1;
        $per_page = isset($post['per_page']) ? min(50, max(1, absint($post['per_page']))) : 10;

        if (!$provider || !$store_id || !$batch_id) {
            $this->send_wp_error(new \WP_Error('missing_params_chunk_logs', __('Missing provider, store_id or batch_id.', 'gpt3-ai-content-generator')));
            return;
        }

        $offset = ($page - 1) * $per_page;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe; logs are dynamic.
        $total = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE provider = %s AND vector_store_id = %s AND batch_id = %s",
            $provider, $store_id, $batch_id
        ));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Table name is safe; logs are dynamic.
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT id, file_id, indexed_content, timestamp FROM {$table} WHERE provider = %s AND vector_store_id = %s AND batch_id = %s ORDER BY id ASC LIMIT %d OFFSET %d",
            $provider, $store_id, $batch_id, $per_page, $offset
        ), ARRAY_A);

        if ($wpdb->last_error) {
            $this->send_wp_error(new \WP_Error('db_error_chunk_logs', __('Failed to fetch chunk logs.', 'gpt3-ai-content-generator'), ['status' => 500]));
            return;
        }

        wp_send_json_success([
            'chunks' => $rows,
            'pagination' => [
                'total' => $total,
                'total_pages' => $per_page ? (int) ceil($total / $per_page) : 1,
                'current_page' => $page,
                'per_page' => $per_page,
            ],
        ]);
    }

    /**
     * AJAX: Retrieves CPTs and their fields/taxonomies for indexing settings UI.
     * @since 2.4.0
     */
    public function ajax_get_cpt_indexing_options()
    {
        $permission_check = $this->check_module_access_permissions(
            'sources',
            'aipkit_ai_training_settings_nonce'
        );
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        $cpt_data = [];
        $post_types = get_post_types(['public' => true], 'objects');
        unset($post_types['attachment']);

        foreach ($post_types as $cpt) {
            $taxonomies = get_object_taxonomies($cpt->name, 'objects');
            $public_taxonomies = [];
            foreach ($taxonomies as $tax) {
                if ($tax->public) {
                    $public_taxonomies[$tax->name] = $tax->label;
                }
            }

            $cpt_data[$cpt->name] = [
                'label'      => $cpt->label,
                'fields'     => $this->get_public_meta_keys_for_post_type($cpt->name),
                'taxonomies' => $public_taxonomies,
                'basic_labels' => [
                    'source_url' => __('Source URL', 'gpt3-ai-content-generator'),
                    'title'      => __('Title', 'gpt3-ai-content-generator'),
                    'excerpt'    => __('Excerpt', 'gpt3-ai-content-generator'),
                    'content'    => __('Content', 'gpt3-ai-content-generator'),
                ]
            ];

            if ($cpt->name === 'product' && class_exists('WooCommerce')) {
                $cpt_data[$cpt->name]['woo_attributes'] = [
                    'sku'        => __('SKU', 'gpt3-ai-content-generator'),
                    'price'      => __('Price', 'gpt3-ai-content-generator'),
                    'stock'      => __('Stock Status', 'gpt3-ai-content-generator'),
                    'dimensions' => __('Weight & Dimensions', 'gpt3-ai-content-generator'),
                    'attributes' => __('Product Attributes', 'gpt3-ai-content-generator'),
                ];
            }
        }

        $saved_settings = get_option('aipkit_indexing_field_settings', []);

        $response_data = [
            'cpt_data'       => $cpt_data,
            'saved_settings' => $saved_settings,
        ];
        
        wp_send_json_success($response_data);
    }

    /**
     * AJAX: Saves the CPT indexing field settings.
     * @since 2.4.0
     */
    public function ajax_save_cpt_indexing_options()
    {
        $permission_check = $this->check_module_access_permissions(
            'sources',
            'aipkit_ai_training_settings_nonce'
        );
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }
        $settings_json = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '{}';
        $settings = json_decode($settings_json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($settings)) {
            $this->send_wp_error(new WP_Error('invalid_json', __('Invalid settings format.', 'gpt3-ai-content-generator')));
            return;
        }

        // --- NEW: Handle general settings separately ---
        $general_settings = get_option('aipkit_training_general_settings', []);
        $general_dirty = false;
        if (isset($settings['hide_user_uploads'])) {
            $general_settings['hide_user_uploads'] = (bool) $settings['hide_user_uploads'];
            unset($settings['hide_user_uploads']);
            $general_dirty = true;
        }
        if (isset($settings['show_index_button'])) {
            $general_settings['show_index_button'] = (bool) $settings['show_index_button'];
            unset($settings['show_index_button']);
            $general_dirty = true;
        }
        // Chunking settings (Pro only)
        if (isset($settings['chunk_avg_chars_per_token']) || isset($settings['chunk_max_tokens_per_chunk']) || isset($settings['chunk_overlap_tokens'])) {
            // Only allow saving if Pro
            $is_pro = \WPAICG\aipkit_dashboard::is_pro_plan();
            if ($is_pro) {
                $avg = isset($settings['chunk_avg_chars_per_token']) ? (int)$settings['chunk_avg_chars_per_token'] : null;
                $max = isset($settings['chunk_max_tokens_per_chunk']) ? (int)$settings['chunk_max_tokens_per_chunk'] : null;
                $ovl = isset($settings['chunk_overlap_tokens']) ? (int)$settings['chunk_overlap_tokens'] : null;

                // Validate ranges
                if ($avg !== null) {
                    if ($avg < 2 || $avg > 10) {
                        $this->send_wp_error(new \WP_Error('invalid_chunk_avg', __('Average chars per token must be between 2 and 10.', 'gpt3-ai-content-generator')));
                        return;
                    }
                    $general_settings['chunk_avg_chars_per_token'] = $avg;
                    $general_dirty = true;
                }
                if ($max !== null) {
                    if ($max < 256 || $max > 8000) {
                        $this->send_wp_error(new \WP_Error('invalid_chunk_max', __('Max tokens per chunk must be between 256 and 8000.', 'gpt3-ai-content-generator')));
                        return;
                    }
                    $general_settings['chunk_max_tokens_per_chunk'] = $max;
                    $general_dirty = true;
                }
                if ($ovl !== null) {
                    if ($ovl < 0 || $ovl > 1000) {
                        $this->send_wp_error(new \WP_Error('invalid_chunk_overlap', __('Overlap tokens must be between 0 and 1000.', 'gpt3-ai-content-generator')));
                        return;
                    }
                    $general_settings['chunk_overlap_tokens'] = $ovl;
                    $general_dirty = true;
                }
            }
            // Remove from specific settings payload
            unset($settings['chunk_avg_chars_per_token'], $settings['chunk_max_tokens_per_chunk'], $settings['chunk_overlap_tokens']);
        }

        if ($general_dirty) {
            update_option('aipkit_training_general_settings', $general_settings);
        }
        // --- END NEW ---

        // Sanitize the settings array
        $sanitized_settings = [];
        foreach ($settings as $cpt => $cpt_settings) {
            $cpt = sanitize_key($cpt);
            $sanitized_settings[$cpt] = [
                'fields' => [],
                'taxonomies' => [],
                'woo_attributes' => [],
                'basic_labels' => [],
            ];
            
            // Handle basic labels
            if (isset($cpt_settings['basic_labels']) && is_array($cpt_settings['basic_labels'])) {
                $allowed_basic_labels = ['source_url', 'title', 'excerpt', 'content'];
                foreach ($cpt_settings['basic_labels'] as $key => $label) {
                    if (in_array($key, $allowed_basic_labels)) {
                        $sanitized_settings[$cpt]['basic_labels'][sanitize_key($key)] = sanitize_text_field($label);
                    }
                }
            }
            
            if (isset($cpt_settings['fields']) && is_array($cpt_settings['fields'])) {
                foreach ($cpt_settings['fields'] as $key => $config) {
                    // Preserve original meta key (may include ":" etc.)
                    $key = is_string($key) ? wp_unslash($key) : $key;
                    // Ensure enabled is properly converted to boolean
                    $enabled = isset($config['enabled']) && $config['enabled'];
                    $sanitized_settings[$cpt]['fields'][$key] = [
                        'enabled' => (bool) $enabled,
                        'label'   => sanitize_text_field($config['label'] ?? ''),
                    ];
                }
            }
            if (isset($cpt_settings['taxonomies']) && is_array($cpt_settings['taxonomies'])) {
                foreach ($cpt_settings['taxonomies'] as $key => $config) {
                    // Preserve original taxonomy slug key
                    $key = is_string($key) ? wp_unslash($key) : $key;
                    // Ensure enabled is properly converted to boolean
                    $enabled = isset($config['enabled']) && $config['enabled'];
                    $sanitized_settings[$cpt]['taxonomies'][$key] = [
                        'enabled' => (bool) $enabled,
                        'label'   => sanitize_text_field($config['label'] ?? ''),
                    ];
                }
            }
            if (isset($cpt_settings['woo_attributes']) && is_array($cpt_settings['woo_attributes'])) {
                foreach ($cpt_settings['woo_attributes'] as $key => $config) {
                    // Preserve original key
                    $key = is_string($key) ? wp_unslash($key) : $key;
                    // Ensure enabled is properly converted to boolean
                    $enabled = isset($config['enabled']) && $config['enabled'];
                    $sanitized_settings[$cpt]['woo_attributes'][$key] = [
                        'enabled' => (bool) $enabled,
                        'label'   => sanitize_text_field($config['label'] ?? ''),
                    ];
                }
            }
        }

        update_option('aipkit_indexing_field_settings', $sanitized_settings, 'no');

        // DEBUG: Log the saved settings structure
        wp_send_json_success(['message' => __('Indexing settings saved successfully.', 'gpt3-ai-content-generator')]);
    }

    /**
     * Fetches public meta keys for a given post type by sampling recent posts.
     * @param string $post_type
     * @param int $limit
     * @return array
     */
    private function get_public_meta_keys_for_post_type(string $post_type, int $limit = 10): array
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Efficiently sampling meta keys.
        $keys = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.post_type = %s AND meta_key NOT LIKE %s ORDER BY pm.meta_id DESC LIMIT 100",
            $post_type,
            $wpdb->esc_like('_') . '%'
        ));
        $formatted_keys = [];
        if ($keys) {
            foreach ($keys as $key) {
                $formatted_keys[$key] = ucwords(str_replace(['_', '-'], ' ', $key));
            }
        }
        return $formatted_keys;
    }

    private function resolve_stats_days($raw_days): int
    {
        $days = absint($raw_days);
        $allowed = [7, 30, 90];
        if (!in_array($days, $allowed, true)) {
            $days = 30;
        }
        return $days;
    }

    private function get_stats_time_range(int $days): array
    {
        $wp_timezone = wp_timezone();
        $end_datetime = new \DateTime('now', $wp_timezone);
        $start_datetime = new \DateTime("-{$days} days", $wp_timezone);
        $start_datetime->setTime(0, 0, 0);

        return [
            'start_ts' => $start_datetime->getTimestamp(),
            'end_ts' => $end_datetime->getTimestamp(),
            'period_label' => sprintf(
                /* translators: %d: number of days */
                __('Last %d days', 'gpt3-ai-content-generator'),
                $days
            ),
        ];
    }

    public function ajax_get_stats_overview()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $days = $this->resolve_stats_days($post_data['days'] ?? 30);
        $range = $this->get_stats_time_range($days);

        global $wpdb;
        $table_name = $wpdb->prefix . 'aipkit_chat_logs';

        $total_conversations = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE last_message_ts >= %d AND last_message_ts <= %d",
            $range['start_ts'],
            $range['end_ts']
        ));

        $total_messages = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(message_count), 0) FROM {$table_name} WHERE last_message_ts >= %d AND last_message_ts <= %d",
            $range['start_ts'],
            $range['end_ts']
        ));

        $unique_users = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT CASE
                WHEN is_guest = 1 AND session_id IS NOT NULL AND session_id != '' THEN CONCAT('g:', session_id)
                WHEN user_id IS NOT NULL THEN CONCAT('u:', user_id)
                ELSE NULL
            END)
            FROM {$table_name}
            WHERE last_message_ts >= %d AND last_message_ts <= %d",
            $range['start_ts'],
            $range['end_ts']
        ));

        $daily_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT FROM_UNIXTIME(last_message_ts, '%%Y-%%m-%%d') AS day, COUNT(*) AS cnt
             FROM {$table_name}
             WHERE last_message_ts >= %d AND last_message_ts <= %d
             GROUP BY day
             ORDER BY day ASC",
            $range['start_ts'],
            $range['end_ts']
        ), ARRAY_A);

        $daily_conversations = [];
        if (!empty($daily_rows)) {
            foreach ($daily_rows as $row) {
                if (!empty($row['day'])) {
                    $daily_conversations[$row['day']] = (int) $row['cnt'];
                }
            }
        }

        $avg_tokens = 0;
        $daily_tokens = [];
        if (class_exists(AIPKit_Stats::class)) {
            $stats = new AIPKit_Stats();
            $token_stats = $stats->get_token_stats_last_days($days);
            if (!is_wp_error($token_stats)) {
                $avg_tokens = (int) ($token_stats['avg_tokens_per_interaction'] ?? 0);
            }
            $daily_stats = $stats->get_daily_token_stats($days);
            if (!is_wp_error($daily_stats)) {
                $daily_tokens = $daily_stats;
            }
        }

        wp_send_json_success([
            'summary' => [
                'total_conversations' => $total_conversations,
                'total_messages' => $total_messages,
                'unique_users' => $unique_users,
                'avg_tokens' => $avg_tokens,
                'period_label' => $range['period_label'],
            ],
            'charts' => [
                'daily_conversations' => $daily_conversations,
                'daily_tokens' => $daily_tokens,
            ],
        ]);
    }

    public function ajax_get_stats_logs()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $days = $this->resolve_stats_days($post_data['days'] ?? 30);
        $range = $this->get_stats_time_range($days);

        $page = isset($post_data['page']) ? absint($post_data['page']) : 1;
        $per_page = isset($post_data['per_page']) ? absint($post_data['per_page']) : 20;
        if ($page < 1) {
            $page = 1;
        }
        if ($per_page < 1) {
            $per_page = 20;
        }
        if ($per_page > 100) {
            $per_page = 100;
        }
        $offset = ($page - 1) * $per_page;

        $filters = [
            'start_ts' => $range['start_ts'],
            'end_ts' => $range['end_ts'],
        ];

        $bot_id_raw = isset($post_data['bot_id']) ? sanitize_text_field($post_data['bot_id']) : '';
        if ($bot_id_raw !== '') {
            $filters['bot_id'] = $bot_id_raw;
        }

        $module = isset($post_data['module']) ? sanitize_key($post_data['module']) : '';
        if ($module !== '') {
            $filters['module'] = $module;
        }

        $search = isset($post_data['search']) ? sanitize_text_field($post_data['search']) : '';
        if ($search !== '') {
            $filters['search'] = $search;
        }

        if (!class_exists(LogStorage::class)) {
            $this->send_wp_error(new WP_Error('missing_log_storage', __('Log storage is unavailable.', 'gpt3-ai-content-generator')));
            return;
        }

        $log_storage = new LogStorage();
        $logs = $log_storage->get_logs($filters, $per_page, $offset);
        $total_logs = $log_storage->count_logs($filters);
        $total_pages = $per_page > 0 ? (int) ceil($total_logs / $per_page) : 1;

        wp_send_json_success([
            'logs' => $logs ?: [],
            'pagination' => [
                'total_logs' => (int) $total_logs,
                'total_pages' => $total_pages,
                'current_page' => $page,
            ],
        ]);
    }

    public function ajax_get_stats_log_detail()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $log_id = isset($post_data['log_id']) ? absint($post_data['log_id']) : 0;
        if (!$log_id) {
            $this->send_wp_error(new WP_Error('missing_log_id', __('Log ID is required.', 'gpt3-ai-content-generator')));
            return;
        }

        if (!class_exists(LogStorage::class)) {
            $this->send_wp_error(new WP_Error('missing_log_storage', __('Log storage is unavailable.', 'gpt3-ai-content-generator')));
            return;
        }

        $log_storage = new LogStorage();
        $log_row = $log_storage->get_log_by_id($log_id);
        if (!$log_row) {
            $this->send_wp_error(new WP_Error('log_not_found', __('Log entry not found.', 'gpt3-ai-content-generator')));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'aipkit_chat_logs';
        $messages_json = $wpdb->get_var($wpdb->prepare(
            "SELECT messages FROM {$table_name} WHERE id = %d",
            $log_id
        ));

        $messages = [];
        if (!empty($messages_json)) {
            $conversation_data = json_decode($messages_json, true);
            if (is_array($conversation_data) && isset($conversation_data['messages']) && is_array($conversation_data['messages'])) {
                $messages = $conversation_data['messages'];
            } elseif (is_array($conversation_data)) {
                $messages = $conversation_data;
            }
        }

        $log_row['messages'] = $messages;
        $log_row['message_count'] = $log_row['message_count'] ?? count($messages);

        wp_send_json_success($log_row);
    }

    public function ajax_export_stats_logs()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $filters = $this->build_stats_log_filters($post_data);
        $limit = isset($post_data['limit']) ? absint($post_data['limit']) : 1000;
        if ($limit < 1) {
            $limit = 1000;
        }
        if ($limit > 5000) {
            $limit = 5000;
        }

        if (!class_exists(LogStorage::class)) {
            $this->send_wp_error(new WP_Error('missing_log_storage', __('Log storage is unavailable.', 'gpt3-ai-content-generator')));
            return;
        }

        $log_storage = new LogStorage();
        $logs = $log_storage->get_logs($filters, $limit, 0);
        $total_logs = $log_storage->count_logs($filters);

        $headers = [
            'log_id' => __('Log ID', 'gpt3-ai-content-generator'),
            'date' => __('Date', 'gpt3-ai-content-generator'),
            'user' => __('User', 'gpt3-ai-content-generator'),
            'source' => __('Source', 'gpt3-ai-content-generator'),
            'module' => __('Module', 'gpt3-ai-content-generator'),
            'messages' => __('Messages', 'gpt3-ai-content-generator'),
            'tokens' => __('Tokens', 'gpt3-ai-content-generator'),
            'preview' => __('Preview', 'gpt3-ai-content-generator'),
            'conversation_uuid' => __('Conversation UUID', 'gpt3-ai-content-generator'),
        ];

        $lines = [];
        $lines[] = implode(',', array_map([$this, 'escape_csv_field'], array_values($headers)));

        if (!empty($logs)) {
            foreach ($logs as $log_row) {
                $date = !empty($log_row['last_message_ts']) ? gmdate('Y-m-d H:i:s', (int) $log_row['last_message_ts']) : '';
                $preview = isset($log_row['last_message_content']) ? (string) $log_row['last_message_content'] : '';
                $line = [
                    $log_row['id'] ?? '',
                    $date,
                    $log_row['user_display_name'] ?? '',
                    $log_row['bot_name'] ?? '',
                    $log_row['module'] ?? '',
                    $log_row['message_count'] ?? '',
                    $log_row['total_conversation_tokens'] ?? '',
                    $preview,
                    $log_row['conversation_uuid'] ?? '',
                ];
                $lines[] = implode(',', array_map([$this, 'escape_csv_field'], $line));
            }
        }

        $csv = implode("\r\n", $lines);
        $filename = sprintf('aipkit-logs-%s.csv', gmdate('Ymd-His'));
        $message = __('Export ready.', 'gpt3-ai-content-generator');
        if ($total_logs > $limit) {
            $message = sprintf(
                /* translators: %d: number of exported rows */
                __('Exported first %d logs (filtered).', 'gpt3-ai-content-generator'),
                $limit
            );
        }

        wp_send_json_success([
            'csv' => $csv,
            'filename' => $filename,
            'message' => $message,
        ]);
    }

    public function ajax_delete_stats_log()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $log_id = isset($post_data['log_id']) ? absint($post_data['log_id']) : 0;
        if (!$log_id) {
            $this->send_wp_error(new WP_Error('missing_log_id', __('Log ID is required.', 'gpt3-ai-content-generator')));
            return;
        }

        if (!class_exists(LogStorage::class)) {
            $this->send_wp_error(new WP_Error('missing_log_storage', __('Log storage is unavailable.', 'gpt3-ai-content-generator')));
            return;
        }

        $log_storage = new LogStorage();
        $log_row = $log_storage->get_log_by_id($log_id);
        if (!$log_row) {
            $this->send_wp_error(new WP_Error('log_not_found', __('Log entry not found.', 'gpt3-ai-content-generator')));
            return;
        }

        $result = $log_storage->delete_single_conversation(
            $log_row['user_id'] ?? null,
            $log_row['session_id'] ?? null,
            $log_row['bot_id'] ?? null,
            $log_row['conversation_uuid'] ?? ''
        );
        if (is_wp_error($result)) {
            $this->send_wp_error($result);
            return;
        }

        wp_send_json_success([
            'message' => __('Conversation deleted.', 'gpt3-ai-content-generator'),
            'log_id' => $log_id,
        ]);
    }

    public function ajax_delete_stats_logs()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $filters = $this->build_stats_log_filters($post_data);

        if (!class_exists(LogStorage::class)) {
            $this->send_wp_error(new WP_Error('missing_log_storage', __('Log storage is unavailable.', 'gpt3-ai-content-generator')));
            return;
        }

        $log_storage = new LogStorage();
        $batch_size = 500;
        $max_batches = 200;
        $total_deleted = 0;
        $batch = 0;

        do {
            $deleted = $log_storage->delete_logs($filters, $batch_size);
            if ($deleted === false) {
                $this->send_wp_error(new WP_Error('delete_failed', __('Failed to delete logs.', 'gpt3-ai-content-generator')));
                return;
            }
            $total_deleted += (int) $deleted;
            $batch++;
        } while ($deleted === $batch_size && $batch < $max_batches);

        $message = sprintf(
            /* translators: %d: number of log entries deleted */
            _n('%d log deleted.', '%d logs deleted.', $total_deleted, 'gpt3-ai-content-generator'),
            number_format_i18n($total_deleted)
        );

        if ($batch >= $max_batches && $deleted === $batch_size) {
            $message = __('Log deletion reached the maximum batch limit. Please run again to continue.', 'gpt3-ai-content-generator');
        }

        wp_send_json_success([
            'message' => $message,
            'deleted' => $total_deleted,
        ]);
    }

    public function ajax_save_stats_settings()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $enable_pruning = isset($post_data['enable_pruning']) && $post_data['enable_pruning'] === '1';
        $retention_period = isset($post_data['retention_period_days']) ? floatval($post_data['retention_period_days']) : 90;

        if (!LogConfig::is_valid_period($retention_period)) {
            $retention_period = 90;
        }

        $is_pro = class_exists('\\WPAICG\\aipkit_dashboard') ? \WPAICG\aipkit_dashboard::is_pro_plan() : false;
        if ($enable_pruning && !$is_pro) {
            $this->send_wp_error(new WP_Error('pro_required', __('Auto-delete logs is a Pro feature.', 'gpt3-ai-content-generator')));
            return;
        }

        $settings = [
            'enable_pruning' => $enable_pruning && $is_pro,
            'retention_period_days' => $retention_period,
        ];
        update_option('aipkit_log_settings', $settings, 'no');

        if (class_exists(LogCronManager::class)) {
            if ($enable_pruning && $is_pro) {
                LogCronManager::schedule_event();
            } else {
                LogCronManager::unschedule_event();
            }
        }

        wp_send_json_success([
            'message' => __('Settings saved.', 'gpt3-ai-content-generator'),
        ]);
    }

    public function ajax_get_stats_log_cron_status()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        $log_settings = LogConfig::get_log_settings();
        $enable_pruning = (bool) ($log_settings['enable_pruning'] ?? false);

        $cron_hook = LogCronManager::HOOK_NAME;
        $next_scheduled = wp_next_scheduled($cron_hook);
        $is_cron_active = $next_scheduled !== false;

        $state = 'disabled';
        $status_text = __('Disabled', 'gpt3-ai-content-generator');
        if ($enable_pruning) {
            if ($is_cron_active) {
                $state = 'scheduled';
                $status_text = __('Scheduled', 'gpt3-ai-content-generator');
            } else {
                $state = 'not-scheduled';
                $status_text = __('Not Scheduled', 'gpt3-ai-content-generator');
            }
        }

        $last_run_option = get_option('aipkit_log_pruning_last_run', '');
        $last_run_label = $last_run_option
            ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_run_option))
            : __('Never', 'gpt3-ai-content-generator');

        wp_send_json_success([
            'state' => $state,
            'status_text' => $status_text,
            'last_run_label' => sprintf(
                /* translators: %s: last run time */
                __('Last run: %s', 'gpt3-ai-content-generator'),
                $last_run_label
            ),
        ]);
    }

    public function ajax_prune_stats_logs_now()
    {
        $permission_check = $this->check_module_access_permissions('stats', 'aipkit_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        if (!class_exists('\\WPAICG\\aipkit_dashboard') || !\WPAICG\aipkit_dashboard::is_pro_plan()) {
            $this->send_wp_error(new WP_Error('pro_required', __('Manual log pruning is a Pro feature. Please upgrade.', 'gpt3-ai-content-generator')));
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce checked above.
        $post_data = wp_unslash($_POST);
        $retention_period = isset($post_data['retention_period_days']) ? floatval($post_data['retention_period_days']) : 90;

        if (!LogConfig::is_valid_period($retention_period)) {
            $this->send_wp_error(new WP_Error('invalid_period', __('Invalid retention period provided.', 'gpt3-ai-content-generator')));
            return;
        }

        if (!class_exists(LogManager::class)) {
            $this->send_wp_error(new WP_Error('dependency_missing', __('Log manager component is unavailable.', 'gpt3-ai-content-generator')));
            return;
        }

        $log_manager = new LogManager();
        $deleted_rows = $log_manager->prune_logs($retention_period);

        if ($deleted_rows === false) {
            $this->send_wp_error(new WP_Error('pruning_failed', __('An error occurred while pruning the logs.', 'gpt3-ai-content-generator')));
            return;
        }

        update_option('aipkit_log_pruning_last_run', current_time('mysql', true), 'no');

        /* translators: %d: number of log entries deleted */
        $message = sprintf(_n('%d log entry pruned.', '%d log entries pruned.', $deleted_rows, 'gpt3-ai-content-generator'), number_format_i18n($deleted_rows));
        wp_send_json_success([
            'message' => $message,
            'deleted_count' => $deleted_rows,
        ]);
    }

    private function build_stats_log_filters(array $post_data): array
    {
        $days = $this->resolve_stats_days($post_data['days'] ?? 30);
        $range = $this->get_stats_time_range($days);

        $filters = [
            'start_ts' => $range['start_ts'],
            'end_ts' => $range['end_ts'],
        ];

        $bot_id_raw = isset($post_data['bot_id']) ? sanitize_text_field($post_data['bot_id']) : '';
        if ($bot_id_raw !== '') {
            $filters['bot_id'] = $bot_id_raw;
        }

        $module = isset($post_data['module']) ? sanitize_key($post_data['module']) : '';
        if ($module !== '') {
            $filters['module'] = $module;
        }

        $search = isset($post_data['search']) ? sanitize_text_field($post_data['search']) : '';
        if ($search !== '') {
            $filters['search'] = $search;
        }

        return $filters;
    }

    private function escape_csv_field(string|int|float|null $value): string
    {
        $string_value = (string) $value;
        $string_value = str_replace(["\r\n", "\n", "\r"], ' ', $string_value);
        $string_value = str_replace('"', '""', $string_value);
        return '"' . $string_value . '"';
    }

}
