<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/inline-create-forms/pinecone.php
// Status: NEW FILE
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_pinecone_create_index_form_wrapper_global" class="aipkit_vs_global_new_store_inline_form_openai" style="display:none;">
    <div class="aipkit_form-group aipkit_vs_global_new_store_name_group">
        <label class="aipkit_form-label" for="aipkit_new_pinecone_index_name_global"><?php esc_html_e('New Index Name', 'gpt3-ai-content-generator'); ?></label>
        <input type="text" id="aipkit_new_pinecone_index_name_global" class="aipkit_form-input" placeholder="<?php esc_attr_e('e.g., my-pinecone-index', 'gpt3-ai-content-generator'); ?>">
    </div>
    <div class="aipkit_form-group aipkit_vs_global_new_store_name_group">
        <label class="aipkit_form-label" for="aipkit_new_pinecone_dimension_global"><?php esc_html_e('Dimension', 'gpt3-ai-content-generator'); ?></label>
        <input type="number" id="aipkit_new_pinecone_dimension_global" class="aipkit_form-input" value="1536" min="1" placeholder="<?php esc_attr_e('e.g., 1536', 'gpt3-ai-content-generator'); ?>" style="max-width: 120px;">
    </div>
    <div class="aipkit_new_store_button_group">
        <button type="button" id="aipkit_submit_create_pinecone_index_btn_global" class="aipkit_btn aipkit_btn-primary">
            <span class="aipkit_btn-text"><?php esc_html_e('Create Index', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_spinner" style="display:none;"></span>
        </button>
        <button type="button" id="aipkit_close_create_pinecone_index_panel_btn_global" class="aipkit_btn aipkit_btn-secondary"><?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?></button>
    </div>
    <div id="aipkit_create_pinecone_index_status_global" class="aipkit_form-help" style="flex-basis: 100%; margin-top: 5px;"></div>
</div>