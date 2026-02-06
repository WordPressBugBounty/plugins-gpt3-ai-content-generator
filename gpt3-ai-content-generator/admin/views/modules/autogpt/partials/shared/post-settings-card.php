<?php
/**
 * Partial: AutoGPT Post Settings Card + Popover Wrapper
 *
 * Expected variables:
 * - $aipkit_post_settings_card_id (string)
 * - $aipkit_post_settings_popover_body_path (string)
 * - $aipkit_post_settings_label (string) Optional
 * - $aipkit_post_settings_placeholder (string) Optional
 * - $aipkit_post_settings_popover_label (string) Optional
 * - $aipkit_post_settings_popover_placement (string) Optional
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_post_settings_card_id = isset($aipkit_post_settings_card_id) ? (string) $aipkit_post_settings_card_id : '';
$aipkit_post_settings_popover_body_path = isset($aipkit_post_settings_popover_body_path) ? (string) $aipkit_post_settings_popover_body_path : '';

if ($aipkit_post_settings_card_id === '' || $aipkit_post_settings_popover_body_path === '') {
    return;
}

if (!file_exists($aipkit_post_settings_popover_body_path)) {
    return;
}

$aipkit_post_settings_label = isset($aipkit_post_settings_label) ? (string) $aipkit_post_settings_label : __('Post Settings', 'gpt3-ai-content-generator');
$aipkit_post_settings_placeholder = isset($aipkit_post_settings_placeholder) ? (string) $aipkit_post_settings_placeholder : __('Configure', 'gpt3-ai-content-generator');
$aipkit_post_settings_popover_label = isset($aipkit_post_settings_popover_label) ? (string) $aipkit_post_settings_popover_label : __('Post Settings', 'gpt3-ai-content-generator');
$aipkit_post_settings_popover_placement = isset($aipkit_post_settings_popover_placement) ? (string) $aipkit_post_settings_popover_placement : 'left';
?>
<button
    type="button"
    class="aipkit_cw_setting_chip aipkit_cw_popover_trigger"
    data-aipkit-popover-target="<?php echo esc_attr($aipkit_post_settings_card_id); ?>"
    data-aipkit-popover-placement="<?php echo esc_attr($aipkit_post_settings_popover_placement); ?>"
    aria-controls="<?php echo esc_attr($aipkit_post_settings_card_id); ?>"
    aria-expanded="false"
>
    <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--primary">
        <span class="dashicons dashicons-admin-post" aria-hidden="true"></span>
    </span>
    <span class="aipkit_cw_setting_chip_content">
        <span class="aipkit_cw_setting_chip_label"><?php echo esc_html($aipkit_post_settings_label); ?></span>
        <span class="aipkit_cw_setting_chip_value" data-aipkit-placeholder="<?php echo esc_attr($aipkit_post_settings_placeholder); ?>" data-aipkit-summary-state="placeholder">
            <?php echo esc_html($aipkit_post_settings_placeholder); ?>
        </span>
    </span>
</button>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="<?php echo esc_attr($aipkit_post_settings_card_id); ?>" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel" role="dialog" aria-label="<?php echo esc_attr($aipkit_post_settings_popover_label); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include $aipkit_post_settings_popover_body_path; ?>
        </div>
    </div>
</div>
