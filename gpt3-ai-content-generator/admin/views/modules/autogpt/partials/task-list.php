<?php
// File: admin/views/modules/autogpt/partials/task-list.php
/**
 * Partial: Automated Task List
 * Displays the table of existing automated tasks.
 * REDESIGNED: Simplified 6-column layout following philosophy principles
 * - Reduced choice overload by consolidating timing columns
 * - Better chunking with cleaner visual hierarchy
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_automated_task_list_wrapper">
    <div class="aipkit_task_list_controls aipkit_sources_toolbar">
        <h4><?php esc_html_e('Tasks', 'gpt3-ai-content-generator'); ?></h4>
        <div class="aipkit_filter_group aipkit_sources_toolbar_group">
            <input type="search" id="aipkit_task_list_search_input" class="aipkit_sources_search_input" placeholder="<?php esc_attr_e('Search tasks...', 'gpt3-ai-content-generator'); ?>">
        </div>
    </div>

    <div class="aipkit_data-table aipkit_sources_table aipkit_autogpt_tasks_table">
        <table>
            <thead>
                <tr>
                    <th class="aipkit-sortable-col" data-sort-by="task_name"><?php esc_html_e('Name', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit-sortable-col" data-sort-by="task_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></th>
                    <th><?php esc_html_e('Frequency', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit-sortable-col" data-sort-by="status"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit-sortable-col" data-sort-by="next_run_time"><?php esc_html_e('Schedule', 'gpt3-ai-content-generator'); ?></th>
                    <th class="aipkit_actions_cell_header"><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></th>
                </tr>
            </thead>
            <tbody id="aipkit_automated_tasks_tbody">
                <tr><td colspan="6" class="aipkit_text-center"><?php esc_html_e('Loading...', 'gpt3-ai-content-generator'); ?></td></tr>
            </tbody>
        </table>
    </div>
    <div id="aipkit_automated_task_list_pagination" class="aipkit_pagination"></div>
</div>
