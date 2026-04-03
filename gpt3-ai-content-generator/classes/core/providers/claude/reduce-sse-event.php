<?php

namespace WPAICG\Core\Providers\Claude\Methods;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Apply an internal Claude event to the flattened parse result used by the stream processor.
 *
 * @param array<string, mixed> $mapped_event
 * @param array<string, mixed> $result
 * @return bool True when parsing should stop immediately.
 */
function reduce_sse_event_logic_for_response_parser(array $mapped_event, array &$result): bool
{
    $kind = isset($mapped_event['kind']) && is_string($mapped_event['kind']) ? $mapped_event['kind'] : 'skip';

    switch ($kind) {
        case 'message_start':
        case 'message_delta':
            if (isset($mapped_event['usage']) && is_array($mapped_event['usage'])) {
                $result['usage'] = merge_claude_sse_usage_logic_for_response_parser(
                    isset($result['usage']) && is_array($result['usage']) ? $result['usage'] : null,
                    $mapped_event['usage']
                );
            }
            if (isset($mapped_event['status']) && is_array($mapped_event['status'])) {
                $result['status'] = $mapped_event['status'];
            }
            if (!empty($mapped_event['warning_text']) && is_string($mapped_event['warning_text'])) {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= $mapped_event['warning_text'];
                $result['is_warning'] = true;
            }
            return false;

        case 'status':
            if (isset($mapped_event['status']) && is_array($mapped_event['status'])) {
                $result['status'] = $mapped_event['status'];
            }
            return false;

        case 'delta':
            $text = isset($mapped_event['text']) ? (string) $mapped_event['text'] : '';
            if ($text !== '') {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= $text;
            }
            return false;

        case 'citations':
            if (isset($mapped_event['citations']) && is_array($mapped_event['citations']) && !empty($mapped_event['citations'])) {
                $existing_citations = isset($result['citations']) && is_array($result['citations'])
                    ? $result['citations']
                    : [];
                $result['citations'] = array_merge($existing_citations, $mapped_event['citations']);
            }
            return false;

        case 'warning':
            if (isset($mapped_event['status']) && is_array($mapped_event['status'])) {
                $result['status'] = $mapped_event['status'];
            }
            $text = isset($mapped_event['text']) ? (string) $mapped_event['text'] : '';
            if ($text !== '') {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= $text;
                $result['is_warning'] = true;
            }
            return false;

        case 'completion':
            $result['is_done'] = true;
            if (isset($mapped_event['status']) && is_array($mapped_event['status'])) {
                $result['status'] = $mapped_event['status'];
            }
            return false;

        case 'error':
            $result['delta'] = isset($mapped_event['message']) ? (string) $mapped_event['message'] : '';
            $result['is_error'] = true;
            return true;

        case 'done':
            $result['is_done'] = true;
            return false;

        default:
            return false;
    }
}

/**
 * Merge Claude usage updates while preserving input token counts from message_start when later cumulative updates omit them.
 *
 * @param array<string, mixed>|null $existing_usage
 * @param array<string, mixed> $incoming_usage
 * @return array<string, mixed>
 */
function merge_claude_sse_usage_logic_for_response_parser(?array $existing_usage, array $incoming_usage): array
{
    $existing_raw = isset($existing_usage['provider_raw']) && is_array($existing_usage['provider_raw'])
        ? $existing_usage['provider_raw']
        : [];
    $incoming_raw = isset($incoming_usage['provider_raw']) && is_array($incoming_usage['provider_raw'])
        ? $incoming_usage['provider_raw']
        : [];

    $input_tokens = null;
    if (array_key_exists('input_tokens', $incoming_raw)) {
        $input_tokens = (int) $incoming_raw['input_tokens'];
    } elseif ($existing_usage !== null && isset($existing_usage['input_tokens'])) {
        $input_tokens = (int) $existing_usage['input_tokens'];
    }

    $output_tokens = null;
    if (array_key_exists('output_tokens', $incoming_raw)) {
        $output_tokens = (int) $incoming_raw['output_tokens'];
    } elseif ($existing_usage !== null && isset($existing_usage['output_tokens'])) {
        $output_tokens = (int) $existing_usage['output_tokens'];
    }

    $total_tokens = null;
    if (array_key_exists('total_tokens', $incoming_raw)) {
        $total_tokens = (int) $incoming_raw['total_tokens'];
    } elseif ($input_tokens !== null || $output_tokens !== null) {
        $total_tokens = (int) ($input_tokens ?? 0) + (int) ($output_tokens ?? 0);
    }

    return [
        'input_tokens' => $input_tokens ?? 0,
        'output_tokens' => $output_tokens ?? 0,
        'total_tokens' => $total_tokens ?? 0,
        'provider_raw' => array_merge($existing_raw, $incoming_raw),
    ];
}
