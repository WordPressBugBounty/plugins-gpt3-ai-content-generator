<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/ajax/pinecone/handler-indexes/ajax-list-indexes.php
// Status: NEW FILE

namespace WPAICG\Dashboard\Ajax\Pinecone\HandlerIndexes;

use WP_Error;
use WPAICG\Dashboard\Ajax\AIPKit_Vector_Store_Pinecone_Ajax_Handler;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the logic for listing Pinecone indexes.
 * Called by AIPKit_Vector_Store_Pinecone_Ajax_Handler::ajax_list_indexes_pinecone().
 *
 * @param AIPKit_Vector_Store_Pinecone_Ajax_Handler $handler_instance
 * @return void
 */
function do_ajax_list_indexes_logic(AIPKit_Vector_Store_Pinecone_Ajax_Handler $handler_instance): void {
    $vector_store_manager = $handler_instance->get_vector_store_manager();
    $vector_store_registry = $handler_instance->get_vector_store_registry();

    if (!$vector_store_manager || !$vector_store_registry) {
        $handler_instance->send_wp_error(new WP_Error('manager_not_ready_list_pinecone', __('Vector Store components not available for Pinecone.', 'gpt3-ai-content-generator'), ['status' => 500]));
        return;
    }

    $pinecone_config = $handler_instance->_get_pinecone_config();
    if (is_wp_error($pinecone_config)) {
        $handler_instance->send_wp_error($pinecone_config);
        return;
    }

    $response = $vector_store_manager->list_all_indexes('Pinecone', $pinecone_config);
    if (is_wp_error($response)) {
        $handler_instance->send_wp_error($response);
        return;
    }

    if (is_array($response)) {
        wp_cache_delete('aipkit_pinecone_index_list', 'options');
        update_option('aipkit_pinecone_index_list', $response, 'no');
        $vector_store_registry->update_registered_stores_for_provider('Pinecone', $response);
    }
    wp_send_json_success(['indexes' => $response, 'message' => __('Pinecone indexes synced successfully.', 'gpt3-ai-content-generator')]);
}