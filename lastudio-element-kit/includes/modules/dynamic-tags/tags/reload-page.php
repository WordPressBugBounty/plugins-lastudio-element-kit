<?php
namespace LaStudioKitThemeBuilder\Modules\DynamicTags\Tags;

use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Pro_Tag;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Module;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Reload_Page extends Pro_Tag {

	public function get_name() {
		return 'reload-page';
	}

	public function get_title() {
		return esc_html__( 'Reload Page', 'lastudio-kit' );
	}

	public function get_group() {
		return Module::ACTION_GROUP;
	}

	public function get_categories() {
		return [ Module::URL_CATEGORY ];
	}

	protected function register_advanced_section() {}

	public function register_controls() {}

	public function render() {
		// PHPCS - the method Plugin::elementor()->frontend->create_action_hash is safe.
		echo Plugin::elementor()->frontend->create_action_hash( 'reload-page' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
