<?php
// File: classes/core/providers/openrouter/map-sse-event.php

namespace WPAICG\Core\Providers\OpenRouter\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Maps a normalized OpenRouter SSE event into an internal typed event.
 *
 * @param array<string, mixed> $decoded_event
 * @return array<string, mixed>
 */
function map_sse_event_logic_for_response_parser(array $decoded_event): array {
    $event_type = isset($decoded_event['event']) && is_string($decoded_event['event']) ? $decoded_event['event'] : 'message';
    $payload = isset($decoded_event['payload']) && is_array($decoded_event['payload']) ? $decoded_event['payload'] : [];

    if ($event_type === '[DONE]') {
        return [
            'kind' => 'done',
            'event' => $event_type,
        ];
    }

    if (in_array($event_type, ['ping', 'keepalive'], true)) {
        return [
            'kind' => 'skip',
            'event' => $event_type,
        ];
    }

    if ($event_type === 'error' || isset($payload['error'])) {
        return [
            'kind' => 'error',
            'event' => $event_type,
            'message' => parse_error_logic_for_response_parser($payload, 500),
        ];
    }

    switch ($event_type) {
        case 'response.created':
        case 'response.in_progress':
        case 'response.queued':
        case 'response.web_search_call.in_progress':
        case 'response.web_search_call.searching':
        case 'response.web_search_call.completed':
        case 'response.file_search_call.in_progress':
        case 'response.file_search_call.searching':
        case 'response.file_search_call.completed':
        case 'response.image_generation_call.in_progress':
        case 'response.image_generation_call.generating':
        case 'response.image_generation_call.completed':
        case 'response.output_item.added':
        case 'response.output_item.done':
        case 'response.content_part.added':
        case 'response.output_text.done':
        case 'response.reasoning.delta':
        case 'response.reasoning.done':
        case 'response.function_call_arguments.delta':
        case 'response.function_call_arguments.done':
        case 'tool.preliminary_result':
        case 'tool.result':
            return [
                'kind' => 'status',
                'event' => $event_type,
                'status' => build_sse_status_logic_for_response_parser($event_type, $payload),
            ];

        case 'response.output_text.delta':
        case 'response.content_part.delta':
            $delta_text = isset($payload['delta']) ? (string) $payload['delta'] : '';
            if ($delta_text === '') {
                return [
                    'kind' => 'skip',
                    'event' => $event_type,
                ];
            }

            return [
                'kind' => 'delta',
                'event' => $event_type,
                'text' => $delta_text,
            ];

        case 'response.refusal.delta':
            $refusal_text = isset($payload['delta']) ? (string) $payload['delta'] : '';
            if ($refusal_text === '') {
                return [
                    'kind' => 'skip',
                    'event' => $event_type,
                ];
            }

            return [
                'kind' => 'warning',
                'event' => $event_type,
                'text' => sprintf(' (%s: %s)', __('Refusal', 'gpt3-ai-content-generator'), $refusal_text),
            ];

        case 'response.done':
        case 'response.completed':
        case 'response.incomplete':
            $warning_text = null;
            if ($event_type === 'response.incomplete') {
                $reason = $payload['response']['incomplete_details']['reason'] ?? 'unknown';
                $warning_text = sprintf(' (%s: %s)', __('Incomplete', 'gpt3-ai-content-generator'), $reason);
            }

            return [
                'kind' => 'completion',
                'event' => $event_type,
                'usage' => extract_sse_usage_logic_for_response_parser($payload),
                'status' => build_sse_status_logic_for_response_parser($event_type, $payload),
                'warning_text' => $warning_text,
            ];

        case 'response.failed':
            $error_message = $payload['response']['error']['message'] ?? __('Response failed', 'gpt3-ai-content-generator');

            return [
                'kind' => 'error',
                'event' => $event_type,
                'message' => sprintf(' (%s: %s)', __('Error', 'gpt3-ai-content-generator'), $error_message),
            ];

        default:
            // Backward-compat fallback for chat-completions stream shape.
            return map_chat_completions_fallback_sse_event_logic_for_response_parser($payload, $event_type);
    }
}

/**
 * Builds the status payload that gets forwarded to the public stream formatter.
 *
 * @param string $type
 * @param array<string, mixed> $payload
 * @return array<string, mixed>
 */
function build_sse_status_logic_for_response_parser(string $type, array $payload): array {
    $status = ['type' => $type];

    if (isset($payload['response']['status'])) {
        $status['status'] = $payload['response']['status'];
    }
    if (isset($payload['response']['id'])) {
        $status['response_id'] = $payload['response']['id'];
    } elseif (isset($payload['response_id'])) {
        $status['response_id'] = $payload['response_id'];
    }
    if (isset($payload['item_id'])) {
        $status['item_id'] = $payload['item_id'];
    }
    if (isset($payload['output_index'])) {
        $status['output_index'] = $payload['output_index'];
    }
    if (isset($payload['call_id'])) {
        $status['call_id'] = $payload['call_id'];
    }
    if (isset($payload['item']['id'])) {
        $status['item_id'] = $payload['item']['id'];
    }
    if (isset($payload['item']['type'])) {
        $status['item_type'] = $payload['item']['type'];
    }
    if (isset($payload['item']['status'])) {
        $status['item_status'] = $payload['item']['status'];
    }
    if (isset($payload['item']['name'])) {
        $status['name'] = $payload['item']['name'];
    }

    return $status;
}

/**
 * Extracts usage information from a completion payload using the existing public parse shape.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>|null
 */
function extract_sse_usage_logic_for_response_parser(array $payload): ?array {
    if (isset($payload['response']['usage']) && is_array($payload['response']['usage'])) {
        $usage = $payload['response']['usage'];
    } elseif (isset($payload['usage']) && is_array($payload['usage'])) {
        $usage = $payload['usage'];
    } else {
        return null;
    }

    return [
        'input_tokens' => $usage['input_tokens'] ?? $usage['prompt_tokens'] ?? 0,
        'output_tokens' => $usage['output_tokens'] ?? $usage['completion_tokens'] ?? 0,
        'total_tokens' => $usage['total_tokens'] ?? 0,
        'provider_raw' => $usage,
    ];
}

/**
 * Handles legacy chat-completions-shaped fallback stream payloads.
 *
 * @param array<string, mixed> $payload
 * @param string $event_type
 * @return array<string, mixed>
 */
function map_chat_completions_fallback_sse_event_logic_for_response_parser(array $payload, string $event_type): array {
    $usage = extract_sse_usage_logic_for_response_parser($payload);
    $delta_text = null;
    if (isset($payload['choices'][0]['delta']['content'])) {
        $delta_text = (string) $payload['choices'][0]['delta']['content'];
        if ($delta_text === '') {
            $delta_text = null;
        }
    }

    $warning_text = null;
    if (isset($payload['choices'][0]['finish_reason']) && $payload['choices'][0]['finish_reason'] === 'content_filter') {
        $warning_text = sprintf(' (%s)', __('Warning: Content Filtered', 'gpt3-ai-content-generator'));
    }

    if ($delta_text === null && $usage === null && $warning_text === null) {
        return [
            'kind' => 'skip',
            'event' => $event_type,
        ];
    }

    return [
        'kind' => 'legacy_chunk',
        'event' => $event_type,
        'usage' => $usage,
        'text' => $delta_text,
        'warning_text' => $warning_text,
    ];
}
