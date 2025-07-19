<?php
/**
 * Partial: Chatbot Settings Pane Content
 *
 * Renders the settings form for a single existing chatbot.
 * Included within a loop in the main chatbot module view.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Variables passed from parent (chatbot/index.php loop):
// $bot_post, $bot_id, $bot_name, $bot_settings, $active_class, $is_default
// Also, all variables needed by the included accordion partials must be available in this scope:
// $providers, $grouped_openai_models, $openrouter_model_list, $google_model_list,
// $azure_deployment_list, $deepseek_model_list, $is_token_management_active, $is_voice_playback_active
// $saved_provider, $saved_model (these should be part of $bot_settings)

$saved_provider = $bot_settings['provider'] ?? 'OpenAI';
$saved_model = $bot_settings['model'] ?? '';

?>
<div class="aipkit_tab-content <?php echo esc_attr($active_class); ?>" id="chatbot-<?php echo esc_attr($bot_id); ?>-content">
    <!-- Settings Form -->
    <div class="aipkit_chatbot-settings-area">
        <form
            class="aipkit_chatbot_settings_form"
            data-bot-id="<?php echo esc_attr($bot_id); ?>"
            onsubmit="return false;"
        >
            <div class="aipkit_accordion-group"> <?php // Wrap all accordions in a group?>
                <?php // include __DIR__ . '/accordion-general.php'; // REMOVED: General settings merged into AI config ?>
                <?php include __DIR__ . '/accordion-ai-config.php'; ?>
                <?php include __DIR__ . '/accordion-appearance.php'; ?>
                <?php include __DIR__ . '/accordion-popup.php'; ?>
                <?php include __DIR__ . '/accordion-images.php'; ?>
                <?php if ($is_voice_playback_active) {
                    include __DIR__ . '/accordion-tts-settings.php';
                } ?>
                <?php if ($is_token_management_active) {
                    include __DIR__ . '/accordion-token-management.php';
                } ?>
                <?php include __DIR__ . '/accordion-context.php'; ?>
                <?php
                // --- MODIFIED: Conditional include for Triggers accordion ---
                if (class_exists('\WPAICG\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan() && \WPAICG\aipkit_dashboard::is_addon_active('triggers')) {
                    $triggers_accordion_path = WPAICG_LIB_DIR . 'views/chatbot/partials/accordion-triggers.php'; // New path
                    if (file_exists($triggers_accordion_path)) {
                        include $triggers_accordion_path;
                    }
                }
// --- END MODIFICATION ---
?>
            </div> <?php // End aipkit_accordion-group?>

            <div class="aipkit_bot-actions">
                <div class="aipkit_bot-actions_left">
                    <div class="aipkit_bot-actions_button_group">
                        <button
                            type="button"
                            class="aipkit_btn aipkit_btn-danger aipkit_delete_bot_btn"
                            data-bot-id="<?php echo esc_attr($bot_id); ?>"
                            data-bot-name="<?php echo esc_attr($bot_name); ?>"
                            data-is-default="<?php echo $is_default ? '1' : '0'; ?>"
                            <?php disabled($is_default); ?>
                            <?php if ($is_default) {
                                echo 'title="' . esc_attr__('The default chatbot cannot be deleted.', 'gpt3-ai-content-generator') . '"';
                            } ?>
                        >
                            <span class="aipkit_btn-text"><?php esc_html_e('Delete', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_spinner" style="display:none;"></span>
                        </button>
                        <button
                            type="button"
                            class="aipkit_btn aipkit_btn-secondary aipkit_reset_bot_btn"
                            data-bot-id="<?php echo esc_attr($bot_id); ?>"
                            data-bot-name="<?php echo esc_attr($bot_name); ?>"
                            data-is-default="<?php echo $is_default ? '1' : '0'; ?>"
                        >
                            <span class="aipkit_btn-text"><?php esc_html_e('Reset', 'gpt3-ai-content-generator'); ?></span>
                            <span class="aipkit_spinner" style="display:none;"></span>
                        </button>
                    </div>
                    <div
                        class="aipkit_action_feedback_area"
                        id="aipkit_action_feedback_<?php echo esc_attr($bot_id); ?>"
                    >
                        <div class="aipkit_confirmation_message aipkit_delete_confirmation" id="aipkit_delete_confirmation_<?php echo esc_attr($bot_id); ?>" style="display:none;"></div>
                        <div class="aipkit_confirmation_message aipkit_reset_confirmation" id="aipkit_reset_confirmation_<?php echo esc_attr($bot_id); ?>" style="display:none;"></div>
                        <div class="aipkit_action_status_message" id="aipkit_action_status_<?php echo esc_attr($bot_id); ?>"></div>
                    </div>
                </div>
                <div class="aipkit_bot-actions_right">
                    <button
                        type="submit"
                        class="aipkit_btn aipkit_btn-primary aipkit_save_bot_settings_btn"
                    >
                        <span class="aipkit_btn-text"><?php esc_html_e('Save', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_spinner" style="display:none;"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>