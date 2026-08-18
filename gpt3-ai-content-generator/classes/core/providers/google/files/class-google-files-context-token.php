<?php

namespace WPAICG\Core\Providers\Google\Files;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Issues tamper-proof, short-lived references to temporary Google files.
 * The token binds a remote file to one user/session, chatbot, and conversation.
 */
final class GoogleFilesContextToken
{
    private const VERSION = 1;
    private const PROVIDER_LIFETIME_SECONDS = 172800;
    private const MAX_LOCAL_LIFETIME_SECONDS = 169200;
    private const EXPIRY_SAFETY_SECONDS = 300;

    /**
     * @param array<string, mixed> $file Normalized Google Files resource.
     * @param array<string, mixed> $binding Request ownership values.
     * @return array{token:string,expires_at:int}|WP_Error
     */
    public static function issue(array $file, array $binding)
    {
        $file_name = GoogleFilesUrlBuilder::normalize_file_name((string) ($file['name'] ?? ''));
        $file_uri = isset($file['uri']) && is_string($file['uri']) ? trim($file['uri']) : '';
        $mime_type = sanitize_mime_type((string) ($file['mime_type'] ?? ''));
        if (is_wp_error($file_name) || $file_uri === '' || $mime_type === '') {
            return new WP_Error(
                'google_file_context_invalid_resource',
                __('A valid Google file is required to create chat context.', 'gpt3-ai-content-generator'),
                ['status' => 500]
            );
        }

        $normalized_binding = self::normalize_binding($binding);
        if (is_wp_error($normalized_binding)) {
            return $normalized_binding;
        }

        $issued_at = time();
        $provider_expiry = self::parse_expiration_time((string) ($file['expiration_time'] ?? ''));
        if ($provider_expiry <= $issued_at) {
            $provider_expiry = $issued_at + self::PROVIDER_LIFETIME_SECONDS;
        }
        $expires_at = min(
            $provider_expiry - self::EXPIRY_SAFETY_SECONDS,
            $issued_at + self::MAX_LOCAL_LIFETIME_SECONDS
        );
        if ($expires_at <= $issued_at) {
            return new WP_Error(
                'google_file_context_already_expired',
                __('Google returned an expired temporary file.', 'gpt3-ai-content-generator'),
                ['status' => 410]
            );
        }

        $payload = [
            'v' => self::VERSION,
            'file_name' => $file_name,
            'file_uri' => $file_uri,
            'mime_type' => $mime_type,
            'original_filename' => sanitize_file_name((string) ($binding['original_filename'] ?? ($file['display_name'] ?? 'document'))),
            'bot_id' => $normalized_binding['bot_id'],
            'conversation_uuid' => $normalized_binding['conversation_uuid'],
            'owner' => $normalized_binding['owner'],
            'issued_at' => $issued_at,
            'expires_at' => $expires_at,
        ];
        $encoded_payload = wp_json_encode($payload);
        if (!is_string($encoded_payload)) {
            return new WP_Error(
                'google_file_context_encode_error',
                __('Could not create Google file context.', 'gpt3-ai-content-generator'),
                ['status' => 500]
            );
        }

        $payload_part = self::base64url_encode($encoded_payload);
        $signature_part = self::base64url_encode(hash_hmac('sha256', $payload_part, self::secret(), true));
        return [
            'token' => $payload_part . '.' . $signature_part,
            'expires_at' => $expires_at,
        ];
    }

    /**
     * @param array<string, mixed> $binding Current request ownership values.
     * @return array<string, mixed>|WP_Error
     */
    public static function verify(string $token, array $binding)
    {
        if ($token === '' || strlen($token) > 4096 || substr_count($token, '.') !== 1) {
            return self::invalid_token_error();
        }
        [$payload_part, $signature_part] = explode('.', $token, 2);
        $supplied_signature = self::base64url_decode($signature_part);
        if ($supplied_signature === null) {
            return self::invalid_token_error();
        }
        $expected_signature = hash_hmac('sha256', $payload_part, self::secret(), true);
        if (!hash_equals($expected_signature, $supplied_signature)) {
            return self::invalid_token_error();
        }

        $decoded_payload = self::base64url_decode($payload_part);
        if ($decoded_payload === null) {
            return self::invalid_token_error();
        }
        $payload = json_decode($decoded_payload, true);
        if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE || (int) ($payload['v'] ?? 0) !== self::VERSION) {
            return self::invalid_token_error();
        }

        $normalized_binding = self::normalize_binding($binding);
        if (is_wp_error($normalized_binding)) {
            return $normalized_binding;
        }
        if (
            (int) ($payload['bot_id'] ?? 0) !== $normalized_binding['bot_id']
            || (string) ($payload['conversation_uuid'] ?? '') !== $normalized_binding['conversation_uuid']
            || (string) ($payload['owner'] ?? '') !== $normalized_binding['owner']
        ) {
            return new WP_Error(
                'google_file_context_owner_mismatch',
                __('This temporary Google file does not belong to the current chat session.', 'gpt3-ai-content-generator'),
                ['status' => 403]
            );
        }

        $expires_at = (int) ($payload['expires_at'] ?? 0);
        if ($expires_at <= time()) {
            return new WP_Error(
                'google_file_context_expired',
                __('This Google file attachment has expired. Please upload it again.', 'gpt3-ai-content-generator'),
                ['status' => 410]
            );
        }

        $file_name = GoogleFilesUrlBuilder::normalize_file_name((string) ($payload['file_name'] ?? ''));
        $file_uri = isset($payload['file_uri']) && is_string($payload['file_uri']) ? trim($payload['file_uri']) : '';
        $mime_type = sanitize_mime_type((string) ($payload['mime_type'] ?? ''));
        if (is_wp_error($file_name) || $file_uri === '' || $mime_type === '') {
            return self::invalid_token_error();
        }
        $payload['file_name'] = $file_name;
        $payload['file_uri'] = $file_uri;
        $payload['mime_type'] = $mime_type;
        $payload['original_filename'] = sanitize_file_name((string) ($payload['original_filename'] ?? 'document'));
        return $payload;
    }

    /**
     * @param array<string, mixed> $payload Verified token payload.
     * @return array{type:string,uri:string,mime_type:string}
     */
    public static function document_input(array $payload): array
    {
        return [
            'type' => 'document',
            'uri' => (string) $payload['file_uri'],
            'mime_type' => (string) $payload['mime_type'],
        ];
    }

    /**
     * @param array<string, mixed> $binding
     * @return array{bot_id:int,conversation_uuid:string,owner:string}|WP_Error
     */
    private static function normalize_binding(array $binding)
    {
        $bot_id = absint($binding['bot_id'] ?? 0);
        $conversation_uuid = sanitize_key((string) ($binding['conversation_uuid'] ?? ''));
        $user_id = absint($binding['user_id'] ?? 0);
        $session_id = sanitize_text_field((string) ($binding['session_id'] ?? ''));
        if ($bot_id < 1 || $conversation_uuid === '') {
            return new WP_Error(
                'google_file_context_missing_chat',
                __('A chatbot and conversation are required for Google file context.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }
        if ($user_id > 0) {
            $owner = 'user:' . $user_id;
        } elseif ($session_id !== '') {
            $owner = 'guest:' . hash('sha256', $session_id);
        } else {
            return new WP_Error(
                'google_file_context_missing_owner',
                __('A valid chat session is required for Google file context.', 'gpt3-ai-content-generator'),
                ['status' => 400]
            );
        }
        return [
            'bot_id' => $bot_id,
            'conversation_uuid' => $conversation_uuid,
            'owner' => $owner,
        ];
    }

    private static function parse_expiration_time(string $expiration_time): int
    {
        if ($expiration_time === '') {
            return 0;
        }
        $parsed = strtotime($expiration_time);
        return $parsed === false ? 0 : (int) $parsed;
    }

    private static function secret(): string
    {
        return wp_salt('auth') . '|aipkit-google-files-context-v1';
    }

    private static function base64url_encode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64url_decode(string $value): ?string
    {
        if ($value === '' || !preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            return null;
        }
        $padding = strlen($value) % 4;
        if ($padding !== 0) {
            $value .= str_repeat('=', 4 - $padding);
        }
        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        return is_string($decoded) ? $decoded : null;
    }

    private static function invalid_token_error(): WP_Error
    {
        return new WP_Error(
            'google_file_context_invalid',
            __('The Google file attachment is invalid. Please upload it again.', 'gpt3-ai-content-generator'),
            ['status' => 400]
        );
    }
}
