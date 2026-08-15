<?php

namespace WPAICG\Core; // *** Correct namespace ***

use WP_Error;
use WPAICG\AIPKit_Providers; // For accessing settings
use WPAICG\Core\Models\AIPKit_Model_Catalog;
use WPAICG\Core\Providers\ProviderStrategyFactory; // *** Use the factory from Core\Providers ***

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * AIPKit_Models_API
 *
 * Manages fetching and formatting model/deployment lists from various AI providers
 * using the Provider Strategy pattern.
 */
class AIPKit_Models_API {

    /**
     * Fetches models or deployments from the provider using its strategy.
     *
     * @param string $provider 'OpenAI','OpenRouter','Google','Azure'.
     * @param array $api_params Contains API key, base_url, api_version, endpoints etc. from provider settings.
     * @return array|WP_Error List of models/deployments [['id' => ..., 'name' => ...]] or WP_Error.
     */
    public static function get_models($provider, $api_params = []) {
        $strategy = ProviderStrategyFactory::get_strategy($provider); // *** Use correct namespace ***
        if (is_wp_error($strategy)) {
            return $strategy;
        }

        // Delegate model fetching to the strategy
        return $strategy->get_models($api_params);
    }


    /**
     * Compatibility wrapper for callers that still expect grouped OpenAI rows.
     * The actual taxonomy lives in the canonical model catalog.
     */
    public static function group_openai_models($models) {
        return is_array($models)
            ? AIPKit_Model_Catalog::group_rows_by_family('OpenAI', $models)
            : [];
    }

}
