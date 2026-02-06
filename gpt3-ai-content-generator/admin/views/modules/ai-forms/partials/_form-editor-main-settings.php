<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/partials/_form-editor-main-settings.php
// Status: MODIFIED

/**
 * Partial: AI Form Editor - Main Settings
 * Renders the content for the right-hand column of the form editor, including
 * Prompt and AI Configuration with a compact settings popover.
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from parent (form-editor.php):
// $providers, $default_temp, $default_max_tokens, $default_top_p, $default_frequency_penalty, $default_presence_penalty
// NEW: Variables passed down from ai-forms/index.php
// $openai_vector_stores, $pinecone_indexes, $qdrant_collections, $openai_embedding_models, $google_embedding_models
?>
<div class="aipkit_ai_form_editor_sidebar">
    <div class="aipkit_form-row aipkit_ai_form_model_config_row" style="flex-wrap: unset;">
        <div class="aipkit_form-group aipkit_form-col aipkit_ai_form_provider_col">
            <label class="aipkit_form-label" for="aipkit_ai_form_ai_provider"><?php esc_html_e('Engine', 'gpt3-ai-content-generator'); ?></label>
            <select id="aipkit_ai_form_ai_provider" name="ai_provider" class="aipkit_form-input" data-aipkit-provider-notice-target="aipkit_provider_notice_ai_forms" data-aipkit-provider-notice-defer="1">
                <?php foreach ($providers as $p_value) :
                    $disabled = false;
                    $label = $p_value;
                    if ($p_value === 'Ollama' && (empty($is_pro) || !$is_pro)) {
                        $disabled = true;
                        $label = __('Ollama (Pro)', 'gpt3-ai-content-generator');
                    }
                ?>
                    <option value="<?php echo esc_attr($p_value); ?>" <?php echo $disabled ? 'disabled' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="aipkit_form-group aipkit_form-col aipkit_ai_form_model_col">
            <label class="aipkit_form-label" for="aipkit_ai_form_ai_model"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></label>
            <select id="aipkit_ai_form_ai_model" name="ai_model" class="aipkit_form-input">
                <option value=""><?php esc_html_e('Sync provider to see models', 'gpt3-ai-content-generator'); ?></option>
            </select>
        </div>
        <div class="aipkit_form-group aipkit_form-col aipkit_ai_form_settings_col">
            <button
                type="button"
                id="aipkit_ai_form_settings_trigger"
                class="aipkit_btn aipkit_btn-secondary aipkit_icon_btn"
                title="<?php esc_attr_e('Settings', 'gpt3-ai-content-generator'); ?>"
                data-aipkit-popover-target="aipkit_ai_form_settings_popover"
                data-aipkit-popover-placement="left"
                aria-controls="aipkit_ai_form_settings_popover"
                aria-expanded="false"
            >
                <span class="dashicons dashicons-admin-generic"></span>
            </button>
        </div>
    </div>

    <div class="aipkit_form-group">
        <label class="aipkit_form-label" for="aipkit_ai_form_prompt_template"><?php esc_html_e('Prompt', 'gpt3-ai-content-generator'); ?></label>
        <div class="aipkit_builder_textarea_wrap">
            <textarea id="aipkit_ai_form_prompt_template" name="prompt_template" class="aipkit_builder_textarea aipkit_form-input" rows="12" placeholder="<?php esc_attr_e('e.g., Generate a meta description for: {your_field_name}', 'gpt3-ai-content-generator'); ?>"></textarea>
            <button
                type="button"
                class="aipkit_builder_icon_btn aipkit_builder_textarea_expand aipkit_ai_form_prompt_expand"
                aria-label="<?php esc_attr_e('Expand prompt editor', 'gpt3-ai-content-generator'); ?>"
            >
                <span class="dashicons dashicons-editor-expand"></span>
            </button>
        </div>
        <div class="aipkit_prompt_snippets_container" id="aipkit_prompt_snippets_container">
            <!-- Snippets will be injected here by JS -->
        </div>
        <div class="aipkit_prompt_validation_area">
            <button type="button" id="aipkit_validate_prompt_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small">
                <span class="dashicons dashicons-editor-spellcheck"></span>
                <span class="aipkit_btn-text"><?php esc_html_e('Validate Prompt', 'gpt3-ai-content-generator'); ?></span>
            </button>
            <div id="aipkit_prompt_validation_results" class="aipkit_form-help" style="margin-top: 8px;"></div>
        </div>
    </div>

    <div
        class="aipkit_model_settings_popover aipkit_ai_form_settings_popover"
        id="aipkit_ai_form_settings_popover"
        aria-hidden="true"
        data-title-root="<?php esc_attr_e('Settings', 'gpt3-ai-content-generator'); ?>"
        data-title-parameters="<?php esc_attr_e('Parameters', 'gpt3-ai-content-generator'); ?>"
        data-title-context="<?php esc_attr_e('Context', 'gpt3-ai-content-generator'); ?>"
        data-title-tools="<?php esc_attr_e('Tools', 'gpt3-ai-content-generator'); ?>"
    >
        <div
            class="aipkit_model_settings_popover_panel aipkit_model_settings_popover_panel--allow-overflow"
            role="dialog"
            aria-modal="false"
            aria-labelledby="aipkit_ai_form_settings_popover_title"
        >
            <div class="aipkit_model_settings_popover_header">
                <div class="aipkit_model_settings_popover_header_start">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_back"
                        aria-label="<?php esc_attr_e('Back', 'gpt3-ai-content-generator'); ?>"
                        hidden
                    >
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <span class="aipkit_model_settings_popover_title" id="aipkit_ai_form_settings_popover_title">
                        <?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_popover_status_inline aipkit_model_sync_status" aria-live="polite"></span>
                </div>
                <div class="aipkit_model_settings_popover_header_end">
                    <button
                        type="button"
                        class="aipkit_model_settings_popover_close"
                        aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>"
                    >
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            </div>
            <div class="aipkit_model_settings_popover_body">
                <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="root">
                    <div class="aipkit_popover_options_list aipkit_popover_options_list--settings-root">
                        <div class="aipkit_popover_option_group">
                            <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                <button
                                    type="button"
                                    class="aipkit_popover_option_nav"
                                    data-aipkit-panel-target="parameters"
                                >
                                    <span class="aipkit_popover_option_label">
                                        <span class="aipkit_popover_option_icon dashicons dashicons-admin-settings" aria-hidden="true"></span>
                                        <span class="aipkit_popover_option_label_content">
                                            <span class="aipkit_popover_option_label_text">
                                                <?php esc_html_e('Parameters', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <span class="aipkit_popover_option_hint">
                                                <?php esc_html_e('Temperature, tokens, penalties', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    </span>
                                </button>
                            </div>
                            <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                <button
                                    type="button"
                                    class="aipkit_popover_option_nav"
                                    data-aipkit-panel-target="context"
                                >
                                    <span class="aipkit_popover_option_label">
                                        <span class="aipkit_popover_option_icon dashicons dashicons-database" aria-hidden="true"></span>
                                        <span class="aipkit_popover_option_label_content">
                                            <span class="aipkit_popover_option_label_text">
                                                <?php esc_html_e('Context', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <span class="aipkit_popover_option_hint">
                                                <?php esc_html_e('Vector store settings', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    </span>
                                </button>
                            </div>
                            <div class="aipkit_popover_option_row aipkit_popover_option_row--nav">
                                <button
                                    type="button"
                                    class="aipkit_popover_option_nav"
                                    data-aipkit-panel-target="tools"
                                >
                                    <span class="aipkit_popover_option_label">
                                        <span class="aipkit_popover_option_icon dashicons dashicons-admin-tools" aria-hidden="true"></span>
                                        <span class="aipkit_popover_option_label_content">
                                            <span class="aipkit_popover_option_label_text">
                                                <?php esc_html_e('Tools', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                            <span class="aipkit_popover_option_hint">
                                                <?php esc_html_e('Web search options', 'gpt3-ai-content-generator'); ?>
                                            </span>
                                        </span>
                                    </span>
                                    <span class="aipkit_popover_option_chevron" aria-hidden="true">
                                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="aipkit_popover_flyout_footer">
                        <span class="aipkit_popover_flyout_footer_text">
                            <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <a
                            class="aipkit_popover_flyout_footer_link"
                            href="<?php echo esc_url('https://docs.aipower.org/docs/category/ai-forms'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                        </a>
                    </div>
                </div>

                <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="parameters" hidden>
                    <div class="aipkit_popover_params_list">
                        <div class="aipkit_popover_param_row">
                            <span class="aipkit_popover_param_label"><?php esc_html_e('Temperature', 'gpt3-ai-content-generator'); ?></span>
                            <div class="aipkit_popover_param_slider">
                                <input type="range" id="aipkit_ai_form_temperature" name="temperature" class="aipkit_form-input aipkit_range_slider aipkit_popover_slider" min="0" max="2" step="0.1" value="<?php echo esc_attr($default_temp); ?>" />
                                <span id="aipkit_ai_form_temperature_value" class="aipkit_popover_param_value"><?php echo esc_html($default_temp); ?></span>
                            </div>
                        </div>
                        <div class="aipkit_popover_param_row">
                            <span class="aipkit_popover_param_label"><?php esc_html_e('Max Tokens', 'gpt3-ai-content-generator'); ?></span>
                            <div class="aipkit_popover_param_slider">
                                <input type="range" id="aipkit_ai_form_max_tokens" name="max_tokens" class="aipkit_form-input aipkit_range_slider aipkit_popover_slider" min="1" max="128000" step="1" value="<?php echo esc_attr($default_max_tokens); ?>" />
                                <span id="aipkit_ai_form_max_tokens_value" class="aipkit_popover_param_value"><?php echo esc_html($default_max_tokens); ?></span>
                            </div>
                        </div>
                        <div class="aipkit_popover_param_row">
                            <span class="aipkit_popover_param_label"><?php esc_html_e('Top P', 'gpt3-ai-content-generator'); ?></span>
                            <div class="aipkit_popover_param_slider">
                                <input type="range" id="aipkit_ai_form_top_p" name="top_p" class="aipkit_form-input aipkit_range_slider aipkit_popover_slider" min="0" max="1" step="0.01" value="<?php echo esc_attr($default_top_p); ?>" />
                                <span id="aipkit_ai_form_top_p_value" class="aipkit_popover_param_value"><?php echo esc_html($default_top_p); ?></span>
                            </div>
                        </div>
                        <div class="aipkit_popover_param_row">
                            <span class="aipkit_popover_param_label"><?php esc_html_e('Frequency Penalty', 'gpt3-ai-content-generator'); ?></span>
                            <div class="aipkit_popover_param_slider">
                                <input type="range" id="aipkit_ai_form_frequency_penalty" name="frequency_penalty" class="aipkit_form-input aipkit_range_slider aipkit_popover_slider" min="0" max="2" step="0.1" value="<?php echo esc_attr($default_frequency_penalty); ?>" />
                                <span id="aipkit_ai_form_frequency_penalty_value" class="aipkit_popover_param_value"><?php echo esc_html($default_frequency_penalty); ?></span>
                            </div>
                        </div>
                        <div class="aipkit_popover_param_row">
                            <span class="aipkit_popover_param_label"><?php esc_html_e('Presence Penalty', 'gpt3-ai-content-generator'); ?></span>
                            <div class="aipkit_popover_param_slider">
                                <input type="range" id="aipkit_ai_form_presence_penalty" name="presence_penalty" class="aipkit_form-input aipkit_range_slider aipkit_popover_slider" min="0" max="2" step="0.1" value="<?php echo esc_attr($default_presence_penalty); ?>" />
                                <span id="aipkit_ai_form_presence_penalty_value" class="aipkit_popover_param_value"><?php echo esc_html($default_presence_penalty); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="aipkit_popover_options_list">
                        <div class="aipkit_popover_option_row aipkit_ai_form_reasoning_effort_field" style="display: none;">
                            <div class="aipkit_popover_option_main">
                                <label class="aipkit_popover_option_label" for="aipkit_ai_form_reasoning_effort"><?php esc_html_e('Reasoning Effort', 'gpt3-ai-content-generator'); ?></label>
                                <select id="aipkit_ai_form_reasoning_effort" name="reasoning_effort" class="aipkit_popover_option_select">
                                    <option value="none"><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="high"><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="xhigh"><?php esc_html_e('XHigh', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="aipkit_popover_flyout_footer">
                        <span class="aipkit_popover_flyout_footer_text">
                            <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <a
                            class="aipkit_popover_flyout_footer_link"
                            href="<?php echo esc_url('https://docs.aipower.org/docs/ai-configuration#ai-parameters'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                        </a>
                    </div>
                </div>

                <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="context" hidden>
                    <?php include __DIR__ . '/vector-config.php'; ?>
                    <div class="aipkit_popover_flyout_footer">
                        <span class="aipkit_popover_flyout_footer_text">
                            <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <a
                            class="aipkit_popover_flyout_footer_link"
                            href="<?php echo esc_url('https://docs.aipower.org/docs/context'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                        </a>
                    </div>
                </div>

                <div class="aipkit_model_settings_panel" data-aipkit-settings-panel="tools" hidden>
                    <?php include __DIR__ . '/tools-config.php'; ?>
                    <div class="aipkit_popover_flyout_footer">
                        <span class="aipkit_popover_flyout_footer_text">
                            <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <a
                            class="aipkit_popover_flyout_footer_link"
                            href="<?php echo esc_url('https://docs.aipower.org/docs/ai-configuration#web-search'); ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
