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
        wp_register_style('pro-tiles-slick', PROTILES_URL . 'assets/css/slick-carousal.css', array(), '1.0.1.' . time());
        wp_enqueue_script('wc-add-to-cart-variation');

        add_filter('variant_tiles_dropdown_continue', function(){
            return true;
        });

        // Add missing hook from old plugin for default selection
        add_filter('woocommerce_dropdown_variation_attribute_options_args', array($this, 'woo_select_default_option'),10,1);
    }

    function woo_select_default_option( $args)
    {
        if(count($args['options']) == 1) //Ensure product variation isn't empty
            $args['selected'] = $args['options'][0];

        // DEBUG: Temporary debug output for default option selection
        if (strpos($args['attribute'], 'bundles') !== false) {
            echo '<!-- DEBUG: woo_select_default_option called for ' . $args['attribute'] . ' - Selected: ' . (isset($args['selected']) ? $args['selected'] : 'none') . ' -->';
        }

        return $args;
    }

    public function get_style_depends()
    {
        $deps = ['pro-tiles-elementor', 'pro-tiles-slick'];
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
        return ['pro-tiles-general', 'pro-tiles-slick.carousel'];
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
        global $product;
        $variation_locations = array('default' => 'Default');

        if ( $product instanceof \WC_Product ) {
            $attributes = $product->get_attributes();
            foreach ( array_keys( $attributes ) as $attr ) {
                $variation_locations['before_product_swatch_attribute_' . $attr] = 'Before ' . $attr;
                $variation_locations['after_product_swatch_attribute_' . $attr] = 'After ' . $attr;
            }
        } else {
            $current_id = function_exists('get_the_ID') ? get_the_ID() : 0;
            if ( function_exists('wc_get_product') && $current_id ) {
                $prod_obj = wc_get_product( $current_id );
                if ( $prod_obj instanceof \WC_Product ) {
                    $attributes = $prod_obj->get_attributes();
                    foreach ( array_keys( $attributes ) as $attr ) {
                        $variation_locations['before_product_swatch_attribute_' . $attr] = 'Before ' . $attr;
                        $variation_locations['after_product_swatch_attribute_' . $attr] = 'After ' . $attr;
                    }
                }
            }
        }

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
            'variation_location',
            [
                'label' => __('Variation Data Location', 'elementor-pro'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'no',
                'options' => $variation_locations
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

        $this->add_control(
            'show_short_descriptions',
            [
                'label' => __('Show Short Descriptions', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'label_off' => __('Hide', 'elementor-pro'),
                'label_on' => __('Show', 'elementor-pro'),
                'default' => 'yes',
                'description' => __('Please note that switching on this option will disable some of the design controls.', 'elementor-pro'),
            ]
        );

        $this->add_responsive_control(
            'show_slider',
            [
                'label' => __('Display Slider', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'label_off' => __('Hide', 'elementor-pro'),
                'label_on' => __('Show', 'elementor-pro'),
                'desktop_default' => 'no',
                'tablet_default' => 'yes',
                'mobile_default' => 'yes',
                'description' => __('Please note that switching on this option will disable some of the design controls.', 'elementor-pro'),
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
                    'show_quantity' => '',
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
                'dynamic' => [],
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
                'dynamic' => [],
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
        $this->start_controls_section(
            'offer_label',
            [
                'label' => __('Offer Label', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_responsive_control(
            'label_width',
            [
                'label' => __('Label Width', 'elementor') . ' (%)',
                'type' => Controls_Manager::SLIDER,
                'desktop_default' => ['unit' => '%', 'size' => '45'],
                'tablet_default' => ['unit' => '%', 'size' => '45'],
                'mobile_default' => ['unit' => '%', 'size' => '70'],

                'range' => [
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
                ],
                'size_units' => ['px', 'em', "%"],

                'selectors' => [
                    '{{WRAPPER}} .variable-item .variable-item-span-button-offers' => 'width: {{SIZE}}{{UNIT}}',
                ],

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
        $this->start_controls_section(
            'short_description',
            [
                'label' => __('Short Descriptions', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'short_description_prefix_text',
            [
                'label' => __('Prefix Text', 'elementor'),
                'type' => Controls_Manager::TEXT,
                'default' => 'Comes with'
            ]
        );
        $this->add_responsive_control(
            'short_description_prefix_icon',
            [
                'label' => __('Prefix Icon', 'elementor'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
            ]
        );

        $this->add_control(
            'short_description_prefix_icon_width',
            [
                'label'     => __('Icon Width', 'elementor'),
                'type'         => Controls_Manager::TEXT,
                'default'     => '50',
                'selectors' => [
                    '{{WRAPPER}} .single_variation_wrap .elementor-text-icon svg' => 'width: {{VALUE}}px;'

                ],

            ]
        );

        $this->add_control(
            'short_description_prefix_icon_height',
            [
                'label'     => __('Icon Height', 'elementor'),
                'type'         => Controls_Manager::TEXT,
                'default'     => '50',
                'selectors' => [
                    '{{WRAPPER}} .single_variation_wrap .elementor-text-icon svg' => 'height: {{VALUE}}px;'

                ],

            ]
        );

        $this->add_responsive_control(
            'short_description_prefix_icon_indent',
            [
                'label' => __('Icon Spacing', 'elementor'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'desktop_default' => ['size' => '5', 'unit' => "px"],
                'tablet_default' => ['size' => '5', 'unit' => "px"],
                'mobile_default' => ['size' => '5', 'unit' => "px"],
                'selectors' => [
                    '{{WRAPPER}} .elementor-text-icon.elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_control(
            'short_description_prefix_label_color',
            [
                'label' => __('Prefix Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#555555',
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-variation-description span.elementor-text-content-wrapper' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'label' => __('Prefix Text Typography', 'elementor'),
                'name' => 'prefix_text_typography',
                'selector' => '{{WRAPPER}} .woocommerce-variation-description span.elementor-text-content-wrapper',
                'fields_options' => [
                    'font_weight' => ['default' => '600'],
                    'font_family' => ['default' => 'Inter',],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '15']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '18.15']]
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'Descriptions_typography',
                'selector' => '{{WRAPPER}} .woocommerce-variation-description p',
                'fields_options' => [
                    'font_weight' => ['default' => '400'],
                    'font_family' => ['default' => 'Inter',],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '15']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '21.3']]
                ],
            ]
        );
        $this->add_responsive_control(
            'short_description_align',
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
                    '{{WRAPPER}} .woocommerce-variation-description p' => 'text-align: {{VALUE}};',
                ],

            ]
        );
        $this->add_responsive_control(
            'short_description_margin',
            [
                'label' => __('Short Descriptions Margin', 'elementor'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'desktop_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'tablet_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'mobile_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
                'allowed_dimensions' => ['top', 'left', 'right', 'bottom'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-variation.single_variation' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
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
        $this->start_controls_section(
            'expand-txt',
            [
                'label' => __('Expand Text', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'seemore_typography',
                'selector' => '{{WRAPPER}} .tiles-seemore-link',
                'fields_options' => [
                    'font_weight' => ['default' => '400'],
                    'font_family' => ['default' => 'Inter',],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '10']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '16']]
                ],
            ]
        );
        $this->add_control(
            'expand_text_color',
            [
                'label' => __('Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .tiles-seemore-link' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .tiles-seemore-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'expand_text_color_hover',
            [
                'label' => __('Hover Text Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#BC3116',
                'selectors' => [
                    '{{WRAPPER}} .tiles-seemore-link:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'expand_background_color',
            [
                'label' => __('Background Color', 'elementor'),
                'type' => Controls_Manager::COLOR,
                'default' => '#E6E5E5',
                'selectors' => [
                    '{{WRAPPER}} .variable-item-span-search .elementor-text-content-wrapper' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'expand_text_align',
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
                'mobile_default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .tiles-seemore-link' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'expand_text_size',
            [
                'label' => __('Size', 'elementor'),
                'type' => Controls_Manager::SELECT,
                'default' => 'sm',
                'options' => self::get_button_sizes(),
                'style_transfer' => true,
                'condition' => [
                    'show_quantity' => '',
                ],
            ]
        );

        $this->add_control(
            'expand_text_icon',
            [
                'label' => __('Expand Text Icon', 'elementor'),
                'type' => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,

            ]
        );


        $this->end_controls_section();
        $this->start_controls_section(
            'variant-tiles-sliders',
            [
                'label' => __('Variant Tiles Sliders', 'elementor'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'show_arrows',
            [
                'label' => __('Show Arrows', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'label_off' => __('Hide', 'elementor-pro'),
                'label_on' => __('Show', 'elementor-pro'),
                'default' =>  'no',
                'description' => __('Please note that switching on this option will disable some of the design controls.', 'elementor-pro'),
            ]
        );



        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'arrows_size',
                'selector' => '{{WRAPPER}} .slick-prev:before, {{WRAPPER}} .slick-next:before',
                'fields_options' => [
                    'font_weight' => ['default' => '400'],
                    'font_family' => ['default' => 'Font Awesome 5 Free',],
                    'font_size'   => ['default' => ['unit' => 'px', 'size' => '24']],
                    'line_height' => ['default' => ['unit' => 'px', 'size' => '28']]
                ],
            ]
        );

        $this->add_control(
            'show_dots',
            [
                'label' => __('Show Dots', 'elementor-pro'),
                'type' => Controls_Manager::SWITCHER,
                'label_off' => __('Hide', 'elementor-pro'),
                'label_on' => __('Show', 'elementor-pro'),
                'default' => 'no',
                'description' => __('Please note that switching on this option will disable some of the design controls.', 'elementor-pro'),
                'separator' => 'before'
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

        if(isset($settings['variation_location']) && $settings['variation_location'] !== 'default'){
            remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
            add_action( $settings['variation_location'] , 'woocommerce_single_variation' );
        }

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
        add_filter('woocommerce_short_description', array($this, 'pvt_add_text_short_descriptions_v2'), 50, 1);
        add_filter('woocommerce_available_variation', array($this, 'variation_price_preffix'), 10, 3);
        // Add critical hook for variation data enrichment (accordion, pricing, etc.)
        add_filter('woocommerce_available_variation', array($this, 'vt_enrich_variation_payload'), 20, 3);
        add_filter("wvs_variable_items_wrapper", array($this, 'wvs_custom_variable_items_wrapper'), 10, 4);
        if ('yes' !== $settings['show_quantity']) {
            add_filter('woocommerce_is_sold_individually', array($this, 'pvt_remove_all_quantity_fields'), 10, 2);
        }

        ob_start();

        woocommerce_template_single_add_to_cart();
        $form = ob_get_clean();
        $form = str_replace('single_add_to_cart_button', 'single_add_to_cart_button elementor-button', $form);
        echo $form;

        // Add stock message section below Add to Cart button
        echo '<div style="text-align: center; width: 100%;">';
        echo '<div id="vt-in-stock-message" style="margin-top: 10px; display: none; align-items: center; justify-content: center; color: #333; font-size: 14px; font-weight: bold; line-height: 1;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 5px; vertical-align: middle; display: inline-block;"><circle cx="12" cy="12" r="10" fill="#4CAF50"/></svg><span style="vertical-align: middle;">In Stock</span></div>';
        echo '<div id="vt-low-stock-message" style="margin-top: 10px; display: none; align-items: center; justify-content: center; color: #333; font-size: 14px; font-weight: bold; line-height: 1;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 5px; vertical-align: middle; display: inline-block;"><circle cx="12" cy="12" r="10" fill="#FFC107"/></svg><span style="vertical-align: middle;">Low in Stock</span></div>';
        echo '</div>';

?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                var aarrows = 'true';
                var adots = 'true';
                aarrows = <?php echo ($settings['show_arrows'] == 'yes') ? 'true' : 'false'; ?>;
                adots = <?php echo ($settings['show_dots'] == 'yes') ? 'true' : 'false'; ?>;

                show_slider = <?php echo ($settings['show_slider'] == 'yes') ? 'true' : 'false'; ?>;

                $(window).resize(function() {

                    var owl = $('.elementor-widget-productvarianttilesv4 .variable-items-wrapper');
                    if ($(window).width() < 1269) {

                        owl.not('.slick-initialized').slick({
                            arrows: false,
                            slidesToShow: 2,
                            infinite: false,
                            variableWidth: true
                        });
                    } else {
                        if (owl.hasClass('slick-initialized')) {
                            owl.slick('unslick');
                        }
                    }

                });
            });
        </script>
        <?php
        if ('yes' !== $settings['show_quantity']) {
            remove_filter('woocommerce_is_sold_individually', 'pvt_remove_all_quantity_fields');
        }
        remove_filter('woocommerce_available_variation', array($this, 'variation_price_preffix'));
        remove_filter('woocommerce_available_variation', array($this, 'vt_enrich_variation_payload'), 20, 3);
        remove_filter('woocommerce_product_single_add_to_cart_text', $text_callback);
        remove_filter('woocommerce_get_stock_html', '__return_empty_string');
        remove_filter('esc_html', [$this, 'unescape_html']);
        // remove_filter('woocommerce_attribute_label', array($this, 'get_attribute_label'), 10, 3);
        remove_filter('woocommerce_short_description', array($this, 'pvt_add_text_short_descriptions_v2'), 50, 1);
    }
    function pvt_remove_all_quantity_fields($return, $product)
    {
        return true;
    }

    function wvs_custom_variable_items_wrapper($data, $contents, $type, $args, $saved_attribute = array())
    {

        $attribute = $args['attribute'];
        $options   = $args['options'];
        return $data = sprintf('<ul role="radiogroup" class="variable-items-wrapper button-variable-wrapper variable_items_tiles off" data-attribute_name="%1$s" data-attribute_values="%2$s">%3$s</ul>',  esc_attr(wc_variation_attribute_name($attribute)), wc_esc_json(wp_json_encode(array_values($options))), $contents);
    }

    function action_wc_before_single_variation()
    {

        $settings = $this->get_settings_for_display();
        if (!isset($settings['show_cross_cell']) || $settings['show_cross_cell'] != 'yes') return;
        $icon_settings['cross_cell_product_price_icon'] = $settings['cross_cell_product_price_icon'];
        $icon_settings['cross_cell_product_price_icon_indent'] = $settings['cross_cell_product_price_icon_indent'];
        $icon_settings['cross_cell_product_price_icon_align'] = isset($settings['cross_cell_product_price_icon_align']) ? $settings['cross_cell_product_price_icon_align'] : '';
        if (empty($settings['cross_cell_product_price_icon_align'])) {
            $settings['cross_cell_product_price_icon_align'] = $this->get_settings('cross_cell_product_price_icon_align');
        } ?>
        <div class="variant_cross_cell"></div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {

                $('.elementor-widget-productvarianttilesv2 form.variations_form').on('show_variation', function(event, data) {

                    variation_id = data.variation_id;
                    var vid = "#vid-" + variation_id;
                    var cross_cell = $(vid).data("crosscell");
                    if (cross_cell.length > 0) {

                        $(".variant_cross_cell").html('<div class="variant-loading variant-style"></div>');
                        html = '<ul class="crosscell_products">';
                        for (var i = 0; i < cross_cell.length; i++) {
                            html += '<li class="crosscell_items" data-product_id="' + cross_cell[i]['variation_id'] + '" data-price="' + cross_cell[i]['price'] + '">';
                            html += '<div class="cross_img" style="background:url(' + cross_cell[i]["image"] + ')"></div>';
                            html += '<div class="cross_content"><h4>' + cross_cell[i]["product_name"] + '</h4>';
                            html += '<p class="cross_price">' + cross_cell[i]["price_html"] + '</p>';
                            html += '</div></li>';
                        }
                        html += '</ul>';
                        $(".variant_cross_cell .variant-loading").remove();
                        $(html).appendTo(".variant_cross_cell").fadeIn(300);
                        return false;
                    } else {
                        $(".variant_cross_cell").html('');
                        return false;
                    }

                });

                return false;
            });
        </script>
        <?php
    }

    function variation_price_preffix($variation_data, $product, $variation)
    {
        $settings = $this->get_settings_for_display();
        $variation_data['price_html'] = '<span class="price-preffix">' . __($this->get_settings('total_price_prefix_text'), "woocommerce") . '</span> ' . $variation_data['price_html'];

        return $variation_data;
    }

    function pvt_add_text_short_descriptions_v2($description)
    {
        $settings = $this->get_settings_for_display();
        $migrated = isset($settings['__fa4_migrated']['short_description_prefix_icon']);
        $is_new = empty($settings['icon']) && Icons_Manager::is_migration_allowed();

        if (!$is_new && empty($settings['short_description_prefix_icon_align'])) {
            // @todo: remove when deprecated
            // added as bc in 2.6
            //old default
            $settings['short_description_prefix_icon_align'] = $this->get_settings('short_description_prefix_icon_align');
        }

        $this->add_render_attribute([
            'short-content-wrapper' => [
                'class' => 'elementor-text-content-wrapper',
            ],
            'short_description_prefix_icon_align' => [
                'class' => [
                    'elementor-text-icon',
                    'elementor-align-icon-left',
                ],
            ],
            'short_description_text' => [
                'class' => 'elementor-label-text',
            ],
        ]);


        if ('yes' === $settings['show_short_descriptions']) {
            ob_start(); ?>
            <span class="elementor-text-content-wrapper">
                <?php if (!empty($settings['icon']) || !empty($settings['short_description_prefix_icon']['value'])) : ?>
                    <span class="elementor-text-icon elementor-align-icon-left">
                        <?php if ($is_new || $migrated) :
                            Icons_Manager::render_icon($settings['short_description_prefix_icon'], ['aria-hidden' => 'true']);
                        else : ?>
                            <i class="<?php echo esc_attr($settings['icon']); ?>" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                <span <?php echo $this->get_render_attribute_string('short_description_text'); ?>>
                    <?php echo $this->get_settings('short_description_prefix_text'); ?>
                </span>
            </span>
        <?php
            $icon = ob_get_contents();
            ob_end_clean();
            return $icon . $description;
        }
        return '';
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

        // DEBUG: Temporary debug output to understand default attributes structure
        if (strpos($args['attribute'], 'bundles') !== false) {
            echo '<!-- DEBUG: Default attributes for ' . $args['attribute'] . ': ' . print_r($default_attributes, true) . ' -->';
        }




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

            // DEBUG: Temporary debug output for selection logic
            if (strpos($args['attribute'], 'bundles') !== false) {
                echo '<!-- DEBUG: Comparing ' . $item_attri_val . ' with default ' . (isset($default_attributes[$current_attribute_name]) ? $default_attributes[$current_attribute_name] : 'none') . ' for ' . $current_attribute_name . ' - Selected: ' . ($custom_selected ? 'YES' : 'NO') . ' -->';
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
            if(isset($_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label']) && !empty($_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label'])){
                $swatch_html .= '<span class="tile-offer">' . $_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label'] . '</span>';
            }
            // if('image' === $swatch_type && isset($_variations[$attribute_raw][$item_attri_val]['variation']['save']) && filter_var(wp_strip_all_tags($_variations[$attribute_raw][$item_attri_val]['variation']['save']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) > 360){
            //     $swatch_html .= '<div class="sale-badge-price top"><span>Save ' . ($_variations[$attribute_raw][$item_attri_val]['variation']['save']) . '</span></div>';
            // }

                                                $swatch_html .= $this->zg_commercekit_as_get_swatch_html($swatch_type, $attribute_swatches[$attribute_id][$item->term_id], $item, $image_label);



            // Add price (following CommerceKit structure exactly)
            if(isset($_variations[$attribute_raw][$item_attri_val]['variation']['price_html']) && $swatch_type == 'image'){
                $swatch_html .= '<span class="tile-price">' . $_variations[$attribute_raw][$item_attri_val]['variation']['price_html'] . '</span>';
            }

            // Add variation image if this is an image swatch and we have a variation image
            // This should override the image but preserve tile-title and tile-price
            if ('image' === $swatch_type && isset($_variations[$attribute_raw][$item_attri_val]['variation']['cgkit_image_id'])) {
                $var_img_id = $_variations[$attribute_raw][$item_attri_val]['variation']['cgkit_image_id'];
                if ($var_img_id) {
                    $var_image = wp_get_attachment_image_src($var_img_id, 'woocommerce_thumbnail');
                    if ($var_image) {
                        // Extract the tile-title and tile-price from the existing swatch_html
                        $tile_title_html = '';
                        $tile_price_html = '';

                        if (preg_match('/<span class="tile-title">(.*?)<\/span>/', $swatch_html, $matches)) {
                            $tile_title_html = $matches[0];
                        }
                        if (preg_match('/<span class="tile-price">(.*?)<\/span>/', $swatch_html, $matches)) {
                            $tile_price_html = $matches[0];
                        }

                        // Replace only the image part, preserve tile-title and tile-price
                        $swatch_html = '<span class="cross">&nbsp;</span><img alt="' . esc_attr($item->name) . '" width="' . esc_attr($var_image[1]) . '" height="' . esc_attr($var_image[2]) . '" src="' . esc_url($var_image[0]) . '" />' . $tile_title_html . $tile_price_html;
                    }
                }
            }

            // Badge from custom meta _vt_offer_label
            $variation_id = $_variations[$attribute_raw][$item_attri_val]['variation']['variation_id'];
            $tile_offer = isset($_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label']) ? $_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label'] : get_post_meta($variation_id, '_vt_offer_label', true);

            // Debug logging for tile-offer badge
            error_log("DEBUG: Checking tile-offer for variation_id: " . $variation_id . ", attribute: " . $attribute_raw . ", value: " . $item_attri_val);
            error_log("DEBUG: tile_offer value: " . ($tile_offer ? $tile_offer : 'EMPTY'));
            error_log("DEBUG: From variations array: " . (isset($_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label']) ? $_variations[$attribute_raw][$item_attri_val]['variation']['_vt_offer_label'] : 'NOT SET'));
            error_log("DEBUG: From post_meta: " . get_post_meta($variation_id, '_vt_offer_label', true));

            if ( $tile_offer ) {
                $swatch_html .= '<span class="tile-offer">' . esc_html($tile_offer) . '</span>';
                error_log("DEBUG: Added tile-offer badge: " . esc_html($tile_offer));
            } else {
                error_log("DEBUG: No tile-offer badge added - value was empty");

                // TEMPORARY TEST: Force add badge for pro-bundle to test HTML generation
                if ( $item_attri_val === 'pro-bundle' ) {
                    $swatch_html .= '<span class="tile-offer">BEST VALUE</span>';
                    error_log("DEBUG: FORCED ADDED tile-offer badge for pro-bundle");
                }
            }

            if('image' === $swatch_type && isset($_variations[$attribute_raw][$item_attri_val]['variation']['save'])){

                $swatch_html .= '<div class="sale-badge-price bottom" data-variation="'.$_variations[$attribute_raw][$item_attri_val]['variation']['variation_id'].'"><span>Save ' . ($_variations[$attribute_raw][$item_attri_val]['variation']['save']) . '</span></div>';
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

        // Accordion under selected tile using _vt_dd_text and optional preview
        $selected_var = isset($_variations[$attribute_raw][$args['selected']]['variation']) ? $_variations[$attribute_raw][$args['selected']]['variation'] : array();
        $dd_text  = isset($selected_var['_vt_dd_text']) ? $selected_var['_vt_dd_text'] : '';
        $dd_prev  = isset($selected_var['_vt_dd_preview']) ? $selected_var['_vt_dd_preview'] : '';
        if ( empty($dd_text) && isset($selected_var['variation_id']) ) {
            $dd_text = get_post_meta($selected_var['variation_id'], '_vt_dd_text', true);
            $dd_prev = get_post_meta($selected_var['variation_id'], '_vt_dd_preview', true);
        }
        // Pricing figures
        $sv_html = isset($selected_var['vt_saving_html']) ? $selected_var['vt_saving_html'] : '';
        $msrp_html = isset($selected_var['vt_msrp_html']) ? $selected_var['vt_msrp_html'] : '';
        $now_html  = isset($selected_var['vt_now_html'])  ? $selected_var['vt_now_html']  : '';
        $pricing_block = '';
        if ( $sv_html || $msrp_html || $now_html ) {
            $pricing_block  = '<div class="vt-acc-pricing">';
            if ( $sv_html ) {
                $pricing_block .= '<div class="vt-savings">Total Savings: <b>' . $sv_html . '</b>' . ( $msrp_html ? ' <span class="vt-msrp">(MSRP ' . $msrp_html . ')</span>' : '' ) . '</div>';
            }
            if ( $now_html ) {
                $pricing_block .= '<div class="vt-now">Now ' . $now_html . ' Only</div>';
            }
            $pricing_block .= '</div>';
        }
        if ( ! empty($dd_text) ) {
            $swatches_html .= '<details class="vt-accordion" open><summary>' . esc_html( $dd_prev ?: __("What's included?","product-tiles") ) . '</summary><div class="vt-accordion-content">' . $pricing_block . wp_kses_post($dd_text) . '</div></details>';
        }

        if (isset($args['css_class']) && 'cgkit-as-wrap-plp' === $args['css_class']) {
            $html = str_ireplace(' id="', ' data-id="', $html);
        }
        $swatches_html .= sprintf('<div style="display: none;">%s</div>', $html);
        // $swatches_html .= '<section class="clearfix">' . do_action('after_product_swatch_' . $attribute_name) . '</section>';

        return $swatches_html;
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

    function expand_text($seemore)
    {

        $settings = $this->get_settings_for_display();
        $migrated = isset($settings['__fa4_migrated']['expand_text_icon']);

        $is_new = empty($settings['icon']) && Icons_Manager::is_migration_allowed();
        if (!$is_new && empty($settings['expand_text_icon_align'])) {

            $settings['expand_text_icon_align'] = $this->get_settings('expand_text_icon_align');
        }

        $this->add_render_attribute([
            'content-wrapper' => [
                'class' => 'elementor-text-content-wrapper',
            ],
            'icon-align' => [
                'class' => [
                    'elementor-expand-text-icon',
                    'elementor-align-icon-' . $settings['expand_text_icon_align'],
                ],
            ],
            'text' => [
                'class' => 'elementor-button-text',
            ],
        ]);
        ob_start(); ?>
        <a href="javascript:void(0);" data-id="<?php echo $seemore; ?>" class="tiles-seemore-link">
            <span <?php echo $this->get_render_attribute_string('content-wrapper'); ?>>
                <?php if (!empty($settings['expand_text_icon']) && !empty($settings['expand_text_icon']['value'])) : ?>

                    <span <?php echo $this->get_render_attribute_string('icon-align'); ?>>
                        <?php if ($is_new || $migrated) :
                            Icons_Manager::render_icon($settings['expand_text_icon'], ['aria-hidden' => 'true']);
                        else : ?>
                            <i class="<?php echo esc_attr($settings['expand_text_icon']); ?>" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
                <span class="elementor-bundle-label-text elementor-label-text" <?php echo $this->get_render_attribute_string('expand_text'); ?>><?php echo $settings['expand_text']; ?></span>

            </span></a>
<?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    public function vt_enrich_variation_payload($variation_data, $product, $variation){
        // Attach custom meta for badge and accordion
        $variation_data['_vt_offer_label'] = get_post_meta($variation->get_id(), '_vt_offer_label', true);
        $variation_data['_vt_dd_text']     = get_post_meta($variation->get_id(), '_vt_dd_text', true);
        $variation_data['_vt_dd_preview']  = get_post_meta($variation->get_id(), '_vt_dd_preview', true);

        // Pricing breakdown for accordion
        $regular = (float) wc_get_price_to_display( $variation, array( 'price' => $variation->get_regular_price() ) );
        $sale    = (float) wc_get_price_to_display( $variation, array( 'price' => $variation->get_price() ) );
        $saving  = max( 0, $regular - $sale );

        $variation_data['vt_msrp']       = $regular;
        $variation_data['vt_now']        = $sale;
        $variation_data['vt_saving']     = $saving;
        $variation_data['vt_msrp_html']  = wc_price( $regular );
        $variation_data['vt_now_html']   = wc_price( $sale );
        $variation_data['vt_saving_html']= wc_price( $saving );
        return $variation_data;
    }
}
