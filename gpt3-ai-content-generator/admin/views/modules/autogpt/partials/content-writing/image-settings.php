<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/autogpt/partials/content-writing/image-settings.php
// Status: MODIFIED

/**
 * Partial: Automated Task Form - AI Image Settings (Popover Body)
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\AIPKit_Providers;
use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompts;

$prompt_library = AIPKit_Content_Writer_Prompts::get_prompt_library();
$default_image_prompt = AIPKit_Content_Writer_Prompts::get_default_image_prompt();
$default_featured_image_prompt = AIPKit_Content_Writer_Prompts::get_default_featured_image_prompt();
$pexels_data = AIPKit_Providers::get_provider_data('Pexels');
$pixabay_data = AIPKit_Providers::get_provider_data('Pixabay');
$replicate_data = AIPKit_Providers::get_provider_data('Replicate');
$current_pexels_api_key = $pexels_data['api_key'] ?? '';
$current_pixabay_api_key = $pixabay_data['api_key'] ?? '';
$current_replicate_api_key = $replicate_data['api_key'] ?? '';

$render_prompt_library_options = static function (array $options): void {
    foreach ($options as $option) {
        if (empty($option['label']) || empty($option['prompt'])) {
            continue;
        }
        printf(
            '<option value="%s">%s</option>',
            esc_attr($option['prompt']),
            esc_html($option['label'])
        );
    }
};
?>

<div class="aipkit_image_settings_redesigned">
    <div class="aipkit_image_settings_chunk aipkit_image_settings_chunk--enable">
        <div class="aipkit_image_settings_chunk_body">
            <div class="aipkit_image_toggle_card">
                <div class="aipkit_image_toggle_row">
                    <div class="aipkit_image_toggle_info">
                        <span class="aipkit_image_toggle_label"><?php esc_html_e('Content Images', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_image_toggle_desc"><?php esc_html_e('Add images within the article body', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_image_toggle_controls">
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_task_cw_generate_images_enabled"
                                name="generate_images_enabled"
                                class="aipkit_toggle_switch aipkit_task_cw_image_enable_toggle"
                                value="1"
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                        <button
                            type="button"
                            class="aipkit_image_prompt_btn aipkit_task_cw_image_prompt_btn"
                            data-aipkit-flyout-target="aipkit_task_cw_image_prompt_flyout"
                            aria-controls="aipkit_task_cw_image_prompt_flyout"
                            aria-expanded="false"
                            title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                        >
                            <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="aipkit_image_toggle_card">
                <div class="aipkit_image_toggle_row">
                    <div class="aipkit_image_toggle_info">
                        <span class="aipkit_image_toggle_label"><?php esc_html_e('Featured Image', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_image_toggle_desc"><?php esc_html_e('Generate post thumbnail', 'gpt3-ai-content-generator'); ?></span>
                    </div>
                    <div class="aipkit_image_toggle_controls">
                        <label class="aipkit_switch">
                            <input
                                type="checkbox"
                                id="aipkit_task_cw_generate_featured_image"
                                name="generate_featured_image"
                                class="aipkit_toggle_switch"
                                value="1"
                            >
                            <span class="aipkit_switch_slider"></span>
                        </label>
                        <button
                            type="button"
                            class="aipkit_image_prompt_btn aipkit_task_cw_featured_image_prompt_btn"
                            data-aipkit-flyout-target="aipkit_task_cw_featured_image_prompt_flyout"
                            aria-controls="aipkit_task_cw_featured_image_prompt_flyout"
                            aria-expanded="false"
                            title="<?php esc_attr_e('Edit prompt', 'gpt3-ai-content-generator'); ?>"
                        >
                            <span class="dashicons dashicons-edit" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="aipkit_task_cw_image_settings_container" style="display: none;">
        <div class="aipkit_image_settings_chunk aipkit_image_settings_chunk--source">
            <div class="aipkit_image_settings_chunk_body">
                <div class="aipkit_image_source_selector">
                    <div class="aipkit_image_source_row">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_image_provider">
                            <?php esc_html_e('Provider', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select
                            id="aipkit_task_cw_image_provider"
                            name="image_provider"
                            class="aipkit_image_settings_select"
                            data-aipkit-provider-notice-target="aipkit_provider_notice_autogpt"
                            data-aipkit-provider-notice-defer="1"
                        >
                            <optgroup label="<?php echo esc_attr__('AI Providers', 'gpt3-ai-content-generator'); ?>">
                                <option value="openai" selected>OpenAI</option>
                                <option value="google">Google</option>
                                <option value="azure">Azure</option>
                                <option value="replicate"><?php esc_html_e('Replicate', 'gpt3-ai-content-generator'); ?></option>
                            </optgroup>
                            <optgroup label="<?php echo esc_attr__('Stock Photos', 'gpt3-ai-content-generator'); ?>">
                                <option value="pexels"><?php esc_html_e('Pexels', 'gpt3-ai-content-generator'); ?></option>
                                <option value="pixabay"><?php esc_html_e('Pixabay', 'gpt3-ai-content-generator'); ?></option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="aipkit_image_source_divider" id="aipkit_task_cw_image_model_divider">
                        <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                    </div>

                    <div class="aipkit_image_source_row aipkit_image_source_row--model" id="aipkit_task_cw_image_model_group">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_image_model">
                            <?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select id="aipkit_task_cw_image_model" name="image_model" class="aipkit_image_settings_select">
                            <?php // Populated by JS ?>
                        </select>
                    </div>
                </div>

                <div class="aipkit_image_provider_options" id="aipkit_task_cw_replicate_options" style="display: none;">
                    <div class="aipkit_image_provider_header">
                        <span class="aipkit_image_provider_badge aipkit_image_provider_badge--replicate">
                            <span class="dashicons dashicons-controls-play" aria-hidden="true"></span>
                            Replicate
                        </span>
                    </div>

                    <div class="aipkit_image_api_key_row">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_replicate_api_key">
                            <?php esc_html_e('API Key', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <div class="aipkit_image_api_key_input">
                            <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                                <input
                                    type="password"
                                    id="aipkit_task_cw_replicate_api_key"
                                    name="replicate_api_key"
                                    class="aipkit_form-input aipkit_image_settings_input aipkit_task_cw_replicate_api_key"
                                    value="<?php echo esc_attr($current_replicate_api_key); ?>"
                                    placeholder="<?php esc_attr_e('Enter API key', 'gpt3-ai-content-generator'); ?>"
                                    autocomplete="new-password"
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                />
                                <span class="aipkit_api-key-toggle">
                                    <span class="dashicons dashicons-visibility"></span>
                                </span>
                            </div>
                            <a href="https://replicate.com/account/api-tokens" target="_blank" rel="noopener noreferrer" class="aipkit_image_get_key_link">
                                <?php esc_html_e('Get key', 'gpt3-ai-content-generator'); ?>
                                <span class="dashicons dashicons-external" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="aipkit_image_provider_options" id="aipkit_task_cw_pexels_options" style="display: none;">
                    <div class="aipkit_image_provider_header">
                        <span class="aipkit_image_provider_badge aipkit_image_provider_badge--pexels">
                            <span class="dashicons dashicons-camera" aria-hidden="true"></span>
                            Pexels
                        </span>
                    </div>

                    <div class="aipkit_image_api_key_row">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_pexels_api_key">
                            <?php esc_html_e('API Key', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <div class="aipkit_image_api_key_input">
                            <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                                <input
                                    type="password"
                                    id="aipkit_task_cw_pexels_api_key"
                                    name="pexels_api_key"
                                    class="aipkit_form-input aipkit_image_settings_input aipkit_task_cw_stock_api_key"
                                    value="<?php echo esc_attr($current_pexels_api_key); ?>"
                                    placeholder="<?php esc_attr_e('Enter API key', 'gpt3-ai-content-generator'); ?>"
                                    autocomplete="new-password"
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                />
                                <span class="aipkit_api-key-toggle">
                                    <span class="dashicons dashicons-visibility"></span>
                                </span>
                            </div>
                            <a href="https://www.pexels.com/api/" target="_blank" rel="noopener noreferrer" class="aipkit_image_get_key_link">
                                <?php esc_html_e('Get key', 'gpt3-ai-content-generator'); ?>
                                <span class="dashicons dashicons-external" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>

                    <div class="aipkit_image_filters_grid">
                        <div class="aipkit_image_filter_item aipkit_form-group">
                            <label class="aipkit_image_settings_label" for="aipkit_task_cw_pexels_orientation"><?php esc_html_e('Orientation', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_task_cw_pexels_orientation" name="pexels_orientation" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                                <option value="none"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                <option value="landscape"><?php esc_html_e('Landscape', 'gpt3-ai-content-generator'); ?></option>
                                <option value="portrait"><?php esc_html_e('Portrait', 'gpt3-ai-content-generator'); ?></option>
                                <option value="square"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                        <div class="aipkit_image_filter_item aipkit_form-group">
                            <label class="aipkit_image_settings_label" for="aipkit_task_cw_pexels_size"><?php esc_html_e('Size', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_task_cw_pexels_size" name="pexels_size" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                                <option value="none"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                <option value="large"><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                                <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                                <option value="small"><?php esc_html_e('Small', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                        <div class="aipkit_image_filter_item aipkit_form-group">
                            <label class="aipkit_image_settings_label" for="aipkit_task_cw_pexels_color"><?php esc_html_e('Color', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_task_cw_pexels_color" name="pexels_color" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                                <option value=""><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                <option value="red"><?php esc_html_e('Red', 'gpt3-ai-content-generator'); ?></option>
                                <option value="orange"><?php esc_html_e('Orange', 'gpt3-ai-content-generator'); ?></option>
                                <option value="yellow"><?php esc_html_e('Yellow', 'gpt3-ai-content-generator'); ?></option>
                                <option value="green"><?php esc_html_e('Green', 'gpt3-ai-content-generator'); ?></option>
                                <option value="turquoise"><?php esc_html_e('Turquoise', 'gpt3-ai-content-generator'); ?></option>
                                <option value="blue"><?php esc_html_e('Blue', 'gpt3-ai-content-generator'); ?></option>
                                <option value="violet"><?php esc_html_e('Violet', 'gpt3-ai-content-generator'); ?></option>
                                <option value="pink"><?php esc_html_e('Pink', 'gpt3-ai-content-generator'); ?></option>
                                <option value="brown"><?php esc_html_e('Brown', 'gpt3-ai-content-generator'); ?></option>
                                <option value="black"><?php esc_html_e('Black', 'gpt3-ai-content-generator'); ?></option>
                                <option value="gray"><?php esc_html_e('Gray', 'gpt3-ai-content-generator'); ?></option>
                                <option value="white"><?php esc_html_e('White', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="aipkit_image_provider_options" id="aipkit_task_cw_pixabay_options" style="display: none;">
                    <div class="aipkit_image_provider_header">
                        <span class="aipkit_image_provider_badge aipkit_image_provider_badge--pixabay">
                            <span class="dashicons dashicons-images-alt2" aria-hidden="true"></span>
                            Pixabay
                        </span>
                    </div>

                    <div class="aipkit_image_api_key_row">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_pixabay_api_key">
                            <?php esc_html_e('API Key', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <div class="aipkit_image_api_key_input">
                            <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                                <input
                                    type="password"
                                    id="aipkit_task_cw_pixabay_api_key"
                                    name="pixabay_api_key"
                                    class="aipkit_form-input aipkit_image_settings_input aipkit_task_cw_stock_api_key"
                                    value="<?php echo esc_attr($current_pixabay_api_key); ?>"
                                    placeholder="<?php esc_attr_e('Enter API key', 'gpt3-ai-content-generator'); ?>"
                                    autocomplete="new-password"
                                    data-lpignore="true"
                                    data-1p-ignore="true"
                                />
                                <span class="aipkit_api-key-toggle">
                                    <span class="dashicons dashicons-visibility"></span>
                                </span>
                            </div>
                            <a href="https://pixabay.com/api/docs/" target="_blank" rel="noopener noreferrer" class="aipkit_image_get_key_link">
                                <?php esc_html_e('Get key', 'gpt3-ai-content-generator'); ?>
                                <span class="dashicons dashicons-external" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>

                    <div class="aipkit_image_filters_grid">
                        <div class="aipkit_image_filter_item aipkit_form-group">
                            <label class="aipkit_image_settings_label" for="aipkit_task_cw_pixabay_orientation"><?php esc_html_e('Orientation', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_task_cw_pixabay_orientation" name="pixabay_orientation" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                                <option value="all"><?php esc_html_e('All', 'gpt3-ai-content-generator'); ?></option>
                                <option value="horizontal"><?php esc_html_e('Horizontal', 'gpt3-ai-content-generator'); ?></option>
                                <option value="vertical"><?php esc_html_e('Vertical', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                        <div class="aipkit_image_filter_item aipkit_form-group">
                            <label class="aipkit_image_settings_label" for="aipkit_task_cw_pixabay_image_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_task_cw_pixabay_image_type" name="pixabay_image_type" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                                <option value="all"><?php esc_html_e('All', 'gpt3-ai-content-generator'); ?></option>
                                <option value="photo"><?php esc_html_e('Photo', 'gpt3-ai-content-generator'); ?></option>
                                <option value="illustration"><?php esc_html_e('Illustration', 'gpt3-ai-content-generator'); ?></option>
                                <option value="vector"><?php esc_html_e('Vector', 'gpt3-ai-content-generator'); ?></option>
                            </select>
                        </div>
                        <div class="aipkit_image_filter_item aipkit_form-group">
                            <label class="aipkit_image_settings_label" for="aipkit_task_cw_pixabay_category"><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_task_cw_pixabay_category" name="pixabay_category" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                                <option value=""><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                <?php
                                $pixabay_categories = ['backgrounds', 'fashion', 'nature', 'science', 'education', 'feelings', 'health', 'people', 'religion', 'places', 'animals', 'industry', 'computer', 'food', 'sports', 'transportation', 'travel', 'buildings', 'business', 'music'];
                                foreach ($pixabay_categories as $cat) {
                                    echo '<option value="' . esc_attr($cat) . '">' . esc_html(ucfirst($cat)) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="aipkit_image_settings_chunk aipkit_image_settings_chunk--display aipkit_image_settings_chunk--collapsible aipkit_task_cw_image_display_chunk">
            <button type="button" class="aipkit_image_settings_chunk_header aipkit_image_settings_chunk_header--collapsible" aria-expanded="false" aria-controls="aipkit_task_cw_display_options_body">
                <span class="aipkit_image_settings_chunk_icon">
                    <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
                </span>
                <span class="aipkit_image_settings_chunk_title"><?php esc_html_e('Display Options', 'gpt3-ai-content-generator'); ?></span>
                <span class="aipkit_image_settings_chunk_toggle">
                    <span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
                </span>
            </button>

            <div class="aipkit_image_settings_chunk_body aipkit_image_settings_chunk_body--collapsible" id="aipkit_task_cw_display_options_body" aria-hidden="true">
                <div class="aipkit_image_display_grid aipkit_image_display_container">
                    <div class="aipkit_image_display_item aipkit_form-group">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_image_count">
                            <?php esc_html_e('Count', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <input
                            type="number"
                            id="aipkit_task_cw_image_count"
                            name="image_count"
                            class="aipkit_image_settings_input aipkit_image_settings_input--number"
                            value="1"
                            min="1"
                            max="10"
                        >
                    </div>
                    <div class="aipkit_image_display_item aipkit_form-group">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_image_size">
                            <?php esc_html_e('Size', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select id="aipkit_task_cw_image_size" name="image_size" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                            <option value="large" selected><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                            <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                            <option value="thumbnail"><?php esc_html_e('Thumbnail', 'gpt3-ai-content-generator'); ?></option>
                            <option value="full"><?php esc_html_e('Full', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                    <div class="aipkit_image_display_item aipkit_form-group">
                        <label class="aipkit_image_settings_label" for="aipkit_task_cw_image_alignment">
                            <?php esc_html_e('Align', 'gpt3-ai-content-generator'); ?>
                        </label>
                        <select id="aipkit_task_cw_image_alignment" name="image_alignment" class="aipkit_image_settings_select aipkit_image_settings_select--compact">
                            <option value="none" selected><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                            <option value="left"><?php esc_html_e('Left', 'gpt3-ai-content-generator'); ?></option>
                            <option value="center"><?php esc_html_e('Center', 'gpt3-ai-content-generator'); ?></option>
                            <option value="right"><?php esc_html_e('Right', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="aipkit_image_placement_wrapper aipkit_image_display_container aipkit_form-group">
                    <label class="aipkit_image_settings_label" for="aipkit_task_cw_image_placement">
                        <?php esc_html_e('Placement', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <div class="aipkit_image_placement_controls">
                        <select id="aipkit_task_cw_image_placement" name="image_placement" class="aipkit_image_settings_select aipkit_task_cw_image_placement_select">
                            <option value="after_first_h2"><?php esc_html_e('After 1st H2', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_first_h3"><?php esc_html_e('After 1st H3', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_every_x_h2"><?php esc_html_e('Every X H2s', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_every_x_h3"><?php esc_html_e('Every X H3s', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_every_x_p"><?php esc_html_e('Every X paragraphs', 'gpt3-ai-content-generator'); ?></option>
                            <option value="at_end"><?php esc_html_e('End of content', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                        <div class="aipkit_image_placement_x_wrapper aipkit_task_cw_image_placement_x_field" style="display: none;">
                            <span class="aipkit_image_placement_x_label">X =</span>
                            <input
                                type="number"
                                id="aipkit_task_cw_image_placement_param_x"
                                name="image_placement_param_x"
                                class="aipkit_image_settings_input aipkit_image_settings_input--small"
                                value="2"
                                min="1"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="aipkit_cw_prompt_flyout" id="aipkit_task_cw_image_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Image Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Image Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_task_cw_image_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_task_cw_image_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_image_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['image'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_task_cw_image_prompt" name="image_prompt" class="aipkit_cw_prompt_editor_textarea" rows="8" placeholder="<?php esc_attr_e('e.g., A photo of a freshly baked chocolate cake on a wooden table.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    id="aipkit_task_cw_image_prompt_placeholders"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                    data-prompt-type="image"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="aipkit_cw_prompt_flyout" id="aipkit_task_cw_featured_image_prompt_flyout" aria-hidden="true">
    <div class="aipkit_cw_prompt_panel aipkit_cw_prompt_panel--tall" role="dialog" aria-label="<?php esc_attr_e('Featured Image Prompt', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_cw_prompt_editor">
            <div class="aipkit_cw_prompt_editor_toolbar">
                <span class="aipkit_cw_prompt_editor_title"><?php esc_html_e('Featured Image Prompt', 'gpt3-ai-content-generator'); ?></span>
                <select
                    id="aipkit_task_cw_featured_image_prompt_library"
                    class="aipkit_cw_prompt_template_select aipkit_cw_prompt_library_select"
                    data-aipkit-prompt-target="aipkit_task_cw_featured_image_prompt"
                    title="<?php esc_attr_e('Load template', 'gpt3-ai-content-generator'); ?>"
                >
                    <option value="<?php echo esc_attr($default_featured_image_prompt); ?>"><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                    <?php $render_prompt_library_options($prompt_library['featured_image'] ?? []); ?>
                </select>
            </div>
            <textarea id="aipkit_task_cw_featured_image_prompt" name="featured_image_prompt" class="aipkit_cw_prompt_editor_textarea" rows="6" placeholder="<?php esc_attr_e('Leave blank to use the main image prompt.', 'gpt3-ai-content-generator'); ?>"></textarea>
            <div class="aipkit_cw_prompt_editor_footer">
                <span
                    class="aipkit_cw_prompt_editor_placeholders"
                    data-label="<?php esc_attr_e('Variables:', 'gpt3-ai-content-generator'); ?>"
                    data-copy-title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>"
                    data-prompt-type="featured_image"
                >
                    <?php esc_html_e('Variables:', 'gpt3-ai-content-generator'); ?>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{topic}</code>
                    <code class="aipkit-placeholder" title="<?php esc_attr_e('Click to copy', 'gpt3-ai-content-generator'); ?>">{keywords}</code>
                </span>
            </div>
        </div>
    </div>
</div>
