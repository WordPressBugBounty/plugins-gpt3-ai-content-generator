<?php

// File: classes/chat/storage/getter/fn-get-claude-specific-config.php

namespace WPAICG\Chat\Storage\GetterMethods;

use WPAICG\Chat\Storage\BotSettingsManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves Claude-specific configuration settings (Web Search).
 *
 * @param int $bot_id The ID of the bot post.
 * @param callable $get_meta_fn A function to retrieve post meta.
 * @return array Associative array of Claude-specific settings.
 */
function get_claude_specific_config_logic(int $bot_id, callable $get_meta_fn): array
{
    $settings = [];

    if (!class_exists(BotSettingsManager::class)) {
        $bsm_path = dirname(__DIR__) . '/class-aipkit_bot_settings_manager.php';
        if (file_exists($bsm_path)) {
            require_once $bsm_path;
        }
    }

    $settings['claude_web_search_enabled'] = in_array(
        $get_meta_fn('_aipkit_claude_web_search_enabled', BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_ENABLED),
        ['0', '1'],
        true
    ) ? $get_meta_fn('_aipkit_claude_web_search_enabled', BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_ENABLED)
      : BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_ENABLED;

    $raw_max_uses = $get_meta_fn('_aipkit_claude_web_search_max_uses', (string) BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_MAX_USES);
    $max_uses = is_numeric($raw_max_uses) ? absint($raw_max_uses) : BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_MAX_USES;
    $settings['claude_web_search_max_uses'] = max(1, min($max_uses, 20));

    $settings['claude_web_search_loc_type'] = $get_meta_fn('_aipkit_claude_web_search_loc_type', BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_LOC_TYPE);
    if (!in_array($settings['claude_web_search_loc_type'], ['none', 'approximate'], true)) {
        $settings['claude_web_search_loc_type'] = BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_LOC_TYPE;
    }

    $settings['claude_web_search_loc_country'] = $get_meta_fn('_aipkit_claude_web_search_loc_country', '');
    $settings['claude_web_search_loc_city'] = $get_meta_fn('_aipkit_claude_web_search_loc_city', '');
    $settings['claude_web_search_loc_region'] = $get_meta_fn('_aipkit_claude_web_search_loc_region', '');
    $settings['claude_web_search_loc_timezone'] = $get_meta_fn('_aipkit_claude_web_search_loc_timezone', '');

    $settings['claude_web_search_allowed_domains'] = $get_meta_fn('_aipkit_claude_web_search_allowed_domains', '');
    $settings['claude_web_search_blocked_domains'] = $get_meta_fn('_aipkit_claude_web_search_blocked_domains', '');
    if (!empty($settings['claude_web_search_allowed_domains']) && !empty($settings['claude_web_search_blocked_domains'])) {
        $settings['claude_web_search_blocked_domains'] = '';
    }

    $settings['claude_web_search_cache_ttl'] = $get_meta_fn('_aipkit_claude_web_search_cache_ttl', BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_CACHE_TTL);
    if (!in_array($settings['claude_web_search_cache_ttl'], ['none', '5m', '1h'], true)) {
        $settings['claude_web_search_cache_ttl'] = BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_CACHE_TTL;
    }

    return $settings;
}
