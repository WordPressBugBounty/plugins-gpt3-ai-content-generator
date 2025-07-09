<?php
/**
 * Partial: Chatbot Custom Theme Settings (Main Orchestrator)
 *
 * This file now includes sub-partials for better organization.
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotSettingsManager;

// Variables available from parent script (accordion-appearance.php):
// $bot_id, $bot_settings

$custom_theme_defaults = BotSettingsManager::get_custom_theme_defaults();

// Helper function to get saved value or default
$get_cts_val = function($key) use ($bot_settings, $custom_theme_defaults) {
    $custom_settings = $bot_settings['custom_theme_settings'] ?? [];
    return $custom_settings[$key] ?? ($custom_theme_defaults[$key] ?? '');
};

// Helper to escape attribute values from the custom theme settings
$esc_cts_val_attr = function($key) use ($get_cts_val) {
    return esc_attr($get_cts_val($key));
};

// Font families array, used by general-appearance-settings.php
$font_families = [
    'System' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
    'Arial' => 'Arial, Helvetica, sans-serif',
    'Verdana' => 'Verdana, Geneva, sans-serif',
    'Tahoma' => 'Tahoma, Geneva, sans-serif',
    'Trebuchet MS' => '"Trebuchet MS", Helvetica, sans-serif',
    '"Times New Roman", Times, serif',
    'Georgia' => 'Georgia, serif',
    'Garamond' => 'Garamond, serif',
    '"Courier New", Courier, monospace',
    '"Brush Script MT", cursive',
];

$custom_theme_partials_path = __DIR__ . '/custom-theme/';

?>
<div
    class="aipkit_custom_theme_settings_container"
    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_custom_theme_settings_container"
    style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--aipkit_container-border);"
    data-defaults="<?php echo esc_attr(wp_json_encode($custom_theme_defaults)); ?>"
>
    <?php include $custom_theme_partials_path . 'reset-theme-button.php'; ?>
    <?php include $custom_theme_partials_path . 'general-appearance-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'dimensions-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'container-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'header-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'messages-area-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'bot-bubble-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'user-bubble-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'input-area-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'send-button-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'action-button-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'sidebar-settings.php'; ?>
    <?php include $custom_theme_partials_path . 'footer-settings.php'; ?>
</div>