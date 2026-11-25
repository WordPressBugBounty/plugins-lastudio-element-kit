<?php
namespace LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Kept for compatibility with the Elementor Pro structure.
 * LA-Studio Kit tags have no license requirement, so no license meta is added to the editor config
 * (otherwise the editor may show the tags as locked when Elementor Pro is not installed).
 */
trait License_Meta_Trait {

	/**
	 * Get the license feature name required for this tag.
	 *
	 * @return string
	 */
	abstract protected function get_required_license_feature();
}
