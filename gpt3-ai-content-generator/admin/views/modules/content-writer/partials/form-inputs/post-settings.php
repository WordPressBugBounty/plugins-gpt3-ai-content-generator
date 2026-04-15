<?php
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.
// Variables from loader-vars.php: $available_post_types, $users_for_author, $current_user_id, $wp_categories
?>

<div class="aipkit_model_settings_popover_header aipkit_cw_settings_sheet_header">
    <span class="aipkit_model_settings_popover_title"><?php esc_html_e('Post settings', 'gpt3-ai-content-generator'); ?></span>
</div>
<div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body aipkit_cw_settings_sheet_body">
    <div class="aipkit_popover_options_list">
        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_content_writer_post_type">
                        <?php esc_html_e('Post Type', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Choose post type.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select
                    id="aipkit_content_writer_post_type"
                    name="post_type"
                    class="aipkit_post_settings_select aipkit_form-input aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                >
                    <?php foreach ($available_post_types as $pt_slug => $pt_obj): ?>
                        <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($pt_slug, 'post'); ?>><?php echo esc_html($pt_obj->label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_content_writer_post_author">
                        <?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Select the post author.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select
                    id="aipkit_content_writer_post_author"
                    name="post_author"
                    class="aipkit_post_settings_select aipkit_form-input aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                >
                    <?php foreach ($users_for_author as $user): ?>
                        <option value="<?php echo esc_attr($user->ID); ?>" data-login="<?php echo esc_attr($user->user_login); ?>" <?php selected($user->ID, $current_user_id); ?>><?php echo esc_html($user->display_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_content_writer_categories">
                        <?php esc_html_e('Categories', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Assign categories.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <div
                    class="aipkit_popover_multiselect aipkit_post_multiselect"
                    data-aipkit-categories-dropdown
                    data-placeholder="<?php echo esc_attr__('Select categories', 'gpt3-ai-content-generator'); ?>"
                    data-selected-label="<?php echo esc_attr__('selected', 'gpt3-ai-content-generator'); ?>"
                >
                    <button
                        type="button"
                        class="aipkit_popover_multiselect_btn aipkit_post_multiselect_btn aipkit_cw_blended_chevron_btn"
                        aria-expanded="false"
                        aria-controls="aipkit_cw_categories_panel"
                    >
                        <span class="aipkit_popover_multiselect_label">
                            <?php esc_html_e('Select categories', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </button>
                    <div
                        id="aipkit_cw_categories_panel"
                        class="aipkit_popover_multiselect_panel"
                        role="menu"
                        hidden
                    >
                        <div class="aipkit_popover_multiselect_options"></div>
                    </div>
                    <select
                        id="aipkit_content_writer_categories"
                        name="post_categories[]"
                        class="aipkit_popover_multiselect_select aipkit_autosave_trigger"
                        multiple
                        size="3"
                        hidden
                        aria-hidden="true"
                        tabindex="-1"
                    >
                        <?php foreach ($wp_categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_cw_generate_toc">
                        <?php esc_html_e('Table of Contents', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Add navigation at the beginning.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select
                    id="aipkit_cw_generate_toc"
                    name="generate_toc"
                    class="aipkit_post_settings_select aipkit_form-input aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                >
                    <option value="1"><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                    <option value="0" selected><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>

        <div class="aipkit_popover_option_row">
            <div class="aipkit_popover_option_main">
                <div class="aipkit_cw_settings_option_text">
                    <label class="aipkit_popover_option_label" for="aipkit_cw_generate_seo_slug">
                        <?php esc_html_e('Optimize URL', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_popover_option_helper">
                        <?php esc_html_e('Generate an SEO-friendly slug.', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>
                <select
                    id="aipkit_cw_generate_seo_slug"
                    name="generate_seo_slug"
                    class="aipkit_post_settings_select aipkit_form-input aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                >
                    <option value="1"><?php esc_html_e('Yes', 'gpt3-ai-content-generator'); ?></option>
                    <option value="0" selected><?php esc_html_e('No', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>
        </div>
    </div>
</div>
