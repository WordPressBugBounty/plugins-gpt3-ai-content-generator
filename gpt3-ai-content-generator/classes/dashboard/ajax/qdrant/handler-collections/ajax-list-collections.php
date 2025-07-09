<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/qdrant/handler-collections/ajax-list-collections.php
// Status: NEW

namespace WPAICG\Dashboard\Ajax\Qdrant\HandlerCollections;

use WP_Error;
use WPAICG\Dashboard\Ajax\AIPKit_Vector_Store_Qdrant_Ajax_Handler;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for listing Qdrant collections.
 * Called by AIPKit_Vector_Store_Qdrant_Ajax_Handler::ajax_list_collections_qdrant().
 *
 * @param AIPKit_Vector_Store_Qdrant_Ajax_Handler $handler_instance
 * @return void
 */
function _aipkit_qdrant_ajax_list_collections_logic(AIPKit_Vector_Store_Qdrant_Ajax_Handler $handler_instance): void {
    $vector_store_manager = $handler_instance->get_vector_store_manager();
    $vector_store_registry = $handler_instance->get_vector_store_registry();

    if (!$vector_store_manager || !$vector_store_registry) {
        $handler_instance->send_wp_error(new WP_Error('manager_not_ready_list_qdrant', __('Vector Store components not available for Qdrant.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }

    $qdrant_config = $handler_instance->_get_qdrant_config();
    if (is_wp_error($qdrant_config)) {
        $handler_instance->send_wp_error($qdrant_config);
        return;
    }

    $response = $vector_store_manager->list_all_indexes('Qdrant', $qdrant_config);
    if (is_wp_error($response)) {
        $handler_instance->send_wp_error($response);
        return;
    }

    if (is_array($response)) {
        // Qdrant list_all_indexes now returns array of objects [{name, id}, ...]
        // No further processing of $response['collections'] needed, $response itself is the array
        wp_cache_delete('aipkit_qdrant_collection_list', 'options');
        update_option('aipkit_qdrant_collection_list', $response, 'no');
        $vector_store_registry->update_registered_stores_for_provider('Qdrant', $response);
    }
    wp_send_json_success(['collections' => $response, 'message' => __('Qdrant collections synced successfully.', 'gpt3-ai-content-generator')]);
}