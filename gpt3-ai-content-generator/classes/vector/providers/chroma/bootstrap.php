<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/vector/providers/chroma/bootstrap.php

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This file only uses local helper/template variables and does not define public globals.

namespace WPAICG\Vector\Providers;

use WPAICG\Vector\AIPKit_Vector_Base_Provider_Strategy;
use WP_Error;

$method_files = [
    '_request.php',
    'collection-helpers.php',
    'connect.php',
    'create-index-if-not-exists.php',
    'upsert-vectors.php',
    'query-vectors.php',
    'delete-vectors.php',
    'delete-index.php',
    'list-indexes.php',
    'describe-index.php',
    'upload-file-for-vector-store.php',
];

foreach ($method_files as $file) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Chroma Vector Store Provider Strategy.
 */
class AIPKit_Vector_Chroma_Strategy extends AIPKit_Vector_Base_Provider_Strategy
{
    protected $api_key;
    protected $chroma_url;
    protected $tenant = 'default_tenant';
    protected $database = 'default_database';

    protected function _request(string $method, string $path, array $body = [], array $query_params = []): array|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\_request_logic($this, $method, $path, $body, $query_params);
    }

    public function connect(array $config): bool|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\connect_logic($this, $config);
    }

    public function create_index_if_not_exists(string $index_name, array $index_config): array|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\create_index_if_not_exists_logic($this, $index_name, $index_config);
    }

    public function upsert_vectors(string $index_name, array $vectors_data): array|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\upsert_vectors_logic($this, $index_name, $vectors_data);
    }

    public function query_vectors(string $index_name, array $query_vector_param, int $top_k, array $filter = []): array|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\query_vectors_logic($this, $index_name, $query_vector_param, $top_k, $filter);
    }

    public function delete_vectors(string $index_name, array $vector_ids_or_filter): bool|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\delete_vectors_logic($this, $index_name, $vector_ids_or_filter);
    }

    public function delete_index(string $index_name): bool|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\delete_index_logic($this, $index_name);
    }

    public function list_indexes(?int $limit = null, ?string $order = null, ?string $after = null, ?string $before = null): array|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\list_indexes_logic($this, $limit, $order, $after, $before);
    }

    public function describe_index(string $index_name): array|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\describe_index_logic($this, $index_name);
    }

    public function upload_file_for_vector_store(string $file_path, string $original_filename, string $purpose = 'user_data'): array|WP_Error
    {
        return \WPAICG\Vector\Providers\Chroma\Methods\upload_file_for_vector_store_logic($this, $file_path, $original_filename, $purpose);
    }

    public function get_api_key(): ?string
    {
        return $this->api_key;
    }

    public function get_chroma_url(): ?string
    {
        return $this->chroma_url;
    }

    public function get_tenant(): string
    {
        return $this->tenant ?: 'default_tenant';
    }

    public function get_database(): string
    {
        return $this->database ?: 'default_database';
    }

    public function get_is_connected_status(): bool
    {
        return $this->is_connected;
    }

    public function set_api_key(?string $key): void
    {
        $this->api_key = $key ? trim($key) : null;
    }

    public function set_chroma_url(?string $url): void
    {
        $url = $url ? rtrim(trim($url), '/') : null;
        if ($url && str_ends_with($url, '/api/v2')) {
            $url = substr($url, 0, -7);
        }
        $this->chroma_url = $url;
    }

    public function set_tenant(?string $tenant): void
    {
        $tenant = $tenant ? trim($tenant) : '';
        $this->tenant = $tenant !== '' ? $tenant : 'default_tenant';
    }

    public function set_database(?string $database): void
    {
        $database = $database ? trim($database) : '';
        $this->database = $database !== '' ? $database : 'default_database';
    }

    public function set_is_connected_status(bool $status): void
    {
        $this->is_connected = $status;
    }

    public function decode_json_public_wrapper(string $json_string, string $context): array|WP_Error
    {
        return parent::decode_json($json_string, $context);
    }

    public function parse_error_response_public_wrapper($response_body, int $status_code, string $context): string
    {
        return parent::parse_error_response($response_body, $status_code, $context);
    }
}
