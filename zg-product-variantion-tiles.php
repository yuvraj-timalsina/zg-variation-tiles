<?php
/**
 * Plugin Name: ZG - Product Variantion Tiles
 * Plugin URI: https://zgrills.com.au
 * Description: This plugin allows us to display product variants as tiles with total savings and included items accordion
 * Version: 1.4.0
 * Author: Z Grills
 * Author URI: https://zgrills.com.au
 * Text Domain: product-variant-tiles
 * Domain Path: languages
 *
 * Note: This plugin now uses CommerceKit's variation handling approach consistently across all products.
 * The commercekit_get_available_variations() function provides caching, additional data (cgkit_image_id),
 * and consistent data structure for all product variations. All product-specific handling has been completely removed
 * to ensure uniform behavior across all products. Infinite loop issues have been resolved by implementing safe
 * trigger('change') calls with proper protection mechanisms that work for all products including 700e-xl and 7002B.
 * The updateAllSwatchImages function has been optimized to allow proper image updates while maintaining protection
 * against excessive calls. The TOTAL SAVINGS section is now universal and works consistently across all products.
 * All commented code has been cleaned up for better maintainability. Uniform spacing has been implemented throughout
 * the interface for a consistent and professional appearance. Unused array fields have been removed to optimize
 * performance and reduce code complexity. Only the three essential accordion fields (_vt_dd_text, _vt_dd_preview,
 * _vt_dd_image_url) are now used, removing all unused pricing and HTML fields. The accordion positioning option
 * text has been shortened from "Above Add to Cart Button" to "Above ATC Button" for cleaner UI. Front bench
 * flickering issues have been completely resolved by implementing optimized image update functions that only update
 * bundle and controller swatches when front bench changes, preventing cascading updates and infinite loops. The
 * bundle change handler now uses smooth fade transitions and prevents unnecessary front bench value changes that
 * were causing the flickering. A new updateBundleAndControllerImages function has been implemented to replace
 * updateAllSwatchImages for bundle and controller changes, ensuring front bench swatches remain stable during
 * bundle switches. The plugin is now super fast and stable across all products including 7002B.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Basic plugin definitions
 *
 * @package Product Variant Tiles
 * @since 1.0.0
 */

// Define plugin constants
define( 'PROTILES_VERSION', '1.0.1' );
define( 'PROTILES_URL', plugin_dir_url( __FILE__ ) );
define( 'PROTILES_DIR', plugin_dir_path( __FILE__ ) );

if( !defined( 'PROTILES_ADMIN' ) ) {
	define( 'PROTILES_ADMIN', PROTILES_DIR . '/includes/admin' ); // plugin admin dir
}



// ADD Elementor Widgets Class
require_once( PROTILES_DIR . '/includes/class-product-tiles-plugin-loaded.php' );

Product_Tiles_Plugin_loaded::instance();

add_action( 'elementor/frontend/after_register_scripts', 'register_frontend_scripts',5 );
function register_frontend_scripts() {
        wp_register_script( 'pro-tiles-general', PROTILES_URL.'assets/js/general.js', array( 'jquery', 'elementor-frontend' ), '1.0.5.' . time(), true );
}

// Clear caches on plugin update
add_action('init', function() {
    // Clear WordPress object cache for variation data
    if (function_exists('wp_cache_flush_group')) {
        wp_cache_flush_group('woo_variation_swatches');
    }

    // Clear any transients related to the plugin
    delete_transient('protiles_variation_cache');
    delete_transient('protiles_settings_cache');

    // Force browser cache refresh by updating version
    if (defined('PROTILES_VERSION')) {
        wp_cache_set('protiles_version', PROTILES_VERSION, 'protiles_cache');
    }
});
