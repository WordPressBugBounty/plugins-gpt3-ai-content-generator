<?php

namespace WPAICG\AutoGPT\Cron\EventProcessor\Helpers;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Returns the automatic queue-worker policy for the current wake-up source.
 *
 * The time budget is the primary limit. The item caps are final guards for
 * very fast jobs, including expensive generative work.
 *
 * @return array{time_budget_seconds:int,max_items:int,max_expensive_items:int,stale_processing_seconds:int,recovery_limit:int}
 */
function get_automatic_worker_policy_logic(string $source): array
{
    $source = sanitize_key($source);
    $is_server_cron = $source === 'server_cron';
    $policy = [
        'time_budget_seconds' => $is_server_cron ? 50 : 20,
        'max_items' => $is_server_cron ? 50 : 20,
        'max_expensive_items' => 5,
        'stale_processing_seconds' => 30 * MINUTE_IN_SECONDS,
        'recovery_limit' => 100,
    ];

    $php_execution_limit = (int) ini_get('max_execution_time');
    if ($php_execution_limit > 0) {
        $policy['time_budget_seconds'] = min(
            $policy['time_budget_seconds'],
            max(5, $php_execution_limit - 5)
        );
    }

    /**
     * Filters the internal automatic worker policy for advanced deployments.
     *
     * This is deliberately not exposed as a numeric UI setting: the bounded
     * defaults protect shared hosting and provider rate limits.
     *
     * @param array<string, int> $policy Automatic worker policy.
     * @param string             $source Worker source, such as wp_cron or server_cron.
     */
    $filtered_policy = apply_filters('aipkit_automation_worker_policy', $policy, $source);
    if (is_array($filtered_policy)) {
        $policy = array_merge($policy, $filtered_policy);
    }

    return [
        'time_budget_seconds' => max(5, min(120, absint($policy['time_budget_seconds'] ?? 20))),
        'max_items' => max(1, min(100, absint($policy['max_items'] ?? 20))),
        'max_expensive_items' => max(1, min(10, absint($policy['max_expensive_items'] ?? 5))),
        'stale_processing_seconds' => max(
            15 * MINUTE_IN_SECONDS,
            min(DAY_IN_SECONDS, absint($policy['stale_processing_seconds'] ?? (30 * MINUTE_IN_SECONDS)))
        ),
        'recovery_limit' => max(1, min(500, absint($policy['recovery_limit'] ?? 100))),
    ];
}

/**
 * Decides whether another expensive item is likely to fit safely in this pass.
 *
 * The observed average adapts to the site's provider and task complexity. A
 * multiplier plus a small margin prevents a single slow request from causing
 * the worker to start another call too close to its execution deadline.
 *
 * @param array<string, int> $policy Worker policy.
 */
function should_continue_after_expensive_item_logic(
    array $policy,
    int $processed_expensive_items,
    float $expensive_processing_seconds,
    float $elapsed_seconds
): bool {
    $maximum = absint($policy['max_expensive_items'] ?? 1);
    if ($processed_expensive_items >= $maximum) {
        return false;
    }

    $average_seconds = $expensive_processing_seconds / max(1, $processed_expensive_items);
    $predicted_next_seconds = max(2.0, ($average_seconds * 1.5) + 2.0);
    $remaining_seconds = max(0.0, absint($policy['time_budget_seconds'] ?? 0) - $elapsed_seconds);

    return $remaining_seconds >= $predicted_next_seconds;
}

/**
 * Returns whether a queue item may involve multiple long-running AI calls.
 */
function is_expensive_queue_task_logic(string $task_type): bool
{
    return strncmp($task_type, 'content_writing', strlen('content_writing')) === 0
        || $task_type === 'enhance_existing_content'
        || $task_type === 'community_reply_comments';
}

/**
 * Detects a provider rate-limit response so the worker stops starting more work.
 * The failed item remains visible for the existing manual retry flow.
 *
 * @param array<string, mixed> $result Queue processor result.
 */
function is_rate_limited_result_logic(array $result): bool
{
    $message = strtolower((string) ($result['message'] ?? ''));
    if ($message === '') {
        return false;
    }

    return strpos($message, 'rate limit') !== false
        || strpos($message, 'too many requests') !== false
        || preg_match('/\b429\b/', $message) === 1;
}
