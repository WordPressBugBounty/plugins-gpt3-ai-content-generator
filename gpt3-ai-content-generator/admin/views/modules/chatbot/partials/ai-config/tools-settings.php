<?php
$bot_id = $initial_active_bot_id;
?>
<div class="aipkit_popover_options_list">
    <div class="aipkit_popover_option_row aipkit_popover_option_row--file-upload<?php echo $can_enable_file_upload ? '' : ' aipkit_popover_option_row--disabled'; ?>">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr($file_upload_tooltip); ?>"
            >
                <?php esc_html_e('File upload', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <?php if (!$is_pro) : ?>
                    <a
                        class="aipkit_popover_upgrade_link"
                        href="<?php echo esc_url(admin_url('admin.php?page=wpaicg-pricing')); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?>
                    </a>
                <?php else : ?>
                    <label class="aipkit_switch">
                        <input
                            type="checkbox"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_file_upload_popover"
                            name="enable_file_upload"
                            class="aipkit_file_upload_toggle_select aipkit_file_upload_toggle_switch"
                            value="1"
                            data-is-pro-plan="<?php echo esc_attr($is_pro_plan ? 'true' : 'false'); ?>"
                            data-tooltip-default="<?php echo esc_attr($file_upload_tooltip_default); ?>"
                            data-tooltip-upgrade="<?php echo esc_attr($file_upload_tooltip_upgrade); ?>"
                            <?php checked($file_upload_toggle_value, '1'); ?>
                            <?php disabled(!$can_enable_file_upload); ?>
                        />
                        <span class="aipkit_switch_slider"></span>
                    </label>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="aipkit_popover_option_row aipkit_web_search_toggle_openai" style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Let the assistant browse the web.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_enabled_popover"
                        name="openai_web_search_enabled"
                        class="aipkit_openai_web_search_enable_toggle"
                        value="1"
                        <?php checked($openai_web_search_enabled_val, '1'); ?>
                    />
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_popover_option_btn aipkit_web_search_config_btn"
                    data-web-provider="openai"
                    style="<?php echo ($openai_web_search_enabled_val === '1') ? '' : 'display:none;'; ?>"
                >
                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="aipkit_popover_option_row aipkit_web_search_toggle_google" style="<?php echo ($current_provider_for_this_bot === 'Google') ? '' : 'display:none;'; ?>">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Let the assistant browse the web.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_search_grounding_enabled_popover"
                        name="google_search_grounding_enabled"
                        class="aipkit_google_search_grounding_enable_toggle"
                        value="1"
                        <?php checked($google_search_grounding_enabled_val, '1'); ?>
                    />
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_popover_option_btn aipkit_web_search_config_btn"
                    data-web-provider="google"
                    style="<?php echo ($google_search_grounding_enabled_val === '1') ? '' : 'display:none;'; ?>"
                >
                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="aipkit_popover_option_row aipkit_web_search_toggle_claude" style="<?php echo ($current_provider_for_this_bot === 'Claude') ? '' : 'display:none;'; ?>">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Let the assistant browse the web.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Web search', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_claude_web_search_enabled_popover"
                        name="claude_web_search_enabled"
                        class="aipkit_claude_web_search_enable_toggle"
                        value="1"
                        <?php checked($claude_web_search_enabled_val, '1'); ?>
                    />
                    <span class="aipkit_switch_slider"></span>
                </label>
                <button
                    type="button"
                    class="aipkit_popover_option_btn aipkit_web_search_config_btn"
                    data-web-provider="claude"
                    style="<?php echo ($claude_web_search_enabled_val === '1') ? '' : 'display:none;'; ?>"
                >
                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Configure image uploads, models, and triggers.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Image', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <button
                    type="button"
                    class="aipkit_popover_option_btn aipkit_image_settings_config_btn"
                    aria-expanded="false"
                >
                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span
                class="aipkit_popover_option_label"
                tabindex="0"
                data-tooltip="<?php echo esc_attr__('Configure speech, playback, and realtime audio settings.', 'gpt3-ai-content-generator'); ?>"
            >
                <?php esc_html_e('Audio', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <button
                    type="button"
                    class="aipkit_popover_option_btn aipkit_audio_settings_config_btn"
                    aria-expanded="false"
                >
                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="aipkit_popover_flyout_footer">
    <span class="aipkit_popover_flyout_footer_text">
        <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
    </span>
    <a
        class="aipkit_popover_flyout_footer_link"
        href="<?php echo esc_url('https://docs.aipower.org/docs/image-features'); ?>"
        target="_blank"
        rel="noopener noreferrer"
    >
        <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
    </a>
</div>
