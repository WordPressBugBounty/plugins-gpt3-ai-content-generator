<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WPAICG\Core\Providers\Google\FileSearch\GoogleFileSearchRequestBuilder;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsRequestBuilder
{
    private const ALLOWED_OPTIONS = [
        'generation_config',
        'previous_interaction_id',
        'response_format',
        'store',
        'stream',
        'system_instruction',
        'tools',
    ];

    private const INPUT_STEP_TYPES = [
        'model_output',
        'user_input',
    ];

    private const INPUT_CONTENT_TYPES = [
        'audio',
        'document',
        'image',
        'text',
        'video',
    ];

    /**
     * Build a strict Interactions request from API-native values.
     *
     * Higher-level provider adapters are responsible for translating plugin
     * history and settings into this contract.
     *
     * @param string             $model   Gemini model ID.
     * @param string|array<mixed> $input   Text, typed content, or typed interaction steps.
     * @param array<string, mixed> $options Optional Interactions fields.
     * @return array<string, mixed>|WP_Error
     */
    public static function build(string $model, $input, array $options = [])
    {
        $model = self::normalize_model_id($model);
        if ($model === '') {
            return new WP_Error(
                'google_interactions_missing_model',
                __('A Google model is required for an interaction.', 'gpt3-ai-content-generator')
            );
        }

        $unknown_options = array_diff(array_keys($options), self::ALLOWED_OPTIONS);
        if (!empty($unknown_options)) {
            return new WP_Error(
                'google_interactions_unsupported_option',
                sprintf(
                    /* translators: %s: Comma-separated unsupported Google Interactions option names. */
                    __('Unsupported Google Interactions option(s): %s.', 'gpt3-ai-content-generator'),
                    implode(', ', array_map('sanitize_key', $unknown_options))
                )
            );
        }

        $normalized_input = self::normalize_input($input);
        if (is_wp_error($normalized_input)) {
            return $normalized_input;
        }

        $payload = [
            'model' => $model,
            'input' => $normalized_input,
            'store' => isset($options['store']) ? (bool) $options['store'] : false,
        ];

        if (isset($options['stream'])) {
            $payload['stream'] = (bool) $options['stream'];
        }

        if (isset($options['previous_interaction_id'])) {
            $previous_interaction_id = is_string($options['previous_interaction_id'])
                ? trim($options['previous_interaction_id'])
                : '';
            if ($previous_interaction_id === '') {
                return new WP_Error(
                    'google_interactions_invalid_previous_id',
                    __('The previous Google interaction ID must be a non-empty string.', 'gpt3-ai-content-generator')
                );
            }
            $payload['previous_interaction_id'] = $previous_interaction_id;
        }

        if (isset($options['system_instruction'])) {
            $system_instruction = is_string($options['system_instruction'])
                ? trim($options['system_instruction'])
                : '';
            if ($system_instruction !== '') {
                $payload['system_instruction'] = $system_instruction;
            }
        }

        if (isset($options['generation_config'])) {
            if (!is_array($options['generation_config'])) {
                return new WP_Error(
                    'google_interactions_invalid_generation_config',
                    __('Google generation configuration must be an array.', 'gpt3-ai-content-generator')
                );
            }
            if (!empty($options['generation_config'])) {
                $payload['generation_config'] = $options['generation_config'];
            }
        }

        if (isset($options['response_format'])) {
            $response_format = self::normalize_response_format($options['response_format']);
            if (is_wp_error($response_format)) {
                return $response_format;
            }
            $payload['response_format'] = $response_format;
        }

        if (isset($options['tools'])) {
            $tools = self::normalize_tools($options['tools'], $model);
            if (is_wp_error($tools)) {
                return $tools;
            }
            if (!empty($tools)) {
                $payload['tools'] = $tools;
            }
        }

        return $payload;
    }

    /**
     * Build one typed user input step for multimodal requests.
     *
     * @param array<int, array<string, mixed>> $content Content items.
     * @return array<string, mixed>|WP_Error
     */
    public static function user_input(array $content)
    {
        $normalized_content = self::normalize_content($content);
        if (is_wp_error($normalized_content)) {
            return $normalized_content;
        }

        return [
            'type' => 'user_input',
            'content' => $normalized_content,
        ];
    }

    private static function normalize_model_id(string $model): string
    {
        $model = trim($model);
        if (strpos($model, 'models/') === 0) {
            $model = (string) substr($model, 7);
        }

        return trim($model);
    }

    /**
     * @param mixed $input
     * @return string|array<int, array<string, mixed>>|WP_Error
     */
    private static function normalize_input($input)
    {
        if (is_string($input)) {
            if (trim($input) === '') {
                return new WP_Error(
                    'google_interactions_missing_input',
                    __('Google interaction input cannot be empty.', 'gpt3-ai-content-generator')
                );
            }

            return $input;
        }

        if (!is_array($input) || empty($input)) {
            return new WP_Error(
                'google_interactions_invalid_input',
                __('Google interaction input must be text or typed input steps.', 'gpt3-ai-content-generator')
            );
        }

        $first_type = isset($input[0]['type']) && is_string($input[0]['type'])
            ? trim($input[0]['type'])
            : '';
        if (in_array($first_type, self::INPUT_CONTENT_TYPES, true)) {
            return self::normalize_content($input);
        }

        $normalized_steps = [];
        foreach ($input as $step) {
            if (!is_array($step)) {
                return new WP_Error(
                    'google_interactions_invalid_input_step',
                    __('Every Google interaction input step must be an array.', 'gpt3-ai-content-generator')
                );
            }

            $type = isset($step['type']) && is_string($step['type']) ? trim($step['type']) : '';
            if (!in_array($type, self::INPUT_STEP_TYPES, true)) {
                return new WP_Error(
                    'google_interactions_invalid_input_step_type',
                    __('Google interaction history accepts only user_input and model_output steps.', 'gpt3-ai-content-generator')
                );
            }

            $content = isset($step['content']) && is_array($step['content']) ? $step['content'] : [];
            $normalized_content = self::normalize_content($content);
            if (is_wp_error($normalized_content)) {
                return $normalized_content;
            }

            $normalized_steps[] = [
                'type' => $type,
                'content' => $normalized_content,
            ];
        }

        return $normalized_steps;
    }

    /**
     * @param mixed $response_format
     * @return array<string, mixed>|array<int, array<string, mixed>>|WP_Error
     */
    private static function normalize_response_format($response_format)
    {
        if (!is_array($response_format) || empty($response_format)) {
            return new WP_Error(
                'google_interactions_invalid_response_format',
                __('Google response format must be a non-empty object or list.', 'gpt3-ai-content-generator')
            );
        }

        $is_list = array_keys($response_format) === range(0, count($response_format) - 1);
        if (!$is_list) {
            return self::normalize_response_format_item($response_format);
        }

        $normalized = [];
        foreach ($response_format as $format_item) {
            if (!is_array($format_item)) {
                return new WP_Error(
                    'google_interactions_invalid_response_format_item',
                    __('Every Google response format entry must be an object.', 'gpt3-ai-content-generator')
                );
            }
            $item = self::normalize_response_format_item($format_item);
            if (is_wp_error($item)) {
                return $item;
            }
            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $format_item
     * @return array<string, mixed>|WP_Error
     */
    private static function normalize_response_format_item(array $format_item)
    {
        $type = isset($format_item['type']) && is_string($format_item['type'])
            ? sanitize_key($format_item['type'])
            : '';
        if (!in_array($type, ['audio', 'image', 'text'], true)) {
            return new WP_Error(
                'google_interactions_invalid_response_format_type',
                __('Google response format type must be text, image, or audio.', 'gpt3-ai-content-generator')
            );
        }

        $allowed_keys = ['type'];
        if ($type === 'image') {
            $allowed_keys = array_merge($allowed_keys, ['aspect_ratio', 'image_size', 'mime_type']);
        } elseif ($type === 'audio') {
            $allowed_keys[] = 'mime_type';
        }
        if (!empty(array_diff(array_keys($format_item), $allowed_keys))) {
            return new WP_Error(
                'google_interactions_unsupported_response_format_option',
                __('Google response format contains an unsupported option.', 'gpt3-ai-content-generator')
            );
        }

        $normalized = ['type' => $type];
        if (isset($format_item['mime_type']) && is_string($format_item['mime_type'])) {
            $mime_type = sanitize_mime_type($format_item['mime_type']);
            $mime_prefix = $type === 'image' ? 'image/' : ($type === 'audio' ? 'audio/' : '');
            if ($mime_type === '' || ($mime_prefix !== '' && strpos($mime_type, $mime_prefix) !== 0)) {
                return new WP_Error(
                    'google_interactions_invalid_response_mime_type',
                    __('Google response format has an invalid MIME type.', 'gpt3-ai-content-generator')
                );
            }
            $normalized['mime_type'] = $mime_type;
        }

        if ($type === 'image') {
            $aspect_ratio = isset($format_item['aspect_ratio']) && is_string($format_item['aspect_ratio'])
                ? trim($format_item['aspect_ratio'])
                : '';
            $allowed_ratios = ['1:1', '1:4', '1:8', '2:3', '3:2', '3:4', '4:1', '4:3', '4:5', '5:4', '8:1', '9:16', '16:9', '21:9'];
            if ($aspect_ratio !== '') {
                if (!in_array($aspect_ratio, $allowed_ratios, true)) {
                    return new WP_Error(
                        'google_interactions_invalid_image_aspect_ratio',
                        __('Google image response format has an unsupported aspect ratio.', 'gpt3-ai-content-generator')
                    );
                }
                $normalized['aspect_ratio'] = $aspect_ratio;
            }

            $image_size = isset($format_item['image_size']) && is_string($format_item['image_size'])
                ? strtoupper(trim($format_item['image_size']))
                : '';
            if ($image_size !== '') {
                if (!in_array($image_size, ['512', '1K', '2K', '4K'], true)) {
                    return new WP_Error(
                        'google_interactions_invalid_image_size',
                        __('Google image response format has an unsupported image size.', 'gpt3-ai-content-generator')
                    );
                }
                $normalized['image_size'] = $image_size;
            }
        }

        return $normalized;
    }

    /**
     * @param array<int, mixed> $content
     * @return array<int, array<string, mixed>>|WP_Error
     */
    private static function normalize_content(array $content)
    {
        if (empty($content)) {
            return new WP_Error(
                'google_interactions_missing_content',
                __('A Google interaction input step must contain at least one content item.', 'gpt3-ai-content-generator')
            );
        }

        $normalized_content = [];
        foreach ($content as $item) {
            if (!is_array($item)) {
                return new WP_Error(
                    'google_interactions_invalid_content',
                    __('Every Google interaction content item must be an array.', 'gpt3-ai-content-generator')
                );
            }

            $type = isset($item['type']) && is_string($item['type']) ? trim($item['type']) : '';
            if (!in_array($type, self::INPUT_CONTENT_TYPES, true)) {
                return new WP_Error(
                    'google_interactions_invalid_content_type',
                    __('Google interaction content has an unsupported type.', 'gpt3-ai-content-generator')
                );
            }

            if ($type === 'text') {
                $text = isset($item['text']) && is_string($item['text']) ? $item['text'] : '';
                if (trim($text) === '') {
                    return new WP_Error(
                        'google_interactions_invalid_text_content',
                        __('Google interaction text content cannot be empty.', 'gpt3-ai-content-generator')
                    );
                }
                $normalized_content[] = ['type' => 'text', 'text' => $text];
                continue;
            }

            $mime_type = isset($item['mime_type']) && is_string($item['mime_type'])
                ? sanitize_mime_type($item['mime_type'])
                : '';
            $data = isset($item['data']) && is_string($item['data']) ? trim($item['data']) : '';
            $uri = isset($item['uri']) && is_string($item['uri']) ? trim($item['uri']) : '';
            if ($mime_type === '' || ($data === '') === ($uri === '')) {
                return new WP_Error(
                    'google_interactions_invalid_media',
                    __('Google media content requires mime_type and exactly one of data or uri.', 'gpt3-ai-content-generator')
                );
            }
            if ($uri !== '' && !self::is_allowed_media_uri($uri)) {
                return new WP_Error(
                    'google_interactions_invalid_media_uri',
                    __('Google media content requires a valid HTTPS URI.', 'gpt3-ai-content-generator')
                );
            }

            $normalized_item = ['type' => $type, 'mime_type' => $mime_type];
            $normalized_item[$data !== '' ? 'data' : 'uri'] = $data !== '' ? $data : $uri;
            $normalized_content[] = $normalized_item;
        }

        return $normalized_content;
    }

    private static function is_allowed_media_uri(string $uri): bool
    {
        $parts = wp_parse_url($uri);
        if (!is_array($parts)) {
            return false;
        }
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $has_credentials = isset($parts['user']) || isset($parts['pass']);
        $is_local_http = $scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        return ($scheme === 'https' || $is_local_http) && $host !== '' && !$has_credentials;
    }

    /**
     * @param mixed $tools
     * @return array<int, array<string, mixed>>|WP_Error
     */
    private static function normalize_tools($tools, string $model)
    {
        if (!is_array($tools)) {
            return new WP_Error(
                'google_interactions_invalid_tools',
                __('Google interaction tools must be an array.', 'gpt3-ai-content-generator')
            );
        }

        $normalized_tools = [];
        $tool_types = [];
        foreach ($tools as $tool) {
            if (!is_array($tool) || !isset($tool['type']) || !is_string($tool['type']) || trim($tool['type']) === '') {
                return new WP_Error(
                    'google_interactions_invalid_tool',
                    __('Every Google interaction tool requires a type.', 'gpt3-ai-content-generator')
                );
            }

            $tool['type'] = sanitize_key($tool['type']);
            if ($tool['type'] === 'file_search') {
                if (!class_exists(GoogleFileSearchRequestBuilder::class)) {
                    return new WP_Error(
                        'google_interactions_file_search_dependency_missing',
                        __('Google File Search is not available because its provider module did not load.', 'gpt3-ai-content-generator')
                    );
                }
                $tool = GoogleFileSearchRequestBuilder::build_tool(
                    isset($tool['file_search_store_names']) && is_array($tool['file_search_store_names'])
                        ? $tool['file_search_store_names']
                        : [],
                    [
                        'model' => $model,
                        'top_k' => $tool['top_k'] ?? null,
                        'metadata_filter' => $tool['metadata_filter'] ?? '',
                    ]
                );
                if (is_wp_error($tool)) {
                    return $tool;
                }
            }
            $tool_types[$tool['type']] = true;
            $normalized_tools[] = $tool;
        }

        if (
            isset($tool_types['file_search'])
            && (isset($tool_types['google_search']) || isset($tool_types['url_context']))
        ) {
            return new WP_Error(
                'google_interactions_incompatible_grounding_tools',
                __('Google File Search cannot be combined with Google Search or URL Context in one request.', 'gpt3-ai-content-generator')
            );
        }

        return $normalized_tools;
    }
}
