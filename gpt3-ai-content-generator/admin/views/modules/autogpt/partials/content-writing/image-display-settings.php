<?php
if (!defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

$aipkit_task_cw_image_display_settings_render_mode =
    isset($aipkit_task_cw_image_display_settings_render_mode)
        ? (string) $aipkit_task_cw_image_display_settings_render_mode
        : 'both';
?>

<?php if ($aipkit_task_cw_image_display_settings_render_mode !== 'popover') : ?>
<button
    type="button"
    class="aipkit_cw_settings_icon_trigger"
    id="aipkit_task_cw_image_display_settings_trigger"
    data-aipkit-popover-target="aipkit_task_cw_image_display_settings_popover"
    data-aipkit-popover-placement="top"
    aria-controls="aipkit_task_cw_image_display_settings_popover"
    aria-expanded="false"
    aria-label="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>"
    title="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>"
    hidden
>
    <span class="dashicons dashicons-admin-settings" aria-hidden="true"></span>
</button>
<?php endif; ?>

<?php if ($aipkit_task_cw_image_display_settings_render_mode !== 'trigger') : ?>
<div class="aipkit_model_settings_popover aipkit_cw_settings_popover" id="aipkit_task_cw_image_display_settings_popover" aria-hidden="true">
    <div class="aipkit_model_settings_popover_panel aipkit_cw_settings_popover_panel aipkit_cw_image_settings_popover_panel" role="dialog" aria-label="<?php esc_attr_e('Image settings', 'gpt3-ai-content-generator'); ?>">
        <div class="aipkit_model_settings_popover_header aipkit_cw_settings_sheet_header">
            <span class="aipkit_model_settings_popover_title"><?php esc_html_e('Image settings', 'gpt3-ai-content-generator'); ?></span>
        </div>
        <div class="aipkit_model_settings_popover_body aipkit_cw_settings_popover_body aipkit_cw_settings_sheet_body">
            <div class="aipkit_popover_options_list">
                <div class="aipkit_popover_option_row" id="aipkit_task_cw_image_display_count_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_task_cw_image_count">
                                <?php esc_html_e('Count', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('How many to insert.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <input
                            type="number"
                            id="aipkit_task_cw_image_count"
                            name="image_count"
                            class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact"
                            value="1"
                            min="1"
                            max="10"
                        />
                    </div>
                </div>

                <div class="aipkit_popover_option_row" id="aipkit_task_cw_image_display_placement_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_task_cw_image_placement">
                                <?php esc_html_e('Placement', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('Where images appear.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <select
                            id="aipkit_task_cw_image_placement"
                            name="image_placement"
                            class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select aipkit_task_cw_image_placement_select"
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

                <div class="aipkit_popover_option_row" id="aipkit_task_cw_image_display_param_x_field" hidden>
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_task_cw_image_placement_param_x">
                                <?php esc_html_e('X', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('Used with every-X placements.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <input
                            type="number"
                            id="aipkit_task_cw_image_placement_param_x"
                            name="image_placement_param_x"
                            class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact"
                            value="2"
                            min="1"
                        />
                    </div>
                </div>

                <div class="aipkit_popover_option_row" id="aipkit_task_cw_image_display_size_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_task_cw_image_size">
                                <?php esc_html_e('Display size', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('WordPress image size in the post.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <select
                            id="aipkit_task_cw_image_size"
                            name="image_size"
                            class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                        >
                            <option value="large" selected><?php esc_html_e('Large', 'gpt3-ai-content-generator'); ?></option>
                            <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                            <option value="thumbnail"><?php esc_html_e('Thumbnail', 'gpt3-ai-content-generator'); ?></option>
                            <option value="full"><?php esc_html_e('Full', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="aipkit_popover_option_row" id="aipkit_task_cw_image_display_alignment_field">
                    <div class="aipkit_popover_option_main">
                        <div class="aipkit_cw_settings_option_text">
                            <label class="aipkit_popover_option_label" for="aipkit_task_cw_image_alignment">
                                <?php esc_html_e('Align', 'gpt3-ai-content-generator'); ?>
                            </label>
                            <span class="aipkit_popover_option_helper">
                                <?php esc_html_e('Image alignment.', 'gpt3-ai-content-generator'); ?>
                            </span>
                        </div>
                        <select
                            id="aipkit_task_cw_image_alignment"
                            name="image_alignment"
                            class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select"
                        >
                            <option value="none" selected><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
                            <option value="left"><?php esc_html_e('Left', 'gpt3-ai-content-generator'); ?></option>
                            <option value="center"><?php esc_html_e('Center', 'gpt3-ai-content-generator'); ?></option>
                            <option value="right"><?php esc_html_e('Right', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                </div>

                <div id="aipkit_task_cw_image_provider_options_block" hidden>
                    <div id="aipkit_task_cw_openai_options" data-aipkit-image-provider-options="openai" hidden>
                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openai_canvas_size"><?php esc_html_e('Canvas size', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Generated image dimensions.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openai_canvas_size" name="openai_canvas_size" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="canvas_size">
                                    <option value="1024x1024" selected><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1536x1024"><?php esc_html_e('Landscape', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1024x1536"><?php esc_html_e('Portrait', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="auto"><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openai_quality"><?php esc_html_e('Quality', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Controls generation cost and detail.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openai_quality" name="openai_quality" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="quality">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="auto"><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="high"><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openai_output_format"><?php esc_html_e('Output format', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Saved file format for generated images.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openai_output_format" name="openai_output_format" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="output_format">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="png">PNG</option>
                                    <option value="jpeg">JPEG</option>
                                    <option value="webp">WebP</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-openai-compression-row hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openai_output_compression"><?php esc_html_e('Compression', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Only used for JPEG or WebP output.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openai_output_compression" name="openai_output_compression" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="output_compression">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="25">25%</option>
                                    <option value="50">50%</option>
                                    <option value="75">75%</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openai_background"><?php esc_html_e('Background', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Sets image background.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openai_background" name="openai_background" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="background">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="auto"><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="opaque"><?php esc_html_e('Opaque', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="transparent" data-aipkit-openai-transparent-background-option hidden disabled><?php esc_html_e('Transparent', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openai_moderation"><?php esc_html_e('Moderation', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Prompt and image filtering strictness.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openai_moderation" name="openai_moderation" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="moderation">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="auto"><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="aipkit_task_cw_azure_options" data-aipkit-image-provider-options="azure" hidden>
                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_azure_canvas_size"><?php esc_html_e('Canvas size', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Generated image dimensions.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_azure_canvas_size" name="azure_canvas_size" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="canvas_size">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1024x1024"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1536x1024"><?php esc_html_e('Landscape', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1024x1536"><?php esc_html_e('Portrait', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="auto"><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_azure_quality"><?php esc_html_e('Quality', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Controls generation cost and detail.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_azure_quality" name="azure_quality" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="quality">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="medium"><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="high"><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_azure_output_format"><?php esc_html_e('Output format', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Saved file format for generated images.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_azure_output_format" name="azure_output_format" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="output_format">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="png">PNG</option>
                                    <option value="jpeg">JPEG</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-azure-compression-row hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_azure_output_compression"><?php esc_html_e('Compression', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Only used for JPEG output.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_azure_output_compression" name="azure_output_compression" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="output_compression">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="25">25%</option>
                                    <option value="50">50%</option>
                                    <option value="75">75%</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_azure_background"><?php esc_html_e('Background', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Transparent output is saved as PNG.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_azure_background" name="azure_background" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="background">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="auto"><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="transparent"><?php esc_html_e('Transparent', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="aipkit_task_cw_google_options" data-aipkit-image-provider-options="google" hidden>
                        <div class="aipkit_popover_option_row" data-aipkit-google-aspect-ratio-row hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_google_aspect_ratio"><?php esc_html_e('Aspect ratio', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Generated image shape.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_google_aspect_ratio" name="google_aspect_ratio" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="aspect_ratio">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1:1"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="3:4"><?php esc_html_e('Portrait 3:4', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="4:3"><?php esc_html_e('Landscape 4:3', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="9:16"><?php esc_html_e('Vertical 9:16', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="16:9"><?php esc_html_e('Wide 16:9', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="2:3">2:3</option>
                                    <option value="3:2">3:2</option>
                                    <option value="4:5">4:5</option>
                                    <option value="5:4">5:4</option>
                                    <option value="21:9">21:9</option>
                                    <option value="1:4">1:4</option>
                                    <option value="4:1">4:1</option>
                                    <option value="1:8">1:8</option>
                                    <option value="8:1">8:1</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-google-image-size-row hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_google_image_size"><?php esc_html_e('Image size', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Provider output resolution.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_google_image_size" name="google_image_size" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="image_size">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="512">512</option>
                                    <option value="1k">1K</option>
                                    <option value="2k">2K</option>
                                    <option value="4k">4K</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-google-person-generation-row hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_google_person_generation"><?php esc_html_e('People', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Imagen person generation policy.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_google_person_generation" name="google_person_generation" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="person_generation">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="dont_allow"><?php esc_html_e('Do not allow', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="allow_adult"><?php esc_html_e('Adults only', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="allow_all"><?php esc_html_e('Adults and children', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="aipkit_task_cw_openrouter_options" data-aipkit-image-provider-options="openrouter" hidden>
                        <div class="aipkit_popover_option_row" data-aipkit-openrouter-aspect-ratio-row hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openrouter_aspect_ratio"><?php esc_html_e('Aspect ratio', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Model-dependent image_config shape.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openrouter_aspect_ratio" name="openrouter_aspect_ratio" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="aspect_ratio">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1:1"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="2:3">2:3</option>
                                    <option value="3:2">3:2</option>
                                    <option value="3:4"><?php esc_html_e('Portrait 3:4', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="4:3"><?php esc_html_e('Landscape 4:3', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="4:5">4:5</option>
                                    <option value="5:4">5:4</option>
                                    <option value="9:16"><?php esc_html_e('Vertical 9:16', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="16:9"><?php esc_html_e('Wide 16:9', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="21:9">21:9</option>
                                    <option value="1:4">1:4</option>
                                    <option value="4:1">4:1</option>
                                    <option value="1:8">1:8</option>
                                    <option value="8:1">8:1</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-openrouter-image-size-row hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_openrouter_image_size"><?php esc_html_e('Image size', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Provider output resolution.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_openrouter_image_size" name="openrouter_image_size" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="image_size">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1k">1K</option>
                                    <option value="2k">2K</option>
                                    <option value="4k">4K</option>
                                    <option value="0.5k">0.5K</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="aipkit_task_cw_xai_options" data-aipkit-image-provider-options="xai" hidden>
                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_xai_aspect_ratio"><?php esc_html_e('Aspect ratio', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Generated image shape.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_xai_aspect_ratio" name="xai_aspect_ratio" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="aspect_ratio">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="auto"><?php esc_html_e('Auto', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1:1"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="16:9"><?php esc_html_e('Wide 16:9', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="9:16"><?php esc_html_e('Vertical 9:16', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="4:3"><?php esc_html_e('Landscape 4:3', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="3:4"><?php esc_html_e('Portrait 3:4', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="3:2">3:2</option>
                                    <option value="2:3">2:3</option>
                                    <option value="2:1">2:1</option>
                                    <option value="1:2">1:2</option>
                                    <option value="19.5:9">19.5:9</option>
                                    <option value="9:19.5">9:19.5</option>
                                    <option value="20:9">20:9</option>
                                    <option value="9:20">9:20</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_xai_resolution"><?php esc_html_e('Resolution', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Provider output resolution.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_xai_resolution" name="xai_resolution" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="resolution">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1k">1K</option>
                                    <option value="2k">2K</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="aipkit_task_cw_replicate_options" data-aipkit-image-provider-options="replicate" hidden>
                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="aspect_ratio" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_aspect_ratio"><?php esc_html_e('Aspect ratio', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Generated image shape.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_replicate_aspect_ratio" name="replicate_aspect_ratio" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="aspect_ratio">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1:1"><?php esc_html_e('Square', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="16:9"><?php esc_html_e('Wide 16:9', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="21:9">21:9</option>
                                    <option value="3:2">3:2</option>
                                    <option value="2:3">2:3</option>
                                    <option value="4:3"><?php esc_html_e('Landscape 4:3', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="3:4"><?php esc_html_e('Portrait 3:4', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="4:5">4:5</option>
                                    <option value="5:4">5:4</option>
                                    <option value="9:16"><?php esc_html_e('Vertical 9:16', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="1:2">1:2</option>
                                    <option value="2:1">2:1</option>
                                    <option value="3:1">3:1</option>
                                    <option value="1:3">1:3</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="width" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_width"><?php esc_html_e('Width', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Provider output width when supported.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <input type="number" id="aipkit_task_cw_replicate_width" name="replicate_width" min="64" max="4096" step="1" class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact" data-aipkit-image-provider-option="width" />
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="height" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_height"><?php esc_html_e('Height', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Provider output height when supported.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <input type="number" id="aipkit_task_cw_replicate_height" name="replicate_height" min="64" max="4096" step="1" class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact" data-aipkit-image-provider-option="height" />
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="negative_prompt" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_negative_prompt"><?php esc_html_e('Negative prompt', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('What the model should avoid.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <input type="text" id="aipkit_task_cw_replicate_negative_prompt" name="replicate_negative_prompt" maxlength="1000" class="aipkit_form-input aipkit_popover_option_input" data-aipkit-image-provider-option="negative_prompt" />
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="guidance" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_guidance"><?php esc_html_e('Guidance', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Prompt adherence strength.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <input type="number" id="aipkit_task_cw_replicate_guidance" name="replicate_guidance" min="0" max="30" step="0.1" class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact" data-aipkit-image-provider-option="guidance" />
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="num_inference_steps" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_num_inference_steps"><?php esc_html_e('Steps', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Inference steps when supported.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <input type="number" id="aipkit_task_cw_replicate_num_inference_steps" name="replicate_num_inference_steps" min="1" max="100" step="1" class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact" data-aipkit-image-provider-option="num_inference_steps" />
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="seed" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_seed"><?php esc_html_e('Seed', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Repeatable generation seed.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <input type="number" id="aipkit_task_cw_replicate_seed" name="replicate_seed" min="0" max="2147483647" step="1" class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact" data-aipkit-image-provider-option="seed" />
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="output_format" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_output_format"><?php esc_html_e('Output format', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Saved file format when supported.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_replicate_output_format" name="replicate_output_format" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select" data-aipkit-image-provider-option="output_format">
                                    <option value="" selected><?php esc_html_e('Default', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="webp">WebP</option>
                                    <option value="png">PNG</option>
                                    <option value="jpg">JPG</option>
                                    <option value="jpeg">JPEG</option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row" data-aipkit-replicate-option-row="output_quality" hidden>
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_replicate_output_quality"><?php esc_html_e('Quality', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Output compression quality.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <input type="number" id="aipkit_task_cw_replicate_output_quality" name="replicate_output_quality" min="0" max="100" step="1" class="aipkit_form-input aipkit_popover_option_input aipkit_popover_option_input--compact" data-aipkit-image-provider-option="output_quality" />
                            </div>
                        </div>
                    </div>

                    <div id="aipkit_task_cw_pexels_options" hidden>
                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_pexels_orientation"><?php esc_html_e('Orientation', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Landscape, portrait, or square results.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_pexels_orientation" name="pexels_orientation" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
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
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_pexels_size"><?php esc_html_e('Size', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Filter results by image size.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_pexels_size" name="pexels_size" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
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
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_pexels_color"><?php esc_html_e('Color', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Filter by dominant color.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_pexels_color" name="pexels_color" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
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

                    <div id="aipkit_task_cw_pixabay_options" hidden>
                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_pixabay_orientation"><?php esc_html_e('Orientation', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Choose landscape or portrait images.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_pixabay_orientation" name="pixabay_orientation" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
                                    <option value="all"><?php esc_html_e('All', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="horizontal"><?php esc_html_e('Horizontal', 'gpt3-ai-content-generator'); ?></option>
                                    <option value="vertical"><?php esc_html_e('Vertical', 'gpt3-ai-content-generator'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aipkit_popover_option_row">
                            <div class="aipkit_popover_option_main">
                                <div class="aipkit_cw_settings_option_text">
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_pixabay_image_type"><?php esc_html_e('Type', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Filter results by image type.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_pixabay_image_type" name="pixabay_image_type" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
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
                                    <label class="aipkit_popover_option_label" for="aipkit_task_cw_pixabay_category"><?php esc_html_e('Category', 'gpt3-ai-content-generator'); ?></label>
                                    <span class="aipkit_popover_option_helper"><?php esc_html_e('Limit results to a subject area.', 'gpt3-ai-content-generator'); ?></span>
                                </div>
                                <select id="aipkit_task_cw_pixabay_category" name="pixabay_category" class="aipkit_popover_option_select aipkit_popover_option_select--fit aipkit_cw_blended_chevron_select">
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
