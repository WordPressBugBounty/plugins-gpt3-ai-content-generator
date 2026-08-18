<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Translates AI Puffer's shared message contract to Gemini Interactions.
 */
final class GoogleInteractionsTextAdapter
{
    /**
     * @param string               $user_message Optional final user message.
     * @param string               $instructions System instructions.
     * @param array<int, mixed>    $history      Shared provider-neutral history.
     * @param array<string, mixed> $ai_params    Shared generation parameters.
     * @return array<string, mixed>|WP_Error
     */
    public static function build(
        string $user_message,
        string $instructions,
        array $history,
        array $ai_params,
        string $model
    ) {
        $history = self::append_user_message_once($history, $user_message);
        $input_steps = self::history_to_input_steps($history);
        $input_steps = self::append_image_inputs($input_steps, $ai_params['image_inputs'] ?? []);
        $input_steps = self::append_document_input($input_steps, $ai_params['google_document_input'] ?? null);

        $previous_interaction_id = isset($ai_params['google_previous_interaction_id'])
            && is_string($ai_params['google_previous_interaction_id'])
            ? trim($ai_params['google_previous_interaction_id'])
            : '';
        if ($previous_interaction_id !== '') {
            $input_steps = self::latest_user_input_only($input_steps);
        }

        $options = [
            'store' => self::is_enabled($ai_params['store_conversation'] ?? false)
                || $previous_interaction_id !== '',
        ];
        if ($previous_interaction_id !== '') {
            $options['previous_interaction_id'] = $previous_interaction_id;
        }
        if (self::is_enabled($ai_params['stream'] ?? false)) {
            $options['stream'] = true;
        }
        if (trim($instructions) !== '') {
            $options['system_instruction'] = $instructions;
        }

        $generation_config = self::generation_config($ai_params);
        if (!empty($generation_config)) {
            $options['generation_config'] = $generation_config;
        }
        $tools = [];
        $file_search_tool = self::file_search_tool($ai_params['google_file_search_tool_config'] ?? []);
        if ($file_search_tool !== null) {
            $tools[] = $file_search_tool;
        } elseif (self::is_enabled($ai_params['frontend_google_search_grounding_active'] ?? false)) {
            $tools[] = ['type' => 'google_search'];
        }
        if (!empty($tools)) {
            $options['tools'] = $tools;
        }

        return GoogleInteractionsRequestBuilder::build($model, $input_steps, $options);
    }

    /**
     * Stateful Interactions already contain prior turns on Google's server.
     * Sending only the latest user input avoids duplicating that history.
     *
     * @param array<int, array<string, mixed>> $steps
     * @return array<int, array<string, mixed>>
     */
    private static function latest_user_input_only(array $steps): array
    {
        for ($index = count($steps) - 1; $index >= 0; $index--) {
            if (($steps[$index]['type'] ?? '') === 'user_input') {
                return [$steps[$index]];
            }
        }

        return $steps;
    }

    /**
     * @param mixed $value
     */
    private static function is_enabled($value): bool
    {
        return in_array($value, [true, 1, '1', 'true'], true);
    }

    /**
     * @param mixed $config
     * @return array<string, mixed>|null
     */
    private static function file_search_tool($config): ?array
    {
        if (!is_array($config)) {
            return null;
        }

        $store_names = isset($config['file_search_store_names']) && is_array($config['file_search_store_names'])
            ? $config['file_search_store_names']
            : [];
        if (empty($store_names)) {
            return null;
        }

        $tool = [
            'type' => 'file_search',
            'file_search_store_names' => $store_names,
        ];
        if (isset($config['top_k'])) {
            $tool['top_k'] = $config['top_k'];
        }
        if (isset($config['metadata_filter'])) {
            $tool['metadata_filter'] = $config['metadata_filter'];
        }

        return $tool;
    }

    /**
     * @param array<int, mixed> $history
     * @return array<int, mixed>
     */
    private static function append_user_message_once(array $history, string $user_message): array
    {
        if ($user_message === '') {
            return $history;
        }

        $last_message = end($history);
        if (
            !is_array($last_message)
            || ($last_message['role'] ?? '') !== 'user'
            || ($last_message['content'] ?? null) !== $user_message
        ) {
            $history[] = [
                'role' => 'user',
                'content' => $user_message,
            ];
        }

        return $history;
    }

    /**
     * @param array<int, mixed> $history
     * @return array<int, array<string, mixed>>
     */
    private static function history_to_input_steps(array $history): array
    {
        $steps = [];

        foreach ($history as $message) {
            if (!is_array($message)) {
                continue;
            }

            $role = isset($message['role']) && is_string($message['role']) ? strtolower($message['role']) : 'user';
            if ($role === 'system') {
                continue;
            }

            $step_type = in_array($role, ['assistant', 'bot', 'model'], true)
                ? 'model_output'
                : 'user_input';
            $content = self::normalize_message_content($message['content'] ?? '');
            if (empty($content)) {
                continue;
            }

            $last_index = count($steps) - 1;
            if ($last_index >= 0 && ($steps[$last_index]['type'] ?? '') === $step_type) {
                $steps[$last_index]['content'] = array_merge($steps[$last_index]['content'], $content);
                continue;
            }

            $steps[] = [
                'type' => $step_type,
                'content' => $content,
            ];
        }

        return $steps;
    }

    /**
     * @param mixed $content
     * @return array<int, array<string, mixed>>
     */
    private static function normalize_message_content($content): array
    {
        if (is_string($content)) {
            return trim($content) === ''
                ? []
                : [['type' => 'text', 'text' => $content]];
        }
        if (!is_array($content)) {
            return [];
        }

        $normalized = [];
        foreach ($content as $part) {
            if (!is_array($part)) {
                continue;
            }

            $type = isset($part['type']) && is_string($part['type']) ? $part['type'] : '';
            if (in_array($type, ['text', 'input_text'], true)) {
                $text = isset($part['text']) && is_string($part['text']) ? $part['text'] : '';
                if (trim($text) !== '') {
                    $normalized[] = ['type' => 'text', 'text' => $text];
                }
                continue;
            }

            if (!in_array($type, ['image_url', 'input_image'], true)) {
                continue;
            }

            $image_url = $part['image_url'] ?? '';
            if (is_array($image_url)) {
                $image_url = $image_url['url'] ?? '';
            }
            $inline_image = is_string($image_url) ? self::data_url_to_image($image_url) : null;
            if ($inline_image !== null) {
                $normalized[] = $inline_image;
            }
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function data_url_to_image(string $data_url): ?array
    {
        if (!preg_match('#^data:([^;,]+);base64,(.+)$#s', trim($data_url), $matches)) {
            return null;
        }

        $mime_type = sanitize_mime_type($matches[1]);
        $data = preg_replace('/\s+/', '', $matches[2]);
        if ($mime_type === '' || !is_string($data) || $data === '') {
            return null;
        }

        return [
            'type' => 'image',
            'mime_type' => $mime_type,
            'data' => $data,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     * @param mixed                            $image_inputs
     * @return array<int, array<string, mixed>>
     */
    private static function append_image_inputs(array $steps, $image_inputs): array
    {
        if (!is_array($image_inputs) || empty($image_inputs)) {
            return $steps;
        }

        $images = [];
        foreach ($image_inputs as $image_input) {
            if (!is_array($image_input)) {
                continue;
            }

            $mime_type = isset($image_input['type']) && is_string($image_input['type'])
                ? sanitize_mime_type($image_input['type'])
                : '';
            $data = isset($image_input['base64']) && is_string($image_input['base64'])
                ? preg_replace('/\s+/', '', $image_input['base64'])
                : '';
            if ($mime_type === '' || !is_string($data) || $data === '') {
                continue;
            }

            $images[] = [
                'type' => 'image',
                'mime_type' => $mime_type,
                'data' => $data,
            ];
        }

        if (empty($images)) {
            return $steps;
        }

        for ($index = count($steps) - 1; $index >= 0; $index--) {
            if (($steps[$index]['type'] ?? '') === 'user_input') {
                $steps[$index]['content'] = array_merge($steps[$index]['content'], $images);
                return $steps;
            }
        }

        $steps[] = [
            'type' => 'user_input',
            'content' => $images,
        ];
        return $steps;
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     * @param mixed $document_input
     * @return array<int, array<string, mixed>>
     */
    private static function append_document_input(array $steps, $document_input): array
    {
        if (!is_array($document_input)) {
            return $steps;
        }
        $uri = isset($document_input['uri']) && is_string($document_input['uri'])
            ? trim($document_input['uri'])
            : '';
        $mime_type = isset($document_input['mime_type']) && is_string($document_input['mime_type'])
            ? sanitize_mime_type($document_input['mime_type'])
            : '';
        if ($uri === '' || $mime_type === '') {
            return $steps;
        }
        $document = [
            'type' => 'document',
            'uri' => $uri,
            'mime_type' => $mime_type,
        ];
        for ($index = count($steps) - 1; $index >= 0; $index--) {
            if (($steps[$index]['type'] ?? '') === 'user_input') {
                $steps[$index]['content'][] = $document;
                return $steps;
            }
        }
        $steps[] = [
            'type' => 'user_input',
            'content' => [$document],
        ];
        return $steps;
    }

    /**
     * @param array<string, mixed> $ai_params
     * @return array<string, mixed>
     */
    private static function generation_config(array $ai_params): array
    {
        $config = [];
        if (isset($ai_params['temperature']) && is_numeric($ai_params['temperature'])) {
            $config['temperature'] = (float) $ai_params['temperature'];
        }
        if (isset($ai_params['max_completion_tokens']) && is_numeric($ai_params['max_completion_tokens'])) {
            $config['max_output_tokens'] = absint($ai_params['max_completion_tokens']);
        }
        if (isset($ai_params['top_p']) && is_numeric($ai_params['top_p'])) {
            $config['top_p'] = (float) $ai_params['top_p'];
        }
        if (isset($ai_params['stop'])) {
            $stop_sequences = is_array($ai_params['stop']) ? $ai_params['stop'] : [$ai_params['stop']];
            $stop_sequences = array_values(
                array_filter(
                    array_map(
                        static fn($value): string => is_scalar($value) ? (string) $value : '',
                        $stop_sequences
                    ),
                    static fn(string $value): bool => $value !== ''
                )
            );
            if (!empty($stop_sequences)) {
                $config['stop_sequences'] = $stop_sequences;
            }
        }

        return $config;
    }
}
