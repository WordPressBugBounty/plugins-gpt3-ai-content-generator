<?php

namespace WPAICG\Core\TokenManager\Ledger;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

class AIPKit_Ledger_Repository
{
    private $table_name;

    public function __construct(?string $table_name = null)
    {
        global $wpdb;
        $this->table_name = $table_name ?: $wpdb->prefix . 'aipkit_token_ledger';
    }

    public function get_table_name(): string
    {
        return $this->table_name;
    }

    /**
     * @param array<string, mixed> $entry
     * @return int|WP_Error
     */
    public function insert_entry(array $entry)
    {
        global $wpdb;

        if (!$this->table_exists()) {
            return new WP_Error('aipkit_token_ledger_missing', __('Token ledger table is not available.', 'gpt3-ai-content-generator'));
        }

        $idempotency_key = sanitize_text_field((string) ($entry['idempotency_key'] ?? ''));
        if ($idempotency_key !== '') {
            $existing = $this->find_by_idempotency_key($idempotency_key);
            if (is_array($existing) && !empty($existing['id'])) {
                return (int) $existing['id'];
            }
        }

        $user_id = isset($entry['user_id']) && is_numeric($entry['user_id']) ? absint($entry['user_id']) : null;
        $session_id = sanitize_text_field((string) ($entry['session_id'] ?? ''));
        $meta = $entry['meta'] ?? null;
        if (is_array($meta) || is_object($meta)) {
            $meta = wp_json_encode($meta);
        } elseif ($meta !== null) {
            $meta = (string) $meta;
        }

        $data = [
            'user_id' => $user_id,
            'session_id' => $session_id !== '' ? $session_id : null,
            'actor_type' => $user_id ? 'user' : 'guest',
            'module' => sanitize_key((string) ($entry['module'] ?? 'chat')),
            'context_type' => $this->sanitize_nullable_text($entry['context_type'] ?? null),
            'context_id' => isset($entry['context_id']) && $entry['context_id'] !== '' ? absint($entry['context_id']) : null,
            'provider' => $this->sanitize_nullable_text($entry['provider'] ?? null),
            'model' => $this->sanitize_nullable_text($entry['model'] ?? null),
            'operation' => sanitize_text_field((string) ($entry['operation'] ?? 'usage')),
            'usage_input_units' => max(0, (int) ($entry['usage_input_units'] ?? 0)),
            'usage_output_units' => max(0, (int) ($entry['usage_output_units'] ?? 0)),
            'usage_total_units' => max(0, (int) ($entry['usage_total_units'] ?? 0)),
            'credits_delta' => (int) ($entry['credits_delta'] ?? 0),
            'entry_type' => sanitize_key((string) ($entry['entry_type'] ?? 'usage')),
            'reference_type' => $this->sanitize_nullable_text($entry['reference_type'] ?? null),
            'reference_id' => $this->sanitize_nullable_text($entry['reference_id'] ?? null),
            'idempotency_key' => $idempotency_key !== '' ? $idempotency_key : null,
            'meta' => $meta,
            'created_at' => sanitize_text_field((string) ($entry['created_at'] ?? current_time('mysql', 1))),
        ];

        $formats = [
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
        ];

        $inserted = $wpdb->insert($this->table_name, $data, $formats);
        if ($inserted === false) {
            return new WP_Error('aipkit_token_ledger_insert_failed', __('Failed to write token ledger entry.', 'gpt3-ai-content-generator'));
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find_by_idempotency_key(string $idempotency_key): ?array
    {
        global $wpdb;

        if ($idempotency_key === '' || !$this->table_exists()) {
            return null;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Ledger lookup from a custom table.
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE idempotency_key = %s LIMIT 1",
                $idempotency_key
            ),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    public function get_balance_total_for_user(int $user_id): int
    {
        global $wpdb;

        if ($user_id <= 0 || !$this->table_exists()) {
            return 0;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Aggregate from a custom table.
        $sum = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COALESCE(SUM(credits_delta), 0) FROM {$this->table_name} WHERE user_id = %d",
                $user_id
            )
        );

        return (int) $sum;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function get_recent_entries(array $filters = [], int $limit = 20): array
    {
        global $wpdb;

        if (!$this->table_exists()) {
            return [];
        }

        $conditions = ['1=1'];
        $values = [];

        if (!empty($filters['user_id'])) {
            $conditions[] = 'user_id = %d';
            $values[] = absint($filters['user_id']);
        }
        if (!empty($filters['session_id'])) {
            $conditions[] = 'session_id = %s';
            $values[] = sanitize_text_field((string) $filters['session_id']);
        }
        if (!empty($filters['module'])) {
            $conditions[] = 'module = %s';
            $values[] = sanitize_key((string) $filters['module']);
        }
        if (!empty($filters['entry_type'])) {
            $conditions[] = 'entry_type = %s';
            $values[] = sanitize_key((string) $filters['entry_type']);
        }

        $limit = max(1, min(100, $limit));
        $sql = "SELECT * FROM {$this->table_name} WHERE " . implode(' AND ', $conditions) . ' ORDER BY created_at DESC, id DESC LIMIT %d';
        $values[] = $limit;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared dynamically from placeholders above.
        $prepared = $wpdb->prepare($sql, ...$values);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Read from a custom table.
        $rows = $wpdb->get_results($prepared, ARRAY_A);

        return is_array($rows) ? $rows : [];
    }

    /**
     * @param mixed $value
     */
    private function sanitize_nullable_text($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return sanitize_text_field((string) $value);
    }

    private function table_exists(): bool
    {
        global $wpdb;

        static $cache = [];
        if (isset($cache[$this->table_name])) {
            return $cache[$this->table_name];
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-time table existence check per request.
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $this->table_name)) === $this->table_name;
        $cache[$this->table_name] = $exists;

        return $exists;
    }
}
