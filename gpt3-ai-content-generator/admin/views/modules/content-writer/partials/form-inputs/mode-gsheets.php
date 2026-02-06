<?php
/**
 * Content Writer Google Sheets Mode tab (module-specific).
 *
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}

// $is_pro is available from the parent scope (loader-vars.php)
if (!$is_pro) {
    $upgrade_url = function_exists('wpaicg_gacg_fs') ? wpaicg_gacg_fs()->get_upgrade_url() : '#';
    echo '<div class="aipkit_upgrade_notice">';
    echo '<span class="dashicons dashicons-lock" aria-hidden="true"></span>';
    echo '<p>' . esc_html__('Google Sheets import is a Pro feature. Upgrade to generate content from spreadsheets.', 'gpt3-ai-content-generator') . '</p>';
    echo '<a class="aipkit_btn aipkit_btn-primary aipkit_upgrade_btn" href="' . esc_url($upgrade_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Upgrade to Pro', 'gpt3-ai-content-generator') . '</a>';
    echo '</div>';
    return;
}
?>
<div class="aipkit_gsheets_import_container aipkit_gsheets_section_container" data-gsheets-container>
    <div class="aipkit_gsheets_input_section" data-gsheets-input-zone>
        <div class="aipkit_gsheets_input_header">
            <span class="aipkit_gsheets_input_icon" aria-hidden="true">
                <span class="dashicons dashicons-media-spreadsheet"></span>
            </span>
            <div class="aipkit_gsheets_input_title">
                <label class="aipkit_gsheets_label" for="aipkit_cw_gsheets_sheet_id"><?php esc_html_e('Google Sheet ID', 'gpt3-ai-content-generator'); ?></label>
                <span class="aipkit_gsheets_sublabel"><?php esc_html_e('Found in your spreadsheet URL after /d/', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
        <div class="aipkit_gsheets_id_input_wrapper">
            <input
                type="text"
                id="aipkit_cw_gsheets_sheet_id"
                name="gsheets_sheet_id"
                class="aipkit_form-input aipkit_gsheets_id_input aipkit_autosave_trigger aipkit_gsheets_sheet_id_input"
                placeholder="<?php esc_attr_e('e.g. 18QIWggMmbTVTb-nztTo7SFdGJTUC6kwRxgc841xq4x0', 'gpt3-ai-content-generator'); ?>"
            >
            <div class="aipkit_gsheets_shortcut_link_wrapper" style="display: none;">
                <a href="#" target="_blank" rel="noopener noreferrer" class="aipkit_gsheets_shortcut_link" aria-label="<?php esc_attr_e('Open spreadsheet in new tab', 'gpt3-ai-content-generator'); ?>">
                    <span class="dashicons dashicons-external" aria-hidden="true"></span>
                    <span class="aipkit_gsheets_shortcut_label"><?php esc_html_e('Open spreadsheet', 'gpt3-ai-content-generator'); ?></span>
                </a>
            </div>
        </div>
    </div>

    <div class="aipkit_gsheets_credentials_section">
        <div class="aipkit_gsheets_credentials_header">
            <span class="aipkit_gsheets_credentials_icon" aria-hidden="true">
                <span class="dashicons dashicons-admin-network"></span>
            </span>
            <div class="aipkit_gsheets_credentials_title">
                <label class="aipkit_gsheets_label" for="aipkit_cw_gsheets_credentials_file"><?php esc_html_e('Service Account Credentials', 'gpt3-ai-content-generator'); ?></label>
                <span class="aipkit_gsheets_sublabel"><?php esc_html_e('Upload your Google Cloud JSON key file', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
        <div class="aipkit_gsheets_upload_zone" data-gsheets-dropzone>
            <div class="aipkit_gsheets_upload_content">
                <div class="aipkit_gsheets_upload_icon_wrapper">
                    <span class="dashicons dashicons-upload" aria-hidden="true"></span>
                </div>
                <div class="aipkit_gsheets_upload_text">
                    <span class="aipkit_gsheets_upload_primary"><?php esc_html_e('Drop JSON file here or click to browse', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_gsheets_upload_hint"><?php esc_html_e('Service account credentials (.json)', 'gpt3-ai-content-generator'); ?></span>
                </div>
            </div>
            <input
                type="file"
                id="aipkit_cw_gsheets_credentials_file"
                name="gsheets_credentials_file"
                class="aipkit_gsheets_credentials_file_input"
                accept=".json,application/json"
            >
            <textarea id="aipkit_cw_gsheets_credentials" name="gsheets_credentials" class="aipkit_autosave_trigger aipkit_gsheets_credentials_hidden_input" style="display:none;"></textarea>
        </div>

        <div class="aipkit_gsheets_file_badge" data-gsheets-file-badge style="display: none;">
            <div class="aipkit_gsheets_file_info">
                <span class="dashicons dashicons-media-code" aria-hidden="true"></span>
                <span class="aipkit_gsheets_file_display"></span>
            </div>
            <button type="button" class="aipkit_gsheets_file_remove" data-gsheets-remove-file aria-label="<?php esc_attr_e('Remove file', 'gpt3-ai-content-generator'); ?>">
                <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <div class="aipkit_gsheets_status_container" data-gsheets-status></div>

    <div class="aipkit_csv_help_content">
        <div class="aipkit_csv_columns_row">
            <div class="aipkit_csv_column_chip aipkit_csv_column_chip--required">
                <span class="aipkit_csv_column_label"><?php esc_html_e('Topic', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_csv_column_tag"><?php esc_html_e('required', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_csv_column_divider">→</span>
            <div class="aipkit_csv_column_chip">
                <span class="aipkit_csv_column_label"><?php esc_html_e('Keywords', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_csv_column_divider">→</span>
            <div class="aipkit_csv_column_chip">
                <span class="aipkit_csv_column_label"><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_csv_column_divider">→</span>
            <div class="aipkit_csv_column_chip">
                <span class="aipkit_csv_column_label"><?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_csv_column_divider">→</span>
            <div class="aipkit_csv_column_chip">
                <span class="aipkit_csv_column_label"><?php esc_html_e('Post Type', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_csv_column_divider">→</span>
            <div class="aipkit_csv_column_chip">
                <span class="aipkit_csv_column_label"><?php esc_html_e('Schedule', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <a href="https://docs.google.com/spreadsheets/d/18QIWggMmbTVTb-nztTo7SFdGJTUC6kwRxgc841xq4x0/edit?gid=0#gid=0" target="_blank" rel="noopener noreferrer" class="aipkit_csv_sample_link">
                <span class="dashicons dashicons-external" aria-hidden="true"></span>
                <?php esc_html_e('View sample', 'gpt3-ai-content-generator'); ?>
            </a>
        </div>
    </div>
</div>
