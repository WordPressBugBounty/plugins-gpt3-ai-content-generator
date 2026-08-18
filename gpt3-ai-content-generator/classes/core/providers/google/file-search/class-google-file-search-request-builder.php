<?php

namespace WPAICG\Core\Providers\Google\FileSearch;

use WPAICG\Core\Providers\Google\Interactions\GoogleModelCapabilityClassifier;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleFileSearchRequestBuilder
{
    private const MAX_DISPLAY_NAME_LENGTH = 512;
    private const MAX_CUSTOM_METADATA = 20;
    private const MAX_METADATA_FILTER_LENGTH = 4096;

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|WP_Error
     */
    public static function build_create_store(string $display_name, array $options = [])
    {
        $display_name = self::normalize_display_name($display_name);
        if (is_wp_error($display_name)) {
            return $display_name;
        }

        $payload = ['displayName' => $display_name];
        if (isset($options['embedding_model'])) {
            $embedding_model = self::normalize_embedding_model($options['embedding_model']);
            if (is_wp_error($embedding_model)) {
                return $embedding_model;
            }
            if ($embedding_model !== '') {
                $payload['embeddingModel'] = $embedding_model;
            }
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|WP_Error
     */
    public static function build_import_file(string $file_name, array $options = [])
    {
        $file_name = GoogleFileSearchUrlBuilder::normalize_file_name($file_name);
        if (is_wp_error($file_name)) {
            return $file_name;
        }

        $payload = ['fileName' => $file_name];
        return self::append_ingestion_options($payload, $options);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>|WP_Error
     */
    public static function build_upload_metadata(string $display_name, string $mime_type, array $options = [])
    {
        $display_name = self::normalize_display_name($display_name);
        if (is_wp_error($display_name)) {
            return $display_name;
        }
        $mime_type = sanitize_mime_type($mime_type);
        if ($mime_type === '') {
            return new WP_Error(
                'google_file_search_invalid_mime_type',
                __('A valid MIME type is required for Google File Search uploads.', 'gpt3-ai-content-generator')
            );
        }

        $payload = [
            'displayName' => $display_name,
            'mimeType' => $mime_type,
        ];
        return self::append_ingestion_options($payload, $options);
    }

    /**
     * Build the Interactions-native File Search tool declaration.
     *
     * @param array<int, string>   $store_names
     * @param array<string, mixed> $options model, top_k, and metadata_filter.
     * @return array<string, mixed>|WP_Error
     */
    public static function build_tool(array $store_names, array $options = [])
    {
        $normalized_names = [];
        foreach ($store_names as $store_name) {
            if (!is_string($store_name)) {
                return new WP_Error(
                    'google_file_search_invalid_tool_store',
                    __('Every Google File Search tool store must be a resource name.', 'gpt3-ai-content-generator')
                );
            }
            $normalized_name = GoogleFileSearchUrlBuilder::normalize_store_name($store_name);
            if (is_wp_error($normalized_name)) {
                return $normalized_name;
            }
            $normalized_names[$normalized_name] = true;
        }
        $normalized_names = array_keys($normalized_names);
        if (empty($normalized_names)) {
            return new WP_Error(
                'google_file_search_missing_tool_store',
                __('Select at least one Google store.', 'gpt3-ai-content-generator')
            );
        }

        $model = isset($options['model']) && is_string($options['model']) ? trim($options['model']) : '';
        if (
            $model !== ''
            && class_exists(GoogleModelCapabilityClassifier::class)
            && !GoogleModelCapabilityClassifier::supports_file_search($model)
        ) {
            return new WP_Error(
                'google_file_search_model_not_supported',
                __('The selected Google model does not support File Search.', 'gpt3-ai-content-generator')
            );
        }

        $tool = [
            'type' => 'file_search',
            'file_search_store_names' => $normalized_names,
        ];

        if (isset($options['top_k'])) {
            if (!is_numeric($options['top_k'])) {
                return new WP_Error(
                    'google_file_search_invalid_top_k',
                    __('Google File Search results limit must be a number.', 'gpt3-ai-content-generator')
                );
            }
            $top_k = (int) $options['top_k'];
            if ($top_k < 1 || $top_k > 20) {
                return new WP_Error(
                    'google_file_search_invalid_top_k',
                    __('Google File Search results limit must be between 1 and 20.', 'gpt3-ai-content-generator')
                );
            }
            $tool['top_k'] = $top_k;
        }

        if (isset($options['metadata_filter'])) {
            if (!is_string($options['metadata_filter'])) {
                return new WP_Error(
                    'google_file_search_invalid_metadata_filter',
                    __('Google File Search metadata filter must be text.', 'gpt3-ai-content-generator')
                );
            }
            $metadata_filter = trim($options['metadata_filter']);
            if (
                strlen($metadata_filter) > self::MAX_METADATA_FILTER_LENGTH
                || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $metadata_filter)
            ) {
                return new WP_Error(
                    'google_file_search_invalid_metadata_filter',
                    __('Google File Search metadata filter contains invalid control characters or is too long.', 'gpt3-ai-content-generator')
                );
            }
            if ($metadata_filter !== '') {
                $tool['metadata_filter'] = $metadata_filter;
            }
        }

        return $tool;
    }

    /**
     * @param mixed $metadata Associative values or API-native metadata entries.
     * @return array<int, array<string, mixed>>|WP_Error
     */
    public static function normalize_custom_metadata($metadata)
    {
        if (!is_array($metadata)) {
            return new WP_Error(
                'google_file_search_invalid_metadata',
                __('Google File Search metadata must be an array.', 'gpt3-ai-content-generator')
            );
        }
        if (empty($metadata)) {
            return [];
        }

        $entries = self::is_associative($metadata) ? self::metadata_map_to_entries($metadata) : $metadata;
        if (count($entries) > self::MAX_CUSTOM_METADATA) {
            return new WP_Error(
                'google_file_search_metadata_limit',
                __('Google File Search supports at most 20 metadata entries per document.', 'gpt3-ai-content-generator')
            );
        }

        $normalized = [];
        $seen = [];
        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                return new WP_Error(
                    'google_file_search_invalid_metadata_entry',
                    __('Every Google File Search metadata entry must contain a key and value.', 'gpt3-ai-content-generator')
                );
            }
            $key = isset($entry['key']) && is_string($entry['key'])
                ? trim(wp_strip_all_tags($entry['key']))
                : '';
            if ($key === '' || strlen($key) > 128 || preg_match('/[\x00-\x1F\x7F]/', $key)) {
                return new WP_Error(
                    'google_file_search_invalid_metadata_key',
                    __('Google File Search metadata keys must be non-empty plain text.', 'gpt3-ai-content-generator')
                );
            }
            if (isset($seen[$key])) {
                return new WP_Error(
                    'google_file_search_duplicate_metadata_key',
                    __('Google File Search metadata keys must be unique.', 'gpt3-ai-content-generator')
                );
            }

            $value_result = self::normalize_metadata_value($entry);
            if (is_wp_error($value_result)) {
                return $value_result;
            }
            $seen[$key] = true;
            $normalized[] = array_merge(['key' => $key], $value_result);
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     * @return array<string, mixed>|WP_Error
     */
    private static function append_ingestion_options(array $payload, array $options)
    {
        if (array_key_exists('custom_metadata', $options)) {
            $metadata = self::normalize_custom_metadata($options['custom_metadata']);
            if (is_wp_error($metadata)) {
                return $metadata;
            }
            if (!empty($metadata)) {
                $payload['customMetadata'] = $metadata;
            }
        }

        if (array_key_exists('chunking_config', $options)) {
            $chunking_config = self::normalize_chunking_config($options['chunking_config']);
            if (is_wp_error($chunking_config)) {
                return $chunking_config;
            }
            if (!empty($chunking_config)) {
                $payload['chunkingConfig'] = $chunking_config;
            }
        }

        return $payload;
    }

    /**
     * @param mixed $chunking_config
     * @return array<string, mixed>|WP_Error
     */
    private static function normalize_chunking_config($chunking_config)
    {
        if (!is_array($chunking_config)) {
            return new WP_Error(
                'google_file_search_invalid_chunking',
                __('Google File Search chunking configuration must be an array.', 'gpt3-ai-content-generator')
            );
        }
        if (empty($chunking_config)) {
            return [];
        }

        $white_space = $chunking_config['whiteSpaceConfig']
            ?? $chunking_config['white_space_config']
            ?? $chunking_config;
        if (!is_array($white_space)) {
            return new WP_Error(
                'google_file_search_invalid_chunking',
                __('Google File Search whitespace chunking configuration is invalid.', 'gpt3-ai-content-generator')
            );
        }

        $max_tokens = $white_space['maxTokensPerChunk'] ?? $white_space['max_tokens_per_chunk'] ?? null;
        $max_overlap = $white_space['maxOverlapTokens'] ?? $white_space['max_overlap_tokens'] ?? 0;
        if (!is_numeric($max_tokens) || !is_numeric($max_overlap)) {
            return new WP_Error(
                'google_file_search_invalid_chunking',
                __('Google File Search chunk sizes must be numeric.', 'gpt3-ai-content-generator')
            );
        }
        $max_tokens = (int) $max_tokens;
        $max_overlap = (int) $max_overlap;
        if ($max_tokens < 1 || $max_overlap < 0 || $max_overlap >= $max_tokens) {
            return new WP_Error(
                'google_file_search_invalid_chunking',
                __('Google File Search overlap must be non-negative and smaller than the chunk size.', 'gpt3-ai-content-generator')
            );
        }

        return [
            'whiteSpaceConfig' => [
                'maxTokensPerChunk' => $max_tokens,
                'maxOverlapTokens' => $max_overlap,
            ],
        ];
    }

    /**
     * @param mixed $embedding_model
     * @return string|WP_Error
     */
    private static function normalize_embedding_model($embedding_model)
    {
        if (!is_string($embedding_model)) {
            return new WP_Error(
                'google_file_search_invalid_embedding_model',
                __('Google File Search embedding model must be text.', 'gpt3-ai-content-generator')
            );
        }
        $embedding_model = trim($embedding_model);
        if ($embedding_model === '') {
            return '';
        }
        if (strpos($embedding_model, 'models/') === 0) {
            $embedding_model = (string) substr($embedding_model, strlen('models/'));
        }
        if (!preg_match('/^[A-Za-z0-9._-]+$/', $embedding_model)) {
            return new WP_Error(
                'google_file_search_invalid_embedding_model',
                __('The Google File Search embedding model ID is invalid.', 'gpt3-ai-content-generator')
            );
        }

        return 'models/' . $embedding_model;
    }

    /**
     * @return string|WP_Error
     */
    private static function normalize_display_name(string $display_name)
    {
        $display_name = trim(wp_strip_all_tags($display_name));
        if ($display_name === '' || strlen($display_name) > self::MAX_DISPLAY_NAME_LENGTH) {
            return new WP_Error(
                'google_file_search_invalid_display_name',
                __('Google File Search display names must contain between 1 and 512 characters.', 'gpt3-ai-content-generator')
            );
        }

        return $display_name;
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<int, array<string, mixed>>
     */
    private static function metadata_map_to_entries(array $metadata): array
    {
        $entries = [];
        foreach ($metadata as $key => $value) {
            $entries[] = ['key' => (string) $key, 'value' => $value];
        }
        return $entries;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>|WP_Error
     */
    private static function normalize_metadata_value(array $entry)
    {
        $value = $entry['value'] ?? null;
        if (array_key_exists('stringValue', $entry) || array_key_exists('string_value', $entry)) {
            $value = $entry['stringValue'] ?? $entry['string_value'];
        } elseif (array_key_exists('numericValue', $entry) || array_key_exists('numeric_value', $entry)) {
            $numeric_value = $entry['numericValue'] ?? $entry['numeric_value'];
            if (!is_numeric($numeric_value)) {
                return self::invalid_metadata_value_error();
            }
            return ['numericValue' => 0 + $numeric_value];
        } elseif (array_key_exists('stringListValue', $entry) || array_key_exists('string_list_value', $entry)) {
            $value = $entry['stringListValue'] ?? $entry['string_list_value'];
            if (is_array($value) && isset($value['values'])) {
                $value = $value['values'];
            }
        }

        if (is_int($value) || is_float($value)) {
            return ['numericValue' => $value];
        }
        if (is_bool($value)) {
            return ['stringValue' => $value ? 'true' : 'false'];
        }
        if (is_string($value)) {
            return ['stringValue' => trim(wp_strip_all_tags($value))];
        }
        if (is_array($value)) {
            $values = [];
            foreach ($value as $list_value) {
                if (!is_scalar($list_value)) {
                    return self::invalid_metadata_value_error();
                }
                $values[] = trim(wp_strip_all_tags((string) $list_value));
            }
            return ['stringListValue' => ['values' => $values]];
        }

        return self::invalid_metadata_value_error();
    }

    private static function invalid_metadata_value_error(): WP_Error
    {
        return new WP_Error(
            'google_file_search_invalid_metadata_value',
            __('Google File Search metadata values must be text, numbers, or lists of text.', 'gpt3-ai-content-generator')
        );
    }

    /**
     * @param array<mixed> $value
     */
    private static function is_associative(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
