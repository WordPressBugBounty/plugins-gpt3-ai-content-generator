<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/inline-create-forms/qdrant.php
// Status: NEW FILE
if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_qdrant_create_collection_form_wrapper_global" class="aipkit_vs_global_new_store_inline_form_openai" style="display:none;">
    <div class="aipkit_form-group aipkit_vs_global_new_store_name_group">
        <label class="aipkit_form-label" for="aipkit_new_qdrant_collection_name_global"><?php esc_html_e('New Collection Name', 'gpt3-ai-content-generator'); ?></label>
        <input type="text" id="aipkit_new_qdrant_collection_name_global" class="aipkit_form-input" placeholder="<?php esc_attr_e('e.g., my-docs', 'gpt3-ai-content-generator'); ?>">
    </div>
    <div class="aipkit_form-group aipkit_vs_global_new_store_name_group">
        <label class="aipkit_form-label" for="aipkit_new_qdrant_vector_size_global"><?php esc_html_e('Dimension', 'gpt3-ai-content-generator'); ?></label>
        <input type="number" id="aipkit_new_qdrant_vector_size_global" class="aipkit_form-input" value="1536" min="1" placeholder="<?php esc_attr_e('e.g., 1536', 'gpt3-ai-content-generator'); ?>" style="max-width: 120px;">
    </div>
    <div class="aipkit_new_store_button_group">
        <button type="button" id="aipkit_submit_create_qdrant_collection_btn_global" class="aipkit_btn aipkit_btn-primary">
            <span class="aipkit_btn-text"><?php esc_html_e('Create', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_spinner" style="display:none;"></span>
        </button>
        <button type="button" id="aipkit_close_create_qdrant_collection_panel_btn_global" class="aipkit_btn aipkit_btn-secondary"><?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?></button>
    </div>
    <div id="aipkit_create_qdrant_collection_status_global" class="aipkit_form-help" style="flex-basis: 100%; margin-top: 5px;"></div>
</div>