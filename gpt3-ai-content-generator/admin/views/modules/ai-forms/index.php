<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/index.php
// Status: MODIFIED

/**
 * AIPKit AI Forms Module - Admin View
 * Main screen for managing AI Forms.
 */

if (!defined('ABSPATH')) {
    exit;
}

// --- ADDED: Fetch Vector Store and Model Data ---
use WPAICG\Vector\AIPKit_Vector_Store_Registry;
use WPAICG\AIPKit_Providers;

$aipkit_openai_vector_stores = [];
if (class_exists(AIPKit_Vector_Store_Registry::class)) {
    $aipkit_openai_vector_stores = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('OpenAI');
}

$aipkit_pinecone_indexes = [];
if (class_exists(AIPKit_Providers::class)) {
    $aipkit_pinecone_indexes = AIPKit_Providers::get_pinecone_indexes();
}

$aipkit_qdrant_collections = [];
if (class_exists(AIPKit_Providers::class)) {
    $aipkit_qdrant_collections = AIPKit_Providers::get_qdrant_collections();
}

$aipkit_openai_embedding_models = [];
$aipkit_google_embedding_models = [];
$aipkit_azure_embedding_models = [];
$aipkit_openrouter_embedding_models = [];
$aipkit_openai_provider_data = [];
$aipkit_pinecone_provider_data = [];
$aipkit_qdrant_provider_data = [];
if (class_exists(AIPKit_Providers::class)) {
    $aipkit_openai_embedding_models = AIPKit_Providers::get_openai_embedding_models();
    $aipkit_google_embedding_models = AIPKit_Providers::get_google_embedding_models();
    $aipkit_azure_embedding_models = AIPKit_Providers::get_azure_embedding_models();
    $aipkit_openrouter_embedding_models = AIPKit_Providers::get_openrouter_embedding_models();
    $aipkit_openai_provider_data = AIPKit_Providers::get_provider_data('OpenAI');
    $aipkit_pinecone_provider_data = AIPKit_Providers::get_provider_data('Pinecone');
    $aipkit_qdrant_provider_data = AIPKit_Providers::get_provider_data('Qdrant');
}
$openai_vector_stores = $aipkit_openai_vector_stores;
$pinecone_indexes = $aipkit_pinecone_indexes;
$qdrant_collections = $aipkit_qdrant_collections;
$openai_embedding_models = $aipkit_openai_embedding_models;
$google_embedding_models = $aipkit_google_embedding_models;
$azure_embedding_models = $aipkit_azure_embedding_models;
$openrouter_embedding_models = $aipkit_openrouter_embedding_models;
$openai_api_key = $aipkit_openai_provider_data['api_key'] ?? '';
$pinecone_api_key = $aipkit_pinecone_provider_data['api_key'] ?? '';
$qdrant_url = $aipkit_qdrant_provider_data['url'] ?? '';
$qdrant_api_key = $aipkit_qdrant_provider_data['api_key'] ?? '';
// --- END ADDED ---

?>
<?php
$aipkit_notice_id = 'aipkit_provider_notice_ai_forms';
include WPAICG_PLUGIN_DIR . 'admin/views/shared/provider-key-notice.php';
?>
<div
    class="aipkit_container aipkit_ai_forms_container"
    id="aipkit_ai_forms_container"
    data-openai-api-key-set="<?php echo esc_attr(!empty($openai_api_key) ? 'true' : 'false'); ?>"
    data-pinecone-api-key-set="<?php echo esc_attr(!empty($pinecone_api_key) ? 'true' : 'false'); ?>"
    data-qdrant-api-key-set="<?php echo esc_attr(!empty($qdrant_api_key) ? 'true' : 'false'); ?>"
    data-qdrant-url-set="<?php echo esc_attr(!empty($qdrant_url) ? 'true' : 'false'); ?>"
>
    <?php include WPAICG_PLUGIN_DIR . 'admin/views/shared/vector-store-nonce-fields.php'; ?>
    <div class="aipkit_container-header">
        <div class="aipkit_container-header-left">
            <div class="aipkit_container-title" id="aipkit_ai_forms_header_title_default"><?php esc_html_e('AI Forms', 'gpt3-ai-content-generator'); ?></div>
            <div class="aipkit_ai_forms_header_title_editor" id="aipkit_ai_forms_header_title_editor" style="display: none;">
                <button
                    type="button"
                    class="aipkit_ai_forms_title_display"
                    id="aipkit_ai_forms_title_display"
                    aria-label="<?php esc_attr_e('Edit form title', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="aipkit_ai_forms_title_text" id="aipkit_ai_forms_title_text"><?php esc_html_e('New Form', 'gpt3-ai-content-generator'); ?></span>
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
                <input
                    type="text"
                    id="aipkit_ai_form_title"
                    name="title"
                    class="aipkit_form-input aipkit_ai_forms_title_input"
                    placeholder="<?php esc_attr_e('New Form', 'gpt3-ai-content-generator'); ?>"
                    required
                    style="display: none;"
                >
            </div>
            <span id="aipkit_ai_forms_status" class="aipkit_training_status aipkit_global_status_area" aria-live="polite"></span>
        </div>
        <div class="aipkit_container-actions">
            <div id="aipkit_ai_forms_editor_actions" class="aipkit_form_editor_actions aipkit_form_editor_actions--header" style="display: none;">
                <button type="button" id="aipkit_save_ai_form_btn" class="aipkit_btn aipkit_btn-primary">
                    <span class="aipkit_btn-text"><?php esc_html_e('Save', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_spinner" style="display:none;"></span>
                </button>
                <button type="button" id="aipkit_preview_ai_form_btn" class="aipkit_btn aipkit_btn-secondary" disabled>
                    <span class="aipkit_btn-text"><?php esc_html_e('Preview', 'gpt3-ai-content-generator'); ?></span>
                </button>
                <button type="button" id="aipkit_cancel_edit_ai_form_btn" class="aipkit_btn aipkit_btn-danger">
                    <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
            <button id="aipkit_create_new_ai_form_btn" class="aipkit_btn aipkit_btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <span class="aipkit_btn-text"><?php esc_html_e('Create New Form', 'gpt3-ai-content-generator'); ?></span>
            </button>
            <button
                type="button"
                class="aipkit_btn aipkit_btn-secondary"
                id="aipkit_ai_forms_settings_trigger"
                data-aipkit-popover-target="aipkit_ai_forms_settings_popover"
                data-aipkit-popover-placement="bottom"
                aria-controls="aipkit_ai_forms_settings_popover"
                aria-expanded="false"
            >
                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
    <div class="aipkit_container-body">
        <div id="aipkit_ai_forms_messages">
            <!-- Messages from AJAX operations will appear here -->
        </div>
        <div id="aipkit_ai_forms_import_messages">
            <!-- Messages for import progress will appear here -->
        </div>
        <input type="file" id="aipkit_ai_forms_import_file_input" style="display: none;" accept="application/json">
        <!-- Form Editor (hidden by default) -->
        <div id="aipkit_form_editor_container" style="display:none;">
            <?php include __DIR__ . '/partials/form-editor.php'; ?>
        </div>
        <!-- List of Forms -->
        <div id="aipkit_ai_forms_list_container">
            <div class="aipkit_ai_forms_list_filters" aria-label="<?php esc_attr_e('Filters', 'gpt3-ai-content-generator'); ?>">
                <div class="aipkit_ai_forms_filters_left">
                    <label class="screen-reader-text" for="aipkit_ai_forms_search_input">
                        <?php esc_html_e('Search forms', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="search"
                        id="aipkit_ai_forms_search_input"
                        class="aipkit_ai_forms_search_input"
                        placeholder="<?php esc_attr_e('Search forms', 'gpt3-ai-content-generator'); ?>"
                    >
                </div>
                <div class="aipkit_ai_forms_filters_right">
                    <span id="aipkit_ai_forms_count_summary" class="aipkit_ai_forms_count_summary" aria-live="polite"></span>
                </div>
            </div>
             <div class="aipkit_data-table aipkit_ai_forms_list_table">
                <table>
                    <thead>
                        <tr>
                            <th class="aipkit-sortable-col" data-sort-key="title"><span><?php esc_html_e('Title', 'gpt3-ai-content-generator'); ?></span></th>
                            <th class="aipkit-sortable-col" data-sort-key="model"><span><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span></th>
                            <th><?php esc_html_e('Shortcode', 'gpt3-ai-content-generator'); ?></th>
                            <th class="aipkit_actions_cell_header">
                                <div class="aipkit_actions_header">
                                    <span><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></span>
                                    <div class="aipkit_actions_menu">
                                        <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_btn-icon aipkit_actions_menu_toggle" title="<?php esc_attr_e('More actions', 'gpt3-ai-content-generator'); ?>">
                                            <span class="dashicons dashicons-ellipsis"></span>
                                        </button>
                                        <div class="aipkit_actions_dropdown_menu">
                                            <button type="button" id="aipkit_export_all_ai_forms_btn" class="aipkit_dropdown-item-btn">
                                                <span class="dashicons dashicons-download"></span>
                                                <span><?php esc_html_e('Export All', 'gpt3-ai-content-generator'); ?></span>
                                            </button>
                                            <button type="button" id="aipkit_import_ai_forms_btn" class="aipkit_dropdown-item-btn">
                                                <span class="dashicons dashicons-upload"></span>
                                                <span><?php esc_html_e('Import', 'gpt3-ai-content-generator'); ?></span>
                                            </button>
                                            <button type="button" id="aipkit_delete_all_ai_forms_btn" class="aipkit_dropdown-item-btn aipkit_dropdown-item--danger">
                                                <span class="dashicons dashicons-trash"></span>
                                                <span><?php esc_html_e('Delete All', 'gpt3-ai-content-generator'); ?></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="aipkit_ai_forms_list_tbody">
                        <!-- Rows loaded by JS -->
                    </tbody>
                     <tfoot>
                        <tr>
                            <th colspan="4">
                                <div id="aipkit_ai_forms_pagination" class="aipkit_pagination">
                                    <!-- Pagination controls loaded by JS -->
                                </div>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div id="aipkit_no_ai_forms_message" style="display: none; text-align: center; padding: 20px; color: var(--aipkit_text-secondary);">
                <?php esc_html_e('No AI Forms have been created yet.', 'gpt3-ai-content-generator'); ?>
            </div>
        </div>
    </div><!-- /.aipkit_container-body -->

    <div
        class="aipkit_model_settings_popover aipkit_ai_forms_settings_popover"
        id="aipkit_ai_forms_settings_popover"
        aria-hidden="true"
        data-title-root="<?php esc_attr_e('Settings', 'gpt3-ai-content-generator'); ?>"
        data-title-limits="<?php esc_attr_e('Limits', 'gpt3-ai-content-generator'); ?>"
        data-title-custom-css="<?php esc_attr_e('Custom CSS', 'gpt3-ai-content-generator'); ?>"
        data-title-provider-filtering="<?php esc_attr_e('Provider Filtering', 'gpt3-ai-content-generator'); ?>"
    >
        <div class="aipkit_model_settings_popover_panel" role="dialog" aria-modal="false" aria-labelledby="aipkit_ai_forms_settings_title">
            <div class="aipkit_model_settings_popover_header">
                <div class="aipkit_model_settings_popover_header_start">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_back"
                        aria-label="<?php esc_attr_e('Back', 'gpt3-ai-content-generator'); ?>"
                        hidden
                    >
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="aipkit_model_settings_popover_title" id="aipkit_ai_forms_settings_title">
                        <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <div class="aipkit_model_settings_popover_header_end">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_close"
                        aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
            <div class="aipkit_model_settings_popover_body">
                <?php include __DIR__ . '/partials/settings-ai-forms.php'; ?>
            </div>
            <div class="aipkit_model_settings_popover_footer" hidden>
                <div class="aipkit_ai_forms_settings_footer_links">
                    <span class="aipkit_popover_flyout_footer_text">
                        <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <a
                        class="aipkit_popover_flyout_footer_link"
                        href="<?php echo esc_url('https://docs.aipower.org/docs/category/ai-forms'); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="aipkit_builder_sheet_overlay aipkit_ai_forms_preview_sheet" id="aipkit_ai_forms_preview_sheet" aria-hidden="true">
        <div
            class="aipkit_builder_sheet_panel"
            role="dialog"
            aria-labelledby="aipkit_ai_forms_preview_sheet_title"
            aria-describedby="aipkit_ai_forms_preview_sheet_description"
        >
            <div class="aipkit_builder_sheet_header">
                <div>
                    <div class="aipkit_builder_sheet_title_row">
                        <h3 class="aipkit_builder_sheet_title" id="aipkit_ai_forms_preview_sheet_title">
                            <?php esc_html_e('Preview', 'gpt3-ai-content-generator'); ?>
                        </h3>
                    </div>
                    <p class="aipkit_builder_sheet_description" id="aipkit_ai_forms_preview_sheet_description"></p>
                </div>
                <button
                    type="button"
                    class="aipkit_builder_sheet_close"
                    id="aipkit_ai_forms_preview_sheet_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                </button>
            </div>
            <div class="aipkit_builder_sheet_body">
                <div class="aipkit_ai_forms_preview_frame" id="aipkit_ai_forms_preview_frame"></div>
            </div>
        </div>
    </div>
</div><!-- /.aipkit_container -->
