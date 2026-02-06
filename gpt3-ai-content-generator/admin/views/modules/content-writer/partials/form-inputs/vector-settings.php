<?php
// File: vector-settings.php
// Status: REDESIGNED
/**
 * Partial: Content Writer Form - Vector (Knowledge Base) Settings
 * 
 * REDESIGNED with three core UX principles:
 * 1. Aesthetic - Clean, modern visual design with consistent spacing and typography
 * 2. Choice Overload Prevention - Progressive disclosure, smart defaults, visual hierarchy
 * 3. Chunking - Logically grouped settings with clear visual separation
 * 
 * @since 2.1
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables from loader-vars.php:
// $openai_vector_stores, $pinecone_indexes, $qdrant_collections
// $openai_embedding_models, $google_embedding_models
?>

<div class="aipkit_vector_settings_redesigned">

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 1: Enable Knowledge Base (Primary Decision)
         Simple toggle to enable/disable - shown first for clarity
         Following Choice Architecture: Simplest decision first
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--enable">
        <div class="aipkit_vector_settings_chunk_body">
            <div class="aipkit_vector_toggle_card">
                <div class="aipkit_vector_toggle_row">
                    <div class="aipkit_vector_toggle_info">
                        <span class="aipkit_vector_toggle_label"><?php esc_html_e('Enable Knowledge Base', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_vector_toggle_desc"><?php esc_html_e('Use your vector stores for RAG-enhanced content', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_vector_toggle_controls">
                        <label class="aipkit_switch">
                            <input 
                                type="checkbox" 
                                id="aipkit_cw_enable_vector_store" 
                                name="enable_vector_store" 
                                class="aipkit_toggle_switch aipkit_cw_vector_store_toggle aipkit_autosave_trigger" 
                                value="1"
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 2: Vector Source (Progressive - only when enabled)
         Provider and store/index/collection selection
         Following Chunking: Related source configuration grouped together
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--source aipkit_cw_vector_store_settings_container" style="display:none;">
        <div class="aipkit_vector_settings_chunk_body">
            <!-- Provider & Store Selector (unified like AI settings) -->
            <div class="aipkit_vector_source_selector">
                <div class="aipkit_vector_source_row">
                    <label class="aipkit_vector_settings_label" for="aipkit_cw_vector_store_provider">
                        <?php esc_html_e('Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select 
                        id="aipkit_cw_vector_store_provider" 
                        name="vector_store_provider" 
                        class="aipkit_vector_settings_select aipkit_autosave_trigger aipkit_cw_vector_store_provider_select"
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
                    <!-- OpenAI Vector Stores (Multi-select) -->
                    <div class="aipkit_cw_vector_openai_field">
                        <label class="aipkit_vector_settings_label" for="aipkit_cw_openai_vector_store_ids">
                            <?php esc_html_e('Vector Store', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <div
                            class="aipkit_popover_multiselect aipkit_vector_multiselect"
                            data-aipkit-vector-stores-dropdown
                            data-placeholder="<?php echo esc_attr__('Select stores', 'gpt3-ai-content-generator'); ?>"
                            data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                        >
                            <button
                                type="button"
                                class="aipkit_popover_multiselect_btn aipkit_vector_multiselect_btn"
                                aria-expanded="false"
                                aria-controls="aipkit_cw_openai_vector_store_panel"
                            >
                                <span class="aipkit_popover_multiselect_label">
                                    <?php esc_html_e('Select stores', 'gpt3-ai-content-generator'); ?>
                                </span>
                            </button>
                            <div
                                id="aipkit_cw_openai_vector_store_panel"
                                class="aipkit_popover_multiselect_panel"
                                role="menu"
                                hidden
                            >
                                <div class="aipkit_popover_multiselect_options"></div>
                            </div>
                        </div>
                        <select
                            id="aipkit_cw_openai_vector_store_ids"
                            name="openai_vector_store_ids[]"
                            class="aipkit_popover_multiselect_select aipkit_autosave_trigger"
                            multiple
                            size="3"
                            hidden
                            aria-hidden="true"
                            tabindex="-1"
                        >
                            <?php if (!empty($openai_vector_stores)): ?>
                                <?php foreach ($openai_vector_stores as $store): ?>
                                    <option value="<?php echo esc_attr($store['id']); ?>"><?php echo esc_html($store['name']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled><?php esc_html_e('No stores found', 'gpt3-ai-content-generator'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Pinecone Index -->
                    <div class="aipkit_cw_vector_pinecone_field" style="display:none;">
                        <label class="aipkit_vector_settings_label" for="aipkit_cw_pinecone_index_name">
                            <?php esc_html_e('Index', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select 
                            id="aipkit_cw_pinecone_index_name" 
                            name="pinecone_index_name" 
                            class="aipkit_vector_settings_select aipkit_autosave_trigger"
                        >
                            <option value=""><?php esc_html_e('Select index', 'gpt3-ai-content-generator'); ?></option>
                            <?php if (!empty($pinecone_indexes)): ?>
                                <?php foreach ($pinecone_indexes as $index): ?>
                                    <option value="<?php echo esc_attr($index['name']); ?>"><?php echo esc_html($index['name']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled><?php esc_html_e('No indexes found', 'gpt3-ai-content-generator'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <!-- Qdrant Collection -->
                    <div class="aipkit_cw_vector_qdrant_field" style="display:none;">
                        <label class="aipkit_vector_settings_label" for="aipkit_cw_qdrant_collection_name">
                            <?php esc_html_e('Collection', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select 
                            id="aipkit_cw_qdrant_collection_name" 
                            name="qdrant_collection_name" 
                            class="aipkit_vector_settings_select aipkit_autosave_trigger"
                        >
                            <option value=""><?php esc_html_e('Select collection', 'gpt3-ai-content-generator'); ?></option>
                            <?php if (!empty($qdrant_collections)): ?>
                                <?php foreach ($qdrant_collections as $collection): ?>
                                    <option value="<?php echo esc_attr($collection['name']); ?>"><?php echo esc_html($collection['name']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled><?php esc_html_e('No collections found', 'gpt3-ai-content-generator'); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 3: Embedding Configuration (Conditional - Pinecone/Qdrant only)
         Provider and model for embeddings
         Following Progressive Disclosure: Only shown when needed
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--embedding aipkit_cw_vector_store_settings_container aipkit_cw_vector_embedding_config_row" style="display:none;">
        <div class="aipkit_vector_settings_chunk_body">
            <div class="aipkit_vector_embedding_selector">
                <div class="aipkit_vector_embedding_row">
                    <label class="aipkit_vector_settings_label" for="aipkit_cw_vector_embedding_provider">
                        <?php esc_html_e('Embed Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select 
                        id="aipkit_cw_vector_embedding_provider" 
                        name="vector_embedding_provider" 
                        class="aipkit_vector_settings_select aipkit_autosave_trigger aipkit_cw_vector_embedding_provider_select"
                    >
                        <option value="openai" selected>OpenAI</option>
                        <option value="google">Google</option>
                        <option value="azure">Azure</option>
                    </select>
                </div>
                
                <div class="aipkit_vector_embedding_divider">
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </div>
                
                <div class="aipkit_vector_embedding_row aipkit_vector_embedding_row--model">
                    <label class="aipkit_vector_settings_label" for="aipkit_cw_vector_embedding_model">
                        <?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select 
                        id="aipkit_cw_vector_embedding_model" 
                        name="vector_embedding_model" 
                        class="aipkit_vector_settings_select aipkit_autosave_trigger aipkit_cw_vector_embedding_model_select"
                    >
                        <option value=""><?php esc_html_e('Select provider first', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 4: Retrieval Settings (Collapsible - Advanced)
         Results limit and confidence threshold
         Following Choice Overload Prevention: Collapsed by default with smart defaults
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_vector_settings_chunk aipkit_vector_settings_chunk--retrieval aipkit_vector_settings_chunk--collapsible aipkit_cw_vector_store_settings_container" style="display:none;">
        <button type="button" class="aipkit_vector_settings_chunk_header aipkit_vector_settings_chunk_header--collapsible" aria-expanded="false" aria-controls="aipkit_cw_retrieval_options_body">
            <span class="aipkit_vector_settings_chunk_icon">
                <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
            </span>
            <span class="aipkit_vector_settings_chunk_title"><?php esc_html_e('Retrieval Options', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_vector_settings_chunk_toggle">
                <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
            </span>
        </button>
        
        <div class="aipkit_vector_settings_chunk_body aipkit_vector_settings_chunk_body--collapsible" id="aipkit_cw_retrieval_options_body" aria-hidden="true">
            <!-- Results Limit Slider -->
            <div class="aipkit_vector_slider_group">
                <div class="aipkit_vector_slider_header">
                    <label class="aipkit_vector_settings_label" for="aipkit_cw_vector_store_top_k">
                        <?php esc_html_e('Results Limit', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_vector_slider_value" id="aipkit_cw_vector_store_top_k_value">3</span>
                </div>
                <div class="aipkit_vector_slider_wrapper">
                    <div class="aipkit_vector_slider_labels">
                        <span class="aipkit_vector_slider_label aipkit_vector_slider_label--min">
                            <span class="dashicons dashicons-minus" aria-hidden="true"></span>
                            <?php esc_html_e('Fewer', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <span class="aipkit_vector_slider_label aipkit_vector_slider_label--max">
                            <?php esc_html_e('More', 'gpt3-ai-content-generator'); ?>
                            <span class="dashicons dashicons-plus" aria-hidden="true"></span>
                        </span>
                    </div>
                    <input 
                        type="range" 
                        id="aipkit_cw_vector_store_top_k" 
                        name="vector_store_top_k" 
                        class="aipkit_vector_slider aipkit_autosave_trigger" 
                        value="3" 
                        min="1" 
                        max="20" 
                        step="1"
                    >
                </div>
                <p class="aipkit_vector_slider_hint"><?php esc_html_e('Number of matching chunks to retrieve', 'gpt3-ai-content-generator'); ?></p>
            </div>

            <!-- Confidence Threshold Slider -->
            <div class="aipkit_vector_slider_group">
                <div class="aipkit_vector_slider_header">
                    <label class="aipkit_vector_settings_label" for="aipkit_cw_vector_store_confidence_threshold">
                        <?php esc_html_e('Confidence Threshold', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_vector_slider_value" id="aipkit_cw_vector_store_confidence_threshold_value">20%</span>
                </div>
                <div class="aipkit_vector_slider_wrapper">
                    <div class="aipkit_vector_slider_labels">
                        <span class="aipkit_vector_slider_label aipkit_vector_slider_label--low">
                            <?php esc_html_e('Lenient', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <span class="aipkit_vector_slider_label aipkit_vector_slider_label--high">
                            <?php esc_html_e('Strict', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input 
                        type="range" 
                        id="aipkit_cw_vector_store_confidence_threshold" 
                        name="vector_store_confidence_threshold" 
                        class="aipkit_vector_slider aipkit_autosave_trigger" 
                        value="20" 
                        min="0" 
                        max="100" 
                        step="1"
                    >
                </div>
                <p class="aipkit_vector_slider_hint"><?php esc_html_e('Minimum similarity score to include results', 'gpt3-ai-content-generator'); ?></p>
            </div>
        </div>
    </div>

</div>
