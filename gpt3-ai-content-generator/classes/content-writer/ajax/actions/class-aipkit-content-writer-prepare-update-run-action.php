<?php

namespace WPAICG\ContentWriter\Ajax\Actions;

use WPAICG\ContentWriter\Ajax\AIPKit_Content_Writer_Base_Ajax_Action;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prepares and validates a server-bound Content Writer update run.
 */
class AIPKit_Content_Writer_Prepare_Update_Run_Action extends AIPKit_Content_Writer_Base_Ajax_Action
{
    private const TRANSIENT_PREFIX = 'aipkit_cw_update_run_';
    private const RUN_TTL = 30 * MINUTE_IN_SECONDS;
    private const TEXT_FIELDS = ['title', 'content', 'meta', 'keyword', 'excerpt', 'tags'];
    private const IMAGE_FIELDS = ['title', 'keyword', 'excerpt', 'content'];

    public function handle(): void
    {
        $permission_check = $this->check_module_access_permissions('content-writer', 'aipkit_content_writer_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        $mode = $this->get_request_key('mode');
        $post_ids = $this->get_request_array('post_ids', 'absint');
        $fields = $this->get_request_array('fields', 'sanitize_key');
        $result = self::create_run($mode, $post_ids, $fields);

        if (is_wp_error($result)) {
            $this->send_wp_error($result);
            return;
        }

        wp_send_json_success(['run_token' => $result]);
    }

    /**
     * Marks a prepared update run as cancelled so in-flight field requests
     * cannot write their generated value after the user presses Stop.
     */
    public function handle_cancel(): void
    {
        $permission_check = $this->check_module_access_permissions('content-writer', 'aipkit_content_writer_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_wp_error($permission_check);
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is checked in check_module_access_permissions.
        $token = isset($_POST['run_token']) ? sanitize_text_field(wp_unslash($_POST['run_token'])) : '';
        $result = self::cancel_run($token);
        if (is_wp_error($result)) {
            $this->send_wp_error($result);
            return;
        }

        wp_send_json_success(['cancelled' => true]);
    }

    /**
     * @param int[]    $post_ids
     * @param string[] $fields
     * @return string|WP_Error
     */
    private static function create_run(string $mode, array $post_ids, array $fields)
    {
        $allowed_modes = ['existing-content', 'existing-images', 'existing-products'];
        if (!in_array($mode, $allowed_modes, true)) {
            return new WP_Error('invalid_update_mode', __('Invalid update mode.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        $post_ids = array_values(array_unique(array_filter(array_map('absint', $post_ids))));
        $fields = array_values(array_unique(array_filter(array_map('sanitize_key', $fields))));
        if (empty($post_ids) || empty($fields)) {
            return new WP_Error('invalid_update_run', __('Select at least one item and field to update.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        $allowed_fields = $mode === 'existing-images' ? self::IMAGE_FIELDS : self::TEXT_FIELDS;
        if (array_diff($fields, $allowed_fields)) {
            return new WP_Error('invalid_update_fields', __('The update contains unsupported fields.', 'gpt3-ai-content-generator'), ['status' => 400]);
        }

        $is_pro = class_exists('\\WPAICG\\aipkit_dashboard') && \WPAICG\aipkit_dashboard::is_pro_plan();
        if (!$is_pro && $mode === 'existing-images' && count($post_ids) > 1) {
            return new WP_Error('bulk_images_pro_required', __('Bulk image optimization is available on Pro.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }
        if (!$is_pro && $mode === 'existing-products') {
            return new WP_Error('products_pro_required', __('Product optimization is a Pro feature.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post || !current_user_can('edit_post', $post_id)) {
                return new WP_Error('invalid_update_item', __('One or more selected items cannot be updated.', 'gpt3-ai-content-generator'), ['status' => 403]);
            }
            if ($mode === 'existing-images' && $post->post_type !== 'attachment') {
                return new WP_Error('invalid_image_item', __('Image updates require media attachments.', 'gpt3-ai-content-generator'), ['status' => 400]);
            }
            if ($mode === 'existing-products' && $post->post_type !== 'product') {
                return new WP_Error('invalid_product_item', __('WooCommerce updates require products.', 'gpt3-ai-content-generator'), ['status' => 400]);
            }
            if ($mode === 'existing-content' && $post->post_type === 'attachment') {
                return new WP_Error('invalid_content_item', __('Rewrite content does not update media attachments.', 'gpt3-ai-content-generator'), ['status' => 400]);
            }
            if (!$is_pro && $mode === 'existing-content' && count($post_ids) > 1 && $post->post_type === 'product') {
                return new WP_Error('bulk_products_pro_required', __('Bulk product optimization is available on Pro.', 'gpt3-ai-content-generator'), ['status' => 403]);
            }
        }

        $token = wp_generate_password(40, false, false);
        $payload = [
            'user_id' => get_current_user_id(),
            'mode' => $mode,
            'post_ids' => $post_ids,
            'fields' => $fields,
            'cancelled' => false,
        ];
        set_transient(self::get_transient_key($token), $payload, self::RUN_TTL);

        return $token;
    }

    /**
     * Verifies that an individual field request belongs to a prepared run.
     *
     * @return mixed[]|WP_Error
     */
    public static function validate_request(string $token, int $post_id, string $field)
    {
        if ($token === '') {
            return new WP_Error('missing_update_run', __('The update session is missing. Please start the update again.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }

        $payload = get_transient(self::get_transient_key($token));
        if (!is_array($payload)) {
            return new WP_Error('expired_update_run', __('The update session expired. Please start the update again.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }
        if ((int) ($payload['user_id'] ?? 0) !== get_current_user_id()) {
            return new WP_Error('invalid_update_run_user', __('This update session is not valid for the current user.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }
        if (!empty($payload['cancelled'])) {
            return new WP_Error('update_run_stopped', __('This update was stopped.', 'gpt3-ai-content-generator'), ['status' => 409]);
        }
        if (!in_array($post_id, $payload['post_ids'] ?? [], true) || !in_array($field, $payload['fields'] ?? [], true)) {
            return new WP_Error('invalid_update_run_item', __('This item or field is not part of the prepared update.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }

        return $payload;
    }

    /**
     * @return true|WP_Error
     */
    private static function cancel_run(string $token)
    {
        if ($token === '') {
            return new WP_Error('missing_update_run', __('The update session is missing. Please start the update again.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }

        $transient_key = self::get_transient_key($token);
        $payload = get_transient($transient_key);
        if (!is_array($payload)) {
            return new WP_Error('expired_update_run', __('The update session expired. Please start the update again.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }
        if ((int) ($payload['user_id'] ?? 0) !== get_current_user_id()) {
            return new WP_Error('invalid_update_run_user', __('This update session is not valid for the current user.', 'gpt3-ai-content-generator'), ['status' => 403]);
        }

        $payload['cancelled'] = true;
        set_transient($transient_key, $payload, self::RUN_TTL);

        return true;
    }

    private static function get_transient_key(string $token): string
    {
        return self::TRANSIENT_PREFIX . md5($token);
    }

    private function get_request_key(string $name): string
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- The nonce is checked at the start of handle().
        return isset($_POST[$name]) ? sanitize_key(wp_unslash($_POST[$name])) : '';
    }

    /**
     * @return mixed[]
     */
    private function get_request_array(string $name, string $sanitize_callback): array
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The nonce is checked at the start of handle; decoded values are sanitized below.
        $raw_value = isset($_POST[$name]) ? wp_unslash($_POST[$name]) : '[]';
        $values = is_array($raw_value) ? $raw_value : json_decode((string) $raw_value, true);
        if (!is_array($values)) {
            return [];
        }

        return array_map($sanitize_callback, $values);
    }
}
