<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/admin/views/modules/content-writer/partials/form-inputs/ai-settings.php
// Status: MODIFIED
/**
 * Partial: Content Writer Form - AI Settings
 * 
 * REDESIGNED with three core UX principles:
 * 1. Aesthetic - Clean, modern visual design with consistent spacing and typography
 * 2. Choice Overload Prevention - Smart defaults, progressive disclosure, visual hierarchy
 * 3. Chunking - Logically grouped settings with clear visual separation
 */
if (!defined('ABSPATH')) {
    exit;
}
// Variables from loader-vars.php: $providers_for_select, $default_provider, $default_temperature

// Compute Pro flag once
$is_pro = class_exists('\\WPAICG\\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
?>

<div class="aipkit_ai_settings_redesigned">

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 1: Model Selection (Primary Decision)
         The most important choice - presented first and prominently
         Following Choice Architecture: Guide users to the key decision first
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_ai_settings_chunk aipkit_ai_settings_chunk--model">
        <div class="aipkit_ai_settings_chunk_body">
            <!-- Provider & Model in a unified visual group -->
            <div class="aipkit_ai_model_selector">
                <div class="aipkit_ai_model_selector_row">
                    <label class="aipkit_ai_settings_label" for="aipkit_content_writer_provider">
                        <?php esc_html_e('Provider', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select 
                        id="aipkit_content_writer_provider" 
                        name="ai_provider" 
                        class="aipkit_ai_settings_select aipkit_autosave_trigger" 
                        data-aipkit-provider-notice-target="aipkit_provider_notice_content_writer" 
                        data-aipkit-provider-notice-defer="1"
                    >
                        <?php
                        if (!empty($providers_for_select) && is_array($providers_for_select)) {
                            foreach ($providers_for_select as $p_value) {
                                if ($p_value === 'DeepSeek' || $p_value === 'Ollama') {
                                    continue;
                                }
                                $val = strtolower($p_value);
                                echo '<option value="' . esc_attr($val) . '"' . selected($default_provider, $val, false) . '>' . esc_html($p_value) . '</option>';
                            }
                        }
                        // DeepSeek option
                        echo '<option value="deepseek"' . selected($default_provider, 'deepseek', false) . '>DeepSeek</option>';
                        // Ollama option (always listed, disabled unless Pro)
                        $ol_disabled = !$is_pro;
                        $ol_label = $ol_disabled ? __('Ollama (Pro)', 'gpt3-ai-content-generator') : 'Ollama';
                        echo '<option value="ollama"' . selected($default_provider, 'ollama', false) . ($ol_disabled ? ' disabled' : '') . '>' . esc_html($ol_label) . '</option>';
                        ?>
                    </select>
                </div>
                
                <div class="aipkit_ai_model_selector_divider">
                    <span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
                </div>
                
                <div class="aipkit_ai_model_selector_row aipkit_ai_model_selector_row--model">
                    <label class="aipkit_ai_settings_label" for="aipkit_content_writer_model">
                        <?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <select 
                        id="aipkit_content_writer_model" 
                        name="ai_model" 
                        class="aipkit_ai_settings_select aipkit_autosave_trigger"
                    ></select>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════
         CHUNK 2: Model Behavior (Secondary - Progressive Disclosure)
         Temperature and Reasoning - shown contextually
         Following Chunking Principle: Related controls grouped together
    ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="aipkit_ai_settings_chunk aipkit_ai_settings_chunk--behavior">
        <div class="aipkit_ai_settings_chunk_body">
            
            <!-- Temperature Control - Redesigned with visual scale -->
            <div class="aipkit_ai_behavior_control aipkit_ai_temperature_control">
                <div class="aipkit_ai_behavior_header">
                    <label class="aipkit_ai_settings_label" for="aipkit_content_writer_temperature">
                        <?php esc_html_e('Creativity', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_ai_behavior_value" id="aipkit_content_writer_temperature_value">
                        <?php echo esc_html($default_temperature); ?>
                    </span>
                </div>
                
                <div class="aipkit_ai_temperature_slider_wrapper">
                    <div class="aipkit_ai_temperature_labels">
                        <span class="aipkit_ai_temperature_label aipkit_ai_temperature_label--low">
                            <span class="dashicons dashicons-editor-textcolor" aria-hidden="true"></span>
                            <?php esc_html_e('Focused', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <span class="aipkit_ai_temperature_label aipkit_ai_temperature_label--high">
                            <span class="dashicons dashicons-art" aria-hidden="true"></span>
                            <?php esc_html_e('Creative', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input 
                        type="range" 
                        id="aipkit_content_writer_temperature" 
                        name="ai_temperature" 
                        class="aipkit_ai_temperature_slider aipkit_autosave_trigger" 
                        min="0" 
                        max="2" 
                        step="0.1" 
                        value="<?php echo esc_attr($default_temperature); ?>"
                    >
                </div>
                
                <p class="aipkit_ai_behavior_hint">
                    <?php esc_html_e('Lower = more predictable, higher = more varied output', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>

            <!-- Content Length Control - Friendly size cap -->
            <div class="aipkit_ai_behavior_control aipkit_ai_length_control">
                <div class="aipkit_ai_behavior_header">
                    <label class="aipkit_ai_settings_label" for="aipkit_content_writer_content_length_slider">
                        <?php esc_html_e('Content length', 'gpt3-ai-content-generator'); ?>
                    </label>
                    <span class="aipkit_ai_behavior_value" id="aipkit_content_writer_content_length_value">
                        <?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?>
                    </span>
                </div>

                <div class="aipkit_ai_length_slider_wrapper">
                    <div class="aipkit_ai_length_labels">
                        <span class="aipkit_ai_length_label">
                            <?php esc_html_e('Short', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <span class="aipkit_ai_length_label">
                            <?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?>
                        </span>
                        <span class="aipkit_ai_length_label">
                            <?php esc_html_e('Long', 'gpt3-ai-content-generator'); ?>
                        </span>
                    </div>
                    <input
                        type="range"
                        id="aipkit_content_writer_content_length_slider"
                        class="aipkit_ai_length_slider"
                        min="1"
                        max="3"
                        step="1"
                        value="2"
                    >
                </div>

                <p class="aipkit_ai_behavior_hint" id="aipkit_content_writer_content_length_hint">
                    <?php esc_html_e('Approx. 1200-1600 words', 'gpt3-ai-content-generator'); ?>
                </p>

                <select
                    id="aipkit_content_writer_content_length"
                    name="content_length"
                    class="aipkit_hidden_form_field aipkit_autosave_trigger"
                    style="display: none;"
                >
                    <option value="short"><?php esc_html_e('Short', 'gpt3-ai-content-generator'); ?></option>
                    <option value="medium" selected><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
                    <option value="long"><?php esc_html_e('Long', 'gpt3-ai-content-generator'); ?></option>
                </select>
            </div>

            <!-- Reasoning Effort - Contextual, only for supported models -->
            <div class="aipkit_ai_behavior_control aipkit_cw_reasoning_effort_field" style="display: none;">
                <div class="aipkit_ai_behavior_header">
                    <label class="aipkit_ai_settings_label aipkit_ai_settings_label--with-badge" for="aipkit_content_writer_reasoning_effort">
                        <?php esc_html_e('Reasoning', 'gpt3-ai-content-generator'); ?>
                        <span class="aipkit_ai_settings_badge"><?php esc_html_e('Advanced', 'gpt3-ai-content-generator'); ?></span>
                    </label>
                </div>
                
                <div class="aipkit_ai_reasoning_options">
                    <?php
                    $reasoning_options = [
                        'none' => [
                            'label' => __('None', 'gpt3-ai-content-generator'),
                            'desc'  => __('No reason', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-dismiss'
                        ],
                        'low' => [
                            'label' => __('Low', 'gpt3-ai-content-generator'),
                            'desc'  => __('Faster', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-minus'
                        ],
                        'medium' => [
                            'label' => __('Medium', 'gpt3-ai-content-generator'),
                            'desc'  => __('Thoughtful', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-lightbulb'
                        ],
                        'high' => [
                            'label' => __('High', 'gpt3-ai-content-generator'),
                            'desc'  => __('Deep', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-admin-network'
                        ],
                        'xhigh' => [
                            'label' => __('XHigh', 'gpt3-ai-content-generator'),
                            'desc'  => __('Maximum', 'gpt3-ai-content-generator'),
                            'icon'  => 'dashicons-chart-line'
                        ],
                    ];
                    
                    foreach ($reasoning_options as $value => $option) : ?>
                        <label class="aipkit_ai_reasoning_option <?php echo $value === 'medium' ? 'aipkit_ai_reasoning_option--selected' : ''; ?>">
                            <input 
                                type="radio" 
                                name="reasoning_effort" 
                                value="<?php echo esc_attr($value); ?>" 
                                class="aipkit_ai_reasoning_input aipkit_autosave_trigger"
                                <?php checked($value, 'medium'); ?>
                            >
                            <span class="aipkit_ai_reasoning_option_content">
                                <span class="aipkit_ai_reasoning_option_icon">
                                    <span class="dashicons <?php echo esc_attr($option['icon']); ?>" aria-hidden="true"></span>
                                </span>
                                <span class="aipkit_ai_reasoning_option_text">
                                    <span class="aipkit_ai_reasoning_option_label"><?php echo esc_html($option['label']); ?></span>
                                    <span class="aipkit_ai_reasoning_option_desc"><?php echo esc_html($option['desc']); ?></span>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <p class="aipkit_ai_behavior_hint">
                    <?php esc_html_e('For o-series, GPT-5 models. Higher reasoning = slower but more thorough.', 'gpt3-ai-content-generator'); ?>
                </p>
            </div>
            
        </div>
    </div>

    <!-- Hidden select to maintain form compatibility for reasoning_effort -->
    <select id="aipkit_content_writer_reasoning_effort" name="reasoning_effort_hidden" class="aipkit_hidden_form_field" style="display: none;">
        <option value="none"><?php esc_html_e('None', 'gpt3-ai-content-generator'); ?></option>
        <option value="low"><?php esc_html_e('Low', 'gpt3-ai-content-generator'); ?></option>
        <option value="medium" selected><?php esc_html_e('Medium', 'gpt3-ai-content-generator'); ?></option>
        <option value="high"><?php esc_html_e('High', 'gpt3-ai-content-generator'); ?></option>
        <option value="xhigh"><?php esc_html_e('XHigh', 'gpt3-ai-content-generator'); ?></option>
    </select>

</div>
