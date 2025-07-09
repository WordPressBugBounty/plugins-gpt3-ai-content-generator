<?php
// File: classes/core/token-manager/reset/IsResetDueLogic.php

namespace WPAICG\Core\TokenManager\Reset;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for checking if a token reset is due based on the last reset timestamp and period.
 *
 * @param int    $last_reset_timestamp Unix timestamp of the last reset.
 * @param string $period 'daily', 'weekly', 'monthly', or 'never'.
 * @return bool True if a reset is due, false otherwise.
 */
function IsResetDueLogic(int $last_reset_timestamp, string $period): bool {
    if ($period === 'never' || $last_reset_timestamp <= 0) {
        return false;
    }

    $current_time = time();
    $next_reset_time = 0;

    // Get WordPress timezone offset
    $wp_timezone_offset = (int) get_option('gmt_offset') * HOUR_IN_SECONDS;

    // Adjust last_reset_timestamp to be based on UTC midnight of that day in WP timezone
    // This ensures resets happen consistently at the start of the day in WP's configured timezone.
    $last_reset_date_wp_tz = date('Y-m-d', $last_reset_timestamp);
    $last_reset_midnight_wp_tz_timestamp_utc = strtotime($last_reset_date_wp_tz . ' 00:00:00 America/New_York') - $wp_timezone_offset; // Using a placeholder and then adjusting
    // A more direct way to get UTC midnight of WP's current day for $last_reset_timestamp:
    $last_reset_object_utc = new \DateTime('@' . $last_reset_timestamp);
    $last_reset_object_utc->setTimezone(new \DateTimeZone(wp_timezone_string())); // Set to WP timezone
    $last_reset_object_utc->setTime(0,0,0); // Set to midnight in WP timezone
    $last_reset_midnight_utc = $last_reset_object_utc->getTimestamp(); // Get UTC timestamp of that midnight


    switch ($period) {
        case 'daily':
            // Next reset is midnight of the day *after* last_reset_midnight_utc
            $next_reset_time = strtotime('+1 day', $last_reset_midnight_utc);
            break;
        case 'weekly':
            $start_of_week_day_num = (int) get_option('start_of_week', 1); // 0 for Sunday, 1 for Monday
             // Convert WP start of week to PHP date('w') format (0=Sun, 1=Mon, ..., 6=Sat)
            $php_start_of_week_day = ($start_of_week_day_num === 0) ? 0 : ($start_of_week_day_num % 7);

            $last_reset_day_of_week_php = (int) date('w', $last_reset_midnight_utc);

            if ($last_reset_day_of_week_php === $php_start_of_week_day) {
                // If last reset was on the start of the week, next reset is next week's start day
                $next_reset_time = strtotime('next ' . date('l', strtotime("Sunday +{$php_start_of_week_day} days")), $last_reset_midnight_utc);
            } else {
                // Find the next occurrence of the start of the week
                $next_reset_time = strtotime('next ' . date('l', strtotime("Sunday +{$php_start_of_week_day} days")), $last_reset_midnight_utc);
                 // If the calculated next reset is still in the same week as last_reset_midnight_utc (or before it),
                 // it means the reset already happened for that week, so jump to the week after.
                 // This logic can be tricky. A simpler way is to ensure the next reset is truly after the last.
                 if ($next_reset_time <= $last_reset_midnight_utc) {
                    $next_reset_time = strtotime('+1 week', $next_reset_time);
                 }
            }
            break;
        case 'monthly':
            // Next reset is the first day of the month *after* the month of last_reset_midnight_utc
            $next_reset_time = strtotime('first day of next month', $last_reset_midnight_utc);
            break;
        default:
            return false; // Should not happen if period is validated before calling
    }

    // Ensure comparison is against the UTC timestamp of midnight in WP's timezone
    return $current_time >= $next_reset_time;
}