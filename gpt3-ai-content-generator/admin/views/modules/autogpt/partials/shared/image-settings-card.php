<?php
/**
 * Partial: AutoGPT Image Settings Card + Popover Wrapper
 *
 * Expected variables:
 * - $aipkit_image_card_id (string)
 * - $aipkit_image_popover_body_path (string)
 * - $aipkit_image_label (string) Optional
 * - $aipkit_image_placeholder (string) Optional
 * - $aipkit_image_popover_label (string) Optional
 * - $aipkit_image_popover_placement (string) Optional
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_image_card_id = isset($aipkit_image_card_id) ? (string) $aipkit_image_card_id : '';
$aipkit_image_popover_body_path = isset($aipkit_image_popover_body_path) ? (string) $aipkit_image_popover_body_path : '';

if ($aipkit_image_card_id === '' || $aipkit_image_popover_body_path === '') {
    return;
}

if (!file_exists($aipkit_image_popover_body_path)) {
    return;
}

$aipkit_image_label = isset($aipkit_image_label) ? (string) $aipkit_image_label : __('Images', 'gpt3-ai-content-generator');
$aipkit_image_placeholder = isset($aipkit_image_placeholder) ? (string) $aipkit_image_placeholder : __('Off', 'gpt3-ai-content-generator');
$aipkit_image_popover_label = isset($aipkit_image_popover_label) ? (string) $aipkit_image_popover_label : __('Image Settings', 'gpt3-ai-content-generator');
$aipkit_image_popover_placement = isset($aipkit_image_popover_placement) ? (string) $aipkit_image_popover_placement : 'left';
?>
<button
    type="button"
    class="aipkit_cw_setting_chip aipkit_cw_popover_trigger"
    data-aipkit-popover-target="<?php echo esc_attr($aipkit_image_card_id); ?>"
    data-aipkit-popover-placement="<?php echo esc_attr($aipkit_image_popover_placement); ?>"
    aria-controls="<?php echo esc_attr($aipkit_image_card_id); ?>"
    aria-expanded="false"
>
    <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--images">
        <span class="dashicons dashicons-format-image" aria-hidden="true"></span>
    </span>
    <span class="aipkit_cw_setting_chip_content">
        <span class="aipkit_cw_setting_chip_label"><?php echo esc_html($aipkit_image_label); ?></span>
        <span
            class="aipkit_cw_setting_chip_value"
            data-aipkit-autogpt-summary="images"
            data-aipkit-autogpt-provider="#aipkit_task_cw_image_provider"
            data-aipkit-autogpt-model="#aipkit_task_cw_image_model"
            data-aipkit-autogpt-toggle="#aipkit_task_cw_generate_images_enabled"
            data-aipkit-autogpt-featured-toggle="#aipkit_task_cw_generate_featured_image"
            data-aipkit-placeholder="<?php echo esc_attr($aipkit_image_placeholder); ?>"
            data-aipkit-disabled-label="<?php echo esc_attr($aipkit_image_placeholder); ?>"
            data-aipkit-summary-state="placeholder"
        >
            <?php echo esc_html($aipkit_image_placeholder); ?>
        </span>
    </span>
</button>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="<?php echo esc_attr($aipkit_image_card_id); ?>" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel" role="dialog" aria-label="<?php echo esc_attr($aipkit_image_popover_label); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include $aipkit_image_popover_body_path; ?>
        </div>
    </div>
</div>
