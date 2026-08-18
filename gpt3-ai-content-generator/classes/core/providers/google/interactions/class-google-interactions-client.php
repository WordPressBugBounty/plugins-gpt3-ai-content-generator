<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WPAICG\Core\AIPKit_HTTP_Request;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsClient
{
    /**
     * Create an interaction that must produce text.
     *
     * @param array<string, mixed> $connection Provider connection values.
     * @param string               $model      Gemini model ID.
     * @param string|array<mixed>  $input      Text or typed input steps.
     * @param array<string, mixed> $options    Interactions request options.
     * @return array<string, mixed>|WP_Error
     */
    public function create_text(array $connection, string $model, $input, array $options = [])
    {
        $result = $this->create($connection, $model, $input, $options);
        if (is_wp_error($result)) {
            return $result;
        }

        return GoogleInteractionsResponseParser::require_text_result($result);
    }

    /**
     * Create a non-streaming Gemini interaction.
     *
     * Streaming uses the same builders and parser but a separate cURL transport
     * in the shared stream pipeline.
     *
     * @param array<string, mixed> $connection Provider connection values.
     * @param string               $model      Gemini model ID.
     * @param string|array<mixed>  $input      Text or typed input steps.
     * @param array<string, mixed> $options    Interactions request options.
     * @return array<string, mixed>|WP_Error
     */
    public function create(array $connection, string $model, $input, array $options = [])
    {
        if (!empty($options['stream'])) {
            return new WP_Error(
                'google_interactions_stream_transport_required',
                __('Streaming Google interactions must use the streaming transport.', 'gpt3-ai-content-generator')
            );
        }

        $api_key = isset($connection['api_key']) && is_string($connection['api_key'])
            ? trim($connection['api_key'])
            : '';
        if ($api_key === '') {
            return new WP_Error(
                'google_interactions_missing_api_key',
                __('A Google API key is required.', 'gpt3-ai-content-generator'),
                ['status' => 400, 'status_code' => 400]
            );
        }

        $url = GoogleInteractionsUrlBuilder::build($connection);
        if (is_wp_error($url)) {
            return $url;
        }

        $payload = GoogleInteractionsRequestBuilder::build($model, $input, $options);
        if (is_wp_error($payload)) {
            return $payload;
        }

        $body = wp_json_encode($payload);
        if (!is_string($body)) {
            return new WP_Error(
                'google_interactions_json_encode_error',
                __('Failed to encode the Google interaction request.', 'gpt3-ai-content-generator'),
                ['status' => 500, 'status_code' => 500]
            );
        }

        $timeout = isset($connection['timeout']) && is_numeric($connection['timeout'])
            ? max(1, min(300, (int) $connection['timeout']))
            : 120;
        $request_args = [
            'method' => 'POST',
            'timeout' => $timeout,
            'headers' => self::headers($api_key, false),
            'body' => $body,
            'data_format' => 'body',
        ];

        $response = class_exists(AIPKit_HTTP_Request::class)
            ? AIPKit_HTTP_Request::request($url, $request_args, true)
            : wp_remote_request($url, $request_args);

        if (is_wp_error($response)) {
            return new WP_Error(
                'google_interactions_http_error',
                sprintf(
                    /* translators: %s: Transport error returned by the WordPress HTTP API. */
                    __('Google interaction request failed: %s', 'gpt3-ai-content-generator'),
                    $response->get_error_message()
                ),
                ['status' => 503, 'status_code' => 503]
            );
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $response_body = (string) wp_remote_retrieve_body($response);
        if ($status_code < 200 || $status_code >= 300) {
            return GoogleInteractionsErrorParser::to_wp_error(
                $response_body,
                $status_code,
                wp_remote_retrieve_header($response, 'retry-after')
            );
        }

        $decoded = json_decode($response_body, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error(
                'google_interactions_invalid_json',
                __('Google returned an invalid JSON interaction response.', 'gpt3-ai-content-generator'),
                ['status' => 502, 'status_code' => 502]
            );
        }

        return GoogleInteractionsResponseParser::parse($decoded);
    }

    /**
     * @return array<string, string>
     */
    public static function headers(string $api_key, bool $stream): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'x-goog-api-key' => $api_key,
            'Api-Revision' => '2026-05-20',
        ];
        if ($stream) {
            $headers['Accept'] = 'text/event-stream';
            $headers['Cache-Control'] = 'no-cache';
        }

        return $headers;
    }
}
