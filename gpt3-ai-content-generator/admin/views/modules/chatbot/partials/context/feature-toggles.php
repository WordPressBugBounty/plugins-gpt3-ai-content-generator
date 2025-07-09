<?php

/**
 * Partial: Appearance - Feature Toggles (Checkboxes)
 * ADDED: Voice Input toggle
 * REVISED: Added specific class to Voice Input toggle
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotSettingsManager;

// Variables available from parent script:
// $bot_id, $bot_settings, $starters_addon_active

$popup_enabled = isset($bot_settings['popup_enabled']) ? $bot_settings['popup_enabled'] : '0';
$enable_fullscreen = isset($bot_settings['enable_fullscreen']) ? $bot_settings['enable_fullscreen'] : '0';
$enable_download = isset($bot_settings['enable_download']) ? $bot_settings['enable_download'] : '0';
$enable_copy_button = isset($bot_settings['enable_copy_button'])
    ? $bot_settings['enable_copy_button']
    : BotSettingsManager::DEFAULT_ENABLE_COPY_BUTTON;
$enable_conversation_starters = isset($bot_settings['enable_conversation_starters'])
    ? $bot_settings['enable_conversation_starters']
    : BotSettingsManager::DEFAULT_ENABLE_CONVERSATION_STARTERS;
$enable_conversation_sidebar = isset($bot_settings['enable_conversation_sidebar'])
    ? $bot_settings['enable_conversation_sidebar']
    : BotSettingsManager::DEFAULT_ENABLE_CONVERSATION_SIDEBAR;
$enable_feedback = isset($bot_settings['enable_feedback'])
    ? $bot_settings['enable_feedback']
    : BotSettingsManager::DEFAULT_ENABLE_FEEDBACK;

$sidebar_disabled_tooltip = __('Sidebar is not available when Popup mode is enabled.', 'gpt3-ai-content-generator');
?>

<!-- 3) Row for Popup / Fullscreen / Download (3 checkboxes) -->
<div class="aipkit_form-row aipkit_checkbox-row">
    <!-- Popup Mode Switch -->
    <div class="aipkit_form-group" style="min-width: 150px;">
        <label
            class="aipkit_form-label aipkit_checkbox-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_enabled"
        >
            <input
                type="checkbox"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_enabled"
                name="popup_enabled"
                class="aipkit_toggle_switch aipkit_popup_toggle_switch"
                value="1"
                data-sidebar-target="aipkit_bot_<?php echo esc_attr($bot_id); ?>_sidebar_group" <?php // Link to sidebar group?>
                <?php checked($popup_enabled, '1'); ?>
            >
            <?php esc_html_e('Popup', 'gpt3-ai-content-generator'); ?>
        </label>
    </div>

    <!-- Fullscreen Checkbox -->
    <div class="aipkit_form-group" style="min-width: 150px;">
        <label
            class="aipkit_form-label aipkit_checkbox-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_fullscreen"
        >
            <input
                type="checkbox"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_fullscreen"
                name="enable_fullscreen"
                class="aipkit_toggle_switch"
                value="1"
                <?php checked($enable_fullscreen, '1'); ?>
            >
            <?php esc_html_e('Fullscreen', 'gpt3-ai-content-generator'); ?>
        </label>
    </div>

    <!-- Download Checkbox -->
    <div class="aipkit_form-group" style="min-width: 150px;">
        <label
            class="aipkit_form-label aipkit_checkbox-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_download"
        >
            <input
                type="checkbox"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_download"
                name="enable_download"
                class="aipkit_toggle_switch"
                value="1"
                <?php checked($enable_download, '1'); ?>
            >
            <?php esc_html_e('Download', 'gpt3-ai-content-generator'); ?>
        </label>
    </div>
</div>

<hr class="aipkit_hr" />

<!-- 4) Row for Copy Button + Starters + Feedback + Sidebar (4 checkboxes) -->
<div class="aipkit_form-row aipkit_checkbox-row">
    <!-- Copy Button Checkbox -->
    <div class="aipkit_form-group" style="min-width: 150px;">
        <label
            class="aipkit_form-label aipkit_checkbox-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_copy_button"
        >
            <input
                type="checkbox"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_copy_button"
                name="enable_copy_button"
                class="aipkit_toggle_switch"
                value="1"
                <?php checked($enable_copy_button, '1'); ?>
            >
            <?php esc_html_e('Copy', 'gpt3-ai-content-generator'); ?>
        </label>
    </div>

    <!-- Feedback Checkbox -->
     <div class="aipkit_form-group" style="min-width: 150px;">
        <label
            class="aipkit_form-label aipkit_checkbox-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_feedback"
        >
            <input
                type="checkbox"
                id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_feedback"
                name="enable_feedback"
                class="aipkit_toggle_switch"
                value="1"
                <?php checked($enable_feedback, '1'); ?>
            >
            <?php esc_html_e('Feedback', 'gpt3-ai-content-generator'); ?>
        </label>
    </div>

    <!-- Conversation Starters Checkbox (only if addon is active) -->
    <?php if ($starters_addon_active): ?>
        <div class="aipkit_form-group" style="min-width: 180px;">
            <label
                class="aipkit_form-label aipkit_checkbox-label"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_starters"
            >
                <input
                    type="checkbox"
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_starters"
                    name="enable_conversation_starters"
                    class="aipkit_toggle_switch aipkit_starters_toggle_switch"
                    value="1"
                    <?php checked($enable_conversation_starters, '1'); ?>
                >
                <?php esc_html_e('Starters', 'gpt3-ai-content-generator'); ?>
            </label>
        </div>
    <?php endif; ?>

     <!-- Conversation Sidebar Checkbox -->
     <div
        class="aipkit_form-group aipkit_sidebar_toggle_group" <?php // Add a group class?>
        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_sidebar_group"
        style="min-width: 150px;"
        title="" <?php // Tooltip added by JS?>
        data-tooltip-disabled="<?php echo esc_attr($sidebar_disabled_tooltip); ?>"
     >
         <label
             class="aipkit_form-label aipkit_checkbox-label"
             for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_sidebar"
         >
             <input
                 type="checkbox"
                 id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_enable_conversation_sidebar"
                 name="enable_conversation_sidebar"
                 class="aipkit_toggle_switch aipkit_sidebar_toggle_switch"
                 value="1"
                 <?php checked($enable_conversation_sidebar, '1'); ?>
                 <?php // Disable if popup is enabled initially - JS will manage this?>
                 <?php disabled($popup_enabled === '1'); ?>
             >
             <?php esc_html_e('Sidebar', 'gpt3-ai-content-generator'); ?>
         </label>
     </div>
</div>