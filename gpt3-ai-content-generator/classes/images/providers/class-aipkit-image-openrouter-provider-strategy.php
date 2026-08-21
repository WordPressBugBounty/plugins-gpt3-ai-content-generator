<?php

namespace WPAICG\Images\Providers;

use WPAICG\Images\AIPKit_Image_Base_Provider_Strategy;
use WPAICG\Utils\AIPKit_Prompt_Sanitizer;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * OpenRouter Image Generation Provider Strategy.
 * Uses OpenRouter's dedicated Image API and its synchronized capability schema.
 */
class AIPKit_Image_OpenRouter_Provider_Strategy extends AIPKit_Image_Base_Provider_Strategy
{
    /**
     * Supported source mime types for OpenRouter edit mode.
     *
     * @var array<int, string>
     */
    private const EDIT_ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /**
     * Raster response formats supported by the plugin's Media Library path.
     *
     * @var array<int, string>
     */
    private const GENERATED_ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /**
     * Build OpenRouter image generation endpoint URL.
     *
     * @param array $api_params Provider api params.
     * @return string|WP_Error
     */
    private function build_api_url(array $api_params)
    {
        // phpcs:ignore PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Provider-specific API transport.
        $base_url = isset($api_params['base_url']) ? esc_url_raw((string) $api_params['base_url']) : 'https://openrouter.ai/api';
        $api_version = isset($api_params['api_version']) ? sanitize_text_field((string) $api_params['api_version']) : 'v1';

        if ($base_url === '') {
            return new WP_Error('openrouter_image_missing_base_url', __('OpenRouter Base URL is required.', 'gpt3-ai-content-generator'));
        }
        if ($api_version === '') {
            $api_version = 'v1';
        }

        $base_url = rtrim($base_url, '/');
        if (strpos($base_url, '/' . trim($api_version, '/')) === false) {
            $base_url .= '/' . trim($api_version, '/');
        }

        return $base_url . '/images';
    }

    /**
     * Maps legacy plugin WxH values to the dedicated Image API aspect ratio.
     *
     * @param string $size Size formatted like "1024x1024".
     * @return string|null
     */
    private function map_size_to_aspect_ratio(string $size): ?string
    {
        $size = strtolower(trim($size));
        if ($size === '') {
            return null;
        }

        $size_map = [
            '1024x1024' => '1:1',
            '1536x1024' => '3:2',
            '1024x1536' => '2:3',
            '1024x768' => '4:3',
            '768x1024' => '3:4',
            '1792x1024' => '16:9',
            '1024x1792' => '9:16',
        ];

        return $size_map[$size] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function get_model_parameter_schema(string $model_id): array
    {
        $model_id = sanitize_text_field($model_id);
        if ($model_id === '') {
            return [];
        }

        $resolver_fn = '\WPAICG\Core\Providers\OpenRouter\Methods\model_image_parameter_schema_logic';
        if (!function_exists($resolver_fn)) {
            $capability_file = WPAICG_PLUGIN_DIR . 'classes/core/providers/openrouter/methods.php';
            if (file_exists($capability_file)) {
                require_once $capability_file;
            }
        }

        if (!function_exists($resolver_fn)) {
            return [];
        }

        $schema = call_user_func($resolver_fn, $model_id);
        return is_array($schema) ? $schema : [];
    }

    /**
     * Return the canonical enum value accepted by a capability descriptor.
     *
     * @param mixed $value Candidate value.
     * @param array<string, mixed> $descriptor Capability descriptor.
     */
    private function normalize_enum_parameter($value, array $descriptor): string
    {
        if (!is_scalar($value)) {
            return '';
        }
        $value = sanitize_text_field((string) $value);
        if ($value === '') {
            return '';
        }

        foreach (($descriptor['values'] ?? []) as $allowed_value) {
            if (is_scalar($allowed_value) && strcasecmp((string) $allowed_value, $value) === 0) {
                return sanitize_text_field((string) $allowed_value);
            }
        }

        return '';
    }

    /**
     * Normalize an integer against an Image Models API range descriptor.
     *
     * @param mixed $value Candidate value.
     * @param array<string, mixed> $descriptor Capability descriptor.
     * @return int|null
     */
    private function normalize_range_parameter($value, array $descriptor): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }
        $value = (int) $value;
        $minimum = isset($descriptor['min']) && is_numeric($descriptor['min']) ? (int) $descriptor['min'] : PHP_INT_MIN;
        $maximum = isset($descriptor['max']) && is_numeric($descriptor['max']) ? (int) $descriptor['max'] : PHP_INT_MAX;
        return max($minimum, min($value, $maximum));
    }

    /**
     * Check if selected OpenRouter model supports image output.
     *
     * @param string $model_id Model id.
     * @return bool
     */
    private function model_supports_image_output(string $model_id): bool
    {
        $model_id = sanitize_text_field($model_id);
        if ($model_id === '') {
            return false;
        }

        $resolver_fn = '\WPAICG\Core\Providers\OpenRouter\Methods\model_supports_image_output_logic';
        if (!function_exists($resolver_fn)) {
            $capability_file = WPAICG_PLUGIN_DIR . 'classes/core/providers/openrouter/methods.php';
            if (file_exists($capability_file)) {
                require_once $capability_file;
            }
        }

        if (!function_exists($resolver_fn)) {
            return true; // Fallback compatibility.
        }

        return (bool) call_user_func($resolver_fn, $model_id);
    }

    /**
     * Check if selected OpenRouter model supports image editing.
     * Image edit requires both image_input and image_output capabilities.
     *
     * @param string $model_id Model id.
     * @return bool
     */
    private function model_supports_image_editing(string $model_id): bool
    {
        $model_id = sanitize_text_field($model_id);
        if ($model_id === '') {
            return false;
        }

        $resolver_fn = '\WPAICG\Core\Providers\OpenRouter\Methods\model_supports_image_editing_logic';
        if (!function_exists($resolver_fn)) {
            $capability_file = WPAICG_PLUGIN_DIR . 'classes/core/providers/openrouter/methods.php';
            if (file_exists($capability_file)) {
                require_once $capability_file;
            }
        }

        if (!function_exists($resolver_fn)) {
            return true; // Fallback compatibility.
        }

        return (bool) call_user_func($resolver_fn, $model_id);
    }

    /**
     * Build one dedicated Image API input reference.
     *
     * @param array  $source_image Source image payload from upload parser.
     * @return array|WP_Error
     */
    private function build_input_reference(array $source_image)
    {
        $mime_type = isset($source_image['mime_type']) ? strtolower(sanitize_text_field((string) $source_image['mime_type'])) : '';
        if ($mime_type === '' || !in_array($mime_type, self::EDIT_ALLOWED_MIME_TYPES, true)) {
            return new WP_Error(
                'openrouter_image_edit_invalid_mime_type',
                __('Selected source image format is not supported for OpenRouter edit mode. Allowed: PNG, JPG, WEBP, GIF.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }

        $base64_data = isset($source_image['base64_data']) ? (string) $source_image['base64_data'] : '';
        if ($base64_data === '') {
            return new WP_Error(
                'openrouter_image_edit_missing_source_data',
                __('Source image is required for OpenRouter edit mode.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }

        $decoded_binary = base64_decode($base64_data, true);
        if (!is_string($decoded_binary) || $decoded_binary === '') {
            return new WP_Error(
                'openrouter_image_edit_invalid_source_data',
                __('Invalid source image payload for OpenRouter edit mode.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }

        return [
            'type' => 'image_url',
            'image_url' => [
                'url' => 'data:' . $mime_type . ';base64,' . $base64_data,
            ],
        ];
    }

    /**
     * Parse image blocks from OpenRouter response into storage-friendly format.
     *
     * @param array $decoded_response Decoded OpenRouter response.
     * @return array<int, array<string, mixed>>
     */
    private function parse_images(array $decoded_response): array
    {
        $images = [];
        $image_blocks = isset($decoded_response['data']) && is_array($decoded_response['data'])
            ? $decoded_response['data']
            : [];

        foreach ($image_blocks as $image_block) {
            if (!is_array($image_block)) {
                continue;
            }

            $base64_data = isset($image_block['b64_json']) && is_string($image_block['b64_json'])
                ? trim($image_block['b64_json'])
                : '';
            if ($base64_data === '' || base64_decode($base64_data, true) === false) {
                continue;
            }

            $mime_type = isset($image_block['media_type']) && is_string($image_block['media_type'])
                ? strtolower(sanitize_text_field($image_block['media_type']))
                : '';
            if ($mime_type === '') {
                $mime_type = 'image/png';
            }
            if (!in_array($mime_type, self::GENERATED_ALLOWED_MIME_TYPES, true)) {
                continue;
            }

            $images[] = [
                'url' => null,
                'b64_json' => $base64_data,
                'mime_type' => $mime_type,
                'revised_prompt' => null,
            ];
        }

        return $images;
    }

    /**
     * Find non-raster media types in otherwise valid response image blocks.
     *
     * @param array $decoded_response Decoded OpenRouter response.
     * @return array<int, string>
     */
    private function get_unsupported_response_media_types(array $decoded_response): array
    {
        $unsupported = [];
        $image_blocks = isset($decoded_response['data']) && is_array($decoded_response['data'])
            ? $decoded_response['data']
            : [];

        foreach ($image_blocks as $image_block) {
            if (!is_array($image_block) || empty($image_block['b64_json']) || !is_string($image_block['b64_json'])) {
                continue;
            }
            if (base64_decode(trim($image_block['b64_json']), true) === false) {
                continue;
            }

            $mime_type = isset($image_block['media_type']) && is_string($image_block['media_type'])
                ? strtolower(sanitize_text_field($image_block['media_type']))
                : 'image/png';
            if ($mime_type !== '' && !in_array($mime_type, self::GENERATED_ALLOWED_MIME_TYPES, true)) {
                $unsupported[] = $mime_type;
            }
        }

        return array_values(array_unique($unsupported));
    }

    /**
     * Build a dedicated Image API payload from normalized plugin options.
     *
     * A non-empty synchronized schema is authoritative. When no dedicated
     * schema has been synchronized yet, conservative documented fallbacks keep
     * existing installations functional until their next model sync.
     *
     * @param string $prompt Sanitized prompt.
     * @param string $model Model id.
     * @param string $image_mode Generate or edit.
     * @param array<string, mixed> $options Runtime options.
     * @param array<string, array<string, mixed>> $parameter_schema Model capability descriptors.
     * @return array<string, mixed>|WP_Error
     */
    private function build_image_payload(string $prompt, string $model, string $image_mode, array $options, array $parameter_schema)
    {
        $payload = [
            'model' => $model,
            'prompt' => $prompt,
        ];
        $image_routing_fn = '\\WPAICG\\Core\\Providers\\OpenRouter\\Methods\\get_saved_image_routing_preferences_logic';
        if (function_exists($image_routing_fn)) {
            $provider_preferences = call_user_func($image_routing_fn);
            if (is_array($provider_preferences) && !empty($provider_preferences)) {
                $payload['provider'] = $provider_preferences;
            }
        }
        $has_authoritative_schema = !empty($parameter_schema);
        $supports_parameter = static function (string $parameter) use ($parameter_schema, $has_authoritative_schema): bool {
            return !$has_authoritative_schema || isset($parameter_schema[$parameter]);
        };

        if ($image_mode === 'edit') {
            if (!$supports_parameter('input_references')) {
                return new WP_Error(
                    'openrouter_image_edit_model_unsupported',
                    __('Selected OpenRouter model does not support image editing.', 'gpt3-ai-content-generator'),
                    ['status' => 400]
                );
            }
            $source_image = isset($options['source_image']) && is_array($options['source_image'])
                ? $options['source_image']
                : null;
            if (!is_array($source_image)) {
                return new WP_Error(
                    'openrouter_image_edit_missing_source',
                    __('Source image is required for OpenRouter edit mode.', 'gpt3-ai-content-generator'),
                    ['status' => 400]
                );
            }
            $input_reference = $this->build_input_reference($source_image);
            if (is_wp_error($input_reference)) {
                return $input_reference;
            }
            $payload['input_references'] = [$input_reference];
        }

        $requested_count = isset($options['n']) ? max(1, min(absint($options['n']), 10)) : 1;
        if ($requested_count > 1 && $supports_parameter('n')) {
            $count_descriptor = isset($parameter_schema['n']) && is_array($parameter_schema['n'])
                ? $parameter_schema['n']
                : ['type' => 'range', 'min' => 1, 'max' => 10];
            $normalized_count = $this->normalize_range_parameter($requested_count, $count_descriptor);
            if ($normalized_count !== null && $normalized_count > 1) {
                $payload['n'] = $normalized_count;
            }
        }

        $fallback_enums = [
            'aspect_ratio' => ['auto', '1:1', '1:2', '2:1', '2:3', '3:2', '3:4', '4:3', '4:5', '5:4', '9:16', '16:9', '9:19.5', '19.5:9', '9:20', '20:9', '9:21', '21:9', '1:4', '4:1', '1:8', '8:1'],
            'resolution' => ['512', '1K', '2K', '4K'],
            'quality' => ['auto', 'low', 'medium', 'high'],
            'output_format' => ['png', 'jpeg', 'webp'],
            'background' => ['auto', 'transparent', 'opaque'],
        ];
        $normalize_enum = function (string $parameter, $value) use ($parameter_schema, $fallback_enums): string {
            $descriptor = isset($parameter_schema[$parameter]) && is_array($parameter_schema[$parameter])
                ? $parameter_schema[$parameter]
                : ['type' => 'enum', 'values' => $fallback_enums[$parameter] ?? []];
            return $this->normalize_enum_parameter($value, $descriptor);
        };

        $aspect_ratio = isset($options['aspect_ratio']) ? $options['aspect_ratio'] : '';
        if ($aspect_ratio === '' && !empty($options['size']) && is_string($options['size'])) {
            $aspect_ratio = $this->map_size_to_aspect_ratio($options['size']) ?? '';
        }
        if ($aspect_ratio !== '' && $supports_parameter('aspect_ratio')) {
            $aspect_ratio = $normalize_enum('aspect_ratio', $aspect_ratio);
            if ($aspect_ratio !== '') {
                $payload['aspect_ratio'] = $aspect_ratio;
            }
        }

        $resolution = $options['resolution'] ?? ($options['image_size'] ?? '');
        if (is_string($resolution) && strtolower(trim($resolution)) === '0.5k') {
            $resolution = '512';
        }
        if ($resolution !== '' && $supports_parameter('resolution')) {
            $resolution = $normalize_enum('resolution', $resolution);
            if ($resolution !== '') {
                $payload['resolution'] = $resolution;
            }
        }

        if (!isset($payload['aspect_ratio']) && $supports_parameter('size') && !empty($options['size']) && is_string($options['size'])) {
            $size = strtolower(sanitize_text_field($options['size']));
            if (preg_match('/^(?:512|1k|2k|4k|[1-9][0-9]{2,4}x[1-9][0-9]{2,4})$/i', $size) === 1) {
                $payload['size'] = strtoupper($size);
            }
        }

        foreach (['quality', 'output_format', 'background'] as $parameter) {
            if (!$supports_parameter($parameter) || empty($options[$parameter])) {
                continue;
            }
            if (
                $parameter === 'output_format'
                && !in_array(strtolower((string) $options[$parameter]), ['png', 'jpeg', 'jpg', 'webp'], true)
            ) {
                continue;
            }
            $normalized_value = $normalize_enum($parameter, $options[$parameter]);
            if ($normalized_value !== '') {
                $payload[$parameter] = $normalized_value;
            }
        }

        if (
            $supports_parameter('output_compression')
            && isset($options['output_compression'])
            && $options['output_compression'] !== ''
            && (!isset($payload['output_format']) || in_array($payload['output_format'], ['jpeg', 'webp'], true))
        ) {
            $compression_descriptor = isset($parameter_schema['output_compression']) && is_array($parameter_schema['output_compression'])
                ? $parameter_schema['output_compression']
                : ['type' => 'range', 'min' => 0, 'max' => 100];
            $compression = $this->normalize_range_parameter($options['output_compression'], $compression_descriptor);
            if ($compression !== null) {
                $payload['output_compression'] = $compression;
            }
        }

        if ($supports_parameter('seed') && isset($options['seed']) && is_numeric($options['seed'])) {
            $payload['seed'] = max(0, (int) $options['seed']);
        }

        return $payload;
    }

    /**
     * Generate image(s) via OpenRouter.
     *
     * @param string $prompt Prompt text.
     * @param array  $api_params Provider API params.
     * @param array  $options Runtime options.
     * @return array|WP_Error
     */
    public function generate_image(string $prompt, array $api_params, array $options = [])
    {
        $api_key = isset($api_params['api_key']) ? sanitize_text_field((string) $api_params['api_key']) : '';
        $model = isset($options['model']) ? sanitize_text_field((string) $options['model']) : '';
        $clean_prompt = AIPKit_Prompt_Sanitizer::sanitize($prompt);
        $image_mode = isset($options['image_mode']) && $options['image_mode'] === 'edit' ? 'edit' : 'generate';

        if ($api_key === '') {
            return new WP_Error('openrouter_image_missing_key', __('OpenRouter API Key is required for image generation.', 'gpt3-ai-content-generator'));
        }
        if ($model === '') {
            return new WP_Error('openrouter_image_missing_model', __('OpenRouter image model is required.', 'gpt3-ai-content-generator'));
        }
        if ($clean_prompt === '') {
            return new WP_Error('openrouter_image_missing_prompt', __('Prompt cannot be empty for image generation.', 'gpt3-ai-content-generator'));
        }
        if ($image_mode === 'edit') {
            if (!$this->model_supports_image_editing($model)) {
                return new WP_Error(
                    'openrouter_image_edit_model_unsupported',
                    __('Selected OpenRouter model does not support image editing.', 'gpt3-ai-content-generator'),
                    ['status' => 400]
                );
            }
        } elseif (!$this->model_supports_image_output($model)) {
            return new WP_Error('openrouter_image_model_unsupported', __('Selected OpenRouter model does not support image output.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        $url = $this->build_api_url($api_params);
        if (is_wp_error($url)) {
            return $url;
        }

        $payload = $this->build_image_payload(
            $clean_prompt,
            $model,
            $image_mode,
            $options,
            $this->get_model_parameter_schema($model)
        );
        if (is_wp_error($payload)) {
            return $payload;
        }

        $headers = $this->get_api_headers($api_key, 'generate');
        $request_options = $this->get_request_options('generate');
        $request_args = array_merge($request_options, [
            'headers' => $headers,
            'body' => wp_json_encode($payload),
            'data_format' => 'body',
        ]);

        $response = wp_remote_post($url, $request_args);
        if (is_wp_error($response)) {
            return new WP_Error('openrouter_image_http_error', __('HTTP error during OpenRouter image generation.', 'gpt3-ai-content-generator'));
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded_response = $this->decode_json($body, 'OpenRouter Image Generation');
        if ($status_code !== 200 || is_wp_error($decoded_response)) {
            $error_message = is_wp_error($decoded_response)
                ? $decoded_response->get_error_message()
                : $this->parse_error_response($body, $status_code, 'OpenRouter Image');
            /* translators: %1$d: HTTP status code, %2$s: error message */
            return new WP_Error('openrouter_image_api_error', sprintf(__('OpenRouter Image API Error (%1$d): %2$s', 'gpt3-ai-content-generator'), $status_code, $error_message));
        }

        $images = $this->parse_images($decoded_response);
        $requested_count = isset($options['n']) ? max(1, min(absint($options['n']), 10)) : 1;
        if ($requested_count > 0 && count($images) > $requested_count) {
            $images = array_slice($images, 0, $requested_count);
        }
        if (empty($images)) {
            $unsupported_media_types = $this->get_unsupported_response_media_types($decoded_response);
            if (!empty($unsupported_media_types)) {
                return new WP_Error(
                    'openrouter_image_unsupported_media_type',
                    __('OpenRouter returned a non-raster image that cannot be saved safely to the WordPress Media Library. Select a raster-capable model or PNG, JPEG, or WEBP output.', 'gpt3-ai-content-generator'),
                    ['status' => 400, 'media_types' => $unsupported_media_types]
                );
            }
            return new WP_Error('openrouter_image_no_data', __('OpenRouter API returned success but no image data was found.', 'gpt3-ai-content-generator'));
        }

        $usage_data = null;
        if (isset($decoded_response['usage']) && is_array($decoded_response['usage'])) {
            $prompt_tokens = absint($decoded_response['usage']['prompt_tokens'] ?? 0);
            $completion_tokens = absint($decoded_response['usage']['completion_tokens'] ?? 0);
            $total_tokens = absint($decoded_response['usage']['total_tokens'] ?? ($prompt_tokens + $completion_tokens));
            $usage_data = [
                'input_tokens' => $prompt_tokens,
                'output_tokens' => $completion_tokens,
                'total_tokens' => $total_tokens,
                'provider_raw' => $decoded_response['usage'],
            ];
            if (isset($decoded_response['usage']['cost']) && is_numeric($decoded_response['usage']['cost'])) {
                $usage_data['cost'] = (float) $decoded_response['usage']['cost'];
            }
        }

        return [
            'images' => $images,
            'usage' => $usage_data,
        ];
    }

    /**
     * OpenRouter image sizes vary by model. Keep empty to avoid invalid hardcoded constraints.
     *
     * @return array
     */
    public function get_supported_sizes(): array
    {
        return [];
    }

    /**
     * Dedicated image generation can legitimately exceed the shared 120 second
     * image timeout for high-resolution models.
     *
     * @param string $operation Operation name.
     * @return array
     */
    public function get_request_options(string $operation): array
    {
        $options = parent::get_request_options($operation);
        if ($operation === 'generate') {
            $options['timeout'] = 180;
        }
        return $options;
    }

    /**
     * OpenRouter request headers.
     *
     * @param string $api_key API key.
     * @param string $operation Operation name.
     * @return array
     */
    public function get_api_headers(string $api_key, string $operation): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
            'HTTP-Referer' => get_bloginfo('url'),
            'X-OpenRouter-Title' => get_bloginfo('name'),
        ];
    }
}
