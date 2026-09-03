<?php


namespace WPAICG\AutoGPT\Cron\EventProcessor\MainEvents;

use WPAICG\AutoGPT\Cron\EventProcessor\Processor;
use WPAICG\AutoGPT\Cron\EventProcessor\Helpers;
use WPAICG\AutoGPT\Cron\AIPKit_Automated_Task_Content_Queuer;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Callback for the main cron hook. Processes items from the task queue.
 *
 * @param bool $schedule_follow_up Whether to schedule another WordPress cron queue event.
 * @param string $source Worker wake-up source.
 * @return array<string, int|bool> Processing summary.
 */
function process_task_queue_event_logic(bool $schedule_follow_up = true, string $source = 'wp_cron'): array
{
    global $wpdb;
    $queue_table_name = $wpdb->prefix . 'aipkit_automated_task_queue';

    Helpers\load_required_classes_logic();
    $policy = Helpers\get_automatic_worker_policy_logic($source);
    $started_at = microtime(true);
    $indexing_results = Helpers\reconcile_indexing_queue_items_logic();
    $recovered_items = recover_stale_queue_items_logic(
        $wpdb,
        $queue_table_name,
        $policy['stale_processing_seconds'],
        $policy['recovery_limit']
    );
    $processed_task_ids = $indexing_results['task_ids'];
    $processed_items = 0;
    $failed_items = $indexing_results['failed'];
    $processed_expensive_items = 0;
    $expensive_processing_seconds = 0.0;
    $task_cursor_option = 'aipkit_automation_queue_task_cursor_v1';
    $task_cursor = absint(get_option($task_cursor_option, 0));

    while ($processed_items < $policy['max_items']) {
        if ($processed_items > 0 && (microtime(true) - $started_at) >= $policy['time_budget_seconds']) {
            break;
        }

        $item = claim_next_queue_item_logic($wpdb, $queue_table_name, $task_cursor);
        if (empty($item)) {
            break;
        }

        $task_cursor = absint($item['task_id'] ?? 0);
        $processed_task_ids[] = $task_cursor;
        $is_expensive_item = Helpers\is_expensive_queue_task_logic((string) ($item['task_type'] ?? ''));
        $item_started_at = microtime(true);
        try {
            $result = Processor\process_queue_item_logic($item);
        } catch (\Throwable $throwable) {
            $result = [
                'status' => 'error',
                'message' => sprintf(
                    'Unexpected queue processing error: %s',
                    sanitize_text_field($throwable->getMessage())
                ),
            ];
        }

        if (!is_array($result) || !isset($result['status'])) {
            $result = [
                'status' => 'error',
                'message' => 'The queue processor returned an invalid result.',
            ];
        }

        $result_status = sanitize_key((string) $result['status']);
        if (!in_array($result_status, ['success', 'error', 'failed'], true)) {
            $result = [
                'status' => 'error',
                'message' => 'The queue processor returned an unsupported status.',
            ];
            $result_status = 'error';
        } else {
            $result['status'] = $result_status;
        }

        if ($result_status === 'success' && !empty($result['processing']) && ($item['task_type'] ?? '') === 'content_indexing') {
            if (Helpers\defer_indexing_queue_item_logic($item, $result)) {
                $result_status = 'processing';
            } else {
                $result_status = 'error';
                $result['message'] = __('Could not save indexing progress. Check Sources before retrying.', 'gpt3-ai-content-generator');
            }
        }

        Helpers\update_queue_status_logic($item['id'], $result_status, $result['message'] ?? null);
        ++$processed_items;

        if (in_array($result_status, ['error', 'failed'], true)) {
            ++$failed_items;
            Helpers\log_cron_error_logic(
                sprintf(
                    'Failed to process item ID %d (Task Type: %s). Reason: %s',
                    absint($item['id'] ?? 0),
                    sanitize_key((string) ($item['task_type'] ?? 'unknown')),
                    (string) ($result['message'] ?? 'Unknown error')
                )
            );
        }

        if ($is_expensive_item) {
            ++$processed_expensive_items;
            $expensive_processing_seconds += microtime(true) - $item_started_at;
        }
        if (Helpers\is_rate_limited_result_logic($result)) {
            break;
        }
        if (
            $is_expensive_item
            && !Helpers\should_continue_after_expensive_item_logic(
                $policy,
                $processed_expensive_items,
                $expensive_processing_seconds,
                microtime(true) - $started_at
            )
        ) {
            break;
        }
    }

    if ($processed_items > 0) {
        update_option($task_cursor_option, $task_cursor, false);
    }

    refill_content_indexing_queue_windows_logic(array_values(array_unique(array_filter($processed_task_ids))));
    if ($schedule_follow_up) {
        Helpers\maybe_reschedule_queue_logic();
    }

    // A bounded existence check avoids an expensive exact count on very large queues.
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Indexed existence check on the plugin queue table.
    $has_remaining_items = (bool) $wpdb->get_var(
        $wpdb->prepare(
            "SELECT 1 FROM " . esc_sql($queue_table_name) . " WHERE status = %s LIMIT 1",
            'pending'
        )
    );

    return [
        'processed_items' => $processed_items + $indexing_results['completed'] + $indexing_results['failed'],
        'failed_items' => $failed_items,
        'has_remaining_items' => $has_remaining_items || Helpers\has_waiting_indexing_queue_items_logic(),
        'recovered_items' => $recovered_items,
    ];
}

/**
 * Atomically claims the next pending queue row.
 *
 * @return array<string, mixed>|null
 */
function claim_next_queue_item_logic(\wpdb $wpdb, string $queue_table_name, int $after_task_id = 0): ?array
{
    for ($claim_attempt = 0; $claim_attempt < 3; ++$claim_attempt) {
        if ($after_task_id > 0) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Indexed, one-row fair-queue lookup in a plugin-owned table.
            $item = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM " . esc_sql($queue_table_name) . " WHERE status = %s AND task_id > %d ORDER BY task_id ASC, id ASC LIMIT 1",
                    'pending',
                    $after_task_id
                ),
                ARRAY_A
            );
        } else {
            $item = null;
        }
        if (!is_array($item) || empty($item['id'])) {
            // Wrap to the first task after reaching the end of the task-ID sequence.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Indexed, one-row fair-queue lookup in a plugin-owned table.
            $item = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM " . esc_sql($queue_table_name) . " WHERE status = %s ORDER BY task_id ASC, id ASC LIMIT 1",
                    'pending'
                ),
                ARRAY_A
            );
        }
        if (!is_array($item) || empty($item['id'])) {
            return null;
        }

        $claimed_at = current_time('mysql', true);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Conditional update atomically claims a plugin-owned queue row.
        $claimed = $wpdb->query(
            $wpdb->prepare(
                "UPDATE " . esc_sql($queue_table_name) . " SET status = %s, last_attempt_time = %s, sort_priority = %d WHERE id = %d AND status = %s",
                'processing',
                $claimed_at,
                2,
                absint($item['id']),
                'pending'
            )
        );
        if ($claimed === 1) {
            $item['status'] = 'processing';
            $item['last_attempt_time'] = $claimed_at;
            $item['sort_priority'] = 2;
            return $item;
        }
        $after_task_id = absint($item['task_id'] ?? $after_task_id);
    }

    return null;
}

/**
 * Returns abandoned processing rows to pending in small, bounded chunks.
 */
function recover_stale_queue_items_logic(
    \wpdb $wpdb,
    string $queue_table_name,
    int $stale_after_seconds,
    int $recovery_limit
): int {
    $cutoff = gmdate('Y-m-d H:i:s', time() - max(1, $stale_after_seconds));
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bounded recovery update in a plugin-owned queue table.
    $recovered = $wpdb->query(
        $wpdb->prepare(
            "UPDATE " . esc_sql($queue_table_name) . " SET status = %s, sort_priority = %d WHERE status = %s AND NOT (task_type = %s AND COALESCE(item_config, '') LIKE %s) AND ((last_attempt_time IS NOT NULL AND last_attempt_time < %s) OR (last_attempt_time IS NULL AND added_at < %s)) LIMIT %d",
            'pending',
            1,
            'processing',
            'content_indexing',
            '%' . $wpdb->esc_like('"_aipkit_indexing_job":') . '%',
            $cutoff,
            $cutoff,
            max(1, $recovery_limit)
        )
    );
    $recovered = max(0, (int) $recovered);
    if ($recovered > 0) {
        Helpers\log_cron_error_logic(
            sprintf('Recovered %d abandoned automation queue item(s).', $recovered)
        );
    }

    return $recovered;
}

/** Refills bounded initial-indexing windows after the worker creates capacity. */
function refill_content_indexing_queue_windows_logic(array $task_ids): void
{
    if (empty($task_ids) || !class_exists(AIPKit_Automated_Task_Content_Queuer::class)) {
        return;
    }

    global $wpdb;
    $tasks_table_name = $wpdb->prefix . 'aipkit_automated_tasks';
    $placeholders = implode(',', array_fill(0, count($task_ids), '%d'));
    $params = array_merge(['content_indexing'], $task_ids);
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Bounded lookup of the task rows represented in the just-processed worker batch.
    $tasks = $wpdb->get_results($wpdb->prepare("SELECT id, task_config, status, last_run_time FROM {$tasks_table_name} WHERE task_type = %s AND id IN ($placeholders)", ...$params), ARRAY_A);
    foreach ((array) $tasks as $task) {
        $task_id = absint($task['id'] ?? 0);
        $task_config = json_decode((string) ($task['task_config'] ?? ''), true);
        if ($task_id <= 0 || !is_array($task_config)) {
            continue;
        }
        $initial_requested = ($task_config['index_existing_now_flag'] ?? '0') === '1';
        $force_requested = get_option('aipkit_initial_queue_force_v2_' . $task_id, '') === 'yes';
        if ($initial_requested || $force_requested) {
            AIPKit_Automated_Task_Content_Queuer::maybe_queue_initial_indexing_content($task_id, $task_config, false);
        }

        $incremental_requested = ($task_config['only_new_updated_flag'] ?? '0') === '1';
        if ($incremental_requested && ($task['status'] ?? '') === 'active') {
            $run_state = AIPKit_Automated_Task_Content_Queuer::queue_new_or_updated_indexing_content(
                $task_id,
                $task_config,
                isset($task['last_run_time']) ? (string) $task['last_run_time'] : null
            );
            if (!empty($run_state['last_run_time']) && $run_state['last_run_time'] !== ($task['last_run_time'] ?? null)) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Updates the safe watermark for the plugin-owned task row after a bounded refill.
                $wpdb->update(
                    $tasks_table_name,
                    ['last_run_time' => (string) $run_state['last_run_time']],
                    ['id' => $task_id],
                    ['%s'],
                    ['%d']
                );
            }
        }
    }
}
