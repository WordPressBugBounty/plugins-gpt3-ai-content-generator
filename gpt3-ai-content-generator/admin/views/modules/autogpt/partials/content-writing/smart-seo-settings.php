<?php
/**
 * Partial: Content Writing Automated Task - Smart SEO Settings
 */

if (!defined('ABSPATH')) {
    exit;
}

$aipkit_task_cw_smart_seo_is_pro = class_exists('\\WPAICG\\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
$aipkit_task_cw_seo_profile = class_exists('\\WPAICG\\SEO\\AIPKit_SEO_Helper')
    ? \WPAICG\SEO\AIPKit_SEO_Helper::get_active_plugin_profile()
    : [
        'profile' => 'aipkit',
        'label' => __('AIPKit SEO', 'gpt3-ai-content-generator'),
        'logo_url' => '',
        'logo_initials' => 'AI',
    ];
$aipkit_task_cw_seo_profile_label = isset($aipkit_task_cw_seo_profile['label']) ? (string) $aipkit_task_cw_seo_profile['label'] : __('AIPKit SEO', 'gpt3-ai-content-generator');
$aipkit_task_cw_seo_profile_key = isset($aipkit_task_cw_seo_profile['profile']) ? (string) $aipkit_task_cw_seo_profile['profile'] : 'aipkit';
$aipkit_task_cw_seo_profile_logo_url = isset($aipkit_task_cw_seo_profile['logo_url']) ? (string) $aipkit_task_cw_seo_profile['logo_url'] : '';
$aipkit_task_cw_seo_profile_logo_initials = isset($aipkit_task_cw_seo_profile['logo_initials']) ? (string) $aipkit_task_cw_seo_profile['logo_initials'] : 'SEO';
$aipkit_task_cw_smart_seo_upgrade_url = function_exists('wpaicg_gacg_fs')
    ? wpaicg_gacg_fs()->get_upgrade_url()
    : admin_url('admin.php?page=wpaicg-pricing');
?>

<div
    class="aipkit_cw_ai_row aipkit_cw_seo_settings_row aipkit_cw_smart_seo_feature_card aipkit_task_cw_smart_seo_settings_row<?php echo $aipkit_task_cw_smart_seo_is_pro ? '' : ' is-pro-locked'; ?>"
    data-aipkit-task-smart-seo-settings-row
    data-aipkit-seo-active-profile="<?php echo esc_attr($aipkit_task_cw_seo_profile_key); ?>"
    data-aipkit-seo-active-profile-label="<?php echo esc_attr($aipkit_task_cw_seo_profile_label); ?>"
    data-aipkit-seo-active-profile-logo="<?php echo esc_url($aipkit_task_cw_seo_profile_logo_url); ?>"
    data-aipkit-seo-active-profile-initials="<?php echo esc_attr($aipkit_task_cw_seo_profile_logo_initials); ?>"
>
    <div class="aipkit_cw_panel_label_wrap">
        <label class="aipkit_cw_panel_label"<?php echo $aipkit_task_cw_smart_seo_is_pro ? ' for="aipkit_task_cw_seo_score_improvement_enabled"' : ''; ?>>
            <span class="aipkit_seo_settings_label">
                <span><?php esc_html_e('Smart SEO', 'gpt3-ai-content-generator'); ?></span>
                <span
                    class="aipkit_seo_profile_logo aipkit_seo_settings_logo"
                    title="<?php echo esc_attr($aipkit_task_cw_seo_profile_label); ?>"
                    aria-label="<?php echo esc_attr($aipkit_task_cw_seo_profile_label); ?>"
                    role="img"
                >
                    <?php if ($aipkit_task_cw_seo_profile_logo_url !== '') : ?>
                        <img src="<?php echo esc_url($aipkit_task_cw_seo_profile_logo_url); ?>" alt="">
                    <?php else : ?>
                        <span><?php echo esc_html($aipkit_task_cw_seo_profile_logo_initials); ?></span>
                    <?php endif; ?>
                </span>
            </span>
        </label>
        <span class="aipkit_cw_smart_seo_feature_helper">
            <?php esc_html_e('Automatically refines content until it achieves a higher SEO score.', 'gpt3-ai-content-generator'); ?>
        </span>
    </div>
    <div class="aipkit_cw_ai_control aipkit_cw_ai_control--compact">
        <div class="aipkit_cw_seo_inline_actions">
            <?php if ($aipkit_task_cw_smart_seo_is_pro) : ?>
                <input type="hidden" name="seo_score_improvement_enabled" value="0" data-aipkit-task-smart-seo-control>
                <label class="aipkit_switch aipkit_cw_seo_inline_switch" title="<?php esc_attr_e('Smart SEO auto-improvement', 'gpt3-ai-content-generator'); ?>">
                    <input
                        type="checkbox"
                        id="aipkit_task_cw_seo_score_improvement_enabled"
                        name="seo_score_improvement_enabled"
                        class="aipkit_toggle_switch aipkit_autosave_trigger"
                        value="1"
                        data-aipkit-task-smart-seo-control
                        data-aipkit-task-smart-seo-main-toggle
                    >
                    <span class="aipkit_switch_slider"></span>
                </label>
            <?php else : ?>
                <a
                    class="aipkit_cw_seo_upgrade_btn"
                    href="<?php echo esc_url($aipkit_task_cw_smart_seo_upgrade_url); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <?php esc_html_e('Pro', 'gpt3-ai-content-generator'); ?>
                </a>
                <input type="hidden" name="seo_score_improvement_enabled" value="0" data-aipkit-task-smart-seo-control>
            <?php endif; ?>
            <input type="hidden" name="seo_score_continue_until_target" value="1" data-aipkit-task-smart-seo-control>
            <input type="hidden" name="seo_score_target" value="100" data-aipkit-task-smart-seo-control>
            <input type="hidden" name="seo_score_max_passes" value="3" data-aipkit-task-smart-seo-control>
            <input type="hidden" name="seo_score_profile" value="auto" data-aipkit-task-smart-seo-control>
        </div>
    </div>
</div>
