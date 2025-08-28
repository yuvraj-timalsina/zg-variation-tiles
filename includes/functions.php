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
			$status = get_post_status($variation_id);
			if($status == 'publish'){
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
		$parent = wp_get_post_parent_id( $variation_id);

	    if ( $parent > 0 ) {
	         $_product = wc_get_product( $variation_id);
	       	 $price_sale = get_post_meta($variation_id, '_sale_price', true);
	       	 if($price_sale > 0){
	       	 	$data['sale'] = '$' . number_format( $_product->get_sale_price(), 0 );

	       	 }
	       	 	$data['regular'] = '$' . number_format( $_product->get_regular_price(), 0 );
	    }
	    return $data;
	}

	/**
     * Return Custom Field Value
     */
	function variation_custom_field( $term, $option, $attribute, $product, $field  ) {
		$variation_id = get_variation_id( $term, $option, $attribute, $product  );
 		if (  $variation_id > 0 ) {
	      return get_post_meta($variation_id, $field, true);
	   }
	   return '';
	}

	function sanitize_name( $value ) {
		return wc_clean( rawurldecode( sanitize_title( wp_unslash( $value ) ) ) );
	}

	function zg_images_url( $file = '' ) {
		return untrailingslashit( plugin_dir_url( PROTILES_DIR ) . 'images' ) . $file;
	}
