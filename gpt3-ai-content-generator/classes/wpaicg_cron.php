<?php

namespace WPAICG;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_Cron')) {
    class WPAICG_Cron
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('init',[$this,'wpaicg_cron_job'],1);
        }

        public function wpaicg_cron_job()
        {
            if(isset($_SERVER['argv']) && is_array($_SERVER['argv']) && count($_SERVER['argv'])){
                foreach( $_SERVER['argv'] as $arg ) {
                    $e = explode( '=', $arg );
                    if($e[0] == 'wpaicg_cron') {
                        if (count($e) == 2)
                            $_GET[$e[0]] = sanitize_text_field($e[1]);
                        else
                            $_GET[$e[0]] = 0;
                    }
                }
            }
            // Check if the cron trigger key is set and has the value 'yes'
            if(isset($_GET['wpaicg_cron']) && sanitize_text_field(wp_unslash($_GET['wpaicg_cron'])) === 'yes'){

                // Initialize WP Filesystem
                global $wp_filesystem;
                if (empty($wp_filesystem)) {
                    require_once ABSPATH . '/wp-admin/includes/file.php';
                    WP_Filesystem();
                }

                // Check if WP_Filesystem is initialized correctly
                if (empty($wp_filesystem)) {
                    // Log an error: Could not initialize WP_Filesystem
                    error_log('AI Power Cron Error: Could not initialize WP_Filesystem.');
                    exit; // Cannot proceed without filesystem access
                }

                $wpaicg_running_file = WPAICG_PLUGIN_DIR . 'wpaicg_running.txt';
                $wpaicg_error_file = WPAICG_PLUGIN_DIR . 'wpaicg_error.txt'; // Define error file path

                // Check if the process is already running (lock file exists)
                if(!$wp_filesystem->exists($wpaicg_running_file)) {
                    // Attempt to create the lock file
                    if ($wp_filesystem->put_contents($wpaicg_running_file, 'running', FS_CHMOD_FILE)) { // FS_CHMOD_FILE sets standard permissions
                        try {
                            // Simulate GET request for potential internal checks
                            // Note: Modifying $_SERVER directly might have unintended consequences.
                            // Consider if this is truly necessary or if components can be called directly.
                            $_SERVER["REQUEST_METHOD"] = 'GET';

                            // Execute the core logic
                            $wpaicg_custom_prompt_enable = get_option('wpaicg_custom_prompt_enable', false);
                            if($wpaicg_custom_prompt_enable){
                                $wpaicg_custom_prompt = WPAICG_Custom_Prompt::get_instance();
                                $wpaicg_custom_prompt->generator();
                            }
                            else{
                                $wpaicg_generator_content = WPAICG_Content::get_instance();
                                $wpaicg_generator_content->wpaicg_bulk_generator();
                            }
                        }
                        catch (\Exception $exception){
                            // Log the exception to the error file
                            $error_message = date_i18n('Y-m-d H:i:s') . ': ' . $exception->getMessage() . PHP_EOL; // Add timestamp

                            // Append error message
                            $existing_errors = $wp_filesystem->exists($wpaicg_error_file) ? $wp_filesystem->get_contents($wpaicg_error_file) : '';
                            if ($existing_errors === false) $existing_errors = ''; // Handle read error
                            $wp_filesystem->put_contents($wpaicg_error_file, $existing_errors . $error_message, FS_CHMOD_FILE);

                        }
                        finally {
                             // Ensure the running file is always deleted after execution attempt
                             $wp_filesystem->delete($wpaicg_running_file);
                        }
                    } else {
                        // Failed to create the lock file
                        $error_message = date_i18n('Y-m-d H:i:s') . ': Failed to create lock file: ' . $wpaicg_running_file . PHP_EOL;
                        $existing_errors = $wp_filesystem->exists($wpaicg_error_file) ? $wp_filesystem->get_contents($wpaicg_error_file) : '';
                        if ($existing_errors === false) $existing_errors = '';
                        $wp_filesystem->put_contents($wpaicg_error_file, $existing_errors . $error_message, FS_CHMOD_FILE);
                        error_log('AI Power Cron Error: Failed to create lock file: ' . $wpaicg_running_file);
                    }
                } else {
                    // Process already running or stale lock file exists
                     error_log('AI Power Cron: Process already running or lock file exists: ' . $wpaicg_running_file);
                }
                exit; // Exit after cron job attempt
            }
        }
    }
    WPAICG_Cron::get_instance();
}
