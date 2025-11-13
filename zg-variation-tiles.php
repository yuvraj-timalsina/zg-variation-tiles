<?php
/**
 * Plugin Name: ZG - Variation Tiles
 * Plugin URI: https://zgrills.com.au
 * Description: Display product variations as interactive tiles with savings calculations and included items accordion
 * Version: 1.1.0
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
define( 'PROTILES_VERSION', '1.1.0' );
define( 'PROTILES_URL', plugin_dir_url( __FILE__ ) );
define( 'PROTILES_DIR', plugin_dir_path( __FILE__ ) );

if( !defined( 'PROTILES_ADMIN' ) ) {
	define( 'PROTILES_ADMIN', PROTILES_DIR . '/includes/admin' );
}

// Load main plugin class
require_once( PROTILES_DIR . '/includes/class-product-tiles-plugin-loaded.php' );
Product_Tiles_Plugin_loaded::instance();

// Load variation price updates
require_once( PROTILES_DIR . '/includes/class-variation-price.php' );

// Register frontend scripts
add_action( 'elementor/frontend/after_register_scripts', 'register_frontend_scripts', 5 );
function register_frontend_scripts() {
    wp_register_script( 'pro-tiles-general', PROTILES_URL.'assets/js/general.js', array( 'jquery', 'elementor-frontend' ), PROTILES_VERSION, true );
    wp_register_script( 'variation-price', PROTILES_URL.'assets/js/variation-price.js', array( 'jquery', 'wc-add-to-cart-variation' ), PROTILES_VERSION, true );
}

// Clear caches on plugin update
add_action('init', function() {
    // Only clear cache once per session to avoid performance issues
    if (!get_transient('protiles_cache_cleared_' . PROTILES_VERSION)) {
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('woo_variation_swatches');
        }
        delete_transient('protiles_variation_cache');
        delete_transient('protiles_settings_cache');

        // Clear additional caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }

        // Clear object cache if available
        if (function_exists('wp_cache_delete_group')) {
            wp_cache_delete_group('elementor');
            wp_cache_delete_group('woocommerce');
        }

        // Clear transients
        delete_transient('elementor_css_' . get_stylesheet());
        delete_transient('elementor_css_' . get_template());

        // Clear WooCommerce variation caches
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_var_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wc_av_%'");

        // Set flag to prevent repeated clearing
        set_transient('protiles_cache_cleared_' . PROTILES_VERSION, true, HOUR_IN_SECONDS);
    }
});

// Force CSS refresh on plugin load
add_action('wp_enqueue_scripts', function() {
    // Only run on product pages and admin
    if (is_product() || is_admin()) {
        // Force refresh CSS files
        wp_deregister_style('pro-tiles-elementor');
        wp_deregister_style('zg-savings-accordion');
    }
}, 1);

// Add admin action to clear all caches
add_action('admin_post_clear_protiles_cache', function() {
    // Clear all possible caches
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }

    // Clear transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_protiles_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_elementor_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_woocommerce_%'");

    // Clear object cache
    if (function_exists('wp_cache_delete_group')) {
        wp_cache_delete_group('elementor');
        wp_cache_delete_group('woocommerce');
        wp_cache_delete_group('woo_variation_swatches');
    }

    wp_redirect(admin_url('admin.php?page=elementor&protiles_cache_cleared=1'));
    exit;
});

// Add a simple test to verify CSS is loaded
add_action('wp_footer', function() {
    if (is_product()) {
        echo '<!-- ZG Variation Tiles CSS Version: ' . PROTILES_VERSION . ' -->';
    }
});

// WooCommerce HPOS Compatibility
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});
