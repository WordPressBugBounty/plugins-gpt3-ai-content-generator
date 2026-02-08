<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/images/providers/openai/OpenAIPayloadFormatter.php
// Status: MODIFIED

namespace WPAICG\Images\Providers\OpenAI;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles formatting request payloads for the OpenAI Image Generation API.
 */
class OpenAIPayloadFormatter
{
    private const OPENAI_EDIT_SUPPORTED_MODELS = ['gpt-image-1.5', 'gpt-image-1', 'gpt-image-1-mini'];
    private const OPENAI_EDIT_ALLOWED_MIME_TYPES = ['image/png', 'image/jpeg', 'image/webp'];

    /**
     * Formats the payload for OpenAI image generation.
     *
     * @param string $prompt The text prompt.
     * @param array  $options Generation options including 'model', 'n', 'size', 'quality', 'style', 'response_format', 'user', etc.
     * @return array The formatted request body data.
     */
    public static function format(string $prompt, array $options): array
    {
        $model = $options['model'] ?? 'dall-e-2'; // Fallback default if not provided in options

        $payload = [
            'model' => $model,
            'prompt' => wp_strip_all_tags($prompt), // Ensure prompt is plain text
        ];

        // Apply options based on the selected model
        $gpt_image_models = ['gpt-image-1.5', 'gpt-image-1', 'gpt-image-1-mini'];
        if (in_array($model, $gpt_image_models, true)) {
            $payload['n'] = 1; // GPT Image models only support n=1
            if (isset($options['size'])) {
                $payload['size'] = $options['size'];
            }

            // GPT Image models use output_format (png, jpeg, webp), not response_format
            if (isset($options['output_format'])) {
                $payload['output_format'] = $options['output_format'];
            } else {
                $payload['output_format'] = 'png';
            } // Default to png if not specified for GPT Image models

            // Additional GPT Image parameters if present in options
            if (isset($options['background'])) {
                $payload['background'] = $options['background'];
            }
            if (isset($options['moderation'])) {
                $payload['moderation'] = $options['moderation'];
            }
            if (isset($options['output_compression'])) {
                $payload['output_compression'] = $options['output_compression'];
            }

        } elseif ($model === 'dall-e-3') {
            $payload['n'] = 1; // DALL-E 3 only supports n=1
            if (isset($options['quality'])) {
                $payload['quality'] = $options['quality'];
            }
            if (isset($options['size'])) {
                $payload['size'] = $options['size'];
            }
            if (isset($options['style'])) {
                $payload['style'] = $options['style'];
            }
            if (isset($options['response_format'])) {
                $payload['response_format'] = $options['response_format'];
            }

        } else { // Defaults for dall-e-2 (or other future models that might use these params)
            $n = isset($options['n']) ? absint($options['n']) : 1;
            $payload['n'] = max(1, min($n, 10)); // DALL-E 2 supports n=1 to 10
            if (isset($options['size'])) {
                $payload['size'] = $options['size'];
            }
            if (isset($options['response_format'])) {
                $payload['response_format'] = $options['response_format'];
            }
        }

        // Common parameter for all models
        if (!empty($options['user'])) {
            $payload['user'] = sanitize_text_field($options['user']);
        }

        return $payload;
    }

    /**
     * Build multipart payload data for OpenAI image edits endpoint.
     *
     * @param string $prompt Prompt text.
     * @param array  $options Runtime options (must include source_image and model for edit flow).
     * @return array|WP_Error {
     *   @type string $body         Raw multipart body.
     *   @type string $content_type Content-Type header including boundary.
     * }
     */
    public static function format_edit_multipart(string $prompt, array $options): array|WP_Error
    {
        $model = isset($options['model']) ? sanitize_text_field((string) $options['model']) : 'gpt-image-1.5';
        if (!self::supports_edit_model($model)) {
            return new WP_Error(
                'openai_edit_model_not_supported',
                __('Selected OpenAI model does not support image editing.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }

        $source_image = $options['source_image'] ?? null;
        if (!is_array($source_image) || empty($source_image['base64_data']) || empty($source_image['mime_type'])) {
            return new WP_Error(
                'openai_edit_missing_source_image',
                __('Source image is required for OpenAI edit mode.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }

        $image_binary = base64_decode((string) $source_image['base64_data'], true);
        if (!is_string($image_binary) || $image_binary === '') {
            return new WP_Error(
                'openai_edit_invalid_source_image',
                __('Invalid source image payload for OpenAI edit mode.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }

        $mime_type = sanitize_text_field((string) $source_image['mime_type']);
        if (!in_array($mime_type, self::OPENAI_EDIT_ALLOWED_MIME_TYPES, true)) {
            return new WP_Error(
                'openai_edit_invalid_source_mime_type',
                __('Selected source image format is not supported for OpenAI edit mode. Allowed: PNG, JPG, WEBP.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }
        $file_name = sanitize_file_name((string) ($source_image['file_name'] ?? 'source-image.png'));
        if ($file_name === '') {
            $file_name = 'source-image.png';
        }

        $fields = [
            'model' => $model,
            'prompt' => wp_strip_all_tags($prompt),
            'n' => '1',
        ];

        if (!empty($options['size'])) {
            $fields['size'] = sanitize_text_field((string) $options['size']);
        }

        // GPT-image models use output_format. Keep png default for stable media save.
        $fields['output_format'] = !empty($options['output_format'])
            ? sanitize_text_field((string) $options['output_format'])
            : 'png';

        if (!empty($options['quality'])) {
            $fields['quality'] = sanitize_text_field((string) $options['quality']);
        }
        if (!empty($options['background'])) {
            $fields['background'] = sanitize_text_field((string) $options['background']);
        }
        if ($model === 'gpt-image-1' && !empty($options['input_fidelity'])) {
            $input_fidelity = sanitize_text_field((string) $options['input_fidelity']);
            if (in_array($input_fidelity, ['low', 'high'], true)) {
                $fields['input_fidelity'] = $input_fidelity;
            }
        }
        if (!empty($options['output_compression'])) {
            $compression = absint($options['output_compression']);
            $fields['output_compression'] = (string) max(0, min($compression, 100));
        }
        if (!empty($options['user'])) {
            $fields['user'] = sanitize_text_field((string) $options['user']);
        }

        $boundary = '----AIPKitFormBoundary' . wp_generate_password(24, false, false);
        $eol = "\r\n";
        $body = '';

        foreach ($fields as $field_name => $field_value) {
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="' . $field_name . '"' . $eol . $eol;
            $body .= $field_value . $eol;
        }

        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="image"; filename="' . $file_name . '"' . $eol;
        $body .= 'Content-Type: ' . $mime_type . $eol . $eol;
        $body .= $image_binary . $eol;
        $body .= '--' . $boundary . '--' . $eol;

        return [
            'body' => $body,
            'content_type' => 'multipart/form-data; boundary=' . $boundary,
        ];
    }

    /**
     * Check whether a model is supported for OpenAI image edit in plugin V1.
     *
     * @param string $model Model ID.
     * @return bool
     */
    public static function supports_edit_model(string $model): bool
    {
        return in_array(strtolower($model), self::OPENAI_EDIT_SUPPORTED_MODELS, true);
    }
}
