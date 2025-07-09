<?php

namespace WPAICG\Admin\Ajax\AIForms;

use WP_Error;
use WPAICG\AIForms\Admin\AIPKit_AI_Form_Ajax_Handler;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the logic for listing all AI forms with dynamic querying.
 * Called by AIPKit_AI_Form_Ajax_Handler::ajax_list_ai_forms().
 * UPDATED: Handles pagination, search, and sorting parameters.
 *
 * @param AIPKit_AI_Form_Ajax_Handler $handler_instance
 * @return void
 */
function do_ajax_list_forms_logic(AIPKit_AI_Form_Ajax_Handler $handler_instance): void
{
    $form_storage = $handler_instance->get_form_storage();
    if (!$form_storage) {
        $handler_instance->send_wp_error(new WP_Error('storage_missing', __('Form storage component is not available.', 'gpt3-ai-content-generator')), 500);
        return;
    }

    // --- NEW: Read and sanitize query parameters from POST ---
    $paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
    $sort_by = isset($_POST['sort_by']) ? sanitize_key($_POST['sort_by']) : 'title';
    $sort_order = isset($_POST['sort_order']) && in_array(strtoupper($_POST['sort_order']), ['ASC', 'DESC']) ? strtoupper($_POST['sort_order']) : 'ASC';
    $provider_filter = isset($_POST['filter_provider']) ? sanitize_text_field(wp_unslash($_POST['filter_provider'])) : 'all';

    // Whitelist sortable columns
    $allowed_sort_keys = ['id', 'title', 'provider', 'model', 'date'];
    if (!in_array($sort_by, $allowed_sort_keys)) {
        $sort_by = 'title';
    }
    // WP_Query uses 'ID' instead of 'id'
    if ($sort_by === 'id') {
        $sort_by = 'ID';
    }


    $args = [
        'paged'          => $paged,
        'search'         => $search,
        'orderby'        => $sort_by,
        'order'          => $sort_order,
        'filter_provider' => $provider_filter,
    ];

    $result = $form_storage->get_forms_list($args);

    // The result from get_forms_list_logic is now an array with 'forms' and 'pagination' keys
    wp_send_json_success($result);
}
