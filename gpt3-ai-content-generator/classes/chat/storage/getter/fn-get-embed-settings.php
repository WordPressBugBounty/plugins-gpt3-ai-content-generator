<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/storage/getter/fn-get-embed-settings.php
// Status: NEW FILE

namespace WPAICG\Chat\Storage\GetterMethods;

use WPAICG\Chat\Storage\BotSettingsManager; // For default constants if needed

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves Embed configuration settings.
 *
 * @param int $bot_id The ID of the bot post.
 * @param callable $get_meta_fn A function to retrieve post meta.
 * @return array Associative array of embed settings.
 */
function get_embed_settings_logic(int $bot_id, callable $get_meta_fn): array
{
    $settings = [];

    $deploy_mode = sanitize_key((string) $get_meta_fn('_aipkit_deploy_mode', ''));
    $settings['deploy_mode'] = in_array($deploy_mode, ['inline', 'popup', 'external'], true)
        ? $deploy_mode
        : '';

    // Get the allowed domains, default to an empty string if not set.
    $settings['embed_allowed_domains'] = $get_meta_fn('_aipkit_embed_allowed_domains', '');

    return $settings;
}
