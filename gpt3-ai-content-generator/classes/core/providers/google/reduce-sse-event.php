<?php
// File: classes/core/providers/google/reduce-sse-event.php

namespace WPAICG\Core\Providers\Google\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Applies an internal typed Google SSE event to the flattened parse result expected by the stream processor.
 *
 * @param array<string, mixed> $mapped_event
 * @param array<string, mixed> $result
 * @return bool True when parsing should stop immediately.
 */
function reduce_sse_event_logic_for_response_parser(array $mapped_event, array &$result): bool {
    $kind = isset($mapped_event['kind']) && is_string($mapped_event['kind']) ? $mapped_event['kind'] : 'skip';

    switch ($kind) {
        case 'chunk':
            if (isset($mapped_event['usage']) && is_array($mapped_event['usage'])) {
                $result['usage'] = $mapped_event['usage'];
            }

            if (isset($mapped_event['grounding_metadata']) && is_array($mapped_event['grounding_metadata'])) {
                $result['grounding_metadata'] = $mapped_event['grounding_metadata'];
            }

            if (isset($mapped_event['citations']) && is_array($mapped_event['citations']) && !empty($mapped_event['citations'])) {
                $existing_citations = isset($result['citations']) && is_array($result['citations'])
                    ? $result['citations']
                    : [];
                $result['citations'] = merge_google_sse_citations_logic_for_response_parser($existing_citations, $mapped_event['citations']);
            }

            if (!empty($mapped_event['delta_text']) && is_string($mapped_event['delta_text'])) {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= $mapped_event['delta_text'];
            }

            if (!empty($mapped_event['notice_text']) && is_string($mapped_event['notice_text'])) {
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= $mapped_event['notice_text'];
            }

            if (!empty($mapped_event['is_warning'])) {
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

/**
 * Merge citations while preserving order and removing duplicates.
 *
 * @param array<int, array<string, mixed>> $existing
 * @param array<int, array<string, mixed>> $incoming
 * @return array<int, array<string, mixed>>
 */
function merge_google_sse_citations_logic_for_response_parser(array $existing, array $incoming): array {
    return dedupe_google_citations_logic_for_response_parser(array_merge($existing, $incoming));
}
