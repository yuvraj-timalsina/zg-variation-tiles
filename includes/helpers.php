<?php
/**
 * Helper Functions for ZG - Product Variant Tile Plugin
 *
 * @package Product Variant Tiles
 * @since 1.2.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get variant tile data for a specific variation
 *
 * @param int $variation_id The variation ID
 * @return array Array containing variant tile data
 */
function vt_get_variation_tile_data( $variation_id ) {
    if ( ! $variation_id ) {
        return array();
    }

    $data = array(
        'text' => get_post_meta( $variation_id, '_vt_dd_text', true ),
        'preview' => get_post_meta( $variation_id, '_vt_dd_preview', true ),
        'offer_label' => get_post_meta( $variation_id, '_vt_offer_label', true ),
        'image_id' => get_post_meta( $variation_id, '_vt_dd_image_id', true ),
        'image_url' => '',
    );

    // Get image URL if image ID exists
    if ( $data['image_id'] && wp_attachment_is_image( $data['image_id'] ) ) {
        $data['image_url'] = wp_get_attachment_image_url( $data['image_id'], 'thumbnail' );
    }

    return $data;
}

/**
 * Get variant tile image HTML
 *
 * @param int $variation_id The variation ID
 * @param string $size Image size (default: 'thumbnail')
 * @param array $attr Additional attributes for the image
 * @return string HTML for the image or empty string if no image
 */
function vt_get_variation_tile_image( $variation_id, $size = 'thumbnail', $attr = array() ) {
    $image_id = get_post_meta( $variation_id, '_vt_dd_image_id', true );

    if ( $image_id && wp_attachment_is_image( $image_id ) ) {
        return wp_get_attachment_image( $image_id, $size, false, $attr );
    }

    return '';
}

/**
 * Check if a product has variant tiles enabled
 *
 * @param int $product_id The product ID
 * @return bool True if variant tiles are enabled
 */
function vt_is_variant_tiles_enabled( $product_id ) {
    return get_post_meta( $product_id, '_vt_enable_tiles', true ) === 'yes';
}

/**
 * Get all variations with tile data for a product
 *
 * @param int $product_id The product ID
 * @return array Array of variations with tile data
 */
function vt_get_product_variations_with_tiles( $product_id ) {
    if ( ! vt_is_variant_tiles_enabled( $product_id ) ) {
        return array();
    }

    $product = wc_get_product( $product_id );
    if ( ! $product || ! $product->is_type( 'variable' ) ) {
        return array();
    }

    $variations = $product->get_available_variations();
    $variations_with_tiles = array();

    foreach ( $variations as $variation ) {
        $tile_data = vt_get_variation_tile_data( $variation['variation_id'] );
        if ( ! empty( $tile_data['text'] ) || ! empty( $tile_data['image_id'] ) ) {
            $variations_with_tiles[] = array_merge( $variation, array( 'tile_data' => $tile_data ) );
        }
    }

    return $variations_with_tiles;
}

/**
 * Sanitize variant tile data
 *
 * @param array $data Raw tile data
 * @return array Sanitized tile data
 */
function vt_sanitize_tile_data( $data ) {
    return array(
        'text' => isset( $data['text'] ) ? wp_kses_post( $data['text'] ) : '',
        'preview' => isset( $data['preview'] ) ? sanitize_textarea_field( $data['preview'] ) : '',
        'offer_label' => isset( $data['offer_label'] ) ? sanitize_text_field( $data['offer_label'] ) : '',
        'image_id' => isset( $data['image_id'] ) ? absint( $data['image_id'] ) : 0,
    );
}