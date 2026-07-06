<?php
/**
 * Partial: Module visibility settings.
 */

use WPAICG\AIPKit_Role_Manager;

if (!defined('ABSPATH')) {
    exit;
}

// Variables required: $module_settings, $can_manage_modules.

$aipkit_settings_modules = array(
    'chat_bot' => array(
        'label'       => __('Chatbots', 'gpt3-ai-content-generator'),
        'description' => __('Create and manage site chatbots.', 'gpt3-ai-content-generator'),
        'data_module' => 'chatbot',
    ),
    'content_writer' => array(
        'label'       => __('Content Writer', 'gpt3-ai-content-generator'),
        'description' => __('Generate posts, pages, and product content.', 'gpt3-ai-content-generator'),
        'data_module' => 'content-writer',
    ),
    'autogpt' => array(
        'label'       => __('Automations', 'gpt3-ai-content-generator'),
        'description' => __('Schedule recurring AI workflows.', 'gpt3-ai-content-generator'),
        'data_module' => 'autogpt',
    ),
    'ai_forms' => array(
        'label'       => __('AI Forms', 'gpt3-ai-content-generator'),
        'description' => __('Build AI-powered forms and responses.', 'gpt3-ai-content-generator'),
        'data_module' => 'ai-forms',
    ),
    'image_generator' => array(
        'label'       => __('Images', 'gpt3-ai-content-generator'),
        'description' => __('Generate images when this tool is enabled.', 'gpt3-ai-content-generator'),
        'data_module' => 'image-generator',
    ),
    'sources' => array(
        'label'       => __('Knowledge Base', 'gpt3-ai-content-generator'),
        'description' => __('Manage sources, embeddings, and retrieval.', 'gpt3-ai-content-generator'),
        'data_module' => 'sources',
    ),
    'stats_viewer' => array(
        'label'       => __('Usage', 'gpt3-ai-content-generator'),
        'description' => __('Review token usage and activity trends.', 'gpt3-ai-content-generator'),
        'data_module' => 'stats',
    ),
);
?>
<div class="aipkit_settings_modules" id="aipkit_settings_modules">
    <div class="aipkit_settings_modules_list">
        <?php foreach ($aipkit_settings_modules as $aipkit_option_key => $aipkit_module): ?>
            <?php
            if (!AIPKit_Role_Manager::user_can_access_module($aipkit_module['data_module'])) {
                continue;
            }
            $aipkit_is_enabled = !isset($module_settings[$aipkit_option_key]) || !empty($module_settings[$aipkit_option_key]);
            $aipkit_field_id = 'aipkit_settings_module_' . $aipkit_option_key;
            ?>
            <div
                class="aipkit_form-group aipkit_settings_simple_row aipkit_settings_module_row<?php echo $aipkit_is_enabled ? '' : ' is-disabled'; ?>"
                data-module="<?php echo esc_attr($aipkit_module['data_module']); ?>"
            >
                <label class="aipkit_form-label" for="<?php echo esc_attr($aipkit_field_id); ?>">
                    <?php echo esc_html($aipkit_module['label']); ?>
                    <span class="aipkit_form-label-helper"><?php echo esc_html($aipkit_module['description']); ?></span>
                </label>
                <label class="aipkit_settings_big_checkbox aipkit_settings_module_control" for="<?php echo esc_attr($aipkit_field_id); ?>">
                    <input
                        type="checkbox"
                        id="<?php echo esc_attr($aipkit_field_id); ?>"
                        class="aipkit_settings_module_toggle_input"
                        data-option-key="<?php echo esc_attr($aipkit_option_key); ?>"
                        data-module="<?php echo esc_attr($aipkit_module['data_module']); ?>"
                        <?php checked($aipkit_is_enabled); ?>
                        <?php disabled(!$can_manage_modules); ?>
                    />
                    <span class="aipkit_settings_big_checkbox_box" aria-hidden="true">
                        <span class="dashicons dashicons-saved"></span>
                    </span>
                    <span class="aipkit_settings_big_checkbox_text" aria-hidden="true"></span>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
