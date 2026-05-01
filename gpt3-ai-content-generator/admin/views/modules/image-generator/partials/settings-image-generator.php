<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/image-generator/partials/settings-image-generator.php
// Status: MODIFIED

/**
 * Partial: Image Generator Settings
 * Renders module-level settings for the Image Generator.
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

use WPAICG\Chat\Storage\BotSettingsManager;
use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler;

$settings_data = AIPKit_Image_Settings_Ajax_Handler::get_settings();
$settings_nonce = wp_create_nonce('aipkit_image_generator_settings_nonce');

$token_settings = $settings_data['token_management'] ?? [];
$custom_css = $settings_data['common']['custom_css'] ?? '';
$frontend_display_settings = $settings_data['frontend_display'] ?? [];
$allowed_models_str = $frontend_display_settings['allowed_models'] ?? '';
$ui_text_settings = $settings_data['ui_text'] ?? [];
$ui_text_defaults = AIPKit_Image_Settings_Ajax_Handler::get_default_ui_text_settings();

$default_css_template = "/* --- AIPKit Image Generator Custom CSS Example --- */
.aipkit_image_generator_public_wrapper.aipkit-theme-custom {
    background-color: #f0f4f8;
    border: 1px solid #d1d9e4;
    color: #2c3e50;
}
.aipkit_image_generator_public_wrapper.aipkit-theme-custom .aipkit_image_generator_input_bar {
    background-color: #ffffff;
    border: 1px solid #d1d9e4;
}
.aipkit_image_generator_public_wrapper.aipkit-theme-custom .aipkit_btn-primary {
    background-color: #3498db;
    border-color: #2980b9;
}
";

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
$token_limit_primary_action_type = $token_settings['token_limit_primary_action_type'] ?? $default_token_limit_actions['primary_type'];
$token_limit_primary_action_label = $token_settings['token_limit_primary_action_label'] ?? $default_token_limit_actions['primary_label'];
$token_limit_primary_action_url = $token_settings['token_limit_primary_action_url'] ?? $default_token_limit_actions['primary_url'];
$token_limit_secondary_action_type = $token_settings['token_limit_secondary_action_type'] ?? $default_token_limit_actions['secondary_type'];
$token_limit_secondary_action_label = $token_settings['token_limit_secondary_action_label'] ?? $default_token_limit_actions['secondary_label'];
$token_limit_secondary_action_url = $token_settings['token_limit_secondary_action_url'] ?? $default_token_limit_actions['secondary_url'];

$role_limits_raw = $token_settings['token_role_limits'] ?? [];
$role_limits = is_string($role_limits_raw) ? json_decode($role_limits_raw, true) : $role_limits_raw;
if (!is_array($role_limits)) {
    $role_limits = [];
}

$guest_limit_value = ($guest_limit === null) ? '' : (string) $guest_limit;
$user_limit_value = ($user_limit === null) ? '' : (string) $user_limit;
$primary_action_show_label = $token_limit_primary_action_type !== 'none';
$primary_action_show_url = $token_limit_primary_action_type === 'custom_url';
$secondary_action_show_label = $token_limit_secondary_action_type !== 'none';
$secondary_action_show_url = $token_limit_secondary_action_type === 'custom_url';

$get_ui_text_value = static function (string $key) use ($ui_text_settings, $ui_text_defaults): string {
    $value = isset($ui_text_settings[$key]) ? (string) $ui_text_settings[$key] : '';
    if ($value === '' && isset($ui_text_defaults[$key])) {
        return (string) $ui_text_defaults[$key];
    }
    return $value;
};

$ui_text_fields = [
    [
        'id' => 'aipkit_image_ui_text_generate_label',
        'name' => 'ui_text_generate_label',
        'key' => 'generate_label',
        'label' => __('Generate button', 'gpt3-ai-content-generator'),
        'helper' => __('Generate action label.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_edit_label',
        'name' => 'ui_text_edit_label',
        'key' => 'edit_label',
        'label' => __('Edit button', 'gpt3-ai-content-generator'),
        'helper' => __('Edit action label.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_mode_generate_label',
        'name' => 'ui_text_mode_generate_label',
        'key' => 'mode_generate_label',
        'label' => __('Generate tab', 'gpt3-ai-content-generator'),
        'helper' => __('Generate mode label.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_mode_edit_label',
        'name' => 'ui_text_mode_edit_label',
        'key' => 'mode_edit_label',
        'label' => __('Edit tab', 'gpt3-ai-content-generator'),
        'helper' => __('Edit mode label.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_generate_placeholder',
        'name' => 'ui_text_generate_placeholder',
        'key' => 'generate_placeholder',
        'label' => __('Generate placeholder', 'gpt3-ai-content-generator'),
        'helper' => __('Prompt field hint.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_edit_placeholder',
        'name' => 'ui_text_edit_placeholder',
        'key' => 'edit_placeholder',
        'label' => __('Edit placeholder', 'gpt3-ai-content-generator'),
        'helper' => __('Edit prompt hint.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_source_image_label',
        'name' => 'ui_text_source_image_label',
        'key' => 'source_image_label',
        'label' => __('Source image', 'gpt3-ai-content-generator'),
        'helper' => __('Upload field label.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_upload_dropzone_title',
        'name' => 'ui_text_upload_dropzone_title',
        'key' => 'upload_dropzone_title',
        'label' => __('Upload title', 'gpt3-ai-content-generator'),
        'helper' => __('Dropzone title.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_upload_dropzone_meta',
        'name' => 'ui_text_upload_dropzone_meta',
        'key' => 'upload_dropzone_meta',
        'label' => __('Upload meta', 'gpt3-ai-content-generator'),
        'helper' => __('Dropzone helper line.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_upload_hint',
        'name' => 'ui_text_upload_hint',
        'key' => 'upload_hint',
        'label' => __('Upload helper', 'gpt3-ai-content-generator'),
        'helper' => __('Upload guidance text.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_history_title',
        'name' => 'ui_text_history_title',
        'key' => 'history_title',
        'label' => __('History title', 'gpt3-ai-content-generator'),
        'helper' => __('User history heading.', 'gpt3-ai-content-generator'),
    ],
    [
        'id' => 'aipkit_image_ui_text_results_empty',
        'name' => 'ui_text_results_empty',
        'key' => 'results_empty',
        'label' => __('Empty results', 'gpt3-ai-content-generator'),
        'helper' => __('Shown before results.', 'gpt3-ai-content-generator'),
    ],
];
?>
<form id="aipkit_image_generator_settings_form" class="aipkit_ai_forms_settings_form aipkit_image_generator_settings_form" onsubmit="return false;">
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
                    <label class="aipkit_form-label" for="aipkit_image_token_guest_limit">
                        <?php esc_html_e('Guest quota', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('0 disables guest access.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="number"
                        id="aipkit_image_token_guest_limit"
                        name="image_token_guest_limit"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                        value="<?php echo esc_attr($guest_limit_value); ?>"
                        min="0"
                        step="1"
                        placeholder="<?php esc_attr_e('Unlimited', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row">
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_mode">
                        <?php esc_html_e('Quota mode', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('For logged-in users.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_image_token_limit_mode"
                        name="image_token_limit_mode"
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
                    <label class="aipkit_form-label" for="aipkit_image_token_user_limit">
                        <?php esc_html_e('User quota', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('0 disables logged-in users.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="number"
                        id="aipkit_image_token_user_limit"
                        name="image_token_user_limit"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_autosave_trigger"
                        value="<?php echo esc_attr($user_limit_value); ?>"
                        min="0"
                        step="1"
                        placeholder="<?php esc_attr_e('Unlimited', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row aipkit_token_reset_period_row">
                    <label class="aipkit_form-label" for="aipkit_image_token_reset_period">
                        <?php esc_html_e('Reset period', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('How often usage resets.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_image_token_reset_period"
                        name="image_token_reset_period"
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
                            $role_limit_value = ($role_limit === null) ? '' : (string) $role_limit;
                            ?>
                            <div class="aipkit_popover_role_limit_row">
                                <span class="aipkit_popover_role_limit_label"><?php echo esc_html($role_name); ?></span>
                                <input
                                    type="number"
                                    id="aipkit_image_token_role_<?php echo esc_attr($role_slug); ?>"
                                    name="image_token_role_limits[<?php echo esc_attr($role_slug); ?>]"
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
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_message">
                        <?php esc_html_e('Quota message', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Shown when the quota is reached.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="aipkit_image_token_limit_message"
                        name="image_token_limit_message"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($limit_message); ?>"
                        placeholder="<?php echo esc_attr($default_limit_message); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row" data-aipkit-limit-action-row="primary">
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_primary_action_type">
                        <?php esc_html_e('Primary button', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Main quota action.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_image_token_limit_primary_action_type"
                        name="image_token_limit_primary_action_type"
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
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_primary_action_label">
                        <?php esc_html_e('Primary label', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Text shown on the primary button.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="aipkit_image_token_limit_primary_action_label"
                        name="image_token_limit_primary_action_label"
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
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_primary_action_url">
                        <?php esc_html_e('Primary URL', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Destination for the primary custom URL.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="url"
                        id="aipkit_image_token_limit_primary_action_url"
                        name="image_token_limit_primary_action_url"
                        class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                        value="<?php echo esc_attr($token_limit_primary_action_url); ?>"
                        placeholder="<?php esc_attr_e('https://example.com/account', 'gpt3-ai-content-generator'); ?>"
                    />
                </div>

                <div class="aipkit_ai_forms_settings_row" data-aipkit-limit-action-row="secondary">
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_secondary_action_type">
                        <?php esc_html_e('Secondary button', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Optional quota action.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <select
                        id="aipkit_image_token_limit_secondary_action_type"
                        name="image_token_limit_secondary_action_type"
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
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_secondary_action_label">
                        <?php esc_html_e('Secondary label', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Text shown on the secondary button.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="text"
                        id="aipkit_image_token_limit_secondary_action_label"
                        name="image_token_limit_secondary_action_label"
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
                    <label class="aipkit_form-label" for="aipkit_image_token_limit_secondary_action_url">
                        <?php esc_html_e('Secondary URL', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_form-label-helper"><?php esc_html_e('Secondary link URL.', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <input
                        type="url"
                        id="aipkit_image_token_limit_secondary_action_url"
                        name="image_token_limit_secondary_action_url"
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
                    <h3 class="aipkit_ai_forms_settings_block_title"><?php esc_html_e('UI text', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_ai_forms_settings_block_helper"><?php esc_html_e('Frontend labels and placeholders.', 'gpt3-ai-content-generator'); ?></p>
                </div>
            </div>
            <div class="aipkit_ai_forms_settings_block_body">
                <?php foreach ($ui_text_fields as $field) : ?>
                    <div class="aipkit_ai_forms_settings_row">
                        <label class="aipkit_form-label" for="<?php echo esc_attr($field['id']); ?>">
                            <?php echo esc_html($field['label']); ?>
                            <span class="aipkit_form-label-helper"><?php echo esc_html($field['helper']); ?></span>
                        </label>
                        <input
                            type="text"
                            id="<?php echo esc_attr($field['id']); ?>"
                            name="<?php echo esc_attr($field['name']); ?>"
                            class="aipkit_form-input aipkit_ai_forms_settings_control aipkit_ai_forms_settings_control--wide aipkit_autosave_trigger"
                            value="<?php echo esc_attr($get_ui_text_value($field['key'])); ?>"
                            placeholder="<?php echo esc_attr($ui_text_defaults[$field['key']] ?? ''); ?>"
                        />
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="aipkit_ai_forms_settings_block">
            <div class="aipkit_ai_forms_settings_block_header">
                <div>
                    <h3 class="aipkit_ai_forms_settings_block_title"><?php esc_html_e('Custom CSS', 'gpt3-ai-content-generator'); ?></h3>
                    <p class="aipkit_ai_forms_settings_block_helper"><?php esc_html_e('Theme overrides for Custom image theme.', 'gpt3-ai-content-generator'); ?></p>
                </div>
            </div>
            <div class="aipkit_ai_forms_settings_block_body">
                <div class="aipkit_ai_forms_settings_row aipkit_ai_forms_settings_row--plain">
                    <textarea
                        id="aipkit_image_generator_custom_css"
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
                        id="aipkit_image_gen_frontend_models"
                        name="frontend_models"
                        class="aipkit_autosave_trigger"
                        rows="4"
                        hidden
                        placeholder="<?php esc_attr_e('Select models below or leave empty for all', 'gpt3-ai-content-generator'); ?>"
                    ><?php echo esc_textarea($allowed_models_str); ?></textarea>
                    <div
                        id="aipkit_image_gen_models_selector"
                        class="aipkit_models_selector"
                        data-initial-value="<?php echo esc_attr($allowed_models_str); ?>"
                        data-empty-all-selected="<?php echo $allowed_models_str === '' ? '1' : '0'; ?>"
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
