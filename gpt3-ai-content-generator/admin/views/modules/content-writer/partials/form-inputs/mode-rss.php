<?php
/**
 * Content Writer RSS Mode tab (module-specific).
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
    <div class="aipkit_feature_promo aipkit_feature_promo--rss">
        <!-- Hero -->
        <div class="aipkit_feature_promo_hero">
            <div class="aipkit_feature_promo_icon_ring">
                <span class="dashicons dashicons-rss" aria-hidden="true"></span>
            </div>
            <h3 class="aipkit_feature_promo_title"><?php esc_html_e('RSS Feed Content Generation', 'gpt3-ai-content-generator'); ?></h3>
            <p class="aipkit_feature_promo_subtitle"><?php esc_html_e('Automatically turn RSS feeds into unique, AI-written posts — hands-free.', 'gpt3-ai-content-generator'); ?></p>
        </div>

        <!-- Steps -->
        <div class="aipkit_feature_promo_steps">
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">1</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Add your RSS feed URLs', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">2</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('AI rewrites each item', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
            <div class="aipkit_feature_promo_step">
                <span class="aipkit_feature_promo_step_num">3</span>
                <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Auto-publish to WordPress', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>

        <!-- Feature cards -->
        <div class="aipkit_feature_promo_cards">
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#c2410c" aria-hidden="true">⊞</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Multiple Feeds', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#16a34a" aria-hidden="true">⚙</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Smart Parsing', 'gpt3-ai-content-generator'); ?></span>
            </div>
            <div class="aipkit_feature_promo_card">
                <span class="aipkit_feature_promo_card_icon" style="color:#2563eb" aria-hidden="true">⏱</span>
                <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Auto-Schedule', 'gpt3-ai-content-generator'); ?></span>
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
<div class="aipkit_rss_import_container" data-rss-container>
    <div class="aipkit_rss_feeds_section" data-rss-input-zone>
        <div class="aipkit_rss_input_header">
            <span class="aipkit_rss_input_icon" aria-hidden="true">
                <span class="dashicons dashicons-rss"></span>
            </span>
            <div class="aipkit_rss_input_title">
                <label class="aipkit_rss_label" for="aipkit_cw_rss_feeds"><?php esc_html_e('RSS Feed URLs', 'gpt3-ai-content-generator'); ?></label>
                <span class="aipkit_rss_sublabel"><?php esc_html_e('Enter one feed URL per line', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
        <div class="aipkit_rss_textarea_wrapper">
            <textarea
                id="aipkit_cw_rss_feeds"
                name="rss_feeds"
                class="aipkit_form-input aipkit_rss_textarea aipkit_autosave_trigger"
                rows="10"
                placeholder="<?php esc_attr_e("https://example.com/feed/\nhttps://blog.example.org/rss", 'gpt3-ai-content-generator'); ?>"
            ></textarea>
            <div class="aipkit_rss_url_counter">
                <span class="aipkit_rss_url_count" data-rss-url-count>0</span>
                <span><?php esc_html_e('feeds', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
    </div>

    <div class="aipkit_rss_status_container" id="aipkit_cw_rss_status_container" data-rss-status hidden>
        <div class="aipkit_rss_status_card" data-rss-status-card>
            <div class="aipkit_rss_status_icon" data-rss-status-icon>
                <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
            </div>
            <div class="aipkit_rss_status_content">
                <span class="aipkit_rss_status_title" data-rss-status-title></span>
                <span class="aipkit_rss_status_message" data-rss-status-message></span>
            </div>
            <button type="button" class="aipkit_rss_status_edit_btn" data-rss-edit-btn aria-label="<?php esc_attr_e('Edit feeds', 'gpt3-ai-content-generator'); ?>">
                <span class="dashicons dashicons-edit" aria-hidden="true"></span>
            </button>
        </div>
    </div>

    <input type="hidden" id="aipkit_cw_rss_items_count" name="rss_items_count" value="0" data-rss-items-count>

    <div class="aipkit_rss_filters_grid">
        <div class="aipkit_rss_filter_card aipkit_rss_filter_card--include">
            <div class="aipkit_rss_filter_header">
                <span class="aipkit_rss_filter_icon aipkit_rss_filter_icon--include">
                    <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                </span>
                <label class="aipkit_rss_filter_label" for="aipkit_cw_rss_include_keywords">
                    <?php esc_html_e('Include', 'gpt3-ai-content-generator'); ?>
                </label>
            </div>
            <input
                type="text"
                id="aipkit_cw_rss_include_keywords"
                name="rss_include_keywords"
                class="aipkit_form-input aipkit_rss_filter_input aipkit_autosave_trigger"
                placeholder="<?php esc_attr_e('wordpress, ai, tutorial', 'gpt3-ai-content-generator'); ?>"
            >
            <span class="aipkit_rss_filter_hint"><?php esc_html_e('Only fetch if title contains these', 'gpt3-ai-content-generator'); ?></span>
        </div>

        <div class="aipkit_rss_filter_card aipkit_rss_filter_card--exclude">
            <div class="aipkit_rss_filter_header">
                <span class="aipkit_rss_filter_icon aipkit_rss_filter_icon--exclude">
                    <span class="dashicons dashicons-minus" aria-hidden="true"></span>
                </span>
                <label class="aipkit_rss_filter_label" for="aipkit_cw_rss_exclude_keywords">
                    <?php esc_html_e('Exclude', 'gpt3-ai-content-generator'); ?>
                </label>
            </div>
            <input
                type="text"
                id="aipkit_cw_rss_exclude_keywords"
                name="rss_exclude_keywords"
                class="aipkit_form-input aipkit_rss_filter_input aipkit_autosave_trigger"
                placeholder="<?php esc_attr_e('review, sponsored, update', 'gpt3-ai-content-generator'); ?>"
            >
            <span class="aipkit_rss_filter_hint"><?php esc_html_e('Skip if title contains these', 'gpt3-ai-content-generator'); ?></span>
        </div>
    </div>
</div>
