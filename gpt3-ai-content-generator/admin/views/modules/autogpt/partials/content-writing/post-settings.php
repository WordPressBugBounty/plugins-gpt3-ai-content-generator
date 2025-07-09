<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/content-writing/post-settings.php
// Status: MODIFIED

/**
 * Partial: Content Writing Automated Task - Post Settings
 *
 * @since NEXT_VERSION
 */

if (!defined('ABSPATH')) {
    exit;
}
// Variables from parent: $cw_available_post_types, $cw_users_for_author, $cw_current_user_id, $cw_wp_categories, $cw_post_statuses
?>
<div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_task_cw_post_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></label>
        <select id="aipkit_task_cw_post_type" name="post_type" class="aipkit_form-input">
            <?php foreach ($cw_available_post_types as $pt_slug => $pt_obj): ?>
                <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($pt_slug, 'post'); ?>><?php echo esc_html($pt_obj->label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_task_cw_post_author"><?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?></label>
        <select id="aipkit_task_cw_post_author" name="post_author" class="aipkit_form-input">
            <?php foreach ($cw_users_for_author as $user): ?>
                <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user->ID, $cw_current_user_id); ?>><?php echo esc_html($user->display_name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_task_cw_post_categories"><?php esc_html_e('Categories', 'gpt3-ai-content-generator'); ?></label>
        <select id="aipkit_task_cw_post_categories" name="post_categories[]" class="aipkit_form-input" multiple size="3" style="height: auto;">
            <?php foreach ($cw_wp_categories as $category): ?>
                <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
     <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_task_cw_post_status"><?php esc_html_e('Status', 'gpt3-ai-content-generator'); ?></label>
        <select id="aipkit_task_cw_post_status" name="post_status" class="aipkit_form-input">
            <?php foreach ($cw_post_statuses as $status_val => $status_label): ?>
                <option value="<?php echo esc_attr($status_val); ?>" <?php selected($status_val, 'draft'); ?>><?php echo esc_html($status_label); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="aipkit_form-row aipkit_task_cw_schedule_row" style="display: none;">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_task_cw_post_schedule_date"><?php esc_html_e('Schedule Date', 'gpt3-ai-content-generator'); ?></label>
        <input type="date" id="aipkit_task_cw_post_schedule_date" name="post_schedule_date" class="aipkit_form-input">
    </div>
     <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="aipkit_task_cw_post_schedule_time"><?php esc_html_e('Schedule Time', 'gpt3-ai-content-generator'); ?></label>
        <input type="time" id="aipkit_task_cw_post_schedule_time" name="post_schedule_time" class="aipkit_form-input">
    </div>
    <div class="aipkit_form-col"></div> <?php // Empty column for alignment?>
</div>
<p class="aipkit_form-help aipkit_task_cw_schedule_row" style="display: none; margin-top:-5px;"><?php esc_html_e('If "Publish" is selected and a future date/time is set, posts will be scheduled.', 'gpt3-ai-content-generator'); ?></p>