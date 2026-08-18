<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsErrorParser
{
    /**
     * Parse both normal Gemini errors and the array-wrapped invalid-key shape.
     *
     * @param string|array<mixed> $response_body Raw or decoded response body.
     * @return array<string, mixed>
     */
    public static function parse($response_body, int $status_code): array
    {
        $decoded = self::decode_body($response_body);
        $error = self::find_error($decoded);
        $message = isset($error['message']) && is_string($error['message'])
            ? trim($error['message'])
            : '';

        if ($message === '' && is_string($response_body)) {
            $message = substr(trim(wp_strip_all_tags($response_body)), 0, 1000);
        }
        if ($message === '') {
            $message = self::fallback_message($status_code);
        }

        return [
            'message' => $message,
            'code' => $error['code'] ?? null,
            'status' => isset($error['status']) && is_string($error['status']) ? $error['status'] : '',
            'details' => isset($error['details']) && is_array($error['details']) ? $error['details'] : [],
            'http_status' => $status_code,
        ];
    }

    /**
     * Create the stable WP_Error contract used by all Interactions consumers.
     *
     * @param string|array<mixed> $response_body Raw or decoded response body.
     * @param mixed               $retry_after_header Retry-After header value.
     */
    public static function to_wp_error($response_body, int $status_code, $retry_after_header = ''): WP_Error
    {
        $parsed = self::parse($response_body, $status_code);
        $data = [
            'status' => $status_code,
            'status_code' => $status_code,
            'provider_code' => $parsed['code'],
            'provider_status' => $parsed['status'],
            'provider_details' => $parsed['details'],
        ];

        $retry_after = self::parse_retry_after($retry_after_header);
        if ($retry_after !== null) {
            $data['retry_after'] = $retry_after;
        }

        return new WP_Error(
            self::error_code_for_status($status_code),
            sprintf(
                /* translators: %1$d: HTTP status code. %2$s: Google API error message. */
                __('Google Interactions API Error (HTTP %1$d): %2$s', 'gpt3-ai-content-generator'),
                $status_code,
                $parsed['message']
            ),
            $data
        );
    }

    /**
     * Convert Retry-After seconds or an HTTP date to a delay in seconds.
     *
     * @param mixed    $header_value Retry-After header value.
     * @param int|null $now           Injectable timestamp for deterministic tests.
     */
    public static function parse_retry_after($header_value, ?int $now = null): ?int
    {
        if (is_array($header_value)) {
            $header_value = reset($header_value);
        }

        if (is_numeric($header_value)) {
            return max(0, (int) ceil((float) $header_value));
        }

        if (!is_string($header_value) || trim($header_value) === '') {
            return null;
        }

        $retry_timestamp = strtotime(trim($header_value));
        if ($retry_timestamp === false) {
            return null;
        }

        $now = $now ?? time();
        return max(0, $retry_timestamp - $now);
    }

    /**
     * @param string|array<mixed> $response_body
     * @return array<mixed>
     */
    private static function decode_body($response_body): array
    {
        if (is_array($response_body)) {
            return $response_body;
        }

        if (!is_string($response_body) || trim($response_body) === '') {
            return [];
        }

        $decoded = json_decode($response_body, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<mixed> $decoded
     * @return array<string, mixed>
     */
    private static function find_error(array $decoded): array
    {
        if (isset($decoded['error']) && is_array($decoded['error'])) {
            return $decoded['error'];
        }

        foreach ($decoded as $item) {
            if (is_array($item) && isset($item['error']) && is_array($item['error'])) {
                return $item['error'];
            }
        }

        return [];
    }

    private static function fallback_message(int $status_code): string
    {
        if ($status_code === 429) {
            return __('Google rate limit exceeded.', 'gpt3-ai-content-generator');
        }
        if ($status_code === 401 || $status_code === 403) {
            return __('Google rejected the API credentials.', 'gpt3-ai-content-generator');
        }
        if ($status_code >= 500) {
            return __('Google is temporarily unavailable.', 'gpt3-ai-content-generator');
        }

        return __('An unknown Google API error occurred.', 'gpt3-ai-content-generator');
    }

    private static function error_code_for_status(int $status_code): string
    {
        if ($status_code === 429) {
            return 'google_interactions_rate_limited';
        }
        if ($status_code === 401 || $status_code === 403) {
            return 'google_interactions_auth_error';
        }
        if ($status_code === 400) {
            return 'google_interactions_invalid_request';
        }
        if ($status_code === 404) {
            return 'google_interactions_not_found';
        }
        if ($status_code >= 500) {
            return 'google_interactions_server_error';
        }

        return 'google_interactions_api_error';
    }
}
