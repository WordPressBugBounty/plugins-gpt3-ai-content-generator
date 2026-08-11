<?php


namespace WPAICG\AutoGPT\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles AJAX requests for the automated task queue.
 *
 * Queue rows use cursor pagination so the first page and deep navigation do not
 * depend on an exact count or a large OFFSET. Aggregate metrics are loaded by a
 * separate, short-lived cached request and never block the queue preview.
 */
class AIPKit_Get_Automated_Task_Queue_Items_Action extends AIPKit_Automated_Task_Base_Ajax_Action
{
    private const ITEMS_PER_PAGE = 15;
    private const SUMMARY_CACHE_KEY = 'aipkit_automated_task_queue_summary_v2';

    public function handle_request()
    {
        $permission_check = $this->check_module_access_permissions('autogpt', self::NONCE_ACTION);
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_module_access_permissions().
        $summary_only = isset($_POST['summary_only']) && sanitize_text_field(wp_unslash($_POST['summary_only'])) === '1';
        if ($summary_only) {
            $this->send_summary_response();
            return;
        }

        global $wpdb;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_module_access_permissions().
        $current_page = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_module_access_permissions().
        $search_term = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_module_access_permissions().
        $status_filter = isset($_POST['status_filter']) ? sanitize_key(wp_unslash($_POST['status_filter'])) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_module_access_permissions().
        $cursor = isset($_POST['cursor']) ? sanitize_text_field(wp_unslash($_POST['cursor'])) : '';

        $where_clauses = [];
        $prepare_args = [];

        if ($search_term !== '') {
            $where_clauses[] = '(q.target_identifier LIKE %s OR t.task_name LIKE %s)';
            $prepare_args[] = '%' . $wpdb->esc_like($search_term) . '%';
            $prepare_args[] = '%' . $wpdb->esc_like($search_term) . '%';
        }

        $allowed_statuses = ['pending', 'processing', 'completed', 'failed'];
        if ($status_filter !== '' && $status_filter !== 'all' && in_array($status_filter, $allowed_statuses, true)) {
            $where_clauses[] = 'q.status = %s';
            $prepare_args[] = $status_filter;
        }

        $cursor_data = $this->decode_cursor($cursor);
        if (!empty($cursor_data)) {
            $where_clauses[] = '(q.sort_priority < %d OR (q.sort_priority = %d AND q.added_at < %s) OR (q.sort_priority = %d AND q.added_at = %s AND q.id < %d))';
            $prepare_args[] = $cursor_data['priority'];
            $prepare_args[] = $cursor_data['priority'];
            $prepare_args[] = $cursor_data['added_at'];
            $prepare_args[] = $cursor_data['priority'];
            $prepare_args[] = $cursor_data['added_at'];
            $prepare_args[] = $cursor_data['id'];
        }

        $where_sql = empty($where_clauses) ? '' : ' WHERE ' . implode(' AND ', $where_clauses);
        $query_args = $prepare_args;
        $query_args[] = self::ITEMS_PER_PAGE + 1;
        $query = "SELECT q.*, t.task_name FROM {$this->queue_table_name} q LEFT JOIN {$this->tasks_table_name} t ON q.task_id = t.id" . $where_sql . ' ORDER BY q.sort_priority DESC, q.added_at DESC, q.id DESC LIMIT %d';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Plugin-owned tables and controlled SQL fragments; all scalar values are prepared.
        $items = $wpdb->get_results($wpdb->prepare($query, $query_args), ARRAY_A);
        $has_more = count((array) $items) > self::ITEMS_PER_PAGE;
        if ($has_more) {
            array_pop($items);
        }

        $enriched_items = $this->enrich_items((array) $items);
        $next_cursor = '';
        if ($has_more && !empty($items)) {
            $last_item = end($items);
            $next_cursor = $this->encode_cursor([
                'priority' => (int) ($last_item['sort_priority'] ?? 1),
                'added_at' => (string) ($last_item['added_at'] ?? ''),
                'id' => (int) ($last_item['id'] ?? 0),
            ]);
        }

        wp_send_json_success([
            'items' => $enriched_items,
            'pagination' => [
                'current_page' => $current_page,
                'per_page' => self::ITEMS_PER_PAGE,
                'has_previous' => $current_page > 1,
                'has_more' => $has_more,
                'next_cursor' => $next_cursor,
                'cursor_mode' => true,
            ],
        ]);
    }

    private function send_summary_response(): void
    {
        $cached_summary = get_transient(self::SUMMARY_CACHE_KEY);
        if (is_array($cached_summary)) {
            wp_send_json_success(['summary' => $cached_summary]);
            return;
        }

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Index-only status aggregation over a plugin-owned table, cached off the listing path.
        $summary_rows = $wpdb->get_results("SELECT status, COUNT(*) AS item_count FROM {$this->queue_table_name} GROUP BY status", ARRAY_A);
        $summary = [
            'pending' => 0,
            'processing' => 0,
            'failed' => 0,
            'total' => 0,
        ];
        foreach ((array) $summary_rows as $summary_row) {
            $status = sanitize_key((string) ($summary_row['status'] ?? ''));
            $count = max(0, (int) ($summary_row['item_count'] ?? 0));
            $summary['total'] += $count;
            if (array_key_exists($status, $summary)) {
                $summary[$status] = $count;
            }
        }

        set_transient(self::SUMMARY_CACHE_KEY, $summary, 30);
        wp_send_json_success(['summary' => $summary]);
    }

    /**
     * @param array<int, array<string, mixed>> $items Queue rows.
     * @return array<int, array<string, mixed>>
     */
    private function enrich_items(array $items): array
    {
        $enriched_items = [];
        foreach ($items as $item) {
            $item_config = json_decode((string) ($item['item_config'] ?? '[]'), true);
            if (!is_array($item_config)) {
                $item_config = [];
            }

            $item['generated_post_id'] = null;
            if (strncmp((string) $item['task_type'], 'content_writing', strlen('content_writing')) === 0 && $item['status'] === 'completed' && !empty($item['error_message'])) {
                if (preg_match('/\(ID: (\d+)\)/', (string) $item['error_message'], $matches)) {
                    $item['generated_post_id'] = (int) $matches[1];
                }
            }

            $item['post_edit_url'] = '';
            $linked_post_id = 0;
            if ($item['status'] === 'completed') {
                if (strncmp((string) $item['task_type'], 'content_writing', strlen('content_writing')) === 0 && !empty($item['generated_post_id'])) {
                    $linked_post_id = absint($item['generated_post_id']);
                } elseif ($item['task_type'] === 'enhance_existing_content') {
                    $linked_post_id = absint($item['target_identifier']);
                }
            }
            if ($linked_post_id && get_post($linked_post_id)) {
                $post_edit_url = get_edit_post_link($linked_post_id, 'raw');
                if (is_string($post_edit_url) && $post_edit_url !== '') {
                    $item['post_edit_url'] = esc_url_raw($post_edit_url);
                }
            }

            if ($item['task_type'] === 'content_indexing' || $item['task_type'] === 'enhance_existing_content') {
                $item['target_title'] = get_the_title(absint($item['target_identifier']));
            } elseif ($item['task_type'] === 'community_reply_comments') {
                $item['target_title'] = 'Comment #' . absint($item['target_identifier']);
            } elseif (strncmp((string) $item['task_type'], 'content_writing', strlen('content_writing')) === 0 && !empty($item_config['content_title'])) {
                $item['target_title'] = $item_config['content_title'];
            } else {
                $item['target_title'] = $item['target_identifier'];
            }

            if (strncmp((string) $item['task_type'], 'content_writing', strlen('content_writing')) === 0 && !empty($item_config['scheduled_gmt_time'])) {
                $item['scheduled_gmt_time'] = $item_config['scheduled_gmt_time'];
            }
            $enriched_items[] = $item;
        }

        return $enriched_items;
    }

    /** @return array{priority:int, added_at:string, id:int}|array{} */
    private function decode_cursor(string $cursor): array
    {
        if ($cursor === '') {
            return [];
        }
        $decoded = base64_decode($cursor, true);
        if ($decoded === false) {
            return [];
        }
        $cursor_data = json_decode($decoded, true);
        if (!is_array($cursor_data)) {
            return [];
        }
        $priority = isset($cursor_data['priority']) ? absint($cursor_data['priority']) : 1;
        $added_at = isset($cursor_data['added_at']) ? sanitize_text_field($cursor_data['added_at']) : '';
        $id = isset($cursor_data['id']) ? absint($cursor_data['id']) : 0;
        if ($added_at === '' || $id <= 0) {
            return [];
        }
        return [
            'priority' => min(2, max(1, $priority)),
            'added_at' => $added_at,
            'id' => $id,
        ];
    }

    /** @param array{priority:int, added_at:string, id:int} $cursor_data */
    private function encode_cursor(array $cursor_data): string
    {
        $encoded = wp_json_encode($cursor_data);
        return $encoded === false ? '' : base64_encode($encoded);
    }
}
