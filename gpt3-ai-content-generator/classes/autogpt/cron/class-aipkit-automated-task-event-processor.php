<?php


namespace WPAICG\AutoGPT\Cron;

// Use statements for the new modularized logic files
use WPAICG\AutoGPT\Cron\EventProcessor\MainEvents;

if (!defined('ABSPATH')) {
    exit;
}

// Load helper and sub-processor logic files that are dependencies for the main events
require_once __DIR__ . '/event-processor/trigger/trigger-content-indexing-task.php';
require_once __DIR__ . '/event-processor/trigger/trigger-content-writing-task.php';
require_once __DIR__ . '/event-processor/processor/process-queue-item.php';
require_once __DIR__ . '/event-processor/helpers/load-required-classes.php';
require_once __DIR__ . '/event-processor/helpers/update-queue-status.php';
require_once __DIR__ . '/event-processor/helpers/maybe-reschedule-queue.php';
require_once __DIR__ . '/event-processor/helpers/log-cron-error.php';
require_once __DIR__ . '/event-processor/helpers/worker-policy.php';

// Load the new main event files which contain the refactored logic
require_once __DIR__ . '/event-processor/main-events/trigger-task-event.php';
require_once __DIR__ . '/event-processor/main-events/process-task-queue-event.php';


/**
 * Handles processing of cron events for Automated Tasks by dispatching to modular logic.
 */
class AIPKit_Automated_Task_Event_Processor
{
    public const MAIN_CRON_HOOK = 'aipkit_process_automated_task_queue';

    /**
     * Callback for task-specific cron events. Triggers processing for that task.
     */
    public static function trigger_task_event(int $task_id)
    {
        if (class_exists(AIPKit_Automation_Runner::class)) {
            AIPKit_Automation_Runner::run_wp_cron_task($task_id);
            return;
        }

        MainEvents\trigger_task_event_logic($task_id);
    }

    /**
     * Executes task trigger logic after the shared runner has acquired its lock.
     */
    public static function process_task_trigger(int $task_id, bool $schedule_queue_event = true): bool
    {
        return MainEvents\trigger_task_event_logic($task_id, $schedule_queue_event);
    }

    /**
     * Callback for the main cron hook. Processes items from the task queue.
     */
    public static function process_task_queue_event()
    {
        if (class_exists(AIPKit_Automation_Runner::class)) {
            AIPKit_Automation_Runner::run_wp_cron_queue();
            return;
        }

        MainEvents\process_task_queue_event_logic();
    }

    /**
     * Processes one automatic, time-budgeted worker pass after the shared runner
     * acquires its lock.
     *
     * @return array<string, int|bool>
     */
    public static function process_queue_batch(bool $schedule_follow_up = true, string $source = 'wp_cron'): array
    {
        return MainEvents\process_task_queue_event_logic($schedule_follow_up, $source);
    }
}
