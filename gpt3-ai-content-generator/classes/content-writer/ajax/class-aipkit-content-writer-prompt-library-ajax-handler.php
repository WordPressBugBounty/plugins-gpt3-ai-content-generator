<?php

namespace WPAICG\ContentWriter\Ajax;

use WPAICG\ContentWriter\AIPKit_Content_Writer_Prompt_Library_Manager;
use WPAICG\Dashboard\Ajax\BaseDashboardAjaxHandler;
use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AJAX handler for global prompt library CRUD endpoints.
 */
class AIPKit_Content_Writer_Prompt_Library_Ajax_Handler extends BaseDashboardAjaxHandler
{
    public const NONCE_ACTION = 'aipkit_nonce';

    /**
     * Modules allowed to manage prompt presets.
     *
     * @var array<int, string>
     */
    private const ALLOWED_MODULES = [
        'content-writer',
        'autogpt',
        'ai-forms',
        'ai_post_enhancer',
        'chatbot',
    ];

    private ?AIPKit_Content_Writer_Prompt_Library_Manager $prompt_library_manager = null;

    public function __construct()
    {
        if (class_exists(AIPKit_Content_Writer_Prompt_Library_Manager::class)) {
            $this->prompt_library_manager = new AIPKit_Content_Writer_Prompt_Library_Manager();
        }
    }

    public function ajax_list_prompt_library()
    {
        $permission_check = $this->check_permissions();
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        if (!$this->prompt_library_manager) {
            $this->send_wp_error(new WP_Error(
                'manager_missing',
                __('Prompt library manager is unavailable.', 'gpt3-ai-content-generator'),
                ['status' => 500]
            ));
            return;
        }

        $prompt_type = '';
        if (isset($_POST['prompt_type'])) {
            $prompt_type = sanitize_key((string) wp_unslash($_POST['prompt_type']));
        }

        $include_builtin = $this->read_post_bool('include_builtin', true);
        $include_custom = $this->read_post_bool('include_custom', true);

        $result = $this->prompt_library_manager->get_library_entries(
            $prompt_type !== '' ? $prompt_type : null,
            $include_builtin,
            $include_custom
        );
        if (is_wp_error($result)) {
            $this->send_wp_error($result);
            return;
        }

        wp_send_json_success($result);
    }

    public function ajax_create_prompt_library_item()
    {
        $permission_check = $this->check_permissions();
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        if (!$this->prompt_library_manager) {
            $this->send_wp_error(new WP_Error(
                'manager_missing',
                __('Prompt library manager is unavailable.', 'gpt3-ai-content-generator'),
                ['status' => 500]
            ));
            return;
        }

        $prompt_type = isset($_POST['prompt_type']) ? sanitize_key((string) wp_unslash($_POST['prompt_type'])) : '';
        $label = isset($_POST['label']) ? sanitize_text_field((string) wp_unslash($_POST['label'])) : '';
        $prompt = isset($_POST['prompt']) ? sanitize_textarea_field((string) wp_unslash($_POST['prompt'])) : '';

        $item = $this->prompt_library_manager->create_custom_prompt(
            $prompt_type,
            $label,
            $prompt,
            get_current_user_id()
        );
        if (is_wp_error($item)) {
            $this->send_wp_error($item);
            return;
        }

        $prompt_type_data = $this->prompt_library_manager->get_library_entries((string) ($item['type'] ?? ''), true, true);
        if (is_wp_error($prompt_type_data)) {
            $this->send_wp_error($prompt_type_data);
            return;
        }

        wp_send_json_success([
            'item'       => $item,
            'type_data'  => $prompt_type_data,
        ]);
    }

    public function ajax_update_prompt_library_item()
    {
        $permission_check = $this->check_permissions();
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        if (!$this->prompt_library_manager) {
            $this->send_wp_error(new WP_Error(
                'manager_missing',
                __('Prompt library manager is unavailable.', 'gpt3-ai-content-generator'),
                ['status' => 500]
            ));
            return;
        }

        $prompt_id = isset($_POST['prompt_id']) ? sanitize_key((string) wp_unslash($_POST['prompt_id'])) : '';
        $updates = [];

        if (array_key_exists('prompt_type', $_POST)) {
            $updates['type'] = sanitize_key((string) wp_unslash($_POST['prompt_type']));
        }
        if (array_key_exists('label', $_POST)) {
            $updates['label'] = sanitize_text_field((string) wp_unslash($_POST['label']));
        }
        if (array_key_exists('prompt', $_POST)) {
            $updates['prompt'] = sanitize_textarea_field((string) wp_unslash($_POST['prompt']));
        }

        $item = $this->prompt_library_manager->update_custom_prompt($prompt_id, $updates);
        if (is_wp_error($item)) {
            $this->send_wp_error($item);
            return;
        }

        $prompt_type_data = $this->prompt_library_manager->get_library_entries((string) ($item['type'] ?? ''), true, true);
        if (is_wp_error($prompt_type_data)) {
            $this->send_wp_error($prompt_type_data);
            return;
        }

        wp_send_json_success([
            'item'      => $item,
            'type_data' => $prompt_type_data,
        ]);
    }

    public function ajax_delete_prompt_library_item()
    {
        $permission_check = $this->check_permissions();
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        if (!$this->prompt_library_manager) {
            $this->send_wp_error(new WP_Error(
                'manager_missing',
                __('Prompt library manager is unavailable.', 'gpt3-ai-content-generator'),
                ['status' => 500]
            ));
            return;
        }

        $prompt_id = isset($_POST['prompt_id']) ? sanitize_key((string) wp_unslash($_POST['prompt_id'])) : '';
        $prompt_type = isset($_POST['prompt_type']) ? sanitize_key((string) wp_unslash($_POST['prompt_type'])) : '';

        $deleted = $this->prompt_library_manager->delete_custom_prompt($prompt_id);
        if (is_wp_error($deleted)) {
            $this->send_wp_error($deleted);
            return;
        }

        $response = [
            'prompt_id' => $prompt_id,
            'deleted'   => true,
        ];

        if ($prompt_type !== '') {
            $prompt_type_data = $this->prompt_library_manager->get_library_entries($prompt_type, true, true);
            if (!is_wp_error($prompt_type_data)) {
                $response['type_data'] = $prompt_type_data;
            }
        }

        wp_send_json_success($response);
    }

    private function check_permissions(): bool|WP_Error
    {
        return $this->check_any_module_access_permissions(self::ALLOWED_MODULES, self::NONCE_ACTION);
    }

    private function read_post_bool(string $key, bool $default): bool
    {
        if (!array_key_exists($key, $_POST)) {
            return $default;
        }

        $value = wp_unslash($_POST[$key]);
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return ((int) $value) === 1;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '') {
            return $default;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}

