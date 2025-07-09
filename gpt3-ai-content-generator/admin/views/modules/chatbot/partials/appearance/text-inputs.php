<?php

/**
 * Partial: Appearance - Text Inputs (Greeting, Footer, Placeholder) and Theme Selection
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent script:
// $bot_id, $bot_settings

$saved_greeting     = isset($bot_settings['greeting']) ? $bot_settings['greeting'] : '';
$saved_footer_text  = isset($bot_settings['footer_text']) ? $bot_settings['footer_text'] : '';
$saved_placeholder  = isset($bot_settings['input_placeholder']) ? $bot_settings['input_placeholder'] : __('Type your message...', 'gpt3-ai-content-generator');
$saved_theme = isset($bot_settings['theme']) ? $bot_settings['theme'] : 'light';

$available_themes = [
    'light'           => __('Light', 'gpt3-ai-content-generator'),
    'dark'            => __('Dark', 'gpt3-ai-content-generator'),
    'chatgpt'         => __('ChatGPT', 'gpt3-ai-content-generator'),
    'custom'          => __('Custom', 'gpt3-ai-content-generator'),
];
?>

<!-- 1) Theme, Greeting, Placeholder, Footer (4 columns in one row) -->
<div class="aipkit_form-row">
    <!-- Theme Dropdown -->
    <div class="aipkit_form-group aipkit_form-col">
        <label
            class="aipkit_form-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme"
        >
            <?php esc_html_e('Theme', 'gpt3-ai-content-generator'); ?>
        </label>
        <select
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_theme"
            name="theme"
            class="aipkit_form-input"
        >
            <?php foreach ($available_themes as $theme_key => $theme_name): ?>
                <option value="<?php echo esc_attr($theme_key); ?>" <?php selected($saved_theme, $theme_key); ?>>
                    <?php echo esc_html($theme_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Greeting -->
    <div class="aipkit_form-group aipkit_form-col">
        <label
            class="aipkit_form-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_greeting"
        >
            <?php esc_html_e('Greeting', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_greeting"
            name="greeting"
            class="aipkit_form-input"
            value="<?php echo esc_attr($saved_greeting); ?>"
            placeholder="<?php esc_attr_e('Hello! How can I help?', 'gpt3-ai-content-generator'); ?>"
        />
    </div>

    <!-- Placeholder -->
    <div class="aipkit_form-group aipkit_form-col">
        <label
            class="aipkit_form-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_input_placeholder"
        >
            <?php esc_html_e('Placeholder', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_input_placeholder"
            name="input_placeholder"
            class="aipkit_form-input"
            value="<?php echo esc_attr($saved_placeholder); ?>"
            placeholder="<?php esc_attr_e('Type your message...', 'gpt3-ai-content-generator'); ?>"
        />
    </div>

    <!-- Footer -->
    <div class="aipkit_form-group aipkit_form-col">
        <label
            class="aipkit_form-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_footer_text"
        >
            <?php esc_html_e('Footer', 'gpt3-ai-content-generator'); ?>
        </label>
        <input
            type="text"
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_footer_text"
            name="footer_text"
            class="aipkit_form-input"
            value="<?php echo esc_attr($saved_footer_text); ?>"
            placeholder="<?php esc_attr_e('Powered by AI', 'gpt3-ai-content-generator'); ?>"
        />
    </div>
</div>