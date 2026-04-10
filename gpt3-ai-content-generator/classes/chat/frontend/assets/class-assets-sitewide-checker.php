<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/assets/class-assets-sitewide-checker.php
// Status: NEW FILE

namespace WPAICG\Chat\Frontend\Assets;

use WPAICG\Chat\Storage\SiteWideBotManager;
use WPAICG\aipkit_dashboard;
use WPAICG\Chat\Storage\BotStorage;
use WPAICG\Chat\Storage\BotSettingsManager;
use WPAICG\Chat\Frontend\Shortcode\FeatureManager;
use WPAICG\Chat\Frontend\Assets as AssetsOrchestrator; // Use the main orchestrator

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Checks for a site-wide bot and sets relevant static flags in the AssetsOrchestrator.
 */
class AssetsSiteWideChecker {

    public function __construct() {
        // Constructor can be empty or initialize dependencies if needed by the check method.
    }

    /**
     * Checks for a site-wide bot and updates static flags in AssetsOrchestrator.
     */
    public function check(): void {
        if (is_admin() || wp_doing_ajax()) return;

        // Ensure required classes are available
        if (!class_exists(SiteWideBotManager::class) ||
            !class_exists(aipkit_dashboard::class) ||
            !class_exists(BotStorage::class) ||
            !class_exists(BotSettingsManager::class)) {
            return;
        }

        $manager = new SiteWideBotManager();
        $bot_id = $manager->get_site_wide_bot_id();

        if ($bot_id) {
            AssetsOrchestrator::$site_wide_injection_needed = true;

            $bot_storage = new BotStorage();
            $settings = $bot_storage->get_chatbot_settings($bot_id);
            if (!class_exists(FeatureManager::class)) {
                $feature_manager_path = WPAICG_PLUGIN_DIR . 'classes/chat/frontend/shortcode/shortcode_featuremanager.php';
                if (file_exists($feature_manager_path)) {
                    require_once $feature_manager_path;
                }
            }

            $feature_flags = class_exists(FeatureManager::class)
                ? FeatureManager::determine_flags($settings)
                : [];

            AssetsOrchestrator::$consent_needed = true;

            if (!empty($feature_flags['enable_copy_button'])) AssetsOrchestrator::$copy_button_needed = true;
            if (!empty($feature_flags['feedback_ui_enabled'])) AssetsOrchestrator::$feedback_needed = true;
            if (!empty($feature_flags['starters_ui_enabled'])) AssetsOrchestrator::$starters_needed = true;
            if (!empty($feature_flags['sidebar_ui_enabled'])) AssetsOrchestrator::$sidebar_needed = true;
            if (!empty($feature_flags['tts_ui_enabled'])) AssetsOrchestrator::$tts_needed = true;
            if (!empty($feature_flags['enable_voice_input_ui'])) AssetsOrchestrator::$stt_needed = true;

            AssetsOrchestrator::$image_gen_needed = true;

            if (!empty($feature_flags['image_upload_ui_enabled'])) {
                AssetsOrchestrator::$chat_image_upload_needed = true;
            }
            if (!empty($feature_flags['file_upload_ui_enabled']) && aipkit_dashboard::is_pro_plan()) {
                AssetsOrchestrator::$chat_file_upload_needed = true;
            }
            if (!empty($feature_flags['enable_realtime_voice_ui']) && aipkit_dashboard::is_pro_plan()) {
                AssetsOrchestrator::$realtime_voice_needed = true;
            }
        }
    }
}
