<?php

namespace WPAICG\PostEnhancer\Ajax;

use WPAICG\Dashboard\Ajax\BaseDashboardAjaxHandler;
use WPAICG\AIPKit_Role_Manager;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles AJAX requests for managing Post Enhancer custom actions.
 */
class AIPKit_Enhancer_Actions_Ajax_Handler extends BaseDashboardAjaxHandler
{
    private const OPTION_NAME = 'aipkit_enhancer_actions';
    public const MODULE_SLUG = 'ai_post_enhancer';
    public const MAX_ACTIONS = 20;

    /**
     * Get the default set of actions.
     * @return array
     */
    public function get_default_actions_public(): array
    {
        return [
            [
                'id' => 'rewrite-' . wp_generate_uuid4(),
                'label' => __('Rewrite', 'gpt3-ai-content-generator'),
                'prompt' => __('Rewrite this to improve clarity and engagement: "%s"', 'gpt3-ai-content-generator'),
                'is_default' => true
            ],
            [
                'id' => 'expand-' . wp_generate_uuid4(),
                'label' => __('Expand', 'gpt3-ai-content-generator'),
                'prompt' => __('Expand on the following point: "%s"', 'gpt3-ai-content-generator'),
                'is_default' => true
            ],
            [
                'id' => 'fix_grammar-' . wp_generate_uuid4(),
                'label' => __('Fix Grammar & Spelling', 'gpt3-ai-content-generator'),
                'prompt' => __('Correct any spelling and grammar mistakes in the following text: "%s"', 'gpt3-ai-content-generator'),
                'is_default' => true
            ],
        ];
    }

    /**
     * AJAX handler to get all custom and default actions.
     */
    public function ajax_get_actions(): void
    {
        $permission_check = $this->check_module_access_permissions(self::MODULE_SLUG, 'aipkit_enhancer_actions_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        $actions = get_option(self::OPTION_NAME);
        if ($actions === false || !is_array($actions)) {
            $actions = $this->get_default_actions_public();
        }

        wp_send_json_success(['actions' => $actions]);
    }

    /**
     * AJAX handler to save or update an action.
     */
    public function ajax_save_action(): void
    {
        $permission_check = $this->check_module_access_permissions(self::MODULE_SLUG, 'aipkit_enhancer_actions_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        $action_id = isset($_POST['id']) && !empty($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : null;
        $label = isset($_POST['label']) ? sanitize_text_field(wp_unslash($_POST['label'])) : '';
        $prompt = isset($_POST['prompt']) ? sanitize_textarea_field(wp_unslash($_POST['prompt'])) : '';

        if (empty($label) || empty($prompt)) {
            $this->send_wp_error(new WP_Error('missing_data', __('Label and prompt are required.', 'gpt3-ai-content-generator')));
            return;
        }

        $actions = get_option(self::OPTION_NAME, $this->get_default_actions_public());
        if (!is_array($actions)) {
            $actions = $this->get_default_actions_public(); // Fallback if option is corrupted
        }

        $found = false;
        if ($action_id && strpos($action_id, 'new-') !== 0) { // It's an existing action
            foreach ($actions as &$action) {
                if (isset($action['id']) && $action['id'] === $action_id) {
                    if (isset($action['is_default']) && $action['is_default']) {
                        $this->send_wp_error(new WP_Error('cannot_edit_default', __('Default actions cannot be modified.', 'gpt3-ai-content-generator')));
                        return;
                    }
                    $action['label'] = $label;
                    $action['prompt'] = $prompt;
                    $found = true;
                    break;
                }
            }
            unset($action);
        }

        $saved_action = null;
        if (!$found) {
            // It's a new action
            if (count($actions) >= self::MAX_ACTIONS) {
                $this->send_wp_error(new WP_Error('limit_reached', __('You have reached the maximum of 20 actions.', 'gpt3-ai-content-generator')));
                return;
            }
            $new_action = [
                'id' => 'custom-' . wp_generate_uuid4(),
                'label' => $label,
                'prompt' => $prompt,
                'is_default' => false
            ];
            $actions[] = $new_action;
            $saved_action = $new_action;
        } else {
            $saved_action_array = array_filter($actions, function ($a) use ($action_id) {
                return isset($a['id']) && $a['id'] === $action_id;
            });
            $saved_action = reset($saved_action_array);
        }

        update_option(self::OPTION_NAME, $actions, 'no');
        wp_send_json_success([
            'message' => __('Action saved successfully.', 'gpt3-ai-content-generator'),
            'action' => $saved_action
        ]);
    }

    /**
     * AJAX handler to delete an action.
     */
    public function ajax_delete_action(): void
    {
        $permission_check = $this->check_module_access_permissions(self::MODULE_SLUG, 'aipkit_enhancer_actions_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        $action_id_to_delete = isset($_POST['id']) ? sanitize_text_field(wp_unslash($_POST['id'])) : null;

        if (empty($action_id_to_delete)) {
            $this->send_wp_error(new WP_Error('missing_id', __('Action ID is required.', 'gpt3-ai-content-generator')));
            return;
        }

        $actions = get_option(self::OPTION_NAME, []);
        if (!is_array($actions)) {
            wp_send_json_success(['message' => __('No actions to delete.', 'gpt3-ai-content-generator')]);
            return;
        }

        $action_to_delete = null;
        foreach ($actions as $action) {
            if (isset($action['id']) && $action['id'] === $action_id_to_delete) {
                $action_to_delete = $action;
                break;
            }
        }
        if ($action_to_delete && isset($action_to_delete['is_default']) && $action_to_delete['is_default']) {
            $this->send_wp_error(new WP_Error('cannot_delete_default', __('Default actions cannot be deleted.', 'gpt3-ai-content-generator')));
            return;
        }

        $updated_actions = array_filter($actions, function ($action) use ($action_id_to_delete) {
            return !isset($action['id']) || $action['id'] !== $action_id_to_delete;
        });

        update_option(self::OPTION_NAME, array_values($updated_actions), 'no');
        wp_send_json_success(['message' => __('Action deleted successfully.', 'gpt3-ai-content-generator')]);
    }
}
