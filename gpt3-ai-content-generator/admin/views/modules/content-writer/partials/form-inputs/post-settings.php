<?php
// File: post-settings.php
// Status: REDESIGNED
/**
 * Partial: Content Writer Form - Post Settings
 * 
 * REDESIGNED with three core UX principles:
 * 1. Aesthetic - Clean, modern visual design with consistent spacing and typography
 * 2. Choice Overload Prevention - Progressive disclosure, smart defaults, visual hierarchy
 * 3. Chunking - Logically grouped settings with clear visual separation
 * 
 * @since 2.1
 */
if (!defined('ABSPATH')) {
    exit;
}
// Variables from loader-vars.php: $available_post_types, $users_for_author, $current_user_id, $wp_categories, $post_statuses
?>

<div class="aipkit_post_settings_redesigned">

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 1: Publishing (Status & Schedule)
         Post status with conditional scheduling options
         Following Progressive Disclosure: Schedule fields shown only when needed
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_post_settings_chunk aipkit_post_settings_chunk--publishing">
        <div class="aipkit_post_settings_chunk_body">
            <!-- Status Row -->
            <div class="aipkit_post_status_row">
                <label class="aipkit_post_settings_label" for="aipkit_content_writer_post_status">
                    <?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?>
                </label>
                <select id="aipkit_content_writer_post_status" name="post_status" class="aipkit_post_settings_select aipkit_autosave_trigger">
                    <?php foreach ($post_statuses as $status_val => $status_label): ?>
                        <option value="<?php echo esc_attr($status_val); ?>" <?php selected($status_val, 'draft'); ?>><?php echo esc_html($status_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Smart Schedule Options (Conditional - shown when status is "publish") -->
            <div id="aipkit_cw_schedule_options_wrapper" class="aipkit_post_smart_schedule_container" style="display: none;">
                <div class="aipkit_post_smart_schedule_header">
                    <span class="aipkit_post_settings_label"><?php esc_html_e('Publishing Schedule', 'gpt3-ai-content-generator'); ?></span>
                </div>
                <div class="aipkit_post_smart_schedule_options">
                    <label class="aipkit_post_schedule_radio">
                        <input type="radio" name="schedule_mode" value="immediate" checked>
                        <span class="aipkit_post_schedule_radio_text"><?php esc_html_e('Publish Immediately', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <label class="aipkit_post_schedule_radio">
                        <input type="radio" name="schedule_mode" value="smart">
                        <span class="aipkit_post_schedule_radio_text"><?php esc_html_e('Smart Schedule', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                    <label class="aipkit_post_schedule_radio aipkit_schedule_from_input_option">
                        <input type="radio" name="schedule_mode" value="from_input">
                        <span class="aipkit_post_schedule_radio_text"><?php esc_html_e('Use Dates from Input', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                </div>
                
                <!-- Smart Schedule Fields (Conditional) -->
                <div id="aipkit_cw_smart_schedule_fields" class="aipkit_post_smart_schedule_fields" style="display: none;">
                    <div class="aipkit_post_smart_schedule_field">
                        <label class="aipkit_post_settings_label" for="aipkit_cw_smart_schedule_start_datetime">
                            <?php esc_html_e('Start Date/Time', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <input type="datetime-local" id="aipkit_cw_smart_schedule_start_datetime" name="smart_schedule_start_datetime" class="aipkit_post_settings_input">
                    </div>
                    <div class="aipkit_post_smart_schedule_field">
                        <label class="aipkit_post_settings_label" for="aipkit_cw_smart_schedule_interval_value">
                            <?php esc_html_e('Publish one post every', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <div class="aipkit_post_smart_schedule_interval">
                            <input type="number" id="aipkit_cw_smart_schedule_interval_value" name="smart_schedule_interval_value" value="1" min="1" class="aipkit_post_settings_input aipkit_post_settings_input--number">
                            <select id="aipkit_cw_smart_schedule_interval_unit" name="smart_schedule_interval_unit" class="aipkit_post_settings_select">
                                <option value="hours"><?php esc_html_e('Hours', 'gpt3-ai-content-generator'); ?></option>
                                <option value="days"><?php esc_html_e('Days', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <p class="aipkit_post_schedule_hint aipkit_schedule_from_input_help" style="display: none;">
                    <?php esc_html_e('Append | YYYY-MM-DD HH:MM to each line or use the schedule column in Google Sheets.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 2: Post Basics (Type, Author, Categories)
         Unified row layout for essential identity fields
         Following Chunking: Related post identity settings grouped together
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_post_settings_chunk aipkit_post_settings_chunk--basics">
        <div class="aipkit_post_settings_chunk_body">
            <div class="aipkit_post_basics_selector">
                <div class="aipkit_post_basics_row">
                    <label class="aipkit_post_settings_label" for="aipkit_content_writer_post_type">
                        <?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select id="aipkit_content_writer_post_type" name="post_type" class="aipkit_post_settings_select aipkit_autosave_trigger">
                        <?php foreach ($available_post_types as $pt_slug => $pt_obj): ?>
                            <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($pt_slug, 'post'); ?>><?php echo esc_html($pt_obj->label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="aipkit_post_basics_row aipkit_post_basics_row--author">
                    <label class="aipkit_post_settings_label" for="aipkit_content_writer_post_author">
                        <?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select id="aipkit_content_writer_post_author" name="post_author" class="aipkit_post_settings_select aipkit_autosave_trigger">
                        <?php foreach ($users_for_author as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" data-login="<?php echo esc_attr($user->user_login); ?>" <?php selected($user->ID, $current_user_id); ?>><?php echo esc_html($user->display_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="aipkit_post_taxonomy_row">
                    <label class="aipkit_post_settings_label" for="aipkit_content_writer_categories">
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
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 4: Content Options (Collapsible)
         TOC and URL optimization toggles
         Following Choice Overload Prevention: Collapsed by default with smart defaults
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_post_settings_chunk aipkit_post_settings_chunk--options aipkit_post_settings_chunk--collapsible">
        <button type="button" class="aipkit_post_settings_chunk_header aipkit_post_settings_chunk_header--collapsible" aria-expanded="false" aria-controls="aipkit_cw_content_options_body">
            <span class="aipkit_post_settings_chunk_icon">
                <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
            </span>
            <span class="aipkit_post_settings_chunk_title"><?php esc_html_e('Content Options', 'gpt3-ai-content-generator'); ?></span>
            <span class="aipkit_post_settings_chunk_toggle">
                <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
            </span>
        </button>
        
        <div class="aipkit_post_settings_chunk_body aipkit_post_settings_chunk_body--collapsible" id="aipkit_cw_content_options_body" aria-hidden="true">
            <!-- Table of Contents Toggle Card -->
            <div class="aipkit_post_toggle_card aipkit_post_options_container">
                <div class="aipkit_post_toggle_row">
                    <div class="aipkit_post_toggle_info">
                        <span class="aipkit_post_toggle_label"><?php esc_html_e('Table of Contents', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_post_toggle_desc"><?php esc_html_e('Add navigation TOC at the beginning', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_post_toggle_controls">
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_cw_generate_toc"
                                name="generate_toc"
                                class="aipkit_toggle_switch aipkit_autosave_trigger"
                                value="1"
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Optimize URL Toggle Card -->
            <div class="aipkit_post_toggle_card aipkit_post_options_container">
                <div class="aipkit_post_toggle_row">
                    <div class="aipkit_post_toggle_info">
                        <span class="aipkit_post_toggle_label"><?php esc_html_e('Optimize URL', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_post_toggle_desc"><?php esc_html_e('Generate SEO-friendly permalink slug', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_post_toggle_controls">
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_cw_generate_seo_slug"
                                name="generate_seo_slug"
                                class="aipkit_toggle_switch aipkit_autosave_trigger"
                                value="1"
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
