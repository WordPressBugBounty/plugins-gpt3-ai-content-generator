<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/autogpt/cron/event-processor/trigger/module/manual-task-generator.php
// Status: NEW FILE

namespace WPAICG\AutoGPT\Cron\EventProcessor\Trigger\Modules;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generates items to be queued from a textarea input (single, bulk, or csv modes).
 *
 * @param array $task_config The configuration of the task.
 * @return array An array of items (strings).
 */
function manual_mode_generate_items_logic(array $task_config): array
{
    $content_titles_raw = $task_config['content_title'] ?? '';
    return array_filter(array_map('trim', explode("\n", $content_titles_raw)));
}
