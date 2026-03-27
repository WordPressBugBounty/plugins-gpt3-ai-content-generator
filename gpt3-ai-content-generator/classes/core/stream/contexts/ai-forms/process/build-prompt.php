<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/stream/contexts/ai-forms/process/build-prompt.php
// Status: MODIFIED

namespace WPAICG\Core\Stream\Contexts\AIForms\Process;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds the final prompt string from the template and submitted data.
 *
 * @param array $form_config The configuration of the form.
 * @param array $submitted_fields The sanitized submitted data.
 * @return string|WP_Error The final prompt string or WP_Error if template is missing.
 */
function build_prompt_logic(array $form_config, array $submitted_fields): string|WP_Error
{
    $prompt_template = $form_config['prompt_template'] ?? '';
    $form_structure = $form_config['structure'] ?? [];

    if (empty($prompt_template)) {
        return new WP_Error('missing_template', __('Form prompt template is not configured.', 'gpt3-ai-content-generator'), ['status' => 500]);
    }

    $final_prompt = $prompt_template;

    if (!empty($form_structure) && is_array($form_structure)) {
        foreach ($form_structure as $row) {
            if (empty($row['columns']) || !is_array($row['columns'])) {
                continue;
            }
            foreach ($row['columns'] as $column) {
                if (empty($column['elements']) || !is_array($column['elements'])) {
                    continue;
                }
                foreach ($column['elements'] as $element) {
                    $field_id = $element['fieldId'] ?? null;
                    if (!$field_id) { // Skip if element has no fieldId
                        continue;
                    }

                    $placeholder = '{' . $field_id . '}';
                    $value_to_substitute = resolve_submitted_field_value_logic($element, $submitted_fields);

                    // Replace placeholder in the prompt with the determined value.
                    // This will also replace with '' if the field wasn't submitted, effectively removing the placeholder.
                    $final_prompt = str_replace($placeholder, $value_to_substitute, $final_prompt);
                }
            }
        }
    }

    // Handle the legacy/simple case where a single 'user_input' is expected
    // This is less likely with the new structure but good for backward compatibility
    if (empty($form_structure) && isset($submitted_fields['user_input'])) {
        $final_prompt = str_replace('{user_input}', $submitted_fields['user_input'], $final_prompt);
    }

    return $final_prompt;
}

/**
 * Builds a moderation-safe text string from submitted field values only.
 *
 * @param array $form_config
 * @param array $submitted_fields
 * @return string
 */
function build_moderation_text_logic(array $form_config, array $submitted_fields): string
{
    $form_structure = $form_config['structure'] ?? [];
    $moderation_segments = [];

    if (!empty($form_structure) && is_array($form_structure)) {
        foreach ($form_structure as $row) {
            if (empty($row['columns']) || !is_array($row['columns'])) {
                continue;
            }
            foreach ($row['columns'] as $column) {
                if (empty($column['elements']) || !is_array($column['elements'])) {
                    continue;
                }
                foreach ($column['elements'] as $element) {
                    $field_id = $element['fieldId'] ?? null;
                    if (!$field_id) {
                        continue;
                    }

                    $resolved_value = trim(resolve_submitted_field_value_logic($element, $submitted_fields));
                    if ($resolved_value !== '') {
                        $moderation_segments[] = $resolved_value;
                    }
                }
            }
        }
    }

    if (empty($moderation_segments)) {
        collect_scalar_values_logic($submitted_fields, $moderation_segments);
    }

    return implode("\n", array_filter(array_map('trim', $moderation_segments), 'strlen'));
}

/**
 * Resolves one submitted field into the display string used in prompts and moderation.
 *
 * @param array $element
 * @param array $submitted_fields
 * @return string
 */
function resolve_submitted_field_value_logic(array $element, array $submitted_fields): string
{
    $field_id = $element['fieldId'] ?? null;
    if (!$field_id || !array_key_exists($field_id, $submitted_fields)) {
        return '';
    }

    $submitted_value = $submitted_fields[$field_id];
    $element_type = $element['type'] ?? 'text-input';

    switch ($element_type) {
        case 'select':
        case 'radio-button':
            $options = $element['options'] ?? [];
            foreach ($options as $option) {
                if (isset($option['value']) && $option['value'] == $submitted_value) {
                    return (string) ($option['text'] ?? $submitted_value);
                }
            }

            return is_scalar($submitted_value) ? (string) $submitted_value : '';

        case 'checkbox':
            $submitted_values_array = [];
            if (is_array($submitted_value)) {
                $submitted_values_array = $submitted_value;
            } elseif (is_string($submitted_value) && $submitted_value !== '') {
                $submitted_values_array = array_map('trim', explode(',', $submitted_value));
            }

            if (empty($submitted_values_array)) {
                return '';
            }

            $labels_to_substitute = [];
            $options = $element['options'] ?? [];
            foreach ($submitted_values_array as $value) {
                $resolved_label = is_scalar($value) ? (string) $value : '';
                foreach ($options as $option) {
                    if (isset($option['value']) && $option['value'] == $value) {
                        $resolved_label = (string) ($option['text'] ?? $value);
                        break;
                    }
                }
                if ($resolved_label !== '') {
                    $labels_to_substitute[] = $resolved_label;
                }
            }

            return implode(', ', $labels_to_substitute);

        default:
            if (is_array($submitted_value)) {
                $scalar_values = [];
                collect_scalar_values_logic($submitted_value, $scalar_values);
                return implode(', ', $scalar_values);
            }

            return is_scalar($submitted_value) ? (string) $submitted_value : '';
    }
}

/**
 * Recursively collects scalar values from nested arrays.
 *
 * @param mixed $value
 * @param array<int, string> $segments
 * @return void
 */
function collect_scalar_values_logic($value, array &$segments): void
{
    if (is_array($value)) {
        foreach ($value as $item) {
            collect_scalar_values_logic($item, $segments);
        }
        return;
    }

    if (is_scalar($value)) {
        $scalar_value = trim((string) $value);
        if ($scalar_value !== '') {
            $segments[] = $scalar_value;
        }
    }
}
