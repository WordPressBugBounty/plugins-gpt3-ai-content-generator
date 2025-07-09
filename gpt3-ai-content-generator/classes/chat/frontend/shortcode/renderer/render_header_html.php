<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/renderer/render_header_html.php

namespace WPAICG\Chat\Frontend\Shortcode\RendererMethods;

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
    ?>
    <div class="aipkit_chat_header">
        <div class="aipkit_header_info">
            <?php if (!$is_popup && $feature_flags['sidebar_ui_enabled']): ?>
                <button class="aipkit_header_btn aipkit_sidebar_toggle_btn" title="<?php echo esc_attr($frontend_config['text']['sidebarToggle']); ?>" aria-label="<?php echo esc_attr($frontend_config['text']['sidebarToggle']); ?>">
                    <span class="dashicons dashicons-menu-alt"></span>
                </button>
            <?php endif; ?>
        </div>
        <div class="aipkit_header_actions">
            <?php if ($feature_flags['enable_fullscreen']): ?>
                <button class="aipkit_header_btn aipkit_fullscreen_btn" title="<?php echo esc_attr($frontend_config['text']['fullscreen']); ?>" aria-label="<?php echo esc_attr($frontend_config['text']['fullscreen']); ?>" aria-expanded="false">
                    <span class="dashicons dashicons-editor-expand"></span>
                </button>
            <?php endif; ?>
            <?php if ($feature_flags['enable_download']): ?>
                <div class="aipkit_download_wrapper">
                    <button class="aipkit_header_btn aipkit_download_btn" title="<?php echo esc_attr($frontend_config['text']['download']); ?>" aria-label="<?php echo esc_attr($frontend_config['text']['download']); ?>">
                        <span class="dashicons dashicons-download"></span>
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
                <button class="aipkit_header_btn aipkit_close_btn" title="<?php echo esc_attr($frontend_config['text']['closeChat']); ?>" aria-label="<?php echo esc_attr($frontend_config['text']['closeChat']); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
}