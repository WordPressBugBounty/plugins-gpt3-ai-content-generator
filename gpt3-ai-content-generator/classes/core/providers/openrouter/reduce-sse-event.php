<?php
// File: classes/core/providers/openrouter/reduce-sse-event.php

namespace WPAICG\Core\Providers\OpenRouter\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Applies an internal typed event to the flattened parse result expected by the stream processor.
 *
 * @param array<string, mixed> $mapped_event
 * @param array<string, mixed> $result
 * @return bool True when parsing should stop immediately.
 */
function reduce_sse_event_logic_for_response_parser(array $mapped_event, array &$result): bool {
    $kind = isset($mapped_event['kind']) && is_string($mapped_event['kind']) ? $mapped_event['kind'] : 'skip';

    switch ($kind) {
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

        case 'warning':
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
            if (isset($mapped_event['usage']) && is_array($mapped_event['usage'])) {
                $result['usage'] = $mapped_event['usage'];
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

        case 'legacy_chunk':
            if (isset($mapped_event['usage']) && is_array($mapped_event['usage'])) {
                $result['usage'] = $mapped_event['usage'];
            }
            if (!empty($mapped_event['text']) && is_string($mapped_event['text'])) {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= $mapped_event['text'];
            }
            if (!empty($mapped_event['warning_text']) && is_string($mapped_event['warning_text'])) {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= $mapped_event['warning_text'];
                $result['is_warning'] = true;
            }
            return false;

        case 'error':
            $message = isset($mapped_event['message']) ? (string) $mapped_event['message'] : '';
            $result['delta'] = $message;
            $result['is_error'] = true;
            return true;

        case 'done':
            $result['is_done'] = true;
            return false;

        default:
            return false;
    }
}
