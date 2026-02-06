<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/content-writer/class-aipkit-content-writer-image-handler.php

namespace WPAICG\ContentWriter;

use WPAICG\Images\AIPKit_Image_Manager;
use WPAICG\Core\AIPKit_AI_Caller;
use WPAICG\Core\AIPKit_OpenAI_Reasoning;
use WPAICG\AIPKit_Providers;
use WPAICG\AIPKIT_AI_Settings;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

class AIPKit_Content_Writer_Image_Handler
{
    private $image_manager;
    private $pexels_image_cache = []; // MODIFIED: Added instance property for Pexels caching
    private const DEFAULT_ALT_TEXT = 'Image';

    public function __construct()
    {
        if (!class_exists(AIPKit_Image_Manager::class)) {
            $manager_path = WPAICG_PLUGIN_DIR . 'classes/images/class-aipkit-image-manager.php';
            if (file_exists($manager_path)) {
                require_once $manager_path;
            } else {
                $this->image_manager = null;
                return;
            }
        }
        $this->image_manager = new AIPKit_Image_Manager();
    }

    private function normalize_provider_name(string $provider): string
    {
        return match (strtolower($provider)) {
            'openai' => 'OpenAI',
            'openrouter' => 'OpenRouter',
            'google' => 'Google',
            'azure' => 'Azure',
            'deepseek' => 'DeepSeek',
            'ollama' => 'Ollama',
            default => ucfirst(strtolower($provider)),
        };
    }

    private function trim_text(string $text, int $max_length): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }
        if (function_exists('mb_strlen') && mb_strlen($text) > $max_length) {
            $text = mb_substr($text, 0, $max_length);
        } elseif (strlen($text) > $max_length) {
            $text = substr($text, 0, $max_length);
        }
        return rtrim($text);
    }

    private function get_primary_keyword(string $keywords): string
    {
        $parts = preg_split('/[,\|\n;]+/', $keywords);
        if (!$parts) {
            return '';
        }
        foreach ($parts as $part) {
            $value = trim($part);
            if ($value !== '') {
                return $value;
            }
        }
        return '';
    }

    private function build_filename_base(string $topic, string $primary_keyword, bool $is_featured, int $index): string
    {
        $parts = [];
        if ($topic !== '') {
            $parts[] = $topic;
        }
        if ($primary_keyword !== '' && stripos($topic, $primary_keyword) === false) {
            $parts[] = $primary_keyword;
        }
        $parts[] = $is_featured ? 'featured' : 'image';
        if ($index > 1) {
            $parts[] = (string) $index;
        }
        return sanitize_title_with_dashes(implode(' ', $parts));
    }

    private function build_attachment_meta(string $topic, string $keywords, string $post_title, string $excerpt, int $index, bool $is_featured): array
    {
        $primary_keyword = $this->get_primary_keyword($keywords);
        $topic_label = $post_title !== '' ? $post_title : ($topic !== '' ? $topic : $primary_keyword);

        if ($topic_label === '') {
            $alt_text = self::DEFAULT_ALT_TEXT;
            $title_text = 'Image';
        } else {
            if ($primary_keyword !== '' && stripos($topic_label, $primary_keyword) === false) {
                $alt_text = sprintf('%s illustration for %s', $primary_keyword, $topic_label);
            } else {
                $alt_text = sprintf('Illustration of %s', $topic_label);
            }
            if ($index > 1) {
                $alt_text = sprintf('Additional %s', $alt_text);
            }
            $title_text = $topic_label;
        }

        $alt_text = $this->trim_text($alt_text, 125);
        $title_suffix = $is_featured ? 'Featured image' : 'Image';
        $title_text = $this->trim_text(sprintf('%s — %s', $title_text, $title_suffix), 80);

        $caption_text = $excerpt !== '' ? $this->trim_text($excerpt, 120) : '';
        $description_text = $excerpt !== ''
            ? $this->trim_text($excerpt, 240)
            : ($topic_label !== '' ? sprintf('Image for %s.', $topic_label) : 'Image.');

        return [
            'filename_base' => $this->build_filename_base($topic_label, $primary_keyword, $is_featured, $index),
            'title' => $title_text,
            'alt' => $alt_text,
            'caption' => $caption_text,
            'description' => $description_text,
        ];
    }

    private function build_image_metadata_prompt(string $template, array $placeholders, string $image_context): string
    {
        $prompt = str_replace(array_keys($placeholders), array_values($placeholders), $template);
        if ($image_context !== '') {
            if (strpos($prompt, '{image_context}') !== false) {
                $prompt = str_replace('{image_context}', $image_context, $prompt);
            } else {
                $prompt .= "\n\nImage context: " . $image_context;
            }
        } else {
            $prompt = str_replace('{image_context}', '', $prompt);
        }

        return trim($prompt);
    }

    private function maybe_generate_image_metadata(
        array $settings,
        array &$image_data,
        string $final_title,
        string $final_keywords,
        string $post_title,
        string $excerpt,
        string $meta_topic
    ): void {
        $mode = sanitize_key((string) ($settings['cw_generation_mode'] ?? ''));
        if ($mode !== '' && strpos($mode, 'existing') === 0) {
            return;
        }

        $has_inline = !empty($image_data['in_content_images']) && is_array($image_data['in_content_images']);
        $has_featured = !empty($image_data['featured_image_id']);
        if (!$has_inline && !$has_featured) {
            return;
        }

        $field_flags = [
            'title' => ($settings['generate_image_title'] ?? '0') === '1',
            'alt' => ($settings['generate_image_alt_text'] ?? '0') === '1',
            'caption' => ($settings['generate_image_caption'] ?? '0') === '1',
            'description' => ($settings['generate_image_description'] ?? '0') === '1',
        ];
        if (!in_array(true, $field_flags, true)) {
            return;
        }

        if (!class_exists(AIPKit_AI_Caller::class)) {
            $ai_caller_path = WPAICG_PLUGIN_DIR . 'classes/core/class-aipkit_ai_caller.php';
            if (file_exists($ai_caller_path)) {
                require_once $ai_caller_path;
            } else {
                return;
            }
        }

        $ai_caller = new AIPKit_AI_Caller();
        $global_config = AIPKit_Providers::get_default_provider_config();
        $global_ai_params = AIPKIT_AI_Settings::get_ai_parameters();

        $provider_raw = (string) ($settings['ai_provider'] ?? ($global_config['provider'] ?? 'OpenAI'));
        $provider = $this->normalize_provider_name($provider_raw);
        $model = (string) ($settings['ai_model'] ?? ($global_config['model'] ?? ''));
        if ($model === '') {
            return;
        }

        $provider_config = AIPKit_Providers::get_provider_data($provider);
        if ($provider !== 'Ollama' && empty($provider_config['api_key'])) {
            return;
        }

        $temperature = isset($settings['ai_temperature']) && $settings['ai_temperature'] !== ''
            ? floatval($settings['ai_temperature'])
            : floatval($global_ai_params['temperature'] ?? 0.7);

        $reasoning_effort = '';
        if ($provider === 'OpenAI') {
            $reasoning_effort = AIPKit_OpenAI_Reasoning::normalize_effort_for_model(
                (string) $model,
                $settings['reasoning_effort'] ?? ''
            );
        }

        $prompt_templates = [
            'title' => trim((string) ($settings['image_title_prompt'] ?? '')),
            'alt' => trim((string) ($settings['image_alt_text_prompt'] ?? '')),
            'caption' => trim((string) ($settings['image_caption_prompt'] ?? '')),
            'description' => trim((string) ($settings['image_description_prompt'] ?? '')),
        ];

        if ($prompt_templates['title'] === '') {
            $prompt_templates['title'] = AIPKit_Content_Writer_Prompts::get_default_image_title_prompt();
        }
        if ($prompt_templates['alt'] === '') {
            $prompt_templates['alt'] = AIPKit_Content_Writer_Prompts::get_default_image_alt_text_prompt();
        }
        if ($prompt_templates['caption'] === '') {
            $prompt_templates['caption'] = AIPKit_Content_Writer_Prompts::get_default_image_caption_prompt();
        }
        if ($prompt_templates['description'] === '') {
            $prompt_templates['description'] = AIPKit_Content_Writer_Prompts::get_default_image_description_prompt();
        }

        $base_placeholders = [
            '{topic}' => $meta_topic !== '' ? $meta_topic : $final_title,
            '{keywords}' => $final_keywords,
            '{post_title}' => $post_title,
            '{excerpt}' => $excerpt,
        ];

        $system_instruction = 'You are an expert assistant. Follow the prompt exactly and return only the requested text.';

        $context_cache = [];
        $process_attachment = function (int $attachment_id, array &$image_item) use (
            $ai_caller,
            $provider,
            $model,
            $temperature,
            $allow_reasoning,
            $reasoning_effort,
            $prompt_templates,
            $field_flags,
            $base_placeholders,
            $system_instruction,
            &$context_cache
        ): void {
            $file_name = '';
            $file_path = (string) get_attached_file($attachment_id);
            if ($file_path !== '') {
                $file_name = wp_basename($file_path);
            }

            $image_context = '';
            if ($provider === 'OpenAI') {
                if (!isset($context_cache[$attachment_id])) {
                    $context_cache[$attachment_id] = $this->get_image_context_for_attachment(
                        $attachment_id,
                        $provider,
                        $ai_caller
                    );
                }
                $image_context = (string) $context_cache[$attachment_id];
            }

            $placeholders = array_merge($base_placeholders, [
                '{file_name}' => $file_name,
            ]);

            $updated_values = [
                'title' => '',
                'alt' => '',
                'caption' => '',
                'description' => '',
            ];

            foreach ($field_flags as $field => $enabled) {
                if (!$enabled) {
                    continue;
                }

                $prompt = $this->build_image_metadata_prompt(
                    $prompt_templates[$field],
                    $placeholders,
                    $image_context
                );
                if ($prompt === '') {
                    continue;
                }

                $max_tokens = 120;
                if ($field === 'title') {
                    $max_tokens = 60;
                } elseif ($field === 'alt' || $field === 'caption') {
                    $max_tokens = 80;
                } elseif ($field === 'description') {
                    $max_tokens = 140;
                }

                $ai_params = [
                    'temperature' => $temperature,
                    'max_completion_tokens' => $max_tokens,
                ];
                if ($reasoning_effort !== '') {
                    $ai_params['reasoning'] = ['effort' => $reasoning_effort];
                }

                $ai_result = $ai_caller->make_standard_call(
                    $provider,
                    $model,
                    [['role' => 'user', 'content' => $prompt]],
                    $ai_params,
                    $system_instruction,
                    ['attachment_id' => $attachment_id]
                );

                if (is_wp_error($ai_result) || empty($ai_result['content'])) {
                    continue;
                }

                $value = trim(preg_replace('/\s+/', ' ', (string) $ai_result['content']));
                if ($value === '') {
                    continue;
                }

                switch ($field) {
                    case 'title':
                        $value = $this->trim_text(sanitize_text_field($value), 80);
                        break;
                    case 'alt':
                        $value = $this->trim_text(sanitize_text_field($value), 125);
                        break;
                    case 'caption':
                        $value = $this->trim_text(sanitize_text_field($value), 120);
                        break;
                    case 'description':
                        $value = $this->trim_text(sanitize_textarea_field($value), 240);
                        break;
                }

                if ($value !== '') {
                    $updated_values[$field] = $value;
                }
            }

            $post_update = ['ID' => $attachment_id];
            if ($updated_values['title'] !== '') {
                $post_update['post_title'] = $updated_values['title'];
                $image_item['title'] = $updated_values['title'];
            }
            if ($updated_values['caption'] !== '') {
                $post_update['post_excerpt'] = $updated_values['caption'];
                $image_item['caption'] = $updated_values['caption'];
            }
            if ($updated_values['description'] !== '') {
                $post_update['post_content'] = $updated_values['description'];
                $image_item['description'] = $updated_values['description'];
            }
            if (count($post_update) > 1) {
                wp_update_post($post_update);
            }
            if ($updated_values['alt'] !== '') {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', $updated_values['alt']);
                update_post_meta($attachment_id, '_aipkit_image_alt_text', $updated_values['alt']);
                $image_item['alt'] = $updated_values['alt'];
            }
        };

        if ($has_inline) {
            foreach ($image_data['in_content_images'] as &$image_item) {
                $attachment_id = isset($image_item['attachment_id']) ? absint($image_item['attachment_id']) : 0;
                if ($attachment_id <= 0) {
                    continue;
                }
                $process_attachment($attachment_id, $image_item);
            }
            unset($image_item);
        }

        if ($has_featured) {
            $featured_id = absint($image_data['featured_image_id']);
            if ($featured_id > 0) {
                $featured_item = ['attachment_id' => $featured_id];
                $process_attachment($featured_id, $featured_item);
            }
        }
    }

    public function generate_and_prepare_images(array $settings, string $final_title, string $final_keywords, ?string $original_topic = null): array|WP_Error
    {
        if (!$this->image_manager) {
            return new WP_Error('image_manager_missing', 'Image Manager dependency is not available.');
        }

        $generate_in_content = ($settings['generate_images_enabled'] ?? '0') === '1';
        $image_count = absint($settings['image_count'] ?? 1);
        $image_start_index = absint($settings['image_start_index'] ?? 1);
        if ($image_start_index < 1) {
            $image_start_index = 1;
        }
        $image_provider = strtolower($settings['image_provider'] ?? 'openai');
        $is_stock_provider = in_array($image_provider, ['pexels', 'pixabay'], true);
        $generate_featured = ($settings['generate_featured_image'] ?? '0') === '1';

        $main_image_prompt_template = $settings['image_prompt'] ?? '{topic}';
        $featured_image_prompt_template = !empty($settings['featured_image_prompt']) ? $settings['featured_image_prompt'] : $main_image_prompt_template;

        $post_title = isset($settings['post_title']) ? sanitize_text_field($settings['post_title']) : $final_title;
        $excerpt = isset($settings['excerpt']) ? sanitize_textarea_field($settings['excerpt']) : '';
        $meta_topic = $final_title !== '' ? $final_title : ($original_topic ?? '');

        // Replace placeholders for AI-specific prompts
        $replacements = [
            '{topic}' => $final_title,
            '{keywords}' => $final_keywords,
            '{post_title}' => $post_title,
            '{excerpt}' => $excerpt,
        ];
        $ai_main_prompt = str_replace(array_keys($replacements), array_values($replacements), $main_image_prompt_template);
        $ai_featured_prompt = str_replace(array_keys($replacements), array_values($replacements), $featured_image_prompt_template);

        // --- MODIFICATION: "Keyword-First" search strategy for Pexels/Pixabay ---
        $stock_image_topic = !empty(trim($final_keywords)) ? trim($final_keywords) : (!empty($original_topic) ? $original_topic : $final_title);
        // --- END MODIFICATION ---
        $prompt_for_main_images = ($image_provider === 'pexels' || $image_provider === 'pixabay') ? $stock_image_topic : $ai_main_prompt;
        $prompt_for_featured_image = !empty($featured_image_prompt_template)
                                     ? (($image_provider === 'pexels' || $image_provider === 'pixabay') ? $stock_image_topic : $ai_featured_prompt)
                                     : $prompt_for_main_images; // Fallback to main prompt if featured is empty

        $final_image_data = [
            'in_content_images' => [],
            'featured_image_id' => null,
            'featured_image_url' => null,
            'placement_settings' => [
                'placement' => $settings['image_placement'] ?? 'after_first_h2',
                'param_x' => absint($settings['image_placement_param_x'] ?? 2)
            ]
        ];

        $current_user_id = get_current_user_id() ?: 1;

        if ($is_stock_provider) {
            $num_to_fetch = 0;
            if ($generate_in_content) {
                $num_to_fetch += $image_count;
            }
            if ($generate_featured) {
                $num_to_fetch++;
            }

            if ($num_to_fetch > 0) {
                $generation_options = ['n' => $num_to_fetch];
                $meta_list = [];
                if ($generate_in_content && $image_count > 0) {
                    for ($i = 1; $i <= $image_count; $i++) {
                        $meta_list[] = $this->build_attachment_meta(
                            $meta_topic,
                            $final_keywords,
                            $post_title,
                            $excerpt,
                            $image_start_index + $i - 1,
                            false
                        );
                    }
                }
                if ($generate_featured) {
                    $meta_list[] = $this->build_attachment_meta(
                        $meta_topic,
                        $final_keywords,
                        $post_title,
                        $excerpt,
                        1,
                        true
                    );
                }
                if (!empty($meta_list)) {
                    $generation_options['aipkit_attachment_meta_list'] = $meta_list;
                }
                if ($image_provider === 'pexels') {
                    $generation_options['provider'] = 'pexels';
                    $generation_options['orientation'] = $settings['pexels_orientation'] ?? 'none';
                    $generation_options['size'] = $settings['pexels_size'] ?? 'none';
                    $generation_options['color'] = $settings['pexels_color'] ?? '';
                } elseif ($image_provider === 'pixabay') {
                    $generation_options['provider'] = 'pixabay';
                    $generation_options['orientation'] = $settings['pixabay_orientation'] ?? 'all';
                    $generation_options['image_type'] = $settings['pixabay_image_type'] ?? 'all';
                    $generation_options['category'] = $settings['pixabay_category'] ?? '';
                }


                $stock_result = $this->image_manager->generate_image($prompt_for_main_images, $generation_options, $current_user_id);
                if (!is_wp_error($stock_result) && !empty($stock_result['images'])) {
                    $this->pexels_image_cache = $stock_result['images']; // Reusing this cache variable name for simplicity
                } else {
                    $error_msg = is_wp_error($stock_result) ? $stock_result->get_error_message() : "No images returned from {$image_provider} API.";
                }
            }

            // Populate in-content images from cache
            if ($generate_in_content && $image_count > 0) {
                $final_image_data['in_content_images'] = array_splice($this->pexels_image_cache, 0, $image_count);
            }

            // Populate featured image from the *remaining* cache
            if ($generate_featured) {
                $featured_image_data = array_shift($this->pexels_image_cache);
                if ($featured_image_data) {
                    if (!empty($featured_image_data['attachment_id'])) {
                        $final_image_data['featured_image_id'] = $featured_image_data['attachment_id'];
                    }
                    $featured_image_url = $featured_image_data['media_library_url'] ?? ($featured_image_data['url'] ?? ($featured_image_data['src'] ?? ($featured_image_data['image_url'] ?? null)));
                    if (!empty($featured_image_url)) {
                        $final_image_data['featured_image_url'] = $featured_image_url;
                    }
                }
            }
        }


        // --- Original AI Generation Logic (for OpenAI, Google, etc.) ---
        // Main image generation
        if (!$is_stock_provider && $generate_in_content && $image_count > 0 && !empty($prompt_for_main_images)) {
            $image_model = $settings['image_model'] ?? 'gpt-image-1';
            $generation_options = [
                'provider' => strtolower($settings['image_provider'] ?? 'openai'),
                'model' => $image_model,
                'size' => '1024x1024',
                'response_format' => 'url',
                'user' => 'cw_user_' . $current_user_id,
                'quality' => 'standard',
                'style' => 'vivid'
            ];

            // Models/providers that only support returning one image per request
            $models_with_n_equals_1 = ['dall-e-3', 'gpt-image-1.5', 'gpt-image-1', 'gpt-image-1-mini'];
            if (strpos($image_model, 'gemini') !== false && strpos($image_model, 'image-generation') !== false) {
                $models_with_n_equals_1[] = $image_model; // handle all Gemini image-generation variants
            }

            $force_single_image_requests = ($image_provider === 'replicate');

            if ($force_single_image_requests || in_array($image_model, $models_with_n_equals_1, true)) {
                for ($i = 0; $i < $image_count; $i++) {
                    $generation_options['n'] = 1;
                    $generation_options['aipkit_attachment_meta'] = $this->build_attachment_meta(
                        $meta_topic,
                        $final_keywords,
                        $post_title,
                        $excerpt,
                        $image_start_index + $i,
                        false
                    );
                    $result = $this->image_manager->generate_image($prompt_for_main_images, $generation_options, $current_user_id);
                    if (!is_wp_error($result) && !empty($result['images'])) {
                        $final_image_data['in_content_images'][] = $result['images'][0];
                    } else {
                        $error_msg = is_wp_error($result) ? $result->get_error_message() : 'No images returned from API.';
                        // Log the error for debugging bulk mode issues
                        error_log("AIPKit Image Generation Error (Image #" . ($i + 1) . "): " . $error_msg . " | Model: " . $image_model . " | Provider: " . ($generation_options['provider'] ?? 'unknown'));
                    }
                }
            } else { // Models that support n > 1
                $max_n = 10;
                if (($settings['image_provider'] ?? 'openai') === 'google' && $image_model === 'imagen-3.0-generate-002') {
                    $max_n = 4;
                }
                $generation_options['n'] = min($image_count, $max_n);
                $meta_list = [];
                for ($i = 1; $i <= $generation_options['n']; $i++) {
                    $meta_list[] = $this->build_attachment_meta(
                        $meta_topic,
                        $final_keywords,
                        $post_title,
                        $excerpt,
                        $image_start_index + $i - 1,
                        false
                    );
                }
                if (!empty($meta_list)) {
                    $generation_options['aipkit_attachment_meta_list'] = $meta_list;
                }

                $result = $this->image_manager->generate_image($prompt_for_main_images, $generation_options, $current_user_id);
                if (!is_wp_error($result) && !empty($result['images'])) {
                    $final_image_data['in_content_images'] = array_merge($final_image_data['in_content_images'], $result['images']);
                } else {
                    $error_msg = is_wp_error($result) ? $result->get_error_message() : 'No images returned from API.';
                    // Log the error for debugging bulk mode issues
                    error_log("AIPKit Image Generation Error (Batch): " . $error_msg . " | Model: " . $image_model . " | Provider: " . ($generation_options['provider'] ?? 'unknown') . " | Count: " . $image_count);
                }
            }
        }

        // Featured image generation
        if (!$is_stock_provider && $generate_featured && !empty($prompt_for_featured_image)) {
            // Note: The original logic already had a separate call for the featured image for AI providers,
            // which is correct behavior as it might use a different prompt.
            $generation_options = [
                'provider' => strtolower($settings['image_provider'] ?? 'openai'),
                'model' => $settings['image_model'] ?? 'gpt-image-1',
                'n' => 1,
                'size' => '1024x1024',
                'response_format' => 'url',
                'user' => 'cw_user_featured_' . $current_user_id,
                'quality' => 'hd',
                'style' => 'vivid'
            ];
            $generation_options['aipkit_attachment_meta'] = $this->build_attachment_meta(
                $meta_topic,
                $final_keywords,
                $post_title,
                $excerpt,
                1,
                true
            );

            $result = $this->image_manager->generate_image($prompt_for_featured_image, $generation_options, $current_user_id);

            if (!is_wp_error($result) && !empty($result['images'][0])) {
                $featured_image = $result['images'][0];
                if (!empty($featured_image['attachment_id'])) {
                    $final_image_data['featured_image_id'] = $featured_image['attachment_id'];
                }
                $featured_image_url = $featured_image['media_library_url'] ?? ($featured_image['url'] ?? ($featured_image['src'] ?? ($featured_image['image_url'] ?? null)));
                if (!empty($featured_image_url)) {
                    $final_image_data['featured_image_url'] = $featured_image_url;
                }
                if (empty($final_image_data['featured_image_id']) && empty($final_image_data['featured_image_url'])) {
                    error_log("AIPKit Featured Image Generation Error: No featured image URL returned. | Model: " . ($generation_options['model'] ?? 'unknown') . " | Provider: " . ($generation_options['provider'] ?? 'unknown'));
                }
            } else {
                $error_msg = is_wp_error($result) ? $result->get_error_message() : 'No featured image returned.';
                // Log the error for debugging
                error_log("AIPKit Featured Image Generation Error: " . $error_msg . " | Model: " . ($generation_options['model'] ?? 'unknown') . " | Provider: " . ($generation_options['provider'] ?? 'unknown'));
            }
        }

        $this->maybe_generate_image_metadata(
            $settings,
            $final_image_data,
            $final_title,
            $final_keywords,
            $post_title,
            $excerpt,
            $meta_topic
        );

        return $final_image_data;
    }

    private function get_image_context_for_attachment(int $attachment_id, string $provider, AIPKit_AI_Caller $ai_caller): string
    {
        if ($provider !== 'OpenAI') {
            return '';
        }

        $openai_config = AIPKit_Providers::get_provider_data('OpenAI');
        if (empty($openai_config['api_key'])) {
            return '';
        }

        $file_path = $this->get_attachment_image_path($attachment_id);
        $file_mtime = $file_path && file_exists($file_path) ? (int) filemtime($file_path) : 0;
        $transient_key = 'aipkit_cw_img_ctx_' . $attachment_id . '_' . $file_mtime;
        $cached_context = get_transient($transient_key);
        if (is_string($cached_context) && $cached_context !== '') {
            return $cached_context;
        }

        $image_payload = $this->get_attachment_image_payload($attachment_id);
        if (empty($image_payload['base64']) || empty($image_payload['type'])) {
            return '';
        }

        $analysis_prompt = 'Describe the image in one short sentence for SEO context. Return only the description.';
        $analysis_params = [
            'temperature' => 0.2,
            'max_completion_tokens' => 60,
            'image_inputs' => [
                [
                    'base64' => $image_payload['base64'],
                    'type' => $image_payload['type'],
                    'detail' => 'low',
                ],
            ],
        ];

        $analysis_result = $ai_caller->make_standard_call(
            'OpenAI',
            'gpt-4.1-mini',
            [['role' => 'user', 'content' => $analysis_prompt]],
            $analysis_params,
            null,
            ['attachment_id' => $attachment_id]
        );

        if (is_wp_error($analysis_result) || empty($analysis_result['content'])) {
            return '';
        }

        $context = trim(preg_replace('/\s+/', ' ', $analysis_result['content']));
        if ($context === '') {
            return '';
        }

        set_transient($transient_key, $context, 30 * MINUTE_IN_SECONDS);
        return $context;
    }

    private function get_attachment_image_path(int $attachment_id): string
    {
        $original_path = (string) get_attached_file($attachment_id);
        if ($original_path === '') {
            return '';
        }

        $meta = wp_get_attachment_metadata($attachment_id);
        if (is_array($meta) && !empty($meta['sizes']['medium']['file'])) {
            $dir = trailingslashit(pathinfo($original_path, PATHINFO_DIRNAME));
            $medium_path = $dir . $meta['sizes']['medium']['file'];
            if (file_exists($medium_path)) {
                return $medium_path;
            }
        }

        return file_exists($original_path) ? $original_path : '';
    }

    private function get_attachment_image_payload(int $attachment_id): array
    {
        $file_path = $this->get_attachment_image_path($attachment_id);
        if ($file_path !== '') {
            $payload = $this->get_image_payload_from_path($file_path);
            if (!empty($payload)) {
                return $payload;
            }
        }

        $image_url = wp_get_attachment_image_url($attachment_id, 'medium');
        if (empty($image_url)) {
            return [];
        }

        $response = wp_remote_get($image_url, ['timeout' => 15]);
        if (is_wp_error($response)) {
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        if ($body === '') {
            return [];
        }

        $content_type = (string) wp_remote_retrieve_header($response, 'content-type');
        $mime_type = trim(strtok($content_type, ';'));
        if ($mime_type === '' || strpos($mime_type, 'image/') !== 0) {
            return [];
        }

        return [
            'base64' => base64_encode($body),
            'type' => $mime_type,
        ];
    }

    private function get_image_payload_from_path(string $file_path): array
    {
        if (!is_readable($file_path)) {
            return [];
        }

        $file_size = filesize($file_path);
        if ($file_size !== false && $file_size > 50 * 1024 * 1024) {
            return [];
        }

        $image_bytes = file_get_contents($file_path);
        if ($image_bytes === false) {
            return [];
        }

        $mime_type = wp_get_image_mime($file_path);
        if (empty($mime_type)) {
            $filetype = wp_check_filetype($file_path);
            $mime_type = $filetype['type'] ?? '';
        }

        if ($mime_type === '' || strpos($mime_type, 'image/') !== 0) {
            return [];
        }

        return [
            'base64' => base64_encode($image_bytes),
            'type' => $mime_type,
        ];
    }
}
