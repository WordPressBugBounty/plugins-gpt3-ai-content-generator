<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/layout/left-panel.php
// Status: MODIFIED

if (!defined('ABSPATH')) {
    exit;
}
// Variables from parent: $all_selectable_post_types, $pinecone_api_key_is_set, $qdrant_url_is_set, $qdrant_api_key_is_set
?>
<div class="aipkit_ai_training_left_column">
    <div class="aipkit_ai_training_section_wrapper aipkit_global_add_data_form_wrapper">
        <?php // -- Controls Section (from former global-form-wrapper.php) -- ?>
        <?php include dirname(__DIR__) . '/global-form/form-controls-row.php'; ?>
        <?php include dirname(__DIR__) . '/global-form/inline-create-forms/openai.php'; ?>
        <?php include dirname(__DIR__) . '/global-form/inline-create-forms/pinecone.php'; ?>
        <?php include dirname(__DIR__) . '/global-form/inline-create-forms/qdrant.php'; ?>

        <?php // -- NEW: Add the global provider status display area -- ?>
        <div id="aipkit_vs_global_provider_status" class="aipkit_form-help" style="margin-top: 10px; min-height:1.5em;"></div>

        <hr class="aipkit_section_divider_hr">

        <?php // -- Data Input Section (original content of this file) -- ?>
        <div class="aipkit_form-group aipkit_vs_global_data_input_area">
            <div id="aipkit_vs_global_text_entry" class="aipkit_vs_global_data_source_type_wrapper">
                <label class="aipkit_form-label" for="aipkit_vs_global_text_content"><?php esc_html_e('Text Content', 'gpt3-ai-content-generator'); ?></label>
                <textarea id="aipkit_vs_global_text_content" class="aipkit_form-input" rows="10" placeholder="<?php esc_attr_e('Paste or type your content here...', 'gpt3-ai-content-generator'); ?>"></textarea>
            </div>
            <div id="aipkit_vs_global_file_upload" class="aipkit_vs_global_data_source_type_wrapper" style="display:none;">
                <label class="aipkit_form-label" for="aipkit_vs_global_file_to_submit"><?php esc_html_e('Select File', 'gpt3-ai-content-generator'); ?></label>
                <div id="aipkit_vs_global_file_upload_actual_input_wrapper">
                    <input type="file" id="aipkit_vs_global_file_to_submit" class="aipkit_form-input">
                    <div id="aipkit_vs_global_submit_upload_limits_info" class="aipkit_upload_limits_info aipkit_vs_global_upload_limits" style="display:none;"></div>
                </div>
                <div id="aipkit_vs_global_file_upload_pro_notice" class="aipkit_notice aipkit_notice-info" style="display:none;">
                    <?php // Content populated by JS?>
                </div>
            </div>
            <div id="aipkit_vs_global_wp_content_selector" class="aipkit_vs_global_data_source_type_wrapper" style="display:none;">
                <div class="aipkit_wp_content_filters aipkit_form-row" style="gap: 10px; margin-bottom: 10px;">
                    <div class="aipkit_form-group aipkit_form-col" style="flex: 1 1 200px;">
                        <label for="aipkit_vs_wp_content_post_types" class="aipkit_form-label"><?php esc_html_e('Post Types', 'gpt3-ai-content-generator'); ?></label>
                        <select id="aipkit_vs_wp_content_post_types" class="aipkit_form-input" multiple size="3" style="min-height: 60px;">
                            <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                <option value="<?php echo esc_attr($post_type_slug); ?>" <?php selected(in_array($post_type_slug, ['post', 'page'])); ?>>
                                    <?php echo esc_html($post_type_obj->label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="aipkit_form-help"><?php esc_html_e('Ctrl/Cmd + click to select multiple.', 'gpt3-ai-content-generator'); ?></div>
                    </div>
                    <div class="aipkit_form-group aipkit_form-col" style="flex: 0 1 150px;">
                        <label for="aipkit_vs_wp_content_status" class="aipkit_form-label"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></label>
                        <select id="aipkit_vs_wp_content_status" class="aipkit_form-input">
                            <option value="publish"><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
                            <option value="draft"><?php esc_html_e('Draft', 'gpt3-ai-content-generator'); ?></option>
                            <option value="any"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                    <div class="aipkit_form-group aipkit_form-col" style="flex: 0 1 auto; align-self: flex-end;">
                        <button type="button" id="aipkit_vs_load_wp_content_btn" class="aipkit_btn aipkit_btn-secondary">
                            <span class="aipkit_btn-text"><?php esc_html_e('Load Content', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_spinner" style="display:none;"></span>
                        </button>
                    </div>
                </div>
                <div id="aipkit_vs_wp_content_list_area" class="aipkit_wp_content_list_area">
                    <p class="aipkit_text-center aipkit_text-secondary"><?php esc_html_e('Select criteria and click "Load Content".', 'gpt3-ai-content-generator'); ?></p>
                </div>
                <div id="aipkit_vs_wp_content_pagination" class="aipkit_wp_content_pagination">
                </div>
                <div id="aipkit_vs_wp_content_messages_area" class="aipkit_wp_content_status aipkit_form-help" style="margin-top: 10px; min-height:1.5em;"></div>
            </div>
        </div>
        <div class="aipkit_form-group aipkit_vs_global_submit_button_wrapper">
            <button type="button" id="aipkit_vs_global_submit_data_btn" class="aipkit_btn aipkit_btn-primary">
                <span class="aipkit_btn-text"><?php esc_html_e('Add Content', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
            <button type="button" id="aipkit_vs_global_stop_indexing_btn" class="aipkit_btn aipkit_btn-danger" style="display:none;">
                <?php esc_html_e('Stop', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
</div>