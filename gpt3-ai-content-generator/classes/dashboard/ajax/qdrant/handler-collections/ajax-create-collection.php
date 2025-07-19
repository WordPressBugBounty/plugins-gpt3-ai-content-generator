<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/qdrant/handler-collections/ajax-create-collection.php
// Status: MODIFIED

namespace WPAICG\Dashboard\Ajax\Qdrant\HandlerCollections;

use WP_Error;
use WPAICG\Dashboard\Ajax\AIPKit_Vector_Store_Qdrant_Ajax_Handler;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for creating a Qdrant collection.
 * Called by AIPKit_Vector_Store_Qdrant_Ajax_Handler::ajax_create_collection_qdrant().
 *
 * @param AIPKit_Vector_Store_Qdrant_Ajax_Handler $handler_instance
 * @return void
 */
function _aipkit_qdrant_ajax_create_collection_logic(AIPKit_Vector_Store_Qdrant_Ajax_Handler $handler_instance): void {
    $vector_store_manager = $handler_instance->get_vector_store_manager();
    $vector_store_registry = $handler_instance->get_vector_store_registry();

    if (!$vector_store_manager || !$vector_store_registry) {
        $error_message = __('Vector Store components not available for Qdrant Create.', 'gpt3-ai-content-generator');
        // Ensure $handler_instance is valid before calling send_wp_error
        if ($handler_instance && method_exists($handler_instance, 'send_wp_error')) {
            $handler_instance->send_wp_error(new WP_Error('manager_not_ready_create_qdrant', $error_message, ['status' => 500]));
        } else {
            wp_send_json_error(['message' => $error_message], 500); // Fallback if handler is invalid
        }
        return;
    }

    $qdrant_config = $handler_instance->_get_qdrant_config();
    if (is_wp_error($qdrant_config)) {
        if ($handler_instance && method_exists($handler_instance, 'send_wp_error')) {
            $handler_instance->send_wp_error($qdrant_config);
        } else {
            wp_send_json_error(['message' => $qdrant_config->get_error_message()], 400); // Fallback
        }
        return;
    }

    $collection_name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $dimension = isset($_POST['dimension']) ? absint($_POST['dimension']) : 0;
    $metric = isset($_POST['metric']) ? sanitize_text_field($_POST['metric']) : 'Cosine'; // Default metric

    if (empty($collection_name)) {
        $error_message = __('Collection name is required.', 'gpt3-ai-content-generator');
        if ($handler_instance && method_exists($handler_instance, 'send_wp_error')) {
            $handler_instance->send_wp_error(new WP_Error('missing_name_qdrant_create', $error_message, ['status' => 400]));
        } else {
             wp_send_json_error(['message' => $error_message], 400);
        }
        return;
    }
    if ($dimension <= 0) {
        $error_message = __('Vector dimension must be a positive integer.', 'gpt3-ai-content-generator');
        if ($handler_instance && method_exists($handler_instance, 'send_wp_error')) {
            $handler_instance->send_wp_error(new WP_Error('invalid_dimension_qdrant_create', $error_message, ['status' => 400]));
        } else {
             wp_send_json_error(['message' => $error_message], 400);
        }
        return;
    }
    if (!in_array(ucfirst(strtolower($metric)), ['Cosine', 'Euclid', 'Dot'])) { // Validate metric
        $metric = 'Cosine';
    }

    $index_config = ['dimension' => $dimension, 'metric' => $metric];

    $create_result = $vector_store_manager->create_index_if_not_exists('Qdrant', $collection_name, $index_config, $qdrant_config);

    if (is_wp_error($create_result)) {
        $log_message = 'Collection creation failed: ' . $create_result->get_error_message();
        $handler_instance->_log_vector_data_source_entry([ // This should be safe if handler_instance is valid
            'vector_store_id' => $collection_name, 'vector_store_name' => $collection_name,
            'status' => 'failed', 'message' => $log_message,
            'source_type_for_log' => 'action_create_collection'
        ]);
        if ($handler_instance && method_exists($handler_instance, 'send_wp_error')) {
            $handler_instance->send_wp_error($create_result);
        } else {
            wp_send_json_error(['message' => $create_result->get_error_message()], 500);
        }
        return;
    }

    // Check for a successful Qdrant collection description structure
    if (is_array($create_result) && isset($create_result['status']) && in_array($create_result['status'], ['green', 'yellow', 'red']) && isset($create_result['config'])) {
        $collection_name_from_response = $collection_name; // Qdrant describe doesn't return 'name' in the main body, use the one we tried to create/describe
        $vector_store_registry->add_registered_store('Qdrant', ['id' => $collection_name_from_response, 'name' => $collection_name_from_response]);
        $log_message = __('Qdrant collection created/verified.', 'gpt3-ai-content-generator');
        $handler_instance->_log_vector_data_source_entry([
            'vector_store_id' => $collection_name_from_response, 'vector_store_name' => $collection_name_from_response,
            'status' => 'success', 'message' => $log_message,
            'source_type_for_log' => 'action_create_collection'
        ]);
        wp_send_json_success(['collection' => $create_result, 'message' => $log_message]);
    } else {
        $log_message = __('Qdrant collection creation response was malformed.', 'gpt3-ai-content-generator');
        $handler_instance->_log_vector_data_source_entry([
            'vector_store_id' => $collection_name, 'vector_store_name' => $collection_name,
            'status' => 'failed', 'message' => $log_message,
            'source_type_for_log' => 'action_create_collection'
        ]);
        if ($handler_instance && method_exists($handler_instance, 'send_wp_error')) {
            $handler_instance->send_wp_error(new WP_Error('qdrant_create_malformed_response', $log_message));
        } else {
            wp_send_json_error(['message' => $log_message], 500);
        }
    }
}