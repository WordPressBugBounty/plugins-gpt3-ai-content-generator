<?php
// File: classes/core/providers/xai/helpers.php

namespace WPAICG\Core\Providers\XAI\Methods;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalizes common truthy settings without treating absent settings as enabled.
 */
function xai_truthy($value): bool {
    return $value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'yes';
}

/**
 * Builds xAI Responses API input messages. xAI does not support the OpenAI-only
 * top-level "instructions" field, so system/developer guidance stays in input.
 *
 * @param string $instructions
 * @param array<int, array<string, mixed>> $history
 * @param string $user_message
 * @param array<string, mixed> $ai_params
 * @return array<int, array<string, mixed>>
 */
function xai_build_input_messages(string $instructions, array $history, string $user_message, array $ai_params): array {
    $input = [];

    if (trim($instructions) !== '') {
        $input[] = [
            'role' => 'system',
            'content' => trim($instructions),
        ];
    }

    foreach ($history as $message) {
        if (!is_array($message)) {
            continue;
        }

        $role = isset($message['role']) ? (string) $message['role'] : 'user';
        $role = ($role === 'bot') ? 'assistant' : $role;
        if (!in_array($role, ['system', 'developer', 'user', 'assistant'], true)) {
            continue;
        }
        if ($role === 'system' && trim($instructions) !== '') {
            continue;
        }

        $content = $message['content'] ?? '';
        if (is_array($content)) {
            $normalized_parts = xai_normalize_content_parts($content);
            if (!empty($normalized_parts)) {
                $input[] = ['role' => $role, 'content' => $normalized_parts];
            }
            continue;
        }

        $content = trim((string) $content);
        if ($content !== '') {
            $input[] = ['role' => $role, 'content' => $content];
        }
    }

    if (trim($user_message) !== '') {
        $input[] = [
            'role' => 'user',
            'content' => trim($user_message),
        ];
    }

    xai_attach_image_inputs($input, $ai_params);

    return $input;
}

/**
 * @param array<int, mixed> $parts
 * @return array<int, array<string, mixed>>
 */
function xai_normalize_content_parts(array $parts): array {
    $normalized = [];

    foreach ($parts as $part) {
        if (!is_array($part)) {
            continue;
        }

        $type = isset($part['type']) ? (string) $part['type'] : '';
        if (in_array($type, ['input_text', 'text'], true) && isset($part['text'])) {
            $normalized[] = [
                'type' => 'input_text',
                'text' => (string) $part['text'],
            ];
            continue;
        }

        if (in_array($type, ['input_image', 'image_url'], true)) {
            $image_url = '';
            if (isset($part['image_url']) && is_string($part['image_url'])) {
                $image_url = $part['image_url'];
            } elseif (isset($part['image_url']['url']) && is_string($part['image_url']['url'])) {
                $image_url = $part['image_url']['url'];
            }

            if ($image_url !== '') {
                $normalized[] = [
                    'type' => 'input_image',
                    'image_url' => $image_url,
                ];
            }
        }
    }

    return $normalized;
}

/**
 * @param array<int, array<string, mixed>> $input
 * @param array<string, mixed> $ai_params
 */
function xai_attach_image_inputs(array &$input, array $ai_params): void {
    if (empty($ai_params['image_inputs']) || !is_array($ai_params['image_inputs'])) {
        return;
    }

    $last_user_index = null;
    for ($i = count($input) - 1; $i >= 0; $i--) {
        if (($input[$i]['role'] ?? '') === 'user') {
            $last_user_index = $i;
            break;
        }
    }

    if ($last_user_index === null) {
        $input[] = ['role' => 'user', 'content' => ''];
        $last_user_index = array_key_last($input);
    }

    $existing_content = $input[$last_user_index]['content'] ?? '';
    $text_content = '';
    if (is_string($existing_content)) {
        $text_content = $existing_content;
    } elseif (is_array($existing_content)) {
        foreach ($existing_content as $part) {
            if (is_array($part) && in_array(($part['type'] ?? ''), ['text', 'input_text'], true) && isset($part['text'])) {
                $text_content = (string) $part['text'];
                break;
            }
        }
    }

    $content_parts = [
        [
            'type' => 'input_text',
            'text' => $text_content,
        ],
    ];

    foreach ($ai_params['image_inputs'] as $image_input) {
        if (!is_array($image_input) || empty($image_input['base64']) || empty($image_input['type'])) {
            continue;
        }

        $content_parts[] = [
            'type' => 'input_image',
            'image_url' => 'data:' . (string) $image_input['type'] . ';base64,' . (string) $image_input['base64'],
        ];
    }

    $input[$last_user_index]['content'] = $content_parts;
}

/**
 * @param array<string, mixed> $ai_params
 * @return array<int, array<string, mixed>>
 */
function xai_build_tools(array $ai_params): array {
    $tools = [];
    $tool_config = [];

    if (isset($ai_params['xai_web_search_tool_config']) && is_array($ai_params['xai_web_search_tool_config'])) {
        $tool_config = $ai_params['xai_web_search_tool_config'];
    } elseif (isset($ai_params['web_search_tool_config']) && is_array($ai_params['web_search_tool_config'])) {
        $tool_config = $ai_params['web_search_tool_config'];
    }

    $bot_allows_web_search = isset($tool_config['enabled']) && $tool_config['enabled'] === true;
    $frontend_requests_web_search = isset($ai_params['frontend_web_search_active']) && $ai_params['frontend_web_search_active'] === true;

    if ($bot_allows_web_search && $frontend_requests_web_search) {
        $tools[] = ['type' => 'web_search'];
    }

    return $tools;
}

/**
 * @param array<string, mixed> $usage
 * @param array<string, mixed> $response_context
 * @return array<string, mixed>
 */
function xai_normalize_usage(array $usage, array $response_context = []): array {
    $input_tokens = (int) ($usage['input_tokens'] ?? $usage['prompt_tokens'] ?? 0);
    $output_tokens = (int) ($usage['output_tokens'] ?? $usage['completion_tokens'] ?? 0);
    $total_tokens = (int) ($usage['total_tokens'] ?? ($input_tokens + $output_tokens));
    $server_side_tool_usage = xai_extract_server_side_tool_usage($response_context, $usage);
    $provider_raw = $usage;
    if (!empty($server_side_tool_usage) && !isset($provider_raw['server_side_tool_usage'])) {
        $provider_raw['server_side_tool_usage'] = $server_side_tool_usage;
    }

    $normalized = [
        'input_tokens' => $input_tokens,
        'output_tokens' => $output_tokens,
        'total_tokens' => $total_tokens,
        'provider_raw' => $provider_raw,
    ];

    if (!empty($server_side_tool_usage)) {
        $normalized['server_side_tool_usage'] = $server_side_tool_usage;
        $normalized['server_side_tool_units'] = xai_sum_numeric_tool_usage($server_side_tool_usage);
    }

    return $normalized;
}

/**
 * Extracts xAI server-side tool usage counts from Responses payloads.
 *
 * @param array<string, mixed> $response_context
 * @param array<string, mixed> $usage
 * @return array<string, mixed>
 */
function xai_extract_server_side_tool_usage(array $response_context, array $usage = []): array {
    $candidates = [$response_context, $usage];

    foreach ($candidates as $candidate) {
        if (isset($candidate['server_side_tool_usage']) && is_array($candidate['server_side_tool_usage'])) {
            return $candidate['server_side_tool_usage'];
        }
        if (isset($candidate['response']['server_side_tool_usage']) && is_array($candidate['response']['server_side_tool_usage'])) {
            return $candidate['response']['server_side_tool_usage'];
        }
    }

    return [];
}

/**
 * @param mixed $tool_usage
 */
function xai_sum_numeric_tool_usage($tool_usage): int {
    if (is_numeric($tool_usage)) {
        return max(0, (int) $tool_usage);
    }
    if (!is_array($tool_usage)) {
        return 0;
    }

    $total = 0;
    foreach ($tool_usage as $value) {
        $total += xai_sum_numeric_tool_usage($value);
    }

    return $total;
}

/**
 * @param array<string, mixed> $response
 */
function xai_extract_response_text(array $response): string {
    if (isset($response['output_text']) && is_string($response['output_text'])) {
        return trim($response['output_text']);
    }

    $chunks = [];
    if (isset($response['output']) && is_array($response['output'])) {
        foreach ($response['output'] as $output_item) {
            if (!is_array($output_item)) {
                continue;
            }
            if (isset($output_item['content']) && is_string($output_item['content'])) {
                $chunks[] = $output_item['content'];
                continue;
            }
            if (empty($output_item['content']) || !is_array($output_item['content'])) {
                continue;
            }
            foreach ($output_item['content'] as $content_part) {
                if (!is_array($content_part)) {
                    continue;
                }
                $type = isset($content_part['type']) ? (string) $content_part['type'] : '';
                if (in_array($type, ['output_text', 'text'], true) && isset($content_part['text'])) {
                    $chunks[] = (string) $content_part['text'];
                } elseif ($type === 'refusal' && isset($content_part['refusal'])) {
                    $chunks[] = (string) $content_part['refusal'];
                }
            }
        }
    }

    if (empty($chunks) && isset($response['choices'][0]['message']['content'])) {
        $fallback_content = $response['choices'][0]['message']['content'];
        if (is_string($fallback_content)) {
            $chunks[] = $fallback_content;
        }
    }

    return trim(implode('', $chunks));
}

/**
 * Recursively extracts URL citations from Responses payloads and tool events.
 *
 * @param mixed $value
 * @return array<int, array<string, string>>
 */
function xai_extract_citations($value): array {
    $citations = [];
    xai_collect_citations($value, $citations);
    return xai_dedupe_citations($citations);
}

/**
 * @param mixed $value
 * @param array<int, array<string, string>> $citations
 */
function xai_collect_citations($value, array &$citations): void {
    if (!is_array($value)) {
        return;
    }

    if (isset($value['url']) && is_string($value['url']) && preg_match('#^https?://#i', $value['url']) === 1) {
        $citations[] = [
            'url' => $value['url'],
            'title' => isset($value['title']) && is_string($value['title']) ? $value['title'] : $value['url'],
            'snippet' => isset($value['snippet']) && is_string($value['snippet'])
                ? $value['snippet']
                : (isset($value['description']) && is_string($value['description']) ? $value['description'] : ''),
        ];
    }

    foreach ($value as $child) {
        xai_collect_citations($child, $citations);
    }
}

/**
 * @param array<int, array<string, string>> $citations
 * @return array<int, array<string, string>>
 */
function xai_dedupe_citations(array $citations): array {
    $seen = [];
    $deduped = [];

    foreach ($citations as $citation) {
        $url = $citation['url'] ?? '';
        if ($url === '' || isset($seen[$url])) {
            continue;
        }
        $seen[$url] = true;
        $deduped[] = $citation;
    }

    return $deduped;
}
