<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/form-inputs.php
// Status: MODIFIED
/**
 * Partial: Content Writer Form Inputs
 * Redesigned with three core UX principles:
 * 1. Aesthetic - Clean, consistent visual design
 * 2. Choice Overload - Reduce overwhelm by showing focused, manageable groups
 * 3. Chunking - Group related settings visually for better comprehension
 */

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
<div class="aipkit_cw_template_panel">
    <?php include __DIR__ . '/template-controls.php'; ?>
</div>

<!-- Quick Settings - Refined Cards with Visual Hierarchy -->
<div class="aipkit_cw_quick_settings">
    <!-- Primary Setting: AI Model -->
    <button
        type="button"
        class="aipkit_cw_setting_chip aipkit_cw_popover_trigger"
        data-aipkit-popover-target="aipkit_cw_ai_settings_popover"
        data-aipkit-popover-placement="left"
        aria-controls="aipkit_cw_ai_settings_popover"
        aria-expanded="false"
    >
        <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--primary">
            <span class="dashicons dashicons-superhero" aria-hidden="true"></span>
        </span>
        <span class="aipkit_cw_setting_chip_content">
            <span class="aipkit_cw_setting_chip_label"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_cw_setting_chip_value" data-aipkit-cw-summary="ai" data-aipkit-placeholder="<?php esc_attr_e('Select', 'gpt3-ai-content-generator'); ?>">
                <?php esc_html_e('Select', 'gpt3-ai-content-generator'); ?>
            </span>
        </span>
    </button>
    
    <!-- Images -->
    <button
        type="button"
        class="aipkit_cw_setting_chip aipkit_cw_popover_trigger"
        data-aipkit-popover-target="aipkit_cw_image_settings_popover"
        data-aipkit-popover-placement="left"
        aria-controls="aipkit_cw_image_settings_popover"
        aria-expanded="false"
    >
        <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--images">
            <span class="dashicons dashicons-format-image" aria-hidden="true"></span>
        </span>
        <span class="aipkit_cw_setting_chip_content">
            <span class="aipkit_cw_setting_chip_label"><?php esc_html_e('Images', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_cw_setting_chip_value" data-aipkit-cw-summary="images" data-aipkit-placeholder="<?php esc_attr_e('Off', 'gpt3-ai-content-generator'); ?>" data-aipkit-disabled-label="<?php esc_attr_e('Off', 'gpt3-ai-content-generator'); ?>">
                <?php esc_html_e('Off', 'gpt3-ai-content-generator'); ?>
            </span>
        </span>
    </button>
    
    <!-- Knowledge Base -->
    <button
        type="button"
        class="aipkit_cw_setting_chip aipkit_cw_popover_trigger"
        data-aipkit-popover-target="aipkit_cw_vector_settings_popover"
        data-aipkit-popover-placement="left"
        aria-controls="aipkit_cw_vector_settings_popover"
        aria-expanded="false"
    >
        <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--vector">
            <span class="dashicons dashicons-database" aria-hidden="true"></span>
        </span>
        <span class="aipkit_cw_setting_chip_content">
            <span class="aipkit_cw_setting_chip_label"><?php esc_html_e('Knowledge Base', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_cw_setting_chip_value" data-aipkit-cw-summary="vector" data-aipkit-placeholder="<?php esc_attr_e('Select', 'gpt3-ai-content-generator'); ?>" data-aipkit-disabled-label="<?php esc_attr_e('Off', 'gpt3-ai-content-generator'); ?>">
                <?php esc_html_e('Off', 'gpt3-ai-content-generator'); ?>
            </span>
        </span>
    </button>
    
    <!-- Prompts (Featured - Primary Action) -->
    <button
        type="button"
        class="aipkit_cw_setting_chip aipkit_cw_setting_chip--featured aipkit_cw_popover_trigger"
        data-aipkit-popover-target="aipkit_cw_prompts_popover"
        data-aipkit-popover-placement="left"
        aria-controls="aipkit_cw_prompts_popover"
        aria-expanded="false"
    >
        <span class="aipkit_cw_setting_chip_icon aipkit_cw_setting_chip_icon--prompts">
            <span class="dashicons dashicons-edit-large" aria-hidden="true"></span>
        </span>
        <span class="aipkit_cw_setting_chip_content">
            <span class="aipkit_cw_setting_chip_label"><?php esc_html_e('Prompts', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_cw_setting_chip_value" data-aipkit-cw-summary="prompts" data-aipkit-placeholder="<?php esc_attr_e('Customize', 'gpt3-ai-content-generator'); ?>">
                <?php esc_html_e('Customize', 'gpt3-ai-content-generator'); ?>
            </span>
        </span>
    </button>

    <div class="aipkit_cw_update_tip_divider" data-aipkit-cw-mode-only="existing-content" hidden></div>
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
</div>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_cw_ai_settings_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel" role="dialog" aria-label="<?php esc_attr_e('AI Settings', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include __DIR__ . '/form-inputs/ai-settings.php'; ?>
        </div>
    </div>
</div>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_cw_image_settings_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel" role="dialog" aria-label="<?php esc_attr_e('Image Settings', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include __DIR__ . '/form-inputs/image-settings.php'; ?>
        </div>
    </div>
</div>

<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_cw_vector_settings_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel aipkit_model_settings_popover_panel--allow-overflow" role="dialog" aria-label="<?php esc_attr_e('Knowledge Base', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include __DIR__ . '/form-inputs/vector-settings.php'; ?>
        </div>
    </div>
</div>

<!-- Prompts Popover - Redesigned with Chunking Principles -->
<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_cw_prompts_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_model_settings_popover_panel--allow-overflow aipkit_cw_settings_popover_panel aipkit_cw_prompts_panel" role="dialog" aria-label="<?php esc_attr_e('Prompts', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body aipkit_cw_prompts_body">
            <?php include __DIR__ . '/form-inputs/prompts-settings.php'; ?>
        </div>
    </div>
</div>

<!-- Post Settings Popover - Focused on publishing options -->
<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_cw_post_settings_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_model_settings_popover_panel--allow-overflow aipkit_cw_settings_popover_panel" role="dialog" aria-label="<?php esc_attr_e('Post Settings', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body">
            <?php include __DIR__ . '/form-inputs/post-settings.php'; ?>
        </div>
    </div>
</div>
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
