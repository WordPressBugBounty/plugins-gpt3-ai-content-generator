<?php
/**
 * Partial: AutoGPT AI Settings Card + Popover Wrapper
 *
 * Expected variables:
 * - $aipkit_ai_card_id (string)
 * - $aipkit_ai_popover_body_path (string)
 * - $aipkit_ai_provider_selector (string) Optional, for summary updates
 * - $aipkit_ai_model_selector (string) Optional, for summary updates
 * - $aipkit_ai_label (string) Optional
 * - $aipkit_ai_placeholder (string) Optional
 * - $aipkit_ai_popover_label (string) Optional
 * - $aipkit_ai_popover_placement (string) Optional
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_ai_card_id = isset($aipkit_ai_card_id) ? (string) $aipkit_ai_card_id : '';
$aipkit_ai_popover_body_path = isset($aipkit_ai_popover_body_path) ? (string) $aipkit_ai_popover_body_path : '';

if ($aipkit_ai_card_id === '' || $aipkit_ai_popover_body_path === '') {
    return;
}

$aipkit_ai_label = isset($aipkit_ai_label) ? (string) $aipkit_ai_label : __('Model', 'gpt3-ai-content-generator');
$aipkit_ai_placeholder = isset($aipkit_ai_placeholder) ? (string) $aipkit_ai_placeholder : __('Select', 'gpt3-ai-content-generator');
$aipkit_ai_popover_label = isset($aipkit_ai_popover_label) ? (string) $aipkit_ai_popover_label : __('AI Settings', 'gpt3-ai-content-generator');
$aipkit_ai_popover_placement = isset($aipkit_ai_popover_placement) ? (string) $aipkit_ai_popover_placement : 'left';
$aipkit_ai_provider_selector = isset($aipkit_ai_provider_selector) ? (string) $aipkit_ai_provider_selector : '';
$aipkit_ai_model_selector = isset($aipkit_ai_model_selector) ? (string) $aipkit_ai_model_selector : '';
?>
<button
    type="button"
    class="aipkit_cw_setting_chip aipkit_cw_popover_trigger"
    data-aipkit-popover-target="<?php echo esc_attr($aipkit_ai_card_id); ?>"
    data-aipkit-popover-placement="<?php echo esc_attr($aipkit_ai_popover_placement); ?>"
    aria-controls="<?php echo esc_attr($aipkit_ai_card_id); ?>"
    aria-expanded="false"
>
    <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--primary">
        <span class="dashicons dashicons-superhero" aria-hidden="true"></span>
    </span>
    <span class="aipkit_cw_setting_chip_content">
        <span class="aipkit_cw_setting_chip_label"><?php echo esc_html($aipkit_ai_label); ?></span>
        <span
            class="aipkit_cw_setting_chip_value"
            data-aipkit-autogpt-summary="ai"
            data-aipkit-autogpt-provider="<?php echo esc_attr($aipkit_ai_provider_selector); ?>"
            data-aipkit-autogpt-model="<?php echo esc_attr($aipkit_ai_model_selector); ?>"
            data-aipkit-placeholder="<?php echo esc_attr($aipkit_ai_placeholder); ?>"
        >
            <?php echo esc_html($aipkit_ai_placeholder); ?>
        </span>
    </span>
</button>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="<?php echo esc_attr($aipkit_ai_card_id); ?>" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel" role="dialog" aria-label="<?php echo esc_attr($aipkit_ai_popover_label); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include $aipkit_ai_popover_body_path; ?>
        </div>
    </div>
</div>
