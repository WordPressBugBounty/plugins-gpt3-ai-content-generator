<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/images/providers/openai/OpenAIImageResponseParser.php

namespace WPAICG\Images\Providers\OpenAI;

use WP_Error;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Handles parsing responses from the OpenAI Image Generation API.
 */
class OpenAIImageResponseParser {

    /**
     * Parses the successful response from OpenAI Image Generation API.
     *
     * @param array $decoded_response The decoded JSON response body.
     * @return array Array of image data objects and usage.
     *               Structure: ['images' => [['url'=>..., 'b64_json'=>..., 'revised_prompt'=>...], ...], 'usage' => array|null]
     */
    public static function parse(array $decoded_response): array {
        $images = [];
        $usage = $decoded_response['usage'] ?? null; // Capture usage if available

        if (isset($decoded_response['data']) && is_array($decoded_response['data'])) {
            foreach ($decoded_response['data'] as $imageData) {
                $images[] = [
                    'url'            => $imageData['url'] ?? null,
                    'b64_json'       => $imageData['b64_json'] ?? null,
                    'revised_prompt' => $imageData['revised_prompt'] ?? null,
                ];
            }
        }
        return ['images' => $images, 'usage' => $usage];
    }

    /**
     * Parses error response from OpenAI API.
     *
     * @param mixed $response_body The raw or decoded error response body.
     * @param int $status_code The HTTP status code.
     * @return string A user-friendly error message.
     */
    public static function parse_error($response_body, int $status_code): string {
        $message = __('An unknown API error occurred.', 'gpt3-ai-content-generator');
        $decoded = is_string($response_body) ? json_decode($response_body, true) : $response_body;

        if (is_array($decoded) && !empty($decoded['error']['message'])) {
            $message = $decoded['error']['message'];
            if (!empty($decoded['error']['code'])) { $message .= ' (Code: ' . $decoded['error']['code'] . ')'; }
            if (!empty($decoded['error']['type'])) { $message .= ' Type: ' . $decoded['error']['type']; }
        } elseif (is_string($response_body)) {
             $message = substr($response_body, 0, 200); // Raw snippet if not JSON
        }

        return trim($message);
    }
}