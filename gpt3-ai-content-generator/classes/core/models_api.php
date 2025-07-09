<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/core/models_api.php

namespace WPAICG\Core; // *** Correct namespace ***

use WP_Error;
use WPAICG\AIPKit_Providers; // For accessing settings
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
     * Group OpenAI models into categories for UI.
     * This remains here as it's primarily UI-related grouping.
     */
    public static function group_openai_models($models) {
         $groups = [
            'GPT-4o'     => [],
            'GPT-4 Turbo'=> [],
            'GPT-4'      => [],
            'GPT-3.5'    => [],
            'Fine-tuned' => [],
            'Other'      => [],
        ];
        if (empty($models) || !is_array($models)) {
            return $groups;
        }
        foreach ($models as $model) {
            $id = $model['id'] ?? '';
            if (empty($id)) continue;

            $idLower = strtolower($id);
            // OpenAI model grouping doesn't typically rely on 'owned_by' as much as it used to.
            // Rely more on naming conventions.

             // Grouping logic based on ID prefixes/contents
             if (strpos($id, 'ft:') === 0 || strpos($id, ':ft-') !== false) { // More robust fine-tune check
                $groups['Fine-tuned'][] = $model;
            } elseif (strpos($idLower, 'gpt-4o') !== false) {
                $groups['GPT-4o'][] = $model;
            } elseif (strpos($idLower, 'gpt-4-turbo') !== false || strpos($idLower, 'gpt-4-1106') !== false || strpos($idLower, 'gpt-4-0125') !== false) {
                 $groups['GPT-4 Turbo'][] = $model;
            } elseif (strpos($idLower, 'gpt-4') !== false) { // Catch remaining GPT-4s (including vision)
                $groups['GPT-4'][] = $model;
            } elseif (strpos($idLower, 'gpt-3.5') !== false) {
                $groups['GPT-3.5'][] = $model;
            } else {
                $groups['Other'][] = $model; // Catch-all for unexpected future models
            }
        }
        // Clean empty groups
        $groups = array_filter($groups);

        // Sort each group by ID (descending for newer models maybe?)
        foreach ($groups as &$items) {
            usort($items, fn($a, $b) => strcmp($b['id'], $a['id'])); // Sort descending by ID
        }
        unset($items);

        // Ensure specific order of groups
        $ordered_groups = [];
        $order = ['GPT-4o', 'GPT-4 Turbo', 'GPT-4', 'GPT-3.5', 'Fine-tuned', 'Other'];
        foreach($order as $key) {
            if (isset($groups[$key])) {
                $ordered_groups[$key] = $groups[$key];
            }
        }

        return $ordered_groups;
    }

}