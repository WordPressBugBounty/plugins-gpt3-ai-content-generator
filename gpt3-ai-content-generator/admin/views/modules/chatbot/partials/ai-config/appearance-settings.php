<?php
$bot_id = $initial_active_bot_id;
$bot_settings = $active_bot_settings;
$saved_greeting = $bot_settings['greeting'] ?? '';
$saved_footer_text = $bot_settings['footer_text'] ?? '';
$saved_subgreeting = $bot_settings['subgreeting'] ?? '';
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
?>
<div class="aipkit_popover_options_list">
    <?php if (!$is_default_active) : ?>
        <div class="aipkit_popover_option_row aipkit_popover_option_row--name">
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_name_popover"
                >
                    <?php esc_html_e('Bot Name', 'gpt3-ai-content-generator'); ?>
                </label>
                <input
                    type="text"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_name_popover"
                    name="bot_name"
                    class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed aipkit_bot_name_input"
                    value="<?php echo esc_attr($active_bot_post->post_title); ?>"
                />
            </div>
        </div>
    <?php endif; ?>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
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
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
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
    </div>
    <div class="aipkit_popover_option_row">
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
    <div class="aipkit_popover_option_row">
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
    <div class="aipkit_popover_option_row">
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
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label">
                <?php esc_html_e('Suggested prompts', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_starters"
                        name="enable_conversation_starters"
                        class="aipkit_toggle_switch aipkit_starters_toggle_switch"
                        value="1"
                        <?php checked($enable_conversation_starters, '1'); ?>
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_popover_option_btn aipkit_starters_config_btn"
                    data-feature="conversation_starters"
                    aria-expanded="false"
                    aria-controls="aipkit_starters_flyout"
                    style="<?php echo ($enable_conversation_starters === '1') ? '' : 'display:none;'; ?>"
                >
                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label">
                <?php esc_html_e('Download', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_download"
                    name="enable_download"
                    class="aipkit_toggle_switch"
                    value="1"
                    <?php checked($enable_download, '1'); ?>
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label">
                <?php esc_html_e('Copy', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_copy_button"
                    name="enable_copy_button"
                    class="aipkit_toggle_switch"
                    value="1"
                    <?php checked($enable_copy_button, '1'); ?>
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label">
                <?php esc_html_e('Fullscreen', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_fullscreen"
                    name="enable_fullscreen"
                    class="aipkit_toggle_switch"
                    value="1"
                    <?php checked($enable_fullscreen, '1'); ?>
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label">
                <?php esc_html_e('Feedback', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_feedback"
                    name="enable_feedback"
                    class="aipkit_toggle_switch"
                    value="1"
                    <?php checked($enable_feedback, '1'); ?>
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
    <div
        class="aipkit_popover_option_row aipkit_sidebar_toggle_group"
        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_sidebar_group"
        title=""
        data-tooltip-disabled="<?php echo esc_attr($sidebar_disabled_tooltip); ?>"
    >
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label">
                <?php esc_html_e('Sidebar', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_sidebar"
                    name="enable_conversation_sidebar"
                    class="aipkit_toggle_switch aipkit_sidebar_toggle_switch"
                    value="1"
                    <?php checked($enable_conversation_sidebar, '1'); ?>
                    <?php disabled($popup_enabled === '1'); ?>
                >
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
        href="<?php echo esc_url('https://docs.aipower.org/docs/Appearance'); ?>"
        target="_blank"
        rel="noopener noreferrer"
    >
        <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
    </a>
</div>
