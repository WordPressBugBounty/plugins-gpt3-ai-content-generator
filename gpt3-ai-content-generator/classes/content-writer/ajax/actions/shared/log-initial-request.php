<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/shared/log-initial-request.php
// Status: NEW FILE

namespace WPAICG\ContentWriter\Ajax\Actions\Shared;

use WPAICG\ContentWriter\Ajax\AIPKit_Content_Writer_Base_Ajax_Action;
use WPAICG\AIPKit\Addons\AIPKit_IP_Anonymization;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logs the initial user request for content generation (both standard and stream).
 *
 * @param AIPKit_Content_Writer_Base_Ajax_Action $handler The handler instance.
 * @param array $request_data The validated and normalized request parameters.
 * @param string $request_type A string indicating the request type (e.g., 'Stream Init', 'AJAX').
 * @return void
 */
function log_initial_request_logic(AIPKit_Content_Writer_Base_Ajax_Action $handler, array $request_data, string $request_type): void
{
    if (!$handler->log_storage) {
        return;
    }

    $initial_request_details_for_log = [
        'title'              => $request_data['content_title'] ?? '',
        'keywords'           => $request_data['content_keywords'] ?? null,
        'content_max_tokens' => $request_data['content_max_tokens'] ?? null,
    ];

    $handler->log_storage->log_message([
        'bot_id'            => null,
        'user_id'           => get_current_user_id(),
        'session_id'        => null,
        'conversation_uuid' => wp_generate_uuid4(), // Generate a unique ID for this one-off interaction
        'module'            => 'content_writer',
        'is_guest'          => 0,
        'role'              => implode(', ', wp_get_current_user()->roles),
        'ip_address'        => AIPKit_IP_Anonymization::maybe_anonymize($_SERVER['REMOTE_ADDR'] ?? null),
        'message_role'      => 'user',
        'message_content'   => "Content Writer Request ({$request_type}): " . esc_html($request_data['content_title']),
        'timestamp'         => time(),
        'ai_provider'       => $request_data['provider'],
        'ai_model'          => $request_data['model'],
        'request_payload'   => $initial_request_details_for_log
    ]);
}
