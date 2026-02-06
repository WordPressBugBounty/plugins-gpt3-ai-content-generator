<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/dashboard.php
// Status: MODIFIED

// Silence direct access
if (!defined('ABSPATH')) {
    exit;
}

// Use the Dashboard_Beta class to retrieve module settings
use WPAICG\aipkit_dashboard;
use WPAICG\AIPKit_Role_Manager; // <-- Import Role Manager

// Retrieve the currently saved module settings
$moduleSettings = aipkit_dashboard::get_module_settings();

/**
 * Map each DB key to the nav label, dashicon, and the data-module attribute used for loading.
 * The data_module must match the folder name in /modules/ (e.g. 'chatbot', 'content-writer', etc.)
 * **AND** must match the keys used in AIPKit_Role_Manager::get_manageable_modules() for permission checks.
 *
 * Grouped by purpose for UX chunking:
 * - Primary: Core creation tools (always visible)
 * - Secondary: Supporting tools (in "More" dropdown on smaller screens or less used)
 */
$primaryModules = array(
    'chat_bot' => array(
        'label'       => __('Chatbots', 'gpt3-ai-content-generator'),
        'icon'        => 'format-chat',
        'data_module' => 'chatbot',
    ),
    'content_writer' => array(
        'label'       => __('Write', 'gpt3-ai-content-generator'),
        'icon'        => 'edit',
        'data_module' => 'content-writer',
    ),
    'autogpt' => array(
        'label'       => __('Automate', 'gpt3-ai-content-generator'),
        'icon'        => 'airplane',
        'data_module' => 'autogpt',
    ),
    'ai_forms' => array(
        'label'       => __('Forms', 'gpt3-ai-content-generator'),
        'icon'        => 'feedback',
        'data_module' => 'ai-forms',
    ),
    'image_generator' => array(
        'label'       => __('Images', 'gpt3-ai-content-generator'),
        'icon'        => 'format-image',
        'data_module' => 'image-generator',
    ),
    'sources' => array(
        'label'       => __('Sources', 'gpt3-ai-content-generator'),
        'icon'        => 'media-document',
        'data_module' => 'sources',
    ),
    'stats_viewer' => array(
        'label'       => __('Usage', 'gpt3-ai-content-generator'),
        'icon'        => 'chart-bar',
        'data_module' => 'stats',
    ),
);

// Combined for module settings dropdown
$modulesMap = $primaryModules;

// Create a nonce for AJAX requests
$aipkit_nonce = wp_create_nonce('aipkit_nonce');

?>
<div class="wrap aipkit_wrap">
    <div class="aipkit_module-tabs">
        <nav class="aipkit_module-tabs_list" role="tablist" aria-label="<?php esc_attr_e('Main navigation', 'gpt3-ai-content-generator'); ?>">
            <!-- Dashboard (Home) - Icon Only -->
            <?php if (AIPKit_Role_Manager::user_can_access_module('settings')): ?>
                <a
                    href="javascript:void(0);"
                    class="aipkit_module-tab aipkit_module-tab--home aipkit_module-link"
                    data-module="settings"
                    onclick="aipkit_loadModule('settings');"
                    aria-label="<?php esc_attr_e('Dashboard', 'gpt3-ai-content-generator'); ?>"
                    title="<?php esc_attr_e('Dashboard', 'gpt3-ai-content-generator'); ?>"
                    role="tab"
                >
                    <img
                        src="<?php echo esc_url(WPAICG_PLUGIN_URL . 'public/images/icon.svg'); ?>"
                        alt=""
                        class="aipkit_module-tab_home-logo"
                        aria-hidden="true"
                    />
                </a>
            <?php endif; ?>

            <!-- Primary Modules Group - Always visible with icons + labels -->
            <div class="aipkit_nav_group aipkit_nav_group--primary">
                <?php foreach ($primaryModules as $optionKey => $mod) :
                    $module_slug = $mod['data_module'];
                    $is_enabled = !isset($moduleSettings[$optionKey]) || !empty($moduleSettings[$optionKey]);
                    if ($is_enabled && AIPKit_Role_Manager::user_can_access_module($module_slug)): ?>
                    <a
                        href="javascript:void(0);"
                        class="aipkit_module-tab aipkit_module-link"
                        data-module="<?php echo esc_attr($module_slug); ?>"
                        onclick="aipkit_loadModule('<?php echo esc_js($module_slug); ?>')"
                        role="tab"
                    >
                        <span class="dashicons dashicons-<?php echo esc_attr($mod['icon']); ?>" aria-hidden="true"></span>
                        <span class="aipkit_module-tab_label"><?php echo esc_html($mod['label']); ?></span>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

        </nav>

        <?php if (current_user_can('manage_options')): ?>
            <div class="aipkit_module-tabs_actions">
                <?php 
                // Show upgrade button only for non-pro users
                $is_pro_plan = class_exists('\\WPAICG\\aipkit_dashboard') ? \WPAICG\aipkit_dashboard::is_pro_plan() : false;
                if (!$is_pro_plan): 
                ?>
                <button 
                    type="button" 
                    class="aipkit_upgrade_btn" 
                    id="aipkit_upgradeBtn"
                    title="<?php echo esc_attr__('Upgrade to Pro', 'gpt3-ai-content-generator'); ?>"
                >
                    <span class="aipkit_upgrade_btn_icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                        </svg>
                    </span>
                    <span class="aipkit_upgrade_btn_label"><?php esc_html_e('Upgrade', 'gpt3-ai-content-generator'); ?></span>
                </button>
                <?php endif; ?>
                <div
                    class="aipkit_modules-menu"
                    id="aipkit_modulesMenu"
                    title="<?php echo esc_attr__('Module Settings', 'gpt3-ai-content-generator'); ?>"
                >
                    <button class="aipkit_menu-trigger" type="button" aria-label="<?php echo esc_attr__('Module Settings', 'gpt3-ai-content-generator'); ?>" aria-haspopup="true" aria-expanded="false">
                        <svg class="aipkit_menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="m19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                    </button>
                    <div class="aipkit_dropdown-panel" role="dialog" aria-labelledby="aipkit_modules_panel_title">
                        <div class="aipkit_panel-header">
                            <h3 id="aipkit_modules_panel_title"><?php echo esc_html__('Modules', 'gpt3-ai-content-generator'); ?></h3>
                            <p><?php echo esc_html__('Toggle visibility of modules', 'gpt3-ai-content-generator'); ?></p>
                        </div>
                        <div class="aipkit_modules-list">
                            <?php foreach ($modulesMap as $optionKey => $mod) :
                                $checked = !isset($moduleSettings[$optionKey]) || !empty($moduleSettings[$optionKey]) ? 'checked' : '';
                                $inputId = 'aipkit_toggle_' . esc_attr($optionKey);
                            ?>
                                <label class="aipkit_module-item" for="<?php echo esc_attr($inputId); ?>">
                                    <div class="aipkit_module-info">
                                        <span class="aipkit_module-icon dashicons dashicons-<?php echo esc_attr($mod['icon']); ?>" aria-hidden="true"></span>
                                        <span class="aipkit_module-label"><?php echo esc_html($mod['label']); ?></span>
                                    </div>
                                    <span class="aipkit_toggle-switch">
                                        <input
                                            type="checkbox"
                                            id="<?php echo esc_attr($inputId); ?>"
                                            name="<?php echo esc_attr($optionKey); ?>"
                                            class="aipkit_module-toggle"
                                            data-module="<?php echo esc_attr($mod['data_module']); ?>"
                                            data-option-key="<?php echo esc_attr($optionKey); ?>"
                                            data-icon="<?php echo esc_attr($mod['icon']); ?>"
                                            data-label="<?php echo esc_attr($mod['label']); ?>"
                                            <?php echo $checked ? 'checked' : ''; ?>
                                        >
                                        <span class="aipkit_toggle-slider"></span>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main content area -->
    <div class="aipkit_main-content" id="aipkit_module-container">
        <!-- Module content will be loaded here -->
    </div>
</div>

<?php 
// Upgrade to Pro Modal - Only show for non-pro users
$is_pro_plan_for_modal = class_exists('\\WPAICG\\aipkit_dashboard') ? \WPAICG\aipkit_dashboard::is_pro_plan() : false;
if (!$is_pro_plan_for_modal && current_user_can('manage_options')): 
    $upgrade_url = function_exists('wpaicg_gacg_fs') ? wpaicg_gacg_fs()->get_upgrade_url() : admin_url('admin.php?page=wpaicg-pricing');
?>
<div class="aipkit_upgrade_modal" id="aipkit_upgradeModal">
    <div class="aipkit_modal_backdrop" data-close-modal></div>
    <div class="aipkit_modal aipkit_upgrade_modal_content">
        <div class="aipkit_modal_header">
            <h2 class="aipkit_modal_title">
                <span class="aipkit_upgrade_modal_icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </span>
                <?php esc_html_e('Unlock Pro Features', 'gpt3-ai-content-generator'); ?>
            </h2>
            <button type="button" class="aipkit_modal_close" data-close-modal aria-label="<?php esc_attr_e('Close', 'gpt3-ai-content-generator'); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="aipkit_modal_body">
            <p class="aipkit_upgrade_modal_intro">
                <?php esc_html_e('Upgrade to Pro and unlock local AI, automation triggers, voice agents, and more.', 'gpt3-ai-content-generator'); ?>
            </p>
            <p class="aipkit_upgrade_modal_note">
                <?php esc_html_e('Note: Purchasing the plugin does not include API credits. It only unlocks Pro features.', 'gpt3-ai-content-generator'); ?>
            </p>
            
            <div class="aipkit_upgrade_plans">
                <!-- Free Plan -->
                <div class="aipkit_upgrade_plan aipkit_upgrade_plan--free">
                    <div class="aipkit_plan_header">
                        <h3 class="aipkit_plan_name"><?php esc_html_e('Free', 'gpt3-ai-content-generator'); ?></h3>
                        <span class="aipkit_plan_badge aipkit_plan_badge--current"><?php esc_html_e('Current', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <ul class="aipkit_plan_features">
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('AI Chatbot with Voice I/O', 'gpt3-ai-content-generator'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Content Writer & Image Generator', 'gpt3-ai-content-generator'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('AI Forms & WooCommerce Writer', 'gpt3-ai-content-generator'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Knowledge Base / Training', 'gpt3-ai-content-generator'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('OpenAI, Google, Azure, DeepSeek', 'gpt3-ai-content-generator'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Semantic Search & Vector Stores', 'gpt3-ai-content-generator'); ?></li>
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Basic Automation Tasks', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--disabled"><span class="dashicons dashicons-no"></span><?php esc_html_e('Ollama (Local AI)', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--disabled"><span class="dashicons dashicons-no"></span><?php esc_html_e('Chatbot Triggers & Automation', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--disabled"><span class="dashicons dashicons-no"></span><?php esc_html_e('Realtime Voice Agent', 'gpt3-ai-content-generator'); ?></li>
                    </ul>
                </div>

                <!-- Pro Plan -->
                <div class="aipkit_upgrade_plan aipkit_upgrade_plan--pro">
                    <div class="aipkit_plan_header">
                        <h3 class="aipkit_plan_name"><?php esc_html_e('Pro', 'gpt3-ai-content-generator'); ?></h3>
                        <span class="aipkit_plan_badge aipkit_plan_badge--recommended"><?php esc_html_e('Recommended', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_plan_price">
                        <span class="aipkit_plan_price_amount">
                            <span class="aipkit_plan_price_currency">$</span>
                            <span class="aipkit_plan_price_major">7</span>
                            <span class="aipkit_plan_price_minor">.99</span>
                        </span>
                        <span class="aipkit_plan_price_term"><?php esc_html_e('/ mo', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <a href="<?php echo esc_url($upgrade_url); ?>" class="aipkit_btn aipkit_btn--primary aipkit_btn--upgrade aipkit_plan_cta">
                        <span class="aipkit_btn_icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                        </span>
                        <?php esc_html_e('Purchase now', 'gpt3-ai-content-generator'); ?>
                    </a>
                    <ul class="aipkit_plan_features">
                        <li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Everything in Free, plus:', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Ollama Integration (Local AI)', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Chatbot Triggers & Automation', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Realtime Voice Agent', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('File Upload for Context (PDF, TXT)', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Embed Anywhere (External Sites)', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('PDF Download of Transcripts', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Index Custom Post Types', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('RSS, URL & Google Sheets Import', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Moderation & Consent Compliance', 'gpt3-ai-content-generator'); ?></li>
                        <li class="aipkit_plan_feature--highlight"><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e('Priority Email Support', 'gpt3-ai-content-generator'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
