<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/inline-create-forms/qdrant.php
// Status: UPDATED - Modern Compact Design

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_vs_global_new_store_inline_form_qdrant" class="aipkit_inline_create_form" style="display: none;">
    <div class="aipkit_inline_form_content">
        <div class="aipkit_form_row_compact">
            <div class="aipkit_form_group_compact">
                <label class="aipkit_form_label_compact" for="aipkit_vs_global_new_store_name_qdrant">
                    <span class="dashicons dashicons-edit"></span>
                    <?php esc_html_e('New Collection Name', 'gpt3-ai-content-generator'); ?>
                </label>
                <input type="text" id="aipkit_vs_global_new_store_name_qdrant" class="aipkit_form_input_compact" placeholder="<?php esc_attr_e('Support FAQ', 'gpt3-ai-content-generator'); ?>">
            </div>
            <div class="aipkit_form_group_compact">
                <label class="aipkit_form_label_compact" for="aipkit_vs_global_new_store_dimension_qdrant">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php esc_html_e('Dimension', 'gpt3-ai-content-generator'); ?>
                </label>
                <input type="number" id="aipkit_vs_global_new_store_dimension_qdrant" class="aipkit_form_input_compact" placeholder="1536" min="1" max="4096">
            </div>
        </div>
        <div class="aipkit_inline_form_actions">
            <button type="button" id="aipkit_vs_global_create_store_btn_qdrant" class="aipkit_btn_compact aipkit_btn_primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <span class="aipkit_btn-text"><?php esc_html_e('Create', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <button type="button" id="aipkit_vs_global_cancel_create_store_btn_qdrant" class="aipkit_btn_compact aipkit_btn_secondary">
                <span class="dashicons dashicons-no-alt"></span>
                <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
        <div id="aipkit_create_qdrant_collection_status_global" class="aipkit_inline_form_status"></div>
    </div>
</div>