<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/partials/tools-config.php
// Status: MODIFIED

/**
 * Partial: AI Form Editor - Tools Configuration
 * Contains settings for enabling and configuring web search integration.
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_popover_options_list">
    <input
        type="checkbox"
        id="aipkit_ai_form_openai_web_search_enabled"
        name="openai_web_search_enabled"
        class="aipkit_ai_form_openai_web_search_toggle"
        value="1"
        hidden
        aria-hidden="true"
        tabindex="-1"
    >

    <div class="aipkit_ai_form_openai_web_search_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_context_size">
                        <?php esc_html_e('Search context', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Amount of web context.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select id="aipkit_ai_form_openai_web_search_context_size" name="openai_web_search_context_size" class="aipkit_popover_option_select">
                    <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                    <option value="medium" selected><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                    <option value="high"><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_popover_option_row aipkit_ai_form_openai_web_search_location_type_row aipkit_last_visible_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_type">
                        <?php esc_html_e('User location', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Optional location signal.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select id="aipkit_ai_form_openai_web_search_loc_type" name="openai_web_search_loc_type" class="aipkit_popover_option_select aipkit_ai_form_openai_web_search_loc_type_select">
                    <option value="none" selected><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                    <option value="approximate"><?php esc_html_e('Approximate', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_ai_form_openai_web_search_location_details" style="display: none;">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_country">
                            <?php esc_html_e('Country', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('Two-letter country code.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_country" name="openai_web_search_loc_country" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('US', 'gpt3-ai-content-generator'); ?>" maxlength="2">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_city">
                            <?php esc_html_e('City', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('City for approximate location.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_city" name="openai_web_search_loc_city" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('London', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_region">
                            <?php esc_html_e('Region', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('State or region.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_region" name="openai_web_search_loc_region" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('California', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_timezone">
                            <?php esc_html_e('Timezone', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('IANA timezone name.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_timezone" name="openai_web_search_loc_timezone" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('America/Chicago', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
        </div>
    </div>

    <input
        type="checkbox"
        id="aipkit_ai_form_claude_web_search_enabled"
        name="claude_web_search_enabled"
        class="aipkit_ai_form_claude_web_search_toggle"
        value="1"
        hidden
        aria-hidden="true"
        tabindex="-1"
    >

    <div class="aipkit_ai_form_claude_web_search_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_max_uses">
                        <?php esc_html_e('Max uses', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Search calls allowed.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <input type="number" id="aipkit_ai_form_claude_web_search_max_uses" name="claude_web_search_max_uses" class="aipkit_form-input aipkit_popover_option_input" min="1" max="20" step="1" value="5" inputmode="numeric">
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_type">
                        <?php esc_html_e('User location', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Optional location signal.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select id="aipkit_ai_form_claude_web_search_loc_type" name="claude_web_search_loc_type" class="aipkit_popover_option_select aipkit_ai_form_claude_web_search_loc_type_select">
                    <option value="none" selected><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                    <option value="approximate"><?php esc_html_e('Approximate', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_ai_form_claude_web_search_location_details" style="display: none;">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_country">
                            <?php esc_html_e('Country', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('Two-letter country code.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_country" name="claude_web_search_loc_country" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('US', 'gpt3-ai-content-generator'); ?>" maxlength="2">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_city">
                            <?php esc_html_e('City', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('City for approximate location.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_city" name="claude_web_search_loc_city" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('London', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_region">
                            <?php esc_html_e('Region', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('State or region.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_region" name="claude_web_search_loc_region" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('California', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_timezone">
                            <?php esc_html_e('Timezone', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('IANA timezone name.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_timezone" name="claude_web_search_loc_timezone" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('America/Chicago', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_allowed_domains">
                        <?php esc_html_e('Allowed domains', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Limit search to these domains.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <input type="text" id="aipkit_ai_form_claude_web_search_allowed_domains" name="claude_web_search_allowed_domains" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('example.com, docs.example.com', 'gpt3-ai-content-generator'); ?>">
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_blocked_domains">
                        <?php esc_html_e('Blocked domains', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Exclude these domains.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <input type="text" id="aipkit_ai_form_claude_web_search_blocked_domains" name="claude_web_search_blocked_domains" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('example.com, ads.example.org', 'gpt3-ai-content-generator'); ?>">
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_cache_ttl">
                        <?php esc_html_e('Cache TTL', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Reuse search results briefly.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select id="aipkit_ai_form_claude_web_search_cache_ttl" name="claude_web_search_cache_ttl" class="aipkit_popover_option_select">
                    <option value="none" selected><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                    <option value="5m"><?php esc_html_e('5 minutes', 'gpt3-ai-content-generator'); ?></option>
                    <option value="1h"><?php esc_html_e('1 hour', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <input
        type="checkbox"
        id="aipkit_ai_form_openrouter_web_search_enabled"
        name="openrouter_web_search_enabled"
        class="aipkit_ai_form_openrouter_web_search_toggle"
        value="1"
        hidden
        aria-hidden="true"
        tabindex="-1"
    >

    <div class="aipkit_ai_form_openrouter_web_search_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openrouter_web_search_engine">
                        <?php esc_html_e('Engine', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Search engine selection.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select id="aipkit_ai_form_openrouter_web_search_engine" name="openrouter_web_search_engine" class="aipkit_popover_option_select">
                    <option value="auto" selected><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                    <option value="native"><?php esc_html_e('Native', 'gpt3-ai-content-generator'); ?></option>
                    <option value="exa"><?php esc_html_e('Exa', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openrouter_web_search_max_results">
                        <?php esc_html_e('Max results', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Results to include.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <input type="number" id="aipkit_ai_form_openrouter_web_search_max_results" name="openrouter_web_search_max_results" class="aipkit_form-input aipkit_popover_option_input" min="1" max="10" step="1" value="5" inputmode="numeric">
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openrouter_web_search_search_prompt">
                        <?php esc_html_e('Search prompt', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Optional search instruction.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <input type="text" id="aipkit_ai_form_openrouter_web_search_search_prompt" name="openrouter_web_search_search_prompt" class="aipkit_form-input aipkit_popover_option_input" placeholder="<?php esc_attr_e('Optional', 'gpt3-ai-content-generator'); ?>">
            </div>
        </div>
    </div>

    <input
        type="checkbox"
        id="aipkit_ai_form_xai_web_search_enabled"
        name="xai_web_search_enabled"
        class="aipkit_ai_form_xai_web_search_toggle"
        value="1"
        hidden
        aria-hidden="true"
        tabindex="-1"
    >

    <div class="aipkit_ai_form_xai_web_search_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                <div class="aipkit_cw_settings_option_text">
                    <span class="aipkit_popover_option_label">
                        <?php esc_html_e('No additional options', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('xAI web search does not have additional web settings or options.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <input
        type="checkbox"
        id="aipkit_ai_form_google_search_grounding_enabled"
        name="google_search_grounding_enabled"
        class="aipkit_ai_form_google_search_grounding_toggle"
        value="1"
        hidden
        aria-hidden="true"
        tabindex="-1"
    >

    <div class="aipkit_ai_form_google_search_grounding_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_google_grounding_mode">
                        <?php esc_html_e('Grounding mode', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('How Google grounding runs.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select id="aipkit_ai_form_google_grounding_mode" name="google_grounding_mode" class="aipkit_popover_option_select aipkit_ai_form_google_grounding_mode_select">
                    <option value="DEFAULT_MODE" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <option value="MODE_DYNAMIC"><?php esc_html_e('Dynamic retrieval', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_ai_form_google_grounding_dynamic_threshold_container" style="display: none;">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <div class="aipkit_cw_settings_option_text">
                        <label class="aipkit_popover_option_label" for="aipkit_ai_form_google_grounding_dynamic_threshold">
                            <?php esc_html_e('Dynamic threshold', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <span class="aipkit_popover_option_helper">
                            <?php esc_html_e('Minimum retrieval confidence.', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input type="number" id="aipkit_ai_form_google_grounding_dynamic_threshold" name="google_grounding_dynamic_threshold" class="aipkit_form-input aipkit_popover_option_input" min="0" max="1" step="0.01" value="0.30" inputmode="decimal">
                </div>
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <span class="aipkit_popover_option_label">
                        <?php esc_html_e('Models', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Supported Gemini models.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <span class="aipkit_popover_option_static">
                    <?php esc_html_e('2.5 Pro, 2.5 Flash, 2.0 Flash, 1.5 Pro, 1.5 Flash', 'gpt3-ai-content-generator'); ?>
                </span>
            </div>
        </div>
    </div>

    <div class="aipkit_ai_form_web_search_empty_state aipkit_form-help" style="display: none;">
        <?php esc_html_e('Web Search is not available for the selected provider or model.', 'gpt3-ai-content-generator'); ?>
    </div>
</div>
