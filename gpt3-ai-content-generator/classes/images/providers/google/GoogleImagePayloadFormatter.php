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
     * Formats the payload for Google Image Generation API.
     *
     * @param string $prompt The text prompt.
     * @param array  $options Generation options including 'model' (full ID), 'n', 'size', etc.
     * @return array The formatted request body data.
     */
    public static function format(string $prompt, array $options): array {
        $model_id = $options['model'] ?? '';
        $payload = [];

        if ($model_id === 'gemini-2.0-flash-preview-image-generation') {
            $payload = [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]],
                // This model also requires explicit generationConfig for image output
                'generationConfig' => [
                    // *** UPDATED: Request both TEXT and IMAGE modalities as per Google's example ***
                    'responseModalities' => ['TEXT', 'IMAGE'],
                ]
            ];
        } elseif ($model_id === 'imagen-3.0-generate-002') {
            $parameters = [
                'sampleCount' => isset($options['n']) ? max(1, min(intval($options['n']), 4)) : 1,
            ];
            $payload = [
                'instances' => [
                    ['prompt' => $prompt]
                ],
                'parameters' => $parameters
            ];
        } else {
            error_log("AIPKit Google Image Payload: Unsupported model ID for formatting: {$model_id}");
        }

        return $payload;
    }
}