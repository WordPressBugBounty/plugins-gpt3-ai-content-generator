<?php

namespace WPAICG\ContentWriter\Ajax\Template;

use WPAICG\ContentWriter\Ajax\AIPKit_Content_Writer_Template_Ajax_Handler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renames an owned custom template without replacing its saved configuration.
 */
function ajax_rename_template_logic(AIPKit_Content_Writer_Template_Ajax_Handler $handler): void
{
    $template_manager = $handler->get_template_manager();
    if (!$template_manager) {
        $handler->send_wp_error(new WP_Error('manager_missing', __('Template manager unavailable.', 'gpt3-ai-content-generator')), 500);
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- The calling handler verifies the nonce.
    $template_id = isset($_POST['template_id']) ? absint($_POST['template_id']) : 0;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- The calling handler verifies the nonce.
    $template_name = isset($_POST['template_name']) ? sanitize_text_field(wp_unslash($_POST['template_name'])) : '';

    if ($template_id < 1 || $template_name === '') {
        $handler->send_wp_error(new WP_Error('invalid_template_rename', __('Choose a template and enter a name.', 'gpt3-ai-content-generator')), 400);
        return;
    }

    $result = $template_manager->rename_template($template_id, $template_name);
    if (is_wp_error($result)) {
        $handler->send_wp_error($result);
        return;
    }

    wp_send_json_success([
        'template_id' => $template_id,
        'template_name' => $template_name,
    ]);
}
