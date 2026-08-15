<?php

namespace WPAICG\Core\Models;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Canonical model and provider-resource registry.
 *
 * Provider responses are stored as normalized, non-autoloaded snapshots. The
 * registry uses the bootstrapped catalog until a provider has been synced, then
 * replaces it with that provider snapshot. It exposes one query contract to
 * PHP, JavaScript localization, REST integrations and module compatibility
 * adapters.
 */
final class AIPKit_Model_Registry
{
    public const SCHEMA_VERSION = 1;
    public const SCHEMA_VERSION_OPTION = 'aipkit_model_registry_schema_version';
    public const MANIFEST_OPTION = 'aipkit_model_registry_manifest';
    private const SNAPSHOT_OPTION_PREFIX = 'aipkit_model_registry_';
    private const DEFAULT_STALE_AFTER = 7 * DAY_IN_SECONDS;

    /** @var array<string, array<string, mixed>> */
    private static $snapshot_cache = [];

    /** @var array<string, array<int, array<string, mixed>>> */
    private static $catalog_cache = [];

    /** @var array<string, array<string, mixed>>|null */
    private static $provider_state_cache = null;

    /**
     * Return bootstrap records before sync, or the persisted snapshot after sync.
     *
     * @param string $catalog_key
     * @param array<string, mixed> $args
     * @return array<int, array<string, mixed>>
     */
    public static function get_catalog_records(string $catalog_key, array $args = []): array
    {
        $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key($catalog_key);
        $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
        if (empty($definition)) {
            return [];
        }

        $args = wp_parse_args($args, [
            'include_bootstrap' => true,
            'include_persisted' => true,
        ]);
        $cache_key = $catalog_key . ':' . (!empty($args['include_bootstrap']) ? '1' : '0') . ':' . (!empty($args['include_persisted']) ? '1' : '0');
        if (isset(self::$catalog_cache[$cache_key])) {
            return self::$catalog_cache[$cache_key];
        }

        $records = [];
        if (!empty($args['include_bootstrap'])) {
            $records = self::normalize_rows(
                $catalog_key,
                AIPKit_Model_Catalog::get_seed_rows($catalog_key),
                'bootstrap',
                'unverified',
                false
            );
        }

        if (!empty($args['include_persisted'])) {
            $persisted = self::get_persisted_catalog($catalog_key);
            if (!empty($persisted['records'])) {
                $records = $persisted['records'];
            } elseif (!empty($persisted['authoritative'])) {
                $records = [];
            }
            $records = self::merge_records(
                $records,
                self::get_reclassified_primary_records($catalog_key)
            );
        }

        self::$catalog_cache[$cache_key] = self::filter_deprecated_records($catalog_key, $records);
        return self::$catalog_cache[$cache_key];
    }

    /**
     * Compatibility output for existing provider getters and current UI code.
     *
     * @return array<mixed>
     */
    public static function get_legacy_model_list(string $catalog_key): array
    {
        $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key($catalog_key);
        $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
        if (empty($definition)) {
            return [];
        }

        return self::records_to_legacy_rows(
            $catalog_key,
            self::get_catalog_records($catalog_key)
        );
    }

    /**
     * Query all known model catalogs through one capability-aware contract.
     *
     * @param array<string, mixed> $args
     * @return array<int, array<string, mixed>>
     */
    public static function query_models(array $args = []): array
    {
        $args = wp_parse_args($args, [
            'providers' => [],
            'catalog_keys' => [],
            'kinds' => [],
            'required_capabilities' => [],
            'statuses' => [],
            'include_bootstrap' => true,
            'include_persisted' => true,
            'with_provider_state' => false,
        ]);

        $providers = self::normalize_string_list($args['providers']);
        $provider_lookup = [];
        foreach ($providers as $provider) {
            $provider_lookup[strtolower($provider)] = true;
        }
        $catalog_keys = self::normalize_string_list($args['catalog_keys']);
        $catalog_lookup = array_fill_keys(array_map([AIPKit_Model_Catalog::class, 'normalize_catalog_key'], $catalog_keys), true);
        $kinds = array_fill_keys(array_map('sanitize_key', self::normalize_string_list($args['kinds'])), true);
        $required_capabilities = array_fill_keys(array_map('sanitize_key', self::normalize_string_list($args['required_capabilities'])), true);
        $statuses = array_fill_keys(array_map('sanitize_key', self::normalize_string_list($args['statuses'])), true);

        $merged = [];
        foreach (AIPKit_Model_Catalog::get_definitions() as $catalog_key => $definition) {
            if (($definition['resource_type'] ?? 'model') !== 'model') {
                continue;
            }
            if (!empty($catalog_lookup) && !isset($catalog_lookup[$catalog_key])) {
                continue;
            }
            $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
            if (!empty($provider_lookup) && !isset($provider_lookup[strtolower($provider)])) {
                continue;
            }

            $records = self::get_catalog_records($catalog_key, [
                'include_bootstrap' => !empty($args['include_bootstrap']),
                'include_persisted' => !empty($args['include_persisted']),
            ]);
            foreach ($records as $record) {
                $identity = strtolower($provider . '|' . (string) ($record['canonical_id'] ?? $record['id'] ?? ''));
                if ($identity === strtolower($provider . '|')) {
                    continue;
                }
                $merged[$identity] = isset($merged[$identity])
                    ? self::merge_record($merged[$identity], $record)
                    : $record;
            }
        }

        $provider_states = !empty($args['with_provider_state']) ? self::get_provider_states() : [];
        $results = [];
        foreach ($merged as $record) {
            if (!empty($kinds)) {
                $record_kinds = array_fill_keys(self::normalize_string_list($record['kinds'] ?? []), true);
                if (empty(array_intersect_key($kinds, $record_kinds))) {
                    continue;
                }
            }

            $capabilities = isset($record['capabilities']) && is_array($record['capabilities'])
                ? $record['capabilities']
                : [];
            $capabilities_match = true;
            foreach (array_keys($required_capabilities) as $capability) {
                if (empty($capabilities[$capability])) {
                    $capabilities_match = false;
                    break;
                }
            }
            if (!$capabilities_match) {
                continue;
            }

            $status = sanitize_key((string) ($record['status'] ?? 'unverified'));
            if (!empty($statuses) && !isset($statuses[$status])) {
                continue;
            }

            if (!empty($provider_states)) {
                $provider_key = strtolower((string) ($record['provider'] ?? ''));
                $record['provider_state'] = $provider_states[$provider_key] ?? null;
            }
            $results[] = $record;
        }

        usort($results, static function (array $left, array $right): int {
            $provider_compare = strcasecmp((string) ($left['provider'] ?? ''), (string) ($right['provider'] ?? ''));
            if ($provider_compare !== 0) {
                return $provider_compare;
            }
            $family_order_compare = (int) ($left['family_order'] ?? 999)
                <=> (int) ($right['family_order'] ?? 999);
            if ($family_order_compare !== 0) {
                return $family_order_compare;
            }
            $family_compare = strcasecmp(
                (string) ($left['family_label'] ?? ''),
                (string) ($right['family_label'] ?? '')
            );
            if ($family_compare !== 0) {
                return $family_compare;
            }
            $recommended_compare = ((int) !empty($right['recommended']))
                <=> ((int) !empty($left['recommended']));
            if ($recommended_compare !== 0) {
                return $recommended_compare;
            }
            $left_order = (int) ($left['order'] ?? PHP_INT_MAX);
            $right_order = (int) ($right['order'] ?? PHP_INT_MAX);
            if ($left_order !== $right_order) {
                return $left_order <=> $right_order;
            }
            return strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
        });

        $filtered = apply_filters('aipkit_model_registry_query_models', $results, $args);
        return is_array($filtered) ? array_values($filtered) : $results;
    }

    /**
     * Resolve the provider and model used to initialize a fresh configuration.
     * Existing saved configurations must bypass this method and keep their
     * explicit provider/model values.
     *
     * @param string $capability Required model capability.
     * @param array<string, mixed> $args {
     *     @type array<int, string>                       $allowed_providers Allowed provider names/keys.
     *     @type string                                   $preferred_provider Configured main provider.
     *     @type array<string, array<string, mixed>>|null $provider_configs Provider settings for state resolution.
     * }
     * @return array<string, mixed>
     */
    public static function resolve_new_model_selection(string $capability = 'text_generation', array $args = []): array
    {
        $capability = sanitize_key($capability);
        $args = wp_parse_args($args, [
            'allowed_providers' => [],
            'preferred_provider' => '',
            'provider_configs' => null,
        ]);

        $catalogs_by_provider = AIPKit_Model_Catalog::get_provider_catalogs_by_capability();
        $provider_names = [];
        foreach (AIPKit_Model_Catalog::get_definitions() as $definition) {
            $provider_name = sanitize_text_field((string) ($definition['provider'] ?? ''));
            if ($provider_name !== '') {
                $provider_names[strtolower($provider_name)] = $provider_name;
            }
        }

        $normalize_provider = static function ($provider) use ($provider_names): string {
            $provider_key = strtolower(trim((string) $provider));
            if ($provider_key === 'anthropic') {
                $provider_key = 'claude';
            }
            return $provider_names[$provider_key] ?? '';
        };

        $allowed = [];
        $has_allowed_list = !empty(array_filter((array) $args['allowed_providers'], static function ($provider): bool {
            return trim((string) $provider) !== '';
        }));
        foreach ((array) $args['allowed_providers'] as $provider) {
            $provider_name = $normalize_provider($provider);
            $provider_key = strtolower($provider_name);
            if (
                $provider_name !== ''
                && isset($catalogs_by_provider[$provider_key][$capability])
            ) {
                $allowed[$provider_key] = $provider_name;
            }
        }

        $priority = [];
        foreach (AIPKit_Model_Catalog::get_provider_priority($capability) as $provider) {
            $provider_name = $normalize_provider($provider);
            $provider_key = strtolower($provider_name);
            if (
                $provider_name === ''
                || !isset($catalogs_by_provider[$provider_key][$capability])
                || ($has_allowed_list && !isset($allowed[$provider_key]))
            ) {
                continue;
            }
            $priority[$provider_key] = $provider_name;
        }
        foreach ($allowed as $provider_key => $provider_name) {
            if (!isset($priority[$provider_key])) {
                $priority[$provider_key] = $provider_name;
            }
        }

        if (empty($priority)) {
            return [
                'provider' => '',
                'provider_key' => '',
                'model' => '',
                'catalog_key' => '',
                'source' => 'none',
                'configured' => false,
                'synced' => false,
                'needs_setup' => true,
            ];
        }

        $provider_configs = is_array($args['provider_configs'])
            ? $args['provider_configs']
            : null;
        $provider_states = self::get_provider_states($provider_configs);
        $resolutions = [];

        foreach ($priority as $provider_key => $provider_name) {
            $catalog_key = (string) $catalogs_by_provider[$provider_key][$capability];
            $state = isset($provider_states[$provider_key]) && is_array($provider_states[$provider_key])
                ? $provider_states[$provider_key]
                : [];
            $configured = !empty($state['configured']) && empty($state['locked']);
            $synced = $configured
                && empty($state['connection_changed'])
                && (int) ($state['last_success'] ?? 0) > 0
                && (int) ($state['model_count'] ?? 0) > 0;
            $model_resolution = self::resolve_new_model_for_catalog($catalog_key, $synced);

            $resolutions[$provider_key] = [
                'provider' => $provider_name,
                'provider_key' => $provider_key,
                'model' => $model_resolution['model'],
                'catalog_key' => $catalog_key,
                'source' => $model_resolution['source'],
                'configured' => $configured,
                'synced' => $synced,
                'needs_setup' => !$configured || $model_resolution['model'] === '',
            ];
        }

        $preferred_name = $normalize_provider($args['preferred_provider']);
        $preferred_key = strtolower($preferred_name);
        if (
            $preferred_key !== ''
            && isset($resolutions[$preferred_key])
            && !empty($resolutions[$preferred_key]['configured'])
            && $resolutions[$preferred_key]['model'] !== ''
        ) {
            return $resolutions[$preferred_key];
        }

        foreach ($resolutions as $resolution) {
            if (!empty($resolution['configured']) && $resolution['model'] !== '') {
                return $resolution;
            }
        }

        foreach ($resolutions as $resolution) {
            if ($resolution['model'] !== '') {
                return $resolution;
            }
        }

        return reset($resolutions);
    }

    /**
     * @return array{model:string,source:string}
     */
    private static function resolve_new_model_for_catalog(string $catalog_key, bool $include_persisted): array
    {
        $records = self::get_catalog_records($catalog_key, [
            'include_bootstrap' => true,
            'include_persisted' => $include_persisted,
        ]);
        $record_ids = [];
        foreach ($records as $record) {
            $model_id = sanitize_text_field((string) ($record['id'] ?? ''));
            $canonical_id = sanitize_text_field((string) ($record['canonical_id'] ?? $model_id));
            if ($model_id === '') {
                continue;
            }
            $record_ids[$model_id] = $model_id;
            if ($canonical_id !== '') {
                $record_ids[$canonical_id] = $model_id;
            }
        }

        $default_model = AIPKit_Model_Catalog::get_default_id($catalog_key);
        if ($default_model !== '' && isset($record_ids[$default_model])) {
            return ['model' => $record_ids[$default_model], 'source' => 'catalog_default'];
        }

        foreach (AIPKit_Model_Catalog::get_recommended_ids($catalog_key) as $model_id) {
            $model_id = sanitize_text_field((string) $model_id);
            if ($model_id !== '' && isset($record_ids[$model_id])) {
                return ['model' => $record_ids[$model_id], 'source' => 'recommended'];
            }
        }

        foreach ($records as $record) {
            $model_id = sanitize_text_field((string) ($record['id'] ?? ''));
            if ($model_id !== '') {
                return ['model' => $model_id, 'source' => 'available'];
            }
        }

        return ['model' => '', 'source' => 'none'];
    }

    /**
     * Query voices, vector targets and other provider resources.
     *
     * @param array<string, mixed> $args
     * @return array<int, array<string, mixed>>
     */
    public static function query_resources(array $args = []): array
    {
        $args = wp_parse_args($args, [
            'providers' => [],
            'catalog_keys' => [],
            'resource_types' => [],
            'include_bootstrap' => true,
            'include_persisted' => true,
        ]);
        $provider_lookup = array_fill_keys(array_map('strtolower', self::normalize_string_list($args['providers'])), true);
        $catalog_lookup = array_fill_keys(array_map(
            [AIPKit_Model_Catalog::class, 'normalize_catalog_key'],
            self::normalize_string_list($args['catalog_keys'])
        ), true);
        $resource_lookup = array_fill_keys(array_map('sanitize_key', self::normalize_string_list($args['resource_types'])), true);
        $results = [];

        foreach (AIPKit_Model_Catalog::get_definitions() as $catalog_key => $definition) {
            $resource_type = sanitize_key((string) ($definition['resource_type'] ?? 'model'));
            if ($resource_type === 'model') {
                continue;
            }
            if (!empty($catalog_lookup) && !isset($catalog_lookup[$catalog_key])) {
                continue;
            }
            $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
            if (!empty($provider_lookup) && !isset($provider_lookup[strtolower($provider)])) {
                continue;
            }
            if (!empty($resource_lookup) && !isset($resource_lookup[$resource_type])) {
                continue;
            }

            foreach (self::get_catalog_records($catalog_key, [
                'include_bootstrap' => !empty($args['include_bootstrap']),
                'include_persisted' => !empty($args['include_persisted']),
            ]) as $record) {
                $results[] = $record;
            }
        }

        usort($results, static function (array $left, array $right): int {
            $provider_compare = strcasecmp((string) ($left['provider'] ?? ''), (string) ($right['provider'] ?? ''));
            if ($provider_compare !== 0) {
                return $provider_compare;
            }
            $type_compare = strcasecmp((string) ($left['resource_type'] ?? ''), (string) ($right['resource_type'] ?? ''));
            if ($type_compare !== 0) {
                return $type_compare;
            }
            return strcasecmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
        });

        $filtered = apply_filters('aipkit_model_registry_query_resources', $results, $args);
        return is_array($filtered) ? array_values($filtered) : $results;
    }

    /**
     * Resolve a saved or requested provider/model selection without rejecting
     * provider-supported custom IDs that have not appeared in a sync response.
     *
     * @return array<string, mixed>
     */
    public static function resolve_selection(
        string $provider,
        string $model_id,
        string $required_capability = '',
        bool $allow_manual = true
    ): array {
        $provider = sanitize_text_field(trim($provider));
        $model_id = sanitize_text_field(trim($model_id));
        $required_capability = sanitize_key($required_capability);
        if ($provider === '' || $model_id === '') {
            return [
                'usable' => false,
                'reason' => 'missing_selection',
                'record' => null,
            ];
        }

        $models = self::query_models([
            'providers' => [$provider],
            'include_bootstrap' => true,
            'include_persisted' => true,
        ]);
        $canonical_requested = self::canonicalize_model_id($provider, $model_id);
        foreach ($models as $record) {
            $candidate_ids = [
                (string) ($record['id'] ?? ''),
                (string) ($record['canonical_id'] ?? ''),
                (string) ($record['raw_id'] ?? ''),
            ];
            $matched = false;
            foreach ($candidate_ids as $candidate_id) {
                if (self::canonicalize_model_id($provider, $candidate_id) === $canonical_requested) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                continue;
            }

            if ($required_capability !== '' && empty($record['capabilities'][$required_capability])) {
                return [
                    'usable' => false,
                    'reason' => 'incompatible_capability',
                    'record' => $record,
                ];
            }

            return [
                'usable' => true,
                'reason' => (string) ($record['status'] ?? 'available'),
                'record' => $record,
            ];
        }

        if (!$allow_manual) {
            return [
                'usable' => false,
                'reason' => 'unknown_model',
                'record' => null,
            ];
        }

        return [
            'usable' => true,
            'reason' => 'manual',
            'record' => [
                'provider' => $provider,
                'id' => $model_id,
                'raw_id' => $model_id,
                'canonical_id' => $canonical_requested,
                'name' => $model_id,
                'resource_type' => 'model',
                'kinds' => [],
                'capabilities' => $required_capability !== '' ? [$required_capability => true] : [],
                'catalog_keys' => [],
                'source' => 'manual',
                'status' => 'legacy',
                'verified' => false,
                'recommended' => false,
                'default' => false,
                'metadata' => [],
            ],
        ];
    }

    /**
     * Publish one or more successfully synchronized catalog slices.
     *
     * @param string $provider Canonical provider name.
     * @param array<string, array<mixed>> $catalogs Catalog key => raw rows.
     * @param array<string, mixed> $args
     * @return array<string, mixed>|WP_Error
     */
    public static function publish_catalogs(string $provider, array $catalogs, array $args = [])
    {
        $provider = sanitize_text_field(trim($provider));
        if ($provider === '') {
            return new WP_Error('aipkit_registry_provider_required', __('Provider is required to publish model catalogs.', 'gpt3-ai-content-generator'));
        }

        $args = wp_parse_args($args, [
            'sync_scope' => $provider,
            'synced_at' => time(),
            'connection' => [],
            'mirror_legacy' => true,
            'source' => 'synced',
            'status' => 'available',
            'verified' => true,
            'sync_scopes' => [],
        ]);
        $snapshot = self::get_provider_snapshot($provider);
        $normalized_catalogs = [];

        foreach ($catalogs as $catalog_key => $rows) {
            $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key((string) $catalog_key);
            $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
            if (empty($definition) || strcasecmp((string) ($definition['provider'] ?? ''), $provider) !== 0) {
                return new WP_Error(
                    'aipkit_registry_catalog_provider_mismatch',
                    sprintf(
                        /* translators: 1: catalog key, 2: provider name */
                        __('Catalog %1$s does not belong to provider %2$s.', 'gpt3-ai-content-generator'),
                        $catalog_key,
                        $provider
                    )
                );
            }
            if (!is_array($rows)) {
                return new WP_Error('aipkit_registry_invalid_catalog', __('Model catalog data must be an array.', 'gpt3-ai-content-generator'));
            }

            $normalized_catalogs[$catalog_key] = self::normalize_rows(
                $catalog_key,
                $rows,
                sanitize_key((string) $args['source']),
                sanitize_key((string) $args['status']),
                !empty($args['verified'])
            );
        }

        foreach ($normalized_catalogs as $catalog_key => $records) {
            $snapshot['catalogs'][$catalog_key] = array_values($records);
        }

        $synced_at = max(1, (int) $args['synced_at']);
        $sync_scope = sanitize_text_field((string) $args['sync_scope']);
        if ($sync_scope === '') {
            $sync_scope = $provider;
        }
        $connection = is_array($args['connection']) ? $args['connection'] : [];
        $successful_scopes = array_values(array_unique(array_merge(
            [$sync_scope],
            self::normalize_string_list($args['sync_scopes'])
        )));
        foreach ($successful_scopes as $successful_scope) {
            $snapshot['sync'][$successful_scope] = [
                'status' => 'ready',
                'last_attempt' => $synced_at,
                'last_success' => $synced_at,
                'error_code' => '',
                'error_message' => '',
                'connection_fingerprint' => self::get_connection_fingerprint($provider, $connection),
            ];
        }
        $snapshot['revision'] = self::create_revision();
        $snapshot['updated_at'] = $synced_at;

        $write_result = self::write_provider_snapshot($provider, $snapshot);
        if (is_wp_error($write_result)) {
            return $write_result;
        }

        if (!empty($args['mirror_legacy'])) {
            foreach ($normalized_catalogs as $catalog_key => $records) {
                self::mirror_legacy_catalog($catalog_key, $records);
            }
            foreach ($successful_scopes as $successful_scope) {
                self::mirror_legacy_sync_timestamp($successful_scope, $synced_at);
            }
        }

        self::clear_caches();
        do_action('aipkit_model_registry_updated', $provider, array_keys($normalized_catalogs), $snapshot);
        return $snapshot;
    }

    /**
     * Record a failed synchronization attempt without replacing catalog data.
     *
     * @param array<string, mixed> $connection
     */
    public static function mark_sync_error(
        string $provider,
        string $sync_scope,
        WP_Error $error,
        array $connection = []
    ): void {
        $provider = sanitize_text_field(trim($provider));
        $sync_scope = sanitize_text_field(trim($sync_scope));
        if ($provider === '' || $sync_scope === '') {
            return;
        }

        $snapshot = self::get_provider_snapshot($provider);
        $previous = isset($snapshot['sync'][$sync_scope]) && is_array($snapshot['sync'][$sync_scope])
            ? $snapshot['sync'][$sync_scope]
            : [];
        $attempted_at = time();
        $snapshot['sync'][$sync_scope] = [
            'status' => 'error',
            'last_attempt' => $attempted_at,
            'last_success' => (int) ($previous['last_success'] ?? 0),
            'error_code' => sanitize_key((string) $error->get_error_code()),
            'error_message' => sanitize_text_field($error->get_error_message()),
            'connection_fingerprint' => self::get_connection_fingerprint($provider, $connection),
        ];
        $snapshot['revision'] = self::create_revision();
        $snapshot['updated_at'] = $attempted_at;
        self::write_provider_snapshot($provider, $snapshot);
        self::clear_caches();
        do_action('aipkit_model_registry_sync_failed', $provider, $sync_scope, $error);
    }

    /**
     * Clear selected provider catalogs after credentials or endpoints change.
     * The provider remains configured, but must be synchronized again.
     *
     * @param array<int, string> $catalog_keys
     * @return true|WP_Error
     */
    public static function invalidate_catalogs(string $provider, array $catalog_keys)
    {
        $provider = sanitize_text_field(trim($provider));
        $known_catalogs = AIPKit_Model_Catalog::get_catalog_keys_by_provider()[$provider] ?? [];
        if ($provider === '' || empty($known_catalogs)) {
            return new WP_Error(
                'aipkit_registry_unknown_provider',
                __('The model registry provider is not supported.', 'gpt3-ai-content-generator')
            );
        }

        $snapshot = self::get_provider_snapshot($provider);
        foreach ($catalog_keys as $catalog_key) {
            $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key((string) $catalog_key);
            if (!in_array($catalog_key, $known_catalogs, true)) {
                continue;
            }
            $snapshot['catalogs'][$catalog_key] = [];
            $sync_scope = self::get_sync_scope_for_catalog($catalog_key);
            unset($snapshot['sync'][$sync_scope]);
            self::remove_legacy_sync_timestamp($sync_scope);
            self::mirror_legacy_catalog($catalog_key, []);
        }

        $snapshot['revision'] = self::create_revision();
        $snapshot['updated_at'] = time();
        $write_result = self::write_provider_snapshot($provider, $snapshot);
        if (is_wp_error($write_result)) {
            return $write_result;
        }
        self::clear_caches();
        do_action('aipkit_model_registry_invalidated', $provider, $catalog_keys, $snapshot);
        return true;
    }

    /**
     * Return provider setup/sync states for all known providers.
     *
     * @param array<string, array<string, mixed>>|null $provider_configs
     * @return array<string, array<string, mixed>> keyed by lower-case provider key
     */
    public static function get_provider_states(?array $provider_configs = null): array
    {
        $use_cache = $provider_configs === null;
        if ($use_cache && self::$provider_state_cache !== null) {
            return self::$provider_state_cache;
        }

        if ($provider_configs === null && class_exists('\WPAICG\AIPKit_Providers')) {
            $provider_configs = \WPAICG\AIPKit_Providers::get_all_providers();
        }
        $provider_configs = is_array($provider_configs) ? $provider_configs : [];
        $provider_names = array_values(array_unique(array_merge(
            array_keys(AIPKit_Model_Catalog::get_catalog_keys_by_provider()),
            array_keys($provider_configs)
        )));
        $stale_after = (int) apply_filters('aipkit_model_registry_stale_after', self::DEFAULT_STALE_AFTER);
        $stale_after = max(HOUR_IN_SECONDS, $stale_after);
        $now = time();
        $states = [];

        foreach ($provider_names as $provider) {
            $provider = sanitize_text_field((string) $provider);
            if ($provider === '') {
                continue;
            }
            $config = isset($provider_configs[$provider]) && is_array($provider_configs[$provider])
                ? $provider_configs[$provider]
                : [];
            $configured = self::is_provider_configured($provider, $config);
            $locked = (bool) apply_filters('aipkit_model_registry_provider_locked', false, $provider);
            $snapshot = self::get_provider_snapshot($provider);
            $sync_states = self::sanitize_sync_map($snapshot['sync'] ?? []);
            $current_fingerprint = self::get_connection_fingerprint($provider, $config);
            $scope_groups = self::get_provider_sync_scope_groups($provider, $sync_states);
            $primary_sync_states = !empty($scope_groups['has_model_catalogs'])
                ? $scope_groups['models']
                : $scope_groups['resources'];
            $primary_summary = self::summarize_provider_sync_state(
                $configured,
                $locked,
                $primary_sync_states,
                $current_fingerprint,
                $stale_after,
                $now
            );
            $resource_summary = !empty($scope_groups['has_resource_catalogs'])
                ? self::summarize_provider_sync_state(
                    $configured,
                    $locked,
                    $scope_groups['resources'],
                    $current_fingerprint,
                    $stale_after,
                    $now
                )
                : null;

            $states[strtolower($provider)] = [
                'provider' => $provider,
                'status' => $primary_summary['status'],
                'configured' => $configured,
                'locked' => $locked,
                'connection_changed' => $primary_summary['connection_changed'],
                'last_attempt' => $primary_summary['last_attempt'],
                'last_success' => $primary_summary['last_success'],
                'error_code' => $primary_summary['error_code'],
                'error_message' => $primary_summary['error_message'],
                'model_count' => self::count_persisted_models($snapshot),
                'resource_count' => self::count_persisted_resources($snapshot),
                'resource_status' => is_array($resource_summary) ? $resource_summary['status'] : 'not_applicable',
                'resource_connection_changed' => is_array($resource_summary) ? $resource_summary['connection_changed'] : false,
                'resource_last_attempt' => is_array($resource_summary) ? $resource_summary['last_attempt'] : 0,
                'resource_last_success' => is_array($resource_summary) ? $resource_summary['last_success'] : 0,
                'resource_error_code' => is_array($resource_summary) ? $resource_summary['error_code'] : '',
                'resource_error_message' => is_array($resource_summary) ? $resource_summary['error_message'] : '',
                'sync_scopes' => self::get_public_sync_scope_states($sync_states),
            ];
        }

        $filtered = apply_filters('aipkit_model_registry_provider_states', $states, $provider_configs);
        $states = is_array($filtered) ? $filtered : $states;
        if ($use_cache) {
            self::$provider_state_cache = $states;
        }
        return $states;
    }

    /**
     * @return array<int, array{id:string,name:string}>
     */
    public static function get_recommended_models(string $catalog_key): array
    {
        $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key($catalog_key);
        $recommended_ids = AIPKit_Model_Catalog::get_recommended_ids($catalog_key);
        if (empty($recommended_ids)) {
            return [];
        }

        $records = self::get_catalog_records($catalog_key);
        $has_verified = false;
        foreach ($records as $record) {
            if (!empty($record['verified']) && ($record['status'] ?? '') === 'available') {
                $has_verified = true;
                break;
            }
        }

        $lookup = [];
        $name_lookup = [];
        foreach ($records as $record) {
            if ($has_verified && empty($record['verified'])) {
                continue;
            }
            $id = (string) ($record['id'] ?? '');
            $canonical_id = (string) ($record['canonical_id'] ?? $id);
            if ($id === '') {
                continue;
            }
            $model = [
                'id' => $id,
                'name' => (string) ($record['name'] ?? $id),
                'family_key' => sanitize_key((string) ($record['family_key'] ?? 'other')),
                'family_label' => sanitize_text_field((string) ($record['family_label'] ?? __('Other', 'gpt3-ai-content-generator'))),
                'family_order' => (int) ($record['family_order'] ?? 999),
                'family_collapsed' => !empty($record['family_collapsed']),
            ];
            $lookup[$id] = $model;
            $lookup[$canonical_id] = $model;
            $name_lookup[$id] = $model['name'];
            $name_lookup[$canonical_id] = $model['name'];
        }

        $recommended = [];
        foreach ($recommended_ids as $model_id) {
            $canonical_id = self::canonicalize_model_id((string) (AIPKit_Model_Catalog::get_definition($catalog_key)['provider'] ?? ''), $model_id);
            if (!isset($lookup[$model_id]) && !isset($lookup[$canonical_id])) {
                continue;
            }
            $matched = $lookup[$model_id] ?? $lookup[$canonical_id];
            $matched['id'] = $model_id;
            $recommended[] = $matched;
        }

        $filtered = apply_filters('aipkit_recommended_models', $recommended, $catalog_key, $recommended_ids, $name_lookup);
        return is_array($filtered) ? array_values($filtered) : $recommended;
    }

    public static function get_default_model_id(string $catalog_key): string
    {
        return AIPKit_Model_Catalog::get_default_id($catalog_key);
    }

    /**
     * Return legacy-shaped sync timestamps from canonical provider snapshots.
     *
     * @return array<string, int>
     */
    public static function get_sync_timestamps(): array
    {
        $timestamps = get_option('aipkit_model_sync_timestamps', []);
        $timestamps = is_array($timestamps) ? $timestamps : [];
        foreach (array_keys(AIPKit_Model_Catalog::get_catalog_keys_by_provider()) as $provider) {
            $snapshot = self::get_provider_snapshot($provider);
            foreach (self::sanitize_sync_map($snapshot['sync'] ?? []) as $scope => $state) {
                $last_success = (int) ($state['last_success'] ?? 0);
                if ($last_success > 0) {
                    $timestamps[$scope] = $last_success;
                }
            }
        }
        $normalized = [];
        foreach ($timestamps as $scope => $timestamp) {
            $scope = sanitize_text_field((string) $scope);
            if ($scope !== '') {
                $normalized[$scope] = max(0, (int) $timestamp);
            }
        }
        return $normalized;
    }

    /**
     * Return the non-sensitive registry revision manifest for API clients.
     *
     * @return array<string, mixed>
     */
    public static function get_manifest_summary(): array
    {
        $manifest = get_option(self::MANIFEST_OPTION, []);
        $manifest = is_array($manifest) ? $manifest : [];
        $providers = [];
        foreach (($manifest['providers'] ?? []) as $provider => $entry) {
            if (!is_array($entry)) {
                continue;
            }
            $provider = sanitize_text_field((string) $provider);
            if ($provider === '') {
                continue;
            }
            $providers[$provider] = [
                'revision' => sanitize_text_field((string) ($entry['revision'] ?? '')),
                'updated_at' => max(0, (int) ($entry['updated_at'] ?? 0)),
            ];
        }

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'updated_at' => max(0, (int) ($manifest['updated_at'] ?? 0)),
            'providers' => $providers,
        ];
    }

    /**
     * Normalize provider rows into the canonical schema.
     *
     * @param array<mixed> $rows
     * @return array<int, array<string, mixed>>
     */
    private static function normalize_rows(
        string $catalog_key,
        array $rows,
        string $source = 'synced',
        string $status = 'available',
        bool $verified = true
    ): array {
        $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key($catalog_key);
        $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
        if (empty($definition)) {
            return [];
        }

        $flattened = self::flatten_rows($rows);
        $normalized = [];
        $indexes = [];
        foreach ($flattened as $order => $entry) {
            $row = $entry['row'];
            if (is_string($row)) {
                $row = ['id' => $row, 'name' => $row];
            }
            if (!is_array($row)) {
                continue;
            }

            $raw_id = sanitize_text_field((string) ($row['raw_id'] ?? $row['id'] ?? $row['model_id'] ?? $row['model'] ?? $row['name'] ?? ''));
            if ($raw_id === '') {
                continue;
            }
            if (!self::model_belongs_to_catalog($catalog_key, $row)) {
                continue;
            }
            $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
            $canonical_id = self::canonicalize_model_id($provider, (string) ($row['canonical_id'] ?? $raw_id));
            if ($canonical_id === '') {
                continue;
            }
            $name = sanitize_text_field((string) ($row['name'] ?? $row['display_name'] ?? $raw_id));
            if ($name === '') {
                $name = $raw_id;
            }

            $metadata = [];
            if (isset($row['metadata']) && is_array($row['metadata'])) {
                $metadata = self::sanitize_recursive($row['metadata']);
            }
            $internal_keys = [
                'provider', 'id', 'raw_id', 'canonical_id', 'name', 'resource_type',
                'kinds', 'capabilities', 'catalog_keys', 'source', 'status',
                'verified', 'recommended', 'default', 'metadata', 'order',
                'family_key', 'family_label', 'family_order', 'family_collapsed',
            ];
            foreach ($row as $key => $value) {
                if (in_array((string) $key, $internal_keys, true)) {
                    continue;
                }
                $metadata[(string) $key] = self::sanitize_recursive($value);
            }
            $capabilities = isset($definition['capabilities']) && is_array($definition['capabilities'])
                ? self::normalize_capabilities($definition['capabilities'])
                : [];
            $openrouter_resolver = '\\WPAICG\\Core\\Providers\\OpenRouter\\Methods\\resolve_model_capabilities_from_metadata_logic';
            if ($catalog_key === 'OpenRouter' && function_exists($openrouter_resolver)) {
                $capabilities = array_merge(
                    $capabilities,
                    self::normalize_capabilities($openrouter_resolver($metadata))
                );
            } elseif ($catalog_key !== 'OpenRouter' && isset($row['capabilities']) && is_array($row['capabilities'])) {
                $capabilities = array_merge($capabilities, self::normalize_capabilities($row['capabilities']));
            }
            $capabilities = self::infer_capabilities($definition, $metadata, $capabilities);
            $kinds = self::derive_kinds($definition, $capabilities);
            $catalog_keys = self::normalize_string_list($row['catalog_keys'] ?? []);
            if (!in_array($catalog_key, $catalog_keys, true)) {
                $catalog_keys[] = $catalog_key;
            }
            $family = AIPKit_Model_Catalog::get_model_family($catalog_key, array_merge($row, [
                'raw_id' => $raw_id,
                'name' => $name,
                'metadata' => $metadata,
            ]));

            $record = [
                'provider' => $provider,
                'id' => $raw_id,
                'raw_id' => $raw_id,
                'canonical_id' => $canonical_id,
                'name' => $name,
                'resource_type' => sanitize_key((string) ($definition['resource_type'] ?? 'model')),
                'kinds' => $kinds,
                'capabilities' => $capabilities,
                'catalog_keys' => array_values(array_unique($catalog_keys)),
                'source' => sanitize_key($source),
                'status' => sanitize_key($status),
                'verified' => $verified,
                'recommended' => in_array($raw_id, AIPKit_Model_Catalog::get_recommended_ids($catalog_key), true)
                    || in_array($canonical_id, AIPKit_Model_Catalog::get_recommended_ids($catalog_key), true),
                'default' => self::canonicalize_model_id($provider, AIPKit_Model_Catalog::get_default_id($catalog_key)) === $canonical_id,
                'family_key' => sanitize_key((string) ($family['key'] ?? 'other')),
                'family_label' => sanitize_text_field((string) ($family['label'] ?? __('Other', 'gpt3-ai-content-generator'))),
                'family_order' => (int) ($family['order'] ?? 999),
                'family_collapsed' => !empty($family['collapsed']),
                'metadata' => $metadata,
                'order' => (int) $order,
            ];

            if (isset($indexes[$canonical_id])) {
                $index = $indexes[$canonical_id];
                $normalized[$index] = self::merge_record($normalized[$index], $record);
            } else {
                $indexes[$canonical_id] = count($normalized);
                $normalized[] = $record;
            }
        }

        return $normalized;
    }

    /**
     * Keep old snapshots honest when a provider's general model endpoint mixes
     * chat models with image, embedding, audio, video, or unsupported models.
     * This also fixes existing installations immediately, before their next sync.
     *
     * @param array<string, mixed> $row
     */
    private static function model_belongs_to_catalog(string $catalog_key, array $row): bool
    {
        if (in_array($catalog_key, [
            'OpenAI',
            'OpenAIImage',
            'OpenAIEmbedding',
            'OpenAITTS',
            'OpenAISTT',
            'OpenAIRealtime',
        ], true)) {
            return self::classify_openai_catalog_key($row) === $catalog_key;
        }

        if (in_array($catalog_key, ['Google', 'GoogleImage', 'GoogleVideo', 'GoogleEmbedding'], true)) {
            return self::classify_google_catalog_key($row) === $catalog_key;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function classify_openai_catalog_key(array $row): string
    {
        $model_id = strtolower(trim((string) ($row['raw_id'] ?? $row['id'] ?? $row['name'] ?? '')));
        if ($model_id === '') {
            return '';
        }
        if (strpos($model_id, 'embedding') !== false) {
            return 'OpenAIEmbedding';
        }
        if (
            strpos($model_id, 'gpt-image') === 0
            || strpos($model_id, 'chatgpt-image') === 0
            || strpos($model_id, 'dall-e') === 0
        ) {
            return 'OpenAIImage';
        }
        if (strpos($model_id, 'realtime') !== false) {
            return 'OpenAIRealtime';
        }
        if (strpos($model_id, 'whisper') !== false || strpos($model_id, 'transcribe') !== false) {
            return 'OpenAISTT';
        }
        if (strpos($model_id, 'tts-') === 0 || strpos($model_id, '-tts') !== false) {
            return 'OpenAITTS';
        }
        foreach (['sora', 'moderation', 'gpt-audio', 'audio-preview', 'computer-use'] as $unsupported_fragment) {
            if (strpos($model_id, $unsupported_fragment) !== false) {
                return '';
            }
        }
        return 'OpenAI';
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function classify_google_catalog_key(array $row): string
    {
        $model_id = strtolower(trim((string) ($row['raw_id'] ?? $row['id'] ?? $row['name'] ?? '')));
        if (strpos($model_id, 'models/') === 0) {
            $model_id = (string) substr($model_id, 7);
        }
        if ($model_id === '') {
            return '';
        }

        $metadata = isset($row['metadata']) && is_array($row['metadata'])
            ? $row['metadata']
            : [];
        $method_source = $row['supportedGenerationMethods']
            ?? $metadata['supportedGenerationMethods']
            ?? $metadata['supportedgenerationmethods']
            ?? [];
        $methods = is_array($method_source)
            ? array_map('strtolower', $method_source)
            : [];
        $is_embedding = in_array('embedcontent', $methods, true)
            || strpos($model_id, 'embedding') !== false;
        if ($is_embedding) {
            return 'GoogleEmbedding';
        }
        if (in_array('predictlongrunning', $methods, true) || strpos($model_id, 'veo') !== false) {
            return 'GoogleVideo';
        }
        $is_image = in_array('predict', $methods, true)
            || strpos($model_id, 'imagen') !== false
            || strpos($model_id, 'nano-banana') !== false
            || strpos($model_id, 'image-generation') !== false
            || strpos($model_id, 'flash-image') !== false
            || strpos($model_id, 'pro-image') !== false
            || strpos($model_id, 'flash-lite-image') !== false;
        if ($is_image) {
            return 'GoogleImage';
        }

        foreach (['native-audio', '-tts', '-live-', 'live-preview', 'live-translate', 'lyria', 'robotics', 'computer-use', 'gemini-omni', 'aqa'] as $unsupported_fragment) {
            if (strpos($model_id, $unsupported_fragment) !== false) {
                return '';
            }
        }

        if (!empty($methods) && !in_array('generatecontent', $methods, true)) {
            return '';
        }
        return 'Google';
    }

    /**
     * Split OpenAI's mixed /models response into capability-specific catalogs.
     *
     * @param array<mixed> $rows
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function partition_openai_models(array $rows): array
    {
        $catalogs = [
            'OpenAI' => [],
            'OpenAIImage' => [],
            'OpenAIEmbedding' => [],
            'OpenAITTS' => [],
            'OpenAISTT' => [],
            'OpenAIRealtime' => [],
        ];

        foreach (self::flatten_rows($rows) as $entry) {
            $row = $entry['row'] ?? null;
            if (is_string($row)) {
                $row = ['id' => $row, 'name' => $row];
            }
            if (!is_array($row)) {
                continue;
            }
            $catalog_key = self::classify_openai_catalog_key($row);
            if ($catalog_key === '') {
                continue;
            }
            $catalogs[$catalog_key][] = $row;
        }

        return $catalogs;
    }

    /**
     * Split Google's mixed model response into capability-specific catalogs.
     * Unsupported endpoint families are omitted instead of appearing as chat
     * models that the plugin cannot call correctly.
     *
     * @param array<mixed> $rows
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function partition_google_models(array $rows): array
    {
        $catalogs = [
            'Google' => [],
            'GoogleImage' => [],
            'GoogleVideo' => [],
            'GoogleEmbedding' => [],
        ];

        foreach (self::flatten_rows($rows) as $entry) {
            $row = $entry['row'] ?? null;
            if (is_string($row)) {
                $row = ['id' => $row, 'name' => $row];
            }
            if (!is_array($row)) {
                continue;
            }
            $catalog_key = self::classify_google_catalog_key($row);
            if ($catalog_key !== '') {
                $catalogs[$catalog_key][] = $row;
            }
        }

        return $catalogs;
    }

    /**
     * Migrate legacy provider-specific options into normalized snapshots.
     */
    public static function migrate_legacy_options(bool $overwrite_existing = false): bool
    {
        $legacy_options = AIPKit_Model_Catalog::get_legacy_option_map();
        $sync_timestamps = get_option('aipkit_model_sync_timestamps', []);
        $sync_timestamps = is_array($sync_timestamps) ? $sync_timestamps : [];
        $catalogs_by_provider = [];

        foreach ($legacy_options as $catalog_key => $option_name) {
            $legacy_rows = get_option($option_name, null);
            if (!is_array($legacy_rows)) {
                continue;
            }
            $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
            $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
            if ($provider === '') {
                continue;
            }
            if (!isset($catalogs_by_provider[$provider])) {
                $catalogs_by_provider[$provider] = [];
            }
            if ($catalog_key === 'OpenAI') {
                foreach (self::partition_openai_models($legacy_rows) as $partition_key => $partition_rows) {
                    if (!empty($partition_rows) || $partition_key === 'OpenAI') {
                        $catalogs_by_provider[$provider][$partition_key] = $partition_rows;
                    }
                }
            } elseif ($catalog_key === 'Google') {
                foreach (self::partition_google_models($legacy_rows) as $partition_key => $partition_rows) {
                    if (!empty($partition_rows) || $partition_key === 'Google') {
                        $catalogs_by_provider[$provider][$partition_key] = $partition_rows;
                    }
                }
            } else {
                $catalogs_by_provider[$provider][$catalog_key] = $legacy_rows;
            }
        }

        $vector_store_registry = get_option('aipkit_vector_stores_registry', []);
        if (
            is_array($vector_store_registry)
            && isset($vector_store_registry['OpenAI'])
            && is_array($vector_store_registry['OpenAI'])
        ) {
            if (!isset($catalogs_by_provider['OpenAI'])) {
                $catalogs_by_provider['OpenAI'] = [];
            }
            $catalogs_by_provider['OpenAI']['OpenAIVectorStores'] = $vector_store_registry['OpenAI'];
        }

        foreach ($sync_timestamps as $sync_scope => $synced_at) {
            if ((int) $synced_at <= 0) {
                continue;
            }
            $definition = AIPKit_Model_Catalog::get_definition((string) $sync_scope);
            $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
            if ($provider !== '' && !isset($catalogs_by_provider[$provider])) {
                $catalogs_by_provider[$provider] = [];
            }
        }

        foreach ($catalogs_by_provider as $provider => $catalogs) {
            $snapshot = self::get_provider_snapshot($provider);
            foreach ($catalogs as $catalog_key => $rows) {
                if (!$overwrite_existing && array_key_exists($catalog_key, $snapshot['catalogs'])) {
                    continue;
                }
                $sync_scope = self::get_sync_scope_for_catalog($catalog_key);
                $synced_at = isset($sync_timestamps[$sync_scope]) ? (int) $sync_timestamps[$sync_scope] : 0;
                $was_synced = $synced_at > 0;
                $snapshot['catalogs'][$catalog_key] = self::normalize_rows(
                    $catalog_key,
                    $rows,
                    $was_synced ? 'synced' : 'legacy',
                    $was_synced ? 'available' : 'legacy',
                    $was_synced
                );
            }

            $sync_scopes = [];
            foreach (array_keys($catalogs) as $catalog_key) {
                $sync_scopes[self::get_sync_scope_for_catalog($catalog_key)] = true;
            }
            foreach (array_keys($sync_scopes) as $sync_scope) {
                $synced_at = isset($sync_timestamps[$sync_scope]) ? (int) $sync_timestamps[$sync_scope] : 0;
                if ($synced_at <= 0 || (!$overwrite_existing && isset($snapshot['sync'][$sync_scope]))) {
                    continue;
                }
                $snapshot['sync'][$sync_scope] = [
                    'status' => 'ready',
                    'last_attempt' => $synced_at,
                    'last_success' => $synced_at,
                    'error_code' => '',
                    'error_message' => '',
                    'connection_fingerprint' => '',
                ];
            }

            foreach ($sync_timestamps as $sync_scope => $synced_at) {
                $definition = AIPKit_Model_Catalog::get_definition((string) $sync_scope);
                if (strcasecmp((string) ($definition['provider'] ?? ''), $provider) !== 0) {
                    continue;
                }
                $synced_at = (int) $synced_at;
                if ($synced_at <= 0 || (!$overwrite_existing && isset($snapshot['sync'][$sync_scope]))) {
                    continue;
                }
                $snapshot['sync'][sanitize_text_field((string) $sync_scope)] = [
                    'status' => 'ready',
                    'last_attempt' => $synced_at,
                    'last_success' => $synced_at,
                    'error_code' => '',
                    'error_message' => '',
                    'connection_fingerprint' => '',
                ];
            }

            $snapshot['revision'] = self::create_revision();
            $snapshot['updated_at'] = time();
            $write_result = self::write_provider_snapshot($provider, $snapshot);
            if (is_wp_error($write_result)) {
                return false;
            }
        }

        update_option(self::SCHEMA_VERSION_OPTION, (string) self::SCHEMA_VERSION, 'no');
        self::clear_caches();
        return true;
    }

    /**
     * Export all canonical snapshots for Settings Backup.
     *
     * @return array<string, mixed>
     */
    public static function export_state(): array
    {
        $snapshots = [];
        foreach (array_keys(AIPKit_Model_Catalog::get_catalog_keys_by_provider()) as $provider) {
            $option_name = self::get_snapshot_option_name($provider);
            $snapshot = get_option($option_name, null);
            if (is_array($snapshot) && !empty($snapshot)) {
                $snapshots[$provider] = $snapshot;
            }
        }

        return [
            'schema_version' => self::SCHEMA_VERSION,
            'manifest' => get_option(self::MANIFEST_OPTION, []),
            'snapshots' => $snapshots,
        ];
    }

    /**
     * Validate a Settings Backup registry block without mutating options.
     *
     * Unknown providers and catalog keys are ignored for forward compatibility,
     * while malformed known snapshot structures are rejected.
     *
     * @return true|WP_Error
     */
    public static function validate_import_state(array $state)
    {
        if (isset($state['schema_version']) && (int) $state['schema_version'] > self::SCHEMA_VERSION) {
            return new WP_Error(
                'aipkit_registry_backup_too_new',
                __('This model registry backup was created by a newer plugin version.', 'gpt3-ai-content-generator')
            );
        }

        if (isset($state['snapshots']) && !is_array($state['snapshots'])) {
            return new WP_Error(
                'aipkit_registry_invalid_backup',
                __('Model registry backup snapshots are invalid.', 'gpt3-ai-content-generator')
            );
        }

        $known_by_provider = AIPKit_Model_Catalog::get_catalog_keys_by_provider();
        foreach (($state['snapshots'] ?? []) as $provider => $snapshot) {
            $provider = sanitize_text_field((string) $provider);
            if ($provider === '' || !is_array($snapshot)) {
                return new WP_Error(
                    'aipkit_registry_invalid_backup',
                    __('Model registry backup data is invalid.', 'gpt3-ai-content-generator')
                );
            }
            if (!isset($known_by_provider[$provider])) {
                continue;
            }
            if (isset($snapshot['catalogs']) && !is_array($snapshot['catalogs'])) {
                return new WP_Error(
                    'aipkit_registry_invalid_catalogs',
                    __('A model registry provider catalog is invalid.', 'gpt3-ai-content-generator')
                );
            }
            if (isset($snapshot['sync']) && !is_array($snapshot['sync'])) {
                return new WP_Error(
                    'aipkit_registry_invalid_sync_state',
                    __('A model registry synchronization state is invalid.', 'gpt3-ai-content-generator')
                );
            }
            foreach (($snapshot['catalogs'] ?? []) as $catalog_key => $rows) {
                $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key((string) $catalog_key);
                if (in_array($catalog_key, $known_by_provider[$provider], true) && !is_array($rows)) {
                    return new WP_Error(
                        'aipkit_registry_invalid_catalog',
                        __('A model registry catalog entry is invalid.', 'gpt3-ai-content-generator')
                    );
                }
            }
        }

        return true;
    }

    /**
     * Import canonical snapshots from Settings Backup.
     *
     * @param array<string, mixed> $state
     * @return true|WP_Error
     */
    public static function import_state(array $state)
    {
        $validation = self::validate_import_state($state);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $snapshots = isset($state['snapshots']) && is_array($state['snapshots'])
            ? $state['snapshots']
            : [];
        foreach ($snapshots as $provider => $snapshot) {
            $provider = sanitize_text_field((string) $provider);
            if ($provider === '' || !is_array($snapshot)) {
                return new WP_Error('aipkit_registry_invalid_backup', __('Model registry backup data is invalid.', 'gpt3-ai-content-generator'));
            }
            $known_catalogs = AIPKit_Model_Catalog::get_catalog_keys_by_provider()[$provider] ?? [];
            if (empty($known_catalogs)) {
                continue;
            }

            $normalized_snapshot = self::empty_snapshot($provider);
            $incoming_catalogs = isset($snapshot['catalogs']) && is_array($snapshot['catalogs'])
                ? $snapshot['catalogs']
                : [];
            foreach ($incoming_catalogs as $catalog_key => $rows) {
                $catalog_key = AIPKit_Model_Catalog::normalize_catalog_key((string) $catalog_key);
                if (!in_array($catalog_key, $known_catalogs, true) || !is_array($rows)) {
                    continue;
                }
                $source = isset($rows[0]['source']) ? sanitize_key((string) $rows[0]['source']) : 'legacy';
                $status = isset($rows[0]['status']) ? sanitize_key((string) $rows[0]['status']) : 'legacy';
                $verified = !empty($rows[0]['verified']);
                $normalized_snapshot['catalogs'][$catalog_key] = self::normalize_rows(
                    $catalog_key,
                    $rows,
                    $source,
                    $status,
                    $verified
                );
            }
            $normalized_snapshot['sync'] = self::sanitize_sync_map($snapshot['sync'] ?? []);
            $normalized_snapshot['revision'] = self::create_revision();
            $normalized_snapshot['updated_at'] = time();
            $write_result = self::write_provider_snapshot($provider, $normalized_snapshot);
            if (is_wp_error($write_result)) {
                return $write_result;
            }
            foreach ($normalized_snapshot['catalogs'] as $catalog_key => $records) {
                self::mirror_legacy_catalog($catalog_key, $records);
            }
        }

        update_option(self::SCHEMA_VERSION_OPTION, (string) self::SCHEMA_VERSION, 'no');
        self::clear_caches();
        return true;
    }

    public static function clear_caches(): void
    {
        self::$snapshot_cache = [];
        self::$catalog_cache = [];
        self::$provider_state_cache = null;

        foreach (array_keys(AIPKit_Model_Catalog::get_definitions()) as $catalog_key) {
            delete_transient('aipkit_' . strtolower($catalog_key) . '_models_cache');
        }
    }

    private static function get_snapshot_option_name(string $provider): string
    {
        return self::SNAPSHOT_OPTION_PREFIX . sanitize_key($provider);
    }

    /**
     * Return a one-way fingerprint of connection values relevant to model sync.
     *
     * @param array<string, mixed> $config
     */
    private static function get_connection_fingerprint(string $provider, array $config): string
    {
        $field_map = [
            'OpenAI' => ['api_key', 'base_url', 'api_version'],
            'OpenRouter' => ['api_key', 'base_url', 'api_version'],
            'Google' => ['api_key', 'base_url', 'api_version'],
            'Azure' => ['api_key', 'endpoint', 'azure_endpoint', 'api_version_authoring'],
            'Claude' => ['api_key', 'base_url', 'api_version'],
            'DeepSeek' => ['api_key', 'base_url', 'api_version'],
            'xAI' => ['api_key', 'base_url', 'api_version'],
            'Ollama' => ['base_url'],
            'ElevenLabs' => ['api_key', 'base_url', 'api_version'],
            'Replicate' => ['api_key'],
            'Pinecone' => ['api_key'],
            'Qdrant' => ['api_key', 'url'],
            'Chroma' => ['api_key', 'url', 'tenant', 'database'],
        ];
        $fields = $field_map[$provider] ?? ['api_key', 'base_url', 'url', 'endpoint'];
        $parts = [$provider];
        $has_value = false;
        foreach ($fields as $field) {
            $value = trim((string) ($config[$field] ?? ''));
            if ($value !== '') {
                $has_value = true;
            }
            $parts[] = $field . '=' . $value;
        }
        if (!$has_value) {
            return '';
        }
        $payload = implode('|', $parts);
        $salt = function_exists('wp_salt') ? wp_salt('auth') : 'aipkit-model-registry';
        return hash_hmac('sha256', $payload, $salt);
    }

    /**
     * @return array<string, mixed>
     */
    private static function get_provider_snapshot(string $provider): array
    {
        $provider = sanitize_text_field(trim($provider));
        $cache_key = strtolower($provider);
        if (isset(self::$snapshot_cache[$cache_key])) {
            return self::$snapshot_cache[$cache_key];
        }

        $snapshot = get_option(self::get_snapshot_option_name($provider), []);
        if (!is_array($snapshot)) {
            $snapshot = [];
        }
        $snapshot = array_merge(self::empty_snapshot($provider), $snapshot);
        $snapshot['catalogs'] = isset($snapshot['catalogs']) && is_array($snapshot['catalogs']) ? $snapshot['catalogs'] : [];
        $snapshot['sync'] = isset($snapshot['sync']) && is_array($snapshot['sync']) ? $snapshot['sync'] : [];
        self::$snapshot_cache[$cache_key] = $snapshot;
        return $snapshot;
    }

    /**
     * @return array{exists:bool,authoritative:bool,records:array<int,array<string,mixed>>}
     */
    private static function get_persisted_catalog(string $catalog_key): array
    {
        $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
        $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
        $snapshot = self::get_provider_snapshot($provider);
        $sync_scope = self::get_sync_scope_for_catalog($catalog_key);
        $sync_state = isset($snapshot['sync'][$sync_scope]) && is_array($snapshot['sync'][$sync_scope])
            ? $snapshot['sync'][$sync_scope]
            : [];
        $authoritative = ($sync_state['status'] ?? '') === 'ready'
            && (int) ($sync_state['last_success'] ?? 0) > 0;
        if (array_key_exists($catalog_key, $snapshot['catalogs']) && is_array($snapshot['catalogs'][$catalog_key])) {
            return [
                'exists' => true,
                'authoritative' => $authoritative,
                'records' => self::normalize_rows_from_snapshot($catalog_key, $snapshot['catalogs'][$catalog_key]),
            ];
        }

        $legacy_option = sanitize_key((string) ($definition['legacy_option'] ?? ''));
        if ($legacy_option !== '') {
            $legacy_rows = get_option($legacy_option, []);
            if (is_array($legacy_rows) && !empty($legacy_rows)) {
                return [
                    'exists' => true,
                    'authoritative' => false,
                    'records' => self::normalize_rows($catalog_key, $legacy_rows, 'legacy', 'legacy', false),
                ];
            }
        }

        return ['exists' => false, 'authoritative' => false, 'records' => []];
    }

    /**
     * Surface models that an older plugin version stored in a provider's main
     * catalog even though they belong to a capability-specific sibling catalog.
     * The next successful sync persists the corrected partition permanently.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function get_reclassified_primary_records(string $catalog_key): array
    {
        $primary_catalog = '';
        if (in_array($catalog_key, ['OpenAIImage', 'OpenAIEmbedding', 'OpenAITTS', 'OpenAISTT', 'OpenAIRealtime'], true)) {
            $primary_catalog = 'OpenAI';
        } elseif (in_array($catalog_key, ['GoogleImage', 'GoogleVideo', 'GoogleEmbedding'], true)) {
            $primary_catalog = 'Google';
        }
        if ($primary_catalog === '') {
            return [];
        }

        $definition = AIPKit_Model_Catalog::get_definition($primary_catalog);
        $provider = sanitize_text_field((string) ($definition['provider'] ?? ''));
        $snapshot = self::get_provider_snapshot($provider);
        $primary_records = $snapshot['catalogs'][$primary_catalog] ?? [];
        return is_array($primary_records)
            ? self::normalize_rows_from_snapshot($catalog_key, $primary_records)
            : [];
    }

    /**
     * @param array<int, mixed> $records
     * @return array<int, array<string, mixed>>
     */
    private static function normalize_rows_from_snapshot(string $catalog_key, array $records): array
    {
        $normalized = [];
        foreach ($records as $record) {
            if (!is_array($record)) {
                continue;
            }
            $source = sanitize_key((string) ($record['source'] ?? 'legacy'));
            $status = sanitize_key((string) ($record['status'] ?? 'legacy'));
            $verified = !empty($record['verified']);
            $row = self::normalize_rows($catalog_key, [$record], $source, $status, $verified);
            if (!empty($row)) {
                $row[0]['order'] = (int) ($record['order'] ?? $row[0]['order'] ?? 0);
                $normalized[] = $row[0];
            }
        }
        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private static function empty_snapshot(string $provider): array
    {
        return [
            'schema_version' => self::SCHEMA_VERSION,
            'provider' => sanitize_text_field($provider),
            'revision' => '',
            'updated_at' => 0,
            'catalogs' => [],
            'sync' => [],
        ];
    }

    /**
     * @param array<string, mixed> $snapshot
     * @return true|WP_Error
     */
    private static function write_provider_snapshot(string $provider, array $snapshot)
    {
        $snapshot['schema_version'] = self::SCHEMA_VERSION;
        $snapshot['provider'] = sanitize_text_field($provider);
        $option_name = self::get_snapshot_option_name($provider);
        update_option($option_name, $snapshot, 'no');
        $stored = get_option($option_name, []);
        if (!is_array($stored) || wp_json_encode($stored) !== wp_json_encode($snapshot)) {
            return new WP_Error('aipkit_registry_snapshot_write_failed', __('Could not save the synchronized model catalog.', 'gpt3-ai-content-generator'));
        }

        $manifest = get_option(self::MANIFEST_OPTION, []);
        $manifest = is_array($manifest) ? $manifest : [];
        $manifest['schema_version'] = self::SCHEMA_VERSION;
        $manifest['updated_at'] = time();
        $manifest['providers'] = isset($manifest['providers']) && is_array($manifest['providers'])
            ? $manifest['providers']
            : [];
        $manifest['providers'][$provider] = [
            'option' => $option_name,
            'revision' => sanitize_text_field((string) ($snapshot['revision'] ?? '')),
            'updated_at' => (int) ($snapshot['updated_at'] ?? 0),
        ];
        update_option(self::MANIFEST_OPTION, $manifest, 'no');

        self::$snapshot_cache[strtolower($provider)] = $snapshot;
        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $records
     */
    private static function mirror_legacy_catalog(string $catalog_key, array $records): void
    {
        $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
        $option_name = sanitize_key((string) ($definition['legacy_option'] ?? ''));
        $legacy_rows = self::records_to_legacy_rows($catalog_key, $records);
        if ($option_name !== '') {
            update_option($option_name, $legacy_rows, 'no');
        }

        if (($definition['resource_type'] ?? '') === 'vector_target') {
            self::mirror_vector_store_registry(
                sanitize_text_field((string) ($definition['provider'] ?? '')),
                $legacy_rows
            );
        }
    }

    /**
     * Keep the established vector target cache usable by modules that have not
     * yet moved to the Phase 2 catalog client.
     *
     * @param array<mixed> $rows
     */
    private static function mirror_vector_store_registry(string $provider, array $rows): void
    {
        if ($provider === '') {
            return;
        }
        $normalized_rows = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $row['provider'] = $provider;
            $normalized_rows[] = $row;
        }

        $registry = get_option('aipkit_vector_stores_registry', []);
        $registry = is_array($registry) ? $registry : [];
        $registry[$provider] = $normalized_rows;
        update_option('aipkit_vector_stores_registry', $registry, 'no');
        wp_cache_delete('aipkit_vector_stores_registry', 'options');
    }

    private static function mirror_legacy_sync_timestamp(string $sync_scope, int $synced_at): void
    {
        $timestamps = get_option('aipkit_model_sync_timestamps', []);
        $timestamps = is_array($timestamps) ? $timestamps : [];
        $timestamps[$sync_scope] = $synced_at;
        update_option('aipkit_model_sync_timestamps', $timestamps, 'no');
    }

    private static function remove_legacy_sync_timestamp(string $sync_scope): void
    {
        $timestamps = get_option('aipkit_model_sync_timestamps', []);
        if (!is_array($timestamps) || !array_key_exists($sync_scope, $timestamps)) {
            return;
        }
        unset($timestamps[$sync_scope]);
        update_option('aipkit_model_sync_timestamps', $timestamps, 'no');
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array<mixed>
     */
    private static function records_to_legacy_rows(string $catalog_key, array $records): array
    {
        $rows = [];
        foreach ($records as $record) {
            $metadata = isset($record['metadata']) && is_array($record['metadata'])
                ? $record['metadata']
                : [];
            $row = $metadata;
            $row['id'] = sanitize_text_field((string) ($record['raw_id'] ?? $record['id'] ?? ''));
            $row['name'] = sanitize_text_field((string) ($record['name'] ?? $row['id']));
            if (!empty($record['capabilities']) && is_array($record['capabilities'])) {
                $row['capabilities'] = $record['capabilities'];
            }
            if ($row['id'] === '') {
                continue;
            }
            $row['recommended'] = !empty($record['recommended']);
            $row['family_key'] = sanitize_key((string) ($record['family_key'] ?? 'other'));
            $row['family_label'] = sanitize_text_field((string) ($record['family_label'] ?? __('Other', 'gpt3-ai-content-generator')));
            $row['family_order'] = (int) ($record['family_order'] ?? 999);
            $row['family_collapsed'] = !empty($record['family_collapsed']);
            $rows[] = $row;
        }

        $definition = AIPKit_Model_Catalog::get_definition($catalog_key);
        if (!empty($definition['legacy_grouped'])) {
            return AIPKit_Model_Catalog::group_rows_by_family($catalog_key, $rows);
        }

        return array_values($rows);
    }

    /**
     * @param array<int, array<string, mixed>> ...$lists
     * @return array<int, array<string, mixed>>
     */
    private static function merge_records(array ...$lists): array
    {
        $merged = [];
        $indexes = [];
        foreach ($lists as $records) {
            foreach ($records as $record) {
                if (!is_array($record)) {
                    continue;
                }
                $identity = strtolower((string) ($record['canonical_id'] ?? $record['id'] ?? ''));
                if ($identity === '') {
                    continue;
                }
                if (isset($indexes[$identity])) {
                    $index = $indexes[$identity];
                    $merged[$index] = self::merge_record($merged[$index], $record);
                } else {
                    $indexes[$identity] = count($merged);
                    $merged[] = $record;
                }
            }
        }
        return $merged;
    }

    /**
     * @param array<string, mixed> $base
     * @param array<string, mixed> $override
     * @return array<string, mixed>
     */
    private static function merge_record(array $base, array $override): array
    {
        $prefer_base = self::get_record_authority_rank($base) > self::get_record_authority_rank($override);
        $merged = $prefer_base
            ? array_merge($override, $base)
            : array_merge($base, $override);
        $base_metadata = isset($base['metadata']) && is_array($base['metadata']) ? $base['metadata'] : [];
        $override_metadata = isset($override['metadata']) && is_array($override['metadata']) ? $override['metadata'] : [];
        $merged['metadata'] = $prefer_base
            ? array_merge($override_metadata, $base_metadata)
            : array_merge($base_metadata, $override_metadata);

        $merged['capabilities'] = [];
        $capability_keys = array_unique(array_merge(
            array_keys(isset($base['capabilities']) && is_array($base['capabilities']) ? $base['capabilities'] : []),
            array_keys(isset($override['capabilities']) && is_array($override['capabilities']) ? $override['capabilities'] : [])
        ));
        foreach ($capability_keys as $capability_key) {
            $merged['capabilities'][$capability_key] = !empty($base['capabilities'][$capability_key])
                || !empty($override['capabilities'][$capability_key]);
        }
        $merged['catalog_keys'] = array_values(array_unique(array_merge(
            self::normalize_string_list($base['catalog_keys'] ?? []),
            self::normalize_string_list($override['catalog_keys'] ?? [])
        )));
        $merged['kinds'] = array_values(array_unique(array_merge(
            self::normalize_string_list($base['kinds'] ?? []),
            self::normalize_string_list($override['kinds'] ?? [])
        )));
        $merged['recommended'] = !empty($base['recommended']) || !empty($override['recommended']);
        $merged['default'] = !empty($base['default']) || !empty($override['default']);
        return $merged;
    }

    /**
     * Prefer synchronized records while still allowing lower-authority seeds
     * to contribute missing capabilities and metadata.
     *
     * @param array<string, mixed> $record
     */
    private static function get_record_authority_rank(array $record): int
    {
        if (!empty($record['verified']) && ($record['status'] ?? '') === 'available') {
            return 4;
        }
        $source = sanitize_key((string) ($record['source'] ?? ''));
        if ($source === 'synced') {
            return 3;
        }
        if (in_array($source, ['legacy', 'manual'], true)) {
            return 2;
        }
        return 1;
    }

    /**
     * @param array<int, array<string, mixed>> $records
     * @return array<int, array<string, mixed>>
     */
    private static function filter_deprecated_records(string $catalog_key, array $records): array
    {
        if (empty(AIPKit_Model_Catalog::get_deprecated_ids($catalog_key))) {
            return array_values($records);
        }

        return array_values(array_filter($records, static function (array $record) use ($catalog_key): bool {
            $model_id = strtolower((string) ($record['canonical_id'] ?? $record['id'] ?? ''));
            return !AIPKit_Model_Catalog::is_deprecated_id($catalog_key, $model_id);
        }));
    }

    /**
     * @param array<mixed> $rows
     * @return array<int, array{row:mixed,group:string}>
     */
    private static function flatten_rows(array $rows): array
    {
        $flattened = [];
        $is_list = empty($rows) || array_keys($rows) === range(0, count($rows) - 1);
        if ($is_list) {
            foreach ($rows as $row) {
                $flattened[] = ['row' => $row, 'group' => ''];
            }
            return $flattened;
        }

        if (isset($rows['id']) || isset($rows['name']) || isset($rows['model'])) {
            return [['row' => $rows, 'group' => '']];
        }

        foreach ($rows as $group => $group_rows) {
            if (!is_array($group_rows)) {
                continue;
            }
            foreach ($group_rows as $row) {
                $flattened[] = ['row' => $row, 'group' => (string) $group];
            }
        }
        return $flattened;
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<string, mixed> $metadata
     * @param array<string, bool> $capabilities
     * @return array<string, bool>
     */
    private static function infer_capabilities(array $definition, array $metadata, array $capabilities): array
    {
        $input_modalities = self::extract_modality_list($metadata, 'input');
        $output_modalities = self::extract_modality_list($metadata, 'output');
        if (!empty($input_modalities)) {
            $capabilities['image_input'] = in_array('image', $input_modalities, true)
                || in_array('image_url', $input_modalities, true)
                || in_array('input_image', $input_modalities, true);
        }
        if (!empty($output_modalities)) {
            $has_image_output = in_array('image', $output_modalities, true)
                || in_array('image_url', $output_modalities, true)
                || in_array('output_image', $output_modalities, true);
            $has_text_output = in_array('text', $output_modalities, true);
            $capabilities['image_generation'] = $has_image_output;
            if (($definition['kind'] ?? '') === 'text') {
                $capabilities['text_generation'] = $has_text_output || !$has_image_output;
            }
        }

        $generation_methods = self::normalize_string_list(
            $metadata['supportedGenerationMethods'] ?? $metadata['supportedgenerationmethods'] ?? []
        );
        $generation_methods = array_map('strtolower', $generation_methods);
        if (in_array('embedcontent', $generation_methods, true)) {
            $capabilities['embeddings'] = true;
        }
        if (in_array('predict', $generation_methods, true)) {
            $capabilities['image_generation'] = true;
        }
        if (in_array('predictlongrunning', $generation_methods, true)) {
            $capabilities['video_generation'] = true;
        }

        if (!empty($capabilities['chat']) || !empty($capabilities['completion'])) {
            $capabilities['text_generation'] = true;
        }
        if (!empty($capabilities['embedding'])) {
            $capabilities['embeddings'] = true;
        }
        if (!empty($capabilities['vision'])) {
            $capabilities['image_input'] = true;
        }
        if (!empty($capabilities['image_output'])) {
            $capabilities['image_generation'] = true;
        }
        if (!empty($capabilities['image_input']) && !empty($capabilities['image_generation'])) {
            $capabilities['image_editing'] = true;
        }

        return self::normalize_capabilities($capabilities);
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<int, string>
     */
    private static function extract_modality_list(array $metadata, string $direction): array
    {
        $key = $direction . '_modalities';
        $modalities = $metadata[$key] ?? [];
        if (empty($modalities) && isset($metadata['architecture']) && is_array($metadata['architecture'])) {
            $modalities = $metadata['architecture'][$key] ?? [];
        }
        return array_map('strtolower', self::normalize_string_list($modalities));
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<string, bool> $capabilities
     * @return array<int, string>
     */
    private static function derive_kinds(array $definition, array $capabilities): array
    {
        if (($definition['resource_type'] ?? 'model') !== 'model') {
            return ['resource'];
        }

        $kinds = [];
        if (!empty($capabilities['text_generation'])) {
            $kinds[] = 'text';
        }
        if (!empty($capabilities['embeddings'])) {
            $kinds[] = 'embedding';
        }
        if (!empty($capabilities['image_generation'])) {
            $kinds[] = 'image';
        }
        if (!empty($capabilities['video_generation'])) {
            $kinds[] = 'video';
        }
        if (!empty($capabilities['tts']) || !empty($capabilities['stt']) || !empty($capabilities['realtime'])) {
            $kinds[] = 'audio';
        }
        if (
            !empty($capabilities['image_input'])
            && (!empty($capabilities['text_generation']) || !empty($capabilities['image_generation']))
        ) {
            $kinds[] = 'multimodal';
        }
        if (empty($kinds) && !empty($definition['kind'])) {
            $kinds[] = sanitize_key((string) $definition['kind']);
        }
        return array_values(array_unique($kinds));
    }

    /**
     * @param array<string, mixed> $capabilities
     * @return array<string, bool>
     */
    private static function normalize_capabilities(array $capabilities): array
    {
        $normalized = [];
        foreach ($capabilities as $capability => $supported) {
            $capability = sanitize_key((string) $capability);
            if ($capability !== '') {
                $normalized[$capability] = (bool) $supported;
            }
        }
        return $normalized;
    }

    private static function canonicalize_model_id(string $provider, string $model_id): string
    {
        $model_id = sanitize_text_field(trim($model_id));
        if (strcasecmp($provider, 'Google') === 0 && strpos($model_id, 'models/') === 0) {
            return (string) substr($model_id, 7);
        }
        return $model_id;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function normalize_string_list($value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }
        if (!is_array($value)) {
            return [];
        }
        $normalized = [];
        foreach ($value as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $item = sanitize_text_field((string) $item);
            if ($item !== '') {
                $normalized[$item] = true;
            }
        }
        return array_keys($normalized);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function sanitize_recursive($value)
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                $safe_key = is_int($key) ? $key : sanitize_text_field((string) $key);
                $sanitized[$safe_key] = self::sanitize_recursive($item);
            }
            return $sanitized;
        }
        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }
        return sanitize_text_field((string) $value);
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function is_provider_configured(string $provider, array $config): bool
    {
        $requirements = [
            'OpenAI' => ['api_key'],
            'OpenRouter' => ['api_key'],
            'Google' => ['api_key'],
            'Azure' => ['api_key', 'endpoint'],
            'Claude' => ['api_key'],
            'DeepSeek' => ['api_key'],
            'xAI' => ['api_key'],
            'Ollama' => ['base_url'],
            'ElevenLabs' => ['api_key'],
            'Replicate' => ['api_key'],
            'Pexels' => ['api_key'],
            'Pixabay' => ['api_key'],
            'Pinecone' => ['api_key'],
            'Qdrant' => ['api_key', 'url'],
            'Chroma' => ['url'],
        ];
        $required_fields = $requirements[$provider] ?? ['api_key'];
        foreach ($required_fields as $field) {
            if (trim((string) ($config[$field] ?? '')) === '') {
                return false;
            }
        }
        return true;
    }

    /**
     * @param mixed $sync_map
     * @return array<string, array<string, mixed>>
     */
    private static function sanitize_sync_map($sync_map): array
    {
        if (!is_array($sync_map)) {
            return [];
        }
        $normalized = [];
        foreach ($sync_map as $scope => $state) {
            if (!is_array($state)) {
                continue;
            }
            $scope = sanitize_text_field((string) $scope);
            if ($scope === '') {
                continue;
            }
            $normalized[$scope] = [
                'status' => sanitize_key((string) ($state['status'] ?? '')),
                'last_attempt' => max(0, (int) ($state['last_attempt'] ?? 0)),
                'last_success' => max(0, (int) ($state['last_success'] ?? 0)),
                'error_code' => sanitize_key((string) ($state['error_code'] ?? '')),
                'error_message' => sanitize_text_field((string) ($state['error_message'] ?? '')),
                'connection_fingerprint' => sanitize_text_field((string) ($state['connection_fingerprint'] ?? '')),
            ];
        }
        return $normalized;
    }

    /**
     * @param mixed $sync_map
     * @return array<string, mixed>
     */
    private static function get_latest_sync_state($sync_map): array
    {
        $sync_map = self::sanitize_sync_map($sync_map);
        $latest = [];
        foreach ($sync_map as $state) {
            if ((int) ($state['last_attempt'] ?? 0) >= (int) ($latest['last_attempt'] ?? 0)) {
                $latest = $state;
            }
        }
        return $latest;
    }

    /**
     * @param mixed $sync_map
     * @return array<string, mixed>
     */
    private static function get_latest_error_state($sync_map): array
    {
        $latest = [];
        foreach (self::sanitize_sync_map($sync_map) as $state) {
            if (($state['status'] ?? '') !== 'error') {
                continue;
            }
            if ((int) ($state['last_attempt'] ?? 0) >= (int) ($latest['last_attempt'] ?? 0)) {
                $latest = $state;
            }
        }
        return $latest;
    }

    /**
     * @param mixed $sync_map
     */
    private static function get_latest_success_timestamp($sync_map): int
    {
        $latest_success = 0;
        foreach (self::sanitize_sync_map($sync_map) as $state) {
            $latest_success = max($latest_success, (int) ($state['last_success'] ?? 0));
        }
        return $latest_success;
    }

    /**
     * Separate model and provider-resource health so one successful resource
     * refresh cannot mask stale model catalogs.
     *
     * @param array<string, array<string, mixed>> $sync_states
     * @return array{models:array<string,array<string,mixed>>,resources:array<string,array<string,mixed>>,has_model_catalogs:bool,has_resource_catalogs:bool}
     */
    private static function get_provider_sync_scope_groups(string $provider, array $sync_states): array
    {
        $model_scopes = [];
        $resource_scopes = [];
        foreach (AIPKit_Model_Catalog::get_definitions() as $catalog_key => $definition) {
            if (strcasecmp((string) ($definition['provider'] ?? ''), $provider) !== 0) {
                continue;
            }
            $sync_scope = self::get_sync_scope_for_catalog($catalog_key);
            if (($definition['resource_type'] ?? 'model') === 'model') {
                $model_scopes[$sync_scope] = true;
            } else {
                $resource_scopes[$sync_scope] = true;
            }
        }

        // Provider-specific extension scopes represent model capabilities unless
        // they are explicitly owned by a resource catalog.
        foreach (array_keys($sync_states) as $sync_scope) {
            if (!isset($resource_scopes[$sync_scope])) {
                $model_scopes[$sync_scope] = true;
            }
        }

        return [
            'models' => array_intersect_key($sync_states, $model_scopes),
            'resources' => array_intersect_key($sync_states, $resource_scopes),
            'has_model_catalogs' => !empty($model_scopes),
            'has_resource_catalogs' => !empty($resource_scopes),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $sync_states
     * @return array{status:string,connection_changed:bool,last_attempt:int,last_success:int,error_code:string,error_message:string}
     */
    private static function summarize_provider_sync_state(
        bool $configured,
        bool $locked,
        array $sync_states,
        string $current_fingerprint,
        int $stale_after,
        int $now
    ): array {
        $latest_sync = self::get_latest_sync_state($sync_states);
        $latest_error = self::get_latest_error_state($sync_states);
        $last_success = self::get_latest_success_timestamp($sync_states);
        $synced_fingerprint = (string) ($latest_sync['connection_fingerprint'] ?? '');
        $connection_changed = $configured
            && $synced_fingerprint !== ''
            && $current_fingerprint !== ''
            && !hash_equals($synced_fingerprint, $current_fingerprint);

        $status = 'not_configured';
        if ($locked) {
            $status = 'locked';
        } elseif ($configured) {
            if ($connection_changed || empty($latest_sync)) {
                $status = 'configured_unsynced';
            } elseif (!empty($latest_error) && $last_success <= 0) {
                $status = 'error';
            } elseif (!empty($latest_error)) {
                $status = 'stale';
            } elseif ($last_success > 0 && ($now - $last_success) > $stale_after) {
                $status = 'stale';
            } elseif ($last_success > 0) {
                $status = 'ready';
            } else {
                $status = 'configured_unsynced';
            }
        }

        return [
            'status' => $status,
            'connection_changed' => $connection_changed,
            'last_attempt' => (int) ($latest_sync['last_attempt'] ?? 0),
            'last_success' => $last_success,
            'error_code' => sanitize_key((string) ($latest_error['error_code'] ?? '')),
            'error_message' => sanitize_text_field((string) ($latest_error['error_message'] ?? '')),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $sync_states
     * @return array<string, array<string, mixed>>
     */
    private static function get_public_sync_scope_states(array $sync_states): array
    {
        $public_states = [];
        foreach ($sync_states as $scope => $state) {
            $public_states[$scope] = [
                'status' => sanitize_key((string) ($state['status'] ?? '')),
                'last_attempt' => max(0, (int) ($state['last_attempt'] ?? 0)),
                'last_success' => max(0, (int) ($state['last_success'] ?? 0)),
                'error_code' => sanitize_key((string) ($state['error_code'] ?? '')),
                'error_message' => sanitize_text_field((string) ($state['error_message'] ?? '')),
            ];
        }
        return $public_states;
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private static function count_persisted_models(array $snapshot): int
    {
        $ids = [];
        foreach (($snapshot['catalogs'] ?? []) as $catalog_key => $records) {
            $definition = AIPKit_Model_Catalog::get_definition((string) $catalog_key);
            if (($definition['resource_type'] ?? '') !== 'model' || !is_array($records)) {
                continue;
            }
            foreach ($records as $record) {
                if (!is_array($record)) {
                    continue;
                }
                $id = sanitize_text_field((string) ($record['canonical_id'] ?? $record['id'] ?? ''));
                if ($id !== '') {
                    $ids[$id] = true;
                }
            }
        }
        return count($ids);
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private static function count_persisted_resources(array $snapshot): int
    {
        $ids = [];
        foreach (($snapshot['catalogs'] ?? []) as $catalog_key => $records) {
            $definition = AIPKit_Model_Catalog::get_definition((string) $catalog_key);
            if (($definition['resource_type'] ?? 'model') === 'model' || !is_array($records)) {
                continue;
            }
            foreach ($records as $record) {
                if (!is_array($record)) {
                    continue;
                }
                $id = sanitize_text_field((string) ($record['canonical_id'] ?? $record['id'] ?? ''));
                if ($id !== '') {
                    $ids[$catalog_key . '|' . $id] = true;
                }
            }
        }
        return count($ids);
    }

    private static function get_sync_scope_for_catalog(string $catalog_key): string
    {
        $scope_map = [
            'OpenAIEmbedding' => 'OpenAI',
            'OpenAITTS' => 'OpenAI',
            'OpenAISTT' => 'OpenAI',
            'OpenAIImage' => 'OpenAI',
            'OpenAIRealtime' => 'OpenAI',
            'OpenAIVectorStores' => 'OpenAIVectorStores',
            'OpenRouterEmbedding' => 'OpenRouter',
            'GoogleImage' => 'Google',
            'GoogleVideo' => 'Google',
            'GoogleEmbedding' => 'Google',
            'AzureImage' => 'Azure',
            'AzureEmbedding' => 'Azure',
            'xAIImage' => 'xAI',
            'OllamaEmbedding' => 'Ollama',
            'OllamaVision' => 'Ollama',
            'OllamaCapabilities' => 'Ollama',
        ];
        return $scope_map[$catalog_key] ?? $catalog_key;
    }

    private static function create_revision(): string
    {
        $suffix = function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('', true);
        return time() . '-' . sanitize_key((string) $suffix);
    }
}
