<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/includes/class-wp-ai-content-generator.php
// Status: MODIFIED
// I have updated the `ensure_tables_exist` method call to `ensure_tables_for_current_site` to reflect the changes in the Activator class and clarified the comment explaining its purpose.

namespace WPAICG;

// --- Load Core Helper Classes FIRST ---
require_once WPAICG_PLUGIN_DIR . 'includes/class-aipkit-dependency-loader.php';
require_once WPAICG_PLUGIN_DIR . 'includes/class-aipkit-hook-manager.php';
require_once WPAICG_PLUGIN_DIR . 'includes/class-aipkit-module-initializer.php';
require_once WPAICG_PLUGIN_DIR . 'includes/class-aipkit-shared-assets-manager.php';
// --- END Load Core Helper Classes FIRST ---

// --- Use statements for NEW Core Helper Classes ---
use WPAICG\Includes\AIPKit_Dependency_Loader;
use WPAICG\Includes\AIPKit_Hook_Manager;
use WPAICG\Includes\AIPKit_Module_Initializer;
use WPAICG\Includes\AIPKit_Shared_Assets_Manager;

// --- END NEW ---

// Ensure Activator and Role Manager classes are loaded as we need their static methods/constants
require_once WPAICG_PLUGIN_DIR . 'includes/class-wp-ai-content-generator-activator.php';
require_once WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_role_manager.php'; // Needed for update check

if (!defined('ABSPATH')) {
    exit;
}

/**
 * The core plugin class. Bootstrapper.
 */
class WP_AI_Content_Generator
{
    private static $instance = null;
    private $version;
    private $plugin_name;
    public const DB_VERSION_OPTION = 'aipkit_plugin_version'; // Option to store current DB version

    public static function get_instance(): WP_AI_Content_Generator
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->version = defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.9.15';
        $this->plugin_name = 'gpt3-ai-content-generator';
    }

    /**
     * Run the plugin setup.
     * Load dependencies, define hooks, initialize modules, and ensure DB tables exist.
     */
    public function run()
    {
        // Load all dependencies using the new loader class
        AIPKit_Dependency_Loader::load();

        // Register shared assets (moved to a separate manager, called on init)
        add_action('init', [$this, 'register_shared_assets'], 0);

        // Check for plugin updates (version change)
        add_action('init', [$this, 'check_for_updates'], 10);

        // Ensure DB tables exist for all users (new and existing) on page load.
        add_action('plugins_loaded', [$this, 'ensure_tables_exist'], 15);

        // Define hooks using the new hook manager
        AIPKit_Hook_Manager::register_hooks($this->version);

        // Initialize modules using the new module initializer
        AIPKit_Module_Initializer::init($this->version);
    }

    /**
     * Ensures all DB tables exist for the current site.
     * Hooked to 'plugins_loaded', this check runs for existing users after an update,
     * not just on first-time activation.
     */
    public function ensure_tables_exist()
    {
        WP_AI_Content_Generator_Activator::ensure_tables_for_current_site();
    }

    /**
     * Register shared assets via the SharedAssetsManager.
     * Hooked to 'init' with priority 0.
     */
    public function register_shared_assets()
    {
        AIPKit_Shared_Assets_Manager::register($this->version);
    }

    /**
     * Check for plugin updates (e.g., version change) and run necessary routines.
     * Now runs on 'init' action hook, after i18n is loaded.
     */
    public function check_for_updates()
    {
        $current_version = $this->version;
        $saved_version = get_option(self::DB_VERSION_OPTION);

        if ($saved_version !== $current_version) {
            error_log("AIPKit: Plugin version changed from {$saved_version} to {$current_version}. Running update checks...");

            // Ensure Role Manager Permissions are Updated/Initialized
            if (class_exists('\\WPAICG\\AIPKit_Role_Manager')) {
                \WPAICG\AIPKit_Role_Manager::update_permissions_on_activation();
            } else {
                error_log("AIPKit Update Error: AIPKit_Role_Manager class not found during version update check.");
            }

            // Ensure Default Chatbot exists
            if (class_exists('\\WPAICG\\Chat\\Storage\\DefaultBotSetup')) {
                \WPAICG\Chat\Storage\DefaultBotSetup::ensure_default_chatbot();
            } else {
                error_log("AIPKit Update Error: DefaultBotSetup class not found during version update check.");
            }

            // Ensure Default Content Writer Template exists
            if (class_exists('\\WPAICG\\ContentWriter\\AIPKit_Content_Writer_Template_Manager')) {
                \WPAICG\ContentWriter\AIPKit_Content_Writer_Template_Manager::ensure_default_template_exists();
            } else {
                error_log("AIPKit Update Error: AIPKit_Content_Writer_Template_Manager class not found during version update check.");
            }

            // Ensure Default AI Forms exist
            if (class_exists('\\WPAICG\\AIForms\\Admin\\AIPKit_AI_Form_Defaults')) {
                \WPAICG\AIForms\Admin\AIPKit_AI_Form_Defaults::ensure_default_forms_exist();
            } else {
                error_log("AIPKit Update Error: AIPKit_AI_Form_Defaults class not found during version update check.");
            }

            // Ensure Cron Jobs are scheduled
            if (class_exists('\\WPAICG\\Core\\TokenManager\\AIPKit_Token_Manager')) {
                \WPAICG\Core\TokenManager\AIPKit_Token_Manager::schedule_token_reset_event();
            }
            if (class_exists('\\WPAICG\\Core\\Stream\\Cache\\AIPKit_SSE_Message_Cache')) {
                \WPAICG\Core\Stream\Cache\AIPKit_SSE_Message_Cache::schedule_cleanup_event();
            }
            if (class_exists('\\WPAICG\\AutoGPT\\AIPKit_Automated_Task_Cron')) {
                \WPAICG\AutoGPT\AIPKit_Automated_Task_Cron::init();
            }

            // Update the stored version
            update_option(self::DB_VERSION_OPTION, $current_version, 'no'); // Use autoload 'no'
        }
    }

    public function get_plugin_name(): string
    {
        return $this->plugin_name;
    }
    public function get_version(): string
    {
        return $this->version;
    }

} // End class
