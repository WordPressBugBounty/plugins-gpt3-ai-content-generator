<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/partials/settings-ai-forms.php
// Status: MODIFIED

/**
 * Partial: AI Forms Token Management Settings
 * Renders token limit settings for the AI Forms module.
 */

if (!defined('ABSPATH')) {
    exit;
}

// NOTE: This class will be created in a subsequent task.
// Using a placeholder check for now to prevent fatal errors.
if (class_exists('\\WPAICG\\AIForms\\Admin\\AIPKit_AI_Form_Settings_Ajax_Handler')) {
    $settings_data = \WPAICG\AIForms\Admin\AIPKit_AI_Form_Settings_Ajax_Handler::get_settings();
} else {
    // Provide default structure if the handler isn't ready yet, so the view doesn't break.
    $settings_data = ['token_management' => [], 'custom_theme' => [], 'frontend_display' => []]; // ADDED frontend_display default
}

use WPAICG\Chat\Storage\BotSettingsManager; // Use for default constants

// Get token management settings from the settings data
$token_settings = $settings_data['token_management'] ?? [];
// --- NEW: Get custom theme settings ---
$custom_theme_settings = $settings_data['custom_theme'] ?? [];
$custom_css = $custom_theme_settings['custom_css'] ?? '';
// --- NEW: Get frontend display settings ---
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
// --- END NEW ---

$settings_nonce = wp_create_nonce('aipkit_ai_forms_settings_nonce'); // Nonce for saving these settings

// --- Defaults ---
$default_reset_period = BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
$default_limit_message = BotSettingsManager::DEFAULT_TOKEN_LIMIT_MESSAGE;
$default_limit_mode = BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;

// --- Get saved values ---
$guest_limit = $token_settings['token_guest_limit'] ?? null;
$user_limit = $token_settings['token_user_limit'] ?? null;
$reset_period = $token_settings['token_reset_period'] ?? $default_reset_period;
$limit_message = $token_settings['token_limit_message'] ?? $default_limit_message;
$limit_mode = $token_settings['token_limit_mode'] ?? $default_limit_mode;
$role_limits = $token_settings['token_role_limits'] ?? [];

$guest_limit_value = ($guest_limit === null) ? '' : (string)$guest_limit;
$user_limit_value = ($user_limit === null) ? '' : (string)$user_limit;
?>
<form id="aipkit_ai_forms_settings_form">
    <input type="hidden" name="_ajax_nonce" value="<?php echo esc_attr($settings_nonce); ?>">
    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="root">
        <div class="aipkit_popover_options_list aipkit_popover_options_list--settings-root">
            <div class="aipkit_popover_option_group">
                <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                    <button
                        type="button"
                        class="aipkit_popover_option_nav aipkit_ai_forms_settings_nav"
                        data-aipkit-panel-target="limits"
                    >
                        <span class="aipkit_popover_option_label">
                            <span class="aipkit_popover_option_icon dashicons dashicons-chart-bar" aria-hidden="true"></span>
                            <span class="aipkit_popover_option_label_content">
                                <span class="aipkit_popover_option_label_text">
                                    <?php esc_html_e('Limits', 'gpt3-ai-content-generator'); ?>
                                </span>
                                <span class="aipkit_popover_option_hint">
                                    <?php esc_html_e('Tokens and usage', 'gpt3-ai-content-generator'); ?>
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
                        class="aipkit_popover_option_nav aipkit_ai_forms_settings_nav"
                        data-aipkit-panel-target="custom-css"
                    >
                        <span class="aipkit_popover_option_label">
                            <span class="aipkit_popover_option_icon dashicons dashicons-editor-code" aria-hidden="true"></span>
                            <span class="aipkit_popover_option_label_content">
                                <span class="aipkit_popover_option_label_text">
                                    <?php esc_html_e('Custom CSS', 'gpt3-ai-content-generator'); ?>
                                </span>
                                <span class="aipkit_popover_option_hint">
                                    <?php esc_html_e('Theme overrides', 'gpt3-ai-content-generator'); ?>
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
                        class="aipkit_popover_option_nav aipkit_ai_forms_settings_nav"
                        data-aipkit-panel-target="provider-filtering"
                    >
                        <span class="aipkit_popover_option_label">
                            <span class="aipkit_popover_option_icon dashicons dashicons-filter" aria-hidden="true"></span>
                            <span class="aipkit_popover_option_label_content">
                                <span class="aipkit_popover_option_label_text">
                                    <?php esc_html_e('Provider filtering', 'gpt3-ai-content-generator'); ?>
                                </span>
                                <span class="aipkit_popover_option_hint">
                                    <?php esc_html_e('Restrict providers and models', 'gpt3-ai-content-generator'); ?>
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
                href="<?php echo esc_url('https://docs.aipower.org/docs/category/ai-forms'); ?>"
                target="_blank"
                rel="noopener noreferrer"
            >
                <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
            </a>
        </div>
    </div>

    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="limits" hidden>
        <div class="aipkit_popover_options_list">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_aiforms_token_guest_limit"
                        data-tooltip="<?php echo esc_attr__('0 = disabled.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Guest limit', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="number"
                        id="aipkit_aiforms_token_guest_limit"
                        name="aiforms_token_guest_limit"
                        class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact aipkit_autosave_trigger"
                        value="<?php echo esc_attr($guest_limit_value); ?>"
                        min="0"
                        step="1"
                        placeholder="<?php esc_attr_e('(Unlimited)', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_aiforms_token_limit_mode"
                        data-tooltip="<?php echo esc_attr__('For logged-in users.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('User limit type', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_aiforms_token_limit_mode"
                        name="aiforms_token_limit_mode"
                        class="aipkit_popover_option_select aipkit_token_limit_mode_select aipkit_autosave_trigger"
                    >
                        <option value="general" <?php selected($limit_mode, 'general'); ?>>
                            <?php esc_html_e('General limit', 'gpt3-ai-content-generator'); ?>
                        </option>
                        <option value="role_based" <?php selected($limit_mode, 'role_based'); ?>>
                            <?php esc_html_e('Role-based limits', 'gpt3-ai-content-generator'); ?>
                        </option>
                    </select>
                </div>
            </div>
            <div
                class="aipkit_popover_option_row aipkit_token_general_user_limit_field"
                <?php echo ($limit_mode === 'general') ? '' : 'hidden'; ?>
            >
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_aiforms_token_user_limit"
                        data-tooltip="<?php echo esc_attr__('0 = disabled.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('General user limit', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="number"
                        id="aipkit_aiforms_token_user_limit"
                        name="aiforms_token_user_limit"
                        class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact aipkit_autosave_trigger"
                        value="<?php echo esc_attr($user_limit_value); ?>"
                        min="0"
                        step="1"
                        placeholder="<?php esc_attr_e('(Unlimited)', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_aiforms_token_reset_period"
                        data-tooltip="<?php echo esc_attr__('How often usage resets.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Reset period', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_aiforms_token_reset_period"
                        name="aiforms_token_reset_period"
                        class="aipkit_popover_option_select aipkit_autosave_trigger"
                    >
                        <option value="never" <?php selected($reset_period, 'never'); ?>>
                            <?php esc_html_e('Never', 'gpt3-ai-content-generator'); ?>
                        </option>
                        <option value="daily" <?php selected($reset_period, 'daily'); ?>>
                            <?php esc_html_e('Daily', 'gpt3-ai-content-generator'); ?>
                        </option>
                        <option value="weekly" <?php selected($reset_period, 'weekly'); ?>>
                            <?php esc_html_e('Weekly', 'gpt3-ai-content-generator'); ?>
                        </option>
                        <option value="monthly" <?php selected($reset_period, 'monthly'); ?>>
                            <?php esc_html_e('Monthly', 'gpt3-ai-content-generator'); ?>
                        </option>
                    </select>
                </div>
            </div>
            <div
                class="aipkit_popover_option_row aipkit_token_role_limits_container"
                <?php echo ($limit_mode === 'role_based') ? '' : 'hidden'; ?>
            >
                <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                    <span
                        class="aipkit_popover_option_label"
                        tabindex="0"
                        data-tooltip="<?php echo esc_attr__('Set limits for specific roles. Leave empty for unlimited, use 0 to disable access for a role.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Role limits', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <div class="aipkit_popover_role_limits">
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
                                    class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact aipkit_autosave_trigger"
                                    value="<?php echo esc_attr($role_limit_value); ?>"
                                    min="0"
                                    step="1"
                                    placeholder="<?php esc_attr_e('(Unlimited)', 'gpt3-ai-content-generator'); ?>"
                                />
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_aiforms_token_limit_message"
                        data-tooltip="<?php echo esc_attr__('The message shown to users when they exceed their token limit for the period.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Limit message', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="text"
                        id="aipkit_aiforms_token_limit_message"
                        name="aiforms_token_limit_message"
                        class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($limit_message); ?>"
                        placeholder="<?php echo esc_attr($default_limit_message); ?>"
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="custom-css" hidden>
        <div class="aipkit_popover_options_list">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                    <textarea
                        id="aipkit_aiforms_custom_css"
                        name="custom_css"
                        class="aipkit_popover_option_textarea aipkit_popover_option_textarea--code aipkit_autosave_trigger"
                        rows="12"
                        placeholder="<?php echo esc_attr($default_css_template); ?>"
                    ><?php echo esc_textarea($custom_css ?: $default_css_template); ?></textarea>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Use the "Custom" theme for a form shortcode to apply these CSS rules. Target the wrapper with ".aipkit-ai-form-wrapper.aipkit-theme-custom".', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="provider-filtering" hidden>
        <div class="aipkit_popover_options_list">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                    <label class="aipkit_popover_option_label" for="aipkit_aiforms_frontend_models">
                        <?php esc_html_e('Allowed models', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <textarea
                        id="aipkit_aiforms_frontend_models"
                        name="frontend_models"
                        class="aipkit_popover_option_textarea aipkit_autosave_trigger"
                        rows="4"
                        hidden
                        placeholder="<?php esc_attr_e('Select models below or leave empty for all', 'gpt3-ai-content-generator'); ?>"
                    ><?php echo esc_textarea($allowed_models_str); ?></textarea>
                    <textarea
                        id="aipkit_aiforms_frontend_providers"
                        name="frontend_providers"
                        class="aipkit_popover_option_textarea aipkit_autosave_trigger"
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
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Pick specific models to show on the frontend. Leave everything unselected to allow ALL models. Providers are inferred from your selection.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</form>
