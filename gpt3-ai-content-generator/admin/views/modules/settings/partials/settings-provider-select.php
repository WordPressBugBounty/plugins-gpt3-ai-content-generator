<?php
/**
 * Partial: AI Provider Selection Dropdown
 */
if (!defined('ABSPATH')) exit;

// Variables required: $current_provider, $providers, $deepseek_addon_active
// No outer .aipkit_form-group div here, it's provided by the parent (settings/index.php)
?>
<label
    class="aipkit_form-label"
    for="aipkit_provider"
>
    <?php echo esc_html__('Engine', 'gpt3-ai-content-generator'); ?>
</label>
<select
    id="aipkit_provider"
    name="provider"
    class="aipkit_form-input aipkit_autosave_trigger"
>
    <?php foreach ($providers as $p_value) :
        // Conditionally skip DeepSeek if addon not active
        if ($p_value === 'DeepSeek' && !$deepseek_addon_active) continue;
    ?>
    <option value="<?php echo esc_attr($p_value); ?>" <?php selected($current_provider, $p_value); ?>>
        <?php echo esc_html($p_value); ?>
    </option>
    <?php endforeach; ?>
</select>