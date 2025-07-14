<?php

namespace WPAICG\AIForms\Storage\Methods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Logic for saving AI Form settings.
 *
 * @param \WPAICG\AIForms\Storage\AIPKit_AI_Form_Storage $storageInstance The instance of the storage class.
 * @param int $form_id The ID of the form CPT.
 * @param array $settings An array containing settings.
 * @return bool True on success, false on failure.
 */
function save_form_settings_logic(\WPAICG\AIForms\Storage\AIPKit_AI_Form_Storage $storageInstance, int $form_id, array $settings): bool
{
    if (isset($settings['prompt_template'])) {
        update_post_meta($form_id, '_aipkit_ai_form_prompt_template', sanitize_textarea_field($settings['prompt_template']));
    }
    if (isset($settings['form_structure'])) {
        $structure_json = $settings['form_structure'];
        $decoded_structure = json_decode($structure_json, true);
        if (is_array($decoded_structure)) {
            update_post_meta($form_id, '_aipkit_ai_form_structure', wp_kses_post($structure_json));
        } else {
            error_log("AIPKit AI Form Storage: Invalid JSON provided for form_structure for form ID {$form_id}.");
        }
    }
    if (isset($settings['ai_provider'])) {
        update_post_meta($form_id, '_aipkit_ai_form_ai_provider', sanitize_text_field($settings['ai_provider']));
    }
    if (isset($settings['ai_model'])) {
        update_post_meta($form_id, '_aipkit_ai_form_ai_model', sanitize_text_field($settings['ai_model']));
    }
    if (isset($settings['temperature'])) {
        update_post_meta($form_id, '_aipkit_ai_form_temperature', sanitize_text_field($settings['temperature']));
    }
    if (isset($settings['max_tokens'])) {
        update_post_meta($form_id, '_aipkit_ai_form_max_tokens', absint($settings['max_tokens']));
    }

    // --- Save Vector Settings ---
    if (isset($settings['enable_vector_store'])) {
        update_post_meta($form_id, '_aipkit_ai_form_enable_vector_store', $settings['enable_vector_store'] === '1' ? '1' : '0');
    }
    if (isset($settings['vector_store_provider'])) {
        update_post_meta($form_id, '_aipkit_ai_form_vector_store_provider', sanitize_key($settings['vector_store_provider']));
    }
    if (isset($settings['openai_vector_store_ids'])) {
        $sanitized_ids = is_array($settings['openai_vector_store_ids']) ? array_map('sanitize_text_field', $settings['openai_vector_store_ids']) : [];
        update_post_meta($form_id, '_aipkit_ai_form_openai_vector_store_ids', wp_json_encode(array_values(array_unique($sanitized_ids))));
    }
    if (isset($settings['pinecone_index_name'])) {
        update_post_meta($form_id, '_aipkit_ai_form_pinecone_index_name', sanitize_text_field($settings['pinecone_index_name']));
    }
    if (isset($settings['qdrant_collection_name'])) {
        update_post_meta($form_id, '_aipkit_ai_form_qdrant_collection_name', sanitize_text_field($settings['qdrant_collection_name']));
    }
    if (isset($settings['vector_embedding_provider'])) {
        update_post_meta($form_id, '_aipkit_ai_form_vector_embedding_provider', sanitize_key($settings['vector_embedding_provider']));
    }
    if (isset($settings['vector_embedding_model'])) {
        update_post_meta($form_id, '_aipkit_ai_form_vector_embedding_model', sanitize_text_field($settings['vector_embedding_model']));
    }
    if (isset($settings['vector_store_top_k'])) {
        update_post_meta($form_id, '_aipkit_ai_form_vector_store_top_k', absint($settings['vector_store_top_k']));
    }

    // --- Save Labels ---
    if (isset($settings['labels']) && is_array($settings['labels'])) {
        $sanitized_labels = [];
        $allowed_keys = ['generate_button', 'stop_button', 'download_button', 'save_button', 'copy_button', 'provider_label', 'model_label'];
        foreach ($settings['labels'] as $key => $value) {
            if (in_array($key, $allowed_keys, true)) {
                $sanitized_labels[$key] = sanitize_text_field($value);
            }
        }
        // FIX: Use JSON_UNESCAPED_UNICODE to prevent encoding issues with special characters.
        $json_to_save = wp_json_encode($sanitized_labels, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        update_post_meta($form_id, '_aipkit_ai_form_labels', $json_to_save);
    }

    return true;
}
