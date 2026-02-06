<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/ai-forms/partials/tools-config.php
// Status: NEW FILE

/**
 * Partial: AI Form Editor - Tools Configuration
 * Contains settings for enabling and configuring web search integration.
 */
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="aipkit_popover_options_list">
    <div class="aipkit_popover_option_row aipkit_ai_form_web_search_row aipkit_ai_form_web_search_row--openai">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label" tabindex="0" data-tooltip="<?php echo esc_attr__('Let OpenAI models browse the web.', 'gpt3-ai-content-generator'); ?>">
                <?php esc_html_e('Web Search', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_ai_form_openai_web_search_enabled"
                        name="openai_web_search_enabled"
                        class="aipkit_ai_form_openai_web_search_toggle"
                        value="1"
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="aipkit_ai_form_openai_web_search_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_context_size">
                    <?php esc_html_e('Search Context Size', 'gpt3-ai-content-generator'); ?>
                </label>
                <select id="aipkit_ai_form_openai_web_search_context_size" name="openai_web_search_context_size" class="aipkit_popover_option_select">
                    <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                    <option value="medium" selected><?php esc_html_e('Medium (Default)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="high"><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_type">
                    <?php esc_html_e('User Location', 'gpt3-ai-content-generator'); ?>
                </label>
                <select id="aipkit_ai_form_openai_web_search_loc_type" name="openai_web_search_loc_type" class="aipkit_popover_option_select aipkit_ai_form_openai_web_search_loc_type_select">
                    <option value="none" selected><?php esc_html_e('None (Default)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="approximate"><?php esc_html_e('Approximate', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_ai_form_openai_web_search_location_details" style="display: none;">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_country">
                        <?php esc_html_e('Country (ISO Code)', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_country" name="openai_web_search_loc_country" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., US, GB', 'gpt3-ai-content-generator'); ?>" maxlength="2">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_city">
                        <?php esc_html_e('City', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_city" name="openai_web_search_loc_city" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., London', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_region">
                        <?php esc_html_e('Region/State', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_region" name="openai_web_search_loc_region" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., California', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_openai_web_search_loc_timezone">
                        <?php esc_html_e('Timezone (IANA)', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_openai_web_search_loc_timezone" name="openai_web_search_loc_timezone" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., America/Chicago', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_popover_option_row aipkit_ai_form_web_search_row aipkit_ai_form_web_search_row--claude">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label" tabindex="0" data-tooltip="<?php echo esc_attr__('Let Claude models browse the web.', 'gpt3-ai-content-generator'); ?>">
                <?php esc_html_e('Web Search', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_ai_form_claude_web_search_enabled"
                        name="claude_web_search_enabled"
                        class="aipkit_ai_form_claude_web_search_toggle"
                        value="1"
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="aipkit_ai_form_claude_web_search_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_max_uses">
                    <?php esc_html_e('Max Uses', 'gpt3-ai-content-generator'); ?>
                </label>
                <input type="number" id="aipkit_ai_form_claude_web_search_max_uses" name="claude_web_search_max_uses" class="aipkit_popover_option_input aipkit_popover_option_input--framed" min="1" max="20" step="1" value="5">
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_type">
                    <?php esc_html_e('User Location', 'gpt3-ai-content-generator'); ?>
                </label>
                <select id="aipkit_ai_form_claude_web_search_loc_type" name="claude_web_search_loc_type" class="aipkit_popover_option_select aipkit_ai_form_claude_web_search_loc_type_select">
                    <option value="none" selected><?php esc_html_e('None (Default)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="approximate"><?php esc_html_e('Approximate', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_ai_form_claude_web_search_location_details" style="display: none;">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_country">
                        <?php esc_html_e('Country (ISO Code)', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_country" name="claude_web_search_loc_country" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., US, GB', 'gpt3-ai-content-generator'); ?>" maxlength="2">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_city">
                        <?php esc_html_e('City', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_city" name="claude_web_search_loc_city" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., London', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_region">
                        <?php esc_html_e('Region/State', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_region" name="claude_web_search_loc_region" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., California', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_loc_timezone">
                        <?php esc_html_e('Timezone (IANA)', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <input type="text" id="aipkit_ai_form_claude_web_search_loc_timezone" name="claude_web_search_loc_timezone" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('e.g., America/Chicago', 'gpt3-ai-content-generator'); ?>">
                </div>
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_allowed_domains">
                    <?php esc_html_e('Allowed Domains', 'gpt3-ai-content-generator'); ?>
                </label>
                <input type="text" id="aipkit_ai_form_claude_web_search_allowed_domains" name="claude_web_search_allowed_domains" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('example.com, docs.anthropic.com', 'gpt3-ai-content-generator'); ?>">
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_blocked_domains">
                    <?php esc_html_e('Blocked Domains', 'gpt3-ai-content-generator'); ?>
                </label>
                <input type="text" id="aipkit_ai_form_claude_web_search_blocked_domains" name="claude_web_search_blocked_domains" class="aipkit_popover_option_input aipkit_popover_option_input--framed" placeholder="<?php esc_attr_e('example.com, ads.example.org', 'gpt3-ai-content-generator'); ?>">
            </div>
        </div>
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_claude_web_search_cache_ttl">
                    <?php esc_html_e('Cache TTL', 'gpt3-ai-content-generator'); ?>
                </label>
                <select id="aipkit_ai_form_claude_web_search_cache_ttl" name="claude_web_search_cache_ttl" class="aipkit_popover_option_select">
                    <option value="none" selected><?php esc_html_e('None (Default)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="5m"><?php esc_html_e('5 minutes', 'gpt3-ai-content-generator'); ?></option>
                    <option value="1h"><?php esc_html_e('1 hour', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="aipkit_popover_option_row aipkit_ai_form_web_search_row aipkit_ai_form_web_search_row--google">
        <div class="aipkit_popover_option_main">
            <span class="aipkit_popover_option_label" tabindex="0" data-tooltip="<?php echo esc_attr__('Let Google models browse the web.', 'gpt3-ai-content-generator'); ?>">
                <?php esc_html_e('Web Search', 'gpt3-ai-content-generator'); ?>
            </span>
            <div class="aipkit_popover_option_actions">
                <label class="aipkit_switch">
                    <input
                        type="checkbox"
                        id="aipkit_ai_form_google_search_grounding_enabled"
                        name="google_search_grounding_enabled"
                        class="aipkit_ai_form_google_search_grounding_toggle"
                        value="1"
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="aipkit_ai_form_google_search_grounding_settings" style="display: none;">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <label class="aipkit_popover_option_label" for="aipkit_ai_form_google_grounding_mode">
                    <?php esc_html_e('Grounding Mode', 'gpt3-ai-content-generator'); ?>
                </label>
                <select id="aipkit_ai_form_google_grounding_mode" name="google_grounding_mode" class="aipkit_popover_option_select aipkit_ai_form_google_grounding_mode_select">
                    <option value="DEFAULT_MODE" selected><?php esc_html_e('Default (Model Decides/Search as Tool)', 'gpt3-ai-content-generator'); ?></option>
                    <option value="MODE_DYNAMIC"><?php esc_html_e('Dynamic Retrieval (Gemini 1.5 Flash only)', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
        <div class="aipkit_ai_form_google_grounding_dynamic_threshold_container" style="display: none;">
            <div class="aipkit_popover_param_row">
                <span class="aipkit_popover_param_label"><?php esc_html_e('Dynamic Retrieval Threshold', 'gpt3-ai-content-generator'); ?></span>
                <div class="aipkit_popover_param_slider">
                    <input type="range" id="aipkit_ai_form_google_grounding_dynamic_threshold" name="google_grounding_dynamic_threshold" class="aipkit_form-input aipkit_range_slider aipkit_popover_slider" min="0.0" max="1.0" step="0.01" value="0.30">
                    <span id="aipkit_ai_form_google_grounding_dynamic_threshold_value" class="aipkit_popover_param_value">0.30</span>
                </div>
            </div>
        </div>
        <div class="aipkit_form-help"><?php esc_html_e('Supported models: Gemini 2.5 Pro, Gemini 2.5 Flash, Gemini 2.0 Flash, Gemini 1.5 Pro, Gemini 1.5 Flash.', 'gpt3-ai-content-generator'); ?></div>
    </div>

    <div class="aipkit_ai_form_web_search_empty_state aipkit_form-help" style="display: none;">
        <?php esc_html_e('Web Search is available for OpenAI, Google, and Claude providers only.', 'gpt3-ai-content-generator'); ?>
    </div>
</div>
