<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/renderer/render_header_html.php

namespace WPAICG\Chat\Frontend\Shortcode\RendererMethods;

use WPAICG\Chat\Utils\AIPKit_SVG_Icons;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for rendering the chat header HTML.
 *
 * @param array $feature_flags
 * @param array $frontend_config
 * @param bool $is_popup
 * @return void Echos HTML.
 */
function render_header_html_logic(array $feature_flags, array $frontend_config, bool $is_popup) {
    // SVG definitions
    $sidebar_toggle_svg = '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-menu-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 6l16 0" /><path d="M4 12l16 0" /><path d="M4 18l16 0" /></svg>';
    $fullscreen_svg = '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-arrows-maximize"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16 4l4 0l0 4" /><path d="M14 10l6 -6" /><path d="M8 20l-4 0l0 -4" /><path d="M4 20l6 -6" /><path d="M16 20l4 0l0 -4" /><path d="M14 14l6 6" /><path d="M8 4l-4 0l0 4" /><path d="M4 4l6 6" /></svg>';
    $download_svg = '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>';
    $close_svg = '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>';
    $header_avatar_type = isset($frontend_config['headerAvatarType']) ? (string) $frontend_config['headerAvatarType'] : '';
    $header_avatar_value = isset($frontend_config['headerAvatarValue']) ? (string) $frontend_config['headerAvatarValue'] : '';
    $header_avatar_url = '';
    $header_avatar_svg = '';
    $popup_icon_url = '';
    $popup_icon_svg = '';
    $resolve_icon_svg = function (string $icon_key): string {
        switch ($icon_key) {
            case 'spark':
                return AIPKit_SVG_Icons::get_spark_svg();
            case 'openai':
                return AIPKit_SVG_Icons::get_openai_svg();
            case 'plus':
                return AIPKit_SVG_Icons::get_plus_svg();
            case 'question-mark':
                return AIPKit_SVG_Icons::get_question_mark_svg();
            case 'chat-bubble':
            default:
                return AIPKit_SVG_Icons::get_chat_bubble_svg();
        }
    };
    $allowed_header_icons = ['chat-bubble', 'spark', 'openai', 'plus', 'question-mark'];
    if ($is_popup) {
        if ($header_avatar_type === 'custom') {
            if ($header_avatar_value !== '') {
                $header_avatar_url = $header_avatar_value;
            } else {
                $legacy_header_url = isset($frontend_config['headerAvatarUrl']) ? trim((string) $frontend_config['headerAvatarUrl']) : '';
                $header_avatar_url = $legacy_header_url;
            }
        } elseif ($header_avatar_type === 'default') {
            $icon_key = in_array($header_avatar_value, $allowed_header_icons, true) ? $header_avatar_value : 'chat-bubble';
            $header_avatar_svg = $resolve_icon_svg($icon_key);
        } else {
            $legacy_header_url = isset($frontend_config['headerAvatarUrl']) ? trim((string) $frontend_config['headerAvatarUrl']) : '';
            $header_avatar_url = $legacy_header_url;
        }

        if ($header_avatar_url === '' && $header_avatar_svg === '') {
            $popup_icon_type = isset($frontend_config['popupIconType']) ? (string) $frontend_config['popupIconType'] : '';
            $popup_icon_value = isset($frontend_config['popupIconValue']) ? (string) $frontend_config['popupIconValue'] : '';
            if ($popup_icon_type === 'custom' && $popup_icon_value !== '') {
                $popup_icon_url = $popup_icon_value;
            } elseif ($popup_icon_value !== '') {
                $popup_icon_svg = $resolve_icon_svg($popup_icon_value);
            }
        }
    }
    $header_name = isset($frontend_config['headerName']) ? trim((string) $frontend_config['headerName']) : '';
    if ($header_name === '') {
        $header_name = __('Chatbot', 'gpt3-ai-content-generator');
    }
    $header_online_text = isset($frontend_config['headerOnlineText']) ? trim((string) $frontend_config['headerOnlineText']) : '';
    if ($header_online_text === '') {
        $header_online_text = __('Online', 'gpt3-ai-content-generator');
    }
    $fallback_avatar_svg = class_exists(AIPKit_SVG_Icons::class)
        ? AIPKit_SVG_Icons::get_chat_bubble_svg()
        : '';
    ?>
    <div class="aipkit_chat_header">
        <div class="aipkit_header_info">
            <?php if (!$is_popup && $feature_flags['sidebar_ui_enabled']): ?>
                <button class="aipkit_header_btn aipkit_sidebar_toggle_btn" title="<?php echo esc_attr($frontend_config['text']['sidebarToggle']); ?>" aria-label="<?php echo esc_attr($frontend_config['text']['sidebarToggle']); ?>">
                    <?php echo $sidebar_toggle_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </button>
            <?php endif; ?>
            <?php if ($is_popup): ?>
                <div class="aipkit_header_identity">
                <div class="aipkit_header_avatar aipkit_header_icon">
                    <?php if (!empty($header_avatar_url)) : ?>
                        <img src="<?php echo esc_url($header_avatar_url); ?>" alt="<?php echo esc_attr($header_name); ?>" class="aipkit_header_avatar_img" />
                    <?php elseif (!empty($header_avatar_svg)) : ?>
                        <span class="aipkit_header_avatar_icon" aria-hidden="true">
                            <?php echo $header_avatar_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </span>
                    <?php elseif (!empty($popup_icon_url)) : ?>
                        <img src="<?php echo esc_url($popup_icon_url); ?>" alt="<?php echo esc_attr($header_name); ?>" class="aipkit_header_avatar_img" />
                    <?php elseif (!empty($popup_icon_svg)) : ?>
                        <span class="aipkit_header_avatar_icon" aria-hidden="true">
                            <?php echo $popup_icon_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </span>
                    <?php elseif (!empty($fallback_avatar_svg)) : ?>
                        <span class="aipkit_header_avatar_icon" aria-hidden="true">
                            <?php echo $fallback_avatar_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </span>
                    <?php endif; ?>
                </div>
                    <div class="aipkit_header_meta">
                        <div class="aipkit_header_name"><?php echo esc_html($header_name); ?></div>
                        <?php if (!empty($header_online_text)) : ?>
                            <div class="aipkit_header_status">
                                <span class="aipkit_header_status_dot" aria-hidden="true"></span>
                                <span class="aipkit_header_status_text"><?php echo esc_html($header_online_text); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="aipkit_header_actions">
            <?php if ($feature_flags['enable_fullscreen']): ?>
                <button class="aipkit_header_btn aipkit_fullscreen_btn" title="<?php echo esc_attr($frontend_config['text']['fullscreen']); ?>" aria-label="<?php echo esc_attr($frontend_config['text']['fullscreen']); ?>" aria-expanded="false">
                    <?php echo $fullscreen_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </button>
            <?php endif; ?>
            <?php if ($feature_flags['enable_download']): ?>
                <div class="aipkit_download_wrapper">
                    <button class="aipkit_header_btn aipkit_download_btn" title="<?php echo esc_attr($frontend_config['text']['download']); ?>" aria-label="<?php echo esc_attr($frontend_config['text']['download']); ?>">
                        <?php echo $download_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    </button>
                    <?php if ($feature_flags['pdf_ui_enabled']): ?>
                        <div class="aipkit_download_menu">
                            <div class="aipkit_download_menu_item" data-format="txt"><?php echo esc_html($frontend_config['text']['downloadTxt']); ?></div>
                            <div class="aipkit_download_menu_item" data-format="pdf"><?php echo esc_html($frontend_config['text']['downloadPdf']); ?></div>
                        </div>
                    <?php elseif ($feature_flags['enable_download']): ?>
                        <div class="aipkit_download_menu">
                            <div class="aipkit_download_menu_item" data-format="txt"><?php echo esc_html($frontend_config['text']['downloadTxt']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($is_popup): ?>
                <button class="aipkit_header_btn aipkit_popup_close_btn" title="<?php echo esc_attr__('Close chat', 'gpt3-ai-content-generator'); ?>" aria-label="<?php echo esc_attr__('Close chat', 'gpt3-ai-content-generator'); ?>">
                    <?php echo $close_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
