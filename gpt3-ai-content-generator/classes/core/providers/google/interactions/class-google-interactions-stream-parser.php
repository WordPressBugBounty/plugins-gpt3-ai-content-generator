<?php

namespace WPAICG\Core\Providers\Google\Interactions;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsStreamParser
{
    /**
     * Parse complete SSE events from an arbitrarily fragmented network chunk.
     *
     * @param string $chunk  Raw upstream bytes.
     * @param string $buffer Incomplete event buffer, updated by reference.
     * @return array<string, mixed>
     */
    public static function parse(string $chunk, string &$buffer): array
    {
        $buffer .= $chunk;
        $buffer = str_replace(["\r\n", "\r"], "\n", $buffer);

        $result = [
            'delta' => null,
            'output_deltas' => [],
            'usage' => null,
            'interaction_id' => null,
            'status' => null,
            'status_event' => null,
            'citations' => [],
            'grounding_metadata' => null,
            'file_search_events' => [],
            'is_error' => false,
            'error' => null,
            'is_warning' => false,
            'is_done' => false,
        ];

        while (($separator_position = strpos($buffer, "\n\n")) !== false) {
            $event_block = (string) substr($buffer, 0, $separator_position);
            $buffer = (string) substr($buffer, $separator_position + 2);
            if (trim($event_block) === '') {
                continue;
            }

            $event = self::decode_event_block($event_block);
            self::apply_event($event, $result);
            if ($result['is_error']) {
                break;
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private static function decode_event_block(string $event_block): array
    {
        $event_type = '';
        $data_lines = [];

        foreach (explode("\n", $event_block) as $line) {
            if ($line === '' || strpos($line, ':') === 0) {
                continue;
            }
            if (strpos($line, 'event:') === 0) {
                $event_type = trim((string) substr($line, 6));
                continue;
            }
            if (strpos($line, 'data:') === 0) {
                $data_lines[] = ltrim((string) substr($line, 5));
            }
        }

        $raw_data = implode("\n", $data_lines);
        $data = [];
        if ($raw_data !== '' && $raw_data !== '[DONE]') {
            $decoded = json_decode($raw_data, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        if ($event_type === '' && isset($data['event_type']) && is_string($data['event_type'])) {
            $event_type = $data['event_type'];
        }

        return [
            'type' => $event_type,
            'data' => $data,
            'raw_data' => $raw_data,
        ];
    }

    /**
     * @param array<string, mixed> $event
     * @param array<string, mixed> $result
     */
    private static function apply_event(array $event, array &$result): void
    {
        $event_type = isset($event['type']) && is_string($event['type']) ? $event['type'] : '';
        $data = isset($event['data']) && is_array($event['data']) ? $event['data'] : [];

        if ($event_type === 'done' || ($event['raw_data'] ?? '') === '[DONE]') {
            $result['is_done'] = true;
            return;
        }

        if ($event_type === 'error' || isset($data['error']) || $event_type === 'interaction.failed') {
            $status_code = isset($data['error']['code']) && is_numeric($data['error']['code'])
                ? (int) $data['error']['code']
                : 500;
            $result['error'] = GoogleInteractionsErrorParser::to_wp_error($data, $status_code);
            $result['is_error'] = true;
            return;
        }

        if ($event_type === 'interaction.created' || $event_type === 'interaction.completed') {
            $interaction = isset($data['interaction']) && is_array($data['interaction']) ? $data['interaction'] : [];
            if (isset($interaction['id']) && is_string($interaction['id']) && $interaction['id'] !== '') {
                $result['interaction_id'] = $interaction['id'];
            }
            if (isset($interaction['status']) && is_string($interaction['status'])) {
                $result['status'] = $interaction['status'];
            }
            if (isset($interaction['usage']) && is_array($interaction['usage'])) {
                $result['usage'] = GoogleInteractionsResponseParser::normalize_usage($interaction['usage']);
            }
            return;
        }

        if ($event_type === 'interaction.in_progress' || $event_type === 'interaction.requires_action') {
            if (isset($data['interaction_id']) && is_string($data['interaction_id']) && $data['interaction_id'] !== '') {
                $result['interaction_id'] = $data['interaction_id'];
            }
            $result['status'] = $event_type === 'interaction.requires_action'
                ? 'requires_action'
                : 'in_progress';
            return;
        }

        if ($event_type === 'interaction.status_update') {
            if (isset($data['interaction_id']) && is_string($data['interaction_id']) && $data['interaction_id'] !== '') {
                $result['interaction_id'] = $data['interaction_id'];
            }
            if (isset($data['status']) && is_string($data['status'])) {
                $result['status'] = $data['status'];
            }
            return;
        }

        if ($event_type === 'step.start' && isset($data['step']) && is_array($data['step'])) {
            if (($data['step']['type'] ?? '') === 'google_search_call') {
                $result['status_event'] = ['type' => 'google_search_call'];
            }
            if (in_array(($data['step']['type'] ?? ''), ['file_search_call', 'file_search_result'], true)) {
                $result['file_search_events'][] = $data['step'];
            }
            return;
        }

        if ($event_type !== 'step.delta' || !isset($data['delta']) || !is_array($data['delta'])) {
            return;
        }

        $delta = $data['delta'];
        $result['output_deltas'][] = $delta;
        if (($delta['type'] ?? '') === 'google_search_call') {
            $result['status_event'] = ['type' => 'google_search_call'];
        }
        if (in_array(($delta['type'] ?? ''), ['file_search_call', 'file_search_result'], true)) {
            $result['file_search_events'][] = $delta;
        }
        if (($delta['type'] ?? '') === 'text' && isset($delta['text']) && is_string($delta['text'])) {
            if ($result['delta'] === null) {
                $result['delta'] = '';
            }
            $result['delta'] .= $delta['text'];
        }

        $result['citations'] = GoogleInteractionsResponseParser::merge_citations(
            $result['citations'],
            GoogleInteractionsResponseParser::extract_citations($delta)
        );

        if (($delta['type'] ?? '') === 'google_search_result') {
            $grounding_metadata = GoogleInteractionsResponseParser::extract_grounding_metadata([$delta]);
            if ($grounding_metadata !== null) {
                $result['grounding_metadata'] = $grounding_metadata;
            }
        }
    }
}
