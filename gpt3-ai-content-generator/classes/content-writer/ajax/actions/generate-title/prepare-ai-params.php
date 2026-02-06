<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/generate-title/prepare-ai-params.php
// Status: MODIFIED
// I have added a conditional check to ensure the `reasoning_effort` parameter is only added for compatible OpenAI models (gpt-5, o-series).

namespace WPAICG\ContentWriter\Ajax\Actions\GenerateTitle;

use WPAICG\Core\AIPKit_OpenAI_Reasoning;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Prepares the final AI parameters by merging global settings with form-specific overrides for title generation.
 *
 * @param array $validated_params The validated settings from the request.
 * @return array The array of AI parameter overrides.
 */
function prepare_ai_params_logic(array $validated_params): array
{
    $ai_params_override = [];

    if (isset($validated_params['ai_temperature'])) {
        $ai_params_override['temperature'] = floatval($validated_params['ai_temperature']);
    }

    // Add reasoning effort to AI params if present and model is compatible
    if (($validated_params['provider'] ?? '') === 'OpenAI') {
        $reasoning_effort = AIPKit_OpenAI_Reasoning::normalize_effort_for_model(
            (string) ($validated_params['ai_model'] ?? ''),
            $validated_params['reasoning_effort'] ?? ''
        );
        if ($reasoning_effort !== '') {
            $ai_params_override['reasoning'] = ['effort' => $reasoning_effort];
        }
    }

    $ai_params_override['top_p'] = null;

    return $ai_params_override;
}
