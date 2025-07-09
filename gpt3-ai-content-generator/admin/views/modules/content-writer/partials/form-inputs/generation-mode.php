<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/form-inputs/generation-mode.php
// Status: MODIFIED
/**
 * Partial: Content Writer - Generation Mode & Topic Input (Tabbed Interface)
 * This is now the main input panel in the center column.
 */

if (!defined('ABSPATH')) {
    exit;
}
// Variables from loader-vars.php: $is_pro
?>
<div class="aipkit_sub_container">
    <div class="aipkit_cw_tabs">
        <div class="aipkit_cw_tab aipkit_active" data-mode="single"><?php esc_html_e('Single', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_cw_tab" data-mode="task"><?php esc_html_e('Bulk', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_cw_tab" data-mode="csv"><?php esc_html_e('CSV', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_cw_tab" data-mode="rss"><?php esc_html_e('RSS', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_cw_tab" data-mode="url"><?php esc_html_e('URL', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_cw_tab" data-mode="gsheets"><?php esc_html_e('Google Sheets', 'gpt3-ai-content-generator'); ?></div>
    </div>
    <div class="aipkit_container-body">
        <div class="aipkit_cw_tab_content_container">
            <!-- Single Topic Pane -->
            <div class="aipkit_cw_tab_content aipkit_active" data-pane="single">
                <div class="aipkit_form-group">
                    <textarea id="aipkit_content_writer_title" name="content_title" class="aipkit_form-input aipkit_autosave_trigger" rows="1" required></textarea>
                </div>
            </div>
            <!-- Bulk Entry Pane -->
            <div class="aipkit_cw_tab_content" data-pane="task">
                <div class="aipkit_form-group">
                    <textarea id="aipkit_cw_bulk_topics" name="content_title_bulk" class="aipkit_form-input" rows="5"></textarea>
                </div>
            </div>
            <!-- CSV Upload Pane -->
            <div class="aipkit_cw_tab_content" data-pane="csv">
                <div class="aipkit_form-group">
                    <label class="aipkit_form-label" for="aipkit_cw_csv_file_input"><?php esc_html_e('Upload CSV File', 'gpt3-ai-content-generator'); ?></label>
                    <input type="file" id="aipkit_cw_csv_file_input" name="csv_file_input" class="aipkit_csv_file_input" accept=".csv, text/csv">
                    <div id="aipkit_cw_csv_analysis_results" class="aipkit_form-help aipkit_csv_analysis_results" style="margin-top: 5px;"></div>
                    <textarea name="content_title_csv" id="aipkit_cw_csv_data_holder" class="aipkit_form-input aipkit_csv_data_holder" style="display: none;" readonly></textarea>
                    <p class="aipkit_form-help">
                        <?php esc_html_e('The first column is used as the {topic}.', 'gpt3-ai-content-generator'); ?>
                    </p>
                    <p class="aipkit_form-help">
                        <?php esc_html_e('Subsequent columns are used for {keywords}, category ID, author login, and post type slug.', 'gpt3-ai-content-generator'); ?>
                    </p>
                    <p class="aipkit_form-help">
                        <a href="https://docs.google.com/spreadsheets/d/1WOnO_UKkbRCoyjRxQnDDTy0i-RsnrY_MDKD3Ks09JJk/edit?usp=sharing" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e('Click here to download a sample CSV file.', 'gpt3-ai-content-generator'); ?>
                        </a>
                    </p>
                </div>
            </div>
            <!-- RSS Feed Pane -->
            <div class="aipkit_cw_tab_content" data-pane="rss">
                <?php include __DIR__ . '/mode-rss.php'; ?>
            </div>
            <!-- Website URL Pane -->
            <div class="aipkit_cw_tab_content" data-pane="url">
                <?php include __DIR__ . '/mode-url.php'; ?>
            </div>
            <!-- Google Sheets Pane -->
            <div class="aipkit_cw_tab_content" data-pane="gsheets">
                 <?php
                    $shared_gsheets_partial = WPAICG_LIB_DIR . 'views/shared/content-writing/input-mode-gsheets.php';
if ($is_pro && file_exists($shared_gsheets_partial)) {
    include $shared_gsheets_partial;
} else {
    echo '<p>' . esc_html__('This is a Pro feature.', 'gpt3-ai-content-generator') . '</p>';
}
?>
            </div>
        </div>
    </div>
    <!-- NEW ACTION BAR LOCATION -->
    <div class="aipkit_cw_action_bar">
        <div class="aipkit_cw_action_bar_secondary">
            <div id="aipkit_cw_task_options" class="aipkit_form-group" style="display: none;">
                <select id="aipkit_cw_task_frequency" name="task_frequency" class="aipkit_form-input">
                    <?php foreach ($task_frequencies as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($value, 'daily'); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="aipkit_cw_action_bar_primary">
            <button type="button" id="aipkit_content_writer_generate_btn" class="aipkit_btn aipkit_btn-primary">
                <span class="aipkit_btn-text"><?php esc_html_e('Generate', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <button type="button" id="aipkit_content_writer_stop_btn" class="aipkit_btn aipkit_btn-secondary" style="display:none;">
                <?php esc_html_e('Stop', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
</div>