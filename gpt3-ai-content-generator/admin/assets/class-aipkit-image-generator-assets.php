<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/assets/class-aipkit-image-generator-assets.php
// Status: MODIFIED

namespace WPAICG\Admin\Assets;

use WPAICG\AIPKit_Providers;
use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Handles enqueueing assets for the AIPKit Image Generator module.
 */
class ImageGeneratorAssets
{
    private $version;
    private $is_admin_main_js_enqueued = false;
    private $is_public_main_js_enqueued = false;
    private $is_public_image_generator_css_enqueued = false;

    public function __construct()
    {
        $this->version = defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0';
    }

    public function register_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_image_generator_assets']);
    }

    public function enqueue_image_generator_assets($hook_suffix)
    {
        $screen = get_current_screen();
        $is_aipkit_page = $screen && strpos($screen->id, 'page_wpaicg') !== false;

        if (!$is_aipkit_page) {
            return;
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();

        // Ensure core dashboard data is localized if admin-main.js was enqueued
        if ($this->is_admin_main_js_enqueued && class_exists(DashboardAssets::class) && method_exists(DashboardAssets::class, 'localize_core_data')) {
            DashboardAssets::localize_core_data($this->version);
        }

        $this->localize_data();
    }

    private function enqueue_styles()
    {
        $dist_css_url = WPAICG_PLUGIN_URL . 'dist/css/';
        $public_img_gen_css_handle = 'aipkit-public-image-generator-css';
        if (!wp_style_is($public_img_gen_css_handle, 'registered')) {
            wp_register_style(
                $public_img_gen_css_handle,
                $dist_css_url . 'public-image-generator.bundle.css',
                [],
                $this->version
            );
        }
        if (!$this->is_public_image_generator_css_enqueued && !wp_style_is($public_img_gen_css_handle, 'enqueued')) {
            wp_enqueue_style($public_img_gen_css_handle);
            $this->is_public_image_generator_css_enqueued = true;
        }
    }

    private function enqueue_scripts()
    {
        $dist_js_url = WPAICG_PLUGIN_URL . 'dist/js/';
        $admin_main_js_handle = 'aipkit-admin-main';
        $public_main_js_handle = 'aipkit-public-main';

        if (!wp_script_is($admin_main_js_handle, 'registered')) {
            wp_register_script(
                $admin_main_js_handle,
                $dist_js_url . 'admin-main.bundle.js',
                ['wp-i18n', 'aipkit_markdown-it'],
                $this->version,
                true
            );
        }
        if (!$this->is_admin_main_js_enqueued && !wp_script_is($admin_main_js_handle, 'enqueued')) {
            wp_enqueue_script($admin_main_js_handle);
            wp_set_script_translations($admin_main_js_handle, 'gpt3-ai-content-generator', WPAICG_PLUGIN_DIR . 'languages');
            $this->is_admin_main_js_enqueued = true;
        }

        if (!wp_script_is($public_main_js_handle, 'registered')) {
            wp_register_script(
                $public_main_js_handle,
                $dist_js_url . 'public-main.bundle.js',
                ['wp-i18n', 'aipkit_markdown-it'],
                $this->version,
                true
            );
        }
        if (!$this->is_public_main_js_enqueued && !wp_script_is($public_main_js_handle, 'enqueued')) {
            wp_enqueue_script($public_main_js_handle);
            wp_set_script_translations($public_main_js_handle, 'gpt3-ai-content-generator', WPAICG_PLUGIN_DIR . 'languages');
            $this->is_public_main_js_enqueued = true;
        }
    }

    private function localize_data()
    {
        $public_main_js_handle = 'aipkit-public-main';

        if (!wp_script_is($public_main_js_handle, 'enqueued')) {
            if (!wp_script_is($public_main_js_handle, 'registered')) {
                $dist_js_url = WPAICG_PLUGIN_URL . 'dist/js/';
                wp_register_script($public_main_js_handle, $dist_js_url . 'public-main.bundle.js', ['wp-i18n', 'aipkit_markdown-it'], $this->version, true);
            }
        }

        $script_data = wp_scripts()->get_data($public_main_js_handle, 'data');
        if (!empty($script_data) && strpos($script_data, 'aipkit_image_generator_config_public') !== false) {
            return;
        }

        $ui_text_settings = [];
        if (class_exists(AIPKit_Image_Settings_Ajax_Handler::class)) {
            $all_image_settings = AIPKit_Image_Settings_Ajax_Handler::get_settings();
            $ui_text_settings = $all_image_settings['ui_text'] ?? [];
        }
        $get_ui_text = static function (string $key, string $default) use ($ui_text_settings): string {
            if (!isset($ui_text_settings[$key])) {
                return $default;
            }
            $value = sanitize_text_field((string) $ui_text_settings[$key]);
            return $value !== '' ? $value : $default;
        };
        $generate_label = $get_ui_text('generate_label', __('Generate', 'gpt3-ai-content-generator'));
        $results_empty = $get_ui_text('results_empty', __('Generated images will appear here.', 'gpt3-ai-content-generator'));

        wp_localize_script($public_main_js_handle, 'aipkit_image_generator_config_public', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aipkit_image_generator_nonce'),
            'text' => [
                'generating' => __('Generating...', 'gpt3-ai-content-generator'),
                'editing' => __('Editing...', 'gpt3-ai-content-generator'),
                'error'      => __('Error generating image.', 'gpt3-ai-content-generator'),
                'generateButton' => $generate_label,
                'noPrompt' => __('Please enter a prompt.', 'gpt3-ai-content-generator'),
                'initialPlaceholder' => $results_empty,
                'viewFullImage' => __('Click to view full image', 'gpt3-ai-content-generator'),
                'viewFullVideo' => __('Click to view full video', 'gpt3-ai-content-generator'),
                'openrouterModelUnsupported' => __('Selected OpenRouter model does not support image generation.', 'gpt3-ai-content-generator'),
                'editUploadRequired' => __('Please upload an image to edit.', 'gpt3-ai-content-generator'),
                'editProviderUnsupported' => __('Image editing is currently supported only for Google, OpenAI and OpenRouter providers.', 'gpt3-ai-content-generator'),
                'editModelUnsupported' => __('Selected model does not support image editing.', 'gpt3-ai-content-generator'),
                'editInvalidFileType' => __('Invalid image type. Allowed types: JPG, PNG, WEBP, GIF.', 'gpt3-ai-content-generator'),
                'editFileTooLarge' => __('Source image is too large. Maximum allowed size is 10MB.', 'gpt3-ai-content-generator'),
                'editDropUsePicker' => __('Could not attach dropped file automatically. Click to choose file.', 'gpt3-ai-content-generator'),
                'editHistoryLoadFailed' => __('Could not load the selected image for editing.', 'gpt3-ai-content-generator'),
                'editHistoryUnavailable' => __('Image editing is not available in the current setup.', 'gpt3-ai-content-generator'),
                'editHistoryLoaded' => __('Source image loaded. Describe your edits and click Edit Image.', 'gpt3-ai-content-generator'),
                'noEditCapableModels' => __('(No edit-capable models available)', 'gpt3-ai-content-generator'),
                'noOpenRouterImageModels' => __('(No image-capable OpenRouter models found)', 'gpt3-ai-content-generator'),
                'noModelsAvailable' => __('(No models available)', 'gpt3-ai-content-generator'),
                'imageModelsGroup' => __('Image Models', 'gpt3-ai-content-generator'),
                'videoModelsGroup' => __('Video Models', 'gpt3-ai-content-generator'),
                'configurationMissing' => __('Error: Configuration missing.', 'gpt3-ai-content-generator'),
                'coreUiMissing' => __('Error: Core UI elements missing.', 'gpt3-ai-content-generator'),
                'missingRequiredSettings' => __('Error: Missing required image generation settings.', 'gpt3-ai-content-generator'),
                'noVideoDataFound' => __('Error: No video data found.', 'gpt3-ai-content-generator'),
                'noImageDataFound' => __('Error: No image data found.', 'gpt3-ai-content-generator'),
                'deleteConfigMissing' => __('Error: Cannot delete image. Configuration missing.', 'gpt3-ai-content-generator'),
                'deleteImageErrorPrefix' => __('Error deleting image:', 'gpt3-ai-content-generator'),
                'revisedPromptPrefix' => __('Revised:', 'gpt3-ai-content-generator'),
                'generatingVideo' => __('Generating Video...', 'gpt3-ai-content-generator'),
                'videoGenerationInProgress' => __('Video generation in progress...', 'gpt3-ai-content-generator'),
                'generatingVideoProgress' => __('Generating video...', 'gpt3-ai-content-generator'),
                'videoGenerationTimedOut' => __('Video generation timed out. Please try again.', 'gpt3-ai-content-generator'),
                'videoGenerationFailed' => __('Video generation failed:', 'gpt3-ai-content-generator'),
            ],
             'edit_upload_max_bytes' => 10 * 1024 * 1024,
             'edit_upload_allowed_mime_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
             'openai_models' => [
                ['id' => 'gpt-image-1.5', 'name' => 'GPT Image 1.5'],
                ['id' => 'gpt-image-1', 'name' => 'GPT Image 1'],
                ['id' => 'gpt-image-1-mini', 'name' => 'GPT Image 1 mini'],
                ['id' => 'dall-e-3', 'name' => 'DALL-E 3'],
                ['id' => 'dall-e-2', 'name' => 'DALL-E 2'],
             ],
             'azure_models' => class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_azure_image_models() : [], 'google_models' => [
                'image' => (class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_google_image_models() : []),
                'video' => (class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_google_video_models() : []),
             ],
             'openrouter_image_models' => class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_openrouter_image_models() : [],
             'replicate_models' => AIPKit_Providers::get_replicate_models()
        ]);
    }
}
