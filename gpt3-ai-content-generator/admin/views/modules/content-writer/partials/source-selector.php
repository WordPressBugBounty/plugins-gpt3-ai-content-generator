<?php
// File: admin/views/modules/content-writer/partials/source-selector.php
// Status: NEW FILE
/**
 * Partial: Content Writer - Source Selector
 * Cards for primary sources + dropdown for update modes.
 */

if (!defined('ABSPATH')) {
    exit;
}

$has_woocommerce = class_exists('WooCommerce') && post_type_exists('product');
?>
<div class="aipkit_cw_source_selector_wrapper">
    <div class="aipkit_cw_mode_cards" role="list" aria-label="<?php esc_attr_e('Content sources', 'gpt3-ai-content-generator'); ?>">
        <button type="button" class="aipkit_cw_mode_card is-active" data-mode="task" aria-pressed="true">
            <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-edit"></span></span>
            <span class="aipkit_cw_mode_text">
                <span class="aipkit_cw_mode_title"><?php esc_html_e('Manual Entry', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_mode_desc"><?php esc_html_e('Type topics directly', 'gpt3-ai-content-generator'); ?></span>
            </span>
        </button>
        <button type="button" class="aipkit_cw_mode_card" data-mode="csv" aria-pressed="false">
            <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-media-spreadsheet"></span></span>
            <span class="aipkit_cw_mode_text">
                <span class="aipkit_cw_mode_title"><?php esc_html_e('Import CSV', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_mode_desc"><?php esc_html_e('Upload a CSV file', 'gpt3-ai-content-generator'); ?></span>
            </span>
        </button>
        <button type="button" class="aipkit_cw_mode_card" data-mode="rss" aria-pressed="false">
            <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-rss"></span></span>
            <span class="aipkit_cw_mode_text">
                <span class="aipkit_cw_mode_title"><?php esc_html_e('RSS Feed', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_mode_desc"><?php esc_html_e('Pull from feed URL', 'gpt3-ai-content-generator'); ?></span>
            </span>
        </button>
        <button type="button" class="aipkit_cw_mode_card" data-mode="url" aria-pressed="false">
            <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-admin-links"></span></span>
            <span class="aipkit_cw_mode_text">
                <span class="aipkit_cw_mode_title"><?php esc_html_e('Website URL', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_mode_desc"><?php esc_html_e('Extract page content', 'gpt3-ai-content-generator'); ?></span>
            </span>
        </button>
        <button type="button" class="aipkit_cw_mode_card" data-mode="gsheets" aria-pressed="false">
            <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-analytics"></span></span>
            <span class="aipkit_cw_mode_text">
                <span class="aipkit_cw_mode_title"><?php esc_html_e('Google Sheets', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_mode_desc"><?php esc_html_e('Sync from spreadsheet', 'gpt3-ai-content-generator'); ?></span>
            </span>
        </button>
        <button type="button" class="aipkit_cw_mode_card" data-mode="existing-content" aria-pressed="false">
            <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-update"></span></span>
            <span class="aipkit_cw_mode_text">
                <span class="aipkit_cw_mode_title"><?php esc_html_e('Update Content', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_mode_desc"><?php esc_html_e('Rewrite titles and copy', 'gpt3-ai-content-generator'); ?></span>
            </span>
        </button>
        <button type="button" class="aipkit_cw_mode_card" data-mode="existing-images" aria-pressed="false">
            <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-format-image"></span></span>
            <span class="aipkit_cw_mode_text">
                <span class="aipkit_cw_mode_title"><?php esc_html_e('Optimize Images', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_cw_mode_desc"><?php esc_html_e('Alt text, title, caption', 'gpt3-ai-content-generator'); ?></span>
            </span>
        </button>
        <?php if ($has_woocommerce): ?>
            <button type="button" class="aipkit_cw_mode_card" data-mode="existing-products" aria-pressed="false">
                <span class="aipkit_cw_mode_icon" aria-hidden="true"><span class="dashicons dashicons-cart"></span></span>
                <span class="aipkit_cw_mode_text">
                    <span class="aipkit_cw_mode_title"><?php esc_html_e('Optimize Products', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_cw_mode_desc"><?php esc_html_e('Improve product copy', 'gpt3-ai-content-generator'); ?></span>
                </span>
            </button>
        <?php endif; ?>
    </div>

    <label class="screen-reader-text" for="aipkit_cw_mode_select"><?php esc_html_e('Source', 'gpt3-ai-content-generator'); ?></label>
    <select id="aipkit_cw_mode_select" name="cw_generation_mode" class="aipkit_form-input aipkit_autosave_trigger screen-reader-text">
        <option value="task"><?php esc_html_e('Manual Entry', 'gpt3-ai-content-generator'); ?></option>
        <option value="csv"><?php esc_html_e('Import CSV', 'gpt3-ai-content-generator'); ?></option>
        <option value="rss"><?php esc_html_e('RSS Feed', 'gpt3-ai-content-generator'); ?></option>
        <option value="url"><?php esc_html_e('Website URL', 'gpt3-ai-content-generator'); ?></option>
        <option value="gsheets"><?php esc_html_e('Google Sheets', 'gpt3-ai-content-generator'); ?></option>
        <option value="existing-content"><?php esc_html_e('Update Content', 'gpt3-ai-content-generator'); ?></option>
        <option value="existing-images"><?php esc_html_e('Optimize Images', 'gpt3-ai-content-generator'); ?></option>
        <?php if ($has_woocommerce): ?>
            <option value="existing-products"><?php esc_html_e('Optimize Products', 'gpt3-ai-content-generator'); ?></option>
        <?php endif; ?>
        <option value="existing"><?php esc_html_e('Update Existing (Legacy)', 'gpt3-ai-content-generator'); ?></option>
    </select>
</div>
