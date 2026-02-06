<?php

namespace WPAICG\Core\Providers\Claude\Methods;

use WPAICG\Core\Providers\ClaudeProviderStrategy;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Parse Claude SSE chunks.
 */
function parse_sse_chunk_logic(
    ClaudeProviderStrategy $strategyInstance,
    string $sse_chunk,
    string &$current_buffer
): array {
    $current_buffer .= $sse_chunk;

    $result = [
        'delta' => null,
        'usage' => null,
        'is_error' => false,
        'is_warning' => false,
        'is_done' => false,
    ];

    while (($line_end_pos = strpos($current_buffer, "\n\n")) !== false) {
        $event_block = substr($current_buffer, 0, $line_end_pos);
        $current_buffer = substr($current_buffer, $line_end_pos + 2);

        $event_type = 'message';
        $event_data_json = '';

        foreach (explode("\n", $event_block) as $line) {
            $line = rtrim($line, "\r");
            if ($line === '' || strpos($line, ':') === false) {
                continue;
            }
            [$field, $value] = explode(':', $line, 2);
            $field = trim($field);
            $value = ltrim($value);

            if ($field === 'event') {
                $event_type = $value;
            } elseif ($field === 'data') {
                $event_data_json .= ($event_data_json === '' ? '' : "\n") . $value;
            }
        }

        if ($event_data_json === '' || $event_data_json === '[DONE]') {
            if ($event_data_json === '[DONE]') {
                $result['is_done'] = true;
            }
            continue;
        }

        $decoded = json_decode($event_data_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            continue;
        }

        if ($event_type === 'error' || isset($decoded['error'])) {
            $result['delta'] = $strategyInstance->parse_error_response($decoded, 500);
            $result['is_error'] = true;
            return $result;
        }

        if (isset($decoded['usage']) && is_array($decoded['usage'])) {
            $input_tokens = (int) ($decoded['usage']['input_tokens'] ?? 0);
            $output_tokens = (int) ($decoded['usage']['output_tokens'] ?? 0);
            $result['usage'] = [
                'input_tokens' => $input_tokens,
                'output_tokens' => $output_tokens,
                'total_tokens' => $input_tokens + $output_tokens,
                'provider_raw' => $decoded['usage'],
            ];
        }

        if (($event_type === 'content_block_delta' || ($decoded['type'] ?? '') === 'content_block_delta')
            && isset($decoded['delta'])
            && is_array($decoded['delta'])
        ) {
            if (($decoded['delta']['type'] ?? '') === 'text_delta' && isset($decoded['delta']['text'])) {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= (string) $decoded['delta']['text'];
            }
        }

        if ($event_type === 'message_stop' || ($decoded['type'] ?? '') === 'message_stop') {
            $result['is_done'] = true;
        }

        if (($event_type === 'message_delta' || ($decoded['type'] ?? '') === 'message_delta')
            && isset($decoded['delta']['stop_reason'])
            && $decoded['delta']['stop_reason'] === 'max_tokens'
        ) {
            $result['is_warning'] = true;
        }
    }

    return $result;
}
