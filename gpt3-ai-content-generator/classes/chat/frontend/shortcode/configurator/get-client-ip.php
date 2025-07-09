<?php
// File: /Applications/MAMP/htdocs/wordpress/wp-content/plugins/gpt3-ai-content-generator/classes/chat/frontend/shortcode/configurator/get-client-ip.php
// Status: NEW FILE

namespace WPAICG\Chat\Frontend\Shortcode\ConfiguratorMethods;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Gets the client's IP address, considering potential proxies.
 * This logic was previously in Configurator::get_client_ip().
 *
 * @return string The client's IP address.
 */
function get_client_ip_logic(): string {
    $ip_keys = [
        'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'
    ];
    foreach ($ip_keys as $key) {
        if (isset($_SERVER[$key])) {
            // HTTP_X_FORWARDED_FOR can contain a comma-separated list of IPs.
            // The client's IP is usually the last one in the list.
            $ip_list = explode(',', $_SERVER[$key]);
            $potential_ip = trim(end($ip_list)); // Get the last IP and trim whitespace
            if (filter_var($potential_ip, FILTER_VALIDATE_IP)) {
                return $potential_ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; // Fallback
}