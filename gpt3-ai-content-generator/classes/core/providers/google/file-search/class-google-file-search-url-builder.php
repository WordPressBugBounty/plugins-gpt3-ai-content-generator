<?php

namespace WPAICG\Core\Providers\Google\FileSearch;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleFileSearchUrlBuilder
{
    // phpcs:ignore PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Provider-specific API transport.
    public const DEFAULT_BASE_URL = 'https://generativelanguage.googleapis.com';
    public const API_VERSION = 'v1beta';

    /**
     * Build one File Search resource URL without putting credentials in it.
     *
     * @param string               $operation stores, store, documents, document, import_file, operation, or upload_start.
     * @param array<string, mixed> $connection Google connection values.
     * @param array<string, mixed> $params Resource and pagination values.
     * @return string|WP_Error
     */
    public static function build(string $operation, array $connection = [], array $params = [])
    {
        $base_url = self::base_url($connection);
        if (is_wp_error($base_url)) {
            return $base_url;
        }

        $api_root = $base_url . '/' . self::API_VERSION;
        if ($operation === 'stores') {
            return self::with_list_query($api_root . '/fileSearchStores', $params);
        }

        $store_name = self::normalize_store_name((string) ($params['store_name'] ?? ''));
        if (is_wp_error($store_name)) {
            return $store_name;
        }

        if ($operation === 'store') {
            return self::with_force_query($api_root . '/' . $store_name, $params);
        }
        if ($operation === 'documents') {
            return self::with_list_query($api_root . '/' . $store_name . '/documents', $params);
        }
        if ($operation === 'import_file') {
            return $api_root . '/' . $store_name . ':importFile';
        }
        if ($operation === 'upload_start') {
            return $base_url . '/upload/' . self::API_VERSION . '/' . $store_name . ':uploadToFileSearchStore';
        }
        if ($operation === 'document') {
            $document_name = self::normalize_document_name((string) ($params['document_name'] ?? ''), $store_name);
            if (is_wp_error($document_name)) {
                return $document_name;
            }
            return self::with_force_query($api_root . '/' . $document_name, $params);
        }
        if ($operation === 'operation') {
            $operation_name = self::normalize_operation_name((string) ($params['operation_name'] ?? ''), $store_name);
            if (is_wp_error($operation_name)) {
                return $operation_name;
            }
            return $api_root . '/' . $operation_name;
        }

        return new WP_Error(
            'google_file_search_unknown_operation',
            __('Unsupported Google File Search operation.', 'gpt3-ai-content-generator')
        );
    }

    /**
     * @return string|WP_Error
     */
    public static function normalize_store_name(string $store_name)
    {
        $store_name = trim($store_name, "/ \t\n\r\0\x0B");
        if (strpos($store_name, 'fileSearchStores/') === 0) {
            $store_name = (string) substr($store_name, strlen('fileSearchStores/'));
        }
        if (!self::is_resource_id($store_name, 40)) {
            return new WP_Error(
                'google_file_search_invalid_store_name',
                __('Google store names must contain only lowercase letters, numbers, and internal hyphens.', 'gpt3-ai-content-generator')
            );
        }

        return 'fileSearchStores/' . $store_name;
    }

    /**
     * @return string|WP_Error
     */
    public static function normalize_document_name(string $document_name, string $expected_store_name = '')
    {
        $document_name = trim($document_name, "/ \t\n\r\0\x0B");
        $matches = [];
        if (!preg_match('#^fileSearchStores/([^/]+)/documents/([^/]+)$#', $document_name, $matches)) {
            return new WP_Error(
                'google_file_search_invalid_document_name',
                __('A complete Google File Search document resource name is required.', 'gpt3-ai-content-generator')
            );
        }
        if (!self::is_resource_id($matches[1], 40) || !self::is_resource_id($matches[2], 40)) {
            return new WP_Error(
                'google_file_search_invalid_document_name',
                __('The Google File Search document resource name is invalid.', 'gpt3-ai-content-generator')
            );
        }

        if ($expected_store_name !== '') {
            $normalized_store = self::normalize_store_name($expected_store_name);
            if (is_wp_error($normalized_store) || $normalized_store !== 'fileSearchStores/' . $matches[1]) {
                return new WP_Error(
                    'google_file_search_document_store_mismatch',
                    __('The Google File Search document does not belong to the selected store.', 'gpt3-ai-content-generator')
                );
            }
        }

        return 'fileSearchStores/' . $matches[1] . '/documents/' . $matches[2];
    }

    /**
     * @return string|WP_Error
     */
    public static function normalize_file_name(string $file_name)
    {
        $file_name = trim($file_name, "/ \t\n\r\0\x0B");
        if (strpos($file_name, 'files/') !== 0) {
            return new WP_Error(
                'google_file_search_invalid_file_name',
                __('A complete Google Files API resource name is required.', 'gpt3-ai-content-generator')
            );
        }
        $file_id = (string) substr($file_name, strlen('files/'));
        if ($file_id === '' || strlen($file_id) > 256 || !preg_match('/^[A-Za-z0-9._~-]+$/', $file_id)) {
            return new WP_Error(
                'google_file_search_invalid_file_name',
                __('The Google Files API resource name is invalid.', 'gpt3-ai-content-generator')
            );
        }

        return 'files/' . $file_id;
    }

    /**
     * @return string|WP_Error
     */
    public static function normalize_operation_name(string $operation_name, string $expected_store_name = '')
    {
        $operation_name = trim($operation_name, "/ \t\n\r\0\x0B");
        $matches = [];
        if (!preg_match('#^fileSearchStores/([^/]+)/(upload/)?operations/([^/]+)$#', $operation_name, $matches)) {
            return new WP_Error(
                'google_file_search_invalid_operation_name',
                __('A complete Google File Search operation resource name is required.', 'gpt3-ai-content-generator')
            );
        }
        if (
            !self::is_resource_id($matches[1], 40)
            || strlen($matches[3]) > 256
            || !preg_match('/^[A-Za-z0-9._~-]+$/', $matches[3])
        ) {
            return new WP_Error(
                'google_file_search_invalid_operation_name',
                __('The Google File Search operation resource name is invalid.', 'gpt3-ai-content-generator')
            );
        }

        if ($expected_store_name !== '') {
            $normalized_store = self::normalize_store_name($expected_store_name);
            if (is_wp_error($normalized_store) || $normalized_store !== 'fileSearchStores/' . $matches[1]) {
                return new WP_Error(
                    'google_file_search_operation_store_mismatch',
                    __('The Google File Search operation does not belong to the selected store.', 'gpt3-ai-content-generator')
                );
            }
        }

        return 'fileSearchStores/' . $matches[1] . '/' . ($matches[2] ?? '') . 'operations/' . $matches[3];
    }

    /**
     * Validate a resumable upload URL before sending document bytes to it.
     */
    public static function is_trusted_upload_url(string $upload_url, array $connection = []): bool
    {
        $upload_parts = wp_parse_url($upload_url);
        $base_url = self::base_url($connection);
        $base_parts = is_wp_error($base_url) ? false : wp_parse_url($base_url);
        if (!is_array($upload_parts) || !is_array($base_parts)) {
            return false;
        }

        $upload_scheme = strtolower((string) ($upload_parts['scheme'] ?? ''));
        $upload_host = strtolower((string) ($upload_parts['host'] ?? ''));
        $base_host = strtolower((string) ($base_parts['host'] ?? ''));
        $upload_port = self::effective_port($upload_parts, $upload_scheme);
        $base_scheme = strtolower((string) ($base_parts['scheme'] ?? ''));
        $base_port = self::effective_port($base_parts, $base_scheme);
        $has_credentials = isset($upload_parts['user']) || isset($upload_parts['pass']);
        $is_local_http = $upload_scheme === 'http' && in_array($upload_host, ['localhost', '127.0.0.1', '::1'], true);

        return ($upload_scheme === 'https' || $is_local_http)
            && $upload_host !== ''
            && $upload_host === $base_host
            && $upload_port === $base_port
            && !$has_credentials;
    }

    /**
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

    private static function invalid_base_url_error(): WP_Error
    {
        return new WP_Error(
            'google_file_search_invalid_base_url',
            __('Google File Search requires an HTTPS endpoint or a local development endpoint.', 'gpt3-ai-content-generator')
        );
    }

    private static function is_resource_id(string $resource_id, int $max_length): bool
    {
        $length = strlen($resource_id);
        return $length >= 1
            && $length <= $max_length
            && (bool) preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $resource_id);
    }

    /**
     * @param array<string, mixed> $url_parts
     */
    private static function effective_port(array $url_parts, string $scheme): int
    {
        if (isset($url_parts['port']) && is_numeric($url_parts['port'])) {
            return (int) $url_parts['port'];
        }

        return $scheme === 'https' ? 443 : 80;
    }

    private static function with_list_query(string $url, array $params): string
    {
        $query = [];
        if (isset($params['page_size'])) {
            $query['pageSize'] = max(1, min(20, (int) $params['page_size']));
        }
        if (isset($params['page_token']) && is_string($params['page_token']) && trim($params['page_token']) !== '') {
            $query['pageToken'] = trim($params['page_token']);
        }

        return empty($query) ? $url : $url . '?' . self::query_string($query);
    }

    private static function with_force_query(string $url, array $params): string
    {
        if (!array_key_exists('force', $params)) {
            return $url;
        }

        return $url . '?' . self::query_string(['force' => !empty($params['force']) ? 'true' : 'false']);
    }

    /**
     * @param array<string, scalar> $query
     */
    private static function query_string(array $query): string
    {
        $parts = [];
        foreach ($query as $key => $value) {
            $parts[] = rawurlencode((string) $key) . '=' . rawurlencode((string) $value);
        }
        return implode('&', $parts);
    }
}
