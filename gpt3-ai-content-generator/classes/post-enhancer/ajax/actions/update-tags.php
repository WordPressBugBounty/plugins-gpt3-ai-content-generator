<?php

// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/post-enhancer/ajax/actions/update-tags.php
// Status: NEW FILE

namespace WPAICG\PostEnhancer\Ajax\Actions;

use WPAICG\PostEnhancer\Ajax\Base\AIPKit_Post_Enhancer_Base_Ajax_Action;
use WP_Error;

class AIPKit_PostEnhancer_Update_Tags extends AIPKit_Post_Enhancer_Base_Ajax_Action
{
    public function handle(): void
    {
        $permission_check = $this->check_permissions('aipkit_update_tags_nonce');
        if (is_wp_error($permission_check)) {
            $this->send_error_response($permission_check);
            return;
        }

        $post = $this->get_post();
        if (is_wp_error($post)) {
            $this->send_error_response($post);
            return;
        }

        $new_tags = isset($_POST['new_value']) ? sanitize_text_field(wp_unslash($_POST['new_value'])) : '';

        // wp_set_post_tags replaces existing tags by default.
        $result = wp_set_post_tags($post->ID, $new_tags, false);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => 'Failed to update post tags: ' . $result->get_error_message()], 500);
        } else {
            wp_send_json_success(['message' => __('Post tags updated successfully.', 'gpt3-ai-content-generator')]);
        }
    }
}