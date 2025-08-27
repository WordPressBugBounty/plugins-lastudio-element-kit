<?php
namespace LaStudioKitThemeBuilder\Modules\Givewp;

use LaStudioKitThemeBuilder\Modules\ThemeBuilder\Classes\Conditions_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Module extends \Elementor\Core\Base\Module {

	public static function is_active() {
		return class_exists( 'Give', false );
	}

	public function get_name() {
		return 'lakie-givewp';
	}
	public function __construct() {
		parent::__construct();
		add_action( 'elementor/theme/register_conditions', [ $this, 'register_conditions' ] );
	}

	/**
	 * @param Conditions_Manager $conditions_manager
	 */
	public function register_conditions( $conditions_manager ) {
		$class_name = lastudio_kit()->has_elementor_pro() ? 'LaStudioKitThemeBuilder\Modules\Givewp\Conditions\Give_Campaign_E' : 'LaStudioKitThemeBuilder\Modules\Givewp\Conditions\Give_Campaign';
		$conditions_manager->get_condition('singular')->register_sub_condition(new $class_name());
	}

}