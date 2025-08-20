<?php
/**
 * Plugin Name: ZG - Product Variant Tile
 * Plugin URI:
 * Description: This plugin allows us to display product variants as tiles with total savings and included items accordion
 * Version: 1.2.0
 * Author:
 * Author URI:
 * Text Domain: product-tiles
 * Domain Path: languages
 *
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
define( 'PROTILES_VERSION', '1.0.0' );
define( 'PROTILES_URL', plugin_dir_url( __FILE__ ) );
define( 'PROTILES_DIR', plugin_dir_path( __FILE__ ) );

if( !defined( 'PROTILES_ADMIN' ) ) {
	define( 'PROTILES_ADMIN', PROTILES_DIR . '/includes/admin' ); // plugin admin dir
}

// require_once( PROTILES_DIR . '/includes/class-woo-variation-swatches.php' );

// ADD Elementor Widgets Class
require_once( PROTILES_DIR . '/includes/class-product-tiles-plugin-loaded.php' );

Product_Tiles_Plugin_loaded::instance();

add_action( 'elementor/frontend/after_register_scripts', 'register_frontend_scripts',5 );
function register_frontend_scripts() {


        wp_register_script( 'pro-tiles-general', PROTILES_URL.'assets/js/general.js', array( 'jquery', 'elementor-frontend' ), '1.0.3.' . time(), true );
}
