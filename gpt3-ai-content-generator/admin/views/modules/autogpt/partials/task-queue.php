<?php
// File: admin/views/modules/autogpt/partials/task-queue.php
/**
 * Partial: Automated Task Queue Viewer
 * Displays items currently in the task queue.
 * REDESIGNED: Simplified 6-column layout following philosophy principles
 * - Removed Attempts column (edge case info, visible in status if failed)
 * - Combined timing info for better chunking
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_automated_task_queue_wrapper">
    <div class="aipkit_task_queue_controls aipkit_sources_toolbar">
        <h4><?php esc_html_e('Queue', 'gpt3-ai-content-generator'); ?></h4>
        <div class="aipkit_filter_group aipkit_sources_toolbar_group">
            <input type="search" id="aipkit_task_queue_search_input" class="aipkit_sources_search_input" placeholder="<?php esc_attr_e('Search queue...', 'gpt3-ai-content-generator'); ?>">
            <select id="aipkit_task_queue_status_filter" class="aipkit_sources_filter_select">
                <option value="all"><?php esc_html_e('All Statuses', 'gpt3-ai-content-generator'); ?></option>
                <option value="pending"><?php esc_html_e('Pending', 'gpt3-ai-content-generator'); ?></option>
                <option value="processing"><?php esc_html_e('Processing', 'gpt3-ai-content-generator'); ?></option>
                <option value="completed"><?php esc_html_e('Completed', 'gpt3-ai-content-generator'); ?></option>
                <option value="failed"><?php esc_html_e('Failed', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button id="aipkit_delete_queue_by_status_btn" class="aipkit_btn aipkit_btn-danger" title="<?php esc_attr_e('Delete filtered items', 'gpt3-ai-content-generator'); ?>">
                <span class="dashicons dashicons-trash"></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <button id="aipkit_refresh_task_queue_btn" class="aipkit_btn aipkit_btn-secondary" title="<?php esc_attr_e('Refresh', 'gpt3-ai-content-generator'); ?>">
                <span class="dashicons dashicons-update-alt"></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
        </div>
    </div>
    <div id="aipkit_automated_task_queue_viewer_area" class="aipkit_data-table aipkit_sources_table aipkit_autogpt_queue_table">
        <table>
            <thead>
                <tr>
                    <th class="aipkit-sortable-col" data-sort-by="q.target_identifier"><?php esc_html_e('Item', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit-sortable-col" data-sort-by="t.task_name"><?php esc_html_e('Task', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit-sortable-col" data-sort-by="q.task_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit-sortable-col" data-sort-by="q.status"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit-sortable-col" data-sort-by="q.added_at"><?php esc_html_e('Added', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit_actions_cell_header"><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></th>
                </tr>
            </thead>
            <tbody id="aipkit_automated_task_queue_tbody">
                <tr><td colspan="6" class="aipkit_text-center"><?php esc_html_e('Loading queue...', 'gpt3-ai-content-generator'); ?></td></tr>
            </tbody>
        </table>
    </div>
    <div id="aipkit_automated_task_queue_pagination" class="aipkit_pagination"></div>
</div>
