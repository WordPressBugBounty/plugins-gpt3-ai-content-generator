<?php
/**
 * Partial: Chatbot Token Management Settings (Flyout)
 */
if (!defined('ABSPATH')) { exit; }

use WPAICG\Chat\Storage\BotSettingsManager;
use WPAICG\Core\TokenManager\Constants\CronHookConstant;

$default_reset_period = BotSettingsManager::DEFAULT_TOKEN_RESET_PERIOD;
$default_limit_message = __('You have reached your token limit for this period.', 'gpt3-ai-content-generator');

$guest_limit = $bot_settings['token_guest_limit'] ?? null;
$user_limit = $bot_settings['token_user_limit'] ?? null;
$reset_period = $bot_settings['token_reset_period'] ?? $default_reset_period;
$limit_message = $bot_settings['token_limit_message'] ?? $default_limit_message;
$limit_mode = $bot_settings['token_limit_mode'] ?? BotSettingsManager::DEFAULT_TOKEN_LIMIT_MODE;
$role_limits = $bot_settings['token_role_limits'] ?? [];

$guest_limit_value = ($guest_limit === null) ? '' : (string) $guest_limit;
$user_limit_value = ($user_limit === null) ? '' : (string) $user_limit;
?>

<div class="aipkit_popover_options_list aipkit_popover_options_list--limits">
  <div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
      <label
        class="aipkit_popover_option_label"
        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_guest_limit_flyout"
        data-tooltip="<?php echo esc_attr__('0 = disabled.', 'gpt3-ai-content-generator'); ?>"
      >
        <?php esc_html_e('Guest limit', 'gpt3-ai-content-generator'); ?>
      </label>
      <input
        type="number"
        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_guest_limit_flyout"
        name="token_guest_limit"
        class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact"
        value="<?php echo esc_attr($guest_limit_value); ?>"
        min="0"
        step="1"
        placeholder="<?php esc_attr_e('(Unlimited)', 'gpt3-ai-content-generator'); ?>"
      />
    </div>
  </div>

  <div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
      <label
        class="aipkit_popover_option_label"
        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_limit_mode_flyout"
        data-tooltip="<?php echo esc_attr__('For logged-in users.', 'gpt3-ai-content-generator'); ?>"
      >
        <?php esc_html_e('Limit type', 'gpt3-ai-content-generator'); ?>
      </label>
      <select
        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_limit_mode_flyout"
        name="token_limit_mode"
        class="aipkit_popover_option_select aipkit_token_limit_mode_select"
      >
        <option value="general" <?php selected($limit_mode, 'general'); ?>>
          <?php esc_html_e('General limit', 'gpt3-ai-content-generator'); ?>
        </option>
        <option value="role_based" <?php selected($limit_mode, 'role_based'); ?>>
          <?php esc_html_e('Role-based limits', 'gpt3-ai-content-generator'); ?>
        </option>
      </select>
    </div>
  </div>

  <div class="aipkit_popover_option_row aipkit_token_general_user_limit_field" style="display: <?php echo ($limit_mode === 'general') ? 'block' : 'none'; ?>;">
    <div class="aipkit_popover_option_main">
      <label
        class="aipkit_popover_option_label"
        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_user_limit_flyout"
        data-tooltip="<?php echo esc_attr__('0 = disabled.', 'gpt3-ai-content-generator'); ?>"
      >
        <?php esc_html_e('User limit', 'gpt3-ai-content-generator'); ?>
      </label>
      <input
        type="number"
        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_user_limit_flyout"
        name="token_user_limit"
        class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact"
        value="<?php echo esc_attr($user_limit_value); ?>"
        min="0"
        step="1"
        placeholder="<?php esc_attr_e('(Unlimited)', 'gpt3-ai-content-generator'); ?>"
      />
    </div>
  </div>

  <div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
      <label
        class="aipkit_popover_option_label"
        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_reset_period_flyout"
        data-tooltip="<?php echo esc_attr__('How often usage resets.', 'gpt3-ai-content-generator'); ?>"
      >
        <?php esc_html_e('Reset period', 'gpt3-ai-content-generator'); ?>
      </label>
      <select
        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_reset_period_flyout"
        name="token_reset_period"
        class="aipkit_popover_option_select"
      >
        <option value="never" <?php selected($reset_period, 'never'); ?>>
          <?php esc_html_e('Never', 'gpt3-ai-content-generator'); ?>
        </option>
        <option value="daily" <?php selected($reset_period, 'daily'); ?>>
          <?php esc_html_e('Daily', 'gpt3-ai-content-generator'); ?>
        </option>
        <option value="weekly" <?php selected($reset_period, 'weekly'); ?>>
          <?php esc_html_e('Weekly', 'gpt3-ai-content-generator'); ?>
        </option>
        <option value="monthly" <?php selected($reset_period, 'monthly'); ?>>
          <?php esc_html_e('Monthly', 'gpt3-ai-content-generator'); ?>
        </option>
      </select>
    </div>
  </div>

  <div class="aipkit_popover_option_row aipkit_token_role_limits_container" style="display: <?php echo ($limit_mode === 'role_based') ? 'block' : 'none'; ?>;">
    <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
      <span
        class="aipkit_popover_option_label"
        tabindex="0"
        data-tooltip="<?php echo esc_attr__('Set limits for specific roles. Leave empty for unlimited, use 0 to disable access for a role.', 'gpt3-ai-content-generator'); ?>"
      >
        <?php esc_html_e('Role limits', 'gpt3-ai-content-generator'); ?>
      </span>
      <div class="aipkit_popover_role_limits">
        <?php
        $editable_roles = get_editable_roles();
        foreach ($editable_roles as $role_slug => $role_info) :
          $role_name = translate_user_role($role_info['name']);
          $role_limit_value = isset($role_limits[$role_slug]) ? $role_limits[$role_slug] : '';
        ?>
          <div class="aipkit_popover_role_limit_row">
            <span class="aipkit_popover_role_limit_label"><?php echo esc_html($role_name); ?></span>
            <input
              type="number"
              id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_role_<?php echo esc_attr($role_slug); ?>_flyout"
              name="token_role_limits[<?php echo esc_attr($role_slug); ?>]"
              class="aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_popover_option_input--compact"
              value="<?php echo esc_attr($role_limit_value); ?>"
              min="0"
              step="1"
              placeholder="<?php esc_attr_e('(Unlimited)', 'gpt3-ai-content-generator'); ?>"
            />
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="aipkit_popover_option_row">
    <div class="aipkit_popover_option_main">
      <label
        class="aipkit_popover_option_label"
        for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_limit_message_flyout"
        data-tooltip="<?php echo esc_attr__('The message shown to users when they exceed their token limit for the period.', 'gpt3-ai-content-generator'); ?>"
      >
        <?php esc_html_e('Limit message', 'gpt3-ai-content-generator'); ?>
      </label>
      <input
        type="text"
        id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_token_limit_message_flyout"
        name="token_limit_message"
        class="aipkit_popover_option_input aipkit_popover_option_input--wide aipkit_popover_option_input--framed"
        value="<?php echo esc_attr($limit_message); ?>"
        placeholder="<?php echo esc_attr($default_limit_message); ?>"
      />
    </div>
  </div>

  <?php if (!wp_next_scheduled(CronHookConstant::CRON_HOOK)) : ?>
    <div class="aipkit_popover_option_row aipkit_popover_option_row--notice">
      <div class="aipkit_popover_option_main">
        <span class="aipkit_popover_option_notice">
          <?php esc_html_e('Warning: WP Cron task for resets is not scheduled!', 'gpt3-ai-content-generator'); ?>
        </span>
      </div>
    </div>
  <?php endif; ?>
</div>
<div class="aipkit_popover_flyout_footer">
  <span class="aipkit_popover_flyout_footer_text">
    <?php esc_html_e('Need help? Read the docs.', 'gpt3-ai-content-generator'); ?>
  </span>
  <a
    class="aipkit_popover_flyout_footer_link"
    href="<?php echo esc_url('https://docs.aipower.org/docs/token-management'); ?>"
    target="_blank"
    rel="noopener noreferrer"
  >
    <?php esc_html_e('Documentation', 'gpt3-ai-content-generator'); ?>
  </a>
</div>
