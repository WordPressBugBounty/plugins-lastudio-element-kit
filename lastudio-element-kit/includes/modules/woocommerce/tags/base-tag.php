<?php
namespace LaStudioKitThemeBuilder\Modules\Woocommerce\Tags;

use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Tag;
use LaStudioKitThemeBuilder\Modules\Woocommerce\Module;
use LaStudioKitThemeBuilder\Modules\Woocommerce\Tags\Traits\Tag_Product_Id;
use LaStudioKitThemeBuilder\Modules\Woocommerce\Traits\Product_Id_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Base_Tag extends Tag {

	use Tag_Product_Id;
	use Product_Id_Trait;

	public function get_group() {
		return Module::WOOCOMMERCE_GROUP;
	}

	public function get_categories() {
		return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ];
	}

	public function get_editor_config() {
		$conf = parent::get_editor_config();
		$conf['force_convert_to_atomic'] = true;
		return $conf;
	}
}
