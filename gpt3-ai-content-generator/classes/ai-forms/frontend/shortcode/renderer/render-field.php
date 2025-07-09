<?php

// File: classes/ai-forms/frontend/shortcode/renderer/render-field.php
// Status: MODIFIED

namespace WPAICG\AIForms\Frontend\Shortcode\Renderer;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Renders the HTML for a single form field based on its configuration.
 *
 * @param array $element The configuration array for the form element.
 * @param int $form_id The ID of the parent form.
 * @return void Echos the HTML for the field.
 */
function render_field_logic(array $element, int $form_id): void
{
    $field_id_attr = 'aipkit_form_field_' . esc_attr($form_id) . '_' . esc_attr($element['fieldId']);
    $field_name_attr = 'aipkit_form_field[' . esc_attr($element['fieldId']) . ']';
    $required_attr = !empty($element['required']) ? 'required' : '';
    $help_text = $element['helpText'] ?? '';

    switch ($element['type']) {
        case 'text-input':
        case 'textarea':
        case 'select':
        case 'checkbox':
            echo '<div class="aipkit_form-group">';
            echo '<label for="' . esc_attr($field_id_attr) . '" class="aipkit_form-label">';
            if ($element['type'] !== 'checkbox') { // Label is separate for most
                echo esc_html($element['label']);
                if (!empty($element['required'])) {
                    echo ' <span class="aipkit-required-indicator" aria-hidden="true">*</span>';
                }
            }
            echo '</label>';
            switch ($element['type']) {
                case 'text-input':
                    echo '<input type="text" id="' . esc_attr($field_id_attr) . '" name="' . esc_attr($field_name_attr) . '" class="aipkit_form-input" placeholder="' . esc_attr($element['placeholder'] ?? '') . '" ' . esc_attr($required_attr) . '>';
                    break;
                case 'textarea':
                    echo '<textarea id="' . esc_attr($field_id_attr) . '" name="' . esc_attr($field_name_attr) . '" class="aipkit_form-input" rows="4" placeholder="' . esc_attr($element['placeholder'] ?? '') . '" ' . esc_attr($required_attr) . '></textarea>';
                    break;
                case 'select':
                    echo '<select id="' . esc_attr($field_id_attr) . '" name="' . esc_attr($field_name_attr) . '" class="aipkit_form-input" ' . esc_attr($required_attr) . '>';
                    if (!empty($element['placeholder'])) {
                        echo '<option value="">' . esc_html($element['placeholder']) . '</option>';
                    }
                    if (!empty($element['options']) && is_array($element['options'])) {
                        foreach ($element['options'] as $option) {
                            echo '<option value="' . esc_attr($option['value']) . '">' . esc_html($option['text']) . '</option>';
                        }
                    }
                    echo '</select>';
                    break;
                case 'checkbox':
                    echo '<label for="' . esc_attr($field_id_attr) . '" class="aipkit_checkbox-label">'; // Re-wrap label for checkbox
                    echo '<input type="checkbox" id="' . esc_attr($field_id_attr) . '" name="' . esc_attr($field_name_attr) . '" value="1" class="aipkit_form-input-checkbox" ' . esc_attr($required_attr) . '>';
                    echo '<span>' . esc_html($element['label']) . (!empty($element['required']) ? ' <span class="aipkit-required-indicator" aria-hidden="true">*</span>' : '') . '</span>';
                    echo '</label>';
                    break;
            }
            if (!empty($help_text)) {
                echo '<p class="aipkit_form-help">' . wp_kses_post($help_text) . '</p>';
            }
            echo '</div>'; // .aipkit_form-group
            break;

        case 'radio-button':
            echo '<fieldset class="aipkit_form-group aipkit-radio-group">';
            echo '<legend class="aipkit_form-label">' . esc_html($element['label']);
            if (!empty($element['required'])) {
                echo ' <span class="aipkit-required-indicator" aria-hidden="true">*</span>';
            }
            echo '</legend>';
            if (!empty($element['options']) && is_array($element['options'])) {
                foreach ($element['options'] as $index => $option) {
                    $radio_id = esc_attr($field_id_attr . '_' . $index);
                    echo '<div class="aipkit-radio-item">';
                    echo '<input type="radio" id="' . $radio_id . '" name="' . esc_attr($field_name_attr) . '" value="' . esc_attr($option['value']) . '" ' . esc_attr($required_attr) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: $radio_id is already escaped at its definition.
                    echo '<label for="' . $radio_id . '">' . esc_html($option['text']) . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Reason: $radio_id is already escaped at its definition.
                    echo '</div>';
                }
            }
            if (!empty($help_text)) {
                echo '<p class="aipkit_form-help">' . wp_kses_post($help_text) . '</p>';
            }
            echo '</fieldset>';
            break;

        case 'file-upload':
            $is_pro = class_exists('\WPAICG\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
            if ($is_pro) {
                $upload_nonce = wp_create_nonce('aipkit_ai_form_upload_nonce');
                echo '<div class="aipkit_form-group aipkit_form-group-file-upload" data-nonce="' . esc_attr($upload_nonce) . '">';
                echo '<label for="' . esc_attr($field_id_attr) . '" class="aipkit_form-label">';
                echo esc_html($element['label']);
                if (!empty($element['required'])) {
                    echo ' <span class="aipkit-required-indicator" aria-hidden="true">*</span>';
                }
                echo '</label>';

                echo '<input type="file" id="' . esc_attr($field_id_attr) . '" class="aipkit_form-input aipkit-file-upload-input" ' . ($required_attr ? 'data-is-required="true"' : '') . ' data-field-id="' . esc_attr($element['fieldId']) . '" accept=".txt,.pdf">';

                echo '<input type="hidden" name="' . esc_attr($field_name_attr) . '" class="aipkit-file-hidden-content" ' . esc_attr($required_attr) . '>';

                echo '<div class="aipkit-file-status-wrapper" style="display:none;">';
                echo '<div class="aipkit-file-upload-status"></div>';
                echo '<button type="button" class="aipkit_btn aipkit_btn-icon aipkit-file-remove-btn" style="display:none;" title="' . esc_attr__('Remove file', 'gpt3-ai-content-generator') . '"><span class="dashicons dashicons-dismiss"></span></button>';
                echo '</div>';

                if (!empty($help_text)) {
                    echo '<p class="aipkit_form-help">' . wp_kses_post($help_text) . '</p>';
                }
                echo '</div>';
            } else {
                echo '<div class="aipkit_form-group">';
                echo '<label class="aipkit_form-label">' . esc_html($element['label']) . '</label>';
                echo '<p class="aipkit_form-help"><em>' . esc_html__('File upload is a Pro feature.', 'gpt3-ai-content-generator') . '</em></p>';
                echo '</div>';
            }
            break;
    }
}
