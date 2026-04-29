<?php
/**
 * Partial: Security Settings Page
 */
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

use WPAICG\Core\Moderation\AIPKit_Global_Security_Settings;

$security_settings = class_exists(AIPKit_Global_Security_Settings::class)
    ? AIPKit_Global_Security_Settings::get_settings()
    : [
        'enable_ip_anonymization' => '0',
        'openai_moderation_enabled' => '0',
        'openai_moderation_message' => __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator'),
        'blocklists' => [
            'banned_words' => '',
            'banned_words_message' => '',
            'banned_ips' => '',
            'banned_ips_message' => '',
        ],
    ];
$security_blocklists = isset($security_settings['blocklists']) && is_array($security_settings['blocklists'])
    ? $security_settings['blocklists']
    : [];
$enable_ip_anonymization = isset($security_settings['enable_ip_anonymization']) && (string) $security_settings['enable_ip_anonymization'] === '1'
    ? '1'
    : '0';
$openai_moderation_enabled = isset($security_settings['openai_moderation_enabled']) && (string) $security_settings['openai_moderation_enabled'] === '1'
    ? '1'
    : '0';
$openai_moderation_message = (string) ($security_settings['openai_moderation_message']
    ?? __('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator'));
$banned_words = (string) ($security_blocklists['banned_words'] ?? '');
$banned_words_message = (string) ($security_blocklists['banned_words_message'] ?? '');
$banned_ips = (string) ($security_blocklists['banned_ips'] ?? '');
$banned_ips_message = (string) ($security_blocklists['banned_ips_message'] ?? '');
$is_pro_plan = class_exists('\WPAICG\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
$openai_moderation_available = $is_pro_plan && class_exists('\WPAICG\Lib\Addons\AIPKit_OpenAI_Moderation');
$pricing_url = admin_url('admin.php?page=wpaicg-pricing');
?>
<div class="aipkit_form-group aipkit_settings_simple_row">
    <label class="aipkit_form-label" for="aipkit_settings_enable_ip_anonymization">
        <?php esc_html_e('IP Anonymization', 'gpt3-ai-content-generator'); ?>
        <span class="aipkit_form-label-helper"><?php esc_html_e('Store anonymized IPs in logs.', 'gpt3-ai-content-generator'); ?></span>
    </label>
    <select
        id="aipkit_settings_enable_ip_anonymization"
        name="security[enable_ip_anonymization]"
        class="aipkit_form-input aipkit_autosave_trigger"
    >
        <option value="1" <?php selected($enable_ip_anonymization, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
        <option value="0" <?php selected($enable_ip_anonymization, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
    </select>
</div>

<div class="aipkit_form-group aipkit_settings_simple_row">
    <label class="aipkit_form-label" for="aipkit_settings_openai_moderation_enabled">
        <?php esc_html_e('OpenAI Moderation', 'gpt3-ai-content-generator'); ?>
    </label>
    <?php if ($openai_moderation_available) : ?>
        <select
            id="aipkit_settings_openai_moderation_enabled"
            name="security[openai_moderation_enabled]"
            class="aipkit_form-input aipkit_autosave_trigger"
        >
            <option value="1" <?php selected($openai_moderation_enabled, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
            <option value="0" <?php selected($openai_moderation_enabled, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
        </select>
    <?php else : ?>
        <div class="aipkit_settings_security_field_group">
            <select
                id="aipkit_settings_openai_moderation_enabled"
                class="aipkit_form-input"
                disabled
                aria-disabled="true"
            >
                <option value="1" <?php selected($openai_moderation_enabled, '1'); ?>><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                <option value="0" <?php selected($openai_moderation_enabled, '0'); ?>><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
            </select>
            <input
                type="hidden"
                name="security[openai_moderation_enabled]"
                value="<?php echo esc_attr($openai_moderation_enabled); ?>"
            />
            <input
                type="hidden"
                name="security[openai_moderation_message]"
                value="<?php echo esc_attr($openai_moderation_message); ?>"
            />
            <a
                class="aipkit_settings_security_upgrade_link"
                href="<?php echo esc_url($pricing_url); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if ($openai_moderation_available) : ?>
<div
    class="aipkit_form-group aipkit_settings_simple_row"
    id="aipkit_settings_openai_moderation_message_row"
    <?php echo ($openai_moderation_enabled === '1') ? '' : 'hidden'; ?>
>
    <label class="aipkit_form-label" for="aipkit_settings_openai_moderation_message">
        <?php esc_html_e('OpenAI Moderation Message', 'gpt3-ai-content-generator'); ?>
    </label>
    <div class="aipkit_settings_security_field_group">
        <input
            type="text"
            id="aipkit_settings_openai_moderation_message"
            name="security[openai_moderation_message]"
            class="aipkit_form-input aipkit_autosave_trigger"
            value="<?php echo esc_attr($openai_moderation_message); ?>"
            placeholder="<?php esc_attr_e('Your message was flagged by the moderation system and could not be sent.', 'gpt3-ai-content-generator'); ?>"
            autocomplete="off"
            data-lpignore="true"
            data-1p-ignore="true"
            data-form-type="other"
        />
    </div>
</div>
<?php endif; ?>

<div class="aipkit_form-group aipkit_settings_simple_row">
    <label class="aipkit_form-label" for="aipkit_settings_banned_words">
        <?php esc_html_e('Banned Words', 'gpt3-ai-content-generator'); ?>
    </label>
    <div class="aipkit_settings_security_field_group">
        <textarea
            id="aipkit_settings_banned_words"
            name="security[blocklists][banned_words]"
            class="aipkit_form-input aipkit_autosave_trigger aipkit_settings_security_textarea"
            rows="4"
            placeholder="<?php esc_attr_e('e.g., word1, another word, specific phrase', 'gpt3-ai-content-generator'); ?>"
        ><?php echo esc_textarea($banned_words); ?></textarea>
    </div>
</div>

<div class="aipkit_form-group aipkit_settings_simple_row">
    <label class="aipkit_form-label" for="aipkit_settings_banned_words_message">
        <?php esc_html_e('Banned Words Message', 'gpt3-ai-content-generator'); ?>
    </label>
    <div class="aipkit_settings_security_field_group">
        <input
            type="text"
            id="aipkit_settings_banned_words_message"
            name="security[blocklists][banned_words_message]"
            class="aipkit_form-input aipkit_autosave_trigger"
            value="<?php echo esc_attr($banned_words_message); ?>"
            placeholder="<?php esc_attr_e('Sorry, your message could not be sent as it contains prohibited words.', 'gpt3-ai-content-generator'); ?>"
            autocomplete="off"
            data-lpignore="true"
            data-1p-ignore="true"
            data-form-type="other"
        />
    </div>
</div>

<div class="aipkit_form-group aipkit_settings_simple_row">
    <label class="aipkit_form-label" for="aipkit_settings_banned_ips">
        <?php esc_html_e('Banned IPs', 'gpt3-ai-content-generator'); ?>
    </label>
    <div class="aipkit_settings_security_field_group">
        <textarea
            id="aipkit_settings_banned_ips"
            name="security[blocklists][banned_ips]"
            class="aipkit_form-input aipkit_autosave_trigger aipkit_settings_security_textarea"
            rows="4"
            placeholder="<?php esc_attr_e('e.g., 123.123.123.123, 111.222.333.444', 'gpt3-ai-content-generator'); ?>"
        ><?php echo esc_textarea($banned_ips); ?></textarea>
    </div>
</div>

<div class="aipkit_form-group aipkit_settings_simple_row">
    <label class="aipkit_form-label" for="aipkit_settings_banned_ips_message">
        <?php esc_html_e('Banned IP Message', 'gpt3-ai-content-generator'); ?>
    </label>
    <div class="aipkit_settings_security_field_group">
        <input
            type="text"
            id="aipkit_settings_banned_ips_message"
            name="security[blocklists][banned_ips_message]"
            class="aipkit_form-input aipkit_autosave_trigger"
            value="<?php echo esc_attr($banned_ips_message); ?>"
            placeholder="<?php esc_attr_e('Access from your IP address has been blocked.', 'gpt3-ai-content-generator'); ?>"
            autocomplete="off"
            data-lpignore="true"
            data-1p-ignore="true"
            data-form-type="other"
        />
    </div>
</div>
