<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/provider-ui/vector-store-qdrant.php

/**
 * Partial: AI Training - Qdrant Vector Store MANAGEMENT UI
 * Displays details and actions for a selected Qdrant collection.
 * The form for creating new collections is now in vector-store.php (global form, triggered by a button).
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\AIPKit_Providers;

$qdrant_data = AIPKit_Providers::get_provider_data('Qdrant');
$qdrant_url_is_set = !empty($qdrant_data['url']);
$qdrant_api_key_is_set = !empty($qdrant_data['api_key']);

?>

<div class="aipkit_settings-section">
    <?php if (!$qdrant_url_is_set || !$qdrant_api_key_is_set): ?>
        <div class="aipkit_notice aipkit_notice-warning">
            <p>
                <?php
                if (!$qdrant_url_is_set && !$qdrant_api_key_is_set) {
                    esc_html_e('Qdrant URL and API Key are not set in global settings. Qdrant management will not be available.', 'gpt3-ai-content-generator');
                } elseif (!$qdrant_url_is_set) {
                    esc_html_e('Qdrant URL is not set in global settings. Qdrant management will not be available.', 'gpt3-ai-content-generator');
                } else {
                    esc_html_e('Qdrant API Key is not set in global settings. Qdrant management will not be available.', 'gpt3-ai-content-generator');
                }
?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg#integrations')); ?>"><?php esc_html_e('Configure Now', 'gpt3-ai-content-generator'); ?></a>
            </p>
        </div>
    <?php else: ?>
        <div id="aipkit_qdrant_vs_main_content_area" class="aipkit_openai_vs_main_content_area">

            <div class="aipkit_ai_training_section_wrapper aipkit_openai_vs_content_section" id="aipkit_qdrant_vs_right_col_placeholder" style="display: flex;">
                 <div class="aipkit_ai_training_section_header">
                    <h5><?php esc_html_e('Qdrant Collection Details', 'gpt3-ai-content-generator'); ?></h5>
                 </div>
                <p class="aipkit_text-center" style="padding: 20px;"><?php esc_html_e('Select a Qdrant collection from the "Target Collection" dropdown in the "Add Content" form above to view its details and manage it, or use the "+" button next to the dropdown to create a new one.', 'gpt3-ai-content-generator'); ?></p>
            </div>

            <div id="aipkit_manage_selected_qdrant_collection_panel" class="aipkit_file_management_panel" style="display:none;">
                <div class="aipkit_panel_body">
                    <div id="aipkit_qdrant_collection_content_area">
                        <?php // Future: Points list, sample points, etc. Currently, only logs are displayed.?>
                    </div>
                    <div id="aipkit_qdrant_collection_logs_container">
                        <h6 style="margin-bottom: 8px;"><?php esc_html_e('Recent Indexing Activity:', 'gpt3-ai-content-generator'); ?></h6>
                         <p class="aipkit_text-center" style="padding: 10px;"><em><?php esc_html_e('Loading records...', 'gpt3-ai-content-generator'); ?></em></p>
                    </div>
                    <div id="aipkit_qdrant_logs_pagination" class="aipkit_logs_pagination_container"></div>
                    <div id="aipkit_manage_qdrant_collection_panel_status" class="aipkit_form-help"></div>
                </div>
            </div>

            <div id="aipkit_search_qdrant_collection_form_wrapper" class="aipkit_ai_training_section_wrapper aipkit_openai_vs_content_section" style="display:none;">
                 <div class="aipkit_ai_training_section_header">
                    <h5 id="aipkit_search_qdrant_collection_form_title"><?php esc_html_e('Search Qdrant Collection', 'gpt3-ai-content-generator'); ?></h5>
                    <button type="button" id="aipkit_close_search_qdrant_panel_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small" title="<?php esc_attr_e('Close Search', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="aipkit_panel_body">
                    <input type="hidden" id="aipkit_search_qdrant_collection_id" value="">
                    <div class="aipkit_form-group">
                        <label class="aipkit_form-label" for="aipkit_search_query_text_qdrant"><?php esc_html_e('Search Query Text', 'gpt3-ai-content-generator'); ?></label>
                        <textarea id="aipkit_search_query_text_qdrant" class="aipkit_form-input" rows="2" placeholder="<?php esc_attr_e('Enter text to search with (will be embedded)...', 'gpt3-ai-content-generator'); ?>"></textarea>
                         <div class="aipkit_form-help"><?php esc_html_e('This text will be embedded using the model selected in the "Add Content" form above.', 'gpt3-ai-content-generator'); ?></div>
                    </div>
                     <div class="aipkit_form-group">
                        <label class="aipkit_form-label" for="aipkit_search_top_k_qdrant"><?php esc_html_e('Number of Results (Top K)', 'gpt3-ai-content-generator'); ?></label>
                        <input type="number" id="aipkit_search_top_k_qdrant" class="aipkit_form-input" value="3" min="1" max="100" style="max-width: 80px;">
                    </div>
                    <div class="aipkit_form-group">
                        <label class="aipkit_form-label" for="aipkit_search_filter_qdrant"><?php esc_html_e('Filter (JSON - Optional)', 'gpt3-ai-content-generator'); ?></label>
                        <textarea id="aipkit_search_filter_qdrant" class="aipkit_form-input" rows="3" placeholder="<?php esc_attr_e('e.g., { "must": [{ "key": "source", "match": { "value": "wordpress_post" }}]}', 'gpt3-ai-content-generator'); ?>"></textarea>
                    </div>
                    <button type="button" id="aipkit_search_qdrant_collection_btn" class="aipkit_btn aipkit_btn-primary">
                        <span class="aipkit_btn-text"><?php esc_html_e('Search', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_spinner" style="display:none;"></span>
                    </button>
                    <div id="aipkit_search_qdrant_collection_form_status" class="aipkit_form-help"></div>
                    <div id="aipkit_search_qdrant_results_area" class="aipkit_search_results_area"></div>
                </div>
            </div>
            <?php // Create Collection UI elements REMOVED. Triggered by global "+" button next to select.?>
        </div>
    <?php endif; ?>
</div>