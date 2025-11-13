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
			// Check if we're actually editing a variable product
			global $post;
			if ( $post && $post->post_type === 'product' ) {
				$product = wc_get_product( $post->ID );
				if ( $product && $product->is_type( 'variable' ) ) {
					wp_enqueue_style( 'product-tiles-style', PROTILES_URL. '/includes/admin/assets/css/style.css',array(),  '1.0.0' );
					wp_enqueue_media(); // Enqueue WordPress media scripts
					wp_enqueue_script( 'product-tiles-general', PROTILES_URL. '/includes/admin/assets/js/general.js', array('jquery', 'media-upload', 'wc-admin-meta-boxes'), '1.0.6', true );

					// Add script dependencies to ensure WooCommerce scripts load first
					wp_script_add_data( 'product-tiles-general', 'deps', array('jquery', 'media-upload', 'wc-admin-meta-boxes') );
				}
			}
		}
	}

	function protiles_variation_main_fields( $loop, $variation_data, $variation ){

		// Convert WP_Post to WC_Product if needed
		if ( is_a( $variation, 'WP_Post' ) ) {
		    $variation = wc_get_product( $variation->ID );
		}

		$variable_is_zg_bundle = $variation ? $variation->get_meta( 'variable_is_zg_bundle', true ) : false;
		?>
		<label class="tips">
			<?php esc_html_e( 'ZG Bundle', 'woocommerce' ); ?>
			<input type="checkbox" class="checkbox variable_is_zg_bundle" name="variable_is_zg_bundle[<?php echo esc_attr( $loop ); ?>]" value="1" <?php checked( $variable_is_zg_bundle, true ); ?> />
		</label>
		<?php
	}

	function protiles_variation_settings_fields( $loop, $variation_data, $variation ) {

	    // Convert WP_Post to WC_Product if needed
	    if ( is_a( $variation, 'WP_Post' ) ) {
	        $variation = wc_get_product( $variation->ID );
	    }

	    if ( $variation ) {
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
	    					$image_id = $variation->get_meta( '_vt_dd_image_id', true );
	    					$image_url = '';
	    					$has_image = false;

	    					if ( $image_id && wp_attachment_is_image( $image_id ) ) {
	    						$image_url = wp_get_attachment_image_url( $image_id, 'medium' );
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
	    						       value="<?php echo esc_attr( $variation->get_meta( '_vt_dd_preview', true ) ); ?>"
	    						       class="short" />
	    					</div>

	    					<div class="vt-field-group" style="margin-bottom: 10px;">
	    						<label for="_vt_dd_text<?php echo esc_attr( $loop ); ?>" style="margin-bottom: 10px;"><?php esc_html_e( 'Dropdown Text', 'woocommerce' ); ?></label>
	    							    						<textarea
	    						       id="_vt_dd_text<?php echo esc_attr( $loop ); ?>"
	    						       name="_vt_dd_text[<?php echo esc_attr( $loop ); ?>]"
	    						       class="short"
	    						       rows="11"><?php echo esc_textarea( $variation->get_meta( '_vt_dd_text', true ) ); ?></textarea>
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
	    $variation = wc_get_product($variation_id);
	    if (!$variation) {
	        return;
	    }

	    if ( isset( $_POST['_vt_dd_text'][ $loop ] ) ) {
	    	$dd_text = wp_kses_post( $_POST['_vt_dd_text'][ $loop ] );
	    	$variation->update_meta_data( '_vt_dd_text', $dd_text );

	    	// Debug: Log what we're saving
	    	error_log( 'VT Debug - Saving _vt_dd_text for variation ' . $variation_id . ': ' . substr($dd_text, 0, 100) );
	    }

	    // Save image ID - allow empty values to clear the image
	    if ( isset( $_POST['_vt_dd_image_id'][ $loop ] ) ) {
	    	$image_id = absint( $_POST['_vt_dd_image_id'][ $loop ] );

	    	if ( $image_id > 0 ) {
	    		// Verify the image exists and is an image
	    		if ( wp_attachment_is_image( $image_id ) ) {
	    			$variation->update_meta_data( '_vt_dd_image_id', $image_id );
	    		} else {
	    			$variation->delete_meta_data( '_vt_dd_image_id' );
	    		}
	    	} else {
	    		// Clear the image if ID is 0 or empty
	    		$variation->delete_meta_data( '_vt_dd_image_id' );
	    	}
	    }

	    if ( isset( $_POST['_vt_dd_preview'][ $loop ] ) ) {
	    	$dd_preview = sanitize_textarea_field( $_POST['_vt_dd_preview'][ $loop ] );
	    	$variation->update_meta_data( '_vt_dd_preview', $dd_preview );

	    	// Debug: Log what we're saving
	    	error_log( 'VT Debug - Saving _vt_dd_preview for variation ' . $variation_id . ': ' . substr($dd_preview, 0, 100) );
	    }

	    $variation->save();

	    // Clear variation cache to ensure frontend shows updated data
	    $this->clear_variation_cache($variation_id);

	}

	function protiles_load_variation_settings_fields( $variation ) {
	    $variation_obj = wc_get_product($variation['variation_id']);
	    if ($variation_obj) {
	        $variation['offer_label'] = $variation_obj->get_meta( 'offer_label', true );

	        // Add dropdown data for frontend JavaScript
	        $dd_text = $variation_obj->get_meta( '_vt_dd_text', true );
	        $dd_preview = $variation_obj->get_meta( '_vt_dd_preview', true );

	        $variation['_vt_dd_text'] = $dd_text;
	        $variation['_vt_dd_preview'] = $dd_preview;

	        // Debug: Log what we're loading
	        error_log( 'VT Debug - Loading _vt_dd_text for variation ' . $variation['variation_id'] . ': ' . substr($dd_text, 0, 100) );
	        error_log( 'VT Debug - Loading _vt_dd_preview for variation ' . $variation['variation_id'] . ': ' . substr($dd_preview, 0, 100) );

	        // Add image data for frontend
	        $image_id = $variation_obj->get_meta( '_vt_dd_image_id', true );
	        if ( $image_id && wp_attachment_is_image( $image_id ) ) {
	            $variation['_vt_dd_image_url'] = wp_get_attachment_image_url( $image_id, 'large' );
	            $variation['_vt_dd_image_id'] = $image_id;
	        } else {
	            $variation['_vt_dd_image_url'] = '';
	            $variation['_vt_dd_image_id'] = '';
	        }
	    }

	    return $variation;
	}

	/**
	 * Clear variation cache to ensure frontend shows updated data
	 */
	function clear_variation_cache($variation_id) {
		$variation = wc_get_product($variation_id);
		if (!$variation) {
			return;
		}

		$product_id = $variation->get_parent_id();

		// Clear WooCommerce variation cache
		if (function_exists('wp_cache_delete')) {
			wp_cache_delete('product-' . $product_id, 'products');
			wp_cache_delete('variations-' . $product_id, 'products');
		}

		// Clear variation swatches cache
		if (function_exists('wp_cache_delete_group')) {
			wp_cache_delete_group('woo_variation_swatches');
		}

		// Clear specific variation images cache
		$cache_key = 'variation_images_of__' . $product_id;
		if (function_exists('wp_cache_delete')) {
			wp_cache_delete($cache_key, 'woo_variation_swatches');
		}

		// Clear WooCommerce available variations cache
		delete_transient('wc_var_prices_' . $product_id);
		delete_transient('wc_av_' . $product_id);

		// Clear Elementor cache if available
		if (class_exists('\Elementor\Plugin')) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	/**
	 * Show cache clear notice on product edit pages
	 */
	function show_cache_clear_notice() {
		$screen = get_current_screen();
		if (!$screen || !in_array($screen->id, array('product', 'edit-product'))) {
			return;
		}

		global $post;
		if (!$post || $post->post_type !== 'product') {
			return;
		}

		$product = wc_get_product($post->ID);
		if (!$product || !$product->is_type('variable')) {
			return;
		}

		// Check if we just cleared cache
		if (isset($_GET['vt_cache_cleared']) && $_GET['vt_cache_cleared'] === '1') {
			echo '<div class="notice notice-success is-dismissible"><p>' .
				 esc_html__('Variation tiles cache cleared successfully!', 'zg-variation-tiles') .
				 '</p></div>';
		}

		// Show cache clear button
		$clear_url = wp_nonce_url(
			admin_url('admin-post.php?action=clear_vt_cache&product_id=' . $post->ID),
			'clear_vt_cache_' . $post->ID
		);

		// Show debug button
		$debug_url = wp_nonce_url(
			admin_url('admin-post.php?action=debug_vt_data&product_id=' . $post->ID),
			'debug_vt_data_' . $post->ID
		);

		echo '<div class="notice notice-info is-dismissible">';
		echo '<p><strong>' . esc_html__('ZG Variation Tiles:', 'zg-variation-tiles') . '</strong> ';
		echo esc_html__('If you\'re not seeing updated accordion content on the frontend, try clearing the cache or debugging the data.', 'zg-variation-tiles');
		echo ' <a href="' . esc_url($clear_url) . '" class="button button-small">' .
			 esc_html__('Clear Cache', 'zg-variation-tiles') . '</a>';
		echo ' <a href="' . esc_url($debug_url) . '" class="button button-small" target="_blank">' .
			 esc_html__('Debug Data', 'zg-variation-tiles') . '</a></p>';
		echo '</div>';
	}

	/**
	 * Handle cache clearing request
	 */
	function handle_clear_cache() {
		$product_id = intval($_GET['product_id'] ?? 0);

		if (!$product_id) {
			wp_die('Invalid product ID');
		}

		// Verify nonce
		if (!wp_verify_nonce($_GET['_wpnonce'], 'clear_vt_cache_' . $product_id)) {
			wp_die('Security check failed');
		}

		// Check permissions
		if (!current_user_can('edit_products')) {
			wp_die('You do not have permission to perform this action');
		}

		$product = wc_get_product($product_id);
		if (!$product || !$product->is_type('variable')) {
			wp_die('Invalid product');
		}

		// Clear all variation caches for this product
		$variation_ids = $product->get_children();
		foreach ($variation_ids as $variation_id) {
			$this->clear_variation_cache($variation_id);
		}

		// Clear general caches
		if (function_exists('wp_cache_flush')) {
			wp_cache_flush();
		}

		// Clear Elementor cache
		if (class_exists('\Elementor\Plugin')) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}

		// Redirect back with success message
		$redirect_url = add_query_arg(
			array('vt_cache_cleared' => '1'),
			admin_url('post.php?post=' . $product_id . '&action=edit')
		);

		wp_redirect($redirect_url);
		exit;
	}

	/**
	 * Debug function to check variation data in database
	 */
	function handle_debug_data() {
		$product_id = intval($_GET['product_id'] ?? 0);

		if (!$product_id) {
			wp_die('Invalid product ID');
		}

		// Check permissions
		if (!current_user_can('edit_products')) {
			wp_die('You do not have permission to perform this action');
		}

		$product = wc_get_product($product_id);
		if (!$product || !$product->is_type('variable')) {
			wp_die('Invalid product');
		}

		echo '<h2>Debug: Variation Tiles Data for Product ' . $product_id . '</h2>';

		$variation_ids = $product->get_children();
		foreach ($variation_ids as $variation_id) {
			$variation = wc_get_product($variation_id);
			if ($variation) {
				echo '<h3>Variation ' . $variation_id . '</h3>';

				$dd_text = $variation->get_meta('_vt_dd_text', true);
				$dd_preview = $variation->get_meta('_vt_dd_preview', true);
				$dd_image_id = $variation->get_meta('_vt_dd_image_id', true);

				echo '<p><strong>_vt_dd_text:</strong> ' . esc_html(substr($dd_text, 0, 200)) . '</p>';
				echo '<p><strong>_vt_dd_preview:</strong> ' . esc_html(substr($dd_preview, 0, 200)) . '</p>';
				echo '<p><strong>_vt_dd_image_id:</strong> ' . esc_html($dd_image_id) . '</p>';

				// Check database directly
				global $wpdb;
				$meta_data = $wpdb->get_results($wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE '_vt_%'",
					$variation_id
				));

				echo '<h4>Direct Database Check:</h4>';
				foreach ($meta_data as $meta) {
					echo '<p><strong>' . esc_html($meta->meta_key) . ':</strong> ' . esc_html(substr($meta->meta_value, 0, 200)) . '</p>';
				}
				echo '<hr>';
			}
		}

		wp_die();
	}

	function init_hooks(){
		// Removed legacy variation option field hooks
		add_action( 'woocommerce_product_after_variable_attributes', array($this, 'protiles_variation_settings_fields'), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array($this, 'protiles_save_variation_settings_fields'), 10, 2 );
		add_filter( 'woocommerce_available_variation', array($this, 'protiles_load_variation_settings_fields'), 10, 1 );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 20 );

		// Add admin notice for cache clearing
		add_action( 'admin_notices', array($this, 'show_cache_clear_notice') );
		add_action( 'admin_post_clear_vt_cache', array($this, 'handle_clear_cache') );

		// Add debug function
		add_action( 'admin_post_debug_vt_data', array($this, 'handle_debug_data') );

	}
}