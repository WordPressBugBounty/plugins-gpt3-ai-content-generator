<?php

// File: classes/chat/core/ai-service/generate-response/ai-params/apply-openrouter-web-search.php

namespace WPAICG\Chat\Core\AIService\GenerateResponse\AiParams;

use WPAICG\Chat\Storage\BotSettingsManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Applies OpenRouter web search plugin configuration to AI parameters.
 *
 * @param array &$final_ai_params Reference to the final AI parameters array to be modified.
 * @param array $bot_settings Bot settings.
 * @param bool $frontend_web_search_active Flag for frontend web search toggle.
 */
function apply_openrouter_web_search_logic(
    array &$final_ai_params,
    array $bot_settings,
    bool $frontend_web_search_active
): void {
    if (!class_exists(BotSettingsManager::class)) {
        $bsm_path = WPAICG_PLUGIN_DIR . 'classes/chat/storage/class-aipkit_bot_settings_manager.php';
        if (file_exists($bsm_path)) {
            require_once $bsm_path;
        } else {
            return;
        }
    }

    $bot_allows_openrouter_web_search = (isset($bot_settings['openrouter_web_search_enabled']) && $bot_settings['openrouter_web_search_enabled'] === '1');
    if (!$bot_allows_openrouter_web_search) {
        return;
    }

    $web_search_config = [
        'enabled' => true,
    ];

    $engine = isset($bot_settings['openrouter_web_search_engine']) ? sanitize_key((string) $bot_settings['openrouter_web_search_engine']) : BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENGINE;
    if (in_array($engine, ['native', 'exa'], true)) {
        $web_search_config['engine'] = $engine;
    }

    $max_results = isset($bot_settings['openrouter_web_search_max_results'])
        ? absint($bot_settings['openrouter_web_search_max_results'])
        : BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_MAX_RESULTS;
    $web_search_config['max_results'] = max(1, min($max_results, 10));

    $search_prompt = isset($bot_settings['openrouter_web_search_search_prompt'])
        ? sanitize_textarea_field((string) $bot_settings['openrouter_web_search_search_prompt'])
        : BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_SEARCH_PROMPT;
    if ($search_prompt !== '') {
        $web_search_config['search_prompt'] = $search_prompt;
    }

    $final_ai_params['web_search_tool_config'] = $web_search_config;
    $final_ai_params['frontend_web_search_active'] = $frontend_web_search_active;
}

