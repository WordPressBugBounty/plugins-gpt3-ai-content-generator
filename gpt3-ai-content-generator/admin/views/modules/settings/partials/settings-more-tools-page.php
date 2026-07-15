<?php
/**
 * Partial: More Tools Settings Page
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

$aipkit_plugin_icon_base_url = defined('WPAICG_PLUGIN_URL')
    ? WPAICG_PLUGIN_URL . 'admin/images/plugins/'
    : '';
$aipkit_can_install_plugins = current_user_can('install_plugins');

$aipkit_more_tools = [
    [
        'name' => __('PufferSights', 'gpt3-ai-content-generator'),
        'summary' => __('See AI crawler traffic, AI referrals, robots.txt checks, and llms.txt coverage from inside WordPress.', 'gpt3-ai-content-generator'),
        'icon_url' => $aipkit_plugin_icon_base_url . 'puffersights.svg',
        'slug' => 'puffersights-ai-crawler-insights',
        'repo_url' => 'https://wordpress.org/plugins/puffersights-ai-crawler-insights/',
    ],
    [
        'name' => __('PufferDesk', 'gpt3-ai-content-generator'),
        'summary' => __('Work in a desktop-like WordPress admin with windows, folders, search, notes, widgets, and shortcuts.', 'gpt3-ai-content-generator'),
        'icon_url' => $aipkit_plugin_icon_base_url . 'pufferdesk.svg',
        'slug' => 'pufferdesk',
        'repo_url' => 'https://wordpress.org/plugins/pufferdesk/',
    ],
    [
        'name' => __('Pufferbay', 'gpt3-ai-content-generator'),
        'summary' => __('Collect feature ideas, publish a roadmap, gather votes, and share changelogs inside WordPress.', 'gpt3-ai-content-generator'),
        'icon_url' => $aipkit_plugin_icon_base_url . 'pufferbay.svg',
        'slug' => 'pufferbay',
        'repo_url' => 'https://wordpress.org/plugins/pufferbay/',
    ],
];
?>

<div class="aipkit_settings_more_tools" id="aipkit_settings_more_tools">
    <div class="aipkit_settings_more_tools_grid" aria-label="<?php esc_attr_e('More tools from AI Puffer', 'gpt3-ai-content-generator'); ?>">
        <?php foreach ($aipkit_more_tools as $aipkit_more_tool) : ?>
            <?php
            $aipkit_more_tool_url = $aipkit_can_install_plugins
                ? self_admin_url(add_query_arg([
                    'tab' => 'plugin-information',
                    'plugin' => $aipkit_more_tool['slug'],
                    'TB_iframe' => 'true',
                    'width' => '600',
                    'height' => '550',
                ], 'plugin-install.php'))
                : $aipkit_more_tool['repo_url'];
            $aipkit_more_tool_link_class = 'aipkit_settings_more_tool_card aipkit_settings_more_tool_card_link';
            $aipkit_more_tool_aria_label = $aipkit_can_install_plugins
                ? sprintf(
                    /* translators: %s: plugin name. */
                    __('Open the WordPress installer for %s', 'gpt3-ai-content-generator'),
                    $aipkit_more_tool['name']
                )
                : sprintf(
                    /* translators: %s: plugin name. */
                    __('View %s on WordPress.org', 'gpt3-ai-content-generator'),
                    $aipkit_more_tool['name']
                );

            if ($aipkit_can_install_plugins) {
                $aipkit_more_tool_link_class .= ' thickbox open-plugin-details-modal';
            }
            ?>
            <a
                class="<?php echo esc_attr($aipkit_more_tool_link_class); ?>"
                href="<?php echo esc_url($aipkit_more_tool_url); ?>"
                aria-label="<?php echo esc_attr($aipkit_more_tool_aria_label); ?>"
                <?php if (!$aipkit_can_install_plugins) : ?>
                    target="_blank"
                    rel="noopener noreferrer"
                <?php endif; ?>
            >
                <div class="aipkit_settings_more_tool_icon" aria-hidden="true">
                    <img
                        src="<?php echo esc_url($aipkit_more_tool['icon_url']); ?>"
                        alt=""
                        loading="lazy"
                        decoding="async"
                    />
                </div>
                <div class="aipkit_settings_more_tool_body">
                    <div class="aipkit_settings_more_tool_header">
                        <div class="aipkit_settings_more_tool_title_group">
                            <h4 class="aipkit_settings_more_tool_title"><?php echo esc_html($aipkit_more_tool['name']); ?></h4>
                        </div>
                    </div>
                    <p class="aipkit_settings_more_tool_summary"><?php echo esc_html($aipkit_more_tool['summary']); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
