<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_cw_update_tip" data-aipkit-cw-mode-only="existing-content" hidden>
    <span class="aipkit_cw_update_tip_icon" aria-hidden="true">
        <span class="dashicons dashicons-lightbulb"></span>
    </span>
    <span class="aipkit_cw_update_tip_text">
        <?php esc_html_e('You can also update existing content directly in the Classic Editor or Gutenberg.', 'gpt3-ai-content-generator'); ?>
        <button
            type="button"
            class="aipkit_cw_tip_link aipkit_builder_sheet_trigger"
            data-sheet-title="<?php echo esc_attr__('Assistant Settings', 'gpt3-ai-content-generator'); ?>"
            data-sheet-description="<?php echo esc_attr__('Configure how the Content Assistant updates existing posts.', 'gpt3-ai-content-generator'); ?>"
            data-sheet-content="assistant"
        >
            <?php esc_html_e('Configure assistant settings.', 'gpt3-ai-content-generator'); ?>
        </button>
    </span>
</div>
