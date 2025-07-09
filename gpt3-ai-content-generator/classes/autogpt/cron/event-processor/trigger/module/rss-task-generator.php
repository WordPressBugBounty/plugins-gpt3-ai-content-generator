<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/autogpt/cron/event-processor/trigger/module/rss-task-generator.php
// Status: NEW FILE

namespace WPAICG\AutoGPT\Cron\EventProcessor\Trigger\Modules;

use WPAICG\aipkit_dashboard;
use WPAICG\Lib\ContentWriter\AIPKit_Rss_Feed_Parser;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/topic-filter-utils.php';

/**
 * Generates items to be queued from RSS feeds.
 *
 * @param int $task_id The ID of the task.
 * @param array $task_config The configuration of the task.
 * @param string|null $last_run_time The GMT timestamp of the last check (for scheduled runs) or null (for "Run Now").
 * @return array|WP_Error An array of items or WP_Error on failure.
 */
function rss_mode_generate_items_logic(int $task_id, array $task_config, ?string $last_run_time): array|WP_Error
{
    if (!aipkit_dashboard::is_pro_plan() || !class_exists(AIPKit_Rss_Feed_Parser::class)) {
        return new WP_Error('rss_feature_unavailable', __('RSS generation is a Pro feature or its components are missing.', 'gpt3-ai-content-generator'), ['status' => 403]);
    }

    $rss_parser = new AIPKit_Rss_Feed_Parser();
    $rss_feeds = $task_config['rss_feeds'] ?? '';
    $log_context = $last_run_time ? "scheduled run since {$last_run_time}" : "manual 'Run Now'";

    $topics_from_feed = $rss_parser->get_latest_items($rss_feeds, $last_run_time);

    $rss_include_keywords_str = $task_config['rss_include_keywords'] ?? '';
    $rss_exclude_keywords_str = $task_config['rss_exclude_keywords'] ?? '';
    $filtered_topics = apply_include_exclude_keywords_logic($topics_from_feed, $rss_include_keywords_str, $rss_exclude_keywords_str);

    return $filtered_topics;
}
