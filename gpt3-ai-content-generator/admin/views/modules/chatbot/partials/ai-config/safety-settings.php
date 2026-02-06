<?php
use WPAICG\Chat\Storage\BotSettingsManager;

$bot_id = $initial_active_bot_id;
$bot_settings = $active_bot_settings;
$enable_ip_anonymization = $bot_settings['enable_ip_anonymization']
    ?? BotSettingsManager::DEFAULT_ENABLE_IP_ANONYMIZATION;
$enable_ip_anonymization = in_array($enable_ip_anonymization, ['0', '1'], true)
    ? $enable_ip_anonymization
    : BotSettingsManager::DEFAULT_ENABLE_IP_ANONYMIZATION;
$enable_consent_compliance = $bot_settings['enable_consent_compliance']
    ?? BotSettingsManager::DEFAULT_ENABLE_CONSENT_COMPLIANCE;
$enable_consent_compliance = in_array($enable_consent_compliance, ['0', '1'], true)
    ? $enable_consent_compliance
    : BotSettingsManager::DEFAULT_ENABLE_CONSENT_COMPLIANCE;
$openai_moderation_enabled = $bot_settings['openai_moderation_enabled']
    ?? BotSettingsManager::DEFAULT_ENABLE_OPENAI_MODERATION;
$openai_moderation_enabled = in_array($openai_moderation_enabled, ['0', '1'], true)
    ? $openai_moderation_enabled
    : BotSettingsManager::DEFAULT_ENABLE_OPENAI_MODERATION;
$openai_moderation_message = $bot_settings['openai_moderation_message']
    ?? __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator');
$consent_title = $bot_settings['consent_title'] ?? __('Consent Required', 'gpt3-ai-content-generator');
$consent_message = $bot_settings['consent_message'] ?? __('Before starting the conversation, please agree to our Terms of Service and Privacy Policy.', 'gpt3-ai-content-generator');
$consent_button = $bot_settings['consent_button'] ?? __('I Agree', 'gpt3-ai-content-generator');
$banned_words = $bot_settings['banned_words'] ?? BotSettingsManager::DEFAULT_BANNED_WORDS;
$banned_words_message = $bot_settings['banned_words_message'] ?? BotSettingsManager::DEFAULT_BANNED_WORDS_MESSAGE;
$banned_ips = $bot_settings['banned_ips'] ?? BotSettingsManager::DEFAULT_BANNED_IPS;
$banned_ips_message = $bot_settings['banned_ips_message'] ?? BotSettingsManager::DEFAULT_BANNED_IPS_MESSAGE;
$placeholder_banned_words_message = __('Sorry, your message could not be sent as it contains prohibited words.', 'gpt3-ai-content-generator');
$placeholder_banned_ips_message = __('Access from your IP address has been blocked.', 'gpt3-ai-content-generator');
?>
<div class="aipkit_popover_options_list">
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label">
                <?php esc_html_e('IP anonymization', 'gpt3-ai-content-generator'); ?>
            </span>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_ip_anonymization"
                    name="enable_ip_anonymization"
                    class="aipkit_toggle_switch"
                    value="1"
                    <?php checked($enable_ip_anonymization, '1'); ?>
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
    </div>
    <?php if ($consent_feature_available) : ?>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <span class="aipkit_popover_option_label">
                    <?php esc_html_e('Consent notice', 'gpt3-ai-content-generator'); ?>
                </span>
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_consent_compliance"
                        name="enable_consent_compliance"
                        class="aipkit_toggle_switch"
                        value="1"
                        <?php checked($enable_consent_compliance, '1'); ?>
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
            </div>
        </div>
        <div
            class="aipkit_popover_option_row aipkit_consent_field_row"
            <?php echo ($enable_consent_compliance === '1') ? '' : 'hidden'; ?>
        >
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_consent_title"
                >
                    <?php esc_html_e('Title', 'gpt3-ai-content-generator'); ?>
                </label>
                <input
                    type="text"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_consent_title"
                    name="consent_title"
                    class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                    value="<?php echo esc_attr($consent_title); ?>"
                    placeholder="<?php esc_attr_e('Consent Required', 'gpt3-ai-content-generator'); ?>"
                    autocomplete="off"
                    data-lpignore="true"
                    data-1p-ignore="true"
                    data-form-type="other"
                />
            </div>
        </div>
        <div
            class="aipkit_popover_option_row aipkit_consent_field_row"
            <?php echo ($enable_consent_compliance === '1') ? '' : 'hidden'; ?>
        >
            <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_consent_message"
                >
                    <?php esc_html_e('Message', 'gpt3-ai-content-generator'); ?>
                </label>
                <textarea
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_consent_message"
                    name="consent_message"
                    class="aipkit_popover_option_textarea"
                    rows="4"
                ><?php echo esc_textarea($consent_message); ?></textarea>
            </div>
        </div>
        <div
            class="aipkit_popover_option_row aipkit_consent_field_row"
            <?php echo ($enable_consent_compliance === '1') ? '' : 'hidden'; ?>
        >
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_consent_button"
                >
                    <?php esc_html_e('Button label', 'gpt3-ai-content-generator'); ?>
                </label>
                <input
                    type="text"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_consent_button"
                    name="consent_button"
                    class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                    value="<?php echo esc_attr($consent_button); ?>"
                    placeholder="<?php esc_attr_e('I Agree', 'gpt3-ai-content-generator'); ?>"
                    autocomplete="off"
                    data-lpignore="true"
                    data-1p-ignore="true"
                    data-form-type="other"
                />
            </div>
        </div>
    <?php else : ?>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <span class="aipkit_popover_option_label">
                    <?php esc_html_e('Consent notice', 'gpt3-ai-content-generator'); ?>
                </span>
                <div class="aipkit_popover_option_actions">
                    <a
                        class="aipkit_popover_upgrade_link"
                        href="<?php echo esc_url($pricing_url); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($openai_moderation_available) : ?>
        <div
            class="aipkit_popover_option_row aipkit_openai_moderation_row"
            style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>"
        >
            <div class="aipkit_popover_option_main">
                <span class="aipkit_popover_option_label">
                    <?php esc_html_e('Moderation', 'gpt3-ai-content-generator'); ?>
                </span>
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_moderation_enabled"
                        name="openai_moderation_enabled"
                        class="aipkit_toggle_switch"
                        value="1"
                        <?php checked($openai_moderation_enabled, '1'); ?>
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
            </div>
        </div>
        <div
            class="aipkit_popover_option_row aipkit_openai_moderation_row aipkit_openai_moderation_field_row"
            <?php echo ($openai_moderation_enabled === '1') ? '' : 'hidden'; ?>
            style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>"
        >
            <div class="aipkit_popover_option_main">
                <label
                    class="aipkit_popover_option_label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_moderation_message"
                >
                    <?php esc_html_e('Notification message', 'gpt3-ai-content-generator'); ?>
                </label>
                <input
                    type="text"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_moderation_message"
                    name="openai_moderation_message"
                    class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                    value="<?php echo esc_attr($openai_moderation_message); ?>"
                    placeholder="<?php esc_attr_e('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator'); ?>"
                    autocomplete="off"
                    data-lpignore="true"
                    data-1p-ignore="true"
                    data-form-type="other"
                />
            </div>
        </div>
    <?php else : ?>
        <div
            class="aipkit_popover_option_row aipkit_openai_moderation_row"
            style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>"
        >
            <div class="aipkit_popover_option_main">
                <span class="aipkit_popover_option_label">
                    <?php esc_html_e('Moderation', 'gpt3-ai-content-generator'); ?>
                </span>
                <div class="aipkit_popover_option_actions">
                    <a
                        class="aipkit_popover_upgrade_link"
                        href="<?php echo esc_url($pricing_url); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
            <label
                class="aipkit_popover_option_label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_words"
            >
                <?php esc_html_e('Banned words (comma-separated)', 'gpt3-ai-content-generator'); ?>
            </label>
            <textarea
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_words"
                name="banned_words"
                class="aipkit_popover_option_textarea"
                rows="3"
                placeholder="<?php esc_attr_e('e.g., word1, another word, specific phrase', 'gpt3-ai-content-generator'); ?>"
            ><?php echo esc_textarea($banned_words); ?></textarea>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <label
                class="aipkit_popover_option_label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_words_message"
            >
                <?php esc_html_e('Banned words message', 'gpt3-ai-content-generator'); ?>
            </label>
            <input
                type="text"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_words_message"
                name="banned_words_message"
                class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                value="<?php echo esc_attr($banned_words_message); ?>"
                placeholder="<?php echo esc_attr($placeholder_banned_words_message); ?>"
                autocomplete="off"
                data-lpignore="true"
                data-1p-ignore="true"
                data-form-type="other"
            />
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
            <label
                class="aipkit_popover_option_label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_ips"
            >
                <?php esc_html_e('Banned IPs (comma-separated)', 'gpt3-ai-content-generator'); ?>
            </label>
            <textarea
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_ips"
                name="banned_ips"
                class="aipkit_popover_option_textarea"
                rows="3"
                placeholder="<?php esc_attr_e('e.g., 123.123.123.123, 111.222.333.444', 'gpt3-ai-content-generator'); ?>"
            ><?php echo esc_textarea($banned_ips); ?></textarea>
        </div>
    </div>
    <div class="aipkit_popover_option_row">
        <div class="aipkit_popover_option_main">
            <label
                class="aipkit_popover_option_label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_ips_message"
            >
                <?php esc_html_e('Banned IP message', 'gpt3-ai-content-generator'); ?>
            </label>
            <input
                type="text"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_banned_ips_message"
                name="banned_ips_message"
                class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
                value="<?php echo esc_attr($banned_ips_message); ?>"
                placeholder="<?php echo esc_attr($placeholder_banned_ips_message); ?>"
                autocomplete="off"
                data-lpignore="true"
                data-1p-ignore="true"
                data-form-type="other"
            />
        </div>
    </div>
</div>
<div class="aipkit_popover_flyout_footer">
    <span class="aipkit_popover_flyout_footer_text">
        <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
    </span>
    <a
        class="aipkit_popover_flyout_footer_link"
        href="<?php echo esc_url('https://docs.aipower.org/docs/security-privacy'); ?>"
        target="_blank"
        rel="noopener noreferrer"
    >
        <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
    </a>
</div>
