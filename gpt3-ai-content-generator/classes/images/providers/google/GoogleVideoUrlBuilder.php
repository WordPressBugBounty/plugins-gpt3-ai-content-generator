<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/images/providers/google/GoogleVideoUrlBuilder.php

namespace WPAICG\Images\Providers\Google;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles building API URLs specific to Google Video Generation models (Veo 3).
 */
class GoogleVideoUrlBuilder {

    /**
     * Build the full API endpoint URL for a given Google Video Generation model.
     *
     * @param string $model_id The specific model ID (e.g., 'veo-3.0-generate-preview').
     * @param array  $api_params Required parameters (base_url, api_version, api_key).
     * @param string $operation The operation type ('generate' or 'poll').
     * @return string|WP_Error The full URL or WP_Error.
     */
    public static function build(string $model_id, array $api_params, string $operation = 'generate'): string|WP_Error {
        $base_url = !empty($api_params['base_url']) ? rtrim($api_params['base_url'], '/') : 'https://generativelanguage.googleapis.com';
        $api_version = !empty($api_params['api_version']) ? $api_params['api_version'] : 'v1beta';
        $api_key = !empty($api_params['api_key']) ? $api_params['api_key'] : '';

        if (empty($api_key)) {
            return new WP_Error('missing_google_api_key_for_video_url', __('Google API key is required for video URL construction.', 'gpt3-ai-content-generator'));
        }

        // Handle different operations
        if ($operation === 'generate') {
            // For video generation, we use predictLongRunning endpoint
            if ($model_id === 'veo-3.0-generate-preview') {
                $endpoint_suffix = ':predictLongRunning';
            } else {
                /* translators: %s: The model ID that was attempted to be used for video URL building. */
                return new WP_Error('unsupported_google_video_model_for_url', sprintf(__('Unsupported Google video model for URL building: %s', 'gpt3-ai-content-generator'), $model_id));
            }
            
            // Construct path: /v1beta/models/MODEL_ID:predictLongRunning
            $full_path = '/' . trim($api_version, '/') . '/models/' . urlencode($model_id) . $endpoint_suffix;
            $url_with_key = $base_url . $full_path . '?key=' . urlencode($api_key);
            
        } elseif ($operation === 'poll') {
            // For polling operation status, we need the operation name passed as model_id
            $operation_name = $model_id; // In this case, model_id is actually the operation name
            $full_path = '/' . trim($api_version, '/') . '/' . $operation_name;
            $url_with_key = $base_url . $full_path . '?key=' . urlencode($api_key);
            
        } else {
            return new WP_Error('unsupported_video_operation', sprintf(__('Unsupported video operation: %s', 'gpt3-ai-content-generator'), $operation));
        }

        return $url_with_key;
    }
} 