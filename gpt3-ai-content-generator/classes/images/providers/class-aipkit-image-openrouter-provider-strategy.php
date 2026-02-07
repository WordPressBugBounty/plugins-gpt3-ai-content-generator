<?php
// File: classes/images/providers/class-aipkit-image-openrouter-provider-strategy.php

namespace WPAICG\Images\Providers;

use WPAICG\Images\AIPKit_Image_Base_Provider_Strategy;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * OpenRouter Image Generation Provider Strategy.
 * Uses OpenRouter Chat Completions with modalities ["image","text"].
 */
class AIPKit_Image_OpenRouter_Provider_Strategy extends AIPKit_Image_Base_Provider_Strategy
{
    /**
     * Build OpenRouter image generation endpoint URL.
     *
     * @param array $api_params Provider api params.
     * @return string|WP_Error
     */
    private function build_api_url(array $api_params): string|WP_Error
    {
        $base_url = isset($api_params['base_url']) ? esc_url_raw((string) $api_params['base_url']) : 'https://openrouter.ai/api';
        $api_version = isset($api_params['api_version']) ? sanitize_text_field((string) $api_params['api_version']) : 'v1';

        if ($base_url === '') {
            return new WP_Error('openrouter_image_missing_base_url', __('OpenRouter Base URL is required.', 'gpt3-ai-content-generator'));
        }
        if ($api_version === '') {
            $api_version = 'v1';
        }

        return rtrim($base_url, '/') . '/' . trim($api_version, '/') . '/chat/completions';
    }

    /**
     * Maps WxH values to aspect ratio accepted by OpenRouter image_config.
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
            $capability_file = WPAICG_PLUGIN_DIR . 'classes/core/providers/openrouter/capabilities.php';
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
     * Parse image blocks from OpenRouter response into storage-friendly format.
     *
     * @param array $decoded_response Decoded OpenRouter response.
     * @return array<int, array<string, mixed>>
     */
    private function parse_images(array $decoded_response): array
    {
        $images = [];
        $choices = isset($decoded_response['choices']) && is_array($decoded_response['choices'])
            ? $decoded_response['choices']
            : [];

        if (empty($choices) || !isset($choices[0]['message']) || !is_array($choices[0]['message'])) {
            return $images;
        }

        $message = $choices[0]['message'];
        $image_blocks = [];

        if (isset($message['images']) && is_array($message['images'])) {
            $image_blocks = $message['images'];
        } elseif (isset($message['content']) && is_array($message['content'])) {
            foreach ($message['content'] as $content_block) {
                if (!is_array($content_block)) {
                    continue;
                }
                $block_type = isset($content_block['type']) ? strtolower((string) $content_block['type']) : '';
                if ($block_type === 'image_url' || $block_type === 'image') {
                    $image_blocks[] = $content_block;
                }
            }
        }

        $revised_prompt = '';
        if (isset($message['content']) && is_string($message['content'])) {
            $revised_prompt = $message['content'];
        }

        foreach ($image_blocks as $image_block) {
            if (!is_array($image_block)) {
                continue;
            }

            $image_url_value = '';
            if (!empty($image_block['image_url']['url']) && is_string($image_block['image_url']['url'])) {
                $image_url_value = $image_block['image_url']['url'];
            } elseif (!empty($image_block['imageUrl']['url']) && is_string($image_block['imageUrl']['url'])) {
                $image_url_value = $image_block['imageUrl']['url'];
            } elseif (!empty($image_block['url']) && is_string($image_block['url'])) {
                $image_url_value = $image_block['url'];
            }

            if ($image_url_value === '') {
                continue;
            }

            $image_item = [
                'url' => null,
                'b64_json' => null,
                'revised_prompt' => $revised_prompt !== '' ? $revised_prompt : null,
            ];

            if (preg_match('#^data:image/[^;]+;base64,#i', $image_url_value) === 1) {
                $base64_data = substr($image_url_value, strpos($image_url_value, ',') + 1);
                if ($base64_data !== '') {
                    $image_item['b64_json'] = $base64_data;
                }
            } else {
                $image_item['url'] = esc_url_raw($image_url_value);
            }

            if ($image_item['url'] === null && $image_item['b64_json'] === null) {
                continue;
            }

            $images[] = $image_item;
        }

        return $images;
    }

    /**
     * Generate image(s) via OpenRouter.
     *
     * @param string $prompt Prompt text.
     * @param array  $api_params Provider API params.
     * @param array  $options Runtime options.
     * @return array|WP_Error
     */
    public function generate_image(string $prompt, array $api_params, array $options = []): array|WP_Error
    {
        $api_key = isset($api_params['api_key']) ? sanitize_text_field((string) $api_params['api_key']) : '';
        $model = isset($options['model']) ? sanitize_text_field((string) $options['model']) : '';
        $clean_prompt = sanitize_textarea_field($prompt);

        if ($api_key === '') {
            return new WP_Error('openrouter_image_missing_key', __('OpenRouter API Key is required for image generation.', 'gpt3-ai-content-generator'));
        }
        if ($model === '') {
            return new WP_Error('openrouter_image_missing_model', __('OpenRouter image model is required.', 'gpt3-ai-content-generator'));
        }
        if ($clean_prompt === '') {
            return new WP_Error('openrouter_image_missing_prompt', __('Prompt cannot be empty for image generation.', 'gpt3-ai-content-generator'));
        }
        if (!$this->model_supports_image_output($model)) {
            return new WP_Error('openrouter_image_model_unsupported', __('Selected OpenRouter model does not support image output.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        $url = $this->build_api_url($api_params);
        if (is_wp_error($url)) {
            return $url;
        }

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $clean_prompt,
                ],
            ],
            'modalities' => ['image', 'text'],
            'stream' => false,
        ];

        if (isset($options['n'])) {
            $num_images = absint($options['n']);
            if ($num_images > 0) {
                $payload['n'] = $num_images;
            }
        }

        $image_config = [];
        if (!empty($options['size']) && is_string($options['size'])) {
            $aspect_ratio = $this->map_size_to_aspect_ratio($options['size']);
            if ($aspect_ratio !== null) {
                $image_config['aspect_ratio'] = $aspect_ratio;
            }
        }
        if (!empty($options['image_size']) && is_string($options['image_size'])) {
            $image_config['image_size'] = sanitize_text_field($options['image_size']);
        }
        if (!empty($image_config)) {
            $payload['image_config'] = $image_config;
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
        $requested_count = isset($payload['n']) ? absint($payload['n']) : 1;
        if ($requested_count > 0 && count($images) > $requested_count) {
            $images = array_slice($images, 0, $requested_count);
        }
        if (empty($images)) {
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
            'X-Title' => get_bloginfo('name'),
        ];
    }
}
