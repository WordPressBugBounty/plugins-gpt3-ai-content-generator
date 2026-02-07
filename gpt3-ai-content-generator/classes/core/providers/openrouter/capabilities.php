<?php
// File: classes/core/providers/openrouter/capabilities.php

namespace WPAICG\Core\Providers\OpenRouter\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Returns default OpenRouter provider capability map used for runtime guards.
 *
 * Important: this is provider-level default. Model-level resolution may narrow
 * capabilities when synced metadata explicitly indicates limitations.
 *
 * @return array<string, bool>
 */
function get_capability_map_logic(): array {
    return [
        'chat' => true,
        'stream' => true,
        'tools' => true,
        'web_search_plugin' => true,
        'image_input' => true,
        'image_output' => true,
        'image_generation' => true,
        'embeddings' => true,
    ];
}

/**
 * Normalizes a mixed list value into lowercase unique strings.
 *
 * @param mixed $value Raw list value.
 * @return array<int, string>
 */
function normalize_capability_list_logic(mixed $value): array {
    if (!is_array($value)) {
        return [];
    }

    $normalized = [];
    foreach ($value as $entry) {
        if (!is_string($entry)) {
            continue;
        }
        $entry = strtolower(trim($entry));
        if ($entry !== '') {
            $normalized[] = $entry;
        }
    }

    return array_values(array_unique($normalized));
}

/**
 * Sanitizes capability payload from stored metadata.
 *
 * @param mixed $capabilities Raw stored capabilities.
 * @return array<string, bool>
 */
function sanitize_capabilities_payload_logic(mixed $capabilities): array {
    if (!is_array($capabilities)) {
        return [];
    }

    $allowed_keys = array_keys(get_capability_map_logic());
    $sanitized = [];
    foreach ($allowed_keys as $key) {
        if (!array_key_exists($key, $capabilities)) {
            continue;
        }
        $sanitized[$key] = (bool) $capabilities[$key];
    }

    return $sanitized;
}

/**
 * Resolves model-level capabilities from OpenRouter model metadata.
 *
 * @param array<string, mixed> $metadata Synchronized model metadata.
 * @return array<string, bool>
 */
function resolve_model_capabilities_from_metadata_logic(array $metadata): array {
    $resolved = get_capability_map_logic();

    // If model already contains normalized capability payload, use it as base.
    $stored_capabilities = sanitize_capabilities_payload_logic($metadata['capabilities'] ?? null);
    if (!empty($stored_capabilities)) {
        $resolved = array_merge($resolved, $stored_capabilities);
    }

    $input_modalities = normalize_capability_list_logic(
        $metadata['input_modalities'] ?? ($metadata['architecture']['input_modalities'] ?? [])
    );
    if (!empty($input_modalities)) {
        $resolved['image_input'] = in_array('image', $input_modalities, true)
            || in_array('image_url', $input_modalities, true)
            || in_array('input_image', $input_modalities, true);
    }

    $output_modalities = normalize_capability_list_logic(
        $metadata['output_modalities'] ?? ($metadata['architecture']['output_modalities'] ?? [])
    );
    if (!empty($output_modalities)) {
        $supports_image_output = in_array('image', $output_modalities, true)
            || in_array('image_url', $output_modalities, true)
            || in_array('output_image', $output_modalities, true);
        $resolved['image_output'] = $supports_image_output;
        $resolved['image_generation'] = $supports_image_output;
    }

    $supported_features = normalize_capability_list_logic(
        $metadata['supported_features'] ?? ($metadata['supportedFeatures'] ?? [])
    );
    if (!empty($supported_features)) {
        $supports_tools_feature = in_array('tools', $supported_features, true)
            || in_array('tool_use', $supported_features, true)
            || in_array('function_calling', $supported_features, true)
            || in_array('function-calling', $supported_features, true)
            || in_array('plugins', $supported_features, true);

        $supports_web_feature = in_array('web_search', $supported_features, true)
            || in_array('web-search', $supported_features, true)
            || in_array('web', $supported_features, true)
            || $supports_tools_feature;

        $supports_image_generation_feature = in_array('image_generation', $supported_features, true)
            || in_array('image-generation', $supported_features, true);

        // Treat explicit supported_features payload as authoritative for these capabilities.
        $resolved['tools'] = $supports_tools_feature;
        $resolved['web_search_plugin'] = $supports_web_feature;
        $resolved['image_generation'] = $supports_image_generation_feature || $resolved['image_generation'];
        $resolved['image_output'] = $resolved['image_generation'];

        if (in_array('embeddings', $supported_features, true) || in_array('embedding', $supported_features, true)) {
            $resolved['embeddings'] = true;
        }
    }

    $supported_parameters = normalize_capability_list_logic(
        $metadata['supported_parameters']
            ?? ($metadata['supportedParameters']
                ?? ($metadata['supported_sampling_parameters'] ?? []))
    );
    if (!empty($supported_parameters)) {
        $supports_tools_param = in_array('plugins', $supported_parameters, true)
            || in_array('tools', $supported_parameters, true)
            || in_array('tool_choice', $supported_parameters, true)
            || in_array('parallel_tool_calls', $supported_parameters, true);

        if (empty($supported_features)) {
            // If we do not have explicit feature metadata, use parameter metadata directly.
            $resolved['tools'] = $supports_tools_param;
            $resolved['web_search_plugin'] = $supports_tools_param;
        } else {
            // If feature metadata exists, treat parameter metadata as supplementary.
            $resolved['tools'] = !empty($resolved['tools']) || $supports_tools_param;
            $resolved['web_search_plugin'] = !empty($resolved['web_search_plugin']) || $supports_tools_param;
        }
    }

    // Keep compatibility with OpenRouter pricing hints.
    if (array_key_exists('pricing_web_search', $metadata)) {
        $resolved['web_search_plugin'] = true;
    }

    // Backward-compatible fallback for legacy sync payloads with no modality/features.
    if (
        empty($input_modalities)
        && empty($output_modalities)
        && empty($supported_features)
        && empty($supported_parameters)
        && !empty($metadata['id'])
    ) {
        $model_id_l = strtolower((string) $metadata['id']);
        $looks_like_image_model = strpos($model_id_l, 'image') !== false
            || strpos($model_id_l, 'flux') !== false
            || strpos($model_id_l, 'stable-diffusion') !== false
            || strpos($model_id_l, 'sdxl') !== false
            || strpos($model_id_l, 'riverflow') !== false;
        if ($looks_like_image_model) {
            $resolved['image_output'] = true;
            $resolved['image_generation'] = true;
        }
    }

    // Keep aliases aligned.
    if ($resolved['image_output'] !== $resolved['image_generation']) {
        $resolved['image_output'] = $resolved['image_generation'] || $resolved['image_output'];
        $resolved['image_generation'] = $resolved['image_output'];
    }

    return $resolved;
}

/**
 * Finds synchronized OpenRouter metadata for a specific model id.
 *
 * @param string $model_id OpenRouter model id.
 * @return array<string, mixed>|null
 */
function get_model_metadata_logic(string $model_id): ?array {
    $model_id = sanitize_text_field($model_id);
    if ($model_id === '') {
        return null;
    }

    $synced_models = get_option('aipkit_openrouter_model_list', []);
    if (!is_array($synced_models)) {
        return null;
    }

    foreach ($synced_models as $model) {
        if (!is_array($model)) {
            continue;
        }
        $candidate_id = isset($model['id']) ? sanitize_text_field((string) $model['id']) : '';
        if ($candidate_id !== '' && $candidate_id === $model_id) {
            return $model;
        }
    }

    return null;
}

/**
 * Resolves model-level capabilities for an OpenRouter model id.
 *
 * @param string $model_id OpenRouter model id.
 * @return array<string, bool>
 */
function resolve_model_capabilities_logic(string $model_id): array {
    $metadata = get_model_metadata_logic($model_id);
    if (!is_array($metadata)) {
        return get_capability_map_logic();
    }

    return resolve_model_capabilities_from_metadata_logic($metadata);
}

/**
 * Checks whether selected OpenRouter model appears to support web search plugin usage.
 *
 * Decision policy:
 * - If synced metadata explicitly declares support (`supported_features` / `supported_parameters`), trust it.
 * - If metadata exists and explicitly excludes tool/web capabilities, return false.
 * - If metadata is missing, return true to avoid breaking existing installs.
 *
 * @param string $model_id OpenRouter model id.
 * @return bool
 */
function model_supports_web_search_plugin_logic(string $model_id): bool {
    $resolved = resolve_model_capabilities_logic($model_id);
    return !empty($resolved['web_search_plugin']);
}

/**
 * Checks whether selected OpenRouter model appears to support image output.
 *
 * @param string $model_id OpenRouter model id.
 * @return bool
 */
function model_supports_image_output_logic(string $model_id): bool {
    $resolved = resolve_model_capabilities_logic($model_id);
    return !empty($resolved['image_output']);
}

/**
 * Sanitizes web search plugin configuration for OpenRouter Responses payloads.
 *
 * @param array $raw_config Raw web search tool config.
 * @return array Sanitized web plugin config fields.
 */
function sanitize_web_search_config_logic(array $raw_config): array {
    $sanitized = [];

    if (isset($raw_config['max_results'])) {
        $max_results = absint($raw_config['max_results']);
        if ($max_results > 0) {
            $sanitized['max_results'] = max(1, min($max_results, 10));
        }
    }

    if (!empty($raw_config['search_prompt'])) {
        $search_prompt = sanitize_textarea_field((string) $raw_config['search_prompt']);
        if ($search_prompt !== '') {
            $sanitized['search_prompt'] = $search_prompt;
        }
    }

    if (!empty($raw_config['engine'])) {
        $engine = sanitize_key((string) $raw_config['engine']);
        if (in_array($engine, ['native', 'exa'], true)) {
            $sanitized['engine'] = $engine;
        }
    }

    return $sanitized;
}
