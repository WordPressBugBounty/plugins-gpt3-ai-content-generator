<?php


namespace WPAICG\AutoGPT\Cron;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/class-aipkit-option-lock.php';

// Require the new logic files
require_once __DIR__ . '/queuer/maybe-queue-initial-indexing-content.php';
require_once __DIR__ . '/queuer/queue-new-or-updated-indexing-content.php';

/**
 * Handles queueing content for automated tasks, specifically for content indexing.
 * This class now acts as a router/wrapper for the modularized logic functions.
 */
class AIPKit_Automated_Task_Content_Queuer
{
    /**
     * Queues initial content for indexing based on task configuration.
     * Delegates logic to an external function.
     */
    public static function maybe_queue_initial_indexing_content(int $task_id, array $task_config, bool $force_all = false)
    {
        $lock_token = self::acquire_producer_lock($task_id);
        if ($lock_token === '') {
            return;
        }

        try {
            Queuer\maybe_queue_initial_indexing_content_logic($task_id, $task_config, $force_all);
        } finally {
            self::release_producer_lock($task_id, $lock_token);
        }
    }

    /**
     * Queues new or updated content for indexing since the last run time.
     * Delegates logic to an external function.
     */
    public static function queue_new_or_updated_indexing_content(int $task_id, array $task_config, ?string $last_run_time)
    {
        $lock_token = self::acquire_producer_lock($task_id);
        if ($lock_token === '') {
            return ['last_run_time' => $last_run_time ?: '1970-01-01 00:00:00'];
        }

        try {
            return Queuer\queue_new_or_updated_indexing_content_logic($task_id, $task_config, $last_run_time);
        } finally {
            self::release_producer_lock($task_id, $lock_token);
        }
    }

    /** Removes persistent and legacy queue-materialization state for a deleted task. */
    public static function clear_task_state(int $task_id): void
    {
        if ($task_id <= 0) {
            return;
        }

        foreach (
            [
                'aipkit_initial_queue_cursor_v2_',
                'aipkit_initial_queue_done_v2_',
                'aipkit_initial_queue_force_v2_',
                'aipkit_incremental_queue_state_v2_',
                'aipkit_queue_producer_lock_v2_',
            ] as $option_prefix
        ) {
            delete_option($option_prefix . $task_id);
        }

        delete_transient('aipkit_initial_queue_page_' . $task_id);
        delete_transient('aipkit_initial_queue_done_' . $task_id);
    }

    /**
     * Acquires a short-lived, database-backed producer lock for this task.
     *
     * add_option() is atomic because option names are unique, preventing the
     * task event and queue worker from materializing the same window together.
     */
    private static function acquire_producer_lock(int $task_id): string
    {
        $lock_key = 'aipkit_queue_producer_lock_v2_' . $task_id;
        return AIPKit_Option_Lock::acquire($lock_key, 300);
    }

    /** Releases the producer lock after a bounded queueing pass. */
    private static function release_producer_lock(int $task_id, string $token): void
    {
        AIPKit_Option_Lock::release('aipkit_queue_producer_lock_v2_' . $task_id, $token);
    }
}
