<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/openai/handler-stores/ajax-delete-vector-store-openai.php
// Status: MODIFIED (Logic moved here)

namespace WPAICG\Dashboard\Ajax\OpenAI\HandlerStores;

use WPAICG\Dashboard\Ajax\AIPKit_OpenAI_Vector_Stores_Ajax_Handler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for deleting an OpenAI Vector Store.
 * Called by AIPKit_OpenAI_Vector_Stores_Ajax_Handler::ajax_delete_vector_store_openai().
 *
 * @param AIPKit_OpenAI_Vector_Stores_Ajax_Handler $handler_instance
 * @return void
 */
function do_ajax_delete_vector_store_openai_logic(AIPKit_OpenAI_Vector_Stores_Ajax_Handler $handler_instance): void {
    // Permission check already done by the handler calling this

    $vector_store_manager = $handler_instance->get_vector_store_manager();
    $vector_store_registry = $handler_instance->get_vector_store_registry();
    $wpdb = $handler_instance->get_wpdb();
    $data_source_table_name = $handler_instance->get_data_source_table_name();


    if (!$vector_store_manager || !$vector_store_registry) {
        $handler_instance->send_wp_error(new WP_Error('manager_not_ready', __('Vector Store Manager or Registry not available.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }

    $openai_config = $handler_instance->_get_openai_config();
    if (is_wp_error($openai_config)) {
        $handler_instance->send_wp_error($openai_config);
        return;
    }

    // Logic from old _aipkit_openai_vs_ajax_delete_vector_store_logic
    $store_id = isset($_POST['store_id']) ? sanitize_text_field($_POST['store_id']) : '';
    if (empty($store_id)) {
        $handler_instance->send_wp_error(new WP_Error('missing_store_id', __('Vector Store ID is required.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }

    $delete_result = $vector_store_manager->delete_index('OpenAI', $store_id, $openai_config);
    if (is_wp_error($delete_result)) {
        \WPAICG\Dashboard\Ajax\OpenAI\_aipkit_openai_vs_stores_log_vector_store_event_logic($wpdb, $data_source_table_name, [
            'vector_store_id' => $store_id,
            'status' => 'failed',
            'message' => 'Store deletion failed: ' . $delete_result->get_error_message(),
            'source_type_for_log' => 'action_delete_store'
        ]);
        $handler_instance->send_wp_error($delete_result);
        return;
    }

    $vector_store_registry->remove_registered_store('OpenAI', $store_id);
    \WPAICG\Dashboard\Ajax\OpenAI\_aipkit_openai_vs_stores_log_vector_store_event_logic($wpdb, $data_source_table_name, [
        'vector_store_id' => $store_id,
        'status' => 'success',
        'message' => 'Vector store deleted.',
        'source_type_for_log' => 'action_delete_store'
    ]);

    $wpdb->delete($data_source_table_name, ['provider' => 'OpenAI', 'vector_store_id' => $store_id], ['%s', '%s']);
    if ($wpdb->last_error) {
        error_log("AIPKit OpenAI VS Delete Logic: Failed to delete data source entries for store {$store_id}. Error: " . $wpdb->last_error);
    }

    wp_send_json_success(['message' => __('Vector Store deleted successfully.', 'gpt3-ai-content-generator')]);
}