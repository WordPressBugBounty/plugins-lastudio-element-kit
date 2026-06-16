<?php

namespace LaStudioKitExtensions\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Wrapper_Link {
    public function __construct() {
        add_action('elementor/element/after_section_end', [ $this, 'init_module'], 10, 2);
        add_action('elementor/frontend/after_register_styles', [ $this, 'register_enqueue_scripts']);
        add_action('elementor/frontend/before_render', [ $this, 'enqueue_in_widget']);
    }

    public function register_enqueue_scripts(){

    }

    public function enqueue_in_widget( $element ) {
        if( in_array($element->get_name(), ['column', 'section', 'container']) ){
            $lakit_element_link = $element->get_settings_for_display('lakit_element_link');
            if( !empty($lakit_element_link['url']) ){
                $allowed_protocols = array_merge( wp_allowed_protocols(), [ 'skype', 'viber' ] );
                $lakit_element_link['url'] = esc_url($lakit_element_link['url'], $allowed_protocols);
                if(!empty($lakit_element_link['url']) && $lakit_element_link['url'] !== '#'){
                    $element->add_render_attribute('_wrapper', [
                        'data-lakit-element-link' => wp_json_encode($lakit_element_link),
                        'style' => 'cursor: pointer'
                    ]);
                    $element->add_script_depends('lastudio-kit-wrapper-links');
                }
            }
        }
    }

	/**
	 * @param \Elementor\Widget_Base $controls_stack
	 * @param $section_id
	 *
	 * @return void
	 */
    public function init_module( $controls_stack, $section_id ){
        $stack_name = $controls_stack->get_name();

        if( (($stack_name === 'column' || $stack_name === 'section') && $section_id === 'section_advanced')
            || ($stack_name === 'container' && in_array($section_id, ['section_layout_additional_options', 'section_layout_items']) )
        ){

            $tabs = \Elementor\Controls_Manager::TAB_CONTENT;
            if($stack_name === 'column' || $stack_name === 'section' || $stack_name === 'container'){
                $tabs = \Elementor\Controls_Manager::TAB_LAYOUT;
            }
            $controls_stack->start_controls_section(
                '_section_lakit_wrapper_link',
                [
                    'label' => __( 'LA-Kit Wrapper Link', 'lastudio-kit' ),
                    'tab'   => $tabs,
                ]
            );
            $controls_stack->add_control(
                'lakit_element_link',
                [
                    'label'       => __( 'Link', 'lastudio-kit' ),
                    'type'        => \Elementor\Controls_Manager::URL,
                    'dynamic'     => [
                        'active' => true,
                    ],
                    'custom_attributes_description' => __( 'Custom Attributes option will not work at this time', 'lastudio-kit' ),
                ]
            );
            $controls_stack->end_controls_section();
        }
    }
}