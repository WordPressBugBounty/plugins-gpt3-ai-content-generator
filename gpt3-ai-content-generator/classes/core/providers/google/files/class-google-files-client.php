<?php

namespace WPAICG\Core\Providers\Google\Files;

use WPAICG\Core\AIPKit_HTTP_Request;
use WPAICG\Core\Providers\Google\Interactions\GoogleInteractionsErrorParser;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Minimal client for temporary Gemini Files API resources.
 */
final class GoogleFilesClient
{
    public const MAX_UPLOAD_BYTES = 52428800;

    /**
     * @param array<string, mixed> $connection
     * @return array<string, mixed>|WP_Error
     */
    public function upload_path(
        array $connection,
        string $file_path,
        string $display_name,
        string $mime_type
    ) {
        if ($file_path === '' || !is_readable($file_path)) {
            return new WP_Error(
                'google_files_unreadable_upload',
                __('The uploaded file cannot be read.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }
        $file_size = filesize($file_path);
        if (!is_int($file_size) || $file_size < 1 || $file_size > self::MAX_UPLOAD_BYTES) {
            return new WP_Error(
                'google_files_invalid_upload_size',
                __('Google document uploads must contain data and cannot exceed 50 MB.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }

        $mime_type = sanitize_mime_type($mime_type);
        if ($mime_type === '') {
            return new WP_Error(
                'google_files_invalid_mime_type',
                __('A valid MIME type is required for a Google file upload.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }
        $api_key = self::api_key($connection);
        if (is_wp_error($api_key)) {
            return $api_key;
        }
        $start_url = GoogleFilesUrlBuilder::build('upload_start', $connection);
        if (is_wp_error($start_url)) {
            return $start_url;
        }

        $metadata_body = wp_json_encode([
            'file' => [
                'display_name' => sanitize_text_field($display_name),
            ],
        ]);
        if (!is_string($metadata_body)) {
            return self::json_encode_error();
        }

        $start_response = $this->perform_http_request(
            $start_url,
            [
                'method' => 'POST',
                'timeout' => self::timeout($connection),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $api_key,
                    'X-Goog-Upload-Protocol' => 'resumable',
                    'X-Goog-Upload-Command' => 'start',
                    'X-Goog-Upload-Header-Content-Length' => (string) $file_size,
                    'X-Goog-Upload-Header-Content-Type' => $mime_type,
                ],
                'body' => $metadata_body,
                'data_format' => 'body',
            ]
        );
        if (is_wp_error($start_response)) {
            return $start_response;
        }
        $start_error = self::http_error_from_response($start_response);
        if ($start_error !== null) {
            return $start_error;
        }

        $upload_url = wp_remote_retrieve_header($start_response, 'x-goog-upload-url');
        $upload_url = is_string($upload_url) ? trim($upload_url) : '';
        if ($upload_url === '' || !GoogleFilesUrlBuilder::is_trusted_upload_url($upload_url, $connection)) {
            return new WP_Error(
                'google_files_invalid_upload_url',
                __('Google did not return a trusted resumable upload URL.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reads a validated temporary upload for the remote Files API request.
        $contents = file_get_contents($file_path);
        if (!is_string($contents) || strlen($contents) !== $file_size) {
            return new WP_Error(
                'google_files_upload_read_error',
                __('The uploaded file could not be read completely.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }

        $upload_response = $this->perform_http_request(
            $upload_url,
            [
                'method' => 'POST',
                'timeout' => self::timeout($connection),
                'headers' => [
                    'Content-Length' => (string) $file_size,
                    'Content-Type' => $mime_type,
                    'X-Goog-Upload-Offset' => '0',
                    'X-Goog-Upload-Command' => 'upload, finalize',
                ],
                'body' => $contents,
                'data_format' => 'body',
            ]
        );
        unset($contents);
        if (is_wp_error($upload_response)) {
            return $upload_response;
        }

        $decoded = $this->decode_http_response($upload_response);
        return is_wp_error($decoded) ? $decoded : self::normalize_file($decoded, $connection);
    }

    /**
     * @param array<string, mixed> $connection
     * @return array<string, mixed>|WP_Error
     */
    public function get(array $connection, string $file_name)
    {
        $url = GoogleFilesUrlBuilder::build('get', $connection, $file_name);
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'GET', $url);
        return is_wp_error($response) ? $response : self::normalize_file($response, $connection);
    }

    /**
     * @param array<string, mixed> $connection
     * @return true|WP_Error
     */
    public function delete(array $connection, string $file_name)
    {
        $url = GoogleFilesUrlBuilder::build('delete', $connection, $file_name);
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'DELETE', $url);
        if (is_wp_error($response)) {
            $error_data = $response->get_error_data();
            $status = is_array($error_data)
                ? (int) ($error_data['status_code'] ?? ($error_data['status'] ?? 0))
                : 0;
            return $status === 404 ? true : $response;
        }
        return true;
    }

    /**
     * @param array<string, mixed> $raw_response
     * @param array<string, mixed> $connection
     * @return array<string, mixed>|WP_Error
     */
    public static function normalize_file(array $raw_response, array $connection = [])
    {
        $raw_file = isset($raw_response['file']) && is_array($raw_response['file'])
            ? $raw_response['file']
            : $raw_response;
        $name = is_string($raw_file['name'] ?? null) ? trim((string) $raw_file['name']) : '';
        $normalized_name = GoogleFilesUrlBuilder::normalize_file_name($name);
        $uri = is_string($raw_file['uri'] ?? null) ? trim((string) $raw_file['uri']) : '';
        $mime_type = sanitize_mime_type((string) ($raw_file['mimeType'] ?? ''));
        if (
            is_wp_error($normalized_name)
            || $normalized_name !== $name
            || $uri === ''
            || !GoogleFilesUrlBuilder::is_trusted_file_uri($uri, $connection)
            || $mime_type === ''
        ) {
            return new WP_Error(
                'google_files_malformed_resource',
                __('Google returned a malformed Files API resource.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }

        $state = strtoupper(sanitize_key((string) ($raw_file['state'] ?? 'ACTIVE')));
        if (!in_array($state, ['PROCESSING', 'ACTIVE', 'FAILED'], true)) {
            $state = 'PROCESSING';
        }

        return [
            'name' => $name,
            'uri' => $uri,
            'mime_type' => $mime_type,
            'display_name' => sanitize_text_field((string) ($raw_file['displayName'] ?? '')),
            'size_bytes' => absint($raw_file['sizeBytes'] ?? 0),
            'state' => $state,
            'create_time' => sanitize_text_field((string) ($raw_file['createTime'] ?? '')),
            'update_time' => sanitize_text_field((string) ($raw_file['updateTime'] ?? '')),
            'expiration_time' => sanitize_text_field((string) ($raw_file['expirationTime'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $connection
     * @return array<string, mixed>|WP_Error
     */
    private function request_json(array $connection, string $method, string $url)
    {
        $api_key = self::api_key($connection);
        if (is_wp_error($api_key)) {
            return $api_key;
        }
        $response = $this->perform_http_request(
            $url,
            [
                'method' => strtoupper($method),
                'timeout' => self::timeout($connection),
                'headers' => [
                    'Accept' => 'application/json',
                    'x-goog-api-key' => $api_key,
                ],
            ]
        );
        return is_wp_error($response) ? $response : $this->decode_http_response($response);
    }

    /**
     * @return array|WP_Error
     */
    private function perform_http_request(string $url, array $args)
    {
        $response = class_exists(AIPKit_HTTP_Request::class)
            ? AIPKit_HTTP_Request::request($url, $args, true)
            : wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return new WP_Error(
                'google_files_http_error',
                sprintf(
                    /* translators: %s: Transport error returned by the WordPress HTTP API. */
                    __('Google Files API request failed: %s', 'gpt3-ai-content-generator'),
                    $response->get_error_message()
                ),
                ['status' => 503, 'status_code' => 503]
            );
        }
        return $response;
    }

    /**
     * @param array $response WordPress HTTP response.
     * @return array<string, mixed>|WP_Error
     */
    private function decode_http_response(array $response)
    {
        $error = self::http_error_from_response($response);
        if ($error !== null) {
            return $error;
        }
        $body = (string) wp_remote_retrieve_body($response);
        if (trim($body) === '') {
            return [];
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'google_files_invalid_json',
                __('Google returned an invalid JSON Files API response.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }
        return $decoded;
    }

    /**
     * @param array $response WordPress HTTP response.
     */
    private static function http_error_from_response(array $response): ?WP_Error
    {
        $status_code = (int) wp_remote_retrieve_response_code($response);
        if ($status_code >= 200 && $status_code < 300) {
            return null;
        }
        return GoogleInteractionsErrorParser::to_wp_error(
            (string) wp_remote_retrieve_body($response),
            $status_code > 0 ? $status_code : 500,
            wp_remote_retrieve_header($response, 'retry-after')
        );
    }

    /**
     * @param array<string, mixed> $connection
     * @return string|WP_Error
     */
    private static function api_key(array $connection)
    {
        $api_key = isset($connection['api_key']) && is_string($connection['api_key'])
            ? trim($connection['api_key'])
            : '';
        if ($api_key === '') {
            return new WP_Error(
                'google_files_missing_api_key',
                __('A Google API key is required for file uploads.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }
        return $api_key;
    }

    /**
     * @param array<string, mixed> $connection
     */
    private static function timeout(array $connection): int
    {
        return isset($connection['timeout']) && is_numeric($connection['timeout'])
            ? max(1, min(300, (int) $connection['timeout']))
            : 120;
    }

    private static function json_encode_error(): WP_Error
    {
        return new WP_Error(
            'google_files_json_encode_error',
            __('Failed to encode the Google Files API request.', 'gpt3-ai-content-generator'),
            ['status' => 500, 'status_code' => 500]
        );
    }
}
