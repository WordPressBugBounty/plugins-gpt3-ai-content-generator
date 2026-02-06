<?php

namespace WPAICG\Core\Providers\Claude\Methods;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared payload formatter for Anthropic Messages API.
 */
function build_claude_payload_shared(
    string $instructions,
    array $history,
    string $user_message,
    array $ai_params,
    string $model,
    bool $stream = false
): array {
    $messages = [];

    foreach ($history as $msg) {
        if (!is_array($msg)) {
            continue;
        }
        $role = $msg['role'] ?? '';
        if ($role === 'bot') {
            $role = 'assistant';
        }
        if (!in_array($role, ['user', 'assistant'], true)) {
            continue;
        }

        $content = isset($msg['content']) ? trim((string) $msg['content']) : '';
        if ($content === '') {
            continue;
        }

        $messages[] = [
            'role' => $role,
            'content' => $content,
        ];
    }

    if ($user_message !== '') {
        $last = end($messages);
        $is_duplicate_last_user = is_array($last)
            && ($last['role'] ?? '') === 'user'
            && trim((string) ($last['content'] ?? '')) === trim($user_message);
        if (!$is_duplicate_last_user) {
            $messages[] = [
                'role' => 'user',
                'content' => trim($user_message),
            ];
        }
    }

    $claude_file_ids = [];
    if (!empty($ai_params['claude_file_ids']) && is_array($ai_params['claude_file_ids'])) {
        foreach ($ai_params['claude_file_ids'] as $file_id_candidate) {
            $file_id = sanitize_text_field((string) $file_id_candidate);
            if ($file_id !== '' && preg_match('/^file_[a-zA-Z0-9_-]+$/', $file_id)) {
                $claude_file_ids[] = $file_id;
            }
        }
        $claude_file_ids = array_values(array_unique($claude_file_ids));
    }

    $has_image_inputs = !empty($ai_params['image_inputs']) && is_array($ai_params['image_inputs']);

    if ($has_image_inputs || !empty($claude_file_ids)) {
        $last_user_index = null;
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if (($messages[$i]['role'] ?? '') === 'user') {
                $last_user_index = $i;
                break;
            }
        }

        if ($last_user_index === null) {
            $messages[] = ['role' => 'user', 'content' => ''];
            $last_user_index = count($messages) - 1;
        }

        $existing_content = $messages[$last_user_index]['content'] ?? '';
        $content_blocks = [];

        if (is_string($existing_content) && trim($existing_content) !== '') {
            $content_blocks[] = [
                'type' => 'text',
                'text' => trim($existing_content),
            ];
        } elseif (is_array($existing_content)) {
            foreach ($existing_content as $block) {
                if (!is_array($block)) {
                    continue;
                }
                if (($block['type'] ?? '') === 'text' && isset($block['text'])) {
                    $content_blocks[] = [
                        'type' => 'text',
                        'text' => (string) $block['text'],
                    ];
                } elseif (($block['type'] ?? '') === 'image' && !empty($block['source']) && is_array($block['source'])) {
                    $content_blocks[] = $block;
                } elseif (($block['type'] ?? '') === 'document' && !empty($block['source']) && is_array($block['source'])) {
                    $content_blocks[] = $block;
                }
            }
        }

        if ($has_image_inputs) {
            foreach ($ai_params['image_inputs'] as $image_input) {
                if (!is_array($image_input)) {
                    continue;
                }
                $base64_data = $image_input['base64'] ?? '';
                $media_type = $image_input['type'] ?? '';
                if ($base64_data === '' || $media_type === '') {
                    continue;
                }

                $content_blocks[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => (string) $media_type,
                        'data' => (string) $base64_data,
                    ],
                ];
            }
        }

        foreach ($claude_file_ids as $claude_file_id) {
            $content_blocks[] = [
                'type' => 'document',
                'source' => [
                    'type' => 'file',
                    'file_id' => $claude_file_id,
                ],
            ];
        }

        if (!empty($content_blocks)) {
            $messages[$last_user_index]['content'] = $content_blocks;
        }
    }

    $payload = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => isset($ai_params['max_completion_tokens'])
            ? max(1, absint($ai_params['max_completion_tokens']))
            : 4096,
    ];

    if ($instructions !== '') {
        $payload['system'] = $instructions;
    }

    $temperature = isset($ai_params['temperature']) && is_numeric($ai_params['temperature'])
        ? floatval($ai_params['temperature'])
        : null;
    $top_p = isset($ai_params['top_p']) && is_numeric($ai_params['top_p'])
        ? max(0.0, min(1.0, floatval($ai_params['top_p'])))
        : null;
    $temperature_is_default = $temperature !== null && abs($temperature - 1.0) < 0.00001;
    $top_p_is_non_default = $top_p !== null && abs($top_p - 1.0) >= 0.00001;

    // Anthropic rejects payloads that include both temperature and top_p.
    if ($top_p_is_non_default && ($temperature === null || $temperature_is_default)) {
        $payload['top_p'] = $top_p;
    } elseif ($temperature !== null) {
        $payload['temperature'] = $temperature;
    } elseif ($top_p !== null) {
        $payload['top_p'] = $top_p;
    }

    if (!empty($ai_params['stop'])) {
        if (is_string($ai_params['stop'])) {
            $payload['stop_sequences'] = [trim($ai_params['stop'])];
        } elseif (is_array($ai_params['stop'])) {
            $stop_sequences = array_values(array_filter(array_map(
                static fn($item) => is_string($item) ? trim($item) : '',
                $ai_params['stop']
            )));
            if (!empty($stop_sequences)) {
                $payload['stop_sequences'] = $stop_sequences;
            }
        }
    }

    $bot_allows_web_search = isset($ai_params['web_search_tool_config']['enabled'])
        && $ai_params['web_search_tool_config']['enabled'] === true;
    $frontend_requests_web_search = !isset($ai_params['frontend_web_search_active'])
        || $ai_params['frontend_web_search_active'] === true;

    if ($bot_allows_web_search && $frontend_requests_web_search) {
        $tool = [
            'name' => 'web_search',
            'type' => $ai_params['web_search_tool_config']['type'] ?? 'web_search_20250305',
        ];

        if (!empty($ai_params['web_search_tool_config']['allowed_domains'])
            && is_array($ai_params['web_search_tool_config']['allowed_domains'])
        ) {
            $tool['allowed_domains'] = array_values(array_filter(array_map(
                static fn($domain) => is_string($domain) ? trim($domain) : '',
                $ai_params['web_search_tool_config']['allowed_domains']
            )));
        }

        if (!empty($ai_params['web_search_tool_config']['blocked_domains'])
            && is_array($ai_params['web_search_tool_config']['blocked_domains'])
        ) {
            $tool['blocked_domains'] = array_values(array_filter(array_map(
                static fn($domain) => is_string($domain) ? trim($domain) : '',
                $ai_params['web_search_tool_config']['blocked_domains']
            )));
        }

        if (!empty($ai_params['web_search_tool_config']['max_uses'])) {
            $tool['max_uses'] = absint($ai_params['web_search_tool_config']['max_uses']);
        }

        if (!empty($ai_params['web_search_tool_config']['user_location'])
            && is_array($ai_params['web_search_tool_config']['user_location'])
        ) {
            $user_location = array_filter($ai_params['web_search_tool_config']['user_location']);
            if (!empty($user_location)) {
                if (!isset($user_location['type'])) {
                    $user_location['type'] = 'approximate';
                }
                $tool['user_location'] = $user_location;
            }
        }

        if (!empty($ai_params['web_search_tool_config']['cache_control'])
            && is_array($ai_params['web_search_tool_config']['cache_control'])
        ) {
            $cache_control = $ai_params['web_search_tool_config']['cache_control'];
            $cache_type = isset($cache_control['type']) ? sanitize_text_field((string) $cache_control['type']) : '';
            $cache_ttl = isset($cache_control['ttl']) ? sanitize_text_field((string) $cache_control['ttl']) : '';
            if ($cache_type === 'ephemeral' && in_array($cache_ttl, ['5m', '1h'], true)) {
                $tool['cache_control'] = [
                    'type' => 'ephemeral',
                    'ttl' => $cache_ttl,
                ];
            }
        }

        $payload['tools'] = [$tool];
    }

    if ($stream) {
        $payload['stream'] = true;
    }

    return $payload;
}

/**
 * Detects whether a Claude payload references Files API document blocks.
 *
 * @param array $payload Claude Messages API payload.
 * @return bool
 */
function claude_payload_requires_files_beta_header(array $payload): bool
{
    if (empty($payload['messages']) || !is_array($payload['messages'])) {
        return false;
    }

    foreach ($payload['messages'] as $message_item) {
        if (!is_array($message_item)) {
            continue;
        }

        $content_blocks = $message_item['content'] ?? null;
        if (!is_array($content_blocks)) {
            continue;
        }

        foreach ($content_blocks as $content_block) {
            if (!is_array($content_block) || (($content_block['type'] ?? '') !== 'document')) {
                continue;
            }

            $source = $content_block['source'] ?? null;
            if (!is_array($source)) {
                continue;
            }

            if (($source['type'] ?? '') === 'file' && !empty($source['file_id'])) {
                return true;
            }
        }
    }

    return false;
}
