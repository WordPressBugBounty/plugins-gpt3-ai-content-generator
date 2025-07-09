<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/generate-title/handle-title-response.php
// Status: NEW FILE

namespace WPAICG\ContentWriter\Ajax\Actions\GenerateTitle;

use WPAICG\ContentWriter\Ajax\Actions\AIPKit_Content_Writer_Generate_Title_Action;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the response from the AI call, cleaning it and sending a JSON response.
 *
 * @param AIPKit_Content_Writer_Generate_Title_Action $handler The handler instance.
 * @param array|WP_Error $result The result from the AI Caller.
 * @return void
 */
function handle_title_response_logic(AIPKit_Content_Writer_Generate_Title_Action $handler, array|WP_Error $result): void
{
    if (is_wp_error($result)) {
        $handler->send_wp_error($result);
        return;
    }

    $generated_title = trim($result['content'] ?? '');

    // Clean up potential extra formatting from the AI
    if (preg_match('/^"(.*)"$/', $generated_title, $matches)) {
        $generated_title = $matches[1];
    }
    $generated_title = trim(str_replace(["\n", "\r"], ' ', $generated_title));
    $generated_title = preg_replace('/\s+/', ' ', $generated_title);

    if (empty($generated_title)) {
        $handler->send_wp_error(new WP_Error('title_gen_empty', __('AI did not return a valid title.', 'gpt3-ai-content-generator')), 500);
        return;
    }

    wp_send_json_success([
        'new_title' => $generated_title,
        'usage' => $result['usage'] ?? null
    ]);
}
