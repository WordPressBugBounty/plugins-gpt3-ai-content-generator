<?php
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

$is_pro = class_exists('\\WPAICG\\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
$seo_profile = class_exists('\\WPAICG\\SEO\\AIPKit_SEO_Helper')
    ? \WPAICG\SEO\AIPKit_SEO_Helper::get_active_plugin_profile()
    : [
        'profile' => 'aipkit',
        'label' => __('AIPKit SEO', 'gpt3-ai-content-generator'),
    ];
$seo_profile_label = isset($seo_profile['label']) ? (string) $seo_profile['label'] : __('AIPKit SEO', 'gpt3-ai-content-generator');
$seo_profile_key = isset($seo_profile['profile']) ? (string) $seo_profile['profile'] : 'aipkit';
$seo_profile_logo_url = isset($seo_profile['logo_url']) ? (string) $seo_profile['logo_url'] : '';
$seo_profile_logo_initials = isset($seo_profile['logo_initials']) ? (string) $seo_profile['logo_initials'] : 'AI';
$seo_plugin_is_active = isset($seo_profile['plugin']) && (string) $seo_profile['plugin'] !== 'none';
$smart_seo_promo_description = $seo_plugin_is_active
    ? sprintf(
        /* translators: %s: active SEO plugin name. */
        __('Automatically rewrites content until it scores higher with %s.', 'gpt3-ai-content-generator'),
        $seo_profile_label
    )
    : __('Automatically rewrites content against standard SEO rules.', 'gpt3-ai-content-generator');
$seo_rules_class = '\\WPAICG\\ContentWriter\\SEO\\AIPKit_Content_Writer_Smart_SEO_Rules';
if (!class_exists($seo_rules_class) && defined('WPAICG_LIB_DIR')) {
    $seo_rules_path = WPAICG_LIB_DIR . 'content-writer/seo/class-aipkit-content-writer-smart-seo-rules.php';
    if (file_exists($seo_rules_path)) {
        require_once $seo_rules_path;
    }
}
$seo_rules_available = class_exists($seo_rules_class) && !empty($seo_rules_class::rule_catalog());
$seo_default_disabled_rules = class_exists('\\WPAICG\\ContentWriter\\SEO\\AIPKit_Content_Writer_SEO_Config')
    ? \WPAICG\ContentWriter\SEO\AIPKit_Content_Writer_SEO_Config::default_disabled_rules()
    : '[]';
$upgrade_url = admin_url('admin.php?page=wpaicg-pricing');
?>

<div class="aipkit_cw_seo_rows">
        <div
            class="aipkit_cw_ai_row aipkit_cw_seo_row aipkit_cw_seo_settings_row aipkit_cw_smart_seo_feature_card<?php echo $is_pro ? '' : ' is-pro-locked aipkit_cw_smart_seo_feature_card--promo'; ?>"
            data-aipkit-seo-settings-row
            data-aipkit-seo-active-profile="<?php echo esc_attr($seo_profile_key); ?>"
            data-aipkit-seo-active-profile-label="<?php echo esc_attr($seo_profile_label); ?>"
            data-aipkit-seo-active-profile-logo="<?php echo esc_url($seo_profile_logo_url); ?>"
            data-aipkit-seo-active-profile-initials="<?php echo esc_attr($seo_profile_logo_initials); ?>"
        >
            <?php if ($is_pro) : ?>
                <div class="aipkit_cw_panel_label_wrap">
                    <label class="aipkit_cw_panel_label" for="aipkit_cw_seo_score_improvement_enabled">
                        <?php esc_html_e('Smart SEO', 'gpt3-ai-content-generator'); ?>
                    </label>
                </div>
                <div class="aipkit_cw_ai_control aipkit_cw_ai_control--compact">
                    <div class="aipkit_cw_seo_inline_actions">
                        <label class="aipkit_switch aipkit_cw_seo_inline_switch" title="<?php esc_attr_e('Smart SEO auto-improvement', 'gpt3-ai-content-generator'); ?>">
                            <input
                                type="checkbox"
                                id="aipkit_cw_seo_score_improvement_enabled"
                                name="seo_score_improvement_enabled"
                                class="aipkit_toggle_switch aipkit_autosave_trigger"
                                value="1"
                                data-aipkit-seo-control
                                data-aipkit-seo-main-toggle
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
            <?php else : ?>
                <?php
                $aipkit_feature_promo_class = 'aipkit_feature_promo--smart-seo';
                $aipkit_feature_promo_dashicon = 'dashicons-star-filled';
                $aipkit_feature_promo_title = __('Smart SEO', 'gpt3-ai-content-generator');
                $aipkit_feature_promo_subtitle = $smart_seo_promo_description;
                $aipkit_feature_promo_steps = [];
                $aipkit_feature_promo_cards = [];
                $aipkit_feature_promo_compact = true;
                $aipkit_feature_promo_minimal = true;
                $aipkit_feature_promo_show_pro_badge = false;
                $aipkit_feature_promo_show_docs_link = false;
                $aipkit_feature_promo_upgrade_label = __('Upgrade', 'gpt3-ai-content-generator');
                $aipkit_feature_promo_upgrade_url = $upgrade_url;
                include WPAICG_PLUGIN_DIR . 'admin/views/modules/shared/feature-promo.php';
                ?>
                <input type="hidden" name="seo_score_improvement_enabled" value="0" data-aipkit-seo-control>
            <?php endif; ?>
            <input type="hidden" name="seo_score_continue_until_target" value="1" data-aipkit-seo-control>
            <input type="hidden" name="seo_score_target" value="100" data-aipkit-seo-control>
            <input type="hidden" name="seo_score_max_passes" value="3" data-aipkit-seo-control>
            <input type="hidden" name="seo_score_profile" value="auto" data-aipkit-seo-control>
            <input type="hidden" name="seo_score_disabled_rules" value="<?php echo esc_attr($seo_default_disabled_rules); ?>" class="aipkit_autosave_trigger" data-aipkit-seo-control data-aipkit-smart-seo-disabled-rules>
        </div>

        <?php if ($is_pro && $seo_rules_available) : ?>
            <div
                class="aipkit_cw_seo_row aipkit_cw_seo_approach_row"
                data-aipkit-smart-seo-rules-action
                hidden
            >
                <div class="aipkit_cw_panel_label_wrap">
                    <span
                        class="aipkit_cw_panel_label"
                        id="aipkit_cw_smart_seo_approach_label"
                    >
                        <?php esc_html_e('SEO approach', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <button
                    type="button"
                    class="aipkit_cw_seo_approach_trigger"
                    id="aipkit_cw_smart_seo_rules_trigger"
                    data-aipkit-smart-seo-modal-trigger
                    data-aipkit-smart-seo-modal-target="aipkit_cw_smart_seo_rules_popover"
                    aria-controls="aipkit_cw_smart_seo_rules_popover"
                    aria-expanded="false"
                    aria-haspopup="dialog"
                    aria-labelledby="aipkit_cw_smart_seo_approach_label aipkit_cw_smart_seo_approach_value"
                >
                    <span id="aipkit_cw_smart_seo_approach_value" data-aipkit-smart-seo-trigger-value>
                        <?php esc_html_e('Balanced', 'gpt3-ai-content-generator'); ?>
                    </span>
                    <span class="aipkit_cw_seo_approach_chevron" aria-hidden="true"></span>
                </button>
            </div>
        <?php endif; ?>

        <div class="aipkit_cw_seo_row aipkit_cw_seo_row--toc">
            <div class="aipkit_cw_panel_label_wrap">
                <label class="aipkit_cw_panel_label" for="aipkit_cw_generate_toc">
                    <?php esc_html_e('Table of contents', 'gpt3-ai-content-generator'); ?>
                </label>
            </div>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_cw_generate_toc"
                    name="generate_toc"
                    class="aipkit_toggle_switch aipkit_autosave_trigger"
                    value="1"
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>

        <div class="aipkit_cw_seo_row aipkit_cw_seo_row--slug">
            <div class="aipkit_cw_panel_label_wrap">
                <label class="aipkit_cw_panel_label" for="aipkit_cw_generate_seo_slug">
                    <?php esc_html_e('Optimize URL', 'gpt3-ai-content-generator'); ?>
                </label>
            </div>
            <label class="aipkit_switch">
                <input
                    type="checkbox"
                    id="aipkit_cw_generate_seo_slug"
                    name="generate_seo_slug"
                    class="aipkit_toggle_switch aipkit_autosave_trigger"
                    value="1"
                >
                <span class="aipkit_switch_slider"></span>
            </label>
        </div>
</div>

<?php
$aipkit_smart_seo_rules_popover_id = 'aipkit_cw_smart_seo_rules_popover';
$aipkit_smart_seo_rules_profile_key = $seo_profile_key;
$aipkit_smart_seo_rules_profile_label = $seo_profile_label;
$aipkit_smart_seo_rules_profile_logo_url = $seo_profile_logo_url;
$aipkit_smart_seo_rules_profile_logo_initials = $seo_profile_logo_initials;
$aipkit_smart_seo_rules_popover_path = defined('WPAICG_LIB_DIR') ? WPAICG_LIB_DIR . 'views/modules/shared/smart-seo-rules-popover.php' : '';
if ($is_pro && $seo_rules_available && $aipkit_smart_seo_rules_popover_path !== '' && file_exists($aipkit_smart_seo_rules_popover_path)) {
    include $aipkit_smart_seo_rules_popover_path;
}
?>
