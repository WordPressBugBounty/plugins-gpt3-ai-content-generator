<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/class-aipkit-content-writer-generate-images-action.php
// Status: MODIFIED

namespace WPAICG\ContentWriter\Ajax\Actions;

use WPAICG\ContentWriter\Ajax\AIPKit_Content_Writer_Base_Ajax_Action;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Image_Handler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the dedicated AJAX action for generating AI images within the Content Writer.
 * UPDATED: Strips large base64 data from the response to prevent "Request Entity Too Large" errors.
 */
class AIPKit_Content_Writer_Generate_Images_Action extends AIPKit_Content_Writer_Base_Ajax_Action
{
    public function handle()
    {
        $permission_check = $this->check_module_access_permissions('content-writer', 'aipkit_content_writer_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $settings = isset($_POST) ? wp_unslash($_POST) : [];
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $final_title = isset($settings['final_title']) ? sanitize_text_field($settings['final_title']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $final_keywords = isset($settings['keywords']) ? sanitize_text_field($settings['keywords']) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Reason: Nonce is checked in check_module_access_permissions.
        $original_topic = isset($settings['original_topic']) ? sanitize_text_field($settings['original_topic']) : $final_title;

        if (!class_exists(AIPKit_Content_Writer_Image_Handler::class)) {
            $this->send_wp_error(new WP_Error('missing_image_handler', 'Image generation component is missing.', ['status' => 500]));
            return;
        }

        $image_handler = new AIPKit_Content_Writer_Image_Handler();
        $image_result = $image_handler->generate_and_prepare_images($settings, $final_title, $final_keywords, $original_topic);

        if (is_wp_error($image_result)) {
            $this->send_wp_error($image_result);
            return;
        }

        // --- START FIX: Strip b64_json from response to prevent 413 "Request Entity Too Large" on subsequent saves ---
        if (isset($image_result['in_content_images']) && is_array($image_result['in_content_images'])) {
            foreach ($image_result['in_content_images'] as &$image_item) {
                unset($image_item['b64_json']);
            }
            unset($image_item); // Unset the reference
        }
        // --- END FIX ---

        wp_send_json_success(['image_data' => $image_result]);
    }
}
