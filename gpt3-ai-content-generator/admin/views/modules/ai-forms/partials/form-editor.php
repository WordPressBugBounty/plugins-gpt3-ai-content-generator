<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/partials/form-editor.php
// Status: MODIFIED

/**
 * Partial: AI Form Editor (Main Orchestrator)
 * UI for creating or editing an AI Form. Implements a modern 3-column layout for an improved user experience.
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

use WPAICG\aipkit_dashboard;
use WPAICG\AIPKIT_AI_Settings;

// --- Get available providers (always show, lock via disabled when not eligible) ---
$providers = ['OpenAI', 'Google', 'Claude', 'OpenRouter', 'Azure', 'Ollama', 'DeepSeek'];
$is_pro = class_exists('\\WPAICG\\aipkit_dashboard') && aipkit_dashboard::is_pro_plan();
// --- Get global AI param defaults ---
$global_ai_params = [];
if (class_exists('\\WPAICG\\AIPKIT_AI_Settings')) {
    $global_ai_params = AIPKIT_AI_Settings::get_ai_parameters();
}
$default_temp = $global_ai_params['temperature'] ?? 1.0;
$default_max_tokens = $global_ai_params['max_completion_tokens'] ?? 4000;
$default_top_p = $global_ai_params['top_p'] ?? 1.0;
$default_frequency_penalty = $global_ai_params['frequency_penalty'] ?? 0.0;
$default_presence_penalty = $global_ai_params['presence_penalty'] ?? 0.0;
$upgrade_url = function_exists('wpaicg_gacg_fs')
    ? wpaicg_gacg_fs()->get_upgrade_url()
    : admin_url('admin.php?page=wpaicg-pricing');
$connected_apps_manage_url = admin_url('admin.php?page=wpaicg&aipkit_module=settings&aipkit_settings_page=apps');
$connected_apps_supported_destinations = [
    [
        'name' => 'Slack',
        'logo_url' => WPAICG_PLUGIN_URL . 'admin/images/apps/slack.svg',
    ],
    [
        'name' => 'HubSpot',
        'logo_url' => WPAICG_PLUGIN_URL . 'admin/images/apps/hubspot.svg',
    ],
    [
        'name' => 'Notion',
        'logo_url' => WPAICG_PLUGIN_URL . 'admin/images/apps/notion.svg',
    ],
    [
        'name' => 'Pipedrive',
        'logo_url' => WPAICG_PLUGIN_URL . 'admin/images/apps/pipedrive.svg',
    ],
    [
        'name' => 'Zapier',
        'logo_url' => WPAICG_PLUGIN_URL . 'admin/images/apps/zapier.svg',
    ],
    [
        'name' => 'Make',
        'logo_url' => WPAICG_PLUGIN_URL . 'admin/images/apps/make.svg',
    ],
    [
        'name' => 'n8n',
        'logo_url' => WPAICG_PLUGIN_URL . 'admin/images/apps/n8n.svg',
    ],
];
$initial_ai_form_connected_apps = [
    'count' => 0,
    'summary' => '',
    'recipes' => [],
];
$render_ai_form_connected_apps_cards = static function (array $connected_apps_payload): void {
    $recipes = isset($connected_apps_payload['recipes']) && is_array($connected_apps_payload['recipes'])
        ? $connected_apps_payload['recipes']
        : [];

    foreach ($recipes as $recipe) {
        if (!is_array($recipe)) {
            continue;
        }

        $recipe_name = sanitize_text_field((string) ($recipe['name'] ?? __('Untitled Recipe', 'gpt3-ai-content-generator')));
        $status_key = sanitize_key((string) ($recipe['status_key'] ?? 'warning'));
        if (!in_array($status_key, ['ready', 'warning', 'error', 'reauth_required'], true)) {
            $status_key = 'warning';
        }
        $status_label = sanitize_text_field((string) ($recipe['status_label'] ?? __('Warning', 'gpt3-ai-content-generator')));
        $connection_label = sanitize_text_field((string) ($recipe['connection_label'] ?? __('No connection', 'gpt3-ai-content-generator')));
        $event_label = sanitize_text_field((string) ($recipe['event_label'] ?? __('No event', 'gpt3-ai-content-generator')));
        $action_label = sanitize_text_field((string) ($recipe['action_label'] ?? __('No action', 'gpt3-ai-content-generator')));
        $scope_label = sanitize_text_field((string) ($recipe['scope_label'] ?? __('All AI Forms', 'gpt3-ai-content-generator')));
        $validation_summary = sanitize_text_field((string) ($recipe['validation_summary'] ?? __('Validation unavailable.', 'gpt3-ai-content-generator')));
        ?>
        <article class="aipkit_chatbot_connected_apps_recipe">
            <div class="aipkit_chatbot_connected_apps_recipe_header">
                <strong class="aipkit_chatbot_connected_apps_recipe_title"><?php echo esc_html($recipe_name); ?></strong>
                <div class="aipkit_chatbot_connected_apps_recipe_flags">
                    <span class="aipkit_settings_recipe_status aipkit_settings_recipe_status--<?php echo esc_attr($status_key); ?>">
                        <?php echo esc_html($status_label); ?>
                    </span>
                    <span class="aipkit_settings_recipe_enabled_flag aipkit_settings_recipe_enabled_flag--<?php echo !empty($recipe['is_enabled']) ? 'enabled' : 'disabled'; ?>">
                        <?php echo !empty($recipe['is_enabled']) ? esc_html__('Enabled', 'gpt3-ai-content-generator') : esc_html__('Disabled', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
            </div>
            <p class="aipkit_chatbot_connected_apps_recipe_summary">
                <?php echo esc_html(implode(' | ', [$connection_label, $event_label, $action_label])); ?>
            </p>
            <div class="aipkit_chatbot_connected_apps_recipe_meta">
                <span class="aipkit_chatbot_connected_apps_recipe_scope"><?php echo esc_html($scope_label); ?></span>
            </div>
            <p class="aipkit_chatbot_connected_apps_recipe_validation aipkit_chatbot_connected_apps_recipe_validation--<?php echo esc_attr($status_key); ?>">
                <?php echo esc_html($validation_summary); ?>
            </p>
        </article>
        <?php
    }
};
?>
<div class="aipkit_form_editor">
    <form id="aipkit_ai_form_editor_form" onsubmit="return false;">
        <input type="hidden" id="aipkit_ai_form_id" name="form_id" value="">

        <!-- New 3-Column Layout Wrapper -->
        <div class="aipkit_form_editor_layout_wrapper">

            <!-- Column 1: Elements Palette / Settings Panel -->
            <div class="aipkit_form_editor_col_left">
                <!-- This wrapper allows the palette and settings panel to switch visibility -->
                <div class="aipkit_form_designer_left_controls_wrapper">
                    <!-- Form Elements & Layouts Palette in a Sub-Container -->
                    <div class="aipkit_sub_container" id="aipkit_ai_form_elements_palette">
                        <div class="aipkit_accordion-group">
                            <!-- Elements Accordion -->
                            <div class="aipkit_accordion">
                                <div class="aipkit_accordion-header aipkit_active">
                                    <span class="dashicons dashicons-list-view"></span>
                                    <?php esc_html_e('Form Elements', 'gpt3-ai-content-generator'); ?>
                                </div>
                                <div class="aipkit_accordion-content aipkit_active">
                                    <div class="aipkit_form_element_item" data-element-type="text-input" draggable="true">
                                        <span class="dashicons dashicons-edit-large"></span>
                                        <?php esc_html_e('Text Input', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-element-type="textarea" draggable="true">
                                        <span class="dashicons dashicons-text"></span>
                                        <?php esc_html_e('Text Area', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-element-type="select" draggable="true">
                                        <span class="dashicons dashicons-menu-alt"></span>
                                        <?php esc_html_e('Dropdown', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-element-type="checkbox" draggable="true">
                                        <span class="dashicons dashicons-yes"></span>
                                        <?php esc_html_e('Checkbox', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-element-type="radio-button" draggable="true">
                                        <span class="dashicons dashicons-marker"></span>
                                        <?php esc_html_e('Radio Buttons', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <?php if (\WPAICG\aipkit_dashboard::is_pro_plan()): ?>
                                        <div class="aipkit_form_element_item" data-element-type="file-upload" draggable="true">
                                            <span class="dashicons dashicons-media-default"></span>
                                            <?php esc_html_e('File Upload', 'gpt3-ai-content-generator'); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="aipkit_form_element_item aipkit-pro-feature-locked" title="<?php esc_attr_e('This is a Pro feature. Please upgrade.', 'gpt3-ai-content-generator'); ?>">
                                            <span class="dashicons dashicons-media-default"></span>
                                            <?php esc_html_e('File Upload', 'gpt3-ai-content-generator'); ?>
                                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg-pricing')); ?>" target="_blank" class="aipkit_pro_tag">Pro</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Layouts Accordion -->
                            <div class="aipkit_accordion">
                                <div class="aipkit_accordion-header">
                                    <span class="dashicons dashicons-layout"></span>
                                    <?php esc_html_e('Layouts', 'gpt3-ai-content-generator'); ?>
                                </div>
                                <div class="aipkit_accordion-content">
                                    <div class="aipkit_form_element_item" data-layout-type="1-col" draggable="true">
                                        <span class="dashicons dashicons-align-wide"></span>
                                        <?php esc_html_e('Single Column', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-layout-type="2-col-50-50" draggable="true">
                                        <span class="dashicons dashicons-editor-table"></span>
                                        <?php esc_html_e('2 Columns (50/50)', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-layout-type="2-col-30-70" draggable="true">
                                        <span class="dashicons dashicons-align-left"></span>
                                        <?php esc_html_e('2 Columns (30/70)', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-layout-type="2-col-70-30" draggable="true">
                                        <span class="dashicons dashicons-align-right"></span>
                                        <?php esc_html_e('2 Columns (70/30)', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                    <div class="aipkit_form_element_item" data-layout-type="3-col-33-33-33" draggable="true">
                                        <span class="dashicons dashicons-editor-table"></span>
                                        <?php esc_html_e('3 Columns', 'gpt3-ai-content-generator'); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Multi-Step Accordion -->
                            <div class="aipkit_accordion">
                                <div class="aipkit_accordion-header">
                                    <span class="dashicons dashicons-format-chat"></span>
                                    <?php esc_html_e('Multi-Step', 'gpt3-ai-content-generator'); ?>
                                    <?php if (!$is_pro): ?>
                                        <a
                                            href="<?php echo esc_url($upgrade_url); ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="aipkit_pro_tag"
                                            onclick="event.stopPropagation();"
                                        ><?php esc_html_e('Pro', 'gpt3-ai-content-generator'); ?></a>
                                    <?php endif; ?>
                                </div>
                                <div class="aipkit_accordion-content">
                                    <?php include __DIR__ . '/conversation-ui-config.php'; ?>
                                </div>
                            </div>

                            <!-- Labels Accordion -->
                            <div class="aipkit_accordion">
                                <div class="aipkit_accordion-header">
                                    <span class="dashicons dashicons-text-page"></span>
                                    <?php esc_html_e('Labels', 'gpt3-ai-content-generator'); ?>
                                </div>
                                <div class="aipkit_accordion-content">
                                    <?php include __DIR__ . '/labels-config.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Panel (Hidden by default, shown when an element is clicked) -->
                    <div class="aipkit_sub_container" id="aipkit_ai_form_element_settings_panel" style="display:none;">
                        <div class="aipkit_sub_container_header">
                            <h5 class="aipkit_sub_container_title">
                                <?php esc_html_e('Element Settings', 'gpt3-ai-content-generator'); ?>
                                <span id="aipkit_settings_panel_element_type" style="font-weight:normal; font-style:italic; font-size:0.9em;"></span>
                            </h5>
                        </div>
                        <div class="aipkit_sub_container_body">
                            <div id="aipkit_settings_panel_fields">
                                <!-- Settings fields will be injected here by JS -->
                            </div>
                            <button type="button" id="aipkit_settings_panel_close_btn" class="aipkit_btn aipkit_btn-secondary" style="margin-top:15px; width: 100%;"><?php esc_html_e('Done', 'gpt3-ai-content-generator'); ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Form Designer / Drop Zone -->
            <div class="aipkit_form_editor_col_center">
                <div class="aipkit_ai_form_designer_area" id="aipkit_ai_form_designer_area">
                    <div class="aipkit_form_designer_placeholder" id="aipkit_form_designer_placeholder">
                        <span class="dashicons dashicons-layout"></span>
                        <?php esc_html_e('Drag a Layout Here to Start', 'gpt3-ai-content-generator'); ?>
                    </div>
                    <!-- Dropped elements will be appended here by JS -->
                </div>
            </div>

            <!-- Column 3: Form Title, Prompt, AI Config -->
            <div class="aipkit_form_editor_col_right">
                <?php include __DIR__ . '/_form-editor-main-settings.php'; ?>
            </div>

        </div>
        <!-- End 3-Column Layout Wrapper -->

    </form>

    <!-- Container for AI Form Preview -->
    <div id="aipkit_ai_form_preview_container" style="display: none; margin-top: 20px; padding: 15px; border: 1px solid var(--aipkit_container-border); border-radius: 4px; background-color: #fff;">
        <!-- Preview will be injected here by JS -->
    </div>

    <div
        class="aipkit-modal-overlay aipkit_builder_instructions_modal aipkit_ai_form_prompt_modal"
        id="aipkit_ai_form_prompt_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_ai_form_prompt_modal_title"
            aria-describedby="aipkit_ai_form_prompt_modal_description"
        >
            <div class="aipkit-modal-header">
                <div>
                    <h3 class="aipkit-modal-title" id="aipkit_ai_form_prompt_modal_title">
                        <?php esc_html_e('Prompt Editor', 'gpt3-ai-content-generator'); ?>
                    </h3>
                    <p class="aipkit_builder_modal_subtitle" id="aipkit_ai_form_prompt_modal_description">
                        <?php esc_html_e('Refine your prompt with a larger editor view.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <button
                    type="button"
                    class="aipkit-modal-close-btn aipkit_ai_form_prompt_modal_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit-modal-body">
                <div class="aipkit_builder_field">
                    <textarea
                        id="aipkit_ai_form_prompt_modal_textarea"
                        class="aipkit_builder_textarea aipkit_builder_textarea_large aipkit_ai_form_prompt_modal_textarea"
                        rows="14"
                        aria-label="<?php esc_attr_e('Prompt', 'gpt3-ai-content-generator'); ?>"
                    ></textarea>
                </div>
                <div class="aipkit_builder_modal_meta">
                    <span class="aipkit_builder_char_count aipkit_ai_form_prompt_count">
                        <?php esc_html_e('0 characters', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div
        class="aipkit-modal-overlay aipkit_builder_instructions_modal aipkit_ai_form_generator_modal"
        id="aipkit_ai_form_generator_modal"
        aria-hidden="true"
    >
        <div
            class="aipkit-modal-content"
            role="dialog"
            aria-modal="true"
            aria-labelledby="aipkit_ai_form_generator_modal_title"
            aria-describedby="aipkit_ai_form_generator_modal_description"
        >
            <div class="aipkit-modal-header">
                <div>
                    <h3 class="aipkit-modal-title" id="aipkit_ai_form_generator_modal_title">
                        <?php esc_html_e('Generate AI Form', 'gpt3-ai-content-generator'); ?>
                    </h3>
                    <p class="aipkit_builder_modal_subtitle" id="aipkit_ai_form_generator_modal_description">
                        <?php esc_html_e('Describe the form you want. AI will generate it for you.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
                <button
                    type="button"
                    class="aipkit-modal-close-btn aipkit_ai_form_generator_modal_close"
                    aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            <div class="aipkit-modal-body">
                <div class="aipkit_form-group">
                    <label class="aipkit_form-label" for="aipkit_ai_form_generator_prompt">
                        <?php esc_html_e('Describe the AI form', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_builder_textarea_wrap aipkit_ai_form_generator_textarea_wrap">
                        <textarea
                            id="aipkit_ai_form_generator_prompt"
                            class="aipkit_builder_textarea aipkit_builder_textarea_large aipkit_ai_form_generator_prompt"
                            rows="12"
                            placeholder="<?php esc_attr_e('e.g., Create a blog brief generator with fields for topic, audience, tone, keywords, and target length.', 'gpt3-ai-content-generator'); ?>"
                        ></textarea>
                        <button
                            type="button"
                            id="aipkit_ai_form_generator_inspire_btn"
                            class="aipkit_btn aipkit_btn-secondary aipkit_ai_form_generator_inspire_btn"
                        >
                            <span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
                            <span><?php esc_html_e('Inspire Me', 'gpt3-ai-content-generator'); ?></span>
                        </button>
                    </div>
                    <div class="aipkit_form-help" style="margin-top: 8px;">
                        <?php esc_html_e('Tip: mention the output goal, the audience, and any fields or option lists you want the form to include.', 'gpt3-ai-content-generator'); ?>
                    </div>
                </div>
                <div id="aipkit_ai_form_generator_status" class="aipkit_form-help" aria-live="polite"></div>
            </div>
            <div class="aipkit_form_editor_actions">
                <button type="button" id="aipkit_ai_form_generator_cancel_btn" class="aipkit_btn aipkit_btn-danger aipkit_ai_form_generator_modal_close">
                    <?php esc_html_e('Cancel', 'gpt3-ai-content-generator'); ?>
                </button>
                <button type="button" id="aipkit_ai_form_generate_draft_btn" class="aipkit_btn aipkit_btn-primary">
                    <span class="aipkit_btn-text"><?php esc_html_e('Generate', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_spinner" style="display:none;"></span>
                </button>
            </div>
        </div>
    </div>
</div>
