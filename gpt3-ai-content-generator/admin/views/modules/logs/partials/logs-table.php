<?php

/**
 * Partial: Logs Table Content (AJAX-loaded).
 *
 * Displays conversation/interaction summaries. Clicking a row loads the detail.
 * Includes filter icons in headers and bulk action menu in the message column header.
 */

if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Utils\Utils;
use WPAICG\AIPKit_Role_Manager; // For manageable module list

// Helper to shorten strings
if (!function_exists('aipkit_shorten_string')) {
    function aipkit_shorten_string($string, $length = 50) {
        if (mb_strlen($string) > $length) {
            return esc_html(mb_substr($string, 0, $length)) . '...';
        }
        return esc_html($string);
    }
}

// Variables passed from ajax_get_chat_logs_html:
// $logs (array of conversation summaries)
// $total_logs (total number of conversations)
// $total_pages
// $current_page
// $base_url (for pagination)

?>
<div class="aipkit_data-table">
    <table>
        <thead>
            <tr>
                <!-- Chatbot column with filter icon (Label changed to "Bot / Source") -->
                <th class="aipkit-log-col-chatbot"> <?php // Keep original class for width consistency ?>
                    <?php esc_html_e('Bot / Source', 'gpt3-ai-content-generator'); ?> <?php // Renamed column header ?>
                    <button class="aipkit_log_filter_btn" data-filter-type="chatbot" title="<?php esc_attr_e('Filter by chatbot','gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-filter"></span>
                    </button>
                    <!-- The select for chatbot filter, hidden by default -->
                    <div class="aipkit_log_filter_box" data-filter-box="chatbot" style="display: none;">
                        <select class="aipkit_log_filter_select" data-filter-field="chatbot_id">
                            <option value=""><?php esc_html_e('All Chatbots','gpt3-ai-content-generator'); ?></option>
                            <option value="0"><?php esc_html_e('(No Specific Bot)','gpt3-ai-content-generator'); ?></option> <?php // Option for non-bot logs ?>
                            <!-- Chatbot options injected by JS -->
                        </select>
                    </div>
                </th>

                <!-- User column with filter icon -->
                <th class="aipkit-log-col-user">
                    <?php esc_html_e('User', 'gpt3-ai-content-generator'); ?>
                    <button class="aipkit_log_filter_btn" data-filter-type="user" title="<?php esc_attr_e('Filter by user','gpt3-ai-content-generator'); ?>">
                        <span class="dashicons dashicons-filter"></span>
                    </button>
                    <!-- The text field for user filter, hidden by default -->
                    <div class="aipkit_log_filter_box" data-filter-box="user" style="display: none;">
                        <input
                            type="text"
                            class="aipkit_log_filter_input"
                            data-filter-field="user_name"
                            placeholder="<?php esc_attr_e('Type username, press Enter','gpt3-ai-content-generator'); ?>"
                        />
                    </div>
                </th>

                <!-- Last Activity column -->
                <th class="aipkit-log-col-time"><?php esc_html_e('Activity', 'gpt3-ai-content-generator'); ?></th>

                <!-- Msgs column -->
                <th class="aipkit-log-col-msgs"><?php esc_html_e('Msgs', 'gpt3-ai-content-generator'); ?></th>

                <!-- Token column -->
                <th class="aipkit-log-col-tokens"><?php esc_html_e('Tokens', 'gpt3-ai-content-generator'); ?></th>

                <!-- Last Message column with filter icon AND bulk actions -->
                <th class="aipkit-log-col-message">
                    <div class="aipkit_log_th_content">
                        <span class="aipkit_log_th_title_filter"> <?php // Wrap title and filter ?>
                            <?php esc_html_e('Msg', 'gpt3-ai-content-generator'); ?> <?php // Changed Title ?>
                            <button class="aipkit_log_filter_btn" data-filter-type="message" title="<?php esc_attr_e('Filter by message content','gpt3-ai-content-generator'); ?>">
                                <span class="dashicons dashicons-filter"></span>
                            </button>
                            <div class="aipkit_log_filter_box" data-filter-box="message" style="display: none;">
                                <input
                                    type="text"
                                    class="aipkit_log_filter_input"
                                    data-filter-field="message_like"
                                    placeholder="<?php esc_attr_e('Search messages, press Enter','gpt3-ai-content-generator'); ?>"
                                />
                            </div>
                        </span>
                        <span class="aipkit_log_th_actions"> <?php // Wrap bulk actions ?>
                            <button id="aipkit_log_bulk_actions_btn" title="<?php esc_attr_e('Bulk Actions','gpt3-ai-content-generator'); ?>">
                                 <span class="dashicons dashicons-ellipsis"></span>
                            </button>
                            <?php // Bulk Action Menu HTML is now in logs/index.php ?>
                             <!-- Inline status/progress area -->
                            <div id="aipkit_log_bulk_action_status_area" style="display:none;">
                                <span class="aipkit_log_bulk_action_spinner aipkit_spinner"></span>
                                <span class="aipkit_log_bulk_action_message"></span>
                            </div>
                             <?php // Bulk Action Confirmation Area HTML is now in logs/index.php ?>
                        </span>
                    </div>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log_summary): // Iterate through conversation summaries ?>
                    <?php
                    // Extract data from the summary row
                    $conv_uuid    = $log_summary['conversation_uuid'] ?? 'N/A';
                    $bot_id       = $log_summary['bot_id'] ?? null;
                    $user_id      = $log_summary['user_id'] ?? null;
                    $session_id   = $log_summary['session_id'] ?? null; // Guest UUID
                    $is_guest     = $log_summary['is_guest'] ?? ($user_id === null);
                    // $module       = $log_summary['module'] ?? __('Unknown', 'gpt3-ai-content-generator'); // Module data not needed for display row
                    $bot_or_source_name = $log_summary['bot_name'] ?? __('(Unknown)', 'gpt3-ai-content-generator'); // Already enriched (includes module name if no bot)
                    $user_info    = $log_summary['user_display_name'] ?? ($is_guest ? __('Guest', 'gpt3-ai-content-generator') : __('(Unknown)', 'gpt3-ai-content-generator'));
                    $has_feedback = $log_summary['has_feedback'] ?? false; // Get feedback flag
                    $total_tokens = $log_summary['total_conversation_tokens'] ?? 0; // Get total tokens

                    if ($is_guest && $session_id) {
                        $user_info .= ' (' . aipkit_shorten_string($session_id, 8) . ')';
                    }

                    $message_count = $log_summary['message_count'] ?? 0;
                    $last_message_ts = $log_summary['last_message_ts'] ?? 0;
                    $time_ago = $last_message_ts ? Utils::aipkit_human_time_diff($last_message_ts) : '-';
                    // Extract last message snippet (already enriched in get_logs)
                    $last_message_role = $log_summary['last_message_role'] ?? '';
                    $last_message_content = $log_summary['last_message_content'] ?? __('(No messages)', 'gpt3-ai-content-generator');
                    ?>
                    <tr
                        class="aipkit_log_row"
                        data-conversation-uuid="<?php echo esc_attr($conv_uuid); ?>"
                        data-bot-id="<?php echo esc_attr($bot_id ?? ''); ?>"
                        data-user-id="<?php echo esc_attr($user_id ?? ''); ?>"
                        data-session-id="<?php echo esc_attr($session_id ?? ''); ?>"
                        style="cursor: pointer;"
                    >
                        <td class="aipkit-log-col-chatbot"><?php echo esc_html($bot_or_source_name); ?></td>
                        <td class="aipkit-log-col-user"><?php echo esc_html($user_info); ?></td>
                        <td class="aipkit-log-col-time"><?php echo esc_html($time_ago); ?></td>
                        <td class="aipkit-log-col-msgs" style="text-align:center;"><?php echo intval($message_count); ?></td>
                        <td class="aipkit-log-col-tokens" style="text-align:right;"><?php echo esc_html( number_format_i18n( $total_tokens ) ); ?></td>
                        <td class="aipkit-log-col-message">
                            <?php if ($has_feedback): ?>
                                <span class="dashicons dashicons-tag aipkit_log_table_feedback_badge" title="<?php esc_attr_e('Contains feedback', 'gpt3-ai-content-generator'); ?>"></span>
                            <?php endif; ?>
                            <?php echo esc_html( aipkit_shorten_string( $last_message_content, 70 ) ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;"> <?php // Colspan is now 6 ?>
                        <?php esc_html_e('No logs found matching filters.', 'gpt3-ai-content-generator'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="aipkit-log-footer">
            <tr>
                <th colspan="6"> <?php // Colspan is now 6 ?>
                    <!-- Pagination -->
                    <div class="aipkit_pagination">
                        <span class="aipkit_pagination-count">
                        <?php
                            // translators: %1$s is the total number of conversations
                            printf(esc_html__( '%1$s logs found', 'gpt3-ai-content-generator' ), esc_html( number_format_i18n( $total_logs ) )); // Changed text
                            ?>
                        </span>
                        <?php if ($total_pages > 1): ?>
                            <span class="aipkit_pagination-links">
                                <?php if ($current_page > 1): ?>
                                    <a
                                        href="<?php echo esc_url(add_query_arg('log_page', $current_page - 1, $base_url)); ?>"
                                        class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"
                                    >
                                        « <?php esc_html_e('Previous', 'gpt3-ai-content-generator'); ?>
                                    </a>
                                <?php else: ?>
                                    <button
                                        class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"
                                        disabled
                                    >
                                        « <?php esc_html_e('Previous', 'gpt3-ai-content-generator'); ?>
                                    </button>
                                <?php endif; ?>

                                <span class="aipkit_pagination-current">
                                <?php
                                    // translators: %1$s is the current page number, %2$s is the total number of pages
                                    printf(esc_html__( 'Page %1$s of %2$s', 'gpt3-ai-content-generator' ), esc_html( number_format_i18n( $current_page ) ), esc_html( number_format_i18n( $total_pages ) ));
                                    ?>
                                </span>

                                <?php if ($current_page < $total_pages): ?>
                                    <a
                                        href="<?php echo esc_url(add_query_arg('log_page', $current_page + 1, $base_url)); ?>"
                                        class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"
                                    >
                                        <?php esc_html_e('Next', 'gpt3-ai-content-generator'); ?> »
                                    </a>
                                <?php else: ?>
                                    <button
                                        class="aipkit_btn aipkit_btn-secondary aipkit_btn-small"
                                        disabled
                                    >
                                        <?php esc_html_e('Next', 'gpt3-ai-content-generator'); ?> »
                                    </button>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </th>
            </tr>
        </tfoot>
    </table>
</div>