<?php
/**
 * Partial: Automated Task Form - Content Enhancement Source Settings Wrapper
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

$aipkit_premium_partial = WPAICG_LIB_DIR . 'views/modules/autogpt/partials/content-enhancement/source-settings.php';

if (!empty($is_pro) && file_exists($aipkit_premium_partial)) {
    include $aipkit_premium_partial;
    return;
}

$upgrade_url = function_exists('wpaicg_gacg_fs') ? wpaicg_gacg_fs()->get_upgrade_url() : '#';
?>
<div class="aipkit_feature_promo aipkit_feature_promo--content-enhance">
    <div class="aipkit_feature_promo_hero">
        <div class="aipkit_feature_promo_icon_ring">
            <span class="dashicons dashicons-update" aria-hidden="true"></span>
        </div>
        <h3 class="aipkit_feature_promo_title"><?php esc_html_e('Bulk Content Enhancement', 'gpt3-ai-content-generator'); ?></h3>
        <p class="aipkit_feature_promo_subtitle"><?php esc_html_e('Automatically refresh and improve your existing posts with AI — at scale.', 'gpt3-ai-content-generator'); ?></p>
    </div>
    <div class="aipkit_feature_promo_steps">
        <div class="aipkit_feature_promo_step">
            <span class="aipkit_feature_promo_step_num">1</span>
            <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Select posts to update', 'gpt3-ai-content-generator'); ?></span>
        </div>
        <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
        <div class="aipkit_feature_promo_step">
            <span class="aipkit_feature_promo_step_num">2</span>
            <span class="aipkit_feature_promo_step_text"><?php esc_html_e('AI enhances content', 'gpt3-ai-content-generator'); ?></span>
        </div>
        <span class="aipkit_feature_promo_step_arrow" aria-hidden="true">→</span>
        <div class="aipkit_feature_promo_step">
            <span class="aipkit_feature_promo_step_num">3</span>
            <span class="aipkit_feature_promo_step_text"><?php esc_html_e('Posts updated automatically', 'gpt3-ai-content-generator'); ?></span>
        </div>
    </div>
    <div class="aipkit_feature_promo_cards">
        <div class="aipkit_feature_promo_card">
            <span class="aipkit_feature_promo_card_icon" style="color:#2563eb" aria-hidden="true">✏</span>
            <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Rewrite & Polish', 'gpt3-ai-content-generator'); ?></span>
        </div>
        <div class="aipkit_feature_promo_card">
            <span class="aipkit_feature_promo_card_icon" style="color:#16a34a" aria-hidden="true">⚙</span>
            <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Custom Prompts', 'gpt3-ai-content-generator'); ?></span>
        </div>
        <div class="aipkit_feature_promo_card">
            <span class="aipkit_feature_promo_card_icon" style="color:#9333ea" aria-hidden="true">⚡</span>
            <span class="aipkit_feature_promo_card_label"><?php esc_html_e('Bulk Processing', 'gpt3-ai-content-generator'); ?></span>
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
