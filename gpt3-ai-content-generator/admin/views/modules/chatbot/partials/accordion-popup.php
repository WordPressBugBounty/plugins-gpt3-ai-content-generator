<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/chatbot/partials/accordion-popup.php
// UPDATED FILE - Added Popup Icon Settings

/**
 * Partial: Chatbot Popup Settings Accordion Content
 *
 * Contains settings related to popup display behavior AND the popup icon.
 * This entire accordion is conditionally displayed based on the 'Popup Mode' checkbox in the Appearance accordion.
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotSettingsManager; // Use for defaults
// --- ADDED: Use statement for the new SVG utility class (ensure it's loaded via Initializer) ---
use WPAICG\Chat\Utils\AIPKit_SVG_Icons;

// --- END ADDED ---

// Variables available from parent script:
// $bot_id, $bot_settings
$popup_position     = isset($bot_settings['popup_position']) ? $bot_settings['popup_position'] : 'bottom-right';
$popup_delay        = isset($bot_settings['popup_delay']) ? absint($bot_settings['popup_delay']) : BotSettingsManager::DEFAULT_POPUP_DELAY;
$site_wide_enabled  = isset($bot_settings['site_wide_enabled']) ? $bot_settings['site_wide_enabled'] : '0';

// NEW: Popup Icon Settings values needed here
$popup_icon_type = $bot_settings['popup_icon_type'] ?? BotSettingsManager::DEFAULT_POPUP_ICON_TYPE;
$popup_icon_style = $bot_settings['popup_icon_style'] ?? BotSettingsManager::DEFAULT_POPUP_ICON_STYLE;
$popup_icon_value = $bot_settings['popup_icon_value'] ?? BotSettingsManager::DEFAULT_POPUP_ICON_VALUE;

// --- UPDATED: Retrieve SVGs using the utility class ---
$default_icons = [];
if (class_exists(AIPKit_SVG_Icons::class)) {
    $default_icons = [
        'chat-bubble' => AIPKit_SVG_Icons::get_chat_bubble_svg(),
        'plus' => AIPKit_SVG_Icons::get_plus_svg(),
        'question-mark' => AIPKit_SVG_Icons::get_question_mark_svg(),
    ];
} else {
    error_log("AIPKit accordion-popup.php Warning: AIPKit_SVG_Icons class not found. Default icons will be missing.");
}
// --- END UPDATE ---

?>
<div class="aipkit_accordion aipkit_popup_settings_accordion" style="display: none;"> <?php // Hidden by default, JS controls visibility?>
    <div class="aipkit_accordion-header">
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <?php esc_html_e('Popup', 'gpt3-ai-content-generator'); ?>
    </div>
    <div class="aipkit_accordion-content">

        <!-- Popup Position Dropdown & Popup Delay -->
        <div
            class="aipkit_form-row aipkit_form-row-align-bottom"
        >
             <!-- Position -->
            <div class="aipkit_form-group aipkit_form-col aipkit_popup_position_group" style="flex: 0 0 180px;"> <?php // Fixed width?>
                <label
                    class="aipkit_form-label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_position"
                >
                    <?php esc_html_e('Popup Position', 'gpt3-ai-content-generator'); ?>
                </label>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_position"
                    name="popup_position"
                    class="aipkit_form-input"
                >
                    <option value="bottom-right" <?php selected($popup_position, 'bottom-right'); ?>>
                        <?php esc_html_e('Bottom Right', 'gpt3-ai-content-generator'); ?>
                    </option>
                    <option value="bottom-left" <?php selected($popup_position, 'bottom-left'); ?>>
                        <?php esc_html_e('Bottom Left', 'gpt3-ai-content-generator'); ?>
                    </option>
                    <option value="top-right" <?php selected($popup_position, 'top-right'); ?>>
                        <?php esc_html_e('Top Right', 'gpt3-ai-content-generator'); ?>
                    </option>
                    <option value="top-left" <?php selected($popup_position, 'top-left'); ?>>
                        <?php esc_html_e('Top Left', 'gpt3-ai-content-generator'); ?>
                    </option>
                </select>
            </div>
             <!-- Delay -->
             <div class="aipkit_form-group aipkit_form-col" style="flex: 0 0 150px;"> <?php // Fixed width?>
                 <label
                    class="aipkit_form-label"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_delay"
                >
                    <?php esc_html_e('Popup Delay (sec)', 'gpt3-ai-content-generator'); ?>
                </label>
                <input
                    type="number"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_delay"
                    name="popup_delay"
                    class="aipkit_form-input"
                    value="<?php echo esc_attr($popup_delay); ?>"
                    min="0"
                    step="1"
                />
            </div>
        </div>
        <!-- Help Text for Popup Settings -->
        <div
            class="aipkit_form-help"
            style="margin-top: 5px;"
        >
             <?php esc_html_e('Choose where the popup icon will appear and the delay before it automatically opens (0 = disabled).', 'gpt3-ai-content-generator'); ?>
        </div>

        <hr class="aipkit_hr">

        <!-- Site-wide Popup Checkbox -->
        <div
            class="aipkit_form-group aipkit_site_wide_group"
        >
            <label
                class="aipkit_form-label aipkit_checkbox-label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_site_wide_enabled"
            >
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_site_wide_enabled"
                    name="site_wide_enabled"
                    class="aipkit_toggle_switch" <?php // Standard toggle style?>
                    value="1"
                    <?php checked($site_wide_enabled, '1'); ?>
                >
                <?php esc_html_e('Site-wide', 'gpt3-ai-content-generator'); ?>
            </label>
             <div class="aipkit_form-help">
                <?php esc_html_e('Inject this popup globally. Only one bot can be site-wide.', 'gpt3-ai-content-generator'); ?>
            </div>
        </div>

        <!-- NEW: Moved Popup Icon Settings Here -->
        <div
            class="aipkit_popup_icon_options_container"
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_settings"
             <?php // No inline style needed here, visibility controlled by parent accordion?>
        >
            <hr class="aipkit_hr"> <?php // Add separator above?>
            <h6><?php esc_html_e('Popup Icon', 'gpt3-ai-content-generator'); ?></h6>

            <div class="aipkit_form-row aipkit_form-row-align-bottom">
                <div class="aipkit_form-group aipkit_form-col">
                    <label class="aipkit_form-label" for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_style"><?php esc_html_e('Icon Style', 'gpt3-ai-content-generator'); ?></label>
                    <select id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_style" name="popup_icon_style" class="aipkit_form-input">
                        <option value="circle" <?php selected($popup_icon_style, 'circle'); ?>><?php esc_html_e('Circle', 'gpt3-ai-content-generator'); ?></option>
                        <option value="square" <?php selected($popup_icon_style, 'square'); ?>><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                        <option value="none" <?php selected($popup_icon_style, 'none'); ?>><?php esc_html_e('None (Original Image)', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                    <div class="aipkit_form-help"><?php esc_html_e('Choose the shape and background of the icon.', 'gpt3-ai-content-generator'); ?></div>
                </div>
            </div>

            <div class="aipkit_form-group aipkit_popup_icon_type_selector">
                <label>
                    <input type="radio" name="popup_icon_type" value="default" <?php checked($popup_icon_type, 'default'); ?>>
                    <?php esc_html_e('Default Icons', 'gpt3-ai-content-generator'); ?>
                </label>
                <label>
                    <input type="radio" name="popup_icon_type" value="custom" <?php checked($popup_icon_type, 'custom'); ?>>
                    <?php esc_html_e('Custom URL', 'gpt3-ai-content-generator'); ?>
                </label>
            </div>

            <!-- Default Icon Selector -->
            <div
                class="aipkit_popup_icon_default_selector_container"
                style="display: <?php echo $popup_icon_type === 'default' ? 'block' : 'none'; ?>;"
            >
                <div class="aipkit_popup_icon_default_selector">
                    <?php
                    foreach ($default_icons as $icon_key => $svg_html) :
                        $icon_checked = ($popup_icon_type === 'default' && $popup_icon_value === $icon_key);
                        $radio_id = 'aipkit_bot_' . esc_attr($bot_id) . '_popup_icon_' . esc_attr($icon_key);
                        ?>
                    <label class="aipkit_popup_icon_default_option" for="<?php echo $radio_id; ?>" title="<?php echo esc_attr(ucfirst(str_replace('-', ' ', $icon_key))); ?>">
                        <input
                            type="radio"
                            id="<?php echo $radio_id; ?>"
                            name="popup_icon_default"
                            value="<?php echo esc_attr($icon_key); ?>"
                            <?php checked($icon_checked); ?>
                        />
                        <?php // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Local, safe SVG string?>
                        <?php echo $svg_html; ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <div class="aipkit_form-help">
                    <?php esc_html_e('Select a default icon for the popup trigger button.', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>

            <!-- Custom Icon URL Input -->
            <div
                class="aipkit_popup_icon_custom_input_container"
                style="display: <?php echo $popup_icon_type === 'custom' ? 'block' : 'none'; ?>;"
            >
                <div class="aipkit_popup_icon_custom_input aipkit_form-group">
                    <label
                        class="aipkit_form-label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_custom_url"
                        style="display: none;" <?php // Hide label, implied by context?>
                    >
                        <?php esc_html_e('Custom Icon URL', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input
                        type="url"
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_custom_url"
                        name="popup_icon_custom_url"
                        class="aipkit_form-input"
                        value="<?php echo ($popup_icon_type === 'custom') ? esc_url($popup_icon_value) : ''; ?>"
                        placeholder="<?php esc_attr_e('Enter full URL (e.g., https://.../icon.png)', 'gpt3-ai-content-generator'); ?>"
                    />
                    <?php /* Optional: Add Media Library Button Here Later */ ?>
                    <?php /* <button type="button" class="aipkit_btn aipkit_btn-secondary aipkit_popup_icon_upload_btn">Upload</button> */ ?>
                </div>
                 <div class="aipkit_form-help">
                     <?php esc_html_e('Enter the full URL of your custom icon image (e.g., PNG, SVG). Recommended size 32x32.', 'gpt3-ai-content-generator'); ?>
                 </div>
            </div>

        </div>
        <!-- END NEW: Moved Popup Icon Settings Here -->

    </div>
</div>