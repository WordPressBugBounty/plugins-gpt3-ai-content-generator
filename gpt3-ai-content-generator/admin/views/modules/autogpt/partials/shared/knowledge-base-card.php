<?php
/**
 * Partial: AutoGPT Knowledge Base Settings Card + Popover Wrapper
 *
 * Expected variables:
 * - $aipkit_kb_card_id (string)
 * - $aipkit_kb_popover_body_path (string)
 * - $aipkit_kb_label (string) Optional
 * - $aipkit_kb_placeholder (string) Optional
 * - $aipkit_kb_popover_label (string) Optional
 * - $aipkit_kb_popover_placement (string) Optional
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_kb_card_id = isset($aipkit_kb_card_id) ? (string) $aipkit_kb_card_id : '';
$aipkit_kb_popover_body_path = isset($aipkit_kb_popover_body_path) ? (string) $aipkit_kb_popover_body_path : '';

if ($aipkit_kb_card_id === '' || $aipkit_kb_popover_body_path === '') {
    return;
}

if (!file_exists($aipkit_kb_popover_body_path)) {
    return;
}

$aipkit_kb_label = isset($aipkit_kb_label) ? (string) $aipkit_kb_label : __('Knowledge Base', 'gpt3-ai-content-generator');
$aipkit_kb_placeholder = isset($aipkit_kb_placeholder) ? (string) $aipkit_kb_placeholder : __('Off', 'gpt3-ai-content-generator');
$aipkit_kb_popover_label = isset($aipkit_kb_popover_label) ? (string) $aipkit_kb_popover_label : __('Knowledge Base', 'gpt3-ai-content-generator');
$aipkit_kb_popover_placement = isset($aipkit_kb_popover_placement) ? (string) $aipkit_kb_popover_placement : 'left';
$aipkit_kb_provider_selector = isset($aipkit_kb_provider_selector) ? (string) $aipkit_kb_provider_selector : '';
$aipkit_kb_openai_store_selector = isset($aipkit_kb_openai_store_selector) ? (string) $aipkit_kb_openai_store_selector : '';
$aipkit_kb_pinecone_selector = isset($aipkit_kb_pinecone_selector) ? (string) $aipkit_kb_pinecone_selector : '';
$aipkit_kb_qdrant_selector = isset($aipkit_kb_qdrant_selector) ? (string) $aipkit_kb_qdrant_selector : '';
$aipkit_kb_store_selector = isset($aipkit_kb_store_selector) ? (string) $aipkit_kb_store_selector : '';
$aipkit_kb_toggle_selector = isset($aipkit_kb_toggle_selector) ? (string) $aipkit_kb_toggle_selector : '';
?>
<button
    type="button"
    class="aipkit_cw_setting_chip aipkit_cw_popover_trigger"
    data-aipkit-popover-target="<?php echo esc_attr($aipkit_kb_card_id); ?>"
    data-aipkit-popover-placement="<?php echo esc_attr($aipkit_kb_popover_placement); ?>"
    aria-controls="<?php echo esc_attr($aipkit_kb_card_id); ?>"
    aria-expanded="false"
>
    <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--vector">
        <span class="dashicons dashicons-database" aria-hidden="true"></span>
    </span>
    <span class="aipkit_cw_setting_chip_content">
        <span class="aipkit_cw_setting_chip_label"><?php echo esc_html($aipkit_kb_label); ?></span>
        <span
            class="aipkit_cw_setting_chip_value"
            data-aipkit-autogpt-summary="vector"
            data-aipkit-autogpt-provider="<?php echo esc_attr($aipkit_kb_provider_selector); ?>"
            data-aipkit-autogpt-openai-store="<?php echo esc_attr($aipkit_kb_openai_store_selector); ?>"
            data-aipkit-autogpt-pinecone-store="<?php echo esc_attr($aipkit_kb_pinecone_selector); ?>"
            data-aipkit-autogpt-qdrant-store="<?php echo esc_attr($aipkit_kb_qdrant_selector); ?>"
            data-aipkit-autogpt-store="<?php echo esc_attr($aipkit_kb_store_selector); ?>"
            data-aipkit-autogpt-toggle="<?php echo esc_attr($aipkit_kb_toggle_selector); ?>"
            data-aipkit-placeholder="<?php echo esc_attr($aipkit_kb_placeholder); ?>"
            data-aipkit-disabled-label="<?php echo esc_attr($aipkit_kb_placeholder); ?>"
            data-aipkit-summary-state="placeholder"
        >
            <?php echo esc_html($aipkit_kb_placeholder); ?>
        </span>
    </span>
</button>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="<?php echo esc_attr($aipkit_kb_card_id); ?>" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_model_settings_popover_panel--allow-overflow aipkit_cw_settings_popover_panel" role="dialog" aria-label="<?php echo esc_attr($aipkit_kb_popover_label); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include $aipkit_kb_popover_body_path; ?>
        </div>
    </div>
</div>
