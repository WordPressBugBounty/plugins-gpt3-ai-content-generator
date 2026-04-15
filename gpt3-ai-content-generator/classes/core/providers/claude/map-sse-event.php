<?php

namespace WPAICG\Core\Providers\Claude\Methods;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Map one normalized Claude SSE event into an internal typed event.
 *
 * @param array<string, mixed> $decoded_event
 * @return array<string, mixed>
 */
function map_sse_event_logic_for_response_parser(array $decoded_event): array
{
    $event_type = isset($decoded_event['event']) && is_string($decoded_event['event']) ? $decoded_event['event'] : 'message';
    $payload = isset($decoded_event['payload']) && is_array($decoded_event['payload']) ? $decoded_event['payload'] : [];

    if ($event_type === '[DONE]') {
        return [
            'kind' => 'done',
            'event' => $event_type,
        ];
    }

    if ($event_type === 'ping') {
        return [
            'kind' => 'skip',
            'event' => $event_type,
        ];
    }

    if ($event_type === 'error' || isset($payload['error'])) {
        return [
            'kind' => 'error',
            'event' => $event_type,
            'message' => parse_claude_stream_error_message_logic_for_response_parser($payload),
        ];
    }

    switch ($event_type) {
        case 'message_start':
            return [
                'kind' => 'message_start',
                'event' => $event_type,
                'usage' => extract_claude_sse_usage_logic_for_response_parser($payload),
                'status' => build_claude_sse_status_logic_for_response_parser($event_type, $payload),
            ];

        case 'message_delta':
            $warning_text = null;
            $stop_reason = $payload['delta']['stop_reason'] ?? null;
            if ($stop_reason === 'max_tokens') {
                $warning_text = __('Claude stopped because the maximum output token limit was reached.', 'gpt3-ai-content-generator');
            }

            return [
                'kind' => 'message_delta',
                'event' => $event_type,
                'usage' => extract_claude_sse_usage_logic_for_response_parser($payload),
                'status' => build_claude_sse_status_logic_for_response_parser($event_type, $payload),
                'warning_text' => $warning_text,
            ];

        case 'message_stop':
            return [
                'kind' => 'completion',
                'event' => $event_type,
                'status' => build_claude_sse_status_logic_for_response_parser($event_type, $payload),
            ];

        case 'content_block_start':
            $content_block = isset($payload['content_block']) && is_array($payload['content_block'])
                ? $payload['content_block']
                : [];
            $tool_error_message = extract_claude_tool_error_message_logic_for_response_parser($content_block);
            if ($tool_error_message !== null) {
                return [
                    'kind' => 'error',
                    'event' => $event_type,
                    'message' => $tool_error_message,
                ];
            }

            $content_block_type = $content_block['type'] ?? '';
            if (in_array($content_block_type, ['tool_use', 'server_tool_use', 'web_search_tool_result', 'thinking'], true)) {
                return [
                    'kind' => 'status',
                    'event' => $event_type,
                    'status' => build_claude_sse_status_logic_for_response_parser($event_type, $payload),
                ];
            }

            return [
                'kind' => 'skip',
                'event' => $event_type,
            ];

        case 'content_block_delta':
            $delta = isset($payload['delta']) && is_array($payload['delta']) ? $payload['delta'] : [];
            $delta_type = $delta['type'] ?? '';

            if ($delta_type === 'text_delta' && isset($delta['text']) && $delta['text'] !== '') {
                return [
                    'kind' => 'delta',
                    'event' => $event_type,
                    'text' => (string) $delta['text'],
                ];
            }

            if ($delta_type === 'citations_delta') {
                $citations = extract_claude_citations_from_delta_logic_for_response_parser(
                    $delta,
                    isset($payload['index']) ? ['content_block_index' => (int) $payload['index']] : []
                );

                if (!empty($citations)) {
                    return [
                        'kind' => 'citations',
                        'event' => $event_type,
                        'citations' => $citations,
                    ];
                }
            }

            if (in_array($delta_type, ['input_json_delta', 'thinking_delta', 'signature_delta', 'citations_delta'], true)) {
                return [
                    'kind' => 'skip',
                    'event' => $event_type,
                ];
            }

            return [
                'kind' => 'skip',
                'event' => $event_type,
            ];

        case 'content_block_stop':
        default:
            return [
                'kind' => 'skip',
                'event' => $event_type,
            ];
    }
}

/**
 * Build a compact status payload for Claude tool/search lifecycle events.
 *
 * @param string $event_type
 * @param array<string, mixed> $payload
 * @return array<string, mixed>
 */
function build_claude_sse_status_logic_for_response_parser(string $event_type, array $payload): array
{
    $status = ['type' => $event_type];

    if (isset($payload['message']['id'])) {
        $status['message_id'] = $payload['message']['id'];
    }
    if (isset($payload['message']['model'])) {
        $status['model'] = $payload['message']['model'];
    }
    if (isset($payload['index'])) {
        $status['index'] = $payload['index'];
    }
    if (isset($payload['delta']['stop_reason'])) {
        $status['stop_reason'] = $payload['delta']['stop_reason'];
    }
    if (isset($payload['delta']['stop_sequence'])) {
        $status['stop_sequence'] = $payload['delta']['stop_sequence'];
    }
    if (isset($payload['content_block']['type'])) {
        $status['content_block_type'] = $payload['content_block']['type'];
    }
    if (isset($payload['content_block']['name'])) {
        $status['name'] = $payload['content_block']['name'];
    }
    if (isset($payload['content_block']['id'])) {
        $status['tool_use_id'] = $payload['content_block']['id'];
    }
    if (isset($payload['content_block']['tool_use_id'])) {
        $status['tool_use_id'] = $payload['content_block']['tool_use_id'];
    }

    return $status;
}

/**
 * Normalize Claude usage data into the shared public parse shape.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>|null
 */
function extract_claude_sse_usage_logic_for_response_parser(array $payload): ?array
{
    $usage = null;

    if (isset($payload['message']['usage']) && is_array($payload['message']['usage'])) {
        $usage = $payload['message']['usage'];
    } elseif (isset($payload['usage']) && is_array($payload['usage'])) {
        $usage = $payload['usage'];
    }

    if ($usage === null) {
        return null;
    }

    $input_tokens = isset($usage['input_tokens']) ? (int) $usage['input_tokens'] : null;
    $output_tokens = isset($usage['output_tokens']) ? (int) $usage['output_tokens'] : null;
    $total_tokens = isset($usage['total_tokens']) ? (int) $usage['total_tokens'] : null;

    return [
        'input_tokens' => $input_tokens,
        'output_tokens' => $output_tokens,
        'total_tokens' => $total_tokens,
        'provider_raw' => $usage,
    ];
}

/**
 * Parse Claude in-stream error payloads without depending on the provider strategy object.
 *
 * @param array<string, mixed> $payload
 * @return string
 */
function parse_claude_stream_error_message_logic_for_response_parser(array $payload): string
{
    $message = __('An unknown API error occurred.', 'gpt3-ai-content-generator');

    if (!empty($payload['error']['message'])) {
        $message = (string) $payload['error']['message'];
        if (!empty($payload['error']['type'])) {
            $message .= ' Type: ' . (string) $payload['error']['type'];
        }
    } elseif (!empty($payload['message'])) {
        $message = (string) $payload['message'];
    }

    return trim($message);
}

/**
 * Extract a user-facing tool error message from Claude content blocks when Anthropic returns a 200 body with a tool error.
 *
 * @param array<string, mixed> $content_block
 * @return string|null
 */
function extract_claude_tool_error_message_logic_for_response_parser(array $content_block): ?string
{
    if (($content_block['type'] ?? '') !== 'web_search_tool_result') {
        return null;
    }

    $content = $content_block['content'] ?? null;
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
