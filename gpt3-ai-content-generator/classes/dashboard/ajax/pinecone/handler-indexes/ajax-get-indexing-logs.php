<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/pinecone/handler-indexes/ajax-get-indexing-logs.php
// Status: NEW FILE

namespace WPAICG\Dashboard\Ajax\Pinecone\HandlerIndexes;

use WP_Error;
use WPAICG\Dashboard\Ajax\AIPKit_Vector_Store_Pinecone_Ajax_Handler;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for fetching Pinecone indexing logs.
 * Called by AIPKit_Vector_Store_Pinecone_Ajax_Handler::ajax_get_pinecone_indexing_logs().
 *
 * @param AIPKit_Vector_Store_Pinecone_Ajax_Handler $handler_instance
 * @return void
 */
function do_ajax_get_indexing_logs_logic(AIPKit_Vector_Store_Pinecone_Ajax_Handler $handler_instance): void {
    $wpdb = $handler_instance->get_wpdb();
    $data_source_table_name = $handler_instance->get_data_source_table_name();

    $index_name = isset($_POST['index_name']) ? sanitize_text_field($_POST['index_name']) : '';
    if (empty($index_name)) {
        $handler_instance->send_wp_error(new WP_Error('missing_index_name_logs', __('Pinecone index name is required to fetch logs.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }

    $logs = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT timestamp, status, message, indexed_content, post_id, embedding_provider, embedding_model, file_id
             FROM {$data_source_table_name}
             WHERE provider = 'Pinecone' AND vector_store_id = %s
             ORDER BY timestamp DESC LIMIT 20",
            $index_name
        ),
        ARRAY_A
    );

    if ($wpdb->last_error) {
        error_log("AIPKit Pinecone AJAX (Get Logs Logic): DB Error fetching indexing logs. Query: " . $wpdb->last_query . " Error: " . $wpdb->last_error);
        $handler_instance->send_wp_error(new WP_Error('db_query_error_pinecone_logs', __('Failed to fetch Pinecone indexing logs.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }
    wp_send_json_success(['logs' => $logs ?: []]);
}