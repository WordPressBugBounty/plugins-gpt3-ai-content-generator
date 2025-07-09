<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/logs/index.php
// UPDATED FILE

/**
 * AIPKit Logs Module - Admin View
 *
 * Displays the AI interaction log viewer interface.
 * Contains the necessary structure for the log scripts to function.
 * **FIXED**: Moved Bulk Action Menu & Confirmation Area outside the AJAX-refreshed wrapper.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WPAICG\Chat\Storage\BotStorage; // Needed for bot list for filters
use WPAICG\AIPKit_Role_Manager; // For manageable module list

// Fetch list of existing chatbots for the filter dropdown
$bot_storage     = new BotStorage();
$aipkit_chatbots = $bot_storage->get_chatbots(); // returns an array of WP_Post objects

// Get manageable modules for the filter dropdown
$manageable_modules = AIPKit_Role_Manager::get_manageable_modules();

?>
<div class="aipkit_container aipkit_logs_module_container" id="aipkit_logs_container">
    <div class="aipkit_container-header">
        <div class="aipkit_container-title"><?php esc_html_e('Logs', 'gpt3-ai-content-generator'); ?></div>
        <div class="aipkit_container-actions">
            <!-- Placeholder for potential future actions like "Prune Logs" -->
        </div>
    </div>
    <div class="aipkit_container-body" style="padding: 0;"> <?php // Remove padding from main body to allow split layout to fill ?>

        <div class="aipkit_log_split_layout">

            <!-- Left column: table (filters embedded in each column header) -->
            <div class="aipkit_log_table_col">
                <?php // The wrapper with ID 'aipkit_log_table_view' is crucial for JS - Content loaded here via AJAX ?>
                <div class="aipkit_log_table_wrapper" id="aipkit_log_table_view">
                    <!-- The logs table content is loaded here via AJAX by chat-admin-logs-fetch.js -->
                    <p style="text-align:center; padding:30px;">
                        <span class="aipkit_spinner"
                              style="display:inline-block;width:20px;height:20px;border-width:3px;vertical-align:middle;margin-right:10px;"
                        ></span>
                        <?php esc_html_e('Loading logs...', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>

                 <!-- Bulk Action Menu & Confirmation Area (Moved Outside AJAX Wrapper) -->
                 <!-- Bulk Action Menu (hidden by default) -->
                <div id="aipkit_log_bulk_action_menu" class="aipkit_log_action_menu" style="display: none; position: fixed; z-index: 1010;"> <?php // Added fixed + z-index ?>
                    <div class="aipkit_log_action_menu_item" data-action="export-all"> <?php // Only show 'all' actions now ?>
                        <span class="dashicons dashicons-database-export"></span>
                        <?php esc_html_e('Export All', 'gpt3-ai-content-generator'); ?>
                    </div>
                    <div class="aipkit_log_action_menu_item" data-action="delete-all">
                        <span class="dashicons dashicons-trash" style="color: #dc3232;"></span>
                        <?php esc_html_e('Delete All', 'gpt3-ai-content-generator'); ?>
                    </div>
                    <?php /* Removed filtered actions
                    <div class="aipkit_log_action_menu_item" data-action="export-filtered">
                        <span class="dashicons dashicons-database-export"></span>
                        <?php esc_html_e('Export Filtered', 'gpt3-ai-content-generator'); ?>
                    </div>
                     <div class="aipkit_log_action_menu_item" data-action="delete-filtered">
                        <span class="dashicons dashicons-trash" style="color: #dc3232;"></span>
                        <?php esc_html_e('Delete Filtered', 'gpt3-ai-content-generator'); ?>
                    </div>
                    <hr style="margin: 4px 0;">
                    */ ?>
                </div>
                <!-- Bulk Action Confirmation (hidden by default) -->
                <div id="aipkit_log_bulk_action_confirmation_area" style="display:none; position: fixed; z-index: 1020;"> <?php // Added fixed + z-index ?>
                    <!-- Content injected by JS -->
                </div>
                 <!-- End Moved Elements -->

            </div>

            <!-- Right column: detail view -->
            <?php // The wrapper with ID 'aipkit_log_detail_view' is crucial for JS ?>
            <div class="aipkit_log_detail_col" id="aipkit_log_detail_view">
                <div class="aipkit_log_detail_header" id="aipkit_log_detail_header_container">
                    <h4 id="aipkit_log_detail_title">
                        <?php esc_html_e('Log Details', 'gpt3-ai-content-generator'); ?>
                    </h4>
                    <span id="aipkit_log_detail_ip" class="aipkit_log_detail_ip_address">
                        <!-- Populated by JS -->
                    </span>
                </div>
                <div id="aipkit_log_detail_conversation" class="aipkit_log-conversation">
                    <p style="text-align:center; padding:20px; font-style:italic;">
                        <?php esc_html_e('Select a conversation from the left to view details.', 'gpt3-ai-content-generator'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Hidden container to store chatbot list for the filter UI. -->
        <div id="aipkit_available_bots_json" style="display:none"
             data-bots="<?php
                $bot_list_output = [];
                if (!empty($aipkit_chatbots)) {
                    foreach ($aipkit_chatbots as $bp) {
                        $bot_list_output[] = [
                            'id'    => $bp->ID,
                            'title' => $bp->post_title,
                        ];
                    }
                }
                echo esc_attr(wp_json_encode($bot_list_output));
             ?>"
        ></div>

         <!-- Hidden container to store module list for the filter UI. -->
        <div id="aipkit_available_modules_json" style="display:none"
             data-modules="<?php
                $module_list_output = [];
                // Add a specific entry for 'chat' if not already in manageable_modules (defensive)
                if (!isset($manageable_modules['chat'])) {
                    $module_list_output[] = ['id' => 'chat', 'title' => __('Chat', 'gpt3-ai-content-generator')];
                }
                foreach ($manageable_modules as $slug => $name) {
                    // Exclude modules that don't generate logs directly or aren't needed here
                    if(!in_array($slug, ['settings', 'addons', 'token_usage_shortcode', 'chatbot'])) { // Exclude 'chatbot' as it's covered by 'chat' or specific bot selection
                        $module_list_output[] = ['id' => $slug, 'title' => $name];
                     } elseif ($slug === 'chatbot' && !isset($module_list_output['chat'])) { // Ensure 'chat' from 'chatbot' if not added above
                         $module_list_output[] = ['id' => 'chat', 'title' => __('Chat', 'gpt3-ai-content-generator')];
                     }
                }
                // ADDED: Manually add 'ai_post_enhancer' if not in manageable modules yet
                 if (!isset($manageable_modules['ai_post_enhancer'])) {
                    $module_list_output[] = ['id' => 'ai_post_enhancer', 'title' => __('Content Enhancer', 'gpt3-ai-content-generator')];
                }
                 // --- End Add ---
                 // Remove duplicates if any arose
                $module_list_output = array_map("unserialize", array_unique(array_map("serialize", $module_list_output)));
                usort($module_list_output, fn($a, $b) => strcmp($a['title'], $b['title']));
                echo esc_attr(wp_json_encode($module_list_output));
             ?>"
        ></div>

    </div><!-- /.aipkit_container-body -->
</div><!-- /.aipkit_container -->