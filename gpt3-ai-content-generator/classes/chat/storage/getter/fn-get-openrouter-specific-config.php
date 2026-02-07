<?php

// File: classes/chat/storage/getter/fn-get-openrouter-specific-config.php

namespace WPAICG\Chat\Storage\GetterMethods;

use WPAICG\Chat\Storage\BotSettingsManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves OpenRouter-specific configuration settings (Web Search).
 *
 * @param int $bot_id The ID of the bot post.
 * @param callable $get_meta_fn A function to retrieve post meta.
 * @return array Associative array of OpenRouter-specific settings.
 */
function get_openrouter_specific_config_logic(int $bot_id, callable $get_meta_fn): array
{
    $settings = [];

    if (!class_exists(BotSettingsManager::class)) {
        $bsm_path = dirname(__DIR__) . '/class-aipkit_bot_settings_manager.php';
        if (file_exists($bsm_path)) {
            require_once $bsm_path;
        }
    }

    $settings['openrouter_web_search_enabled'] = in_array(
        $get_meta_fn('_aipkit_openrouter_web_search_enabled', BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENABLED),
        ['0', '1'],
        true
    ) ? $get_meta_fn('_aipkit_openrouter_web_search_enabled', BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENABLED)
      : BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENABLED;

    $engine = $get_meta_fn('_aipkit_openrouter_web_search_engine', BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENGINE);
    $settings['openrouter_web_search_engine'] = in_array($engine, ['auto', 'native', 'exa'], true)
        ? $engine
        : BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_ENGINE;

    $raw_max_results = $get_meta_fn(
        '_aipkit_openrouter_web_search_max_results',
        (string) BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_MAX_RESULTS
    );
    $max_results = is_numeric($raw_max_results) ? absint($raw_max_results) : BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_MAX_RESULTS;
    $settings['openrouter_web_search_max_results'] = max(1, min($max_results, 10));

    $settings['openrouter_web_search_search_prompt'] = $get_meta_fn(
        '_aipkit_openrouter_web_search_search_prompt',
        BotSettingsManager::DEFAULT_OPENROUTER_WEB_SEARCH_SEARCH_PROMPT
    );

    return $settings;
}
