<?php

namespace WPAICG\Core\Providers\Google\Files;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Builds and validates endpoints for Google's temporary Files API.
 */
final class GoogleFilesUrlBuilder
{
    // phpcs:ignore PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Provider-specific API transport.
    public const DEFAULT_BASE_URL = 'https://generativelanguage.googleapis.com';
    public const API_VERSION = 'v1beta';

    /**
     * @param array<string, mixed> $connection
     * @return string|WP_Error
     */
    public static function build(string $operation, array $connection = [], string $file_name = '')
    {
        $base_url = self::base_url($connection);
        if (is_wp_error($base_url)) {
            return $base_url;
        }

        if ($operation === 'upload_start') {
            return $base_url . '/upload/' . self::API_VERSION . '/files';
        }

        $normalized_file_name = self::normalize_file_name($file_name);
        if (is_wp_error($normalized_file_name)) {
            return $normalized_file_name;
        }
        if (in_array($operation, ['get', 'delete'], true)) {
            return $base_url . '/' . self::API_VERSION . '/' . $normalized_file_name;
        }

        return new WP_Error(
            'google_files_unknown_operation',
            __('Unsupported Google Files API operation.', 'gpt3-ai-content-generator')
        );
    }

    /**
     * @return string|WP_Error
     */
    public static function normalize_file_name(string $file_name)
    {
        $file_name = trim($file_name, "/ \t\n\r\0\x0B");
        if (strpos($file_name, 'files/') !== 0) {
            return new WP_Error(
                'google_files_invalid_file_name',
                __('A complete Google Files API resource name is required.', 'gpt3-ai-content-generator')
            );
        }

        $file_id = (string) substr($file_name, strlen('files/'));
        if ($file_id === '' || strlen($file_id) > 256 || !preg_match('/^[A-Za-z0-9._~-]+$/', $file_id)) {
            return new WP_Error(
                'google_files_invalid_file_name',
                __('The Google Files API resource name is invalid.', 'gpt3-ai-content-generator')
            );
        }

        return 'files/' . $file_id;
    }

    /**
     * Google returns an opaque resumable URL. Restrict it to the configured
     * provider origin before sending private file bytes to it.
     *
     * @param array<string, mixed> $connection
     */
    public static function is_trusted_upload_url(string $upload_url, array $connection = []): bool
    {
        $base_url = self::base_url($connection);
        return !is_wp_error($base_url) && self::has_same_trusted_origin($upload_url, $base_url);
    }

    /**
     * @param array<string, mixed> $connection
     */
    public static function is_trusted_file_uri(string $file_uri, array $connection = []): bool
    {
        $base_url = self::base_url($connection);
        if (is_wp_error($base_url) || !self::has_same_trusted_origin($file_uri, $base_url)) {
            return false;
        }

        $uri_parts = wp_parse_url($file_uri);
        $base_parts = wp_parse_url($base_url);
        if (!is_array($uri_parts) || !is_array($base_parts) || isset($uri_parts['query']) || isset($uri_parts['fragment'])) {
            return false;
        }

        $base_path = rtrim((string) ($base_parts['path'] ?? ''), '/');
        $expected_prefix = $base_path . '/' . self::API_VERSION . '/files/';
        $uri_path = (string) ($uri_parts['path'] ?? '');
        if (strpos($uri_path, $expected_prefix) !== 0) {
            return false;
        }

        $file_id = (string) substr($uri_path, strlen($expected_prefix));
        return $file_id !== ''
            && strlen($file_id) <= 256
            && (bool) preg_match('/^[A-Za-z0-9._~-]+$/', $file_id);
    }

    /**
     * @param array<string, mixed> $connection
     * @return string|WP_Error
     */
    private static function base_url(array $connection)
    {
        $base_url = isset($connection['base_url']) && is_string($connection['base_url'])
            ? rtrim(trim($connection['base_url']), '/')
            : self::DEFAULT_BASE_URL;
        if ($base_url === '') {
            $base_url = self::DEFAULT_BASE_URL;
        }

        $parts = wp_parse_url($base_url);
        if (!is_array($parts)) {
            return self::invalid_base_url_error();
        }
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $has_credentials = isset($parts['user']) || isset($parts['pass']);
        $has_query_or_fragment = isset($parts['query']) || isset($parts['fragment']);
        $is_local_http = $scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true);
        if (($scheme !== 'https' && !$is_local_http) || $host === '' || $has_credentials || $has_query_or_fragment) {
            return self::invalid_base_url_error();
        }

        return $base_url;
    }

    private static function has_same_trusted_origin(string $candidate_url, string $base_url): bool
    {
        $candidate = wp_parse_url($candidate_url);
        $base = wp_parse_url($base_url);
        if (!is_array($candidate) || !is_array($base)) {
            return false;
        }

        $candidate_scheme = strtolower((string) ($candidate['scheme'] ?? ''));
        $candidate_host = strtolower((string) ($candidate['host'] ?? ''));
        $base_scheme = strtolower((string) ($base['scheme'] ?? ''));
        $base_host = strtolower((string) ($base['host'] ?? ''));
        $candidate_port = self::effective_port($candidate, $candidate_scheme);
        $base_port = self::effective_port($base, $base_scheme);
        $has_credentials = isset($candidate['user']) || isset($candidate['pass']);
        $is_local_http = $candidate_scheme === 'http'
            && in_array($candidate_host, ['localhost', '127.0.0.1', '::1'], true);

        return ($candidate_scheme === 'https' || $is_local_http)
            && $candidate_scheme === $base_scheme
            && $candidate_host !== ''
            && $candidate_host === $base_host
            && $candidate_port === $base_port
            && !$has_credentials;
    }

    /**
     * @param array<string, mixed> $parts
     */
    private static function effective_port(array $parts, string $scheme): int
    {
        if (isset($parts['port'])) {
            return (int) $parts['port'];
        }
        return $scheme === 'https' ? 443 : 80;
    }

    private static function invalid_base_url_error(): WP_Error
    {
        return new WP_Error(
            'google_files_invalid_base_url',
            __('Google Files API requires an HTTPS endpoint or a local development endpoint.', 'gpt3-ai-content-generator')
        );
    }
}
