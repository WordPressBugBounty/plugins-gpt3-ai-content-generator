<?php
$bot_id = $initial_active_bot_id;
$current_provider_for_this_bot = isset($current_provider_for_this_bot)
    ? (string) $current_provider_for_this_bot
    : 'OpenAI';
$rt_disabled_by_plan = isset($rt_disabled_by_plan)
    ? (bool) $rt_disabled_by_plan
    : !(isset($is_pro_plan) && $is_pro_plan);
$realtime_voice_tooltip_default = __('Enable real-time voice conversation mode.', 'gpt3-ai-content-generator');
$realtime_voice_tooltip_upgrade = __('Realtime voice is a paid feature. Please upgrade.', 'gpt3-ai-content-generator');
$realtime_voice_tooltip = $rt_disabled_by_plan
    ? $realtime_voice_tooltip_upgrade
    : $realtime_voice_tooltip_default;
$realtime_voice_toggle_value = (!$rt_disabled_by_plan && ($enable_realtime_voice ?? '0') === '1')
    ? '1'
    : '0';
$stt_model_count_for_tools = (isset($openai_stt_models) && is_array($openai_stt_models))
    ? count($openai_stt_models)
    : 0;
$stt_controls_hidden_for_tools = $stt_model_count_for_tools <= 1;

$is_current_provider_web_enabled = false;
switch ($current_provider_for_this_bot) {
    case 'OpenAI':
        $is_current_provider_web_enabled = ($openai_web_search_enabled_val ?? '0') === '1';
        break;
    case 'Google':
        $is_current_provider_web_enabled = ($google_search_grounding_enabled_val ?? '0') === '1';
        break;
    case 'Claude':
        $is_current_provider_web_enabled = ($claude_web_search_enabled_val ?? '0') === '1';
        break;
    case 'OpenRouter':
        $is_current_provider_web_enabled = ($openrouter_web_search_enabled_val ?? '0') === '1';
        break;
}

$tools_master_options = [
    'file_upload'    => [
        'label'    => __('File upload', 'gpt3-ai-content-generator'),
        'enabled'  => ($file_upload_toggle_value ?? '0') === '1',
        'disabled' => !$can_enable_file_upload,
    ],
    'web_search'     => [
        'label'    => __('Web search', 'gpt3-ai-content-generator'),
        'enabled'  => $is_current_provider_web_enabled,
        'disabled' => false,
    ],
    'image_analysis' => [
        'label'    => __('Image analysis', 'gpt3-ai-content-generator'),
        'enabled'  => ($enable_image_upload ?? '0') === '1',
        'disabled' => false,
    ],
    'image_generation' => [
        'label'    => __('Image generation', 'gpt3-ai-content-generator'),
        'enabled'  => false,
        'disabled' => false,
    ],
    'speech_to_text' => [
        'label'    => __('Speech to Text', 'gpt3-ai-content-generator'),
        'enabled'  => ($enable_voice_input ?? '0') === '1',
        'disabled' => false,
    ],
    'text_to_speech' => [
        'label'    => __('Text to Speech', 'gpt3-ai-content-generator'),
        'enabled'  => ($tts_enabled ?? '0') === '1',
        'disabled' => false,
    ],
    'realtime_voice' => [
        'label'    => __('Realtime Voice', 'gpt3-ai-content-generator'),
        'enabled'  => $realtime_voice_toggle_value === '1',
        'disabled' => $rt_disabled_by_plan,
    ],
];
$tools_master_selected_labels = [];
foreach ($tools_master_options as $tools_master_option) {
    if (!empty($tools_master_option['enabled'])) {
        $tools_master_selected_labels[] = (string) $tools_master_option['label'];
    }
}
$tools_master_selected_count = count($tools_master_selected_labels);
$tools_master_dropdown_label = __('Select tools', 'gpt3-ai-content-generator');
if ($tools_master_selected_count > 2) {
    $tools_master_dropdown_label = sprintf(
        _n('%d selected', '%d selected', $tools_master_selected_count, 'gpt3-ai-content-generator'),
        $tools_master_selected_count
    );
} elseif ($tools_master_selected_count > 0) {
    $tools_master_dropdown_label = implode(', ', $tools_master_selected_labels);
}

$image_model_groups = [];
$known_image_model_ids = [];
$image_model_dropdown_label = '';
$replicate_group_trigger_rendered = false;
foreach ($available_image_models as $provider_group => $models) {
    foreach ($models as $model) {
        $model_id = isset($model['id']) ? (string) $model['id'] : '';
        $model_name = isset($model['name']) ? (string) $model['name'] : $model_id;
        if ($model_id === '' || $model_name === '') {
            continue;
        }
        if (!isset($image_model_groups[$provider_group])) {
            $image_model_groups[$provider_group] = [];
        }
        $image_model_groups[$provider_group][] = [
            'id' => $model_id,
            'name' => $model_name,
        ];
        $known_image_model_ids[] = $model_id;
        if ((string) $chat_image_model_id === $model_id) {
            $image_model_dropdown_label = $model_name;
        }
    }
}
if ($image_model_dropdown_label === '' && !empty($chat_image_model_id)) {
    $image_model_dropdown_label = sprintf(
        /* translators: %s is the current custom model id */
        __('%s', 'gpt3-ai-content-generator'),
        (string) $chat_image_model_id
    );
}
if ($image_model_dropdown_label === '') {
    $image_model_dropdown_label = __('Select model', 'gpt3-ai-content-generator');
}
?>

<div class="aipkit_tools_feature_rows">
    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_tools_feature_row--enabled-tools">
        <div class="aipkit_tools_feature_left">
            <span class="aipkit_tools_feature_label aipkit_popover_option_label">
                <?php esc_html_e('Enabled tools', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Pick tools for this bot.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <div
                class="aipkit_popover_multiselect aipkit_tools_enabled_multiselect"
                data-aipkit-tools-enabled-dropdown
                data-placeholder="<?php echo esc_attr__('Select tools', 'gpt3-ai-content-generator'); ?>"
            >
                <button
                    type="button"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_tools_enabled_select"
                    class="aipkit_popover_multiselect_btn"
                    aria-expanded="false"
                    aria-controls="aipkit_bot_<?php echo esc_attr($bot_id); ?>_tools_enabled_panel"
                >
                    <span class="aipkit_popover_multiselect_label">
                        <?php echo esc_html($tools_master_dropdown_label); ?>
                    </span>
                </button>
                <div
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_tools_enabled_panel"
                    class="aipkit_popover_multiselect_panel aipkit_tools_enabled_panel"
                    role="menu"
                    hidden
                >
                    <div class="aipkit_popover_multiselect_options">
                        <?php foreach ($tools_master_options as $tool_key => $tool_option) : ?>
                            <?php
                            $show_tools_menu_upgrade = !$is_pro_plan
                                && !empty($tool_option['disabled'])
                                && in_array($tool_key, ['file_upload', 'realtime_voice'], true);
                            ?>
                            <label
                                class="aipkit_popover_multiselect_item aipkit_tools_enabled_item<?php echo !empty($tool_option['disabled']) ? ' is-disabled' : ''; ?><?php echo $show_tools_menu_upgrade ? ' aipkit_tools_enabled_item--has-upgrade' : ''; ?>"
                                <?php if (!empty($tool_option['disabled']) && in_array($tool_key, ['file_upload', 'realtime_voice'], true)) : ?>
                                    title="<?php echo esc_attr($tool_key === 'file_upload' ? $file_upload_tooltip : $realtime_voice_tooltip); ?>"
                                <?php endif; ?>
                            >
                                <input
                                    type="checkbox"
                                    class="aipkit_tools_enabled_option"
                                    value="<?php echo esc_attr($tool_key); ?>"
                                    data-tool-key="<?php echo esc_attr($tool_key); ?>"
                                    data-static-disabled="<?php echo !empty($tool_option['disabled']) ? '1' : '0'; ?>"
                                    <?php checked(!empty($tool_option['enabled'])); ?>
                                    <?php disabled(!empty($tool_option['disabled'])); ?>
                                />
                                <span class="aipkit_popover_multiselect_text"><?php echo esc_html($tool_option['label']); ?></span>
                                <?php if ($show_tools_menu_upgrade) : ?>
                                    <a
                                        class="aipkit_tools_enabled_item_upgrade aipkit_popover_upgrade_link"
                                        href="<?php echo esc_url($pricing_url); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?>
                                    </a>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_popover_option_row--file-upload<?php echo $can_enable_file_upload ? '' : ' aipkit_popover_option_row--disabled'; ?>" data-aipkit-tool-key="file_upload">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr($file_upload_tooltip); ?>"
            >
                <?php esc_html_e('File upload', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Allow users to upload documents.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_file_upload_tools"
                name="enable_file_upload"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_file_upload_toggle_select aipkit_file_upload_toggle_switch"
                data-is-pro-plan="<?php echo esc_attr($is_pro_plan ? 'true' : 'false'); ?>"
                data-tooltip-default="<?php echo esc_attr($file_upload_tooltip_default); ?>"
                data-tooltip-upgrade="<?php echo esc_attr($file_upload_tooltip_upgrade); ?>"
                <?php disabled(!$can_enable_file_upload); ?>
            >
                <option value="1" <?php selected($file_upload_toggle_value, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($file_upload_toggle_value, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_image_analysis_popover_row" data-aipkit-tool-key="image_analysis" style="<?php echo (($current_provider_for_this_bot === 'OpenAI' || $current_provider_for_this_bot === 'Claude' || $current_provider_for_this_bot === 'OpenRouter')) ? '' : 'display:none;'; ?>">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Allow users to upload images for analysis.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Image analysis', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Let users attach images in chat.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_image_upload_tools"
                name="enable_image_upload"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_image_analysis_select aipkit_image_analysis_checkbox"
            >
                <option value="1" <?php selected($enable_image_upload, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($enable_image_upload, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_tools_feature_row--image-generation aipkit_tools_feature_row--is-hidden" data-aipkit-tool-key="image_generation">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Select the image model for this bot.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Image generation', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Generate images using chat.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right aipkit_tools_feature_right--image-generation">
            <div class="aipkit_tools_image_generation_controls">
                <div
                    class="aipkit_popover_multiselect aipkit_tools_image_model_dropdown"
                    data-aipkit-image-model-dropdown
                    data-placeholder="<?php echo esc_attr__('Select model', 'gpt3-ai-content-generator'); ?>"
                >
                    <button
                        type="button"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_image_model_id_tools_btn"
                        class="aipkit_popover_multiselect_btn"
                        aria-expanded="false"
                        aria-controls="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_image_model_id_tools_panel"
                    >
                        <span class="aipkit_popover_multiselect_label">
                            <?php echo esc_html($image_model_dropdown_label); ?>
                        </span>
                    </button>
                    <div
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_image_model_id_tools_panel"
                        class="aipkit_popover_multiselect_panel aipkit_tools_image_model_panel"
                        role="menu"
                        hidden
                    >
                        <div class="aipkit_popover_multiselect_options aipkit_tools_image_model_options">
                            <?php foreach ($image_model_groups as $provider_group => $image_model_options) : ?>
                                <div class="aipkit_tools_image_model_group">
                                    <div class="aipkit_tools_image_model_group_heading">
                                        <p class="aipkit_tools_image_model_group_title">
                                            <?php echo esc_html($provider_group); ?>
                                        </p>
                                        <?php if (!$replicate_group_trigger_rendered && stripos((string) $provider_group, 'replicate') !== false) : ?>
                                            <button
                                                type="button"
                                                class="aipkit_popover_option_btn aipkit_tools_replicate_key_trigger aipkit_tools_replicate_key_trigger--panel"
                                                data-aipkit-replicate-key-trigger
                                                aria-expanded="false"
                                                aria-controls="aipkit_chat_image_replicate_key_popover_tools_<?php echo esc_attr($bot_id); ?>"
                                            >
                                                <?php esc_html_e('Set API Key', 'gpt3-ai-content-generator'); ?>
                                            </button>
                                            <?php $replicate_group_trigger_rendered = true; ?>
                                        <?php endif; ?>
                                    </div>
                                    <?php foreach ($image_model_options as $image_model_option) : ?>
                                        <label class="aipkit_popover_multiselect_item aipkit_tools_image_model_item">
                                            <span class="aipkit_tools_image_model_item_label">
                                                <input
                                                    type="radio"
                                                    class="aipkit_tools_image_model_radio"
                                                    name="aipkit_image_model_choice_<?php echo esc_attr($bot_id); ?>"
                                                    value="<?php echo esc_attr($image_model_option['id']); ?>"
                                                    <?php checked((string) $chat_image_model_id, (string) $image_model_option['id']); ?>
                                                />
                                                <span class="aipkit_popover_multiselect_text"><?php echo esc_html($image_model_option['name']); ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (!empty($chat_image_model_id) && !in_array((string) $chat_image_model_id, $known_image_model_ids, true)) : ?>
                                <div class="aipkit_tools_image_model_group aipkit_tools_image_model_group--current">
                                    <p class="aipkit_tools_image_model_group_title">
                                        <?php esc_html_e('Current', 'gpt3-ai-content-generator'); ?>
                                    </p>
                                    <label class="aipkit_popover_multiselect_item aipkit_tools_image_model_item">
                                        <span class="aipkit_tools_image_model_item_label">
                                            <input
                                                type="radio"
                                                class="aipkit_tools_image_model_radio"
                                                name="aipkit_image_model_choice_<?php echo esc_attr($bot_id); ?>"
                                                value="<?php echo esc_attr($chat_image_model_id); ?>"
                                                checked
                                            />
                                            <span class="aipkit_popover_multiselect_text">
                                                <?php
                                                echo esc_html(
                                                    sprintf(
                                                        /* translators: %s is the current custom model id */
                                                        __('%s', 'gpt3-ai-content-generator'),
                                                        (string) $chat_image_model_id
                                                    )
                                                );
                                                ?>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <input
                    type="text"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_image_triggers_tools"
                    name="image_triggers"
                    class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                    placeholder="/image, /generate"
                    value="<?php echo esc_attr($image_triggers); ?>"
                    aria-label="<?php esc_attr_e('Image generation triggers', 'gpt3-ai-content-generator'); ?>"
                />
            </div>
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_image_model_id_tools"
                name="chat_image_model_id"
                class="aipkit_form-input aipkit_popover_option_select aipkit_tools_image_model_hidden_select"
            >
                <?php foreach ($available_image_models as $provider_group => $models) : ?>
                    <optgroup label="<?php echo esc_attr($provider_group); ?>">
                        <?php foreach ($models as $model) : ?>
                            <option value="<?php echo esc_attr($model['id']); ?>" <?php selected($chat_image_model_id, $model['id']); ?>>
                                <?php echo esc_html($model['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
                <?php if (!empty($chat_image_model_id) && !in_array((string) $chat_image_model_id, $known_image_model_ids, true)) : ?>
                    <option value="<?php echo esc_attr($chat_image_model_id); ?>" selected="selected">
                        <?php
                        echo esc_html(
                            sprintf(
                                /* translators: %s is the current custom model id */
                                __('%s', 'gpt3-ai-content-generator'),
                                (string) $chat_image_model_id
                            )
                        );
                        ?>
                    </option>
                <?php endif; ?>
            </select>
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_image_generation_visibility_tools"
                class="aipkit_form-input aipkit_popover_option_select aipkit_tools_image_generation_toggle aipkit_tools_image_model_hidden_select"
                aria-hidden="true"
                tabindex="-1"
            >
                <option value="1"><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" selected="selected"><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <div
                id="aipkit_chat_image_replicate_key_popover_tools_<?php echo esc_attr($bot_id); ?>"
                class="aipkit_tools_replicate_key_popover"
                data-aipkit-replicate-key-popover
                hidden
            >
                <div class="aipkit_tools_replicate_key_popover_header">
                    <span class="aipkit_tools_replicate_key_popover_title">
                        <?php esc_html_e('Replicate API key', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <button
                        type="button"
                        class="aipkit_tools_replicate_key_popover_close"
                        data-aipkit-replicate-key-close
                        aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                    <input
                        type="password"
                        id="aipkit_chat_image_replicate_api_key_tools"
                        name="replicate_api_key"
                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($replicate_api_key); ?>"
                        placeholder="<?php esc_attr_e('Enter your Replicate API key', 'gpt3-ai-content-generator'); ?>"
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
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_web_search_toggle_openai" data-aipkit-tool-key="web_search" style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Let the assistant browse the web.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Use online sources in responses.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_enabled_tools"
                name="openai_web_search_enabled"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_openai_web_search_enable_toggle"
            >
                <option value="1" <?php selected($openai_web_search_enabled_val, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($openai_web_search_enabled_val, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button
                type="button"
                class="aipkit_popover_option_btn aipkit_web_search_config_btn aipkit_tools_options_btn"
                data-web-provider="openai"
                aria-expanded="false"
                aria-controls="aipkit_builder_web_settings_modal"
                style="<?php echo ($openai_web_search_enabled_val === '1') ? '' : 'display:none;'; ?>"
            >
                <?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_web_search_toggle_google" data-aipkit-tool-key="web_search" style="<?php echo ($current_provider_for_this_bot === 'Google') ? '' : 'display:none;'; ?>">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Let the assistant browse the web.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Use online sources in responses.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_search_grounding_enabled_tools"
                name="google_search_grounding_enabled"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_google_search_grounding_enable_toggle"
            >
                <option value="1" <?php selected($google_search_grounding_enabled_val, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($google_search_grounding_enabled_val, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button
                type="button"
                class="aipkit_popover_option_btn aipkit_web_search_config_btn aipkit_tools_options_btn"
                data-web-provider="google"
                aria-expanded="false"
                aria-controls="aipkit_builder_web_settings_modal"
                style="<?php echo ($google_search_grounding_enabled_val === '1') ? '' : 'display:none;'; ?>"
            >
                <?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_web_search_toggle_claude" data-aipkit-tool-key="web_search" style="<?php echo ($current_provider_for_this_bot === 'Claude') ? '' : 'display:none;'; ?>">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Let the assistant browse the web.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Use online sources in responses.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_claude_web_search_enabled_tools"
                name="claude_web_search_enabled"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_claude_web_search_enable_toggle"
            >
                <option value="1" <?php selected($claude_web_search_enabled_val, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($claude_web_search_enabled_val, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button
                type="button"
                class="aipkit_popover_option_btn aipkit_web_search_config_btn aipkit_tools_options_btn"
                data-web-provider="claude"
                aria-expanded="false"
                aria-controls="aipkit_builder_web_settings_modal"
                style="<?php echo ($claude_web_search_enabled_val === '1') ? '' : 'display:none;'; ?>"
            >
                <?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_web_search_toggle_openrouter" data-aipkit-tool-key="web_search" style="<?php echo ($current_provider_for_this_bot === 'OpenRouter') ? '' : 'display:none;'; ?>">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Let the assistant browse the web. Availability depends on the selected OpenRouter model.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Use online sources in responses.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openrouter_web_search_enabled_tools"
                name="openrouter_web_search_enabled"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_openrouter_web_search_enable_toggle"
            >
                <option value="1" <?php selected($openrouter_web_search_enabled_val, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($openrouter_web_search_enabled_val, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button
                type="button"
                class="aipkit_popover_option_btn aipkit_web_search_config_btn aipkit_tools_options_btn"
                data-web-provider="openrouter"
                aria-expanded="false"
                aria-controls="aipkit_builder_web_settings_modal"
                style="<?php echo ($openrouter_web_search_enabled_val === '1') ? '' : 'display:none;'; ?>"
            >
                <?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_audio_toggle_voice_input_row" data-aipkit-tool-key="speech_to_text">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Allow users to speak using their microphone.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Speech to Text', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Capture voice input from users.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_voice_input_tools"
                name="enable_voice_input"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_voice_input_toggle_switch"
            >
                <option value="1" <?php selected($enable_voice_input, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($enable_voice_input, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button
                type="button"
                class="aipkit_popover_option_btn aipkit_audio_settings_config_btn aipkit_tools_options_btn"
                data-audio-feature="stt"
                aria-expanded="false"
                aria-controls="aipkit_builder_audio_settings_modal"
                style="<?php echo ($enable_voice_input === '1' && !$stt_controls_hidden_for_tools) ? '' : 'display:none;'; ?>"
            >
                <?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_audio_toggle_tts_row" data-aipkit-tool-key="text_to_speech">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Enable voice playback for assistant responses.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Text to Speech', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Read assistant replies aloud.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_tts_enabled_tools"
                name="tts_enabled"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_tts_toggle_switch"
            >
                <option value="1" <?php selected($tts_enabled, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($tts_enabled, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button
                type="button"
                class="aipkit_popover_option_btn aipkit_audio_settings_config_btn aipkit_tools_options_btn"
                data-audio-feature="tts"
                aria-expanded="false"
                aria-controls="aipkit_builder_audio_settings_modal"
                style="<?php echo ($tts_enabled === '1') ? '' : 'display:none;'; ?>"
            >
                <?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>

    <div class="aipkit_tools_feature_row aipkit_popover_option_row aipkit_audio_toggle_realtime_row<?php echo $rt_disabled_by_plan ? ' aipkit_popover_option_row--disabled' : ''; ?>" data-aipkit-tool-key="realtime_voice">
        <div class="aipkit_tools_feature_left">
            <span
                class="aipkit_tools_feature_label aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr($realtime_voice_tooltip); ?>"
            >
                <?php esc_html_e('Realtime', 'gpt3-ai-content-generator'); ?>
            </span>
            <p class="aipkit_tools_feature_hint"><?php esc_html_e('Use low-latency live voice mode.', 'gpt3-ai-content-generator'); ?></p>
        </div>
        <div class="aipkit_tools_feature_right">
            <select
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_realtime_voice_tools"
                name="enable_realtime_voice"
                class="aipkit_popover_option_select aipkit_tools_toggle_select aipkit_enable_realtime_voice_toggle"
                <?php echo $rt_disabled_by_plan ? 'disabled' : ''; ?>
            >
                <option value="1" <?php selected($realtime_voice_toggle_value, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($realtime_voice_toggle_value, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <button
                type="button"
                class="aipkit_popover_option_btn aipkit_audio_settings_config_btn aipkit_tools_options_btn"
                data-audio-feature="realtime"
                aria-expanded="false"
                aria-controls="aipkit_builder_audio_settings_modal"
                style="<?php echo ($realtime_voice_toggle_value === '1' && !$rt_disabled_by_plan) ? '' : 'display:none;'; ?>"
            >
                <?php esc_html_e('Options', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    </div>
</div>

<div
    id="aipkit_builder_web_settings_modal"
    class="aipkit_builder_web_settings_modal aipkit_popover_web_flyout"
    aria-hidden="true"
>
    <div
        class="aipkit-modal-content"
        role="dialog"
        aria-modal="false"
        aria-labelledby="aipkit_builder_web_settings_title"
    >
        <div class="aipkit_popover_flyout_header">
            <span class="aipkit_popover_flyout_title" id="aipkit_builder_web_settings_title">
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <button
                type="button"
                class="aipkit_popover_flyout_close aipkit_builder_web_settings_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit_popover_flyout_body aipkit_popover_web_body">
            <?php include __DIR__ . '/web-settings-flyout.php'; ?>
        </div>
    </div>
</div>

<div
    id="aipkit_builder_audio_settings_modal"
    class="aipkit_builder_audio_settings_modal aipkit_popover_audio_flyout"
    aria-hidden="true"
>
    <div
        class="aipkit-modal-content"
        role="dialog"
        aria-modal="false"
        aria-labelledby="aipkit_builder_audio_settings_title"
    >
        <div class="aipkit_popover_flyout_header">
            <div class="aipkit_popover_flyout_title_wrap">
                <span class="aipkit_popover_flyout_title" id="aipkit_builder_audio_settings_title">
                    <?php esc_html_e('Audio', 'gpt3-ai-content-generator'); ?>
                </span>
                <span class="aipkit_popover_status_inline aipkit_tts_sync_status" aria-live="polite"></span>
            </div>
            <button
                type="button"
                class="aipkit_popover_flyout_close aipkit_builder_audio_settings_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit_popover_flyout_body aipkit_popover_audio_body">
            <?php include __DIR__ . '/audio-settings-flyout.php'; ?>
        </div>
    </div>
</div>
