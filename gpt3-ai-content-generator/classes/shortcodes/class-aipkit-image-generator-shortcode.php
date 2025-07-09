<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/shortcodes/class-aipkit-image-generator-shortcode.php
// Status: MODIFIED

namespace WPAICG\Shortcodes;

use WPAICG\aipkit_dashboard; // To check module status
use WPAICG\AIPKit_Role_Manager; // To check permissions
use WPAICG\AIPKit_Providers; // To get default provider if needed
use WPAICG\Images\AIPKit_Image_Settings_Ajax_Handler; // Use settings handler
use WP_Query; // For image history

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Image_Generator_Shortcode
 *
 * Handles the rendering of the [aipkit_image_generator] shortcode.
 * REVISED: Adjusted permission check to allow rendering for guests.
 *          AJAX handler will enforce usage limits/restrictions.
 * UPDATED: Added history attribute and rendering logic.
 */
class AIPKit_Image_Generator_Shortcode
{
    /**
     * Render the shortcode output.
     *
     * @param array $atts Shortcode attributes.
     *        Supported attributes:
     *        - show_provider (bool|string 'true'/'false', default true)
     *        - show_model (bool|string 'true'/'false', default true)
     *        - provider (string 'openai', 'azure' etc. - presets the provider)
     *        - model (string 'dall-e-3', etc. - presets the model)
     *        - size (string '1024x1024' etc. - presets the size)
     *        - number (int 1-4 - presets the number)
     *        - theme (string 'light', 'dark', 'custom', default 'dark')
     *        - history (bool|string 'true'/'false', default 'false')
     *
     * @return string HTML output.
     */
    public function render_shortcode($atts = [])
    {
        // 1. Check if the main module is active
        $module_settings = aipkit_dashboard::get_module_settings();
        if (empty($module_settings['image_generator'])) {
            if (current_user_can('manage_options')) {
                return '<p style="color:orange;"><em>[' . esc_html__('AIPKit Image Generator Shortcode: Module is disabled in settings.', 'gpt3-ai-content-generator') . ']</em></p>';
            }
            return '';
        }

        // 2. Permission Check (REVISED)
        if (is_user_logged_in() && !AIPKit_Role_Manager::user_can_access_module('image_generator')) {
            if (current_user_can('manage_options')) {
                return '<p style="color:orange;"><em>[' . esc_html__('AIPKit Image Generator Shortcode: Current user role lacks permission.', 'gpt3-ai-content-generator') . ']</em></p>';
            }
            return '';
        }

        // --- 2.5 Get Default Image Settings ---
        $image_gen_settings = AIPKit_Image_Settings_Ajax_Handler::get_settings();
        $frontend_display_settings = $image_gen_settings['frontend_display'] ?? [];
        $allowed_providers_str = $frontend_display_settings['allowed_providers'] ?? '';
        $allowed_models_str = $frontend_display_settings['allowed_models'] ?? '';
        // --- End Get Settings ---

        // 3. Parse Attributes
        $default_atts = [
            'show_provider' => 'true',
            'show_model'    => 'true',
            'provider'      => 'openai',
            'model'         => 'dall-e-3',
            'size'          => '1024x1024',
            'number'        => 1,
            'theme'         => 'dark',
            'history'       => 'false',
        ];
        $atts = shortcode_atts($default_atts, $atts, 'aipkit_image_generator');

        $show_provider = filter_var($atts['show_provider'], FILTER_VALIDATE_BOOLEAN);
        $show_model    = filter_var($atts['show_model'], FILTER_VALIDATE_BOOLEAN);
        $show_history  = filter_var($atts['history'], FILTER_VALIDATE_BOOLEAN); // NEW

        $preset_provider_from_att = !empty($atts['provider']) ? strtolower(sanitize_text_field($atts['provider'])) : null;
        $preset_model    = !empty($atts['model']) ? sanitize_text_field($atts['model']) : null;
        $preset_size     = !empty($atts['size']) ? sanitize_text_field($atts['size']) : null;
        $preset_number   = !empty($atts['number']) ? absint($atts['number']) : null;
        $valid_themes = ['light', 'dark', 'custom'];
        $theme = isset($atts['theme']) && in_array(strtolower($atts['theme']), $valid_themes, true)
                 ? strtolower($atts['theme'])
                 : 'dark';

        // --- 4. Determine Final Values ---
        $final_provider_key = $preset_provider_from_att ?? 'openai';
        $final_provider_normalized = match($final_provider_key) {
            'openai' => 'OpenAI', 'azure' => 'Azure', 'google' => 'Google',
            'replicate' => 'Replicate',
            default => 'OpenAI',
        };
        $final_model = $preset_model;
        $final_size = $preset_size;
        $final_number = $preset_number;
        // --- End Determine Final Values ---

        // 5. Signal assets needed
        add_filter('aipkit_enqueue_public_image_generator_assets', '__return_true');

        // 6. Prepare data for the view
        $view_data = [
            'nonce' => wp_create_nonce('aipkit_image_generator_nonce'),
            'show_provider' => $show_provider,
            'show_model'    => $show_model,
            'preset_provider' => $preset_provider_from_att ? $final_provider_normalized : null,
            'preset_model'    => $preset_model,
            'preset_size'     => $preset_size,
            'preset_number'   => $preset_number,
            'final_provider' => $final_provider_normalized,
            'final_model'    => $final_model,
            'final_size'     => $final_size,
            'final_number'   => $final_number,
            'theme'          => $theme,
            'show_history'   => $show_history, // NEW: Pass to view
            'image_history_html' => ($show_history && is_user_logged_in()) ? $this->render_image_history() : '', // NEW: Render history HTML
            'allowed_providers' => $allowed_providers_str,
            'allowed_models' => $allowed_models_str,
        ];

        // 7. Include the partial view
        ob_start();
        extract($view_data);
        $view_path = WPAICG_PLUGIN_DIR . 'public/views/shortcodes/image-generator.php';
        if (file_exists($view_path)) {
            include $view_path;
        } else {
            echo '<p style="color:red;">Image Generator UI cannot be loaded.</p>';
            error_log("AIPKit Image Generator Shortcode Error: View file not found at {$view_path}");
        }
        return ob_get_clean();
    }

    /**
     * Renders the HTML for the user's image generation history.
     *
     * @return string HTML for the image history section.
     */
    private function render_image_history(): string
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user_id = get_current_user_id();
        $args = [
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'author'         => $user_id,
            'posts_per_page' => 20,
            'meta_query'     => [
                [
                    'key'     => '_aipkit_generated_image',
                    'value'   => '1',
                    'compare' => '=',
                ],
            ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];
        $query = new WP_Query($args);

        ob_start();
        if (!$query->have_posts()) {
            echo '<p class="aipkit-image-history-empty">' . esc_html__('You have not generated any images yet.', 'gpt3-ai-content-generator') . '</p>';
        } else {
            ?>
            <div class="aipkit-image-history-grid">
                <?php while ($query->have_posts()) : $query->the_post();
                    $attachment_id = get_the_ID();
                    $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'thumbnail'); // Or other size
                    $full_url = wp_get_attachment_url($attachment_id);
                    $prompt = get_post_meta($attachment_id, '_aipkit_image_prompt', true);
                    $provider = get_post_meta($attachment_id, '_aipkit_image_provider', true);
                    $model = get_post_meta($attachment_id, '_aipkit_image_model', true);
                    $size = get_post_meta($attachment_id, '_aipkit_image_size', true);
                    ?>
                    <div class="aipkit-image-history-item">
                        <a href="<?php echo esc_url($full_url); ?>" target="_blank" rel="noopener noreferrer">
                            <img src="<?php echo esc_url($thumbnail_url); ?>" alt="<?php echo esc_attr($prompt ?: 'AI Generated Image'); ?>">
                        </a>
                        <button type="button" class="aipkit-image-history-delete-btn" data-attachment-id="<?php echo esc_attr($attachment_id); ?>" title="<?php esc_attr_e('Delete Image', 'gpt3-ai-content-generator'); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                        <div class="aipkit-image-history-info">
                            <?php if ($prompt): ?>
                                <p class="aipkit-image-history-prompt" title="<?php echo esc_attr($prompt); ?>">
                                    <strong><?php esc_html_e('Prompt:', 'gpt3-ai-content-generator'); ?></strong> <?php echo esc_html(wp_trim_words($prompt, 10, '...')); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($model): ?>
                                 <p class="aipkit-image-history-meta">
                                    <?php echo esc_html($provider . ' / ' . $model . ' / ' . $size); ?>
                                 </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php if ($query->max_num_pages > 1): ?>
                <div class="aipkit-image-history-load-more-container">
                    <button type="button" class="aipkit_image_generator_btn aipkit_image_generator_btn_secondary aipkit_image_generator_btn_icon aipkit-image-history-load-more-btn" title="<?php esc_attr_e('Load More', 'gpt3-ai-content-generator'); ?>" data-current-page="1" data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>">
                        <span class="aipkit_btn-icon-content dashicons dashicons-update-alt"></span>
                        <span class="aipkit_spinner" style="display: none;"></span>
                    </button>
                </div>
            <?php endif; ?>
            <?php
        }
        wp_reset_postdata();
        return ob_get_clean();
    }
}
