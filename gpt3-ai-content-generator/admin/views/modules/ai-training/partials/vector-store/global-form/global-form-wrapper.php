<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/global-form/global-form-wrapper.php
// Status: REDESIGNED - Modern Minimal Card Style

/**
 * Partial: AI Training - Global Add Content Form
 * REDESIGNED: Modern minimal card-style form with compact layout
 */

if (!defined('ABSPATH')) {
    exit;
}
// Variables from parent vector-store.php:
// $all_selectable_post_types, $pinecone_api_key_is_set, $qdrant_url_is_set, $qdrant_api_key_is_set
?>
<div class="aipkit_add_content_card">
    <div class="aipkit_add_content_card_header">
        <h3 class="aipkit_add_content_card_title">
            <span class="dashicons dashicons-plus-alt2"></span>
            <?php esc_html_e('Add Content', 'gpt3-ai-content-generator'); ?>
        </h3>
    </div>
    
    <div class="aipkit_add_content_card_body">
        <!-- Source Selection Cards - Top Row -->
        <div class="aipkit_form_controls_compact">
            <div class="aipkit_form_group_compact aipkit_source_selection_group">
                <label class="aipkit_form_label_compact">
                    <span class="dashicons dashicons-admin-links"></span>
                    <?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_source_cards">
                    <div class="aipkit_source_card aipkit_source_card_active" data-source="text_entry">
                        <div class="aipkit_source_card_icon">
                            <span class="dashicons dashicons-editor-paste-text"></span>
                        </div>
                        <div class="aipkit_source_card_content">
                            <h4 class="aipkit_source_card_title"><?php esc_html_e('Text', 'gpt3-ai-content-generator'); ?></h4>
                            <p class="aipkit_source_card_desc"><?php esc_html_e('Paste or type content directly', 'gpt3-ai-content-generator'); ?></p>
                        </div>
                    </div>
                    <div class="aipkit_source_card" data-source="file_upload">
                        <div class="aipkit_source_card_icon">
                            <span class="dashicons dashicons-upload"></span>
                        </div>
                        <div class="aipkit_source_card_content">
                            <h4 class="aipkit_source_card_title"><?php esc_html_e('Files', 'gpt3-ai-content-generator'); ?></h4>
                            <p class="aipkit_source_card_desc"><?php esc_html_e('Upload PDF, DOC, TXT files', 'gpt3-ai-content-generator'); ?></p>
                        </div>
                    </div>
                    <div class="aipkit_source_card" data-source="wordpress_content">
                        <div class="aipkit_source_card_icon">
                            <span class="dashicons dashicons-admin-post"></span>
                        </div>
                        <div class="aipkit_source_card_content">
                            <h4 class="aipkit_source_card_title"><?php esc_html_e('Site Content', 'gpt3-ai-content-generator'); ?></h4>
                            <p class="aipkit_source_card_desc"><?php esc_html_e('Select from your WordPress posts', 'gpt3-ai-content-generator'); ?></p>
                        </div>
                    </div>
                </div>
                <!-- Hidden select for form compatibility -->
                <select id="aipkit_vs_global_data_source" class="aipkit_form_input_compact" style="display: none;">
                    <option value="text_entry" selected><?php esc_html_e('Text', 'gpt3-ai-content-generator'); ?></option>
                    <option value="file_upload"><?php esc_html_e('Files', 'gpt3-ai-content-generator'); ?></option>
                    <option value="wordpress_content"><?php esc_html_e('Site Content', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>

        <!-- Form Controls - Bottom Row -->
        <div class="aipkit_form_controls_compact">
            <div class="aipkit_form_row_compact aipkit_form_single_row">
                <div class="aipkit_form_group_compact">
                    <label class="aipkit_form_label_compact" for="aipkit_vs_global_provider_select">
                        <span class="dashicons dashicons-database"></span>
                        <?php esc_html_e('Vector DB', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select id="aipkit_vs_global_provider_select" class="aipkit_form_input_compact">
                        <option value="openai" selected>OpenAI</option>
                        <option value="pinecone" <?php disabled(!$pinecone_api_key_is_set); ?>>Pinecone</option>
                        <option value="qdrant" <?php disabled(!$qdrant_url_is_set || !$qdrant_api_key_is_set); ?>>Qdrant</option>
                    </select>
                </div>
                
                <div class="aipkit_form_group_compact" id="aipkit_vs_global_embedding_model_inline_group" style="display: none;">
                    <label class="aipkit_form_label_compact" for="aipkit_vs_global_embedding_model_select">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e('Embedding', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select id="aipkit_vs_global_embedding_model_select" class="aipkit_form_input_compact">
                        <option value=""><?php esc_html_e('-- Select Model --', 'gpt3-ai-content-generator'); ?></option>
                        <?php // Optgroups and options populated by JS?>
                    </select>
                </div>
                
                <div class="aipkit_form_group_compact aipkit_target_group">
                    <label class="aipkit_form_label_compact" id="aipkit_vs_global_target_label" for="aipkit_vs_global_target_select">
                        <span class="dashicons dashicons-admin-site"></span>
                        <?php esc_html_e('Target Store', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_input_with_actions">
                        <select id="aipkit_vs_global_target_select" class="aipkit_form_input_compact">
                            <option value=""><?php esc_html_e('-- Select Target --', 'gpt3-ai-content-generator'); ?></option>
                            <?php // Options populated by JS?>
                        </select>
                        <div class="aipkit_input_actions">
                            <button type="button" id="aipkit_vs_global_refresh_data_btn" class="aipkit_btn_icon" title="<?php esc_attr_e('Sync Stores', 'gpt3-ai-content-generator'); ?>">
                                <span class="dashicons dashicons-image-rotate"></span>
                                <span class="aipkit_spinner" style="display:none;"></span>
                            </button>
                            <button type="button" id="aipkit_vs_global_add_openai_store_btn" class="aipkit_btn_icon" title="<?php esc_attr_e('Create New Store', 'gpt3-ai-content-generator'); ?>" style="display: none;">
                                <span class="dashicons dashicons-plus-alt2"></span>
                            </button>
                            <button type="button" id="aipkit_vs_global_add_pinecone_index_btn" class="aipkit_btn_icon" title="<?php esc_attr_e('Create New Index', 'gpt3-ai-content-generator'); ?>" style="display: none;">
                                <span class="dashicons dashicons-plus-alt2"></span>
                            </button>
                            <button type="button" id="aipkit_vs_global_add_qdrant_collection_btn" class="aipkit_btn_icon" title="<?php esc_attr_e('Create New Collection', 'gpt3-ai-content-generator'); ?>" style="display: none;">
                                <span class="dashicons dashicons-plus-alt2"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inline Create Forms -->
        <div class="aipkit_inline_forms_container">
            <?php include __DIR__ . '/inline-create-forms/openai.php'; ?>
            <?php include __DIR__ . '/inline-create-forms/pinecone.php'; ?>
            <?php include __DIR__ . '/inline-create-forms/qdrant.php'; ?>
        </div>

        <!-- Content Input Area -->
        <div class="aipkit_content_input_area">
            <div id="aipkit_vs_global_text_entry" class="aipkit_content_source_wrapper">
                <label class="aipkit_form_label_compact" for="aipkit_vs_global_text_content">
                    <span class="dashicons dashicons-editor-paste-text"></span>
                    <?php esc_html_e('Text Content', 'gpt3-ai-content-generator'); ?>
                </label>
                <textarea id="aipkit_vs_global_text_content" class="aipkit_form_textarea_compact" rows="6" placeholder="<?php esc_attr_e('Paste or type your content here...', 'gpt3-ai-content-generator'); ?>"></textarea>
            </div>
            
            <div id="aipkit_vs_global_file_upload" class="aipkit_content_source_wrapper" style="display:none;">
                <label class="aipkit_form_label_compact" for="aipkit_vs_global_file_to_submit">
                    <span class="dashicons dashicons-upload"></span>
                    <?php esc_html_e('Select File', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_file_upload_wrapper">
                    <input type="file" id="aipkit_vs_global_file_to_submit" class="aipkit_form_input_compact">
                    <div id="aipkit_vs_global_submit_upload_limits_info" class="aipkit_upload_limits_info" style="display:none;"></div>
                </div>
                <div id="aipkit_vs_global_file_upload_pro_notice" class="aipkit_notice_compact" style="display:none;">
                    <?php // Content populated by JS?>
                </div>
            </div>
            
            <div id="aipkit_vs_global_wp_content_selector" class="aipkit_content_source_wrapper" style="display:none;">
                <label class="aipkit_form_label_compact">
                    <span class="dashicons dashicons-admin-post"></span>
                    <?php esc_html_e('Site Content', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_wp_content_filters_compact">
                    <div class="aipkit_form_row_compact">
                        <div class="aipkit_form_group_compact">
                            <label for="aipkit_vs_wp_content_post_types" class="aipkit_form_label_compact"><?php esc_html_e('Post Types', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_vs_wp_content_post_types" class="aipkit_form_input_compact" multiple size="3">
                                <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                    <option value="<?php echo esc_attr($post_type_slug); ?>" <?php selected(in_array($post_type_slug, ['post', 'page'], true)); ?>>
                                        <?php echo esc_html($post_type_obj->label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="aipkit_form_group_compact">
                            <label for="aipkit_vs_wp_content_status" class="aipkit_form_label_compact"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_vs_wp_content_status" class="aipkit_form_input_compact">
                                <option value="publish"><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
                                <option value="draft"><?php esc_html_e('Draft', 'gpt3-ai-content-generator'); ?></option>
                                <option value="any"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                        <div class="aipkit_form_group_compact aipkit_load_content_group">
                            <label class="aipkit_form_label_compact"><?php esc_html_e('Action', 'gpt3-ai-content-generator'); ?></label>
                            <button type="button" id="aipkit_vs_load_wp_content_btn" class="aipkit_btn_compact aipkit_btn_secondary">
                                <span class="aipkit_btn-text"><?php esc_html_e('Load Content', 'gpt3-ai-content-generator'); ?></span>
                                <span class="aipkit_spinner" style="display:none;"></span>
                            </button>
                        </div>
                    </div>
                </div>
                <div id="aipkit_vs_wp_content_list_area" class="aipkit_wp_content_list_area">
                    <p class="aipkit_text_placeholder"><?php esc_html_e('Select criteria and click "Load Content".', 'gpt3-ai-content-generator'); ?></p>
                </div>
                <div id="aipkit_vs_wp_content_pagination" class="aipkit_wp_content_pagination"></div>
                <div id="aipkit_vs_wp_content_messages_area" class="aipkit_wp_content_status" style="margin-top: 10px; min-height:1.5em;"></div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="aipkit_add_content_card_footer">
        <div class="aipkit_action_buttons">
            <button type="button" id="aipkit_vs_global_submit_data_btn" class="aipkit_btn_compact aipkit_btn_primary">
                <span class="aipkit_btn-text"><?php esc_html_e('Add', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <button type="button" id="aipkit_vs_global_stop_indexing_btn" class="aipkit_btn_compact aipkit_btn_danger" style="display:none;">
                <span class="dashicons dashicons-controls-stop"></span>
                <?php esc_html_e('Stop', 'gpt3-ai-content-generator'); ?>
            </button>
            <button type="button" id="aipkit_vs_global_cancel_add_content_btn" class="aipkit_btn_compact aipkit_btn_secondary">
                <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
</div>