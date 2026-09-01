<?php
/**
 * Inline post settings for the AutoGPT Finish step.
 */

if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables come from the parent view.
$cw_available_post_types = isset($cw_available_post_types) && is_array($cw_available_post_types) ? $cw_available_post_types : [];
$cw_users_for_author = isset($cw_users_for_author) && is_array($cw_users_for_author) ? $cw_users_for_author : [];
$cw_wp_categories = isset($cw_wp_categories) && is_array($cw_wp_categories) ? $cw_wp_categories : [];
$cw_current_user_id = isset($cw_current_user_id) ? (int) $cw_current_user_id : 0;
?>

<div
    class="aipkit_autogpt_post_settings_inline"
    data-aipkit-autogpt-schedule-section="content_writing"
    hidden
>
    <div class="aipkit_cw_publishing_row aipkit_autogpt_question_row aipkit_task_post_setting_row">
        <div class="aipkit_cw_panel_label_wrap">
            <label class="aipkit_cw_panel_label aipkit_autogpt_question" for="aipkit_task_cw_post_content_format">
                <?php esc_html_e('Content format', 'gpt3-ai-content-generator'); ?>
            </label>
        </div>
        <div class="aipkit_cw_publishing_row_actions">
            <select
                id="aipkit_task_cw_post_content_format"
                name="post_content_format"
                class="aipkit_post_settings_select aipkit_form-input aipkit_autosave_trigger aipkit_cw_publishing_select aipkit_cw_blended_chevron_select"
            >
                <option value="gutenberg"><?php esc_html_e('Gutenberg', 'gpt3-ai-content-generator'); ?></option>
                <option value="html"><?php esc_html_e('Classic', 'gpt3-ai-content-generator'); ?></option>
            </select>
        </div>
    </div>

    <div class="aipkit_cw_publishing_row aipkit_autogpt_question_row aipkit_task_post_setting_row">
        <div class="aipkit_cw_panel_label_wrap">
            <label class="aipkit_cw_panel_label aipkit_autogpt_question" for="aipkit_task_cw_post_type">
                <?php esc_html_e('Post Type', 'gpt3-ai-content-generator'); ?>
            </label>
        </div>
        <div class="aipkit_cw_publishing_row_actions">
            <select
                id="aipkit_task_cw_post_type"
                name="post_type"
                class="aipkit_post_settings_select aipkit_form-input aipkit_autosave_trigger aipkit_cw_publishing_select aipkit_cw_blended_chevron_select"
            >
                <?php foreach ($cw_available_post_types as $pt_slug => $pt_obj) : ?>
                    <option value="<?php echo esc_attr($pt_slug); ?>" <?php selected($pt_slug, 'post'); ?>>
                        <?php echo esc_html($pt_obj->label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="aipkit_cw_publishing_row aipkit_autogpt_question_row aipkit_task_post_setting_row">
        <div class="aipkit_cw_panel_label_wrap">
            <label class="aipkit_cw_panel_label aipkit_autogpt_question" for="aipkit_task_cw_post_author">
                <?php esc_html_e('Author', 'gpt3-ai-content-generator'); ?>
            </label>
        </div>
        <div class="aipkit_cw_publishing_row_actions">
            <select
                id="aipkit_task_cw_post_author"
                name="post_author"
                class="aipkit_post_settings_select aipkit_form-input aipkit_autosave_trigger aipkit_cw_publishing_select aipkit_cw_blended_chevron_select"
            >
                <?php foreach ($cw_users_for_author as $user) : ?>
                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($user->ID, $cw_current_user_id); ?>>
                        <?php echo esc_html($user->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="aipkit_cw_publishing_row aipkit_autogpt_question_row aipkit_task_post_setting_row">
        <div class="aipkit_cw_panel_label_wrap">
            <label class="aipkit_cw_panel_label aipkit_autogpt_question" for="aipkit_task_cw_post_categories">
                <?php esc_html_e('Categories', 'gpt3-ai-content-generator'); ?>
            </label>
        </div>
        <div class="aipkit_cw_publishing_row_actions">
            <?php
            $aipkit_category_dropdown_config = [
                'id'              => 'aipkit_task_cw_post_categories',
                'panel_id'        => 'aipkit_task_cw_categories_panel',
                'categories'      => $cw_wp_categories,
                'wrapper_classes' => 'aipkit_autogpt_finish_categories',
                'button_classes'  => 'aipkit_cw_blended_chevron_btn',
                'select_classes'  => 'aipkit_autosave_trigger',
            ];
            require dirname(__DIR__, 3) . '/shared/category-dropdown.php';
            ?>
        </div>
    </div>
</div>
