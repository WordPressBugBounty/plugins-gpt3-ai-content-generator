<?php
if (!defined('ABSPATH')) {
    exit;
}

$aipkit_cw_image_display_settings_render_mode = $aipkit_cw_image_display_settings_render_mode ?? 'both';
$aipkit_cw_render_image_settings_trigger = $aipkit_cw_image_display_settings_render_mode !== 'popover';
$aipkit_cw_render_image_settings_popover = $aipkit_cw_image_display_settings_render_mode !== 'trigger';
?>

<?php if ($aipkit_cw_render_image_settings_trigger) : ?>
<button
    type="button"
    class="aipkit_cw_settings_icon_trigger"
    id="aipkit_cw_image_display_settings_trigger"
    data-aipkit-popover-target="aipkit_cw_image_display_settings_popover"
    data-aipkit-popover-placement="left"
    aria-controls="aipkit_cw_image_display_settings_popover"
    aria-expanded="false"
    aria-label="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>"
    title="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>"
>
    <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
</button>
<?php endif; ?>

<?php if ($aipkit_cw_render_image_settings_popover) : ?>
<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_cw_image_display_settings_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel aipkit_cw_image_settings_popover_panel" role="dialog" aria-label="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_header aipkit_cw_settings_sheet_header">
            <span class="aipkit_model_settings_popover_title"><?php esc_html_e('Image settings', 'gpt3-ai-content-generator'); ?></span>
        </div>
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body aipkit_cw_settings_sheet_body">
            <div class="aipkit_popover_options_list">
                <div class="aipkit_popover_option_row" id="aipkit_cw_image_display_count_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_cw_image_count">
                                <?php esc_html_e('Count', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('How many to insert.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <input
                            type="number"
                            id="aipkit_cw_image_count"
                            name="image_count"
                            class="aipkit_form-input aipkit_autosave_trigger aipkit_popover_option_input aipkit_popover_option_input--compact"
                            value="1"
                            min="1"
                            max="10"
                        />
                    </div>
                </div>

                <div class="aipkit_popover_option_row" id="aipkit_cw_image_display_size_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_cw_image_size">
                                <?php esc_html_e('Size', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('Image size in the post.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <select
                            id="aipkit_cw_image_size"
                            name="image_size"
                            class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                        >
                            <option value="large" selected><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                            <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                            <option value="thumbnail"><?php esc_html_e('Thumbnail', 'gpt3-ai-content-generator'); ?></option>
                            <option value="full"><?php esc_html_e('Full', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="aipkit_popover_option_row" id="aipkit_cw_image_display_alignment_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_cw_image_alignment">
                                <?php esc_html_e('Align', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('Image alignment.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <select
                            id="aipkit_cw_image_alignment"
                            name="image_alignment"
                            class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                        >
                            <option value="none" selected><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                            <option value="left"><?php esc_html_e('Left', 'gpt3-ai-content-generator'); ?></option>
                            <option value="center"><?php esc_html_e('Center', 'gpt3-ai-content-generator'); ?></option>
                            <option value="right"><?php esc_html_e('Right', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="aipkit_popover_option_row" id="aipkit_cw_image_display_placement_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_cw_image_placement">
                                <?php esc_html_e('Placement', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('Where images appear.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <select
                            id="aipkit_cw_image_placement"
                            name="image_placement"
                            class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                        >
                            <option value="after_first_h2"><?php esc_html_e('After 1st H2', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_first_h3"><?php esc_html_e('After 1st H3', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_every_x_h2"><?php esc_html_e('Every X H2s', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_every_x_h3"><?php esc_html_e('Every X H3s', 'gpt3-ai-content-generator'); ?></option>
                            <option value="after_every_x_p"><?php esc_html_e('Every X paragraphs', 'gpt3-ai-content-generator'); ?></option>
                            <option value="at_end"><?php esc_html_e('End of content', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="aipkit_popover_option_row" id="aipkit_cw_image_display_param_x_field" hidden>
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_cw_image_placement_param_x">
                                <?php esc_html_e('X', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('Used with every-X placements.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <input
                            type="number"
                            id="aipkit_cw_image_placement_param_x"
                            name="image_placement_param_x"
                            class="aipkit_form-input aipkit_autosave_trigger aipkit_popover_option_input aipkit_popover_option_input--compact"
                            value="2"
                            min="1"
                        />
                    </div>
                </div>

                <div id="aipkit_cw_image_provider_options_block" hidden>
                    <div id="aipkit_cw_pexels_options" hidden>
                        <div class="aipkit_popover_option_row aipkit_popover_option_row--section">
                            <div class="aipkit_popover_option_main">
                                <span class="aipkit_popover_option_section_title"><?php esc_html_e('Pexels', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                                <div class="aipkit_popover_option_header">
                                    <div class="aipkit_cw_settings_option_text">
                                        <label class="aipkit_popover_option_label" for="aipkit_cw_pexels_api_key">
                                            <?php esc_html_e('API Key', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                        <span class="aipkit_popover_option_helper">
                                            <?php esc_html_e('Required to fetch images from Pexels.', 'gpt3-ai-content-generator'); ?>
                                        </span>
                                    </div>
                                    <a href="https://www.pexels.com/api/" target="_blank" rel="noopener noreferrer" class="aipkit_image_get_key_link">
                                        <?php esc_html_e('Get key', 'gpt3-ai-content-generator'); ?>
                                        <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                    </a>
                                </div>
                                <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                                    <input
                                        type="password"
                                        id="aipkit_cw_pexels_api_key"
                                        name="pexels_api_key"
                                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_cw_stock_api_key"
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
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_cw_pexels_orientation"><?php esc_html_e('Orientation', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Landscape, portrait, or square results.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_cw_pexels_orientation" name="pexels_orientation" class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
                                    <option value="none"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="landscape"><?php esc_html_e('Landscape', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="portrait"><?php esc_html_e('Portrait', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="square"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_cw_pexels_size"><?php esc_html_e('Size', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Filter results by image size.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_cw_pexels_size" name="pexels_size" class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
                                    <option value="none"><?php esc_html_e('Any', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="large"><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="small"><?php esc_html_e('Small', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_cw_pexels_color"><?php esc_html_e('Color', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Filter by dominant color.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_cw_pexels_color" name="pexels_color" class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
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

                    <div id="aipkit_cw_pixabay_options" hidden>
                        <div class="aipkit_popover_option_row aipkit_popover_option_row--section">
                            <div class="aipkit_popover_option_main">
                                <span class="aipkit_popover_option_section_title"><?php esc_html_e('Pixabay', 'gpt3-ai-content-generator'); ?></span>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main aipkit_popover_option_main--stacked">
                                <div class="aipkit_popover_option_header">
                                    <div class="aipkit_cw_settings_option_text">
                                        <label class="aipkit_popover_option_label" for="aipkit_cw_pixabay_api_key">
                                            <?php esc_html_e('API Key', 'gpt3-ai-content-generator'); ?>
                                        </label>
                                        <span class="aipkit_popover_option_helper">
                                            <?php esc_html_e('Required to fetch images from Pixabay.', 'gpt3-ai-content-generator'); ?>
                                        </span>
                                    </div>
                                    <a href="https://pixabay.com/api/docs/" target="_blank" rel="noopener noreferrer" class="aipkit_image_get_key_link">
                                        <?php esc_html_e('Get key', 'gpt3-ai-content-generator'); ?>
                                        <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                    </a>
                                </div>
                                <div class="aipkit_api-key-wrapper aipkit_popover_api_key_wrapper">
                                    <input
                                        type="password"
                                        id="aipkit_cw_pixabay_api_key"
                                        name="pixabay_api_key"
                                        class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--framed aipkit_cw_stock_api_key"
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
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_cw_pixabay_orientation"><?php esc_html_e('Orientation', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Horizontal or vertical results.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_cw_pixabay_orientation" name="pixabay_orientation" class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
                                    <option value="all"><?php esc_html_e('All', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="horizontal"><?php esc_html_e('Horizontal', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="vertical"><?php esc_html_e('Vertical', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_cw_pixabay_image_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Choose photo, illustration, or vector.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_cw_pixabay_image_type" name="pixabay_image_type" class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
                                    <option value="all"><?php esc_html_e('All', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="photo"><?php esc_html_e('Photo', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="illustration"><?php esc_html_e('Illustration', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="vector"><?php esc_html_e('Vector', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_cw_pixabay_category"><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Narrow results to a topic.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_cw_pixabay_category" name="pixabay_category" class="aipkit_autosave_trigger aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
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
        </div>
    </div>
</div>
<?php endif; ?>
