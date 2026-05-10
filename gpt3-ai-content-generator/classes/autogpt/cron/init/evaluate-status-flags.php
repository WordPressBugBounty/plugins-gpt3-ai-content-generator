<?php

namespace WPAICG\AutoGPT\Cron\Init;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quotes a plugin-owned table identifier after strict validation.
 *
 * @param string $table_name Table name.
 * @return string Backticked table identifier, or an empty string when invalid.
 */
function aipkit_get_validated_table_identifier_logic(string $table_name): string
{
    $table_name = trim($table_name);
    if ($table_name === '' || !preg_match('/^[A-Za-z0-9_]+$/', $table_name)) {
        return '';
    }

    return '`' . $table_name . '`';
}

/**
 * Evaluates the current state of active tasks and pending queue items against the previously known state.
 *
 * @param \wpdb $wpdb The WordPress database object.
 * @param string $tasks_table_name The name of the tasks table.
 * @return array An associative array with 'active_task_count', 'pending_queue_count', and 'did_active_tasks_exist'.
 */
function evaluate_status_flags_logic(\wpdb $wpdb, string $tasks_table_name): array
{
    $option_key_tasks_exist = 'aipkit_active_tasks_exist';
    $queue_table_name = $wpdb->prefix . 'aipkit_automated_task_queue';
    $tasks_table_identifier = aipkit_get_validated_table_identifier_logic($tasks_table_name);
    $queue_table_identifier = aipkit_get_validated_table_identifier_logic($queue_table_name);

    if ($tasks_table_identifier === '' || $queue_table_identifier === '') {
        return [
            'active_task_count' => 0,
            'pending_queue_count' => 0,
            'did_active_tasks_exist' => (bool) get_option($option_key_tasks_exist, false),
        ];
    }
    
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Direct query to a custom table; table identifier is plugin-owned, validated, and backticked above.
    $active_task_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$tasks_table_identifier} WHERE status = %s", 'active'));
    
    // Also check for pending items in the queue - these need processing even if no tasks are active
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Direct query to a custom table; table identifier is plugin-owned, validated, and backticked above.
    $pending_queue_count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$queue_table_identifier} WHERE status = %s", 'pending'));
    
    $did_active_tasks_exist = (bool) get_option($option_key_tasks_exist, false);

    return [
        'active_task_count' => $active_task_count,
        'pending_queue_count' => $pending_queue_count,
        'did_active_tasks_exist' => $did_active_tasks_exist,
    ];
}
