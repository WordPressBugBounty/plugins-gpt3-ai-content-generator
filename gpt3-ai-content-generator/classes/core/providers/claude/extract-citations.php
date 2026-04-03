<?php

namespace WPAICG\Core\Providers\Claude\Methods;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalize one Claude citation payload into a stable, frontend-safe structure.
 *
 * @param array<string, mixed> $citation
 * @param array<string, mixed> $extra
 * @return array<string, mixed>|null
 */
function normalize_claude_citation_logic_for_response_parser(array $citation, array $extra = []): ?array
{
    $normalized = [];
    $string_fields = [
        'type',
        'cited_text',
        'document_title',
        'title',
        'website_title',
        'source_title',
        'url',
    ];
    $integer_fields = [
        'document_index',
        'start_char_index',
        'end_char_index',
        'start_page_number',
        'end_page_number',
        'start_block_index',
        'end_block_index',
        'content_block_index',
    ];

    foreach ($string_fields as $field) {
        if (isset($citation[$field]) && is_string($citation[$field]) && trim($citation[$field]) !== '') {
            $normalized[$field] = trim($citation[$field]);
        }
    }

    if (!isset($normalized['url']) && isset($citation['source']) && is_array($citation['source'])) {
        if (isset($citation['source']['url']) && is_string($citation['source']['url']) && trim($citation['source']['url']) !== '') {
            $normalized['url'] = trim($citation['source']['url']);
        }
        if (!isset($normalized['title']) && isset($citation['source']['title']) && is_string($citation['source']['title']) && trim($citation['source']['title']) !== '') {
            $normalized['title'] = trim($citation['source']['title']);
        }
    }

    foreach ($integer_fields as $field) {
        $value = $extra[$field] ?? ($citation[$field] ?? null);
        if ($value === null || $value === '') {
            continue;
        }
        if (is_numeric($value)) {
            $normalized[$field] = (int) $value;
        }
    }

    if (empty($normalized)) {
        return null;
    }

    $has_meaningful_reference = isset($normalized['cited_text'])
        || isset($normalized['url'])
        || isset($normalized['document_title'])
        || isset($normalized['title'])
        || isset($normalized['document_index']);

    return $has_meaningful_reference ? $normalized : null;
}

/**
 * Extract citations from a Claude text content block.
 *
 * @param array<string, mixed> $block
 * @param array<string, mixed> $extra
 * @return array<int, array<string, mixed>>
 */
function extract_claude_citations_from_text_block_logic_for_response_parser(array $block, array $extra = []): array
{
    if (($block['type'] ?? '') !== 'text' || empty($block['citations']) || !is_array($block['citations'])) {
        return [];
    }

    $citations = [];
    foreach ($block['citations'] as $citation) {
        if (!is_array($citation)) {
            continue;
        }
        $normalized = normalize_claude_citation_logic_for_response_parser($citation, $extra);
        if ($normalized !== null) {
            $citations[] = $normalized;
        }
    }

    return $citations;
}

/**
 * Extract citations from a Claude streaming delta payload.
 *
 * @param array<string, mixed> $delta
 * @param array<string, mixed> $extra
 * @return array<int, array<string, mixed>>
 */
function extract_claude_citations_from_delta_logic_for_response_parser(array $delta, array $extra = []): array
{
    $raw_citations = [];

    if (isset($delta['citation']) && is_array($delta['citation'])) {
        $raw_citations[] = $delta['citation'];
    }
    if (isset($delta['citations']) && is_array($delta['citations'])) {
        foreach ($delta['citations'] as $citation) {
            if (is_array($citation)) {
                $raw_citations[] = $citation;
            }
        }
    }

    if (empty($raw_citations)) {
        return [];
    }

    $citations = [];
    foreach ($raw_citations as $citation) {
        $normalized = normalize_claude_citation_logic_for_response_parser($citation, $extra);
        if ($normalized !== null) {
            $citations[] = $normalized;
        }
    }

    return $citations;
}
