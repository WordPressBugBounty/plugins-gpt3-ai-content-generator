<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/openai/handler-files/ajax-list-files-in-vector-store-openai.php
// Status: MODIFIED (Logic moved here)

namespace WPAICG\Dashboard\Ajax\OpenAI\HandlerFiles;

use WPAICG\Dashboard\Ajax\AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for listing files in an OpenAI Vector Store.
 * Called by AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler::ajax_list_files_in_vector_store_openai().
 *
 * @param AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler $handler_instance
 * @return void
 */
function do_ajax_list_files_in_vector_store_openai_logic(AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler $handler_instance): void {
    // Permission check already done by the handler calling this

    $vector_store_manager = $handler_instance->get_vector_store_manager();
    $wpdb = $handler_instance->get_wpdb();
    $data_source_table_name = $handler_instance->get_data_source_table_name();

    if (!$vector_store_manager) {
        $handler_instance->send_wp_error(new WP_Error('manager_not_ready', __('Vector Store Manager not available.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }

    $openai_config = $handler_instance->_get_openai_config();
    if (is_wp_error($openai_config)) {
        $handler_instance->send_wp_error($openai_config);
        return;
    }

    // Logic from old _aipkit_openai_vs_files_ajax_list_files_in_vector_store_openai_logic
    $store_id = isset($_POST['store_id']) ? sanitize_text_field($_POST['store_id']) : '';
    if (empty($store_id)) {
        $handler_instance->send_wp_error(new WP_Error('missing_store_id', __('Vector Store ID is required.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }

    $query_params = [];
    if (isset($_POST['limit']) && is_numeric($_POST['limit'])) $query_params['limit'] = absint($_POST['limit']);
    if (isset($_POST['order']) && in_array($_POST['order'], ['asc', 'desc'])) $query_params['order'] = sanitize_key($_POST['order']);
    if (isset($_POST['after']) && !empty($_POST['after'])) $query_params['after'] = sanitize_text_field($_POST['after']);
    if (isset($_POST['before']) && !empty($_POST['before'])) $query_params['before'] = sanitize_text_field($_POST['before']);
    if (isset($_POST['filter']) && in_array($_POST['filter'], ['in_progress', 'completed', 'failed', 'cancelled'])) $query_params['filter'] = sanitize_key($_POST['filter']);

    $files_response = $vector_store_manager->list_files_in_store('OpenAI', $store_id, $openai_config, $query_params);

    if (is_wp_error($files_response)) {
        $handler_instance->send_wp_error($files_response);
        return;
    }

    $enriched_files = [];
    if (!empty($files_response) && is_array($files_response)) {
        foreach ($files_response as $file) {
            if (isset($file['id'])) {
                $log_entry = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT user_id, post_id, post_title, indexed_content FROM {$data_source_table_name} WHERE file_id = %s ORDER BY timestamp DESC LIMIT 1",
                        $file['id']
                    ),
                    ARRAY_A
                );
                if ($log_entry) {
                    $file['user_display_name'] = __('N/A', 'gpt3-ai-content-generator');
                    if (!empty($log_entry['user_id'])) {
                        $user_data = get_userdata($log_entry['user_id']);
                        $file['user_display_name'] = $user_data ? $user_data->display_name : __('Deleted User', 'gpt3-ai-content-generator');
                    }
                    $file['post_title'] = $log_entry['post_title'] ?: ($log_entry['post_id'] ? get_the_title($log_entry['post_id']) : __('N/A', 'gpt3-ai-content-generator'));
                    $file['post_id'] = $log_entry['post_id'] ?? null;
                    $file['indexed_content_snippet'] = $log_entry['indexed_content'] ?? null;
                } else {
                    $file['user_display_name'] = __('N/A', 'gpt3-ai-content-generator');
                    $file['post_title'] = __('N/A', 'gpt3-ai-content-generator');
                    $file['post_id'] = null;
                    $file['indexed_content_snippet'] = null;
                }
            }
            $enriched_files[] = $file;
        }
    }
    wp_send_json_success(['files' => $enriched_files]);
}