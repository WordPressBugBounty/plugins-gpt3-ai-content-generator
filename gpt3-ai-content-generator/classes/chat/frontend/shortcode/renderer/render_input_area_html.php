<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/renderer/render_input_area_html.php

namespace WPAICG\Chat\Frontend\Shortcode\RendererMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for rendering the chat input area HTML.
 *
 * @param array $frontend_config
 * @param bool $is_inline Whether the bot is in inline mode.
 * @param array $feature_flags Determined feature flags.
 * @param bool $allow_openai_web_search_tool Whether the OpenAI web search tool is allowed for this bot.
 * @param bool $allow_google_search_grounding Whether Google Search Grounding is allowed for this bot.
 * @return void Echos HTML.
 */
function render_input_area_html_logic(array $frontend_config, bool $is_inline = false, array $feature_flags = [], bool $allow_openai_web_search_tool = false, bool $allow_google_search_grounding = false) {
    $autofocus_attr = $is_inline ? 'autofocus' : '';
    $input_action_button_enabled = $feature_flags['input_action_button_enabled'] ?? false;
    $file_upload_ui_enabled = $feature_flags['file_upload_ui_enabled'] ?? false;
    $image_upload_ui_enabled = $feature_flags['image_upload_ui_enabled'] ?? false;
    $voice_input_enabled_ui = $feature_flags['enable_voice_input_ui'] ?? false;
    $bot_id = $frontend_config['botId'] ?? 'default';

    $initial_icon_class = 'dashicons-paperclip';
    $initial_aria_label = __('Attach files or use tools', 'gpt3-ai-content-generator');
    $initial_has_popup = 'true';

    if ($file_upload_ui_enabled && !$image_upload_ui_enabled) {
        $initial_icon_class = 'dashicons-media-document aipkit-icon-pdf';
        $initial_aria_label = __('Upload PDF', 'gpt3-ai-content-generator');
        $initial_has_popup = 'false';
    } elseif (!$file_upload_ui_enabled && $image_upload_ui_enabled) {
        $initial_icon_class = 'dashicons-format-image aipkit-icon-image';
        $initial_aria_label = __('Upload Image', 'gpt3-ai-content-generator');
        $initial_has_popup = 'false';
    }

    ?>
    <div class="aipkit_chat_input">
        <div class="aipkit_chat_input_wrapper">
            <textarea
                id="aipkit_chat_input_field_<?php echo esc_attr($bot_id); ?>"
                name="aipkit_chat_message_<?php echo esc_attr($bot_id); ?>"
                class="aipkit_chat_input_field"
                placeholder="<?php echo esc_attr($frontend_config['text']['typeMessage']); ?>"
                aria-label="<?php esc_attr_e('Chat message input', 'gpt3-ai-content-generator'); ?>"
                rows="1"
                <?php echo esc_attr($autofocus_attr); ?>
            ></textarea>
             <div class="aipkit_chat_input_actions_bar">
                <div class="aipkit_chat_input_actions_left">
                    <button
                        type="button"
                        class="aipkit_input_action_btn aipkit_input_action_toggle"
                        aria-label="<?php echo esc_attr($initial_aria_label); ?>"
                        role="button"
                        <?php if ($initial_has_popup === 'true'): ?>
                            aria-haspopup="true"
                            aria-expanded="false"
                        <?php endif; ?>
                        style="display: <?php echo $input_action_button_enabled ? 'inline-flex' : 'none'; ?>;"
                    >
                        <span class="dashicons <?php echo esc_attr($initial_icon_class); ?>"></span>
                    </button>
                     <?php if ($allow_openai_web_search_tool): ?>
                     <button
                        type="button"
                        class="aipkit_input_action_btn aipkit_web_search_toggle"
                        aria-label="<?php echo esc_attr($frontend_config['text']['webSearchToggle'] ?? __('Toggle Web Search', 'gpt3-ai-content-generator')); ?>"
                        title="<?php echo esc_attr($frontend_config['text']['webSearchInactive'] ?? __('Web Search Inactive', 'gpt3-ai-content-generator')); ?>"
                        role="button"
                        aria-pressed="false"
                        style="display: inline-flex;"
                    >
                        <span class="dashicons dashicons-admin-site"></span>
                    </button>
                    <?php endif; ?>
                    <?php if ($allow_google_search_grounding): ?>
                     <button
                        type="button"
                        class="aipkit_input_action_btn aipkit_google_search_grounding_toggle"
                        aria-label="<?php echo esc_attr($frontend_config['text']['googleSearchGroundingToggle'] ?? __('Toggle Google Search Grounding', 'gpt3-ai-content-generator')); ?>"
                        title="<?php echo esc_attr($frontend_config['text']['googleSearchGroundingInactive'] ?? __('Google Search Grounding Inactive', 'gpt3-ai-content-generator')); ?>"
                        role="button"
                        aria-pressed="false"
                        style="display: inline-flex;"
                    >
                        <span class="dashicons dashicons-google"></span>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="aipkit_chat_input_actions_right">
                    <button
                        class="aipkit_input_action_btn aipkit_voice_input_btn"
                        aria-label="<?php esc_attr_e('Voice input', 'gpt3-ai-content-generator'); ?>"
                        title="<?php esc_attr_e('Voice input', 'gpt3-ai-content-generator'); ?>"
                        type="button"
                        style="display: <?php echo $voice_input_enabled_ui ? 'inline-flex' : 'none'; ?>;"
                    >
                        <span class="dashicons dashicons-microphone"></span>
                    </button>
                    <button
                        class="aipkit_input_action_btn aipkit_chat_action_btn aipkit_send_btn"
                        aria-label="<?php echo esc_attr($frontend_config['text']['sendMessage']); ?>"
                        title="<?php echo esc_attr($frontend_config['text']['sendMessage']); ?>"
                        type="button"
                    >
                        <span class="aipkit_send_icon dashicons dashicons-arrow-up-alt"></span>
                        <span class="aipkit_clear_icon dashicons dashicons-admin-appearance" style="display:none;"></span>
                        <span class="aipkit_spinner" style="display:none;"></span>
                    </button>
                </div>
            </div>
        </div>
        <?php if ($input_action_button_enabled && ($file_upload_ui_enabled && $image_upload_ui_enabled) ): ?>
            <div class="aipkit_input_action_menu" id="aipkit_input_action_menu_<?php echo esc_attr(uniqid()); ?>" role="menu" aria-hidden="true">
                <?php if ($file_upload_ui_enabled): ?>
                    <button type="button" class="aipkit_input_action_menu_item" role="menuitem" aria-label="<?php esc_attr_e('Upload PDF', 'gpt3-ai-content-generator'); ?>"><span class="dashicons dashicons-media-document"></span></button>
                <?php endif; ?>
                <?php if ($image_upload_ui_enabled): ?>
                    <button type="button" class="aipkit_input_action_menu_item" role="menuitem" aria-label="<?php esc_attr_e('Upload image', 'gpt3-ai-content-generator'); ?>"><span class="dashicons dashicons-format-image"></span></button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}