<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/qdrant/handler-collections/ajax-get-vector-data-source-logs.php
// Status: MODIFIED

namespace WPAICG\Dashboard\Ajax\Qdrant\HandlerCollections;

use WP_Error;
use WPAICG\Dashboard\Ajax\AIPKit_Vector_Store_Qdrant_Ajax_Handler;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for ajax_get_vector_data_source_logs_for_store.
 * Called by AIPKit_Vector_Store_Qdrant_Ajax_Handler::ajax_get_vector_data_source_logs_for_store().
 *
 * @param AIPKit_Vector_Store_Qdrant_Ajax_Handler $handler_instance
 * @return void
 */
function _aipkit_qdrant_ajax_get_vector_data_source_logs_logic(AIPKit_Vector_Store_Qdrant_Ajax_Handler $handler_instance): void
{
    $wpdb = $handler_instance->get_wpdb();
    $data_source_table_name = $handler_instance->get_data_source_table_name();

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in the calling handler method.
    $post_data = wp_unslash($_POST);
    $provider = isset($post_data['provider']) ? sanitize_key($post_data['provider']) : '';
    $store_id = isset($post_data['store_id']) ? sanitize_text_field($post_data['store_id']) : '';

    if (empty($provider) || empty($store_id)) {
        $handler_instance->send_wp_error(new WP_Error('missing_params_logs_qdrant', __('Provider and Store/Index/Collection ID are required to fetch logs.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }

    $cache_key = strtolower($provider) . '_logs_' . sanitize_key($store_id);
    $cache_group = 'aipkit_vector_logs';
    $logs = wp_cache_get($cache_key, $cache_group);

    if (false === $logs) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $data_source_table_name is safe.
        $logs = $wpdb->get_results($wpdb->prepare("SELECT id, timestamp, status, message, indexed_content, post_id, embedding_provider, embedding_model, file_id FROM {$data_source_table_name} WHERE provider = %s AND vector_store_id = %s ORDER BY timestamp DESC LIMIT 20", $provider, $store_id), ARRAY_A);
        wp_cache_set($cache_key, $logs, $cache_group, MINUTE_IN_SECONDS * 5); // Cache for 5 minutes
    }


    if ($wpdb->last_error) {
        $handler_instance->send_wp_error(new WP_Error('db_query_error_qdrant_logs', __('Failed to fetch indexing logs for Qdrant.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }
    wp_send_json_success(['logs' => $logs ?: []]);
}

/**
 * Logs an entry for vector store related events to the `wp_aipkit_vector_data_source` table.
 * This helper is specific to Qdrant for this file.
 *
 * @param \wpdb $wpdb WordPress database object.
 * @param string $data_source_table_name The name of the data source log table.
 * @param array $log_data Data for the log entry.
 */
function _aipkit_qdrant_log_vector_data_source_entry_logic(\wpdb $wpdb, string $data_source_table_name, array $log_data): void
{
    $defaults = [
        'user_id' => get_current_user_id(),
        'timestamp' => current_time('mysql', 1),
        'provider' => 'Qdrant',
        'vector_store_id' => 'unknown',
        'vector_store_name' => null,
        'post_id' => null,
        'post_title' => null,
        'status' => 'info',
        'message' => '',
        'indexed_content' => null,
        'file_id' => null,
        'batch_id' => null,
        'embedding_provider' => null,
        'embedding_model' => null,
        'source_type_for_log' => null,
    ];
    $data_to_insert = wp_parse_args($log_data, $defaults);

    $source_type = $data_to_insert['source_type_for_log'] ?? ($data_to_insert['post_id'] ? 'wordpress_post' : 'unknown');
    $should_truncate = true;
    if (in_array($source_type, ['text_entry_global_form', 'file_upload_global_form', 'text_entry_qdrant_direct', 'file_upload_qdrant_direct'])) {
        $should_truncate = false;
    }

    if ($should_truncate && is_string($data_to_insert['indexed_content']) && mb_strlen($data_to_insert['indexed_content']) > 1000) {
        $data_to_insert['indexed_content'] = mb_substr($data_to_insert['indexed_content'], 0, 997) . '...';
    }
    unset($data_to_insert['source_type_for_log']);

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
    $result = $wpdb->insert($data_source_table_name, $data_to_insert);

    // Invalidate cache after successful insert
    if ($result) {
        $provider = $log_data['provider'] ?? 'Qdrant';
        $store_id = $log_data['vector_store_id'] ?? null;
        if ($store_id) {
            $cache_key = strtolower($provider) . '_logs_' . sanitize_key($store_id);
            $cache_group = 'aipkit_vector_logs';
            wp_cache_delete($cache_key, $cache_group);
        }
    }
}