<?php
// File: classes/core/stream/cache/fn-run-db-cleanup.php

namespace WPAICG\Core\Stream\Cache;

use DateTime;
use DateTimeZone;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Cron callback function to delete expired cache entries from the DB table.
 * Only runs if external object cache is NOT being used.
 *
 * @return void
 */
function run_db_cleanup_logic(): void {
    error_log('AIPKit SSE Cache Cleanup (run_db_cleanup_logic): Cron job triggered.');

    if (wp_using_ext_object_cache()) {
        error_log('AIPKit SSE Cache Cleanup (run_db_cleanup_logic): Skipped DB cleanup (using object cache).');
        return;
    }

    global $wpdb;
    // Access DB_TABLE_SUFFIX via a constant or pass it in. For now, hardcoding for simplicity.
    $table = $wpdb->prefix . 'aipkit_sse_message_cache';
    $now_utc = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    $deleted_count = $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE expires_at <= %s", $now_utc));

    if ($deleted_count === false) {
        error_log('AIPKit SSE Cache Cleanup (run_db_cleanup_logic): Error deleting expired entries from DB. Error: ' . $wpdb->last_error);
    } else {
        error_log("AIPKit SSE Cache Cleanup (run_db_cleanup_logic): Deleted {$deleted_count} expired entries from DB table.");
    }
}