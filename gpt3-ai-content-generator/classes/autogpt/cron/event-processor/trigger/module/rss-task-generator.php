<?php

namespace WPAICG\AutoGPT\Cron\EventProcessor\Trigger\Modules;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

$premium_logic_path = WPAICG_LIB_DIR . 'autogpt/cron/event-processor/trigger/module/rss-task-generator.php';
if (file_exists($premium_logic_path)) {
    require_once $premium_logic_path;
}

/**
 * Safe fallback when the premium RSS runtime is not present.
 *
 * @param int $task_id The ID of the task.
 * @param array $task_config The configuration of the task.
 * @param string|null $last_run_time The GMT timestamp of the last check.
 * @return array|WP_Error An array of items or WP_Error on failure.
 */
if (!function_exists(__NAMESPACE__ . '\\rss_mode_generate_items_logic')) {
    function rss_mode_generate_items_logic(int $task_id, array $task_config, ?string $last_run_time): array|WP_Error
    {
        unset($task_id, $task_config, $last_run_time);

        return new WP_Error('rss_feature_unavailable', __('RSS generation is a Pro feature or its components are missing.', 'gpt3-ai-content-generator'), ['status' => 403]);
    }
}
