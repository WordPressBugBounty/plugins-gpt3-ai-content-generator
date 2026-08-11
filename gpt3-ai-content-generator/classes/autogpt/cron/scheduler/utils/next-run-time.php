<?php

namespace WPAICG\AutoGPT\Cron\Scheduler\Utils;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Returns the interval, in seconds, for a recurring automation frequency.
 *
 * @param string $frequency WordPress cron schedule slug.
 * @return int Interval in seconds, or zero for one-time/invalid schedules.
 */
function get_frequency_interval_logic(string $frequency): int
{
    if ($frequency === 'one-time') {
        return 0;
    }

    $schedules = wp_get_schedules();
    return isset($schedules[$frequency]['interval'])
        ? absint($schedules[$frequency]['interval'])
        : 0;
}

/**
 * Calculates the first authoritative database run time for an active task.
 *
 * @param string $frequency WordPress cron schedule slug.
 * @return int|null UTC timestamp, or null for an invalid schedule.
 */
function calculate_initial_run_timestamp_logic(string $frequency): ?int
{
    if ($frequency === 'one-time') {
        return time() + 10;
    }

    if (get_frequency_interval_logic($frequency) <= 0) {
        return null;
    }

    return time() + 30;
}

/**
 * Advances a recurring task from its authoritative database run time.
 * Missed intervals are skipped so a delayed runner does not replay a burst.
 *
 * @param string   $frequency             WordPress cron schedule slug.
 * @param int|null $previous_run_timestamp Previous scheduled UTC timestamp.
 * @return int|null Next UTC timestamp, or null for one-time/invalid schedules.
 */
function calculate_next_run_timestamp_logic(string $frequency, ?int $previous_run_timestamp = null): ?int
{
    $interval = get_frequency_interval_logic($frequency);
    if ($interval <= 0) {
        return null;
    }

    $now = time();
    $base = $previous_run_timestamp && $previous_run_timestamp > 0
        ? $previous_run_timestamp
        : $now;
    $next = $base + $interval;

    if ($next <= $now) {
        $missed_intervals = (int) floor(($now - $next) / $interval) + 1;
        $next += $missed_intervals * $interval;
    }

    return $next;
}
