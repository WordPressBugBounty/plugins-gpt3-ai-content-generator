<?php

namespace WPAICG\Chat\Admin\Ajax;

use WPAICG\Chat\Storage\LogStorage;
use WPAICG\Chat\Utils\Utils; // Needed for time diff
use WP_Error; // Added use statement

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles AJAX requests related to Chat Log management (fetching, exporting, deleting).
 * REMOVED: 'module' filter from extract_log_filters_from_post.
 */
class LogAjaxHandler extends BaseAjaxHandler {

    private $log_storage;

    public function __construct() {
        if (!class_exists(\WPAICG\Chat\Storage\LogStorage::class)) {
            error_log('AIPKit Error: LogStorage class not found during LogAjaxHandler construction.');
            return;
        }
        $this->log_storage = new LogStorage();
    }

    /** Extracts and sanitizes log filters from POST data. */
    private function extract_log_filters_from_post(array $post_data): array {
        $filters = [];
        // Check only if filter keys are explicitly sent and non-empty
        if (isset($post_data['filter_user_name']) && $post_data['filter_user_name'] !== '') {
            $filters['user_name'] = sanitize_text_field(wp_unslash($post_data['filter_user_name']));
        }
        if (isset($post_data['filter_chatbot_id']) && $post_data['filter_chatbot_id'] !== '') {
            $bot_filter = trim($post_data['filter_chatbot_id']);
            if ($bot_filter === '0') {
                $filters['bot_id'] = 0; // Explicitly filter for "No Bot"
            } else {
                $filters['bot_id'] = absint($bot_filter);
            }
        }
        if (isset($post_data['filter_message_like']) && $post_data['filter_message_like'] !== '') {
            $filters['message_like'] = sanitize_text_field(wp_unslash($post_data['filter_message_like']));
        }
        // if (isset($post_data['filter_module']) && $post_data['filter_module'] !== '') { // REMOVED
        //     $filters['module'] = sanitize_key(wp_unslash($post_data['filter_module'])); // REMOVED
        // } // REMOVED
        return $filters;
    }

    /** Converts an array into a CSV-formatted string line. */
    private function array_to_csv_line(array $fields): string {
        $f = fopen('php://memory', 'r+');
        if (fputcsv($f, $fields) === false) { fclose($f); error_log("AIPKit Log Export: Failed fputcsv: " . print_r($fields, true)); return ''; }
        rewind($f); $csv_line = stream_get_contents($f); fclose($f);
        if ($csv_line === false) { error_log("AIPKit Log Export: Failed stream_get_contents."); return ''; }
        return rtrim($csv_line) . "\n";
    }

    /** AJAX: Retrieves conversation summaries for the admin log view. */
    public function ajax_get_chat_logs_html() {
        $permission_check = $this->check_module_access_permissions('logs');
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        $current_page = isset($_POST['log_page']) ? absint($_POST['log_page']) : 1;
        $logs_per_page = 20; $offset = ($current_page - 1) * $logs_per_page;

        // FIX: Only extract filters if they are actually sent beyond just pagination
        $filters = [];
        $post_keys = array_keys($_POST);
        $filter_keys_present = array_filter($post_keys, function($key) {
            return strpos($key, 'filter_') === 0;
        });
        if (!empty($filter_keys_present)) {
            $filters = $this->extract_log_filters_from_post($_POST);
        }

        // Fetch logs with potentially empty filters (for initial load)
        // Default sort order changed in LogManager::get_logs
        $logs = $this->log_storage->get_logs($filters, $logs_per_page, $offset);
        $total_logs = $this->log_storage->count_logs($filters);
        $total_pages = ($total_logs > 0) ? ceil($total_logs / $logs_per_page) : 0;
        $base_url = admin_url('admin-ajax.php?action=aipkit_get_chat_logs_html');

        ob_start();
        $partial_path = WPAICG_PLUGIN_DIR . 'admin/views/modules/logs/partials/logs-table.php';
        if (file_exists($partial_path)) include $partial_path;
        else { echo '<p style="color:red;">Error: Log table template file not found.</p>'; error_log("AIPKit ajax_get_chat_logs_html: Log table template not found at {$partial_path}"); }
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    /**
     * AJAX: Handles exporting chat messages based on filters.
     * Iterates through messages within each conversation.
     * **REVISED**: Includes Usage and Feedback columns in export.
     * **FIXED**: Defined $offset before use.
     * REMOVED: 'module' from exported columns.
     */
    public function ajax_export_chat_logs() {
        $permission_check = $this->check_module_access_permissions('logs');
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        $page = isset($_POST['page']) ? absint($_POST['page']) : 0;
        $batch_size = 50;
        $total_conversations_known = isset($_POST['total_count']) ? absint($_POST['total_count']) : 0;
        $filters = $this->extract_log_filters_from_post($_POST);

        // *** FIX: Define offset based on page and batch size ***
        $offset = $page * $batch_size;

        try {
            $total_conversations = $total_conversations_known;
            if ($page === 0) {
                $total_conversations = $this->log_storage->count_logs($filters);
                 if($total_conversations === 0) {
                     wp_send_json_success(['csv_chunk' => '', 'is_last_batch' => true, 'total_count' => 0, 'exported_count' => 0]); return;
                 }
            }
            if ($total_conversations === 0 && $page > 0) {
                 wp_send_json_success(['csv_chunk' => '', 'is_last_batch' => true, 'total_count' => 0, 'exported_count' => $total_conversations_known]); return;
            }

            // *** Pass the calculated offset ***
            $conversations = $this->log_storage->get_raw_conversations_for_export($filters, $batch_size, $offset);

            if (empty($conversations) && $page === 0) {
                 wp_send_json_success(['csv_chunk' => '', 'is_last_batch' => true, 'total_count' => 0, 'exported_count' => 0]); return;
            }

            $csv_chunk = '';
            if ($page === 0) {
                $headers = [
                    'Conversation Parent ID', 'Message ID', 'Conversation UUID', 'Bot Name', // Removed Module
                    'User ID', 'User Name', 'Guest Session ID', 'Message Timestamp', 'Message Role',
                    'Message Content', 'AI Provider', 'AI Model', 'IP Address',
                    'Feedback', 'Input Tokens', 'Output Tokens', 'Total Tokens',
                    'Usage Details JSON'
                ];
                $csv_chunk .= $this->array_to_csv_line($headers);
            }

            $conversations_processed_in_batch = 0;
            foreach ($conversations as $conv) {
                 $bot_name = $conv['bot_name'] ?? __('(Unknown Bot)', 'gpt3-ai-content-generator');
                 // $module_name = $conv['module'] ?? ''; // Module name no longer needed in export
                 $user_display_name = $conv['user_display_name'] ?? __('(Unknown User)', 'gpt3-ai-content-generator');
                 $conversation_uuid = $conv['conversation_uuid'] ?? '';
                 $user_id = $conv['user_id'] ?? '';
                 $session_id = $conv['session_id'] ?? '';
                 $ip_address = $conv['ip_address'] ?? '';

                 $conversation_data = json_decode($conv['messages'] ?? '[]', true);
                 $parent_id = '';
                 $messages_array = [];

                 if (is_array($conversation_data) && isset($conversation_data['parent_id']) && isset($conversation_data['messages']) && is_array($conversation_data['messages'])) {
                    $parent_id = $conversation_data['parent_id'];
                    $messages_array = $conversation_data['messages'];
                 } elseif (is_array($conversation_data)) {
                    $messages_array = $conversation_data;
                 }

                 if (is_array($messages_array)) {
                     foreach($messages_array as $msg) {
                         $usage = $msg['usage'] ?? null;
                         $input_tokens = $usage['input_tokens'] ?? ($usage['promptTokenCount'] ?? '');
                         $output_tokens = $usage['output_tokens'] ?? ($usage['candidatesTokenCount'] ?? '');
                         $total_tokens = $usage['total_tokens'] ?? ($usage['totalTokenCount'] ?? '');
                         $usage_details_json = $usage ? wp_json_encode($usage, JSON_UNESCAPED_UNICODE) : '';

                         $csv_row = [
                            $parent_id, $msg['message_id'] ?? '', $conversation_uuid, $bot_name, // $module_name removed
                            $user_id, $user_display_name, $session_id, isset($msg['timestamp']) ? date('Y-m-d H:i:s', $msg['timestamp']) : '',
                            $msg['role'] ?? '', $msg['content'] ?? '', $msg['provider'] ?? '', $msg['model'] ?? '',
                            $ip_address, $msg['feedback'] ?? '', $input_tokens, $output_tokens, $total_tokens,
                            $usage_details_json,
                        ];
                        $csv_chunk .= $this->array_to_csv_line($csv_row);
                     }
                 }
                 $conversations_processed_in_batch++;
            }

            $conversations_processed_so_far = ($page * $batch_size) + $conversations_processed_in_batch;
            $is_last_batch = ($conversations_processed_so_far >= $total_conversations);

            wp_send_json_success([
                'csv_chunk' => $csv_chunk,
                'is_last_batch' => $is_last_batch,
                'total_count' => $total_conversations,
                'exported_count' => $conversations_processed_so_far
            ]);
        } catch (\Exception $e) {
             error_log("AIPKit Log Export Error: " . $e->getMessage());
             wp_send_json_error(['message' => 'Export error: ' . $e->getMessage()], 500);
         }
    }


    /** AJAX: Handles deleting chat messages based on filters. */
    public function ajax_delete_chat_logs() {
        $permission_check = $this->check_module_access_permissions('logs');
        if (is_wp_error($permission_check)) { $this->send_wp_error($permission_check); return; }

        $page = isset($_POST['page']) ? absint($_POST['page']) : 0;
        $batch_size = 100;
        $total_count_known = isset($_POST['total_count']) ? absint($_POST['total_count']) : 0;
        $deleted_so_far = isset($_POST['deleted_count']) ? absint($_POST['deleted_count']) : 0;
        $filters = $this->extract_log_filters_from_post($_POST);

        try {
            $total_conversations_to_delete = $total_count_known;
            if ($page === 0) {
                $total_conversations_to_delete = $this->log_storage->count_logs($filters);
                 if($total_conversations_to_delete === 0) {
                     wp_send_json_success(['deleted_total' => 0, 'is_last_batch' => true, 'total_count' => 0]); return;
                 }
            }
            if ($total_conversations_to_delete === 0 && $page > 0) {
                 wp_send_json_success(['deleted_total' => $deleted_so_far, 'is_last_batch' => true, 'total_count' => 0]); return;
            }

            $deleted_in_this_batch = $this->log_storage->delete_logs($filters, $batch_size);

            if ($deleted_in_this_batch === false) throw new \Exception(__('Database error during log deletion.', 'gpt3-ai-content-generator'));

            $new_deleted_total = $deleted_so_far + $deleted_in_this_batch;
            $is_last_batch = ($deleted_in_this_batch < $batch_size) || ($total_conversations_to_delete > 0 && $new_deleted_total >= $total_conversations_to_delete);

            wp_send_json_success(['deleted_total' => $new_deleted_total, 'is_last_batch' => $is_last_batch, 'total_count'   => $total_conversations_to_delete]);
        } catch (\Exception $e) {
             error_log("AIPKit Log Deletion Error: " . $e->getMessage());
             wp_send_json_error(['message' => __('Deletion error:', 'gpt3-ai-content-generator') . ' ' . $e->getMessage()], 500);
         }
    }
}