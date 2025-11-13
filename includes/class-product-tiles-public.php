<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Public Class - Handles frontend functionality for variation tiles
 *
 * @package Product Variant Tiles
 * @since 1.0.0
 */

class Product_Tiles_Public
{

	function __construct()
	{
		// Add hooks for savings display and accordion
		add_action('woocommerce_before_add_to_cart_button', array($this, 'display_total_savings_and_accordion'), 20);
		add_action('wp_enqueue_scripts', array($this, 'enqueue_savings_accordion_assets'));
	}

	/**
	 * Set selected attributes for product variant tiles
	 */
	function set_selected_attribute_args($args = array())
	{

		$product   = $args['product'];
		$attributes = !empty($product->get_default_attributes()) ? $product->get_default_attributes() : [];

		// Check if there's a default for the specific attribute being processed
		$current_attribute = $args['attribute'];
		if (isset($attributes[$current_attribute])) {
			$args['selected'] = $attributes[$current_attribute];
		} else {
			// Fallback to first default if no specific default for this attribute
			$default = array_values($attributes);
			$args['selected'] = !empty($default) ? $default[0] : '';
		}

		return $args;
	}

	public function disable_variable_price_range($price, $product)
	{
		$variable_pricing = $product->get_meta('variable_pricing', true);
		if ($variable_pricing == 'yes') {
			$min_var_reg_price = $product->get_variation_regular_price('min', true);
			$min_var_sale_price = $product->get_variation_sale_price('min', true);
			$max_var_reg_price  = $product->get_variation_regular_price('max', true);
			$price = ($product->is_on_sale()) ? sprintf('<ins>%1$s</ins>', wc_price($min_var_sale_price)) : sprintf('%1$s ',  wc_price($min_var_reg_price));

			return $price;
		}
		return $price;
	}

	public function get_image_variation_id($variation_id)
	{
		$variation = new WC_Product_Variation($variation_id);
		return $variation->get_image_id();
	}

	function enqueue_styles()
	{

	}

	function remove_zero_decimals($formatted_price, $price, $decimal_places, $decimal_separator, $thousand_separator)
	{

		if ($price - intval($price) == 0) {
			// Format units, including thousands separator if necessary.
			return $unit = number_format(intval($price), 0, $decimal_separator, $thousand_separator);
		} else {
			return $formatted_price;
		}
	}

	/**
	 * Get cross-sell products for variants
	 */
	function variant_get_cross_products()
	{
		// TODO: Add proper nonce verification when frontend is updated
		// if (!wp_verify_nonce($_POST['nonce'] ?? '', 'zg_variation_tiles_nonce')) {
		// 	wp_die('Security check failed');
		// }

		// Sanitize and validate input data
		$variation_id = absint($_POST['variation_id'] ?? 0);
		if (!$variation_id) {
			echo '';
			die;
		}

		$variation = wc_get_product($variation_id);
		if (!$variation) {
			echo '';
			die;
		}

		$cross_cell = $variation->get_meta('zg_variation_cross_sell_id', true);
		$settings = wp_parse_args($_POST['settings'] ?? array(), array());

		$icon = '';
		if (!empty($settings['icon']) || !empty($settings['cross_cell_product_price_icon']['value'])) {
			$icon_value = $settings['cross_cell_product_price_icon']['value'] ?? $settings['icon'] ?? '';
			$icon = '<span class="elementor-align-icon-left"><i class="' . esc_attr($icon_value) . '" aria-hidden="true"></i></span>';
		}

		$data = '';
		if (!empty($cross_cell) && is_array($cross_cell)) {
			$data = '<ul class="crosscell_products">';
			foreach ($cross_cell as $product_id) {
				$product_id = absint($product_id);
				if (!$product_id) continue;

				$_product = wc_get_product($product_id);
				if (!$_product) continue;

				$data .= '<li class="crosscell_items" data-product_id="' . esc_attr($_product->get_id()) . '" data-price="' . esc_attr($_product->get_price()) . '">';
				$data .= '<div class="cross_img" style="background:url(' . esc_url(wp_get_attachment_url($_product->get_image_id())) . ')"></div>';
				$data .= '<div class="cross_content"><h4>' . esc_html($_product->get_title()) . '</h4>';
				$data .= '<p class="cross_price">' . $icon . $_product->get_price_html() . '</p>';
				$data .= '</div></li>';
			}
			$data .= '</ul>';
		}

		echo $data;
		wp_reset_postdata();
		die;
	}

	/**
	 * Cross Cell Product Variant Tiles
	 *
	 * Handles generic Frontend functionality.
	 *
	 * @package Product Variant Tiles
	 * @since 1.0.1
	 */
	function woocommerce_ajax_add_to_cart()
	{
		// TODO: Add proper nonce verification when frontend is updated
		// if (!wp_verify_nonce($_POST['nonce'] ?? '', 'zg_variation_tiles_nonce')) {
		// 	wp_die('Security check failed');
		// }

		// Sanitize and validate input data
		$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id'] ?? 0));
		$quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
		$variation_id = absint($_POST['variation_id'] ?? 0);
		$other_products = array_map('absint', $_POST['other_products'] ?? array());

		if (!$product_id) {
			echo wp_send_json(array('error' => 'Invalid product ID'));
			die;
		}

		$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
		$product = wc_get_product($product_id);
		$product_status = $product ? $product->get_status() : '';

		if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity, $variation_id) && 'publish' === $product_status) {
			do_action('woocommerce_ajax_added_to_cart', $product_id);

			if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
				wc_add_to_cart_message(array($product_id => $quantity), true);
			}
		} else {
			$data = array(
				'error' => true,
				'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
			);
			echo wp_send_json($data);
		}

		// Handle additional products
		if (!empty($other_products)) {
			foreach ($other_products as $oproduct_id) {
				$oproduct_id = apply_filters('woocommerce_add_to_cart_product_id', absint($oproduct_id));
				if (!$oproduct_id) continue;

				$oproduct = wc_get_product($oproduct_id);
				$p_status = $oproduct ? $oproduct->get_status() : '';
				$p_validation = apply_filters('woocommerce_add_to_cart_validation', true, $oproduct_id, 1);

						if ($p_validation && WC()->cart->add_to_cart(absint($oproduct_id), 1) !== false) {
							if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
								wc_add_to_cart_message(array($oproduct_id => 1), true);
							}
						} else {
							$data = array(
								'error' => true,
								'product_url' => $oproduct_id
							);
							echo wp_send_json($data);
						}
			}
		}

		WC_AJAX::get_refreshed_fragments();
		wp_die();
	}
	/**
	 * Cross Cell Product Variant Tiles
	 *
	 * Handles generic Frontend functionality.
	 *
	 * @package Product Variant Tiles
	 * @since 1.0.1
	 */
	function woocommerce_ajax_check_add_to_cart()
	{
		// TODO: Add proper nonce verification when frontend is updated
		// if (!wp_verify_nonce($_POST['nonce'] ?? '', 'zg_variation_tiles_nonce')) {
		// 	wp_die('Security check failed');
		// }

		// Sanitize and validate input data
		$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id'] ?? 0));
		$variation_id = absint($_POST['variation_id'] ?? 0);
		$oproduct_id = absint($_POST['other_products'] ?? 0);
		$mode = sanitize_text_field($_POST['mode'] ?? '');

		if (!$product_id) {
			echo wp_send_json(array('error' => 'Invalid product ID'));
			die;
		}

		if ($mode === 'delete') {
			if (!WC()->cart->is_empty()) {
				foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
					if ($cart_item['product_id'] == $oproduct_id) {
						if ((float)$cart_item['quantity'] > 1) {
							$qty = (float)$cart_item['quantity'] - 1;
							WC()->cart->set_quantity($cart_item['key'], $qty);
						} else {
							WC()->cart->remove_cart_item($cart_item['key']);
						}
						WC_AJAX::get_refreshed_fragments();
					}
				}
			}
		} else {
			if (!WC()->cart->is_empty()) {
				foreach (WC()->cart->get_cart() as $cart_item) {
					if ($cart_item['product_id'] === $product_id && $cart_item['variation_id'] == $variation_id) {
						$oproduct_id = apply_filters('woocommerce_add_to_cart_product_id', absint($oproduct_id));
						if (!$oproduct_id) {
							echo wp_send_json(array('error' => 'Invalid other product ID'));
							die;
						}

						$oproduct = wc_get_product($oproduct_id);
						$p_status = $oproduct ? $oproduct->get_status() : '';
						$p_validation = apply_filters('woocommerce_add_to_cart_validation', true, $oproduct_id, 1);

						if ($p_validation && WC()->cart->add_to_cart(absint($oproduct_id), 1) !== false) {
							if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
								wc_add_to_cart_message(array($oproduct_id => 1), true);
							}
							WC_AJAX::get_refreshed_fragments();
						} else {
							$data = array(
								'error' => true,
								'product_url' => $oproduct_id
							);
							echo wp_send_json($data);
						}
					} else {
						echo 'fail';
					}
				}
			} else {
				echo 'fail';
			}
		}
		wp_die();
	}

	function variable_product_total_amount_qty_change($data, $product, $variation)
	{

		$data['price_html'] .= '<input type="hidden" name="total_price" value="' . $variation->get_price() . '" />'; ?>

		<?php

		return $data;
	}

	public function dropdown($html, $args)
	{
		if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->documents->get_current()) {
			$document = \Elementor\Plugin::$instance->documents->get_current();

			// Check if the current post uses an Elementor template
			if ($document && $document->is_built_with_elementor()) {
				$content = $document->get_elements_raw_data();
				if (!$this->has_custom_widget($content)) {
					return $html;
				}
			}
		}else{
			return $html;
		}

		// Check if VT tiles are enabled for this product
		global $product;

		// Check if product exists and is valid
		if (!$product || !is_object($product) || !method_exists($product, 'get_id')) {
			return $html;
		}



		$is_continue = false;

		if(apply_filters('variant_tiles_dropdown_not_continue', $is_continue)){
			return apply_filters( 'woo_variation_swatches_html', $html, $args, [], $this );
		}

		$args = wp_parse_args( apply_filters( 'woocommerce_dropdown_variation_attribute_options_args', $args ), array(
			'options'          => false,
			'attribute'        => false,
			'product'          => false,
			'selected'         => false,
			'name'             => '',
			'id'               => '',
			'class'            => '',
			'show_option_none' => esc_html__( 'Choose an option', 'woo-variation-swatches' ),
			'is_archive'       => false
		) );

		if ( apply_filters( 'default_woo_variation_swatches_single_product_dropdown_html', false, $args, $html, $this ) ) {
			return $html;
		}

		// Get selected value.
		if ( empty( $args[ 'selected' ] ) && $args[ 'attribute' ] && $args[ 'product' ] instanceof WC_Product ) {
			$selected_key = wc_variation_attribute_name( $args[ 'attribute' ] );
			$args[ 'selected' ] = isset( $_REQUEST[ $selected_key ] ) ? sanitize_name( $_REQUEST[ $selected_key ] ) : $args[ 'product' ]->get_variation_default_attribute( $args[ 'attribute' ] );
		}

		// Process the HTML to remove CommerceKit disabled class from grill-only
		if ( strpos( $args['attribute'], 'bundles' ) !== false ) {
			$html = preg_replace(
				'/(<button[^>]*data-attribute-value="grill-only"[^>]*class="[^"]*)\bcgkit-disabled\b([^"]*")/i',
				'$1$2',
				$html
			);
		}

			return $html;
	}

	public function image_attribute( $data, $attribute_type, $variation_data = array() ) {

		if ( 'image' === $attribute_type ) {

			$option_name = $data[ 'option_name' ];

			// Global
			$image = $this->get_image_attribute( $data, $attribute_type, $variation_data );

			$template_format = apply_filters( 'woo_variation_swatches_image_attribute_template', '<img class="variable-item-image" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" />', $data, $attribute_type, $variation_data );

			return sprintf( $template_format, esc_attr( $option_name ), esc_url( $image[ 0 ] ), esc_attr( $image[ 1 ] ), esc_attr( $image[ 2 ] ) );
		}
	}

	public function button_attribute( $data, $attribute_type, $variation_data = array() ) {

		if ( 'button' === $attribute_type ) {
			$option_name = $data[ 'option_name' ];

			$template_format = apply_filters( 'woo_variation_swatches_button_attribute_template', '<span class="variable-item-span variable-item-span-button">%s</span>', $data, $attribute_type, $variation_data );

			return sprintf( $template_format, esc_html( $option_name ) );
		}
	}

	public function radio_attribute( $data, $attribute_type, $variation_data = array() ) {

		if ( 'radio' === $attribute_type ) {

			$attribute_name = $data[ 'attribute_name' ];
			$product        = $data[ 'product' ];
			$product_id     = absint( $product->get_id() );
			$attributes     = $product->get_variation_attributes();
			// $attributes  = $this->get_cached_variation_attributes( $product );
			$slug        = $data[ 'slug' ];
			$is_selected = wc_string_to_bool( $data[ 'is_selected' ] );
			$option_name = $data[ 'option_name' ];

			/*
			 * $get_variations       = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
			 * $available_variations = $get_variations ? $product->get_available_variations() : false;
			*/

			$name            = sprintf( 'wvs_radio_%s__%d', $attribute_name, $product_id );
			$attribute_value = $slug;

			$label          = esc_html( $option_name );
			$label_template = apply_filters( 'woo_variation_swatches_global_item_radio_label_template', '%image% - %variation% - %price% %stock%', $data );

			if ( count( array_keys( $attributes ) ) === 1 ) {

				// $available_variations = $product->get_available_variations();
				$available_variations = $this->get_available_variation_images( $product );

				$variation = $this->get_variation_by_attribute_name_value( $available_variations, $attribute_name, $attribute_value );

				if ( ! empty( $variation ) ) {

					$image_id = $variation[ 'variation_image_id' ];

					$image_size = apply_filters( 'woo_variation_swatches_global_product_attribute_image_size', sanitize_text_field( get_option( 'attribute_image_size', 'variation_swatches_image_size' ) ), $data );

					// $image_size = sanitize_text_field( get_option( 'attribute_image_size', 'variation_swatches_image_size' ) );

					$variation_image = $this->get_variation_img_src( $image_id, $image_size );

					$image = sprintf( '<img src="%1$s" title="%2$s" alt="%2$s" width="%3$s" height="%4$s" />', esc_url( $variation_image[ 'src' ] ), $label, absint( $variation_image[ 'width' ] ), absint( $variation_image[ 'height' ] ) );
					$stock = wp_kses_post( $variation[ 'availability_html' ] );
					$price = wp_kses_post( $variation[ 'price_html' ] );
					$label = str_ireplace( array( '%image%', '%variation%', '%price%', '%stock%' ), array(
						$image,
						'<span class="variable-item-radio-value">' . esc_html( $option_name ) . '</span>',
						$price,
						$stock
					),                     $label_template );
				}
			}

			$template_format = apply_filters( 'woo_variation_swatches_radio_attribute_template', '<label class="variable-item-radio-input-wrapper"><input name="%1$s" class="variable-item-radio-input" %2$s type="radio" value="%3$s" data-value="%3$s" /><span class="variable-item-radio-value-wrapper">%4$s</span></label>', $data, $attribute_type, $variation_data );

			return sprintf( $template_format, $name, checked( $is_selected, true, false ), esc_attr( $slug ), $label );
		}
	}

	public function get_variation_img_src( $image_id, $image_size ) {
		$image = array(
			'src'     => zg_images_url( '/placeholder.png' ),
			'width'   => 50,
			'height'  => 50,
			'resized' => 0,
		);

		$image_data = wp_get_attachment_image_src( absint( $image_id ), $image_size );

		if ( $image_data ) {
			$image = array(
				'src'     => $image_data[ 0 ],
				'width'   => $image_data[ 1 ],
				'height'  => $image_data[ 2 ],
				'resized' => $image_data[ 3 ],
			);
		}

		return $image;

	}

	public function get_image_attribute( $data, $attribute_type, $variation_data = array() ) {
		if ( 'image' === $attribute_type ) {

			$term = $data[ 'item' ];

			// Global
			$attachment_id = apply_filters( 'woo_variation_swatches_global_product_attribute_image_id', absint( $this->get_product_attribute_image( $term, $data ) ), $data );
			$image_size    = apply_filters( 'woo_variation_swatches_global_product_attribute_image_size', sanitize_text_field( get_option( 'attribute_image_size', 'variation_swatches_image_size' ) ), $data );

			if ( empty( $attachment_id ) && $data[ 'total_attributes' ] === 1 && $data[ 'variation_image_id' ] > 0 ) {
				$attachment_id = $data[ 'variation_image_id' ];
			}

			return wp_get_attachment_image_src( $attachment_id, $image_size );
		}
	}

	public function get_product_attribute_image( $term, $data = array() ) {

		$term_id = 0;
		if ( is_numeric( $term ) ) {
			$term_id = $term;
		}

		if ( is_object( $term ) ) {
			$term_id = $term->term_id;
		}

		return get_term_meta( $term_id, 'product_attribute_image', true );
	}

	public function item_start( $data, $attribute_type, $variation_data = array() ) {

		$args           = $data[ 'args' ];
		$term_or_option = $data[ 'item' ];

		$options     = $args[ 'options' ];
		$product     = $args[ 'product' ];
		$attribute   = $args[ 'attribute' ];
		$is_selected = $data[ 'is_selected' ];
		$option_name = $data[ 'option_name' ];
		$option_slug = $data[ 'option_slug' ];
		$slug        = $data[ 'slug' ];

		$is_term = wc_string_to_bool( $data[ 'is_term' ] );

		$css_class = implode( ' ', array_unique( array_values( apply_filters( 'woo_variation_swatches_variable_item_css_class', $this->get_item_css_classes( $data, $attribute_type, $variation_data ), $data, $attribute_type, $variation_data ) ) ) );

		$html_attributes = array(
			'aria-checked' => ( $is_selected ? 'true' : 'false' ),
			'tabindex'     => ( wp_is_mobile() ? '2' : '0' ),
		);

		$html_attributes = wp_parse_args( $this->get_item_tooltip_attribute( $data, $attribute_type, $variation_data ), $html_attributes );

		$html_attributes = apply_filters( 'woo_variation_swatches_variable_item_custom_attributes', $html_attributes, $data, $attribute_type, $variation_data );

		return sprintf( '<li %1$s class="variable-item %2$s-variable-item %2$s-variable-item-%3$s %4$s" title="%5$s" data-title="%5$s" data-value="%6$s" role="radio" tabindex="0"><div class="variable-item-contents">', wc_implode_html_attributes( $html_attributes ), esc_attr( $attribute_type ), esc_attr( $option_slug ), esc_attr( $css_class ), esc_html( $option_name ), esc_attr( $slug ) );
	}

	public function item_end() {
		$html = '';
		if ( wc_string_to_bool( get_option( 'show_variation_stock_info', 'no' ) ) ) {
			$html .= '<div class="wvs-stock-left-info" data-wvs-stock-info=""></div>';
		}
		$html .= '</div></li>';

		return $html;
	}

	public function wrapper_end() {
		return '</ul>';
	}

	public function get_item_tooltip_attribute( $data, $attribute_type, $variation_data = array() ) {

		$html_attributes = array();

		$option_name = $data[ 'option_name' ];

		$enable_tooltip = wc_string_to_bool( get_option( 'enable_tooltip', 'yes' ) );

		if ( $enable_tooltip ) {
			$tooltip = trim( apply_filters( 'woo_variation_swatches_global_variable_item_tooltip_text', $option_name, $data ) );

			$html_attributes[ 'data-wvstooltip' ] = esc_attr( $tooltip );
		}

		return $html_attributes;
	}

	public function color_attribute( $data, $attribute_type, $variation_data = array() ) {
		// Color
		if ( 'color' === $attribute_type ) {

			$term = $data[ 'item' ];

			// Global Color
			$color = sanitize_hex_color( $this->get_product_attribute_color( $term, $data ) );

			$template_format = apply_filters( 'woo_variation_swatches_color_attribute_template', '<span class="variable-item-span variable-item-span-color" style="background-color:%s;"></span>', $data, $attribute_type, $variation_data );

			return sprintf( $template_format, esc_attr( $color ) );
		}
	}

	public function get_product_attribute_color( $term, $data = array() ) {

		$term_id = 0;
		if ( is_numeric( $term ) ) {
			$term_id = $term;
		}

		if ( is_object( $term ) ) {
			$term_id = $term->term_id;
		}

		return get_term_meta( $term_id, 'product_attribute_color', true );
	}

	public function get_item_css_classes( $data, $attribute_type, $variation_data = array() ) {

		$css_classes = array();

		$is_selected = wc_string_to_bool( $data[ 'is_selected' ] );

		if ( $is_selected ) {
			$css_classes[] = 'selected';
		}

		return $css_classes;
	}

	public function wrapper_class( $args, $attribute, $product, $attribute_type ) {

		$classes = array();

		$shape     = sprintf( 'wvs-style-%s', get_option( 'shape_style', 'squared' ) );
		$classes[] = 'variable-items-wrapper';
		$classes[] = sprintf( '%s-variable-items-wrapper', $attribute_type );
		$classes[] = sanitize_text_field( $shape );

		return $classes;
	}

	public function wrapper_start( $args, $attribute, $product, $attribute_type, $options ) {

		$html_attributes = $this->wrapper_html_attribute( $args, $attribute, $product, $attribute_type, $options );

		// return sprintf( '<ul role="radiogroup" aria-label="%1$s" class="%2$s" data-attribute_name="%3$s" data-attribute_values="%4$s">', esc_attr( wc_attribute_label( $attribute, $product ) ), implode( ' ', array_unique( array_values( $classes ) ) ), esc_attr( wc_variation_attribute_name( $attribute ) ), wc_esc_json( wp_json_encode( array_values( $options ) ) ) );
		return sprintf( '<ul %s>', wc_implode_html_attributes( $html_attributes ) );
	}

	public function wrapper_html_attribute( $args, $attribute, $product, $attribute_type, $options ) {

		$raw_html_attributes = array();
		$css_classes         = $this->wrapper_class( $args, $attribute, $product, $attribute_type );

		$raw_html_attributes[ 'role' ]                  = 'radiogroup';
		$raw_html_attributes[ 'aria-label' ]            = wc_attribute_label( $attribute, $product );
		$raw_html_attributes[ 'class' ]                 = implode( ' ', array_unique( array_values( $css_classes ) ) );
		$raw_html_attributes[ 'data-attribute_name' ]   = wc_variation_attribute_name( $attribute );
		$raw_html_attributes[ 'data-attribute_values' ] = wc_esc_json( wp_json_encode( array_values( $options ) ) );

		return $raw_html_attributes;
	}

	public function get_swatch_data( $args, $term_or_option ) {

		$options          = $args[ 'options' ];
		$product          = $args[ 'product' ];
		$attribute        = $args[ 'attribute' ];
		$attributes       = $product->get_variation_attributes();
		$count_attributes = count( array_keys( $attributes ) );

		$is_term = is_object( $term_or_option );

		if ( $is_term ) {

			$term        = $term_or_option;
			$slug        = $term->slug;
			$is_selected = ( sanitize_title( $args[ 'selected' ] ) === $term->slug );
			$option_name = apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute, $product );

		} else {
			$option      = $slug = $term_or_option;
			$is_selected = ( sanitize_title( $args[ 'selected' ] ) === $args[ 'selected' ] ) ? ( $args[ 'selected' ] === sanitize_title( $option ) ) : ( $args[ 'selected' ] === $option );
			$option_name = apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute, $product );
		}

		$attribute_name  = wc_variation_attribute_name( $attribute );
		$attribute_value = $slug;

		$single_attribute_variation_image_id = 0;
		if ( count( array_keys( $attributes ) ) === 1 ) {
			$available_variations = $this->get_available_variation_images( $product );

			$variation = $this->get_variation_by_attribute_name_value( $available_variations, $attribute_name, $attribute_value );

			$single_attribute_variation_image_id = empty( $variation ) ? 0 : $variation[ 'variation_image_id' ];
		}

		$data = array(
			'is_archive'         => isset( $args[ 'is_archive' ] ) ? $args[ 'is_archive' ] : false,
			'is_selected'        => $is_selected,
			'is_term'            => $is_term,
			'term_id'            => $is_term ? $term->term_id : sanitize_name( $option ),
			'slug'               => $slug,
			'variation_image_id' => absint( $single_attribute_variation_image_id ),
			'total_attributes'   => absint( $count_attributes ),
			'option_slug'        => sanitize_name( $slug ),
			'item'               => $term_or_option,
			'options'            => $options,
			'option_name'        => $option_name,
			'attribute'          => $attribute,
			'attribute_key'      => sanitize_title( $attribute ),
			'attribute_name'     => wc_variation_attribute_name( $attribute ),
			'attribute_label'    => wc_attribute_label( $attribute, $product ),
			'args'               => $args,
			'product'            => $product,
		);

		return apply_filters( 'woo_variation_swatches_get_swatch_data', $data, $args, $product );
	}

	public function get_available_variation_images( $product ) {

		$cache_key   = get_cache_key( sprintf( 'variation_images_of__%s', $product->get_id() ) );
		$cache_group = 'woo_variation_swatches';

		$default_to_image_from_parent = wc_string_to_bool( get_option( 'default_to_image_from_parent', 'yes' ) );

		if ( false === ( $variations = wp_cache_get( $cache_key, $cache_group ) ) ) {

			$variation_ids        = $product->get_children();
			$available_variations = array();

			foreach ( $variation_ids as $variation_id ) {

				$variation = wc_get_product( $variation_id );

				// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
				if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
					//	continue;
				}

				if ( ! $variation || ! $variation->exists() ) {
					continue;
				}

				// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
				if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
					continue;
				}

				if ( ! $variation->get_image_id( 'edit' ) > 0 && ! $default_to_image_from_parent ) {
					continue;
				}

				$available_variations[] = $this->get_available_variation_image( $variation, $product );
			}

			$variations = array_values( array_filter( $available_variations ) );

			wp_cache_set( $cache_key, $variations, $cache_group );
		}

		return $variations;
	}

	public function get_available_variation_image( $variation, $product ) {
		if ( is_numeric( $variation ) ) {
			$variation = wc_get_product( $variation );
		}
		if ( ! $variation instanceof WC_Product_Variation ) {
			return false;
		}

		// $placeholder_image_id = get_option( 'woocommerce_placeholder_image', 0 );
		// $variation_image_id = $variation->get_image_id() ? $variation->get_image_id() : $placeholder_image_id;

		$available_variation = array(
			'attributes'           => $variation->get_variation_attributes(),
			'image_id'             => $variation->get_image_id(),
			'is_in_stock'          => $variation->is_in_stock(),
			'is_purchasable'       => $variation->is_purchasable(),
			'variation_id'         => $variation->get_id(),
			'variation_image_id'   => $variation->get_image_id(),
			'product_id'           => $product->get_id(),
			'availability_html'    => wc_get_stock_html( $variation ),
			'price_html'           => '<span class="price">' . $variation->get_price_html() . '</span>',
			'variation_is_active'  => $variation->variation_is_active(),
			'variation_is_visible' => $variation->variation_is_visible(),
		);

		return apply_filters( 'woo_variation_swatches_get_available_variation_image', $available_variation, $variation, $product );
	}

	public function get_variation_by_attribute_name_value( $available_variations, $attribute_name, $attribute_value ) {
		return array_reduce( $available_variations, function ( $item, $variation ) use ( $attribute_name, $attribute_value ) {

			if ( $variation[ 'attributes' ][ $attribute_name ] === $attribute_value ) {
				$item = $variation;
			}

			return $item;
		}, array() );
	}

	/**
	 * Init Hooks
	 *
	 * Call when class excuted;
	 *
	 * Init Hooks
	 *
	 * @since 1.0.0
	 *
	 */
	function init_hooks()
	{
		// Get Selected Variations for Default Value
		add_filter('woocommerce_dropdown_variation_attribute_options_args', array($this, 'set_selected_attribute_args'), 50, 1);

		// Remove Reset Product Tiles
		add_filter('woocommerce_reset_variations_link',  '__return_empty_string', 15);

		add_action('wp_enqueue_scripts',  array($this, 'enqueue_styles'), 50);

		// Ensure only one swatch stylesheet is enqueued if CommerceKit provides it
		add_action('wp_print_styles', array($this, 'maybe_dequeue_fallback_style'), 100);

		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'dropdown' ), 25, 2 );

		// Remove cgkit-disabled class from grill-only specifically
		add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'remove_disabled_from_grill_only' ), 30, 2 );

		// Only ensure grill-only is always available in variations
		add_filter( 'woocommerce_available_variation', array( $this, 'ensure_grill_only_availability' ), 5, 3 );

		// Ensure grill-only is included in dropdown options
		add_filter( 'woocommerce_dropdown_variation_attribute_options_args', array( $this, 'ensure_grill_only_in_options' ), 5, 1 );

		// Add missing hooks from old plugin
		add_filter('woocommerce_variable_price_html', array($this, 'disable_variable_price_range'), 100, 2);
		add_filter('formatted_woocommerce_price',  array($this, 'remove_zero_decimals'), 10, 5);
		add_action('wp_ajax_get_cross_products', array($this, 'variant_get_cross_products')); // for logged in users
		add_action('wp_ajax_nopriv_get_cross_products', array($this, 'variant_get_cross_products')); // for logged in users
		add_action('wp_ajax_woocommerce_ajax_add_to_cart', array($this, 'woocommerce_ajax_add_to_cart'), 10);
		add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', array($this, 'woocommerce_ajax_add_to_cart'), 10);
		add_action('wp_ajax_woocommerce_ajax_check_add_to_cart', array($this, 'woocommerce_ajax_check_add_to_cart'), 10);
		add_action('wp_ajax_nopriv_woocommerce_ajax_check_add_to_cart', array($this, 'woocommerce_ajax_check_add_to_cart'), 10);
		add_action('woocommerce_available_variation', array($this, 'variable_product_total_amount_qty_change'), 25, 3);

		// Add hooks for grill-only cart validation
		add_filter('woocommerce_add_to_cart_validation', array($this, 'ensure_grill_only_cart_validation'), 10, 4);
		add_filter('woocommerce_available_variations', array($this, 'ensure_grill_only_in_available_variations'), 10, 2);
	}

	/**
	 * Ensure grill-only is always included in variation attribute options
	 */
	public function ensure_grill_only_in_options( $args ) {
		// Only process bundles attribute
		if ( ! isset( $args['attribute'] ) || strpos( $args['attribute'], 'bundles' ) === false ) {
			return $args;
		}

		// Ensure grill-only is in the options array
		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			if ( ! in_array( 'grill-only', $args['options'] ) ) {
				$args['options'][] = 'grill-only';
			}
		}

		return $args;
	}

	/**
	 * Remove cgkit-disabled class from grill-only option in CommerceKit output
	 */
	public function remove_disabled_from_grill_only( $html, $args ) {
		// Only process if this is a bundles attribute
		if ( strpos( $args['attribute'], 'bundles' ) === false ) {
			return $html;
		}

		// Remove cgkit-disabled class from grill-only button - multiple patterns
		$patterns = array(
			// Pattern 1: cgkit-disabled with spaces around
			'/(\s+)cgkit-disabled(\s+)/i',
			// Pattern 2: cgkit-disabled at start of class
			'/class="cgkit-disabled(\s+)/i',
			// Pattern 3: cgkit-disabled at end of class
			'/(\s+)cgkit-disabled"/i',
			// Pattern 4: cgkit-disabled as only class
			'/class="cgkit-disabled"/i'
		);

		$replacements = array(
			'$1$2',          // Remove with spaces
			'class="$1',     // Remove from start
			'$1"',           // Remove from end
			'class=""'       // Remove if only class
		);

		// Only process grill-only buttons
		if ( strpos( $html, 'data-attribute-value="grill-only"' ) !== false ) {
			$html = preg_replace( $patterns, $replacements, $html );

			// Fallback: direct string replacement for common cases
			$html = str_replace( 'cgkit-disabled', '', $html );

			// Clean up any double spaces that might result
			$html = preg_replace( '/\s+/', ' ', $html );
			$html = str_replace( 'class=" ', 'class="', $html );
			$html = str_replace( ' "', '"', $html );
		}

		return $html;
	}

	/**
	 * Ensure grill-only variation is always available and purchasable
	 */
	public function ensure_grill_only_availability( $variation_data, $product, $variation ) {
		// Check if this variation has the grill-only attribute
		$attributes = $variation->get_variation_attributes();
		foreach ( $attributes as $attr_name => $attr_value ) {
			if ( strpos( $attr_name, 'bundles' ) !== false && $attr_value === 'grill-only' ) {
				// Force grill-only to be available and purchasable
				$variation_data['is_in_stock'] = true;
				$variation_data['is_purchasable'] = true;
				$variation_data['variation_is_active'] = true;
				$variation_data['variation_is_visible'] = true;

				// Ensure the variation has a valid price
				if ( empty( $variation_data['display_price'] ) ) {
					$variation_data['display_price'] = $variation->get_price();
				}
				if ( empty( $variation_data['display_regular_price'] ) ) {
					$variation_data['display_regular_price'] = $variation->get_regular_price();
				}

				break;
			}
		}

		return $variation_data;
	}

	/**
	 * Ensure grill-only variations are always considered valid for cart operations
	 */
	public function ensure_grill_only_cart_validation( $passed, $product_id, $quantity, $variation_id = 0 ) {
		// Only process if this is a variation product
		if ( $variation_id > 0 ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation && $variation->is_type( 'variation' ) ) {
				$attributes = $variation->get_variation_attributes();

				// Check if this is a grill-only variation
				foreach ( $attributes as $attr_name => $attr_value ) {
					if ( strpos( $attr_name, 'bundles' ) !== false && $attr_value === 'grill-only' ) {
						// Force validation to pass for grill-only variations
						return true;
					}
				}
			}
		}

		return $passed;
	}

	/**
	 * Ensure grill-only variations are always included in available variations
	 */
	public function ensure_grill_only_in_available_variations( $variations, $product ) {
		// Only process variable products
		if ( ! $product->is_type( 'variable' ) ) {
			return $variations;
		}

		// Get all variation IDs
		$variation_ids = $product->get_children();

		foreach ( $variation_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( ! $variation || ! $variation->is_type( 'variation' ) ) {
				continue;
			}

			$attributes = $variation->get_variation_attributes();
			$is_grill_only = false;

			// Check if this is a grill-only variation
			foreach ( $attributes as $attr_name => $attr_value ) {
				if ( strpos( $attr_name, 'bundles' ) !== false && $attr_value === 'grill-only' ) {
					$is_grill_only = true;
					break;
				}
			}

			if ( $is_grill_only ) {
				// Ensure this variation is included in available variations
				$variation_data = array(
					'variation_id' => $variation_id,
					'attributes' => $variation->get_variation_attributes(),
					'is_in_stock' => true,
					'is_purchasable' => true,
					'variation_is_active' => true,
					'variation_is_visible' => true,
					'display_price' => $variation->get_price(),
					'display_regular_price' => $variation->get_regular_price(),
					'price_html' => $variation->get_price_html(),
					'availability_html' => wc_get_stock_html( $variation ),
				);

				// Add to variations array if not already present
				$found = false;
				foreach ( $variations as $existing_variation ) {
					if ( $existing_variation['variation_id'] == $variation_id ) {
						$found = true;
						break;
					}
				}

				if ( ! $found ) {
					$variations[] = $variation_data;
				}
			}
		}

		return $variations;
	}

	function has_custom_widget($elements) {
		foreach ($elements as $element) {
			if (!empty($element['widgetType']) && ($element['widgetType'] === 'product-variant-tiles' || $element['widgetType'] === 'productvarianttilesv4')) {
				return true;
			}

			if (!empty($element['elements'])) {
				if ($this->has_custom_widget($element['elements'])) {
					return true;
				}
			}
		}
		return false;
	}

	public function maybe_dequeue_fallback_style() {
		if ( function_exists('is_product') && is_product() ) {

		}
	}

	/**
	 * Enqueue assets for savings display and accordion functionality
	 */
	public function enqueue_savings_accordion_assets() {
		if (is_product()) {
			wp_enqueue_style(
				'zg-savings-accordion',
				PROTILES_URL . 'assets/css/savings-accordion.css',
				array(),
				'1.0.0.' . time()
			);

			wp_enqueue_script(
				'zg-savings-accordion',
				PROTILES_URL . 'assets/js/savings-accordion.js',
				array('jquery'),
				'1.0.0.' . time(),
				true
			);
		}
	}

				/**
	 * Display total savings and accordion section above ATC button
	 */
	public function display_total_savings_and_accordion() {
		// This method is now handled by the v4 widget
		// Keeping it for backward compatibility but not using it
		return;
	}

		/**
	 * Get default variation data for the product
	 */
	private function get_default_variation_data($product) {
		$data = array(
			'accordion_title' => '',
			'accordion_content' => '',
			'accordion_preview' => '',
			'savings' => 0,
			'has_content' => false
		);

		if ($product->is_type('variable')) {
			// Get default variation or first available variation using CommerceKit approach
			$default_attributes = $product->get_default_attributes();
			// Use CommerceKit's variation handling for consistent data structure and caching
		$variations = commercekit_get_available_variations($product);

			if (!empty($variations)) {
				// Try to find default variation, otherwise use first
				$default_variation = null;
				if (!empty($default_attributes)) {
					foreach ($variations as $variation) {
						$matches_default = true;
						foreach ($default_attributes as $attr_name => $attr_value) {
							if (!isset($variation['attributes']['attribute_' . $attr_name]) ||
								$variation['attributes']['attribute_' . $attr_name] !== $attr_value) {
								$matches_default = false;
								break;
							}
						}
						if ($matches_default) {
							$default_variation = $variation;
							break;
						}
					}
				}

				// Use first variation if no default found
				if (!$default_variation) {
					$default_variation = $variations[0];
				}

				$variation_id = $default_variation['variation_id'];
				$data = $this->get_variation_data($variation_id, $default_variation);
			}
		}

		return $data;
	}

	/**
	 * Get variation-specific data
	 */
	private function get_variation_data($variation_id, $variation = null) {
		$data = array(
			'accordion_title' => '',
			'accordion_content' => '',
			'accordion_preview' => '',
			'savings' => 0,
			'has_content' => false
		);

		// Get variation-specific dropdown data
		$variation = wc_get_product($variation_id);
		$dd_text = $variation ? $variation->get_meta('_vt_dd_text', true) : '';
		$dd_preview = $variation ? $variation->get_meta('_vt_dd_preview', true) : '';

		if (!empty($dd_text)) {
			$data['accordion_title'] = "What's included?";
			$data['accordion_content'] = $dd_text;
			$data['has_content'] = true;
		}

		if (!empty($dd_preview)) {
			$data['accordion_preview'] = $dd_preview;
		}

		// Calculate savings for this variation
		if ($variation) {
			$regular_price = $variation['display_regular_price'];
			$sale_price = $variation['display_price'];

			if ($regular_price && $sale_price && $regular_price > $sale_price) {
				$data['savings'] = $regular_price - $sale_price;
			}
		}

		return $data;
	}
}