<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/collection-helpers.php

namespace WPAICG\Vector\Providers\Chroma\Methods;

use WPAICG\Vector\Providers\AIPKit_Vector_Chroma_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

function collection_base_path_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance): string
{
    return '/api/v2/tenants/' . rawurlencode($strategyInstance->get_tenant()) .
        '/databases/' . rawurlencode($strategyInstance->get_database()) .
        '/collections';
}

function collection_records_path_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $collection_id, string $operation): string
{
    return collection_base_path_logic($strategyInstance) . '/' . rawurlencode($collection_id) . '/' . ltrim($operation, '/');
}

function resolve_collection_logic(AIPKit_Vector_Chroma_Strategy $strategyInstance, string $collection_name_or_id): array|WP_Error
{
    $target = trim($collection_name_or_id);
    if ($target === '') {
        return new WP_Error('chroma_collection_name_missing', __('Chroma collection name is required.', 'gpt3-ai-content-generator'), ['status' => 400]);
    }

    $collections = list_indexes_logic($strategyInstance, null, null, null, null);
    if (is_wp_error($collections)) {
        return $collections;
    }

    foreach ($collections as $collection) {
        $collection_id = isset($collection['id']) ? (string) $collection['id'] : '';
        $collection_name = isset($collection['name']) ? (string) $collection['name'] : '';
        if ($collection_id === $target || $collection_name === $target) {
            return $collection;
        }
    }

    /* translators: %s: Chroma collection name. */
    return new WP_Error('chroma_collection_not_found', sprintf(__('Chroma collection "%s" was not found.', 'gpt3-ai-content-generator'), esc_html($target)), ['status' => 404]);
}

function flatten_metadata_logic(array $metadata, string $prefix = ''): array
{
    $flat = [];
    foreach ($metadata as $key => $value) {
        $key = trim((string) $key);
        if ($key === '') {
            continue;
        }
        $flat_key = $prefix === '' ? $key : $prefix . '_' . $key;

        if ($value === null) {
            continue;
        }
        if (is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            $flat[$flat_key] = $value;
            continue;
        }
        if (is_array($value)) {
            $all_scalar = true;
            foreach ($value as $item) {
                if (!is_bool($item) && !is_int($item) && !is_float($item) && !is_string($item)) {
                    $all_scalar = false;
                    break;
                }
            }
            if ($all_scalar) {
                $encoded = wp_json_encode(array_values($value));
                if (is_string($encoded)) {
                    $flat[$flat_key] = $encoded;
                }
            } else {
                $flat = array_merge($flat, flatten_metadata_logic($value, $flat_key));
            }
            continue;
        }

        $encoded = wp_json_encode($value);
        if (is_string($encoded)) {
            $flat[$flat_key] = $encoded;
        }
    }

    return $flat;
}

function prepare_vectors_for_chroma_logic(array $vectors_data): array|WP_Error
{
    if (isset($vectors_data['ids'], $vectors_data['embeddings']) && is_array($vectors_data['ids']) && is_array($vectors_data['embeddings'])) {
        return validate_chroma_columnar_payload_logic($vectors_data);
    }

    $vectors_list = $vectors_data['vectors'] ?? ($vectors_data['points'] ?? $vectors_data);
    if (!is_array($vectors_list)) {
        return new WP_Error('chroma_upsert_invalid_vectors', __('Invalid vectors payload for Chroma upsert.', 'gpt3-ai-content-generator'));
    }

    $ids = [];
    $embeddings = [];
    $documents = [];
    $metadatas = [];
    $uris = [];
    $has_uri = false;

    foreach ($vectors_list as $item) {
        if (!is_array($item)) {
            continue;
        }

        $id = isset($item['id']) && (string) $item['id'] !== '' ? (string) $item['id'] : wp_generate_uuid4();
        $embedding = $item['values'] ?? ($item['vector'] ?? ($item['embedding'] ?? null));
        if (!is_array($embedding) || empty($embedding)) {
            return new WP_Error('chroma_vector_missing_embedding', __('Each Chroma vector must include an embedding array.', 'gpt3-ai-content-generator'));
        }

        $raw_metadata = [];
        if (isset($item['metadata']) && is_array($item['metadata'])) {
            $raw_metadata = $item['metadata'];
        } elseif (isset($item['payload']) && is_array($item['payload'])) {
            $raw_metadata = $item['payload'];
        }
        $metadata = flatten_metadata_logic($raw_metadata);

        $document = $item['document'] ?? ($item['content'] ?? ($item['text'] ?? null));
        if ($document === null) {
            $document = $metadata['original_content'] ?? ($metadata['text_content'] ?? null);
        }

        $ids[] = $id;
        $embeddings[] = array_values($embedding);
        $documents[] = $document !== null ? (string) $document : null;
        $metadatas[] = !empty($metadata) ? $metadata : null;

        if (isset($item['uri']) && (string) $item['uri'] !== '') {
            $uris[] = (string) $item['uri'];
            $has_uri = true;
        } else {
            $uris[] = null;
        }
    }

    $payload = [
        'ids'        => $ids,
        'embeddings' => $embeddings,
        'documents'  => $documents,
        'metadatas'  => $metadatas,
    ];
    if ($has_uri) {
        $payload['uris'] = $uris;
    }

    return validate_chroma_columnar_payload_logic($payload);
}

function validate_chroma_columnar_payload_logic(array $payload): array|WP_Error
{
    $ids = $payload['ids'] ?? [];
    $embeddings = $payload['embeddings'] ?? [];

    if (empty($ids) || empty($embeddings) || count($ids) !== count($embeddings)) {
        return new WP_Error('chroma_columnar_payload_mismatch', __('Chroma IDs and embeddings must be non-empty arrays with matching lengths.', 'gpt3-ai-content-generator'));
    }

    $expected_dim = null;
    foreach ($embeddings as $idx => $embedding) {
        if (!is_array($embedding) || empty($embedding)) {
            return new WP_Error('chroma_embedding_invalid', __('Each Chroma embedding must be a non-empty array.', 'gpt3-ai-content-generator'));
        }
        $payload['embeddings'][$idx] = array_values($embedding);
        $dim = count($embedding);
        if ($expected_dim === null) {
            $expected_dim = $dim;
        } elseif ($dim !== $expected_dim) {
            return new WP_Error('chroma_vector_dimension_inconsistent', __('Vectors have inconsistent dimensions in the Chroma upsert payload.', 'gpt3-ai-content-generator'));
        }
    }

    if (isset($payload['metadatas']) && is_array($payload['metadatas'])) {
        foreach ($payload['metadatas'] as $idx => $metadata) {
            $payload['metadatas'][$idx] = is_array($metadata) ? flatten_metadata_logic($metadata) : null;
        }
    }

    foreach (['documents', 'metadatas', 'uris'] as $optional_key) {
        if (isset($payload[$optional_key]) && is_array($payload[$optional_key]) && count($payload[$optional_key]) !== count($ids)) {
            /* translators: %s: Chroma payload field name. */
            return new WP_Error('chroma_columnar_optional_payload_mismatch', sprintf(__('Chroma "%s" must have the same length as IDs.', 'gpt3-ai-content-generator'), $optional_key));
        }
    }

    return $payload;
}

function normalize_chroma_score_logic($distance): ?float
{
    if (!is_numeric($distance)) {
        return null;
    }

    $distance = max(0.0, (float) $distance);
    return round(1 / (1 + $distance), 6);
}
