<?php
/**
 * Partial: Automated Task Form - Comment Reply AI & Prompt Configuration
 * This is the content pane for the "AI & Prompt" step in the wizard.
 *
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div id="aipkit_task_config_comment_reply_ai_main" class="aipkit_task_config_section">
    <div class="aipkit_autogpt_setting_cards">
        <?php
        $comment_reply_ai_settings_partial = __DIR__ . '/community-engagement/ai-settings.php';
        $aipkit_ai_card_id = 'aipkit_autogpt_cc_ai_settings_popover';
        $aipkit_ai_popover_body_path = $comment_reply_ai_settings_partial;
        $aipkit_ai_provider_selector = '#aipkit_task_cc_ai_provider';
        $aipkit_ai_model_selector = '#aipkit_task_cc_ai_model';
        include __DIR__ . '/shared/ai-settings-card.php';

        $aipkit_reply_prompt_flyout_id = 'aipkit_task_cc_reply_prompt_flyout';
        ?>
        <button
            type="button"
            class="aipkit_cw_setting_chip aipkit_cw_setting_chip--featured"
            data-aipkit-flyout-target="<?php echo esc_attr($aipkit_reply_prompt_flyout_id); ?>"
            aria-controls="<?php echo esc_attr($aipkit_reply_prompt_flyout_id); ?>"
            aria-expanded="false"
        >
            <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--prompts">
                <span class="dashicons dashicons-edit-large" aria-hidden="true"></span>
            </span>
            <span class="aipkit_cw_setting_chip_content">
                <span class="aipkit_cw_setting_chip_label"><?php esc_html_e('Reply Prompt', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_setting_chip_value" data-aipkit-placeholder="<?php echo esc_attr__('Customize', 'gpt3-ai-content-generator'); ?>" data-aipkit-summary-state="placeholder">
                    <?php esc_html_e('Customize', 'gpt3-ai-content-generator'); ?>
                </span>
            </span>
        </button>

        <?php include __DIR__ . '/community-engagement/prompts-settings.php'; ?>
    </div>
</div>
