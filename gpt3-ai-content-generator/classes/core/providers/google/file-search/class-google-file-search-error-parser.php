<?php

namespace WPAICG\Core\Providers\Google\FileSearch;

use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsErrorParser;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleFileSearchErrorParser
{
    /**
     * Convert google.rpc.Code values returned inside long-running operations
     * into the HTTP status used by the rest of the provider error contract.
     */
    public static function http_status_from_rpc_code(int $rpc_code): int
    {
        $status_map = [
            1 => 499, // CANCELLED.
            2 => 500, // UNKNOWN.
            3 => 400, // INVALID_ARGUMENT.
            4 => 504, // DEADLINE_EXCEEDED.
            5 => 404, // NOT_FOUND.
            6 => 409, // ALREADY_EXISTS.
            7 => 403, // PERMISSION_DENIED.
            8 => 429, // RESOURCE_EXHAUSTED.
            9 => 400, // FAILED_PRECONDITION.
            10 => 409, // ABORTED.
            11 => 400, // OUT_OF_RANGE.
            12 => 501, // UNIMPLEMENTED.
            13 => 500, // INTERNAL.
            14 => 503, // UNAVAILABLE.
            15 => 500, // DATA_LOSS.
            16 => 401, // UNAUTHENTICATED.
        ];

        return $status_map[$rpc_code] ?? 500;
    }

    /**
     * @param string|array<mixed> $response_body
     * @param mixed               $retry_after_header
     */
    public static function to_wp_error($response_body, int $status_code, $retry_after_header = ''): WP_Error
    {
        $parsed = GoogleInteractionsErrorParser::parse($response_body, $status_code);
        $data = [
            'status' => $status_code,
            'status_code' => $status_code,
            'provider_code' => $parsed['code'],
            'provider_status' => $parsed['status'],
            'provider_details' => $parsed['details'],
        ];
        $retry_after = GoogleInteractionsErrorParser::parse_retry_after($retry_after_header);
        if ($retry_after !== null) {
            $data['retry_after'] = $retry_after;
        }

        return new WP_Error(
            self::error_code_for_status($status_code),
            sprintf(
                /* translators: %1$d: HTTP status code. %2$s: Google API error message. */
                __('Google File Search API Error (HTTP %1$d): %2$s', 'gpt3-ai-content-generator'),
                $status_code,
                $parsed['message']
            ),
            $data
        );
    }

    private static function error_code_for_status(int $status_code): string
    {
        if ($status_code === 429) {
            return 'google_file_search_rate_limited';
        }
        if ($status_code === 401 || $status_code === 403) {
            return 'google_file_search_auth_error';
        }
        if ($status_code === 400) {
            return 'google_file_search_invalid_request';
        }
        if ($status_code === 404) {
            return 'google_file_search_not_found';
        }
        if ($status_code >= 500) {
            return 'google_file_search_server_error';
        }

        return 'google_file_search_api_error';
    }
}
