<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/core/ai-service/generate-response/ai-params/apply-openai-reasoning.php
// Status: NEW FILE

namespace WPAICG\Chat\Core\AIService\GenerateResponse\AiParams;

use WPAICG\Core\AIPKit_OpenAI_Reasoning;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Applies OpenAI Reasoning parameters if the model is compatible.
 *
 * @param array &$final_ai_params Reference to the final AI parameters array to be modified.
 * @param array $bot_settings Bot settings.
 * @param string $model The selected AI model name.
 */
function apply_openai_reasoning_logic(
    array &$final_ai_params,
    array $bot_settings,
    string $model
): void {
    $reasoning_effort = AIPKit_OpenAI_Reasoning::normalize_effort_for_model(
        (string) $model,
        $bot_settings['reasoning_effort'] ?? ''
    );

    if ($reasoning_effort !== '') {
        $final_ai_params['reasoning'] = ['effort' => $reasoning_effort];
    }
}
