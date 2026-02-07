<?php
// File: classes/core/providers/openrouter/parse-chat.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\OpenRouter\Methods;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the parse_chat static method of OpenRouterResponseParser.
 *
 * @param array $decoded_response The decoded JSON response.
 * @return array|WP_Error ['content' => string, 'usage' => array|null] or WP_Error.
 */
function parse_chat_logic_for_response_parser(array $decoded_response): array|WP_Error {
    $content = null;
    $usage = null;

    if (isset($decoded_response['status']) && $decoded_response['status'] === 'failed') {
        $failed_message = parse_error_logic_for_response_parser($decoded_response, 500);
        return new WP_Error('openrouter_failed_response_logic', $failed_message);
    }

    if (isset($decoded_response['output_text']) && is_string($decoded_response['output_text'])) {
        $output_text = trim($decoded_response['output_text']);
        if ($output_text !== '') {
            $content = $output_text;
        }
    }

    if ($content === null && isset($decoded_response['output']) && is_array($decoded_response['output'])) {
        $parts = [];
        foreach ($decoded_response['output'] as $output_item) {
            if (!is_array($output_item) || ($output_item['type'] ?? '') !== 'message') {
                continue;
            }
            if (empty($output_item['content']) || !is_array($output_item['content'])) {
                continue;
            }
            foreach ($output_item['content'] as $content_item) {
                if (!is_array($content_item)) {
                    continue;
                }
                $part_type = $content_item['type'] ?? '';
                if (($part_type === 'output_text' || $part_type === 'text') && isset($content_item['text']) && is_string($content_item['text'])) {
                    $parts[] = $content_item['text'];
                }
            }
        }
        if (!empty($parts)) {
            $joined = trim(implode('', $parts));
            if ($joined !== '') {
                $content = $joined;
            }
        }
    }

    // Backward-compat fallback for chat-completions shaped payloads.
    if ($content === null) {
        if (isset($decoded_response['choices'][0]['message']['content']) && is_string($decoded_response['choices'][0]['message']['content'])) {
            $content = trim($decoded_response['choices'][0]['message']['content']);
        } elseif (isset($decoded_response['choices'][0]['delta']['content']) && is_string($decoded_response['choices'][0]['delta']['content'])) {
            $content = trim($decoded_response['choices'][0]['delta']['content']);
        } elseif (isset($decoded_response['choices'][0]['text']) && is_string($decoded_response['choices'][0]['text'])) {
            $content = trim($decoded_response['choices'][0]['text']);
        }
    }

    if (isset($decoded_response['status']) && $decoded_response['status'] === 'incomplete') {
        $reason = $decoded_response['incomplete_details']['reason'] ?? 'unknown';
        if ($content !== null && $content !== '') {
            $content .= sprintf(' (%s: %s)', __('Incomplete', 'gpt3-ai-content-generator'), $reason);
        } else {
            /* translators: %s: The reason why the OpenRouter response was incomplete. */
            return new WP_Error('openrouter_incomplete_response_logic', sprintf(__('Response incomplete due to: %s', 'gpt3-ai-content-generator'), $reason));
        }
    }

    $usage_source = null;
    if (isset($decoded_response['usage']) && is_array($decoded_response['usage'])) {
        $usage_source = $decoded_response['usage'];
    } elseif (isset($decoded_response['response']['usage']) && is_array($decoded_response['response']['usage'])) {
        $usage_source = $decoded_response['response']['usage'];
    }
    if ($usage_source !== null) {
        $usage = [
            'input_tokens'  => $usage_source['input_tokens'] ?? $usage_source['prompt_tokens'] ?? 0,
            'output_tokens' => $usage_source['output_tokens'] ?? $usage_source['completion_tokens'] ?? 0,
            'total_tokens'  => $usage_source['total_tokens'] ?? 0,
            'provider_raw'  => $usage_source,
        ];
    }

    if ($content === null) {
        if (isset($decoded_response['choices'][0]['finish_reason']) && $decoded_response['choices'][0]['finish_reason'] === 'content_filter') {
            return new WP_Error('content_filter_logic', __('Response blocked due to content filtering.', 'gpt3-ai-content-generator'));
        }
        return new WP_Error('invalid_response_structure_openrouter_logic', __('Unexpected response structure from OpenRouter API.', 'gpt3-ai-content-generator'));
    }

    return ['content' => $content, 'usage' => $usage];
}
