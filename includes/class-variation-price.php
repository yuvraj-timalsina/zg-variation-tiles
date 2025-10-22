<?php
/**
 * ZG Variation Tiles - Price and ATC Button Updates
 *
 * This class provides PHP hooks to ensure proper integration
 * with WooCommerce variation handling.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class ZG_Variation_Price {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('woocommerce_before_single_product_summary', array($this, 'add_variation_data'));
        add_filter('woocommerce_available_variation', array($this, 'enhance_variation_data'), 10, 3);
    }

    /**
     * Enqueue necessary scripts
     */
    public function enqueue_scripts() {
        if (is_product()) {
            global $product;

            // Ensure $product is a valid WooCommerce product object
            if ($product && is_object($product) && method_exists($product, 'is_type') && $product->is_type('variable')) {
                wp_enqueue_script('wc-add-to-cart-variation');
                wp_enqueue_script('variation-price');
            }
        }
    }

    /**
     * Add variation data to the page
     */
    public function add_variation_data() {
        global $product;

        if (!$product || !$product->is_type('variable')) {
            return;
        }

        // Add data attributes to help JavaScript identify the form
        echo '<script type="text/javascript">';
        echo 'window.zgVariationData = {';
        echo 'productId: ' . $product->get_id() . ',';
        echo 'isVariable: true,';
        echo 'variationFormSelector: ".variations_form"';
        echo '};';
        echo '</script>';
    }

    /**
     * Enhance variation data with additional information
     */
    public function enhance_variation_data($variation_data, $product, $variation) {
        // Ensure price_html is always available
        if (empty($variation_data['price_html'])) {
            $variation_data['price_html'] = $variation->get_price_html();
        }

        // Add variation ID for tracking
        $variation_data['variation_id'] = $variation->get_id();

        // Add display price for easy access
        $variation_data['display_price'] = $variation->get_price();

        // Add formatted price without HTML
        $variation_data['formatted_price'] = strip_tags($variation->get_price_html());

        return $variation_data;
    }
}

// Initialize the price updates
new ZG_Variation_Price();
