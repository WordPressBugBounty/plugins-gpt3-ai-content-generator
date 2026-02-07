<?php
$bot_id = $initial_active_bot_id;
?>
<div class="aipkit_popover_options_list">
    <div class="aipkit_popover_option_row aipkit_vector_store_popover_row">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label_group">
                <span
                    class="aipkit_popover_option_label"
                    tabindex="0"
                    data-tooltip="<?php echo esc_attr__('Use your knowledge base for more accurate answers.', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Knowledge base', 'gpt3-ai-content-generator'); ?>
                </span>
                <span class="aipkit_popover_warning" data-tooltip="" aria-hidden="true">
                    <span class="dashicons dashicons-warning"></span>
                </span>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_vector_store_popover"
                        name="enable_vector_store"
                        class="aipkit_vector_store_enable_select"
                        value="1"
                        <?php checked($enable_vector_store, '1'); ?>
                    />
                    <span class="aipkit_switch_slider"></span>
                </label>
            </div>
        </div>
    </div>
    <div class="aipkit_vector_store_settings_conditional_row" style="<?php echo ($enable_vector_store === '1') ? '' : 'display:none;'; ?>">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_provider_modal"
                >
                    <?php esc_html_e('Vector provider', 'gpt3-ai-content-generator'); ?>
                </label>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_provider_modal"
                    name="vector_store_provider"
                    class="aipkit_popover_option_select aipkit_vector_store_provider_select"
                >
                    <option value="openai" <?php selected($vector_store_provider, 'openai'); ?>>OpenAI</option>
                    <option value="pinecone" <?php selected($vector_store_provider, 'pinecone'); ?>>Pinecone</option>
                    <option value="qdrant" <?php selected($vector_store_provider, 'qdrant'); ?>>Qdrant</option>
                    <option value="claude_files" <?php selected($vector_store_provider, 'claude_files'); ?>><?php esc_html_e('Claude Files', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>

        <div class="aipkit_popover_option_row aipkit_vector_store_openai_field" style="<?php echo ($enable_vector_store === '1' && $vector_store_provider === 'openai') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_vector_store_ids_modal"
                    data-tooltip="<?php echo esc_attr__('Select up to two stores.', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Vector stores (max 2)', 'gpt3-ai-content-generator'); ?>
                </label>
                <div
                    class="aipkit_popover_multiselect"
                    data-aipkit-vector-stores-dropdown
                    data-placeholder="<?php echo esc_attr__('Select stores', 'gpt3-ai-content-generator'); ?>"
                    data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                >
                    <button
                        type="button"
                        class="aipkit_popover_multiselect_btn"
                        aria-expanded="false"
                        aria-controls="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_vector_store_panel"
                    >
                        <span class="aipkit_popover_multiselect_label">
                            <?php esc_html_e('Select stores', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </button>
                    <div
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_vector_store_panel"
                        class="aipkit_popover_multiselect_panel"
                        role="menu"
                        hidden
                    >
                        <div class="aipkit_popover_multiselect_options"></div>
                    </div>
                </div>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_vector_store_ids_modal"
                    name="openai_vector_store_ids[]"
                    class="aipkit_popover_multiselect_select"
                    multiple
                    size="3"
                    hidden
                    aria-hidden="true"
                    tabindex="-1"
                >
                    <?php
                    if (!empty($openai_vector_stores)) {
                        $store_index = 1;
                        foreach ($openai_vector_stores as $store) {
                            $store_id_val = $store['id'] ?? '';
                            $store_name = $store['name'] ?? '';
                            if ($store_name === '') {
                                $store_name = sprintf(
                                    /* translators: %d is the vector store index. */
                                    __('Untitled store %d', 'gpt3-ai-content-generator'),
                                    $store_index
                                );
                            }
                            $file_count_total = $store['file_counts']['total'] ?? null;
                            $file_count_display = ($file_count_total !== null) ? " ({$file_count_total} " . _n('File', 'Files', (int) $file_count_total, 'gpt3-ai-content-generator') . ")" : ' (Files: N/A)';
                            $option_text = $store_name . $file_count_display;
                            echo '<option value="' . esc_attr($store_id_val) . '"' . selected(in_array($store_id_val, $openai_vector_store_ids_saved, true), true, false) . '>' . esc_html($option_text) . '</option>';
                            $store_index++;
                        }
                    }
                    $manual_index = 1;
                    foreach ($openai_vector_store_ids_saved as $saved_id) {
                        $found_in_list = false;
                        if (!empty($openai_vector_stores)) {
                            foreach ($openai_vector_stores as $store) {
                                if (($store['id'] ?? '') === $saved_id) { $found_in_list = true; break; }
                            }
                        }
                        if (!$found_in_list) {
                            $manual_label = sprintf(
                                /* translators: %d is the manual vector store index. */
                                __('Manual store %d', 'gpt3-ai-content-generator'),
                                $manual_index
                            );
                            echo '<option value="' . esc_attr($saved_id) . '" selected="selected">' . esc_html($manual_label) . '</option>';
                            $manual_index++;
                        }
                    }
                    if (empty($openai_vector_stores) && empty($openai_vector_store_ids_saved)) {
                        echo '<option value="" disabled>' . esc_html__('-- No Vector Stores Found --', 'gpt3-ai-content-generator') . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="aipkit_popover_option_row aipkit_vector_store_pinecone_field" style="<?php echo ($enable_vector_store === '1' && $vector_store_provider === 'pinecone') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_pinecone_index_name_modal"
                >
                    <?php esc_html_e('Index', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_popover_inline_controls">
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_pinecone_index_name_modal"
                        name="pinecone_index_name"
                        class="aipkit_popover_option_select"
                    >
                        <?php
                        if (!empty($pinecone_indexes)) {
                            foreach ($pinecone_indexes as $index) {
                                $index_name = is_array($index) ? ($index['name'] ?? '') : (string) $index;
                                echo '<option value="' . esc_attr($index_name) . '"' . selected($pinecone_index_name, $index_name, false) . '>' . esc_html($index_name) . '</option>';
                            }
                        }
                        if (!empty($pinecone_index_name) && (empty($pinecone_indexes) || !in_array($pinecone_index_name, array_column($pinecone_indexes, 'name')))) {
                            echo '<option value="' . esc_attr($pinecone_index_name) . '" selected="selected">' . esc_html($pinecone_index_name) . ' (Manual)</option>';
                        }
                        if (empty($pinecone_indexes) && empty($pinecone_index_name)) {
                            echo '<option value="" disabled>' . esc_html__('-- No Indexes Found --', 'gpt3-ai-content-generator') . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="aipkit_popover_option_row aipkit_vector_store_pinecone_key_field" style="<?php echo ($enable_vector_store === '1' && $vector_store_provider === 'pinecone') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_pinecone_api_key_modal"
                >
                    <?php esc_html_e('API key', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                    <input
                        type="password"
                        id="aipkit_pinecone_api_key_modal"
                        name="pinecone_api_key"
                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--vector-wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($pinecone_api_key); ?>"
                        autocomplete="new-password"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                    />
                    <span class="aipkit_api-key-toggle">
                        <span class="dashicons dashicons-visibility"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="aipkit_popover_option_row aipkit_vector_store_qdrant_url_field" style="<?php echo ($enable_vector_store === '1' && $vector_store_provider === 'qdrant') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_qdrant_url_modal"
                >
                    <?php esc_html_e('URL', 'gpt3-ai-content-generator'); ?>
                </label>
                <input
                    type="url"
                    id="aipkit_qdrant_url_modal"
                    name="qdrant_url"
                    class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--vector-wide aipkit_popover_option_input--framed"
                    placeholder="<?php esc_attr_e('e.g., http://localhost:6333', 'gpt3-ai-content-generator'); ?>"
                    value="<?php echo esc_attr($qdrant_url); ?>"
                />
            </div>
        </div>
        <div class="aipkit_popover_option_row aipkit_vector_store_qdrant_key_field" style="<?php echo ($enable_vector_store === '1' && $vector_store_provider === 'qdrant') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_qdrant_api_key_modal"
                >
                    <?php esc_html_e('API key', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                    <input
                        type="password"
                        id="aipkit_qdrant_api_key_modal"
                        name="qdrant_api_key"
                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--vector-wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($qdrant_api_key); ?>"
                        autocomplete="new-password"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                    />
                    <span class="aipkit_api-key-toggle">
                        <span class="dashicons dashicons-visibility"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="aipkit_popover_option_row aipkit_vector_store_qdrant_field" style="<?php echo ($enable_vector_store === '1' && $vector_store_provider === 'qdrant') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_qdrant_collection_names_modal"
                    data-tooltip="<?php echo esc_attr__('Select one or more collections.', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Collections', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_popover_option_actions">
                    <div
                        class="aipkit_popover_multiselect"
                        data-aipkit-qdrant-collections-dropdown
                        data-placeholder="<?php echo esc_attr__('Select collections', 'gpt3-ai-content-generator'); ?>"
                        data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                    >
                        <button
                            type="button"
                            class="aipkit_popover_multiselect_btn"
                            aria-expanded="false"
                            aria-controls="aipkit_bot_<?php echo esc_attr($bot_id); ?>_qdrant_collections_panel"
                        >
                            <span class="aipkit_popover_multiselect_label">
                                <?php esc_html_e('Select collections', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </button>
                        <div
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_qdrant_collections_panel"
                            class="aipkit_popover_multiselect_panel"
                            role="menu"
                            hidden
                        >
                            <div class="aipkit_popover_multiselect_options"></div>
                        </div>
                    </div>
                </div>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_qdrant_collection_names_modal"
                    name="qdrant_collection_names[]"
                    class="aipkit_popover_multiselect_select"
                    multiple
                    size="3"
                    hidden
                    aria-hidden="true"
                    tabindex="-1"
                >
                    <?php
                    if (!empty($qdrant_collections)) {
                        foreach ($qdrant_collections as $collection) {
                            $collection_name = is_array($collection) ? ($collection['name'] ?? '') : (string) $collection;
                            echo '<option value="' . esc_attr($collection_name) . '"' . selected(in_array($collection_name, $qdrant_collection_names, true), true, false) . '>' . esc_html($collection_name) . '</option>';
                        }
                    }
                    foreach ($qdrant_collection_names as $saved_name) {
                        if (!in_array($saved_name, array_map(function ($c) { return is_array($c) ? ($c['name'] ?? '') : (string) $c; }, $qdrant_collections), true)) {
                            echo '<option value="' . esc_attr($saved_name) . '" selected="selected">' . esc_html($saved_name) . ' (Manual)</option>';
                        }
                    }
                    if (empty($qdrant_collections) && empty($qdrant_collection_names)) {
                        echo '<option value="" disabled>' . esc_html__('-- No Collections Found --', 'gpt3-ai-content-generator') . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>

        <div
            class="aipkit_popover_option_row aipkit_vector_store_embedding_config_row"
            style="<?php echo ($enable_vector_store === '1' && in_array($vector_store_provider, ['pinecone', 'qdrant'], true)) ? '' : 'display:none;'; ?>"
        >
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_select_modal"
                >
                    <?php esc_html_e('Embedding', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_popover_inline_controls">
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_select_modal"
                        class="aipkit_popover_option_select aipkit_vector_embedding_select"
                    >
                        <?php
                        $embedding_groups = [
                            'openai' => [
                                'label' => esc_html__('OpenAI', 'gpt3-ai-content-generator'),
                                'models' => $openai_embedding_models,
                            ],
                            'google' => [
                                'label' => esc_html__('Google', 'gpt3-ai-content-generator'),
                                'models' => $google_embedding_models,
                            ],
                            'azure' => [
                                'label' => esc_html__('Azure', 'gpt3-ai-content-generator'),
                                'models' => $azure_embedding_models,
                            ],
                            'openrouter' => [
                                'label' => esc_html__('OpenRouter', 'gpt3-ai-content-generator'),
                                'models' => $openrouter_embedding_models,
                            ],
                        ];
                        $embedding_model_map = [];
                        foreach ($embedding_groups as $group_key => $group_data) {
                            foreach ($group_data['models'] as $model) {
                                $model_id_val = $model['id'] ?? '';
                                if ($model_id_val !== '') {
                                    $embedding_model_map[$model_id_val] = $group_key;
                                }
                            }
                        }
                        $selected_embedding_value = '';
                        if (!empty($vector_embedding_provider) && !empty($vector_embedding_model)) {
                            $selected_embedding_value = $vector_embedding_provider . '::' . $vector_embedding_model;
                        }
                        echo '<option value="">' . esc_html__('-- Select Embedding --', 'gpt3-ai-content-generator') . '</option>';
                        $manual_included = false;
                        foreach ($embedding_groups as $group_key => $group_data) {
                            echo '<optgroup label="' . esc_attr($group_data['label']) . '">';
                            foreach ($group_data['models'] as $model) {
                                $model_id_val = $model['id'] ?? '';
                                if ($model_id_val === '') {
                                    continue;
                                }
                                $model_name_val = $model['name'] ?? $model_id_val;
                                $option_value = $group_key . '::' . $model_id_val;
                                $is_selected = selected($selected_embedding_value, $option_value, false);
                                echo '<option value="' . esc_attr($option_value) . '" data-provider="' . esc_attr($group_key) . '" ' . $is_selected . '>' . esc_html($model_name_val) . '</option>';
                            }
                            if (!$manual_included && !empty($vector_embedding_model) && $vector_embedding_provider === $group_key && !isset($embedding_model_map[$vector_embedding_model])) {
                                $manual_value = $vector_embedding_provider . '::' . $vector_embedding_model;
                                echo '<option value="' . esc_attr($manual_value) . '" data-provider="' . esc_attr($vector_embedding_provider) . '" selected="selected">' . esc_html($vector_embedding_model) . ' (Manual)</option>';
                                $manual_included = true;
                            }
                            echo '</optgroup>';
                        }
                        if (!$manual_included && !empty($vector_embedding_model) && !isset($embedding_model_map[$vector_embedding_model])) {
                            $manual_provider = $vector_embedding_provider ?: 'manual';
                            $manual_value = $manual_provider . '::' . $vector_embedding_model;
                            echo '<optgroup label="' . esc_attr__('Manual', 'gpt3-ai-content-generator') . '">';
                            echo '<option value="' . esc_attr($manual_value) . '" data-provider="' . esc_attr($manual_provider) . '" selected="selected">' . esc_html($vector_embedding_model) . ' (Manual)</option>';
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_provider_modal"
                        name="vector_embedding_provider"
                        class="aipkit_popover_option_select aipkit_vector_embedding_provider_select aipkit_hidden"
                        aria-hidden="true"
                        tabindex="-1"
                    >
                        <option value="openai" <?php selected($vector_embedding_provider, 'openai'); ?>>OpenAI</option>
                        <option value="google" <?php selected($vector_embedding_provider, 'google'); ?>>Google</option>
                        <option value="azure" <?php selected($vector_embedding_provider, 'azure'); ?>>Azure</option>
                        <option value="openrouter" <?php selected($vector_embedding_provider, 'openrouter'); ?>>OpenRouter</option>
                    </select>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_embedding_model_modal"
                        name="vector_embedding_model"
                        class="aipkit_popover_option_select aipkit_vector_embedding_model_select aipkit_hidden"
                        aria-hidden="true"
                        tabindex="-1"
                    >
                        <option value=""><?php esc_html_e('-- Select Model --', 'gpt3-ai-content-generator'); ?></option>
                        <?php
                        $current_embedding_list = [];
                        if ($vector_embedding_provider === 'openai') {
                            $current_embedding_list = $openai_embedding_models;
                        } elseif ($vector_embedding_provider === 'google') {
                            $current_embedding_list = $google_embedding_models;
                        } elseif ($vector_embedding_provider === 'azure') {
                            $current_embedding_list = $azure_embedding_models;
                        } elseif ($vector_embedding_provider === 'openrouter') {
                            $current_embedding_list = $openrouter_embedding_models;
                        }
                        if (!empty($current_embedding_list)) {
                            foreach ($current_embedding_list as $model) {
                                $model_id_val = $model['id'] ?? '';
                                $model_name_val = $model['name'] ?? $model_id_val;
                                echo '<option value="' . esc_attr($model_id_val) . '" ' . selected($vector_embedding_model, $model_id_val, false) . '>' . esc_html($model_name_val) . '</option>';
                            }
                        }
                        if (!empty($vector_embedding_model) && (empty($current_embedding_list) || !in_array($vector_embedding_model, array_column($current_embedding_list, 'id'), true))) {
                            echo '<option value="' . esc_attr($vector_embedding_model) . '" selected="selected">' . esc_html($vector_embedding_model) . ' (Manual)</option>';
                        }
                        if (empty($current_embedding_list) && empty($vector_embedding_model)) {
                            echo '<option value="" disabled>' . esc_html__('-- Select Provider --', 'gpt3-ai-content-generator') . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="aipkit_popover_option_row aipkit_vector_store_top_k_field" style="<?php echo ($enable_vector_store === '1' && in_array($vector_store_provider, ['openai', 'pinecone', 'qdrant'], true)) ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_top_k_modal"
                    data-tooltip="<?php echo esc_attr__('Number of results to retrieve from vector store.', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Limit', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_popover_param_slider">
                    <input
                        type="range"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_top_k_modal"
                        name="vector_store_top_k"
                        class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                        min="1"
                        max="20"
                        step="1"
                        value="<?php echo esc_attr($vector_store_top_k); ?>"
                    />
                    <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_top_k_modal_value" class="aipkit_popover_param_value">
                        <?php echo esc_html($vector_store_top_k); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="aipkit_popover_option_row aipkit_vector_store_confidence_field aipkit_popover_option_row--force-divider" style="<?php echo ($enable_vector_store === '1' && in_array($vector_store_provider, ['openai', 'pinecone', 'qdrant'], true)) ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_confidence_threshold_modal"
                    data-tooltip="<?php echo esc_attr__('Only use results with a similarity score above this.', 'gpt3-ai-content-generator'); ?>"
                >
                    <?php esc_html_e('Score threshold', 'gpt3-ai-content-generator'); ?>
                </label>
                <div class="aipkit_popover_param_slider">
                    <input
                        type="range"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_confidence_threshold_modal"
                        name="vector_store_confidence_threshold"
                        class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                        min="0"
                        max="100"
                        step="1"
                        value="<?php echo esc_attr($vector_store_confidence_threshold); ?>"
                        data-suffix="%"
                    />
                    <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_vector_store_confidence_threshold_modal_value" class="aipkit_popover_param_value">
                        <?php echo esc_html($vector_store_confidence_threshold); ?>%
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Use the current page content as context (limited).', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Page context', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_content_aware_enabled_popover"
                    name="content_aware_enabled"
                    class="aipkit_content_aware_enable_select"
                    value="1"
                    <?php checked($content_aware_enabled, '1'); ?>
                />
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
</div>
<div class="aipkit_popover_flyout_footer">
    <span class="aipkit_popover_flyout_footer_text">
        <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
    </span>
    <a
        class="aipkit_popover_flyout_footer_link"
        href="<?php echo esc_url('https://docs.aipower.org/docs/context'); ?>"
        target="_blank"
        rel="noopener noreferrer"
    >
        <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
    </a>
</div>
