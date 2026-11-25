<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparator_Provider;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparators_Checker;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Login_Status_Condition extends Condition_Base {

	public function get_name() {
		return 'login_status';
	}

	public function get_label() {
		return esc_html__( 'Login Status', 'lastudio-kit' );
	}

	public function get_group() {
		return 'user';
	}

	public function check( $args ) : bool {
		return Comparators_Checker::check_equality( $args['comparator'], 'logged_in' === $args['status'], is_user_logged_in() );
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
					'logged_in' => esc_html__( 'Logged In', 'lastudio-kit' ),
					'logged_out' => esc_html__( 'Logged Out', 'lastudio-kit' ),
				],
				'default' => 'logged_in',
			]
		);
	}
}
