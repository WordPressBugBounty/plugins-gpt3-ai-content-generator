<?php
/**
 * Partial: AutoGPT AI Settings (Redesigned)
 *
 * Expected variables:
 * - $aipkit_ai_provider_id
 * - $aipkit_ai_provider_name
 * - $aipkit_ai_model_id
 * - $aipkit_ai_model_name
 * - $aipkit_ai_temperature_id
 * - $aipkit_ai_temperature_name
 * - $aipkit_ai_temperature_default
 * - $aipkit_ai_reasoning_id
 * - $aipkit_ai_reasoning_name
 * - $aipkit_ai_reasoning_wrapper_class
 * - $aipkit_ai_length_mode (content_length|max_tokens)
 * - $aipkit_ai_length_input_id
 * - $aipkit_ai_length_input_name
 * - $aipkit_ai_length_default
 * - $aipkit_ai_length_short_tokens
 * - $aipkit_ai_length_medium_tokens
 * - $aipkit_ai_length_long_tokens
 * - $aipkit_ai_providers_for_select (optional)
 * - $aipkit_ai_provider_notice_target (optional)
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_ai_provider_id = isset($aipkit_ai_provider_id) ? (string) $aipkit_ai_provider_id : '';
$aipkit_ai_provider_name = isset($aipkit_ai_provider_name) ? (string) $aipkit_ai_provider_name : '';
$aipkit_ai_model_id = isset($aipkit_ai_model_id) ? (string) $aipkit_ai_model_id : '';
$aipkit_ai_model_name = isset($aipkit_ai_model_name) ? (string) $aipkit_ai_model_name : '';
$aipkit_ai_temperature_id = isset($aipkit_ai_temperature_id) ? (string) $aipkit_ai_temperature_id : '';
$aipkit_ai_temperature_name = isset($aipkit_ai_temperature_name) ? (string) $aipkit_ai_temperature_name : '';
$aipkit_ai_temperature_default = isset($aipkit_ai_temperature_default) ? (string) $aipkit_ai_temperature_default : '1';
$aipkit_ai_reasoning_id = isset($aipkit_ai_reasoning_id) ? (string) $aipkit_ai_reasoning_id : '';
$aipkit_ai_reasoning_name = isset($aipkit_ai_reasoning_name) ? (string) $aipkit_ai_reasoning_name : '';
$aipkit_ai_reasoning_wrapper_class = isset($aipkit_ai_reasoning_wrapper_class) ? (string) $aipkit_ai_reasoning_wrapper_class : '';
$aipkit_ai_length_mode = isset($aipkit_ai_length_mode) ? (string) $aipkit_ai_length_mode : 'content_length';
$aipkit_ai_length_input_id = isset($aipkit_ai_length_input_id) ? (string) $aipkit_ai_length_input_id : '';
$aipkit_ai_length_input_name = isset($aipkit_ai_length_input_name) ? (string) $aipkit_ai_length_input_name : '';
$aipkit_ai_length_default = isset($aipkit_ai_length_default) ? (string) $aipkit_ai_length_default : 'medium';
$aipkit_ai_length_short_tokens = isset($aipkit_ai_length_short_tokens) ? (int) $aipkit_ai_length_short_tokens : 2000;
$aipkit_ai_length_medium_tokens = isset($aipkit_ai_length_medium_tokens) ? (int) $aipkit_ai_length_medium_tokens : 4000;
$aipkit_ai_length_long_tokens = isset($aipkit_ai_length_long_tokens) ? (int) $aipkit_ai_length_long_tokens : 6000;

$aipkit_ai_providers_for_select = isset($aipkit_ai_providers_for_select) && is_array($aipkit_ai_providers_for_select)
    ? $aipkit_ai_providers_for_select
    : (isset($cw_providers_for_select) && is_array($cw_providers_for_select) ? $cw_providers_for_select : []);

$aipkit_ai_provider_notice_target = isset($aipkit_ai_provider_notice_target)
    ? (string) $aipkit_ai_provider_notice_target
    : 'aipkit_provider_notice_autogpt';

$aipkit_ai_length_default = in_array($aipkit_ai_length_default, ['short', 'medium', 'long'], true)
    ? $aipkit_ai_length_default
    : 'medium';
$aipkit_ai_length_mode = $aipkit_ai_length_mode === 'max_tokens' ? 'max_tokens' : 'content_length';

$length_label_map = [
    'short' => __('Short', 'gpt3-ai-content-generator'),
    'medium' => __('Medium', 'gpt3-ai-content-generator'),
    'long' => __('Long', 'gpt3-ai-content-generator'),
];
$length_hint_map = [
    'short' => __('Approx. 600-800 words', 'gpt3-ai-content-generator'),
    'medium' => __('Approx. 1200-1600 words', 'gpt3-ai-content-generator'),
    'long' => __('Approx. 2000-2500 words', 'gpt3-ai-content-generator'),
];
$length_token_map = [
    'short' => $aipkit_ai_length_short_tokens,
    'medium' => $aipkit_ai_length_medium_tokens,
    'long' => $aipkit_ai_length_long_tokens,
];
$length_token_default = isset($length_token_map[$aipkit_ai_length_default])
    ? $length_token_map[$aipkit_ai_length_default]
    : $aipkit_ai_length_medium_tokens;
$length_slider_value = $aipkit_ai_length_default === 'short' ? 1 : ($aipkit_ai_length_default === 'long' ? 3 : 2);
$aipkit_ai_length_slider_id = $aipkit_ai_length_input_id ? $aipkit_ai_length_input_id . '_slider' : '';
$aipkit_ai_length_value_id = $aipkit_ai_length_input_id ? $aipkit_ai_length_input_id . '_value' : '';
$aipkit_ai_length_hint_id = $aipkit_ai_length_input_id ? $aipkit_ai_length_input_id . '_hint' : '';

$default_provider = strtolower(WPAICG\AIPKit_Providers::get_current_provider());
$is_pro = class_exists('\\WPAICG\\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
?>

<div class="aipkit_ai_settings_redesigned">
    <div class="aipkit_ai_settings_chunk aipkit_ai_settings_chunk--model">
        <div class="aipkit_ai_settings_chunk_body">
            <div class="aipkit_ai_model_selector">
                <div class="aipkit_ai_model_selector_row">
                    <label class="aipkit_ai_settings_label" for="<?php echo esc_attr($aipkit_ai_provider_id); ?>">
                        <?php esc_html_e('Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="<?php echo esc_attr($aipkit_ai_provider_id); ?>"
                        name="<?php echo esc_attr($aipkit_ai_provider_name); ?>"
                        class="aipkit_ai_settings_select aipkit_autosave_trigger"
                        data-aipkit-provider-notice-target="<?php echo esc_attr($aipkit_ai_provider_notice_target); ?>"
                        data-aipkit-provider-notice-defer="1"
                    >
                        <?php
                        if (!empty($aipkit_ai_providers_for_select)) {
                            foreach ($aipkit_ai_providers_for_select as $provider_label) {
                                $provider_value = strtolower($provider_label);
                                $disabled = false;
                                $label = $provider_label;
                                if ($provider_label === 'Ollama' && !$is_pro) {
                                    $disabled = true;
                                    $label = __('Ollama (Pro)', 'gpt3-ai-content-generator');
                                }
                                echo '<option value="' . esc_attr($provider_value) . '"' . selected($default_provider, $provider_value, false) . ($disabled ? ' disabled' : '') . '>' . esc_html($label) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="aipkit_ai_model_selector_divider">
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </div>

                <div class="aipkit_ai_model_selector_row aipkit_ai_model_selector_row--model">
                    <label class="aipkit_ai_settings_label" for="<?php echo esc_attr($aipkit_ai_model_id); ?>">
                        <?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select
                        id="<?php echo esc_attr($aipkit_ai_model_id); ?>"
                        name="<?php echo esc_attr($aipkit_ai_model_name); ?>"
                        class="aipkit_ai_settings_select aipkit_autosave_trigger"
                    ></select>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_ai_settings_chunk aipkit_ai_settings_chunk--behavior">
        <div class="aipkit_ai_settings_chunk_body">
            <div class="aipkit_ai_behavior_control aipkit_ai_temperature_control">
                <div class="aipkit_ai_behavior_header">
                    <label class="aipkit_ai_settings_label" for="<?php echo esc_attr($aipkit_ai_temperature_id); ?>">
                        <?php esc_html_e('Creativity', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_ai_behavior_value" id="<?php echo esc_attr($aipkit_ai_temperature_id); ?>_value">
                        <?php echo esc_html($aipkit_ai_temperature_default); ?>
                    </span>
                </div>

                <div class="aipkit_ai_temperature_slider_wrapper">
                    <div class="aipkit_ai_temperature_labels">
                        <span class="aipkit_ai_temperature_label aipkit_ai_temperature_label--low">
                            <span class="dashicons dashicons-editor-textcolor" aria-hidden="true"></span>
                            <?php esc_html_e('Focused', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <span class="aipkit_ai_temperature_label aipkit_ai_temperature_label--high">
                            <span class="dashicons dashicons-art" aria-hidden="true"></span>
                            <?php esc_html_e('Creative', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input
                        type="range"
                        id="<?php echo esc_attr($aipkit_ai_temperature_id); ?>"
                        name="<?php echo esc_attr($aipkit_ai_temperature_name); ?>"
                        class="aipkit_ai_temperature_slider aipkit_autosave_trigger"
                        min="0"
                        max="2"
                        step="0.1"
                        value="<?php echo esc_attr($aipkit_ai_temperature_default); ?>"
                    >
                </div>

                <p class="aipkit_ai_behavior_hint">
                    <?php esc_html_e('Lower = more predictable, higher = more varied output', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>

            <div
                class="aipkit_ai_behavior_control aipkit_ai_length_control"
                data-aipkit-length-mode="<?php echo esc_attr($aipkit_ai_length_mode); ?>"
                data-aipkit-length-default="<?php echo esc_attr($aipkit_ai_length_default); ?>"
                data-aipkit-length-short="<?php echo esc_attr($aipkit_ai_length_short_tokens); ?>"
                data-aipkit-length-medium="<?php echo esc_attr($aipkit_ai_length_medium_tokens); ?>"
                data-aipkit-length-long="<?php echo esc_attr($aipkit_ai_length_long_tokens); ?>"
            >
                <div class="aipkit_ai_behavior_header">
                    <label class="aipkit_ai_settings_label" for="<?php echo esc_attr($aipkit_ai_length_slider_id); ?>">
                        <?php esc_html_e('Content length', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_ai_behavior_value" id="<?php echo esc_attr($aipkit_ai_length_value_id); ?>">
                        <?php echo esc_html($length_label_map[$aipkit_ai_length_default]); ?>
                    </span>
                </div>

                <div class="aipkit_ai_length_slider_wrapper">
                    <div class="aipkit_ai_length_labels">
                        <span class="aipkit_ai_length_label"><?php esc_html_e('Short', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_ai_length_label"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_ai_length_label"><?php esc_html_e('Long', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <input
                        type="range"
                        id="<?php echo esc_attr($aipkit_ai_length_slider_id); ?>"
                        class="aipkit_ai_length_slider"
                        min="1"
                        max="3"
                        step="1"
                        value="<?php echo esc_attr($length_slider_value); ?>"
                    >
                </div>

                <p class="aipkit_ai_behavior_hint" id="<?php echo esc_attr($aipkit_ai_length_hint_id); ?>">
                    <?php echo esc_html($length_hint_map[$aipkit_ai_length_default]); ?>
                </p>

                <?php if ($aipkit_ai_length_mode === 'content_length') : ?>
                    <select
                        id="<?php echo esc_attr($aipkit_ai_length_input_id); ?>"
                        name="<?php echo esc_attr($aipkit_ai_length_input_name); ?>"
                        class="aipkit_hidden_form_field aipkit_autosave_trigger"
                        data-aipkit-length-input="true"
                    >
                        <option value="short" <?php selected($aipkit_ai_length_default, 'short'); ?>><?php esc_html_e('Short', 'gpt3-ai-content-generator'); ?></option>
                        <option value="medium" <?php selected($aipkit_ai_length_default, 'medium'); ?>><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                        <option value="long" <?php selected($aipkit_ai_length_default, 'long'); ?>><?php esc_html_e('Long', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                <?php else : ?>
                    <input
                        type="hidden"
                        id="<?php echo esc_attr($aipkit_ai_length_input_id); ?>"
                        name="<?php echo esc_attr($aipkit_ai_length_input_name); ?>"
                        value="<?php echo esc_attr($length_token_default); ?>"
                        data-aipkit-length-input="true"
                    >
                <?php endif; ?>
            </div>

            <div class="aipkit_ai_behavior_control <?php echo esc_attr($aipkit_ai_reasoning_wrapper_class); ?>" style="display: none;">
                <div class="aipkit_ai_behavior_header">
                    <label class="aipkit_ai_settings_label aipkit_ai_settings_label--with-badge" for="<?php echo esc_attr($aipkit_ai_reasoning_id); ?>">
                        <?php esc_html_e('Reasoning', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_ai_settings_badge"><?php esc_html_e('Advanced', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                </div>

                <div class="aipkit_ai_reasoning_options">
                    <?php
                    $reasoning_options = [
                        'none' => [
                            'label' => __('None', 'gpt3-ai-content-generator'),
                            'desc'  => __('No reason', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-dismiss',
                        ],
                        'low' => [
                            'label' => __('Low', 'gpt3-ai-content-generator'),
                            'desc'  => __('Faster', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-minus',
                        ],
                        'medium' => [
                            'label' => __('Medium', 'gpt3-ai-content-generator'),
                            'desc'  => __('Thoughtful', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-lightbulb',
                        ],
                        'high' => [
                            'label' => __('High', 'gpt3-ai-content-generator'),
                            'desc'  => __('Deep', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-admin-network',
                        ],
                        'xhigh' => [
                            'label' => __('XHigh', 'gpt3-ai-content-generator'),
                            'desc'  => __('Maximum', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-chart-line',
                        ],
                    ];
                    $reasoning_default = 'medium';
                    foreach ($reasoning_options as $value => $option) : ?>
                        <label class="aipkit_ai_reasoning_option <?php echo $value === $reasoning_default ? 'aipkit_ai_reasoning_option--selected' : ''; ?>">
                            <input
                                type="radio"
                                name="<?php echo esc_attr($aipkit_ai_reasoning_name . '_choice'); ?>"
                                value="<?php echo esc_attr($value); ?>"
                                class="aipkit_ai_reasoning_input"
                                <?php checked($value, $reasoning_default); ?>
                            >
                            <span class="aipkit_ai_reasoning_option_content">
                                <span class="aipkit_ai_reasoning_option_icon">
                                    <span class="dashicons <?php echo esc_attr($option['icon']); ?>" aria-hidden="true"></span>
                                </span>
                                <span class="aipkit_ai_reasoning_option_text">
                                    <span class="aipkit_ai_reasoning_option_label"><?php echo esc_html($option['label']); ?></span>
                                    <span class="aipkit_ai_reasoning_option_desc"><?php echo esc_html($option['desc']); ?></span>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <p class="aipkit_ai_behavior_hint">
                    <?php esc_html_e('For o-series, GPT-5 models. Higher reasoning = slower but more thorough.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
        </div>
    </div>

    <select
        id="<?php echo esc_attr($aipkit_ai_reasoning_id); ?>"
        name="<?php echo esc_attr($aipkit_ai_reasoning_name); ?>"
        class="aipkit_hidden_form_field"
        data-aipkit-reasoning-select="true"
    >
        <option value="none"><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
        <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
        <option value="medium" selected><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
        <option value="high"><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
        <option value="xhigh"><?php esc_html_e('XHigh', 'gpt3-ai-content-generator'); ?></option>
    </select>
</div>
