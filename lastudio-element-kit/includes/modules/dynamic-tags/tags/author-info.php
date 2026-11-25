<?php
namespace LaStudioKitThemeBuilder\Modules\DynamicTags\Tags;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Author_Tag;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Author_Info extends Author_Tag {

	public function get_name() {
		return 'author-info';
	}

	public function get_title() {
		return esc_html__( 'Author Info', 'lastudio-kit' );
	}

	public function get_editor_config() {
		$config = parent::get_editor_config();

		$config['display_conditions'] = [
			'author_info_bio' => [
				'label' => esc_html__( 'Author Bio', 'lastudio-kit' ),
				'settings' => [ 'key' => 'description' ],
				'group' => 'author',
			],
			'author_info_email' => [
				'label' => esc_html__( 'Author Email', 'lastudio-kit' ),
				'settings' => [ 'key' => 'email' ],
				'group' => 'author',
			],
			'author_info_website' => [
				'label' => esc_html__( 'Author Website', 'lastudio-kit' ),
				'settings' => [ 'key' => 'url' ],
				'group' => 'author',
			],
		];

		return $config;
	}

	protected function register_controls() {
		$this->add_control(
			'key',
			[
				'label' => esc_html__( 'Field', 'lastudio-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'description',
				'options' => $this->get_key_options(),
			]
		);
	}

	private function get_key_options() {
		$options = [];
		$options['description'] = esc_html__( 'Bio', 'lastudio-kit' );

		if ( current_user_can( 'manage_options' ) ) {
			$options['email'] = esc_html__( 'Email', 'lastudio-kit' );
		}

		$options['url'] = esc_html__( 'Website', 'lastudio-kit' );

		return $options;
	}
}
