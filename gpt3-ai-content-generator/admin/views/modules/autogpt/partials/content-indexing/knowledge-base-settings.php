<?php
/**
 * Partial: Content Indexing Automated Task - Knowledge Base Settings
 * Mirrors the content writer knowledge base popover style.
 *
 * @since 2.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_vector_settings_redesigned">
    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--source">
        <div class="aipkit_vector_settings_chunk_body">
            <div class="aipkit_vector_source_selector">
                <div class="aipkit_vector_source_row">
                    <label class="aipkit_vector_settings_label" for="aipkit_task_content_indexing_target_store_provider">
                        <?php esc_html_e('Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_task_content_indexing_target_store_provider"
                        name="target_store_provider"
                        class="aipkit_vector_settings_select aipkit_autosave_trigger"
                    >
                        <option value="openai" selected>OpenAI</option>
                        <option value="pinecone">Pinecone</option>
                        <option value="qdrant">Qdrant</option>
                    </select>
                </div>
                <div class="aipkit_vector_source_divider">
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </div>
                <div class="aipkit_vector_source_row aipkit_vector_source_row--store">
                    <label class="aipkit_vector_settings_label" for="aipkit_task_content_indexing_target_store_id">
                        <?php esc_html_e('Vector Store', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_task_content_indexing_target_store_id"
                        name="target_store_id"
                        class="aipkit_vector_settings_select aipkit_autosave_trigger"
                    >
                        <option value=""><?php esc_html_e('-- Select Store/Index --', 'gpt3-ai-content-generator'); ?></option>
                        <?php // Options populated by JS. ?>
                    </select>
                </div>
                <div class="aipkit_vector_source_divider" id="aipkit_task_content_indexing_embedding_model_divider" style="display: none;">
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </div>
                <div class="aipkit_vector_source_row aipkit_vector_source_row--model" id="aipkit_task_content_indexing_embedding_model_group" style="display: none;">
                    <label class="aipkit_vector_settings_label" for="aipkit_task_content_indexing_embedding_model">
                        <?php esc_html_e('Embedding', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_task_content_indexing_embedding_model"
                        name="embedding_model"
                        class="aipkit_vector_settings_select aipkit_autosave_trigger"
                    >
                        <option value=""><?php esc_html_e('-- Select Model --', 'gpt3-ai-content-generator'); ?></option>
                        <?php // Options and optgroups populated by JS. ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
