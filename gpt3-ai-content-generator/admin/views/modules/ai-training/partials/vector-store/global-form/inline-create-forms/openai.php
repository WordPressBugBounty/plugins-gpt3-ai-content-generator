<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/inline-create-forms/openai.php
// Status: UPDATED - Modern Compact Design

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_vs_global_new_store_inline_form_openai" class="aipkit_inline_create_form" style="display: none;">
    <div class="aipkit_inline_form_content">
        <div class="aipkit_form_group_compact">
            <label class="aipkit_form_label_compact" for="aipkit_vs_global_new_store_name_openai">
                <span class="dashicons dashicons-edit"></span>
                <?php esc_html_e('New Store Name', 'gpt3-ai-content-generator'); ?>
            </label>
            <input type="text" id="aipkit_vs_global_new_store_name_openai" class="aipkit_form_input_compact" placeholder="<?php esc_attr_e('Support FAQ', 'gpt3-ai-content-generator'); ?>">
        </div>
        <div class="aipkit_inline_form_actions">
            <button type="button" id="aipkit_vs_global_create_store_btn_openai" class="aipkit_btn_compact aipkit_btn_primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <span class="aipkit_btn-text"><?php esc_html_e('Create', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <button type="button" id="aipkit_vs_global_cancel_create_store_btn_openai" class="aipkit_btn_compact aipkit_btn_secondary">
                <span class="dashicons dashicons-no-alt"></span>
                <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
</div>