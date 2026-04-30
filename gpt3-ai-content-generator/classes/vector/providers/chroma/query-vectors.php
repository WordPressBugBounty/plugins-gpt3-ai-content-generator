<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/query-vectors.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function query_vectors_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $index_name, array $query_vector_param, int $top_k, array $filter = []): array|WP_Error
{
    $collection = resolve_collection_logic($strategyInstance, $index_name);
    if (is_wp_error($collection)) {
        return $collection;
    }

    $collection_id = isset($collection['id']) ? (string) $collection['id'] : '';
    if ($collection_id === '') {
        return new WP_Error('chroma_collection_id_missing', __('Chroma collection ID is missing for query operation.', 'gpt3-ai-content-generator'));
    }

    $vector_values = $query_vector_param['vector'] ?? ($query_vector_param['query'] ?? ($query_vector_param ?: null));
    if (!is_array($vector_values) || empty($vector_values)) {
        return new WP_Error('invalid_chroma_query_vector', __('Invalid query vector provided for Chroma search.', 'gpt3-ai-content-generator'));
    }

    $body = [
        'query_embeddings' => [array_values($vector_values)],
        'n_results'        => max(1, $top_k),
        'include'          => ['documents', 'metadatas', 'distances'],
    ];

    if (!empty($filter)) {
        $filter_handled = false;
        if (isset($filter['where']) && is_array($filter['where'])) {
            $body['where'] = $filter['where'];
            $filter_handled = true;
        }
        if (isset($filter['where_document']) && is_array($filter['where_document'])) {
            $body['where_document'] = $filter['where_document'];
            $filter_handled = true;
        }
        if (isset($filter['ids']) && is_array($filter['ids'])) {
            $body['ids'] = $filter['ids'];
            $filter_handled = true;
        }
        if (!$filter_handled) {
            $body['where'] = $filter;
        }
    }

    $response = _request_logic($strategyInstance, 'POST', collection_records_path_logic($strategyInstance, $collection_id, 'query'), $body);
    if (is_wp_error($response)) {
        return $response;
    }

    $ids = $response['ids'][0] ?? [];
    $distances = $response['distances'][0] ?? [];
    $documents = $response['documents'][0] ?? [];
    $metadatas = $response['metadatas'][0] ?? [];
    $results = [];

    foreach ($ids as $idx => $id) {
        $metadata = isset($metadatas[$idx]) && is_array($metadatas[$idx]) ? $metadatas[$idx] : [];
        $document = $documents[$idx] ?? null;
        if ($document !== null && !isset($metadata['original_content'])) {
            $metadata['original_content'] = (string) $document;
        }

        $distance = $distances[$idx] ?? null;
        $score = normalize_chroma_score_logic($distance);
        if ($score !== null) {
            $metadata['chroma_distance'] = (float) $distance;
        }

        $results[] = [
            'id'         => $id,
            'score'      => $score,
            'distance'   => $distance,
            'metadata'   => $metadata,
            'collection' => $collection['name'] ?? $index_name,
        ];
    }

    return $results;
}
