<?php

namespace WPAICG\Core\Providers\OpenAI;

use WPAICG\Core\Providers\Traits\ChatCompletionsPayloadTrait;
use WPAICG\Core\Providers\Traits\ChatCompletionsResponseParserTrait;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adapts the shared Chat Completions contract for custom OpenAI-compatible
 * endpoints without changing the default OpenAI Responses implementation.
 */
final class OpenAIChatCompletionsAdapter
{
    use ChatCompletionsPayloadTrait;
    use ChatCompletionsResponseParserTrait;

    public function format_chat(
        string $instructions,
        array $history,
        string $user_message,
        array $ai_params,
        string $model
    ): array {
        $payload = $this->format_chat_completions_payload(
            $instructions,
            $history,
            $user_message,
            $ai_params,
            $model,
            true
        );

        return $this->apply_compatible_extensions($payload, $ai_params);
    }

    public function format_sse(
        array $messages,
        string $instructions,
        array $ai_params,
        string $model
    ): array {
        $payload = $this->format_sse_chat_completions_payload(
            $messages,
            $instructions,
            $ai_params,
            $model,
            true,
            false
        );

        $payload = $this->apply_compatible_extensions($payload, $ai_params);
        $payload['stream_options'] = ['include_usage' => true];

        return $payload;
    }

    /**
     * @return mixed[]|\WP_Error
     */
    public function parse_chat(array $decoded_response, array $request_data)
    {
        return $this->parse_chat_response($decoded_response, $request_data);
    }

    private function apply_compatible_extensions(array $payload, array $ai_params): array
    {
        if (isset($payload['max_tokens'])) {
            $payload['max_completion_tokens'] = $payload['max_tokens'];
            unset($payload['max_tokens']);
        }

        if (!empty($ai_params['reasoning']['effort']) && $ai_params['reasoning']['effort'] !== 'none') {
            $payload['reasoning_effort'] = sanitize_key((string) $ai_params['reasoning']['effort']);
        }

        if (!empty($ai_params['image_inputs']) && is_array($ai_params['image_inputs'])) {
            $payload = $this->attach_image_inputs($payload, $ai_params['image_inputs']);
        }

        return $payload;
    }

    private function attach_image_inputs(array $payload, array $image_inputs): array
    {
        if (empty($payload['messages']) || !is_array($payload['messages'])) {
            return $payload;
        }

        $last_message_key = array_key_last($payload['messages']);
        if (
            $last_message_key === null
            || ($payload['messages'][$last_message_key]['role'] ?? '') !== 'user'
        ) {
            return $payload;
        }

        $existing_content = $payload['messages'][$last_message_key]['content'] ?? '';
        $content_parts = [];
        if (is_string($existing_content) && $existing_content !== '') {
            $content_parts[] = ['type' => 'text', 'text' => $existing_content];
        }

        foreach ($image_inputs as $image_input) {
            if (!is_array($image_input) || empty($image_input['type']) || empty($image_input['base64'])) {
                continue;
            }

            $image_url = [
                'url' => 'data:' . $image_input['type'] . ';base64,' . $image_input['base64'],
            ];
            if (!empty($image_input['detail'])) {
                $image_url['detail'] = sanitize_key((string) $image_input['detail']);
            }
            $content_parts[] = [
                'type' => 'image_url',
                'image_url' => $image_url,
            ];
        }

        if (!empty($content_parts)) {
            $payload['messages'][$last_message_key]['content'] = $content_parts;
        }

        return $payload;
    }
}
