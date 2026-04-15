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
    $citations = [];
    if (!empty($decoded_response['content']) && is_array($decoded_response['content'])) {
        foreach ($decoded_response['content'] as $block) {
            if (!is_array($block)) {
                continue;
            }
            $tool_error_message = extract_claude_non_stream_tool_error_message_logic($block);
            if ($tool_error_message !== null) {
                return new WP_Error(
                    'claude_tool_error',
                    $tool_error_message
                );
            }
            if (($block['type'] ?? '') === 'text' && isset($block['text'])) {
                $content_parts[] = (string) $block['text'];
                $citations = array_merge(
                    $citations,
                    extract_claude_citations_from_text_block_logic_for_response_parser($block)
                );
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

    $return_data = [
        'content' => $content,
        'usage' => $usage,
    ];

    if (!empty($citations)) {
        $return_data['citations'] = $citations;
    }

    return $return_data;
}

/**
 * Detect Claude server-tool error blocks returned inside a successful 200 response body.
 *
 * @param array<string, mixed> $block
 * @return string|null
 */
function extract_claude_non_stream_tool_error_message_logic(array $block): ?string
{
    if (($block['type'] ?? '') !== 'web_search_tool_result') {
        return null;
    }

    $content = $block['content'] ?? null;
    $error_payload = null;

    if (is_array($content) && isset($content['type']) && $content['type'] === 'web_search_tool_result_error') {
        $error_payload = $content;
    } elseif (is_array($content)) {
        foreach ($content as $item) {
            if (is_array($item) && (($item['type'] ?? '') === 'web_search_tool_result_error')) {
                $error_payload = $item;
                break;
            }
        }
    }

    if ($error_payload === null) {
        return null;
    }

    $error_code = isset($error_payload['error_code']) ? (string) $error_payload['error_code'] : 'unknown';

    switch ($error_code) {
        case 'too_many_requests':
            $detail = __('rate limit exceeded', 'gpt3-ai-content-generator');
            break;
        case 'invalid_input':
            $detail = __('invalid search query', 'gpt3-ai-content-generator');
            break;
        case 'max_uses_exceeded':
            $detail = __('maximum web search uses exceeded', 'gpt3-ai-content-generator');
            break;
        case 'query_too_long':
            $detail = __('search query is too long', 'gpt3-ai-content-generator');
            break;
        case 'unavailable':
            $detail = __('service temporarily unavailable', 'gpt3-ai-content-generator');
            break;
        default:
            $detail = str_replace('_', ' ', $error_code);
            break;
    }

    return sprintf(
        /* translators: %s: human-readable Claude web search error detail. */
        __('Claude web search failed: %s.', 'gpt3-ai-content-generator'),
        $detail
    );
}
