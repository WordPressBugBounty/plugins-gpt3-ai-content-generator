<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsResponseParser
{
    /**
     * Parse a completed interaction without assuming the output modality.
     *
     * @param array<string, mixed> $response Decoded Interactions response.
     * @return array<string, mixed>|WP_Error
     */
    public static function parse(array $response)
    {
        if (isset($response['error']) || self::contains_array_wrapped_error($response)) {
            return GoogleInteractionsErrorParser::to_wp_error($response, 500);
        }

        $status = isset($response['status']) && is_string($response['status'])
            ? $response['status']
            : '';
        if (in_array($status, ['cancelled', 'failed'], true)) {
            return GoogleInteractionsErrorParser::to_wp_error($response, 500);
        }

        $steps = isset($response['steps']) && is_array($response['steps']) ? $response['steps'] : [];
        $output_items = [];
        $tool_steps = [];
        $text = '';
        $citations = [];

        foreach ($steps as $step) {
            if (!is_array($step)) {
                continue;
            }

            $step_type = isset($step['type']) && is_string($step['type']) ? $step['type'] : '';
            if ($step_type !== 'model_output') {
                if ($step_type !== '' && $step_type !== 'thought') {
                    $tool_steps[] = $step;
                }
                continue;
            }

            $content = isset($step['content']) && is_array($step['content']) ? $step['content'] : [];
            foreach ($content as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $output_items[] = $item;
                if (($item['type'] ?? '') === 'text' && isset($item['text']) && is_string($item['text'])) {
                    $text .= $item['text'];
                }

                $citations = self::merge_citations($citations, self::extract_citations($item));
            }
        }

        return [
            'content' => trim($text),
            'output_items' => $output_items,
            'tool_steps' => $tool_steps,
            'file_search_steps' => self::extract_file_search_steps($tool_steps),
            'citations' => $citations,
            'grounding_metadata' => self::extract_grounding_metadata($tool_steps),
            'usage' => self::normalize_usage(isset($response['usage']) && is_array($response['usage']) ? $response['usage'] : []),
            'interaction_id' => isset($response['id']) && is_string($response['id']) && $response['id'] !== '' ? $response['id'] : null,
            'status' => $status,
            'model' => isset($response['model']) && is_string($response['model']) ? $response['model'] : '',
        ];
    }

    /**
     * Parse an interaction that must contain text output.
     *
     * @param array<string, mixed> $response Decoded Interactions response.
     * @return array<string, mixed>|WP_Error
     */
    public static function parse_text(array $response)
    {
        $parsed = self::parse($response);
        if (is_wp_error($parsed)) {
            return $parsed;
        }

        return self::require_text_result($parsed);
    }

    /**
     * Validate an already parsed interaction as a text result.
     *
     * @param array<string, mixed> $parsed Parsed interaction.
     * @return array<string, mixed>|WP_Error
     */
    public static function require_text_result(array $parsed)
    {
        if (($parsed['content'] ?? '') === '') {
            return new WP_Error(
                'google_interactions_missing_text_output',
                __('Google completed the interaction without returning text.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }

        return $parsed;
    }

    /**
     * Validate an already parsed interaction as an image result.
     *
     * @param array<string, mixed> $parsed Parsed interaction.
     * @return array<string, mixed>|WP_Error
     */
    public static function require_image_result(array $parsed)
    {
        return self::require_media_result($parsed, 'image');
    }

    /**
     * Validate an already parsed interaction as an audio result.
     *
     * @param array<string, mixed> $parsed Parsed interaction.
     * @return array<string, mixed>|WP_Error
     */
    public static function require_audio_result(array $parsed)
    {
        return self::require_media_result($parsed, 'audio');
    }

    /**
     * Normalize Interactions usage into the shared AI Puffer token shape.
     *
     * @param array<string, mixed> $usage Raw Google usage.
     * @return array<string, mixed>|null
     */
    public static function normalize_usage(array $usage): ?array
    {
        if (empty($usage)) {
            return null;
        }

        return [
            'input_tokens' => (int) ($usage['total_input_tokens'] ?? 0),
            'output_tokens' => (int) ($usage['total_output_tokens'] ?? 0),
            'total_tokens' => (int) ($usage['total_tokens'] ?? 0),
            'cached_tokens' => (int) ($usage['total_cached_tokens'] ?? 0),
            'thought_tokens' => (int) ($usage['total_thought_tokens'] ?? 0),
            'tool_use_tokens' => (int) ($usage['total_tool_use_tokens'] ?? 0),
            'provider_raw' => $usage,
        ];
    }

    /**
     * Extract and normalize URL citations from one text output or delta.
     *
     * @param array<string, mixed> $content_item Output item or stream delta.
     * @return array<int, array<string, mixed>>
     */
    public static function extract_citations(array $content_item): array
    {
        $annotations = isset($content_item['annotations']) && is_array($content_item['annotations'])
            ? $content_item['annotations']
            : [];
        $citations = [];

        foreach ($annotations as $annotation) {
            if (!is_array($annotation)) {
                continue;
            }

            $annotation_type = isset($annotation['type']) && is_string($annotation['type'])
                ? $annotation['type']
                : '';
            if ($annotation_type === 'file_citation') {
                $citation = self::normalize_file_citation($annotation);
                if (!empty($citation)) {
                    $citations[] = $citation;
                }
                continue;
            }
            if ($annotation_type !== 'url_citation') {
                continue;
            }

            $url = '';
            foreach (['url', 'uri'] as $url_key) {
                if (isset($annotation[$url_key]) && is_string($annotation[$url_key]) && trim($annotation[$url_key]) !== '') {
                    $url = trim($annotation[$url_key]);
                    break;
                }
            }
            if ($url === '') {
                continue;
            }

            $citation = [
                'type' => 'url_citation',
                'url' => esc_url_raw($url),
            ];
            if ($citation['url'] === '') {
                continue;
            }
            if (isset($annotation['title']) && is_string($annotation['title']) && trim($annotation['title']) !== '') {
                $citation['title'] = sanitize_text_field($annotation['title']);
                $citation['source_title'] = $citation['title'];
            }
            foreach (['start_index', 'end_index'] as $index_key) {
                if (isset($annotation[$index_key]) && is_numeric($annotation[$index_key])) {
                    $citation[$index_key] = (int) $annotation[$index_key];
                }
            }

            $citations[] = $citation;
        }

        return self::merge_citations([], $citations);
    }

    /**
     * @param array<int, array<string, mixed>> $tool_steps
     * @return array<int, array<string, mixed>>
     */
    public static function extract_file_search_steps(array $tool_steps): array
    {
        return array_values(array_filter(
            $tool_steps,
            static function ($step): bool {
                return is_array($step)
                    && in_array(($step['type'] ?? ''), ['file_search_call', 'file_search_result'], true);
            }
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $existing Existing citations.
     * @param array<int, array<string, mixed>> $incoming Incoming citations.
     * @return array<int, array<string, mixed>>
     */
    public static function merge_citations(array $existing, array $incoming): array
    {
        $merged = [];
        $seen = [];

        foreach (array_merge($existing, $incoming) as $citation) {
            if (!is_array($citation) || empty($citation)) {
                continue;
            }

            $key = wp_json_encode($citation);
            if (!is_string($key) || isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $merged[] = $citation;
        }

        return $merged;
    }

    /**
     * Preserve Google's required Search Suggestions rendering payload in the
     * existing normalized grounding shape used by the chat frontend.
     *
     * @param array<int, array<string, mixed>> $tool_steps
     * @return array<string, mixed>|null
     */
    public static function extract_grounding_metadata(array $tool_steps): ?array
    {
        foreach ($tool_steps as $step) {
            if (!is_array($step) || ($step['type'] ?? '') !== 'google_search_result') {
                continue;
            }

            $results = isset($step['result']) && is_array($step['result']) ? $step['result'] : [];
            foreach ($results as $result) {
                $rendered_content = is_array($result) && isset($result['search_suggestions'])
                    && is_string($result['search_suggestions'])
                    ? trim($result['search_suggestions'])
                    : '';
                if ($rendered_content !== '') {
                    return [
                        'searchEntryPoint' => [
                            'renderedContent' => $rendered_content,
                        ],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $annotation
     * @return array<string, mixed>
     */
    private static function normalize_file_citation(array $annotation): array
    {
        $file_name = isset($annotation['file_name']) && is_string($annotation['file_name'])
            ? sanitize_text_field($annotation['file_name'])
            : '';
        $document_uri = isset($annotation['document_uri']) && is_string($annotation['document_uri'])
            ? sanitize_text_field($annotation['document_uri'])
            : '';
        $source = isset($annotation['source']) && is_string($annotation['source'])
            ? sanitize_textarea_field($annotation['source'])
            : '';
        if ($file_name === '' && $document_uri === '' && $source === '') {
            return [];
        }

        $citation = ['type' => 'file_citation'];
        if ($file_name !== '') {
            $citation['file_name'] = $file_name;
            $citation['title'] = $file_name;
            $citation['source_title'] = $file_name;
        }
        if ($document_uri !== '') {
            $citation['document_uri'] = $document_uri;
        }
        if ($source !== '') {
            $citation['source'] = $source;
        }
        if (isset($annotation['media_id']) && is_string($annotation['media_id'])) {
            $citation['media_id'] = sanitize_text_field($annotation['media_id']);
        }
        foreach (['start_index', 'end_index', 'page_number'] as $index_key) {
            if (isset($annotation[$index_key]) && is_numeric($annotation[$index_key])) {
                $citation[$index_key] = (int) $annotation[$index_key];
            }
        }
        if (isset($annotation['custom_metadata']) && is_array($annotation['custom_metadata'])) {
            $citation['custom_metadata'] = self::sanitize_citation_metadata($annotation['custom_metadata']);
        }

        return $citation;
    }

    /**
     * @param array<mixed> $metadata
     * @return array<mixed>
     */
    private static function sanitize_citation_metadata(array $metadata, int $depth = 0): array
    {
        if ($depth >= 5) {
            return [];
        }
        $sanitized = [];
        foreach (array_slice($metadata, 0, 20, true) as $key => $value) {
            $safe_key = is_string($key) ? sanitize_key($key) : $key;
            if (is_array($value)) {
                $sanitized[$safe_key] = self::sanitize_citation_metadata($value, $depth + 1);
            } elseif (is_bool($value) || is_int($value) || is_float($value)) {
                $sanitized[$safe_key] = $value;
            } elseif (is_string($value)) {
                $sanitized[$safe_key] = sanitize_text_field($value);
            }
        }
        return $sanitized;
    }

    /**
     * @param array<mixed> $response
     */
    private static function contains_array_wrapped_error(array $response): bool
    {
        foreach ($response as $item) {
            if (is_array($item) && isset($item['error'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $parsed Parsed interaction.
     * @return array<string, mixed>|WP_Error
     */
    private static function require_media_result(array $parsed, string $type)
    {
        $items = isset($parsed['output_items']) && is_array($parsed['output_items'])
            ? $parsed['output_items']
            : [];
        $media = array_values(array_filter(
            $items,
            static function ($item) use ($type): bool {
                return is_array($item)
                    && ($item['type'] ?? '') === $type
                    && isset($item['data'])
                    && is_string($item['data'])
                    && trim($item['data']) !== '';
            }
        ));
        if (empty($media)) {
            return new WP_Error(
                'google_interactions_missing_' . $type . '_output',
                sprintf(
                    /* translators: %s: Expected Google output type, such as image or audio. */
                    __('Google completed the interaction without returning %s output.', 'gpt3-ai-content-generator'),
                    $type
                ),
                ['status' => 502, 'status_code' => 502]
            );
        }

        $parsed[$type . '_outputs'] = $media;
        return $parsed;
    }
}
