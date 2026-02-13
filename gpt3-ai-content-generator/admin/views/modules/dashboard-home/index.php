<?php
/**
 * AIPKit Dashboard Home — Module Selection Landing
 */

use WPAICG\aipkit_dashboard;
use WPAICG\AIPKit_Role_Manager;

if (!defined('ABSPATH')) {
    exit;
}

$module_settings = aipkit_dashboard::get_module_settings();
$is_admin = current_user_can('manage_options');

$modules = array(
    'chat_bot' => array(
        'label'       => __('Chatbots', 'gpt3-ai-content-generator'),
        'description' => __('Build assistants, configure behavior, and deploy chat experiences.', 'gpt3-ai-content-generator'),
        'icon'        => 'format-chat',
        'data_module' => 'chatbot',
    ),
    'content_writer' => array(
        'label'       => __('Content Writer', 'gpt3-ai-content-generator'),
        'description' => __('Create articles, pages, and product content from structured prompts.', 'gpt3-ai-content-generator'),
        'icon'        => 'edit',
        'data_module' => 'content-writer',
    ),
    'autogpt' => array(
        'label'       => __('Automations', 'gpt3-ai-content-generator'),
        'description' => __('Run scheduled workflows and recurring AI tasks with less manual work.', 'gpt3-ai-content-generator'),
        'icon'        => 'airplane',
        'data_module' => 'autogpt',
    ),
    'ai_forms' => array(
        'label'       => __('Forms', 'gpt3-ai-content-generator'),
        'description' => __('Collect user input and generate AI-powered responses.', 'gpt3-ai-content-generator'),
        'icon'        => 'feedback',
        'data_module' => 'ai-forms',
    ),
    'image_generator' => array(
        'label'       => __('Images', 'gpt3-ai-content-generator'),
        'description' => __('Generate and iterate on images using model-specific settings.', 'gpt3-ai-content-generator'),
        'icon'        => 'format-image',
        'data_module' => 'image-generator',
    ),
    'sources' => array(
        'label'       => __('Knowledge Base', 'gpt3-ai-content-generator'),
        'description' => __('Manage data sources, embeddings, and retrieval context for AI.', 'gpt3-ai-content-generator'),
        'icon'        => 'media-document',
        'data_module' => 'sources',
    ),
    'stats_viewer' => array(
        'label'       => __('Usage', 'gpt3-ai-content-generator'),
        'description' => __('Track token consumption, activity trends, and usage patterns.', 'gpt3-ai-content-generator'),
        'icon'        => 'chart-bar',
        'data_module' => 'stats',
    ),
);

$available_modules = array();
foreach ($modules as $option_key => $module) {
    if (!AIPKit_Role_Manager::user_can_access_module($module['data_module'])) {
        continue;
    }

    $is_enabled = !isset($module_settings[$option_key]) || !empty($module_settings[$option_key]);
    $module['option_key'] = $option_key;
    $module['is_enabled'] = $is_enabled;
    $available_modules[] = $module;
}

$available_count = count($available_modules);
?>
<div class="aipkit_home" id="aipkit_dashboard_home">
    <div class="aipkit_home_layout">
        <section class="aipkit_home_primary" aria-labelledby="aipkit_home_title">
            <header class="aipkit_home_hero">
                <p class="aipkit_home_eyebrow"><?php esc_html_e('AI Puffer Platform', 'gpt3-ai-content-generator'); ?></p>
                <h2 class="aipkit_home_title" id="aipkit_home_title"><?php esc_html_e('Your AI engine for WordPress.', 'gpt3-ai-content-generator'); ?></h2>
                <p class="aipkit_home_summary">
                    <?php esc_html_e('Chat, write, automate, and generate — all in one workspace.', 'gpt3-ai-content-generator'); ?>
                </p>
            </header>

            <section class="aipkit_home_section aipkit_home_section--modules" aria-labelledby="aipkit_home_modules_title">
                <div class="aipkit_home_section_header">
                    <h3 class="aipkit_home_section_title" id="aipkit_home_modules_title"><?php esc_html_e('Modules', 'gpt3-ai-content-generator'); ?></h3>
                </div>

                <?php if ($available_count === 0): ?>
                    <div class="aipkit_home_module_empty">
                        <p><?php esc_html_e('No modules are available for your role right now.', 'gpt3-ai-content-generator'); ?></p>
                    </div>
                <?php else: ?>
                    <div class="aipkit_home_module_list" id="aipkit_home_module_list">
                        <?php foreach ($available_modules as $module): ?>
                            <?php
                            $row_class = 'aipkit_home_module_row';
                            if (!$module['is_enabled']) {
                                $row_class .= ' is-disabled';
                            }
                            ?>
                            <article
                                class="<?php echo esc_attr($row_class); ?>"
                                data-module="<?php echo esc_attr($module['data_module']); ?>"
                                data-module-label="<?php echo esc_attr($module['label']); ?>"
                            >
                                <div
                                    class="aipkit_home_module_main"
                                    role="button"
                                    tabindex="<?php echo $module['is_enabled'] ? '0' : '-1'; ?>"
                                    aria-disabled="<?php echo $module['is_enabled'] ? 'false' : 'true'; ?>"
                                    <?php if ($module['is_enabled']): ?>
                                        data-aipkit-open-module="<?php echo esc_attr($module['data_module']); ?>"
                                    <?php endif; ?>
                                >
                                    <div class="aipkit_home_module_text">
                                        <h4 class="aipkit_home_module_title"><?php echo esc_html($module['label']); ?></h4>
                                        <p class="aipkit_home_module_desc"><?php echo esc_html($module['description']); ?></p>
                                    </div>
                                </div>

                                <?php if ($is_admin): ?>
                                    <div class="aipkit_home_module_controls">
                                        <label class="aipkit_home_toggle" for="aipkit_home_toggle_<?php echo esc_attr($module['option_key']); ?>">
                                            <span class="screen-reader-text"><?php esc_html_e('Enable module', 'gpt3-ai-content-generator'); ?></span>
                                            <span class="aipkit_home_toggle_switch">
                                                <input
                                                    type="checkbox"
                                                    id="aipkit_home_toggle_<?php echo esc_attr($module['option_key']); ?>"
                                                    class="aipkit_home_toggle_input"
                                                    data-option-key="<?php echo esc_attr($module['option_key']); ?>"
                                                    data-module="<?php echo esc_attr($module['data_module']); ?>"
                                                    <?php checked($module['is_enabled']); ?>
                                                    aria-label="<?php echo esc_attr(sprintf(__('Enable %s module', 'gpt3-ai-content-generator'), $module['label'])); ?>"
                                                />
                                                <span class="aipkit_home_toggle_slider" aria-hidden="true"></span>
                                            </span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </section>

        <aside class="aipkit_home_sidebar">
            <section class="aipkit_home_panel aipkit_home_panel--setup">
                <h3 class="aipkit_home_panel_title"><?php esc_html_e('Quick Setup', 'gpt3-ai-content-generator'); ?></h3>
                <ol class="aipkit_home_steps">
                    <li>
                        <?php
                        $settings_link = '<a href="#" data-aipkit-open-module="settings">' . esc_html__('Settings', 'gpt3-ai-content-generator') . '</a>';
                        echo wp_kses(
                            sprintf(
                                __('Go to %s.', 'gpt3-ai-content-generator'),
                                $settings_link
                            ),
                            [
                                'a' => [
                                    'href' => [],
                                    'data-aipkit-open-module' => [],
                                ],
                            ]
                        );
                        ?>
                    </li>
                    <li><?php esc_html_e('Select your provider and enter your API key.', 'gpt3-ai-content-generator'); ?></li>
                    <li><?php esc_html_e('Open any module and start using.', 'gpt3-ai-content-generator'); ?></li>
                </ol>
            </section>

            <section class="aipkit_home_panel aipkit_home_panel--chart" aria-labelledby="aipkit_home_chart_title">
                <div class="aipkit_home_panel_header">
                    <h3 class="aipkit_home_panel_title" id="aipkit_home_chart_title"><?php esc_html_e('Token Usage', 'gpt3-ai-content-generator'); ?></h3>
                </div>
                <div
                    id="aipkit_token_usage_chart_container"
                    class="aipkit_token_usage_chart_container aipkit_home_chart_canvas"
                    data-default-days="7"
                >
                    <div class="aipkit_chart_loading_placeholder">
                        <span class="aipkit_spinner" aria-hidden="true"></span>
                        <span><?php esc_html_e('Loading chart data...', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_chart_error_placeholder"></div>
                    <div class="aipkit_chart_nodata_placeholder"></div>
                </div>
            </section>
        </aside>
    </div>
</div>
