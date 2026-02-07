<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/public/views/shortcodes/image-generator.php
// Status: MODIFIED

/**
 * Partial View: Frontend Image Generator Shortcode UI
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use WPAICG\AIPKit_Providers;

// Variables passed from the shortcode class: $nonce,
// $show_provider, $show_model,
// $preset_provider, $preset_model, $preset_size, $preset_number,
// $final_provider, $final_model, $final_size, $final_number,
// $theme, $show_history, $image_history_html,
// $allowed_providers, $allowed_models

$openai_models_display = [ // For display in dropdown
    'gpt-image-1.5' => 'GPT Image 1.5',
    'gpt-image-1' => 'GPT Image 1',
    'gpt-image-1-mini' => 'GPT Image 1 mini',
    'dall-e-3' => 'DALL-E 3',
    'dall-e-2' => 'DALL-E 2',
];
// Build Google models dynamically from synced option
$google_models_display = [ 'image' => [], 'video' => [] ];
$openrouter_models_display = [];
$replicate_models_display = [];
$azure_models_display = [];
if (class_exists('\\WPAICG\\AIPKit_Providers')) {
    $google_image_models = AIPKit_Providers::get_google_image_models();
    if (!empty($google_image_models)) {
        foreach ($google_image_models as $mdl) {
            $mid = is_array($mdl) ? ($mdl['id'] ?? null) : (is_string($mdl) ? $mdl : null);
            $mname = is_array($mdl) ? ($mdl['name'] ?? $mid) : $mid;
            if ($mid) {
                $google_models_display['image'][$mid] = $mname;
            }
        }
    }
    $google_video_models = AIPKit_Providers::get_google_video_models();
    if (!empty($google_video_models)) {
        foreach ($google_video_models as $mdl) {
            $mid = is_array($mdl) ? ($mdl['id'] ?? null) : (is_string($mdl) ? $mdl : null);
            $mname = is_array($mdl) ? ($mdl['name'] ?? $mid) : $mid;
            if ($mid) {
                $google_models_display['video'][$mid] = $mname;
            }
        }
    }

    $openrouter_models = AIPKit_Providers::get_openrouter_image_models();
    if (!empty($openrouter_models)) {
        foreach ($openrouter_models as $mdl) {
            $mid = is_array($mdl) ? ($mdl['id'] ?? null) : (is_string($mdl) ? $mdl : null);
            $mname = is_array($mdl) ? ($mdl['name'] ?? $mid) : $mid;
            if ($mid) {
                $openrouter_models_display[$mid] = $mname;
            }
        }
    }

    $replicate_models = AIPKit_Providers::get_replicate_models();
    if (!empty($replicate_models)) {
        foreach ($replicate_models as $mdl) {
            $mid = is_array($mdl) ? ($mdl['id'] ?? null) : (is_string($mdl) ? $mdl : null);
            $mname = is_array($mdl) ? ($mdl['name'] ?? $mid) : $mid;
            if ($mid) {
                $replicate_models_display[$mid] = $mname;
            }
        }
    }

    $azure_models_list = AIPKit_Providers::get_azure_image_models();
    if (!empty($azure_models_list)) {
        foreach ($azure_models_list as $mdl) {
            $mid = is_array($mdl) ? ($mdl['id'] ?? null) : (is_string($mdl) ? $mdl : null);
            $mname = is_array($mdl) ? ($mdl['name'] ?? $mid) : $mid;
            if ($mid) {
                $azure_models_display[$mid] = $mname;
            }
        }
    }
}

$theme_class = 'aipkit-theme-' . esc_attr($theme);

?>
<div class="aipkit_shortcode_container aipkit_image_generator_public_wrapper <?php echo esc_attr($theme_class); ?>" id="aipkit_public_image_generator" data-allowed-models="<?php echo esc_attr($allowed_models); ?>">
    <div class="aipkit_shortcode_body">
        <div class="aipkit_image_generator_input_bar">
            <div class="aipkit_form-group aipkit_image_generator_prompt_area">
                <textarea
                    id="aipkit_public_image_prompt"
                    name="image_prompt"
                    class="aipkit_form-input aipkit_image_prompt_textarea"
                    rows="3"
                    placeholder="<?php esc_attr_e('Describe the image you want to generate...', 'gpt3-ai-content-generator'); ?>"
                ></textarea>
            </div>
             <div class="aipkit_image_generator_controls_row">
                <div class="aipkit_image_generator_options">
                    <?php if ($show_provider) : ?>
                        <div class="aipkit_form-group aipkit_image_gen_option">
                            <label class="aipkit_form-label" for="aipkit_public_image_provider"><?php esc_html_e('Provider', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_public_image_provider" name="image_provider" class="aipkit_form-input" data-aipkit-provider-notice-target="aipkit_provider_notice_image_generator">
                                <?php
                                $all_providers = ['OpenAI', 'Google', 'OpenRouter', 'Azure', 'Replicate'];
                                $allowed_models_arr = !empty($allowed_models) ? array_map('trim', explode(',', strtolower($allowed_models))) : [];
                                if (!empty($allowed_models_arr)) {
                                    $derived = [];
                                    $openai_lookup = array_flip(array_map('strtolower', array_keys($openai_models_display)));
                                    $google_lookup = [];
                                    foreach ($google_models_display as $type => $models_array) {
                                        foreach ($models_array as $id => $name) {
                                            $google_lookup[strtolower((string) $id)] = true;
                                        }
                                    }
                                    $openrouter_lookup = array_flip(array_map('strtolower', array_keys($openrouter_models_display)));
                                    $azure_lookup = array_flip(array_map('strtolower', array_keys($azure_models_display)));
                                    $replicate_lookup = array_flip(array_map('strtolower', array_keys($replicate_models_display)));
                                    foreach ($allowed_models_arr as $mid) {
                                        if (isset($openai_lookup[$mid])) {
                                            $derived['OpenAI'] = true;
                                        } elseif (isset($google_lookup[$mid])) {
                                            $derived['Google'] = true;
                                        } elseif (isset($openrouter_lookup[$mid])) {
                                            $derived['OpenRouter'] = true;
                                        } elseif (isset($azure_lookup[$mid])) {
                                            $derived['Azure'] = true;
                                        } elseif (isset($replicate_lookup[$mid])) {
                                            $derived['Replicate'] = true;
                                        } elseif (strpos($mid, '/') !== false) {
                                            // Fallback for older settings when full model list isn't synced yet.
                                            if (strpos($mid, ':') !== false) {
                                                $derived['Replicate'] = true;
                                            } else {
                                                $derived['OpenRouter'] = true;
                                            }
                                        }
                                    }
                                    $providers_to_show = array_values(array_intersect($all_providers, array_keys($derived)));
                                    if (empty($providers_to_show)) {
                                        $providers_to_show = $all_providers; // fallback safety
                                    }
                                } else {
                                    $providers_to_show = $all_providers;
                                }
                                foreach ($providers_to_show as $provider_name) : ?>
                                    <option value="<?php echo esc_attr($provider_name); ?>" <?php selected($final_provider, $provider_name); ?>><?php echo esc_html($provider_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else : ?>
                        <input type="hidden" name="image_provider" value="<?php echo esc_attr($final_provider); ?>">
                    <?php endif; ?>

                    <?php if ($show_model) : ?>
                        <div class="aipkit_form-group aipkit_image_gen_option">
                            <label class="aipkit_form-label" for="aipkit_public_image_model"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></label>
                            <select id="aipkit_public_image_model" name="image_model" class="aipkit_form-input">
                                 <?php // Options populated by JS, but set selected based on final_model?>
                                 <?php if ($final_provider === 'OpenAI'): ?>
                                    <?php
                            // Sort OpenAI models for display: gpt-image-1.5, gpt-image-1, gpt-image-1-mini, dall-e-3, dall-e-2
                            $sorted_openai_keys_render = ['gpt-image-1.5', 'gpt-image-1', 'gpt-image-1-mini', 'dall-e-3', 'dall-e-2'];
                                     $final_openai_models_render = [];
                                     foreach ($sorted_openai_keys_render as $key) {
                                         if (isset($openai_models_display[$key])) {
                                             $final_openai_models_render[$key] = $openai_models_display[$key];
                                         }
                                     }
                                     // Add any other OpenAI models not in the sort list (future-proofing)
                                     foreach ($openai_models_display as $id => $name) {
                                         if (!isset($final_openai_models_render[$id])) {
                                             $final_openai_models_render[$id] = $name;
                                         }
                                     }
                                     ?>
                                    <?php foreach ($final_openai_models_render as $id => $name): ?>
                                        <option value="<?php echo esc_attr($id); ?>" <?php selected($final_model, $id); ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                     <?php if ($final_model && !array_key_exists($final_model, $openai_models_display)) : ?>
                                        <option value="<?php echo esc_attr($final_model); ?>" selected><?php echo esc_html($final_model); ?> (Manual)</option>
                                     <?php endif; ?>
                                 <?php elseif ($final_provider === 'Google'): ?>
                                     <?php foreach ($google_models_display as $type => $models_array): ?>
                                        <optgroup label="<?php echo esc_attr(ucfirst($type) . ' Models'); ?>">
                                            <?php foreach ($models_array as $id => $name): ?>
                                                <option value="<?php echo esc_attr($id); ?>" <?php selected($final_model, $id); ?>>
                                                    <?php echo esc_html($name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                     <?php endforeach; ?>
                                     <?php 
                                     // Check if final_model exists in any of the optgroups
                                     $model_found = false;
                                     foreach ($google_models_display as $type => $models_array) {
                                         if (array_key_exists($final_model, $models_array)) {
                                             $model_found = true;
                                             break;
                                         }
                                     }
                                     if ($final_model && !$model_found) : ?>
                                        <option value="<?php echo esc_attr($final_model); ?>" selected><?php echo esc_html($final_model); ?> (Manual)</option>
                                     <?php endif; ?>
                                 <?php elseif ($final_provider === 'OpenRouter'): ?>
                                     <?php foreach ($openrouter_models_display as $id => $name): ?>
                                        <option value="<?php echo esc_attr($id); ?>" <?php selected($final_model, $id); ?>>
                                            <?php echo esc_html($name); ?>
                                        </option>
                                     <?php endforeach; ?>
                                     <?php if ($final_model && !array_key_exists($final_model, $openrouter_models_display)) : ?>
                                        <option value="<?php echo esc_attr($final_model); ?>" selected><?php echo esc_html($final_model); ?> (Manual)</option>
                                     <?php endif; ?>
                                 <?php elseif ($final_provider === 'Azure'): ?>
                                     <?php 
                                     // Azure models handling
                                     $azure_models_display_rows = AIPKit_Providers::get_azure_image_models();
                                     ?>
                                     <?php foreach ($azure_models_display_rows as $model): ?>
                                        <option value="<?php echo esc_attr($model['id']); ?>" <?php selected($final_model, $model['id']); ?>>
                                            <?php echo esc_html($model['name']); ?>
                                        </option>
                                     <?php endforeach; ?>
                                     <?php 
                                     // Check if final_model exists in azure models
                                     $model_found = false;
                                     foreach ($azure_models_display_rows as $model) {
                                         if ($model['id'] === $final_model) {
                                             $model_found = true;
                                             break;
                                         }
                                     }
                                     if ($final_model && !$model_found) : ?>
                                        <option value="<?php echo esc_attr($final_model); ?>" selected><?php echo esc_html($final_model); ?> (Manual)</option>
                                     <?php endif; ?>
                                 <?php else: ?>
                                     <option value=""><?php esc_html_e('(Select Provider)', 'gpt3-ai-content-generator'); ?></option>
                                 <?php endif; ?>
                            </select>
                        </div>
                     <?php else : ?>
                        <input type="hidden" name="image_model" value="<?php echo esc_attr($final_model); ?>">
                    <?php endif; ?>
                </div>
                <div class="aipkit_image_generator_action_area">
                    <button id="aipkit_public_generate_image_btn" class="aipkit_btn aipkit_btn-primary aipkit_image_generate_btn">
                        <span class="dashicons dashicons-images-alt"></span>
                        <span class="aipkit_btn-text"><?php esc_html_e('Generate', 'gpt3-ai-content-generator'); ?></span>
                        <span class="aipkit_spinner" style="display:none;"></span>
                    </button>
                </div>
             </div>
        </div>
        <div class="aipkit_image_generator_results" id="aipkit_public_image_results">
             <p class="aipkit_image_results_placeholder" style="text-align:center; font-style: italic;"></p>
        </div>
        <input type="hidden" id="aipkit_image_generator_public_nonce" value="<?php echo esc_attr($nonce); ?>">

        <?php if (isset($show_history) && $show_history && is_user_logged_in() && !empty(trim($image_history_html))): ?>
            <div class="aipkit_image_history_section">
                <h3 class="aipkit_image_history_title"><?php esc_html_e('Your Images', 'gpt3-ai-content-generator'); ?></h3>
                <?php
                // We're keeping the HTML generation in PHP for initial load, JS handles deletion.
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is generated in the shortcode class.
                echo $image_history_html;
?>
            </div>
        <?php endif; ?>
    </div>
</div>
