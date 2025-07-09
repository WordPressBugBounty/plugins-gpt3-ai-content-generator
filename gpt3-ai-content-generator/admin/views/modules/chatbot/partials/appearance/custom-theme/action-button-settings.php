<?php
// File: admin/views/modules/chatbot/partials/appearance/custom-theme/action-button-settings.php
// NEW FILE

/**
 * Partial: Custom Theme - Action & Utility Button Color Settings
 */
if (!defined('ABSPATH')) {
    exit;
}

// Variables available from parent script (custom-theme-settings.php):
// $bot_id, $esc_cts_val_attr
?>
<h5 style="margin-top:15px;"><?php esc_html_e('Action & Utility Buttons', 'gpt3-ai-content-generator'); ?></h5>
 <div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_action_button_bg_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Background', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_action_button_bg_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[action_button_bg_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo $esc_cts_val_attr('action_button_bg_color'); ?>">
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_action_button_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Icon/Text Color', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_action_button_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[action_button_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo $esc_cts_val_attr('action_button_color'); ?>">
    </div>
     <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_action_button_border_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Border Color', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_action_button_border_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[action_button_border_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo $esc_cts_val_attr('action_button_border_color'); ?>">
    </div>
</div>
 <div class="aipkit_form-row">
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_action_button_hover_bg_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Hover Background', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_action_button_hover_bg_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[action_button_hover_bg_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo $esc_cts_val_attr('action_button_hover_bg_color'); ?>">
    </div>
    <div class="aipkit_form-group aipkit_form-col">
        <label class="aipkit_form-label" for="cts_action_button_hover_color_<?php echo esc_attr($bot_id); ?>">
            <?php esc_html_e('Hover Icon/Text Color', 'gpt3-ai-content-generator'); ?>
        </label>
        <input type="color" id="cts_action_button_hover_color_<?php echo esc_attr($bot_id); ?>" name="custom_theme_settings[action_button_hover_color]" class="aipkit_form-input aipkit_color_picker_input" value="<?php echo $esc_cts_val_attr('action_button_hover_color'); ?>">
    </div>
</div>