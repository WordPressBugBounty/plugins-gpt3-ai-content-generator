<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/form-controls-row.php
// Status: MODIFIED

if (!defined('ABSPATH')) {
    exit;
}
// Variables from parent: $pinecone_api_key_is_set, $qdrant_url_is_set, $qdrant_api_key_is_set
?>
<div class="aipkit_form-row aipkit_global_add_data_form_controls_row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_vs_global_data_source"><?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?></label>
        <select id="aipkit_vs_global_data_source" class="aipkit_form-input">
            <option value="text_entry" selected><?php esc_html_e('Text', 'gpt3-ai-content-generator'); ?></option>
            <option value="file_upload"><?php esc_html_e('Files', 'gpt3-ai-content-generator'); ?></option>
            <option value="wordpress_content"><?php esc_html_e('Site Content', 'gpt3-ai-content-generator'); ?></option>
        </select>
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_vs_global_provider_select"><?php esc_html_e('Vector DB', 'gpt3-ai-content-generator'); ?></label>
        <select id="aipkit_vs_global_provider_select" class="aipkit_form-input">
            <option value="openai" selected>OpenAI</option>
            <option value="pinecone" <?php disabled(!$pinecone_api_key_is_set); ?>>Pinecone</option>
            <option value="qdrant" <?php disabled(!$qdrant_url_is_set || !$qdrant_api_key_is_set); ?>>Qdrant</option>
        </select>
    </div>
</div>
<div class="aipkit_form-row aipkit_global_add_data_form_controls_row">
    <div class="aipkit_form-group aipkit_form-col" id="aipkit_vs_global_embedding_model_inline_group" style="display: none;">
        <label class="aipkit_form-label" for="aipkit_vs_global_embedding_model_select"><?php esc_html_e('Embedding', 'gpt3-ai-content-generator'); ?></label>
        <select id="aipkit_vs_global_embedding_model_select" class="aipkit_form-input">
            <option value=""><?php esc_html_e('-- Select Embedding Model --', 'gpt3-ai-content-generator'); ?></option>
            <?php // Optgroups and options populated by JS?>
        </select>
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" id="aipkit_vs_global_target_label" for="aipkit_vs_global_target_select"><?php esc_html_e('Target Store', 'gpt3-ai-content-generator'); ?></label>
        <div class="aipkit_input-with-button">
            <select id="aipkit_vs_global_target_select" class="aipkit_form-input">
                <option value=""><?php esc_html_e('-- Select Target --', 'gpt3-ai-content-generator'); ?></option>
                <?php // Options populated by JS?>
            </select>
            <button type="button" id="aipkit_vs_global_refresh_data_btn" class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn" title="<?php esc_attr_e('Sync Stores/Indexes/Collections', 'gpt3-ai-content-generator'); ?>">
                <span class="dashicons dashicons-image-rotate"></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <button type="button" id="aipkit_vs_global_add_openai_store_btn" class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn" title="<?php esc_attr_e('Create New OpenAI Store', 'gpt3-ai-content-generator'); ?>" style="display: none;">
                <span class="dashicons dashicons-plus-alt2"></span>
            </button>
            <button type="button" id="aipkit_vs_global_add_pinecone_index_btn" class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn" title="<?php esc_attr_e('Create New Pinecone Index', 'gpt3-ai-content-generator'); ?>" style="display: none;">
                <span class="dashicons dashicons-plus-alt2"></span>
            </button>
            <button type="button" id="aipkit_vs_global_add_qdrant_collection_btn" class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn" title="<?php esc_attr_e('Create New Qdrant Collection', 'gpt3-ai-content-generator'); ?>" style="display: none;">
                <span class="dashicons dashicons-plus-alt2"></span>
            </button>
        </div>
    </div>
</div>