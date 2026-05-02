<?php
// File: classes/core/providers/xai/parse-chat-response.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param XAIProviderStrategy $strategyInstance
 * @param array<string, mixed> $decoded_response
 * @param array<string, mixed> $request_data
 * @return array<string, mixed>|WP_Error
 */
function parse_chat_response_logic(
    XAIProviderStrategy $strategyInstance,
    array $decoded_response,
    array $request_data
): array|WP_Error {
    if (($decoded_response['status'] ?? '') === 'failed') {
        $message = $decoded_response['error']['message'] ?? __('xAI response failed.', 'gpt3-ai-content-generator');
        $code = $decoded_response['error']['code'] ?? 'xai_failed_response';
        return new WP_Error((string) $code, (string) $message);
    }

    $content = xai_extract_response_text($decoded_response);
    $is_incomplete = ($decoded_response['status'] ?? '') === 'incomplete';
    if ($content === '' && !$is_incomplete) {
        return new WP_Error(
            'invalid_response_structure_xai',
            __('Unexpected response structure from xAI Responses API.', 'gpt3-ai-content-generator')
        );
    }

    if ($is_incomplete) {
        $reason = $decoded_response['incomplete_details']['reason'] ?? 'unknown';
        if ($content !== '') {
            $content .= sprintf(' (%s: %s)', __('Incomplete', 'gpt3-ai-content-generator'), $reason);
        } else {
            return new WP_Error(
                'xai_incomplete_response',
                sprintf(
                    /* translators: %s: The reason why the response was incomplete. */
                    __('Response incomplete due to: %s', 'gpt3-ai-content-generator'),
                    (string) $reason
                )
            );
        }
    }

    $usage = null;
    $has_tool_usage = !empty(xai_extract_server_side_tool_usage($decoded_response));
    if (isset($decoded_response['usage']) && is_array($decoded_response['usage'])) {
        $usage = xai_normalize_usage($decoded_response['usage'], $decoded_response);
    } elseif ($has_tool_usage) {
        $usage = xai_normalize_usage([], $decoded_response);
    }

    $return_data = [
        'content' => $content,
        'usage' => $usage,
    ];

    if (!empty($decoded_response['id']) && is_string($decoded_response['id'])) {
        $return_data['xai_response_id'] = $decoded_response['id'];
    }

    $citations = xai_extract_citations($decoded_response);
    if (!empty($citations)) {
        $return_data['citations'] = $citations;
    }

    return $return_data;
}
