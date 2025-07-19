<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/dashboard/class-aipkit_dashboard.php
// Status: MODIFIED
// I have added the 'semantic_search' addon to the default status list.

namespace WPAICG;

use WPAICG\Stats\AIPKit_Stats;
use WPAICG\AIPKit_Role_Manager;
use WPAICG\Vector\AIPKit_Vector_Store_Registry;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\\WPAICG\\aipkit_dashboard')) {
    class aipkit_dashboard
    {
        /**
         * Default module settings (true=enabled, false=disabled).
         * ADDED: ai_forms default.
         * MODIFIED: Changed visibility from private to public.
         */
        public static $default_module_settings = array( // MODIFIED: private to public
            'chat_bot'        => true,
            'content_writer'  => true,
            'autogpt'         => true,
            'ai_forms'        => true,
            'image_generator' => true,
            'training'        => true,
            'ai_account'      => false,
            'audio_converter' => false,
            'logs_viewer'     => true,
        );

        /**
         * Default addon status (true=active, false=inactive).
         * ADDED: file_upload default.
         * ADDED: triggers default.
         * MODIFIED: Changed visibility from private to public.
         */
        public static $default_addon_status = array( // MODIFIED: private to public
           'ai_post_enhancer'           => true,
           'consent_compliance'         => false,
           'conversation_starters'      => false,
           'deepseek'                   => false,
           'file_upload'                => false,
           'ip_anonymization'           => false,
           'openai_moderation'          => false,
           'pdf_download'               => false,
           'replicate'                  => false,
           'semantic_search'            => false,
           'stock_images'               => false,
           'token_management'           => false,
           'triggers'                   => false,
           'vector_databases'           => false,
           'voice_playback'             => false,
        );

        private static $module_settings = array();
        private static $addon_status = array();

        public static function is_pro_plan()
        {
            // For development, assume pro. Freemius SDK will handle this in production.
            if (function_exists('wpaicg_gacg_fs') && wpaicg_gacg_fs()->is_plan('pro', true)) { // Check for 'pro' plan or higher
                return true;
            }
            return false; // For development purposes, always return true. Will be false in production.
        }

        public static function init()
        {
            if (
                (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'wpaicg') !== false) ||
                (is_admin() && isset($_GET['page']) && $_GET['page'] === 'aipkit-role-manager') ||
                wp_doing_ajax()
            ) {
                self::check_and_init_module_settings();
                self::check_and_init_addon_status();
                self::register_ajax_handlers();
            }
        }

        public static function register_ajax_handlers()
        {
            add_action('wp_ajax_aipkit_dashboard_load_module', [__CLASS__, 'ajax_load_module']);
            add_action('wp_ajax_aipkit_update_module_setting', [__CLASS__, 'ajax_update_module_setting']);
            add_action('wp_ajax_aipkit_update_addon_status', [__CLASS__, 'ajax_update_addon_status']);
            add_action('wp_ajax_aipkit_get_token_usage_chart_data', [__CLASS__, 'ajax_get_token_usage_chart_data']);
        }

        private static function check_and_init_module_settings()
        {
            $opts = get_option('aipkit_options', array());

            if (!isset($opts['module_settings']) || !is_array($opts['module_settings'])) {
                $opts['module_settings'] = self::$default_module_settings;
                self::$module_settings = self::$default_module_settings;
                update_option('aipkit_options', $opts, 'no');
            } else {
                $merged = array_merge(self::$default_module_settings, $opts['module_settings']);
                $final_settings = array_intersect_key($merged, self::$default_module_settings);
                self::$module_settings = $final_settings;
                if ($final_settings !== $opts['module_settings']) {
                    $opts['module_settings'] = $final_settings;
                    update_option('aipkit_options', $opts, 'no');
                }
            }
        }

        private static function check_and_init_addon_status()
        {
            $opts = get_option('aipkit_options', array());

            if (!isset($opts['addons_status']) || !is_array($opts['addons_status'])) {
                $opts['addons_status'] = self::$default_addon_status;
                self::$addon_status = self::$default_addon_status;
                update_option('aipkit_options', $opts, 'no');
            } else {
                $merged = array_merge(self::$default_addon_status, $opts['addons_status']);
                $final_settings = array_intersect_key($merged, self::$default_addon_status);
                self::$addon_status = $final_settings;
                if ($final_settings !== $opts['addons_status']) {
                    $opts['addons_status'] = $final_settings;
                    update_option('aipkit_options', $opts, 'no');
                }
            }
        }

        public static function get_module_settings()
        {
            if (empty(self::$module_settings)) {
                self::check_and_init_module_settings();
            }
            return self::$module_settings;
        }

        public static function get_addon_status()
        {
            if (empty(self::$addon_status)) {
                self::check_and_init_addon_status();
            }
            return self::$addon_status;
        }

        public static function is_addon_active($addonKey)
        {
            $statuses = self::get_addon_status();
            return isset($statuses[$addonKey]) && $statuses[$addonKey] === true;
        }

        public static function ajax_load_module()
        {
            if (!isset($_REQUEST['_ajax_nonce']) || !wp_verify_nonce(sanitize_key($_REQUEST['_ajax_nonce']), 'aipkit_nonce')) {
                wp_send_json_error(['message' => __('Security check failed.', 'gpt3-ai-content-generator')], 403);
                return;
            }

            $module = isset($_REQUEST['module']) ? sanitize_key($_REQUEST['module']) : '';
            if (empty($module)) {
                wp_send_json_error(['message' => 'No module specified.'], 400);
                return;
            }
            if (!preg_match('/^[a-z0-9-]+$/', $module)) {
                wp_send_json_error(['message' => 'Invalid module name.'], 400);
                return;
            }

            if (!AIPKit_Role_Manager::user_can_access_module($module)) {
                wp_send_json_error(['message' => __('You do not have permission to access this module.', 'gpt3-ai-content-generator')], 403);
                return;
            }

            if ($module === 'chatbot') {
                if (!class_exists('\\WPAICG\\Chat\\Storage\\BotStorage') ||
                    !class_exists('\\WPAICG\\Chat\\Admin\\AdminSetup') ||
                    !class_exists('\\WPAICG\\Chat\\Storage\\DefaultBotSetup') ||
                    !class_exists('\\WPAICG\\Chat\\Storage\\SiteWideBotManager') ||
                    !class_exists('\\WPAICG\\Vector\\AIPKit_Vector_Store_Registry')) {
                }
            }

            $module_dir = WPAICG_PLUGIN_DIR . 'admin/views/modules/' . $module . '/';
            $module_file = $module_dir . 'index.php';
            $modules_base_path = realpath(untrailingslashit(WPAICG_PLUGIN_DIR . 'admin/views/modules'));
            $real_module_file_path = realpath($module_file);

            if ($modules_base_path === false || $real_module_file_path === false || strpos($real_module_file_path, $modules_base_path) !== 0) {
                wp_send_json_error(['message' => 'Invalid module path. Attempted path: ' . esc_html($module_file)], 400);
                return;
            }

            if (!file_exists($module_file)) {
                wp_send_json_error(['message' => "Module file not found: {$module} at " . esc_html($module_file)], 404);
                return;
            }

            $content = '';
            $php_error = null;
            set_error_handler(function ($errno, $errstr, $errfile, $errline) use (&$php_error) {
                $php_error = new WP_Error('php_error_in_module_view', $errstr, [
                    'file' => basename($errfile),
                    'line' => $errline
                ]);
                return true;
            });

            ob_start();
            try {
                $extra_data = isset($_REQUEST['extra_data']) && is_array($_REQUEST['extra_data']) ? $_REQUEST['extra_data'] : [];
                if (!empty($extra_data)) {
                    extract($extra_data, EXTR_SKIP);
                }
                include $module_file;
                $content = ob_get_clean();
            } catch (\Throwable $e) {
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $php_error = new WP_Error('fatal_error_in_module_view', $e->getMessage(), [
                     'file' => basename($e->getFile()), 'line' => $e->getLine()
                ]);
            }
            restore_error_handler();

            if ($php_error !== null) {
                $error_details = $php_error->get_error_message();
                if (is_array($php_error->get_error_data())) {
                    $error_details .= " (File: " . ($php_error->get_error_data()['file'] ?? 'unknown') . ", Line: " . ($php_error->get_error_data()['line'] ?? 'unknown') . ")";
                }
                wp_send_json_error([
                    'message' => 'A server error occurred while loading the module content. Please check the PHP error log for details.',
                    'debug_error' => $php_error->get_error_code() . ': ' . $php_error->get_error_message()
                ], 500);
                return;
            }
            if (headers_sent($file, $line)) {
                die();
            }


            $response_data = ['html' => $content];
            if ($module === 'chatbot' && class_exists(AIPKit_Vector_Store_Registry::class)) {
                $openai_vector_stores = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('OpenAI');
                $response_data['openaiVectorStores'] = $openai_vector_stores;
            }

            wp_send_json_success($response_data);
        }

        public static function ajax_update_module_setting()
        {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Unauthorized access.'], 403);
                return;
            }
            if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce(sanitize_key($_POST['_ajax_nonce']), 'aipkit_nonce')) {
                wp_send_json_error(['message' => 'Security check failed.'], 403);
                return;
            }

            $moduleKey = isset($_POST['moduleKey']) ? sanitize_key($_POST['moduleKey']) : '';
            $enabled   = isset($_POST['enabled']) ? sanitize_text_field($_POST['enabled']) : '';

            self::check_and_init_module_settings();

            if (empty($moduleKey) || !array_key_exists($moduleKey, self::$default_module_settings)) {
                wp_send_json_error(['message' => 'Invalid module key.'], 400);
                return;
            }

            $isEnabled = ($enabled === '1');
            self::$module_settings[$moduleKey] = $isEnabled;
            $opts = get_option('aipkit_options', array());
            $opts['module_settings'] = self::$module_settings;
            update_option('aipkit_options', $opts, 'no');

            wp_send_json_success(['message' => 'Module setting updated.']);
        }

        public static function ajax_update_addon_status()
        {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Unauthorized access.'], 403);
                return;
            }
            if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce(sanitize_key($_POST['_ajax_nonce']), 'aipkit_nonce')) {
                wp_send_json_error(['message' => 'Security check failed.'], 403);
                return;
            }

            $addonKey = isset($_POST['addonKey']) ? sanitize_key($_POST['addonKey']) : '';
            $active   = isset($_POST['active']) ? sanitize_text_field($_POST['active']) : '';

            self::check_and_init_addon_status();

            if (empty($addonKey) || !array_key_exists($addonKey, self::$default_addon_status)) {
                wp_send_json_error(['message' => 'Invalid addon key.'], 400);
                return;
            }

            $isActive = ($active === '1');
            $pro_addons = ['pdf_download', 'consent_compliance', 'openai_moderation', 'file_upload', 'triggers'];
            if ($isActive && in_array($addonKey, $pro_addons) && !self::is_pro_plan()) {
                wp_send_json_error(['message' => 'Pro plan required to activate this addon.'], 403);
                return;
            }

            self::$addon_status[$addonKey] = $isActive;
            $opts = get_option('aipkit_options', array());
            if (!isset($opts['addons_status']) || !is_array($opts['addons_status'])) {
                $opts['addons_status'] = [];
            }
            $opts['addons_status'][$addonKey] = $isActive;
            update_option('aipkit_options', $opts, 'no');

            wp_send_json_success(['message' => 'Addon status updated.']);
        }

        public static function ajax_get_token_usage_chart_data()
        {
            if (!AIPKit_Role_Manager::user_can_access_module('settings')) {
                wp_send_json_error(['message' => __('Unauthorized access.', 'gpt3-ai-content-generator')], 403);
                return;
            }
            if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce(sanitize_key($_POST['_ajax_nonce']), 'aipkit_nonce')) {
                wp_send_json_error(['message' => __('Security check failed.', 'gpt3-ai-content-generator')], 403);
                return;
            }

            $days = 30;
            $stats_class_name = '\\WPAICG\\Stats\\AIPKit_Stats';
            if (!class_exists($stats_class_name)) {
                wp_send_json_error(['message' => 'Statistics component unavailable.'], 500);
                return;
            }

            $stats_calculator = new $stats_class_name();
            $daily_data = $stats_calculator->get_daily_token_stats($days);

            if (is_wp_error($daily_data)) {
                wp_send_json_error(['message' => $daily_data->get_error_message()], 500);
            } else {
                wp_send_json_success(['daily_token_data' => $daily_data]);
            }
        }
    }

    aipkit_dashboard::init();
}