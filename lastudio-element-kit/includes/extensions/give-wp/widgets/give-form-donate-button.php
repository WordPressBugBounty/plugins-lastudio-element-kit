<?php

namespace LaStudioKitExtensions\GiveWp\Widgets;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Elementor\Controls_Manager;
use Elementor\LaStudioKit_Button;

class GiveFormDonateButton extends LaStudioKit_Button {

	public $givewp_css_handle = 'lakit-givewp';

	public $css_file_name = 'givewp.min.css';

	protected function enqueue_addon_resources(){
		if(!lastudio_kit()->is_optimized_css_mode()) {
			$this->add_style_depends( 'lastudio-kit-base' );
			wp_register_style( $this->givewp_css_handle, lastudio_kit()->plugin_url( 'assets/css/addons/' . $this->css_file_name ), null, lastudio_kit()->get_version() );
			$this->add_style_depends( $this->givewp_css_handle );
		}
	}

	public function get_widget_css_config($widget_name){

        $css_file_name = $this->css_file_name;

		$file_url = lastudio_kit()->plugin_url(  'assets/css/addons/' . $css_file_name );
		$file_path = lastudio_kit()->plugin_path( 'assets/css/addons/' . $css_file_name );

		return [
			'key'       => $this->givewp_css_handle,
			'version'   => lastudio_kit()->get_version(true),
			'file_path' => $file_path,
			'data' => [
				'file_url' => $file_url
			]
		];
	}

	public function get_name() {
		return 'lakit-give-donate-button';
	}

	public function get_widget_title() {
		return __('GiveWP Donate Button', 'lastudio-kit');
	}

	public function get_keywords() {
		return [ 'give', 'donation', 'grid', 'form', 'goal' ];
	}

	protected function control_link() {

	}

    protected function register_controls()
    {
        $this->_start_controls_section(
            'section_meta',
            [
                'label' => __( 'Settings', 'lastudio-kit' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'form_id',
            [
                'label' =>  esc_html__( 'Form ID', 'lastudio-kit' ),
                'type' => 'lastudiokit-query',
                'options' => [],
                'label_block' => true,
                'autocomplete' => [
                    'object' => 'post',
                    'query' => [
                        'post_type' => [ 'give_forms' ],
                    ],
                ],
            ]
        );

        $this->_end_controls_section();

		parent::register_controls();

    }

	protected function render() {
		$form_id = $this->get_settings_for_display('form_id');
		if(empty($form_id)){
			$form_id = get_the_ID();
		}

        $isV3 = false;

        if(class_exists('\Give\Helpers\Form\Utils') && \Give\Helpers\Form\Utils::isV3Form($form_id)){
            $isV3 = true;
        }

		$this->add_render_attribute( 'button', 'class', 'lakit-posts__btn-donate' );
		$this->add_render_attribute( 'button', 'data-id', $form_id );
		$this->add_render_attribute( 'button', 'data-isv3', $isV3 ? 'true' : 'false' );
		parent::render();
	}
}