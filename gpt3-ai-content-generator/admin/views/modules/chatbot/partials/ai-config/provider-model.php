<?php

/**
 * Partial: AI Config - Provider and Model Selection
 */
if (!defined('ABSPATH')) {
    exit;
}

use WPAICG\Chat\Storage\BotSettingsManager; // Use new class for constants

$saved_stream_enabled = isset($bot_settings['stream_enabled'])
                        ? $bot_settings['stream_enabled']
                        : \WPAICG\Chat\Storage\BotSettingsManager::DEFAULT_STREAM_ENABLED;

// Get saved Azure deployment name if applicable
$saved_azure_deployment = ($saved_provider === 'Azure') ? $saved_model : '';
$shortcode_text_main = '';
if (!empty($bot_id)) {
    $shortcode_text_main = sprintf('[aipkit_chatbot id=%d]', absint($bot_id));
}
$shortcode_label = '';
$shortcode_pill = '';
if (!empty($shortcode_text_main)) {
    $shortcode_label = '<span class="aipkit_form-label aipkit_form-label--inline aipkit_shortcode_label">' .
        esc_html__('Shortcode', 'gpt3-ai-content-generator') .
        '</span>';
    $shortcode_pill = sprintf(
        '<div class="aipkit_shortcode_pill aipkit_builder_shortcode_pill" data-shortcode="%1$s" title="%2$s"><span class="aipkit_shortcode_text">%3$s</span></div>',
        esc_attr($shortcode_text_main),
        esc_attr__('Click to copy shortcode', 'gpt3-ai-content-generator'),
        esc_html($shortcode_text_main)
    );
}

$recommended_openai = \WPAICG\AIPKit_Providers::get_recommended_models('OpenAI');
$recommended_openai = array_values(array_filter($recommended_openai, static function ($model) {
    return is_array($model) && !empty($model['id']);
}));
$recommended_openai_ids = array_column($recommended_openai, 'id');
$recommended_openai_lookup = array_fill_keys($recommended_openai_ids, true);

$recommended_openrouter = \WPAICG\AIPKit_Providers::get_recommended_models('OpenRouter');
$recommended_openrouter = array_values(array_filter($recommended_openrouter, static function ($model) {
    return is_array($model) && !empty($model['id']);
}));
$recommended_openrouter_ids = array_column($recommended_openrouter, 'id');
$recommended_openrouter_lookup = array_fill_keys($recommended_openrouter_ids, true);

$recommended_google = \WPAICG\AIPKit_Providers::get_recommended_models('Google');
$recommended_google = array_values(array_filter($recommended_google, static function ($model) {
    return is_array($model) && !empty($model['id']);
}));
$recommended_google_ids = array_column($recommended_google, 'id');
$recommended_google_lookup = array_fill_keys($recommended_google_ids, true);

$recommended_claude = \WPAICG\AIPKit_Providers::get_recommended_models('Claude');
$recommended_claude = array_values(array_filter($recommended_claude, static function ($model) {
    return is_array($model) && !empty($model['id']);
}));
$recommended_claude_ids = array_column($recommended_claude, 'id');
$recommended_claude_lookup = array_fill_keys($recommended_claude_ids, true);
$show_chatbot_selector = empty($is_next_layout) || !$is_next_layout;

?>
<!-- Row container for Bot + Provider + Model -->
<div class="aipkit_form-row aipkit_form-row-align-bottom aipkit_builder_inline_row aipkit_chatbot_model_row">
    <?php if ($show_chatbot_selector) : ?>
        <!-- Chatbot Selection Column -->
        <div class="aipkit_form-group aipkit_form-col aipkit_chatbot_model_col aipkit_chatbot_model_col--bot">
            <label
                class="aipkit_form-label"
                for="aipkit_chatbot_builder_bot_select"
            >
                <span><?php esc_html_e('Chatbot', 'gpt3-ai-content-generator'); ?></span>
            </label>
            <div class="aipkit_input-with-button">
                <select
                    id="aipkit_chatbot_builder_bot_select"
                    name="aipkit_chatbot_builder_bot_select"
                    class="aipkit_form-input aipkit_builder_bot_select_input"
                    <?php echo empty($all_bots_ordered_entries) ? 'disabled' : ''; ?>
                >
                    <?php if (empty($all_bots_ordered_entries)) : ?>
                        <option value="">
                            <?php esc_html_e('No chatbots yet', 'gpt3-ai-content-generator'); ?>
                        </option>
                    <?php else : ?>
                        <option value="__new__">
                            <?php esc_html_e('+ New Bot', 'gpt3-ai-content-generator'); ?>
                        </option>
                        <option value="" disabled>----------</option>
                        <?php foreach ($all_bots_ordered_entries as $bot_entry) : ?>
                            <?php $bot_post = $bot_entry['post']; ?>
                            <option
                                value="<?php echo esc_attr($bot_post->ID); ?>"
                                <?php selected($bot_id, $bot_post->ID); ?>
                            >
                                <?php echo esc_html($bot_post->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    <?php endif; ?>

    <!-- AI Provider Column -->
    <div class="aipkit_form-group aipkit_form-col aipkit_chatbot_model_col aipkit_chatbot_model_col--provider">
        <label
            class="aipkit_form-label"
            for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_provider"
        >
            <?php esc_html_e('Engine', 'gpt3-ai-content-generator'); ?>
        </label>
        <select
            id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_provider"
            name="provider"
            class="aipkit_form-input aipkit_chatbot_provider_select" <?php // JS targets this class?>
            data-aipkit-provider-notice-target="aipkit_provider_notice_chatbot"
        >
            <?php foreach ($providers as $p_value) :
                $disabled = false;
                $label = $p_value;

                if ($p_value === 'Ollama' && !$is_pro) {
                    $disabled = true;
                    $label = __('Ollama (Pro)', 'gpt3-ai-content-generator');
                }
            ?>
                <option
                    value="<?php echo esc_attr($p_value); ?>"
                    <?php selected($saved_provider, $p_value); ?> <?php echo $disabled ? 'disabled' : ''; ?>
                >
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Model Selection Column -->
    <div class="aipkit_form-group aipkit_form-col aipkit_chatbot_model_col aipkit_chatbot_model_col--model">
        <!-- OpenAI Model -->
        <div
            class="aipkit_chatbot_model_field" <?php // JS targets this class?>
            data-provider="OpenAI"
            style="display: <?php echo $saved_provider === 'OpenAI' ? 'block' : 'none'; ?>;"
        >
            <div class="aipkit_input-with-button aipkit_input-with-button--labels aipkit_input-with-button--shortcode"> <?php // NEW WRAPPER?>
                <label
                class="aipkit_form-label aipkit_form-label--status"
                for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_model"
                >
                    <span class="aipkit_model_label_text"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_model_status_slot">
                        <span class="aipkit_model_sync_status" aria-live="polite"></span>
                    </span>
                </label>
                <?php echo $shortcode_label; ?>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openai_model"
                    name="openai_model"
                    class="aipkit_form-input"
                >
                    <?php
                     // $grouped_openai_models now only contains chat models (already filtered if applicable)
                     $foundCurrentOpenAI = false;
                    if (!empty($recommended_openai)) : ?>
                        <optgroup label="<?php echo esc_attr__('Recommended', 'gpt3-ai-content-generator'); ?>">
                            <?php foreach ($recommended_openai as $rec):
                                $rec_id = $rec['id'] ?? '';
                                $rec_name = $rec['name'] ?? $rec_id;
                                if (!$rec_id) {
                                    continue;
                                }
                                if ($rec_id === $saved_model) {
                                    $foundCurrentOpenAI = true;
                                }
                                ?>
                                <option
                                    value="<?php echo esc_attr($rec_id); ?>"
                                    <?php selected($saved_model, $rec_id); ?>
                                >
                                    <?php echo esc_html($rec_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif;
                    if (!empty($grouped_openai_models) && is_array($grouped_openai_models)): ?>
                        <?php foreach ($grouped_openai_models as $groupLabel => $groupItems): ?>
                            <optgroup label="<?php echo esc_attr($groupLabel); ?>">
                                <?php foreach ($groupItems as $m):
                                    $model_id   = $m['id'] ?? '';
                                    $model_name = $m['name'] ?? $model_id;
                                    if (!empty($recommended_openai_lookup[$model_id])) {
                                        continue;
                                    }
                                    if ($model_id === $saved_model) {
                                        $foundCurrentOpenAI = true;
                                    }
                                    ?>
                                    <option
                                        value="<?php echo esc_attr($model_id); ?>"
                                        <?php selected($saved_model, $model_id); ?>
                                    >
                                        <?php echo esc_html($model_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php
                    // If saved model not found OR list is empty
                    // AND the saved model is NOT an OpenAI TTS model (as this dropdown is for CHAT models)
                    if (!$foundCurrentOpenAI && !empty($saved_model) && $saved_provider === 'OpenAI' && strpos($saved_model, 'tts-') !== 0) {
                        echo '<option value="' . esc_attr($saved_model) . '" selected>' . esc_html($saved_model) . '</option>';
                    } elseif (empty($grouped_openai_models) && empty($recommended_openai) && (!$foundCurrentOpenAI || empty($saved_model) || strpos($saved_model, 'tts-') === 0)) {
                        echo '<option value="">'.esc_html__('(Sync models in main AI Settings)', 'gpt3-ai-content-generator').'</option>';
                    }
?>
                </select>
                <?php echo $shortcode_pill; ?>
                <!-- OpenAI Web Search checkbox moved to Features subsection -->
            </div> <?php // END WRAPPER?>
        </div>

        <!-- OpenRouter Model -->
        <div
            class="aipkit_chatbot_model_field"
            data-provider="OpenRouter"
            style="display: <?php echo $saved_provider === 'OpenRouter' ? 'block' : 'none'; ?>;"
        >
             <div class="aipkit_input-with-button aipkit_input-with-button--labels aipkit_input-with-button--shortcode"> <?php // NEW WRAPPER?>
                <label
                    class="aipkit_form-label aipkit_form-label--status"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openrouter_model"
                >
                    <span class="aipkit_model_label_text"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_model_status_slot">
                        <span class="aipkit_model_sync_status" aria-live="polite"></span>
                    </span>
                </label>
                <?php echo $shortcode_label; ?>
               <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_openrouter_model"
                    name="openrouter_model"
                    class="aipkit_form-input"
                >
                    <?php
$foundCurrentOR = false;
if (!empty($recommended_openrouter)) : ?>
                        <optgroup label="<?php echo esc_attr__('Recommended', 'gpt3-ai-content-generator'); ?>">
                            <?php foreach ($recommended_openrouter as $rec):
                                $rec_id = $rec['id'] ?? '';
                                $rec_name = $rec['name'] ?? $rec_id;
                                if (!$rec_id) {
                                    continue;
                                }
                                if ($rec_id === $saved_model) {
                                    $foundCurrentOR = true;
                                }
                                ?>
                                <option
                                    value="<?php echo esc_attr($rec_id); ?>"
                                    <?php selected($saved_model, $rec_id); ?>
                                >
                                    <?php echo esc_html($rec_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif;
if (!empty($openrouter_model_list)) {
    $grouped = [];
    foreach ($openrouter_model_list as $model) {
        if (!empty($model['id']) && !empty($model['name'])) {
            $parts  = explode('/', $model['id']);
            $prefix = strtolower(trim($parts[0]));
            if (!isset($grouped[$prefix])) {
                $grouped[$prefix] = [];
            }
            $grouped[$prefix][] = $model;
        }
    }
    ksort($grouped);
    foreach ($grouped as $prefix => $modelsInGroup): ?>
                            <optgroup label="<?php echo esc_attr(ucfirst($prefix)); ?>">
                                <?php
            usort($modelsInGroup, fn ($a, $b) => strcmp($a['name'], $b['name']));
        foreach ($modelsInGroup as $m):
            if (!empty($recommended_openrouter_lookup[$m['id'] ?? ''])) {
                continue;
            }
            if ($m['id'] === $saved_model) {
                $foundCurrentOR = true;
            } ?>
                                    <option
                                        value="<?php echo esc_attr($m['id']); ?>"
                                        <?php selected($saved_model, $m['id']); ?>
                                    >
                                        <?php echo esc_html($m['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach;
}
if (!$foundCurrentOR && !empty($saved_model) && $saved_provider === 'OpenRouter') { ?>
                        <option value="<?php echo esc_attr($saved_model); ?>" selected><?php echo esc_html($saved_model); ?></option>
                    <?php } elseif (empty($openrouter_model_list) && empty($recommended_openrouter) && empty($saved_model)) { ?>
                        <option value=""><?php esc_html_e('(Sync models in main AI Settings)', 'gpt3-ai-content-generator'); ?></option>
                    <?php } ?>
                </select>
                <?php echo $shortcode_pill; ?>
            </div> <?php // END WRAPPER?>
        </div>

        <!-- Google Model -->
        <div
            class="aipkit_chatbot_model_field"
            data-provider="Google"
            style="display: <?php echo $saved_provider === 'Google' ? 'block' : 'none'; ?>;"
        >
             <div class="aipkit_input-with-button aipkit_input-with-button--labels aipkit_input-with-button--shortcode"> <?php // NEW WRAPPER?>
                <label
                    class="aipkit_form-label aipkit_form-label--status"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_model"
                >
                    <span class="aipkit_model_label_text"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_model_status_slot">
                        <span class="aipkit_model_sync_status" aria-live="polite"></span>
                    </span>
                </label>
                <?php echo $shortcode_label; ?>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_google_model"
                    name="google_model"
                    class="aipkit_form-input"
                >
                     <?php
$foundCurrentGoogle = false;
if (!empty($recommended_google)) : ?>
                        <optgroup label="<?php echo esc_attr__('Recommended', 'gpt3-ai-content-generator'); ?>">
                            <?php foreach ($recommended_google as $rec):
                                $rec_id = $rec['id'] ?? '';
                                $rec_name = $rec['name'] ?? $rec_id;
                                if (!$rec_id) {
                                    continue;
                                }
                                if ($rec_id === $saved_model || $saved_model === 'models/' . $rec_id) {
                                    $foundCurrentGoogle = true;
                                }
                                ?>
                                <option
                                    value="<?php echo esc_attr($rec_id); ?>"
                                    <?php selected($saved_model, $rec_id); ?>
                                >
                                    <?php echo esc_html($rec_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif;
if (!empty($google_model_list)): ?>
                        <?php if (!empty($recommended_google)) : ?>
                            <optgroup label="<?php echo esc_attr__('All models', 'gpt3-ai-content-generator'); ?>">
                        <?php endif; ?>
                        <?php foreach ($google_model_list as $gm):
                            $gId   = $gm['id'] ?? ($gm['name'] ?? '');
                            $gName = $gm['name'] ?? $gId;
                            $selectedValue = $gId;
                            $isSelected = ($saved_model === $selectedValue || $saved_model === 'models/'.$selectedValue);
                            if ($isSelected) {
                                $foundCurrentGoogle = true;
                            }
                            if (!empty($recommended_google_lookup[$selectedValue])) {
                                continue;
                            }
                            ?>
                            <option
                                value="<?php echo esc_attr($selectedValue); ?>"
                                <?php echo $isSelected ? 'selected' : ''; ?>
                            >
                                <?php echo esc_html($gName); ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (!empty($recommended_google)) : ?>
                            </optgroup>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php
                    if (!$foundCurrentGoogle && !empty($saved_model) && $saved_provider === 'Google'): ?>
                         <?php $displayModel = (strpos($saved_model, 'models/') === 0) ? substr($saved_model, 7) : $saved_model; ?>
                        <option value="<?php echo esc_attr($saved_model); ?>" selected><?php echo esc_html($displayModel); ?></option>
                    <?php elseif (empty($google_model_list) && empty($recommended_google) && !$foundCurrentGoogle && empty($saved_model)): ?>
                        <option value=""><?php esc_html_e('(Sync models in main AI Settings)', 'gpt3-ai-content-generator'); ?></option>
                    <?php endif; ?>
                </select>
                <?php echo $shortcode_pill; ?>
                <!-- Google Search Grounding checkbox moved to Features subsection -->
            </div> <?php // END WRAPPER?>
        </div>

        <!-- Claude Model -->
        <div
            class="aipkit_chatbot_model_field"
            data-provider="Claude"
            style="display: <?php echo $saved_provider === 'Claude' ? 'block' : 'none'; ?>;"
        >
             <div class="aipkit_input-with-button aipkit_input-with-button--labels aipkit_input-with-button--shortcode"> <?php // NEW WRAPPER?>
                <label
                    class="aipkit_form-label aipkit_form-label--status"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_claude_model"
                >
                    <span class="aipkit_model_label_text"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_model_status_slot">
                        <span class="aipkit_model_sync_status" aria-live="polite"></span>
                    </span>
                </label>
                <?php echo $shortcode_label; ?>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_claude_model"
                    name="claude_model"
                    class="aipkit_form-input"
                >
                     <?php
$foundCurrentClaude = false;
if (!empty($recommended_claude)) : ?>
                        <optgroup label="<?php echo esc_attr__('Recommended', 'gpt3-ai-content-generator'); ?>">
                            <?php foreach ($recommended_claude as $rec):
                                $rec_id = $rec['id'] ?? '';
                                $rec_name = $rec['name'] ?? $rec_id;
                                if (!$rec_id) {
                                    continue;
                                }
                                if ($rec_id === $saved_model) {
                                    $foundCurrentClaude = true;
                                }
                                ?>
                                <option
                                    value="<?php echo esc_attr($rec_id); ?>"
                                    <?php selected($saved_model, $rec_id); ?>
                                >
                                    <?php echo esc_html($rec_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif;
if (!empty($claude_model_list)): ?>
                        <?php if (!empty($recommended_claude)) : ?>
                            <optgroup label="<?php echo esc_attr__('All models', 'gpt3-ai-content-generator'); ?>">
                        <?php endif; ?>
                        <?php foreach ($claude_model_list as $model):
                            $model_id = $model['id'] ?? '';
                            $model_name = $model['name'] ?? $model_id;
                            if (!$model_id || !empty($recommended_claude_lookup[$model_id])) {
                                continue;
                            }
                            if ($model_id === $saved_model) {
                                $foundCurrentClaude = true;
                            }
                            ?>
                            <option
                                value="<?php echo esc_attr($model_id); ?>"
                                <?php selected($saved_model, $model_id); ?>
                            >
                                <?php echo esc_html($model_name); ?>
                            </option>
                        <?php endforeach; ?>
                        <?php if (!empty($recommended_claude)) : ?>
                            </optgroup>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!$foundCurrentClaude && !empty($saved_model) && $saved_provider === 'Claude'): ?>
                        <option value="<?php echo esc_attr($saved_model); ?>" selected><?php echo esc_html($saved_model); ?></option>
                    <?php elseif (empty($claude_model_list) && empty($recommended_claude) && !$foundCurrentClaude && empty($saved_model)): ?>
                        <option value=""><?php esc_html_e('(Sync models in main AI Settings)', 'gpt3-ai-content-generator'); ?></option>
                    <?php endif; ?>
                </select>
                <?php echo $shortcode_pill; ?>
            </div> <?php // END WRAPPER?>
        </div>

        <!-- Azure Deployment Only -->
        <div
            class="aipkit_chatbot_model_field"
            data-provider="Azure"
            style="display: <?php echo $saved_provider === 'Azure' ? 'block' : 'none'; ?>;"
        >
             <div class="aipkit_input-with-button aipkit_input-with-button--labels aipkit_input-with-button--shortcode"> <?php // NEW WRAPPER?>
                <label
                    class="aipkit_form-label aipkit_form-label--status"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_azure_deployment"
                >
                    <span class="aipkit_model_label_text"><?php esc_html_e('Deployment', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_model_status_slot">
                        <span class="aipkit_model_sync_status" aria-live="polite"></span>
                    </span>
                </label>
                <?php echo $shortcode_label; ?>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_azure_deployment"
                    name="azure_deployment"
                    class="aipkit_form-input"
                >
                    <?php
                    $foundOldAzure = false;
if (is_array($azure_deployment_list) && !empty($azure_deployment_list)) {
    foreach ($azure_deployment_list as $dep) {
        $dep_id   = $dep['id'] ?? '';
        $dep_name = $dep['name'] ?? $dep_id;
        $label = $dep_id;
        if (!empty($dep_name) && $dep_name !== $dep_id) {
            $label .= ' (model: ' . $dep_name . ')';
        }
        $selected = selected($saved_azure_deployment, $dep_id, false);
        if (!empty($selected)) {
            $foundOldAzure = true;
        }
        echo '<option value="' . esc_attr($dep_id) . '" ' . esc_attr($selected) . '>' . esc_html($label) . '</option>';
    }
}
if (!$foundOldAzure && !empty($saved_azure_deployment)) {
    echo '<option value="'.esc_attr($saved_azure_deployment).'" selected>'.esc_html($saved_azure_deployment . ($foundOldAzure === false && !empty($azure_deployment_list) ? '' : '')).'</option>';
} elseif (empty($saved_azure_deployment) && empty($azure_deployment_list)) {
    echo '<option value="">'.esc_html__('(Sync deployments in main AI Settings)', 'gpt3-ai-content-generator').'</option>';
}
?>
                </select>
                <?php echo $shortcode_pill; ?>
            </div> <?php // END WRAPPER?>
        </div>

        <!-- DeepSeek Model -->
        <div
            class="aipkit_chatbot_model_field"
            data-provider="DeepSeek"
            style="display: <?php echo $saved_provider === 'DeepSeek' ? 'block' : 'none'; ?>;"
        >
             <div class="aipkit_input-with-button aipkit_input-with-button--labels aipkit_input-with-button--shortcode"> <?php // NEW WRAPPER?>
                <label
                    class="aipkit_form-label aipkit_form-label--status"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_deepseek_model"
                >
                    <span class="aipkit_model_label_text"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_model_status_slot">
                        <span class="aipkit_model_sync_status" aria-live="polite"></span>
                    </span>
                </label>
                <?php echo $shortcode_label; ?>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_deepseek_model"
                    name="deepseek_model"
                    class="aipkit_form-input"
                >
                    <?php
 $foundCurrentDeepSeek = false;
if (!empty($deepseek_model_list)): ?>
                        <?php foreach ($deepseek_model_list as $m):
                            $model_id   = $m['id'] ?? '';
                            $model_name = $m['name'] ?? $model_id;
                            if ($model_id === $saved_model) {
                                $foundCurrentDeepSeek = true;
                            }
                            ?>
                            <option
                                value="<?php echo esc_attr($model_id); ?>"
                                <?php selected($saved_model, $model_id); ?>
                            >
                                <?php echo esc_html($model_name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!$foundCurrentDeepSeek && !empty($saved_model) && $saved_provider === 'DeepSeek'): ?>
                        <option value="<?php echo esc_attr($saved_model); ?>" selected><?php echo esc_html($saved_model); ?></option>
                    <?php elseif (empty($deepseek_model_list) && !$foundCurrentDeepSeek && empty($saved_model)): ?>
                        <option value=""><?php esc_html_e('(Sync models in main AI Settings)', 'gpt3-ai-content-generator'); ?></option>
                    <?php endif; ?>
                </select>
                <?php echo $shortcode_pill; ?>
            </div> <?php // END WRAPPER?>
        </div>

        <!-- Ollama Model -->
        <div
            class="aipkit_chatbot_model_field"
            data-provider="Ollama"
            style="display: <?php echo $saved_provider === 'Ollama' ? 'block' : 'none'; ?>;"
        >
             <div class="aipkit_input-with-button aipkit_input-with-button--labels aipkit_input-with-button--shortcode"> <?php // NEW WRAPPER?>
                <label
                    class="aipkit_form-label aipkit_form-label--status"
                    for="aipkit_bot_<?php echo esc_attr($bot_id); ?>_ollama_model"
                >
                    <span class="aipkit_model_label_text"><?php esc_html_e('Model', 'gpt3-ai-content-generator'); ?></span>
                    <span class="aipkit_model_status_slot">
                        <span class="aipkit_model_sync_status" aria-live="polite"></span>
                    </span>
                </label>
                <?php echo $shortcode_label; ?>
                <select
                    id="aipkit_bot_<?php echo esc_attr($bot_id); ?>_ollama_model"
                    name="ollama_model"
                    class="aipkit_form-input"
                >
                    <?php
                    $foundCurrentOllama = false;
                    if (!empty($ollama_model_list)): ?>
                        <?php foreach ($ollama_model_list as $m):
                            $model_id   = $m['id'] ?? '';
                            $model_name = $m['name'] ?? $model_id;
                            if ($model_id === $saved_model) {
                                $foundCurrentOllama = true;
                            }
                            ?>
                            <option
                                value="<?php echo esc_attr($model_id); ?>"
                                <?php selected($saved_model, $model_id); ?>
                            >
                                <?php echo esc_html($model_name); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php if (!$foundCurrentOllama && !empty($saved_model) && $saved_provider === 'Ollama'): ?>
                        <option value="<?php echo esc_attr($saved_model); ?>" selected><?php echo esc_html($saved_model); ?></option>
                    <?php elseif (empty($ollama_model_list) && !$foundCurrentOllama && empty($saved_model)): ?>
                        <option value=""><?php esc_html_e('(Sync models in main AI Settings)', 'gpt3-ai-content-generator'); ?></option>
                    <?php endif; ?>
                </select>
                <?php echo $shortcode_pill; ?>
            </div> <?php // END WRAPPER?>
        </div>

    </div><!-- /Model Selection Column -->
</div> <!-- /Row container -->
