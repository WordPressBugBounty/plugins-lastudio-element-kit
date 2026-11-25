<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparator_Provider;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparators_Checker;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class From_URL_Condition extends Condition_Base {

	public function get_label() {
		return esc_html__( 'From URL', 'lastudio-kit' );
	}

	public function get_options() {
		$comparators = Comparator_Provider::get_comparators(
			[
				Comparator_Provider::COMPARATOR_IS,
				Comparator_Provider::COMPARATOR_IS_NOT,
				Comparator_Provider::COMPARATOR_CONTAINS,
				Comparator_Provider::COMPARATOR_NOT_CONTAIN,
			]
		);

		$this->add_control(
			'comparator',
			[
				'type' => Controls_Manager::SELECT,
				'options' => $comparators,
				'default' => Comparator_Provider::COMPARATOR_IS,
			]
		);

		$this->add_control(
			'from_url',
			[
				'label' => esc_html__( 'URL', 'lastudio-kit' ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'URL', 'lastudio-kit' ),
				'required' => true,
			]
		);
	}

	public function get_name() {
		return 'from_url';
	}

	public function wp_get_referer() {
		return wp_get_raw_referer();
	}

	public function check( $args ) : bool {
		$referrer = $this->wp_get_referer();

		return Comparators_Checker::check_string_contains( $args['comparator'], $args['from_url'], $referrer );
	}
}
