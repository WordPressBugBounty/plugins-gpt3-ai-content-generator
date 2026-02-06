<?php

// File: classes/chat/core/ai-service/generate-response/ai-params/apply-claude-web-search.php

namespace WPAICG\Chat\Core\AIService\GenerateResponse\AiParams;

use WPAICG\Chat\Storage\BotSettingsManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Applies Claude Web Search tool configuration to AI parameters.
 *
 * @param array &$final_ai_params Reference to the final AI parameters array to be modified.
 * @param array $bot_settings Bot settings.
 * @param bool $frontend_web_search_active Flag for frontend web search toggle.
 */
function apply_claude_web_search_logic(
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

    $bot_allows_claude_web_search = (isset($bot_settings['claude_web_search_enabled']) && $bot_settings['claude_web_search_enabled'] === '1');
    if (!$bot_allows_claude_web_search) {
        return;
    }

    $split_domains = static function ($domains_raw): array {
        if (!is_string($domains_raw) || trim($domains_raw) === '') {
            return [];
        }
        $parts = preg_split('/[\r\n,]+/', $domains_raw);
        if (!is_array($parts)) {
            return [];
        }
        $domains = array_values(array_filter(array_map(static function ($part) {
            $domain = strtolower(trim((string) $part));
            if ($domain === '') {
                return '';
            }
            $domain = preg_replace('/^https?:\/\//', '', $domain);
            $domain = trim((string) $domain, " \t\n\r\0\x0B/");
            if ($domain === '' || !preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $domain)) {
                return '';
            }
            return $domain;
        }, $parts)));
        return array_values(array_unique($domains));
    };

    $web_search_config = [
        'enabled' => true,
        'type' => 'web_search_20250305',
    ];

    $max_uses = isset($bot_settings['claude_web_search_max_uses'])
        ? absint($bot_settings['claude_web_search_max_uses'])
        : BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_MAX_USES;
    $web_search_config['max_uses'] = max(1, min($max_uses, 20));

    $allowed_domains = $split_domains($bot_settings['claude_web_search_allowed_domains'] ?? '');
    $blocked_domains = $split_domains($bot_settings['claude_web_search_blocked_domains'] ?? '');
    if (!empty($allowed_domains)) {
        $web_search_config['allowed_domains'] = $allowed_domains;
    } elseif (!empty($blocked_domains)) {
        $web_search_config['blocked_domains'] = $blocked_domains;
    }

    if (($bot_settings['claude_web_search_loc_type'] ?? 'none') === 'approximate') {
        $user_location = array_filter([
            'country' => $bot_settings['claude_web_search_loc_country'] ?? null,
            'city' => $bot_settings['claude_web_search_loc_city'] ?? null,
            'region' => $bot_settings['claude_web_search_loc_region'] ?? null,
            'timezone' => $bot_settings['claude_web_search_loc_timezone'] ?? null,
        ]);
        if (!empty($user_location)) {
            $user_location['type'] = 'approximate';
            $web_search_config['user_location'] = $user_location;
        }
    }

    $cache_ttl = $bot_settings['claude_web_search_cache_ttl'] ?? BotSettingsManager::DEFAULT_CLAUDE_WEB_SEARCH_CACHE_TTL;
    if (in_array($cache_ttl, ['5m', '1h'], true)) {
        $web_search_config['cache_control'] = [
            'type' => 'ephemeral',
            'ttl' => $cache_ttl,
        ];
    }

    $final_ai_params['web_search_tool_config'] = $web_search_config;
    $final_ai_params['frontend_web_search_active'] = $frontend_web_search_active;
}
