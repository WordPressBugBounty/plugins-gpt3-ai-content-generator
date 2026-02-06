<?php
/**
 * Partial: Content Writing Automated Task - RSS Input Mode
 */

if (!defined('ABSPATH')) {
    exit;
}
// $is_pro is available from the parent scope
?>
<div id="aipkit_task_cw_input_mode_rss" class="aipkit_task_cw_input_mode_section" style="display:none;">
    <div class="aipkit_rss_import_container" data-rss-container>
        <div class="aipkit_rss_feeds_section" data-rss-input-zone>
            <div class="aipkit_rss_textarea_wrapper">
                <textarea
                    id="aipkit_task_cw_rss_feeds"
                    name="rss_feeds"
                    class="aipkit_form-input aipkit_rss_textarea"
                    rows="10"
                    placeholder="<?php esc_attr_e("https://example.com/feed/\nhttps://blog.example.org/rss", 'gpt3-ai-content-generator'); ?>"
                    <?php disabled(!$is_pro); ?>
                ></textarea>
                <div class="aipkit_rss_url_counter">
                    <span class="aipkit_rss_url_count" data-rss-url-count>0</span>
                    <span><?php esc_html_e('feeds', 'gpt3-ai-content-generator'); ?></span>
                </div>
            </div>
        </div>

        <div class="aipkit_rss_status_container" id="aipkit_task_cw_rss_status_container" data-rss-status hidden>
            <div class="aipkit_rss_status_card" data-rss-status-card>
                <div class="aipkit_rss_status_icon" data-rss-status-icon>
                    <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                </div>
                <div class="aipkit_rss_status_content">
                    <span class="aipkit_rss_status_title" data-rss-status-title></span>
                    <span class="aipkit_rss_status_message" data-rss-status-message></span>
                </div>
                <button type="button" class="aipkit_rss_status_edit_btn" data-rss-edit-btn aria-label="<?php esc_attr_e('Edit feeds', 'gpt3-ai-content-generator'); ?>">
                    <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                </button>
            </div>
        </div>

        <div class="aipkit_rss_filters_grid">
            <div class="aipkit_rss_filter_card aipkit_rss_filter_card--include">
                <div class="aipkit_rss_filter_header">
                    <span class="aipkit_rss_filter_icon aipkit_rss_filter_icon--include">
                        <span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
                    </span>
                    <label class="aipkit_rss_filter_label" for="aipkit_task_cw_rss_include_keywords">
                        <?php esc_html_e('Include', 'gpt3-ai-content-generator'); ?>
                    </label>
                </div>
                <input
                    type="text"
                    id="aipkit_task_cw_rss_include_keywords"
                    name="rss_include_keywords"
                    class="aipkit_form-input aipkit_rss_filter_input"
                    placeholder="<?php esc_attr_e('wordpress, ai, tutorial', 'gpt3-ai-content-generator'); ?>"
                    <?php disabled(!$is_pro); ?>
                >
                <span class="aipkit_rss_filter_hint"><?php esc_html_e('Only fetch if title contains these', 'gpt3-ai-content-generator'); ?></span>
            </div>

            <div class="aipkit_rss_filter_card aipkit_rss_filter_card--exclude">
                <div class="aipkit_rss_filter_header">
                    <span class="aipkit_rss_filter_icon aipkit_rss_filter_icon--exclude">
                        <span class="dashicons dashicons-minus" aria-hidden="true"></span>
                    </span>
                    <label class="aipkit_rss_filter_label" for="aipkit_task_cw_rss_exclude_keywords">
                        <?php esc_html_e('Exclude', 'gpt3-ai-content-generator'); ?>
                    </label>
                </div>
                <input
                    type="text"
                    id="aipkit_task_cw_rss_exclude_keywords"
                    name="rss_exclude_keywords"
                    class="aipkit_form-input aipkit_rss_filter_input"
                    placeholder="<?php esc_attr_e('review, sponsored, update', 'gpt3-ai-content-generator'); ?>"
                    <?php disabled(!$is_pro); ?>
                >
                <span class="aipkit_rss_filter_hint"><?php esc_html_e('Skip if title contains these', 'gpt3-ai-content-generator'); ?></span>
            </div>
        </div>
    </div>
</div>
