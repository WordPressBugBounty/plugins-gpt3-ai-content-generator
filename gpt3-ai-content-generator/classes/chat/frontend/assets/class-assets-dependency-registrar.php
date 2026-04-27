<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/assets/class-assets-dependency-registrar.php
// Status: MODIFIED

namespace WPAICG\Chat\Frontend\Assets;

// REMOVED Unused use statements for sub-registrars as they are no longer needed here.

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the registration of all public chat JavaScript dependencies.
 */
class AssetsDependencyRegistrar
{
    public static function register(): void
    {
        $version = defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0';
        $plugin_base_url = defined('WPAICG_PLUGIN_URL') ? WPAICG_PLUGIN_URL : plugin_dir_url(dirname(dirname(dirname(dirname(dirname(__FILE__))))));
        $dist_js_url = $plugin_base_url . 'dist/js/';

        // Main public bundle (contains chat frontend JS only).
        // wp-i18n is no longer needed here because the chat runtime does not call wp.i18n.
        $public_main_js_handle = 'aipkit-public-main';
        if (!wp_script_is($public_main_js_handle, 'registered')) {
            wp_register_script(
                $public_main_js_handle,
                $dist_js_url . 'public-main.bundle.js',
                [],
                $version,
                true
            );
        }

        $public_chat_sidebar_js_handle = 'aipkit-public-chat-sidebar';
        if (!wp_script_is($public_chat_sidebar_js_handle, 'registered')) {
            wp_register_script(
                $public_chat_sidebar_js_handle,
                $dist_js_url . 'public-chat-sidebar.bundle.js',
                [$public_main_js_handle],
                $version,
                true
            );
        }
    }
}
