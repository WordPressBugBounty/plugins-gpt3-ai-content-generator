<?php

// File: classes/chat/storage/getter/fn-get-xai-specific-config.php

namespace WPAICG\Chat\Storage\GetterMethods;

use WPAICG\Chat\Storage\BotSettingsManager;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Retrieves xAI-specific chatbot settings.
 *
 * @param int $bot_id The ID of the bot post.
 * @param callable $get_meta_fn A function to retrieve post meta.
 * @return array Associative array of xAI-specific settings.
 */
function get_xai_specific_config_logic(int $bot_id, callable $get_meta_fn): array
{
    if (!class_exists(BotSettingsManager::class)) {
        $bsm_path = dirname(__DIR__) . '/class-aipkit_bot_settings_manager.php';
        if (file_exists($bsm_path)) {
            require_once $bsm_path;
        }
    }

    $enabled = $get_meta_fn('_aipkit_xai_web_search_enabled', BotSettingsManager::DEFAULT_XAI_WEB_SEARCH_ENABLED);

    return [
        'xai_web_search_enabled' => in_array($enabled, ['0', '1'], true)
            ? $enabled
            : BotSettingsManager::DEFAULT_XAI_WEB_SEARCH_ENABLED,
    ];
}
