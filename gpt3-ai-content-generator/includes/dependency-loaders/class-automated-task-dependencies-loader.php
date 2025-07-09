<?php

namespace WPAICG\Includes\DependencyLoaders;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Automated_Task_Dependencies_Loader {
    public static function load() {
        $autogpt_path = WPAICG_PLUGIN_DIR . 'classes/autogpt/';
        $main_manager_path = $autogpt_path . 'class-aipkit-automated-task-manager.php';
        $main_cron_path = $autogpt_path . 'class-aipkit-automated-task-cron.php';

        if (file_exists($main_manager_path)) { require_once $main_manager_path; }
        else { error_log("AIPKit Dependency Error: Main Automated Task Manager file not found at {$main_manager_path}"); }

        if (file_exists($main_cron_path)) { require_once $main_cron_path; }
        else { error_log("AIPKit Dependency Error: Main Automated Task Cron file not found at {$main_cron_path}"); }
    }
}