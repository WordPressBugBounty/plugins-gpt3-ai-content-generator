<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/class-aipkit-event-payload-builder.php

namespace WPAICG\Core;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Builds canonical payload envelopes for Universal Event Webhooks.
 */
class AIPKit_Event_Payload_Builder
{
    /**
     * Builds the event envelope.
     *
     * @param string $event_name
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public static function build_envelope(string $event_name, array $payload = [], array $context = []): array
    {
        $definition = AIPKit_Event_Registry::get_definition($event_name) ?? [];
        $module = sanitize_key((string) ($context['module'] ?? ($definition['module'] ?? 'system')));
        $resource = self::normalize_resource($context['resource'] ?? []);
        $meta = self::normalize_meta($context['meta'] ?? []);
        $occurred_at = gmdate('c');
        $event_id = wp_generate_uuid4();

        $envelope = [
            'id' => $event_id,
            'type' => $event_name,
            'schema_version' => AIPKit_Event_Registry::get_schema_version(),
            'occurred_at' => $occurred_at,
            'idempotency_key' => self::build_idempotency_key($event_name, $module, $resource, $context),
            'site' => [
                'url' => site_url(),
                'name' => get_bloginfo('name'),
            ],
            'plugin' => [
                'slug' => 'gpt3-ai-content-generator',
                'version' => defined('WPAICG_VERSION') ? WPAICG_VERSION : '1.0.0',
            ],
            'source' => [
                'module' => $module,
                'origin' => sanitize_key((string) ($context['origin'] ?? 'internal')),
            ],
            'data' => $payload,
        ];

        if (!empty($resource)) {
            $envelope['resource'] = $resource;
        }
        if (!empty($meta)) {
            $envelope['meta'] = $meta;
        }

        return $envelope;
    }

    /**
     * Normalizes the resource section.
     *
     * @param mixed $resource
     * @return array<string, mixed>
     */
    private static function normalize_resource($resource): array
    {
        if (!is_array($resource)) {
            return [];
        }

        $normalized = [];
        if (isset($resource['type']) && $resource['type'] !== '') {
            $normalized['type'] = sanitize_key((string) $resource['type']);
        }
        if (isset($resource['id']) && is_scalar($resource['id']) && (string) $resource['id'] !== '') {
            $normalized['id'] = is_numeric($resource['id'])
                ? (int) $resource['id']
                : sanitize_text_field((string) $resource['id']);
        }
        if (isset($resource['label']) && is_scalar($resource['label']) && (string) $resource['label'] !== '') {
            $normalized['label'] = sanitize_text_field((string) $resource['label']);
        }

        return $normalized;
    }

    /**
     * Normalizes envelope meta.
     *
     * @param mixed $meta
     * @return array<string, mixed>
     */
    private static function normalize_meta($meta): array
    {
        if (!is_array($meta)) {
            return [];
        }

        $normalized = [];
        foreach ($meta as $key => $value) {
            $sanitized_key = sanitize_key((string) $key);
            if ($sanitized_key === '') {
                continue;
            }

            if (is_scalar($value)) {
                $normalized[$sanitized_key] = is_numeric($value)
                    ? 0 + $value
                    : sanitize_text_field((string) $value);
            } elseif (is_array($value)) {
                $normalized[$sanitized_key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Builds an idempotency key from stable event context.
     *
     * @param string $event_name
     * @param string $module
     * @param array<string, mixed> $resource
     * @param array<string, mixed> $context
     * @return string
     */
    private static function build_idempotency_key(string $event_name, string $module, array $resource, array $context): string
    {
        if (!empty($context['idempotency_key']) && is_scalar($context['idempotency_key'])) {
            return sanitize_text_field((string) $context['idempotency_key']);
        }

        $resource_type = isset($resource['type']) ? (string) $resource['type'] : '';
        $resource_id = isset($resource['id']) ? (string) $resource['id'] : '';
        $seed = implode('|', [
            $event_name,
            $module,
            $resource_type,
            $resource_id,
            (string) ($context['origin'] ?? 'internal'),
        ]);

        return sha1($seed);
    }
}
