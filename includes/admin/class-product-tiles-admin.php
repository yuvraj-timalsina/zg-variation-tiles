<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class - Handles admin functionality for variation tiles
 *
 * @package PROTILES
 * @since 1.0.0
 */

class Product_Tiles_Admin {

	function __construct(){

	}

	function admin_enqueue_scripts(){
		// Only enqueue on product edit pages
		$screen = get_current_screen();
		if ( $screen && in_array( $screen->id, array( 'product', 'edit-product' ) ) ) {
			wp_enqueue_style( 'product-tiles-style', PROTILES_URL. '/includes/admin/assets/css/style.css',array(),  '1.0.0' );
			wp_enqueue_media(); // Enqueue WordPress media scripts
			wp_enqueue_script( 'product-tiles-general', PROTILES_URL. '/includes/admin/assets/js/general.js', array('jquery', 'media-upload'), '1.0.5', true );
		}
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
	    	?>
	    	<div class="vt-variation-tile-section" style="background: #f8f9fa; border: 2px solid #0073aa; border-radius: 8px; padding: 20px; margin: 15px 0; position: relative;">
	    		<div style="position: absolute; top: -10px; left: 15px; background: #0073aa; color: white; padding: 5px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
	    			<?php esc_html_e( 'Variant Tile Data', 'woocommerce' ); ?>
	    		</div>

	    			    			    		<div style="margin-top: 10px;">
	    				    				    			<!-- Two Column Layout -->
	    			<div style="display: flex; gap: 20px; align-items: flex-start;">
	    				<!-- Left Column: Image (Full Height) -->
	    				<div style="flex: 1; display: flex; flex-direction: column;">
	    					<?php
	    					// Image upload field
	    					$image_id = get_post_meta( $variation->ID, '_vt_dd_image_id', true );
	    					$image_url = '';
	    					$has_image = false;

	    					if ( $image_id && wp_attachment_is_image( $image_id ) ) {
	    						$image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
	    						$has_image = true;
	    					}
	    					?>
	    					<div class="vt-field-group" style="margin-bottom: 10px; flex: 1; display: flex; flex-direction: column;">
	    						<label for="_vt_dd_image_id<?php echo esc_attr( $loop ); ?>" style="margin-bottom: 10px;"><?php esc_html_e( 'Dropdown Image', 'woocommerce' ); ?></label>
	    						<input type="hidden"
	    						       id="_vt_dd_image_id<?php echo esc_attr( $loop ); ?>"
	    						       name="_vt_dd_image_id[<?php echo esc_attr( $loop ); ?>]"
	    						       value="<?php echo esc_attr( $image_id ); ?>"
	    						       class="vt-image-id" />

	    						<div class="vt-media-upload-container" style="flex: 1; display: flex; flex-direction: column;">
	    							<div class="vt-media-preview" id="vt-media-preview-<?php echo esc_attr( $loop ); ?>" <?php echo ! $has_image ? 'style="display:none;"' : ''; ?> style="flex: 1; display: flex; align-items: center; justify-content: center;">
	    								<?php if ( $has_image && $image_url ) : ?>
	    									<div class="vt-media-item" style="text-align: center;">
	    										<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php esc_attr_e( 'Variant tile image', 'woocommerce' ); ?>" style="max-width: 100%; max-height: 300px; height: auto; object-fit: contain;" />
	    									</div>
	    								<?php endif; ?>
	    							</div>

	    							<div class="vt-media-actions" style="margin-top: auto;">
	    								<button type="button" class="button vt-media-upload-btn" data-loop="<?php echo esc_attr( $loop ); ?>">
	    									<span class="dashicons dashicons-plus-alt2"></span>
	    									<?php esc_html_e( 'Add Media', 'woocommerce' ); ?>
	    								</button>
	    								<button type="button" class="button vt-media-edit-btn" data-loop="<?php echo esc_attr( $loop ); ?>" <?php echo ! $has_image ? 'style="display:none;"' : ''; ?>>
	    									<span class="dashicons dashicons-edit"></span>
	    									<?php esc_html_e( 'Edit', 'woocommerce' ); ?>
	    								</button>
	    								<button type="button" class="button vt-media-remove-btn" data-loop="<?php echo esc_attr( $loop ); ?>" <?php echo ! $has_image ? 'style="display:none;"' : ''; ?>>
	    									<span class="dashicons dashicons-trash"></span>
	    									<?php esc_html_e( 'Remove', 'woocommerce' ); ?>
	    								</button>
	    							</div>
	    						</div>
	    					</div>

	    				</div>

	    				<!-- Right Column: Preview Text + Dropdown Text -->
	    				<div style="flex: 1;">
	    						    					<div class="vt-field-group" style="margin-bottom: 10px;">
	    						<label for="_vt_dd_preview<?php echo esc_attr( $loop ); ?>" style="margin-bottom: 10px;"><?php esc_html_e( 'Preview Text', 'woocommerce' ); ?></label>
	    						<input type="text"
	    						       id="_vt_dd_preview<?php echo esc_attr( $loop ); ?>"
	    						       name="_vt_dd_preview[<?php echo esc_attr( $loop ); ?>]"
	    						       value="<?php echo esc_attr( get_post_meta( $variation->ID, '_vt_dd_preview', true ) ); ?>"
	    						       class="short" />
	    					</div>

	    					<div class="vt-field-group" style="margin-bottom: 10px;">
	    						<label for="_vt_dd_text<?php echo esc_attr( $loop ); ?>" style="margin-bottom: 10px;"><?php esc_html_e( 'Dropdown Text', 'woocommerce' ); ?></label>
	    							    						<textarea
	    						       id="_vt_dd_text<?php echo esc_attr( $loop ); ?>"
	    						       name="_vt_dd_text[<?php echo esc_attr( $loop ); ?>]"
	    						       class="short"
	    						       rows="11"><?php echo esc_textarea( get_post_meta( $variation->ID, '_vt_dd_text', true ) ); ?></textarea>
	    					</div>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    	<?php
	    }
	}

		function protiles_save_variation_settings_fields( $variation_id, $loop ) {
	    // Verify user permissions
	    if ( ! current_user_can( 'edit_products' ) ) {
	        return;
	    }

	    // Save new optional fields with proper sanitization
	    if ( isset( $_POST['_vt_dd_text'][ $loop ] ) ) {
	    	update_post_meta( $variation_id, '_vt_dd_text', wp_kses_post( $_POST['_vt_dd_text'][ $loop ] ) );
	    }

	    // Save image ID - allow empty values to clear the image
	    if ( isset( $_POST['_vt_dd_image_id'][ $loop ] ) ) {
	    	$image_id = absint( $_POST['_vt_dd_image_id'][ $loop ] );

	    	if ( $image_id > 0 ) {
	    		// Verify the image exists and is an image
	    		if ( wp_attachment_is_image( $image_id ) ) {
	    			update_post_meta( $variation_id, '_vt_dd_image_id', $image_id );
	    		} else {
	    			delete_post_meta( $variation_id, '_vt_dd_image_id' );
	    		}
	    	} else {
	    		// Clear the image if ID is 0 or empty
	    		delete_post_meta( $variation_id, '_vt_dd_image_id' );
	    	}
	    }

	    if ( isset( $_POST['_vt_dd_preview'][ $loop ] ) ) {
	    	update_post_meta( $variation_id, '_vt_dd_preview', sanitize_textarea_field( $_POST['_vt_dd_preview'][ $loop ] ) );
	    }

	}

	function protiles_load_variation_settings_fields( $variation ) {
	    $variation['offer_label'] = get_post_meta( $variation[ 'variation_id' ], 'offer_label', true );

	    // Add dropdown data for frontend JavaScript
	    $variation['_vt_dd_text'] = get_post_meta( $variation[ 'variation_id' ], '_vt_dd_text', true );
	    $variation['_vt_dd_preview'] = get_post_meta( $variation[ 'variation_id' ], '_vt_dd_preview', true );

	    // Add image data for frontend
	    $image_id = get_post_meta( $variation[ 'variation_id' ], '_vt_dd_image_id', true );
	    if ( $image_id && wp_attachment_is_image( $image_id ) ) {
	        $variation['_vt_dd_image_url'] = wp_get_attachment_image_url( $image_id, 'thumbnail' );
	        $variation['_vt_dd_image_id'] = $image_id;
	    } else {
	        $variation['_vt_dd_image_url'] = '';
	        $variation['_vt_dd_image_id'] = '';
	    }

	    return $variation;
	}


	function init_hooks(){
		// Removed legacy variation option field hooks
		add_action( 'woocommerce_product_after_variable_attributes', array($this, 'protiles_variation_settings_fields'), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array($this, 'protiles_save_variation_settings_fields'), 10, 2 );
		add_filter( 'woocommerce_available_variation', array($this, 'protiles_load_variation_settings_fields'), 10, 1 );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 20 );

	}
}