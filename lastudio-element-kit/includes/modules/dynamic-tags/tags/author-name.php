<?php
namespace LaStudioKitThemeBuilder\Modules\DynamicTags\Tags;

use LaStudioKitThemeBuilder\Modules\DynamicTags\Classes\Pro_Utils as Utils;

use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Pro_Tag;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Author_Name extends Pro_Tag {

	public function get_name() {
		return 'author-name';
	}

	public function get_title() {
		return esc_html__( 'Author Name', 'lastudio-kit' );
	}

	public function get_group() {
		return Module::AUTHOR_GROUP;
	}

	public function get_categories() {
		return [ Module::TEXT_CATEGORY ];
	}

	public function render() {
		// `$authordata` is only set inside The Loop; make sure it matches the current post.
		Utils::set_global_authordata();

		echo wp_kses_post( get_the_author() );
	}
}
