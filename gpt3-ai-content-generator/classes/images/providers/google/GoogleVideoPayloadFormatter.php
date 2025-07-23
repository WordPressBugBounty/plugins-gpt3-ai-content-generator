<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/images/providers/google/GoogleVideoPayloadFormatter.php

namespace WPAICG\Images\Providers\Google;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles formatting request payloads for Google Video Generation models (Veo 3).
 */
class GoogleVideoPayloadFormatter {

    /**
     * Formats the payload for Google Video Generation API.
     *
     * @param string $prompt The text prompt.
     * @param array  $options Generation options including 'model' (full ID), 'aspect_ratio', 'negative_prompt', etc.
     * @return array The formatted request body data.
     */
    public static function format(string $prompt, array $options): array {
        $model_id = $options['model'] ?? '';
        $payload = [];

        if ($model_id === 'veo-3.0-generate-preview') {
            // Build the instances array for Veo 3
            $instance = [
                'prompt' => $prompt
            ];

            $payload = [
                'instances' => [$instance]
            ];

            // Add parameters if provided
            $parameters = [];
            
            // Aspect ratio (default to 16:9 for Veo 3)
            if (isset($options['aspect_ratio'])) {
                $parameters['aspectRatio'] = $options['aspect_ratio'];
            } else {
                $parameters['aspectRatio'] = '16:9';
            }
            
            // Negative prompt
            if (isset($options['negative_prompt']) && !empty($options['negative_prompt'])) {
                $parameters['negativePrompt'] = $options['negative_prompt'];
            }
            
            // Person generation (default to allow_all for Veo 3)
            if (isset($options['person_generation'])) {
                $parameters['personGeneration'] = $options['person_generation'];
            } else {
                $parameters['personGeneration'] = 'allow_all';
            }

            // Only add parameters if we have any
            if (!empty($parameters)) {
                $payload['parameters'] = $parameters;
            }
        }

        return $payload;
    }
} 