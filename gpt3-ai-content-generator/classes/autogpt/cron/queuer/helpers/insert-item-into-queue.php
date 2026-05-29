<?php
 namespace WPAICG\AutoGPT\Cron\Queuer\Helpers; if (!defined('ABSPATH')) { exit; } function insert_item_into_queue_logic( \wpdb $wpdb, string $queue_table_name, int $task_id, int $post_id, string $task_type, array $item_config ): bool { $encoded_item_config = wp_json_encode($item_config); if ($encoded_item_config === false) { $encoded_item_config = '{}'; } $existing_item_id = $wpdb->get_var( $wpdb->prepare( "SELECT id
             FROM " . esc_sql($queue_table_name) . "
             WHERE task_id = %d
               AND target_identifier = %s
               AND task_type = %s
               AND item_config = %s
               AND status IN ('pending', 'processing')
             LIMIT 1", $task_id, (string) $post_id, $task_type, $encoded_item_config ) ); if (!empty($existing_item_id)) { return false; } $inserted = $wpdb->insert( $queue_table_name, [ 'task_id' => $task_id, 'target_identifier' => $post_id, 'task_type' => $task_type, 'item_config' => $encoded_item_config, 'status' => 'pending', 'added_at' => current_time('mysql', 1) ], ['%d', '%s', '%s', '%s', '%s', '%s'] ); return (bool) $inserted; } 