<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/content-writing/post-settings.php
// Status: REDESIGNED

/**
 * Partial: Content Writing Automated Task - Post Settings (Redesigned)
 *
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}
// Variables from parent: $cw_available_post_types, $cw_users_for_author, $cw_current_user_id, $cw_wp_categories
?>

<div class="aipkit_post_settings_redesigned">
    <div class="aipkit_post_settings_chunk aipkit_post_settings_chunk--basics">
        <div class="aipkit_post_settings_chunk_body">
            <div class="aipkit_post_basics_selector">
                <div class="aipkit_post_basics_row">
                    <label class="aipkit_post_settings_label" for="aipkit_task_cw_post_type">
                        <?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select id="aipkit_task_cw_post_type" name="post_type" class="aipkit_post_settings_select">
                        <?php foreach ($cw_available_post_types as $pt_slug => $pt_obj): ?>
                            <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($pt_slug, 'post'); ?>><?php echo esc_html($pt_obj->label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="aipkit_post_basics_row aipkit_post_basics_row--author">
                    <label class="aipkit_post_settings_label" for="aipkit_task_cw_post_author">
                        <?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select id="aipkit_task_cw_post_author" name="post_author" class="aipkit_post_settings_select">
                        <?php foreach ($cw_users_for_author as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user->ID, $cw_current_user_id); ?>><?php echo esc_html($user->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="aipkit_post_taxonomy_row">
                    <label class="aipkit_post_settings_label" for="aipkit_task_cw_post_categories">
                        <?php esc_html_e('Categories', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div
                        class="aipkit_popover_multiselect aipkit_post_multiselect"
                        data-aipkit-categories-dropdown
                        data-placeholder="<?php echo esc_attr__('Select categories', 'gpt3-ai-content-generator'); ?>"
                        data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                    >
                        <button
                            type="button"
                            class="aipkit_popover_multiselect_btn aipkit_post_multiselect_btn"
                            aria-expanded="false"
                            aria-controls="aipkit_task_cw_categories_panel"
                        >
                            <span class="aipkit_popover_multiselect_label">
                                <?php esc_html_e('Select categories', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </button>
                        <div
                            id="aipkit_task_cw_categories_panel"
                            class="aipkit_popover_multiselect_panel"
                            role="menu"
                            hidden
                        >
                            <div class="aipkit_popover_multiselect_options"></div>
                        </div>
                    </div>
                    <select
                        id="aipkit_task_cw_post_categories"
                        name="post_categories[]"
                        class="aipkit_popover_multiselect_select"
                        multiple
                        size="3"
                        hidden
                        aria-hidden="true"
                        tabindex="-1"
                    >
                        <?php foreach ($cw_wp_categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_post_settings_chunk aipkit_post_settings_chunk--options aipkit_post_settings_chunk--collapsible">
        <button type="button" class="aipkit_post_settings_chunk_header aipkit_post_settings_chunk_header--collapsible" aria-expanded="false" aria-controls="aipkit_task_cw_content_options_body">
            <span class="aipkit_post_settings_chunk_icon">
                <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
            </span>
            <span class="aipkit_post_settings_chunk_title"><?php esc_html_e('Content Options', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_post_settings_chunk_toggle">
                <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
            </span>
        </button>

        <div class="aipkit_post_settings_chunk_body aipkit_post_settings_chunk_body--collapsible" id="aipkit_task_cw_content_options_body" aria-hidden="true">
            <div class="aipkit_post_toggle_card aipkit_post_options_container">
                <div class="aipkit_post_toggle_row">
                    <div class="aipkit_post_toggle_info">
                        <span class="aipkit_post_toggle_label"><?php esc_html_e('Table of Contents', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_post_toggle_desc"><?php esc_html_e('Add navigation TOC at the beginning', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_post_toggle_controls">
                        <label class="aipkit_switch">
                            <input type="checkbox" id="aipkit_task_cw_generate_toc" name="generate_toc" class="aipkit_toggle_switch" value="1">
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="aipkit_post_toggle_card aipkit_post_options_container">
                <div class="aipkit_post_toggle_row">
                    <div class="aipkit_post_toggle_info">
                        <span class="aipkit_post_toggle_label"><?php esc_html_e('Optimize URL', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_post_toggle_desc"><?php esc_html_e('Generate SEO-friendly permalink slug', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_post_toggle_controls">
                        <label class="aipkit_switch">
                            <input type="checkbox" id="aipkit_task_cw_generate_seo_slug" name="generate_seo_slug" class="aipkit_toggle_switch" value="1">
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
