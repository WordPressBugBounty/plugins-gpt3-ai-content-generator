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

?>
<?php include __DIR__ . '/template-controls.php'; ?>

<?php include __DIR__ . '/form-inputs/ai-settings.php'; ?>

<section class="aipkit_cw_inline_section aipkit_cw_inline_section--images">
    <?php include __DIR__ . '/form-inputs/image-settings.php'; ?>
</section>

<section class="aipkit_cw_inline_section aipkit_cw_inline_section--prompts">
    <?php include __DIR__ . '/form-inputs/prompts-settings.php'; ?>
</section>

<section class="aipkit_cw_inline_section aipkit_cw_inline_section--publishing">
    <?php include __DIR__ . '/form-inputs/publishing-settings.php'; ?>
</section>

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
