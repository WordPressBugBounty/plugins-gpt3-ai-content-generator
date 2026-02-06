<?php
/**
 * Partial: AutoGPT Prompts Settings Card + Popover Wrapper
 *
 * Expected variables:
 * - $aipkit_prompts_card_id (string)
 * - $aipkit_prompts_popover_body_path (string)
 * - $aipkit_prompts_label (string) Optional
 * - $aipkit_prompts_placeholder (string) Optional
 * - $aipkit_prompts_popover_label (string) Optional
 * - $aipkit_prompts_popover_placement (string) Optional
 * - $aipkit_prompts_featured (bool) Optional
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_prompts_card_id = isset($aipkit_prompts_card_id) ? (string) $aipkit_prompts_card_id : '';
$aipkit_prompts_popover_body_path = isset($aipkit_prompts_popover_body_path) ? (string) $aipkit_prompts_popover_body_path : '';

if ($aipkit_prompts_card_id === '' || $aipkit_prompts_popover_body_path === '') {
    return;
}

if (!file_exists($aipkit_prompts_popover_body_path)) {
    return;
}

$aipkit_prompts_label = isset($aipkit_prompts_label) ? (string) $aipkit_prompts_label : __('Prompts', 'gpt3-ai-content-generator');
$aipkit_prompts_placeholder = isset($aipkit_prompts_placeholder) ? (string) $aipkit_prompts_placeholder : __('Customize', 'gpt3-ai-content-generator');
$aipkit_prompts_popover_label = isset($aipkit_prompts_popover_label) ? (string) $aipkit_prompts_popover_label : __('Prompts', 'gpt3-ai-content-generator');
$aipkit_prompts_popover_placement = isset($aipkit_prompts_popover_placement) ? (string) $aipkit_prompts_popover_placement : 'left';
$aipkit_prompts_featured = isset($aipkit_prompts_featured) ? (bool) $aipkit_prompts_featured : true;
$aipkit_prompts_card_classes = 'aipkit_cw_setting_chip aipkit_cw_popover_trigger';
$aipkit_prompts_card_classes .= $aipkit_prompts_featured ? ' aipkit_cw_setting_chip--featured' : '';
?>
<button
    type="button"
    class="<?php echo esc_attr($aipkit_prompts_card_classes); ?>"
    data-aipkit-popover-target="<?php echo esc_attr($aipkit_prompts_card_id); ?>"
    data-aipkit-popover-placement="<?php echo esc_attr($aipkit_prompts_popover_placement); ?>"
    aria-controls="<?php echo esc_attr($aipkit_prompts_card_id); ?>"
    aria-expanded="false"
>
    <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--prompts">
        <span class="dashicons dashicons-edit-large" aria-hidden="true"></span>
    </span>
    <span class="aipkit_cw_setting_chip_content">
        <span class="aipkit_cw_setting_chip_label"><?php echo esc_html($aipkit_prompts_label); ?></span>
        <span class="aipkit_cw_setting_chip_value" data-aipkit-placeholder="<?php echo esc_attr($aipkit_prompts_placeholder); ?>" data-aipkit-summary-state="placeholder">
            <?php echo esc_html($aipkit_prompts_placeholder); ?>
        </span>
    </span>
</button>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="<?php echo esc_attr($aipkit_prompts_card_id); ?>" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_model_settings_popover_panel--allow-overflow aipkit_cw_settings_popover_panel aipkit_cw_prompts_panel" role="dialog" aria-label="<?php echo esc_attr($aipkit_prompts_popover_label); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body aipkit_cw_prompts_body">
            <?php include $aipkit_prompts_popover_body_path; ?>
        </div>
    </div>
</div>
