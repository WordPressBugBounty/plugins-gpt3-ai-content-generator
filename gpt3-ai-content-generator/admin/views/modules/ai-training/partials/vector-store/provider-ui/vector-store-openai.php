<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/provider-ui/vector-store-openai.php
// Status: NEW FILE

/**
 * Partial: AI Training - OpenAI Vector Store MANAGEMENT UI (Revised)
 * REVISED: Single-pane layout. Content (placeholder, files, search) dynamically shown based on global dropdown.
 * REVISED: Removed the "+" button and its associated file upload tool from the file management panel.
 * UPDATED: Added "Info" button to toggle metadata display.
 * UPDATED: Added "User" and "Post" columns to the files table.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variables from parent vector-store.php: $openai_api_key_is_set
?>
<div class="aipkit_settings-section">
    <?php if (!$openai_api_key_is_set): ?>
        <div class="aipkit_notice aipkit_notice-warning">
            <p>
                <?php esc_html_e('OpenAI API Key is not set in global settings. OpenAI Vector Store management will not be available.', 'gpt3-ai-content-generator'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg#providers')); ?>"><?php esc_html_e('Configure API Key', 'gpt3-ai-content-generator'); ?></a>
            </p>
        </div>
    <?php return; endif; ?>

    <!-- Main Content Area for OpenAI Vector Store Management -->
    <div id="aipkit_openai_vs_main_content_area" class="aipkit_openai_vs_main_content_area">
        <!-- Placeholder for when no store is selected or initially -->
        <div class="aipkit_ai_training_section_wrapper aipkit_openai_vs_content_section" id="aipkit_openai_vs_right_col_placeholder" style="display: flex;">
             <div class="aipkit_ai_training_section_header">
                <h5><?php esc_html_e('Store Details', 'gpt3-ai-content-generator'); ?></h5>
            </div>
            <p class="aipkit_text-center" style="padding: 20px;"><?php esc_html_e('Select a vector store from the "Store" dropdown in the "Add Content" form above to view its details and manage files, or select "Create New" to make a new one.', 'gpt3-ai-content-generator'); ?></p>
        </div>

        <!-- File Management Panel (Initially Hidden) -->
        <div id="aipkit_manage_files_panel_openai" class="aipkit_ai_training_section_wrapper aipkit_file_management_panel aipkit_openai_vs_content_section" style="display:none;">
            <div class="aipkit_ai_training_section_header">
                <h5 id="aipkit_manage_files_panel_title_openai"><?php esc_html_e('Store Files', 'gpt3-ai-content-generator'); ?></h5>
                <div class="aipkit_panel_header_actions">
                    <button type="button" id="aipkit_toggle_store_metadata_btn_openai" class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn" title="<?php esc_attr_e('View Store Metadata', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-info-outline"></span> <?php // Changed icon ?>
                    </button>
                    <button type="button" id="aipkit_panel_search_this_store_btn_openai" class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn" title="<?php esc_attr_e('Search This Store', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-search"></span>
                    </button>
                    <button type="button" id="aipkit_delete_selected_store_btn_openai" class="aipkit_btn aipkit_btn-danger aipkit_icon_btn" title="<?php esc_attr_e('Delete This Vector Store', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            <div class="aipkit_panel_body">
                <div id="aipkit_vector_store_metadata_display_openai" class="aipkit_store_metadata_display" style="display:none;"></div>
                <div id="aipkit_manage_files_list_wrapper_openai" class="aipkit_data-table">
                    <table id="aipkit_vector_store_files_table_openai">
                        <thead><tr>
                            <th><?php esc_html_e('File ID', 'gpt3-ai-content-generator'); ?></th>
                            <th><?php esc_html_e('User', 'gpt3-ai-content-generator'); ?></th>
                            <th><?php esc_html_e('Source Post', 'gpt3-ai-content-generator'); ?></th>
                            <th><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></th>
                            <th class="aipkit_text-right"><?php esc_html_e('Bytes', 'gpt3-ai-content-generator'); ?></th>
                            <th><?php esc_html_e('Created', 'gpt3-ai-content-generator'); ?></th>
                            <th class="aipkit_text-right"><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></th>
                        </tr></thead>
                        <tbody id="aipkit_vector_store_files_table_body_openai">
                            <!-- File rows populated by JS -->
                        </tbody>
                    </table>
                </div>
                <div id="aipkit_manage_files_panel_status_openai" class="aipkit_form-help"></div>
            </div>
        </div>

        <!-- Search Form & Results Area (Initially Hidden) -->
        <div id="aipkit_search_store_form_openai_wrapper_modal_placeholder" class="aipkit_ai_training_section_wrapper aipkit_openai_vs_content_section" style="display:none;">
             <div class="aipkit_ai_training_section_header">
                <h5 id="aipkit_search_store_form_title_openai"><?php esc_html_e('Search Vector Store', 'gpt3-ai-content-generator'); ?></h5>
                <button type="button" id="aipkit_close_search_panel_btn_openai" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small" title="<?php esc_attr_e('Close Search', 'gpt3-ai-content-generator'); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit_panel_body">
                <input type="hidden" id="aipkit_search_vector_store_id_openai" value="">
                <div class="aipkit_form-group">
                    <label class="aipkit_form-label" for="aipkit_search_query_text_openai"><?php esc_html_e('Search Query', 'gpt3-ai-content-generator'); ?></label>
                    <textarea id="aipkit_search_query_text_openai" class="aipkit_form-input" rows="3" placeholder="<?php esc_attr_e('Enter your search query...', 'gpt3-ai-content-generator'); ?>"></textarea>
                </div>
                <div class="aipkit_form-group">
                    <label class="aipkit_form-label" for="aipkit_search_top_k_openai"><?php esc_html_e('Number of Results (Top K)', 'gpt3-ai-content-generator'); ?></label>
                    <input type="number" id="aipkit_search_top_k_openai" class="aipkit_form-input" value="3" min="1" max="20" style="max-width: 80px;">
                </div>
                <button type="button" id="aipkit_search_vector_store_btn_openai" class="aipkit_btn aipkit_btn-primary">
                    <span class="aipkit_btn-text"><?php esc_html_e('Search', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_spinner"></span>
                </button>
                <div id="aipkit_search_vector_store_form_status_openai" class="aipkit_form-help"></div>
                <div id="aipkit_search_results_area_openai" class="aipkit_search_results_area"></div>
            </div>
        </div>
    </div>
</div>