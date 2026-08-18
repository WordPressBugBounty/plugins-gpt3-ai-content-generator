<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsImageAdapter
{
    /**
     * Translate AI Puffer image options into one Interactions request.
     *
     * @param array<string, mixed> $options Image generation options.
     * @return array{input:string|array<mixed>,options:array<string,mixed>}|WP_Error
     */
    public static function build(string $prompt, string $model, array $options)
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            return new WP_Error(
                'google_interactions_image_missing_prompt',
                __('A prompt is required for Google image generation.', 'gpt3-ai-content-generator')
            );
        }

        $input = $prompt;
        if (($options['image_mode'] ?? 'generate') === 'edit') {
            $source = isset($options['source_image']) && is_array($options['source_image'])
                ? $options['source_image']
                : [];
            $mime_type = isset($source['mime_type']) && is_string($source['mime_type'])
                ? sanitize_mime_type($source['mime_type'])
                : '';
            $data = isset($source['base64_data']) && is_string($source['base64_data'])
                ? preg_replace('/\s+/', '', $source['base64_data'])
                : '';
            if (
                $mime_type === ''
                || strpos($mime_type, 'image/') !== 0
                || $data === ''
                || base64_decode($data, true) === false
            ) {
                return new WP_Error(
                    'google_interactions_image_missing_source',
                    __('A valid source image is required for Google image editing.', 'gpt3-ai-content-generator')
                );
            }
            $input = [
                ['type' => 'text', 'text' => $prompt],
                ['type' => 'image', 'mime_type' => $mime_type, 'data' => $data],
            ];
        }

        $response_format = ['type' => 'image'];
        $aspect_ratio = isset($options['aspect_ratio']) && is_string($options['aspect_ratio'])
            ? trim($options['aspect_ratio'])
            : '';
        if ($aspect_ratio !== '' && self::supports_aspect_ratio($model, $aspect_ratio)) {
            $response_format['aspect_ratio'] = $aspect_ratio;
        }
        $image_size = isset($options['image_size']) && is_string($options['image_size'])
            ? strtoupper(trim($options['image_size']))
            : '';
        if ($image_size !== '' && self::supports_image_size($model, $image_size)) {
            $response_format['image_size'] = $image_size;
        }

        return [
            'input' => $input,
            'options' => [
                'store' => false,
                'response_format' => $response_format,
            ],
        ];
    }

    private static function supports_aspect_ratio(string $model, string $aspect_ratio): bool
    {
        $common = ['1:1', '2:3', '3:2', '3:4', '4:3', '4:5', '5:4', '9:16', '16:9', '21:9'];
        $extended = ['1:4', '1:8', '4:1', '8:1'];
        $allowed = strpos(strtolower($model), 'gemini-3.1-flash-image') !== false
            && strpos(strtolower($model), 'flash-lite') === false
            ? array_merge($common, $extended)
            : $common;

        return in_array($aspect_ratio, $allowed, true);
    }

    private static function supports_image_size(string $model, string $image_size): bool
    {
        $model = strtolower($model);
        if (strpos($model, 'gemini-3.1-flash-lite-image') !== false) {
            return $image_size === '1K';
        }
        if (strpos($model, 'gemini-3.1-flash-image') !== false) {
            return in_array($image_size, ['512', '1K', '2K', '4K'], true);
        }
        if (strpos($model, 'gemini-3-pro-image') !== false) {
            return in_array($image_size, ['1K', '2K', '4K'], true);
        }

        return false;
    }
}
