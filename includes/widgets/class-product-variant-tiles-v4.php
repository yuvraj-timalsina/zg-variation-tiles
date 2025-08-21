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
                                                // Generate basic swatch HTML first (following old plugin structure exactly)
            $swatch_html = $this->zg_commercekit_as_get_swatch_html($swatch_type, $attribute_swatches[$attribute_id][$item->term_id], $item, $image_label);

            // Add price AFTER basic swatch HTML (following old plugin structure exactly)
            if(isset($_variations[$attribute_raw][$item_attri_val]['variation']['price_html']) && $swatch_type == 'image'){
                // Ensure consistent price HTML structure to prevent layout shifts
                $price_html = $this->standardize_price_html($_variations[$attribute_raw][$item_attri_val]['variation']['price_html']);

                $swatch_html .= '<span class="tile-price">' . $price_html . '</span>';
            }

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

            // Badge admin field exists but no rendering - admin field only

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
                $(document.body).on('found_variation', function(event, variation) {
                    updateAccordionContent(variation);
                    updateSwatchImages(variation);
                });

                // Handle variation reset
                $(document.body).on('reset_data', function() {
                    resetAccordionContent();
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

                    // Also update all bundle swatch images based on current combination
                    updateAllBundleSwatchImages(variation);
                }

                                                                                function updateAllBundleSwatchImages(variation) {
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

                    // Prevent variation searching when grill-only is selected with non-wireless controller
                    if (selectedAttributes['attribute_pa_bundles'] === 'grill-only' &&
                        selectedAttributes['attribute_pa_controller'] === 'non-wireless') {
                        console.log('PHP: Grill-only with non-wireless detected - skipping all variation searches');
                        return;
                    }

                    // Check global flag to prevent variation searching
                    if (typeof window.preventVariationSearch !== 'undefined' && window.preventVariationSearch) {
                        console.log('PHP: Global preventVariationSearch flag is true - skipping all variation searches');
                        return;
                    }

                    // Get variations data
                    var variations = $form.data('product_variations');
                    if (!variations) return;



                    // Update each bundle swatch image - target bundles attribute specifically
                    $('.cgkit-attribute-swatches[data-attribute="attribute_pa_bundles"] .cgkit-attribute-swatch.cgkit-image').each(function() {
                        var $swatch = $(this);
                        var bundleValue = $swatch.data('attribute-value') || $swatch.find('button').data('attribute-value');

                        // Skip if we can't find the bundle value
                        if (!bundleValue) {
                            console.log('Skipping swatch - no bundle value found');
                            return; // Continue to next iteration
                        }

                        // Create target combination for this bundle with ALL other selected attributes
                        var targetCombination = {};
                        for (var attr in selectedAttributes) {
                            if (attr !== 'attribute_pa_bundles') { // Exclude bundle attribute itself
                                targetCombination[attr] = selectedAttributes[attr];
                            }
                        }
                        targetCombination['attribute_pa_bundles'] = bundleValue;

                        // Special handling for Grill Only - it should always use "none" for front bench
                        if (bundleValue === 'grill-only') {
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
                        } else {
                            console.log('No matching variation found for bundle ' + bundleValue);
                        }
                    });
                }

                // Also trigger image and badge updates when Controller or Front Bench changes via WooCommerce events
                $(document).on('woocommerce_variation_select_change', function(event, attribute, value) {
                    // Only update images and badges if Controller or Front Bench changed, NOT bundle
                    if (attribute === 'attribute_pa_controller' || attribute === 'attribute_pa_front-bench') {

                        setTimeout(function() {
                            updateAllBundleSwatchImages();
                        }, 100);
                    }
                });

                // Trigger image and badge updates on page load to set initial state
                $(document).ready(function() {
                    setTimeout(function() {
                        updateAllBundleSwatchImages();
                    }, 500);
                });

                                // Additional event handlers to catch Controller and Front Bench changes ONLY
                $(document).on('change', 'select[name="attribute_pa_controller"]', function() {

                    setTimeout(function() {
                        updateAllBundleSwatchImages();
                    }, 100);
                });

                $(document).on('change', 'select[name="attribute_pa_front-bench"]', function() {

                    setTimeout(function() {
                        updateAllBundleSwatchImages();
                    }, 100);
                });

                // Handle when variation is reset
                $(document.body).on('reset_data', function() {
                    setTimeout(function() {
                        updateAllBundleSwatchImages();

                    }, 100);
                });

                                                function updateAccordionContent(variation) {
                    var $excerpt = $('.zg-accordion-excerpt');
                    var $content = $('.zg-accordion-content');
                    var $savingsSection = $('.zg-accordion-savings');

                    // Update excerpt text
                    if (variation._vt_dd_preview && variation._vt_dd_preview.trim() !== '') {
                        $excerpt.html(variation._vt_dd_preview).show();
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

                                                                                // Update savings information - always show section, conditionally show savings line
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

                                                        }

                function resetAccordionContent() {
                    // Reset to default content if needed
                    // This will be handled by the initial PHP content

                    // Reset accordion savings section visibility - always show section, conditionally show savings line
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
                }
            });
            </script>
            <style>
            .zg-accordion-icon.rotated {
                transform: rotate(180deg);
            }
            </style>

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
            $data['accordion_preview'] = $dd_preview;
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
        <div style="background: #efefef; border: 1px solid #e9ecef; border-radius: 8px; margin: 15px 0;">
            <div class="zg-accordion-header" style="padding: 15px 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600; font-size: 16px; color: #212529;">What's included?</span>
                <span class="zg-accordion-icon dashicons dashicons-arrow-down-alt2" style="font-size: 16px; color: #212529;"></span>
            </div>

            <!-- Excerpt text (shown when collapsed) -->
            <?php if (!empty($default_variation_data['accordion_preview'])) : ?>
                <div class="zg-accordion-excerpt" style="padding: 10px 20px 15px 20px; color: #6c757d; font-size: 13px; font-style: italic;">
                    <?php echo esc_html($default_variation_data['accordion_preview']); ?>
                </div>
            <?php endif; ?>

            <!-- Full description (shown when expanded) -->
            <div class="zg-accordion-content" style="display: none;">
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
                <!-- Dynamic savings section at bottom of expanded content -->
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
            </div>
        </div>
        <?php
    }
}
