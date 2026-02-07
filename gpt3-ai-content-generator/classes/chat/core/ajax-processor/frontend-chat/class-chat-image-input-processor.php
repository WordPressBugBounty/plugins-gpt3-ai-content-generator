<?php
// File: classes/chat/core/ajax-processor/frontend-chat/class-chat-image-input-processor.php
// Status: NEW FILE

namespace WPAICG\Chat\Core\AjaxProcessor\FrontendChat;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ChatImageInputProcessor {

    /**
     * Processes the image_inputs JSON string from POST.
     * Currently supports only a single image.
     *
     * @param string|null $image_inputs_json The JSON string of image inputs.
     * @return array|WP_Error|null The processed image data for AIService, WP_Error when invalid, or null when absent.
     */
    public function process(?string $image_inputs_json): array|WP_Error|null {
        if (!class_exists(ChatImageInputValidator::class)) {
            $validator_path = __DIR__ . '/class-chat-image-input-validator.php';
            if (file_exists($validator_path)) {
                require_once $validator_path;
            }
        }

        if (!class_exists(ChatImageInputValidator::class)) {
            return new WP_Error(
                'image_validator_missing',
                __('Image validation component is unavailable.', 'gpt3-ai-content-generator'),
                ['status' => 500]
            );
        }

        return ChatImageInputValidator::parse_and_validate($image_inputs_json);
    }
}
