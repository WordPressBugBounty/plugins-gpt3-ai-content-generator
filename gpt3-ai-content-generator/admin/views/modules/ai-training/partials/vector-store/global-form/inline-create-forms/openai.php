<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/inline-create-forms/openai.php
// Status: NEW FILE
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_vs_global_new_store_inline_form_openai" class="aipkit_vs_global_new_store_inline_form_openai" style="display: none;">
    <div class="aipkit_form-group aipkit_vs_global_new_store_name_group">
        <label class="aipkit_form-label" for="aipkit_vs_global_new_store_name_openai"><?php esc_html_e('New Store Name', 'gpt3-ai-content-generator'); ?></label>
        <input type="text" id="aipkit_vs_global_new_store_name_openai" class="aipkit_form-input" placeholder="<?php esc_attr_e('Support FAQ', 'gpt3-ai-content-generator'); ?>">
    </div>
    <div class="aipkit_new_store_button_group">
        <button type="button" id="aipkit_vs_global_create_store_btn_openai" class="aipkit_btn aipkit_btn-primary">
            <span class="aipkit_btn-text"><?php esc_html_e('Create', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_spinner" style="display:none;"></span>
        </button>
        <button type="button" id="aipkit_vs_global_cancel_create_store_btn_openai" class="aipkit_btn aipkit_btn-secondary"><?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?></button>
    </div>
</div>