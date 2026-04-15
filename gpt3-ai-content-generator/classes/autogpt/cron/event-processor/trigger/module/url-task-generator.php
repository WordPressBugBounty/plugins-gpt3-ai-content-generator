<?php

namespace WPAICG\AutoGPT\Cron\EventProcessor\Trigger\Modules;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

$premium_logic_path = WPAICG_LIB_DIR . 'autogpt/cron/event-processor/trigger/module/url-task-generator.php';
if (file_exists($premium_logic_path)) {
    require_once $premium_logic_path;
}

/**
 * Safe fallback when the premium URL runtime is not present.
 *
 * @param int $task_id The ID of the task.
 * @param array $task_config The configuration of the task.
 * @return array|WP_Error An array containing 'topics' and 'contexts' or a WP_Error on failure.
 */
if (!function_exists(__NAMESPACE__ . '\\url_mode_generate_items_logic')) {
    function url_mode_generate_items_logic(int $task_id, array $task_config): array|WP_Error
    {
        unset($task_id, $task_config);

        return new WP_Error('url_feature_unavailable', __('URL extracting is a Pro feature or its components are missing.', 'gpt3-ai-content-generator'), ['status' => 403]);
    }
}
