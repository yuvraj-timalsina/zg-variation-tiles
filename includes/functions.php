<?php
if ( !defined( 'ABSPATH' ) ) exit;

	/**
	 *  provide term, options, attribute of product
     */
	function get_variation_id($term, $option,  $attribute, $product  ){
		global $wpdb, $product;

	    if ( empty( $term ) ) return $term;
	    if ( empty($product) || empty( $product->get_id() ) ) return $term;

	    $id = $product->get_id();

	    $result = $wpdb->get_col( "SELECT slug FROM {$wpdb->prefix}terms WHERE name = '$term'" );

	    $term_slug = ( !empty( $result ) ) ? $result[0] : $term;

	    $query = "SELECT postmeta.post_id AS product_id
	                FROM {$wpdb->prefix}postmeta AS postmeta
	                    LEFT JOIN {$wpdb->prefix}posts AS products ON ( products.ID = postmeta.post_id )
	                WHERE postmeta.meta_key LIKE 'attribute_%'
	                    AND postmeta.meta_value = '$term_slug'
	                    AND products.post_parent = $id";

	    $variation_id = $wpdb->get_col( $query );
	    return $variation_id[0];
	}

	/**
	 * Check Product variation is enabled or not
     */
	function is_variation_product_enbled($variation_id){
		if($variation_id > 0){
			$variation = wc_get_product($variation_id);
			if($variation && $variation->get_status() == 'publish'){
				return true;
			}
		}
		return false;
	}

	/**
     * Return String of Price For Display
     */
	function display_variation_price( $term, $option, $attribute, $product  ) {
 		$variation_id = get_variation_id( $term, $option, $attribute, $product  );
		$variation = wc_get_product( $variation_id );

	    if ( $variation && $variation->get_parent_id() > 0 ) {
	       	 $price_sale = $variation->get_sale_price();
	       	 if($price_sale > 0){
	       	 	$data['sale'] = '$' . number_format( $price_sale, 0 );

	       	 }
	       	 	$data['regular'] = '$' . number_format( $variation->get_regular_price(), 0 );
	    }
	    return $data;
	}

	/**
     * Return Custom Field Value
     */
	function variation_custom_field( $term, $option, $attribute, $product, $field  ) {
		$variation_id = get_variation_id( $term, $option, $attribute, $product  );
 		if (  $variation_id > 0 ) {
	      $variation = wc_get_product($variation_id);
	      if($variation) {
	          return $variation->get_meta($field, true);
	      }
	   }
	   return '';
	}

	function sanitize_name( $value ) {
		return wc_clean( rawurldecode( sanitize_title( wp_unslash( $value ) ) ) );
	}

	function zg_images_url( $file = '' ) {
		return untrailingslashit( plugin_dir_url( PROTILES_DIR ) . 'images' ) . $file;
	}

	/**
	 * Check if post is WooCommerce order (HPOS compatible)
	 *
	 * @param int $post_id Post id.
	 *
	 * @return bool $bool True|false.
	 */
	if ( ! function_exists( 'zg_is_wc_order' ) ) {
		function zg_is_wc_order( $post_id = 0 ) {
			$bool = false;
			if ( class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
				if ( 'shop_order' === \Automattic\WooCommerce\Utilities\OrderUtil::get_order_type( $post_id ) ) {
					$bool = true;
				}
			} else {
				// Fallback for older WooCommerce versions
				$post_type = get_post_type( $post_id );
				if ( 'shop_order' === $post_type ) {
					$bool = true;
				}
			}

			return $bool;
		}
	}
