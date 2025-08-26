<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/logs/index.php
// Status: MODIFIED
/**
 * AIPKit Logs Module - Admin View
 *
 * Displays the AI interaction log viewer interface.
 * Contains the necessary structure for the log scripts to function.
 * **REVISED**: Added a tabbed interface for "Log Viewer" and "Settings".
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotStorage;
use WPAICG\AIPKit_Role_Manager;

// Fetch list of existing chatbots for the filter dropdown
$bot_storage     = new BotStorage();
$aipkit_chatbots = $bot_storage->get_chatbots();

// Get manageable modules for the filter dropdown
$manageable_modules = AIPKit_Role_Manager::get_manageable_modules();

?>
<div class="aipkit_container aipkit_logs_module_container" id="aipkit_logs_container">
    <div class="aipkit_container-header">
        <div class="aipkit_container-title"><?php esc_html_e('Logs', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_container-actions">
            <!-- Placeholder for potential future actions -->
        </div>
    </div>
    <div class="aipkit_container-body" style="padding: 0;">

        <!-- Tabs for Logs and Settings -->
        <div class="aipkit_tabs">
            <div class="aipkit_tab aipkit_active" data-tab="log-viewer"><?php esc_html_e('Log Viewer', 'gpt3-ai-content-generator'); ?></div>
            <div class="aipkit_tab" data-tab="log-settings"><?php esc_html_e('Settings', 'gpt3-ai-content-generator'); ?></div>
        </div>

        <div class="aipkit_tab_content_container">
            <!-- Log Viewer Content -->
            <div class="aipkit_tab-content aipkit_active" id="log-viewer-content">
                <div class="aipkit_log_split_layout">
                    <!-- Left column: table -->
                    <div class="aipkit_log_table_col">
                        <div class="aipkit_log_table_wrapper" id="aipkit_log_table_view">
                            <p style="text-align:center; padding:30px;">
                                <span class="aipkit_spinner" style="display:inline-block;width:20px;height:20px;border-width:3px;vertical-align:middle;margin-right:10px;"></span>
                                <?php esc_html_e('Loading logs...', 'gpt3-ai-content-generator'); ?>
                            </p>
                        </div>

                        <!-- Bulk Action Menu & Confirmation Area -->
                        <div id="aipkit_log_bulk_action_menu" class="aipkit_log_action_menu" style="display: none; position: fixed; z-index: 1010;">
                            <div class="aipkit_log_action_menu_item" data-action="export-all">
                                <span class="dashicons dashicons-database-export"></span>
                                <?php esc_html_e('Export All', 'gpt3-ai-content-generator'); ?>
                            </div>
                            <div class="aipkit_log_action_menu_item" data-action="delete-all">
                                <span class="dashicons dashicons-trash" style="color: #dc3232;"></span>
                                <?php esc_html_e('Delete All', 'gpt3-ai-content-generator'); ?>
                            </div>
                        </div>
                        <div id="aipkit_log_bulk_action_confirmation_area" style="display:none; position: fixed; z-index: 1020;"></div>
                    </div>

                    <!-- Right column: detail view -->
                    <div class="aipkit_log_detail_col" id="aipkit_log_detail_view">
                        <div class="aipkit_log_detail_header" id="aipkit_log_detail_header_container">
                            <h4 id="aipkit_log_detail_title"><?php esc_html_e('Log Details', 'gpt3-ai-content-generator'); ?></h4>
                            <span id="aipkit_log_detail_ip" class="aipkit_log_detail_ip_address"></span>
                        </div>
                        <div id="aipkit_log_detail_conversation" class="aipkit_log-conversation">
                            <p style="text-align:center; padding:20px; font-style:italic;">
                                <?php esc_html_e('Select a conversation from the left to view details.', 'gpt3-ai-content-generator'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="aipkit_tab-content" id="log-settings-content" style="padding: 15px;">
                <?php include __DIR__ . '/partials/logs-settings.php'; ?>
            </div>
        </div>

        <!-- Hidden data containers for JS -->
        <div id="aipkit_available_bots_json" style="display:none" data-bots="<?php
        $bot_list_output = [];
        if (!empty($aipkit_chatbots)) {
            foreach ($aipkit_chatbots as $bp) {
                $bot_list_output[] = ['id' => $bp->ID, 'title' => $bp->post_title];
            }
        }
        echo esc_attr(wp_json_encode($bot_list_output));
        ?>"></div>
        <div id="aipkit_available_modules_json" style="display:none" data-modules="<?php
        $module_list_output = [];
        if (!isset($manageable_modules['chat'])) {
            $module_list_output[] = ['id' => 'chat', 'title' => __('Chat', 'gpt3-ai-content-generator')];
        }
        foreach ($manageable_modules as $slug => $name) {
            if (!in_array($slug, ['settings', 'addons', 'token_usage_shortcode', 'chatbot'])) {
                $module_list_output[] = ['id' => $slug, 'title' => $name];
            } elseif ($slug === 'chatbot' && !isset($module_list_output['chat'])) {
                $module_list_output[] = ['id' => 'chat', 'title' => __('Chat', 'gpt3-ai-content-generator')];
            }
        }
        if (!isset($manageable_modules['ai_post_enhancer'])) {
            $module_list_output[] = ['id' => 'ai_post_enhancer', 'title' => __('Content Assistant', 'gpt3-ai-content-generator')];
        }
        $module_list_output = array_map("unserialize", array_unique(array_map("serialize", $module_list_output)));
        usort($module_list_output, fn($a, $b) => strcmp($a['title'], $b['title']));
        echo esc_attr(wp_json_encode($module_list_output));
        ?>"></div>

    </div>
</div>