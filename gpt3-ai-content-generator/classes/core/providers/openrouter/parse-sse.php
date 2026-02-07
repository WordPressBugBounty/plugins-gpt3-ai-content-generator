<?php
// File: classes/core/providers/openrouter/parse-sse.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\OpenRouter\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for the parse_sse_chunk static method of OpenRouterResponseParser.
 *
 * @param string $sse_chunk The raw chunk received.
 * @param string &$current_buffer Reference to the incomplete buffer.
 * @return array Result containing delta, usage, flags.
 */
function parse_sse_chunk_logic_for_response_parser(string $sse_chunk, string &$current_buffer): array {
    $current_buffer .= $sse_chunk;
    $result = [
        'delta' => null,
        'usage' => null,
        'is_error' => false,
        'is_warning' => false,
        'is_done' => false,
        'status' => null,
    ];

    while (preg_match("/\r?\n\r?\n/", $current_buffer, $separator_match, PREG_OFFSET_CAPTURE) === 1) {
        $separator_offset = (int) $separator_match[0][1];
        $separator_length = strlen((string) $separator_match[0][0]);
        $event_block = substr($current_buffer, 0, $separator_offset);
        $current_buffer = substr($current_buffer, $separator_offset + $separator_length);

        if (trim($event_block) === '') {
            continue;
        }

        $event_data_lines = [];
        $event_type = null;

        foreach (preg_split("/\r?\n/", $event_block) as $line) {
            if ($line === '' || strpos($line, ':') === false) {
                continue;
            }
            [$field, $value] = explode(':', $line, 2);
            $field = trim($field);
            // SSE spec allows a single leading space after ":".
            $value = ltrim((string) $value, ' ');
            if ($field === 'event') {
                $event_type = trim($value);
            } elseif ($field === 'data') {
                $event_data_lines[] = $value;
            }
        }

        if (empty($event_data_lines)) {
            continue;
        }
        $event_data_json = implode("\n", $event_data_lines);

        if ($event_data_json === '[DONE]') {
            $result['is_done'] = true;
            continue;
        }

        $decoded = json_decode($event_data_json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            continue;
        }

        // OpenRouter Responses streams can omit `event:` and rely on payload `type`.
        if (($event_type === null || $event_type === '') && isset($decoded['type']) && is_string($decoded['type'])) {
            $event_type = $decoded['type'];
        }
        if ($event_type === null || $event_type === '') {
            $event_type = 'message';
        }

        $set_status = static function (string $type, array $payload = []) use (&$result): void {
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
            $result['status'] = $status;
        };

        if ($event_type === 'error' || isset($decoded['error'])) {
            $result['delta'] = parse_error_logic_for_response_parser($decoded, 500);
            $result['is_error'] = true;
            return $result;
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
                $set_status($event_type, $decoded);
                break;

            case 'response.output_text.delta':
            case 'response.content_part.delta':
                $delta_text = isset($decoded['delta']) ? (string) $decoded['delta'] : '';
                if ($delta_text !== '') {
                    if ($result['delta'] === null) {
                        $result['delta'] = '';
                    }
                    $result['delta'] .= $delta_text;
                }
                break;

            case 'response.refusal.delta':
                $refusal_text = isset($decoded['delta']) ? (string) $decoded['delta'] : '';
                if ($refusal_text !== '') {
                    if ($result['delta'] === null) {
                        $result['delta'] = '';
                    }
                    $result['delta'] .= sprintf(' (%s: %s)', __('Refusal', 'gpt3-ai-content-generator'), $refusal_text);
                    $result['is_warning'] = true;
                }
                break;

            case 'response.done':
            case 'response.completed':
            case 'response.incomplete':
                $result['is_done'] = true;
                if (isset($decoded['response']['usage']) && is_array($decoded['response']['usage'])) {
                    $usage = $decoded['response']['usage'];
                    $result['usage'] = [
                        'input_tokens'  => $usage['input_tokens'] ?? $usage['prompt_tokens'] ?? 0,
                        'output_tokens' => $usage['output_tokens'] ?? $usage['completion_tokens'] ?? 0,
                        'total_tokens'  => $usage['total_tokens'] ?? 0,
                        'provider_raw'  => $usage,
                    ];
                } elseif (isset($decoded['usage']) && is_array($decoded['usage'])) {
                    $usage = $decoded['usage'];
                    $result['usage'] = [
                        'input_tokens'  => $usage['input_tokens'] ?? $usage['prompt_tokens'] ?? 0,
                        'output_tokens' => $usage['output_tokens'] ?? $usage['completion_tokens'] ?? 0,
                        'total_tokens'  => $usage['total_tokens'] ?? 0,
                        'provider_raw'  => $usage,
                    ];
                }
                if ($event_type === 'response.incomplete') {
                    $reason = $decoded['response']['incomplete_details']['reason'] ?? 'unknown';
                    if ($result['delta'] === null) {
                        $result['delta'] = '';
                    }
                    $result['delta'] .= sprintf(' (%s: %s)', __('Incomplete', 'gpt3-ai-content-generator'), $reason);
                    $result['is_warning'] = true;
                }
                break;

            case 'response.failed':
                $error_message = $decoded['response']['error']['message'] ?? __('Response failed', 'gpt3-ai-content-generator');
                if ($result['delta'] === null) {
                    $result['delta'] = '';
                }
                $result['delta'] .= sprintf(' (%s: %s)', __('Error', 'gpt3-ai-content-generator'), $error_message);
                $result['is_error'] = true;
                return $result;

            default:
                // Backward-compat fallback for chat-completions stream shape.
                if (isset($decoded['usage']) && is_array($decoded['usage'])) {
                    $usage = $decoded['usage'];
                    $result['usage'] = [
                        'input_tokens'  => $usage['input_tokens'] ?? $usage['prompt_tokens'] ?? 0,
                        'output_tokens' => $usage['output_tokens'] ?? $usage['completion_tokens'] ?? 0,
                        'total_tokens'  => $usage['total_tokens'] ?? 0,
                        'provider_raw'  => $usage,
                    ];
                }

                if (isset($decoded['choices'][0]['delta']['content'])) {
                    $delta_text = (string) $decoded['choices'][0]['delta']['content'];
                    if ($delta_text !== '') {
                        if ($result['delta'] === null) {
                            $result['delta'] = '';
                        }
                        $result['delta'] .= $delta_text;
                    }
                }

                if (isset($decoded['choices'][0]['finish_reason']) && $decoded['choices'][0]['finish_reason'] === 'content_filter') {
                    if ($result['delta'] === null) {
                        $result['delta'] = '';
                    }
                    $result['delta'] .= sprintf(' (%s)', __('Warning: Content Filtered', 'gpt3-ai-content-generator'));
                    $result['is_warning'] = true;
                }
                break;
        }
    }

    return $result;
}
