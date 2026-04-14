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
$aipkit_show_rss_fetch_button = !empty($aipkit_show_rss_fetch_button);
$aipkit_premium_partial = WPAICG_LIB_DIR . 'views/modules/content-writer/partials/form-inputs/mode-rss.php';

if ($is_pro && file_exists($aipkit_premium_partial)) {
    include $aipkit_premium_partial;
    return;
}

{
    $upgrade_url = function_exists('wpaicg_gacg_fs') ? wpaicg_gacg_fs()->get_upgrade_url() : '#';
    ?>
    <div class="aipkit_feature_promo aipkit_feature_promo--rss">
        <div class="aipkit_feature_promo_hero">
            <div class="aipkit_feature_promo_icon_ring">
                <span class="dashicons dashicons-rss" aria-hidden="true"></span>
            </div>
            <h3 class="aipkit_feature_promo_title"><?php esc_html_e('RSS Feed Content Generation', 'gpt3-ai-content-generator'); ?></h3>
            <p class="aipkit_feature_promo_subtitle"><?php esc_html_e('Automatically turn RSS feeds into unique, AI-written posts — hands-free.', 'gpt3-ai-content-generator'); ?></p>
        </div>

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

        <div class="aipkit_feature_promo_cta">
            <a class="aipkit_btn aipkit_btn-primary aipkit_feature_promo_btn" href="<?php echo esc_url($upgrade_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?>
            </a>
            <a class="aipkit_feature_promo_link" href="https://docs.aipower.org/" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e('Learn more', 'gpt3-ai-content-generator'); ?>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
    <?php
}
