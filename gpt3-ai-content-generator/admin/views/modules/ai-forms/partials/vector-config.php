<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/partials/vector-config.php
// Status: NEW FILE

/**
 * Partial: AI Form Editor - Vector Configuration
 * Contains settings for enabling and configuring vector store integration for the form.
 */
if (!defined('ABSPATH')) {
    exit;
}
// Variables passed from parent (index.php -> form-editor.php -> _form-editor-main-settings.php -> this):
// $openai_vector_stores, $pinecone_indexes, $qdrant_collections
// $openai_embedding_models, $google_embedding_models
?>
<div class="aipkit_form-group">
    <label class="aipkit_form-label aipkit_checkbox-label" for="aipkit_ai_form_enable_vector_store">
        <input
            type="checkbox"
            id="aipkit_ai_form_enable_vector_store"
            name="enable_vector_store"
            class="aipkit_toggle_switch aipkit_ai_form_vector_store_toggle"
            value="1"
        >
        <?php esc_html_e('Enable Vector Store', 'gpt3-ai-content-generator'); ?>
    </label>
    <div class="aipkit_form-help">
        <?php esc_html_e('Allow this form to query a vector store for context before generating a response.', 'gpt3-ai-content-generator'); ?>
    </div>
</div>

<div class="aipkit_ai_form_vector_store_conditional_settings" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--aipkit_container-border);">
    <div class="aipkit_form-row" style="flex-wrap: nowrap; gap: 10px;">
        <div class="aipkit_form-group aipkit_form-col" style="flex: 0 1 180px;">
            <label class="aipkit_form-label" for="aipkit_ai_form_vector_store_provider"><?php esc_html_e('Vector Store Provider', 'gpt3-ai-content-generator'); ?></label>
            <select id="aipkit_ai_form_vector_store_provider" name="vector_store_provider" class="aipkit_form-input aipkit_ai_form_vector_store_provider_select">
                <option value="openai">OpenAI</option>
                <option value="pinecone">Pinecone</option>
                <option value="qdrant">Qdrant</option>
            </select>
        </div>
        <div class="aipkit_form-group aipkit_form-col" style="flex: 1 1 auto;">
            <!-- OpenAI Selector -->
            <div class="aipkit_form-group aipkit_ai_form_vector_openai_field">
                <label class="aipkit_form-label" for="aipkit_ai_form_openai_vector_store_ids"><?php esc_html_e('OpenAI Vector Stores', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_ai_form_openai_vector_store_ids" name="openai_vector_store_ids[]" class="aipkit_form-input" multiple size="3">
                     <?php if (!empty($openai_vector_stores)): ?>
                        <?php foreach ($openai_vector_stores as $store): ?>
                            <option value="<?php echo esc_attr($store['id']); ?>"><?php echo esc_html($store['name']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled><?php esc_html_e('No stores found (Sync in AI Training)', 'gpt3-ai-content-generator'); ?></option>
                    <?php endif; ?>
                </select>
            </div>
            <!-- Pinecone Selector -->
            <div class="aipkit_form-group aipkit_ai_form_vector_pinecone_field" style="display:none;">
                <label class="aipkit_form-label" for="aipkit_ai_form_pinecone_index_name"><?php esc_html_e('Pinecone Index', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_ai_form_pinecone_index_name" name="pinecone_index_name" class="aipkit_form-input">
                    <option value=""><?php esc_html_e('-- Select Index --', 'gpt3-ai-content-generator'); ?></option>
                     <?php if (!empty($pinecone_indexes)): ?>
                        <?php foreach ($pinecone_indexes as $index): ?>
                            <option value="<?php echo esc_attr($index['name']); ?>"><?php echo esc_html($index['name']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled><?php esc_html_e('No indexes found (Sync in AI Settings)', 'gpt3-ai-content-generator'); ?></option>
                    <?php endif; ?>
                </select>
            </div>
            <!-- Qdrant Selector -->
            <div class="aipkit_form-group aipkit_ai_form_vector_qdrant_field" style="display:none;">
                <label class="aipkit_form-label" for="aipkit_ai_form_qdrant_collection_name"><?php esc_html_e('Qdrant Collection', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_ai_form_qdrant_collection_name" name="qdrant_collection_name" class="aipkit_form-input">
                    <option value=""><?php esc_html_e('-- Select Collection --', 'gpt3-ai-content-generator'); ?></option>
                     <?php if (!empty($qdrant_collections)): ?>
                        <?php foreach ($qdrant_collections as $collection): ?>
                            <option value="<?php echo esc_attr($collection['name']); ?>"><?php echo esc_html($collection['name']); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled><?php esc_html_e('No collections found (Sync in AI Settings)', 'gpt3-ai-content-generator'); ?></option>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Embedding Config (for Pinecone/Qdrant) -->
    <div class="aipkit_ai_form_embedding_config_row" style="display: none; margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--aipkit_container-border);">
        <div class="aipkit_form-row" style="flex-wrap: nowrap; gap: 10px;">
            <div class="aipkit_form-group aipkit_form-col" style="flex: 0 1 180px;">
                <label class="aipkit_form-label" for="aipkit_ai_form_vector_embedding_provider"><?php esc_html_e('Embedding Provider', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_ai_form_vector_embedding_provider" name="vector_embedding_provider" class="aipkit_form-input aipkit_ai_form_vector_embedding_provider_select">
                    <option value="openai">OpenAI</option>
                    <option value="google">Google</option>
                </select>
            </div>
            <div class="aipkit_form-group aipkit_form-col" style="flex: 1 1 auto;">
                <label class="aipkit_form-label" for="aipkit_ai_form_vector_embedding_model"><?php esc_html_e('Embedding Model', 'gpt3-ai-content-generator'); ?></label>
                <select id="aipkit_ai_form_vector_embedding_model" name="vector_embedding_model" class="aipkit_form-input aipkit_ai_form_vector_embedding_model_select">
                     <option value=""><?php esc_html_e('Select Provider First', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <!-- Top K Slider -->
    <div class="aipkit_form-group" style="margin-top: 15px;">
        <label class="aipkit_form-label" for="aipkit_ai_form_vector_store_top_k"><?php esc_html_e('Results Limit', 'gpt3-ai-content-generator'); ?></label>
        <div class="aipkit_slider_wrapper">
            <input type="range" id="aipkit_ai_form_vector_store_top_k" name="vector_store_top_k" class="aipkit_form-input aipkit_range_slider" min="1" max="20" step="1" value="3" />
            <span id="aipkit_ai_form_vector_store_top_k_value" class="aipkit_slider_value">3</span>
        </div>
        <p class="aipkit_form-help"><?php esc_html_e('Max relevant results from vector store. Applies per OpenAI store if multiple are selected.', 'gpt3-ai-content-generator'); ?></p>
    </div>
</div>