<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/chatbot/partials/accordion-context.php
// Status: MODIFIED

/**
 * Partial: Chatbot Context & Data Settings Accordion Content
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotSettingsManager; // For constants
use WPAICG\Vector\AIPKit_Vector_Store_Registry; // For fetching OpenAI vector stores
use WPAICG\AIPKit_Providers; // For fetching embedding models and Pinecone indexes
use WPAICG\aipkit_dashboard; // ADDED for addon and plan checks

// Variables available from parent script:
// $bot_id, $bot_settings
$content_aware_enabled = isset($bot_settings['content_aware_enabled']) ? $bot_settings['content_aware_enabled'] : BotSettingsManager::DEFAULT_CONTENT_AWARE_ENABLED;

// --- Vector Store Settings ---
$enable_vector_store = isset($bot_settings['enable_vector_store'])
                       ? $bot_settings['enable_vector_store']
                       : BotSettingsManager::DEFAULT_ENABLE_VECTOR_STORE;

// --- Logic from feature-toggles.php ---
$enable_file_upload = $bot_settings['enable_file_upload'] ?? BotSettingsManager::DEFAULT_ENABLE_FILE_UPLOAD;
$can_enable_file_upload = false;
$file_upload_disabled_reason = '';
$is_pro_plan_for_data_attr = 'false';
$file_upload_addon_active_for_data_attr = 'false';

if (class_exists(aipkit_dashboard::class)) {
    $is_pro_plan = aipkit_dashboard::is_pro_plan();
    $file_upload_addon_active = aipkit_dashboard::is_addon_active('file_upload');
    $is_vector_store_enabled_for_bot = ($enable_vector_store === '1');

    $is_pro_plan_for_data_attr = $is_pro_plan ? 'true' : 'false';
    $file_upload_addon_active_for_data_attr = $file_upload_addon_active ? 'true' : 'false';

    if (!$is_pro_plan) {
        $file_upload_disabled_reason = __('File upload is a Pro feature. Please upgrade.', 'gpt3-ai-content-generator');
    } elseif (!$file_upload_addon_active) {
        $file_upload_disabled_reason =
        'The "File Upload" addon is not active. Please activate it in Add-ons.';
    } elseif (!$is_vector_store_enabled_for_bot) {
        $file_upload_disabled_reason =
        '"Enable Vector Store" must be active for this bot to use file uploads.';
    } else {
        $can_enable_file_upload = true;
    }
} else {
    $file_upload_disabled_reason = __('Cannot determine Pro status or addon activation.', 'gpt3-ai-content-generator');
}
// --- END Logic from feature-toggles.php ---

$vector_store_provider = isset($bot_settings['vector_store_provider'])
                         ? $bot_settings['vector_store_provider']
                         : BotSettingsManager::DEFAULT_VECTOR_STORE_PROVIDER;

// OpenAI Specific
$openai_vector_store_ids_saved = isset($bot_settings['openai_vector_store_ids']) && is_array($bot_settings['openai_vector_store_ids'])
                               ? $bot_settings['openai_vector_store_ids']
                               : [];
// Pinecone Specific
$pinecone_index_name = $bot_settings['pinecone_index_name'] ?? BotSettingsManager::DEFAULT_PINECONE_INDEX_NAME;
$vector_embedding_provider = $bot_settings['vector_embedding_provider'] ?? BotSettingsManager::DEFAULT_VECTOR_EMBEDDING_PROVIDER;
$vector_embedding_model = $bot_settings['vector_embedding_model'] ?? BotSettingsManager::DEFAULT_VECTOR_EMBEDDING_MODEL;
// --- NEW: Qdrant Specific ---
$qdrant_collection_name = $bot_settings['qdrant_collection_name'] ?? BotSettingsManager::DEFAULT_QDRANT_COLLECTION_NAME;
// --- END NEW ---

$vector_store_top_k = isset($bot_settings['vector_store_top_k'])
                      ? absint($bot_settings['vector_store_top_k'])
                      : BotSettingsManager::DEFAULT_VECTOR_STORE_TOP_K;
$vector_store_top_k = max(1, min($vector_store_top_k, 20));

// --- NEW: Get Confidence Threshold ---
$vector_store_confidence_threshold = $bot_settings['vector_store_confidence_threshold']
                                     ?? BotSettingsManager::DEFAULT_VECTOR_STORE_CONFIDENCE_THRESHOLD;
$vector_store_confidence_threshold = max(0, min(absint($vector_store_confidence_threshold), 100));
// --- END NEW ---


// Fetch available OpenAI vector stores
$openai_vector_stores = [];
if (class_exists(AIPKit_Vector_Store_Registry::class)) {
    $openai_vector_stores = AIPKit_Vector_Store_Registry::get_registered_stores_by_provider('OpenAI');
}

// Fetch available Pinecone indexes
$pinecone_indexes = [];
if (class_exists(AIPKit_Providers::class)) {
    $pinecone_indexes = AIPKit_Providers::get_pinecone_indexes();
}

// --- NEW: Fetch available Qdrant Collections ---
$qdrant_collections = [];
if (class_exists(AIPKit_Providers::class)) {
    $qdrant_collections = AIPKit_Providers::get_qdrant_collections();
}
// --- END NEW ---


// Fetch available Embedding Models (for Pinecone/Qdrant)
$openai_embedding_models = [];
$google_embedding_models = [];
$azure_embedding_models = [];
if (class_exists(AIPKit_Providers::class)) {
    $openai_embedding_models = AIPKit_Providers::get_openai_embedding_models();
    $google_embedding_models = AIPKit_Providers::get_google_embedding_models();
    $azure_embedding_models = AIPKit_Providers::get_azure_embedding_models();
}

?>
<div class="aipkit_accordion">
    <div class="aipkit_accordion-header">
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <?php esc_html_e('Context', 'gpt3-ai-content-generator'); ?>
    </div>
    <div class="aipkit_accordion-content">

        <!-- Inline row for Context Feature Toggles -->
        <div class="aipkit_form-row aipkit_checkbox-row">

            <!-- Content Aware Checkbox -->
            <div class="aipkit_form-group">
                <label
                    class="aipkit_form-label aipkit_checkbox-label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_content_aware_enabled"
                >
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_content_aware_enabled"
                        name="content_aware_enabled"
                        class="aipkit_toggle_switch"
                        value="1"
                        <?php checked($content_aware_enabled, '1'); ?>
                    >
                    <?php esc_html_e('Content Aware', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_form-help">
                    <?php esc_html_e('Use page content as context.', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>

            <!-- Vector Store Checkbox -->
            <div class="aipkit_form-group">
                <label
                    class="aipkit_form-label aipkit_checkbox-label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_vector_store"
                >
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_vector_store"
                        name="enable_vector_store"
                        class="aipkit_toggle_switch aipkit_vector_store_toggle_switch"
                        value="1"
                        <?php checked($enable_vector_store, '1'); ?>
                    >
                    <?php esc_html_e('Vector Store', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_form-help">
                    <?php esc_html_e('Use knowledge base.', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>

            <!-- File Upload Checkbox -->
            <div class="aipkit_form-group aipkit_file_upload_field_group">
                <label
                    class="aipkit_form-label aipkit_checkbox-label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_file_upload"
                    <?php if (!empty($file_upload_disabled_reason)): ?>
                        title="<?php echo esc_attr($file_upload_disabled_reason); ?>"
                    <?php endif; ?>
                >
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_file_upload"
                        name="enable_file_upload"
                        class="aipkit_toggle_switch aipkit_file_upload_toggle_switch"
                        value="1"
                        <?php checked($can_enable_file_upload && ($enable_file_upload === '1')); ?>
                        <?php disabled(!$can_enable_file_upload); ?>
                        data-is-pro-plan="<?php echo esc_attr($is_pro_plan_for_data_attr); ?>"
                        data-addon-active="<?php echo esc_attr($file_upload_addon_active_for_data_attr); ?>"
                    >
                    <?php esc_html_e('File Upload', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_form-help">
                    <?php esc_html_e('Allow users to upload files.', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>

        </div> <!-- End of inline checkbox row -->

        <!-- Vector Store Settings Section -->
        <div
            class="aipkit_vector_store_settings_conditional_row"
            style="display: <?php echo $enable_vector_store === '1' ? 'block' : 'none'; ?>; margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--aipkit_container-border);"
        >
            <div class="aipkit_form-row aipkit_form-row-align-bottom" style="flex-wrap: nowrap; gap: 10px;">
                <div class="aipkit_form-group aipkit_form-col" style="flex: 0 1 180px;">
                    <label
                        class="aipkit_form-label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_provider"
                    >
                        <?php esc_html_e('Vector Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_provider"
                        name="vector_store_provider"
                        class="aipkit_form-input aipkit_vector_store_provider_select"
                    >
                        <option value="openai" <?php selected($vector_store_provider, 'openai'); ?>>OpenAI</option>
                        <option value="pinecone" <?php selected($vector_store_provider, 'pinecone'); ?>>Pinecone</option>
                        <option value="qdrant" <?php selected($vector_store_provider, 'qdrant'); ?>>Qdrant</option>
                    </select>
                </div>

                <!-- OpenAI Vector Store ID Select (Conditional) -->
                <div
                    class="aipkit_form-group aipkit_form-col aipkit_vector_store_openai_field"
                    style="flex: 1 1 auto; display: <?php echo ($enable_vector_store === '1' && $vector_store_provider === 'openai') ? 'block' : 'none'; ?>;"
                >
                    <label
                        class="aipkit_form-label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_vector_store_ids"
                    >
                        <?php esc_html_e('Vector Stores (max 2)', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_vector_store_ids"
                        name="openai_vector_store_ids[]"
                        class="aipkit_form-input"
                        multiple
                        size="3"
                        style="min-height: 60px;"
                    >
                        <?php
                        if (!empty($openai_vector_stores)) {
                            foreach ($openai_vector_stores as $store) {
                                $store_id_val = $store['id'] ?? '';
                                $store_name = $store['name'] ?? $store_id_val;
                                $file_count_total = $store['file_counts']['total'] ?? null;
                                $file_count_display = ($file_count_total !== null) ? " ({$file_count_total} " . _n('File', 'Files', (int)$file_count_total, 'gpt3-ai-content-generator') . ")" : ' (Files: N/A)';
                                $option_text = esc_html($store_name . $file_count_display);
                                $is_selected_attr = in_array($store_id_val, $openai_vector_store_ids_saved, true) ? 'selected="selected"' : '';
                                echo '<option value="' . esc_attr($store_id_val) . '" ' . $is_selected_attr . '>' . $option_text . ' (ID: ' . esc_html(substr($store_id_val,0,15)).'...)</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: $is_selected_attr is a safe hardcoded attribute and $option_text is pre-escaped with esc_html().
                            }
                        }
                        foreach ($openai_vector_store_ids_saved as $saved_id) {
                            $found_in_list = false;
                            if (!empty($openai_vector_stores)) {
                                foreach ($openai_vector_stores as $store) {
                                    if (($store['id'] ?? '') === $saved_id) { $found_in_list = true; break; }
                                }
                            }
                            if (!$found_in_list) {
                                echo '<option value="' . esc_attr($saved_id) . '" selected="selected">' . esc_html($saved_id) . ' (Manual/Not Synced)</option>';
                            }
                        }
                        if (empty($openai_vector_stores) && empty($openai_vector_store_ids_saved)) {
                             echo '<option value="" disabled>'.esc_html__('-- No Stores Found (Sync in AI Training) --', 'gpt3-ai-content-generator').'</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Pinecone Index Select (Conditional) -->
                <div
                    class="aipkit_form-group aipkit_form-col aipkit_vector_store_pinecone_field"
                    style="flex: 1 1 auto; display: <?php echo ($enable_vector_store === '1' && $vector_store_provider === 'pinecone') ? 'block' : 'none'; ?>;"
                >
                    <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_pinecone_index_name">
                        <?php esc_html_e('Index', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_pinecone_index_name"
                        name="pinecone_index_name"
                        class="aipkit_form-input"
                    >
                        <option value=""><?php esc_html_e('-- Select Index --', 'gpt3-ai-content-generator'); ?></option>
                        <?php
                        if (!empty($pinecone_indexes)) {
                            foreach ($pinecone_indexes as $index) {
                                $index_id_val = $index['name'] ?? ($index['id'] ?? '');
                                $index_display_name = $index['name'] ?? $index_id_val;
                                $vector_count_display = (isset($index['total_vector_count']) && $index['total_vector_count'] !== 'Error' && $index['total_vector_count'] !== 'No Host')
                                                        ? sprintf(' (Vectors: %s)', number_format_i18n($index['total_vector_count']))
                                                        : (($index['total_vector_count'] ?? '') === 'Error' || ($index['total_vector_count'] ?? '') === 'No Host' ? ' (Stats Err)' : ' (Vectors: ?)');
                                $option_text = esc_html($index_display_name . $vector_count_display);
                                echo '<option value="' . esc_attr($index_id_val) . '" ' . selected($pinecone_index_name, $index_id_val, false) . '>' . $option_text . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: $option_text is pre-escaped with esc_html().
                            }
                        }
                        if (!empty($pinecone_index_name) && (empty($pinecone_indexes) || !in_array($pinecone_index_name, array_column($pinecone_indexes, 'name')))) {
                            echo '<option value="' . esc_attr($pinecone_index_name) . '" selected="selected">' . esc_html($pinecone_index_name) . ' (Manual/Not Synced)</option>';
                        }
                        if (empty($pinecone_indexes) && empty($pinecone_index_name)) {
                             echo '<option value="" disabled>'.esc_html__('-- No Indexes Found (Sync in AI Settings) --', 'gpt3-ai-content-generator').'</option>';
                        }
                        ?>
                    </select>
                </div>
                <!-- Qdrant Collection Select (Conditional) -->
                <div
                    class="aipkit_form-group aipkit_form-col aipkit_vector_store_qdrant_field"
                    style="flex: 1 1 auto; display: <?php echo ($enable_vector_store === '1' && $vector_store_provider === 'qdrant') ? 'block' : 'none'; ?>;"
                >
                    <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_qdrant_collection_name">
                        <?php esc_html_e('Collection', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_qdrant_collection_name"
                        name="qdrant_collection_name"
                        class="aipkit_form-input"
                    >
                        <option value=""><?php esc_html_e('-- Select Collection --', 'gpt3-ai-content-generator'); ?></option>
                        <?php
                        if (!empty($qdrant_collections)) {
                            foreach ($qdrant_collections as $collection) {
                                $collection_id_val = $collection['name'] ?? ($collection['id'] ?? '');
                                $collection_display_name = $collection['name'] ?? $collection_id_val;
                                echo '<option value="' . esc_attr($collection_id_val) . '" ' . selected($qdrant_collection_name, $collection_id_val, false) . '>' . esc_html($collection_display_name) . '</option>';
                            }
                        }
                        if (!empty($qdrant_collection_name) && (empty($qdrant_collections) || !in_array($qdrant_collection_name, array_column($qdrant_collections, 'name')))) {
                            echo '<option value="' . esc_attr($qdrant_collection_name) . '" selected="selected">' . esc_html($qdrant_collection_name) . ' (Manual/Not Synced)</option>';
                        }
                        if (empty($qdrant_collections) && empty($qdrant_collection_name)) {
                             echo '<option value="" disabled>'.esc_html__('-- No Collections Found (Sync in AI Settings) --', 'gpt3-ai-content-generator').'</option>';
                        }
                        ?>
                    </select>
                </div>

            </div>

             <!-- Embedding Provider & Model for Pinecone/Qdrant (Conditional) - Row 2 -->
            <div
                class="aipkit_vector_store_embedding_config_row"
                style="display: <?php echo ($enable_vector_store === '1' && ($vector_store_provider === 'pinecone' || $vector_store_provider === 'qdrant')) ? 'block' : 'none'; ?>; margin-top: 10px;"
            >
                <div class="aipkit_form-row" style="flex-wrap: nowrap; gap: 10px;">
                    <div class="aipkit_form-group aipkit_form-col" style="flex: 0 1 180px;">
                        <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_provider">
                            <?php esc_html_e('Embedding Provider', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_provider"
                            name="vector_embedding_provider"
                            class="aipkit_form-input aipkit_vector_embedding_provider_select" <?php // Class for JS ?>
                        >
                            <option value="openai" <?php selected($vector_embedding_provider, 'openai'); ?>>OpenAI</option>
                            <option value="google" <?php selected($vector_embedding_provider, 'google'); ?>>Google</option>
                            <option value="azure" <?php selected($vector_embedding_provider, 'azure'); ?>>Azure</option>
                        </select>
                    </div>
                    <div class="aipkit_form-group aipkit_form-col" style="flex: 1 1 auto;">
                        <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_model">
                            <?php esc_html_e('Embedding Model', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_model"
                            name="vector_embedding_model"
                            class="aipkit_form-input aipkit_vector_embedding_model_select" <?php // Class for JS ?>
                        >
                            <option value=""><?php esc_html_e('-- Select Model --', 'gpt3-ai-content-generator'); ?></option>
                            <?php
                            $current_embedding_list = [];
                            if ($vector_embedding_provider === 'openai') $current_embedding_list = $openai_embedding_models;
                            elseif ($vector_embedding_provider === 'google') $current_embedding_list = $google_embedding_models;
                            elseif ($vector_embedding_provider === 'azure') $current_embedding_list = $azure_embedding_models;

                            if (!empty($current_embedding_list)) {
                                foreach ($current_embedding_list as $model) {
                                    $model_id_val = $model['id'] ?? '';
                                    $model_name_val = $model['name'] ?? $model_id_val;
                                    echo '<option value="' . esc_attr($model_id_val) . '" ' . selected($vector_embedding_model, $model_id_val, false) . '>' . esc_html($model_name_val) . '</option>';
                                }
                            }
                            if (!empty($vector_embedding_model) && (empty($current_embedding_list) || !in_array($vector_embedding_model, array_column($current_embedding_list, 'id')))) {
                                 echo '<option value="' . esc_attr($vector_embedding_model) . '" selected="selected">' . esc_html($vector_embedding_model) . ' (Manual/Not Synced)</option>';
                            }
                            if (empty($current_embedding_list) && empty($vector_embedding_model)) {
                                 echo '<option value="" disabled>'.esc_html__('-- Select Provider or Sync Models --', 'gpt3-ai-content-generator').'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Dedicated row for Limit and Score Threshold sliders - Row 3 -->
            <div class="aipkit_form-row" style="margin-top: 10px; gap: 10px;">
                <!-- Top K Setting (always visible when vector store is enabled) -->
                <div
                    class="aipkit_form-group aipkit_form-col aipkit_vector_store_top_k_field"
                    style="flex: 1;"
                >
                    <label
                        class="aipkit_form-label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_top_k"
                    >
                        <?php esc_html_e('Limit', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_slider_wrapper">
                        <input
                            type="range"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_top_k"
                            name="vector_store_top_k"
                            class="aipkit_form-input aipkit_range_slider"
                            min="1"
                            max="20"
                            step="1"
                            value="<?php echo esc_attr($vector_store_top_k); ?>"
                        />
                        <span
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_top_k_value"
                            class="aipkit_slider_value"
                        >
                            <?php echo esc_html($vector_store_top_k); ?>
                        </span>
                    </div>
                </div>

                <!-- Confidence Threshold Setting (always visible when vector store is enabled) -->
                <div
                    class="aipkit_form-group aipkit_form-col aipkit_vector_store_confidence_field"
                    style="flex: 1;"
                >
                    <label
                        class="aipkit_form-label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_confidence_threshold"
                    >
                        <?php esc_html_e('Score Threshold', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_slider_wrapper">
                        <input
                            type="range"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_confidence_threshold"
                            name="vector_store_confidence_threshold"
                            class="aipkit_form-input aipkit_range_slider"
                            min="0" max="100" step="1"
                            value="<?php echo esc_attr($vector_store_confidence_threshold); ?>"
                        />
                        <span
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_confidence_threshold_value"
                            class="aipkit_slider_value"
                        >
                            <?php echo esc_html($vector_store_confidence_threshold); ?>%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Help text for Limit and Score Threshold -->
            <div class="aipkit_form-row" style="margin-top: 5px; gap: 10px;">
                <div class="aipkit_form-group" style="flex: 1;">
                    <div class="aipkit_form-help">
                        <?php esc_html_e('Number of results to retrieve from vector store.', 'gpt3-ai-content-generator'); ?>
                    </div>
                </div>
                <div class="aipkit_form-group" style="flex: 1;">
                    <div class="aipkit_form-help">
                        <?php esc_html_e('Only use results with a similarity score above this.', 'gpt3-ai-content-generator'); ?>
                    </div>
                </div>
            </div>

        </div>
        <!-- END Vector Store Settings Section -->

    </div>
</div>