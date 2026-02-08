<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/shortcodes/class-aipkit-shortcodes-manager.php
// Status: MODIFIED

namespace WPAICG\Shortcodes;

use WPAICG\Shortcodes\AIPKit_Token_Usage_Shortcode;
use WPAICG\Shortcodes\AIPKit_Image_Generator_Shortcode;
use WPAICG\Shortcodes\AIPKit_Semantic_Search_Shortcode;
use WPAICG\aipkit_dashboard;
use WPAICG\AIPKit_Providers;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Shortcodes_Manager
 * Registers shortcodes and handles their asset enqueueing using bundled files.
 */
class AIPKit_Shortcodes_Manager
{
    private $version;
    private $token_usage_shortcode = null;
    private $image_generator_shortcode = null;
    private $semantic_search_shortcode = null; // NEW
    private $is_token_management_active = true;
    private $is_image_generator_active = false;
    private $is_semantic_search_active = true; // NEW
    private $is_public_main_js_enqueued_by_shortcodes = false; // Track if main JS is enqueued by this manager
    private $is_token_usage_css_enqueued = false;
    private $is_image_generator_css_enqueued = false;
    private $is_semantic_search_css_enqueued = false; // NEW
    private $is_ai_forms_css_enqueued = false; // Keep track of AI Forms CSS

    public function __construct($version)
    {
        $this->version = $version;
        if (!class_exists('\\WPAICG\\aipkit_dashboard')) {
            $dashboard_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_dashboard.php';
            if (file_exists($dashboard_path)) {
                require_once $dashboard_path;
            }
        }
        if (class_exists('\\WPAICG\\aipkit_dashboard')) {
            $module_settings = aipkit_dashboard::get_module_settings();
            $this->is_image_generator_active = !empty($module_settings['image_generator']);
        }
    }

    public function init_hooks()
    {
        $this->load_dependencies();
        if ($this->is_token_management_active && $this->token_usage_shortcode) {
            add_shortcode('aipkit_token_usage', [$this->token_usage_shortcode, 'render_shortcode']);
            if (method_exists($this->token_usage_shortcode, 'init_hooks')) {
                $this->token_usage_shortcode->init_hooks();
            }
        }
        if ($this->is_image_generator_active && $this->image_generator_shortcode) {
            add_shortcode('aipkit_image_generator', [$this->image_generator_shortcode, 'render_shortcode']);
        }
        if ($this->is_semantic_search_active && $this->semantic_search_shortcode) {
            add_shortcode('aipkit_semantic_search', [$this->semantic_search_shortcode, 'render_shortcode']);
        }
        add_action('wp_enqueue_scripts', [$this, 'register_and_enqueue_assets']);
    }

    private function load_dependencies()
    {
        if ($this->is_token_management_active) {
            $token_usage_path = WPAICG_PLUGIN_DIR . 'classes/shortcodes/class-aipkit-token-usage-shortcode.php';
            if (file_exists($token_usage_path)) {
                require_once $token_usage_path;
                if (class_exists('\\WPAICG\\Shortcodes\\AIPKit_Token_Usage_Shortcode')) {
                    $this->token_usage_shortcode = new AIPKit_Token_Usage_Shortcode();
                }
            }
        }
        if ($this->is_image_generator_active) {
            $image_gen_path = WPAICG_PLUGIN_DIR . 'classes/shortcodes/class-aipkit-image-generator-shortcode.php';
            if (file_exists($image_gen_path)) {
                require_once $image_gen_path;
                if (class_exists('\\WPAICG\\Shortcodes\\AIPKit_Image_Generator_Shortcode')) {
                    $this->image_generator_shortcode = new AIPKit_Image_Generator_Shortcode();
                }
            }
        }
        if ($this->is_semantic_search_active) {
            $semantic_search_path = WPAICG_PLUGIN_DIR . 'classes/shortcodes/class-aipkit-semantic-search-shortcode.php';
            if (file_exists($semantic_search_path)) {
                require_once $semantic_search_path;
                if (class_exists('\\WPAICG\\Shortcodes\\AIPKit_Semantic_Search_Shortcode')) {
                    $this->semantic_search_shortcode = new AIPKit_Semantic_Search_Shortcode();
                }
            }
        }
    }

    public function register_and_enqueue_assets()
    {
        if (is_admin()) {
            return;
        }

        global $post;
        $content = is_a($post, 'WP_Post') ? $post->post_content : '';
        $dist_css_url = WPAICG_PLUGIN_URL . 'dist/css/';
        $dist_js_url = WPAICG_PLUGIN_URL . 'dist/js/';
        $public_main_js_handle = 'aipkit-public-main'; // Central handle for public JS

        $ai_forms_present = has_shortcode($content, 'aipkit_ai_form');
        $force_load_ai_forms = apply_filters('aipkit_enqueue_public_ai_forms_assets', false);
        if ($ai_forms_present || $force_load_ai_forms) {
            $public_ai_forms_css_handle = 'aipkit-public-ai-forms';
            if (!wp_style_is($public_ai_forms_css_handle, 'registered')) {
                wp_register_style(
                    $public_ai_forms_css_handle,
                    $dist_css_url . 'public-ai-forms.bundle.css',
                    [],
                    $this->version
                );
            }
            if (!$this->is_ai_forms_css_enqueued && !wp_style_is($public_ai_forms_css_handle, 'enqueued')) {
                wp_enqueue_style($public_ai_forms_css_handle);
                $this->is_ai_forms_css_enqueued = true;
            }
        }

        if ($this->is_token_management_active && has_shortcode($content, 'aipkit_token_usage')) {
            $token_usage_css_handle = 'aipkit-public-token-usage';
            if (!wp_style_is($token_usage_css_handle, 'registered')) {
                wp_register_style($token_usage_css_handle, $dist_css_url . 'public-token-usage.bundle.css', [], $this->version);
            }
            if (!$this->is_token_usage_css_enqueued && !wp_style_is($token_usage_css_handle, 'enqueued')) {
                wp_enqueue_style($token_usage_css_handle);
                $this->is_token_usage_css_enqueued = true;
            }
        }

        $image_generator_present = $this->is_image_generator_active && has_shortcode($content, 'aipkit_image_generator');
        $force_load_image_gen = apply_filters('aipkit_enqueue_public_image_generator_assets', false);

        if ($image_generator_present || $force_load_image_gen) {
            $public_img_gen_css_handle = 'aipkit-public-image-generator-css';
            if (!wp_style_is($public_img_gen_css_handle, 'registered')) {
                wp_register_style($public_img_gen_css_handle, $dist_css_url . 'public-image-generator.bundle.css', [], $this->version);
            }
            if (!$this->is_image_generator_css_enqueued && !wp_style_is($public_img_gen_css_handle, 'enqueued')) {
                wp_enqueue_style($public_img_gen_css_handle);
                $this->is_image_generator_css_enqueued = true;
            }
        }

        if ($this->is_semantic_search_active && has_shortcode($content, 'aipkit_semantic_search')) {
            $semantic_search_css_handle = 'aipkit-public-semantic-search';
            if (!wp_style_is($semantic_search_css_handle, 'registered')) {
                wp_register_style($semantic_search_css_handle, $dist_css_url . 'public-semantic-search.bundle.css', [], $this->version);
            }
            if (!$this->is_semantic_search_css_enqueued && !wp_style_is($semantic_search_css_handle, 'enqueued')) {
                wp_enqueue_style($semantic_search_css_handle);
                $this->is_semantic_search_css_enqueued = true;
            }
        }

        // Enqueue main public script if any of our shortcodes are present
        if ($ai_forms_present || ($this->is_token_management_active && has_shortcode($content, 'aipkit_token_usage')) || $image_generator_present || ($this->is_semantic_search_active && has_shortcode($content, 'aipkit_semantic_search')) || $force_load_ai_forms || $force_load_image_gen) {
            if (!wp_script_is($public_main_js_handle, 'registered')) {
                wp_register_script($public_main_js_handle, $dist_js_url . 'public-main.bundle.js', ['wp-i18n', 'aipkit_markdown-it'], $this->version, true);
            }
            if (!$this->is_public_main_js_enqueued_by_shortcodes && !wp_script_is($public_main_js_handle, 'enqueued')) {
                wp_enqueue_script($public_main_js_handle);
                wp_set_script_translations($public_main_js_handle, 'gpt3-ai-content-generator', WPAICG_PLUGIN_DIR . 'languages');
                $this->is_public_main_js_enqueued_by_shortcodes = true;
            }
        }

        // --- START FIX: Localize data for Image Generator shortcode ---
        if (($image_generator_present || $force_load_image_gen) && wp_script_is($public_main_js_handle, 'enqueued')) {
            static $image_gen_localized = false;
            if (!$image_gen_localized) {
                if (!class_exists('\\WPAICG\\AIPKit_Providers')) {
                    $providers_path = WPAICG_PLUGIN_DIR . 'classes/dashboard/class-aipkit_providers.php';
                    if (file_exists($providers_path)) {
                        require_once $providers_path;
                    }
                }

                // Get attributes from the shortcode class
                $image_gen_atts = class_exists('\\WPAICG\\Shortcodes\\AIPKit_Image_Generator_Shortcode')
                                  ? AIPKit_Image_Generator_Shortcode::get_current_attributes()
                                  : [];
                $allowed_models_str = $image_gen_atts['allowed_models'] ?? null;

                if (!class_exists('\\WPAICG\\Images\\AIPKit_Image_Settings_Ajax_Handler')) {
                    $settings_handler_path = WPAICG_PLUGIN_DIR . 'classes/images/class-aipkit-image-settings-ajax-handler.php';
                    if (file_exists($settings_handler_path)) {
                        require_once $settings_handler_path;
                    }
                }

                $ui_text_settings = [];
                if (class_exists('\\WPAICG\\Images\\AIPKit_Image_Settings_Ajax_Handler')) {
                    $all_image_settings = \WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler::get_settings();
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
                    'allowed_models' => $allowed_models_str,
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
                    'azure_models' => class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_azure_image_models() : [],
                    'google_models' => [
                        'image' => (class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_google_image_models() : []),
                        'video' => (class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_google_video_models() : []),
                    ],
                    'openrouter_image_models' => class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_openrouter_image_models() : [],
                    'replicate_models' => class_exists('\\WPAICG\\AIPKit_Providers') ? AIPKit_Providers::get_replicate_models() : []
                ]);
                $image_gen_localized = true;
            }
        }
        // --- END FIX ---

        // Localize data for each shortcode if present and script is enqueued
        if ($this->is_token_management_active && has_shortcode($content, 'aipkit_token_usage') && wp_script_is($public_main_js_handle, 'enqueued')) {
            static $token_usage_localized = false;
            if (!$token_usage_localized) {
                wp_localize_script($public_main_js_handle, 'aipkit_token_usage_config', [
                    'ajaxUrl' => admin_url('admin-ajax.php'), 'nonce'   => wp_create_nonce('aipkit_token_usage_details_nonce'),
                    /* translators: %s is the name of the token, e.g. "OpenAI" */
                    'text' => ['loadingDetails' => __('Loading details...', 'gpt3-ai-content-generator'), 'errorLoading' => __('Error loading details.', 'gpt3-ai-content-generator'), 'close' => __('Close', 'gpt3-ai-content-generator'), 'usageDetailsTitle' => __('Usage Details for %s', 'gpt3-ai-content-generator'), 'pageLabel' => __('Page', 'gpt3-ai-content-generator'), 'ofLabel' => __('of', 'gpt3-ai-content-generator'), 'previous' => __('Previous', 'gpt3-ai-content-generator'), 'next' => __('Next', 'gpt3-ai-content-generator'),]
                ]);
                $token_usage_localized = true;
            }
        }
        if ($this->is_semantic_search_active && has_shortcode($content, 'aipkit_semantic_search') && wp_script_is($public_main_js_handle, 'enqueued')) {
            static $semantic_search_localized = false;
            if (!$semantic_search_localized) {
                $opts = get_option('aipkit_options', []);
                $semantic_search_settings = $opts['semantic_search'] ?? [];
                wp_localize_script($public_main_js_handle, 'aipkit_semantic_search_config', [
                    'ajaxUrl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('aipkit_semantic_search_nonce'),
                    'settings' => $semantic_search_settings,
                    'text' => ['searching' => __('Searching...', 'gpt3-ai-content-generator'), 'error' => __('An error occurred while searching.', 'gpt3-ai-content-generator'),]
                ]);
                $semantic_search_localized = true;
            }
        }
    }
}
