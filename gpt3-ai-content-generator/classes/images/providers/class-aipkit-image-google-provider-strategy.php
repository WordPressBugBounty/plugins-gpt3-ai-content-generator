<?php
// File: classes/images/providers/class-aipkit-image-google-provider-strategy.php
// REVISED FILE

namespace WPAICG\Images\Providers;

use WPAICG\Images\AIPKit_Image_Base_Provider_Strategy;
use WPAICG\Images\Providers\Google\GoogleImageUrlBuilder;
use WPAICG\Images\Providers\Google\GoogleImagePayloadFormatter;
use WPAICG\Images\Providers\Google\GoogleImageResponseParser;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Google Image Generation Provider Strategy.
 * Supports Gemini Flash and Imagen 3 models.
 */
class AIPKit_Image_Google_Provider_Strategy extends AIPKit_Image_Base_Provider_Strategy {

    public function __construct() {
        $google_image_dir = __DIR__ . '/google/';
        if (!class_exists(GoogleImageUrlBuilder::class) && file_exists($google_image_dir . 'GoogleImageUrlBuilder.php')) {
             require_once $google_image_dir . 'GoogleImageUrlBuilder.php';
        }
        if (!class_exists(GoogleImagePayloadFormatter::class) && file_exists($google_image_dir . 'GoogleImagePayloadFormatter.php')) {
             require_once $google_image_dir . 'GoogleImagePayloadFormatter.php';
        }
        if (!class_exists(GoogleImageResponseParser::class) && file_exists($google_image_dir . 'GoogleImageResponseParser.php')) {
            require_once $google_image_dir . 'GoogleImageResponseParser.php';
        }
    }

    /**
     * Generate an image based on a text prompt using Google's services.
     *
     * @param string $prompt The text prompt describing the image.
     * @param array $api_params API connection parameters. Must include 'api_key'.
     *                          Optional: 'base_url', 'api_version'.
     * @param array $options Generation options. Must include 'model'.
     *                       Optional: 'n', 'size' (interpreted based on model).
     * @return array|WP_Error Array containing 'images' and 'usage' (usage is null for Google Images) or WP_Error on failure.
     */
    public function generate_image(string $prompt, array $api_params, array $options = []): array|WP_Error {
        $api_key = $api_params['api_key'] ?? null;
        $model_id = $options['model'] ?? null; // Full model ID like 'gemini-2.0-flash-preview-image-generation'

        if (empty($api_key)) return new WP_Error('google_image_missing_key', __('Google API Key is required for image generation.', 'gpt3-ai-content-generator'));
        if (empty($model_id)) return new WP_Error('google_image_missing_model', __('Google image model ID is required.', 'gpt3-ai-content-generator'));
        if (empty($prompt)) return new WP_Error('google_image_missing_prompt', __('Prompt cannot be empty for image generation.', 'gpt3-ai-content-generator'));

        // Ensure component classes are loaded (they should be by constructor, but defensive check)
        if (!class_exists(GoogleImageUrlBuilder::class) || !class_exists(GoogleImagePayloadFormatter::class) || !class_exists(GoogleImageResponseParser::class)) {
            return new WP_Error('google_image_dependency_missing', __('Google image generation components are missing.', 'gpt3-ai-content-generator'), ['status' => 500]);
        }

        // Pass the full model ID to the URL builder
        $url = GoogleImageUrlBuilder::build($model_id, $api_params);
        if (is_wp_error($url)) return $url;

        // Options already contain the model ID which the formatter will use to switch logic
        $payload = GoogleImagePayloadFormatter::format($prompt, $options);
        if (empty($payload)) { // Formatter might return empty for unsupported models
            return new WP_Error('google_image_payload_error', __('Failed to format payload for Google image model: ', 'gpt3-ai-content-generator') . $model_id);
        }

        $headers_array = $this->get_api_headers($api_key, 'generate');
        $request_options_base = $this->get_request_options('generate');
        $request_body_json = wp_json_encode($payload);

        $request_args = array_merge($request_options_base, [
            'headers' => $headers_array,
            'body' => $request_body_json,
            'data_format' => 'body', // wp_remote_request handles JSON encoding if body is array
        ]);

        $response = wp_remote_post($url, $request_args);

        if (is_wp_error($response)) {
            return new WP_Error('google_image_http_error', __('HTTP error during Google image generation.', 'gpt3-ai-content-generator'));
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        $decoded_response = $this->decode_json($body, 'Google Image Generation');

        if ($status_code !== 200 || is_wp_error($decoded_response)) {
            $error_msg = is_wp_error($decoded_response)
                        ? $decoded_response->get_error_message()
                        : GoogleImageResponseParser::parse_error($body, $status_code);
            /* translators: %1$d: HTTP status code, %2$s: Error message from the API. */
            return new WP_Error('google_image_api_error', sprintf(__('Google Image API Error (%1$d): %2$s', 'gpt3-ai-content-generator'), $status_code, $error_msg));
        }

        return GoogleImageResponseParser::parse($decoded_response, $model_id);
    }

    /**
     * Get the supported image sizes (Placeholder - needs specific model logic).
     */
    public function get_supported_sizes(): array {
        // For shortcode UI, a common list. Strategy should validate/adapt.
        return ['1024x1024', '1536x1024', '1024x1536', '1024x768', '768x1024'];
    }

    /**
     * Get API headers (Google API key is in URL).
     */
    public function get_api_headers(string $api_key, string $operation): array {
         return ['Content-Type' => 'application/json'];
    }
}