<?php

namespace LaStudioKitExtensions\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Element_Visibility {

    public function __construct() {
        add_action('elementor/element/common/_section_style/after_section_end', [ $this, 'init_module' ]);
        add_action('elementor/element/section/section_advanced/after_section_end', [ $this, 'init_module' ]);
		add_action('elementor/element/container/section_layout/after_section_end', [ $this, 'init_module' ]);

        add_filter( 'elementor/widget/render_content', [ $this, 'content_change' ], 999, 2 );

        add_filter( 'elementor/frontend/section/should_render', [ $this, 'item_should_render' ], 10, 2 );
        add_filter( 'elementor/frontend/widget/should_render', [ $this, 'item_should_render' ], 10, 2 );
        add_filter( 'elementor/frontend/repeater/should_render', [ $this, 'item_should_render' ], 10, 2 );
        add_filter( 'elementor/frontend/container/should_render', [ $this, 'item_should_render' ], 10, 2 );

        add_filter('elementor/element/is_dynamic_content', [ $this, 'filter_check_e_cache' ], 10, 3);
    }

    private function get_roles() {

        static $cache_roles = null;

        if($cache_roles){
            return $cache_roles;
        }

        global $wp_roles;

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new \WP_Roles();
        }
        $all_roles      = $wp_roles->roles;
        $editable_roles = apply_filters( 'editable_roles', $all_roles );

        $data = [
            'lakit-vlogic-guest'    => esc_html__('Guests', 'lastudio-kit'),
            'lakit-vlogic-user'     => esc_html__('Logged in users', 'lastudio-kit'),
            'wc_cart_empty'         => esc_html__('WC Cart Empty', 'lastudio-kit'),
        ];

        foreach ( $editable_roles as $k => $role ) {
            $data[ $k ] = $role['name'];
        }


        $cache_roles = $data;

        return $data;
    }

    public function init_module($element) {
        $element->start_controls_section('section_lakit_vlogic', [
            'label' => esc_html__('LA-Kit Visibility Logic', 'lastudio-kit'),
            'tab' => \Elementor\Controls_Manager::TAB_ADVANCED,
        ]);
        $element->add_control('lakit_vlogic_enabled', [
            'label' => esc_html__('Enable Conditions', 'lastudio-kit'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'render_type' => 'none',
            'default'      => '',
            'label_on'     => esc_html__( 'Yes', 'lastudio-kit' ),
            'label_off'    => esc_html__( 'No', 'lastudio-kit' ),
            'return_value' => 'yes',
        ]);
        $element->add_control('lakit_vlogic_role_visible', [
            'label' => esc_html__('Visible for:', 'lastudio-kit'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'render_type' => 'none',
            'options'     => $this->get_roles(),
            'multiple'    => true,
            'label_block' => true,
            'conditions' => [
	            'relation' => 'and',
	            'terms' => [
		            [
			            'name' => 'lakit_vlogic_enabled',
			            'operator' => '==',
			            'value' => 'yes'
		            ],
		            [
			            'name' => 'lakit_vlogic_role_hidden',
			            'operator' => '==',
			            'value' => ''
		            ]
	            ]
            ],
        ]);
        $element->add_control('lakit_vlogic_role_hidden', [
            'label' => esc_html__('Hidden for:', 'lastudio-kit'),
            'type' => \Elementor\Controls_Manager::SELECT2,
            'render_type' => 'none',
            'options'     => $this->get_roles(),
            'multiple'    => true,
            'label_block' => true,
            'conditions' => [
	            'relation' => 'and',
	            'terms' => [
		            [
			            'name' => 'lakit_vlogic_enabled',
			            'operator' => '==',
			            'value' => 'yes'
		            ],
		            [
			            'name' => 'lakit_vlogic_role_visible',
			            'operator' => '==',
			            'value' => ''
		            ]
	            ]
            ],
        ]);
        $element->end_controls_section();
    }

    public function item_should_render(  $should_render, $section ){

        if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
            return $should_render;
        }

        // Get the settings
        $settings = $section->get_settings();

        if ( ! $this->should_render( $settings ) ) {
            return false;
        }

        return $should_render;
    }

    /**
     * @param string $content
     * @param $widget \Elementor\Widget_Base
     *
     * @return string
     */
    public function content_change( $content, $widget ) {

        if( \Elementor\Plugin::$instance->editor->is_edit_mode() ){
            return $content;
        }

        // Get the settings
        $settings = $widget->get_data('settings');

        if ( ! $this->should_render( $settings ) ) {
            return '';
        }

        return $content;
    }

    /**
     * Check if conditions are matched
     *
     * @param array $settings
     *
     * @return boolean
     */
    private function should_render( $settings ) {
        $user_state = is_user_logged_in();

        if ( !empty($settings['lakit_vlogic_enabled']) && $settings['lakit_vlogic_enabled'] === 'yes' ) {

            $isWcLostPassPage = function_exists('is_lost_password_page') && is_lost_password_page();

            $visible_roles = $settings['lakit_vlogic_role_visible'] ?? [];
            $hidden_roles = $settings['lakit_vlogic_role_hidden'] ?? [];

            //visible for
            if ( ! empty( $visible_roles ) ) {

                if( in_array('wc_cart_empty', $visible_roles, true ) ) {
                    if( function_exists('WC') && !is_null(WC()->cart)) {
                        return WC()->cart->is_empty();
                    }
                }

                if ( in_array( 'lakit-vlogic-guest', $visible_roles, true) ) {
	                if($isWcLostPassPage || $user_state){
		                return false;
	                }
                }
                elseif ( in_array( 'lakit-vlogic-user', $visible_roles, true ) ) {
	                if($isWcLostPassPage){
		                return true;
	                }
                    if ( $user_state === false ) {
                        return false;
                    }
                }
                else {
                    if ( $user_state === false ) {
                        return false;
                    }
                    $user = wp_get_current_user();

                    $has_role = false;
                    foreach ( $visible_roles as $setting ) {
                        if ( in_array( $setting, (array) $user->roles ) ) {
                            $has_role = true;
                        }
                    }
                    if ( $has_role === false ) {
                        return false;
                    }
                }

            } //hidden for
            elseif ( ! empty( $hidden_roles ) ) {

                if( in_array('wc_cart_empty', $hidden_roles, true ) ) {
                    if( function_exists('WC') && !is_null(WC()->cart) ) {
                        return !WC()->cart->is_empty();
                    }
                }
                if(in_array( 'lakit-vlogic-user', $hidden_roles, true )){
                    if($user_state || $isWcLostPassPage){
                       return false;
                    }
                }
                if(!$user_state && in_array( 'lakit-vlogic-guest', $hidden_roles, true )){
                    return false;
                }
                if ( $user_state === false && in_array( 'lakit-vlogic-guest', $hidden_roles, true ) ) {
                    if($isWcLostPassPage){
                        return true;
                    }
                    return false;
                }

                if ( $user_state === false ) {
                    return true;
                }
                $user = wp_get_current_user();

                foreach ( $hidden_roles as $setting ) {
                    if ( in_array( $setting, (array) $user->roles, true) ) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function filter_check_e_cache($is_dynamic_content, $raw_data, $instance){
        if( !empty($raw_data['settings']['lakit_vlogic_enabled']) && filter_var($raw_data['settings']['lakit_vlogic_enabled'], FILTER_VALIDATE_BOOLEAN) ){
            $is_dynamic_content = true;
        }
        return $is_dynamic_content;
    }

}
