<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/seo/seo-helper.php
// Status: MODIFIED

namespace WPAICG\SEO;

use WPAICG\SEO\Yoast\AIPKit_Yoast_Handler;
use WPAICG\SEO\RankMath\AIPKit_Rank_Math_Handler;
use WPAICG\SEO\AIOSEO\AIPKit_AIOSEO_Handler;
use WPAICG\SEO\Framework\AIPKit_Framework_Handler;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AIPKit_SEO_Helper
 *
 * Main orchestrator for interacting with various SEO plugins.
 * Detects the active plugin and delegates actions to the appropriate handler.
 */
class AIPKit_SEO_Helper
{
    private static $active_plugin = null;
    private static $handler_instance = null;

    /**
     * Detects the active SEO plugin. Caches the result for the request.
     * @return string The identifier for the active plugin ('yoast', 'rank_math', 'aioseo', 'framework', 'none').
     */
    private static function get_active_plugin(): string
    {
        if (self::$active_plugin !== null) {
            return self::$active_plugin;
        }

        if (defined('WPSEO_VERSION')) {
            self::$active_plugin = 'yoast';
        } elseif (defined('RANK_MATH_VERSION')) {
            self::$active_plugin = 'rank_math';
        } elseif (defined('AIOSEO_VERSION')) {
            self::$active_plugin = 'aioseo';
        } elseif (class_exists('\The_SEO_Framework\API')) {
            self::$active_plugin = 'framework';
        } else {
            self::$active_plugin = 'none';
        }
        return self::$active_plugin;
    }

    /**
     * Loads and instantiates the correct handler class for the active SEO plugin.
     * @return AIPKit_SEO_Handler_Interface|null The handler instance or null if none active/found.
     */
    private static function get_handler(): ?AIPKit_SEO_Handler_Interface
    {
        if (self::$handler_instance !== null) {
            return self::$handler_instance;
        }

        $plugin = self::get_active_plugin();
        if ($plugin === 'none') {
            return null;
        }

        $handler_class = null;
        $handler_path = null;

        switch ($plugin) {
            case 'yoast':
                $handler_class = AIPKit_Yoast_Handler::class;
                $handler_path = __DIR__ . '/yoast/class-aipkit-yoast-handler.php';
                break;
            case 'rank_math':
                $handler_class = AIPKit_Rank_Math_Handler::class;
                $handler_path = __DIR__ . '/rank-math/class-aipkit-rank-math-handler.php';
                break;
            case 'aioseo':
                $handler_class = AIPKit_AIOSEO_Handler::class;
                $handler_path = __DIR__ . '/aioseo/class-aipkit-aioseo-handler.php';
                break;
            case 'framework':
                $handler_class = AIPKit_Framework_Handler::class;
                $handler_path = __DIR__ . '/framework/class-aipkit-framework-handler.php';
                break;
        }

        if ($handler_path && file_exists($handler_path)) {
            if (!class_exists($handler_class)) {
                $interface_path = __DIR__ . '/interface-aipkit-seo-handler.php';
                if (file_exists($interface_path) && !interface_exists(AIPKit_SEO_Handler_Interface::class)) {
                    require_once $interface_path;
                }
                require_once $handler_path;
            }
            if (class_exists($handler_class)) {
                self::$handler_instance = new $handler_class();
                return self::$handler_instance;
            }
        }

        error_log("AIPKit SEO Helper: Could not load handler for active plugin '{$plugin}'.");
        return null;
    }

    /**
     * Updates the SEO meta description for a post.
     * Delegates to the active SEO plugin's handler.
     *
     * @param int $post_id The ID of the post.
     * @param string $description The new meta description.
     * @return bool True on success, false on failure.
     */
    public static function update_meta_description(int $post_id, string $description): bool
    {
        $handler = self::get_handler();
        if ($handler) {
            return $handler->update_meta_description($post_id, $description);
        }
        // Fallback for no SEO plugin: save to our own meta key.
        return update_post_meta($post_id, '_aipkit_meta_description', sanitize_text_field($description));
    }

    /**
     * Updates the focus keyword for a post.
     *
     * @param int $post_id The ID of the post.
     * @param string $keyword The new focus keyword.
     * @return bool True on success, false on failure.
     */
    public static function update_focus_keyword(int $post_id, string $keyword): bool
    {
        $handler = self::get_handler();
        if ($handler) {
            return $handler->update_focus_keyword($post_id, $keyword);
        }
        return false;
    }

    /**
     * Retrieves the focus keyword for a post.
     *
     * @param int $post_id The ID of the post.
     * @return string|null The focus keyword, or null if not found/supported.
     */
    public static function get_focus_keyword(int $post_id): ?string
    {
        $handler = self::get_handler();
        if ($handler) {
            return $handler->get_focus_keyword($post_id);
        }
        return null;
    }
}
