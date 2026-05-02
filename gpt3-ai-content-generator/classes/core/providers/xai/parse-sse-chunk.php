<?php
// File: classes/core/providers/xai/parse-sse-chunk.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param XAIProviderStrategy $strategyInstance
 * @return array<string, mixed>
 */
function parse_sse_chunk_logic(XAIProviderStrategy $strategyInstance, string $sse_chunk, string &$current_buffer): array {
    $current_buffer .= $sse_chunk;
    $result = [
        'delta' => null,
        'usage' => null,
        'is_error' => false,
        'is_warning' => false,
        'is_done' => false,
        'status' => null,
        'citations' => null,
        'xai_response_id' => null,
    ];

    foreach (xai_extract_sse_event_blocks($current_buffer) as $event_block) {
        $event = xai_decode_sse_event_block($event_block);
        if ($event === null) {
            continue;
        }

        $should_stop = xai_apply_sse_event($strategyInstance, $event, $result);
        if ($should_stop) {
            return $result;
        }
    }

    return $result;
}

/**
 * @return array<int, string>
 */
function xai_extract_sse_event_blocks(string &$current_buffer): array {
    $event_blocks = [];

    while (preg_match("/\r?\n\r?\n/", $current_buffer, $separator_match, PREG_OFFSET_CAPTURE) === 1) {
        $separator_offset = (int) $separator_match[0][1];
        $separator_length = strlen((string) $separator_match[0][0]);
        $event_block = substr($current_buffer, 0, $separator_offset);
        $current_buffer = substr($current_buffer, $separator_offset + $separator_length);

        if (trim($event_block) !== '') {
            $event_blocks[] = $event_block;
        }
    }

    return $event_blocks;
}

/**
 * @return array<string, mixed>|null
 */
function xai_decode_sse_event_block(string $event_block): ?array {
    $event_type = null;
    $data_lines = [];

    foreach (preg_split("/\r?\n/", $event_block) as $line) {
        $line = rtrim((string) $line, "\r");
        if ($line === '' || $line[0] === ':') {
            continue;
        }
        if (strpos($line, ':') === false) {
            continue;
        }

        [$field, $value] = explode(':', $line, 2);
        $field = trim($field);
        $value = ltrim((string) $value, ' ');

        if ($field === 'event') {
            $event_type = trim($value);
        } elseif ($field === 'data') {
            $data_lines[] = $value;
        }
    }

    if (empty($data_lines)) {
        return null;
    }

    $data = implode("\n", $data_lines);
    if ($data === '[DONE]') {
        return [
            'event' => '[DONE]',
            'payload' => null,
        ];
    }

    $payload = json_decode($data, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
        return null;
    }

    if (($event_type === null || $event_type === '') && isset($payload['type']) && is_string($payload['type'])) {
        $event_type = $payload['type'];
    }
    if ($event_type === null || $event_type === '') {
        $event_type = 'message';
    }

    return [
        'event' => $event_type,
        'payload' => $payload,
    ];
}

/**
 * @param XAIProviderStrategy $strategyInstance
 * @param array<string, mixed> $event
 * @param array<string, mixed> $result
 */
function xai_apply_sse_event(XAIProviderStrategy $strategyInstance, array $event, array &$result): bool {
    $event_type = isset($event['event']) && is_string($event['event']) ? $event['event'] : 'message';
    $payload = isset($event['payload']) && is_array($event['payload']) ? $event['payload'] : [];

    if ($event_type === '[DONE]') {
        $result['is_done'] = true;
        return false;
    }

    if (in_array($event_type, ['ping', 'keepalive'], true)) {
        return false;
    }

    if ($event_type === 'error' || isset($payload['error'])) {
        $result['delta'] = $strategyInstance->parse_error_response($payload, 500);
        $result['is_error'] = true;
        return true;
    }

    $event_citations = xai_extract_citations($payload);
    if (!empty($event_citations)) {
        $result['citations'] = xai_dedupe_citations(array_merge(
            is_array($result['citations']) ? $result['citations'] : [],
            $event_citations
        ));
    }

    switch ($event_type) {
        case 'response.created':
        case 'response.in_progress':
        case 'response.queued':
        case 'response.web_search_call.in_progress':
        case 'response.web_search_call.searching':
        case 'response.web_search_call.completed':
        case 'response.function_call_arguments.delta':
        case 'response.function_call_arguments.done':
            $result['status'] = xai_build_sse_status($event_type, $payload);
            return false;

        case 'response.output_text.delta':
            $delta = isset($payload['delta']) ? (string) $payload['delta'] : '';
            if ($delta !== '') {
                $result['delta'] = ($result['delta'] ?? '') . $delta;
            }
            return false;

        case 'response.refusal.delta':
            $refusal = isset($payload['delta']) ? (string) $payload['delta'] : '';
            if ($refusal !== '') {
                $result['delta'] = ($result['delta'] ?? '') . sprintf(' (%s: %s)', __('Refusal', 'gpt3-ai-content-generator'), $refusal);
                $result['is_warning'] = true;
            }
            return false;

        case 'response.completed':
        case 'response.incomplete':
            $response = isset($payload['response']) && is_array($payload['response']) ? $payload['response'] : $payload;
            $result['is_done'] = true;
            $has_tool_usage = !empty(xai_extract_server_side_tool_usage($response));
            if (isset($response['usage']) && is_array($response['usage'])) {
                $result['usage'] = xai_normalize_usage($response['usage'], $response);
            } elseif ($has_tool_usage) {
                $result['usage'] = xai_normalize_usage([], $response);
            }
            if (!empty($response['id']) && is_string($response['id'])) {
                $result['xai_response_id'] = $response['id'];
            }
            if ($event_type === 'response.incomplete') {
                $reason = $response['incomplete_details']['reason'] ?? 'unknown';
                $result['delta'] = ($result['delta'] ?? '') . sprintf(' (%s: %s)', __('Incomplete', 'gpt3-ai-content-generator'), (string) $reason);
                $result['is_warning'] = true;
            }
            return false;

        case 'response.failed':
            $response = isset($payload['response']) && is_array($payload['response']) ? $payload['response'] : $payload;
            $message = $response['error']['message'] ?? __('xAI response failed.', 'gpt3-ai-content-generator');
            $result['delta'] = sprintf(' (%s: %s)', __('Error', 'gpt3-ai-content-generator'), (string) $message);
            $result['is_error'] = true;
            return true;

        case 'message':
            return xai_apply_message_event($payload, $result);

        default:
            return false;
    }
}

/**
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $result
 */
function xai_apply_message_event(array $payload, array &$result): bool {
    if (isset($payload['choices'][0]['delta']['content'])) {
        $delta = $payload['choices'][0]['delta']['content'];
        if (is_string($delta) && $delta !== '') {
            $result['delta'] = ($result['delta'] ?? '') . $delta;
        }
    }

    if (isset($payload['choices'][0]['finish_reason']) && $payload['choices'][0]['finish_reason'] !== null) {
        $result['is_done'] = true;
    }

    $has_tool_usage = !empty(xai_extract_server_side_tool_usage($payload));
    if (isset($payload['usage']) && is_array($payload['usage'])) {
        $result['usage'] = xai_normalize_usage($payload['usage'], $payload);
    } elseif ($has_tool_usage) {
        $result['usage'] = xai_normalize_usage([], $payload);
    }

    return false;
}

/**
 * @param array<string, mixed> $payload
 * @return array<string, mixed>
 */
function xai_build_sse_status(string $event_type, array $payload): array {
    $status = ['type' => $event_type];

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
