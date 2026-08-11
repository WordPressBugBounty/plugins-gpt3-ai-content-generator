<?php


namespace WPAICG\AutoGPT\Cron\Queuer;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/helpers/build-index-item-config.php';
require_once __DIR__ . '/helpers/insert-item-into-queue.php';

/**
 * Queues a bounded, resumable window of posts modified since the last scan.
 *
 * @return array{last_run_time:string} Safe watermark for the parent task row.
 */
function queue_new_or_updated_indexing_content_logic(int $task_id, array $task_config, ?string $last_run_time): array
{
    global $wpdb;
    $queue_table_name = $wpdb->prefix . 'aipkit_automated_task_queue';
    $state_key = 'aipkit_incremental_queue_state_v2_' . $task_id;
    $state = get_option($state_key, []);
    if (!is_array($state) || empty($state['since']) || empty($state['until'])) {
        $state = [
            'since' => $last_run_time ?: '1970-01-01 00:00:00',
            'until' => current_time('mysql', true),
            'cursor_modified' => $last_run_time ?: '1970-01-01 00:00:00',
            'cursor_id' => 0,
        ];
    }

    $available_capacity = Helpers\get_queue_window_capacity_logic($wpdb, $queue_table_name, $task_id);
    if ($available_capacity <= 0) {
        update_option($state_key, $state, false);
        return ['last_run_time' => (string) $state['since']];
    }
    $batch_size = min(200, $available_capacity);
    $post_types = array_values(array_filter(array_map('sanitize_key', (array) ($task_config['post_types'] ?? ['post']))));
    if (empty($post_types)) {
        $post_types = ['post'];
    }

    $post_type_placeholders = implode(',', array_fill(0, count($post_types), '%s'));
    $query_args = array_merge(
        ['publish'],
        $post_types,
        [
            (string) $state['since'],
            (string) $state['until'],
            (string) $state['cursor_modified'],
            (string) $state['cursor_modified'],
            absint($state['cursor_id'] ?? 0),
            $batch_size,
        ]
    );
    $posts_query = "SELECT ID, post_modified_gmt FROM {$wpdb->posts} WHERE post_status = %s AND post_type IN ($post_type_placeholders) AND post_modified_gmt > %s AND post_modified_gmt <= %s AND (post_modified_gmt > %s OR (post_modified_gmt = %s AND ID > %d)) ORDER BY post_modified_gmt ASC, ID ASC LIMIT %d";
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Bounded keyset scan of WordPress posts with prepared post types and watermarks.
    $posts = $wpdb->get_results($wpdb->prepare($posts_query, $query_args), ARRAY_A);
    $item_config = Helpers\build_index_item_config_logic($task_config);
    foreach ((array) $posts as $post) {
        Helpers\insert_item_into_queue_logic($wpdb, $queue_table_name, $task_id, absint($post['ID'] ?? 0), 'content_indexing', $item_config);
    }

    if (count((array) $posts) >= $batch_size) {
        $last_post = end($posts);
        $state['cursor_modified'] = (string) ($last_post['post_modified_gmt'] ?? $state['cursor_modified']);
        $state['cursor_id'] = absint($last_post['ID'] ?? $state['cursor_id']);
        update_option($state_key, $state, false);
        return ['last_run_time' => (string) $state['since']];
    }

    delete_option($state_key);
    return ['last_run_time' => (string) $state['until']];
}
