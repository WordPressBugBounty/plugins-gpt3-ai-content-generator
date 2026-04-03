<?php

namespace WPAICG\Chat\Core\AIService\GenerateResponse\AiParams;

use WPAICG\Core\AIPKit_OpenAI_Reasoning;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Apply Ollama thinking controls using the existing reasoning setting.
 *
 * The provider strategy maps this normalized effort into Ollama's `think`
 * payload shape, including GPT-OSS level-based values.
 *
 * @param array<string, mixed> $final_ai_params
 * @param array<string, mixed> $bot_settings
 * @return void
 */
function apply_ollama_thinking_logic(array &$final_ai_params, array $bot_settings): void
{
    $reasoning_effort = AIPKit_OpenAI_Reasoning::sanitize_effort($bot_settings['reasoning_effort'] ?? '');
    if ($reasoning_effort === '' || $reasoning_effort === 'none') {
        return;
    }

    $final_ai_params['reasoning'] = ['effort' => $reasoning_effort];
}
