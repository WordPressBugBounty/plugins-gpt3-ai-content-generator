<?php
/**
 * Partial: Content Assistant Settings
 * 
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables from parent: $enhancer_editor_integration_enabled
$aipkit_render_assistant_accordion = isset($aipkit_render_assistant_accordion)
    ? (bool) $aipkit_render_assistant_accordion
    : true;
$aipkit_render_assistant_footer = isset($aipkit_render_assistant_footer)
    ? (bool) $aipkit_render_assistant_footer
    : true;

$pos_current = isset($enhancer_default_insert_position) ? sanitize_key($enhancer_default_insert_position) : 'replace';
$aipkit_options = get_option('aipkit_options', []);
$enhancer_list_button_enabled = $aipkit_options['enhancer_settings']['show_list_button'] ?? '1';
?>

<?php if ($aipkit_render_assistant_accordion) : ?>
<div class="aipkit_accordion">
    <div class="aipkit_accordion-header">
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <?php esc_html_e('Content Assistant', 'gpt3-ai-content-generator'); ?>
    </div>
    <div class="aipkit_accordion-content">
<?php endif; ?>

<div class="aipkit_assistant_settings_redesigned">

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 1: Quick Settings (Primary - Always Visible)
         Following Choice Architecture: Most important decisions first
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_popover_options_list">
        <div class="aipkit_popover_option_group">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_popover_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_enhancer_editor_integration">
                            <?php esc_html_e('Enable in Editors', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('Show Content Assistant in Classic and Block editors', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <label class="aipkit_switch">
                        <input
                            type="checkbox"
                            id="aipkit_enhancer_editor_integration"
                            name="enhancer_editor_integration"
                            class="aipkit_autosave_trigger"
                            value="1"
                            <?php checked($enhancer_editor_integration_enabled, '1'); ?>
                        >
                        <span class="aipkit_switch_slider"></span>
                    </label>
                </div>
            </div>

            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_popover_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_enhancer_list_button">
                            <?php esc_html_e('Show on Post Lists', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('Show the Content Assistant button next to “Add New” on post, page, and product lists.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <label class="aipkit_switch">
                        <input
                            type="checkbox"
                            id="aipkit_enhancer_list_button"
                            name="enhancer_list_button"
                            class="aipkit_autosave_trigger"
                            value="1"
                            <?php checked($enhancer_list_button_enabled, '1'); ?>
                        >
                        <span class="aipkit_switch_slider"></span>
                    </label>
                </div>
            </div>

            <div class="aipkit_popover_option_row aipkit_popover_option_row--force-divider">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_popover_option_text">
                        <label class="aipkit_popover_option_label">
                            <?php esc_html_e('Insert Position', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('Choose where assistant output is inserted.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <div class="aipkit_assistant_position_options">
                        <label class="aipkit_assistant_position_option">
                            <input
                                type="radio"
                                name="enhancer_insert_position_default"
                                value="replace"
                                class="aipkit_autosave_trigger"
                                <?php checked($pos_current, 'replace'); ?>
                            >
                            <span class="aipkit_assistant_position_icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 8h12"></path>
                                    <path d="M6 16h12"></path>
                                    <path d="M9 5l-3 3 3 3"></path>
                                    <path d="M15 19l3-3-3-3"></path>
                                </svg>
                            </span>
                            <span class="aipkit_assistant_position_name">
                                <?php esc_html_e('Replace', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </label>
                        <label class="aipkit_assistant_position_option">
                            <input
                                type="radio"
                                name="enhancer_insert_position_default"
                                value="before"
                                class="aipkit_autosave_trigger"
                                <?php checked($pos_current, 'before'); ?>
                            >
                            <span class="aipkit_assistant_position_icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 12h12"></path>
                                    <path d="M10 8l-4 4 4 4"></path>
                                </svg>
                            </span>
                            <span class="aipkit_assistant_position_name">
                                <?php esc_html_e('Before', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </label>
                        <label class="aipkit_assistant_position_option">
                            <input
                                type="radio"
                                name="enhancer_insert_position_default"
                                value="after"
                                class="aipkit_autosave_trigger"
                                <?php checked($pos_current, 'after'); ?>
                            >
                            <span class="aipkit_assistant_position_icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 12h12"></path>
                                    <path d="M14 8l4 4-4 4"></path>
                                </svg>
                            </span>
                            <span class="aipkit_assistant_position_name">
                                <?php esc_html_e('After', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 2: AI Actions Management
         Following Chunking Principle: Separate complex configuration area
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_assistant_actions_section">
        <div class="aipkit_assistant_actions_toolbar">
            <button type="button" class="aipkit_assistant_action_btn aipkit_assistant_action_btn--primary aipkit-enhancer-add-new-btn">
                <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                <span><?php esc_html_e('Add', 'gpt3-ai-content-generator'); ?></span>
            </button>
        </div>
        <div id="aipkit_enhancer_actions_container" class="aipkit_assistant_actions_container">
            <!-- Actions rendered as cards instead of table for better UX -->
            <div id="aipkit_enhancer_actions_list" class="aipkit_assistant_actions_list">
                <!-- JS will render action cards here -->
            </div>
        </div>
    </div>

    <?php if ($aipkit_render_assistant_footer) : ?>
        <div class="aipkit_model_settings_popover_footer aipkit_model_settings_popover_footer--right">
            <button
                type="button"
                class="aipkit_popover_footer_btn aipkit_popover_footer_btn--secondary aipkit-enhancer-reset-actions-btn"
            >
                <?php esc_html_e('Reset', 'gpt3-ai-content-generator'); ?>
            </button>
        </div>
    <?php endif; ?>

</div>

<?php if ($aipkit_render_assistant_accordion) : ?>
    </div>
</div>
<?php endif; ?>
