<?php

namespace WPAICG\Core\Providers\Google\FileSearch;

use WPAICG\Core\AIPKit_HTTP_Request;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleFileSearchClient
{
    public const MAX_DOCUMENT_BYTES = 104857600;

    /**
     * @param array<string, mixed> $connection
     * @param array<string, mixed> $options
     * @return array<string, mixed>|WP_Error
     */
    public function create_store(array $connection, string $display_name, array $options = [])
    {
        $payload = GoogleFileSearchRequestBuilder::build_create_store($display_name, $options);
        if (is_wp_error($payload)) {
            return $payload;
        }
        $url = GoogleFileSearchUrlBuilder::build('stores', $connection);
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'POST', $url, $payload);
        if (is_wp_error($response)) {
            return $response;
        }

        return self::normalize_store($response);
    }

    /**
     * @param array<string, mixed> $connection
     * @return array{data:array<int, array<string, mixed>>,next_page_token:string}|WP_Error
     */
    public function list_stores(array $connection, int $page_size = 20, string $page_token = '')
    {
        $url = GoogleFileSearchUrlBuilder::build(
            'stores',
            $connection,
            ['page_size' => $page_size, 'page_token' => $page_token]
        );
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'GET', $url);
        if (is_wp_error($response)) {
            return $response;
        }
        if (isset($response['fileSearchStores']) && !is_array($response['fileSearchStores'])) {
            return self::malformed_resource_error('store list');
        }

        $stores = [];
        foreach ((array) ($response['fileSearchStores'] ?? []) as $store) {
            if (is_array($store)) {
                $normalized_store = self::normalize_store($store);
                if (is_wp_error($normalized_store)) {
                    return $normalized_store;
                }
                $stores[] = $normalized_store;
            }
        }
        return [
            'data' => $stores,
            'next_page_token' => is_string($response['nextPageToken'] ?? null)
                ? (string) $response['nextPageToken']
                : '',
        ];
    }

    /**
     * @param array<string, mixed> $connection
     * @return array<int, array<string, mixed>>|WP_Error
     */
    public function list_all_stores(array $connection, int $max_items = 1000)
    {
        $max_items = max(1, min(1000, $max_items));
        $stores = [];
        $page_token = '';
        do {
            $page = $this->list_stores($connection, min(20, $max_items - count($stores)), $page_token);
            if (is_wp_error($page)) {
                return $page;
            }
            $stores = array_merge($stores, $page['data']);
            $page_token = (string) $page['next_page_token'];
        } while ($page_token !== '' && count($stores) < $max_items);

        return array_slice($stores, 0, $max_items);
    }

    /**
     * @param array<string, mixed> $connection
     * @return array<string, mixed>|WP_Error
     */
    public function get_store(array $connection, string $store_name)
    {
        $url = GoogleFileSearchUrlBuilder::build('store', $connection, ['store_name' => $store_name]);
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'GET', $url);
        return is_wp_error($response) ? $response : self::normalize_store($response);
    }

    /**
     * @param array<string, mixed> $connection
     * @return true|WP_Error
     */
    public function delete_store(array $connection, string $store_name, bool $force = true)
    {
        $url = GoogleFileSearchUrlBuilder::build(
            'store',
            $connection,
            ['store_name' => $store_name, 'force' => $force]
        );
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'DELETE', $url);
        return is_wp_error($response) ? $response : true;
    }

    /**
     * @param array<string, mixed> $connection
     * @return array{data:array<int, array<string, mixed>>,next_page_token:string}|WP_Error
     */
    public function list_documents(
        array $connection,
        string $store_name,
        int $page_size = 20,
        string $page_token = ''
    ) {
        $url = GoogleFileSearchUrlBuilder::build(
            'documents',
            $connection,
            [
                'store_name' => $store_name,
                'page_size' => $page_size,
                'page_token' => $page_token,
            ]
        );
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'GET', $url);
        if (is_wp_error($response)) {
            return $response;
        }
        if (isset($response['documents']) && !is_array($response['documents'])) {
            return self::malformed_resource_error('document list');
        }

        $documents = [];
        foreach ((array) ($response['documents'] ?? []) as $document) {
            if (is_array($document)) {
                $normalized_document = self::normalize_document($document);
                if (is_wp_error($normalized_document)) {
                    return $normalized_document;
                }
                $documents[] = $normalized_document;
            }
        }
        return [
            'data' => $documents,
            'next_page_token' => is_string($response['nextPageToken'] ?? null)
                ? (string) $response['nextPageToken']
                : '',
        ];
    }

    /**
     * @param array<string, mixed> $connection
     * @return array<int, array<string, mixed>>|WP_Error
     */
    public function list_all_documents(array $connection, string $store_name, int $max_items = 1000)
    {
        $max_items = max(1, min(1000, $max_items));
        $documents = [];
        $page_token = '';
        do {
            $page = $this->list_documents(
                $connection,
                $store_name,
                min(20, $max_items - count($documents)),
                $page_token
            );
            if (is_wp_error($page)) {
                return $page;
            }
            $documents = array_merge($documents, $page['data']);
            $page_token = (string) $page['next_page_token'];
        } while ($page_token !== '' && count($documents) < $max_items);

        return array_slice($documents, 0, $max_items);
    }

    /**
     * @param array<string, mixed> $connection
     * @return array<string, mixed>|WP_Error
     */
    public function get_document(array $connection, string $store_name, string $document_name)
    {
        $url = GoogleFileSearchUrlBuilder::build(
            'document',
            $connection,
            ['store_name' => $store_name, 'document_name' => $document_name]
        );
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'GET', $url);
        return is_wp_error($response) ? $response : self::normalize_document($response);
    }

    /**
     * @param array<string, mixed> $connection
     * @return true|WP_Error
     */
    public function delete_document(
        array $connection,
        string $store_name,
        string $document_name,
        bool $force = true
    ) {
        $url = GoogleFileSearchUrlBuilder::build(
            'document',
            $connection,
            [
                'store_name' => $store_name,
                'document_name' => $document_name,
                'force' => $force,
            ]
        );
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'DELETE', $url);
        return is_wp_error($response) ? $response : true;
    }

    /**
     * Import a temporary Files API object into a persistent File Search store.
     *
     * @param array<string, mixed> $connection
     * @param array<string, mixed> $options
     * @return array<string, mixed>|WP_Error
     */
    public function import_file(array $connection, string $store_name, string $file_name, array $options = [])
    {
        $payload = GoogleFileSearchRequestBuilder::build_import_file($file_name, $options);
        if (is_wp_error($payload)) {
            return $payload;
        }
        $url = GoogleFileSearchUrlBuilder::build('import_file', $connection, ['store_name' => $store_name]);
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'POST', $url, $payload);
        return is_wp_error($response) ? $response : $this->normalize_operation($response);
    }

    /**
     * Upload in-memory content through Google's resumable two-request protocol.
     * Pro file handlers remain under lib/ and may provide a streaming transport;
     * this shared primitive also serves free text/Q&A/content ingestion.
     *
     * @param array<string, mixed> $connection
     * @param array<string, mixed> $options
     * @return array<string, mixed>|WP_Error
     */
    public function upload_bytes(
        array $connection,
        string $store_name,
        string $contents,
        string $display_name,
        string $mime_type,
        array $options = []
    ) {
        $content_length = strlen($contents);
        if ($content_length < 1 || $content_length > self::MAX_DOCUMENT_BYTES) {
            return new WP_Error(
                'google_file_search_invalid_upload_size',
                __('Google File Search uploads must contain data and cannot exceed 100 MB.', 'gpt3-ai-content-generator')
            );
        }

        $metadata = GoogleFileSearchRequestBuilder::build_upload_metadata($display_name, $mime_type, $options);
        if (is_wp_error($metadata)) {
            return $metadata;
        }
        $url = GoogleFileSearchUrlBuilder::build('upload_start', $connection, ['store_name' => $store_name]);
        if (is_wp_error($url)) {
            return $url;
        }

        $api_key = self::api_key($connection);
        if (is_wp_error($api_key)) {
            return $api_key;
        }
        $metadata_body = wp_json_encode($metadata);
        if (!is_string($metadata_body)) {
            return self::json_encode_error();
        }
        $start_response = $this->perform_http_request(
            $url,
            [
                'method' => 'POST',
                'timeout' => self::timeout($connection),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $api_key,
                    'X-Goog-Upload-Protocol' => 'resumable',
                    'X-Goog-Upload-Command' => 'start',
                    'X-Goog-Upload-Header-Content-Length' => (string) $content_length,
                    'X-Goog-Upload-Header-Content-Type' => sanitize_mime_type($mime_type),
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
        if ($upload_url === '' || !GoogleFileSearchUrlBuilder::is_trusted_upload_url($upload_url, $connection)) {
            return new WP_Error(
                'google_file_search_invalid_upload_url',
                __('Google did not return a trusted resumable upload URL.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }

        $upload_response = $this->perform_http_request(
            $upload_url,
            [
                'method' => 'POST',
                'timeout' => self::timeout($connection),
                'headers' => [
                    'Content-Length' => (string) $content_length,
                    'Content-Type' => sanitize_mime_type($mime_type),
                    'X-Goog-Upload-Offset' => '0',
                    'X-Goog-Upload-Command' => 'upload, finalize',
                ],
                'body' => $contents,
                'data_format' => 'body',
            ]
        );
        if (is_wp_error($upload_response)) {
            return $upload_response;
        }
        $decoded = $this->decode_http_response($upload_response);
        return is_wp_error($decoded) ? $decoded : $this->normalize_operation($decoded);
    }

    /**
     * Poll one long-running import or upload operation without blocking/sleeping.
     *
     * @param array<string, mixed> $connection
     * @return array<string, mixed>|WP_Error
     */
    public function get_operation(array $connection, string $store_name, string $operation_name)
    {
        $url = GoogleFileSearchUrlBuilder::build(
            'operation',
            $connection,
            ['store_name' => $store_name, 'operation_name' => $operation_name]
        );
        if (is_wp_error($url)) {
            return $url;
        }
        $response = $this->request_json($connection, 'GET', $url);
        return is_wp_error($response) ? $response : $this->normalize_operation($response);
    }

    /**
     * @param array<string, mixed> $raw_store
     * @return array<string, mixed>|WP_Error
     */
    public static function normalize_store(array $raw_store)
    {
        $resource_name = is_string($raw_store['name'] ?? null) ? trim((string) $raw_store['name']) : '';
        $normalized_resource_name = GoogleFileSearchUrlBuilder::normalize_store_name($resource_name);
        if (is_wp_error($normalized_resource_name) || $normalized_resource_name !== $resource_name) {
            return self::malformed_resource_error('store');
        }
        $display_name = is_string($raw_store['displayName'] ?? null) ? trim((string) $raw_store['displayName']) : '';
        return [
            'id' => $resource_name,
            'name' => $display_name !== '' ? $display_name : $resource_name,
            'provider' => 'Google',
            'resource_name' => $resource_name,
            'display_name' => $display_name,
            'create_time' => (string) ($raw_store['createTime'] ?? ''),
            'update_time' => (string) ($raw_store['updateTime'] ?? ''),
            'active_documents_count' => (int) ($raw_store['activeDocumentsCount'] ?? 0),
            'pending_documents_count' => (int) ($raw_store['pendingDocumentsCount'] ?? 0),
            'failed_documents_count' => (int) ($raw_store['failedDocumentsCount'] ?? 0),
            'size_bytes' => (int) ($raw_store['sizeBytes'] ?? 0),
            'embedding_model' => (string) ($raw_store['embeddingModel'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $raw_document
     * @return array<string, mixed>|WP_Error
     */
    public static function normalize_document(array $raw_document)
    {
        $resource_name = is_string($raw_document['name'] ?? null) ? trim((string) $raw_document['name']) : '';
        $normalized_resource_name = GoogleFileSearchUrlBuilder::normalize_document_name($resource_name);
        if (is_wp_error($normalized_resource_name) || $normalized_resource_name !== $resource_name) {
            return self::malformed_resource_error('document');
        }
        $display_name = is_string($raw_document['displayName'] ?? null) ? trim((string) $raw_document['displayName']) : '';
        return [
            'id' => $resource_name,
            'name' => $display_name !== '' ? $display_name : $resource_name,
            'provider' => 'Google',
            'resource_name' => $resource_name,
            'display_name' => $display_name,
            'custom_metadata' => is_array($raw_document['customMetadata'] ?? null)
                ? $raw_document['customMetadata']
                : [],
            'create_time' => (string) ($raw_document['createTime'] ?? ''),
            'update_time' => (string) ($raw_document['updateTime'] ?? ''),
            'state' => sanitize_key((string) ($raw_document['state'] ?? '')),
            'size_bytes' => (int) ($raw_document['sizeBytes'] ?? 0),
            'mime_type' => sanitize_mime_type((string) ($raw_document['mimeType'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $connection
     * @param array<string, mixed>|null $body
     * @return array<string, mixed>|WP_Error
     */
    private function request_json(array $connection, string $method, string $url, ?array $body = null)
    {
        $api_key = self::api_key($connection);
        if (is_wp_error($api_key)) {
            return $api_key;
        }
        $args = [
            'method' => strtoupper($method),
            'timeout' => self::timeout($connection),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $api_key,
            ],
        ];
        if ($body !== null) {
            $encoded = wp_json_encode($body);
            if (!is_string($encoded)) {
                return self::json_encode_error();
            }
            $args['body'] = $encoded;
            $args['data_format'] = 'body';
        }

        $response = $this->perform_http_request($url, $args);
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
                'google_file_search_http_error',
                sprintf(
                    /* translators: %s: Transport error returned by the WordPress HTTP API. */
                    __('Google File Search request failed: %s', 'gpt3-ai-content-generator'),
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
                'google_file_search_invalid_json',
                __('Google returned an invalid JSON File Search response.', 'gpt3-ai-content-generator'),
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

        return GoogleFileSearchErrorParser::to_wp_error(
            (string) wp_remote_retrieve_body($response),
            $status_code > 0 ? $status_code : 500,
            wp_remote_retrieve_header($response, 'retry-after')
        );
    }

    /**
     * @param array<string, mixed> $operation
     * @return array<string, mixed>|WP_Error
     */
    private function normalize_operation(array $operation)
    {
        $name = is_string($operation['name'] ?? null) ? trim((string) $operation['name']) : '';
        if ($name === '') {
            return new WP_Error(
                'google_file_search_malformed_operation',
                __('Google returned a malformed File Search operation.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }
        $done = !empty($operation['done']);
        if ($done && isset($operation['error']) && is_array($operation['error'])) {
            $rpc_code = isset($operation['error']['code']) && is_numeric($operation['error']['code'])
                ? (int) $operation['error']['code']
                : 2;
            $status_code = GoogleFileSearchErrorParser::http_status_from_rpc_code($rpc_code);
            $error = GoogleFileSearchErrorParser::to_wp_error(['error' => $operation['error']], $status_code);
            $error_data = $error->get_error_data();
            $error->add_data(array_merge(
                is_array($error_data) ? $error_data : [],
                ['operation_failed' => true]
            ));
            return $error;
        }

        return [
            'name' => $name,
            'done' => $done,
            'metadata' => is_array($operation['metadata'] ?? null) ? $operation['metadata'] : [],
            'response' => is_array($operation['response'] ?? null) ? $operation['response'] : [],
        ];
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
                'google_file_search_missing_api_key',
                __('A Google API key is required for File Search.', 'gpt3-ai-content-generator'),
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
            'google_file_search_json_encode_error',
            __('Failed to encode the Google File Search request.', 'gpt3-ai-content-generator'),
            ['status' => 500, 'status_code' => 500]
        );
    }

    private static function malformed_resource_error(string $resource_type): WP_Error
    {
        return new WP_Error(
            'google_file_search_malformed_' . sanitize_key($resource_type),
            sprintf(
                /* translators: %s: File Search resource type, such as store or document list. */
                __('Google returned a malformed File Search %s.', 'gpt3-ai-content-generator'),
                $resource_type
            ),
            ['status' => 502, 'status_code' => 502]
        );
    }
}
