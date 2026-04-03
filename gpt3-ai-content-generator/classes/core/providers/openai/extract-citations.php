<?php
// File: classes/core/providers/openai/extract-citations.php

namespace WPAICG\Core\Providers\OpenAI\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Extract citations from a completed OpenAI Responses payload.
 *
 * @param array<string, mixed> $decoded_response
 * @return array<int, array<string, mixed>>
 */
function extract_openai_citations_from_response_logic_for_response_parser(array $decoded_response): array {
    $output = $decoded_response['output'] ?? null;
    if (!is_array($output)) {
        return [];
    }

    $annotation_citations = extract_openai_annotation_citations_from_output_logic_for_response_parser($output);
    if (!empty($annotation_citations)) {
        return dedupe_openai_citations_logic_for_response_parser($annotation_citations);
    }

    return dedupe_openai_citations_logic_for_response_parser(
        extract_openai_source_citations_from_output_logic_for_response_parser($output)
    );
}

/**
 * Extract citations from a streamed OpenAI event payload.
 *
 * @param array<string, mixed> $payload
 * @return array<int, array<string, mixed>>
 */
function extract_openai_citations_from_event_payload_logic_for_response_parser(array $payload, bool $allow_source_fallback = false): array {
    $annotation_citations = [];

    if (isset($payload['part']) && is_array($payload['part'])) {
        $annotation_citations = array_merge(
            $annotation_citations,
            extract_openai_citations_from_output_text_block_logic_for_response_parser($payload['part'])
        );
    }

    if (isset($payload['item']) && is_array($payload['item'])) {
        $annotation_citations = array_merge(
            $annotation_citations,
            extract_openai_annotation_citations_from_output_item_logic_for_response_parser($payload['item'])
        );
    }

    if (isset($payload['output']) && is_array($payload['output'])) {
        $annotation_citations = array_merge(
            $annotation_citations,
            extract_openai_annotation_citations_from_output_logic_for_response_parser($payload['output'])
        );
    }

    if (isset($payload['response']) && is_array($payload['response'])) {
        $response = $payload['response'];

        if (isset($response['output']) && is_array($response['output'])) {
            $annotation_citations = array_merge(
                $annotation_citations,
                extract_openai_annotation_citations_from_output_logic_for_response_parser($response['output'])
            );
        }

        if (isset($response['annotations']) && is_array($response['annotations'])) {
            $annotation_citations = array_merge(
                $annotation_citations,
                extract_openai_citations_from_annotations_logic_for_response_parser(
                    $response['annotations'],
                    isset($response['text']) && is_string($response['text']) ? $response['text'] : ''
                )
            );
        }
    }

    if (!empty($annotation_citations)) {
        return dedupe_openai_citations_logic_for_response_parser($annotation_citations);
    }

    if (!$allow_source_fallback) {
        return [];
    }

    $source_citations = [];

    if (isset($payload['item']) && is_array($payload['item'])) {
        $source_citations = array_merge(
            $source_citations,
            extract_openai_source_citations_from_output_item_logic_for_response_parser($payload['item'])
        );
    }

    if (isset($payload['output']) && is_array($payload['output'])) {
        $source_citations = array_merge(
            $source_citations,
            extract_openai_source_citations_from_output_logic_for_response_parser($payload['output'])
        );
    }

    if (isset($payload['response']['output']) && is_array($payload['response']['output'])) {
        $source_citations = array_merge(
            $source_citations,
            extract_openai_source_citations_from_output_logic_for_response_parser($payload['response']['output'])
        );
    }

    return dedupe_openai_citations_logic_for_response_parser($source_citations);
}

/**
 * Extract annotation-based citations from a list of output items.
 *
 * @param array<int, mixed> $output
 * @return array<int, array<string, mixed>>
 */
function extract_openai_annotation_citations_from_output_logic_for_response_parser(array $output): array {
    $citations = [];

    foreach ($output as $output_item) {
        if (!is_array($output_item)) {
            continue;
        }

        $citations = array_merge(
            $citations,
            extract_openai_annotation_citations_from_output_item_logic_for_response_parser($output_item)
        );
    }

    return $citations;
}

/**
 * Extract annotation-based citations from one OpenAI output item.
 *
 * @param array<string, mixed> $output_item
 * @return array<int, array<string, mixed>>
 */
function extract_openai_annotation_citations_from_output_item_logic_for_response_parser(array $output_item): array {
    $citations = [];
    $item_type = isset($output_item['type']) && is_string($output_item['type']) ? $output_item['type'] : '';

    if ($item_type === 'message' && !empty($output_item['content']) && is_array($output_item['content'])) {
        foreach ($output_item['content'] as $content_part) {
            if (!is_array($content_part)) {
                continue;
            }

            $citations = array_merge(
                $citations,
                extract_openai_citations_from_output_text_block_logic_for_response_parser($content_part)
            );
        }
    }

    return $citations;
}

/**
 * Extract fallback source citations from a list of output items.
 *
 * @param array<int, mixed> $output
 * @return array<int, array<string, mixed>>
 */
function extract_openai_source_citations_from_output_logic_for_response_parser(array $output): array {
    $citations = [];

    foreach ($output as $output_item) {
        if (!is_array($output_item)) {
            continue;
        }

        $citations = array_merge(
            $citations,
            extract_openai_source_citations_from_output_item_logic_for_response_parser($output_item)
        );
    }

    return $citations;
}

/**
 * Extract fallback source citations from a single OpenAI output item.
 *
 * @param array<string, mixed> $output_item
 * @return array<int, array<string, mixed>>
 */
function extract_openai_source_citations_from_output_item_logic_for_response_parser(array $output_item): array {
    $item_type = isset($output_item['type']) && is_string($output_item['type']) ? $output_item['type'] : '';

    if ($item_type !== 'web_search_call') {
        return [];
    }

    return extract_openai_citations_from_web_search_call_logic_for_response_parser($output_item);
}

/**
 * Extract citations from one output_text content block.
 *
 * @param array<string, mixed> $content_part
 * @return array<int, array<string, mixed>>
 */
function extract_openai_citations_from_output_text_block_logic_for_response_parser(array $content_part): array {
    if (($content_part['type'] ?? '') !== 'output_text' || empty($content_part['annotations']) || !is_array($content_part['annotations'])) {
        return [];
    }

    $full_text = isset($content_part['text']) && is_string($content_part['text']) ? $content_part['text'] : '';

    return extract_openai_citations_from_annotations_logic_for_response_parser($content_part['annotations'], $full_text);
}

/**
 * Extract citations from an annotations list.
 *
 * @param array<int, mixed> $annotations
 * @param string $full_text
 * @return array<int, array<string, mixed>>
 */
function extract_openai_citations_from_annotations_logic_for_response_parser(array $annotations, string $full_text = ''): array {
    $citations = [];

    foreach ($annotations as $annotation) {
        if (!is_array($annotation)) {
            continue;
        }

        $normalized = normalize_openai_citation_logic_for_response_parser($annotation, $full_text);
        if ($normalized !== null) {
            $citations[] = $normalized;
        }
    }

    return $citations;
}

/**
 * Normalize one OpenAI annotation/source into a stable frontend-safe citation structure.
 *
 * @param array<string, mixed> $citation
 * @param string $full_text
 * @return array<string, mixed>|null
 */
function normalize_openai_citation_logic_for_response_parser(array $citation, string $full_text = ''): ?array {
    $type = isset($citation['type']) && is_string($citation['type']) ? trim($citation['type']) : '';
    $normalized = [];

    if ($type !== '') {
        $normalized['type'] = $type === 'url_citation' ? 'char_location' : $type;
    }

    if (isset($citation['url']) && is_string($citation['url']) && trim($citation['url']) !== '') {
        $normalized['url'] = trim($citation['url']);
    } elseif (isset($citation['uri']) && is_string($citation['uri']) && trim($citation['uri']) !== '') {
        $normalized['url'] = trim($citation['uri']);
    }

    if (isset($citation['title']) && is_string($citation['title']) && trim($citation['title']) !== '') {
        $normalized['title'] = trim($citation['title']);
        $normalized['source_title'] = trim($citation['title']);
    }

    if (isset($citation['filename']) && is_string($citation['filename']) && trim($citation['filename']) !== '') {
        $normalized['document_title'] = trim($citation['filename']);
        if (!isset($normalized['title'])) {
            $normalized['title'] = trim($citation['filename']);
        }
    }

    $start_index = null;
    if (isset($citation['start_index']) && is_numeric($citation['start_index'])) {
        $start_index = (int) $citation['start_index'];
    } elseif (isset($citation['start_char_index']) && is_numeric($citation['start_char_index'])) {
        $start_index = (int) $citation['start_char_index'];
    }

    $end_index = null;
    if (isset($citation['end_index']) && is_numeric($citation['end_index'])) {
        $end_index = (int) $citation['end_index'];
    } elseif (isset($citation['end_char_index']) && is_numeric($citation['end_char_index'])) {
        $end_index = (int) $citation['end_char_index'];
    }

    if ($start_index !== null) {
        $normalized['start_char_index'] = $start_index;
    }
    if ($end_index !== null) {
        $normalized['end_char_index'] = $end_index;
    }

    if (isset($citation['index']) && is_numeric($citation['index'])) {
        $normalized['document_index'] = (int) $citation['index'];
    }

    $cited_text = '';
    foreach (['text', 'quote', 'snippet', 'excerpt'] as $field) {
        if (isset($citation[$field]) && is_string($citation[$field]) && trim($citation[$field]) !== '') {
            $cited_text = trim($citation[$field]);
            break;
        }
    }
    if ($cited_text === '' && $full_text !== '' && $start_index !== null && $end_index !== null && $end_index > $start_index) {
        $cited_text = extract_openai_text_slice_logic_for_response_parser($full_text, $start_index, $end_index);
    }
    if ($cited_text !== '') {
        $normalized['cited_text'] = $cited_text;
    }

    $has_reference = isset($normalized['url'])
        || isset($normalized['title'])
        || isset($normalized['document_title'])
        || isset($normalized['cited_text'])
        || isset($normalized['document_index']);

    return $has_reference ? $normalized : null;
}

/**
 * Extract web search sources from a completed web_search_call item when include data is available.
 *
 * @param array<string, mixed> $web_search_call
 * @return array<int, array<string, mixed>>
 */
function extract_openai_citations_from_web_search_call_logic_for_response_parser(array $web_search_call): array {
    $action = $web_search_call['action'] ?? null;
    if (!is_array($action) || empty($action['sources']) || !is_array($action['sources'])) {
        return [];
    }

    $citations = [];
    foreach ($action['sources'] as $source) {
        if (!is_array($source)) {
            continue;
        }

        $normalized = normalize_openai_source_logic_for_response_parser($source);
        if ($normalized !== null) {
            $citations[] = $normalized;
        }
    }

    return $citations;
}

/**
 * Normalize a web search source include entry.
 *
 * @param array<string, mixed> $source
 * @return array<string, mixed>|null
 */
function normalize_openai_source_logic_for_response_parser(array $source): ?array {
    $source_payload = isset($source['source']) && is_array($source['source']) ? $source['source'] : $source;

    $url = null;
    foreach (['url', 'uri', 'link'] as $field) {
        if (isset($source_payload[$field]) && is_string($source_payload[$field]) && trim($source_payload[$field]) !== '') {
            $url = trim($source_payload[$field]);
            break;
        }
    }

    $title = null;
    foreach (['title', 'source_title', 'website_title', 'name'] as $field) {
        if (isset($source_payload[$field]) && is_string($source_payload[$field]) && trim($source_payload[$field]) !== '') {
            $title = trim($source_payload[$field]);
            break;
        }
    }

    $snippet = null;
    foreach (['snippet', 'excerpt', 'text', 'summary'] as $field) {
        if (isset($source_payload[$field]) && is_string($source_payload[$field]) && trim($source_payload[$field]) !== '') {
            $snippet = trim($source_payload[$field]);
            break;
        }
    }

    if ($url === null && $title === null && $snippet === null) {
        return null;
    }

    $normalized = ['type' => 'url_citation'];
    if ($url !== null) {
        $normalized['url'] = $url;
    }
    if ($title !== null) {
        $normalized['title'] = $title;
        $normalized['source_title'] = $title;
    }
    if ($snippet !== null) {
        $normalized['cited_text'] = $snippet;
    }

    return $normalized;
}

/**
 * Return a best-effort UTF-8-safe text slice from a response body.
 *
 * @param string $text
 * @param int $start_index
 * @param int $end_index
 * @return string
 */
function extract_openai_text_slice_logic_for_response_parser(string $text, int $start_index, int $end_index): string {
    if ($end_index <= $start_index) {
        return '';
    }

    $length = $end_index - $start_index;

    if (function_exists('mb_substr')) {
        return trim((string) mb_substr($text, $start_index, $length, 'UTF-8'));
    }

    return trim(substr($text, $start_index, $length));
}

/**
 * Deduplicate citations while preserving order.
 *
 * @param array<int, array<string, mixed>> $citations
 * @return array<int, array<string, mixed>>
 */
function dedupe_openai_citations_logic_for_response_parser(array $citations): array {
    $deduped = [];
    $seen = [];

    foreach ($citations as $citation) {
        if (!is_array($citation) || empty($citation)) {
            continue;
        }

        $reference_key = build_openai_citation_reference_key_logic_for_response_parser($citation);
        if ($reference_key !== '') {
            if (isset($seen[$reference_key])) {
                $existing_index = $seen[$reference_key];
                $existing_citation = $deduped[$existing_index];

                if (score_openai_citation_logic_for_response_parser($citation) > score_openai_citation_logic_for_response_parser($existing_citation)) {
                    $deduped[$existing_index] = merge_openai_citation_entries_logic_for_response_parser($existing_citation, $citation);
                } else {
                    $deduped[$existing_index] = merge_openai_citation_entries_logic_for_response_parser($citation, $existing_citation);
                }
                continue;
            }

            $seen[$reference_key] = count($deduped);
            $deduped[] = $citation;
            continue;
        }

        $encoded = wp_json_encode($citation);
        if (!is_string($encoded) || isset($seen[$encoded])) {
            continue;
        }
        $seen[$encoded] = true;
        $deduped[] = $citation;
    }

    return $deduped;
}

/**
 * Build a loose reference key so multiple annotations for the same source collapse into one source row.
 *
 * @param array<string, mixed> $citation
 * @return string
 */
function build_openai_citation_reference_key_logic_for_response_parser(array $citation): string {
    $parts = [
        isset($citation['url']) && is_string($citation['url']) ? trim($citation['url']) : '',
        isset($citation['document_title']) && is_string($citation['document_title']) ? trim($citation['document_title']) : '',
        isset($citation['title']) && is_string($citation['title']) ? trim($citation['title']) : '',
        isset($citation['source_title']) && is_string($citation['source_title']) ? trim($citation['source_title']) : '',
    ];

    $parts = array_values(array_filter($parts, static function ($value) {
        return $value !== '';
    }));

    return empty($parts) ? '' : implode('|', $parts);
}

/**
 * Prefer citations with excerpts/locations over bare source-only rows.
 *
 * @param array<string, mixed> $citation
 * @return int
 */
function score_openai_citation_logic_for_response_parser(array $citation): int {
    $score = 0;

    if (!empty($citation['url'])) {
        $score += 2;
    }
    if (!empty($citation['title']) || !empty($citation['document_title']) || !empty($citation['source_title'])) {
        $score += 2;
    }
    if (!empty($citation['cited_text'])) {
        $score += 4;
    }
    if (isset($citation['start_char_index']) || isset($citation['end_char_index'])) {
        $score += 2;
    }
    if (isset($citation['document_index'])) {
        $score += 1;
    }

    return $score;
}

/**
 * Merge two source rows without dropping richer metadata already captured.
 *
 * @param array<string, mixed> $base
 * @param array<string, mixed> $preferred
 * @return array<string, mixed>
 */
function merge_openai_citation_entries_logic_for_response_parser(array $base, array $preferred): array {
    return array_merge($base, $preferred);
}
