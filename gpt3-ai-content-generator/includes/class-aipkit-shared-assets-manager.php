<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/includes/class-aipkit-shared-assets-manager.php
// Status: MODIFIED

namespace WPAICG\Includes;

// Ensure this file is only loaded by WordPress
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Shared_Assets_Manager
 * Handles registering scripts shared across admin and public contexts.
 * REVISED: Only registers vendor scripts. Core utils are now part of main bundles.
 */
class AIPKit_Shared_Assets_Manager
{
    /**
     * Build a versioned plugin asset URL for lazy-loaded frontend bundles.
     *
     * @param string $relative_path
     * @return string
     */
    private static function get_versioned_asset_url(string $relative_path): string
    {
        $version = defined('WPAICG_VERSION') ? (string) WPAICG_VERSION : '1.0.0';

        return add_query_arg(
            'ver',
            rawurlencode($version),
            WPAICG_PLUGIN_URL . ltrim($relative_path, '/')
        );
    }

    /**
     * Return public asset URLs used by lazy frontend loaders.
     *
     * @return array<string, string>
     */
    public static function get_public_asset_urls(): array
    {
        $asset_urls = [
            'markdownIt' => self::get_versioned_asset_url('dist/vendor/js/markdown-it.min.js'),
            'markdownit' => self::get_versioned_asset_url('dist/vendor/js/markdown-it.min.js'),
            'chatSidebar' => self::get_versioned_asset_url('dist/js/public-chat-sidebar.bundle.js'),
            'chatStt' => self::get_versioned_asset_url('dist/js/public-chat-stt.bundle.js'),
            'chatUploads' => self::get_versioned_asset_url('dist/js/public-chat-uploads.bundle.js'),
            'chatTts' => self::get_versioned_asset_url('dist/js/public-chat-tts.bundle.js'),
            'chatStarters' => self::get_versioned_asset_url('dist/js/public-chat-starters.bundle.js'),
            'chatImageCommand' => self::get_versioned_asset_url('dist/js/public-chat-image-command.bundle.js'),
            'chatRealtime' => self::get_versioned_asset_url('dist/js/public-chat-realtime.bundle.js'),
            'chatPdf' => self::get_versioned_asset_url('dist/js/public-chat-pdf.bundle.js'),
        ];

        return $asset_urls;
    }

    /**
     * Register scripts shared across admin and public contexts.
     *
     * @param string $plugin_version The current plugin version.
     */
    public static function register(string $plugin_version)
    {
        // Vendor JS files are copied to dist/vendor/js/ by esbuild
        $vendor_js_url = WPAICG_PLUGIN_URL . 'dist/vendor/js/';

        // Markdown-it (copied by esbuild)
        $markdownit_url = $vendor_js_url . 'markdown-it.min.js';
        if (!wp_script_is('aipkit_markdown-it', 'registered')) {
            wp_register_script('aipkit_markdown-it', $markdownit_url, [], '14.1.0', true); // Assuming version from previous config
        }

        // Note: Core utility scripts like btn-utils, html-escaper, date-utils
        // are now imported directly into admin-main.js or public-main.js and bundled.
        // They are no longer registered as separate handles here.
    }

    /**
     * Expose shared public asset URLs to frontend bundles that lazy-load vendor scripts.
     *
     * @param string $handle The registered script handle that should receive the config.
     */
    public static function attach_public_asset_urls(string $handle): void
    {
        if (!wp_script_is($handle, 'registered')) {
            return;
        }

        static $attached_handles = [];
        if (isset($attached_handles[$handle])) {
            return;
        }

        $asset_urls = self::get_public_asset_urls();

        wp_add_inline_script(
            $handle,
            'window.aipkitPublicAssetUrls = Object.assign({}, window.aipkitPublicAssetUrls || {}, ' . wp_json_encode($asset_urls) . ');',
            'before'
        );

        $attached_handles[$handle] = true;
    }
}
