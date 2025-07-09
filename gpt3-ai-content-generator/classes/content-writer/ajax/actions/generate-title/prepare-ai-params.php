<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/ajax/actions/generate-title/prepare-ai-params.php
// Status: NEW FILE

namespace WPAICG\ContentWriter\Ajax\Actions\GenerateTitle;

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
    $ai_params_override = [
        'max_completion_tokens' => 60, // Set a specific, low token limit for title generation
    ];

    if (isset($validated_params['ai_temperature'])) {
        $ai_params_override['temperature'] = floatval($validated_params['ai_temperature']);
    }

    return $ai_params_override;
}
