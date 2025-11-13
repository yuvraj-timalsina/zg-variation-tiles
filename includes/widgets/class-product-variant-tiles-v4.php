<?php

namespace Elementor;

use Elementor\Widget_Button;
use ElementorPro\Base\Base_Widget_Trait;
use ElementorPro\Modules\QueryControl\Module;
use ElementorPro\Modules\Woocommerce\Widgets\Add_To_Cart;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Box_Shadow;
use ElementorPro\Plugin;
use Elementor\Controls_Manager;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class ProductVariantTilesV4 extends  Widget_Base
{
    use Base_Widget_Trait;

    /**
     * Class constructor.
     *
     * @param array $data Widget data.
     * @param array $args Widget arguments.
     */
    public function __construct($data = array(), $args = null)
    {
        parent::__construct($data, $args);

            // Register styles with consistent versioning and cache busting
    $version = '1.0.2.' . time();
        wp_register_style('pro-tiles-elementor', PROTILES_URL . 'assets/css/pro-tile-elmentor.css', array(), $version);
        wp_register_style('zg-savings-accordion', PROTILES_URL . 'assets/css/savings-accordion.css', array(), $version);

        // Register scripts
        wp_register_script('zg-savings-accordion', PROTILES_URL . 'assets/js/savings-accordion.js', array('jquery'), $version, true);
        wp_enqueue_script('wc-add-to-cart-variation');

        add_filter('variant_tiles_dropdown_continue', function(){
            return true;
        });

        // Add hook for CommerceKit default selection
        add_filter('commercekit_as_get_attribute_swatches_args', array($this, 'commercekit_default_selection'), 10, 1);
    }



    /**
     * Set selection for CommerceKit swatches
     */
    function commercekit_default_selection($args) {
        global $product;

        if (!$product) {
            $product_id = get_queried_object_id();
            $product = wc_get_product($product_id);
        }

        if ($product && $product->is_type('variable')) {
            $default_attributes = $product->get_default_attributes();
            $attribute_name = str_replace('attribute_', '', $args['attribute']);

            // Use product's default attributes if set
            if (isset($default_attributes[$attribute_name])) {
                $args['selected'] = $default_attributes[$attribute_name];
            } else {
                // Fallback: use first available option if no default is set
                if (!empty($args['options']) && is_array($args['options'])) {
                    $args['selected'] = $args['options'][0];
                }
            }
        }

        return $args;
    }

    public function get_style_depends()
    {
        // Core styles always needed
        $deps = ['pro-tiles-elementor', 'zg-savings-accordion'];

        // Only add swatch styles on variable product pages
        if (function_exists('is_product') && is_product()) {
            global $product;

            // Check if current product is variable
            if ($product && method_exists($product, 'is_type') && $product->is_type('variable')) {
                // Use CommerceKit swatches
                $deps[] = 'commercekit-attribute-swatches-css';
            }
        }

        return $deps;
    }

    public function get_script_depends()
    {
        return ['pro-tiles-general', 'zg-savings-accordion', 'variation-price'];
    }

    public function get_name()
    {
        return 'productvarianttilesv4';
    }

    public function get_title()
    {
        return 'Product Variant Tiles V4';
    }

    public function get_icon()
    {
        return 'eicon-woocommerce';
    }

    public function get_categories()
    {
        return ['woocommerce-elements'];
    }

    public function get_keywords()
    {
        return ['woocommerce', 'shop', 'store', 'cart', 'product', 'button', 'add to cart'];
    }

    public function on_export($element)
    {
        unset($element['settings']['product_id']);

        return $element;
    }

    public function unescape_html($safe_text, $text)
    {
        return $text;
    }

    protected function register_controls()
    {
        // General Settings Section
        $this->start_controls_section(
            'section_general',
            [
                'label' => __('General Settings', 'elementor-pro'),
                'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

        $this->add_control(
            'dropdown_location',
            [
                'label' => __('Dropdown Location', 'elementor-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'above_atc',
                'options' => [
                    'above_atc' => __('Above ATC Button', 'elementor-pro'),
                    'below_bundles' => __('Below Bundles', 'elementor-pro'),
                    'below_controller' => __('Below Controller', 'elementor-pro'),
                    'below_front_bench' => __('Below Front Bench', 'elementor-pro'),
                ],
            ]
        );

        $this->add_control(
            'show_quantity',
            [
                'label' => __('Show Quantity', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'label_off' => __('Hide', 'elementor-pro'),
                'label_on' => __('Show', 'elementor-pro'),
                'default' => 'no',
                'description' => __('Please note that switching on this option will disable some of the design controls.', 'elementor-pro'),
            ]
        );

        // Dispatch Date Settings
        $this->add_control(
            'dispatch_date_heading',
            [
                'label' => __('Dispatch Date', 'elementor-pro'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'dispatch_date_text',
            [
                'label' => __('Dispatch Date Text', 'elementor-pro'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Expected to dispatch on 30 Nov', 'elementor-pro'),
                'description' => __('Text to display below Add to Cart button. Leave empty to hide.', 'elementor-pro'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Layout & Ordering Controls
        $this->start_controls_section(
            'section_arrangement',
            [
                'label' => __('Layout & Ordering', 'elementor-pro'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Section Order Group
        $this->add_control(
            'section_order_heading',
            [
                'label' => __('Section Order', 'elementor-pro'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'controller_order',
            [
                'label' => __('Controller Order', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => '1',
                'options' => [
                    '1' => __('1st', 'elementor-pro'),
                    '2' => __('2nd', 'elementor-pro'),
                    '3' => __('3rd', 'elementor-pro'),
                ],
                'description' => __('Set the order in which the Controller section appears', 'elementor-pro'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'bundles_order',
            [
                'label' => __('Bundles Order', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => '2',
                'options' => [
                    '1' => __('1st', 'elementor-pro'),
                    '2' => __('2nd', 'elementor-pro'),
                    '3' => __('3rd', 'elementor-pro'),
                ],
                'description' => __('Set the order in which the Bundles section appears', 'elementor-pro'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'front_bench_order',
            [
                'label' => __('Front Bench Order', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => '3',
                'options' => [
                    '1' => __('1st', 'elementor-pro'),
                    '2' => __('2nd', 'elementor-pro'),
                    '3' => __('3rd', 'elementor-pro'),
                ],
                'description' => __('Set the order in which the Front Bench section appears', 'elementor-pro'),
                'label_block' => true,
            ]
        );

        // Options Order Group
        $this->add_control(
            'options_order_heading',
            [
                'label' => __('Options Order', 'elementor-pro'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'controller_options_order',
            [
                'label' => __('Controller Options Order', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => 'non-wireless,wireless-enabled',
                'options' => [
                    'non-wireless,wireless-enabled' => __('Non-Wireless → Wireless Enabled', 'elementor-pro'),
                    'wireless-enabled,non-wireless' => __('Wireless Enabled → Non-Wireless', 'elementor-pro'),
                ],
                'description' => __('Set the order of controller options within the Controller section', 'elementor-pro'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'bundles_options_order',
            [
                'label' => __('Bundles Options Order', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => 'grill-only,basic-bundle,pro-bundle',
                'options' => [
                    'grill-only,basic-bundle,pro-bundle' => __('Grill Only → Basic Bundle → Pro Bundle', 'elementor-pro'),
                    'grill-only,pro-bundle,basic-bundle' => __('Grill Only → Pro Bundle → Basic Bundle', 'elementor-pro'),
                    'basic-bundle,grill-only,pro-bundle' => __('Basic Bundle → Grill Only → Pro Bundle', 'elementor-pro'),
                    'basic-bundle,pro-bundle,grill-only' => __('Basic Bundle → Pro Bundle → Grill Only', 'elementor-pro'),
                    'pro-bundle,grill-only,basic-bundle' => __('Pro Bundle → Grill Only → Basic Bundle', 'elementor-pro'),
                    'pro-bundle,basic-bundle,grill-only' => __('Pro Bundle → Basic Bundle → Grill Only', 'elementor-pro'),
                ],
                'description' => __('Set the order of bundle options within the Bundles section', 'elementor-pro'),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'front_bench_options_order',
            [
                'label' => __('Front Bench Options Order', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => 'wood,stainless-steel',
                'label_block' => true,
                'options' => [
                    'wood,stainless-steel' => __('Wood → Stainless Steel', 'elementor-pro'),
                    'stainless-steel,wood' => __('Stainless Steel → Wood', 'elementor-pro'),
                ],
                'description' => __('Set the order of front bench options within the Front Bench section', 'elementor-pro'),
            ]
        );

        // Accordion Settings Group
        $this->add_control(
            'accordion_settings_heading',
            [
                'label' => __('Accordion Settings', 'elementor-pro'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'preview_text_limit',
            [
                'label' => __('Preview Text Character Limit', 'elementor-pro'),
                'type' => Controls_Manager::NUMBER,
                'default' => 100,
                'min' => 20,
                'max' => 500,
                'step' => 10,
                'description' => __('Maximum characters for accordion preview text. Text will be truncated with "..." if longer.', 'elementor-pro'),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Badge Controls
        $this->start_controls_section(
            'badge_controls',
            [
                'label' => __('Variant Badges', 'elementor'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        // Wireless Controller
        $this->add_control(
            'wireless_heading',
            [
                'label' => __('Wireless Controller', 'elementor'),
                'type' => Controls_Manager::HEADING,
            ]
        );

        // Wireless + Pro Bundle
        $this->add_control(
            'wireless_pro_badge_text',
            [
                'label' => __('Pro Bundle', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => __('Best Deal', 'elementor'),
                'label_block' => false,
            ]
        );

        // Wireless + Basic Bundle
        $this->add_control(
            'wireless_basic_badge_text',
            [
                'label' => __('Basic Bundle', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => __('', 'elementor'),
                'label_block' => false,
            ]
        );

        // Wireless + Grill Only
        $this->add_control(
            'wireless_grill_badge_text',
            [
                'label' => __('Grill Only', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => __('', 'elementor'),
                'label_block' => false,
            ]
        );

        // Non-Wireless Controller
        $this->add_control(
            'non_wireless_heading',
            [
                'label' => __('Non-Wireless Controller', 'elementor'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // Non-Wireless + Pro Bundle
        $this->add_control(
            'non_wireless_pro_badge_text',
            [
                'label' => __('Pro Bundle', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => __('', 'elementor'),
                'label_block' => false,
            ]
        );

        // Non-Wireless + Basic Bundle
        $this->add_control(
            'non_wireless_basic_badge_text',
            [
                'label' => __('Basic Bundle', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => __('', 'elementor'),
                'label_block' => false,
            ]
        );

        // Non-Wireless + Grill Only
        $this->add_control(
            'non_wireless_grill_badge_text',
            [
                'label' => __('Grill Only', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => __('', 'elementor'),
                'label_block' => false,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        global $product, $post;
        $settings = $this->get_settings_for_display();
        $settings['product_id'] = $post->ID;

        // Get product ID from current post or AJAX request
        if (!empty($settings['product_id'])) {
            $product_id = $settings['product_id'];
        } elseif (wp_doing_ajax()) {
            $product_id = absint($_POST['post_id'] ?? 0);
        } else {
            $product_id = get_queried_object_id();
        }
        $this->add_render_attribute('_wrapper', 'class', 'summary product elementor-widget-productvarianttilesv4');

        $product = wc_get_product($product_id);
        $this->render_form_button($product);
    }

    private function render_form_button($product)
    {
        $settings = $this->get_settings_for_display();

        if (!$product && current_user_can('manage_options')) {
            echo __('Please set a valid product', 'elementor-pro');

            return;
        }

        $text_callback = function () {
            ob_start();
            $this->render_text();

            return ob_get_clean();
        };

        add_filter('woocommerce_product_single_add_to_cart_text', $text_callback);

        add_filter('woocommerce_dropdown_variation_attribute_options_html', array($this, 'protiles_wvs_swatch_variable_item'), 60, 2);
        add_filter('esc_html', [$this, 'unescape_html'], 10, 2);

        add_filter('woocommerce_available_variation', array($this, 'vt_enrich_variation_payload'), 20, 3);
        add_filter("wvs_variable_items_wrapper", array($this, 'wvs_custom_variable_items_wrapper'), 10, 4);
        if ('yes' !== $settings['show_quantity']) {
            add_filter('woocommerce_is_sold_individually', array($this, 'pvt_remove_all_quantity_fields'), 10, 2);
        }

        // Add hook to render savings and accordion before ATC button
        add_action('woocommerce_before_add_to_cart_button', array($this, 'render_savings_and_accordion_hook'), 20);

        // Add arrangement filters
        $this->setup_arrangement_filters($settings);

        ob_start();

        woocommerce_template_single_add_to_cart();
        $form = ob_get_clean();
        $form = str_replace('single_add_to_cart_button', 'single_add_to_cart_button elementor-button', $form);
        echo $form;

        // Render dispatch date right after ATC button
        $this->render_dispatch_date();

        // Cleanup filters
        if ('yes' !== $settings['show_quantity']) {
            remove_filter('woocommerce_is_sold_individually', 'pvt_remove_all_quantity_fields');
        }

        remove_filter('woocommerce_available_variation', array($this, 'vt_enrich_variation_payload'), 20, 3);
        remove_filter('woocommerce_product_single_add_to_cart_text', $text_callback);
        remove_filter('esc_html', [$this, 'unescape_html']);

        // Remove arrangement filters
        remove_filter('woocommerce_product_get_attributes', array($this, 'reorder_product_attributes'), 10);
        remove_filter('woocommerce_product_get_terms', array($this, 'reorder_attribute_terms'), 10);
        remove_filter('woocommerce_dropdown_variation_attribute_options_args', array($this, 'reorder_dropdown_options'), 10);
        remove_filter('commercekit_as_get_attribute_terms', array($this, 'reorder_commercekit_terms'), 10);
        remove_filter('zg_reorder_attr_terms', array($this, 'reorder_attr_terms_for_swatches'), 10);
        remove_filter('commercekit_as_get_attribute_swatches_args', array($this, 'commercekit_default_selection'), 10);
    }

    function pvt_remove_all_quantity_fields($return, $product)
    {
        return true;
    }

            /**
     * Setup arrangement filters for sections and options
     */
    private function setup_arrangement_filters($settings) {
        // Store settings for use in filters
        $this->arrangement_settings = $settings;

        // Debug arrangement settings
        $this->debug_arrangement('Setup arrangement filters', $settings);

        // Validate arrangement settings to prevent conflicts
        $this->validate_arrangement_settings();

        // Add filters for section ordering
        add_filter('woocommerce_product_get_attributes', array($this, 'reorder_product_attributes'), 10, 2);

        // Add filters for option ordering within sections
        add_filter('woocommerce_product_get_terms', array($this, 'reorder_attribute_terms'), 10, 4);
        add_filter('woocommerce_dropdown_variation_attribute_options_args', array($this, 'reorder_dropdown_options'), 10, 1);
        add_filter('commercekit_as_get_attribute_terms', array($this, 'reorder_commercekit_terms'), 10, 3);
        add_filter('zg_reorder_attr_terms', array($this, 'reorder_attr_terms_for_swatches'), 10, 2);
        add_filter('commercekit_as_get_attribute_swatches_args', array($this, 'commercekit_default_selection'), 10, 1);
    }

    /**
     * Validate arrangement settings to prevent conflicts
     */
    private function validate_arrangement_settings() {
        if (empty($this->arrangement_settings)) {
            return;
        }

        $bundles_order = isset($this->arrangement_settings['bundles_order']) ? (int)$this->arrangement_settings['bundles_order'] : 2;
        $controller_order = isset($this->arrangement_settings['controller_order']) ? (int)$this->arrangement_settings['controller_order'] : 1;
        $front_bench_order = isset($this->arrangement_settings['front_bench_order']) ? (int)$this->arrangement_settings['front_bench_order'] : 3;

        // Check for conflicts
        $orders = array($bundles_order, $controller_order, $front_bench_order);
        $unique_orders = array_unique($orders);

        if (count($orders) !== count($unique_orders)) {
            // Reset to default if there are conflicts
            $this->arrangement_settings['bundles_order'] = '2';
            $this->arrangement_settings['controller_order'] = '1';
            $this->arrangement_settings['front_bench_order'] = '3';
        }
    }

        /**
     * Reorder product attributes based on arrangement settings
     */
    public function reorder_product_attributes($attributes, $product) {
        if (empty($this->arrangement_settings) || empty($attributes)) {
            return $attributes;
        }

        $bundles_order = isset($this->arrangement_settings['bundles_order']) ? (int)$this->arrangement_settings['bundles_order'] : 2;
        $controller_order = isset($this->arrangement_settings['controller_order']) ? (int)$this->arrangement_settings['controller_order'] : 1;
        $front_bench_order = isset($this->arrangement_settings['front_bench_order']) ? (int)$this->arrangement_settings['front_bench_order'] : 3;

        // Create order mapping
        $order_mapping = array();
        $found_attributes = array();

        // Find bundles attribute
        foreach ($attributes as $key => $attribute) {
            if (strpos($attribute->get_name(), 'bundles') !== false) {
                $order_mapping[$bundles_order] = $key;
                $found_attributes[] = 'bundles';
                break;
            }
        }

        // Find controller attribute
        foreach ($attributes as $key => $attribute) {
            if (strpos($attribute->get_name(), 'controller') !== false) {
                $order_mapping[$controller_order] = $key;
                $found_attributes[] = 'controller';
                break;
            }
        }

        // Find front bench attribute
        foreach ($attributes as $key => $attribute) {
            if (strpos($attribute->get_name(), 'front-bench') !== false) {
                $order_mapping[$front_bench_order] = $key;
                $found_attributes[] = 'front-bench';
                break;
            }
        }

        // If no target attributes found, return original attributes
        if (empty($found_attributes)) {
            $this->debug_arrangement('No target attributes found for reordering');
            return $attributes;
        }

        $this->debug_arrangement('Found attributes for reordering', $found_attributes);

        // Reorder attributes
        $reordered_attributes = array();
        for ($i = 1; $i <= 3; $i++) {
            if (isset($order_mapping[$i])) {
                $reordered_attributes[] = $attributes[$order_mapping[$i]];
            }
        }

        // Add any remaining attributes
        foreach ($attributes as $key => $attribute) {
            if (!in_array($key, $order_mapping)) {
                $reordered_attributes[] = $attribute;
            }
        }

        return $reordered_attributes;
    }

    /**
     * Debug method to log arrangement information
     */
    private function debug_arrangement($message, $data = null) {
        // Debug function removed for production
    }

    /**
     * Truncate text to specified character limit with ellipsis
     */
    private function truncate_text($text, $limit = 100) {
        if (empty($text)) {
            return $text;
        }

        $text = strip_tags($text);

        if (strlen($text) <= $limit) {
            return $text;
        }

        return substr($text, 0, $limit) . '...';
    }

    /**
     * Reorder attribute terms based on arrangement settings
     */
    public function reorder_attribute_terms($terms, $product, $taxonomy, $args) {
        if (empty($this->arrangement_settings) || empty($terms)) {
            return $terms;
        }

        $taxonomy_name = str_replace('pa_', '', $taxonomy);

        // Handle bundles options
        if (strpos($taxonomy_name, 'bundles') !== false) {
            $bundles_order = isset($this->arrangement_settings['bundles_options_order']) ? $this->arrangement_settings['bundles_options_order'] : 'grill-only,basic-bundle,pro-bundle';
            return $this->reorder_terms_by_slugs($terms, $bundles_order);
        }

        // Handle controller options
        if (strpos($taxonomy_name, 'controller') !== false) {
            $controller_order = isset($this->arrangement_settings['controller_options_order']) ? $this->arrangement_settings['controller_options_order'] : 'non-wireless,wireless-enabled';
            return $this->reorder_terms_by_slugs($terms, $controller_order);
        }

        // Handle front bench options
        if (strpos($taxonomy_name, 'front-bench') !== false) {
            $front_bench_order = isset($this->arrangement_settings['front_bench_options_order']) ? $this->arrangement_settings['front_bench_options_order'] : 'wood,stainless-steel';
            return $this->reorder_terms_by_slugs($terms, $front_bench_order);
        }

        return $terms;
    }

    /**
     * Reorder attribute terms specifically for swatch generation
     */
    public function reorder_attr_terms_for_swatches($attr_terms, $args) {
        if (empty($this->arrangement_settings) || empty($attr_terms)) {
            return $attr_terms;
        }

        $attribute_name = $args['attribute'];

        // Handle bundles options
        if (strpos($attribute_name, 'bundles') !== false) {
            $bundles_order = isset($this->arrangement_settings['bundles_options_order']) ? $this->arrangement_settings['bundles_options_order'] : 'grill-only,basic-bundle,pro-bundle';
            return $this->reorder_attr_terms_by_slugs($attr_terms, $bundles_order);
        }

        // Handle controller options
        if (strpos($attribute_name, 'controller') !== false) {
            $controller_order = isset($this->arrangement_settings['controller_options_order']) ? $this->arrangement_settings['controller_options_order'] : 'non-wireless,wireless-enabled';
            return $this->reorder_attr_terms_by_slugs($attr_terms, $controller_order);
        }

        // Handle front bench options
        if (strpos($attribute_name, 'front-bench') !== false) {
            $front_bench_order = isset($this->arrangement_settings['front_bench_options_order']) ? $this->arrangement_settings['front_bench_options_order'] : 'wood,stainless-steel';
            return $this->reorder_attr_terms_by_slugs($attr_terms, $front_bench_order);
        }

        return $attr_terms;
    }

    /**
     * Helper method to reorder attribute terms by their slugs
     */
    private function reorder_attr_terms_by_slugs($attr_terms, $order_string) {
        $desired_order = explode(',', $order_string);
        $reordered_terms = array();

        // First, add terms in the desired order
        foreach ($desired_order as $slug) {
            foreach ($attr_terms as $term) {
                if ($term->slug === $slug) {
                    $reordered_terms[] = $term;
                    break;
                }
            }
        }

        // Then add any remaining terms that weren't in the order string
        foreach ($attr_terms as $term) {
            if (!in_array($term->slug, $desired_order)) {
                $reordered_terms[] = $term;
            }
        }

        return $reordered_terms;
    }

    /**
     * Helper method to reorder terms by their slugs
     */
    private function reorder_terms_by_slugs($terms, $order_string) {
        $desired_order = explode(',', $order_string);
        $reordered_terms = array();

        // First, add terms in the desired order
        foreach ($desired_order as $slug) {
            foreach ($terms as $term) {
                if ($term->slug === $slug) {
                    $reordered_terms[] = $term;
                    break;
                }
            }
        }

        // Then add any remaining terms that weren't in the order string
        foreach ($terms as $term) {
            if (!in_array($term->slug, $desired_order)) {
                $reordered_terms[] = $term;
            }
        }

        return $reordered_terms;
    }

    /**
     * Reorder dropdown options based on arrangement settings
     */
    public function reorder_dropdown_options($args) {
        if (empty($this->arrangement_settings) || empty($args['options'])) {
            return $args;
        }

        $attribute_name = $args['attribute'];

        // Handle bundles options
        if (strpos($attribute_name, 'bundles') !== false) {
            $bundles_order = isset($this->arrangement_settings['bundles_options_order']) ? $this->arrangement_settings['bundles_options_order'] : 'grill-only,basic-bundle,pro-bundle';
            $args['options'] = $this->reorder_options_array($args['options'], $bundles_order);
        }

        // Handle controller options
        if (strpos($attribute_name, 'controller') !== false) {
            $controller_order = isset($this->arrangement_settings['controller_options_order']) ? $this->arrangement_settings['controller_options_order'] : 'non-wireless,wireless-enabled';
            $args['options'] = $this->reorder_options_array($args['options'], $controller_order);
        }

        // Handle front bench options
        if (strpos($attribute_name, 'front-bench') !== false) {
            $front_bench_order = isset($this->arrangement_settings['front_bench_options_order']) ? $this->arrangement_settings['front_bench_options_order'] : 'wood,stainless-steel';
            $args['options'] = $this->reorder_options_array($args['options'], $front_bench_order);
        }

        return $args;
    }

    /**
     * Helper method to reorder options array
     */
    private function reorder_options_array($options, $order_string) {
        $desired_order = explode(',', $order_string);
        $reordered_options = array();

        // First, add options in the desired order
        foreach ($desired_order as $option) {
            if (in_array($option, $options)) {
                $reordered_options[] = $option;
            }
        }

        // Then add any remaining options that weren't in the order string
        foreach ($options as $option) {
            if (!in_array($option, $desired_order)) {
                $reordered_options[] = $option;
            }
        }

        return $reordered_options;
    }

    /**
     * Reorder CommerceKit attribute terms
     */
    public function reorder_commercekit_terms($terms, $product, $attribute_name) {
        if (empty($this->arrangement_settings) || empty($terms)) {
            return $terms;
        }

        // Handle bundles options
        if (strpos($attribute_name, 'bundles') !== false) {
            $bundles_order = isset($this->arrangement_settings['bundles_options_order']) ? $this->arrangement_settings['bundles_options_order'] : 'grill-only,basic-bundle,pro-bundle';
            return $this->reorder_terms_by_slugs($terms, $bundles_order);
        }

        // Handle controller options
        if (strpos($attribute_name, 'controller') !== false) {
            $controller_order = isset($this->arrangement_settings['controller_options_order']) ? $this->arrangement_settings['controller_options_order'] : 'non-wireless,wireless-enabled';
            return $this->reorder_terms_by_slugs($terms, $controller_order);
        }

        // Handle front bench options
        if (strpos($attribute_name, 'front-bench') !== false) {
            $front_bench_order = isset($this->arrangement_settings['front_bench_options_order']) ? $this->arrangement_settings['front_bench_options_order'] : 'wood,stainless-steel';
            return $this->reorder_terms_by_slugs($terms, $front_bench_order);
        }

        return $terms;
    }



    function wvs_custom_variable_items_wrapper($data, $contents, $type, $args, $saved_attribute = array())
    {

        $attribute = $args['attribute'];
        $options   = $args['options'];
        return $data = sprintf('<ul role="radiogroup" class="variable-items-wrapper button-variable-wrapper variable_items_tiles off" data-attribute_name="%1$s" data-attribute_values="%2$s">%3$s</ul>',  esc_attr(wc_variation_attribute_name($attribute)), wc_esc_json(wp_json_encode(array_values($options))), $contents);
    }



    function variation_price_preffix($variation_data, $product, $variation)
    {
        $settings = $this->get_settings_for_display();
        $variation_data['price_html'] = '<span class="price-preffix">' . __($this->get_settings('total_price_prefix_text'), "woocommerce") . '</span> ' . $variation_data['price_html'];

        return $variation_data;
    }


    /**
     * Render button text.
     *
     * Render button widget text.
     *
     * @since 1.5.0
     * @access protected
     */
    protected function render_text()
    {
        // Use uniform "Add to Cart" text for all products
        $button_text = __('Add to Cart', 'woocommerce');

        $this->add_render_attribute([
            'content-wrapper' => [
                'class' => 'elementor-button-content-wrapper',
            ],
            'text' => [
                'class' => 'elementor-button-text',
            ],
        ]);

        $this->add_inline_editing_attributes('text', 'none');
        ?>
        <span <?php echo $this->get_render_attribute_string('content-wrapper'); ?>>
            <span <?php echo $this->get_render_attribute_string('text'); ?>><?php echo $button_text; ?></span>
            <span class="vt-button-price"></span>
        </span>
    <?php
    }

            function protiles_wvs_swatch_variable_item($html, $args)
    {
        global $product, $cgkit_as_caching;

                // Function is now working correctly

        if (commercegurus_as_is_wc_composite_product()) {
            return $html;
        }

        if (!$product || (method_exists($product, 'is_type') && !$product->is_type('variable'))) {
            return $html;
        }

        if (empty($args['options'])) {
            return $html;
        }

        $arg_product = isset($args['product']) ? $args['product'] : $product;
        $product_id  = $arg_product->get_id();

        $commercekit_options = get_option('commercekit', array());
        $attribute_swatches  = $arg_product->get_meta('commercekit_attribute_swatches', true);
        if (!is_array($attribute_swatches)) {
            $attribute_swatches = array();
        }
        $as_enabled = isset($commercekit_options['attribute_swatches']) && 1 === (int) $commercekit_options['attribute_swatches'] ? true : false;
        if ($as_enabled && isset($attribute_swatches['enable_product']) && 0 === (int) $attribute_swatches['enable_product']) {
            return $html;
        }

        if (!$as_enabled && (!isset($cgkit_as_caching) || false === $cgkit_as_caching)) {
            return $html;
        }
        $as_enabled_pdp = isset($commercekit_options['attribute_swatches_pdp']) && 0 === (int) $commercekit_options['attribute_swatches_pdp'] ? false : true;
        if (!$as_enabled_pdp && (!isset($cgkit_as_caching) || false === $cgkit_as_caching)) {
            return $html;
        }

        $attribute_raw  = sanitize_title($args['attribute']);
        $attribute_name = commercekit_as_get_attribute_slug($attribute_raw, true);

        $is_taxonomy = true;
        $attr_terms  = wc_get_product_terms(
            $product->get_id(),
            $args['attribute'],
            array(
                'fields' => 'all',
            )
        );
        if (!count($attr_terms)) {
            $_options = $args['options'];
            if (count($_options)) {
                $is_taxonomy = false;
                foreach ($_options as $_option) {
                    $attr_terms[] = (object) array(
                        'name'    => $_option,
                        'slug'    => sanitize_title($_option),
                        'term_id' => $_option,
                    );
                }
            }
        }
        if (!count($attr_terms)) {
            return $html;
        }

        $attribute_id = $is_taxonomy ? wc_attribute_taxonomy_id_by_name($args['attribute']) : sanitize_title($args['attribute']);
        $swatch_type  = isset($attribute_swatches[$attribute_id]['cgkit_type']) ? $attribute_swatches[$attribute_id]['cgkit_type'] : 'button';

        // Force image type for bundles attribute to ensure images are displayed
        if (strpos($args['attribute'], 'bundles') !== false) {
            $swatch_type = 'image';
        }

        if (empty($swatch_type)) {
            return $html;
        }
        $as_quickadd_txt = isset($commercekit_options['as_quickadd_txt']) && !empty($commercekit_options['as_quickadd_txt']) ? commercekit_get_multilingual_string(stripslashes_deep($commercekit_options['as_quickadd_txt'])) : commercekit_get_default_settings('as_quickadd_txt');
        $as_more_opt_txt = isset($commercekit_options['as_more_opt_txt']) && !empty($commercekit_options['as_more_opt_txt']) ? commercekit_get_multilingual_string(stripslashes_deep($commercekit_options['as_more_opt_txt'])) : commercekit_get_default_settings('as_more_opt_txt');
        $as_activate_atc = isset($commercekit_options['as_activate_atc']) && 1 === (int) $commercekit_options['as_activate_atc'] ? true : false;
        $as_button_style = isset($commercekit_options['as_button_style']) && 1 === (int) $commercekit_options['as_button_style'] ? true : false;
        $attr_count      = isset($args['attr_count']) ? (int) $args['attr_count'] : 2;
        $attr_index      = isset($args['attr_index']) ? (int) $args['attr_index'] : 1;
        if (2 < $attr_count || !$as_activate_atc) {
            $as_quickadd_txt = $as_more_opt_txt;
        }

        $single_attribute = false;

        $_variations = array();
        $_variations = array();
        $_var_images = array();
        $_gal_images = array();
        $any_attrib  = false;
        // Use CommerceKit's variation handling for consistent data structure and caching
        $variations  = commercekit_get_available_variations($product);




        $default_attributes = $product->get_default_attributes();






        if (is_array($variations) && count($variations)) {
            foreach ($variations as $id => $variation) {
                if (isset($variation['attributes']) && count($variation['attributes'])) {
                    // Use CommerceKit's variation image (same as old plugin)
                    $variation_obj = wc_get_product($variation['variation_id']);
                    $variation_img_id = isset($variation['cgkit_image_id']) ? $variation['cgkit_image_id'] : ($variation_obj ? $variation_obj->get_image_id() : 0);
                    foreach ($variation['attributes'] as $a_key => $a_value) {
                        $a_key = str_ireplace('attribute_', '', $a_key);

                        $_variations[$a_key][] = $a_value;
                        $_variations[$a_key][$a_value]['variation'] = $variation;
                        // Store the variation image ID for this attribute value (same as old plugin)
                        if ($variation_img_id) {
                            $_variations[$a_key][$a_value]['variation']['cgkit_image_id'] = $variation_img_id;
                        }
                        // $_variations[$a_key][$a_value]['variation']['raw_badge'] = get_post_meta($variation['variation_id'], 'price_difference_badge', true);;
                        if ($variation_img_id) {
                            $_var_images[$a_key][$a_value] = $variation_img_id;
                        }
                        if ('' === $a_value) {
                            $any_attrib = true;
                        } else {
                            if (1 === count($variation['attributes'])) {
                                $single_attribute = true;
                            }
                        }
                    }
                }
            }
            $cgkit_image_gallery = $arg_product->get_meta('commercekit_image_gallery', true);
            if (is_array($cgkit_image_gallery)) {
                $cgkit_image_gallery = array_filter($cgkit_image_gallery);
            }
            if (is_array($cgkit_image_gallery) && count($cgkit_image_gallery)) {
                foreach ($cgkit_image_gallery as $slug => $image_gallery) {
                    if ('global_gallery' === $slug) {
                        continue;
                    }
                    $images = explode(',', trim($image_gallery));
                    if (isset($images[0]) && !empty($images[0])) {
                        $slugs = explode('_cgkit_', $slug);
                        if (count($slugs)) {
                            foreach ($slugs as $slg) {
                                $_gal_images[$slg] = $images[0];
                            }
                        }
                    }
                }
            }
        } else {
            return $html;
        }
        $attribute_css  = isset($args['css_class']) && !empty($args['css_class']) ? $args['css_class'] : 'cgkit-as-wrap';
        $item_class     = '';
        $item_wrp_class = '';
        $before_swatches = esc_html(do_action('before_product_swatch_' . $attribute_name, ''));

        // Apply arrangement filter to attr_terms
        $attr_terms = apply_filters('zg_reorder_attr_terms', $attr_terms, $args);

        $swatches_html  = sprintf('<div class="%s">%s<span class="cgkit-swatch-title">%s</span><ul class="cgkit-attribute-swatches %s %s" data-attribute="%s" data-no-selection="%s">', $attribute_css, $before_swatches, $as_quickadd_txt, ('swatch_' . $swatch_type), $item_wrp_class, $attribute_name, esc_html__('No selection', 'commercegurus-commercekit'));
        foreach ($attr_terms as $item) {
            // Processing bundle attributes
            if (!isset($attribute_swatches[$attribute_id])) {
                $attribute_swatches[$attribute_id] = array();
            }
            if (!isset($attribute_swatches[$attribute_id][$item->term_id])) {
                $attribute_swatches[$attribute_id][$item->term_id]['btn'] = $item->name;
            }
            if ($is_taxonomy && !in_array($item->slug, $args['options'], true)) {
                continue;
            }
            if ($is_taxonomy) {
                if (!$any_attrib && (!isset($_variations[$attribute_raw]) || !in_array($item->slug, $_variations[$attribute_raw], true))) {
                    continue;
                }
            } else {
                if (!$any_attrib && (!isset($_variations[$attribute_raw]) || !in_array($item->name, $_variations[$attribute_raw], true))) {
                    continue;
                }
            }

            $item_attri_val = $is_taxonomy ? $item->slug : $item->name;

            $custom_selected = false;
            // Check if this attribute value is a default for the current attribute
            $current_attribute_name = $args['attribute'];
            if (isset($default_attributes[$current_attribute_name]) && $item_attri_val == $default_attributes[$current_attribute_name]) {
                $custom_selected = true;
            }

            // Additional check: if no default is set, select the first option
            if (!$custom_selected && !isset($args['selected']) && $item === $attr_terms[0]) {
                $custom_selected = true;
            }

            // $is_selected = (isset($args['selected']) && sanitize_title( $args['selected'] ) == $item_attri_val ) ? true : false;

            $selected       = (($args['selected'] === $item_attri_val) || $custom_selected )? 'cgkit-swatch-selected' : '';
            if ($as_button_style && 'button' === $swatch_type) {
                $selected .= ' button-fluid';
            }

            $image_label = esc_html(apply_filters('woocommerce_variation_option_name', $item->name));

            // Bundle names are being generated correctly

            $swatch_html = '';
                                                // Generate basic swatch HTML independently (bypass CommerceKit)
            $swatch_html = $this->generate_swatch_html_independent($swatch_type, $attribute_swatches[$attribute_id][$item->term_id], $item, $image_label);





            // Handle variation image override (restored for proper image display)
            if ('image' === $swatch_type && isset($_variations[$attribute_raw][$item_attri_val]['variation']['cgkit_image_id'])) {
                $var_img_id = $_variations[$attribute_raw][$item_attri_val]['variation']['cgkit_image_id'];
                if ($var_img_id) {
                    $var_image = wp_get_attachment_image_src($var_img_id, 'woocommerce_thumbnail');
                    if ($var_image) {
                        // Extract existing title and price
                        $tile_title_html = '';
                        $tile_price_html = '';

                        if (preg_match('/<span class="tile-title">(.*?)<\/span>/', $swatch_html, $matches)) {
                            $tile_title_html = $matches[0];
                        }
                        if (preg_match('/<span class="tile-price">(.*?)<\/span>/', $swatch_html, $matches)) {
                            $tile_price_html = $matches[0];
                        }

                        // Rebuild swatch HTML with variation image but preserve title and price
                        $swatch_html = '<img alt="' . esc_attr($item->name) . '" width="' . esc_attr($var_image[1]) . '" height="' . esc_attr($var_image[2]) . '" src="' . esc_url($var_image[0]) . '" />' . $tile_title_html . $tile_price_html;
                    }
                }
            }

            // RENDER BADGE USING ELEMENTOR SETTINGS
            $swatch_html .= $this->render_tile_badge($attribute_raw, $item_attri_val, $_variations);

            // VARIABLE PRODUCT PRICE DISPLAY - EXACTLY LIKE TILES-OLD (which works perfectly)
            if(isset($_variations[$attribute_raw][$item_attri_val]['variation']['price_html']) && $swatch_type == 'image'){
                $swatch_html .= '<span class="tile-price">' . $_variations[$attribute_raw][$item_attri_val]['variation']['price_html'] . '</span>';
            }

            $item_title  = 'button' === $swatch_type && isset($attribute_swatches[$attribute_id][$item->term_id]['btn']) ? $attribute_swatches[$attribute_id][$item->term_id]['btn'] : $item->name;
            if ($single_attribute) {
                $selected .= ' cgkit-as-single';
            }
            $item_tooltip = '';
            if (in_array($swatch_type, array('color', 'image'), true)) {
                $item_tooltip = ' cgkit-tooltip="' . $item_title . '"';
            }
            $gal_img_slug   = is_numeric($item->term_id) ? $item->term_id : sanitize_title($item->term_id);
            $item_gimg_id   = isset($_gal_images[$gal_img_slug]) ? $_gal_images[$gal_img_slug] : '';


            // $raw_badge = isset($_variations[$attribute_raw][$item_attri_val]['variation']['raw_badge']) ? '<br>' . $_variations[$attribute_raw][$item_attri_val]['variation']['raw_badge'] : '';
            $swatches_html .= sprintf('<li class="cgkit-attribute-swatch cgkit-%s %s" %s data-variation-id="%s"><button type="button" data-type="%s" data-attribute-value="%s" data-attribute-text="%s" aria-label="%s" data-oos-text="" title="%s" class="swatch cgkit-swatch %s" data-clicker="%s" data-gimg_id="%s">%s<span class="raw-badge"></span></button></li>', $swatch_type, $item_class, $item_tooltip, $_variations[$attribute_raw][$item_attri_val]['variation']['variation_id'], $swatch_type, esc_attr($item_attri_val), esc_attr($item->name), esc_attr($item_title), esc_attr($item_title), $selected, $selected, $item_gimg_id, $swatch_html);



            // Final HTML structure generated
        }
        $swatches_html .= '</ul>';

        $swatch_html .= esc_html(do_action('zg_after_swatch_ul', esc_attr($item_attri_val), $attribute_name));
        $swatch_html .='</div>';
        $swatch_html .= '<div class="clearfix"></div>';
        $swatches_html .= '<section class="clearfix">' . do_action('after_product_swatch_' . $attribute_name) . '</section>';

        // Accordion positioning based on dropdown_location setting
        $dropdown_location = 'above_atc'; // Default fallback
        try {
            $settings = $this->get_settings_for_display();
            $dropdown_location = isset($settings['dropdown_location']) ? $settings['dropdown_location'] : 'above_atc';
        } catch (Exception $e) {
            // Fallback to default if settings not available
        }

        // Only render accordion in swatches if it's set to appear below specific attributes
        if (in_array($dropdown_location, ['below_bundles', 'below_controller', 'below_front_bench'])) {
            // Check if this is the correct attribute to render accordion after
            $should_render_accordion = false;

            if ($dropdown_location === 'below_bundles' && strpos($args['attribute'], 'bundles') !== false) {
                $should_render_accordion = true;
            } elseif ($dropdown_location === 'below_controller' && strpos($args['attribute'], 'controller') !== false) {
                $should_render_accordion = true;
            } elseif ($dropdown_location === 'below_front_bench' && strpos($args['attribute'], 'front-bench') !== false) {
                $should_render_accordion = true;
            }

            if ($should_render_accordion) {
                // Get default variation data for accordion
                global $product;
                $default_variation_data = $this->get_default_variation_data($product);

                // Render accordion after this attribute
                $swatches_html .= '<div class="zg-accordion-positioned">';

                // Capture accordion output
                ob_start();
                $this->render_accordion($default_variation_data);
                $accordion_html = ob_get_clean();

                $swatches_html .= $accordion_html;
                $swatches_html .= '</div>';
            }
        }

        if (isset($args['css_class']) && 'cgkit-as-wrap-plp' === $args['css_class']) {
            $html = str_ireplace(' id="', ' data-id="', $html);
        }
        $swatches_html .= sprintf('<div style="display: none;">%s</div>', $html);
        // $swatches_html .= '<section class="clearfix">' . do_action('after_product_swatch_' . $attribute_name) . '</section>';

        return $swatches_html;
    }

        /**
     * Generate swatch HTML independently (bypass CommerceKit)
     *
     * @param string $swatch_type type of swatch.
     * @param string $data data of attribute.
     * @param string $item data of term.
     * @param string $image_label label for image.
     */
    function generate_swatch_html_independent($swatch_type, $data, $item, $image_label = '')
    {
        $swatch_html = '';
        if ('image' === $swatch_type) {
            $image = null;
            // Try to get image from CommerceKit data first, then fallback to term meta
            if (isset($data['img']) && !empty($data['img'])) {
                $cgkit_image_swatch = 'woocommerce_thumbnail';

                // Only call CommerceKit function if it exists
                if (function_exists('commercekit_as_generate_attachment_size')) {
                    commercekit_as_generate_attachment_size($data['img'], $cgkit_image_swatch);
                }
                $image = wp_get_attachment_image_src($data['img'], $cgkit_image_swatch);
            } else {
                // Fallback to term meta image if CommerceKit image not set
                $term_img_id = get_term_meta($item->term_id, 'product_attribute_image', true);
                if ($term_img_id) {
                    $image = wp_get_attachment_image_src($term_img_id, 'woocommerce_thumbnail');
                }
            }
            if ($image) {
                $swatch_html = '<img alt="' . esc_attr($item->name) . '" width="' . esc_attr($image[1]) . '" height="' . esc_attr($image[2]) . '" src="' . esc_url($image[0]) . '" />' . ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            } else {
                // For bundle swatches, still show the title even if no image data
                $swatch_html = ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            }

        } elseif ('color' === $swatch_type) {
            if (isset($data['clr']) && !empty($data['clr'])) {
                $bg_color2  = isset($data['clr2']) ? $data['clr2'] : '';
                $bg_type    = isset($data['ctyp']) ? (int) $data['ctyp'] : 1;
                $background = $data['clr'];
                if (2 === $bg_type && !empty($bg_color2)) {
                    $background = 'linear-gradient(135deg, ' . $data['clr'] . ' 50%, ' . $bg_color2 . ' 50%)';
                }
                $swatch_html = '<span class="color-div" style="background: ' . esc_attr($background) . ';" data-color="' . esc_attr($data['clr']) . '" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            } else {
                $swatch_html = '<span class="color-div" style="" data-color="" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            }
        } elseif ('button' === $swatch_type) {
            if (isset($data['btn']) && strlen($data['btn'])) {
                $swatch_html = esc_attr($data['btn']);
            } else {
                $swatch_html = esc_attr($item->name);
            }
        }

        return $swatch_html;
    }

    /**
     * Attribute swatches get swatch html.
     *
     * @param string $swatch_type type of swatch.
     * @param string $data data of attribute.
     * @param string $item data of term.
     */
    function zg_commercekit_as_get_swatch_html($swatch_type, $data, $item, $image_label = '')
    {
        $swatch_html = '';
        if ('image' === $swatch_type) {
            $image = null;
            // Try to get image from CommerceKit data first, then fallback to term meta
            if (isset($data['img']) && !empty($data['img'])) {
                $cgkit_image_swatch = 'woocommerce_thumbnail'; //commercekit_as_get_image_swatch_size();

                commercekit_as_generate_attachment_size($data['img'], $cgkit_image_swatch);
                $image = wp_get_attachment_image_src($data['img'], $cgkit_image_swatch);
            } else {
                // Fallback to term meta image if CommerceKit image not set
                $term_img_id = get_term_meta($item->term_id, 'product_attribute_image', true);
                if ($term_img_id) {
                    $image = wp_get_attachment_image_src($term_img_id, 'woocommerce_thumbnail');
                }
            }
            if ($image) {
                $swatch_html = '<img alt="' . esc_attr($item->name) . '" width="' . esc_attr($image[1]) . '" height="' . esc_attr($image[2]) . '" src="' . esc_url($image[0]) . '" />' . ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            } else {
                // For bundle swatches, still show the title even if no image data
                $swatch_html = ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            }

        } elseif ('color' === $swatch_type) {
            if (isset($data['clr']) && !empty($data['clr'])) {
                $bg_color2  = isset($data['clr2']) ? $data['clr2'] : '';
                $bg_type    = isset($data['ctyp']) ? (int) $data['ctyp'] : 1;
                $background = $data['clr'];
                if (2 === $bg_type && !empty($bg_color2)) {
                    $background = 'linear-gradient(135deg, ' . $data['clr'] . ' 50%, ' . $bg_color2 . ' 50%)';
                }
                $swatch_html = '<span class="color-div" style="background: ' . esc_attr($background) . ';" data-color="' . esc_attr($data['clr']) . '" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            } else {
                $swatch_html = '<span class="color-div" style="" data-color="" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            }
        } elseif ('button' === $swatch_type) {
            if (isset($data['btn']) && strlen($data['btn'])) {
                $swatch_html = '' . esc_attr($data['btn']);
            } else {
                $swatch_html = '' . esc_attr($item->name);
            }
        }

        return $swatch_html;
    }



    public function vt_enrich_variation_payload($variation_data, $product, $variation){
        // Attach custom meta for accordion - only the fields actually used in JavaScript
        $dd_text = $variation->get_meta('_vt_dd_text', true);
        $dd_preview = $variation->get_meta('_vt_dd_preview', true);

        $variation_data['_vt_dd_text'] = $dd_text;
        $variation_data['_vt_dd_preview'] = $dd_preview;

        // Debug: Log what the widget is receiving
        error_log( 'VT Widget Debug - Variation ' . $variation->get_id() . ' _vt_dd_text: ' . substr($dd_text, 0, 100) );
        error_log( 'VT Widget Debug - Variation ' . $variation->get_id() . ' _vt_dd_preview: ' . substr($dd_preview, 0, 100) );

        // Add variant tile image data
        $dd_image_id = $variation->get_meta('_vt_dd_image_id', true);
        if ($dd_image_id && wp_attachment_is_image($dd_image_id)) {
            $variation_data['_vt_dd_image_url'] = wp_get_attachment_image_url($dd_image_id, 'large');
        } else {
            $variation_data['_vt_dd_image_url'] = '';
        }

        // Add image data for swatch updates
        $image_id = $variation->get_image_id();
        if ($image_id) {
            $image = wp_get_attachment_image_src($image_id, 'woocommerce_thumbnail');
            if ($image) {
                $variation_data['image'] = array(
                    'src' => $image[0],
                    'width' => $image[1],
                    'height' => $image[2],
                    'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true)
                );
            }
        }

        return $variation_data;
    }

    /**
     * Standardize price HTML to prevent layout shifts
     */
    private function standardize_price_html($price_html) {
        // Ensure consistent price structure
        $price_html = str_replace('<span class="price">', '<span class="price" style="display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis;">', $price_html);

        // Ensure del and ins elements are properly contained
        $price_html = str_replace('<del>', '<del style="display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis;">', $price_html);
        $price_html = str_replace('<ins>', '<ins style="display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis;">', $price_html);

        return $price_html;
    }

            /**
     * Hook method to render savings and accordion before ATC button
     */
    public function render_savings_and_accordion_hook() {
        global $product;
        if (!$product || !is_object($product)) {
            return;
        }

        // Only show on frontend, not in Elementor editor
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return;
        }

        $this->render_savings_and_accordion($product);
    }

    /**
     * Render savings and accordion section
     */
    private function render_savings_and_accordion($product) {
        if (!$product || !is_object($product)) {
            return;
        }

        // Get default variation data for initial display
        $default_variation_data = $this->get_default_variation_data($product);

        // Get settings for dropdown location
        $settings = $this->get_settings_for_display();

        ?>
        <div class="zg-product-savings-section" data-product-id="<?php echo esc_attr($product->get_id()); ?>">

        <?php
        // Render accordion based on dropdown location setting
        $dropdown_location = isset($settings['dropdown_location']) ? $settings['dropdown_location'] : 'above_atc';

        if ($dropdown_location === 'above_atc') {
            $this->render_accordion($default_variation_data);
        }
        ?>

        <script type="text/javascript">
            // Pass settings to JavaScript
            var zgAccordionSettings = {
                previewTextLimit: <?php echo isset($settings['preview_text_limit']) ? (int)$settings['preview_text_limit'] : 100; ?>
            };
        </script>

                                    <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Only run on product pages
                if (!$('body').hasClass('single-product') && !$('body').hasClass('woocommerce-page')) {
                    return;
                }

                // Accordion toggle with excerpt/description switching
                $('.zg-accordion-header').on('click', function() {
                    var $header = $(this);
                    var $excerpt = $header.next('.zg-accordion-excerpt');
                    var $content = $excerpt.next('.zg-accordion-content');
                    var $icon = $header.find('.zg-accordion-icon');

                    if ($content.is(':visible')) {
                        // Collapse: hide full content, show excerpt
                        $content.slideUp(300);
                        $excerpt.slideDown(300);
                        $icon.removeClass('rotated');
                    } else {
                        // Expand: hide excerpt, show full content
                        $excerpt.slideUp(300);
                        $content.slideDown(300);
                        $icon.addClass('rotated');
                    }
                });

                // Handle variation changes to update accordion content
                var lastVariationId = null;
                $(document.body).on('found_variation', function(event, variation) {
                    // Prevent duplicate processing of the same variation
                    if (lastVariationId === variation.variation_id) {
                        return;
                    }

                    lastVariationId = variation.variation_id;
                    var currentControllerValue = $('select[name="attribute_pa_controller"]').val();
                    var bundleValue = $('select[name="attribute_pa_bundles"]').val();

                    // CRITICAL: Store controller in persistent memory during variation found
                    if (currentControllerValue && currentControllerValue !== '') {
                        window.persistentControllerSelection = currentControllerValue;
                    }

                    // CRITICAL: Store bundle in persistent memory during variation found
                    if (bundleValue && bundleValue !== '') {
                        window.persistentBundleSelection = bundleValue;
                    }
                    updateAccordionContent(variation);
                    updateAllSwatchImages(variation);

                                        // Update visual swatch state to match current selections
                    setTimeout(function() {
                        var $form = $('form.variations_form');
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var value = $select.val();

                                                                        if (value && (attribute === 'attribute_pa_bundles' || attribute === 'attribute_pa_controller')) {
                                var $swatches = $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch');
                                var $targetSwatch = $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch[data-attribute-value="' + value + '"]');
                                $swatches.removeClass('cgkit-swatch-selected zg-permanent-selected');
                                $targetSwatch.addClass('cgkit-swatch-selected zg-permanent-selected');
                            }
                        });
                    }, 100);

                    // CRITICAL: Always ensure controller is restored from persistent memory
                    if (window.persistentControllerSelection) {
                        var $controllerSelect = $('select[name="attribute_pa_controller"]');
                        if ($controllerSelect.val() !== window.persistentControllerSelection) {
                            $controllerSelect.val(window.persistentControllerSelection);
                            // Update visual swatch state
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch[data-attribute-value="' + window.persistentControllerSelection + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                        }
                    }

                    // CRITICAL: Always ensure bundle is restored from persistent memory
                    if (window.persistentBundleSelection) {
                        var $bundleSelect = $('select[name="attribute_pa_bundles"]');
                        if ($bundleSelect.val() !== window.persistentBundleSelection) {
                            $bundleSelect.val(window.persistentBundleSelection);
                            // Update visual swatch state
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch[data-attribute-value="' + window.persistentBundleSelection + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                        }
                    }
                });

                                // Handle variation reset and preserve selections
                $(document.body).on('reset_data', function() {
                    var currentControllerValue = $('select[name="attribute_pa_controller"]').val();
                    var bundleValue = $('select[name="attribute_pa_bundles"]').val();
                    var frontBenchValue = $('select[name="attribute_pa_front-bench"]').val();
                    var pizzaOvenValue = $('select[name="attribute_pa_pizza-oven"]').val();
                    var paymentMethodValue = $('select[name="attribute_pa_payment-method"]').val();

                    // CRITICAL: Always try to get controller from visual swatch if dropdown is empty
                    if (!currentControllerValue || currentControllerValue === '') {
                        var $selectedControllerSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.cgkit-swatch-selected, .cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.zg-permanent-selected');
                        if ($selectedControllerSwatch.length) {
                            currentControllerValue = $selectedControllerSwatch.data('attribute-value');
                        }
                    }

                    // CRITICAL: Always try to get bundle from visual swatch if dropdown is empty
                    if (!bundleValue || bundleValue === '') {
                        var $selectedBundleSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch.cgkit-swatch-selected, .cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch.zg-permanent-selected');
                        if ($selectedBundleSwatch.length) {
                            bundleValue = $selectedBundleSwatch.data('attribute-value');
                        }
                    }

                    // PERSISTENT CONTROLLER MEMORY - Never loses controller selection
                    if (currentControllerValue && currentControllerValue !== '') {
                        window.persistentControllerSelection = currentControllerValue;
                    }
                    // Always preserve the persistent selection, even if current dropdown is empty
                    if (window.persistentControllerSelection) {
                        window.preservedControllerValue = window.persistentControllerSelection;
                    }

                    // PERSISTENT BUNDLE MEMORY - Never loses bundle selection
                    if (bundleValue && bundleValue !== '') {
                        window.persistentBundleSelection = bundleValue;
                    }
                    // Always preserve the persistent selection, even if current dropdown is empty
                    if (window.persistentBundleSelection) {
                        window.preservedBundleValue = window.persistentBundleSelection;
                    }

                    if (frontBenchValue && frontBenchValue !== '' && frontBenchValue !== 'none') {
                        window.preservedFrontBenchValue = frontBenchValue;
                        window.lastValidFrontBenchSelection = frontBenchValue;
                    }

                    // PERSISTENT PIZZA OVEN MEMORY - Never loses pizza oven selection
                    if (pizzaOvenValue && pizzaOvenValue !== '') {
                        window.persistentPizzaOvenSelection = pizzaOvenValue;
                    }
                    // Always preserve the persistent selection, even if current dropdown is empty
                    if (window.persistentPizzaOvenSelection) {
                        window.preservedPizzaOvenValue = window.persistentPizzaOvenSelection;
                    }

                    // PERSISTENT PAYMENT METHOD MEMORY - Never loses payment method selection
                    if (paymentMethodValue && paymentMethodValue !== '') {
                        window.persistentPaymentMethodSelection = paymentMethodValue;
                    }
                    // Always preserve the persistent selection, even if current dropdown is empty
                    if (window.persistentPaymentMethodSelection) {
                        window.preservedPaymentMethodValue = window.persistentPaymentMethodSelection;
                    }

                    resetAccordionContent();

                    // Restore selections after reset
                    setTimeout(function() {
                        // CRITICAL: Always restore controller from persistent memory
                        if (window.persistentControllerSelection) {
                            $('select[name="attribute_pa_controller"]').val(window.persistentControllerSelection);
                            // Update visual swatch state immediately
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch[data-attribute-value="' + window.persistentControllerSelection + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                        }

                        // CRITICAL: Always restore bundle from persistent memory
                        if (window.persistentBundleSelection) {
                            $('select[name="attribute_pa_bundles"]').val(window.persistentBundleSelection);
                            // Update visual swatch state immediately
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch[data-attribute-value="' + window.persistentBundleSelection + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                        } else if (window.preservedBundleValue && $('select[name="attribute_pa_bundles"]').val() === '') {
                            $('select[name="attribute_pa_bundles"]').val(window.preservedBundleValue);
                        } else if (window.lastBundleSelection && $('select[name="attribute_pa_bundles"]').val() === '') {
                            $('select[name="attribute_pa_bundles"]').val(window.lastBundleSelection);
                        }

                        // Restore front bench if it was lost
                        if (window.preservedFrontBenchValue && $('select[name="attribute_pa_front-bench"]').val() === '') {
                            $('select[name="attribute_pa_front-bench"]').val(window.preservedFrontBenchValue);
                        } else if (window.lastValidFrontBenchSelection && $('select[name="attribute_pa_front-bench"]').val() === '') {
                            $('select[name="attribute_pa_front-bench"]').val(window.lastValidFrontBenchSelection);
                        }

                        // Restore pizza oven if it was lost
                        if (window.preservedPizzaOvenValue && $('select[name="attribute_pa_pizza-oven"]').val() === '') {
                            $('select[name="attribute_pa_pizza-oven"]').val(window.preservedPizzaOvenValue);
                        }

                        // Restore payment method if it was lost
                        if (window.preservedPaymentMethodValue && $('select[name="attribute_pa_payment-method"]').val() === '') {
                            $('select[name="attribute_pa_payment-method"]').val(window.preservedPaymentMethodValue);
                        }

                        // Sync visual state for all attributes
                        var $form = $('form.variations_form');
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var value = $select.val();

                            if (value && (attribute === 'attribute_pa_bundles' || attribute === 'attribute_pa_controller' || attribute === 'attribute_pa_front-bench' || attribute === 'attribute_pa_pizza-oven' || attribute === 'attribute_pa_payment-method')) {
                                var $swatches = $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch');
                                $swatches.removeClass('cgkit-swatch-selected zg-permanent-selected');
                                $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch[data-attribute-value="' + value + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                            }
                        });

                        // Clear temporary preserved values (but keep persistent controller memory)
                        delete window.preservedControllerValue;
                        delete window.preservedBundleValue;
                        delete window.preservedFrontBenchValue;
                    }, 100);
                });

                function updateSwatchImages(variation) {
                    // Update swatch images based on selected variation
                    if (variation && variation.variation_id) {
                        // Find the swatch card for this variation
                        var $swatchCard = $('[data-variation-id="' + variation.variation_id + '"]');
                        if ($swatchCard.length) {
                            // Update the image in the swatch card
                            var $swatchImg = $swatchCard.find('img');
                            if ($swatchImg.length && variation.image) {
                                $swatchImg.attr('src', variation.image.src);
                                $swatchImg.attr('srcset', variation.image.srcset || '');
                                $swatchImg.attr('sizes', variation.image.sizes || '');
                                $swatchImg.attr('alt', variation.image.alt || '');
                            }
                        }
                    }

                    // Also update all swatch images based on current combination
                    updateAllSwatchImages(variation);
                }

                                                                                                // Debounce function to prevent excessive calls
                var updateAllBundleSwatchImagesTimeout;
                var isUpdatingBundleImages = false;
                var updateAllBundleSwatchImagesTimeout;
                var lastUpdateAllSwatchImagesCall = 0;

                                                                                function updateAllSwatchImages(variation) {
                    // Prevent calls that are too frequent (less than 200ms apart)
                    var now = Date.now();
                    if (now - lastUpdateAllSwatchImagesCall < 200) {
                        return;
                    }
                    lastUpdateAllSwatchImagesCall = now;

                    // Prevent multiple simultaneous executions
                    if (isUpdatingBundleImages) {
                        return;
                    }

                    // Clear any existing timeout
                    if (updateAllBundleSwatchImagesTimeout) {
                        clearTimeout(updateAllBundleSwatchImagesTimeout);
                    }

                    // Set a new timeout to debounce the function
                    updateAllBundleSwatchImagesTimeout = setTimeout(function() {
                        // Double-check flags before proceeding
                        if (isUpdatingBundleImages) {
                            return;
                        }

                        isUpdatingBundleImages = true;

                    // Get current form and selected attributes
                    var $form = $('form.variations_form');
                    var selectedAttributes = {};

                    // Get all selected attribute values
                    $form.find('.variations select').each(function() {
                        var $select = $(this);
                        var attribute = $select.attr('name');
                        var value = $select.val();
                        if (value) {
                            selectedAttributes[attribute] = value;
                        }
                    });

                        // Get variations data
                        var variations = $form.data('product_variations');
                        if (!variations) {
                            isUpdatingBundleImages = false;
                        return;
                    }

                        // Update ALL swatch images (controller, bundle, front bench)
                        $('.cgkit-attribute-swatches .cgkit-attribute-swatch.cgkit-image').each(function() {
                        var $swatch = $(this);
                            var attribute = $swatch.closest('.cgkit-attribute-swatches').data('attribute');
                            var swatchValue = $swatch.data('attribute-value') || $swatch.find('button').data('attribute-value');

                            // Skip if we can't find the swatch value
                            if (!swatchValue) {
                            return; // Continue to next iteration
                        }

                            // Create target combination for this swatch with ALL other selected attributes
                        var targetCombination = {};
                        for (var attr in selectedAttributes) {
                                if (attr !== attribute) { // Exclude the current attribute itself
                                targetCombination[attr] = selectedAttributes[attr];
                            }
                        }
                            targetCombination[attribute] = swatchValue;

                        // Special handling for Grill Only - it should always use "none" for front bench
                            if (swatchValue === 'grill-only') {
                            targetCombination['attribute_pa_front-bench'] = 'none';
                        }

                        // Find matching variation
                        var matchingVariation = null;
                        for (var i = 0; i < variations.length; i++) {
                            var var_data = variations[i];
                            var isMatch = true;

                            for (var attr in targetCombination) {
                                var expectedValue = targetCombination[attr];
                                var actualValue = var_data.attributes[attr];

                                if (actualValue !== expectedValue) {
                                    isMatch = false;
                                    break;
                                }
                            }

                            if (isMatch) {
                                matchingVariation = var_data;
                                break;
                            }
                        }

                        // Update image if matching variation found
                        if (matchingVariation && matchingVariation.image) {
                            var $swatchImg = $swatch.find('img');
                            if ($swatchImg.length) {
                                $swatchImg.attr('src', matchingVariation.image.src);
                                $swatchImg.attr('srcset', matchingVariation.image.srcset || '');
                                $swatchImg.attr('sizes', matchingVariation.image.sizes || '');
                                $swatchImg.attr('alt', matchingVariation.image.alt || '');
                                }
                            }
                        });

                        // Reset the flag when execution completes
                        isUpdatingBundleImages = false;

                    }, 100); // 100ms debounce delay
                }



                // Trigger image and badge updates on page load to set initial state
                var initializationComplete = false;
                $(document).ready(function() {
                    setTimeout(function() {
                        if (!initializationComplete) {
                            initializationComplete = true;
                            ensureDefaultSelection();
                        }
                    }, 500);
                });

                                                // Function to ensure default selection is applied
                var defaultSelectionApplied = false;
                function ensureDefaultSelection() {
                    if (defaultSelectionApplied) {
                        return; // Prevent multiple calls
                    }

                    var $form = $('form.variations_form');
                    var variations = $form.data('product_variations');

                    if (!variations || variations.length === 0) {
                        return;
                    }

                    // Temporarily disable the preventVariationSearch flag for default selection
                    var originalFlag = window.preventVariationSearch;
                    window.preventVariationSearch = false;

                    // Check if any attributes are not selected
                    var hasUnselectedAttributes = false;
                    $form.find('.variations select').each(function() {
                        var $select = $(this);
                        if (!$select.val()) {
                            hasUnselectedAttributes = true;
                        }
                    });

                    // If there are unselected attributes, try to set defaults
                    if (hasUnselectedAttributes) {
                        // Try to find a default variation (usually the first one)
                        var defaultVariation = variations[0];
                        if (defaultVariation && defaultVariation.attributes) {
                            // Set the default attributes
                            for (var attr in defaultVariation.attributes) {
                                var $select = $form.find('select[name="' + attr + '"]');
                                if ($select.length && !$select.val()) {
                                    $select.val(defaultVariation.attributes[attr]);

                                    // Update swatch visual state for bundles and controller
                                    if (attr === 'attribute_pa_bundles' || attr === 'attribute_pa_controller') {
                                        var $swatches = $('.cgkit-attribute-swatches[data-attribute="' + attr + '"] .cgkit-swatch');
                                        $swatches.removeClass('cgkit-swatch-selected zg-permanent-selected');
                                        $('.cgkit-attribute-swatches[data-attribute="' + attr + '"] .cgkit-swatch[data-attribute-value="' + defaultVariation.attributes[attr] + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                                    }

                                    // Don't trigger change event to prevent cascading updates
                                    // The variation will be found automatically by WooCommerce
                                }
                            }
                            }
                        } else {
                        // Update visual swatch state for existing selections with PERMANENT class
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var value = $select.val();

                            if (value && (attribute === 'attribute_pa_bundles' || attribute === 'attribute_pa_controller')) {
                                var $swatches = $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch');
                                $swatches.removeClass('cgkit-swatch-selected zg-permanent-selected');
                                $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch[data-attribute-value="' + value + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                        }
                    });
                }

                    // Ensure front bench visibility is correct based on bundle selection
                    var bundleValue = $form.find('select[name="attribute_pa_bundles"]').val();
                    var $frontBenchRow = $form.find('tr').filter(function() {
                        return $(this).find('label[for="pa_front-bench"]').length > 0;
                    });

                    if (bundleValue === 'grill-only') {
                        $frontBenchRow.hide();
                    } else {
                        $frontBenchRow.show();
                    }

                    // Restore the original flag state
                    window.preventVariationSearch = originalFlag;

                    // Mark as applied to prevent future calls
                    defaultSelectionApplied = true;

                    // Ensure visual state is synced with dropdown values
                        setTimeout(function() {
                        var $form = $('form.variations_form');
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var value = $select.val();

                            if (value && (attribute === 'attribute_pa_bundles' || attribute === 'attribute_pa_controller' || attribute === 'attribute_pa_front-bench' || attribute === 'attribute_pa_pizza-oven' || attribute === 'attribute_pa_payment-method')) {
                                var $swatches = $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch');
                                $swatches.removeClass('cgkit-swatch-selected zg-permanent-selected');
                                $('.cgkit-attribute-swatches[data-attribute="' + attribute + '"] .cgkit-swatch[data-attribute-value="' + value + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                            }
                        });
                    }, 200);
                }

                                                                // Flag to prevent infinite loops in controller changes
                var isProcessingControllerChange = false;

                                // Additional event handlers to catch attribute changes
                $(document).on('change', 'select[name="attribute_pa_controller"]', function() {
                    // Prevent infinite loops
                    if (isProcessingControllerChange) {
                        return;
                    }

                    // Prevent infinite loops by checking global flag
                    if (window.isProcessingVariationChange) {
                        return;
                    }

                    isProcessingControllerChange = true;
                    window.isProcessingVariationChange = true;

                    var value = $(this).val();
                    var bundleValue = $('select[name="attribute_pa_bundles"]').val();

                    // Get current variation data and update images
                    var $form = $('form.variations_form');
                    var variations = $form.data('product_variations');
                    if (variations && variations.length > 0) {
                        // Find current variation based on selected attributes
                        var selectedAttributes = {};
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var attrValue = $select.val();
                            if (attrValue) {
                                selectedAttributes[attribute] = attrValue;
                            }
                        });

                        // Find matching variation
                        var currentVariation = null;
                        for (var i = 0; i < variations.length; i++) {
                            var variation = variations[i];
                            var isMatch = true;
                            for (var attr in selectedAttributes) {
                                if (variation.attributes[attr] !== selectedAttributes[attr]) {
                                    isMatch = false;
                                    break;
                                }
                            }
                            if (isMatch) {
                                currentVariation = variation;
                                break;
                            }
                        }

                        // Update images ONLY for bundle and controller swatches (not front bench)
                        if (currentVariation) {
                            updateBundleAndControllerImages(currentVariation);
                        } else {
                            updateBundleAndControllerImages();
                        }

                        // Ensure cart button is enabled for grill-only even if no exact variation match
                        if (bundleValue === 'grill-only' && controllerValue) {
                            $('.single_add_to_cart_button').removeClass('disabled wc-variation-is-unavailable');
                            $('.single_add_to_cart_button').addClass('wc-variation-selected');
                        }
                    } else {
                        updateBundleAndControllerImages();
                    }

                    // Reset the processing flag after a short delay
                    setTimeout(function() {
                        isProcessingControllerChange = false;
                        window.isProcessingVariationChange = false;
                    }, 100);
                });

                                                // Flag to prevent infinite loops in bundle changes
                var isProcessingBundleChange = false;
                var lastBundleValue = null;

                                                                $(document).on('change', 'select[name="attribute_pa_bundles"]', function() {
                    // Prevent infinite loops but allow processing if it's been too long
                    if (isProcessingBundleChange) {
                        return;
                    }

                    // Prevent infinite loops by checking global flag
                    if (window.isProcessingVariationChange) {
                        return;
                    }

                    var bundleValue = $(this).val();

                    // Skip if bundle value hasn't actually changed
                    if (bundleValue === lastBundleValue) {
                        return;
                    }

                    // Safety check - ensure bundleValue is defined
                    if (!bundleValue) {
                        return;
                    }

                    lastBundleValue = bundleValue;
                    isProcessingBundleChange = true;
                    window.isProcessingVariationChange = true;

                    // CRITICAL: Get controller from multiple sources to ensure we never lose it
                    var $selectedControllerSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.cgkit-swatch-selected, .cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.zg-permanent-selected');
                    var currentControllerValue = $selectedControllerSwatch.length ? $selectedControllerSwatch.data('attribute-value') : $('select[name="attribute_pa_controller"]').val();

                    // Safety check - ensure currentControllerValue is defined
                    if (!currentControllerValue) {
                        currentControllerValue = '';
                    }

                    // Store in persistent memory immediately
                    if (currentControllerValue && currentControllerValue !== '') {
                        window.persistentControllerSelection = currentControllerValue;
                    }

                    // CRITICAL: Get bundle from multiple sources to ensure we never lose it
                    var $selectedBundleSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch.cgkit-swatch-selected, .cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch.zg-permanent-selected');
                    var currentBundleValue = $selectedBundleSwatch.length ? $selectedBundleSwatch.data('attribute-value') : bundleValue;

                    // Safety check - ensure currentBundleValue is defined
                    if (!currentBundleValue) {
                        currentBundleValue = bundleValue || '';
                    }

                    // Store in persistent memory immediately
                    if (currentBundleValue && currentBundleValue !== '') {
                        window.persistentBundleSelection = currentBundleValue;
                    }

                    var $form = $('form.variations_form');
                    var $frontBenchRow = $form.find('tr').filter(function() {
                        return $(this).find('label[for="pa_front-bench"]').length > 0;
                    });

                    // OPTIMIZED: Manage front bench visibility without triggering unnecessary changes
                    if (bundleValue === 'grill-only') {
                        // Hide front bench row smoothly without changing values
                        $frontBenchRow.fadeOut(200);
                        $frontBenchRow.closest('tr').fadeOut(200);

                        // Set front bench to 'none' only if it's not already set
                        var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();
                        if (currentFrontBench !== 'none') {
                            $form.find('select[name="attribute_pa_front-bench"]').val('none');
                        }
                    } else {
                        // Show front bench row smoothly
                        $frontBenchRow.fadeIn(200);
                        $frontBenchRow.closest('tr').fadeIn(200);

                        // Only change front bench value if it's currently 'none'
                        var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();
                        if (currentFrontBench === 'none') {
                            // Use last valid selection or default to stainless-steel
                            var newFrontBenchValue = window.lastValidFrontBenchSelection || 'stainless-steel';
                            $form.find('select[name="attribute_pa_front-bench"]').val(newFrontBenchValue);

                            // Update visual state without triggering change event
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch[data-attribute-value="' + newFrontBenchValue + '"]').addClass('cgkit-swatch-selected zg-permanent-selected');
                        }
                    }

                    // OPTIMIZED: Update images only for bundle and controller swatches (not front bench)
                    var variations = $form.data('product_variations');
                    if (variations && variations.length > 0) {
                        // Find current variation based on selected attributes
                        var selectedAttributes = {};
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var attrValue = $select.val();
                            if (attrValue) {
                                selectedAttributes[attribute] = attrValue;
                            }
                        });

                        // Find matching variation
                        var currentVariation = null;
                        for (var i = 0; i < variations.length; i++) {
                            var variation = variations[i];
                            var isMatch = true;
                            for (var attr in selectedAttributes) {
                                if (variation.attributes[attr] !== selectedAttributes[attr]) {
                                    isMatch = false;
                                    break;
                                }
                            }
                            if (isMatch) {
                                currentVariation = variation;
                                break;
                            }
                        }

                        // Update images ONLY for bundle and controller swatches to prevent front bench flickering
                        if (currentVariation) {
                            updateBundleAndControllerImages(currentVariation);
                        } else {
                            updateBundleAndControllerImages();
                        }
                    } else {
                        updateBundleAndControllerImages();
                    }

                    // Reset the processing flag after a longer delay to prevent rapid re-triggering
                    setTimeout(function() {
                        isProcessingBundleChange = false;
                        window.isProcessingVariationChange = false;

                        // Ensure cart button is enabled for grill-only
                        if (bundleValue === 'grill-only') {
                            $('.single_add_to_cart_button').removeClass('disabled wc-variation-is-unavailable');
                            $('.single_add_to_cart_button').addClass('wc-variation-selected');
                        }
                    }, 300);
                });

                // Flag to prevent infinite loops in front bench changes
                var isProcessingFrontBenchChange = false;

                                                // Global event handler to track front bench clicks and sync selections
                $(document).on('click', '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch', function(e) {
                    var frontBenchValue = $(this).data('attribute-value');
                    var currentDropdownValue = $('select[name="attribute_pa_front-bench"]').val();
                    var bundleValue = $('select[name="attribute_pa_bundles"]').val();

                    // Store the last valid front bench selection (not 'none')
                    if (frontBenchValue && frontBenchValue !== 'none') {
                        window.lastValidFrontBenchSelection = frontBenchValue;
                    }

                    // Sync the dropdown value with the clicked swatch
                    if (frontBenchValue && frontBenchValue !== currentDropdownValue) {
                        $('select[name="attribute_pa_front-bench"]').val(frontBenchValue);

                        // Update visual state immediately
                        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                        $(this).addClass('cgkit-swatch-selected zg-permanent-selected');

                        // Update dropdown value and trigger change event safely
                        $('select[name="attribute_pa_front-bench"]').val($(this).data('attribute-value'));

                        // Trigger change event with a small delay to prevent infinite loops
                            setTimeout(function() {
                            if (!isProcessingFrontBenchChange && !window.isProcessingVariationChange) {
                                $('select[name="attribute_pa_front-bench"]').trigger('change');
                        }
                        }, 100);

                        // Additional direct image update for 7002B - REMOVED to prevent infinite loop
                        // The change event handler will handle image updates properly
                    }
                });

                $(document).on('change', 'select[name="attribute_pa_front-bench"]', function() {
                    // Prevent infinite loops
                    if (isProcessingFrontBenchChange) {
                        return;
                    }

                    // Prevent infinite loops by checking global flag
                    if (window.isProcessingVariationChange) {
                        return;
                    }

                    isProcessingFrontBenchChange = true;
                    window.isProcessingVariationChange = true;

                    var value = $(this).val();
                    var bundleValue = $('select[name="attribute_pa_bundles"]').val();
                    var controllerValue = $('select[name="attribute_pa_controller"]').val();

                    // OPTIMIZED: Only update bundle and controller images when front bench changes
                    // This prevents front bench swatches from flickering when they change
                    var $form = $('form.variations_form');
                    var variations = $form.data('product_variations');

                    if (variations && variations.length > 0) {
                        // Find current variation based on selected attributes
                        var selectedAttributes = {};
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var attrValue = $select.val();
                            if (attrValue) {
                                selectedAttributes[attribute] = attrValue;
                            }
                        });

                        // Find matching variation
                        var currentVariation = null;
                        for (var i = 0; i < variations.length; i++) {
                            var variation = variations[i];
                            var isMatch = true;
                            for (var attr in selectedAttributes) {
                                if (variation.attributes[attr] !== selectedAttributes[attr]) {
                                    isMatch = false;
                                    break;
                                }
                            }
                            if (isMatch) {
                                currentVariation = variation;
                                break;
                            }
                        }

                        // Update images ONLY for bundle and controller swatches (not front bench)
                        if (currentVariation) {
                            updateBundleAndControllerImages(currentVariation);
                        } else {
                            updateBundleAndControllerImages();
                        }
                    } else {
                        updateBundleAndControllerImages();
                    }

                    // Reset the processing flag after a longer delay to prevent rapid re-triggering
                    setTimeout(function() {
                        isProcessingFrontBenchChange = false;
                        window.isProcessingVariationChange = false;
                    }, 300);
                });

                // Flag to prevent infinite loops in pizza oven changes
                var isProcessingPizzaOvenChange = false;

                $(document).on('change', 'select[name="attribute_pa_pizza-oven"]', function() {
                    // Prevent infinite loops
                    if (isProcessingPizzaOvenChange) {
                        return;
                    }

                    // Prevent infinite loops by checking global flag
                    if (window.isProcessingVariationChange) {
                        return;
                    }

                    isProcessingPizzaOvenChange = true;
                    window.isProcessingVariationChange = true;

                    var pizzaOvenValue = $(this).val();


                    // Get current variation data and update images
                    var $form = $('form.variations_form');
                    if ($form.length) {
                        var variations = $form.data('product_variations');
                        if (variations && variations.length > 0) {
                            // Find current variation based on selected attributes
                            var selectedAttributes = {};
                            $form.find('.variations select').each(function() {
                                var $select = $(this);
                                var attribute = $select.attr('name');
                                var attrValue = $select.val();
                                if (attrValue) {
                                    selectedAttributes[attribute] = attrValue;
                                }
                            });


                            // Find matching variation
                            var currentVariation = null;
                            for (var i = 0; i < variations.length; i++) {
                                var variation = variations[i];
                                var isMatch = true;
                                for (var attr in selectedAttributes) {
                                    if (variation.attributes[attr] !== selectedAttributes[attr]) {
                                        isMatch = false;
                                        break;
                                    }
                                }
                                if (isMatch) {
                                    currentVariation = variation;
                                    break;
                                }
                            }

                            if (!currentVariation) {
                            }

                            // Update images for bundle and controller swatches
                            if (currentVariation) {
                                updateBundleAndControllerImages(currentVariation);
                                // Also force immediate update for Grill Only if it's selected
                                if (selectedAttributes['attribute_pa_bundles'] === 'grill-only') {
                                    setTimeout(function() {
                                        forceUpdateBundleImages(currentVariation);
                                    }, 5); // Even faster for Trimal Series 1
                                }
                            } else {
                                updateBundleAndControllerImages();
                            }
                        } else {
                            updateBundleAndControllerImages();
                        }
                    }

                    // Reset the processing flag after a short delay
                    setTimeout(function() {
                        isProcessingPizzaOvenChange = false;
                        window.isProcessingVariationChange = false;
                    }, 100);
                });

                // Flag to prevent infinite loops in payment method changes
                var isProcessingPaymentMethodChange = false;

                $(document).on('change', 'select[name="attribute_payment-method"]', function() {
                    // Prevent infinite loops
                    if (isProcessingPaymentMethodChange) {
                        return;
                    }

                    // Prevent infinite loops by checking global flag
                    if (window.isProcessingVariationChange) {
                        return;
                    }

                    isProcessingPaymentMethodChange = true;
                    window.isProcessingVariationChange = true;

                    var paymentMethodValue = $(this).val();


                    // Get current variation data and update images
                    var $form = $('form.variations_form');
                    if ($form.length) {
                        var variations = $form.data('product_variations');
                        if (variations && variations.length > 0) {
                            // Find current variation based on selected attributes
                            var selectedAttributes = {};
                            $form.find('.variations select').each(function() {
                                var $select = $(this);
                                var attribute = $select.attr('name');
                                var attrValue = $select.val();
                                if (attrValue) {
                                    selectedAttributes[attribute] = attrValue;
                                }
                            });


                            // Find matching variation
                            var currentVariation = null;
                            for (var i = 0; i < variations.length; i++) {
                                var variation = variations[i];
                                var isMatch = true;
                                for (var attr in selectedAttributes) {
                                    if (variation.attributes[attr] !== selectedAttributes[attr]) {
                                        isMatch = false;
                                        break;
                                    }
                                }
                                if (isMatch) {
                                    currentVariation = variation;
                                    break;
                                }
                            }

                            if (!currentVariation) {
                            }

                            // Update images for bundle and controller swatches
                            if (currentVariation) {
                                updateBundleAndControllerImages(currentVariation);
                                // Also force immediate update for Grill Only if it's selected
                                if (selectedAttributes['attribute_pa_bundles'] === 'grill-only') {
                                    setTimeout(function() {
                                        forceUpdateBundleImages(currentVariation);
                                    }, 5); // Even faster for Trimal Series 1
                                }
                            } else {
                                updateBundleAndControllerImages();
                            }
                        } else {
                            updateBundleAndControllerImages();
                        }
                    }

                    // Reset the processing flag after a short delay
                    setTimeout(function() {
                        isProcessingPaymentMethodChange = false;
                        window.isProcessingVariationChange = false;
                    }, 100);
                });

                // Handle when variation is reset - REMOVED DUPLICATE HANDLER
                // The main reset_data handler above already handles this properly

                // Additional handler for form reset events
                $(document).on('woocommerce_reset_variations', function() {
                    // Reset the flag to allow re-initialization
                    defaultSelectionApplied = false;
                    setTimeout(function() {
                        ensureDefaultSelection();
                    }, 200);
                });

                // PERMANENT SELECTION CSS - This class NEVER gets removed
                $('<style>')
                    .prop('type', 'text/css')
                    .html(`
                        .zg-permanent-selected {
                            border: 2px solid #ff0000 !important;
                            background-color: #f3d8d3 !important;
                            box-shadow: 0 0 10px rgba(255, 0, 0, 0.3) !important;
                        }
                        .zg-permanent-selected .tile-title {
                            font-weight: bold !important;
                            color: #ff0000 !important;
                        }
                    `)
                    .appendTo('head');

                                                                                                // CONTROLLER CLICK HANDLER - Store in persistent memory immediately
                $(document).on('click', '.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch', function(e) {
                    var $clickedSwatch = $(this);
                    var controllerValue = $clickedSwatch.data('attribute-value');

                    // CRITICAL: Store immediately in persistent memory
                    window.persistentControllerSelection = controllerValue;

                    // Remove selection from all controller swatches
                    $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');

                    // Add BOTH classes to clicked swatch
                    $clickedSwatch.addClass('cgkit-swatch-selected zg-permanent-selected');

                    // Update dropdown and trigger change event safely
                    $('select[name="attribute_pa_controller"]').val(controllerValue);

                    // Trigger change event with a small delay to prevent infinite loops
                    setTimeout(function() {
                        if (!isProcessingControllerChange && !window.isProcessingVariationChange) {
                            $('select[name="attribute_pa_controller"]').trigger('change');
                        }
                    }, 100);
                });

                                                                                // BUNDLE CLICK HANDLER - Store in persistent memory immediately
                $(document).on('click', '.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch', function(e) {
                    var $clickedSwatch = $(this);
                    var bundleValue = $clickedSwatch.data('attribute-value');

                    // CRITICAL: Store immediately in persistent memory
                    window.persistentBundleSelection = bundleValue;

                    // Remove selection from all bundle swatches
                    $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');

                    // Add BOTH classes to clicked swatch
                    $clickedSwatch.addClass('cgkit-swatch-selected zg-permanent-selected');

                    // Update dropdown and trigger change event safely
                    $('select[name="attribute_pa_bundles"]').val(bundleValue);

                    // Trigger change event with a small delay to prevent infinite loops
                    setTimeout(function() {
                        if (!isProcessingBundleChange && !window.isProcessingVariationChange) {
                            $('select[name="attribute_pa_bundles"]').trigger('change');
                        }
                    }, 100);
                });

                // Global event handler to prevent grill-only selection loss
                $(document).on('click', '.cgkit-swatch[data-attribute-value="grill-only"]', function(e) {
                    // Prevent other event handlers from interfering
                    e.stopPropagation();

                    // Check if this is a bundle-only product (no controller attribute)
                    var $controllerSelect = $('select[name="attribute_pa_controller"]');
                    var hasController = $controllerSelect.length > 0;

                    // CRITICAL: Preserve controller in persistent memory immediately (only if controller exists)
                    if (hasController) {
                        var $selectedControllerSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.cgkit-swatch-selected, .cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.zg-permanent-selected');
                        var currentControllerValue = $selectedControllerSwatch.length ? $selectedControllerSwatch.data('attribute-value') : $controllerSelect.val();

                        if (currentControllerValue && currentControllerValue !== '') {
                            window.persistentControllerSelection = currentControllerValue;
                        }
                    }

                    // Ensure the selection is maintained
                    var $this = $(this);

                    // Always ensure the dropdown value is set immediately
                    var $bundleSelect = $('select[name="attribute_pa_bundles"]');

                    if (!$this.hasClass('cgkit-swatch-selected')) {
                        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                        $this.addClass('cgkit-swatch-selected zg-permanent-selected');
                        $bundleSelect.val('grill-only');
                    } else {
                        $this.addClass('cgkit-swatch-selected zg-permanent-selected');
                        $bundleSelect.val('grill-only');
                    }

                    // Ensure front bench is set to none for grill-only (only if front bench exists)
                    var $frontBenchSelect = $('select[name="attribute_pa_front-bench"]');
                    if ($frontBenchSelect.length > 0) {
                        // Don't set to 'none' as it causes label to show "None" - just hide the row
                        $frontBenchSelect.closest('tr').hide();
                    }

                    // CRITICAL: Store grill-only in persistent memory immediately
                    window.persistentBundleSelection = 'grill-only';

                    // Force the bundle selection to persist with persistent memory backup
                    var attempts = 0;
                    var maxAttempts = 3;
                    var checkBundleSelection = function() {
                        attempts++;
                        var currentValue = $bundleSelect.val();
                        if (currentValue !== 'grill-only') {
                            // Restore from persistent memory
                            $bundleSelect.val('grill-only');
                            // Update visual state immediately
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                            $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch[data-attribute-value="grill-only"]').addClass('cgkit-swatch-selected zg-permanent-selected');

                            if (attempts < maxAttempts) {
                                setTimeout(checkBundleSelection, 50);
                            }
                        }
                    };
                    setTimeout(checkBundleSelection, 50);

                    // Don't trigger additional image updates here - let the change event handle it
                });

                                                function updateAccordionContent(variation) {
                    var $excerpt = $('.zg-accordion-excerpt');
                    var $content = $('.zg-accordion-content');

                    // Update excerpt text with character limiting
                    if (variation._vt_dd_preview && variation._vt_dd_preview.trim() !== '') {
                        var previewText = variation._vt_dd_preview.trim();
                        var previewLimit = typeof zgAccordionSettings !== 'undefined' ? zgAccordionSettings.previewTextLimit : 100;

                        // Truncate text if it exceeds the limit
                        if (previewText.length > previewLimit) {
                            previewText = previewText.substring(0, previewLimit) + '...';
                        }

                        $excerpt.html(previewText).show();
                    } else {
                        $excerpt.html('').hide();
                    }

                    // Update full content with image and text
                    var contentHtml = '';

                    // Add image if available
                    if (variation._vt_dd_image_url && variation._vt_dd_image_url.trim() !== '') {
                        contentHtml += '<div style="margin-bottom: 15px; width: 100%;">';
                        contentHtml += '<img src="' + variation._vt_dd_image_url + '" alt="Variant tile image" class="zg-accordion-image" style="width: 100%; height: auto; display: block;">';
                        contentHtml += '</div>';
                    }

                    // Add text content
                    if (variation._vt_dd_text && variation._vt_dd_text.trim() !== '') {
                        contentHtml += variation._vt_dd_text;
                    }

                    $content.find('div').first().html(contentHtml);
                }

                function resetAccordionContent() {
                    // Reset to default content if needed
                    // This will be handled by the initial PHP content
                }

                // DIRECT: Function to force immediate image update (bypasses debounce)
                function forceUpdateBundleImages(variation) {

                    // Get current form and selected attributes
                    var $form = $('form.variations_form');
                    var selectedAttributes = {};

                    // Get all selected attribute values
                    $form.find('.variations select').each(function() {
                        var $select = $(this);
                        var attribute = $select.attr('name');
                        var value = $select.val();
                        if (value) {
                            selectedAttributes[attribute] = value;
                        }
                    });

                    // Get variations data
                    var variations = $form.data('product_variations');
                    if (!variations) {
                        return;
                    }

                    // Update ONLY bundle swatch images
                    $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-attribute-swatch.cgkit-image').each(function() {
                        var $swatch = $(this);
                        var attribute = $swatch.closest('.cgkit-attribute-swatches').data('attribute');
                        var swatchValue = $swatch.data('attribute-value') || $swatch.find('button').data('attribute-value');

                        if (!swatchValue) {
                            return;
                        }

                        // Create target combination for this swatch with ALL other selected attributes
                        var targetCombination = {};
                        for (var attr in selectedAttributes) {
                            if (attr !== attribute) {
                                targetCombination[attr] = selectedAttributes[attr];
                            }
                        }
                        targetCombination[attribute] = swatchValue;

                        // Special handling for Grill Only
                        if (swatchValue === 'grill-only') {
                            targetCombination['attribute_pa_front-bench'] = 'none';
                        }

                        // Find matching variation
                        var matchingVariation = null;
                        for (var i = 0; i < variations.length; i++) {
                            var var_data = variations[i];
                            var isMatch = true;

                            for (var attr in targetCombination) {
                                var expectedValue = targetCombination[attr];
                                var actualValue = var_data.attributes[attr];

                                if (actualValue !== expectedValue) {
                                    isMatch = false;
                                    break;
                                }
                            }

                            if (isMatch) {
                                matchingVariation = var_data;
                                break;
                            }
                        }

                        // Update image if matching variation found
                        if (matchingVariation && matchingVariation.image) {
                            var $swatchImg = $swatch.find('img');
                            if ($swatchImg.length) {
                                var oldSrc = $swatchImg.attr('src');
                                $swatchImg.attr('src', matchingVariation.image.src);
                                $swatchImg.attr('srcset', matchingVariation.image.srcset || '');
                                $swatchImg.attr('sizes', matchingVariation.image.sizes || '');
                                $swatchImg.attr('alt', matchingVariation.image.alt || '');


                            }
                        }
                    });
                }

                // OPTIMIZED: Function to update only bundle and controller images (prevents front bench flickering)
                function updateBundleAndControllerImages(variation) {

                    // Check if this is Trimal Series 1 (has payment-method attribute)
                    var isTrimalSeries1 = $('select[name="attribute_payment-method"]').length > 0;

                    // Prevent calls that are too frequent - more lenient for Trimal Series 1
                    var now = Date.now();
                    var debounceTime = isTrimalSeries1 ? 5 : 10; // Even faster for Trimal Series 1
                    if (now - lastUpdateAllSwatchImagesCall < debounceTime) {
                        return;
                    }
                    lastUpdateAllSwatchImagesCall = now;

                    // Prevent multiple simultaneous executions
                    if (isUpdatingBundleImages) {
                        return;
                    }

                    // Clear any existing timeout
                    if (updateAllBundleSwatchImagesTimeout) {
                        clearTimeout(updateAllBundleSwatchImagesTimeout);
                    }

                    // Set a new timeout to debounce the function
                    updateAllBundleSwatchImagesTimeout = setTimeout(function() {
                        // Double-check flags before proceeding
                        if (isUpdatingBundleImages) {
                            return;
                        }

                        isUpdatingBundleImages = true;

                        // Get current form and selected attributes
                        var $form = $('form.variations_form');
                        var selectedAttributes = {};

                        // Get all selected attribute values
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var value = $select.val();
                            if (value) {
                                selectedAttributes[attribute] = value;
                            }
                        });

                        // Get variations data
                        var variations = $form.data('product_variations');
                        if (!variations) {
                            isUpdatingBundleImages = false;
                            return;
                        }

                        // Update ONLY bundle and controller swatch images (exclude front bench to prevent flickering)
                        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-attribute-swatch.cgkit-image, .cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-attribute-swatch.cgkit-image').each(function() {
                            var $swatch = $(this);
                            var attribute = $swatch.closest('.cgkit-attribute-swatches').data('attribute');
                            var swatchValue = $swatch.data('attribute-value') || $swatch.find('button').data('attribute-value');

                            // Skip if we can't find the swatch value
                            if (!swatchValue) {
                                return; // Continue to next iteration
                            }

                            // Create target combination for this swatch with ALL other selected attributes
                            var targetCombination = {};
                            for (var attr in selectedAttributes) {
                                if (attr !== attribute) { // Exclude the current attribute itself
                                    targetCombination[attr] = selectedAttributes[attr];
                                }
                            }
                            targetCombination[attribute] = swatchValue;

                            // Special handling for Grill Only - it should always use "none" for front bench
                            if (swatchValue === 'grill-only') {
                                targetCombination['attribute_pa_front-bench'] = 'none';
                                // For Grill Only, we need to be more flexible with payment method matching
                                // Try to find any grill-only variation first, then match the closest one
                            }

                            // Find matching variation
                            var matchingVariation = null;

                            // Special flexible matching for Grill Only
                            if (swatchValue === 'grill-only') {

                                // First, try exact match
                                for (var i = 0; i < variations.length; i++) {
                                    var var_data = variations[i];
                                    var isMatch = true;

                                    for (var attr in targetCombination) {
                                        var expectedValue = targetCombination[attr];
                                        var actualValue = var_data.attributes[attr];

                                        if (actualValue !== expectedValue) {
                                            isMatch = false;
                                            break;
                                        }
                                    }

                                    if (isMatch) {
                                        matchingVariation = var_data;
                                        break;
                                    }
                                }

                                // If no exact match, find the closest grill-only variation
                                if (!matchingVariation) {

                                    for (var i = 0; i < variations.length; i++) {
                                        var var_data = variations[i];

                                        // Must be grill-only bundle
                                        if (var_data.attributes['attribute_pa_bundles'] === 'grill-only') {
                                            // Must match pizza oven setting
                                            if (var_data.attributes['attribute_pa_pizza-oven'] === targetCombination['attribute_pa_pizza-oven']) {
                                                matchingVariation = var_data;
                                                break;
                                            }
                                        }
                                    }
                                }
                            } else {
                                // Normal matching for other swatches
                                for (var i = 0; i < variations.length; i++) {
                                    var var_data = variations[i];
                                    var isMatch = true;

                                    for (var attr in targetCombination) {
                                        var expectedValue = targetCombination[attr];
                                        var actualValue = var_data.attributes[attr];

                                        if (actualValue !== expectedValue) {
                                            isMatch = false;
                                            break;
                                        }
                                    }

                                    if (isMatch) {
                                        matchingVariation = var_data;
                                        break;
                                    }
                                }
                            }

                            if (!matchingVariation) {
                                // No matching variation found
                            }

                            // Update image if matching variation found
                            if (matchingVariation && matchingVariation.image) {
                                var $swatchImg = $swatch.find('img');
                                if ($swatchImg.length) {
                                    var oldSrc = $swatchImg.attr('src');
                                    $swatchImg.attr('src', matchingVariation.image.src);
                                    $swatchImg.attr('srcset', matchingVariation.image.srcset || '');
                                    $swatchImg.attr('sizes', matchingVariation.image.sizes || '');
                                    $swatchImg.attr('alt', matchingVariation.image.alt || '');

                                }
                            }
                        });

                        // Reset the flag when execution completes
                        isUpdatingBundleImages = false;

                    }, isTrimalSeries1 ? 10 : 20); // Faster for Trimal Series 1 (10ms) vs others (20ms)
                }
            });
            </script>
            <style>
            .zg-accordion-icon.rotated {
                transform: rotate(180deg);
            }

            /* Prevent layout shifts in accordion excerpt */
            .zg-accordion-excerpt {
                min-height: 20px;
                line-height: 1.4;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }

            /* Ensure consistent spacing */
            .zg-accordion-container {
                transition: all 0.3s ease;
            }


            .zg-product-savings-section > *:first-child {
                margin-top: 0;
            }

            .zg-product-savings-section > *:last-child {
                margin-bottom: 0;
            }

            /* Ensure equal spacing for accordion header and content */
            .zg-accordion-header {
                padding: 20px !important;
            }

            .zg-accordion-excerpt {
                padding: 0 20px 20px 20px !important;
            }

            .zg-accordion-content {
                padding: 0 20px 20px 20px !important;
            }

            /* BADGE STYLING - HANDLED IN CSS FILE */
            </style>

                                                                        <script>
            jQuery(document).ready(function($) {
                // Only run on product pages
                if (!$('body').hasClass('single-product') && !$('body').hasClass('woocommerce-page')) {
                    return;
                }
                // Badge configuration from Elementor settings
                var badgeConfig = {
                    'wireless-enabled': {
                        'pro-bundle': '<?php echo esc_js($settings['wireless_pro_badge_text'] ?? ''); ?>',
                        'basic-bundle': '<?php echo esc_js($settings['wireless_basic_badge_text'] ?? ''); ?>',
                        'grill-only': '<?php echo esc_js($settings['wireless_grill_badge_text'] ?? ''); ?>'
                    },
                    'non-wireless': {
                        'pro-bundle': '<?php echo esc_js($settings['non_wireless_pro_badge_text'] ?? ''); ?>',
                        'basic-bundle': '<?php echo esc_js($settings['non_wireless_basic_badge_text'] ?? ''); ?>',
                        'grill-only': '<?php echo esc_js($settings['non_wireless_grill_badge_text'] ?? ''); ?>'
                    }
                };

                                // Store current controller
                var currentController = null;

                                // Function to update badges based on controller selection
                function updateBadges(controllerValue) {
                    if (!controllerValue || !badgeConfig[controllerValue]) {
                        return;
                    }

                    currentController = controllerValue;

                    // Update badges for each bundle
                    $('[data-attribute-value="pro-bundle"], [data-attribute-value="basic-bundle"], [data-attribute-value="grill-only"]').each(function() {
                        var $button = $(this);
                        var bundleValue = $button.data('attribute-value');
                        var $existingBadge = $button.find('.tile-offer');
                        var badgeText = badgeConfig[controllerValue][bundleValue];

                        // Remove existing badge
                        $existingBadge.remove();

                        // Add new badge if text exists
                        if (badgeText && badgeText.trim() !== '') {
                            var badgeHtml = '<span class="tile-offer">' + badgeText + '</span>';
                            $button.append(badgeHtml);
                        }
                    });
                }

                // Function to restore badges immediately
                function restoreBadges() {
                    if (currentController) {
                        updateBadges(currentController);
                    }
                }

                // Update badges ONLY when controller changes
                $(document).on('change', '[name="attribute_pa_controller"]', function() {
                    var controllerValue = $(this).val();
                    updateBadges(controllerValue);
                });

                // Update badges when controller swatch is clicked
                $(document).on('click', '[data-attribute-value="wireless-enabled"], [data-attribute-value="non-wireless"]', function() {
                    var controllerValue = $(this).data('attribute-value');
                    updateBadges(controllerValue);
                });

                                                // Initialize badges on page load
                var initialController = $('[name="attribute_pa_controller"]').val() ||
                                      $('.cgkit-swatch-selected[data-attribute-value*="wireless"]').data('attribute-value') ||
                                      'wireless-enabled';
                updateBadges(initialController);

                // PREVENT badge removal by intercepting all variation events
                $(document).on('found_variation reset_data', function(e) {
                    // Immediately restore badges
                    restoreBadges();
                });

                // PREVENT badge removal on bundle/front bench changes
                $(document).on('change', '[name="attribute_pa_bundles"], [name="attribute_pa_front-bench"]', function() {
                    // Immediately restore badges
                    restoreBadges();
                });

                $(document).on('click', '[data-attribute-value*="bundle"], [data-attribute-value*="front-bench"]', function() {
                    // Immediately restore badges
                    restoreBadges();
                });

                                                                // Check for missing badges every 500ms (less aggressive to prevent shifting)
                setInterval(function() {
                    if (currentController) {
                        // Check if badges exist, if not restore them
                        var missingBadges = false;
                        $('[data-attribute-value="pro-bundle"], [data-attribute-value="basic-bundle"], [data-attribute-value="grill-only"]').each(function() {
                            var $button = $(this);
                            var bundleValue = $button.data('attribute-value');
                            var badgeText = badgeConfig[currentController][bundleValue];

                            if (badgeText && badgeText.trim() !== '' && $button.find('.tile-offer').length === 0) {
                                missingBadges = true;
                            }
                        });

                        if (missingBadges) {
                            restoreBadges();
                        }
                    }
                }, 500);

                // DOM observer as final protection
                var observer = new MutationObserver(function(mutations) {
                    var shouldRestore = false;
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList') {
                            mutation.removedNodes.forEach(function(node) {
                                if (node.nodeType === 1 && node.classList && node.classList.contains('tile-offer')) {
                                    shouldRestore = true;
                                }
                            });
                        }
                    });

                    if (shouldRestore) {
                        restoreBadges();
                    }
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                // Periodic check to ensure cart button is enabled for grill-only
                setInterval(function() {
                    var currentBundle = $('select[name="attribute_pa_bundles"]').val();
                    var currentController = $('select[name="attribute_pa_controller"]').val();

                    if (currentBundle === 'grill-only' && currentController) {
                        $('.single_add_to_cart_button').removeClass('disabled wc-variation-is-unavailable');
                        $('.single_add_to_cart_button').addClass('wc-variation-selected');
                    }
                }, 2000);

            });
            </script>

            <!-- Total Savings Display - Works for all products -->
            <div class="zg-total-savings" style="background: transparent; border: none; padding: 0; margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                <span class="zg-savings-label" style="color: #212529; font-weight: 900; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Inter';">TOTAL SAVINGS:</span>
                <span class="zg-savings-amount" style="color: #dc3545; font-weight: 700; font-size: 18px; font-family: 'Inter';">
                <?php if ($default_variation_data['savings'] > 0) : ?>
                    $<?php echo number_format($default_variation_data['savings'], 0); ?>
                <?php else : ?>
                    $120 (Default Savings)
                <?php endif; ?>
                </span>
            </div>

        </div>


        <?php
    }

    /**
     * Render dispatch date text below ATC button
     */
    private function render_dispatch_date() {
        // Only show on frontend, not in Elementor editor
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return;
        }

        $settings = $this->get_settings_for_display();
        $dispatch_text = isset($settings['dispatch_date_text']) ? trim($settings['dispatch_date_text']) : '';

        // Don't render if text is empty
        if (empty($dispatch_text)) {
            return;
        }

        // Split text to separate label from date (everything after the colon)
        $parts = explode(':', $dispatch_text, 2);
        $label = trim($parts[0]);
        $date = isset($parts[1]) ? trim($parts[1]) : '';

        ?>
        <!-- Dispatch Date Display -->
        <div class="zg-dispatch-date" style="background: transparent; border: none; padding: 0; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; font-style: normal !important;">
            <span class="zg-dispatch-text" style="color: #212529; font-weight: 800; font-size: 14px; letter-spacing: 0.5px; font-family: 'Inter'; font-style: normal !important; text-align: center;">
                <?php if ($date) : ?>
                    <?php echo esc_html($label); ?>: <span style="font-weight: 700;"><?php echo esc_html($date); ?></span>
                <?php else : ?>
                    <?php echo esc_html($dispatch_text); ?>
                <?php endif; ?>
            </span>
        </div>
        <?php
    }

    /**
     * Render badges based on Elementor settings
     */
                    private function render_tile_badge($attribute_raw, $item_attri_val, $_variations) {
        $settings = $this->get_settings_for_display();

        $badge_text = '';

        // Only render badges on bundle swatches
        if ($attribute_raw === 'bundles') {
            // Get current controller selection (persistent, not affected by other variations)
            $current_controller = $this->get_current_controller_selection($_variations);

            // Skip badge rendering for bundle-only products (no controller)
            if ($current_controller === null) {
                return '';
            }

            // Map controller + bundle combinations to badge settings
            $badge_map = [
                'wireless-enabled' => [
                    'pro-bundle' => $settings['wireless_pro_badge_text'] ?? '',
                    'basic-bundle' => $settings['wireless_basic_badge_text'] ?? '',
                    'grill-only' => $settings['wireless_grill_badge_text'] ?? '',
                ],
                'non-wireless' => [
                    'pro-bundle' => $settings['non_wireless_pro_badge_text'] ?? '',
                    'basic-bundle' => $settings['non_wireless_basic_badge_text'] ?? '',
                    'grill-only' => $settings['non_wireless_grill_badge_text'] ?? '',
                ],
            ];

            // Get badge text for current controller + bundle combination
            // This will be persistent regardless of front bench or other variation changes
            if (isset($badge_map[$current_controller][$item_attri_val])) {
                $badge_text = $badge_map[$current_controller][$item_attri_val];
            }
        }

        // Render badge if text found
        if (!empty($badge_text)) {
            return '<span class="tile-offer">' . esc_html($badge_text) . '</span>';
        }

        return '';
    }

    /**
     * Get current controller selection for badge logic
     */
    private function get_current_controller_selection($_variations) {
        // For bundle-only products (no controller variations), return null to disable badges
        if (!isset($_variations['controller']) || empty($_variations['controller'])) {
            return null;
        }

        // Try to get from URL parameters first (for direct links)
        if (isset($_GET['attribute_pa_controller'])) {
            return sanitize_text_field($_GET['attribute_pa_controller']);
        }

        // Try to get from default product attributes
        global $product;
        if ($product && $product->is_type('variable')) {
            $default_attributes = $product->get_default_attributes();
            if (isset($default_attributes['pa_controller'])) {
                return $default_attributes['pa_controller'];
            }
        }

        // Try to find the first controller variation with wireless-enabled
        if (isset($_variations['controller'])) {
            foreach ($_variations['controller'] as $controller_value => $controller_data) {
                if ($controller_value === 'wireless-enabled') {
                    return 'wireless-enabled';
                }
            }
            // If wireless not found, return first available controller
            $controller_keys = array_keys($_variations['controller']);
            if (!empty($controller_keys)) {
                return $controller_keys[0];
            }
        }

        // Default fallback
        return 'wireless-enabled';
    }

    /**
     * Get default variation data for the product
     */
    private function get_default_variation_data($product) {
        $data = array(
            'accordion_content' => '',
            'accordion_preview' => '',
            'accordion_image' => '',
            'savings' => 0
        );

        if ($product->is_type('variable')) {
            // Get default variation or first available variation using CommerceKit approach
            $default_attributes = $product->get_default_attributes();
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
            'accordion_content' => '',
            'accordion_preview' => '',
            'accordion_image' => '',
            'savings' => 0
        );

        // Get variation-specific dropdown data
        $variation_obj = wc_get_product($variation_id);
        $dd_text = $variation_obj ? $variation_obj->get_meta('_vt_dd_text', true) : '';
        $dd_preview = $variation_obj ? $variation_obj->get_meta('_vt_dd_preview', true) : '';

        // Debug: Log what we're getting in get_variation_data
        error_log( 'VT get_variation_data Debug - Variation ' . $variation_id . ' _vt_dd_text: ' . substr($dd_text, 0, 100) );
        error_log( 'VT get_variation_data Debug - Variation ' . $variation_id . ' _vt_dd_preview: ' . substr($dd_preview, 0, 100) );
        $dd_image_id = $variation_obj ? $variation_obj->get_meta('_vt_dd_image_id', true) : '';

        // Get image data
        if ($dd_image_id && wp_attachment_is_image($dd_image_id)) {
            $data['accordion_image'] = wp_get_attachment_image($dd_image_id, 'large', false, array('class' => 'zg-accordion-image'));
        }

        if (!empty($dd_text)) {
            $data['accordion_content'] = $dd_text;
        }

        if (!empty($dd_preview)) {
            $settings = $this->get_settings_for_display();
            $preview_limit = isset($settings['preview_text_limit']) ? (int)$settings['preview_text_limit'] : 100;
            $data['accordion_preview'] = $this->truncate_text($dd_preview, $preview_limit);
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

    /**
     * Render accordion component
     */
    private function render_accordion($default_variation_data) {
        ?>
        <!-- Variant Tiles Accordion -->
        <div class="zg-accordion-container" style="background: #efefef; border: 1px solid #e9ecef; border-radius: 8px;">
            <div class="zg-accordion-header" style="padding: 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-radius: 8px;">
                <span style="font-weight: 600; font-size: 16px; color: #212529;">What's included?</span>
                <span class="zg-accordion-icon dashicons dashicons-arrow-down-alt2" style="font-size: 16px; color: #212529;"></span>
            </div>

            <!-- Excerpt text (shown when collapsed) -->
            <?php if (!empty($default_variation_data['accordion_preview'])) : ?>
                <div class="zg-accordion-excerpt" style="padding: 0 20px 20px 20px; color: #6c757d; font-size: 13px; font-style: italic; min-height: 20px;">
                    <?php echo esc_html($default_variation_data['accordion_preview']); ?>
                </div>
            <?php else : ?>
                <div class="zg-accordion-excerpt" style="padding: 0 20px 20px 20px; color: #6c757d; font-size: 13px; font-style: italic; min-height: 20px; display: none;"></div>
            <?php endif; ?>

            <!-- Full description (shown when expanded) -->
            <div class="zg-accordion-content" style="display: none; padding: 0 20px 20px 20px;">
                <div style="color: #495057; font-size: 14px; line-height: 1.5;">
                    <?php if (!empty($default_variation_data['accordion_image'])) : ?>
                        <div style="margin-bottom: 15px; width: 100%;">
                            <?php echo $default_variation_data['accordion_image']; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($default_variation_data['accordion_content'])) : ?>
                        <?php echo wp_kses_post($default_variation_data['accordion_content']); ?>
                    <?php else : ?>
                        Grill and 2 x Food Temperature Probes
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
