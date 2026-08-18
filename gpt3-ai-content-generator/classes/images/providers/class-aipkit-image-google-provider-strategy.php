<?php

namespace WPAICG\Images\Providers;

use WPAICG\AIPKit_Providers;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsClient;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsImageAdapter;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsResponseParser;
use WPAICG\Images\AIPKit_Image_Base_Provider_Strategy;
use WPAICG\Images\Providers\Google\GoogleVideoPayloadFormatter;
use WPAICG\Images\Providers\Google\GoogleVideoResponseParser;
use WPAICG\Images\Providers\Google\GoogleVideoUrlBuilder;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Google media strategy: Interactions for Gemini images and the specialized
 * long-running API for Veo videos.
 */
class AIPKit_Image_Google_Provider_Strategy extends AIPKit_Image_Base_Provider_Strategy
{
    /**
     * @return array<string, mixed>|WP_Error
     */
    public function generate_image(string $prompt, array $api_params, array $options = [])
    {
        $api_key = isset($api_params['api_key']) ? trim((string) $api_params['api_key']) : '';
        $model_id = isset($options['model']) ? trim((string) $options['model']) : '';
        if ($api_key === '') {
            return new WP_Error('google_missing_key', __('Google API Key is required for generation.', 'gpt3-ai-content-generator'));
        }
        if ($model_id === '') {
            return new WP_Error('google_missing_model', __('Google model ID is required.', 'gpt3-ai-content-generator'));
        }
        if (trim($prompt) === '') {
            return new WP_Error('google_missing_prompt', __('Prompt cannot be empty for generation.', 'gpt3-ai-content-generator'));
        }

        // Veo remains on its specialized asynchronous operation API.
        if ($this->is_video_model($model_id)) {
            return $this->generate_video($prompt, $api_params, $options);
        }

        // Transparently converts removed Imagen IDs and unknown stale IDs to
        // the current default, without requiring users to resave settings.
        $model_id = AIPKit_Providers::normalize_google_image_model($model_id);
        $options['model'] = $model_id;
        $image_mode = ($options['image_mode'] ?? 'generate') === 'edit' ? 'edit' : 'generate';
        if ($image_mode === 'edit' && !$this->supports_image_editing_model($model_id)) {
            return new WP_Error(
                'google_model_not_supported_for_edit',
                __('Selected Google model does not support image editing.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }

        $request = GoogleInteractionsImageAdapter::build($prompt, $model_id, $options);
        if (is_wp_error($request)) {
            return $request;
        }

        $connection = [
            'api_key' => $api_key,
            'base_url' => $api_params['base_url'] ?? '',
            'api_version' => 'v1beta',
            'timeout' => 180,
        ];
        $image_count = max(1, min(4, (int) ($options['n'] ?? 1)));
        $images = [];
        $usage = self::empty_usage();
        $has_usage = false;
        $client = new GoogleInteractionsClient();

        for ($index = 0; $index < $image_count; $index++) {
            $result = $client->create($connection, $model_id, $request['input'], $request['options']);
            if (is_wp_error($result)) {
                return $result;
            }
            $result = GoogleInteractionsResponseParser::require_image_result($result);
            if (is_wp_error($result)) {
                return $result;
            }

            foreach ($result['image_outputs'] as $image_output) {
                $images[] = [
                    'b64_json' => (string) $image_output['data'],
                    'mime_type' => isset($image_output['mime_type'])
                        ? sanitize_mime_type((string) $image_output['mime_type'])
                        : 'image/png',
                ];
            }
            if (!empty($result['usage']) && is_array($result['usage'])) {
                $has_usage = true;
                self::merge_usage($usage, $result['usage']);
            }
        }

        return [
            'images' => $images,
            'usage' => $has_usage ? $usage : null,
        ];
    }

    private function is_video_model(string $model_id): bool
    {
        $video_models = AIPKit_Providers::get_google_video_models();
        foreach ($video_models as $model) {
            $candidate = is_array($model) ? (string) ($model['id'] ?? '') : (string) $model;
            if ($candidate !== '' && $candidate === $model_id) {
                return true;
            }
        }

        return strpos(strtolower($model_id), 'veo') !== false;
    }

    private function supports_image_editing_model(string $model_id): bool
    {
        return AIPKit_Providers::is_supported_google_image_model($model_id);
    }

    /**
     * @return array<string, mixed>|WP_Error
     */
    private function generate_video(string $prompt, array $api_params, array $options)
    {
        $model_id = $options['model'] ?? null;
        if (!class_exists(GoogleVideoUrlBuilder::class) || !class_exists(GoogleVideoPayloadFormatter::class) || !class_exists(GoogleVideoResponseParser::class)) {
            return new WP_Error('google_video_dependency_missing', __('Google video generation components are missing.', 'gpt3-ai-content-generator'), ['status' => 500]);
        }

        $url = GoogleVideoUrlBuilder::build($model_id, $api_params, 'generate');
        if (is_wp_error($url)) {
            return $url;
        }
        $payload = GoogleVideoPayloadFormatter::format($prompt, $options);
        if (empty($payload)) {
            return new WP_Error('google_video_payload_error', __('Failed to format payload for Google video model: ', 'gpt3-ai-content-generator') . $model_id);
        }

        $request_args = array_merge($this->get_request_options('generate'), [
            'headers' => $this->get_api_headers($api_params['api_key'] ?? '', 'generate'),
            'body' => wp_json_encode($payload),
            'data_format' => 'body',
            'timeout' => 120,
        ]);
        $response = wp_remote_post($url, $request_args);
        if (is_wp_error($response)) {
            return new WP_Error('google_video_http_error', __('HTTP error during Google video generation.', 'gpt3-ai-content-generator'));
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded_response = $this->decode_json($body, 'Google Video Generation');
        if ($status_code !== 200 || is_wp_error($decoded_response)) {
            $error_msg = is_wp_error($decoded_response)
                ? $decoded_response->get_error_message()
                : GoogleVideoResponseParser::parse_error($body, $status_code);
            return new WP_Error(
                'google_video_api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message from the API. */
                    __('Google Video API Error (%1$d): %2$s', 'gpt3-ai-content-generator'),
                    $status_code,
                    $error_msg
                )
            );
        }

        $parse_result = GoogleVideoResponseParser::parse($decoded_response, $model_id, $api_params);
        if (is_wp_error($parse_result)) {
            return $parse_result;
        }
        if (($parse_result['status'] ?? '') === 'processing') {
            return [
                'status' => 'processing',
                'operation_name' => $parse_result['operation_name'],
                'message' => $parse_result['message'],
            ];
        }

        return $parse_result;
    }

    public function get_supported_sizes(): array
    {
        return ['1024x1024', '1536x1024', '1024x1536', '1024x768', '768x1024'];
    }

    /**
     * Veo uses API-key query authentication; image requests use Interactions headers.
     */
    public function get_api_headers(string $api_key, string $operation): array
    {
        return ['Content-Type' => 'application/json'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function empty_usage(): array
    {
        return [
            'input_tokens' => 0,
            'output_tokens' => 0,
            'total_tokens' => 0,
            'cached_tokens' => 0,
            'thought_tokens' => 0,
            'tool_use_tokens' => 0,
            'provider_raw' => [],
        ];
    }

    /**
     * @param array<string, mixed> $target
     * @param array<string, mixed> $incoming
     */
    private static function merge_usage(array &$target, array $incoming): void
    {
        foreach (['input_tokens', 'output_tokens', 'total_tokens', 'cached_tokens', 'thought_tokens', 'tool_use_tokens'] as $key) {
            $target[$key] += (int) ($incoming[$key] ?? 0);
        }
        if (isset($incoming['provider_raw'])) {
            $target['provider_raw'][] = $incoming['provider_raw'];
        }
    }
}
