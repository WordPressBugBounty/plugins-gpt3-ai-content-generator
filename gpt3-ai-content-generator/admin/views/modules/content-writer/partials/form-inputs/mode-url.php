<?php
/**
 * Content Writer URL Mode tab (module-specific).
 *
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}

// $is_pro is available from the parent scope (loader-vars.php)
if (!$is_pro) {
    $upgrade_url = function_exists('wpaicg_gacg_fs') ? wpaicg_gacg_fs()->get_upgrade_url() : '#';
    ?>
    <div class="aipkit_feature_promo aipkit_feature_promo--url">
        <!-- Hero -->
        <div class="aipkit_feature_promo_hero">
            <div class="aipkit_feature_promo_icon_ring">
                <span class="dashicons dashicons-admin-links" aria-hidden="true"></span>
            </div>
            <h3 class="aipkit_feature_promo_title"><?php esc_html_e('URL Content Extraction', 'gpt3-ai-content-generator'); ?></h3>
            <p class="aipkit_feature_promo_subtitle"><?php esc_html_e('Paste any URL and let AI extract, rewrite, and publish the content for you.', 'gpt3-ai-content-generator'); ?></p>
        </div>

        <!-- Steps -->
        <div class="aipkit_feature_promo_steps">
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">1</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Paste website URLs', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">2</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('AI extracts key content', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">3</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Generate unique posts', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>

        <!-- Feature cards -->
        <div class="aipkit_feature_promo_cards">
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#2563eb" aria-hidden="true">🌐</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Any Website', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#9333ea" aria-hidden="true">✦</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Smart Extraction', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#c2410c" aria-hidden="true">⚡</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Bulk Processing', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>

        <!-- CTA -->
        <div class="aipkit_feature_promo_cta">
            <a class="aipkit_btn aipkit_btn-primary aipkit_feature_promo_btn" href="<?php echo esc_url($upgrade_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>
            </a>
            <a class="aipkit_feature_promo_link" href="https://aipower.org/docs/" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Learn more', 'gpt3-ai-content-generator'); ?>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
    <?php
    return;
}
?>
<div class="aipkit_url_import_container" data-url-container>
    <div class="aipkit_url_input_section" data-url-input-zone>
        <div class="aipkit_url_input_header">
            <span class="aipkit_url_input_icon" aria-hidden="true">
                <span class="dashicons dashicons-admin-links"></span>
            </span>
            <div class="aipkit_url_input_title">
                <label class="aipkit_url_label" for="aipkit_cw_url_list"><?php esc_html_e('Website URLs', 'gpt3-ai-content-generator'); ?></label>
                <span class="aipkit_url_sublabel"><?php esc_html_e('Enter one URL per line to extract content', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
        <div class="aipkit_url_textarea_wrapper">
            <textarea
                id="aipkit_cw_url_list"
                name="url_list"
                class="aipkit_form-input aipkit_url_textarea aipkit_autosave_trigger"
                rows="10"
                placeholder="<?php esc_attr_e("https://example.com/article-1\nhttps://blog.example.org/post", 'gpt3-ai-content-generator'); ?>"
            ></textarea>
            <div class="aipkit_url_counter">
                <span class="aipkit_url_count" data-url-count>0</span>
                <span><?php esc_html_e('URLs', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
        <div class="aipkit_url_actions">
            <button type="button" id="aipkit_cw_test_scrape_btn" class="aipkit_btn aipkit_btn-secondary aipkit_btn-small aipkit_url_test_btn">
                <span class="dashicons dashicons-visibility" aria-hidden="true"></span>
                <span class="aipkit_btn-text"><?php esc_html_e('Test First URL', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_spinner" style="display:none;"></span>
            </button>
        </div>
    </div>

    <div class="aipkit_url_status_container" id="aipkit_cw_scrape_results_wrapper" data-url-status hidden>
        <div class="aipkit_url_status_card" data-url-status-card>
            <div class="aipkit_url_status_header">
                <div class="aipkit_url_status_icon" data-url-status-icon>
                    <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                </div>
                <div class="aipkit_url_status_title_block">
                    <span class="aipkit_url_status_title"><?php esc_html_e('Content Preview', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_url_status_hint" data-url-preview-hint></span>
                </div>
                <button type="button" class="aipkit_url_status_close_btn" data-url-preview-close aria-label="<?php esc_attr_e('Close preview', 'gpt3-ai-content-generator'); ?>">
                    <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
                </button>
            </div>
            <div class="aipkit_url_preview_content">
                <pre id="aipkit_cw_scrape_results" class="aipkit_url_preview_text" data-url-preview-text></pre>
            </div>
        </div>
    </div>
</div>
