<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality.
 *
 * @package PROTILES
 * @since 1.0.0
 */

class Product_Tiles_Admin {

	function __construct(){

	}

	function admin_enqueue_scripts(){
		wp_enqueue_style( 'product-tiles-style', PROTILES_URL. '/includes/admin/assets/css/style.css',array(),  '1.0.0' );
		// wp_enqueue_script( 'product-tiles-variations-editor', '/includes/admin/assets/js/variations-editor.js',   array('jquery', 'quicktags'), '1.0.0' );
		wp_enqueue_script( 'product-tiles-general', PROTILES_URL. '/includes/admin/assets/js/general.js', array(), '1.0.0' );
	}

	function protiles_variation_main_fields( $loop, $variation_data, $variation ){

		$variable_is_zg_bundle = get_post_meta( $variation->ID, 'variable_is_zg_bundle', true );
		?>
		<label class="tips">
			<?php esc_html_e( 'ZG Bundle', 'woocommerce' ); ?>
			<input type="checkbox" class="checkbox variable_is_zg_bundle" name="variable_is_zg_bundle[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variable_is_zg_bundle, true ); ?> />
		</label>
		<?php
	}

	function protiles_variation_settings_fields( $loop, $variation_data, $variation ) {

	    $enable_tiles = get_post_meta( get_the_ID(), '_vt_enable_tiles', true ) === 'yes';
	    if ( $enable_tiles ) {
	    	woocommerce_wp_textarea_input(
	        array(
	    			'id'            => "_vt_dd_text{$loop}",
	    			'name'          => "_vt_dd_text[{$loop}]",
	    			'value'         => get_post_meta( $variation->ID, '_vt_dd_text', true ),
	    			'label'         => __( 'Description – Drop Down Text', 'woocommerce' ),
	            'wrapper_class' => 'form-row form-row-full',
	        )
	    );
	     woocommerce_wp_text_input(
	        array(
	    			'id'            => "_vt_dd_image_id{$loop}",
	    			'name'          => "_vt_dd_image_id[{$loop}]",
	    			'value'         => get_post_meta( $variation->ID, '_vt_dd_image_id', true ),
	    			'label'         => __( 'Variation Include (Image) – Drop Down Image ID', 'woocommerce' ),
	            'wrapper_class' => 'form-row form-row-full',
	    			'type'          => 'number',
	        )
	    );
	    	woocommerce_wp_text_input(
	        array(
	    			'id'            => "_vt_dd_preview{$loop}",
	    			'name'          => "_vt_dd_preview[{$loop}]",
	    			'value'         => get_post_meta( $variation->ID, '_vt_dd_preview', true ),
	    			'label'         => __( 'Include Excerpt – Drop Down Preview Text', 'woocommerce' ),
	    			'wrapper_class' => 'form-row form-row-full',
	        )
	    );
	    	woocommerce_wp_text_input(
		        array(
	    			'id'            => "_vt_offer_label{$loop}",
	    			'name'          => "_vt_offer_label[{$loop}]",
	    			'value'         => get_post_meta( $variation->ID, '_vt_offer_label', true ),
	    			'label'         => __( 'Offer Label – Tile Offer Label', 'woocommerce' ),
	            'wrapper_class' => 'form-row form-row-full',
	        )
	    );
	    }
	}

	function protiles_save_variation_settings_fields( $variation_id, $loop ) {
	    // Save new optional fields
	    if ( isset( $_POST['_vt_dd_text'][ $loop ] ) ) {
	    	update_post_meta( $variation_id, '_vt_dd_text', wp_kses_post( $_POST['_vt_dd_text'][ $loop ] ) );
	    }
	    if ( isset( $_POST['_vt_dd_image_id'][ $loop ] ) ) {
	    	update_post_meta( $variation_id, '_vt_dd_image_id', absint( $_POST['_vt_dd_image_id'][ $loop ] ) );
	    }
	    if ( isset( $_POST['_vt_dd_preview'][ $loop ] ) ) {
	    	update_post_meta( $variation_id, '_vt_dd_preview', sanitize_text_field( $_POST['_vt_dd_preview'][ $loop ] ) );
	    }
	    if ( isset( $_POST['_vt_offer_label'][ $loop ] ) ) {
	    	update_post_meta( $variation_id, '_vt_offer_label', sanitize_text_field( $_POST['_vt_offer_label'][ $loop ] ) );
	    }
	}

	function protiles_load_variation_settings_fields( $variation ) {
	    $variation['offer_label'] = get_post_meta( $variation[ 'variation_id' ], 'offer_label', true );

	    // Add dropdown data for frontend JavaScript
	    $variation['_vt_dd_text'] = get_post_meta( $variation[ 'variation_id' ], '_vt_dd_text', true );
	    $variation['_vt_dd_preview'] = get_post_meta( $variation[ 'variation_id' ], '_vt_dd_preview', true );

	    return $variation;
	}

	function add_vt_enable_tiles_field(){
		echo '<div class="options_group">';
		 woocommerce_wp_checkbox(
	        array(
	            'id'            => '_vt_enable_tiles',
	            'label'         =>  __( 'Enable Variant Tile (CK augment)', 'woocommerce' ),
	            'desc_tip'      => false,
	            'value'         => get_post_meta( get_the_ID(), '_vt_enable_tiles', true ) ? 'yes' : '',
	            'wrapper_class' => 'form-row',
	        )
	    );
		echo '</div>';
	}


	function protiles_linked_products_data_custom_field(){

		   woocommerce_wp_select_multiple_products(
	        array(
	            'id' => "zg_exclusion_product_id",
	            'name' => "zg_exclusion_product_id",
	            'value' => get_post_meta(get_the_ID(), 'zg_exclusion_product_id', true),
	            'label' => __('ZG Simple Custom Exclusion', 'woocommerce'),
	            'options' =>array(),
	            'wrapper_class' => 'form-row form-row-full select',
	            'class' => 'seemore_editor mceEditor',
	            'varition_id' => get_the_ID(),
	        )
	    );
	}
	function protiles_linked_products_data_custom_field_save($post_id ){

		$enable = isset( $_POST['_vt_enable_tiles'] ) ? 'yes' : 'no';
	    	update_post_meta( $post_id, '_vt_enable_tiles', $enable );

	}
	function init_hooks(){
		// Removed legacy variation option field hooks
		add_action( 'woocommerce_product_after_variable_attributes', array($this, 'protiles_variation_settings_fields'), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array($this, 'protiles_save_variation_settings_fields'), 10, 2 );
		add_filter( 'woocommerce_available_variation', array($this, 'protiles_load_variation_settings_fields'), 10, 1 );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 20 );
		add_action( 'woocommerce_product_options_advanced', array($this, 'add_vt_enable_tiles_field'), 20 );
		add_action( 'woocommerce_process_product_meta', array($this, 'protiles_linked_products_data_custom_field_save') );
	}
}