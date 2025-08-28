<?php
/**
 * Plugin Name: ZG - Variation Tiles
 * Plugin URI: https://zgrills.com.au
 * Description: Display product variations as interactive tiles with savings calculations and included items accordion
 * Version: 1.0.6
 * Author: Z Grills
 * Author URI: https://zgrills.com.au
 * Text Domain: zg-variation-tiles
 * Domain Path: languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 * WC HPOS Compatible: Yes
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'PROTILES_VERSION', '1.0.6' );
define( 'PROTILES_URL', plugin_dir_url( __FILE__ ) );
define( 'PROTILES_DIR', plugin_dir_path( __FILE__ ) );

if( !defined( 'PROTILES_ADMIN' ) ) {
	define( 'PROTILES_ADMIN', PROTILES_DIR . '/includes/admin' );
}

// Load main plugin class
require_once( PROTILES_DIR . '/includes/class-product-tiles-plugin-loaded.php' );
Product_Tiles_Plugin_loaded::instance();

// Register frontend scripts
add_action( 'elementor/frontend/after_register_scripts', 'register_frontend_scripts', 5 );
function register_frontend_scripts() {
    wp_register_script( 'pro-tiles-general', PROTILES_URL.'assets/js/general.js', array( 'jquery', 'elementor-frontend' ), PROTILES_VERSION, true );
}

// Clear caches on plugin update
add_action('init', function() {
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('woo_variation_swatches');
    }
    delete_transient('protiles_variation_cache');
    delete_transient('protiles_settings_cache');
});

// WooCommerce HPOS Compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
