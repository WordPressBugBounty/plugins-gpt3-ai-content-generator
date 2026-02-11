<?php
$aipkit_popup_default_icon_url = esc_url((defined('WPAICG_PLUGIN_URL') ? WPAICG_PLUGIN_URL : plugin_dir_url(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . 'public/images/icon.svg');
$aipkit_validate_url = static function ($url) {
    $url = trim((string)$url);
    if ($url === '') {
        return false;
    }
    if (function_exists('wp_http_validate_url')) {
        return (bool) wp_http_validate_url($url);
    }
    return (bool) filter_var($url, FILTER_VALIDATE_URL);
};
$aipkit_popup_custom_icon_url_value = '';
if ($popup_icon_type === 'custom') {
    $popup_icon_candidate = trim((string)$popup_icon_value);
    if ($aipkit_validate_url($popup_icon_candidate)) {
        $aipkit_popup_custom_icon_url_value = $popup_icon_candidate;
    } else {
        $aipkit_popup_custom_icon_url_value = $aipkit_popup_default_icon_url;
    }
}

$aipkit_header_avatar_custom_url_value = '';
if ($saved_header_avatar_type === 'custom') {
    $header_avatar_candidate = trim((string)$saved_header_avatar_url);
    if ($header_avatar_candidate === '' && !empty($saved_header_avatar_value)) {
        $header_avatar_candidate = trim((string)$saved_header_avatar_value);
    }
    if ($aipkit_validate_url($header_avatar_candidate)) {
        $aipkit_header_avatar_custom_url_value = $header_avatar_candidate;
    } else {
        $aipkit_header_avatar_custom_url_value = $aipkit_popup_default_icon_url;
    }
}
?>
<div class="aipkit_interface_section aipkit_interface_section--popup">
        <div class="aipkit_interface_popup_settings" id="aipkit_builder_popup_settings_panel">
            <div class="aipkit_interface_popup_grid">
                <div class="aipkit_popover_option_row aipkit_interface_cell aipkit_interface_cell--popup aipkit_interface_cell--popup-wide aipkit_interface_popup_icons_row">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_popover_inline_controls aipkit_popover_inline_controls--labeled aipkit_interface_popup_icon_controls">
                            <div class="aipkit_interface_popup_inline_field aipkit_interface_popup_inline_field--icons">
                                <label class="aipkit_popover_option_label aipkit_interface_popup_inline_label">
                                    <?php esc_html_e('Icon', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <div class="aipkit_popup_icon_default_selector_container">
                                    <div class="aipkit_popup_icon_default_selector">
                                        <?php foreach ($popup_icons as $icon_key => $svg_html) : ?>
                                            <?php
                                            $radio_id = 'aipkit_bot_' . absint($bot_id) . '_popup_icon_deploy_' . sanitize_key($icon_key);
                                            $icon_checked = ($popup_icon_type !== 'custom' && $popup_icon_value === $icon_key);
                                            ?>
                                            <label class="aipkit_option_card" for="<?php echo esc_attr($radio_id); ?>" title="<?php echo esc_attr(ucfirst(str_replace('-', ' ', $icon_key))); ?>">
                                                <input
                                                    type="radio"
                                                    id="<?php echo esc_attr($radio_id); ?>"
                                                    name="popup_icon_default"
                                                    value="<?php echo esc_attr($icon_key); ?>"
                                                    <?php checked($icon_checked); ?>
                                                />
                                                <?php echo $svg_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            </label>
                                        <?php endforeach; ?>
                                        <?php $popup_custom_radio_id = 'aipkit_bot_' . absint($bot_id) . '_popup_icon_deploy_custom'; ?>
                                        <label class="aipkit_option_card aipkit_option_card--custom-url" for="<?php echo esc_attr($popup_custom_radio_id); ?>">
                                            <input
                                                type="radio"
                                                id="<?php echo esc_attr($popup_custom_radio_id); ?>"
                                                name="popup_icon_default"
                                                value="__custom__"
                                                <?php checked($popup_icon_type, 'custom'); ?>
                                            />
                                            <span><?php esc_html_e('Custom', 'gpt3-ai-content-generator'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="aipkit_interface_popup_inline_field aipkit_interface_popup_inline_field--custom-url aipkit_interface_popup_inline_field--no-label aipkit_popup_icon_custom_input_container" <?php echo ($popup_icon_type === 'custom') ? '' : 'hidden'; ?>>
                                <input
                                    type="url"
                                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_popup_icon_custom_url_deploy"
                                    name="popup_icon_custom_url"
                                    class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                                    aria-label="<?php esc_attr_e('Launcher icon URL', 'gpt3-ai-content-generator'); ?>"
                                    data-default-url="<?php echo esc_url($aipkit_popup_default_icon_url); ?>"
                                    value="<?php echo ($popup_icon_type === 'custom') ? esc_url($aipkit_popup_custom_icon_url_value) : ''; ?>"
                                    placeholder="<?php esc_attr_e('Enter full URL (e.g., https://.../icon.png)', 'gpt3-ai-content-generator'); ?>"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="aipkit_popover_option_row aipkit_interface_cell aipkit_interface_cell--popup aipkit_interface_cell--popup-wide">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_popover_inline_controls aipkit_popover_inline_controls--labeled aipkit_interface_popup_icon_controls">
                            <div class="aipkit_interface_popup_inline_field aipkit_interface_popup_inline_field--icons">
                                <label class="aipkit_popover_option_label aipkit_interface_popup_inline_label">
                                    <?php esc_html_e('Avatar', 'gpt3-ai-content-generator'); ?>
                                </label>
                                <div class="aipkit_header_avatar_default_selector_container">
                                    <div class="aipkit_popup_icon_default_selector">
                                        <?php foreach ($popup_icons as $icon_key => $svg_html) : ?>
                                            <?php
                                            $radio_id = 'aipkit_bot_' . absint($bot_id) . '_header_avatar_icon_deploy_' . sanitize_key($icon_key);
                                            $icon_checked = ($saved_header_avatar_type !== 'custom' && $saved_header_avatar_value === $icon_key);
                                            ?>
                                            <label class="aipkit_option_card" for="<?php echo esc_attr($radio_id); ?>" title="<?php echo esc_attr(ucfirst(str_replace('-', ' ', $icon_key))); ?>">
                                                <input
                                                    type="radio"
                                                    id="<?php echo esc_attr($radio_id); ?>"
                                                    name="header_avatar_default"
                                                    value="<?php echo esc_attr($icon_key); ?>"
                                                    <?php checked($icon_checked); ?>
                                                />
                                                <?php echo $svg_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                            </label>
                                        <?php endforeach; ?>
                                        <?php $header_custom_radio_id = 'aipkit_bot_' . absint($bot_id) . '_header_avatar_icon_deploy_custom'; ?>
                                        <label class="aipkit_option_card aipkit_option_card--custom-url" for="<?php echo esc_attr($header_custom_radio_id); ?>">
                                            <input
                                                type="radio"
                                                id="<?php echo esc_attr($header_custom_radio_id); ?>"
                                                name="header_avatar_default"
                                                value="__custom__"
                                                <?php checked($saved_header_avatar_type, 'custom'); ?>
                                            />
                                            <span><?php esc_html_e('Custom', 'gpt3-ai-content-generator'); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="aipkit_interface_popup_inline_field aipkit_interface_popup_inline_field--custom-url aipkit_interface_popup_inline_field--no-label aipkit_header_avatar_custom_input_container" <?php echo ($saved_header_avatar_type === 'custom') ? '' : 'hidden'; ?>>
                                <input
                                    type="url"
                                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_header_avatar_url_deploy"
                                    name="header_avatar_url"
                                    class="aipkit_popover_option_input aipkit_popover_option_input--framed"
                                    aria-label="<?php esc_attr_e('Avatar URL', 'gpt3-ai-content-generator'); ?>"
                                    data-default-url="<?php echo esc_url($aipkit_popup_default_icon_url); ?>"
                                    value="<?php echo ($saved_header_avatar_type === 'custom') ? esc_url($aipkit_header_avatar_custom_url_value) : ''; ?>"
                                    placeholder="<?php esc_attr_e('Enter full URL (e.g., https://.../avatar.png)', 'gpt3-ai-content-generator'); ?>"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
