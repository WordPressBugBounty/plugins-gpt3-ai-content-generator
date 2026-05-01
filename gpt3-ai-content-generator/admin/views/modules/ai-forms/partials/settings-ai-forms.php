<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/partials/settings-ai-forms.php
// Status: MODIFIED

/**
 * Partial: AI Forms Settings
 * Renders module-level settings for AI Forms.
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

if (class_exists('\\WPAICG\\AIForms\\Admin\\AIPKit_AI_Form_Settings_Ajax_Handler')) {
    $settings_data = \WPAICG\AIForms\Admin\AIPKit_AI_Form_Settings_Ajax_Handler::get_settings();
} else {
    $settings_data = ['token_management' => [], 'custom_theme' => [], 'frontend_display' => []];
}

use WPAICG\Chat\Storage\BotSettingsManager;

$token_settings = $settings_data['token_management'] ?? [];
$custom_theme_settings = $settings_data['custom_theme'] ?? [];
$custom_css = $custom_theme_settings['custom_css'] ?? '';
$frontend_display_settings = $settings_data['frontend_display'] ?? [];
$allowed_providers_str = $frontend_display_settings['allowed_providers'] ?? '';
$allowed_models_str = $frontend_display_settings['allowed_models'] ?? '';

$default_css_template = "/* --- AIPKit AI Forms Custom CSS Example --- */
.aipkit-ai-form-wrapper.aipkit-theme-custom {
    background-color: #f0f4f8;
    border: 1px solid #d1d9e4;
    color: #2c3e50;
}
.aipkit-ai-form-wrapper.aipkit-theme-custom h5 {
    color: #2c3e50;
    border-bottom: 1px solid #d1d9e4;
}
.aipkit-ai-form-wrapper.aipkit-theme-custom .aipkit_btn-primary {
    background-color: #3498db;
    border-color: #2980b9;
}
.aipkit-ai-form-wrapper.aipkit-theme-custom .aipkit_btn-primary:hover {
    background-color: #2980b9;
}
";

$settings_nonce = wp_create_nonce('aipkit_ai_forms_settings_nonce');

$default_reset_period = BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
$default_limit_message = BotSettingsManager::get_default_token_limit_message();
$default_limit_mode = BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;
$default_token_limit_actions = BotSettingsManager::get_default_token_limit_action_settings();
$token_limit_action_options = BotSettingsManager::get_token_limit_action_options();

$guest_limit = $token_settings['token_guest_limit'] ?? null;
$user_limit = $token_settings['token_user_limit'] ?? null;
$reset_period = $token_settings['token_reset_period'] ?? $default_reset_period;
$limit_message = $token_settings['token_limit_message'] ?? $default_limit_message;
$limit_mode = $token_settings['token_limit_mode'] ?? $default_limit_mode;
$role_limits = $token_settings['token_role_limits'] ?? [];
$token_limit_primary_action_type = $token_settings['token_limit_primary_action_type'] ?? $default_token_limit_actions['primary_type'];
$token_limit_primary_action_label = $token_settings['token_limit_primary_action_label'] ?? $default_token_limit_actions['primary_label'];
$token_limit_primary_action_url = $token_settings['token_limit_primary_action_url'] ?? $default_token_limit_actions['primary_url'];
$token_limit_secondary_action_type = $token_settings['token_limit_secondary_action_type'] ?? $default_token_limit_actions['secondary_type'];
$token_limit_secondary_action_label = $token_settings['token_limit_secondary_action_label'] ?? $default_token_limit_actions['secondary_label'];
$token_limit_secondary_action_url = $token_settings['token_limit_secondary_action_url'] ?? $default_token_limit_actions['secondary_url'];

$guest_limit_value = ($guest_limit === null) ? '' : (string)$guest_limit;
$user_limit_value = ($user_limit === null) ? '' : (string)$user_limit;
$primary_action_show_label = $token_limit_primary_action_type !== 'none';
$primary_action_show_url = $token_limit_primary_action_type === 'custom_url';
$secondary_action_show_label = $token_limit_secondary_action_type !== 'none';
$secondary_action_show_url = $token_limit_secondary_action_type === 'custom_url';
?>
<form id="aipkit_ai_forms_settings_form" class="aipkit_ai_forms_settings_form">
    <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr($settings_nonce); ?>">
    <div class="aipkit_ai_forms_settings_page">
        <section class="aipkit_ai_forms_settings_block">
            <div class="aipkit_ai_forms_settings_block_header">
                <div>
                    <h3 class="aipkit_ai_forms_settings_block_title"><?php esc_html_e('Limits', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_ai_forms_settings_block_helper"><?php esc_html_e('Quotas and reset rules.', 'gpt3-ai-content-generator'); ?></p>
                </div>
            </div>
            <div class="aipkit_ai_forms_settings_block_body">
                <div class="aipkit_ai_forms_settings_row">
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_guest_limit">
                        <?php esc_html_e('Guest quota', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('0 disables guest access.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="number"
                        id="aipkit_aiforms_token_guest_limit"
                        name="aiforms_token_guest_limit"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                        value="<?php echo esc_attr($guest_limit_value); ?>"
                        min="0"
                        step="1"
                        placeholder="<?php esc_attr_e('Unlimited', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row">
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_mode">
                        <?php esc_html_e('Quota mode', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('For logged-in users.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_aiforms_token_limit_mode"
                        name="aiforms_token_limit_mode"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_token_limit_mode_select aipkit_autosave_trigger"
                    >
                        <option value="general" <?php selected($limit_mode, 'general'); ?>>
                            <?php esc_html_e('Same quota for all users', 'gpt3-ai-content-generator'); ?>
                        </option>
                        <option value="role_based" <?php selected($limit_mode, 'role_based'); ?>>
                            <?php esc_html_e('Role-based quotas', 'gpt3-ai-content-generator'); ?>
                        </option>
                    </select>
                </div>

                <div
                    class="aipkit_ai_forms_settings_row aipkit_token_general_user_limit_field"
                    <?php echo ($limit_mode === 'general') ? '' : 'hidden'; ?>
                >
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_user_limit">
                        <?php esc_html_e('User quota', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('0 disables logged-in users.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="number"
                        id="aipkit_aiforms_token_user_limit"
                        name="aiforms_token_user_limit"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                        value="<?php echo esc_attr($user_limit_value); ?>"
                        min="0"
                        step="1"
                        placeholder="<?php esc_attr_e('Unlimited', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row">
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_reset_period">
                        <?php esc_html_e('Reset period', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('How often usage resets.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_aiforms_token_reset_period"
                        name="aiforms_token_reset_period"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                    >
                        <option value="never" <?php selected($reset_period, 'never'); ?>><?php esc_html_e('Never', 'gpt3-ai-content-generator'); ?></option>
                        <option value="daily" <?php selected($reset_period, 'daily'); ?>><?php esc_html_e('Daily', 'gpt3-ai-content-generator'); ?></option>
                        <option value="weekly" <?php selected($reset_period, 'weekly'); ?>><?php esc_html_e('Weekly', 'gpt3-ai-content-generator'); ?></option>
                        <option value="monthly" <?php selected($reset_period, 'monthly'); ?>><?php esc_html_e('Monthly', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>

                <div
                    class="aipkit_ai_forms_settings_row aipkit_token_role_limits_container aipkit_limits_role_row"
                    <?php echo ($limit_mode === 'role_based') ? '' : 'hidden'; ?>
                >
                    <div class="aipkit_form-label">
                        <?php esc_html_e('Role quotas', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Empty allows unlimited. 0 disables a role.', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_popover_role_limits aipkit_ai_forms_role_limits">
                        <?php
                        $editable_roles = get_editable_roles();
                        foreach ($editable_roles as $role_slug => $role_info) :
                            $role_name = translate_user_role($role_info['name']);
                            $role_limit = $role_limits[$role_slug] ?? null;
                            $role_limit_value = ($role_limit === null) ? '' : (string)$role_limit;
                            ?>
                            <div class="aipkit_popover_role_limit_row">
                                <span class="aipkit_popover_role_limit_label"><?php echo esc_html($role_name); ?></span>
                                <input
                                    type="number"
                                    id="aipkit_aiforms_token_role_<?php echo esc_attr($role_slug); ?>"
                                    name="aiforms_token_role_limits[<?php echo esc_attr($role_slug); ?>]"
                                    class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                                    value="<?php echo esc_attr($role_limit_value); ?>"
                                    min="0"
                                    step="1"
                                    placeholder="<?php esc_attr_e('Unlimited', 'gpt3-ai-content-generator'); ?>"
                                />
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="aipkit_ai_forms_settings_row aipkit_limits_message_row">
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_message">
                        <?php esc_html_e('Quota message', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Shown when the quota is reached.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="aipkit_aiforms_token_limit_message"
                        name="aiforms_token_limit_message"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($limit_message); ?>"
                        placeholder="<?php echo esc_attr($default_limit_message); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row" data-aipkit-limit-action-row="primary">
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_primary_action_type">
                        <?php esc_html_e('Primary button', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Main quota action.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_aiforms_token_limit_primary_action_type"
                        name="aiforms_token_limit_primary_action_type"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                    >
                        <?php foreach ($token_limit_action_options as $action_value => $action_label) : ?>
                            <option
                                value="<?php echo esc_attr($action_value); ?>"
                                data-default-label="<?php echo esc_attr(BotSettingsManager::get_token_limit_action_default_label($action_value)); ?>"
                                <?php selected($token_limit_primary_action_type, $action_value); ?>
                            >
                                <?php echo esc_html($action_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div
                    class="aipkit_ai_forms_settings_row"
                    data-aipkit-limit-action-dependent-for="primary"
                    data-aipkit-limit-action-field="label"
                    <?php if (!$primary_action_show_label) : ?>hidden<?php endif; ?>
                >
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_primary_action_label">
                        <?php esc_html_e('Primary label', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Text shown on the primary button.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="aipkit_aiforms_token_limit_primary_action_label"
                        name="aiforms_token_limit_primary_action_label"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($token_limit_primary_action_label); ?>"
                        placeholder="<?php echo esc_attr(BotSettingsManager::get_token_limit_action_default_label($token_limit_primary_action_type)); ?>"
                    />
                </div>

                <div
                    class="aipkit_ai_forms_settings_row"
                    data-aipkit-limit-action-dependent-for="primary"
                    data-aipkit-limit-action-field="url"
                    <?php if (!$primary_action_show_url) : ?>hidden<?php endif; ?>
                >
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_primary_action_url">
                        <?php esc_html_e('Primary URL', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Destination for the primary custom URL.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="url"
                        id="aipkit_aiforms_token_limit_primary_action_url"
                        name="aiforms_token_limit_primary_action_url"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($token_limit_primary_action_url); ?>"
                        placeholder="<?php esc_attr_e('https://example.com/account', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row" data-aipkit-limit-action-row="secondary">
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_secondary_action_type">
                        <?php esc_html_e('Secondary button', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Optional quota action.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_aiforms_token_limit_secondary_action_type"
                        name="aiforms_token_limit_secondary_action_type"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                    >
                        <?php foreach ($token_limit_action_options as $action_value => $action_label) : ?>
                            <option
                                value="<?php echo esc_attr($action_value); ?>"
                                data-default-label="<?php echo esc_attr(BotSettingsManager::get_token_limit_action_default_label($action_value)); ?>"
                                <?php selected($token_limit_secondary_action_type, $action_value); ?>
                            >
                                <?php echo esc_html($action_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div
                    class="aipkit_ai_forms_settings_row"
                    data-aipkit-limit-action-dependent-for="secondary"
                    data-aipkit-limit-action-field="label"
                    <?php if (!$secondary_action_show_label) : ?>hidden<?php endif; ?>
                >
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_secondary_action_label">
                        <?php esc_html_e('Secondary label', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Text shown on the secondary button.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="aipkit_aiforms_token_limit_secondary_action_label"
                        name="aiforms_token_limit_secondary_action_label"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($token_limit_secondary_action_label); ?>"
                        placeholder="<?php echo esc_attr(BotSettingsManager::get_token_limit_action_default_label($token_limit_secondary_action_type)); ?>"
                    />
                </div>

                <div
                    class="aipkit_ai_forms_settings_row"
                    data-aipkit-limit-action-dependent-for="secondary"
                    data-aipkit-limit-action-field="url"
                    <?php if (!$secondary_action_show_url) : ?>hidden<?php endif; ?>
                >
                    <label class="aipkit_form-label" for="aipkit_aiforms_token_limit_secondary_action_url">
                        <?php esc_html_e('Secondary URL', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Secondary link URL.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="url"
                        id="aipkit_aiforms_token_limit_secondary_action_url"
                        name="aiforms_token_limit_secondary_action_url"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($token_limit_secondary_action_url); ?>"
                        placeholder="<?php esc_attr_e('https://example.com/support', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>
            </div>
        </section>

        <section class="aipkit_ai_forms_settings_block">
            <div class="aipkit_ai_forms_settings_block_header">
                <div>
                    <h3 class="aipkit_ai_forms_settings_block_title"><?php esc_html_e('Custom CSS', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_ai_forms_settings_block_helper"><?php esc_html_e('Theme overrides for Custom form theme.', 'gpt3-ai-content-generator'); ?></p>
                </div>
            </div>
            <div class="aipkit_ai_forms_settings_block_body">
                <div class="aipkit_ai_forms_settings_row aipkit_ai_forms_settings_row--plain">
                    <textarea
                        id="aipkit_aiforms_custom_css"
                        name="custom_css"
                        class="aipkit_form-input aipkit_ai_forms_settings_textarea aipkit_ai_forms_settings_textarea--code aipkit_autosave_trigger"
                        rows="12"
                        placeholder="<?php echo esc_attr($default_css_template); ?>"
                    ><?php echo esc_textarea($custom_css ?: $default_css_template); ?></textarea>
                </div>
            </div>
        </section>

        <section class="aipkit_ai_forms_settings_block">
            <div class="aipkit_ai_forms_settings_block_header">
                <div>
                    <h3 class="aipkit_ai_forms_settings_block_title"><?php esc_html_e('Frontend Models', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_ai_forms_settings_block_helper"><?php esc_html_e('Restrict providers and models shown to visitors.', 'gpt3-ai-content-generator'); ?></p>
                </div>
            </div>
            <div class="aipkit_ai_forms_settings_block_body">
                <div class="aipkit_ai_forms_settings_row aipkit_ai_forms_settings_row--plain">
                    <textarea
                        id="aipkit_aiforms_frontend_models"
                        name="frontend_models"
                        class="aipkit_autosave_trigger"
                        rows="4"
                        hidden
                        placeholder="<?php esc_attr_e('Select models below or leave empty for all', 'gpt3-ai-content-generator'); ?>"
                    ><?php echo esc_textarea($allowed_models_str); ?></textarea>
                    <textarea
                        id="aipkit_aiforms_frontend_providers"
                        name="frontend_providers"
                        class="aipkit_autosave_trigger"
                        rows="2"
                        hidden
                    ><?php echo esc_textarea($allowed_providers_str); ?></textarea>
                    <div
                        id="aipkit_ai_forms_models_selector"
                        class="aipkit_models_selector"
                        data-initial-value="<?php echo esc_attr($allowed_models_str); ?>"
                    >
                        <div class="aipkit_models_selector-loading">
                            <?php esc_html_e('Loading model list...', 'gpt3-ai-content-generator'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</form>
