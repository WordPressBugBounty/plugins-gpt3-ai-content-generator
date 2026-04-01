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
    ?>
    <div class="aipkit_feature_promo aipkit_feature_promo--gsheets">
        <div class="aipkit_feature_promo_hero">
            <div class="aipkit_feature_promo_icon_ring">
                <span class="dashicons dashicons-media-spreadsheet" aria-hidden="true"></span>
            </div>
            <h3 class="aipkit_feature_promo_title"><?php esc_html_e('Google Sheets Import', 'gpt3-ai-content-generator'); ?></h3>
            <p class="aipkit_feature_promo_subtitle"><?php esc_html_e('Connect a spreadsheet, map columns to prompts, and bulk-generate content.', 'gpt3-ai-content-generator'); ?></p>
        </div>

        <div class="aipkit_feature_promo_steps">
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">1</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Connect your Google Sheet', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">2</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Map columns to fields', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">3</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Bulk-generate content', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>

        <div class="aipkit_feature_promo_cards">
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#16a34a" aria-hidden="true">↻</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Live Sync', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#2563eb" aria-hidden="true">⊞</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Column Mapping', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#9333ea" aria-hidden="true">⚡</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Bulk Generation', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>

        <div class="aipkit_feature_promo_cta">
            <a class="aipkit_btn aipkit_btn-primary aipkit_feature_promo_btn" href="<?php echo esc_url($upgrade_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
            </a>
            <a class="aipkit_feature_promo_link" href="https://docs.aipower.org/" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Learn more', 'gpt3-ai-content-generator'); ?>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
    <?php
    return;
}
?>
<div class="aipkit_gsheets_import_container aipkit_gsheets_section_container aipkit_cw_source_mode_shell aipkit_cw_source_mode_shell--gsheets" data-gsheets-container>
    <div class="aipkit_cw_source_mode_header">
        <h3 class="aipkit_cw_source_mode_title"><?php esc_html_e('Import from Google Sheets', 'gpt3-ai-content-generator'); ?></h3>
        <p class="aipkit_cw_source_mode_desc"><?php esc_html_e('Add your sheet ID, verify access with a service account JSON file, and use rows as generation input.', 'gpt3-ai-content-generator'); ?></p>
    </div>

    <div class="aipkit_cw_source_mode_stage">
        <div class="aipkit_gsheets_connection_shell" data-gsheets-shell data-gsheets-lock-state="open">
            <div class="aipkit_gsheets_connection_summary" data-gsheets-summary>
                <div class="aipkit_gsheets_summary_primary">
                    <span class="aipkit_gsheets_summary_badge" data-gsheets-summary-badge data-state="idle"><?php esc_html_e('Setup required', 'gpt3-ai-content-generator'); ?></span>
                    <div class="aipkit_gsheets_summary_meta">
                        <span class="aipkit_gsheets_summary_item">
                            <span class="aipkit_gsheets_summary_key"><?php esc_html_e('Sheet', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_gsheets_summary_value" data-gsheets-summary-sheet><?php esc_html_e('Not added', 'gpt3-ai-content-generator'); ?></span>
                        </span>
                        <span class="aipkit_gsheets_summary_item">
                            <span class="aipkit_gsheets_summary_key"><?php esc_html_e('Credentials', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_gsheets_summary_value" data-gsheets-summary-file><?php esc_html_e('Not added', 'gpt3-ai-content-generator'); ?></span>
                        </span>
                    </div>
                </div>

                <div class="aipkit_gsheets_summary_actions">
                    <button type="button" class="button button-secondary aipkit_btn aipkit_btn-secondary aipkit_btn-small aipkit_cw_button_match aipkit_gsheets_lock_toggle" data-gsheets-lock-toggle style="display: none;">
                        <?php esc_html_e('Lock', 'gpt3-ai-content-generator'); ?>
                    </button>
                    <div class="aipkit_gsheets_shortcut_link_wrapper" style="display: none;">
                        <a href="#" target="_blank" rel="noopener noreferrer" class="button button-secondary aipkit_btn aipkit_btn-secondary aipkit_btn-small aipkit_cw_button_match aipkit_gsheets_shortcut_link" aria-label="<?php esc_attr_e('Open spreadsheet in new tab', 'gpt3-ai-content-generator'); ?>">
                            <span class="dashicons dashicons-external" aria-hidden="true"></span>
                            <span class="aipkit_gsheets_shortcut_label"><?php esc_html_e('Open', 'gpt3-ai-content-generator'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="aipkit_gsheets_editor" data-gsheets-editor>
                <div class="aipkit_gsheets_editor_grid">
                    <div class="aipkit_gsheets_input_section" data-gsheets-input-zone>
                        <label class="aipkit_gsheets_label" for="aipkit_cw_gsheets_sheet_id"><?php esc_html_e('Google Sheet ID', 'gpt3-ai-content-generator'); ?></label>
                        <div class="aipkit_gsheets_id_input_wrapper">
                            <input
                                type="text"
                                id="aipkit_cw_gsheets_sheet_id"
                                name="gsheets_sheet_id"
                                class="aipkit_form-input aipkit_gsheets_id_input aipkit_autosave_trigger aipkit_gsheets_sheet_id_input"
                                placeholder="<?php esc_attr_e('Paste the ID from your sheet URL', 'gpt3-ai-content-generator'); ?>"
                            >
                        </div>
                    </div>

                    <div class="aipkit_gsheets_credentials_section">
                        <label class="aipkit_gsheets_label" for="aipkit_cw_gsheets_credentials_file"><?php esc_html_e('Service Account JSON', 'gpt3-ai-content-generator'); ?></label>
                        <div class="aipkit_gsheets_upload_zone" data-gsheets-dropzone>
                            <div class="aipkit_gsheets_upload_content">
                                <div class="aipkit_gsheets_upload_icon_wrapper">
                                    <span class="dashicons dashicons-upload" aria-hidden="true"></span>
                                </div>
                                <div class="aipkit_gsheets_upload_text">
                                    <span class="aipkit_gsheets_upload_primary"><?php esc_html_e('Upload credentials.json', 'gpt3-ai-content-generator'); ?></span>
                                    <span class="aipkit_gsheets_upload_hint"><?php esc_html_e('Upload your service account file', 'gpt3-ai-content-generator'); ?></span>
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
                </div>

                <div class="aipkit_gsheets_status_container" data-gsheets-status></div>
            </div>
        </div>
    </div>

    <div class="aipkit_cw_source_mode_footer aipkit_csv_help_content">
        <div class="aipkit_csv_columns_row">
            <div class="aipkit_csv_column_chip">
                <span class="aipkit_csv_column_label"><?php esc_html_e('Topic', 'gpt3-ai-content-generator'); ?></span>
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
