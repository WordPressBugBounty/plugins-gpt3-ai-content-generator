<?php
// File: classes/chat/storage/saver/handle-site-wide-logic.php
// Status: NEW FILE

namespace WPAICG\Chat\Storage\SaverMethods;

use WPAICG\Chat\Storage\SiteWideBotManager;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles site-wide bot uniqueness and reports whether cache invalidation is needed.
 *
 * @param SiteWideBotManager $site_wide_manager The SiteWideBotManager instance.
 * @param int $botId The ID of the bot being saved.
 * @param string $site_wide_enabled_flag '0' or '1' indicating if site-wide is enabled for this bot.
 * @return bool Whether the site-wide cache should be cleared after meta is saved.
 */
function handle_site_wide_logic(SiteWideBotManager $site_wide_manager, int $botId, string $site_wide_enabled_flag): bool {
    return $site_wide_manager->ensure_site_wide_uniqueness($botId, $site_wide_enabled_flag === '1');
}
