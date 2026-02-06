<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/image-generator/partials/settings-token-management.php
// Status: NEW FILE

/**
 * Partial: Image Generator Token Management Settings
 * Renders token limit settings for the Image Generator module.
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler;
use WPAICG\Chat\Storage\BotSettingsManager; // Use for default constants

// Get settings
$settings_data = AIPKit_Image_Settings_Ajax_Handler::get_settings();
$token_settings = $settings_data['token_management'] ?? [];

// --- Defaults ---
$default_reset_period = BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
$default_limit_message = BotSettingsManager::DEFAULT_TOKEN_LIMIT_MESSAGE ?: __('You have reached your token limit for this period.', 'gpt3-ai-content-generator');
$default_limit_mode = BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;

// --- Get saved values ---
$guest_limit = $token_settings['token_guest_limit'] ?? null;
$user_limit = $token_settings['token_user_limit'] ?? null;
$reset_period = $token_settings['token_reset_period'] ?? $default_reset_period;
$limit_message = $token_settings['token_limit_message'] ?? $default_limit_message;
$limit_mode = $token_settings['token_limit_mode'] ?? $default_limit_mode;

// Handle role limits - they are JSON encoded in the option
$role_limits_raw = $token_settings['token_role_limits'] ?? '[]';
$role_limits = is_string($role_limits_raw) ? json_decode($role_limits_raw, true) : ($role_limits_raw ?: []);
if (!is_array($role_limits)) {
    $role_limits = [];
}


$guest_limit_value = ($guest_limit === null) ? '' : (string)$guest_limit;
$user_limit_value = ($user_limit === null) ? '' : (string)$user_limit;
?>

<div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
        <label
            class="aipkit_popover_option_label"
            for="aipkit_image_token_guest_limit"
            data-tooltip="<?php echo esc_attr__('0 = disabled.', 'gpt3-ai-content-generator'); ?>"
        >
            <?php esc_html_e('Guest limit', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="number"
            id="aipkit_image_token_guest_limit"
            name="image_token_guest_limit"
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
            for="aipkit_image_token_limit_mode"
            data-tooltip="<?php echo esc_attr__('For logged-in users.', 'gpt3-ai-content-generator'); ?>"
        >
            <?php esc_html_e('User limit type', 'gpt3-ai-content-generator'); ?>
        </label>
        <select
            id="aipkit_image_token_limit_mode"
            name="image_token_limit_mode"
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
            for="aipkit_image_token_user_limit"
            data-tooltip="<?php echo esc_attr__('0 = disabled.', 'gpt3-ai-content-generator'); ?>"
        >
            <?php esc_html_e('General user limit', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="number"
            id="aipkit_image_token_user_limit"
            name="image_token_user_limit"
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
            for="aipkit_image_token_reset_period"
            data-tooltip="<?php echo esc_attr__('How often usage resets.', 'gpt3-ai-content-generator'); ?>"
        >
            <?php esc_html_e('Reset period', 'gpt3-ai-content-generator'); ?>
        </label>
        <select
            id="aipkit_image_token_reset_period"
            name="image_token_reset_period"
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
                        id="aipkit_image_token_role_<?php echo esc_attr($role_slug); ?>"
                        name="image_token_role_limits[<?php echo esc_attr($role_slug); ?>]"
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
            for="aipkit_image_token_limit_message"
            data-tooltip="<?php echo esc_attr__('The message shown to users when they exceed their token limit for the period.', 'gpt3-ai-content-generator'); ?>"
        >
            <?php esc_html_e('Limit message', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_image_token_limit_message"
            name="image_token_limit_message"
            class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--wide aipkit_autosave_trigger"
            value="<?php echo esc_attr($limit_message); ?>"
            placeholder="<?php echo esc_attr($default_limit_message); ?>"
        />
    </div>
</div>
