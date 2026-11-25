<?php
namespace LaStudioKitThemeBuilder\Modules\DynamicTags\Tags;

use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Author_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Author_Meta extends Author_Tag {

	public function get_name() {
		return 'author-meta';
	}

	public function get_title() {
		return esc_html__( 'Author Meta', 'lastudio-kit' );
	}

	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label' => esc_html__( 'Meta Key', 'lastudio-kit' ),
				'ai' => [
					'active' => false,
				],
			]
		);
	}
}
