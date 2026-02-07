<?php
// File: classes/core/providers/openrouter/get-models.php
// Status: NEW FILE

namespace WPAICG\Core\Providers\OpenRouter\Methods;

use WPAICG\Core\Providers\OpenRouterProviderStrategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists(__NAMESPACE__ . '\\resolve_model_capabilities_from_metadata_logic')) {
    require_once __DIR__ . '/capabilities.php';
}

/**
 * Logic for the get_models method of OpenRouterProviderStrategy.
 *
 * @param OpenRouterProviderStrategy $strategyInstance The instance of the strategy class.
 * @param array $api_params Connection parameters (api_key, base_url, etc.).
 * @return array|WP_Error Formatted list [['id' => ..., 'name' => ...]] or WP_Error.
 */
function get_models_logic(OpenRouterProviderStrategy $strategyInstance, array $api_params): array|WP_Error {
    $url = $strategyInstance->build_api_url('models', $api_params);
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

    $status_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    if ($status_code !== 200) {
        $error_msg = $strategyInstance->parse_error_response($body, $status_code);
        return new WP_Error('api_error_openrouter_models_logic', sprintf('OpenRouter API Error (HTTP %d): %s', $status_code, esc_html($error_msg)));
    }

    // decode_json is public in BaseProviderStrategy
    $decoded = $strategyInstance->decode_json($body, 'OpenRouter Models');
    if (is_wp_error($decoded)) {
        return $decoded;
    }

    $raw_models = $decoded['data'] ?? [];
    if (!is_array($raw_models)) {
        return [];
    }

    $formatted = [];
    foreach ($raw_models as $model) {
        if (!is_array($model)) {
            continue;
        }

        $id = isset($model['id']) ? sanitize_text_field((string) $model['id']) : '';
        if ($id === '') {
            continue;
        }

        $name = isset($model['name']) ? sanitize_text_field((string) $model['name']) : $id;
        $item = [
            'id'   => $id,
            'name' => $name,
            'status'  => isset($model['status']) ? sanitize_text_field((string) $model['status']) : null,
            'version' => isset($model['version']) ? sanitize_text_field((string) $model['version']) : null,
        ];

        $supported_parameters = [];
        $raw_supported_parameters = $model['supported_parameters']
            ?? $model['supportedParameters']
            ?? $model['supported_sampling_parameters']
            ?? [];
        if (is_array($raw_supported_parameters)) {
            foreach ($raw_supported_parameters as $param) {
                if (!is_string($param)) {
                    continue;
                }
                $param = strtolower(trim($param));
                if ($param !== '') {
                    $supported_parameters[] = $param;
                }
            }
            $supported_parameters = array_values(array_unique($supported_parameters));
        }
        if (!empty($supported_parameters)) {
            $item['supported_parameters'] = $supported_parameters;
        }

        $input_modalities = [];
        $raw_input_modalities = [];
        if (isset($model['input_modalities']) && is_array($model['input_modalities'])) {
            $raw_input_modalities = $model['input_modalities'];
        } elseif (isset($model['architecture']['input_modalities']) && is_array($model['architecture']['input_modalities'])) {
            $raw_input_modalities = $model['architecture']['input_modalities'];
        }
        foreach ($raw_input_modalities as $modality) {
            if (!is_string($modality)) {
                continue;
            }
            $modality = strtolower(trim($modality));
            if ($modality !== '') {
                $input_modalities[] = $modality;
            }
        }
        $input_modalities = array_values(array_unique($input_modalities));
        if (!empty($input_modalities)) {
            $item['input_modalities'] = $input_modalities;
        }

        $output_modalities = [];
        $raw_output_modalities = [];
        if (isset($model['output_modalities']) && is_array($model['output_modalities'])) {
            $raw_output_modalities = $model['output_modalities'];
        } elseif (isset($model['architecture']['output_modalities']) && is_array($model['architecture']['output_modalities'])) {
            $raw_output_modalities = $model['architecture']['output_modalities'];
        }
        foreach ($raw_output_modalities as $modality) {
            if (!is_string($modality)) {
                continue;
            }
            $modality = strtolower(trim($modality));
            if ($modality !== '') {
                $output_modalities[] = $modality;
            }
        }
        $output_modalities = array_values(array_unique($output_modalities));
        if (!empty($output_modalities)) {
            $item['output_modalities'] = $output_modalities;
        }

        $supported_features = [];
        $raw_supported_features = $model['supported_features'] ?? $model['supportedFeatures'] ?? [];
        if (is_array($raw_supported_features)) {
            foreach ($raw_supported_features as $feature) {
                if (!is_string($feature)) {
                    continue;
                }
                $feature = strtolower(trim($feature));
                if ($feature !== '') {
                    $supported_features[] = $feature;
                }
            }
            $supported_features = array_values(array_unique($supported_features));
        }
        if (!empty($supported_features)) {
            $item['supported_features'] = $supported_features;
        }

        // Keep a tiny pricing hint for runtime feature guards.
        if (isset($model['pricing']) && is_array($model['pricing']) && isset($model['pricing']['web_search'])) {
            $item['pricing_web_search'] = sanitize_text_field((string) $model['pricing']['web_search']);
        }

        // Centralized normalized capability contract consumed across modules.
        $item['capabilities'] = resolve_model_capabilities_from_metadata_logic($item);

        $formatted[] = $item;
    }

    usort(
        $formatted,
        static fn(array $a, array $b): int => strcasecmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''))
    );

    return $formatted;
}
