<?php
/**
 * AIPKit Sources Module - Admin View
 */

if (!defined('ABSPATH')) {
    exit;
}

$pinecone_data = [];
$qdrant_data = [];
if (class_exists('\\WPAICG\\AIPKit_Providers')) {
    $pinecone_data = \WPAICG\AIPKit_Providers::get_provider_data('Pinecone');
    $qdrant_data = \WPAICG\AIPKit_Providers::get_provider_data('Qdrant');
}

$pinecone_api_key = $pinecone_data['api_key'] ?? '';
$qdrant_api_key = $qdrant_data['api_key'] ?? '';
$qdrant_url = $qdrant_data['url'] ?? '';
$aipkit_options = get_option('aipkit_options', []);
$semantic_search_settings = $aipkit_options['semantic_search'] ?? [];
$semantic_vector_provider = $semantic_search_settings['vector_provider'] ?? 'pinecone';
$semantic_target_id = $semantic_search_settings['target_id'] ?? '';
$semantic_embedding_provider = $semantic_search_settings['embedding_provider'] ?? 'openai';
$semantic_embedding_model = $semantic_search_settings['embedding_model'] ?? '';
$semantic_num_results = $semantic_search_settings['num_results'] ?? 5;
$semantic_no_results_text = $semantic_search_settings['no_results_text'] ?? __('No results found.', 'gpt3-ai-content-generator');
$pinecone_index_list = [];
$qdrant_collection_list = [];
$openai_embedding_models = [];
$google_embedding_models = [];
$openrouter_embedding_models = [];
if (class_exists('\\WPAICG\\AIPKit_Providers')) {
    $pinecone_index_list = \WPAICG\AIPKit_Providers::get_pinecone_indexes();
    $qdrant_collection_list = \WPAICG\AIPKit_Providers::get_qdrant_collections();
    $openai_embedding_models = \WPAICG\AIPKit_Providers::get_openai_embedding_models();
    $google_embedding_models = \WPAICG\AIPKit_Providers::get_google_embedding_models();
    $openrouter_embedding_models = \WPAICG\AIPKit_Providers::get_openrouter_embedding_models();
}
$all_embedding_models_map = [];
foreach ($openai_embedding_models as $model_item) {
    if (!empty($model_item['id'])) {
        $all_embedding_models_map[$model_item['id']] = true;
    }
}
foreach ($google_embedding_models as $model_item) {
    if (!empty($model_item['id'])) {
        $all_embedding_models_map[$model_item['id']] = true;
    }
}
foreach ($openrouter_embedding_models as $model_item) {
    if (!empty($model_item['id'])) {
        $all_embedding_models_map[$model_item['id']] = true;
    }
}
$training_general_settings = get_option('aipkit_training_general_settings', [
    'hide_user_uploads' => true,
    'show_index_button' => true,
]);
$hide_user_uploads_checked = $training_general_settings['hide_user_uploads'] ?? true;
$show_index_button_checked = $training_general_settings['show_index_button'] ?? true;
$chunk_avg_chars = isset($training_general_settings['chunk_avg_chars_per_token'])
    ? (int) $training_general_settings['chunk_avg_chars_per_token']
    : 4;
$chunk_max_tokens = isset($training_general_settings['chunk_max_tokens_per_chunk'])
    ? (int) $training_general_settings['chunk_max_tokens_per_chunk']
    : 3000;
$chunk_overlap_tokens = isset($training_general_settings['chunk_overlap_tokens'])
    ? (int) $training_general_settings['chunk_overlap_tokens']
    : 150;
$is_pro_plan = class_exists('\\WPAICG\\aipkit_dashboard')
    ? \WPAICG\aipkit_dashboard::is_pro_plan()
    : false;
$upgrade_url = admin_url('admin.php?page=wpaicg-pricing');
$post_types_args = ['public' => true];
$all_selectable_post_types = get_post_types($post_types_args, 'objects');
$all_selectable_post_types = array_filter($all_selectable_post_types, function ($post_type_obj) {
    return $post_type_obj->name !== 'attachment';
});
?>
<div
    class="aipkit_container aipkit_sources_container"
    id="aipkit_sources_module_container"
    data-pinecone-api-key="<?php echo esc_attr($pinecone_api_key); ?>"
    data-qdrant-api-key="<?php echo esc_attr($qdrant_api_key); ?>"
    data-qdrant-url="<?php echo esc_attr($qdrant_url); ?>"
    data-settings-nonce="<?php echo esc_attr(wp_create_nonce('aipkit_ai_training_settings_nonce')); ?>"
    data-settings-is-pro="<?php echo $is_pro_plan ? '1' : '0'; ?>"
    data-semantic-search-nonce="<?php echo esc_attr(wp_create_nonce('aipkit_semantic_search_nonce')); ?>"
    data-semantic-pinecone-indexes="<?php echo esc_attr(wp_json_encode($pinecone_index_list ?: [])); ?>"
    data-semantic-qdrant-collections="<?php echo esc_attr(wp_json_encode($qdrant_collection_list ?: [])); ?>"
>
    <div class="aipkit_container-header">
        <div class="aipkit_container-header-left">
            <div class="aipkit_container-title"><?php esc_html_e('Sources', 'gpt3-ai-content-generator'); ?></div>
            <span id="aipkit_sources_status" class="aipkit_training_status aipkit_global_status_area" aria-live="polite"></span>
            <span
                id="aipkit_indexing_settings_messages"
                class="aipkit_training_status aipkit_global_status_area"
                aria-live="polite"
            ></span>
        </div>
        <div class="aipkit_container-actions">
            <button
                type="button"
                class="aipkit_btn aipkit_btn-primary"
                id="aipkit_sources_training_toggle"
                aria-expanded="false"
                aria-controls="aipkit_sources_training_card"
            >
                <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                <?php esc_html_e('Add data', 'gpt3-ai-content-generator'); ?>
            </button>
            <button
                type="button"
                class="aipkit_btn aipkit_btn-secondary"
                id="aipkit_sources_settings_trigger"
                data-aipkit-popover-target="aipkit_sources_settings_popover"
                data-aipkit-popover-placement="bottom"
                aria-controls="aipkit_sources_settings_popover"
                aria-expanded="false"
            >
                <span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
                <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
    <div class="aipkit_container-body" id="aipkit_sources_container_body">
        <div class="aipkit_chatbot_builder" data-aipkit-context="sources">
            <?php include WPAICG_PLUGIN_DIR . 'admin/views/shared/vector-store-nonce-fields.php'; ?>
            <div class="aipkit_sources_meta" aria-label="<?php esc_attr_e('Filters', 'gpt3-ai-content-generator'); ?>">
                <label class="screen-reader-text" for="aipkit_sources_provider_filter">
                    <?php esc_html_e('Provider filter', 'gpt3-ai-content-generator'); ?>
                </label>
                <select
                    id="aipkit_sources_provider_filter"
                    class="aipkit_popover_select aipkit_sources_filter_select aipkit_chatbot_provider_select"
                    data-aipkit-picker-title="<?php esc_attr_e('Provider', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value=""><?php esc_html_e('All providers', 'gpt3-ai-content-generator'); ?></option>
                    <option value="openai"><?php esc_html_e('OpenAI', 'gpt3-ai-content-generator'); ?></option>
                    <option value="pinecone"><?php esc_html_e('Pinecone', 'gpt3-ai-content-generator'); ?></option>
                    <option value="qdrant"><?php esc_html_e('Qdrant', 'gpt3-ai-content-generator'); ?></option>
                </select>
                <label class="screen-reader-text" for="aipkit_sources_index_filter">
                    <?php esc_html_e('Index selection', 'gpt3-ai-content-generator'); ?>
                </label>
                <select
                    id="aipkit_sources_index_filter"
                    class="aipkit_popover_select aipkit_sources_filter_select"
                    data-aipkit-picker-title="<?php esc_attr_e('Index', 'gpt3-ai-content-generator'); ?>"
                    hidden
                    disabled
                >
                    <option value=""><?php esc_html_e('All indexes', 'gpt3-ai-content-generator'); ?></option>
                </select>
                <span id="aipkit_sources_embedding_row" hidden>
                    <label class="screen-reader-text" for="aipkit_sources_embedding_model">
                        <?php esc_html_e('Embedding model', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select id="aipkit_sources_embedding_model" class="aipkit_popover_select aipkit_sources_filter_select">
                        <option value=""><?php esc_html_e('Select embedding model', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </span>
                <span class="aipkit_sources_index_empty_action" id="aipkit_sources_index_empty_action" hidden>
                    <button
                        type="button"
                        class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"
                        id="aipkit_sources_index_create_trigger"
                    >
                        <?php esc_html_e('Create new', 'gpt3-ai-content-generator'); ?>
                    </button>
                </span>
                <label class="screen-reader-text" for="aipkit_sources_search_input">
                    <?php esc_html_e('Search sources', 'gpt3-ai-content-generator'); ?>
                </label>
                <input
                    type="search"
                    id="aipkit_sources_search_input"
                    class="aipkit_sources_search_input"
                    placeholder="<?php esc_attr_e('Search sources', 'gpt3-ai-content-generator'); ?>"
                >
            </div>
            <div
                class="aipkit_model_settings_popover aipkit_sources_settings_popover"
                id="aipkit_sources_settings_popover"
                aria-hidden="true"
                data-title-root="<?php esc_attr_e('Settings', 'gpt3-ai-content-generator'); ?>"
                data-title-general="<?php esc_attr_e('General', 'gpt3-ai-content-generator'); ?>"
                data-title-provider-credentials="<?php esc_attr_e('Provider credentials', 'gpt3-ai-content-generator'); ?>"
                data-title-document-chunking="<?php esc_attr_e('Document chunking', 'gpt3-ai-content-generator'); ?>"
            >
                <div class="aipkit_model_settings_popover_panel" role="dialog" aria-modal="false" aria-labelledby="aipkit_sources_settings_title">
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
                            <span class="aipkit_model_settings_popover_title" id="aipkit_sources_settings_title">
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
                        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="root">
                            <div class="aipkit_popover_options_list aipkit_popover_options_list--settings-root">
                                <div class="aipkit_popover_option_group">
                                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                        <button
                                            type="button"
                                            class="aipkit_popover_option_nav aipkit_sources_settings_nav"
                                            data-aipkit-panel-target="general"
                                        >
                                            <span class="aipkit_popover_option_label">
                                                <span class="aipkit_popover_option_icon dashicons dashicons-admin-settings" aria-hidden="true"></span>
                                                <span class="aipkit_popover_option_label_content">
                                                    <span class="aipkit_popover_option_label_text">
                                                        <?php esc_html_e('General', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                    <span class="aipkit_popover_option_hint">
                                                        <?php esc_html_e('Visibility and basics', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                        <button
                                            type="button"
                                            class="aipkit_popover_option_nav aipkit_sources_settings_nav"
                                            data-aipkit-panel-target="provider-credentials"
                                        >
                                            <span class="aipkit_popover_option_label">
                                                <span class="aipkit_popover_option_icon dashicons dashicons-lock" aria-hidden="true"></span>
                                                <span class="aipkit_popover_option_label_content">
                                                    <span class="aipkit_popover_option_label_text">
                                                        <?php esc_html_e('Provider credentials', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                    <span class="aipkit_popover_option_hint">
                                                        <?php esc_html_e('Keys and endpoints', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="aipkit_popover_option_group">
                                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                        <button
                                            type="button"
                                            class="aipkit_popover_option_nav aipkit_sources_settings_nav"
                                            data-aipkit-panel-target="document-chunking"
                                        >
                                            <span class="aipkit_popover_option_label">
                                                <span class="aipkit_popover_option_icon dashicons dashicons-editor-ol" aria-hidden="true"></span>
                                                <span class="aipkit_popover_option_label_content">
                                                    <span class="aipkit_popover_option_label_text">
                                                        <?php esc_html_e('Document chunking', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                    <span class="aipkit_popover_option_hint">
                                                        <?php esc_html_e('Chunk size settings', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                        <button
                                            type="button"
                                            class="aipkit_popover_option_nav aipkit_sources_settings_nav"
                                            data-aipkit-settings-action="indexing-controls"
                                        >
                                            <span class="aipkit_popover_option_label">
                                                <span class="aipkit_popover_option_icon dashicons dashicons-controls-repeat" aria-hidden="true"></span>
                                                <span class="aipkit_popover_option_label_content">
                                                    <span class="aipkit_popover_option_label_text">
                                                        <?php esc_html_e('Indexing controls', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                    <span class="aipkit_popover_option_hint">
                                                        <?php esc_html_e('Manage indexing rules', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                            </span>
                                        </button>
                                    </div>
                                    <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                        <button
                                            type="button"
                                            class="aipkit_popover_option_nav aipkit_sources_settings_nav"
                                            data-aipkit-settings-action="semantic-search"
                                        >
                                            <span class="aipkit_popover_option_label">
                                                <span class="aipkit_popover_option_icon dashicons dashicons-search" aria-hidden="true"></span>
                                                <span class="aipkit_popover_option_label_content">
                                                    <span class="aipkit_popover_option_label_text">
                                                        <?php esc_html_e('Semantic search', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                    <span class="aipkit_popover_option_hint">
                                                        <?php esc_html_e('Search configuration', 'gpt3-ai-content-generator'); ?>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="aipkit_popover_flyout_footer">
                                <span class="aipkit_popover_flyout_footer_text">
                                    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                                </span>
                                <a
                                    class="aipkit_popover_flyout_footer_link"
                                    href="<?php echo esc_url('https://docs.aipower.org/docs'); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="general" hidden>
                            <div class="aipkit_popover_options_list">
                                <div class="aipkit_popover_option_row" id="general-training-settings-content">
                                    <div class="aipkit_popover_option_main">
                                        <span class="aipkit_popover_option_label"><?php esc_html_e('Hide user uploads', 'gpt3-ai-content-generator'); ?></span>
                                        <label class="aipkit_switch">
                                            <input
                                                type="checkbox"
                                                id="aipkit_hide_user_uploads_checkbox"
                                                name="hide_user_uploads"
                                                value="1"
                                                <?php checked($hide_user_uploads_checked); ?>
                                            >
                                            <span class="aipkit_switch_slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="aipkit_popover_option_row">
                                    <div class="aipkit_popover_option_main">
                                        <span class="aipkit_popover_option_label"><?php esc_html_e('Show index button', 'gpt3-ai-content-generator'); ?></span>
                                        <label class="aipkit_switch">
                                            <input
                                                type="checkbox"
                                                id="aipkit_show_index_button_checkbox"
                                                name="show_index_button"
                                                value="1"
                                                <?php checked($show_index_button_checked); ?>
                                            >
                                            <span class="aipkit_switch_slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="provider-credentials" hidden>
                            <div class="aipkit_popover_options_list">
                                <div id="aipkit_sources_provider_popover_body" class="aipkit_popover_option_group">
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <label class="aipkit_popover_option_label" for="aipkit_sources_pinecone_api_key">
                                                <?php esc_html_e('Pinecone API Key', 'gpt3-ai-content-generator'); ?>
                                            </label>
                                            <div class="aipkit_popover_option_actions">
                                                <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                                                    <input
                                                        type="password"
                                                        id="aipkit_sources_pinecone_api_key"
                                                        name="pinecone_api_key"
                                                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--vector-wide aipkit_popover_option_input--framed"
                                                        value="<?php echo esc_attr($pinecone_api_key); ?>"
                                                        placeholder="<?php esc_attr_e('Enter your Pinecone API key', 'gpt3-ai-content-generator'); ?>"
                                                        data-aipkit-provider-config="pinecone_api_key"
                                                    >
                                                    <span class="aipkit_api-key-toggle"><span class="dashicons dashicons-visibility"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <label class="aipkit_popover_option_label" for="aipkit_sources_qdrant_url">
                                                <?php esc_html_e('Qdrant URL', 'gpt3-ai-content-generator'); ?>
                                            </label>
                                            <div class="aipkit_popover_option_actions">
                                                <div class="aipkit_input-with-icon-wrapper">
                                                    <input
                                                        type="url"
                                                        id="aipkit_sources_qdrant_url"
                                                        name="qdrant_url"
                                                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--vector-wide aipkit_popover_option_input--framed"
                                                        value="<?php echo esc_attr($qdrant_url); ?>"
                                                        placeholder="<?php esc_attr_e('e.g., http://localhost:6333 or https://your-cloud-id.qdrant.cloud', 'gpt3-ai-content-generator'); ?>"
                                                        data-aipkit-provider-config="qdrant_url"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="aipkit_popover_option_row">
                                        <div class="aipkit_popover_option_main">
                                            <label class="aipkit_popover_option_label" for="aipkit_sources_qdrant_api_key">
                                                <?php esc_html_e('Qdrant API Key', 'gpt3-ai-content-generator'); ?>
                                            </label>
                                            <div class="aipkit_popover_option_actions">
                                                <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                                                    <input
                                                        type="password"
                                                        id="aipkit_sources_qdrant_api_key"
                                                        name="qdrant_api_key"
                                                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--vector-wide aipkit_popover_option_input--framed"
                                                        value="<?php echo esc_attr($qdrant_api_key); ?>"
                                                        placeholder="<?php esc_attr_e('Enter your Qdrant API key', 'gpt3-ai-content-generator'); ?>"
                                                        data-aipkit-provider-config="qdrant_api_key"
                                                    >
                                                    <span class="aipkit_api-key-toggle"><span class="dashicons dashicons-visibility"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="document-chunking" hidden>
                            <div class="aipkit_popover_options_list">
                                <div class="aipkit_popover_option_row">
                                    <div class="aipkit_popover_option_main">
                                        <label class="aipkit_popover_option_label" for="aipkit_chunk_avg_chars_per_token">
                                            <?php esc_html_e('Avg chars per token', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                        <input
                                            type="number"
                                            min="2"
                                            max="10"
                                            step="1"
                                            id="aipkit_chunk_avg_chars_per_token"
                                            name="chunk_avg_chars_per_token"
                                            class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact aipkit_popover_option_input--framed"
                                            value="<?php echo esc_attr($chunk_avg_chars); ?>"
                                            <?php echo !$is_pro_plan ? 'disabled' : ''; ?>
                                        >
                                    </div>
                                </div>
                                <div class="aipkit_popover_option_row">
                                    <div class="aipkit_popover_option_main">
                                        <label class="aipkit_popover_option_label" for="aipkit_chunk_max_tokens_per_chunk">
                                            <?php esc_html_e('Max tokens per chunk', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                        <input
                                            type="number"
                                            min="256"
                                            max="8000"
                                            step="1"
                                            id="aipkit_chunk_max_tokens_per_chunk"
                                            name="chunk_max_tokens_per_chunk"
                                            class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact aipkit_popover_option_input--framed"
                                            value="<?php echo esc_attr($chunk_max_tokens); ?>"
                                            <?php echo !$is_pro_plan ? 'disabled' : ''; ?>
                                        >
                                    </div>
                                </div>
                                <div class="aipkit_popover_option_row">
                                    <div class="aipkit_popover_option_main">
                                        <label class="aipkit_popover_option_label" for="aipkit_chunk_overlap_tokens">
                                            <?php esc_html_e('Overlap tokens', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                        <input
                                            type="number"
                                            min="0"
                                            max="1000"
                                            step="1"
                                            id="aipkit_chunk_overlap_tokens"
                                            name="chunk_overlap_tokens"
                                            class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact aipkit_popover_option_input--framed"
                                            value="<?php echo esc_attr($chunk_overlap_tokens); ?>"
                                            <?php echo !$is_pro_plan ? 'disabled' : ''; ?>
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div id="aipkit_sources_training_card" hidden>
                <div class="aipkit_builder_card_body">
                    <div class="aipkit_builder_tabs aipkit_builder_tabs--training" role="tablist" aria-label="<?php esc_attr_e('Training sources', 'gpt3-ai-content-generator'); ?>" data-aipkit-tabs="training">
                        <button type="button" class="aipkit_builder_tab is-active" role="tab" aria-selected="true" data-aipkit-tab="qa">
                            <?php esc_html_e('Q&A', 'gpt3-ai-content-generator'); ?>
                        </button>
                        <button type="button" class="aipkit_builder_tab" role="tab" aria-selected="false" data-aipkit-tab="text">
                            <?php esc_html_e('Text', 'gpt3-ai-content-generator'); ?>
                        </button>
                        <button type="button" class="aipkit_builder_tab" role="tab" aria-selected="false" data-aipkit-tab="files">
                            <?php esc_html_e('Files', 'gpt3-ai-content-generator'); ?>
                        </button>
                        <button type="button" class="aipkit_builder_tab" role="tab" aria-selected="false" data-aipkit-tab="website">
                            <?php esc_html_e('Website', 'gpt3-ai-content-generator'); ?>
                        </button>
                    </div>

                    <div class="aipkit_builder_tab_panels aipkit_builder_tab_panels--training">
                        <div class="aipkit_builder_tab_panel is-active" data-aipkit-panel="qa">
                            <div class="aipkit_builder_training_qa">
                                <div class="aipkit_training_field">
                                    <label class="aipkit_training_label" for="aipkit_training_qa_question">
                                        <?php esc_html_e('Question', 'gpt3-ai-content-generator'); ?>
                                    </label>
                                    <textarea
                                        id="aipkit_training_qa_question"
                                        class="aipkit_builder_textarea aipkit_training_textarea"
                                        rows="3"
                                        placeholder="<?php esc_attr_e('What is your refund policy?', 'gpt3-ai-content-generator'); ?>"
                                    ></textarea>
                                </div>
                                <div class="aipkit_training_field">
                                    <label class="aipkit_training_label" for="aipkit_training_qa_answer">
                                        <?php esc_html_e('Answer', 'gpt3-ai-content-generator'); ?>
                                    </label>
                                    <textarea
                                        id="aipkit_training_qa_answer"
                                        class="aipkit_builder_textarea aipkit_training_textarea"
                                        rows="3"
                                        placeholder="<?php esc_attr_e('We offer refunds within 30 days of purchase.', 'gpt3-ai-content-generator'); ?>"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="aipkit_builder_tab_panel" data-aipkit-panel="text" hidden>
                            <div class="aipkit_training_field">
                                <label class="aipkit_training_label" for="aipkit_training_text_input">
                                    <?php esc_html_e('Text', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <textarea
                                    id="aipkit_training_text_input"
                                    name="training_text"
                                    class="aipkit_builder_textarea aipkit_training_textarea aipkit_training_text_input"
                                    rows="6"
                                    placeholder="<?php esc_attr_e('Add training text...', 'gpt3-ai-content-generator'); ?>"
                                ></textarea>
                            </div>
                        </div>
                        <div class="aipkit_builder_tab_panel" data-aipkit-panel="files" hidden>
                            <div class="aipkit_training_field">
                                <span class="aipkit_training_label">
                                    <?php esc_html_e('Files', 'gpt3-ai-content-generator'); ?>
                                </span>
                                <div class="aipkit_builder_dropzone aipkit_training_dropzone">
                                    <div class="aipkit_builder_dropzone_inner">
                                        <?php if ( $is_pro_plan ) : ?>
                                            <input
                                                id="aipkit_training_files_input"
                                                class="aipkit_training_files_input"
                                                type="file"
                                                multiple
                                                accept=".pdf,.docx,.txt,.md,.csv,.json"
                                                hidden
                                            >
                                            <button
                                                type="button"
                                                class="aipkit_btn aipkit_btn-secondary aipkit_builder_action_btn aipkit_training_files_button"
                                            >
                                                <?php esc_html_e('Choose files', 'gpt3-ai-content-generator'); ?>
                                            </button>
                                        <?php else : ?>
                                            <a
                                                class="aipkit_btn aipkit_btn-primary aipkit_builder_action_btn aipkit_training_files_button"
                                                href="<?php echo esc_url($upgrade_url); ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
                                            </a>
                                        <?php endif; ?>
                                        <p class="aipkit_builder_help_text">
                                            <?php esc_html_e('Supported: pdf, docx, txt, md, csv, json', 'gpt3-ai-content-generator'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="aipkit_training_file_list"
                                id="aipkit_training_file_list"
                                data-upgrade-url="<?php echo esc_url($upgrade_url); ?>"
                            ></div>
                        </div>
                        <div class="aipkit_builder_tab_panel" data-aipkit-panel="website" hidden>
                            <div class="aipkit_training_website">
                                <div class="aipkit_training_site_row">
                                    <span class="aipkit_training_site_label"><?php esc_html_e('Mode', 'gpt3-ai-content-generator'); ?></span>
                                    <div class="aipkit_training_site_toggle">
                                        <label class="aipkit_training_site_option">
                                            <input type="radio" name="aipkit_wp_content_mode" id="aipkit_wp_content_mode_bulk" value="bulk" checked>
                                            <?php esc_html_e('All', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                        <label class="aipkit_training_site_option">
                                            <input type="radio" name="aipkit_wp_content_mode" id="aipkit_wp_content_mode_specific" value="specific">
                                            <?php esc_html_e('Specific', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                    </div>
                                    <span class="aipkit_training_site_divider" aria-hidden="true">|</span>
                                    <div class="aipkit_training_site_group aipkit_training_site_group--status">
                                        <span class="aipkit_training_site_label"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></span>
                                        <div id="aipkit_wp_content_status_wrap_bulk" class="aipkit_training_site_status_wrap">
                                            <select id="aipkit_vs_wp_content_status" class="aipkit_form-input aipkit_training_site_select">
                                                <option value="publish"><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
                                                <option value="draft"><?php esc_html_e('Draft', 'gpt3-ai-content-generator'); ?></option>
                                                <option value="any"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                            </select>
                                        </div>
                                        <div id="aipkit_wp_content_status_wrap_specific" class="aipkit_training_site_status_wrap" hidden>
                                            <select id="aipkit_vs_wp_content_status_specific" class="aipkit_form-input aipkit_training_site_select">
                                                <option value="publish"><?php esc_html_e('Published', 'gpt3-ai-content-generator'); ?></option>
                                                <option value="draft"><?php esc_html_e('Draft', 'gpt3-ai-content-generator'); ?></option>
                                                <option value="any"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <span class="aipkit_training_site_divider" aria-hidden="true">|</span>
                                    <div id="aipkit_wp_content_bulk_panel" class="aipkit_training_site_group">
                                        <span class="aipkit_training_site_label"><?php esc_html_e('Types', 'gpt3-ai-content-generator'); ?></span>
                                        <div
                                            class="aipkit_training_site_dropdown"
                                            data-aipkit-training-types="bulk"
                                            data-placeholder="<?php echo esc_attr__('Select types', 'gpt3-ai-content-generator'); ?>"
                                            data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                                        >
                                            <button
                                                type="button"
                                                class="aipkit_training_site_dropdown_btn"
                                                aria-expanded="false"
                                                aria-controls="aipkit_training_types_menu_bulk"
                                            >
                                                <span class="aipkit_training_site_dropdown_label">
                                                    <?php esc_html_e('Select types', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </button>
                                            <div
                                                id="aipkit_training_types_menu_bulk"
                                                class="aipkit_training_site_dropdown_panel"
                                                role="menu"
                                                hidden
                                            >
                                                <div id="aipkit_vs_wp_types_checkboxes" class="aipkit_training_site_checks aipkit_training_site_checks--dropdown">
                                                    <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                                        <label class="aipkit_training_site_check" data-ptype="<?php echo esc_attr($post_type_slug); ?>">
                                                            <input type="checkbox" class="aipkit_wp_type_cb" value="<?php echo esc_attr($post_type_slug); ?>" <?php checked(in_array($post_type_slug, ['post', 'page'], true)); ?> />
                                                            <span class="aipkit_training_site_check_label"><?php echo esc_html($post_type_obj->label); ?></span>
                                                            <span class="aipkit_count_badge" data-count="-1"></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <select id="aipkit_vs_wp_content_post_types" class="aipkit_training_site_hidden_select" multiple size="3">
                                        <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                            <option value="<?php echo esc_attr($post_type_slug); ?>" <?php selected(in_array($post_type_slug, ['post', 'page'], true)); ?>>
                                                <?php echo esc_html($post_type_obj->label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div id="aipkit_wp_content_specific_types_panel" class="aipkit_training_site_group" hidden>
                                        <span class="aipkit_training_site_label"><?php esc_html_e('Types', 'gpt3-ai-content-generator'); ?></span>
                                        <div
                                            class="aipkit_training_site_dropdown"
                                            data-aipkit-training-types="specific"
                                            data-placeholder="<?php echo esc_attr__('Select types', 'gpt3-ai-content-generator'); ?>"
                                            data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                                        >
                                            <button
                                                type="button"
                                                class="aipkit_training_site_dropdown_btn"
                                                aria-expanded="false"
                                                aria-controls="aipkit_training_types_menu_specific"
                                            >
                                                <span class="aipkit_training_site_dropdown_label">
                                                    <?php esc_html_e('Select types', 'gpt3-ai-content-generator'); ?>
                                                </span>
                                            </button>
                                            <div
                                                id="aipkit_training_types_menu_specific"
                                                class="aipkit_training_site_dropdown_panel"
                                                role="menu"
                                                hidden
                                            >
                                                <div id="aipkit_vs_wp_types_checkboxes_specific" class="aipkit_training_site_checks aipkit_training_site_checks--dropdown">
                                                    <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                                        <label class="aipkit_training_site_check" data-ptype="<?php echo esc_attr($post_type_slug); ?>">
                                                            <input type="checkbox" class="aipkit_wp_type_cb" value="<?php echo esc_attr($post_type_slug); ?>" <?php checked(in_array($post_type_slug, ['post', 'page'], true)); ?> />
                                                            <span class="aipkit_training_site_check_label"><?php echo esc_html($post_type_obj->label); ?></span>
                                                            <span class="aipkit_count_badge" data-count="-1"></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <select id="aipkit_vs_wp_content_post_types_specific" class="aipkit_training_site_hidden_select" multiple size="3">
                                        <?php foreach ($all_selectable_post_types as $post_type_slug => $post_type_obj) : ?>
                                            <option value="<?php echo esc_attr($post_type_slug); ?>" <?php selected(in_array($post_type_slug, ['post', 'page'], true)); ?>>
                                                <?php echo esc_html($post_type_obj->label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <input type="hidden" id="aipkit_vs_wp_content_mode" value="bulk" />
                                <div id="aipkit_wp_content_bulk_hint" class="aipkit_training_site_hint">
                                    <?php esc_html_e('Select post types to index all matching content.', 'gpt3-ai-content-generator'); ?>
                                </div>
                                <div id="aipkit_background_indexing_confirm" class="aipkit_inline_confirm" hidden>
                                    <div class="aipkit_inline_confirm_content">
                                        <p id="aipkit_background_indexing_message" class="aipkit_builder_help_text"></p>
                                        <div class="aipkit_inline_confirm_actions">
                                            <button type="button" id="aipkit_background_indexing_yes" class="aipkit_btn aipkit_btn-primary aipkit_btn-sm">
                                                <?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?>
                                            </button>
                                            <button type="button" id="aipkit_background_indexing_no" class="aipkit_btn aipkit_btn-secondary aipkit_btn-sm">
                                                <?php esc_html_e('No', 'gpt3-ai-content-generator'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div id="aipkit_wp_content_specific_panel" class="aipkit_training_site_panel" hidden>
                                    <div id="aipkit_vs_wp_content_list_area" class="aipkit_training_site_list"></div>
                                    <div id="aipkit_vs_wp_content_pagination" class="aipkit_training_site_pagination"></div>
                                </div>
                                <div id="aipkit_vs_wp_content_messages_area" class="aipkit_form-help aipkit_training_site_status" aria-live="polite"></div>
                                <select id="aipkit_vs_global_target_select" class="aipkit_training_site_target_select" aria-hidden="true" tabindex="-1"></select>
                            </div>
                        </div>
                    </div>

                    <div class="aipkit_training_footer">
                        <div class="aipkit_builder_action_row aipkit_training_action_row">
                            <div class="aipkit_builder_action_group aipkit_training_primary_actions">
                                <button
                                    type="button"
                                    class="aipkit_btn aipkit_btn-primary aipkit_builder_action_btn aipkit_training_action_btn"
                                    data-training-action="add"
                                >
                                    <?php esc_html_e('Train', 'gpt3-ai-content-generator'); ?>
                                </button>
                                <button
                                    type="button"
                                    class="aipkit_btn aipkit_btn-secondary aipkit_builder_action_btn aipkit_training_stop_btn"
                                    hidden
                                >
                                    <?php esc_html_e('Stop', 'gpt3-ai-content-generator'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="aipkit_data-table aipkit_sources_table">
                <table>
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Time', 'gpt3-ai-content-generator'); ?></th>
                            <th><?php esc_html_e('Index', 'gpt3-ai-content-generator'); ?></th>
                            <th>
                                <label class="screen-reader-text" for="aipkit_sources_type_filter">
                                    <?php esc_html_e('Type filter', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <select id="aipkit_sources_type_filter" class="aipkit_popover_select aipkit_sources_filter_select">
                                    <option value=""><?php esc_html_e('Types', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="site"><?php esc_html_e('Site Content', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="text"><?php esc_html_e('Text', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="file"><?php esc_html_e('File Upload', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </th>
                            <th><?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?></th>
                            <th>
                                <label class="screen-reader-text" for="aipkit_sources_status_filter">
                                    <?php esc_html_e('Status filter', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <select id="aipkit_sources_status_filter" class="aipkit_popover_select aipkit_sources_filter_select">
                                    <option value=""><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="indexed"><?php esc_html_e('Trained', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="processing"><?php esc_html_e('Processing', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="failed"><?php esc_html_e('Failed', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </th>
                            <th class="aipkit_actions_cell_header"><?php esc_html_e('Actions', 'gpt3-ai-content-generator'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="aipkit_sources_table_body">
                        <tr>
                            <td colspan="6" class="aipkit_text-center">
                                <?php esc_html_e('Sources will appear here.', 'gpt3-ai-content-generator'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="aipkit_sources_pagination" class="aipkit_logs_pagination_container"></div>
            <div class="aipkit_builder_sheet_overlay" id="aipkit_sources_settings_sheet" aria-hidden="true">
                <div
                    class="aipkit_builder_sheet_panel"
                    role="dialog"
                    aria-labelledby="aipkit_sources_settings_sheet_title"
                    aria-describedby="aipkit_sources_settings_sheet_description"
                >
                    <div class="aipkit_builder_sheet_header">
                        <div>
                            <div class="aipkit_builder_sheet_title_row">
                                <h3 class="aipkit_builder_sheet_title" id="aipkit_sources_settings_sheet_title">
                                    <?php esc_html_e('Content Indexing Controls', 'gpt3-ai-content-generator'); ?>
                                </h3>
                            </div>
                            <p class="aipkit_builder_sheet_description" id="aipkit_sources_settings_sheet_description">
                                <?php esc_html_e('Select which fields and taxonomies are included when indexing content.', 'gpt3-ai-content-generator'); ?>
                            </p>
                        </div>
                        <button type="button" class="aipkit_builder_sheet_close" id="aipkit_sources_settings_sheet_close" aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>">
                            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="aipkit_builder_sheet_body">
                        <div id="indexing-settings-tab-content" data-initialized="false">
                            <form id="aipkit_indexing_settings_form" onsubmit="return false;">
                                <div id="aipkit_indexing_settings_form_container"></div>
                            </form>
                        </div>
                        <div class="aipkit_builder_action_row aipkit_sources_settings_sheet_actions">
                            <div id="aipkit_sources_settings_sheet_messages" class="aipkit_settings_messages"></div>
                            <?php if ($is_pro_plan): ?>
                                <button id="aipkit_save_indexing_settings_btn" class="aipkit_btn aipkit_btn-primary">
                                    <span class="aipkit_btn-text"><?php esc_html_e('Save', 'gpt3-ai-content-generator'); ?></span>
                                    <span class="aipkit_spinner" style="display:none;"></span>
                                </button>
                            <?php else: ?>
                                <button
                                    id="aipkit_save_indexing_settings_btn"
                                    class="aipkit_btn aipkit_btn-secondary"
                                    data-upgrade-only="1"
                                    data-upgrade-url="<?php echo esc_url($upgrade_url); ?>"
                                >
                                    <span class="aipkit_btn-text"><?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?></span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="aipkit_builder_sheet_overlay" id="aipkit_sources_semantic_sheet" aria-hidden="true">
                <div
                    class="aipkit_builder_sheet_panel"
                    role="dialog"
                    aria-labelledby="aipkit_sources_semantic_sheet_title"
                    aria-describedby="aipkit_sources_semantic_sheet_description"
                >
                    <div class="aipkit_builder_sheet_header">
                        <div>
                            <div class="aipkit_builder_sheet_title_row">
                                <h3 class="aipkit_builder_sheet_title" id="aipkit_sources_semantic_sheet_title">
                                    <?php esc_html_e('Semantic Search', 'gpt3-ai-content-generator'); ?>
                                </h3>
                            </div>
                            <p class="aipkit_builder_sheet_description" id="aipkit_sources_semantic_sheet_description">
                                <?php esc_html_e('Configure global semantic search settings and preview results before embedding.', 'gpt3-ai-content-generator'); ?>
                            </p>
                        </div>
                        <button
                            type="button"
                            class="aipkit_builder_sheet_close"
                            id="aipkit_sources_semantic_sheet_close"
                            aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                        >
                            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="aipkit_builder_sheet_body">
                        <div class="aipkit_builder_card">
                            <div class="aipkit_builder_card_header">
                                <h4 class="aipkit_builder_card_title"><?php esc_html_e('Configuration', 'gpt3-ai-content-generator'); ?></h4>
                            </div>
                            <form id="aipkit_sources_semantic_form" onsubmit="return false;">
                                <div class="aipkit_form-row aipkit_sources_semantic_row">
                                    <div class="aipkit_form-group aipkit_form-col">
                                        <label class="aipkit_form-label" for="aipkit_sources_semantic_vector_provider"><?php esc_html_e('Vector DB', 'gpt3-ai-content-generator'); ?></label>
                                        <select id="aipkit_sources_semantic_vector_provider" name="semantic_search_vector_provider" class="aipkit_form-input">
                                            <option value="pinecone" <?php selected($semantic_vector_provider, 'pinecone'); ?>><?php esc_html_e('Pinecone', 'gpt3-ai-content-generator'); ?></option>
                                            <option value="qdrant" <?php selected($semantic_vector_provider, 'qdrant'); ?>><?php esc_html_e('Qdrant', 'gpt3-ai-content-generator'); ?></option>
                                        </select>
                                    </div>
                                    <div class="aipkit_form-group aipkit_form-col aipkit_sources_semantic_target">
                                        <label class="aipkit_form-label" id="aipkit_sources_semantic_target_label" for="aipkit_sources_semantic_target_id"><?php esc_html_e('Index', 'gpt3-ai-content-generator'); ?></label>
                                        <select id="aipkit_sources_semantic_target_id" name="semantic_search_target_id" class="aipkit_form-input">
                                            <option value=""><?php esc_html_e('-- Select --', 'gpt3-ai-content-generator'); ?></option>
                                            <?php
                                            $semantic_current_list = [];
                                            if ($semantic_vector_provider === 'pinecone') {
                                                $semantic_current_list = $pinecone_index_list;
                                            } elseif ($semantic_vector_provider === 'qdrant') {
                                                $semantic_current_list = $qdrant_collection_list;
                                            }

                                            $semantic_target_found = false;
                                            if (!empty($semantic_current_list)) {
                                                foreach ($semantic_current_list as $item) {
                                                    $item_name = is_array($item) ? ($item['name'] ?? ($item['id'] ?? '')) : $item;
                                                    if (empty($item_name)) {
                                                        continue;
                                                    }
                                                    $is_selected = selected($semantic_target_id, $item_name, false);
                                                    if ($is_selected) {
                                                        $semantic_target_found = true;
                                                    }
                                                    echo '<option value="' . esc_attr($item_name) . '" ' . $is_selected . '>' . esc_html($item_name) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe selected output.
                                                }
                                            }
                                            if (!$semantic_target_found && !empty($semantic_target_id)) {
                                                echo '<option value="' . esc_attr($semantic_target_id) . '" selected>' . esc_html($semantic_target_id) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="aipkit_form-group aipkit_form-col aipkit_sources_semantic_embedding">
                                        <label class="aipkit_form-label" for="aipkit_sources_semantic_embedding_model"><?php esc_html_e('Embedding', 'gpt3-ai-content-generator'); ?></label>
                                        <select id="aipkit_sources_semantic_embedding_model" name="semantic_search_embedding_model" class="aipkit_form-input">
                                            <optgroup label="<?php esc_attr_e('OpenAI', 'gpt3-ai-content-generator'); ?>">
                                                <?php foreach ($openai_embedding_models as $model_item): ?>
                                                    <option value="<?php echo esc_attr($model_item['id']); ?>" <?php selected($semantic_embedding_model, $model_item['id']); ?> data-provider="openai">
                                                        <?php echo esc_html($model_item['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <optgroup label="<?php esc_attr_e('Google', 'gpt3-ai-content-generator'); ?>">
                                                <?php foreach ($google_embedding_models as $model_item): ?>
                                                    <option value="<?php echo esc_attr($model_item['id']); ?>" <?php selected($semantic_embedding_model, $model_item['id']); ?> data-provider="google">
                                                        <?php echo esc_html($model_item['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <optgroup label="<?php esc_attr_e('OpenRouter', 'gpt3-ai-content-generator'); ?>">
                                                <?php foreach ($openrouter_embedding_models as $model_item): ?>
                                                    <option value="<?php echo esc_attr($model_item['id']); ?>" <?php selected($semantic_embedding_model, $model_item['id']); ?> data-provider="openrouter">
                                                        <?php echo esc_html($model_item['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                            <?php if (!empty($semantic_embedding_model) && !isset($all_embedding_models_map[$semantic_embedding_model])): ?>
                                                <option value="<?php echo esc_attr($semantic_embedding_model); ?>" data-provider="<?php echo esc_attr($semantic_embedding_provider); ?>" selected><?php echo esc_html($semantic_embedding_model); ?></option>
                                            <?php endif; ?>
                                        </select>
                                        <input type="hidden" id="aipkit_sources_semantic_embedding_provider" name="semantic_search_embedding_provider" value="<?php echo esc_attr($semantic_embedding_provider); ?>">
                                    </div>
                                    <div class="aipkit_form-group aipkit_form-col aipkit_sources_semantic_count">
                                        <label class="aipkit_form-label" for="aipkit_sources_semantic_num_results"><?php esc_html_e('Number of Results', 'gpt3-ai-content-generator'); ?></label>
                                        <input type="number" id="aipkit_sources_semantic_num_results" name="semantic_search_num_results" class="aipkit_form-input" value="<?php echo esc_attr($semantic_num_results); ?>" min="1" max="20" />
                                    </div>
                                    <div class="aipkit_form-group aipkit_form-col aipkit_sources_semantic_text">
                                        <label class="aipkit_form-label" for="aipkit_sources_semantic_no_results_text"><?php esc_html_e('No Results Text', 'gpt3-ai-content-generator'); ?></label>
                                        <input type="text" id="aipkit_sources_semantic_no_results_text" name="semantic_search_no_results_text" class="aipkit_form-input" value="<?php echo esc_attr($semantic_no_results_text); ?>" />
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="aipkit_builder_card">
                            <div class="aipkit_builder_card_header aipkit_builder_card_header--shortcode">
                                <h4 class="aipkit_builder_card_title"><?php esc_html_e('Shortcode', 'gpt3-ai-content-generator'); ?></h4>
                                <span class="aipkit_builder_card_hint"><?php esc_html_e('Use this on any page', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <div class="aipkit_input-with-button">
                                <input type="text" class="aipkit_form-input" value="[aipkit_semantic_search]" readonly>
                                <button type="button" class="aipkit_btn aipkit_btn-secondary" id="aipkit_sources_semantic_shortcode_copy">
                                    <span class="aipkit_btn-text"><?php esc_html_e('Copy', 'gpt3-ai-content-generator'); ?></span>
                                </button>
                            </div>
                            <p class="aipkit_form-help"><?php esc_html_e('Paste the shortcode to embed the semantic search form.', 'gpt3-ai-content-generator'); ?></p>
                        </div>
                        <div class="aipkit_builder_card">
                            <div class="aipkit_builder_card_header">
                                <h4 class="aipkit_builder_card_title"><?php esc_html_e('Try semantic search', 'gpt3-ai-content-generator'); ?></h4>
                            </div>
                            <form id="aipkit_sources_semantic_demo_form" class="aipkit_form-row">
                                <div class="aipkit_form-group aipkit_form-col">
                                    <input
                                        type="search"
                                        id="aipkit_sources_semantic_demo_input"
                                        class="aipkit_form-input"
                                        placeholder="<?php esc_attr_e('Search...', 'gpt3-ai-content-generator'); ?>"
                                        required
                                    >
                                </div>
                                <div class="aipkit_form-group">
                                    <button type="submit" class="aipkit_btn aipkit_btn-primary" id="aipkit_sources_semantic_demo_submit">
                                        <span class="aipkit_btn-text"><?php esc_html_e('Search', 'gpt3-ai-content-generator'); ?></span>
                                        <span class="aipkit_spinner" style="display:none;"></span>
                                    </button>
                                </div>
                            </form>
                            <div id="aipkit_sources_semantic_demo_results" class="aipkit_sources_semantic_results" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div
    class="aipkit-modal-overlay aipkit_builder_sources_editor_modal"
    id="aipkit_sources_editor_modal"
    aria-hidden="true"
>
    <div
        class="aipkit-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="aipkit_sources_editor_title"
        aria-describedby="aipkit_sources_editor_description"
    >
        <div class="aipkit-modal-header">
            <div>
                <h3 class="aipkit-modal-title" id="aipkit_sources_editor_title">
                    <?php esc_html_e('Edit source', 'gpt3-ai-content-generator'); ?>
                </h3>
                <p class="aipkit_builder_modal_subtitle" id="aipkit_sources_editor_description">
                    <?php esc_html_e('Update the source text and save to retrain this entry.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
            <button
                type="button"
                class="aipkit-modal-close-btn aipkit_sources_editor_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit-modal-body">
            <div class="aipkit_builder_field">
                <textarea
                    class="aipkit_builder_textarea aipkit_builder_textarea_large aipkit_sources_editor_textarea"
                    rows="10"
                    aria-label="<?php esc_attr_e('Source text', 'gpt3-ai-content-generator'); ?>"
                ></textarea>
            </div>
            <div class="aipkit_builder_action_row aipkit_sources_editor_actions">
                <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_sources_editor_cancel">
                    <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
                </button>
                <button type="button" class="aipkit_btn aipkit_btn-primary aipkit_sources_editor_save">
                    <?php esc_html_e('Save & retrain', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div
    class="aipkit-modal-overlay aipkit_sources_view_modal"
    id="aipkit_sources_view_modal"
    aria-hidden="true"
>
    <div
        class="aipkit-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="aipkit_sources_view_title"
        aria-describedby="aipkit_sources_view_description"
    >
        <div class="aipkit-modal-header">
            <div>
                <h3 class="aipkit-modal-title" id="aipkit_sources_view_title">
                    <?php esc_html_e('Source preview', 'gpt3-ai-content-generator'); ?>
                </h3>
                <p class="aipkit_builder_modal_subtitle" id="aipkit_sources_view_description">
                    <?php esc_html_e('Review the indexed content stored for this source.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
            <button
                type="button"
                class="aipkit-modal-close-btn aipkit_sources_view_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit-modal-body">
            <div class="aipkit_builder_field">
                <textarea
                    class="aipkit_builder_textarea aipkit_builder_textarea_large aipkit_sources_view_textarea"
                    rows="12"
                    readonly
                    aria-label="<?php esc_attr_e('Source content preview', 'gpt3-ai-content-generator'); ?>"
                ></textarea>
            </div>
            <div class="aipkit_builder_action_row">
                <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_sources_view_close_btn">
                    <?php esc_html_e('Close', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div
    class="aipkit-modal-overlay aipkit_builder_sources_delete_modal"
    id="aipkit_sources_delete_modal"
    aria-hidden="true"
>
    <div
        class="aipkit-modal-content"
        role="dialog"
        aria-modal="true"
        aria-labelledby="aipkit_sources_delete_title"
        aria-describedby="aipkit_sources_delete_description"
    >
        <div class="aipkit-modal-header">
            <div>
                <h3 class="aipkit-modal-title" id="aipkit_sources_delete_title">
                    <?php esc_html_e('Delete source', 'gpt3-ai-content-generator'); ?>
                </h3>
                <p class="aipkit_builder_modal_subtitle" id="aipkit_sources_delete_description">
                    <?php esc_html_e('This cannot be undone. The source will be removed from your knowledge base.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
            <button
                type="button"
                class="aipkit-modal-close-btn aipkit_sources_delete_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit-modal-body">
            <div class="aipkit_builder_action_row">
                <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_sources_delete_cancel">
                    <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
                </button>
                <button type="button" class="aipkit_btn aipkit_btn-danger aipkit_sources_delete_confirm">
                    <?php esc_html_e('Delete', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
