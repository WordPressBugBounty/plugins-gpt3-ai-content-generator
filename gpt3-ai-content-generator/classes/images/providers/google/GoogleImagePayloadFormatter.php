<?php

namespace WPAICG\Images\Providers\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles formatting request payloads for Google Image Generation models.
 */
class GoogleImagePayloadFormatter {
    /**
     * Build Gemini parts payload for prompt-only or edit-mode requests.
     *
     * @param string $prompt User prompt text.
     * @param array  $options Request options.
     * @return array<int, array<string, mixed>>
     */
    public static function build_gemini_parts(string $prompt, array $options): array {
        $image_mode = isset($options['image_mode']) && $options['image_mode'] === 'edit' ? 'edit' : 'generate';
        $parts = [['text' => $prompt]];

        $source_image = isset($options['source_image']) && is_array($options['source_image'])
            ? $options['source_image']
            : null;

        if (
            $image_mode === 'edit' &&
            is_array($source_image) &&
            !empty($source_image['mime_type']) &&
            !empty($source_image['base64_data'])
        ) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => sanitize_text_field((string) $source_image['mime_type']),
                    'data' => preg_replace('/\s+/', '', (string) $source_image['base64_data']),
                ],
            ];
        }

        return $parts;
    }

    /**
     * Formats the payload for Google Image Generation API.
     *
     * @param string $prompt The text prompt.
     * @param array  $options Generation options including 'model' (full ID), 'n', 'size', etc.
     * @return array The formatted request body data.
     */
    public static function format(string $prompt, array $options): array {
        $model_id = $options['model'] ?? '';
        $payload = [];

        $n = isset($options['n']) ? max(1, (int)$options['n']) : 1;

        // Gemini image-generation models use the text+image modality on generateContent
        if (strpos($model_id, 'gemini') !== false && strpos($model_id, 'image-generation') !== false) {
            $parts = self::build_gemini_parts($prompt, $options);

            $payload = [
                'contents' => [[
                    'parts' => $parts,
                ]],
                'generationConfig' => [
                    'responseModalities' => ['TEXT', 'IMAGE'],
                ],
            ];
        }
        // Imagen models use the :predict endpoint with instances/parameters
        elseif (strpos($model_id, 'imagen') !== false) {
            $parameters = [ 'sampleCount' => $n ];
            $payload = [
                'instances' => [ ['prompt' => $prompt] ],
                'parameters' => $parameters,
            ];
        }

        return $payload;
    }
}
