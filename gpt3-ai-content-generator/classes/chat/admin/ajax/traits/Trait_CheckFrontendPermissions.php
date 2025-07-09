<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/admin/ajax/traits/Trait_CheckFrontendPermissions.php
// Status: NEW FILE

namespace WPAICG\Chat\Admin\Ajax\Traits;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

trait Trait_CheckFrontendPermissions {
    /**
     * Helper to check nonce for FRONTEND actions (can be called by admin JS).
     *
     * @param string $nonce_action The nonce action string.
     * @return bool|WP_Error True if permissions are valid, WP_Error otherwise.
     */
    protected function check_frontend_permissions(string $nonce_action = 'aipkit_frontend_chat_nonce'): bool|WP_Error {
        // Use check_ajax_referer for standard WP behavior, checking $_REQUEST
        if (!check_ajax_referer($nonce_action, '_ajax_nonce', false)) {
            return new WP_Error('nonce_failure', __('Security check failed (nonce).', 'gpt3-ai-content-generator'), ['status' => 403]);
        }
        // No capability check here, as it's primarily for frontend/guest use,
        // but can be called by admin JS (e.g., sidebar). Specific methods might add checks.
        return true;
    }
}