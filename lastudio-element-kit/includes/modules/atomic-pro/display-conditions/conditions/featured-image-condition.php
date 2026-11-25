<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparator_Provider;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparators_Checker;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Featured_Image_Condition extends Condition_Base {

	public function get_name() {
		return 'featured_image';
	}

	public function get_label() {
		return esc_html__( 'Featured Image', 'lastudio-kit' );
	}

	public function get_group() {
		return 'post';
	}

	public function check( $args ) : bool {
		return Comparators_Checker::check_equality(
			$args['comparator'],
			'set' === $args['status'],
			$this->wordpress_adapter->has_post_thumbnail()
		);
	}

	public function get_options() {
		$comparators = Comparator_Provider::get_comparators(
			[
				Comparator_Provider::COMPARATOR_IS,
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
			'status',
			[
				'type' => Controls_Manager::SELECT,
				'options' => [
					'set' => esc_html__( 'Set', 'lastudio-kit' ),
					'not_set' => esc_html__( 'Not Set', 'lastudio-kit' ),
				],
				'default' => 'set',
			]
		);
	}
}
