<?php
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

                    <div id="aipkit_cw_batch_queue" class="aipkit_cw_batch_queue" hidden>
                        <div class="aipkit_cw_batch_workspace aipkit_cw_output_workspace aipkit_cw_output_workspace--studio">
                            <aside class="aipkit_cw_output_brief aipkit_cw_batch_brief">
                                <div class="aipkit_cw_studio_panel">
                                    <div class="aipkit_cw_studio_panel_header">
                                        <span class="aipkit_cw_studio_panel_label"><?php esc_html_e('Brief', 'gpt3-ai-content-generator'); ?></span>
                                        <span class="aipkit_cw_studio_panel_hint"><?php esc_html_e('Live snapshot of the current batch run', 'gpt3-ai-content-generator'); ?></span>
                                    </div>
                                    <dl class="aipkit_cw_studio_brief_list">
                                        <div class="aipkit_cw_studio_brief_row">
                                            <dt><?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?></dt>
                                            <dd id="aipkit_cw_batch_brief_source_value" class="is-placeholder"><?php esc_html_e('Not set', 'gpt3-ai-content-generator'); ?></dd>
                                        </div>
                                        <div class="aipkit_cw_studio_brief_row">
                                            <dt><?php esc_html_e('Mode', 'gpt3-ai-content-generator'); ?></dt>
                                            <dd id="aipkit_cw_batch_brief_mode_value" class="is-placeholder"><?php esc_html_e('Not set', 'gpt3-ai-content-generator'); ?></dd>
                                        </div>
                                        <div class="aipkit_cw_studio_brief_row">
                                            <dt><?php esc_html_e('Items', 'gpt3-ai-content-generator'); ?></dt>
                                            <dd id="aipkit_cw_batch_brief_items_value" class="is-placeholder"><?php esc_html_e('Not set', 'gpt3-ai-content-generator'); ?></dd>
                                        </div>
                                        <div class="aipkit_cw_studio_brief_row">
                                            <dt><?php esc_html_e('Publish Target', 'gpt3-ai-content-generator'); ?></dt>
                                            <dd id="aipkit_cw_batch_brief_publish_value" class="is-placeholder"><?php esc_html_e('Not set', 'gpt3-ai-content-generator'); ?></dd>
                                        </div>
                                        <div class="aipkit_cw_studio_brief_row">
                                            <dt><?php esc_html_e('Outputs', 'gpt3-ai-content-generator'); ?></dt>
                                            <dd id="aipkit_cw_batch_brief_outputs_value" class="is-placeholder"><?php esc_html_e('Not set', 'gpt3-ai-content-generator'); ?></dd>
                                        </div>
                                    </dl>
                                </div>
                            </aside>

                            <div class="aipkit_cw_output_main aipkit_cw_batch_main">
                                <section class="aipkit_cw_output_chunk aipkit_cw_output_chunk--primary aipkit_cw_batch_ledger_surface">
                                    <div class="aipkit_cw_chunk_header">
                                        <div class="aipkit_cw_chunk_title_row">
                                            <span class="aipkit_cw_chunk_icon dashicons dashicons-list-view" aria-hidden="true"></span>
                                            <span class="aipkit_cw_chunk_label"><?php esc_html_e('Queue', 'gpt3-ai-content-generator'); ?></span>
                                        </div>
                                    </div>
                                    <div class="aipkit_cw_batch_queue_body">
                                        <div class="aipkit_cw_batch_list" role="list"></div>
                                    </div>
                                </section>
                            </div>

                            <aside class="aipkit_cw_output_sidebar aipkit_cw_batch_sidebar">
                                <section class="aipkit_cw_output_sidebar_card aipkit_cw_output_sidebar_card--session aipkit_cw_batch_session_card">
                                    <div class="aipkit_cw_output_sidebar_header aipkit_cw_output_sidebar_header--progress">
                                        <div class="aipkit_cw_output_sidebar_header_copy">
                                            <div class="aipkit_cw_output_sidebar_title"><?php esc_html_e('Progress', 'gpt3-ai-content-generator'); ?></div>
                                            <p class="aipkit_cw_output_sidebar_hint"><?php esc_html_e('Track each queued item as it completes.', 'gpt3-ai-content-generator'); ?></p>
                                        </div>
                                    </div>

                                    <div class="aipkit_cw_batch_header_actions"></div>

                                    <div class="aipkit_cw_batch_session_actions" hidden>
                                        <button
                                            type="button"
                                            id="aipkit_cw_batch_start_over_btn"
                                            class="button aipkit_btn aipkit_cw_output_action_btn aipkit_cw_output_action_btn--reset"
                                        >
                                            <span class="aipkit_btn-text"><?php esc_html_e('Start Over', 'gpt3-ai-content-generator'); ?></span>
                                        </button>
                                    </div>

                                    <div class="aipkit_cw_batch_summary">
                                        <span class="aipkit_cw_batch_count">0/0</span>
                                        <span class="aipkit_cw_batch_label"><?php esc_html_e('processed', 'gpt3-ai-content-generator'); ?></span>
                                    </div>

                                    <div class="aipkit_cw_batch_progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                        <span class="aipkit_cw_batch_progress_bar"></span>
                                    </div>

                                    <div class="aipkit_cw_output_sidebar_meta aipkit_cw_batch_stats">
                                        <span id="aipkit_cw_batch_stat_waiting" class="aipkit_cw_output_sidebar_pill">0 <?php esc_html_e('queued', 'gpt3-ai-content-generator'); ?></span>
                                        <span id="aipkit_cw_batch_stat_running" class="aipkit_cw_output_sidebar_pill">0 <?php esc_html_e('running', 'gpt3-ai-content-generator'); ?></span>
                                        <span id="aipkit_cw_batch_stat_success" class="aipkit_cw_output_sidebar_pill">0 <?php esc_html_e('done', 'gpt3-ai-content-generator'); ?></span>
                                        <span id="aipkit_cw_batch_stat_failed" class="aipkit_cw_output_sidebar_pill">0 <?php esc_html_e('failed', 'gpt3-ai-content-generator'); ?></span>
                                        <span id="aipkit_cw_batch_stat_stopped" class="aipkit_cw_output_sidebar_pill">0 <?php esc_html_e('stopped', 'gpt3-ai-content-generator'); ?></span>
                                    </div>
                                </section>
                            </aside>
                        </div>
                    </div>

                    <!-- Main Output Area -->
                    <?php include __DIR__ . '/partials/output-area.php'; ?>
                </div>

                <!-- Settings rail -->
                <div class="aipkit_content_writer_column aipkit_content_writer_inputs">
                    <?php include __DIR__ . '/partials/form-inputs.php'; ?>
                    <?php include __DIR__ . '/partials/advanced-settings.php'; ?>
                    <?php include __DIR__ . '/partials/existing-update-tip.php'; ?>
                </div>

            </div>
        </form>
    </div>
</div>
