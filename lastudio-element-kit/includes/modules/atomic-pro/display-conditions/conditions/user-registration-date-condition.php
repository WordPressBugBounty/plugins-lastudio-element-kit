<?php

namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparator_Provider;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparators_Checker;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Date_Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class User_Registration_Date_Condition extends Date_Condition_Base {
	const CONDITION_KEY = 'date';
	const GROUP_KEY = 'user';

	public function get_name() {
		return 'user_registration_date';
	}

	public function get_label() {
		return esc_html__( 'Registration Date', 'lastudio-kit' );
	}

	public function __construct() {
		parent::__construct( self::CONDITION_KEY, self::GROUP_KEY );
	}

	public function check( $args ) : bool {
		return parent::check_date( $args, $this->get_user_registration_date() );
	}

	private function get_user_registration_date() {
		$registration_date = date_create( wp_get_current_user()->user_registered )->format( self::DATE_FORMAT );
		$registration_date = date_create_from_format( self::DATE_FORMAT, $registration_date );

		return $registration_date;
	}
}
