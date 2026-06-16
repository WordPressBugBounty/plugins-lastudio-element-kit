<?php

/**
 * Class: LaStudioKit_Woo_Single_Product_Images
 * Name: Product Images
 * Slug: lakit-wooproduct-images
 */

namespace Elementor;

if (!defined('WPINC')) {
    die;
}

/**
 * Woo Widget
 */
class LaStudioKit_Woo_Single_Product_Images extends LaStudioKit_Base {

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    public function get_style_depends()
    {
        return ['lastudio-kit-woocommerce' ];
    }

    public function get_name() {
        return 'lakit-wooproduct-images';
    }

    public function get_categories() {
        return [ 'lastudiokit-woo-product' ];
    }

    public function get_keywords() {
        return [ 'woocommerce', 'shop', 'store', 'image', 'product', 'gallery', 'lightbox' ];
    }

    public function get_widget_title() {
        return esc_html__( 'Product Images', 'lastudio-kit' );
    }

    public function get_icon() {
        return 'eicon-product-images';
    }

    protected function register_controls()
    {

        $this->start_controls_section(
            'section_product',
            [
                'label' => esc_html__('Product', 'lastudio-kit'),
            ]
        );
        $this->add_control(
            'wc_product_warning',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('Leave a blank to get the data for current product.', 'lastudio-kit'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );
        $this->add_control(
            'product_id',
            [
                'label' => esc_html__('Product', 'lastudio-kit'),
                'type' => 'lastudiokit-query',
                'options' => [],
                'label_block' => true,
                'autocomplete' => [
                    'object' => 'post',
                    'query' => [
                        'post_type' => ['product'],
                    ],
                ],
            ]
        );
        $this->end_controls_section();

        $this->register_lastudio_theme_controls();
    }

    protected function register_lastudio_theme_controls()
    {
        $this->start_controls_section(
            'section_product_gallery_layout',
            [
                'label' => esc_html__( 'Setting', 'lastudio-kit' ),
            ]
        );
        $this->add_control(
            'layout_type',
            [
                'label' => esc_html__( 'Gallery Layout', 'lastudio-kit' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    '1' => esc_html__('Thumbnail at bottom', 'lastudio-kit'),
                    '2' => esc_html__('Thumbnail at left', 'lastudio-kit'),
                    '3' => esc_html__('Thumbnail at right', 'lastudio-kit'),
                    '4' => esc_html__('No thumbnail', 'lastudio-kit'),
                    '5' => esc_html__('Metro', 'lastudio-kit'),
                    '6' => esc_html__('Flat', 'lastudio-kit'),
                    'wc' => esc_html__('Default from WooCommerce', 'lastudio-kit'),
                ],
                'default' => '1',
            ]
        );
        $this->add_control(
            'force_layout_bottom',
            [
                'label' => esc_html__( 'Mobile layout', 'lastudio-kit' ),
                'type' => Controls_Manager::SELECT,
                'description' => esc_html__( 'Note: Choose at which breakpoint thumbnails will automatically move to the bottom.', 'lastudio-kit' ),
                'options' => [
                        'none' => esc_html__( 'None', 'lastudio-kit' )
                    ] + lastudio_kit_helper()->get_active_breakpoints(false, true),
                'default' => 'none',
                'condition' => [
                    'layout_type' => ['2', '3']
                ]
            ]
        );
        $this->add_responsive_control(
            'gallery_column',
            [
                'label' => esc_html__( 'Gallery Column', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'range' => array(
                    'px' => array(
                        'min' => 1,
                        'max' => 10,
                        'step' => 1
                    ),
                ),
                'selectors' => [
                    '{{WRAPPER}}' => '--singleproduct-image-column: {{SIZE}}',
                ],
                'condition' => [
                    'layout_type' => ['1', '4']
                ]
            ]
        );
        $this->add_responsive_control(
            'thumb_width',
            [
                'label' => esc_html__( 'Thumbnail Width', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%', 'vh', 'vw' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--singleproduct-thumbs-width: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'layout_type' => ['2','3']
                ]
            ]
        );
        $this->add_control(
            'sale_flash',
            [
                'label' => esc_html__( 'Sale Flash', 'lastudio-kit' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'lastudio-kit' ),
                'label_off' => esc_html__( 'Hide', 'lastudio-kit' ),
                'render_type' => 'template',
                'return_value' => 'yes',
                'default' => 'yes'
            ]
        );
        $this->add_control(
            'enable_zoom',
            [
                'label' => esc_html__( 'Enable Zoom', 'lastudio-kit' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'lastudio-kit' ),
                'label_off' => esc_html__( 'No', 'lastudio-kit' ),
                'return_value' => 'yes',
                'default' => '',
                'render_type' => 'ui',
                'frontend_available' => true,
            ]
        );
        $this->add_control(
            'zoom_scale',
            [
                'label' => esc_html__( 'Max Scale', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'render_type' => 'ui',
                'range' => array(
                    'px' => array(
                        'min' => 1,
                        'max' => 3,
                        'step' => 0.1
                    ),
                ),
                'condition' => [
                    'enable_zoom' => 'yes'
                ],
                'frontend_available' => true,
            ]
        );
        $this->end_controls_section();
        $this->start_controls_section(
            'section_product_image_style',
            [
                'label' => esc_html__( 'Main Gallery', 'lastudio-kit' ),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'image_bg',
            [
                'label' => esc_html__( 'Background Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-product-gallery__image' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .layout-type-4 .woocommerce-product-gallery__image a' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'custom_main_height',
            [
                'label' => esc_html__( 'Custom Image Height', 'lastudio-kit' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'lastudio-kit' ),
                'label_off' => esc_html__( 'No', 'lastudio-kit' ),
                'return_value' => 'yes',
                'prefix_class' => 'custom-main-height-',
                'condition' => [
                    'layout_type!' => '5'
                ]
            ]
        );

        $this->add_responsive_control(
            'main_image_height',
            [
                'label' => esc_html__( 'Height', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--singleproduct-image-height: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'custom_main_height' => 'yes',
                    'layout_type!' => '5'
                ]
            ]
        );
        $this->add_responsive_control(
            'main_image_spacing',
            [
                'label' => esc_html__( 'Main image gap', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--singleproduct-image-spacing: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'image_big_height',
            [
                'label' => esc_html__( 'Image big height', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%', 'vh', 'vw' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--singleproduct-image-height2: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'layout_type' => ['5']
                ]
            ]
        );
        $this->add_responsive_control(
            'image_small_height',
            [
                'label' => esc_html__( 'Image small height', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%', 'vh', 'vw' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--singleproduct-image-height: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'layout_type' => ['5']
                ]
            ]
        );

        $this->add_responsive_control(
            'gallery_radius',
            array(
                'label'      => esc_html__( 'Border Radius', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--singleproduct-gallery-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

	    $this->add_responsive_control(
		    'gallery_padding',
		    array(
			    'label'      => esc_html__( 'Padding', 'lastudio-kit' ),
			    'type'       => Controls_Manager::DIMENSIONS,
			    'size_units' => array( 'px', '%', 'em' ),
			    'selectors'  => array(
				    '{{WRAPPER}}' => '--singleproduct-gallery-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			    ),
		    )
	    );

	    $this->add_group_control(
		    Group_Control_Border::get_type(),
		    array(
			    'name'        => 'gallery_border',
			    'label'       => esc_html__( 'Border', 'lastudio-kit' ),
			    'placeholder' => '1px',
			    'default'     => '1px',
			    'selector'    => '{{WRAPPER}} .lakit-product-images:is(.layout-type-5, .layout-type-6) .woocommerce-product-gallery__image, {{WRAPPER}} .lakit-product-images:is(.layout-type-1, .layout-type-2, .layout-type-3, .layout-type-wc) .lakit-embla-main, {{WRAPPER}} .layout-type-4 .woocommerce-product-gallery__image a',
		    )
	    );

	    $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'gallery_shadow',
                'selector' => '{{WRAPPER}} .lakit-product-images:is(.layout-type-5, .layout-type-6) .woocommerce-product-gallery__image, {{WRAPPER}} .lakit-product-images:is(.layout-type-1, .layout-type-2, .layout-type-3, .layout-type-wc) .lakit-embla-main, {{WRAPPER}} .layout-type-4 .woocommerce-product-gallery__image a',
            )
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_thumbnail',
            [
                'label' => esc_html__( 'Thumbnails', 'lastudio-kit' ),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'layout_type!' => ['4', '5', '6']
                ]
            ]
        );

        $this->add_control(
            'custom_thumb_height',
            [
                'label' => esc_html__( 'Custom Thumbnail Height', 'lastudio-kit' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Yes', 'lastudio-kit' ),
                'label_off' => esc_html__( 'No', 'lastudio-kit' ),
                'return_value' => 'yes',
                'prefix_class' => 'custom-thumb-height-',
            ]
        );

        $this->add_responsive_control(
            'thumb_image_height',
            [
                'label' => esc_html__( 'Height', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--singleproduct-thumb-height: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'custom_thumb_height' => 'yes'
                ]
            ]
        );

	    $this->add_responsive_control(
		    'box_thumb_margin',
		    array(
			    'label'      => esc_html__( 'Box Margin', 'lastudio-kit' ),
			    'type'       => Controls_Manager::DIMENSIONS,
			    'size_units' => array( 'px', '%' ),
			    'selectors'  => array(
				    '{{WRAPPER}}' => '--singleproduct-boxthumb-margin-top: {{TOP}}{{UNIT}}; --singleproduct-boxthumb-margin-right: {{RIGHT}}{{UNIT}}; --singleproduct-boxthumb-margin-bottom:{{BOTTOM}}{{UNIT}};--singleproduct-boxthumb-margin-left: {{LEFT}}{{UNIT}};',
			    ),
		    )
	    );

	    $this->add_responsive_control(
		    'thumb_image_spacing',
		    [
			    'label' => esc_html__( 'Thumbnail item gap', 'lastudio-kit' ),
			    'type' => Controls_Manager::SLIDER,
			    'size_units' => [ 'px', 'em' ],
			    'selectors' => [
				    '{{WRAPPER}}' => '--singleproduct-thumb-spacing: {{SIZE}}{{UNIT}}',
			    ],
		    ]
	    );

        $this->add_responsive_control(
            'thumb_radius',
            array(
                'label'      => esc_html__( 'Border Radius', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}}' => '--singleproduct-thumb-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_responsive_control(
            'thumb_padding',
            array(
                'label'      => esc_html__( 'Padding', 'lastudio-kit' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%', 'em' ),
                'selectors'  => array(
                    '{{WRAPPER}} .embla__dot span:first-child' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
	    $this->add_group_control(
		    Group_Control_Border::get_type(),
		    array(
			    'name'        => 'thumb_border',
			    'label'       => esc_html__( 'Border', 'lastudio-kit' ),
			    'placeholder' => '1px',
			    'default'     => '1px',
			    'selector'    => '{{WRAPPER}} .embla__dot span:first-child',
		    )
	    );
	    $this->add_group_control(
		    Group_Control_Box_Shadow::get_type(),
		    array(
			    'name'     => 'thumb_shadow',
			    'selector' => '{{WRAPPER}} .embla__dot span:first-child',
		    )
	    );
        $this->end_controls_section();

        $start_logical = is_rtl() ? 'right' : 'left';
        $end_logical = is_rtl() ? 'left' : 'right';

        $this->_start_controls_section(
            'arrow_style_section',
            array(
                'label' => esc_html__('Arrows', 'lastudio-kit'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'layout_type!' => ['5', '6']
                ]
            )
        );

        $this->add_control(
            'show_arrow_hover',
            [
                'label' => esc_html__('Display on hover', 'lastudio-kit'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'lastudio-kit'),
                'label_off' => esc_html__('No', 'lastudio-kit'),
                'return_value' => 'yes',
                'default' => 'no',
                'prefix_class' => 'lakit-arrow--showonhover-',
            ]
        );

        $this->_add_control(
            'prev_arrow_heading',
            array(
                'label' => esc_html__('Prev Arrow Position', 'lastudio-kit'),
                'type' => Controls_Manager::HEADING,
            )
        );

        $this->add_responsive_control(
            'prev_arrow_v_pos',
            [
                'label' => esc_html__( 'Vertical position', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'top' => [
                        'title' => esc_html__( 'Top', 'lastudio-kit' ),
                        'icon' => 'eicon-v-align-top'
                    ],
                    'middle' => [
                        'title' => esc_html__( 'Middle', 'lastudio-kit' ),
                        'icon' => 'eicon-v-align-middle'
                    ],
                    'bottom' => [
                        'title' => esc_html__( 'Bottom', 'lastudio-kit' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'middle',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'top' => '--e-prev-arrow-v-position:0px;--e-prev-arrow-v-transform:0%;',
                    'middle' => '--e-prev-arrow-v-position:50%;--e-prev-arrow-v-transform:-50%;',
                    'bottom' => '--e-prev-arrow-v-position:100%;--e-prev-arrow-v-transform:-100%;',
                ]
            ]
        );
        $this->add_responsive_control(
            'prev_arrow_v_offset',
            array_merge( $this->dotv2_get_position_slider_initial_configuration(), [
                'label' => esc_html__( 'Vertical offset', 'lastudio-kit' ),
                'selectors' => [
                    '{{WRAPPER}}' => '--e-prev-arrow-v-offset: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'size' => 0,
                ],
            ] )
        );

        $this->add_responsive_control(
            'prev_arrow_h_pos',
            [
                'label' => esc_html__( 'Horizontal position', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'lastudio-kit' ),
                        'icon' => 'eicon-h-align-' . $start_logical
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'lastudio-kit' ),
                        'icon' => 'eicon-h-align-center'
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'lastudio-kit' ),
                        'icon' => 'eicon-h-align-' . $end_logical,
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'left' => '--e-prev-arrow-h-position:0px;--e-prev-arrow-h-transform:0%;',
                    'center' => '--e-prev-arrow-h-position:50%;--e-prev-arrow-h-transform:-50%;',
                    'right' => '--e-prev-arrow-h-position:100%;--e-prev-arrow-h-transform:-100%;',
                ]
            ]
        );
        $this->add_responsive_control(
            'prev_arrow_h_offset',
            array_merge( $this->dotv2_get_position_slider_initial_configuration(), [
                'label' => esc_html__( 'Horizontal offset', 'lastudio-kit' ),
                'selectors' => [
                    '{{WRAPPER}}' => '--e-prev-arrow-h-offset: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'size' => 0,
                ],
            ] )
        );

        $this->_add_control(
            'next_arrow_heading',
            array(
                'label' => esc_html__('Next Arrow Position', 'lastudio-kit'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_responsive_control(
            'next_arrow_v_pos',
            [
                'label' => esc_html__( 'Vertical position', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'top' => [
                        'title' => esc_html__( 'Top', 'lastudio-kit' ),
                        'icon' => 'eicon-v-align-top'
                    ],
                    'middle' => [
                        'title' => esc_html__( 'Middle', 'lastudio-kit' ),
                        'icon' => 'eicon-v-align-middle'
                    ],
                    'bottom' => [
                        'title' => esc_html__( 'Bottom', 'lastudio-kit' ),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'middle',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'top' => '--e-next-arrow-v-position:0px;--e-next-arrow-v-transform:0%;',
                    'middle' => '--e-next-arrow-v-position:50%;--e-next-arrow-v-transform:-50%;',
                    'bottom' => '--e-next-arrow-v-position:100%;--e-next-arrow-v-transform:-100%;',
                ]
            ]
        );
        $this->add_responsive_control(
            'next_arrow_v_offset',
            array_merge( $this->dotv2_get_position_slider_initial_configuration(), [
                'label' => esc_html__( 'Vertical offset', 'lastudio-kit' ),
                'selectors' => [
                    '{{WRAPPER}}' => '--e-next-arrow-v-offset: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'size' => 0,
                ],
            ] )
        );

        $this->add_responsive_control(
            'next_arrow_h_pos',
            [
                'label' => esc_html__( 'Horizontal position', 'lastudio-kit' ),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Left', 'lastudio-kit' ),
                        'icon' => 'eicon-h-align-' . $start_logical
                    ],
                    'center' => [
                        'title' => esc_html__( 'Center', 'lastudio-kit' ),
                        'icon' => 'eicon-h-align-center'
                    ],
                    'right' => [
                        'title' => esc_html__( 'Right', 'lastudio-kit' ),
                        'icon' => 'eicon-h-align-' . $end_logical,
                    ],
                ],
                'default' => 'right',
                'selectors' => [
                    '{{WRAPPER}}' => '{{VALUE}}',
                ],
                'selectors_dictionary' => [
                    'left' => '--e-next-arrow-h-position:0px;--e-next-arrow-h-transform:0%;',
                    'center' => '--e-next-arrow-h-position:50%;--e-next-arrow-h-transform:-50%;',
                    'right' => '--e-next-arrow-h-position:100%;--e-next-arrow-h-transform:-100%;',
                ]
            ]
        );
        $this->add_responsive_control(
            'next_arrow_h_offset',
            array_merge( $this->dotv2_get_position_slider_initial_configuration(), [
                'label' => esc_html__( 'Horizontal offset', 'lastudio-kit' ),
                'selectors' => [
                    '{{WRAPPER}}' => '--e-next-arrow-h-offset: {{SIZE}}{{UNIT}};',
                ],
                'default' => [
                    'size' => 0,
                ],
            ] )
        );

        $this->start_controls_tabs( 'arrow_tabs' );
        $this->start_controls_tab( 'arrow_tab_normal', [
            'label' => esc_html__( 'Normal', 'lastudio-kit' ),
        ]);
        $this->add_control(
            'arrow_color',
            [
                'label' => esc_html__( 'Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'arrow_bgcolor',
            [
                'label' => esc_html__( 'Background Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-bgcolor: {{VALUE}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_icon_size',
            [
                'label' => esc_html__( 'Icon Size', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-iconsize: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_width',
            [
                'label' => esc_html__( 'Width', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_height',
            [
                'label' => esc_html__( 'Height', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_scale',
            [
                'label' => esc_html__( 'Scale', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-scale: {{SIZE}}',
                ],
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.01,
                    ),
                ),
            ]
        );
        $this->add_responsive_control(
            'arrow_opacity',
            [
                'label' => esc_html__( 'Opacity', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-opacity: {{SIZE}}',
                ],
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.01,
                    ),
                ),
            ]
        );
        $this->_add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name' => 'arrow_border',
                'label' => esc_html__('Border', 'lastudio-kit'),
                'placeholder' => '1px',
                'default' => '1px',
                'selector' => '{{WRAPPER}} .lakit-embla__arrow',
            )
        );
        $this->add_responsive_control(
            'arrow_radius',
            [
                'label' => esc_html__( 'Border Radius', 'lastudio-kit' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
					'{{WRAPPER}} .lakit-embla__arrow' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );
        $this->_add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'arrow_shadow',
                'selector' => '{{WRAPPER}} .lakit-embla__arrow'
            )
        );
        $this->end_controls_tab();
        $this->start_controls_tab( 'arrow_tab_hover', [
            'label' => esc_html__( 'Hover', 'lastudio-kit' ),
        ]);
        $this->add_control(
            'arrow_hover_color',
            [
                'label' => esc_html__( 'Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-hover-color: {{VALUE}}',
                ],
            ]
        );
        $this->add_control(
            'arrow_hover_bgcolor',
            [
                'label' => esc_html__( 'Background Color', 'lastudio-kit' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-hover-bgcolor: {{VALUE}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_hover_icon_size',
            [
                'label' => esc_html__( 'Icon Size', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-hover-iconsize: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_hover_width',
            [
                'label' => esc_html__( 'Width', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-hover-width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_hover_height',
            [
                'label' => esc_html__( 'Height', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-hover-height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        $this->add_responsive_control(
            'arrow_hover_scale',
            [
                'label' => esc_html__( 'Scale', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-hover-scale: {{SIZE}}',
                ],
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.01,
                    ),
                ),
            ]
        );
        $this->add_responsive_control(
            'arrow_hover_opacity',
            [
                'label' => esc_html__( 'Opacity', 'lastudio-kit' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}}' => '--e-arrow-hover-opacity: {{SIZE}}',
                ],
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.01,
                    ),
                ),
            ]
        );
        $this->_add_group_control(
            Group_Control_Border::get_type(),
            array(
                'name' => 'arrow_hover_border',
                'label' => esc_html__('Border', 'lastudio-kit'),
                'placeholder' => '1px',
                'default' => '1px',
                'selector' => '{{WRAPPER}} .lakit-embla__arrow:hover',
            )
        );
        $this->add_responsive_control(
            'arrow_hover_radius',
            [
                'label' => esc_html__( 'Border Radius', 'lastudio-kit' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .lakit-embla__arrow:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
                ],
            ]
        );
        $this->_add_group_control(
            Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'arrow_hover_shadow',
                'selector' => '{{WRAPPER}} .lakit-embla__arrow:hover'
            )
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }

    public function render()
    {
        if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
            return;
        }
        $_product_id = $this->get_settings_for_display('product_id');
        $product_id = !empty($_product_id) ? $_product_id : get_queried_object_id();
        global $product;
        $product = wc_get_product($product_id);
        if (empty($product)) {
            return;
        }

        $this->add_render_attribute('_wrapper', 'data-product_id', $product->get_id());

        if (isset($_GET['render_mode']) && $_GET['render_mode'] == 'screenshot') {
            echo '<div class="lakit-product-images placeholder"><img src="' . esc_url(lastudio_kit()->plugin_url('assets/images/placeholder.png')) . '" width="400" height="300" alt="Placeholder"/> </div>';
            return;
        }

        $layout_type = $this->get_settings_for_display('layout_type');
        $classes = ['lakit-product-images'];
        $classes[] = 'layout-type-' . esc_attr($layout_type);

        $product_gallery_ids = $product->get_gallery_image_ids();
        $post_thumbnail_id = $product->get_image_id();
        if(empty($product_gallery_ids) || empty($post_thumbnail_id)) {
            $classes[] = 'no-gallery';
        }

        $bk_vl = false;
        $force_layout_bottom = $this->get_settings_for_display('force_layout_bottom');
        if(!empty($force_layout_bottom) && in_array($layout_type, ['2', '3'], true)){
            $active_breakpoints = lastudio_kit_helper()->get_active_breakpoints();
            if(isset($active_breakpoints[$force_layout_bottom])){
                $bk_vl = $active_breakpoints[$force_layout_bottom];
            }
        }
        ?>
            <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
                <div class="woocommerce-product-gallery lakit-woo-gallery-embla">
                    <div class="lakit-embla lakit-embla-main">
                        <?php
                        if ('yes' === $this->get_settings_for_display('sale_flash')) {
                            wc_get_template('loop/sale-flash.php');
                        }
                        ?>
                        <div class="lakit-embla__viewport">
                            <div class="lakit-embla__container">
                                <?php
                                if ( $post_thumbnail_id ) {
                                    $html = wc_get_gallery_image_html( $post_thumbnail_id, true );
                                }
                                else {
                                    $wrapper_classname = $product->is_type( 'variable' ) && ! empty( $product->get_visible_children() ) && '' !== $product->get_price() ?
                                            'woocommerce-product-gallery__image woocommerce-product-gallery__image--placeholder' :
                                            'woocommerce-product-gallery__image--placeholder';
                                    $html              = sprintf( '<div class="%s">', esc_attr( $wrapper_classname ) );
                                    $html             .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'lastudio-kit' ) );
                                    $html             .= '</div>';
                                }
                                echo str_replace( 'class="woocommerce-product-gallery__image"', 'class="woocommerce-product-gallery__image lakit-embla__slide"', apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id ) ); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
                                if(!empty($product_gallery_ids) && !empty($post_thumbnail_id)){
                                    foreach ( $product_gallery_ids as $key => $attachment_id ) {
                                        echo str_replace('class="woocommerce-product-gallery__image"', 'class="woocommerce-product-gallery__image lakit-embla__slide"', apply_filters( 'woocommerce_single_product_image_thumbnail_html', wc_get_gallery_image_html( $attachment_id, false, $key ), $attachment_id )); // PHPCS:Ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                            if(!in_array($layout_type, ['5', '6'], true)){
                                echo '<button type="button" class="lakit-embla__arrow lakit-embla__arrow-prev"><i class="lastudioicon lastudioicon-arrow-left"></i></button><button type="button" class="lakit-embla__arrow lakit-embla__arrow-next"><i class="lastudioicon lastudioicon-arrow-right"></i></button>';
                            }
                        ?>
                    </div>
                    <?php
                    if(!in_array($layout_type, ['5', '6'], true)){
                        echo '<div class="lakit-embla--dots"></div>';
                    }
                    ?>
                </div>
                <?php
                if(!empty($bk_vl)){
                    $el_css_base = 'body .elementor-element-' . $this->get_id() . ' .lakit-product-images';
                    ?>
<style>
@media(max-width: <?php echo esc_html($bk_vl) ?>px){
    <?php echo esc_html($el_css_base); ?> .lakit-woo-gallery-embla{
        flex-direction: column;
        gap: var(--singleproduct-image-spacing)
    }
    <?php echo esc_html($el_css_base); ?> .lakit-embla--dots{
        order: 2;
        flex: 0 0 100%;
        flex-flow: row nowrap;
        max-height: none;
        width: 100%;
    }
    <?php echo esc_html($el_css_base); ?>.e--dots-center .lakit-embla--dots{
        justify-content: center;
    }
    <?php echo esc_html($el_css_base); ?> .lakit-embla-main{
        flex: 0 0 100%;
    }
    <?php echo esc_html($el_css_base); ?> .lakit-embla--dots .embla__dot{
        flex: 0 0 var(--singleproduct-thumbs-width);
        padding:0 calc(var(--singleproduct-thumb-spacing) / 2);
    }
}
</style>
                    <?php
                }
                ?>
            </div>
        <?php
    }

}