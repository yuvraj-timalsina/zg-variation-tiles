<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Elementor Class
 *
 * Handles generic Admin functionality.
 * 
 * @package PROTILES
 * @since 1.0.0
 */
class Product_Variant_Tiles_Widget {
protected static $instance = null;

	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	protected function __construct() {
		require_once('class-product-tiles-widget.php');
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
	}

	public function register_widgets() {
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\Product_Variant_Tiles() );
	
	}
}

