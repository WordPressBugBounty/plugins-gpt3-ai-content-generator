<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-training/partials/vector-store/provider-ui/vector-store-pinecone.php

/**
 * Partial: AI Training - Pinecone Vector Store MANAGEMENT UI
 * Displays details and actions for a selected Pinecone index.
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\AIPKit_Providers;

$pinecone_data = AIPKit_Providers::get_provider_data('Pinecone');
$pinecone_api_key_is_set = !empty($pinecone_data['api_key']);

$pinecone_nonce = wp_create_nonce('aipkit_vector_store_pinecone_nonce');
?>
<!-- Ensure nonce is available for JS -->
<input type="hidden" id="aipkit_vector_store_pinecone_nonce_management" value="<?php echo esc_attr($pinecone_nonce); ?>">

<div class="aipkit_settings-section">
    <?php if (!$pinecone_api_key_is_set): ?>
        <div class="aipkit_notice aipkit_notice-warning">
            <p>
                <?php echo esc_html( __('Pinecone API Key is not set in global settings. Pinecone management will not be available.', 'gpt3-ai-content-generator') ); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg#integrations')); ?>"><?php esc_html_e('Configure API Key', 'gpt3-ai-content-generator'); ?></a>
            </p>
        </div>
    <?php else: ?>
        <!-- Main Content Area for Pinecone Index Management (Populated by JS) -->
        <div id="aipkit_pinecone_vs_main_content_area" class="aipkit_openai_vs_main_content_area"> <?php // Re-use OpenAI styles for now ?>

            <!-- Placeholder for when no index is selected or initially -->
            <div class="aipkit_ai_training_section_wrapper aipkit_openai_vs_content_section" id="aipkit_pinecone_vs_right_col_placeholder" style="display: flex;">
                 <div class="aipkit_ai_training_section_header">
                    <h5><?php esc_html_e('Pinecone Index Details', 'gpt3-ai-content-generator'); ?></h5>
                 </div>
                <p class="aipkit_text-center" style="padding: 20px;"><?php esc_html_e('Select a Pinecone index from the "Target Index" dropdown in the "Add Content" form above to view its details and manage it.', 'gpt3-ai-content-generator'); ?></p>
            </div>

            <!-- Index Details & Management Panel (Initially Hidden, shown by JS) -->
            <div id="aipkit_manage_selected_pinecone_index_panel" class="aipkit_file_management_panel" style="display:none;">
                <div class="aipkit_panel_body">
                    <div id="aipkit_pinecone_index_content_area">
                        <?php // Content might include vector count, namespace management etc. later ?>
                    </div>
                     <!-- NEW: Container for Indexing Logs -->
                    <div id="aipkit_pinecone_index_logs_container">
                        <h6 style="margin-bottom: 8px;"><?php esc_html_e('Recent Indexing Activity:', 'gpt3-ai-content-generator'); ?></h6>
                        <!-- Logs will be populated by JS -->
                         <p class="aipkit_text-center" style="padding: 10px;"><em><?php esc_html_e('Loading records...', 'gpt3-ai-content-generator'); ?></em></p>
                    </div>
                    <div id="aipkit_pinecone_logs_pagination" class="aipkit_logs_pagination_container"></div>
                    <!-- END NEW -->
                    <div id="aipkit_manage_pinecone_index_panel_status" class="aipkit_form-help"></div>
                </div>
            </div>

            <!-- Search Form & Results Area for Pinecone (Initially Hidden, similar to OpenAI's) -->
            <div id="aipkit_search_pinecone_index_form_wrapper" class="aipkit_ai_training_section_wrapper aipkit_openai_vs_content_section" style="display:none;">
                 <div class="aipkit_ai_training_section_header">
                    <h5 id="aipkit_search_pinecone_index_form_title"><?php esc_html_e('Search Pinecone Index', 'gpt3-ai-content-generator'); ?></h5>
                    <button type="button" id="aipkit_close_search_pinecone_panel_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small" title="<?php esc_attr_e('Close Search', 'gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="aipkit_panel_body">
                    <input type="hidden" id="aipkit_search_pinecone_index_id" value="">
                    <div class="aipkit_form-group">
                        <label class="aipkit_form-label" for="aipkit_search_query_vector_pinecone"><?php esc_html_e('Search Query Text', 'gpt3-ai-content-generator'); ?></label>
                        <textarea id="aipkit_search_query_vector_pinecone" class="aipkit_form-input" rows="2" placeholder="<?php esc_attr_e('Enter text to search with (will be embedded)...', 'gpt3-ai-content-generator'); ?>"></textarea>
                        <div class="aipkit_form-help"><?php esc_html_e('This text will be embedded using the model selected in the "Add Content" form above.', 'gpt3-ai-content-generator'); ?></div>
                    </div>
                    <div class="aipkit_form-group">
                        <label class="aipkit_form-label" for="aipkit_search_pinecone_namespace"><?php esc_html_e('Namespace (Optional)', 'gpt3-ai-content-generator'); ?></label>
                        <input type="text" id="aipkit_search_pinecone_namespace" class="aipkit_form-input" placeholder="<?php esc_attr_e('Enter namespace if applicable', 'gpt3-ai-content-generator'); ?>" style="max-width: 250px;">
                    </div>
                    <div class="aipkit_form-group">
                        <label class="aipkit_form-label" for="aipkit_search_top_k_pinecone"><?php esc_html_e('Number of Results (Top K)', 'gpt3-ai-content-generator'); ?></label>
                        <input type="number" id="aipkit_search_top_k_pinecone" class="aipkit_form-input" value="3" min="1" max="100" style="max-width: 80px;">
                    </div>
                    <button type="button" id="aipkit_search_pinecone_index_btn" class="aipkit_btn aipkit_btn-primary">
                        <span class="aipkit_btn-text"><?php esc_html_e('Search', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_spinner"></span>
                    </button>
                    <div id="aipkit_search_pinecone_index_form_status" class="aipkit_form-help"></div>
                    <div id="aipkit_search_pinecone_results_area" class="aipkit_search_results_area"></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>