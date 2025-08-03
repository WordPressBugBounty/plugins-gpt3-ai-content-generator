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
            // imagen-3.0-generate-002 and imagen-4.0-generate-preview-06-06 and imagen-4.0-ultra-generate-preview-06-06 
        } elseif ($model_id === 'imagen-3.0-generate-002' || $model_id === 'imagen-4.0-generate-preview-06-06' || $model_id === 'imagen-4.0-ultra-generate-preview-06-06') {
            $parameters = [
                'sampleCount' => $options['n'] ?? 1,
            ];
            $payload = [
                'instances' => [
                    ['prompt' => $prompt]
                ],
                'parameters' => $parameters
            ];
        }

        return $payload;
    }
}