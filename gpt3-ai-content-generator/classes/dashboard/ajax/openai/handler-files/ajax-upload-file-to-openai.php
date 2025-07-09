<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/openai/handler-files/ajax-upload-file-to-openai.php
// Status: MODIFIED (Logic moved here)

namespace WPAICG\Dashboard\Ajax\OpenAI\HandlerFiles;

use WPAICG\Dashboard\Ajax\AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler;
use WPAICG\aipkit_dashboard;
use WPAICG\Vector\AIPKit_Vector_Provider_Strategy_Factory;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for uploading a file to OpenAI.
 * Called by AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler::ajax_upload_file_to_openai().
 *
 * @param AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler $handler_instance
 * @return void
 */
function do_ajax_upload_file_to_openai_logic(AIPKit_OpenAI_Vector_Store_Files_Ajax_Handler $handler_instance): void {
    // Permission check already done by the handler calling this

    // --- Pro Check ---
    if (!aipkit_dashboard::is_pro_plan()) {
        $handler_instance->send_wp_error(new WP_Error('pro_feature_openai_upload', __('File upload to OpenAI is a Pro feature. Please upgrade.', 'gpt3-ai-content-generator'), ['status' => 403]));
        return;
    }
    // --- End Pro Check ---

    // Logic from /lib/vector-stores/file-upload/openai/fn-upload-file.php is now inlined here,
    // as per the "one-function-per-file" rule for the core AJAX action.
    // The Pro check above Gatekeeps this function.

    if (!isset($_FILES['aipkit_file_to_upload'])) {
        $handler_instance->send_wp_error(new WP_Error('no_file', __('No file provided.', 'gpt3-ai-content-generator'), ['status' => 400]));
        return;
    }
    $file = $_FILES['aipkit_file_to_upload'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $handler_instance->send_wp_error(new WP_Error('upload_error', __('Error during file upload: Code ', 'gpt3-ai-content-generator') . $file['error'], ['status' => 400]));
        return;
    }

    $openai_config = $handler_instance->_get_openai_config();
    if (is_wp_error($openai_config)) {
        $handler_instance->send_wp_error($openai_config);
        return;
    }

    $strategy = AIPKit_Vector_Provider_Strategy_Factory::get_strategy('OpenAI');
    if (is_wp_error($strategy) || !method_exists($strategy, 'upload_file_for_vector_store')) {
        $handler_instance->send_wp_error(new WP_Error('strategy_error_upload', __('File upload component not available for OpenAI.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }
    $strategy->connect($openai_config);
    $purpose = isset($_POST['purpose']) ? sanitize_text_field($_POST['purpose']) : 'assistants_file';
    $upload_result = $strategy->upload_file_for_vector_store($file['tmp_name'], $file['name'], $purpose);

    if (is_wp_error($upload_result)) {
        $handler_instance->send_wp_error($upload_result);
    } else {
        wp_send_json_success(['message' => __('File uploaded successfully.', 'gpt3-ai-content-generator'), 'file_id' => $upload_result['id'] ?? null, 'file_data' => $upload_result]);
    }
}