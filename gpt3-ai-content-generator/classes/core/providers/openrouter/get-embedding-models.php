<?php
// File: classes/core/providers/openrouter/get-embedding-models.php

namespace WPAICG\Core\Providers\OpenRouter\Methods;

use WPAICG\Core\Providers\OpenRouterProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for fetching OpenRouter embedding models from /embeddings/models.
 *
 * @param OpenRouterProviderStrategy $strategyInstance The OpenRouter strategy instance.
 * @param array $api_params Connection parameters (api_key, base_url, api_version).
 * @return array|WP_Error Formatted list [['id' => ..., 'name' => ...]] or WP_Error.
 */
function get_embedding_models_logic(OpenRouterProviderStrategy $strategyInstance, array $api_params): array|WP_Error {
    $url = $strategyInstance->build_api_url('embedding_models', $api_params);
    if (is_wp_error($url)) {
        return $url;
    }

    $headers = $strategyInstance->get_api_headers($api_params['api_key'] ?? '', 'embedding_models');
    $options = $strategyInstance->get_request_options('embedding_models');
    $options['method'] = 'GET';

    $response = wp_remote_get($url, array_merge($options, ['headers' => $headers]));
    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    if ($status_code !== 200) {
        $error_msg = $strategyInstance->parse_error_response($body, $status_code);
        /* translators: %1$d: HTTP status code, %2$s: API error message. */
        return new WP_Error(
            'api_error_openrouter_embedding_models_logic',
            sprintf(__('OpenRouter Embedding Models API Error (HTTP %1$d): %2$s', 'gpt3-ai-content-generator'), $status_code, esc_html($error_msg))
        );
    }

    $decoded = $strategyInstance->decode_json($body, 'OpenRouter Embedding Models');
    if (is_wp_error($decoded)) {
        return $decoded;
    }

    $raw_models = $decoded['data'] ?? [];
    if (!is_array($raw_models)) {
        return [];
    }

    $formatted = [];
    foreach ($raw_models as $model) {
        if (is_string($model)) {
            $id = sanitize_text_field($model);
            if ($id === '') {
                continue;
            }
            $formatted[] = ['id' => $id, 'name' => $id];
            continue;
        }

        if (!is_array($model)) {
            continue;
        }

        $id = isset($model['id']) ? sanitize_text_field((string) $model['id']) : '';
        if ($id === '') {
            continue;
        }
        $name = isset($model['name']) ? sanitize_text_field((string) $model['name']) : $id;
        $formatted[] = ['id' => $id, 'name' => $name];
    }

    usort(
        $formatted,
        static fn(array $a, array $b): int => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''))
    );

    return $formatted;
}

