<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/template-controls.php
// Status: MODIFIED
/**
 * Partial: Content Writer Template Controls
 * Contains the dropdown to select a template and buttons to save/manage templates.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_cw_template_controls">
    <button
        type="button"
        id="aipkit_cw_template_picker_btn"
        class="aipkit_cw_setting_chip aipkit_cw_template_picker_btn"
        data-aipkit-popover-target="aipkit_cw_template_picker_popover"
        data-aipkit-popover-placement="bottom"
        aria-controls="aipkit_cw_template_picker_popover"
        aria-expanded="false"
    >
        <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--primary" aria-hidden="true">
            <span class="dashicons dashicons-media-text"></span>
        </span>
        <span class="aipkit_cw_setting_chip_content">
            <span class="aipkit_cw_setting_chip_label"><?php esc_html_e('Template', 'gpt3-ai-content-generator'); ?></span>
            <span
                class="aipkit_cw_setting_chip_value aipkit_cw_template_picker_label"
                id="aipkit_cw_template_picker_label"
                data-aipkit-placeholder="<?php esc_attr_e('Select template', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Select template', 'gpt3-ai-content-generator'); ?>
            </span>
        </span>
    </button>
    <select id="aipkit_cw_template_select" name="cw_template_id" class="aipkit_form-input screen-reader-text">
        <option value=""><?php esc_html_e('-- Select Template --', 'gpt3-ai-content-generator'); ?></option>
        <?php // Options will be populated by JS?>
    </select>
</div>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_cw_template_picker_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel aipkit_cw_template_picker_popover_panel" role="dialog" aria-label="<?php esc_attr_e('Templates', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <!-- Search filter to reduce choice overload -->
            <div class="aipkit_cw_template_picker_search_wrap">
                <span class="dashicons dashicons-search aipkit_cw_template_picker_search_icon" aria-hidden="true"></span>
                <input
                    type="text"
                    id="aipkit_cw_template_picker_search"
                    class="aipkit_cw_template_picker_search"
                    placeholder="<?php esc_attr_e('Search templates...', 'gpt3-ai-content-generator'); ?>"
                    autocomplete="off"
                >
                <button
                    type="button"
                    id="aipkit_cw_template_picker_search_clear"
                    class="aipkit_cw_template_picker_search_clear"
                    aria-label="<?php esc_attr_e('Clear search', 'gpt3-ai-content-generator'); ?>"
                    style="display: none;"
                >
                    <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                </button>
            </div>
            <!-- Template list with chunked groups -->
            <div class="aipkit_cw_template_picker_list" id="aipkit_cw_template_picker_list" role="listbox" aria-label="<?php esc_attr_e('Template list', 'gpt3-ai-content-generator'); ?>">
                <div class="aipkit_cw_template_picker_empty">
                    <?php esc_html_e('Loading templates...', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>
            <!-- Actions section -->
            <div class="aipkit_cw_template_picker_actions">
                <div class="aipkit_cw_template_action_list" id="aipkit_cw_template_action_list">
                    <button type="button" id="aipkit_cw_save_as_template_btn" class="aipkit_btn aipkit_btn-primary aipkit_btn-small">
                        <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                        <?php esc_html_e('New', 'gpt3-ai-content-generator'); ?>
                    </button>
                    <button type="button" id="aipkit_cw_rename_template_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small" disabled>
                        <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                        <?php esc_html_e('Rename', 'gpt3-ai-content-generator'); ?>
                    </button>
                    <button type="button" id="aipkit_cw_delete_template_btn" class="aipkit_btn aipkit_btn-danger aipkit_btn-small" disabled>
                        <span class="dashicons dashicons-trash" aria-hidden="true"></span>
                        <?php esc_html_e('Delete', 'gpt3-ai-content-generator'); ?>
                    </button>
                    <button type="button" id="aipkit_cw_reset_starter_templates_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small">
                        <span class="dashicons dashicons-image-rotate" aria-hidden="true"></span>
                        <?php esc_html_e('Reset', 'gpt3-ai-content-generator'); ?>
                    </button>
                </div>
                <div id="aipkit_cw_template_inline_form" class="aipkit_cw_template_inline_form" style="display: none;">
                    <input type="text" id="aipkit_cw_template_name_input" class="aipkit_form-input" placeholder="<?php esc_attr_e('Enter template name...', 'gpt3-ai-content-generator'); ?>">
                    <div class="aipkit_cw_template_inline_form_actions">
                        <button type="button" id="aipkit_cw_template_save_inline_btn" class="aipkit_btn aipkit_btn-primary aipkit_btn-small"><?php esc_html_e('Save', 'gpt3-ai-content-generator'); ?></button>
                        <button type="button" id="aipkit_cw_template_cancel_inline_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"><?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?></button>
                    </div>
                </div>
                <div id="aipkit_cw_template_inline_delete_confirm" class="aipkit_cw_template_inline_delete_confirm" style="display: none;">
                    <span id="aipkit_cw_template_delete_confirm_text"></span>
                    <div class="aipkit_cw_template_inline_form_actions">
                        <button type="button" id="aipkit_cw_template_confirm_delete_btn" class="aipkit_btn aipkit_btn-danger aipkit_btn-small"><?php esc_html_e('Confirm', 'gpt3-ai-content-generator'); ?></button>
                        <button type="button" id="aipkit_cw_template_cancel_delete_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"><?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
