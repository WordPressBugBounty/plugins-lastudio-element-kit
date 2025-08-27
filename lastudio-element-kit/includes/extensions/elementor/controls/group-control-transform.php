<?php

namespace LaStudioKitExtensions\Elementor\Controls;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Base;

class Group_Control_Transform extends Group_Control_Base {

    protected static $fields;

    public static function get_type() {
        return 'lakit-transform';
    }

    protected function init_fields() {

        $fields = [];

        $fields['_offset_x'] = [
	        'label' => esc_html__( 'Offset X', 'elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'responsive' => true,
	        'size_units' => [ 'px', '%'],
	        'range' => [
		        '%' => [
			        'min' => -100,
			        'max' => 100,
		        ],
		        'px' => [
			        'min' => -1000,
			        'max' => 1000,
		        ],
	        ],
            'selectors' => [
                '{{SELECTOR}}' => '{{css_var_prefix}}-offset-x: {{SIZE}}{{UNIT}}',
            ],
            'render_type' => 'ui',
        ];

		$fields['_offset_y'] = [
	        'label' => esc_html__( 'Offset Y', 'elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'responsive' => true,
	        'size_units' => [ 'px', '%'],
	        'range' => [
		        '%' => [
			        'min' => -100,
			        'max' => 100,
		        ],
		        'px' => [
			        'min' => -1000,
			        'max' => 1000,
		        ],
	        ],
            'selectors' => [
                '{{SELECTOR}}' => '{{css_var_prefix}}-offset-y: {{SIZE}}{{UNIT}}',
            ],
            'render_type' => 'ui',
        ];

        $fields['_rotate'] = [
	        'label' => esc_html__( 'Rotate', 'elementor' ),
            'type'       => Controls_Manager::SLIDER,
            'responsive' => true,
	        'range' => [
		        'px' => [
			        'min' => -360,
			        'max' => 360,
		        ],
	        ],
            'selectors' => [
                '{{SELECTOR}}' => '{{css_var_prefix}}-rotate: {{SIZE}}deg',
            ],
            'render_type' => 'ui',
        ];


        return $fields;
    }

    protected function get_default_options() {
        return [
            'popover' => [
                'starter_title' => esc_html__( 'Transform', 'elementor' ),
                'starter_name' => 'transform_type',
                'starter_value' => 'yes',
                'settings' => [
                    'render_type' => 'ui',
                ],
            ],
        ];
    }
	protected function prepare_fields( $fields ) {
		$args = $this->get_args();
		$css_var_prefix = isset( $args['css_var_prefix'] ) ? esc_attr($args['css_var_prefix']) : '--lakit-e-c';
		foreach ( $fields as &$field ) {
			if(isset($field['selectors'])){
				$new_val = [];
				foreach ($field['selectors'] as $key => $value) {
					$new_val[$key] = str_replace('{{css_var_prefix}}', $css_var_prefix, $value);
				}
				$field['selectors'] = $new_val;
			}
		}
		return parent::prepare_fields( $fields );
	}
}
