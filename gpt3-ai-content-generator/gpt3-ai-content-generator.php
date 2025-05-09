<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://aipower.org
 * @since             1.0.0
 * @package           Wp_Ai_Content_Generator
 *
 * @wordpress-plugin
 * Plugin Name:       AI Power: Complete AI Pack
 * Description:       ChatGPT, Content Writer, Auto Content Writer, ChatBot, Product Writer, Image Generator, AutoGPT, ChatPDF, AI Training, Embeddings and more.
 * Version:           1.9.18
 * Author:            Senol Sahin
 * Author URI:        https://aipower.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gpt3-ai-content-generator
 * Domain Path:       /languages
 */
if ( !defined( 'WPINC' ) ) {
    die;
}
define( 'WP_AI_CONTENT_GENERATOR_VERSION', '1.9.18' );
if ( !class_exists( '\\WPAICG\\WPAICG_OpenAI' ) ) {
    require_once __DIR__ . '/includes/class-wp-ai-openai.php';
}
if ( !class_exists( '\\WPAICG\\WPAICG_AzureAI' ) ) {
    require_once __DIR__ . '/includes/class-wp-ai-azure.php';
}
if ( !class_exists( '\\WPAICG\\WPAICG_Google' ) ) {
    require_once __DIR__ . '/includes/class-wp-ai-google.php';
}
if ( !class_exists( '\\WPAICG\\WPAICG_OpenRouter' ) ) {
    require_once __DIR__ . '/includes/class-wp-ai-openrouter.php';
}
if ( function_exists( 'wpaicg_gacg_fs' ) ) {
    wpaicg_gacg_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    if ( !function_exists( 'wpaicg_gacg_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wpaicg_gacg_fs() {
            global $wpaicg_gacg_fs;
            if ( !isset( $wpaicg_gacg_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_11606_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_11606_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wpaicg_gacg_fs = fs_dynamic_init( array(
                    'id'             => '11606',
                    'slug'           => 'gpt3-ai-content-generator',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_374fe2f12f24f09286bc6f89cd0c6',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug'       => 'wpaicg',
                        'first-path' => 'admin.php?page=wpaicg',
                        'support'    => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $wpaicg_gacg_fs;
        }

        // Init Freemius.
        wpaicg_gacg_fs();
        // Signal that SDK was initiated.
        do_action( 'wpaicg_gacg_fs_loaded' );
    }
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-wp-ai-content-generator-activator.php
     */
    function activate_wp_ai_content_generator() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-ai-content-generator-activator.php';
        Wp_Ai_Content_Generator_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-wp-ai-content-generator-deactivator.php
     */
    function deactivate_wp_ai_content_generator() {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp-ai-content-generator-deactivator.php';
        Wp_Ai_Content_Generator_Deactivator::deactivate();
    }

    function uninstall_wp_ai_content_generator() {
        global $wpdb;
        $wpaicg_all_plugins = get_plugins();
        $wpaicgPlugins = 0;
        foreach ( $wpaicg_all_plugins as $key => $wpaicg_all_plugin ) {
            if ( strpos( $key, 'gpt3-ai-content-generator' ) !== false ) {
                $wpaicgPlugins++;
            }
        }
        if ( $wpaicgPlugins == 1 ) {
            $wpaicgTable = $wpdb->prefix . 'wpaicg';
            $wpdb->query( "TRUNCATE TABLE {$wpaicgTable}" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpaicgTable}" );
        }
    }

    register_activation_hook( __FILE__, 'activate_wp_ai_content_generator' );
    register_deactivation_hook( __FILE__, 'deactivate_wp_ai_content_generator' );
    register_uninstall_hook( __FILE__, 'uninstall_wp_ai_content_generator' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/class-wp-ai-content-generator.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_wp_ai_content_generator() {
        $plugin = new Wp_Ai_Content_Generator();
        $plugin->run();
    }

    run_wp_ai_content_generator();
}
require_once __DIR__ . '/gpt3-ai-content-extra.php';