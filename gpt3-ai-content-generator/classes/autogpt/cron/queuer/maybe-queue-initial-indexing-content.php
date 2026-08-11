<?php


namespace WPAICG\AutoGPT\Cron\Queuer;

use WP_Query;

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/helpers/build-index-item-config.php';
require_once __DIR__ . '/helpers/insert-item-into-queue.php';
require_once __DIR__ . '/helpers/update-task-flag.php';

/**
 * Queues a bounded window of initial content using a durable keyset cursor.
 *
 * A task never materializes its full site into the queue. The worker refills
 * this window as rows are processed, keeping memory, query time, and database
 * growth bounded even on sites with millions of posts.
 */
function maybe_queue_initial_indexing_content_logic(int $task_id, array $task_config, bool $force_all = false): void
{
    global $wpdb;
    $queue_table_name = $wpdb->prefix . 'aipkit_automated_task_queue';
    $tasks_table_name = $wpdb->prefix . 'aipkit_automated_tasks';
    $cursor_key = 'aipkit_initial_queue_cursor_v2_' . $task_id;
    $completed_key = 'aipkit_initial_queue_done_v2_' . $task_id;
    $force_key = 'aipkit_initial_queue_force_v2_' . $task_id;
    $legacy_page_key = 'aipkit_initial_queue_page_' . $task_id;
    $legacy_completed_key = 'aipkit_initial_queue_done_' . $task_id;

    if ($force_all) {
        delete_option($cursor_key);
        delete_option($completed_key);
        update_option($force_key, 'yes', false);
        delete_transient($legacy_page_key);
        delete_transient($legacy_completed_key);
    }

    $initial_indexing_requested = ($task_config['index_existing_now_flag'] ?? '0') === '1';
    $force_scan_requested = get_option($force_key, '') === 'yes';
    $is_completed = get_option($completed_key, '') === 'yes';
    if (!$is_completed && get_transient($legacy_completed_key)) {
        update_option($completed_key, 'yes', false);
        $is_completed = true;
    }
    if (!$force_all && ($is_completed || (!$initial_indexing_requested && !$force_scan_requested))) {
        return;
    }

    $available_capacity = Helpers\get_queue_window_capacity_logic($wpdb, $queue_table_name, $task_id);
    if ($available_capacity <= 0) {
        return;
    }
    $batch_size = min(200, $available_capacity);
    $cursor_state = get_option($cursor_key, []);
    if (!is_array($cursor_state)) {
        $cursor_state = [];
    }
    $item_config = Helpers\build_index_item_config_logic($task_config);
    $specific_post_ids = isset($task_config['specific_post_ids']) && is_array($task_config['specific_post_ids'])
        ? array_values(array_filter(array_map('absint', $task_config['specific_post_ids'])))
        : [];

    if (!empty($specific_post_ids)) {
        $specific_offset = ($cursor_state['mode'] ?? '') === 'specific' ? absint($cursor_state['offset'] ?? 0) : 0;
        $specific_batch = array_slice($specific_post_ids, $specific_offset, $batch_size);
        if (!empty($specific_batch)) {
            $query = new WP_Query([
                'post_type' => !empty($task_config['post_types']) ? $task_config['post_types'] : 'any',
                'post_status' => 'publish',
                'post__in' => $specific_batch,
                'posts_per_page' => count($specific_batch),
                'fields' => 'ids',
                'orderby' => 'post__in',
                'no_found_rows' => true,
            ]);
            foreach ((array) $query->posts as $post_id) {
                Helpers\insert_item_into_queue_logic($wpdb, $queue_table_name, $task_id, absint($post_id), 'content_indexing', $item_config);
            }
            $specific_offset += count($specific_batch);
        }

        if ($specific_offset < count($specific_post_ids)) {
            update_option($cursor_key, ['mode' => 'specific', 'offset' => $specific_offset], false);
            return;
        }

        complete_initial_queue_scan_logic($wpdb, $tasks_table_name, $task_id, $task_config, $cursor_key, $completed_key, $force_key, $force_all);
        return;
    }

    $last_post_id = ($cursor_state['mode'] ?? '') === 'all' ? absint($cursor_state['post_id'] ?? 0) : 0;
    if ($last_post_id <= 0 && (int) get_transient($legacy_page_key) > 1) {
        // Upgrade an in-progress page cursor without restarting a very large scan.
        // The task_id index is ordered by the primary key, so this lookup remains bounded.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- One-row migration lookup in a plugin-owned queue table.
        $last_post_id = absint($wpdb->get_var($wpdb->prepare("SELECT target_identifier FROM {$queue_table_name} WHERE task_id = %d AND task_type = %s ORDER BY id DESC LIMIT 1", $task_id, 'content_indexing')));
        delete_transient($legacy_page_key);
    }

    $post_types = array_values(array_filter(array_map('sanitize_key', (array) ($task_config['post_types'] ?? ['post']))));
    if (empty($post_types)) {
        $post_types = ['post'];
    }
    $post_type_placeholders = implode(',', array_fill(0, count($post_types), '%s'));
    $query_args = array_merge([$last_post_id, 'publish'], $post_types, [$batch_size]);
    $posts_query = "SELECT ID FROM {$wpdb->posts} WHERE ID > %d AND post_status = %s AND post_type IN ($post_type_placeholders) ORDER BY ID ASC LIMIT %d";
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Bounded keyset scan of WordPress post IDs with prepared post types.
    $post_ids = $wpdb->get_col($wpdb->prepare($posts_query, $query_args));

    foreach ((array) $post_ids as $post_id) {
        Helpers\insert_item_into_queue_logic($wpdb, $queue_table_name, $task_id, absint($post_id), 'content_indexing', $item_config);
    }

    if (count((array) $post_ids) >= $batch_size) {
        $last_post_id = absint(end($post_ids));
        update_option($cursor_key, ['mode' => 'all', 'post_id' => $last_post_id], false);
        return;
    }

    complete_initial_queue_scan_logic($wpdb, $tasks_table_name, $task_id, $task_config, $cursor_key, $completed_key, $force_key, $force_all);
}

/** Completes the durable producer scan and clears the one-time task flag. */
function complete_initial_queue_scan_logic(
    \wpdb $wpdb,
    string $tasks_table_name,
    int $task_id,
    array $task_config,
    string $cursor_key,
    string $completed_key,
    string $force_key,
    bool $force_all
): void {
    delete_option($cursor_key);
    delete_option($force_key);
    update_option($completed_key, 'yes', false);
    if (!$force_all) {
        Helpers\update_task_flag_logic($wpdb, $tasks_table_name, $task_id, $task_config);
    }
}
