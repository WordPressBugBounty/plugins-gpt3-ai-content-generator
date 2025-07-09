<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/openai/handler-files/ajax-delete-file-from-vector-store-openai.php
// Status: MODIFIED (Logic moved here)

namespace WPAICG\Dashboard\Ajax\OpenAI\HandlerFiles;

use WPAICG\Dashboard\Ajax\AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for deleting a file from an OpenAI Vector Store.
 * Called by AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler::ajax_delete_file_from_vector_store_openai().
 *
 * @param AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler $handler_instance
 * @return void
 */
function do_ajax_delete_file_from_vector_store_openai_logic(AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler $handler_instance): void {
    // Permission check already done by the handler calling this

    $vector_store_manager = $handler_instance->get_vector_store_manager();
    $vector_store_registry = $handler_instance->get_vector_store_registry();
    $wpdb = $handler_instance->get_wpdb();
    $data_source_table_name = $handler_instance->get_data_source_table_name();

    if (!$vector_store_manager || !$vector_store_registry) {
        $handler_instance->send_wp_error(new WP_Error('manager_not_ready', __('Vector Store components not available.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }

    $openai_config = $handler_instance->_get_openai_config();
    if (is_wp_error($openai_config)) {
        $handler_instance->send_wp_error($openai_config);
        return;
    }

    // Logic from old _aipkit_openai_vs_files_ajax_delete_file_from_vector_store_openai_logic
    $store_id = isset($_POST['store_id']) ? sanitize_text_field($_POST['store_id']) : '';
    $file_id = isset($_POST['file_id']) ? sanitize_text_field($_POST['file_id']) : '';
    if (empty($store_id) || empty($file_id)) {
        $handler_instance->send_wp_error(new WP_Error('missing_ids', __('Vector Store ID and File ID are required.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }

    $result = $vector_store_manager->delete_vectors('OpenAI', $store_id, [$file_id], $openai_config);
    if (is_wp_error($result)) {
        $handler_instance->send_wp_error($result);
        return;
    }

    $updated_store_data = $vector_store_manager->describe_single_index('OpenAI', $store_id, $openai_config);
    if (!is_wp_error($updated_store_data) && is_array($updated_store_data) && isset($updated_store_data['id'])) {
        $vector_store_registry->add_registered_store('OpenAI', $updated_store_data);
    }

    \WPAICG\Dashboard\Ajax\OpenAI\_aipkit_openai_vs_files_log_vector_data_source_entry($wpdb, $data_source_table_name, [
        'vector_store_id' => $store_id,
        'status' => 'file_deleted',
        'message' => 'File deleted from store.',
        'file_id' => $file_id,
        'source_type_for_log' => 'action_delete_file'
    ]);

    wp_send_json_success(['message' => __('File deleted from vector store successfully.', 'gpt3-ai-content-generator')]);
}