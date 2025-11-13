<?php

namespace Elementor;

use Elementor\Controls_Manager;

use Elementor\Widget_Button;
use ElementorPro\Base\Base_Widget_Trait;
use ElementorPro\Modules\QueryControl\Module;
use ElementorPro\Modules\Woocommerce\Widgets\Add_To_Cart;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use ElementorPro\Plugin;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class ProductVariantTiles extends  Widget_Base
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

		        wp_register_style('pro-tiles-elementor', PROTILES_URL . 'assets/css/pro-tile-elmentor.css', array(), '1.0.0.' . time());

		add_action('wp_footer', array($this, 'enqueue_custom_variation_swatch_script'));
	}
	public function get_style_depends()
	{
		$deps = ['pro-tiles-elementor'];
		if ( function_exists('is_product') && is_product() ) {
			$deps[] = 'commercekit-attribute-swatches-css';
		}
		return $deps;
	}
	public function get_script_depends()
	{
		return ['pro-tiles-elementor'];
	}
	public function get_name()
	{
		return 'product-variant-tiles';
	}

	public function get_title()
	{
		return 'Product Variant Tiles';
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

		// $this->add_control(
		// 	'product_id',
		// 	[
		// 		'label' => __( 'Product', 'elementor-pro' ),
		// 		'type' => Module::QUERY_CONTROL_ID,
		// 		'options' => [],
		// 		'label_block' => true,
		// 		'autocomplete' => [
		// 			'object' => Module::QUERY_OBJECT_POST,
		// 			'query' => [
		// 				'post_type' => [ 'product' ],
		// 			],
		// 		],
		// 	]
		// );

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
				'default'	=> [
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

				'desktop_default'	=> ['top' => 5, 'bottom' => 5, 'left' => 45, 'right' => 45],
				'tablet_default'	=> ['top' => 5, 'bottom' => 5, 'left' => 45, 'right' => 45],
				'mobile_default'	=> ['top' => 5, 'bottom' => 5, 'left' => 45, 'right' => 45],
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

		$this->start_controls_section(
			'bundle_prefix',
			[
				'label' => __('Bundle Prefix', 'elementor'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'bundle_prefix_text',
			[
				'label'   => __('Bundle Prefix Text', 'elementor'),
				'type'    => Controls_Manager::TEXT,
				'default' => 'Bundle'
			]
		);
		$this->add_control(
			'bundle_prefix_color',
			[
				'label' 	=> __('Text Color', 'elementor'),
				'type' 		=> Controls_Manager::COLOR,
				'default' 	=> '#555555',
				'selectors' => [
					'.variations td.label label, .woo-selected-variation-item-name' => 'color: {{VALUE}};'

				],

			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'bundle_prefix_typography',
				'selector' => '.variations td.label label, .woo-selected-variation-item-name',
				'fields_options' => [
					'font_weight' => ['default' => '600'],
					'font_family' => ['default' => "Inter",],
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '22']],
					'text_transform' => ['default' => 'capitalize']
				],
			]
		);
		$this->add_responsive_control(
			'bundle_prefix_icon',
			[
				'label' => __('Prefix Icon', 'elementor'),
				'type' => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin' => 'inline',
				'label_block' => false,
			]
		);

		$this->add_responsive_control(
			'bundle_prefix_icon_indent',
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
					'.elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'.elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'bundle_prefix_padding',
			[
				'label' => __('Padding', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors' => [
					'.variations td.label label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],

			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'variant_tiles',
			[
				'label' => __('Variant Tiles', 'elementor'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'entire_background_color',
			[
				'label' => __('Entire Tiles Background Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'dynamic' => [],
				'selectors' => [
					'.woo-variation-swatches.wvs-style-squared.elementor-page .variable-items-wrapper' => 'background-color: {{VALUE}};',

				],
			]
		);

		$this->add_responsive_control(
			'tiles_width',
			[
				'label' => __('Tiles Width', 'elementor') . ' (px)',
				'type' => Controls_Manager::SLIDER,
				'desktop_default' => ['size' => '180', 'unit' => "px"],
				'tablet_default' => ['size' => '180', 'unit' => "px"],
				'mobile_default' => ['size' => '48', 'unit' => "%"],
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
					'{{WRAPPER}} .elementor-widget-container .variable-items-wrapper .variable-item.button-variable-item' =>  'width: {{SIZE}}{{UNIT}}'
				],

			]
		);
		$this->add_responsive_control(
			'tiles_height',
			[
				'label' => __('Tiles Height', 'elementor') . ' (%)',
				'type' => Controls_Manager::SLIDER,
				'desktop_default' => ['size' => '79', 'unit' => "px"],
				'tablet_default' => ['size' => '79', 'unit' => "px"],
				'mobile_default' => ['size' => '79', 'unit' => "px"],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
						'step' => 1,
					],
				],
				'size_units' => ['px', '%', 'em'],

				'selectors' => [
					'{{WRAPPER}} .variable-item' => 'height: {{SIZE}}{{UNIT}}',
				],

			]
		);

		$this->add_responsive_control(
			'Vertical_space',
			[
				'label' => __('Tiles Vertical Spacing', 'elementor') . ' (px)',
				'type' => Controls_Manager::SLIDER,
				'desktop_default' => ['size' => 15, 'unit' => "px"],
				'tablet_default' => ['size' => 15, 'unit' => "px"],
				'mobile_default' => ['size' => 2, 'unit' => "%"],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'size_units' => ['px', '%', 'em'],
				'placeholder' => '20',
				'description' => __('Sets the default Vertical space between widgets (Default: 20)', 'elementor'),
				'selectors' => [
					'.variable-items-wrapper .variable-item.button-variable-item' => 'margin-bottom: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .variable-item' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_responsive_control(
			'horizontal_space',
			[
				'label' => __('Tiles Horizontal Spacing', 'elementor') . ' (px)',
				'type' => Controls_Manager::SLIDER,
				'desktop_default' => ['size' => 15, 'unit' => "px"],
				'tablet_default' => ['size' => 15, 'unit' => "px"],
				'mobile_default' => ['size' => 2, 'unit' => "%"],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 200,
					],
				],
				'size_units' => ['px', '%', 'em'],
				'placeholder' => '20',
				'description' => __('Sets the default Horizontal space between tiles (Default: 20)', 'elementor'),
				'selectors' => [
					'.variable-items-wrapper .variable-item.button-variable-item' => 'margin-right: {{SIZE}}{{UNIT}}',
					'{{WRAPPER}} .variable-item' => 'margin-right: {{SIZE}}{{UNIT}}',
				],
			]
		);
		$this->add_responsive_control(
			'tiles_padding',
			[
				'label' => __('Tiles Padding', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'desktop_default' => ['top' => 0, 'bottom' => 0, 'left' => 10, 'right' => 0],
				'tablet_default' => ['top' => 0, 'bottom' => 0, 'left' => 10, 'right' => 0],
				'mobile_default' => ['top' => 0, 'bottom' => 0, 'left' => 10, 'right' => 0],
				'selectors' => [
					'{{WRAPPER}} .elementor-widget-container .variable-items-wrapper .variable-item.button-variable-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .variable-items-wrapper .variable-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
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
					'.woo-variation-swatches .variable-items-wrapper .variable-item.button-variable-item' => 'border: {{SIZE}}{{UNIT}} solid;',
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
				'desktop_default' => ['unit' => 'px', 'size' => '10'],
				'tablet_default' => ['unit' => 'px', 'size' => '10'],
				'mobile_default' => ['unit' => 'px', 'size' => '10'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					' .variable-items-wrapper .variable-item.button-variable-item' => 'border-radius:{{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .variable-item' => 'border-radius: {{SIZE}}{{UNIT}};'
				],
			]
		);
		$this->add_control(
			'tiles_border_color',
			[
				'label' => __('Border Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'default' => '#675c5c',
				'selectors' => [
					'.woo-variation-swatches .variable-items-wrapper .variable-item.button-variable-item' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .variable-item' => 'border-color: {{VALUE}};'

				],
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
					'.variable-items-wrapper .variable-item.button-variable-item.selected' => 'border: {{SIZE}}{{UNIT}} solid;',
					'{{WRAPPER}} .variable-item.selected' => 'border-width: {{SIZE}}{{UNIT}};',
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
					'.variable-items-wrapper .variable-item.button-variable-item.selected' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .variable-item.selected' => 'border-color: {{VALUE}};'

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
					'.variable-item .variable-item-span-button-price, .woo-variation-swatches.elementor-page .variable-items-wrapper .variable-item:not(.radio-variable-item).button-variable-item' => 'text-align: {{VALUE}};',
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
					'.variable-item .variable-item-span-button-offers' => 'width: {{SIZE}}{{UNIT}}',
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
					'.variable-item .variable-item-span-button-offers' => 'background-color: {{VALUE}};'

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
					'.variable-item .variable-item-span-button-offers' => 'color: {{VALUE}};'

				],
			]
		);
		$this->add_responsive_control(
			'label_margin',
			[
				'label' => __('Label Margin', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'desktop_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 10],
				'tablet_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 10],
				'mobile_default' =>  ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 5],
				'allowed_dimensions' => ['left', 'right', 'bottom'],
				'selectors' => [
					'.variable-item .variable-item-span-button-offers' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'.variable-item .variable-item-span-button-offers' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
					'.variable-item .variable-item-span-button-offers' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'label_typography',
				'selector' => '.variable-item .variable-item-span-button-offers',
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
					'.variable-items-wrapper .variable-item-span-button-offers' => 'text-align: {{VALUE}};',
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
		$this->add_control(
			'regular_price_color',
			[
				'label' => __('Regular Text Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'default' => '#2E282A',
				'selectors' => [
					'.variable-item .variable-item-span-button-price .reg-price' => 'color: {{VALUE}};'

				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'regular_price_typography',
				'selector' => '.variable-item .variable-item-span-button-price .reg-price',
				'fields_options' => [
					'font_weight' => ['default' => '600'],
					'font_family' => ['default' => "Inter",],
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '26']]
				],
			]
		);
		$this->add_responsive_control(
			'regular_price_padding',
			[
				'label' => __('Padding', 'elementor'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'desktop_default' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
				'tablet_default' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
				'mobile_default' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
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
					'.variable-item .variable-item-span-button-price.s-price .reg-price' => 'color: {{VALUE}};'

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
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '14']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '20']],
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
					'.variable-item .variable-item-span-button-price .sale-price' => 'color: {{VALUE}};'

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
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '20']]
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
				'label' => __('Attribute Label', 'elementor'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'attribute_label_color',
			[
				'label' => __('Attribute Text Color', 'elementor'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'.variable-item .variable-item-span.variable-item-span-button' => 'color: {{VALUE}};'
				],
				'default' => '#2E282A'
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'attribute_label_typography',
				'selector' => '.variable-item .variable-item-span.variable-item-span-button',
				'fields_options' => [
					'font_weight' => ['default' => '600'],
					'font_family' => ['default' => 'Inter'],
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '20']]
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
					'.variable-items-wrapper .variable-item .variable-item-span.variable-item-span-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'.variable-items-wrapper .variable-item.button-variable-item div.variable-item-span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
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
					'.elementor-text-icon.elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
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
					'.woocommerce-variation-description span.elementor-text-content-wrapper' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'label' => __('Prefix Text Typography', 'elementor'),
				'name' => 'prefix_text_typography',
				'selector' => '.woocommerce-variation-description span.elementor-text-content-wrapper',
				'fields_options' => [
					'font_weight' => ['default' => '600'],
					'font_family' => ['default' => 'Inter',],
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '25.56']]
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
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '27']]
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
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '18']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '36']]
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
					'font_size'   => ['default' => ['unit' => 'px', 'size' => '32']],
					'line_height' => ['default' => ['unit' => 'px', 'size' => '36']]
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
	}

	protected function render()
	{
		global $product, $post;
		$settings = $this->get_settings_for_display();
		$settings['product_id'] = $post->ID; //$this->get_settings( 'product_id' );
		$settings['number_of_tiles'] = $this->get_settings('number_of_tiles');
		if (!empty($settings['product_id'])) {
			$product_id = $settings['product_id'];
		} elseif (wp_doing_ajax()) {
			$product_id = absint($_POST['post_id'] ?? 0);
		} else {
			$product_id = get_queried_object_id();
		}
        $this->add_render_attribute( '_wrapper', 'class', 'summary product' );

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

		add_filter('woocommerce_get_stock_html', '__return_empty_string');
		add_filter('woocommerce_product_single_add_to_cart_text', $text_callback);
		// add_filter('wvs_default_variable_item', array($this, 'protiles_wvs_variable_item'), 60, 5 );
		add_filter('woo_variation_swatches_html', array($this, 'protiles_wvs_swatch_variable_item'), 60, 4);
		add_filter('esc_html', [$this, 'unescape_html'], 10, 2);
		add_filter('woocommerce_attribute_label', array($this, 'get_attribute_label'), 10, 3);
		add_filter('woocommerce_short_description', array($this, 'pvt_add_text_short_descriptions'), 50, 1);
		add_filter('woocommerce_available_variation', array($this, 'variation_price_preffix'), 10, 3);
		if ('yes' !== $settings['show_quantity']) {
			add_filter('woocommerce_is_sold_individually', array($this, 'pvt_remove_all_quantity_fields'), 10, 2);
		}

		ob_start();
		woocommerce_template_single_add_to_cart();
		$form = ob_get_clean();
		$form = str_replace('single_add_to_cart_button', 'single_add_to_cart_button elementor-button', $form);
		echo $form;
		if ('yes' !== $settings['show_quantity']) {
			remove_filter('woocommerce_is_sold_individually', 'pvt_remove_all_quantity_fields');
		}
		remove_filter('woocommerce_available_variation', array($this, 'variation_price_preffix'));
		remove_filter('woocommerce_product_single_add_to_cart_text', $text_callback);
		remove_filter('woocommerce_get_stock_html', '__return_empty_string');
		remove_filter('esc_html', [$this, 'unescape_html']);
		remove_filter('woocommerce_attribute_label', array($this, 'get_attribute_label'), 10, 3);
	}
	function pvt_remove_all_quantity_fields($return, $product)
	{
		return true;
	}

	function variation_price_preffix($variation_data, $product, $variation)
	{
		$settings = $this->get_settings_for_display();
		$variation_data['price_html'] = '<span class="price-preffix">' . __($this->get_settings('total_price_prefix_text'), "woocommerce") . '</span> ' . $variation_data['price_html'];

		return $variation_data;
	}
	function pvt_add_text_short_descriptions($description)
	{
		$settings = $this->get_settings_for_display();
		$migrated = isset($settings['__fa4_migrated']['short_description_prefix_icon']);
		$is_new = empty($settings['icon']) && Icons_Manager::is_migration_allowed();



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
						<?php endif; ?></span>
				<?php endif; ?>
				<span <?php echo $this->get_render_attribute_string('short_description_text'); ?>>
					<?php echo $this->get_settings('short_description_prefix_text'); ?>
				</span>

			</span>
		<?php $icon = ob_get_contents();
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
		</span>
	<?php
	}

	function get_attribute_label($label, $name, $product)
	{
		// For "pa_farge" attribute taxonomy on single product pages.
		// return  __('NEW NAME', 'product-tiles');
		$settings = $this->get_settings_for_display();

		$label = $this->get_settings('bundle_prefix');
		$migrated = isset($settings['__fa4_migrated']['bundle_prefix_icon']);
		$is_new = empty($settings['icon']) && Icons_Manager::is_migration_allowed();



		$this->add_render_attribute([
			'bundle-content-wrapper' => [
				'class' => 'elementor-text-content-wrapper',
			],
			'bundle_prefix_icon_align' => [
				'class' => [
					'elementor-bundle-text-icon',
					'elementor-align-icon-left',
				],
			],
			'bundle_text' => [
				'class' => 'elementor-bundle-label-text',
			],
		]);

		ob_start(); ?>
		<span class="elementor-text-content-wrapper">
			<?php if (!empty($settings['bundle_prefix_icon']) || !empty($settings['bundle_prefix_icon']['value'])) : ?>
				<span class="elementor-bundle-text-icon elementor-align-icon-left">
					<?php if ($is_new || $migrated) :
						Icons_Manager::render_icon($settings['bundle_prefix_icon'], ['aria-hidden' => 'true']);
					else : ?>
						<i class="<?php echo esc_attr($settings['bundle_prefix_icon']); ?>" aria-hidden="true"></i>
					<?php endif; ?></span>
			<?php endif; ?>
			<span class="elementor-bundle-label-text elementor-label-text">
				<?php echo $this->get_settings('bundle_prefix_text'); ?>
			</span>

		</span>
	<?php $icon = ob_get_contents();
		ob_end_clean();

		return $icon . $label;
	}

	function protiles_wvs_variable_item($data, $type, $options, $args, $array)
	{
		global  $product_tiles_public;
		$product   = $args['product'];
		$attribute = $args['attribute'];
		$assigned  = $args['assigned'];

		$settings = $this->get_settings_for_display();
		$is_archive           = (isset($args['is_archive']) && $args['is_archive']);
		$show_archive_tooltip = wc_string_to_bool(get_option('show_tooltip_on_archive'));

		$data = '';

		if (isset($args['fallback_type']) && $args['fallback_type'] === 'select') {
			//	return '';
		}

		if (!empty($options)) {
			if ($product && taxonomy_exists($attribute)) {
				$terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all', 'menu_order' => 'ASC'));
				$args = array(
					'post_type'     => 'product_variation',
					'post_status'   => array('private', 'publish'),
					'numberposts'   => -1,
					'orderby'       => 'menu_order',
					'order'         => 'asc',
					'post_parent'   => $product->get_ID() // get parent post-ID
				);
				$variations = $product->get_available_variations();
				$pro_title = $product->get_title();
				$pro_terms = [];
				if (!empty($variations)) {
					foreach ($variations as $variation) {

						$title = explode($pro_title . " - ", $variation->post_title);

						$key = $this->getkey($terms, $title[1]);
						$pro_terms[] = $terms[$key];
					}
				}
				$name  = uniqid(wc_variation_attribute_name($attribute));

				foreach ($pro_terms as $term) {
					$variation_id = get_variation_id($term->name, $term, $attribute, $product);
					if (is_variation_product_enbled($variation_id) === true)
						if (in_array($term->slug, $options, true)) {
							$option = esc_html(apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute, $product));
							$price = display_variation_price($term->name, $term, $attribute, $product);
							$offer_label = variation_custom_field($term->name, $term, $attribute, $product, 'offer_label');
							$is_selected = (isset($args['selected']) && sanitize_title($args['selected']) == $term->slug) ? true : false;

							$selected_class = $is_selected ? 'selected' : '';
							$tooltip        = trim(apply_filters('wvs_variable_item_tooltip', $option, $term, $args));

							if ($is_archive && !$show_archive_tooltip) {
								$tooltip = false;
							}

							$tooltip_html_attr       = !empty($tooltip) ? sprintf(' data-wvstooltip="%s"', esc_attr($tooltip)) : '';
							$screen_reader_html_attr = $is_selected ? ' aria-checked="true"' : ' aria-checked="false"';
							if (wp_is_mobile()) {
								$tooltip_html_attr .= !empty($tooltip) ? ' tabindex="2"' : '';
							}
							$type = isset($assigned[$term->slug]) ? $assigned[$term->slug]['type'] : $type;
							if (!isset($assigned[$term->slug]) || empty($assigned[$term->slug]['image_id'])) {
								$type = 'button';
							}
							$data .= sprintf('<li %1$s class="variable-item %2$s-variable-item %2$s-variable-item-%3$s %4$s product-variant-tiles-v1" title="%5$s" data-title="%5$s"  data-value="%3$s" role="radio" tabindex="0"><div class="variable-item-contents">', $screen_reader_html_attr . $tooltip_html_attr, esc_attr($type), esc_attr($term->slug), esc_attr($selected_class), $option);

							switch ($type):

								case 'image':
									$attachment_id = $assigned[$term->slug]['image_id'];
									$image_size    = sanitize_text_field(get_option('attribute_image_size'));
									$image         = wp_get_attachment_image_src($attachment_id, apply_filters('wvs_product_attribute_image_size', $image_size, $attribute, $product));

									$data .= sprintf('<img class="variable-item-image" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" />', esc_attr($option), esc_url($image[0]), esc_attr($image[1]), esc_attr($image[2]));
									// $data .= $image_html;
									break;

								case 'button':

									$data .= sprintf('<div class="variable-item-span variable-item-span-%s">%s</div>', esc_attr($type), $option);
									if (isset($price['sale']) && $price['sale'] != '') {
										$data .= sprintf('<div class="variable-item-span-%s-price s-price"><span class="reg-price"><del>%s</del> </span><span class="sale-price">%s</span></div>', esc_attr($type), esc_html($price['regular']), esc_html($price['sale']));
									} else {
										$data .= sprintf('<div class="variable-item-span-%s-price"><span class="reg-price">%s</span></div>', esc_attr($type), esc_html($price['regular']));
									}
									if ($offer_label != '') {
										$data .= sprintf('<div class="variable-item-span-%s-offers">%s</div>', esc_attr($type), $offer_label);
									}
									break;

								default:
									$data .= apply_filters('wvs_variable_default_item_content', '', $term, $args, $saved_attribute);
									break;
							endswitch;
							$data .= '</div></li>';
						}
				}
			} else {

				foreach ($options as $option) {

					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.

					$option = esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute, $product));
					$price = display_variation_price($term, $option, $attribute, $product);

					$offer_label = variation_custom_field($term->name, $term, $attribute, $product, 'offer_label');
					$is_selected = (sanitize_title($option) == sanitize_title($args['selected']));

					$selected_class = $is_selected ? 'selected' : '';
					$tooltip        = trim(apply_filters('wvs_variable_item_tooltip', $option, $options, $args));

					if ($is_archive && !$show_archive_tooltip) {
						$tooltip = false;
					}

					$tooltip_html_attr       = !empty($tooltip) ? sprintf('data-wvstooltip="%s"', esc_attr($tooltip)) : '';
					$screen_reader_html_attr = $is_selected ? ' aria-checked="true"' : ' aria-checked="false"';

					if (wp_is_mobile()) {
						$tooltip_html_attr .= !empty($tooltip) ? ' tabindex="2"' : '';
					}

					$type = isset($assigned[$option]) ? $assigned[$option]['type'] : $type;

					if (!isset($assigned[$option]) || empty($assigned[$option]['image_id'])) {
						$type = 'button';
					}

					$data .= sprintf('<li %1$s class="variable-item %2$s-variable-item %2$s-variable-item-%3$s %4$s" title="%5$s" data-title="%5$s"  data-value="%3$s" role="radio" tabindex="0"><div class="variable-item-contents">', $screen_reader_html_attr . $tooltip_html_attr, esc_attr($type), esc_attr($option), esc_attr($selected_class), esc_html($option));

					switch ($type):

						case 'image':
							$attachment_id = $assigned[$option]['image_id'];
							$image_size    = sanitize_text_field(get_option('attribute_image_size'));
							$image         = wp_get_attachment_image_src($attachment_id, apply_filters('wvs_product_attribute_image_size', $image_size, $attribute, $product));

							$data .= sprintf('<img class="variable-item-image" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" />', esc_attr($option), esc_url($image[0]), esc_attr($image[1]), esc_attr($image[2]));
							// $data .= $image_html;
							break;

						case 'button':

							$data .= sprintf('<span class="variable-item-span variable-item-span-%s">%s</span>', esc_attr($type), esc_html($option));
							if ($price['sale'] != '') {
								$data .= sprintf('<div class="variable-item-span-%s-price"><span class="reg-price"><del>%s</del></span><span class="sale-price">%s</span></div>', esc_attr($type), esc_html($price['regular']), esc_html($price['sale']));
							} else {
								$data .= sprintf('<span class="variable-item-span-%s-price">%s</span>', esc_attr($type), esc_html($price));
							}
							if ($offer_label != '') {
								$data .= sprintf('<span class="variable-item-span-%s-offers">%s</span>', esc_attr($type), $offer_label);
							}
							break;

						default:
							$data .= apply_filters('wvs_variable_default_item_content', '', $option, $args, array());
							break;
					endswitch;
					$data .= '</div></li>';
				}
			}
		}
		return $data;
	}

	function protiles_woocommerce_select($args, $swatches_data, $obj)
	{
		$html = '';
		$options          = $args['options'];
		$product          = $args['product'];
		$attribute        = $args['attribute'];
		$name             = $args['name'] ? $args['name'] : wc_variation_attribute_name($attribute);
		$id               = $args['id'] ? $args['id'] : sanitize_title($attribute);
		$class            = $args['class'];
		$show_option_none = (bool) $args['show_option_none'];
		// $show_option_none      = true;
		$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : esc_html__('Choose an option', 'woocommerce'); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

		if (empty($options) && !empty($product) && !empty($attribute)) {
			$attributes = $product->get_variation_attributes();
			$options    = $attributes[$attribute];
		}

		// Default Convert to button
		$global_convert_to_button = wc_string_to_bool(get_option('default_to_button', 'yes'));
		$get_attribute            = get_attribute_taxonomy_by_name($attribute);
		$attribute_types          = array_keys(array(
			'select' => esc_html__('Select', 'woo-variation-swatches'),
			'color'  => esc_html__('Color', 'woo-variation-swatches'),
			'image'  => esc_html__('Image', 'woo-variation-swatches'),
			'button' => esc_html__('Button', 'woo-variation-swatches'),
			'radio'  => esc_html__('Radio', 'woo-variation-swatches'),
		));
		$attribute_type           = ($get_attribute) ? $get_attribute->attribute_type : 'select';
		$swatches_data            = array();

		if (!in_array($attribute_type, $attribute_types)) {
			return $html;
		}

		$select_inline_style = '';

		if ($global_convert_to_button && $attribute_type === 'select') {
			$attribute_type = 'button';
		}

		if ($attribute_type !== 'select') {
			$select_inline_style = 'style="display:none"';
			$class               .= ' woo-variation-raw-select';
		}

		$html = '<select ' . $select_inline_style . ' id="' . esc_attr($id) . '" class="' . esc_attr($class) . '" name="' . esc_attr($name) . '" data-attribute_name="' . esc_attr(wc_variation_attribute_name($attribute)) . '" data-show_option_none="' . ($show_option_none ? 'yes' : 'no') . '">';
		$html .= '<option value="">' . esc_html($show_option_none_text) . '</option>';

		if (!empty($options)) {
			if ($product && taxonomy_exists($attribute)) {
				// Get terms if this is a taxonomy - ordered. We need the names too.
				$terms = wc_get_product_terms($product->get_id(), $attribute, array(
					'fields' => 'all',
				));

				foreach ($terms as $term) {
					if (in_array($term->slug, $options, true)) {

						$swatches_data[] = $obj->get_swatch_data($args, $term);

						$html .= '<option value="' . esc_attr($term->slug) . '" ' . selected(sanitize_title($args['selected']), $term->slug, false) . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute, $product)) . '</option>';
					}
				}
			} else {
				foreach ($options as $option) {
					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
					$selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);

					$swatches_data[] = $obj->get_swatch_data($args, $option);

					$html .= '<option value="' . esc_attr($option) . '" ' . $selected . '>' . esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute, $product)) . '</option>';
				}
			}
		}

		$html .= '</select>';

		return $html;
	}

	function protiles_wvs_swatch_variable_item($data, $args, $swatches_data, $obj)
	{
		global  $product_tiles_public;
		$product   = $args['product'];
		$attribute = $args['attribute'];
		$assigned  = $args['assigned'];
		$options   = $args['options'];

		$settings = $this->get_settings_for_display();
		$is_archive           = (isset($args['is_archive']) && $args['is_archive']);
		$show_archive_tooltip = wc_string_to_bool(get_option('show_tooltip_on_archive'));

		$data = $this->protiles_woocommerce_select($args, $swatches_data, $obj);

		if (isset($args['fallback_type']) && $args['fallback_type'] === 'select') {
			//	return '';
		}

		$get_attribute            = get_attribute_taxonomy_by_name($attribute);
		$attribute_types          = array_keys(array(
			'select' => esc_html__('Select', 'woo-variation-swatches'),
			'color'  => esc_html__('Color', 'woo-variation-swatches'),
			'image'  => esc_html__('Image', 'woo-variation-swatches'),
			'button' => esc_html__('Button', 'woo-variation-swatches'),
			'radio'  => esc_html__('Radio', 'woo-variation-swatches'),
		));
		$attribute_type           = ($get_attribute) ? $get_attribute->attribute_type : 'select';

		if (!empty($options) && !empty($swatches_data) && $product) {
			$data .= $obj->wrapper_start($args, $attribute, $product, $attribute_type, $options);
			if ($product && taxonomy_exists($attribute)) {
				$terms = wc_get_product_terms($product->get_id(), $attribute, array('fields' => 'all', 'menu_order' => 'ASC'));
				$args = array(
					'post_type'     => 'product_variation',
					'post_status'   => array('private', 'publish'),
					'numberposts'   => -1,
					'orderby'       => 'menu_order',
					'order'         => 'asc',
					'post_parent'   => $product->get_ID() // get parent post-ID
				);
				$variations = $product->get_available_variations();
				$pro_title = $product->get_title();
				$pro_terms = [];
				if (!empty($variations)) {
					foreach ($variations as $variation) {

						$title = explode($pro_title . " - ", $variation->post_title);

						$key = $this->getkey($terms, $title[1]);
						$pro_terms[] = $terms[$key];
					}
				}

				$name  = uniqid(wc_variation_attribute_name($attribute));

				foreach ($pro_terms as $term) {
					$variation_id = get_variation_id($term->name, $term, $attribute, $product);
					if (is_variation_product_enbled($variation_id) === true)
						if (in_array($term->slug, $options, true)) {
							$option = esc_html(apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute, $product));
							$price = display_variation_price($term->name, $term, $attribute, $product);
							$offer_label = variation_custom_field($term->name, $term, $attribute, $product, 'offer_label');
							$is_selected = (isset($args['selected']) && sanitize_title($args['selected']) == $term->slug) ? true : false;

							$selected_class = $is_selected ? 'selected' : '';
							$tooltip        = trim(apply_filters('wvs_variable_item_tooltip', $option, $term, $args));

							if ($is_archive && !$show_archive_tooltip) {
								$tooltip = false;
							}

							$tooltip_html_attr       = !empty($tooltip) ? sprintf(' data-wvstooltip="%s"', esc_attr($tooltip)) : '';
							$screen_reader_html_attr = $is_selected ? ' aria-checked="true"' : ' aria-checked="false"';
							if (wp_is_mobile()) {
								$tooltip_html_attr .= !empty($tooltip) ? ' tabindex="2"' : '';
							}
							$type = isset($assigned[$term->slug]) ? $assigned[$term->slug]['type'] : $type;
							if (!isset($assigned[$term->slug]) || empty($assigned[$term->slug]['image_id'])) {
								$type = 'button';
							}
							$data .= sprintf('<li %1$s class="variable-item %2$s-variable-item %2$s-variable-item-%3$s %4$s product-variant-tiles-v1" title="%5$s" data-title="%5$s"  data-value="%3$s" role="radio" tabindex="0"><div class="variable-item-contents">', $screen_reader_html_attr . $tooltip_html_attr, esc_attr($type), esc_attr($term->slug), esc_attr($selected_class), $option);

							switch ($type):

								case 'image':
									$attachment_id = $assigned[$term->slug]['image_id'];
									$image_size    = sanitize_text_field(get_option('attribute_image_size'));
									$image         = wp_get_attachment_image_src($attachment_id, apply_filters('wvs_product_attribute_image_size', $image_size, $attribute, $product));

									$data .= sprintf('<img class="variable-item-image" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" />', esc_attr($option), esc_url($image[0]), esc_attr($image[1]), esc_attr($image[2]));
									// $data .= $image_html;
									break;

								case 'button':

									$data .= sprintf('<div class="variable-item-span variable-item-span-%s">%s</div>', esc_attr($type), $option);
									if (isset($price['sale']) && $price['sale'] != '') {
										$data .= sprintf('<div class="variable-item-span-%s-price s-price"><span class="reg-price"><del>%s</del> </span><span class="sale-price">%s</span></div>', esc_attr($type), esc_html($price['regular']), esc_html($price['sale']));
									} else {
										$data .= sprintf('<div class="variable-item-span-%s-price"><span class="reg-price">%s</span></div>', esc_attr($type), esc_html($price['regular']));
									}
									if ($offer_label != '') {
										$data .= sprintf('<div class="variable-item-span-%s-offers">%s</div>', esc_attr($type), $offer_label);
									}
									break;

								default:
									$data .= apply_filters('wvs_variable_default_item_content', '', $term, $args, $saved_attribute);
									break;
							endswitch;
							$data .= '</div></li>';
						}
				}
			} else {

				foreach ($options as $option) {

					// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.

					$option = esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute, $product));
					$price = display_variation_price($term, $option, $attribute, $product);

					$offer_label = variation_custom_field($term->name, $term, $attribute, $product, 'offer_label');
					$is_selected = (sanitize_title($option) == sanitize_title($args['selected']));

					$selected_class = $is_selected ? 'selected' : '';
					$tooltip        = trim(apply_filters('wvs_variable_item_tooltip', $option, $options, $args));

					if ($is_archive && !$show_archive_tooltip) {
						$tooltip = false;
					}

					$tooltip_html_attr       = !empty($tooltip) ? sprintf('data-wvstooltip="%s"', esc_attr($tooltip)) : '';
					$screen_reader_html_attr = $is_selected ? ' aria-checked="true"' : ' aria-checked="false"';

					if (wp_is_mobile()) {
						$tooltip_html_attr .= !empty($tooltip) ? ' tabindex="2"' : '';
					}

					$type = isset($assigned[$option]) ? $assigned[$option]['type'] : $type;

					if (!isset($assigned[$option]) || empty($assigned[$option]['image_id'])) {
						$type = 'button';
					}

					$data .= sprintf('<li %1$s class="variable-item %2$s-variable-item %2$s-variable-item-%3$s %4$s" title="%5$s" data-title="%5$s"  data-value="%3$s" role="radio" tabindex="0"><div class="variable-item-contents">', $screen_reader_html_attr . $tooltip_html_attr, esc_attr($type), esc_attr($option), esc_attr($selected_class), esc_html($option));

					switch ($type):

						case 'image':
							$attachment_id = $assigned[$option]['image_id'];
							$image_size    = sanitize_text_field(get_option('attribute_image_size'));
							$image         = wp_get_attachment_image_src($attachment_id, apply_filters('wvs_product_attribute_image_size', $image_size, $attribute, $product));

							$data .= sprintf('<img class="variable-item-image" aria-hidden="true" alt="%s" src="%s" width="%d" height="%d" />', esc_attr($option), esc_url($image[0]), esc_attr($image[1]), esc_attr($image[2]));
							// $data .= $image_html;
							break;

						case 'button':

							$data .= sprintf('<span class="variable-item-span variable-item-span-%s">%s</span>', esc_attr($type), esc_html($option));
							if ($price['sale'] != '') {
								$data .= sprintf('<div class="variable-item-span-%s-price"><span class="reg-price"><del>%s</del></span><span class="sale-price">%s</span></div>', esc_attr($type), esc_html($price['regular']), esc_html($price['sale']));
							} else {
								$data .= sprintf('<span class="variable-item-span-%s-price">%s</span>', esc_attr($type), esc_html($price));
							}
							if ($offer_label != '') {
								$data .= sprintf('<span class="variable-item-span-%s-offers">%s</span>', esc_attr($type), $offer_label);
							}
							break;

						default:
							$data .= apply_filters('wvs_variable_default_item_content', '', $option, $args, array());
							break;
					endswitch;
					$data .= '</div></li>';
				}
			}
			$data .= $obj->wrapper_end();
		}
		return $data;
	}

	function getkey($products, $value)
	{

		foreach ($products as $key => $product) {

			if ($product->name === $value)
				return $key;
		}
		return false;
	}

	function enqueue_custom_variation_swatch_script()
	{
	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
			// Only run on product pages
			if (!$('body').hasClass('single-product') && !$('body').hasClass('woocommerce-page')) {
				return;
			}

				function setDefaultActiveClass() {
					var selectElement = $('select.woo-variation-raw-select');
					var selectedValue = selectElement.val();
					$('ul.variable-items-wrapper.wvs-style-squared li').each(function() {
						if ($(this).data('value') == selectedValue) {
							$(this).addClass('active');
						} else {
							$(this).removeClass('active');
						}
					});
				}

				setDefaultActiveClass();

				// When an li element is clicked
				$('ul.variable-items-wrapper.wvs-style-squared li').on('click', function() {

					// Get the data-value attribute of the clicked li element
					var selectedValue = $(this).data('value');

					// Find the select element
					var selectElement = $('select.woo-variation-raw-select');

					// Set the select element's value to the selectedValue
					selectElement.val(selectedValue).trigger('change');
					$('ul.variable-items-wrapper.wvs-style-squared li').removeClass('active');
					$(this).addClass('active');
				});
			});
		</script>
<?php
	}
}
