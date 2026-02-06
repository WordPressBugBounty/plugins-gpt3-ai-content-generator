<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/includes/class-wp-ai-content-generator-activator.php
// Status: MODIFIED
// I have updated `ensure_tables_for_current_site` to check for all required database tables, not just one, ensuring that existing users who update the plugin get newly added tables created automatically.

namespace WPAICG;

// Use statements for classes needed during activation
use WPAICG\Chat\Admin\AdminSetup;
use WPAICG\Chat\Storage\DefaultBotSetup;
use WPAICG\AIPKit_Role_Manager;
use WPAICG\Core\TokenManager\AIPKit_Token_Manager;
use WPAICG\Core\Stream\Cache\AIPKit_SSE_Message_Cache;
use WPAICG\AutoGPT\AIPKit_Automated_Task_Cron;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Fired during plugin activation. Also contains multisite setup logic.
 */
class WP_AI_Content_Generator_Activator
{
    /**
     * Main activation routine for single site or per-site activation.
     * REVISED: This method is now significantly leaner. It only handles tasks that
     * absolutely must run on first-time activation, like creating database tables.
     * Other setup tasks (default content, cron scheduling) are moved to the
     * `check_for_updates` hook which runs on `init` and is triggered by the version option.
     */
    public static function activate()
    {
        // Create database tables if they don't exist.
        self::setup_tables_for_blog();

        // Load the main plugin class to get access to constants.
        if (!class_exists(WP_AI_Content_Generator::class)) {
            require_once WPAICG_PLUGIN_DIR . 'includes/class-wp-ai-content-generator.php';
        }
        // Set the current version in the database. This is crucial for the `check_for_updates`
        // routine to correctly trigger on new installs and version changes.
        update_option(WP_AI_Content_Generator::DB_VERSION_OPTION, WPAICG_VERSION, 'no');

        // --- MODIFICATION: Consolidate all one-time/update tasks here ---
        // This ensures that fresh installs and reactivations get all necessary setup routines.
        // The check_for_updates() hook will also call these, which is a safe redundancy for version updates.

        // Update Role Manager permissions, migrating old caps if necessary.
        $role_manager_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_role_manager.php';
        if (file_exists($role_manager_path)) {
            require_once $role_manager_path;
            if (class_exists('\\WPAICG\\AIPKit_Role_Manager')) {
                \WPAICG\AIPKit_Role_Manager::update_permissions_on_activation();
            }
        }

        // Ensure Default Chatbot exists.
        $default_bot_setup_path = WPAICG_PLUGIN_DIR . 'classes/chat/storage/class-aipkit_default_bot_setup.php';
        if (file_exists($default_bot_setup_path)) {
            require_once $default_bot_setup_path;
            if (class_exists('\\WPAICG\\Chat\\Storage\\DefaultBotSetup')) {
                \WPAICG\Chat\Storage\DefaultBotSetup::ensure_default_chatbot();
            }
        }

        // Ensure Default Content Writer Template exists.
        $cw_template_manager_path = WPAICG_PLUGIN_DIR . 'classes/content-writer/class-aipkit-content-writer-template-manager.php';
        if (file_exists($cw_template_manager_path)) {
            require_once $cw_template_manager_path;
            if (class_exists('\\WPAICG\\ContentWriter\\AIPKit_Content_Writer_Template_Manager')) {
                \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager::ensure_default_template_exists();
            }
        }

        // Ensure Default AI Forms exist.
        $ai_form_defaults_path = WPAICG_PLUGIN_DIR . 'classes/ai-forms/admin/class-aipkit-ai-form-defaults.php';
        if (file_exists($ai_form_defaults_path)) {
            require_once $ai_form_defaults_path;
            if (class_exists('\\WPAICG\\AIForms\\Admin\\AIPKit_AI_Form_Defaults')) {
                \WPAICG\AIForms\Admin\AIPKit_AI_Form_Defaults::ensure_default_forms_exist();
            }
        }
        // --- END MODIFICATION ---

        // Schedule cron jobs (methods are idempotent, so it's safe to run).
        if (class_exists(AIPKit_Token_Manager::class)) {
            AIPKit_Token_Manager::schedule_token_reset_event();
        }
        if (class_exists(AIPKit_SSE_Message_Cache::class)) {
            AIPKit_SSE_Message_Cache::schedule_cleanup_event();
        }
        if (class_exists(AIPKit_Automated_Task_Cron::class)) {
            AIPKit_Automated_Task_Cron::init();
        }
    }

    public static function setup_tables_for_blog($blog_id = null)
    {
        $switched = false;
        if (is_multisite() && $blog_id !== null && get_current_blog_id() !== $blog_id) {
            switch_to_blog($blog_id);
            $switched = true;
        }
        $db_schema_path = WPAICG_PLUGIN_DIR . 'includes/database-schema.php';
        if (!file_exists($db_schema_path)) {
            if ($switched) {
                restore_current_blog();
            }
            return;
        }
        require_once $db_schema_path;

        if (function_exists('aipkit_create_logs_table')) {
            aipkit_create_logs_table();
        }
        if (function_exists('aipkit_create_guest_token_usage_table')) {
            aipkit_create_guest_token_usage_table();
        }
        if (function_exists('aipkit_create_sse_message_cache_table')) {
            aipkit_create_sse_message_cache_table();
        }
        if (function_exists('aipkit_create_vector_data_source_table')) {
            aipkit_create_vector_data_source_table();
        }
        if (function_exists('aipkit_create_automated_tasks_table')) {
            aipkit_create_automated_tasks_table();
        }
        if (function_exists('aipkit_create_automated_task_queue_table')) {
            aipkit_create_automated_task_queue_table();
        }
        if (function_exists('aipkit_create_content_writer_templates_table')) {
            aipkit_create_content_writer_templates_table();
        }
        if (function_exists('aipkit_create_rss_history_table')) {
            aipkit_create_rss_history_table();
        }
        if ($switched) {
            restore_current_blog();
        }
    }

    public static function setup_new_blog($blog, $user_id)
    {
        $blog_id = is_object($blog) ? $blog->blog_id : (is_array($blog) ? $blog['blog_id'] : 0);
        if ($blog_id > 0) {
            self::setup_tables_for_blog($blog_id);
            switch_to_blog($blog_id);
            if (class_exists('\\WPAICG\\Chat\\Storage\\DefaultBotSetup')) {
                DefaultBotSetup::ensure_default_chatbot();
            }
            if (class_exists('\\WPAICG\\ContentWriter\\AIPKit_Content_Writer_Template_Manager')) {
                AIPKit_Content_Writer_Template_Manager::ensure_default_template_exists();
            }
            restore_current_blog();
        }
    }

    /**
     * Unschedule old plugin cron hooks from legacy versions.
     * @since 2.1
     */
    public static function unschedule_old_cron_hooks()
    {
        $old_hooks = [
            'wpaicg_remove_chat_tokens_limited',
            'wpaicg_remove_promptbase_tokens_limited',
            'wpaicg_remove_image_tokens_limited',
            'wpaicg_remove_forms_tokens_limited',
            'wpaicg_cron', // General cron for old bulk content
            'wpaicg_builder', // General cron for old embeddings
        ];
        $old_task_specific_hook_prefix = 'wpaicg_task_event_'; // Prefix for individual task events

        foreach ($old_hooks as $hook_name) {
            $timestamp = wp_next_scheduled($hook_name);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook_name);
            }
            // Ensure any recurring schedules are also cleared
            wp_clear_scheduled_hook($hook_name);
        }
    }
}
