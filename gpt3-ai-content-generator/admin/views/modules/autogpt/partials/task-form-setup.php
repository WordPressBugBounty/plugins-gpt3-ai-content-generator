<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/task-form-setup.php
// Status: MODIFIED
/**
 * Partial: Automated Task Form - Task Setup Section
 * UPDATED: Replaced single Task Type dropdown with a two-step Category/Type selection.
 */

if (!defined('ABSPATH')) {
    exit;
}
// Variables from parent: $task_categories, $frequencies, $aipkit_task_statuses_for_select, etc.
?>
<label class="screen-reader-text" for="aipkit_automated_task_category"><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></label>
<select id="aipkit_automated_task_category" name="task_category" class="aipkit_form-input aipkit_hidden_form_field" aria-hidden="true" tabindex="-1">
    <?php foreach ($task_categories as $cat_key => $cat_label) : ?>
        <option value="<?php echo esc_attr($cat_key); ?>"><?php echo esc_html($cat_label); ?></option>
    <?php endforeach; ?>
</select>
<!-- NEW: Wrapper for Content Indexing source settings -->
<div id="aipkit_task_ci_source_wrapper" class="aipkit_task_config_section" style="display: none;">
    <?php include __DIR__ . '/content-indexing/source-settings.php'; ?>
</div>
<!-- Wrapper for Content Writing input modes -->
<div id="aipkit_task_cw_input_modes_wrapper" class="aipkit_task_config_section" style="display: none;">
    <?php // This hidden input will be set by JS based on the selected task_type?>
    <input type="hidden" name="cw_generation_mode" id="aipkit_task_cw_generation_mode" value="">
    <div class="aipkit_form-group" id="aipkit_automated_task_type_group">
        <div id="aipkit_automated_task_type_radios" class="aipkit_task_type_radios" role="radiogroup"></div>
        <select id="aipkit_automated_task_type" name="task_type" class="aipkit_form-input aipkit_hidden_form_field" aria-hidden="true" tabindex="-1" disabled>
            <option value=""><?php esc_html_e('-- Select a category first --', 'gpt3-ai-content-generator'); ?></option>
        </select>
    </div>

    <!-- Input sections for different modes -->
    <?php include __DIR__ . '/content-writing/input-mode-bulk.php'; ?>
    <?php include __DIR__ . '/content-writing/input-mode-csv.php'; ?>

    <?php // Pro Feature: RSS Mode?>
    <div id="aipkit_task_cw_input_mode_rss" class="aipkit_task_cw_input_mode_section" style="display:none;">
        <?php
        $shared_rss_partial = WPAICG_LIB_DIR . 'views/shared/content-writing/input-mode-rss.php';
        if (file_exists($shared_rss_partial)) {
            include $shared_rss_partial;
        } else {
            $fallback_rss_partial = WPAICG_PLUGIN_DIR . 'admin/views/modules/content-writer/partials/form-inputs/mode-rss.php';
            if (file_exists($fallback_rss_partial)) {
                include $fallback_rss_partial;
            } else {
                echo '<p>This is a Pro feature. Please upgrade to access the RSS feature.</p>';
            }
        }
?>
    </div>

    <?php // Pro Feature: URL Mode?>
    <div id="aipkit_task_cw_input_mode_url" class="aipkit_task_cw_input_mode_section" style="display:none;">
        <?php
$shared_url_partial = WPAICG_LIB_DIR . 'views/shared/content-writing/input-mode-url.php';
if (file_exists($shared_url_partial)) {
    include $shared_url_partial;
} else {
    $fallback_url_partial = WPAICG_PLUGIN_DIR . 'admin/views/modules/content-writer/partials/form-inputs/mode-url.php';
    if (file_exists($fallback_url_partial)) {
        include $fallback_url_partial;
    } else {
        echo '<p>This is a Pro feature. Please upgrade to access the URL feature.</p>';
    }
}
?>
    </div>

    <?php // Pro Feature: Google Sheets Mode?>
    <div id="aipkit_task_cw_input_mode_gsheets" class="aipkit_task_cw_input_mode_section" style="display:none;">
        <?php
$shared_gsheets_partial = WPAICG_LIB_DIR . 'views/shared/content-writing/input-mode-gsheets.php';
if (file_exists($shared_gsheets_partial)) {
    $prefix = 'aipkit_task_cw';
    include $shared_gsheets_partial;
} else {
    $fallback_gsheets_partial = WPAICG_PLUGIN_DIR . 'admin/views/modules/content-writer/partials/form-inputs/mode-gsheets.php';
    if (file_exists($fallback_gsheets_partial)) {
        include $fallback_gsheets_partial;
    } else {
        echo '<p>This is a Pro feature. Please upgrade to access the Google Sheets feature.</p>';
    }
}
?>
    </div>
</div>
<!-- Wrapper for Comment Reply source settings -->
<div id="aipkit_task_cc_source_wrapper" class="aipkit_task_config_section" style="display: none;">
    <div class="aipkit_form-row">
        <?php
$comment_reply_source_partial = __DIR__ . '/community-engagement/source-settings.php';
if (file_exists($comment_reply_source_partial)) {
    include $comment_reply_source_partial;
} else {
    echo '<p>Error: Comment Reply Source Settings UI partial is missing.</p>';
}
?>
    </div>
</div>
<!-- Wrapper for Content Enhancement source settings -->
<div id="aipkit_task_ce_content_selection_wrapper" class="aipkit_task_config_section" style="display: none;">
    <?php
    $content_enhancement_source_partial = __DIR__ . '/content-enhancement/source-settings.php';
if (file_exists($content_enhancement_source_partial)) {
    include $content_enhancement_source_partial;
} else {
    echo '<p>Error: Content Enhancement Source Settings UI partial is missing.</p>';
}
?>
</div>
