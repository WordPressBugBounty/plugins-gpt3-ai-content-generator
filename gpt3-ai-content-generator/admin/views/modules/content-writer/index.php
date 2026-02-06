<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/index.php
// Status: MODIFIED
/**
 * AIPKit Content Writer Module - Main View
 * UPDATED: Re-architected into a two-column layout with a central tabbed input panel and action bar.
 * MODIFIED: Moved status indicators above the mode selector.
 */

if (!defined('ABSPATH')) {
    exit;
}

// --- MODIFIED: Load shared variables at the top level ---
require_once __DIR__ . '/partials/form-inputs/loader-vars.php';
// --- END MODIFICATION ---

$content_writer_nonce = wp_create_nonce('aipkit_content_writer_nonce');
$content_writer_template_nonce = wp_create_nonce('aipkit_content_writer_template_nonce');
$frontend_stream_nonce = wp_create_nonce('aipkit_frontend_chat_nonce');

$aipkit_cw_max_execution_time = function_exists('ini_get') ? (int) ini_get('max_execution_time') : 0;
$aipkit_cw_socket_timeout = function_exists('ini_get') ? (int) ini_get('default_socket_timeout') : 0;
$aipkit_cw_timeout_warnings = [];
if ($aipkit_cw_max_execution_time > 0 && $aipkit_cw_max_execution_time <= 30) {
    $aipkit_cw_timeout_warnings[] = sprintf('max_execution_time=%ds', $aipkit_cw_max_execution_time);
}
if ($aipkit_cw_socket_timeout > 0 && $aipkit_cw_socket_timeout <= 30) {
    $aipkit_cw_timeout_warnings[] = sprintf('default_socket_timeout=%ds', $aipkit_cw_socket_timeout);
}
?>
<?php
$aipkit_notice_id = 'aipkit_provider_notice_content_writer';
include WPAICG_PLUGIN_DIR . 'admin/views/shared/provider-key-notice.php';
?>
<?php if (!empty($aipkit_cw_timeout_warnings)) : ?>
<div class="aipkit_notification_bar aipkit_notification_bar--warning">
    <div class="aipkit_notification_bar__icon" aria-hidden="true">
        <span class="dashicons dashicons-clock"></span>
    </div>
    <div class="aipkit_notification_bar__content">
        <p>
            <?php
            printf(
                esc_html__(
                    'Low PHP timeouts detected (%s). Long content generations may time out. Increase max_execution_time/default_socket_timeout in php.ini and any web-server timeouts.',
                    'gpt3-ai-content-generator'
                ),
                esc_html(implode(', ', $aipkit_cw_timeout_warnings))
            );
            ?>
        </p>
    </div>
</div>
<?php endif; ?>
<div class="aipkit_container aipkit_module_content_writer" id="aipkit_content_writer_container">
    <div class="aipkit_container-header">
        <div class="aipkit_container-header-left">
            <h2 class="aipkit_container-title"><?php esc_html_e('Content Writer', 'gpt3-ai-content-generator'); ?></h2>
            <div class="aipkit_global_status_area aipkit_content_writer_header_status" aria-live="polite">
                <span id="aipkit_content_writer_form_status" class="aipkit_cw_status_badge"></span>
                <div id="aipkit_content_writer_messages" class="aipkit_settings_messages" role="status" aria-live="polite"></div>
            </div>
        </div>
    </div>
    <div class="aipkit_container-body">
        <form id="aipkit_content_writer_form" onsubmit="return false;">
            <!-- Hidden inputs for nonces, cache keys etc. needed by JS -->
            <input type="hidden" name="_ajax_nonce" id="aipkit_content_writer_nonce" value="<?php echo esc_attr($content_writer_nonce); ?>">
            <input type="hidden" id="aipkit_content_writer_frontend_stream_nonce" value="<?php echo esc_attr($frontend_stream_nonce); ?>">
            <input type="hidden" id="aipkit_content_writer_template_nonce_field" value="<?php echo esc_attr($content_writer_template_nonce); ?>">
            <input type="hidden" name="stream_cache_key" id="aipkit_content_writer_stream_cache_key" value="">
            <input type="hidden" name="image_data" id="aipkit_cw_image_data_holder" value="">

            <div class="aipkit_content_writer_layout">
                <div class="aipkit_content_writer_column aipkit_content_writer_sources">
                    <div class="aipkit_sub_container aipkit_cw_sources_card">
                        <div class="aipkit_sub_container_body">
                            <div class="aipkit_cw_sources_stack">
                                <?php include __DIR__ . '/partials/source-selector.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Main generation area -->
                <div class="aipkit_content_writer_column aipkit_content_writer_output">
                    <!-- Mode Input Panel -->
                    <?php include __DIR__ . '/partials/form-inputs/generation-mode.php'; ?>

                    <div class="aipkit_cw_topbar">
                        <div class="aipkit_cw_topbar_primary"></div>
                        <div class="aipkit_cw_topbar_actions">
                            <select id="aipkit_cw_task_frequency" name="task_frequency" class="aipkit_cw_task_frequency" aria-hidden="true" tabindex="-1" hidden>
                                <?php foreach ($task_frequencies as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($value, 'daily'); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button
                                type="button"
                                class="aipkit_cw_post_summary_trigger aipkit_cw_popover_trigger"
                                data-aipkit-popover-target="aipkit_cw_post_settings_popover"
                                data-aipkit-popover-placement="bottom"
                                aria-controls="aipkit_cw_post_settings_popover"
                                aria-expanded="false"
                                aria-label="<?php esc_attr_e('Post settings', 'gpt3-ai-content-generator'); ?>"
                            >
                                <span class="screen-reader-text"><?php esc_html_e('Post settings', 'gpt3-ai-content-generator'); ?></span>
                                <span class="aipkit_cw_post_summary_icon" aria-hidden="true">
                                    <span class="dashicons dashicons-admin-post"></span>
                                </span>
                                <span class="aipkit_cw_post_summary_value" data-aipkit-cw-summary="post" data-aipkit-placeholder="<?php esc_attr_e('Configure', 'gpt3-ai-content-generator'); ?>">
                                    <?php esc_html_e('Configure', 'gpt3-ai-content-generator'); ?>
                                </span>
                                <span class="dashicons dashicons-arrow-down-alt2 aipkit_cw_post_summary_chevron" aria-hidden="true"></span>
                            </button>
                            <div class="aipkit_cw_generate_split" data-aipkit-cw-action="generate">
                                <button type="button" id="aipkit_content_writer_generate_btn" class="aipkit_btn aipkit_btn-primary">
                                    <span class="aipkit_cw_btn_timer" aria-hidden="true" hidden></span>
                                    <span class="aipkit_btn-text"><?php esc_html_e('Generate', 'gpt3-ai-content-generator'); ?></span>
                                    <span class="aipkit_cw_task_suffix" hidden></span>
                                    <span class="aipkit_spinner" style="display:none;"></span>
                                </button>
                                <button
                                    type="button"
                                    class="aipkit_btn aipkit_btn-primary aipkit_cw_generate_toggle"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    aria-controls="aipkit_cw_generate_menu"
                                >
                                    <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                                    <span class="screen-reader-text"><?php esc_html_e('More actions', 'gpt3-ai-content-generator'); ?></span>
                                </button>
                                <div id="aipkit_cw_generate_menu" class="aipkit_cw_generate_menu" role="menu" hidden>
                                    <div class="aipkit_cw_generate_menu_panel" data-menu-panel="actions">
                                        <button type="button" class="aipkit_cw_generate_menu_item is-active" data-action="generate" role="menuitemradio" aria-checked="true">
                                            <span class="dashicons dashicons-yes aipkit_cw_generate_menu_check" aria-hidden="true"></span>
                                            <?php esc_html_e('Generate', 'gpt3-ai-content-generator'); ?>
                                        </button>
                                        <button type="button" class="aipkit_cw_generate_menu_item" data-action="create_task" role="menuitemradio" aria-checked="false">
                                            <span class="dashicons dashicons-yes aipkit_cw_generate_menu_check" aria-hidden="true"></span>
                                            <?php esc_html_e('Create Task', 'gpt3-ai-content-generator'); ?>
                                        </button>
                                    </div>
                                    <div class="aipkit_cw_generate_menu_panel" data-menu-panel="intervals" hidden>
                                        <button type="button" class="aipkit_cw_generate_menu_back" data-menu-back>
                                            <span class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></span>
                                            <?php esc_html_e('Back', 'gpt3-ai-content-generator'); ?>
                                        </button>
                                        <?php foreach ($task_frequencies as $value => $label): ?>
                                            <button type="button" class="aipkit_cw_generate_menu_item" data-interval="<?php echo esc_attr($value); ?>" role="menuitemradio" aria-checked="<?php echo $value === 'daily' ? 'true' : 'false'; ?>">
                                                <span class="dashicons dashicons-yes aipkit_cw_generate_menu_check" aria-hidden="true"></span>
                                                <?php echo esc_html($label); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="aipkit_content_writer_stop_btn" class="aipkit_btn aipkit_btn-secondary" style="display:none;">
                                <?php esc_html_e('Stop', 'gpt3-ai-content-generator'); ?>
                            </button>
                            <span id="aipkit_cw_action_validation" class="aipkit_cw_action_validation" aria-live="polite"></span>
                        </div>
                    </div>
                    
                    <div id="aipkit_cw_batch_queue" class="aipkit_cw_batch_queue" hidden>
                        <div class="aipkit_cw_batch_header">
                            <div class="aipkit_cw_batch_summary">
                                <span class="aipkit_cw_batch_count">0/0</span>
                                <span class="aipkit_cw_batch_label"><?php esc_html_e('completed', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                            <div class="aipkit_cw_batch_progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <span class="aipkit_cw_batch_progress_bar"></span>
                            </div>
                        </div>
                        <div class="aipkit_cw_batch_list" role="list"></div>
                    </div>

                    <!-- Main Output Area -->
                    <div class="aipkit_cw_output_card" id="aipkit_cw_output_card" style="display: none;">
                        <?php include __DIR__ . '/partials/output-area.php'; ?>
                    </div>
                </div>

                <!-- Settings rail -->
                <div class="aipkit_content_writer_column aipkit_content_writer_inputs">
                    <div class="aipkit_sub_container aipkit_cw_settings_card">
                        <div class="aipkit_sub_container_body">
                            <?php include __DIR__ . '/partials/form-inputs.php'; ?>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
