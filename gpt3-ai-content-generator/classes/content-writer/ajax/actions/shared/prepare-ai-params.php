<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/shared/prepare-ai-params.php
// Status: MODIFIED
// I have added a conditional check to ensure the `reasoning_effort` parameter is only added for compatible OpenAI models (gpt-5, o-series).

namespace WPAICG\ContentWriter\Ajax\Actions\Shared;

use WPAICG\Core\AIPKit_OpenAI_Reasoning;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prepares an array of AI parameter overrides from the submitted settings.
 * This does NOT merge with global defaults; it only prepares the override values.
 *
 * @param array $settings The validated settings from the request.
 * @return array The array of AI parameter overrides.
 */
function prepare_ai_params_logic(array $settings): array
{
    $ai_params_override = [];

    if (isset($settings['ai_temperature'])) {
        $ai_params_override['temperature'] = floatval($settings['ai_temperature']);
    }

    $max_completion_tokens = null;
    if (isset($settings['max_completion_tokens']) && is_numeric($settings['max_completion_tokens'])) {
        $max_completion_tokens = absint($settings['max_completion_tokens']);
    } elseif (isset($settings['max_tokens']) && is_numeric($settings['max_tokens'])) {
        $max_completion_tokens = absint($settings['max_tokens']);
    } else {
        $content_length = isset($settings['content_length'])
            ? sanitize_key($settings['content_length'])
            : '';
        $length_map = [
            'short' => 2000,
            'medium' => 4000,
            'long' => 6000,
        ];
        if (isset($length_map[$content_length])) {
            $max_completion_tokens = $length_map[$content_length];
        }
    }
    if ($max_completion_tokens) {
        $ai_params_override['max_completion_tokens'] = $max_completion_tokens;
    }
    // Add reasoning effort to AI params if present and model is compatible
    if (($settings['provider'] ?? '') === 'OpenAI') {
        $reasoning_effort = AIPKit_OpenAI_Reasoning::normalize_effort_for_model(
            (string) ($settings['ai_model'] ?? ''),
            $settings['reasoning_effort'] ?? ''
        );
        if ($reasoning_effort !== '') {
            $ai_params_override['reasoning'] = ['effort' => $reasoning_effort];
        }
    }

    $ai_params_override['top_p'] = null;


    return $ai_params_override;
}
