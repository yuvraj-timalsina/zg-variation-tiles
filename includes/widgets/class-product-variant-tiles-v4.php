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
        wp_register_style('pro-tiles-elementor', PROTILES_URL . 'assets/css/pro-tile-elmentor.css', array(), '1.0.26.' . time());
        wp_register_style('zg-savings-accordion', PROTILES_URL . 'assets/css/savings-accordion.css', array(), '1.0.0.' . time());
        wp_register_script('zg-savings-accordion', PROTILES_URL . 'assets/js/savings-accordion.js', array('jquery'), '1.0.0.' . time(), true);
        wp_enqueue_script('wc-add-to-cart-variation');

        add_filter('variant_tiles_dropdown_continue', function(){
            return true;
        });

        // Add missing hook from old plugin for default selection
        add_filter('woocommerce_dropdown_variation_attribute_options_args', array($this, 'woo_select_default_option'),10,1);
        add_filter('commercekit_as_get_attribute_swatches_args', array($this, 'commercekit_default_selection'), 10, 1);
    }

    function woo_select_default_option( $args)
    {
        // Get the product to check for default attributes
        global $product;

        if (!$product) {
            $product_id = get_queried_object_id();
            $product = wc_get_product($product_id);
        }

        if ($product && $product->is_type('variable')) {
            $default_attributes = $product->get_default_attributes();
            $attribute_name = str_replace('attribute_', '', $args['attribute']);

            // Check if this attribute has a default value
            if (isset($default_attributes[$attribute_name])) {
                $args['selected'] = $default_attributes[$attribute_name];
            } else {
                // If no default is set, try to set a sensible default
                if (strpos($args['attribute'], 'bundles') !== false) {
                    // For bundles, prioritize: pro-bundle > basic-bundle > grill-only
                    if (in_array('pro-bundle', $args['options'])) {
                        $args['selected'] = 'pro-bundle';
                    } elseif (in_array('basic-bundle', $args['options'])) {
                        $args['selected'] = 'basic-bundle';
                    } elseif (in_array('grill-only', $args['options'])) {
                        $args['selected'] = 'grill-only';
                    } elseif (count($args['options']) > 0) {
            $args['selected'] = $args['options'][0];
                    }
                } elseif (strpos($args['attribute'], 'controller') !== false) {
                    // For controller, default to 'wireless-enabled' if available
                    if (in_array('wireless-enabled', $args['options'])) {
                        $args['selected'] = 'wireless-enabled';
                    } elseif (count($args['options']) > 0) {
                        $args['selected'] = $args['options'][0];
                    }
                } elseif (strpos($args['attribute'], 'front-bench') !== false) {
                    // For front bench, default to 'stainless-steel' if available
                    if (in_array('stainless-steel', $args['options'])) {
                        $args['selected'] = 'stainless-steel';
                    } elseif (in_array('wood', $args['options'])) {
                        $args['selected'] = 'wood';
                    } elseif (count($args['options']) > 0) {
                        $args['selected'] = $args['options'][0];
                    }
                } else {
                    // For other attributes, select the first option if only one option
                    if (count($args['options']) == 1) {
                        $args['selected'] = $args['options'][0];
                    }
                }
            }
        }

        // Enhanced debug output for default option selection
        if (strpos($args['attribute'], 'bundles') !== false && !isset($args['debug_logged'])) {
            $product_id = $product ? $product->get_id() : 'unknown';
            $selected = isset($args['selected']) ? $args['selected'] : 'none';
            $options = isset($args['options']) ? implode(', ', $args['options']) : 'none';
            echo '<!-- DEBUG: woo_select_default_option - Product: ' . $product_id . ' - Attribute: ' . $args['attribute'] . ' - Options: ' . $options . ' - Selected: ' . $selected . ' -->';
            $args['debug_logged'] = true;
        }

        return $args;
    }

    /**
     * Set default selection for CommerceKit swatches
     */
    function commercekit_default_selection($args) {
        // Get the product to check for default attributes
        global $product;

        if (!$product) {
            $product_id = get_queried_object_id();
            $product = wc_get_product($product_id);
        }

        if ($product && $product->is_type('variable')) {
            $default_attributes = $product->get_default_attributes();
            $attribute_name = str_replace('attribute_', '', $args['attribute']);

            // Check if this attribute has a default value
            if (isset($default_attributes[$attribute_name])) {
                $args['selected'] = $default_attributes[$attribute_name];
            } else {
                // If no default is set, try to set a sensible default
        if (strpos($args['attribute'], 'bundles') !== false) {
                    // For bundles, prioritize: pro-bundle > basic-bundle > grill-only
                    if (isset($args['options']) && in_array('pro-bundle', $args['options'])) {
                        $args['selected'] = 'pro-bundle';
                    } elseif (isset($args['options']) && in_array('basic-bundle', $args['options'])) {
                        $args['selected'] = 'basic-bundle';
                    } elseif (isset($args['options']) && in_array('grill-only', $args['options'])) {
                        $args['selected'] = 'grill-only';
                    } elseif (isset($args['options']) && count($args['options']) > 0) {
                        $args['selected'] = $args['options'][0];
                    }
                } elseif (strpos($args['attribute'], 'controller') !== false) {
                    // For controller, default to 'wireless-enabled' if available
                    if (isset($args['options']) && in_array('wireless-enabled', $args['options'])) {
                        $args['selected'] = 'wireless-enabled';
                    } elseif (isset($args['options']) && count($args['options']) > 0) {
                        $args['selected'] = $args['options'][0];
                    }
                } elseif (strpos($args['attribute'], 'front-bench') !== false) {
                    // For front bench, default to 'stainless-steel' if available
                    if (isset($args['options']) && in_array('stainless-steel', $args['options'])) {
                        $args['selected'] = 'stainless-steel';
                    } elseif (isset($args['options']) && in_array('wood', $args['options'])) {
                        $args['selected'] = 'wood';
                    } elseif (isset($args['options']) && count($args['options']) > 0) {
                        $args['selected'] = $args['options'][0];
                    }
                }
            }
        }

        // Enhanced debug output for CommerceKit default selection
        if (strpos($args['attribute'], 'bundles') !== false && !isset($args['debug_logged'])) {
            $product_id = $product ? $product->get_id() : 'unknown';
            $selected = isset($args['selected']) ? $args['selected'] : 'none';
            $options = isset($args['options']) ? implode(', ', $args['options']) : 'none';
            echo '<!-- DEBUG: commercekit_default_selection - Product: ' . $product_id . ' - Attribute: ' . $args['attribute'] . ' - Options: ' . $options . ' - Selected: ' . $selected . ' -->';
            $args['debug_logged'] = true;
        }

        return $args;
    }

    public function get_style_depends()
    {
        $deps = ['pro-tiles-elementor', 'zg-savings-accordion'];
        if ( function_exists('is_product') && is_product() ) {
            $deps[] = 'commercekit-attribute-swatches-css';
            $deps[] = 'woo-variation-swatches-frontend';
        } else {
            $deps[] = 'woo-variation-swatches-frontend';
        }
        return $deps;
    }

    public function get_script_depends()
    {
        return ['pro-tiles-general', 'zg-savings-accordion'];
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
    /**
     * Get button sizes.
     *
     * Retrieve an array of button sizes for the button widget.
     *
     * @since 1.0.0
     * @access public
     * @static
     *
     * @return array An array containing button sizes.
     */
    public static function get_button_sizes()
    {
        return [
            'xs' => __('Extra Small', 'elementor'),
            'sm' => __('Small', 'elementor'),
            'md' => __('Medium', 'elementor'),
            'lg' => __('Large', 'elementor'),
            'xl' => __('Extra Large', 'elementor'),
        ];
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'section_product',
            [
                'label' => __('Product', 'elementor-pro'),
            ]
        );

		$this->add_control(
			'product_id',
			[
				'label' => __('Product', 'elementor-pro'),
				'type' => Module::QUERY_CONTROL_ID,
				'options' => [],
				'label_block' => true,
				'autocomplete' => [
					'object' => Module::QUERY_OBJECT_POST,
					'query' => [
						'post_type' => ['product'],
					],
				],
			]
		);

        $this->add_control(
            'dropdown_location',
            [
                'label' => __('Dropdown Location', 'elementor-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'above_atc',
                'options' => [
                    'above_atc' => __('Above Add to Cart Button', 'elementor-pro'),
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



        $this->end_controls_section();

        // Section Arrangement Controls
        $this->start_controls_section(
            'section_arrangement',
            [
                'label' => __('Section Arrangement', 'elementor-pro'),
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
            ]
        );

        $this->add_control(
            'front_bench_options_order',
            [
                'label' => __('Front Bench Options Order', 'elementor-pro'),
                'type' => Controls_Manager::SELECT,
                'default' => 'wood,stainless-steel',
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
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_button',
            [
                'label' => __('Cart Button', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_type',
            [
                'label' => __('Type', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Default', 'elementor'),
                    'info' => __('Info', 'elementor'),
                    'success' => __('Success', 'elementor'),
                    'warning' => __('Warning', 'elementor'),
                    'danger' => __('Danger', 'elementor'),
                ],
                'prefix_class' => 'elementor-button-',
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => __('Add To Cart', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => __('Add To Cart', 'elementor'),
                'placeholder' => __('Add To Cart', 'elementor'),
            ]
        );

        $this->add_control(
            'link',
            [
                'type' => Controls_Manager::HIDDEN,
                'default' => [
                    'url' => '',
                ],
            ]
        );

        $this->add_responsive_control(
            'addtocart_width',
            [
                'label' => __('Add To Cart Width', 'elementor') . ' (px)',
                'type' => Controls_Manager::SLIDER,
                'desktop_default' => ['size' => '100', 'unit' => "%"],
                'tablet_default' => ['size' => '100', 'unit' => "%"],
                'mobile_default' => ['size' => '100', 'unit' => "%"],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 2000,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%', 'em'],

                'selectors' => [

                    '{{WRAPPER}} .elementor-button.single_add_to_cart_button' => 'width: {{SIZE}}{{UNIT}}',

                ],

            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => __('Alignment', 'elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left'    => [
                        'title' => __('Left', 'elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'elementor'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'prefix_class' => 'elementor%s-align-',
                'desktop_default' => 'left',
                'tablet_default' => 'left',
                'mobile_default' => 'center',
            ]
        );

        $this->add_control(
            'size',
            [
                'label' => __('Size', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => 'sm',
                'options' => self::get_button_sizes(),
                'style_transfer' => true,
                'condition' => [
                    'show_quantity' => 'no',
                ],
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label' => __('Icon', 'elementor'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,

            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label' => __('Icon Position', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => 'left',
                'options' => [
                    'left' => __('Before', 'elementor'),
                    'right' => __('After', 'elementor'),
                ],
                'condition' => [
                    'selected_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'icon_indent',
            [
                'label' => __('Icon Spacing', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .elementor-button .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                ],
                'selector' => '{{WRAPPER}} .elementor-button.single_add_to_cart_button',
            ]
        );

        $this->add_group_control(
            Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .elementor-button.single_add_to_cart_button',
            ]
        );

        $this->start_controls_tabs('tabs_button_style');

        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => __('Normal', 'elementor'),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#F3E9DC',
                'selectors' => [
                    '{{WRAPPER}} .elementor-button.single_add_to_cart_button' => 'fill: {{VALUE}}; color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'label' => __('Background', 'elementor'),
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .elementor-button.single_add_to_cart_button',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#BC3116',
                    ],
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => __('Hover', 'elementor'),
            ]
        );

        $this->add_control(
            'hover_color',
            [
                'label' => __('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .elementor-button:hover, {{WRAPPER}} .elementor-button:focus' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .elementor-button.single_add_to_cart_button:hover svg, {{WRAPPER}} .elementor-button:focus svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'button_background_hover',
                'label' => __('Background', 'elementor'),
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .elementor-button.single_add_to_cart_button:hover, {{WRAPPER}} .elementor-button:focus',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ], 'color' => [
                        'default' => '#BC3116',
                    ],
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Border Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'condition' => [
                    'border_border!' => '',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button.single_add_to_cart_button:hover, {{WRAPPER}} .elementor-button:focus' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_animation',
            [
                'label' => __('Hover Animation', 'elementor'),
                'type' => Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'selector' => '{{WRAPPER}} .elementor-button.single_add_to_cart_button',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default'    => [
                    'top' => 100,
                    'bottom' => 100,
                    'left' => 100,
                    'right' => 100,
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button.single_add_to_cart_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .elementor-button.single_add_to_cart_button',
            ]
        );

        $this->add_responsive_control(
            'text_padding',
            [
                'label' => __('Padding', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button.single_add_to_cart_button .elementor-button-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],

                'desktop_default'    => ['top' => 5, 'bottom' => 5, 'left' => 45, 'right' => 45],
                'tablet_default'    => ['top' => 5, 'bottom' => 5, 'left' => 45, 'right' => 45],
                'mobile_default'    => ['top' => 5, 'bottom' => 5, 'left' => 45, 'right' => 45],
                'separator' => 'before',
            ]
        );


        $this->add_control(
            'view',
            [
                'label' => __('View', 'elementor'),
                'type' => Controls_Manager::HIDDEN,
                'default' => 'traditional',
            ]
        );
        $this->end_controls_section();

        // $this->start_controls_section(
        //     'bundle_prefix',
        //     [
        //         'label' => __('Bundle Prefix', 'elementor'),
        //         'tab' => Controls_Manager::TAB_STYLE,
        //     ]
        // );
        // $this->add_control(
        //     'bundle_prefix_text',
        //     [
        //         'label'   => __('Bundle Prefix Text', 'elementor'),
        //         'type'    => Controls_Manager::TEXT,
        //         'default' => 'Choose Your Bundle:'
        //     ]
        // );
        // $this->add_control(
        //     'bundle_prefix_color',
        //     [
        //         'label'     => __('Text Color', 'elementor'),
        //         'type'         => Controls_Manager::COLOR,
        //         'default'     => '#555555',
        //         'selectors' => [
        //             '{{WRAPPER}} .variations td.label label, .woo-selected-variation-item-name' => 'color: {{VALUE}};'

        //         ],

        //     ]
        // );
        // $this->add_group_control(
        //     Group_Control_Typography::get_type(),
        //     [
        //         'name' => 'bundle_prefix_typography',
        //         'selector' => '{{WRAPPER}} .variations td.label label, .woo-selected-variation-item-name',
        //         'fields_options' => [
        //             'font_weight' => ['default' => '600'],
        //             'font_family' => ['default' => "Inter",],
        //             'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
        //             'line_height' => ['default' => ['unit' => 'px', 'size' => '22']],
        //             'text_transform' => ['default' => 'capitalize']
        //         ],
        //     ]
        // );
        // $this->add_responsive_control(
        //     'bundle_prefix_icon',
        //     [
        //         'label' => __('Prefix Icon', 'elementor'),
        //         'type' => Controls_Manager::ICONS,
        //         'fa4compatibility' => 'icon',
        //         'skin' => 'inline',
        //         'label_block' => false,
        //     ]
        // );

        // $this->add_control(
        //     'bundle_prefix_icon_width',
        //     [
        //         'label'     => __('Icon Width', 'elementor'),
        //         'type'         => Controls_Manager::TEXT,
        //         'default'     => '50',
        //         'selectors' => [
        //             '{{WRAPPER}} .variations .elementor-bundle-text-icon svg' => 'width: {{VALUE}}px;'

        //         ],

        //     ]
        // );

        // $this->add_control(
        //     'bundle_prefix_icon_height',
        //     [
        //         'label'     => __('Icon Height', 'elementor'),
        //         'type'         => Controls_Manager::TEXT,
        //         'default'     => '50',
        //         'selectors' => [
        //             '{{WRAPPER}} .variations .elementor-bundle-text-icon svg' => 'height: {{VALUE}}px;'

        //         ],

        //     ]
        // );

        // $this->add_responsive_control(
        //     'bundle_prefix_icon_indent',
        //     [
        //         'label' => __('Icon Spacing', 'elementor'),
        //         'type' => Controls_Manager::SLIDER,
        //         'range' => [
        //             'px' => [
        //                 'max' => 100,
        //             ],
        //         ],
        //         'desktop_default' => ['size' => '5', 'unit' => "px"],
        //         'tablet_default' => ['size' => '5', 'unit' => "px"],
        //         'mobile_default' => ['size' => '5', 'unit' => "px"],
        //         'selectors' => [
        //             '{{WRAPPER}} .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
        //             '{{WRAPPER}} .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
        //         ],
        //     ]
        // );

        // $this->add_responsive_control(
        //     'bundle_prefix_padding',
        //     [
        //         'label' => __('Padding', 'elementor'),
        //         'type' => Controls_Manager::DIMENSIONS,
        //         'size_units' => ['px', '%'],
        //         'selectors' => [
        //             '{{WRAPPER}} .variations td.label label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        //         ],

        //     ]
        // );

        // $this->end_controls_section();

        // ACTIVE SECTION - Variant Tiles (CommerceKit system)
        // NOTE: This section controls actual tile styling (.variable-item, .cgkit-swatch)
        // Used by current CommerceKit implementation
        $this->start_controls_section(
            'variant_tiles',
            [
                'label' => __('Variant Tiles', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );



        $this->add_responsive_control(
            'tiles_width',
            [
                'label' => __('Tiles Width', 'elementor') . ' (px)',
                'type' => Controls_Manager::SLIDER,
                'desktop_default' => ['size' => '180', 'unit' => "px"],
                'tablet_default' => ['size' => '160', 'unit' => "px"],
                'mobile_default' => ['size' => '160', 'unit' => "px"],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 2000,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%', 'em'],

                'selectors' => [

                    '{{WRAPPER}} .variable-item' => 'width: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .elementor-widget-container .variable-items-wrapper .variable-item.button-variable-item' =>  'width: {{SIZE}}{{UNIT}} !important'
                ],

            ]
        );
        $this->add_responsive_control(
            'Vertical_space',
            [
                'label' => __('Tiles Vertical Spacing', 'elementor') . ' (px)',
                'type' => Controls_Manager::SLIDER,
                'desktop_default' => ['size' => 5, 'unit' => "px"],
                'tablet_default' => ['size' => 5, 'unit' => "px"],
                'mobile_default' => ['size' => 5, 'unit' => "px"],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'size_units' => ['px', '%', 'em'],
                'placeholder' => '5',
                'description' => __('Sets the default Vertical space between widgets (Default: 15)', 'elementor'),
                'selectors' => [
                    '.variable-items-wrapper .variable-item.button-variable-item' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .variable-item' => 'margin-bottom: {{SIZE}}{{UNIT}}; margin-top: {{SIZE}}{{UNIT}}; ',
                ],
            ]
        );
        $this->add_responsive_control(
            'horizontal_space',
            [
                'label' => __('Tiles Horizontal Spacing', 'elementor') . ' (px)',
                'type' => Controls_Manager::SLIDER,
                'desktop_default' => ['size' => 5, 'unit' => "px"],
                'tablet_default' => ['size' => 5, 'unit' => "px"],
                'mobile_default' => ['size' => 5, 'unit' => "px"],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'size_units' => ['px', '%', 'em'],
                'placeholder' => '5',
                'description' => __('Sets the default Horizontal space between tiles (Default: 15)', 'elementor'),
                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper .variable-item.button-variable-item' => 'margin-right: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .variable-item' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'tiles_padding',
            [
                'label' => __('Tiles Padding', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'desktop_default' => ['top' => 0, 'bottom' => 5, 'left' => 10, 'right' => 10],
                'tablet_default' => ['top' => 0, 'bottom' => 5, 'left' => 10, 'right' => 10],
                'mobile_default' => ['top' => 0, 'bottom' => 5, 'left' => 10, 'right' => 10],
                'selectors' => [
                    '{{WRAPPER}} .elementor-widget-container .variable-items-wrapper .variable-item.button-variable-item .variable-item-contents' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .variable-items-wrapper .variable-item .variable-item-contents' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'tiles_alignment',
            [
                'label' => __('Alignment', 'elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'elementor'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .woo-variation-swatches.elementor-page .variable-items-wrapper .variable-item:not(.radio-variable-item).button-variable-item' => 'text-align: {{VALUE}};',
                ],

            ]
        );
        $this->add_control(
            'tiles_background_color',
            [
                'label' => __('Tiles Background Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper li.variable-item:not(.radio-variable-item)' => 'background-color: {{VALUE}};',

                ],
                'separator' => 'before'
            ]
        );
        $this->add_responsive_control(
            'tiles_border_width',
            [
                'label' => __('Border Width', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'desktop_default' => ['unit' => 'px', 'size' => '1'],
                'tablet_default' => ['unit' => 'px', 'size' => '1'],
                'mobile_default' => ['unit' => 'px', 'size' => '1'],
                'selectors' => [
                    '{{WRAPPER}} .woo-variation-swatches .variable-items-wrapper .variable-item.button-variable-item' => 'border: {{SIZE}}{{UNIT}} solid;',
                    '{{WRAPPER}} .variable-item' => 'border: {{SIZE}}{{UNIT}} solid;',
                ],


            ]
        );
        $this->add_responsive_control(
            'tiles_border_radius',
            [
                'label' => __('Border Radius', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'desktop_default' => ['unit' => 'px', 'size' => '2'],
                'tablet_default' => ['unit' => 'px', 'size' => '2'],
                'mobile_default' => ['unit' => 'px', 'size' => '2'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper .variable-item.button-variable-item ' => 'border-radius:{{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .variable-item' => 'border-radius: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .variable-item-image' => 'border-radius: {{SIZE}}{{UNIT}} {{SIZE}}{{UNIT}} 0px 0px;'
                ],
            ]
        );

        $this->add_control(
            'tiles_border_color',
            [
                'label' => __('Border Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#e6e5e5',
                'selectors' => [
                    '{{WRAPPER}} .woo-variation-swatches .variable-items-wrapper .variable-item.button-variable-item' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .variable-item' => 'border-color: {{VALUE}};'

                ],
            ]
        );
        $this->add_control(
            'selected_tiles_background_color',
            [
                'label' => __('Selected Tiles Background Color', 'elementor'),
                'type' => Controls_Manager::COLOR,

                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper li.variable-item.selected:not(.radio-variable-item)' => 'background-color: {{VALUE}};',

                ],
                'separator' => 'before'
            ]
        );
        $this->add_responsive_control(
            'selected_borders_width',
            [
                'label' => __('Selected Tile Border Width', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'default' => ['unit' => 'px', 'size' => '1'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper .variable-item.button-variable-item.selected' => 'border-width: {{SIZE}}{{UNIT}}; border-style: solid;',
                    '{{WRAPPER}} .variable-item.selected ' => 'border-width: {{SIZE}}{{UNIT}};',
                ],

            ]
        );
        $this->add_control(
            'selected_border_color',
            [
                'label' => __('Select Tile Border Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#752D20',
                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper .variable-item.button-variable-item.selected ' => 'border-color: {{VALUE}} !important;',
                    '{{WRAPPER}} .variable-item.selected' => 'border-color: {{VALUE}} !important;'

                ],
            ]
        );

        $this->end_controls_section();
        // LEGACY SECTION - Offer Label (variable-item system)
        // NOTE: This section targets old .variable-item-span-button-offers classes
        // Current system uses CommerceKit with .tile-offer badges instead
        // Kept for backward compatibility - may be removed in future versions
        $this->start_controls_section(
            'offer_label',
            [
                'label' => __('Offer Label', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_background_color',
            [
                'label' => __('Label Background Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#BC3116',
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-offers' => 'background-color: {{VALUE}};'

                ],
            ]
        );
        $this->add_control(
            'label_color',
            [
                'label' => __('Label Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#F3E9DC',
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-offers' => 'color: {{VALUE}};'

                ],
            ]
        );
        $this->add_responsive_control(
            'label_margin',
            [
                'label' => __('Label Margin', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'desktop_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 10, 'right' => 0],
                'tablet_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 10, 'right' => 0],
                'mobile_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 10, 'right' => 0],
                'allowed_dimensions' => ['top', 'left', 'right', 'bottom'],
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-offers' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'label_padding',
            [
                'label' => __('Label Padding', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'desktop_default' =>  ['top' => 2, 'bottom' => 2, 'left' => 5, 'right' => 5],
                'tablet_default' =>  ['top' => 2, 'bottom' => 2, 'left' => 5, 'right' => 5],
                'mobile_default' =>  ['top' => 2, 'bottom' => 2, 'left' => 5, 'right' => 5],
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-offers' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'label_border_radius',
            [
                'label' => __('Border Radius', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'desktop_default' =>  ['top' => 0, 'bottom' => 8, 'left' => 8, 'right' => 0],
                'tablet_default' =>  ['top' => 0, 'bottom' => 8, 'left' => 8, 'right' => 0],
                'mobile_default' =>  ['top' => 0, 'bottom' => 8, 'left' => 8, 'right' => 0],
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-offers' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .variable-item .variable-item-span-button-offers',
                'fields_options' => [
                    'font_weight' => ['default' => '600'],
                    'font_family' => ['default' => "Inter",],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '9']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '12.78']]
                ],
            ]
        );
        $this->add_responsive_control(
            'label_alignment',
            [
                'label' => __('Alignment', 'elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'elementor'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'desktop_default' => 'center',
                'tablet_default' => 'center',
                'mobile_default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper .variable-item-span-button-offers' => 'text-align: {{VALUE}};',
                ],

            ]
        );
        $this->end_controls_section();

        // NEW SECTION - Badge Controls
        $this->start_controls_section(
            'badge_controls',
            [
                'label' => __('Tile Badges', 'elementor'),
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

        // LEGACY SECTION - Regular Price (variable-item system)
        // NOTE: This section targets old .variable-item-span-button-price classes
        // Current system uses CommerceKit with .tile-price classes instead
        // Kept for backward compatibility - may be removed in future versions
        $this->start_controls_section(
            'price',
            [
                'label' => __('Regular Price', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'price_width',
            [
                'label' => __('Price Width', 'elementor') . ' (%)',
                'type' => Controls_Manager::SLIDER,
                'desktop_default' => ['size' => '45', 'unit' => "%"],
                'tablet_default' => ['size' => '45', 'unit' => "%"],
                'mobile_default' => ['size' => '45', 'unit' => "%"],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 2000,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%', 'em'],

                'selectors' => [

                    '{{WRAPPER}} .variable-item .variable-item-span-button-price' => 'width: {{SIZE}}{{UNIT}}; display:inline-block',
                    '{{WRAPPER}} .variable-item .variable-item-span-button-price' =>  'width: {{SIZE}}{{UNIT}}; display:inline-block'
                ],

            ]
        );
        $this->add_responsive_control(
            'price_description_align',
            [
                'label' => __('Alignment', 'elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'elementor'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'desktop_default' => 'right',
                'tablet_default' => 'right',
                'mobile_default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-price' => 'text-align: {{VALUE}}; float:{{VALUE}};',
                ],

            ]
        );
        $this->add_control(
            'regular_price_color',
            [
                'label' => __('Regular Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2E282A',
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-price .reg-price' => 'color: {{VALUE}};'

                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'regular_price_typography',
                'selector' => '{{WRAPPER}} .variable-item .variable-item-span-button-price .reg-price',
                'fields_options' => [
                    'font_weight' => ['default' => '600'],
                    'font_family' => ['default' => "Inter",],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '12']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '15']]
                ],
            ]
        );
        $this->add_responsive_control(
            'regular_price_padding',
            [
                'label' => __('Padding', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'desktop_default' => ['top' => 15, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'tablet_default' => ['top' => 15, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'mobile_default' => ['top' => 15, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'selectors' => [
                    '{{WRAPPER}} .variable-item-span-button-price ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
        // LEGACY SECTION - Sale Price (variable-item system)
        // NOTE: This section targets old .variable-item-span-button-price classes
        // Current system uses CommerceKit with .tile-price classes instead
        // Kept for backward compatibility - may be removed in future versions
        $this->start_controls_section(
            'sale_price',
            [
                'label' => __('Sale Price', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'regular_delete_price_color',
            [
                'label' => __('Regular Price Delete Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#555555',
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-price.s-price .reg-price' => 'color: {{VALUE}};'

                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'regular_delete_price_typography',
                'selector' => '.variable-item .variable-item-span-button-price.s-price .reg-price',
                'fields_options' => [
                    'font_weight' => ['default' => '400'],
                    'font_family' => ['default' => 'Inter'],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '12']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '15']],
                    'font_style'  => ['default' => 'normal']
                ],
            ]
        );
        $this->add_control(
            'sale_price_color',
            [
                'label' => __('Sale Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2E282A',
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-price .sale-price' => 'color: {{VALUE}};'

                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'sale_price_typography',
                'selector' => '.variable-item .variable-item-span-button-price .sale-price',
                'fields_options' => [
                    'font_weight' => ['default' => '600'],
                    'font_family' => ['default' => 'Inter'],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '12']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '15']]
                ],
            ]
        );
        $this->add_responsive_control(
            'sale_price_padding',
            [
                'label' => __('Padding', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'desktop_default' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'tablet_default' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'mobile_default' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'selectors' => [
                    '{{WRAPPER}} .s-price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
        // LEGACY SECTION - Bundle Name (variable-item system)
        // NOTE: This section targets old .variable-item-span-button classes
        // Current system uses CommerceKit with .tile-title classes instead
        // Kept for backward compatibility - may be removed in future versions
        $this->start_controls_section(
            'attribute_label',
            [
                'label' => __('Bundle Name', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_responsive_control(
            'attri_width',
            [
                'label' => __('Attribute Width', 'elementor') . ' (%)',
                'type' => Controls_Manager::SLIDER,
                'desktop_default' => ['size' => '49', 'unit' => "%"],
                'tablet_default' => ['size' => '49', 'unit' => "%"],
                'mobile_default' => ['size' => '49', 'unit' => "%"],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 2000,
                        'step' => 10,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => ['px', '%', 'em'],

                'selectors' => [

                    '{{WRAPPER}} .variable-item .variable-item-span' => 'width: {{SIZE}}{{UNIT}}; display:inline-block',
                    '{{WRAPPER}} .variable-item .variable-item-span' =>  'width: {{SIZE}}{{UNIT}}; display:inline-block'
                ],

            ]
        );

        $this->add_responsive_control(
            'attr_description_align',
            [
                'label' => __('Alignment', 'elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'elementor'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'desktop_default' => 'left',
                'tablet_default' => 'left',
                'mobile_default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span' => 'text-align: {{VALUE}};',
                    '{{WRAPPER}} .variable-item .variable-item-span' => 'text-align: {{VALUE}};',
                ],

            ]
        );
        $this->add_control(
            'attribute_label_color',
            [
                'label' => __('Attribute Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span.variable-item-span-button' => 'color: {{VALUE}};'
                ],
                'default' => '#2E282A'
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'attribute_label_typography',
                'selector' => '{{WRAPPER}} .variable-item .variable-item-span.variable-item-span-button',
                'fields_options' => [
                    'font_weight' => ['default' => '600'],
                    'font_family' => ['default' => 'Inter'],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '12']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '15']]
                ],
            ]
        );
        $this->add_responsive_control(
            'attr_price_padding',
            [
                'label' => __('Padding', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'desktop_default' => ['top' => 15, 'bottom' => 5, 'left' => 0, 'right' => 0],
                'tablet_default' => ['top' => 15, 'bottom' => 5, 'left' => 0, 'right' => 0],
                'mobile_default' => ['top' => 15, 'bottom' => 5, 'left' => 0, 'right' => 0],
                'selectors' => [
                    '{{WRAPPER}} .variable-items-wrapper .variable-item .variable-item-span.variable-item-span-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; width:auto',
                    '{{WRAPPER}} .variable-items-wrapper .variable-item.button-variable-item div.variable-item-span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; width:auto'
                ],
            ]
        );
        $this->end_controls_section();

        // ACTIVE SECTION - Total Price (WooCommerce variation display)
        // NOTE: This section controls WooCommerce variation price display
        // Used by current system for showing total prices
        $this->start_controls_section(
            'total_price',
            [
                'label' => __('Total Price', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'total_price_prefix_text',
            [
                'label' => __('Prefix Text', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Total:'
            ]
        );

        $this->add_control(
            'total_price_prefix_label_color',
            [
                'label' => __('Prefix Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#555555',
                'selectors' => [
                    '{{WRAPPER}} .price-preffix' => 'color: {{VALUE}};'

                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => __('Prefix Text Typography', 'elementor'),
                'name' => 'total_price_prefix_typography',
                'selector' => '{{WRAPPER}} .price-preffix',
                'fields_options' => [
                    'font_weight' => ['default' => '400'],
                    'font_family' => ['default' => 'Inter',],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '15']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '18.15']]
                ],
            ]
        );
        $this->add_control(
            'total_price_color',
            [
                'label' => __('Price Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#2E282A',
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-Price-amount' => 'color: {{VALUE}};'

                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'total_price_typography',
                'selector' => '{{WRAPPER}} .woocommerce-Price-amount',
                'fields_options' => [
                    'font_weight' => ['default' => '600'],
                    'font_family' => ['default' => 'Inter',],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '22']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '26.63']]
                ],
            ]
        );
        $this->add_responsive_control(
            'total_price_padding',
            [
                'label' => __('Padding', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' =>  ['top' => 15, 'bottom' => 15, 'left' => 0, 'right' => 0],
                'desktop_default' =>  ['top' => 15, 'bottom' => 15, 'left' => 0, 'right' => 0],
                'tablet_default' =>  ['top' => 15, 'bottom' => 15, 'left' => 0, 'right' => 0],
                'mobile_default' =>  ['top' => 15, 'bottom' => 15, 'left' => 0, 'right' => 0],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-variation-price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'total_price_align',
            [
                'label' => __('Alignment', 'elementor'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified', 'elementor'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'desktop_default' => 'left',
                'tablet_default' => 'left',
                'mobile_default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} woocommerce-variation-price' => 'text-align: {{VALUE}};',
                ],

            ]
        );
        $this->end_controls_section();
        // ACTIVE SECTION - Product Image (CommerceKit tiles)
        // NOTE: This section controls image height in CommerceKit tiles
        // Used by current system for tile image dimensions
        $this->start_controls_section(
            'product-image',
            [
                'label' => __('Product Image', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'product-image-height',
            [
                'label' => __('Image Height', 'elementor'),
                'type' =>  Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 500,
                    ],

                ],
                'size_units' => ['px', '%', 'vh'],
                'default' =>  ['size' => '130', 'unit' => "px"],
                'desktop_default' =>   ['size' => '130', 'unit' => "px"],
                'tablet_default' => ['size' => '130', 'unit' => "px"],
                'mobile_default' => ['size' => '130', 'unit' => "px"],
                'selectors' => [
                    '{{WRAPPER}} .variable-item-image' => 'height: {{size}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();


    }

    protected function render()
    {
        global $product, $post;
        $settings = $this->get_settings_for_display();
        $settings['product_id'] = $post->ID; //$this->get_settings('product_id');
		if(empty(wc_get_product($settings['product_id']))){
        	$settings['product_id'] = $this->get_settings('product_id');
		}
        $settings['number_of_tiles'] = $this->get_settings('number_of_tiles');
        if (!empty($settings['product_id'])) {
            $product_id = $settings['product_id'];
        } elseif (wp_doing_ajax()) {
            $product_id = $_POST['post_id'];
        } else {
            $product_id = get_queried_object_id();
        }
        $this->add_render_attribute('_wrapper', 'class', 'summary product');

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



        // add_action('woocommerce_before_single_variation', array($this, 'action_wc_before_single_variation'), 20);
        add_filter('woocommerce_get_stock_html', '__return_empty_string');
        add_filter('woocommerce_product_single_add_to_cart_text', $text_callback);
        // add_filter('wvs_default_variable_item', array($this, 'protiles_wvs_variable_item'), 60, 5);
        // add_filter('variant_tiles_dropdown_not_continue', function($continue){
        //     return true;
        // });
        add_filter('woocommerce_dropdown_variation_attribute_options_html', array($this, 'protiles_wvs_swatch_variable_item'), 60, 2);
        add_filter('esc_html', [$this, 'unescape_html'], 10, 2);
        // add_filter('woocommerce_attribute_label', array($this, 'get_attribute_label'), 10, 3);
        // add_filter('woocommerce_short_description', array($this, 'pvt_add_text_short_descriptions_v2'), 50, 1);
        // add_filter('woocommerce_available_variation', array($this, 'variation_price_preffix'), 10, 3);
        // Add critical hook for variation data enrichment (accordion, pricing, etc.)
        add_filter('woocommerce_available_variation', array($this, 'vt_enrich_variation_payload'), 20, 3);
        add_filter("wvs_variable_items_wrapper", array($this, 'wvs_custom_variable_items_wrapper'), 10, 4);
        if ('yes' !== $settings['show_quantity']) {
            add_filter('woocommerce_is_sold_individually', array($this, 'pvt_remove_all_quantity_fields'), 10, 2);
        }

        // Add hook to render savings and accordion before Add to Cart button
        add_action('woocommerce_before_add_to_cart_button', array($this, 'render_savings_and_accordion_hook'), 20);

        // Add arrangement filters
        $this->setup_arrangement_filters($settings);

        ob_start();

        woocommerce_template_single_add_to_cart();
        $form = ob_get_clean();
        $form = str_replace('single_add_to_cart_button', 'single_add_to_cart_button elementor-button', $form);
        echo $form;

        // Add stock message section below Add to Cart button
        echo '<div style="text-align: center; width: 100%;">';
        echo '<div id="vt-in-stock-message" style="margin-top: 10px; display: none; align-items: center; justify-content: center; color: #333; font-size: 14px; font-weight: bold; line-height: 1;"><div class="stock-dot in-stock" style="margin-right: 8px; vertical-align: middle; display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: #4CAF50; position: relative;"></div><span style="vertical-align: middle;">In Stock</span></div>';
        echo '<div id="vt-low-stock-message" style="margin-top: 10px; display: none; align-items: center; justify-content: center; color: #333; font-size: 14px; font-weight: bold; line-height: 1;"><div class="stock-dot low-stock" style="margin-right: 8px; vertical-align: middle; display: inline-block; width: 12px; height: 12px; border-radius: 50%; background-color: #FFC107; position: relative;"></div><span style="vertical-align: middle;">Low in Stock</span></div>';
        echo '</div>';

        // Add custom CSS for arrangement styling
        echo '<style>
        .zg-variant-tiles-arranged .variations {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .zg-variant-tiles-arranged .variations .value {
            margin-bottom: 0;
        }
        .zg-variant-tiles-arranged .variations .label {
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
        }
        </style>';
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var variationsForm = document.querySelector(".variations_form");
            if (variationsForm) {
                variationsForm.classList.add("zg-variant-tiles-arranged");
            }
        });
        </script>';

?>

        <?php
        if ('yes' !== $settings['show_quantity']) {
            remove_filter('woocommerce_is_sold_individually', 'pvt_remove_all_quantity_fields');
        }
        // remove_filter('woocommerce_available_variation', array($this, 'variation_price_preffix'));
        remove_filter('woocommerce_available_variation', array($this, 'vt_enrich_variation_payload'), 20, 3);
        remove_filter('woocommerce_product_single_add_to_cart_text', $text_callback);


        remove_filter('woocommerce_get_stock_html', '__return_empty_string');
        remove_filter('esc_html', [$this, 'unescape_html']);
        // remove_filter('woocommerce_attribute_label', array($this, 'get_attribute_label'), 10, 3);
        // remove_filter('woocommerce_short_description', array($this, 'pvt_add_text_short_descriptions_v2'), 50, 1);

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
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('ZG Variant Tiles Arrangement: ' . $message);
            if ($data !== null) {
                error_log('ZG Variant Tiles Data: ' . print_r($data, true));
            }
        }
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

        $settings = $this->get_settings_for_display();

        $migrated = isset($settings['__fa4_migrated']['selected_icon']);
        $is_new = empty($settings['icon']) && Icons_Manager::is_migration_allowed();

        if (!$is_new && empty($settings['icon_align'])) {
            // @todo: remove when deprecated
            // added as bc in 2.6
            //old default
            $settings['icon_align'] = $this->get_settings('icon_align');
        }

        $this->add_render_attribute([
            'content-wrapper' => [
                'class' => 'elementor-button-content-wrapper',
            ],
            'icon-align' => [
                'class' => [
                    'elementor-button-icon',
                    'elementor-align-icon-left',
                ],
            ],
            'text' => [
                'class' => 'elementor-button-text',
            ],
        ]);
        if (!$is_new && empty($settings['number_of_tiles'])) {
            // @todo: remove when deprecated
            // added as bc in 2.6
            //old default
            $settings['number_of_tiles'] = $this->get_settings('number_of_tiles');
        }


        $this->add_inline_editing_attributes('text', 'none');
        ?>
        <span <?php echo $this->get_render_attribute_string('content-wrapper'); ?>
            >
            <?php if (!empty($settings['icon']) || !empty($settings['selected_icon']['value'])) : ?>
                <span <?php echo $this->get_render_attribute_string('icon-align'); ?>>
                    <?php if ($is_new || $migrated) :
                        Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']);
                    else : ?>
                        <i class="<?php echo esc_attr($settings['icon']); ?>" aria-hidden="true"></i>
                    <?php endif;
                    $this->get_settings('icon_align'); ?>
                </span>
            <?php endif; ?>
            <span <?php echo $this->get_render_attribute_string('text'); ?>><?php echo $settings['text']; ?></span>
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
        $attribute_swatches  = get_post_meta($product_id, 'commercekit_attribute_swatches', true);
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
        $single_attr_oos  = array();

        $_variations = array();
        $_variations = array();
        $_var_images = array();
        $_gal_images = array();
        $any_attrib  = false;
        $variations  = commercekit_get_available_variations($product);




        $default_attributes = $product->get_default_attributes();






        if (is_array($variations) && count($variations)) {
            foreach ($variations as $id => $variation) {
                if (isset($variation['attributes']) && count($variation['attributes'])) {
                    // Use CommerceKit's variation image (same as old plugin)
                    $variation_img_id = isset($variation['cgkit_image_id']) ? $variation['cgkit_image_id'] : get_post_thumbnail_id($variation['variation_id']);
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
                                if (isset($variation['is_in_stock']) && 1 !== (int) $variation['is_in_stock']) {
                                    $single_attr_oos[$a_key][$a_value] = true;
                                }
                            }
                        }
                    }
                }
            }
            $cgkit_image_gallery = get_post_meta($product_id, 'commercekit_image_gallery', true);
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
        $item_oos_text  = ''; //esc_html__('Out of stock', 'commercegurus-commercekit');
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



            // $is_selected = (isset($args['selected']) && sanitize_title( $args['selected'] ) == $item_attri_val ) ? true : false;

            $selected       = (($args['selected'] === $item_attri_val) || $custom_selected )? 'cgkit-swatch-selected' : '';
            if ($as_button_style && 'button' === $swatch_type) {
                $selected .= ' button-fluid';
            }

            $image_label = esc_html(apply_filters('woocommerce_variation_option_name', $item->name));

            // Bundle names are being generated correctly

            // print_r($_variations[$attribute_raw][$item_attri_val]['variation']);
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
                        $swatch_html = '<span class="cross">&nbsp;</span><img alt="' . esc_attr($item->name) . '" width="' . esc_attr($var_image[1]) . '" height="' . esc_attr($var_image[2]) . '" src="' . esc_url($var_image[0]) . '" />' . $tile_title_html . $tile_price_html;
                    }
                }
            }

            // BADGE LOGIC MOVED TO ELEMENTOR WIDGET CONTROLS
            // RENDER OFFER LABEL BADGE FROM VARIANT TILE DATA
            // if (isset($_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label']) &&
            //     !empty($_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label'])) {
            //
            //     $offer_label = $_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label'];
            //     $swatch_html .= '<span class="tile-offer" style="position: absolute !important; left: 0 !important; right: 0 !important; max-width: max-content !important; margin: 0 auto !important; top: -12px !important; background: var(--vt-accent) !important; color: white !important; font-weight: bold !important; border-radius: 9999px !important; padding: 4px 12px !important; font-size: 12px !important; text-align: center !important; border: 2px solid var(--vt-accent) !important; z-index: 9999 !important; display: block !important; visibility: visible !important; opacity: 1 !important; white-space: nowrap !important; line-height: 1 !important;">' . esc_html($offer_label) . '</span>';
            // }

            // RENDER BADGE USING ELEMENTOR SETTINGS
            $swatch_html .= $this->render_tile_badge($attribute_raw, $item_attri_val, $_variations);

            // SIMPLE PRICE DISPLAY - EXACTLY LIKE TILES-OLD (which works perfectly)
            if(isset($_variations[$attribute_raw][$item_attri_val]['variation']['price_html']) && $swatch_type == 'image'){
                $swatch_html .= '<span class="tile-price">' . $_variations[$attribute_raw][$item_attri_val]['variation']['price_html'] . '</span>';
            }

            $item_title  = 'button' === $swatch_type && isset($attribute_swatches[$attribute_id][$item->term_id]['btn']) ? $attribute_swatches[$attribute_id][$item->term_id]['btn'] : $item->name;
            if (isset($single_attr_oos[$attribute_raw][$item_attri_val]) && true === $single_attr_oos[$attribute_raw][$item_attri_val]) {
                $selected  .= ' cgkit-as-outofstock';
                $item_title = $item_title . ' - ' . $item_oos_text;
            }
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
            $swatches_html .= sprintf('<li class="cgkit-attribute-swatch cgkit-%s %s" %s data-variation-id="%s"><button type="button" data-type="%s" data-attribute-value="%s" data-attribute-text="%s" aria-label="%s" data-oos-text="%s" title="%s" class="swatch cgkit-swatch %s" data-clicker="%s" data-gimg_id="%s">%s<span class="raw-badge"></span></button></li>', $swatch_type, $item_class, $item_tooltip, $_variations[$attribute_raw][$item_attri_val]['variation']['variation_id'], $swatch_type, esc_attr($item_attri_val), esc_attr($item->name), esc_attr($item_title), $item_oos_text, esc_attr($item_title), $selected, $selected, $item_gimg_id, $swatch_html);



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
                $swatch_html = '<span class="cross">&nbsp;</span><img alt="' . esc_attr($item->name) . '" width="' . esc_attr($image[1]) . '" height="' . esc_attr($image[2]) . '" src="' . esc_url($image[0]) . '" />' . ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            } else {
                // For bundle swatches, still show the title even if no image data
                $swatch_html = '<span class="cross">&nbsp;</span>' . ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            }

        } elseif ('color' === $swatch_type) {
            if (isset($data['clr']) && !empty($data['clr'])) {
                $bg_color2  = isset($data['clr2']) ? $data['clr2'] : '';
                $bg_type    = isset($data['ctyp']) ? (int) $data['ctyp'] : 1;
                $background = $data['clr'];
                if (2 === $bg_type && !empty($bg_color2)) {
                    $background = 'linear-gradient(135deg, ' . $data['clr'] . ' 50%, ' . $bg_color2 . ' 50%)';
                }
                $swatch_html = '<span class="cross">&nbsp;</span><span class="color-div" style="background: ' . esc_attr($background) . ';" data-color="' . esc_attr($data['clr']) . '" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            } else {
                $swatch_html = '<span class="cross">&nbsp;</span><span class="color-div" style="" data-color="" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            }
        } elseif ('button' === $swatch_type) {
            if (isset($data['btn']) && strlen($data['btn'])) {
                $swatch_html = '<span class="cross">&nbsp;</span>' . esc_attr($data['btn']);
            } else {
                $swatch_html = '<span class="cross">&nbsp;</span>' . esc_attr($item->name);
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
                $swatch_html = '<span class="cross">&nbsp;</span><img alt="' . esc_attr($item->name) . '" width="' . esc_attr($image[1]) . '" height="' . esc_attr($image[2]) . '" src="' . esc_url($image[0]) . '" />' . ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            } else {
                // For bundle swatches, still show the title even if no image data
                $swatch_html = '<span class="cross">&nbsp;</span>' . ($image_label ? '<span class="tile-title">'.$image_label.'</span>' : '');
            }

        } elseif ('color' === $swatch_type) {
            if (isset($data['clr']) && !empty($data['clr'])) {
                $bg_color2  = isset($data['clr2']) ? $data['clr2'] : '';
                $bg_type    = isset($data['ctyp']) ? (int) $data['ctyp'] : 1;
                $background = $data['clr'];
                if (2 === $bg_type && !empty($bg_color2)) {
                    $background = 'linear-gradient(135deg, ' . $data['clr'] . ' 50%, ' . $bg_color2 . ' 50%)';
                }
                $swatch_html = '<span class="cross">&nbsp;</span><span class="color-div" style="background: ' . esc_attr($background) . ';" data-color="' . esc_attr($data['clr']) . '" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            } else {
                $swatch_html = '<span class="cross">&nbsp;</span><span class="color-div" style="" data-color="" aria-hidden="true">&nbsp;' . esc_attr($item->name) . '</span>';
            }
        } elseif ('button' === $swatch_type) {
            if (isset($data['btn']) && strlen($data['btn'])) {
                $swatch_html = '<span class="cross">&nbsp;</span>' . esc_attr($data['btn']);
            } else {
                $swatch_html = '<span class="cross">&nbsp;</span>' . esc_attr($item->name);
            }
        }

        return $swatch_html;
    }



    public function vt_enrich_variation_payload($variation_data, $product, $variation){
        // Attach custom meta for accordion and badge
        $variation_data['_vt_offer_label'] = get_post_meta($variation->get_id(), '_vt_offer_label', true);
        $variation_data['_vt_dd_text']     = get_post_meta($variation->get_id(), '_vt_dd_text', true);
        $variation_data['_vt_dd_preview']  = get_post_meta($variation->get_id(), '_vt_dd_preview', true);

        // DEBUG: Log variant tile data enrichment
        if(strpos($product->get_name(), '450A') !== false){
            error_log('VARIANT TILE DEBUG: Enriching variation ' . $variation->get_id() . ' for product ' . $product->get_name());
            error_log('VARIANT TILE DEBUG: _vt_dd_text = ' . ($variation_data['_vt_dd_text'] ?: 'EMPTY'));
            error_log('VARIANT TILE DEBUG: _vt_dd_preview = ' . ($variation_data['_vt_dd_preview'] ?: 'EMPTY'));
            error_log('VARIANT TILE DEBUG: _vt_offer_label = ' . ($variation_data['_vt_offer_label'] ?: 'EMPTY'));
        }

        // Add variant tile image data
        $dd_image_id = get_post_meta($variation->get_id(), '_vt_dd_image_id', true);
        if ($dd_image_id && wp_attachment_is_image($dd_image_id)) {
            $variation_data['_vt_dd_image_url'] = wp_get_attachment_image_url($dd_image_id, 'medium');
        } else {
            $variation_data['_vt_dd_image_url'] = '';
        }

        // Pricing breakdown for accordion
        $regular = (float) wc_get_price_to_display( $variation, array( 'price' => $variation->get_regular_price() ) );
        $sale    = (float) wc_get_price_to_display( $variation, array( 'price' => $variation->get_price() ) );
        $saving  = max( 0, $regular - $sale );

        $variation_data['vt_msrp']       = $regular;
        $variation_data['vt_now']        = $sale;
        $variation_data['vt_saving']     = $saving;
        $variation_data['vt_msrp_html']  = '$' . number_format( $regular, 0 );
        $variation_data['vt_now_html']   = '$' . number_format( $sale, 0 );
        $variation_data['vt_saving_html']= '$' . number_format( $saving, 0 );

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
     * Hook method to render savings and accordion before Add to Cart button
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

        // Only render accordion if it's set to appear above ATC button (default position)
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

                        // Sync visual state for all attributes
                        var $form = $('form.variations_form');
                        $form.find('.variations select').each(function() {
                            var $select = $(this);
                            var attribute = $select.attr('name');
                            var value = $select.val();

                            if (value && (attribute === 'attribute_pa_bundles' || attribute === 'attribute_pa_controller' || attribute === 'attribute_pa_front-bench')) {
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
                                                                                function updateAllSwatchImages(variation) {
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

                function updateAllBundleSwatchImages_OLD_DEPRECATED(variation) {
                    // DEPRECATED - This function should not be called anymore
                    return;
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

                            if (value && (attribute === 'attribute_pa_bundles' || attribute === 'attribute_pa_controller' || attribute === 'attribute_pa_front-bench')) {
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

                    isProcessingControllerChange = true;

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

                        // Update images with current variation data
                        if (currentVariation) {
                            updateAllSwatchImages(currentVariation);
                        } else {
                            updateAllSwatchImages();
                        }
                    } else {
                        updateAllSwatchImages();
                    }

                    // Reset the processing flag after a short delay
                    setTimeout(function() {
                        isProcessingControllerChange = false;
                    }, 100);
                });

                                                // Flag to prevent infinite loops in bundle changes
                var isProcessingBundleChange = false;

                                                                $(document).on('change', 'select[name="attribute_pa_bundles"]', function() {
                    // Prevent infinite loops but allow processing if it's been too long
                    if (isProcessingBundleChange) {
                        // Reset the flag if it's been stuck for too long
                        setTimeout(function() {
                            if (isProcessingBundleChange) {
                                isProcessingBundleChange = false;
                            }
                    }, 500);
                        return;
                    }

                    isProcessingBundleChange = true;

                    var bundleValue = $(this).val();

                                        // CRITICAL: Get controller from multiple sources to ensure we never lose it
                    var $selectedControllerSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.cgkit-swatch-selected, .cgkit-attribute-swatches[data-attribute="attribute_pa_controller"] .cgkit-swatch.zg-permanent-selected');
                    var currentControllerValue = $selectedControllerSwatch.length ? $selectedControllerSwatch.data('attribute-value') : $('select[name="attribute_pa_controller"]').val();

                    // Store in persistent memory immediately
                    if (currentControllerValue && currentControllerValue !== '') {
                        window.persistentControllerSelection = currentControllerValue;
                    }

                    // CRITICAL: Get bundle from multiple sources to ensure we never lose it
                    var $selectedBundleSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch.cgkit-swatch-selected, .cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-swatch.zg-permanent-selected');
                    var currentBundleValue = $selectedBundleSwatch.length ? $selectedBundleSwatch.data('attribute-value') : bundleValue;

                    // Store in persistent memory immediately
                    if (currentBundleValue && currentBundleValue !== '') {
                        window.persistentBundleSelection = currentBundleValue;
                    }

                    // PRESERVE FRONT BENCH SELECTION - Store it before any changes
                    var currentFrontBenchValue = $('select[name="attribute_pa_front-bench"]').val();
                    var $selectedFrontBenchSwatch = $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch.cgkit-swatch-selected');
                    var frontBenchVisualValue = $selectedFrontBenchSwatch.length ? $selectedFrontBenchSwatch.data('attribute-value') : currentFrontBenchValue;

                    // Store the last valid front bench selection (not 'none')
                    if (frontBenchVisualValue && frontBenchVisualValue !== 'none') {
                        window.lastValidFrontBenchSelection = frontBenchVisualValue;
                    }

                    var $form = $('form.variations_form');
                    var $frontBenchRow = $form.find('tr').filter(function() {
                        return $(this).find('label[for="pa_front-bench"]').length > 0;
                    });

                    // Manage front bench visibility and selection based on bundle
                    if (bundleValue === 'grill-only') {
                        $frontBenchRow.hide();
                        $form.find('select[name="attribute_pa_front-bench"]').val('none');
                    } else {
                        $frontBenchRow.show();
                        var currentFrontBench = $form.find('select[name="attribute_pa_front-bench"]').val();
                        if (currentFrontBench === 'none' && window.lastValidFrontBenchSelection) {
                            $form.find('select[name="attribute_pa_front-bench"]').val(window.lastValidFrontBenchSelection);
                        } else if (currentFrontBench === 'none') {
                            $form.find('select[name="attribute_pa_front-bench"]').val('stainless-steel');
                        }
                    }



                    // Get current variation data and update images
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

                        // Update images with current variation data
                        if (currentVariation) {
                            updateAllSwatchImages(currentVariation);
                        } else {
                            updateAllSwatchImages();
                        }
                    } else {
                        updateAllSwatchImages();
                    }

                    // Reset the processing flag after a short delay
                    setTimeout(function() {
                        isProcessingBundleChange = false;
                    }, 100);
                });

                // Flag to prevent infinite loops in front bench changes
                var isProcessingFrontBenchChange = false;

                                                // Global event handler to track front bench clicks and sync selections
                $(document).on('click', '.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch', function(e) {
                    var frontBenchValue = $(this).data('attribute-value');
                    var currentDropdownValue = $('select[name="attribute_pa_front-bench"]').val();
                    var bundleValue = $('select[name="attribute_pa_bundles"]').val();

                    // Sync the dropdown value with the clicked swatch
                    if (frontBenchValue && frontBenchValue !== currentDropdownValue) {
                        $('select[name="attribute_pa_front-bench"]').val(frontBenchValue);

                        // Update visual state immediately
                        $('.cgkit-attribute-swatches[data-attribute="attribute_pa_front-bench"] .cgkit-swatch').removeClass('cgkit-swatch-selected zg-permanent-selected');
                        $(this).addClass('cgkit-swatch-selected zg-permanent-selected');

                        // Trigger change event to update images
                        $('select[name="attribute_pa_front-bench"]').trigger('change');

                        // Additional direct image update for 7002B
                        var productTitle = $('h1.product_title').text() || 'Unknown Product';
                        if (productTitle.toLowerCase().includes('7002b')) {
                            setTimeout(function() {
                                updateAllSwatchImages();
                            }, 50);
                        }
                    }
                });

                $(document).on('change', 'select[name="attribute_pa_front-bench"]', function() {
                    // Prevent infinite loops
                    if (isProcessingFrontBenchChange) {
                        console.log('🪑 FRONT BENCH CHANGE SKIPPED - already processing');
                        return;
                    }

                    isProcessingFrontBenchChange = true;

                    var value = $(this).val();
                    var bundleValue = $('select[name="attribute_pa_bundles"]').val();
                    var controllerValue = $('select[name="attribute_pa_controller"]').val();
                    var productTitle = $('h1.product_title').text() || 'Unknown Product';

                    console.log('🪑 FRONT BENCH CHANGE DEBUG:');
                    console.log('  - Product:', productTitle);
                    console.log('  - Front bench value:', value);
                    console.log('  - Bundle value:', bundleValue);
                    console.log('  - Controller value:', controllerValue);
                    console.log('  - Triggered by:', this);

                    // Special handling for 7002B product
                    var is7002B = productTitle.toLowerCase().includes('7002b');
                    console.log('  - Is 7002B product:', is7002B);

                    // Get current variation data and update images
                    var $form = $('form.variations_form');
                    var variations = $form.data('product_variations');
                    console.log('  - Total variations available:', variations ? variations.length : 0);

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

                        // Update images with current variation data
                        if (currentVariation) {
                            console.log('  - Found variation ID:', currentVariation.variation_id);
                            console.log('  - Variation attributes:', currentVariation.attributes);
                            updateAllSwatchImages(currentVariation);
                        } else {
                            console.log('  - No matching variation found');
                            updateAllSwatchImages();
                        }
                    } else {
                        console.log('  - No variations data available');
                        updateAllSwatchImages();
                    }

                    // Reset the processing flag after a short delay
                    setTimeout(function() {
                        console.log('🪑 FRONT BENCH CHANGE COMPLETE - resetting processing flag');

                        // Additional image update for 7002B to ensure it works
                        if (is7002B) {
                            console.log('  - 7002B: Additional image update to ensure functionality');
                            updateAllSwatchImages();
                        }

                        isProcessingFrontBenchChange = false;
                    }, 100);
                });

                // Handle when variation is reset - REMOVED DUPLICATE HANDLER
                // The main reset_data handler above already handles this properly

                // Additional handler for form reset events
                $(document).on('woocommerce_reset_variations', function() {
                    console.log('=== WooCommerce Reset Variations Event ===');
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
                            background-color: #fff5f5 !important;
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

                    // Update dropdown
                    $('select[name="attribute_pa_controller"]').val(controllerValue);
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

                    // Update dropdown
                    $('select[name="attribute_pa_bundles"]').val(bundleValue);
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
                        $bundleSelect.trigger('change');
                    } else {
                        $this.addClass('cgkit-swatch-selected zg-permanent-selected');
                        $bundleSelect.val('grill-only');
                    }

                    // Ensure front bench is set to none for grill-only (only if front bench exists)
                    var $frontBenchSelect = $('select[name="attribute_pa_front-bench"]');
                    if ($frontBenchSelect.length > 0) {
                        $frontBenchSelect.val('none');
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
                            } else {
                                $bundleSelect.trigger('change');
                            }
                        } else {
                            $bundleSelect.trigger('change');
                        }
                    };
                    setTimeout(checkBundleSelection, 50);

                    // Don't trigger additional image updates here - let the change event handle it
                });

                                                function updateAccordionContent(variation) {
                    var $excerpt = $('.zg-accordion-excerpt');
                    var $content = $('.zg-accordion-content');
                    // var $savingsSection = $('.zg-accordion-savings'); // COMMENTED OUT

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

                    // Update savings information - COMMENTED OUT
                    /*
                    var regularPrice = parseFloat(variation.display_regular_price);
                    var salePrice = parseFloat(variation.display_price);
                    var $savingsLine = $savingsSection.find('div').first(); // The div with "Total Savings"

                    // Always ensure the parent savings section is visible
                    $savingsSection.show();

                    if (regularPrice && salePrice && regularPrice > salePrice) {
                        var savings = regularPrice - salePrice;
                        $savingsSection.find('.zg-savings-amount').text('$' + Math.round(savings));
                        $savingsSection.find('.zg-msrp-text').text('(MSRP $' + Math.round(regularPrice) + ')');
                        $savingsLine.show(); // Show the "Total Savings" line
                    } else {
                        $savingsLine.hide(); // Hide the "Total Savings" line
                    }

                    // Always update "Now $X Only" price
                    $savingsSection.find('.zg-current-price').text('Now $' + Math.round(salePrice || regularPrice) + ' Only');
                    */
                                                        }

                function resetAccordionContent() {
                    // Reset to default content if needed
                    // This will be handled by the initial PHP content

                    // Reset accordion savings section visibility - COMMENTED OUT
                    /*
                    var $savingsSection = $('.zg-accordion-savings');
                    var $savingsLine = $savingsSection.find('div').first(); // The div with "Total Savings"
                    var defaultSavings = <?php echo $default_variation_data['savings']; ?>;

                    // Always ensure the parent savings section is visible
                    $savingsSection.show();

                    if (defaultSavings > 0) {
                        $savingsLine.show(); // Show the "Total Savings" line
                    } else {
                        $savingsLine.hide(); // Hide the "Total Savings" line
                    }
                    */
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

                        /* BADGE STYLING - FIXED POSITIONING */
            .tile-offer {
                position: absolute !important;
                left: 0 !important;
                right: 0 !important;
                top: -12px !important;
                max-width: max-content !important;
                margin: 0 auto !important;
                background: #BC3116 !important;
                color: white !important;
                font-weight: bold !important;
                border-radius: 9999px !important;
                padding: 4px 12px !important;
                font-size: 12px !important;
                text-align: center !important;
                border: 2px solid #BC3116 !important;
                z-index: 9999 !important;
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                white-space: nowrap !important;
                line-height: 1 !important;
                transform: none !important;
                transition: none !important;
            }
            </style>

                                                                        <script>
            jQuery(document).ready(function($) {
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


            });
            </script>

            <!-- Always show savings for testing -->
                                            <div class="zg-total-savings" style="background: transparent; border: none; padding: 0; margin: 0; display: flex; align-items: center; gap: 8px;">
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
            'accordion_title' => '',
            'accordion_content' => '',
            'accordion_preview' => '',
            'accordion_image' => '',
            'accordion_image_url' => '',
            'savings' => 0,
            'msrp' => 0,
            'current_price' => 0,
            'has_content' => false
        );

        if ($product->is_type('variable')) {
            // Get default variation or first available variation
            $default_attributes = $product->get_default_attributes();
            $variations = $product->get_available_variations();

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
        } else {
            // For simple products, calculate savings
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();

            if ($regular_price && $sale_price && $regular_price > $sale_price) {
                $data['savings'] = $regular_price - $sale_price;
            }

            // Set MSRP and current price for simple products
            $data['msrp'] = $regular_price ? $regular_price : 0;
            $data['current_price'] = $sale_price ? $sale_price : $regular_price;

            // Set default accordion content for simple products
            $data['accordion_title'] = "What's included?";
            $data['accordion_content'] = "Grill and 2 x Food Temperature Probes";
            $data['has_content'] = true;
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
            'accordion_image' => '',
            'accordion_image_url' => '',
            'savings' => 0,
            'msrp' => 0,
            'current_price' => 0,
            'has_content' => false
        );

        // Get variation-specific dropdown data
        $dd_text = get_post_meta($variation_id, '_vt_dd_text', true);
        $dd_preview = get_post_meta($variation_id, '_vt_dd_preview', true);
        $dd_image_id = get_post_meta($variation_id, '_vt_dd_image_id', true);

        // Get image data
        if ($dd_image_id && wp_attachment_is_image($dd_image_id)) {
            $data['accordion_image'] = wp_get_attachment_image($dd_image_id, 'medium', false, array('class' => 'zg-accordion-image'));
            $data['accordion_image_url'] = wp_get_attachment_image_url($dd_image_id, 'medium');
        }

        if (!empty($dd_text)) {
            $data['accordion_title'] = "What's included?";
            $data['accordion_content'] = $dd_text;
            $data['has_content'] = true;
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

            // Set MSRP and current price for variations
            $data['msrp'] = $regular_price ? $regular_price : 0;
            $data['current_price'] = $sale_price ? $sale_price : $regular_price;
        }

        return $data;
    }

    /**
     * Render accordion component
     */
    private function render_accordion($default_variation_data) {
        ?>
        <!-- Simple visible accordion -->
        <div class="zg-accordion-container" style="background: #efefef; border: 1px solid #e9ecef; border-radius: 8px;">
            <div class="zg-accordion-header" style="padding: 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
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
                <!-- Dynamic savings section at bottom of expanded content - COMMENTED OUT -->
                <!--
                <div class="zg-accordion-savings" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
                    <div style="color: #6c757d; font-size: 15px; margin-bottom: 5px;">
                        <strong style="font-weight: 700;">Total Savings:</strong>
                        <span class="zg-savings-amount" style="color: #dc3545; font-weight: bold;">$<?php echo esc_html(number_format($default_variation_data['savings'], 0)); ?></span>
                        <span class="zg-msrp-text" style="color: #6c757d; font-size: 12px;">(MSRP $<?php echo esc_html(number_format($default_variation_data['msrp'], 0)); ?>)</span>
                    </div>
                    <div class="zg-current-price" style="color: #dc3545; font-weight: bold; font-size: 16px;">
                        Now $<?php echo esc_html(number_format($default_variation_data['current_price'], 0)); ?> Only
                    </div>
                </div>
                -->
            </div>
        </div>
        <?php
    }
}
