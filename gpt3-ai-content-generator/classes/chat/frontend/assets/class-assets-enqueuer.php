<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/assets/class-assets-enqueuer.php
// Status: MODIFIED

namespace WPAICG\Chat\Frontend\Assets;

use WPAICG\Chat\Frontend\Assets as AssetsOrchestrator;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles the actual enqueuing of frontend assets based on set flags.
 * REVISED: Enqueues public-main.bundle.js and public-main.bundle.css.
 * MODIFIED: Explicitly enqueues 'aipkit_jspdf' if AssetsOrchestrator::$jspdf_needed is true and script is registered.
 */
class AssetsEnqueuer
{
    // --- MODIFIED: Added CSS enqueue tracker ---
    private $is_public_main_css_enqueued = false;
    // --- END MODIFICATION ---
    private $is_public_main_js_enqueued_by_this = false; // Tracker specific to this class

    public function __construct()
    {
        // Constructor can be empty
    }

    public function process_assets(): void
    {
        if (is_admin()) {
            return;
        }

        // Ensure dependencies are registered if they haven't been yet.
        if (!AssetsOrchestrator::$assets_registered) {
            if (class_exists(AssetsDependencyRegistrar::class) && method_exists(AssetsDependencyRegistrar::class, 'register')) {
                AssetsDependencyRegistrar::register(); // This registers all public chat JS handles
                AssetsOrchestrator::$assets_registered = true;
            } else {
                error_log("AIPKit AssetsEnqueuer: AssetsDependencyRegistrar class or register method not found.");
                return;
            }
        }

        $should_enqueue_core_css = AssetsOrchestrator::$shortcode_rendered || AssetsOrchestrator::$site_wide_injection_needed;
        $should_enqueue_core_js = $should_enqueue_core_css; // JS likely needed if CSS is

        // Main public CSS bundle (dist/css/public-main.bundle.css)
        $public_main_css_handle = 'aipkit-public-main-css';
        if (!wp_style_is($public_main_css_handle, 'registered')) {
            wp_register_style(
                $public_main_css_handle,
                WPAICG_PLUGIN_URL . 'dist/css/public-main.bundle.css',
                ['dashicons'], // Assuming dashicons is a common dependency
                defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0'
            );
        }
        // --- MODIFIED: Enqueue public-main.bundle.css if needed ---
        if ($should_enqueue_core_css && !$this->is_public_main_css_enqueued && !wp_style_is($public_main_css_handle, 'enqueued')) {
            wp_enqueue_style($public_main_css_handle);
            $this->is_public_main_css_enqueued = true;
        }
        // --- END MODIFICATION ---


        // Main public JS bundle (dist/js/public-main.bundle.js)
        $public_main_js_handle = 'aipkit-public-main';
        // Registration is now handled by AssetsDependencyRegistrar and potentially modified by lib/wpaicg__premium_only.php
        // So, just check if it's registered before trying to enqueue.
        if (!wp_script_is($public_main_js_handle, 'registered')) {
            // Fallback registration if somehow missed, without jspdf
            error_log("AIPKit AssetsEnqueuer: Fallback registration for {$public_main_js_handle}. This indicates an issue in the primary registration flow.");
            wp_register_script(
                $public_main_js_handle,
                WPAICG_PLUGIN_URL . 'dist/js/public-main.bundle.js',
                ['wp-i18n', 'aipkit_markdown-it'], // No 'aipkit_jspdf' here
                defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0',
                true
            );
        }

        if ($should_enqueue_core_js) {
            if (!wp_script_is($public_main_js_handle, 'enqueued')) {
                wp_enqueue_script($public_main_js_handle);
                wp_set_script_translations($public_main_js_handle, 'gpt3-ai-content-generator', WPAICG_PLUGIN_DIR . 'languages');
                $this->is_public_main_js_enqueued_by_this = true;
            }

            // --- MODIFICATION: Conditionally enqueue jspdf ---
            if (AssetsOrchestrator::$jspdf_needed && wp_script_is('aipkit_jspdf', 'registered')) {
                if (!wp_script_is('aipkit_jspdf', 'enqueued')) {
                    wp_enqueue_script('aipkit_jspdf');
                    // error_log("AIPKit AssetsEnqueuer: Enqueued 'aipkit_jspdf' because jspdf_needed is true.");
                }
            } elseif (AssetsOrchestrator::$jspdf_needed && !wp_script_is('aipkit_jspdf', 'registered')) {
                error_log("AIPKit AssetsEnqueuer: 'jspdf_needed' is true, but 'aipkit_jspdf' script is not registered. PDF download might fail.");
            }
            // --- END MODIFICATION ---


            // Global localization (if not already done by WP_AI_Content_Generator_Public or Shortcodes_Manager)
            // This is for general chat UI texts that are always needed if chat is active.
            static $global_chat_localized = false;
            if (!$global_chat_localized && wp_script_is($public_main_js_handle, 'enqueued')) {
                wp_localize_script($public_main_js_handle, 'aipkit_chat_config_global', [
                    'text' => [
                        'fullscreenError' => __('Error: Fullscreen functionality is unavailable.', 'gpt3-ai-content-generator'),
                        'copySuccess' => __('Copied!', 'gpt3-ai-content-generator'), 'copyFail' => __('Failed to copy', 'gpt3-ai-content-generator'),
                        'copyActionLabel' => __('Copy response', 'gpt3-ai-content-generator'),
                        'feedbackSubmitted' => __('Feedback submitted', 'gpt3-ai-content-generator'), 'feedbackError' => __('Error saving feedback', 'gpt3-ai-content-generator'),
                        'newChat' => __('New Chat', 'gpt3-ai-content-generator'), 'sidebarToggle' => __('Toggle Conversation Sidebar', 'gpt3-ai-content-generator'),
                        'historyGuests' => __('History unavailable for guests.', 'gpt3-ai-content-generator'), 'historyEmpty' => __('No past conversations.', 'gpt3-ai-content-generator'),
                        'playActionLabel' => __('Play audio', 'gpt3-ai-content-generator'),
                        'pauseActionLabel' => __('Pause audio', 'gpt3-ai-content-generator'),
                        'uploadFile' => __('Upload File (TXT, PDF)', 'gpt3-ai-content-generator'),
                        'fileContextActive' => __('Chatting with: %s', 'gpt3-ai-content-generator'), // %s will be filename
                        'clearFileContext' => __('Clear file context', 'gpt3-ai-content-generator'),
                        // ... any other truly global texts needed by public-main.bundle.js
                    ]
                ]);
                $global_chat_localized = true;
            }
        }
    }
}
