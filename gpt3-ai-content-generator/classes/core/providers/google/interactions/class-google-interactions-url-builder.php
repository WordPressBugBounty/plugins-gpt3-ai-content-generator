<?php

namespace WPAICG\Core\Providers\Google\Interactions;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

final class GoogleInteractionsUrlBuilder
{
    // phpcs:ignore PluginCheck.CodeAnalysis.AIProvider.DirectIntegration -- Provider-specific API transport.
    public const DEFAULT_BASE_URL = 'https://generativelanguage.googleapis.com';
    public const STABLE_API_VERSION = 'v1';
    public const PREVIEW_API_VERSION = 'v1beta';

    /**
     * Build a Gemini Interactions endpoint without putting the API key in the URL.
     *
     * @param array<string, mixed> $connection Connection values.
     * @return string|WP_Error
     */
    public static function build(array $connection)
    {
        $base_url = isset($connection['base_url']) && is_string($connection['base_url'])
            ? rtrim(trim($connection['base_url']), '/')
            : self::DEFAULT_BASE_URL;
        $api_version = isset($connection['api_version']) && is_string($connection['api_version'])
            ? trim($connection['api_version'], "/ \t\n\r\0\x0B")
            : self::STABLE_API_VERSION;

        if ($base_url === '') {
            $base_url = self::DEFAULT_BASE_URL;
        }
        if ($api_version === '') {
            $api_version = self::STABLE_API_VERSION;
        }

        if (!self::is_allowed_base_url($base_url)) {
            return new WP_Error(
                'google_interactions_invalid_base_url',
                __('Google Interactions requires an HTTPS endpoint or a local development endpoint.', 'gpt3-ai-content-generator')
            );
        }

        if (!in_array($api_version, [self::STABLE_API_VERSION, self::PREVIEW_API_VERSION], true)) {
            return new WP_Error(
                'google_interactions_invalid_api_version',
                __('Google Interactions supports only the v1 and v1beta API versions.', 'gpt3-ai-content-generator')
            );
        }

        return $base_url . '/' . $api_version . '/interactions';
    }

    private static function is_allowed_base_url(string $base_url): bool
    {
        $parts = wp_parse_url($base_url);
        if (!is_array($parts)) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $has_credentials = isset($parts['user']) || isset($parts['pass']);
        $has_query_or_fragment = isset($parts['query']) || isset($parts['fragment']);
        $is_local_http = $scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], true);

        return ($scheme === 'https' || $is_local_http)
            && $host !== ''
            && !$has_credentials
            && !$has_query_or_fragment;
    }
}
