<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


	/**
	 *  provide term, options, attribute of product
	 *
     * Return Variation ID
     *
     * @since 1.0.0
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
     * Return True and False
     *
     * @since 1.0.0
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
	 *
     * Return String of Price For Display
     *
     * @since 1.0.0
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
	 *
     * Return Custom Field Value
     *
     * @since 1.0.0
     */
	function variation_custom_field( $term, $option, $attribute, $product, $field  ) {
		$variation_id = get_variation_id( $term, $option, $attribute, $product  );
 		if (  $variation_id > 0 ) {
	      return get_post_meta($variation_id, $field, true);
	   }
	   return '';
	}

	function woocommerce_wp_select_multiple_products( $field ) {
    	global $thepostid, $post, $woocommerce;
	   $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	   $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
	   $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	   $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	   $field['value']         = isset( $field['value'] ) ? $field['value'] : array();
	   $svalue = [];
	  	foreach($field['value'] as $key => $value){
	  		array_push($svalue,(int)$value);
	  	}

  	   $args = array(
	      'post_type'      => 'product',
	      'posts_per_page' => -1,
	      'post__not_in' => array($thepostid),
	      'tax_query' => array(
		      array(
			      'taxonomy' => 'product_type',
			      'field'    => 'slug',
			      'terms'    => 'simple',
			   ),
			),
	      'fields' => 'ids'
	    );
     	$loop = new WP_Query( $args );

 		echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
 		echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '[]" class="' . esc_attr( $field['class'] ) . '" multiple="multiple" style="width: 100%">';
    	if( $loop->have_posts() ){
 			foreach ( $loop->posts as $key => $post_id ) {
 				echo '<option value="' . $post_id . '" ' .( in_array( $post_id, $svalue ) ? 'selected="selected"' : " " ). '>' . esc_html( get_the_title( $post_id ) ) . '</option>';
 			}
 		}
		echo '</select></p>';
      wp_reset_query();
   ?>
    <script>
    	jQuery(document).ready(function($) {
	    	$('#<?php echo $field['id'] ?>').select2({placeholder: 'Choose Products'});
		});
    </script>
    <?php
	}

	function woocommerce_wp_select_multiple_bundle_products( $field ){
		global $thepostid, $post, $woocommerce;
	   	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	   	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
	   	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	   	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	   	$field['value']         = isset( $field['value'] ) ? $field['value'] : array();
	   	$svalue = [];
	  	foreach( $field['value'] as $key => $value ){
	  		array_push( $svalue, (int) $value );
	  	}

	  	$selected_unique = array_unique( $svalue );

  	   	$args = array(
	      	'post_type'      => 'product',
	      	'posts_per_page' => -1,
	      	'post__not_in' => array($thepostid),
	      	'tax_query' => array(
	      		array(
	      			'taxonomy' => 'product_type',
	      			'field'    => 'slug',
	      			'terms'    => 'simple',
	      		),
	      	)
	    );

     	$loop = new WP_Query( $args );
    	$options = array();
     	if( $loop->have_posts() ) {
     		while ( $loop->have_posts() ) {
     			$loop->the_post();
     			$id = get_the_ID();
     			//if( !in_array( $id, $selected_unique ) ){
     				$options[$id] = esc_html( get_the_title() );
     			//}
     		}
     	}
     	wp_reset_query();

 		echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
 			echo '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';
 			echo '<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '[]" class="' . esc_attr( $field['class'] ) . '" multiple="multiple" style="width: 100%">';
    			foreach ( $options as $id => $title ) {
      				echo '<option value="' . $id . '">' . $title . '</option>';
    			}
    			foreach ( $svalue as $key => $saved_id ) {
    				echo '<option value="' . $saved_id . '" selected="selected">' . get_the_title( $saved_id ) . '</option>';
    			}
			echo '</select>';
		echo '</p>';
   		?>
    	<script>
    		jQuery(document).ready(function($) {

	    		$('#<?php echo $field['id'] ?>').select2();
	    		$('#<?php echo $field['id'] ?>').on("select2:select", function( e ) {
	    			console.log( 'select' );
				    e.preventDefault();
				    var element                = e.params.data.element;
				    var $element               = $(element);
				    $element.detach();
				    $(this).append($element);
				    $(this).trigger("change");
				    $('#<?php echo $field['id'] ?>').append('<option value="'+e.params.data.id+'">' +e.params.data.text + '</option>');
				    $('#<?php echo $field['id'] ?>').trigger('select2:close');
				    return true;
				});
				$('#<?php echo $field['id'] ?>').on('select2:unselect', function( event ) {
					console.log( 'unselect' );
				    var detect                 = false;
				    var element                = event.params.data.text;
				    var selections             = $('#<?php echo $field['id'] ?>').select2('data');
				    var el                     = event.params.data.element;
				    var $el                    = $(el);
				    $el.detach();
				});
				$('#<?php echo $field['id'] ?>').on('select2:close', function( event ) {
					console.log( 'close' );
				    var select = document.getElementById("<?php echo $field['id'] ?>");
				    var options = [];
				    document.querySelectorAll('#<?php echo $field['id'] ?> > option').forEach(
				      option => options.push(option)
				    );
				    while (select.firstChild) {
				        select.removeChild(select.firstChild);
				    }
				    options.sort((a, b) => parseInt(a.innerText)-parseInt(b.innerText));
				    for (var i in options) {
				        select.appendChild(options[i]);
				    }
				});
			});
    	</script>
    <?php
	}

	function sanitize_name( $value ) {
		return wc_clean( rawurldecode( sanitize_title( wp_unslash( $value ) ) ) );
	}

	function zg_images_url( $file = '' ) {
		return untrailingslashit( plugin_dir_url( PROTILES_DIR ) . 'images' ) . $file;
	}

	function get_attribute_taxonomy_by_name( $attribute_name ) {

		$transient_key = get_cache_key( sprintf( 'woo_variation_swatches_cache_attribute_taxonomy__%s', $attribute_name ) );

		if ( ! taxonomy_exists( $attribute_name ) ) {
			return false;
		}

		if ( 'pa_' === substr( $attribute_name, 0, 3 ) ) {
			$attribute_name = str_replace( 'pa_', '', wc_sanitize_taxonomy_name( $attribute_name ) );
		} else {
			return false;
		}

		if ( false === ( $attribute_taxonomy = get_transient( $transient_key ) ) ) {

			global $wpdb;

			$attribute_taxonomy = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s", esc_sql( $attribute_name ) ) );

			set_transient( $transient_key, $attribute_taxonomy );
		}

		return apply_filters( 'woo_variation_swatches_get_wc_attribute_taxonomy', $attribute_taxonomy, $attribute_name );
	}

	function get_cache_key( $name = false ) {
		return get_cache_prefix( 'zg_group' ) . ( $name ? $name : 'zg_variants' );
	}

	function get_cache_prefix( $group ) {

		$prefix_key = 'woo_variation_swatches_';

		// Get cache key - uses cache key {woo_variation_swatches_products_cache_prefix} to invalidate when needed.
		$prefix_string = $prefix_key . $group . '_cache_prefix';
		$prefix        = wp_cache_get( $prefix_string, $group );

		if ( false === $prefix ) {
			$prefix = microtime();
			wp_cache_set( $prefix_string, $prefix, $group );
		}

		return $prefix_key . '_cache_' . $prefix . '_';
	}

	// function woo_variation_swatches() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid

    //     if ( ! class_exists( 'WooCommerce', false ) ) {
    //         return false;
    //     }

    //     if ( function_exists( 'woo_variation_swatches_pro' ) ) {
    //         return woo_variation_swatches_pro();
    //     }

    //     return Woo_Variation_Swatches::instance();
    // }
