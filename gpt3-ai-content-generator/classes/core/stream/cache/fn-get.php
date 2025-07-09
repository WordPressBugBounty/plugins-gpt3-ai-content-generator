<?php

// File: classes/core/stream/cache/fn-get.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Cache;

use WP_Error;
use DateTime;
use DateTimeZone;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Retrieves a message from the cache using its key.
 * MODIFIED: If object cache is enabled but returns a miss, it now falls back to checking the database.
 *
 * @param \WPAICG\Core\Stream\Cache\AIPKit_SSE_Message_Cache $cacheInstance The instance of the cache class.
 * @param string $key The cache key.
 * @return string|WP_Error The message content on success, WP_Error if not found or expired.
 */
function get_logic(\WPAICG\Core\Stream\Cache\AIPKit_SSE_Message_Cache $cacheInstance, string $key): string|\WP_Error
{
    if (empty($key)) {
        return new WP_Error('sse_cache_empty_key', __('Cache key cannot be empty.', 'gpt3-ai-content-generator'));
    }

    // First, try the object cache if it's enabled.
    if ($cacheInstance->is_using_object_cache()) {
        $message = wp_cache_get($key, $cacheInstance::CACHE_GROUP);
        if ($message !== false) {
            // Found in object cache, return it.
            return $message;
        }
        // If not found in object cache, don't return an error yet. Fall through to check the DB.
        error_log("AIPKit SSE Cache (get_logic): Object cache miss for key {$key}. Falling back to DB.");
    }

    // DB fallback logic (for non-object-cache environments OR object cache misses)
    global $wpdb;
    $now_utc = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT message_content FROM {$cacheInstance->get_db_table_name()} WHERE cache_key = %s AND expires_at > %s LIMIT 1",
            $key,
            $now_utc
        ),
        ARRAY_A
    );

    if ($row) {
        return $row['message_content'];
    } else {
        // If it's not in the DB either, then it's truly not found or expired.
        $exists = $wpdb->get_var($wpdb->prepare("SELECT 1 FROM {$cacheInstance->get_db_table_name()} WHERE cache_key = %s LIMIT 1", $key));
        if ($exists) {
            return new WP_Error('sse_cache_expired', __('Cached message has expired.', 'gpt3-ai-content-generator'));
        } else {
            return new WP_Error('sse_cache_not_found', __('Message not found in cache.', 'gpt3-ai-content-generator'));
        }
    }
}
