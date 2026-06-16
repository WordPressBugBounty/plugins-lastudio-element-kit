<?php

namespace LaStudioKitExtensions\GiveWp\Widgets;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use Elementor\Controls_Manager;
use Elementor\LaStudioKit_Button;

class GiveFormDonateButton extends LaStudioKit_Button {

    protected function is_dynamic_content(): bool
    {
        return true;
    }

    public function get_style_depends()
    {
        return ['lakit-givewp'];
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