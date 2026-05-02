<?php
// File: classes/core/providers/xai/get-models.php

namespace WPAICG\Core\Providers\XAI\Methods;

use WPAICG\Core\Providers\XAIProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @param XAIProviderStrategy $strategyInstance
 * @param array<string, mixed> $api_params
 * @return array<int, array<string, mixed>>|WP_Error
 */
function get_models_logic(XAIProviderStrategy $strategyInstance, array $api_params): array|WP_Error {
    $url = $strategyInstance->build_api_url('language_models', $api_params);
    if (is_wp_error($url)) {
        return $url;
    }

    $headers = $strategyInstance->get_api_headers($api_params['api_key'] ?? '', 'models');
    $options = $strategyInstance->get_request_options('models');
    $options['method'] = 'GET';

    $response = wp_remote_get($url, array_merge($options, ['headers' => $headers]));
    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if (in_array($status_code, [404, 405], true)) {
        $fallback_url = $strategyInstance->build_api_url('models', $api_params);
        if (is_wp_error($fallback_url)) {
            return $fallback_url;
        }
        $response = wp_remote_get($fallback_url, array_merge($options, ['headers' => $headers]));
        if (is_wp_error($response)) {
            return $response;
        }
        $status_code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
    }

    if ($status_code !== 200) {
        $error_msg = $strategyInstance->parse_error_response($body, $status_code);
        return new WP_Error(
            'api_error_xai_models',
            sprintf('xAI API Error (HTTP %d): %s', $status_code, esc_html($error_msg))
        );
    }

    $decoded = $strategyInstance->decode_json_public($body, 'xAI Models');
    if (is_wp_error($decoded)) {
        return $decoded;
    }

    $raw_models = [];
    if (isset($decoded['models']) && is_array($decoded['models'])) {
        $raw_models = $decoded['models'];
    } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
        $raw_models = $decoded['data'];
    } elseif (xai_array_is_list($decoded)) {
        $raw_models = $decoded;
    }

    return xai_format_model_list($raw_models);
}

/**
 * @param array<mixed> $array
 */
function xai_array_is_list(array $array): bool {
    $expected_key = 0;
    foreach ($array as $key => $_value) {
        if ($key !== $expected_key) {
            return false;
        }
        $expected_key++;
    }

    return true;
}

/**
 * @param array<int, mixed> $raw_models
 * @return array<int, array<string, mixed>>
 */
function xai_format_model_list(array $raw_models): array {
    $formatted = [];

    foreach ($raw_models as $model) {
        if (is_string($model)) {
            $formatted[] = ['id' => $model, 'name' => $model];
            continue;
        }
        if (!is_array($model)) {
            continue;
        }

        $id = $model['id'] ?? $model['model'] ?? $model['name'] ?? null;
        if (!is_string($id) || trim($id) === '') {
            continue;
        }

        $name = $model['name'] ?? $model['display_name'] ?? $id;
        $item = [
            'id' => $id,
            'name' => is_string($name) && trim($name) !== '' ? $name : $id,
            'status' => $model['status'] ?? null,
            'version' => $model['version'] ?? null,
        ];

        $metadata_keys = [
            'aliases',
            'created',
            'fingerprint',
            'input_modalities',
            'output_modalities',
            'owned_by',
            'prompt_text_token_price',
            'cached_prompt_text_token_price',
            'completion_text_token_price',
            'prompt_image_token_price',
            'search_price',
        ];
        foreach ($metadata_keys as $metadata_key) {
            if (array_key_exists($metadata_key, $model)) {
                $item[$metadata_key] = $model[$metadata_key];
            }
        }

        $formatted[] = $item;
    }

    usort($formatted, fn ($a, $b) => strcasecmp($a['name'] ?? '', $b['name'] ?? ''));

    return $formatted;
}
