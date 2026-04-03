<?php
// File: classes/core/providers/openai/map-sse-event.php

namespace WPAICG\Core\Providers\OpenAI\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Maps a normalized upstream OpenAI SSE event into an internal typed event.
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

    $annotation_citations = extract_openai_citations_from_event_payload_logic_for_response_parser($payload, false);

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
            return [
                'kind' => 'status',
                'event' => $event_type,
                'status' => build_sse_status_logic_for_response_parser($event_type, $payload),
            ];

        case 'response.output_text.delta':
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

        case 'response.content_part.done':
        case 'response.output_item.done':
            if (!empty($annotation_citations)) {
                return [
                    'kind' => 'citations',
                    'event' => $event_type,
                    'citations' => $annotation_citations,
                ];
            }

            return [
                'kind' => 'skip',
                'event' => $event_type,
            ];

        case 'response.completed':
        case 'response.incomplete':
            $completion_citations = extract_openai_citations_from_event_payload_logic_for_response_parser($payload, true);
            $warning_text = null;
            if ($event_type === 'response.incomplete') {
                $reason = $payload['response']['incomplete_details']['reason'] ?? 'unknown';
                $warning_text = sprintf(' (%s: %s)', __('Incomplete', 'gpt3-ai-content-generator'), $reason);
            }

            return [
                'kind' => 'completion',
                'event' => $event_type,
                'usage' => extract_sse_usage_logic_for_response_parser($payload),
                'response_id' => $payload['response']['id'] ?? null,
                'warning_text' => $warning_text,
                'citations' => $completion_citations,
            ];

        case 'response.failed':
            $error_message = $payload['response']['error']['message'] ?? __('Response failed', 'gpt3-ai-content-generator');

            return [
                'kind' => 'error',
                'event' => $event_type,
                'message' => sprintf(' (%s: %s)', __('Error', 'gpt3-ai-content-generator'), $error_message),
            ];

        default:
            return [
                'kind' => 'skip',
                'event' => $event_type,
            ];
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
    }
    if (isset($payload['item_id'])) {
        $status['item_id'] = $payload['item_id'];
    }
    if (isset($payload['output_index'])) {
        $status['output_index'] = $payload['output_index'];
    }

    return $status;
}

/**
 * Extracts usage information from a completion event payload using the existing public parse shape.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>|null
 */
function extract_sse_usage_logic_for_response_parser(array $payload): ?array {
    if (!isset($payload['response']['usage']) || !is_array($payload['response']['usage'])) {
        return null;
    }

    $usage = $payload['response']['usage'];

    return [
        'input_tokens' => $usage['input_tokens'] ?? 0,
        'output_tokens' => $usage['output_tokens'] ?? 0,
        'total_tokens' => $usage['total_tokens'] ?? 0,
        'provider_raw' => $usage,
    ];
}
