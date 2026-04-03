<?php
// File: classes/core/providers/openrouter/decode-sse-event-block.php

namespace WPAICG\Core\Providers\OpenRouter\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Decodes a single OpenRouter SSE event block into a normalized upstream event.
 *
 * @param string $event_block The raw SSE event block.
 * @return array<string, mixed>|null
 */
function decode_sse_event_block_logic_for_response_parser(string $event_block): ?array {
    $event_type = null;
    $event_data_lines = [];

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
            $event_data_lines[] = $value;
        }
    }

    if (empty($event_data_lines)) {
        return null;
    }

    $event_data = implode("\n", $event_data_lines);
    if ($event_data === '[DONE]') {
        return [
            'event' => '[DONE]',
            'payload' => null,
        ];
    }

    $decoded_data = json_decode($event_data, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded_data)) {
        return null;
    }

    if (($event_type === null || $event_type === '') && isset($decoded_data['type']) && is_string($decoded_data['type'])) {
        $event_type = $decoded_data['type'];
    }

    if ($event_type === null || $event_type === '') {
        $event_type = 'message';
    }

    return [
        'event' => $event_type,
        'payload' => $decoded_data,
    ];
}
