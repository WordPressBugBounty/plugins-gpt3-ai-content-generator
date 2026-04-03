<?php
// File: classes/core/providers/google/extract-citations.php

namespace WPAICG\Core\Providers\Google\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Convert Gemini grounding metadata into a stable citation list for the shared chat UI.
 *
 * @param array<string, mixed> $grounding_metadata
 * @return array<int, array<string, mixed>>
 */
function extract_google_citations_from_grounding_metadata_logic_for_response_parser(array $grounding_metadata): array {
    $grounding_chunks = isset($grounding_metadata['groundingChunks']) && is_array($grounding_metadata['groundingChunks'])
        ? $grounding_metadata['groundingChunks']
        : [];

    if (empty($grounding_chunks)) {
        return [];
    }

    $support_texts_by_chunk = [];
    $grounding_supports = isset($grounding_metadata['groundingSupports']) && is_array($grounding_metadata['groundingSupports'])
        ? $grounding_metadata['groundingSupports']
        : [];

    foreach ($grounding_supports as $support) {
        if (!is_array($support)) {
            continue;
        }

        $segment = isset($support['segment']) && is_array($support['segment']) ? $support['segment'] : [];
        $segment_text = isset($segment['text']) && is_string($segment['text']) ? trim($segment['text']) : '';
        $chunk_indices = isset($support['groundingChunkIndices']) && is_array($support['groundingChunkIndices'])
            ? $support['groundingChunkIndices']
            : [];

        if ($segment_text === '' || empty($chunk_indices)) {
            continue;
        }

        foreach ($chunk_indices as $chunk_index) {
            if (!is_numeric($chunk_index)) {
                continue;
            }
            $chunk_index = (int) $chunk_index;
            if (!isset($support_texts_by_chunk[$chunk_index])) {
                $support_texts_by_chunk[$chunk_index] = [];
            }
            $support_texts_by_chunk[$chunk_index][] = $segment_text;
        }
    }

    $citations = [];
    foreach ($grounding_chunks as $chunk_index => $chunk) {
        if (!is_array($chunk)) {
            continue;
        }

        $normalized = normalize_google_grounding_chunk_logic_for_response_parser(
            $chunk,
            isset($support_texts_by_chunk[$chunk_index]) && is_array($support_texts_by_chunk[$chunk_index])
                ? $support_texts_by_chunk[$chunk_index]
                : [],
            is_numeric($chunk_index) ? (int) $chunk_index : null
        );

        if ($normalized !== null) {
            $citations[] = $normalized;
        }
    }

    return dedupe_google_citations_logic_for_response_parser($citations);
}

/**
 * Normalize one Gemini grounding chunk into the shared citation shape.
 *
 * @param array<string, mixed> $chunk
 * @param array<int, string> $support_texts
 * @param int|null $chunk_index
 * @return array<string, mixed>|null
 */
function normalize_google_grounding_chunk_logic_for_response_parser(array $chunk, array $support_texts = [], ?int $chunk_index = null): ?array {
    $source = extract_google_grounding_source_logic_for_response_parser($chunk);
    $title = isset($source['title']) && is_string($source['title']) ? trim($source['title']) : '';
    $url = isset($source['url']) && is_string($source['url']) ? trim($source['url']) : '';

    $unique_support_texts = [];
    foreach ($support_texts as $support_text) {
        if (!is_string($support_text)) {
            continue;
        }

        $support_text = trim($support_text);
        if ($support_text === '' || in_array($support_text, $unique_support_texts, true)) {
            continue;
        }

        $unique_support_texts[] = $support_text;
    }

    $cited_text = trim(implode(' ', $unique_support_texts));

    if ($title === '' && $url === '' && $cited_text === '') {
        return null;
    }

    $normalized = [
        'type' => 'url_citation',
    ];

    if ($url !== '') {
        $normalized['url'] = $url;
    }
    if ($title !== '') {
        $normalized['title'] = $title;
        $normalized['source_title'] = $title;
    }
    if ($cited_text !== '') {
        $normalized['cited_text'] = $cited_text;
    }
    if ($chunk_index !== null) {
        $normalized['document_index'] = $chunk_index;
    }

    return $normalized;
}

/**
 * Extract the best available title/url pair from a grounding chunk.
 *
 * @param array<string, mixed> $chunk
 * @return array<string, string>
 */
function extract_google_grounding_source_logic_for_response_parser(array $chunk): array {
    $candidates = [];

    foreach (['web', 'retrievedContext', 'source', 'document'] as $key) {
        if (isset($chunk[$key]) && is_array($chunk[$key])) {
            $candidates[] = $chunk[$key];
        }
    }

    $candidates[] = $chunk;

    foreach ($candidates as $candidate) {
        $url = '';
        foreach (['uri', 'url', 'link'] as $field) {
            if (isset($candidate[$field]) && is_string($candidate[$field]) && trim($candidate[$field]) !== '') {
                $url = trim($candidate[$field]);
                break;
            }
        }

        $title = '';
        foreach (['title', 'source_title', 'website_title', 'name'] as $field) {
            if (isset($candidate[$field]) && is_string($candidate[$field]) && trim($candidate[$field]) !== '') {
                $title = trim($candidate[$field]);
                break;
            }
        }

        if ($url !== '' || $title !== '') {
            return [
                'url' => $url,
                'title' => $title,
            ];
        }
    }

    return [
        'url' => '',
        'title' => '',
    ];
}

/**
 * Deduplicate citations while preserving order.
 *
 * @param array<int, array<string, mixed>> $citations
 * @return array<int, array<string, mixed>>
 */
function dedupe_google_citations_logic_for_response_parser(array $citations): array {
    $deduped = [];
    $seen = [];

    foreach ($citations as $citation) {
        if (!is_array($citation) || empty($citation)) {
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
