<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/form-inputs/post-settings.php
// Status: MODIFIED
/**
 * Partial: Content Writer Form - Post Settings
 */
if (!defined('ABSPATH')) {
    exit;
}
// Variables from loader-vars.php: $available_post_types, $users_for_author, $current_user_id, $wp_categories, $post_statuses
?>
<div class="aipkit_accordion">
    <div class="aipkit_accordion-header">
        <span class="dashicons dashicons-arrow-right-alt2"></span>
        <?php esc_html_e('Post', 'gpt3-ai-content-generator'); ?>
    </div>
    <div class="aipkit_accordion-content">
        <div class="aipkit_form-row">
            <div class="aipkit_form-group aipkit_form-col">
                <label class="aipkit_form-label" for="aipkit_content_writer_post_type">Type</label>
                <select id="aipkit_content_writer_post_type" name="post_type" class="aipkit_form-input">
                    <?php foreach ($available_post_types as $pt_slug => $pt_obj): ?>
                        <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($pt_slug, 'post'); ?>><?php echo esc_html($pt_obj->label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="aipkit_form-group aipkit_form-col">
                <label class="aipkit_form-label" for="aipkit_content_writer_post_author">Author</label>
                <select id="aipkit_content_writer_post_author" name="post_author" class="aipkit_form-input">
                    <?php foreach ($users_for_author as $user): ?>
                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user->ID, $current_user_id); ?>><?php echo esc_html($user->display_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="aipkit_form-group">
            <label class="aipkit_form-label" for="aipkit_content_writer_categories">Categories</label>
            <select id="aipkit_content_writer_categories" name="post_categories[]" class="aipkit_form-input" multiple size="3" style="height: auto;">
                <?php foreach ($wp_categories as $category): ?>
                    <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="aipkit_form-row">
            <div class="aipkit_form-group aipkit_form-col">
                <label class="aipkit_form-label" for="aipkit_content_writer_post_status">Status</label>
                <select id="aipkit_content_writer_post_status" name="post_status" class="aipkit_form-input">
                    <?php foreach ($post_statuses as $status_val => $status_label): ?>
                        <option value="<?php echo esc_attr($status_val); ?>" <?php selected($status_val, 'draft'); ?>><?php echo esc_html($status_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="aipkit_form-group aipkit_form-col aipkit_cw_schedule_date_group" style="display: none;">
                <label class="aipkit_form-label" for="aipkit_content_writer_post_schedule_date">Schedule Date</label>
                <input type="date" id="aipkit_content_writer_post_schedule_date" name="post_schedule_date" class="aipkit_form-input">
            </div>
             <div class="aipkit_form-group aipkit_form-col aipkit_cw_schedule_time_group" style="display: none;">
                <label class="aipkit_form-label" for="aipkit_content_writer_post_schedule_time">Schedule Time</label>
                <input type="time" id="aipkit_content_writer_post_schedule_time" name="post_schedule_time" class="aipkit_form-input">
            </div>
        </div>
    </div>
</div>