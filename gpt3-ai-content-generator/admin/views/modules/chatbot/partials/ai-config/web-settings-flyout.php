<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="aipkit_popover_options_list aipkit_popover_options_list--web">
    <div class="aipkit_popover_option_group aipkit_web_modal_section_openai" style="<?php echo ($current_provider_for_this_bot === 'OpenAI') ? '' : 'display:none;'; ?>">
        <div class="aipkit_openai_web_search_conditional_settings" style="<?php echo ($current_provider_for_this_bot === 'OpenAI' && $openai_web_search_enabled_val === '1') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_context_size_modal"
                        data-tooltip="<?php echo esc_attr__('Amount of web context to include.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Search context size', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_context_size_modal"
                        name="openai_web_search_context_size"
                        class="aipkit_popover_option_select"
                    >
                        <option value="low" <?php selected($openai_web_search_context_size_val, 'low'); ?>><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                        <option value="medium" <?php selected($openai_web_search_context_size_val, 'medium'); ?>><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                        <option value="high" <?php selected($openai_web_search_context_size_val, 'high'); ?>><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
            </div>
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_type_modal"
                        data-tooltip="<?php echo esc_attr__('Improves local relevance when set to Approximate.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('User location', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_type_modal"
                        name="openai_web_search_loc_type"
                        class="aipkit_popover_option_select aipkit_openai_web_search_loc_type_select"
                    >
                        <option value="none" <?php selected($openai_web_search_loc_type_val, 'none'); ?>><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                        <option value="approximate" <?php selected($openai_web_search_loc_type_val, 'approximate'); ?>><?php esc_html_e('Approximate', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
            </div>
            <div class="aipkit_openai_web_search_location_details" style="<?php echo ($current_provider_for_this_bot === 'OpenAI' && $openai_web_search_enabled_val === '1' && $openai_web_search_loc_type_val === 'approximate') ? '' : 'display:none;'; ?>">
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_country_modal"
                            data-tooltip="<?php echo esc_attr__('2-letter code, e.g., US or GB.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Country (ISO Code)', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <input
                            type="text"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_country_modal"
                            name="openai_web_search_loc_country"
                            class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                            value="<?php echo esc_attr($openai_web_search_loc_country_val); ?>"
                            placeholder="<?php esc_attr_e('e.g., US, GB', 'gpt3-ai-content-generator'); ?>"
                            maxlength="2"
                        />
                    </div>
                </div>
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_city_modal"
                            data-tooltip="<?php echo esc_attr__('Optional city name.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('City', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <input
                            type="text"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_city_modal"
                            name="openai_web_search_loc_city"
                            class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                            value="<?php echo esc_attr($openai_web_search_loc_city_val); ?>"
                            placeholder="<?php esc_attr_e('e.g., London', 'gpt3-ai-content-generator'); ?>"
                        />
                    </div>
                </div>
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_region_modal"
                            data-tooltip="<?php echo esc_attr__('Optional region or state.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Region/State', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <input
                            type="text"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_region_modal"
                            name="openai_web_search_loc_region"
                            class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                            value="<?php echo esc_attr($openai_web_search_loc_region_val); ?>"
                            placeholder="<?php esc_attr_e('e.g., California', 'gpt3-ai-content-generator'); ?>"
                        />
                    </div>
                </div>
                <div class="aipkit_popover_option_row">
                    <div class="aipkit_popover_option_main">
                        <label
                            class="aipkit_popover_option_label"
                            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_timezone_modal"
                            data-tooltip="<?php echo esc_attr__('IANA format, e.g., America/Chicago.', 'gpt3-ai-content-generator'); ?>"
                        >
                            <?php esc_html_e('Timezone (IANA)', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <input
                            type="text"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_web_search_loc_timezone_modal"
                            name="openai_web_search_loc_timezone"
                            class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                            value="<?php echo esc_attr($openai_web_search_loc_timezone_val); ?>"
                            placeholder="<?php esc_attr_e('e.g., America/Chicago', 'gpt3-ai-content-generator'); ?>"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_popover_option_group aipkit_web_modal_section_google" style="<?php echo ($current_provider_for_this_bot === 'Google') ? '' : 'display:none;'; ?>">
        <div class="aipkit_google_search_grounding_conditional_settings" style="<?php echo ($current_provider_for_this_bot === 'Google' && $google_search_grounding_enabled_val === '1') ? '' : 'display:none;'; ?>">
            <div class="aipkit_popover_option_row">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_mode_modal"
                        data-tooltip="<?php echo esc_attr__('Default lets the model decide; Dynamic always retrieves.', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Mode', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_mode_modal"
                        name="google_grounding_mode"
                        class="aipkit_popover_option_select aipkit_google_grounding_mode_select"
                    >
                        <option value="DEFAULT_MODE" <?php selected($google_grounding_mode_val, 'DEFAULT_MODE'); ?>><?php esc_html_e('Default (Model Decides)', 'gpt3-ai-content-generator'); ?></option>
                        <option value="MODE_DYNAMIC" <?php selected($google_grounding_mode_val, 'MODE_DYNAMIC'); ?>><?php esc_html_e('Dynamic (Gemini 1.5 Flash only)', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
            </div>
            <div class="aipkit_popover_option_row aipkit_google_grounding_dynamic_threshold_container" style="<?php echo ($current_provider_for_this_bot === 'Google' && $google_search_grounding_enabled_val === '1' && $google_grounding_mode_val === 'MODE_DYNAMIC') ? '' : 'display:none;'; ?>">
                <div class="aipkit_popover_option_main">
                    <label
                        class="aipkit_popover_option_label"
                        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_dynamic_threshold_modal"
                        data-tooltip="<?php echo esc_attr__('Higher requires stronger evidence (0–1).', 'gpt3-ai-content-generator'); ?>"
                    >
                        <?php esc_html_e('Retrieval threshold', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_popover_param_slider">
                        <input
                            type="range"
                            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_dynamic_threshold_modal"
                            name="google_grounding_dynamic_threshold"
                            class="aipkit_form-input aipkit_range_slider aipkit_popover_slider"
                            min="0.0"
                            max="1.0"
                            step="0.01"
                            value="<?php echo esc_attr($google_grounding_dynamic_threshold_val); ?>"
                        />
                        <span id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_grounding_dynamic_threshold_modal_value" class="aipkit_popover_param_value"><?php echo esc_html(number_format($google_grounding_dynamic_threshold_val, 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
