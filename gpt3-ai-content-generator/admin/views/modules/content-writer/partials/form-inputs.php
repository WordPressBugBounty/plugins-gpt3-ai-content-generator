<?php

if (!defined('ABSPATH')) {
    exit;
}

// Load shared variables used by the partials
require_once __DIR__ . '/form-inputs/loader-vars.php';

$aipkit_options = get_option('aipkit_options', []);
$enhancer_editor_integration_enabled = $aipkit_options['enhancer_settings']['editor_integration'] ?? '1';
$enhancer_default_insert_position = $aipkit_options['enhancer_settings']['default_insert_position'] ?? 'replace';
$aipkit_render_assistant_accordion = false;
$aipkit_render_assistant_footer = true;
$content_length_options = [
    'short' => __('Short', 'gpt3-ai-content-generator'),
    'medium' => __('Medium', 'gpt3-ai-content-generator'),
    'long' => __('Long', 'gpt3-ai-content-generator'),
];

?>
<div class="aipkit_cw_inspector_stack">
    <section class="aipkit_cw_inspector_card aipkit_cw_inspector_card--run">
        <div class="aipkit_cw_inspector_card_header">
            <div class="aipkit_cw_inspector_card_header_copy">
                <div class="aipkit_cw_inspector_card_title">
                    <?php esc_html_e('Setup', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>
        </div>
        <div class="aipkit_cw_inspector_card_body aipkit_cw_inspector_card_body--run">
            <?php include __DIR__ . '/template-controls.php'; ?>
            <?php include __DIR__ . '/form-inputs/ai-settings.php'; ?>
            <div class="aipkit_cw_ai_row">
                <div class="aipkit_cw_panel_label_wrap">
                    <label class="aipkit_cw_panel_label" for="aipkit_content_writer_content_length">
                        <?php esc_html_e('Length', 'gpt3-ai-content-generator'); ?>
                    </label>
                </div>
                <div class="aipkit_cw_ai_control aipkit_cw_ai_control--compact">
                    <select
                        id="aipkit_content_writer_content_length"
                        name="content_length"
                        class="aipkit_autosave_trigger aipkit_form-input aipkit_cw_blended_chevron_select"
                    >
                        <?php foreach ($content_length_options as $content_length_value => $content_length_label) : ?>
                            <option value="<?php echo esc_attr($content_length_value); ?>" <?php selected($content_length_value, 'medium'); ?>>
                                <?php echo esc_html($content_length_label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php include __DIR__ . '/form-inputs/publishing-settings.php'; ?>
            <?php include __DIR__ . '/form-inputs/prompts-settings.php'; ?>
        </div>
    </section>

    <section class="aipkit_cw_inspector_card aipkit_cw_inspector_card--media">
        <div class="aipkit_cw_inspector_card_header">
            <div class="aipkit_cw_inspector_card_header_copy">
                <div class="aipkit_cw_inspector_card_title">
                    <?php esc_html_e('Media', 'gpt3-ai-content-generator'); ?>
                </div>
            </div>
        </div>
        <div class="aipkit_cw_inspector_card_body aipkit_cw_inspector_card_body--media">
            <?php include __DIR__ . '/form-inputs/image-settings.php'; ?>
        </div>
    </section>
</div>

<div
    class="aipkit_builder_sheet_overlay"
    id="aipkit_cw_builder_sheet"
    aria-hidden="true"
>
    <div
        class="aipkit_builder_sheet_panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="aipkit_cw_builder_sheet_title"
        aria-describedby="aipkit_cw_builder_sheet_description"
    >
        <div class="aipkit_builder_sheet_header">
            <div>
                <div class="aipkit_builder_sheet_title_row">
                    <h3 class="aipkit_builder_sheet_title" id="aipkit_cw_builder_sheet_title">
                        <?php esc_html_e('Sheet', 'gpt3-ai-content-generator'); ?>
                    </h3>
                </div>
                <p class="aipkit_builder_sheet_description" id="aipkit_cw_builder_sheet_description">
                    <?php esc_html_e('Settings will appear here.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
            <button
                type="button"
                class="aipkit_builder_sheet_close"
                aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit_builder_sheet_body">
            <div class="aipkit_builder_sheet_section" data-sheet="placeholder">
                <p class="aipkit_builder_help_text">
                    <?php esc_html_e('This panel will contain the selected settings section.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
            <div class="aipkit_builder_sheet_section" data-sheet="assistant" hidden>
                <div id="aipkit_settings_container" data-aipkit-assistant-settings="true">
                    <?php include WPAICG_PLUGIN_DIR . 'admin/views/modules/settings/partials/integrations/ai-assistant.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>
