<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse Claude non-stream response.
 */
function parse_chat_response_logic(
    ClaudeProviderStrategy $strategyInstance,
    array $decoded_response,
    array $request_data
): array|WP_Error {
    if (isset($decoded_response['error'])) {
        return new WP_Error(
            'claude_api_error',
            $strategyInstance->parse_error_response($decoded_response, 500)
        );
    }

    $content_parts = [];
    if (!empty($decoded_response['content']) && is_array($decoded_response['content'])) {
        foreach ($decoded_response['content'] as $block) {
            if (!is_array($block)) {
                continue;
            }
            if (($block['type'] ?? '') === 'text' && isset($block['text'])) {
                $content_parts[] = (string) $block['text'];
            }
        }
    }

    $content = trim(implode('', $content_parts));
    if ($content === '') {
        return new WP_Error(
            'invalid_response_structure_claude',
            __('Unexpected response structure from Claude API.', 'gpt3-ai-content-generator')
        );
    }

    $usage = null;
    if (!empty($decoded_response['usage']) && is_array($decoded_response['usage'])) {
        $input_tokens = (int) ($decoded_response['usage']['input_tokens'] ?? 0);
        $output_tokens = (int) ($decoded_response['usage']['output_tokens'] ?? 0);
        $usage = [
            'input_tokens' => $input_tokens,
            'output_tokens' => $output_tokens,
            'total_tokens' => $input_tokens + $output_tokens,
            'provider_raw' => $decoded_response['usage'],
        ];
    }

    return [
        'content' => $content,
        'usage' => $usage,
    ];
}
