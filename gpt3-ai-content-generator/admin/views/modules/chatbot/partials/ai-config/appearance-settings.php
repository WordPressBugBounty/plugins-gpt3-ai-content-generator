<?php
$bot_id = $initial_active_bot_id;
$bot_settings = $active_bot_settings;
$saved_footer_text = $bot_settings['footer_text'] ?? '';
$saved_placeholder = $bot_settings['input_placeholder'] ?? __('Type your message...', 'gpt3-ai-content-generator');
$custom_typing_text = $bot_settings['custom_typing_text'] ?? '';
$enable_fullscreen = $bot_settings['enable_fullscreen'] ?? '0';
$enable_download = $bot_settings['enable_download'] ?? '0';
$enable_copy_button = $bot_settings['enable_copy_button']
    ?? \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_COPY_BUTTON;
$enable_conversation_starters = $bot_settings['enable_conversation_starters']
    ?? \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_CONVERSATION_STARTERS;
$enable_conversation_sidebar = $bot_settings['enable_conversation_sidebar']
    ?? \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_CONVERSATION_SIDEBAR;
$enable_feedback = $bot_settings['enable_feedback']
    ?? \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_ENABLE_FEEDBACK;
$sidebar_disabled_tooltip = __('Sidebar is not available when Popup mode is enabled.', 'gpt3-ai-content-generator');
$status_text_disabled_tooltip = __('Status text is only applicable to Popup mode.', 'gpt3-ai-content-generator');
$is_default_bot_name_locked = !empty($is_default_active);
$default_bot_name_tooltip = __('Default bot name cannot be changed.', 'gpt3-ai-content-generator');
$active_bot_name_value = ($active_bot_post && isset($active_bot_post->post_title))
    ? (string) $active_bot_post->post_title
    : '';
?>
<div class="aipkit_popover_options_list aipkit_interface_options">
    <div class="aipkit_builder_field aipkit_builder_field--theme-row">
        <div class="aipkit_interface_theme_rows">
            <div class="aipkit_interface_theme_top_row aipkit_interface_theme_top_row--primary">
                <div class="aipkit_interface_theme_top_cell aipkit_interface_theme_top_cell--theme">
                    <?php
                    $theme_dropdown_label = __('Select theme', 'gpt3-ai-content-generator');
                    $saved_theme_key = isset($saved_theme) ? (string) $saved_theme : '';
                    if ($saved_theme_key === 'custom' && !empty($selected_theme_preset_label)) {
                        $theme_dropdown_label = (string) $selected_theme_preset_label;
                    } elseif ($saved_theme_key !== '' && isset($available_themes[$saved_theme_key])) {
                        $theme_dropdown_label = (string) $available_themes[$saved_theme_key];
                    } elseif ($saved_theme_key !== '') {
                        $theme_dropdown_label = ucwords(str_replace(['-', '_'], ' ', $saved_theme_key));
                    }
                    ?>
                    <div class="aipkit_interface_theme_label_row">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme_dropdown_btn"
                        >
                            <?php esc_html_e('Theme', 'gpt3-ai-content-generator'); ?>
                        </label>
                    </div>
                    <div
                        class="aipkit_popover_multiselect aipkit_interface_theme_dropdown"
                        data-aipkit-theme-dropdown
                        data-placeholder="<?php echo esc_attr__('Select theme', 'gpt3-ai-content-generator'); ?>"
                    >
                        <button
                            type="button"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme_dropdown_btn"
                            class="aipkit_popover_multiselect_btn"
                            aria-expanded="false"
                            aria-controls="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme_dropdown_panel"
                        >
                            <span class="aipkit_popover_multiselect_label">
                                <?php echo esc_html($theme_dropdown_label); ?>
                            </span>
                        </button>
                        <div
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme_dropdown_panel"
                            class="aipkit_popover_multiselect_panel aipkit_interface_theme_panel"
                            role="menu"
                            hidden
                        >
                            <div class="aipkit_popover_multiselect_options aipkit_interface_theme_options">
                                <?php foreach ($available_themes as $theme_key => $theme_name) : ?>
                                    <?php
                                    if ($theme_key === 'custom') {
                                        continue;
                                    }
                                    $theme_is_selected = ($saved_theme_key === (string) $theme_key);
                                    ?>
                                    <label class="aipkit_popover_multiselect_item aipkit_interface_theme_item">
                                        <span class="aipkit_interface_theme_item_label">
                                            <input
                                                type="radio"
                                                class="aipkit_interface_theme_radio"
                                                name="aipkit_theme_choice_<?php echo esc_attr($bot_id); ?>"
                                                value="<?php echo esc_attr($theme_key); ?>"
                                                data-theme-value="<?php echo esc_attr($theme_key); ?>"
                                                data-preset-key=""
                                                <?php checked($theme_is_selected, true); ?>
                                            />
                                            <span class="aipkit_popover_multiselect_text"><?php echo esc_html($theme_name); ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>

                                <?php if (isset($available_themes['custom']) && !empty($custom_theme_presets)) : ?>
                                    <?php foreach ($custom_theme_presets as $preset) : ?>
                                        <?php
                                        if (!is_array($preset)) {
                                            continue;
                                        }
                                        $preset_key = isset($preset['key']) ? sanitize_key((string) $preset['key']) : '';
                                        $preset_label = isset($preset['label']) ? (string) $preset['label'] : '';
                                        if ($preset_key === '' || $preset_label === '') {
                                            continue;
                                        }
                                        $preset_is_selected = ($saved_theme_key === 'custom' && $selected_theme_preset_key === $preset_key);
                                        ?>
                                        <label class="aipkit_popover_multiselect_item aipkit_interface_theme_item">
                                            <span class="aipkit_interface_theme_item_label">
                                                <input
                                                    type="radio"
                                                    class="aipkit_interface_theme_radio"
                                                    name="aipkit_theme_choice_<?php echo esc_attr($bot_id); ?>"
                                                    value="custom"
                                                    data-theme-value="custom"
                                                    data-preset-key="<?php echo esc_attr($preset_key); ?>"
                                                    <?php checked($preset_is_selected, true); ?>
                                                    <?php disabled($aipkit_hide_custom_theme); ?>
                                                />
                                                <span class="aipkit_popover_multiselect_text"><?php echo esc_html($preset_label); ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (isset($available_themes['custom'])) : ?>
                                    <?php $custom_theme_selected = ($saved_theme_key === 'custom' && $selected_theme_preset_key === ''); ?>
                                    <div class="aipkit_popover_multiselect_item aipkit_interface_theme_item aipkit_interface_theme_item--custom">
                                        <label class="aipkit_interface_theme_item_label">
                                            <input
                                                type="radio"
                                                class="aipkit_interface_theme_radio"
                                                name="aipkit_theme_choice_<?php echo esc_attr($bot_id); ?>"
                                                value="custom"
                                                data-theme-value="custom"
                                                data-preset-key=""
                                                <?php checked($custom_theme_selected, true); ?>
                                                <?php disabled($aipkit_hide_custom_theme); ?>
                                            />
                                            <span class="aipkit_popover_multiselect_text"><?php echo esc_html($available_themes['custom']); ?></span>
                                        </label>
                                        <button
                                            type="button"
                                            class="aipkit_popover_option_btn aipkit_theme_config_btn aipkit_theme_config_btn--inline"
                                            aria-expanded="false"
                                            aria-controls="aipkit_custom_theme_flyout"
                                            data-aipkit-theme-custom-edit
                                            <?php echo $aipkit_hide_custom_theme ? 'hidden' : ''; ?>
                                            <?php disabled($aipkit_hide_custom_theme); ?>
                                        >
                                            <?php esc_html_e('Edit', 'gpt3-ai-content-generator'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme"
                            name="theme"
                            class="aipkit_popover_option_select aipkit_theme_hidden_select"
                        >
                            <?php foreach ($available_themes as $theme_key => $theme_name) : ?>
                                <?php
                                if ($theme_key === 'custom') {
                                    continue;
                                }
                                $theme_is_selected = ((string) $saved_theme === (string) $theme_key);
                                ?>
                                <option
                                    value="<?php echo esc_attr($theme_key); ?>"
                                    <?php selected($theme_is_selected, true); ?>
                                >
                                    <?php echo esc_html($theme_name); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (isset($available_themes['custom']) && !empty($custom_theme_presets)) : ?>
                                <?php foreach ($custom_theme_presets as $preset) : ?>
                                    <?php
                                    if (!is_array($preset)) {
                                        continue;
                                    }
                                    $preset_key = isset($preset['key']) ? sanitize_key((string) $preset['key']) : '';
                                    $preset_label = isset($preset['label']) ? (string) $preset['label'] : '';
                                    $preset_primary = isset($preset['primary']) ? (string) $preset['primary'] : '';
                                    $preset_secondary = isset($preset['secondary']) ? (string) $preset['secondary'] : '';
                                    if ($preset_key === '' || $preset_label === '') {
                                        continue;
                                    }
                                    $preset_is_selected = ($saved_theme === 'custom' && $selected_theme_preset_key === $preset_key);
                                    ?>
                                    <option
                                        value="custom"
                                        data-preset-key="<?php echo esc_attr($preset_key); ?>"
                                        data-primary="<?php echo esc_attr($preset_primary); ?>"
                                        data-secondary="<?php echo esc_attr($preset_secondary); ?>"
                                        <?php selected($preset_is_selected, true); ?>
                                        <?php disabled($aipkit_hide_custom_theme); ?>
                                    >
                                        <?php echo esc_html($preset_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (isset($available_themes['custom'])) : ?>
                                <?php $custom_theme_selected = ($saved_theme === 'custom' && $selected_theme_preset_key === ''); ?>
                                <option
                                    value="custom"
                                    <?php selected($custom_theme_selected, true); ?>
                                    <?php disabled($aipkit_hide_custom_theme); ?>
                                >
                                    <?php echo esc_html($available_themes['custom']); ?>
                                </option>
                            <?php endif; ?>
                        </select>
                        <input
                            type="hidden"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme_preset_key"
                            name="theme_preset_key"
                            value="<?php echo esc_attr($selected_theme_preset_key); ?>"
                        />
                    </div>
                </div>
                <div class="aipkit_interface_theme_top_cell aipkit_interface_theme_top_cell--popup">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_enabled_appearance"
                    >
                        <?php esc_html_e('Popup', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_enabled_appearance"
                        class="aipkit_popover_option_select"
                        data-aipkit-appearance-popup-select
                    >
                        <option value="1" <?php selected($popup_enabled, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                        <option value="0" <?php selected($popup_enabled, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
                <div
                    class="aipkit_interface_theme_top_cell aipkit_interface_theme_top_cell--scope aipkit_interface_popup_scope_row"
                    data-aipkit-popup-scope-cell
                    <?php echo ($popup_enabled === '1') ? '' : 'hidden'; ?>
                >
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_scope_select_appearance"
                    >
                        <?php esc_html_e('Site-Wide', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_scope_select_appearance"
                        name="aipkit_deploy_popup_scope"
                        class="aipkit_popover_option_select"
                    >
                        <option value="sitewide" <?php selected($deploy_popup_scope, 'sitewide'); ?>>
                            <?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?>
                        </option>
                        <option value="page" <?php selected($deploy_popup_scope, 'page'); ?>>
                            <?php esc_html_e('No', 'gpt3-ai-content-generator'); ?>
                        </option>
                    </select>
                </div>
                <div
                    class="aipkit_interface_theme_top_cell aipkit_interface_theme_top_cell--launcher"
                    data-aipkit-popup-launcher-cell
                    <?php echo ($popup_enabled === '1') ? '' : 'hidden'; ?>
                >
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_launcher_select_appearance"
                    >
                        <?php esc_html_e('Launcher', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div
                        class="aipkit_popover_multiselect aipkit_popup_launcher_multiselect"
                        data-aipkit-popup-launcher-dropdown
                        data-placeholder="<?php echo esc_attr__('Position, style, size', 'gpt3-ai-content-generator'); ?>"
                    >
                        <button
                            type="button"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_launcher_select_appearance"
                            class="aipkit_popover_multiselect_btn"
                            aria-expanded="false"
                            aria-controls="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_launcher_panel_appearance"
                        >
                            <span class="aipkit_popover_multiselect_label">
                                <?php esc_html_e('Position, style, size', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </button>
                        <div
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_launcher_panel_appearance"
                            class="aipkit_popover_multiselect_panel aipkit_popup_launcher_panel"
                            role="menu"
                            hidden
                        >
                            <div class="aipkit_popover_multiselect_options aipkit_popup_launcher_options">
                                <div class="aipkit_popup_launcher_group">
                                    <p class="aipkit_popup_launcher_group_title"><?php esc_html_e('Position', 'gpt3-ai-content-generator'); ?></p>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_position_choice" data-target-name="popup_position" value="bottom-right" <?php checked($popup_position, 'bottom-right'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Bottom Right', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_position_choice" data-target-name="popup_position" value="bottom-left" <?php checked($popup_position, 'bottom-left'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Bottom Left', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_position_choice" data-target-name="popup_position" value="top-right" <?php checked($popup_position, 'top-right'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Top Right', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_position_choice" data-target-name="popup_position" value="top-left" <?php checked($popup_position, 'top-left'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Top Left', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                </div>
                                <div class="aipkit_popup_launcher_group">
                                    <p class="aipkit_popup_launcher_group_title"><?php esc_html_e('Style', 'gpt3-ai-content-generator'); ?></p>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_style_choice" data-target-name="popup_icon_style" value="circle" <?php checked($popup_icon_style, 'circle'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Circle', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_style_choice" data-target-name="popup_icon_style" value="square" <?php checked($popup_icon_style, 'square'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_style_choice" data-target-name="popup_icon_style" value="none" <?php checked($popup_icon_style, 'none'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                </div>
                                <div class="aipkit_popup_launcher_group">
                                    <p class="aipkit_popup_launcher_group_title"><?php esc_html_e('Size', 'gpt3-ai-content-generator'); ?></p>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_size_choice" data-target-name="popup_icon_size" value="small" <?php checked($popup_icon_size, 'small'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Small', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_size_choice" data-target-name="popup_icon_size" value="medium" <?php checked($popup_icon_size, 'medium'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_size_choice" data-target-name="popup_icon_size" value="large" <?php checked($popup_icon_size, 'large'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <label class="aipkit_popover_multiselect_item aipkit_popup_launcher_item">
                                        <input type="radio" class="aipkit_popup_launcher_radio" name="popup_launcher_size_choice" data-target-name="popup_icon_size" value="xlarge" <?php checked($popup_icon_size, 'xlarge'); ?> />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('X-Large', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <div class="aipkit_popup_launcher_behavior_group">
                                        <p class="aipkit_popup_launcher_group_title aipkit_popup_launcher_group_title--behavior">
                                            <?php esc_html_e('Behavior', 'gpt3-ai-content-generator'); ?>
                                        </p>
                                        <div class="aipkit_popover_multiselect_item aipkit_popup_launcher_item aipkit_popup_launcher_item--delay aipkit_popup_launcher_delay_field">
                                            <label
                                                class="aipkit_popup_launcher_delay_label"
                                                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_delay_launcher"
                                            >
                                                <?php esc_html_e('Delay (s)', 'gpt3-ai-content-generator'); ?>
                                            </label>
                                            <input
                                                type="number"
                                                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_delay_launcher"
                                                name="popup_delay"
                                                class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popup_launcher_delay_input"
                                                value="<?php echo esc_attr($popup_delay); ?>"
                                                min="0"
                                                step="1"
                                            />
                                        </div>
                                        <div class="aipkit_popover_multiselect_item aipkit_popup_launcher_item aipkit_popup_launcher_item--hint">
                                            <label class="aipkit_popup_launcher_item_main" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_enabled_launcher">
                                                <input
                                                    type="checkbox"
                                                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_label_enabled_launcher"
                                                    name="popup_label_enabled"
                                                    class="aipkit_popup_hint_toggle_switch"
                                                    value="1"
                                                    <?php checked($popup_label_enabled, '1'); ?>
                                                />
                                                <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Hint', 'gpt3-ai-content-generator'); ?></span>
                                            </label>
                                            <button
                                                type="button"
                                                class="aipkit_popover_option_btn aipkit_popup_hint_config_btn aipkit_popup_hint_config_btn--inline"
                                                aria-expanded="false"
                                                aria-controls="aipkit_popup_hint_flyout"
                                                <?php echo ($popup_label_enabled === '1') ? '' : 'hidden'; ?>
                                            >
                                                <?php esc_html_e('Edit', 'gpt3-ai-content-generator'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="aipkit_popup_launcher_hidden_fields" aria-hidden="true">
                        <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_position_appearance" name="popup_position" class="aipkit_popover_option_select">
                            <option value="bottom-right" <?php selected($popup_position, 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'gpt3-ai-content-generator'); ?></option>
                            <option value="bottom-left" <?php selected($popup_position, 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'gpt3-ai-content-generator'); ?></option>
                            <option value="top-right" <?php selected($popup_position, 'top-right'); ?>><?php esc_html_e('Top Right', 'gpt3-ai-content-generator'); ?></option>
                            <option value="top-left" <?php selected($popup_position, 'top-left'); ?>><?php esc_html_e('Top Left', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_style_appearance" name="popup_icon_style" class="aipkit_popover_option_select">
                            <option value="circle" <?php selected($popup_icon_style, 'circle'); ?>><?php esc_html_e('Circle', 'gpt3-ai-content-generator'); ?></option>
                            <option value="square" <?php selected($popup_icon_style, 'square'); ?>><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                            <option value="none" <?php selected($popup_icon_style, 'none'); ?>><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_size_appearance" name="popup_icon_size" class="aipkit_popover_option_select">
                            <option value="small" <?php selected($popup_icon_size, 'small'); ?>><?php esc_html_e('Small', 'gpt3-ai-content-generator'); ?></option>
                            <option value="medium" <?php selected($popup_icon_size, 'medium'); ?>><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                            <option value="large" <?php selected($popup_icon_size, 'large'); ?>><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                            <option value="xlarge" <?php selected($popup_icon_size, 'xlarge'); ?>><?php esc_html_e('X-Large', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="aipkit_interface_theme_top_row aipkit_interface_theme_top_row--identity">
                <div
                    class="aipkit_interface_theme_top_cell aipkit_interface_theme_top_cell--name aipkit_bot_name_group<?php echo $is_default_bot_name_locked ? ' aipkit-disabled-tooltip' : ''; ?>"
                    data-tooltip-disabled="<?php echo esc_attr($default_bot_name_tooltip); ?>"
                    title="<?php echo esc_attr($is_default_bot_name_locked ? $default_bot_name_tooltip : ''); ?>"
                >
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_name_popover"
                        title="<?php echo esc_attr($is_default_bot_name_locked ? $default_bot_name_tooltip : __('Chatbot name', 'gpt3-ai-content-generator')); ?>"
                    >
                        <?php esc_html_e('Bot Name', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_name_popover"
                        name="bot_name"
                        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed aipkit_bot_name_input"
                        value="<?php echo esc_attr($active_bot_name_value); ?>"
                        <?php echo $is_default_bot_name_locked ? 'disabled aria-disabled="true"' : ''; ?>
                        title="<?php echo esc_attr($is_default_bot_name_locked ? $default_bot_name_tooltip : __('Chatbot name', 'gpt3-ai-content-generator')); ?>"
                    />
                </div>
                <div class="aipkit_interface_theme_top_cell">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_greeting"
                    >
                        <?php esc_html_e('Greeting', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_greeting"
                        name="greeting"
                        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($saved_greeting); ?>"
                        placeholder="<?php esc_attr_e('Hello there!', 'gpt3-ai-content-generator'); ?>"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                    />
                </div>
                <div class="aipkit_interface_theme_top_cell">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_subgreeting"
                    >
                        <?php esc_html_e('Subgreeting', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_subgreeting"
                        name="subgreeting"
                        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($saved_subgreeting); ?>"
                        placeholder="<?php esc_attr_e('How can I help you today?', 'gpt3-ai-content-generator'); ?>"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                    />
                </div>
                <div class="aipkit_interface_theme_top_cell aipkit_interface_theme_top_cell--controls aipkit_interface_cell--controls">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_controls_select"
                    >
                        <?php esc_html_e('UI features', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div
                        class="aipkit_popover_multiselect aipkit_interface_controls_multiselect"
                        data-aipkit-interface-controls-dropdown
                        data-placeholder="<?php echo esc_attr__('Select controls', 'gpt3-ai-content-generator'); ?>"
                    >
                        <button
                            type="button"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_controls_select"
                            class="aipkit_popover_multiselect_btn"
                            aria-expanded="false"
                            aria-controls="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_controls_panel"
                        >
                            <span class="aipkit_popover_multiselect_label">
                                <?php esc_html_e('Select controls', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </button>
                        <div
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_chat_controls_panel"
                            class="aipkit_popover_multiselect_panel"
                            role="menu"
                            hidden
                        >
                            <div class="aipkit_popover_multiselect_options">
                                <label class="aipkit_popover_multiselect_item aipkit_interface_control_item">
                                    <input
                                        type="checkbox"
                                        class="aipkit_interface_control_option"
                                        value="enable_download"
                                        <?php checked($enable_download, '1'); ?>
                                    />
                                    <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Download', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                                <label class="aipkit_popover_multiselect_item aipkit_interface_control_item">
                                    <input
                                        type="checkbox"
                                        class="aipkit_interface_control_option"
                                        value="enable_copy_button"
                                        <?php checked($enable_copy_button, '1'); ?>
                                    />
                                    <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Copy', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                                <label class="aipkit_popover_multiselect_item aipkit_interface_control_item">
                                    <input
                                        type="checkbox"
                                        class="aipkit_interface_control_option"
                                        value="enable_feedback"
                                        <?php checked($enable_feedback, '1'); ?>
                                    />
                                    <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Feedback', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                                <label class="aipkit_popover_multiselect_item aipkit_interface_control_item">
                                    <input
                                        type="checkbox"
                                        class="aipkit_interface_control_option"
                                        value="enable_fullscreen"
                                        <?php checked($enable_fullscreen, '1'); ?>
                                    />
                                    <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Fullscreen', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                                <label
                                    class="aipkit_popover_multiselect_item aipkit_interface_control_item aipkit_interface_control_item--sidebar<?php echo ($popup_enabled === '1') ? ' is-disabled' : ''; ?>"
                                    data-tooltip-disabled="<?php echo esc_attr($sidebar_disabled_tooltip); ?>"
                                    title="<?php echo esc_attr(($popup_enabled === '1') ? $sidebar_disabled_tooltip : ''); ?>"
                                >
                                    <input
                                        type="checkbox"
                                        class="aipkit_interface_control_option aipkit_interface_control_option--sidebar"
                                        value="enable_conversation_sidebar"
                                        <?php checked($enable_conversation_sidebar, '1'); ?>
                                        <?php disabled($popup_enabled === '1'); ?>
                                    />
                                    <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Sidebar', 'gpt3-ai-content-generator'); ?></span>
                                </label>
                                <div class="aipkit_popover_multiselect_item aipkit_interface_control_item aipkit_interface_control_item--starters">
                                    <label class="aipkit_interface_control_item_main">
                                        <input
                                            type="checkbox"
                                            class="aipkit_interface_control_option"
                                            value="enable_conversation_starters"
                                            <?php checked($enable_conversation_starters, '1'); ?>
                                        />
                                        <span class="aipkit_popover_multiselect_text"><?php esc_html_e('Starters', 'gpt3-ai-content-generator'); ?></span>
                                    </label>
                                    <button
                                        type="button"
                                        class="aipkit_popover_option_btn aipkit_starters_config_btn aipkit_starters_config_btn--inline"
                                        data-feature="conversation_starters"
                                        aria-expanded="false"
                                        aria-controls="aipkit_starters_flyout"
                                        <?php echo ((string) $enable_conversation_starters === '1') ? '' : 'hidden'; ?>
                                    >
                                        <?php esc_html_e('Edit', 'gpt3-ai-content-generator'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="aipkit_interface_control_hidden_fields aipkit_sidebar_toggle_group"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_sidebar_group"
                        data-tooltip-disabled="<?php echo esc_attr($sidebar_disabled_tooltip); ?>"
                        aria-hidden="true"
                    >
                        <span class="aipkit_interface_toggle_label screen-reader-text">
                            <?php esc_html_e('Sidebar', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_download"
                            name="enable_download"
                            class="aipkit_form-input aipkit_popover_option_select aipkit_toggle_switch_select aipkit_interface_control_hidden_select"
                        >
                            <option value="1" <?php selected($enable_download, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                            <option value="0" <?php selected($enable_download, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_copy_button"
                            name="enable_copy_button"
                            class="aipkit_form-input aipkit_popover_option_select aipkit_toggle_switch_select aipkit_interface_control_hidden_select"
                        >
                            <option value="1" <?php selected($enable_copy_button, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                            <option value="0" <?php selected($enable_copy_button, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_feedback"
                            name="enable_feedback"
                            class="aipkit_form-input aipkit_popover_option_select aipkit_toggle_switch_select aipkit_interface_control_hidden_select"
                        >
                            <option value="1" <?php selected($enable_feedback, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                            <option value="0" <?php selected($enable_feedback, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_fullscreen"
                            name="enable_fullscreen"
                            class="aipkit_form-input aipkit_popover_option_select aipkit_toggle_switch_select aipkit_interface_control_hidden_select"
                        >
                            <option value="1" <?php selected($enable_fullscreen, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                            <option value="0" <?php selected($enable_fullscreen, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_sidebar"
                            name="enable_conversation_sidebar"
                            class="aipkit_form-input aipkit_popover_option_select aipkit_toggle_switch_select aipkit_sidebar_toggle_switch aipkit_interface_control_hidden_select"
                            <?php disabled($popup_enabled === '1'); ?>
                        >
                            <option value="1" <?php selected($enable_conversation_sidebar, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                            <option value="0" <?php selected($enable_conversation_sidebar, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <select
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_starters"
                            name="enable_conversation_starters"
                            class="aipkit_form-input aipkit_popover_option_select aipkit_toggle_switch_select aipkit_starters_toggle_switch aipkit_interface_control_hidden_select"
                        >
                            <option value="1" <?php selected($enable_conversation_starters, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                            <option value="0" <?php selected($enable_conversation_starters, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="aipkit_interface_section aipkit_interface_section--text">
        <div class="aipkit_interface_grid aipkit_interface_grid--text">
            <div class="aipkit_popover_option_row aipkit_interface_cell aipkit_interface_cell--text">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_input_placeholder"
                    >
                        <?php esc_html_e('Placeholder', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_input_placeholder"
                        name="input_placeholder"
                        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($saved_placeholder); ?>"
                        placeholder="<?php esc_attr_e('Type your message...', 'gpt3-ai-content-generator'); ?>"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                    />
                </div>
            </div>
            <div class="aipkit_popover_option_row aipkit_interface_cell aipkit_interface_cell--text">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_footer_text"
                    >
                        <?php esc_html_e('Footer', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_footer_text"
                        name="footer_text"
                        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($saved_footer_text); ?>"
                        placeholder="<?php esc_attr_e('Powered by AI', 'gpt3-ai-content-generator'); ?>"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                    />
                </div>
            </div>
            <div class="aipkit_popover_option_row aipkit_interface_cell aipkit_interface_cell--text">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_custom_typing_text"
                    >
                        <?php esc_html_e('Typing text', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_custom_typing_text"
                        name="custom_typing_text"
                        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($custom_typing_text); ?>"
                        placeholder="<?php esc_attr_e('Thinking', 'gpt3-ai-content-generator'); ?>"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                    />
                </div>
            </div>
            <div
                class="aipkit_popover_option_row aipkit_interface_cell aipkit_interface_cell--text aipkit_popup_status_text_group<?php echo ($popup_enabled !== '1') ? ' aipkit-disabled-tooltip' : ''; ?>"
                data-tooltip-disabled="<?php echo esc_attr($status_text_disabled_tooltip); ?>"
                title="<?php echo esc_attr($popup_enabled === '1' ? '' : $status_text_disabled_tooltip); ?>"
            >
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_header_online_text"
                        title="<?php echo esc_attr($popup_enabled === '1' ? '' : $status_text_disabled_tooltip); ?>"
                    >
                        <?php esc_html_e('Status text', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_header_online_text"
                        name="header_online_text"
                        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                        value="<?php echo esc_attr($saved_header_online_text); ?>"
                        placeholder="<?php esc_attr_e('Online', 'gpt3-ai-content-generator'); ?>"
                        autocomplete="off"
                        data-lpignore="true"
                        data-1p-ignore="true"
                        data-form-type="other"
                        <?php disabled($popup_enabled !== '1'); ?>
                        title="<?php echo esc_attr($popup_enabled === '1' ? '' : $status_text_disabled_tooltip); ?>"
                    />
                </div>
            </div>
        </div>
    </div>
    <div
        class="aipkit_interface_section aipkit_interface_section--popup-inline"
        data-aipkit-popup-options
        data-aipkit-popup-options-inline
        <?php echo ($popup_enabled === '1') ? '' : 'hidden'; ?>
    >
        <div class="aipkit_accordion_body" data-aipkit-settings-panel="popup">
            <?php include __DIR__ . '/popup-settings.php'; ?>
        </div>
    </div>
</div>
