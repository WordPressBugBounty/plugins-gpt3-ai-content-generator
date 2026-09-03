<?php

namespace WPAICG\AutoGPT\Cron\EventProcessor\Helpers;

if (!defined('ABSPATH')) {
    exit;
}

/** Link a submitted provider job without introducing a second upload or queue state. */
function defer_indexing_queue_item_logic(array $item, array $result): bool
{
    global $wpdb;
    $job_id = absint($result['job_id'] ?? 0);
    $config = json_decode((string) ($item['item_config'] ?? ''), true);
    if (!$job_id || !is_array($config) || ($item['task_type'] ?? '') !== 'content_indexing') {
        return false;
    }
    $config['_aipkit_indexing_job'] = $job_id;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Persist the source identity on the claimed queue item before releasing the worker.
    return $wpdb->update(
        $wpdb->prefix . 'aipkit_automated_task_queue',
        ['item_config' => wp_json_encode($config)],
        ['id' => absint($item['id']), 'status' => 'processing'],
        ['%s'],
        ['%d', '%s']
    ) !== false;
}

/** True when provider jobs, rather than PHP workers, own processing queue items. */
function has_waiting_indexing_queue_items_logic(): bool
{
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bounded queue existence query; the marker is an internal JSON key, not user SQL.
    return (bool) $wpdb->get_var($wpdb->prepare(
        'SELECT 1 FROM ' . esc_sql($wpdb->prefix . 'aipkit_automated_task_queue') . ' WHERE status = %s AND task_type = %s AND item_config LIKE %s LIMIT 1',
        'processing', 'content_indexing', '%' . $wpdb->esc_like('"_aipkit_indexing_job":') . '%'
    ));
}

/** Read confirmed source results locally. Never call a provider or resubmit content. */
function reconcile_indexing_queue_items_logic(): array
{
    global $wpdb;
    $table = $wpdb->prefix . 'aipkit_automated_task_queue';
    $cursor_option = 'aipkit_indexing_completion_cursor';
    $cursor = absint(get_option($cursor_option, 0));
    $items = [];
    for ($pass = 0; $pass < 2; ++$pass) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bounded, cursor-based check of plugin-owned asynchronous queue items.
        $items = $wpdb->get_results($wpdb->prepare(
            'SELECT id, task_id, target_identifier, item_config FROM ' . esc_sql($table) . ' WHERE status = %s AND task_type = %s AND item_config LIKE %s AND id > %d ORDER BY id ASC LIMIT 20',
            'processing', 'content_indexing', '%' . $wpdb->esc_like('"_aipkit_indexing_job":') . '%', $cursor
        ), ARRAY_A) ?: [];
        if ($items || !$cursor) {
            break;
        }
        $cursor = 0;
    }
    $summary = ['completed' => 0, 'failed' => 0, 'task_ids' => []];
    foreach ($items as $item) {
        $cursor = absint($item['id']);
        $config = json_decode((string) $item['item_config'], true) ?: [];
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Exact source record lookup; no remote work is repeated.
        $source = $wpdb->get_row($wpdb->prepare(
            'SELECT status, message, provider, post_id, vector_store_id FROM ' . esc_sql($wpdb->prefix . 'aipkit_vector_data_source') . ' WHERE id = %d',
            absint($config['_aipkit_indexing_job'] ?? 0)
        ), ARRAY_A);
        $matches = $source
            && (int) $source['post_id'] === (int) $item['target_identifier']
            && strtolower($source['provider']) === strtolower((string) ($config['target_store_provider'] ?? ''))
            && $source['vector_store_id'] === (string) ($config['target_store_id'] ?? '');
        if ($matches && in_array($source['status'], ['processing', 'queued'], true)) {
            continue;
        }
        $ready = $matches && $source['status'] === 'indexed';
        $message = $matches ? (string) $source['message'] : __('The indexing result is no longer available. Check Sources before retrying.', 'gpt3-ai-content-generator');
        update_queue_status_logic($cursor, $ready ? 'success' : 'error', $message);
        ++$summary[$ready ? 'completed' : 'failed'];
        $summary['task_ids'][] = absint($item['task_id']);
    }
    if ($items) {
        update_option($cursor_option, $cursor, false);
    } else {
        delete_option($cursor_option);
    }
    return $summary;
}
