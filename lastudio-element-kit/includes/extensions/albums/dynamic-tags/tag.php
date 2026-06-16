<?php
namespace LaStudioKitExtensions\Albums\DynamicTags;

use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Tag as DynamicTagsTag;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Module as DynamicTagsModule;
use LaStudioKitExtensions\Elementor\Controls\Control_Query as QueryControlModule;
use Elementor\Core\Base\Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Tag extends DynamicTagsTag {

	public function get_name() {
		return 'lakit_album_player';
	}

	public function get_title() {
		return esc_html__( 'Album Popup', 'lastudio-kit' );
	}

	public function get_group() {
		return DynamicTagsModule::ACTION_GROUP;
	}

	public function get_categories() {
		return [ DynamicTagsModule::URL_CATEGORY ];
	}

	public static function on_import_replace_dynamic_content( $config, $map_old_new_post_ids ) {
		if ( isset( $config['settings']['lakit_popup_id'] ) ) {
			$config['settings']['lakit_popup_id'] = $map_old_new_post_ids[ $config['settings']['lakit_popup_id'] ];
		}
		if ( isset( $config['settings']['lakit_album_id'] ) ) {
			$config['settings']['lakit_album_id'] = $map_old_new_post_ids[ $config['settings']['lakit_album_id'] ];
		}

		return $config;
	}

	public function register_controls() {
		$this->add_control(
			'lakit_popup_id',
			[
				'label' => esc_html__( 'Popup Template', 'lastudio-kit' ),
				'type' => QueryControlModule::QUERY_CONTROL_ID,
				'autocomplete' => [
					'object' => QueryControlModule::QUERY_OBJECT_LIBRARY_TEMPLATE,
					'query' => [
						'posts_per_page' => 20,
						'post_status' => [ 'publish', 'private' ],
						'meta_query' => [
							[
								'key' => Document::TYPE_META_KEY,
								'value' => 'popup',
							],
						],
					],
				],
				'label_block' => true
			]
		);
		$this->add_control(
			'lakit_album_id',
			[
				'label' => esc_html__( 'Album ID', 'lastudio-kit' ),
				'type' => QueryControlModule::QUERY_CONTROL_ID,
				'autocomplete' => [
					'object' => 'post',
					'query' => [
						'posts_per_page' => 20,
						'post_status' => [ 'publish' ],
						'post_type' => [ 'la_album' ]
					],
				],
				'label_block' => true
			]
		);
	}

	public function render() {
		$settings = $this->get_active_settings();
		$this->print_open_popup_link( $settings );
	}

	// Keep Empty to avoid default advanced section
	protected function register_advanced_section() {}

	private function print_open_popup_link( array $settings ) {
		if ( empty($settings['lakit_popup_id']) || empty($settings['lakit_album_id']) ) {
			return '';
		}

		$lakit_popup_id = absint( $settings['lakit_popup_id'] );
		$lakit_album_id = absint( $settings['lakit_album_id'] );

		if( 'elementor_library' !== get_post_type($lakit_popup_id) || 'la_album' !== get_post_type($lakit_album_id) ){
			return '';
		}

		$link_action_url = lastudio_kit()->elementor()->frontend->create_action_hash( 'lakit_album_player:open', [
			'popup_id'    => $lakit_popup_id,
			'album_id'    => $lakit_album_id,
		] );

		wp_enqueue_script('lastudio-kit-popup');
		wp_enqueue_style('lastudio-kit-popup');
		wp_enqueue_script('lastudio-kit-player');
		wp_enqueue_style('lastudio-kit-player');

		// PHPCS - `create_action_hash` is safe.
		echo $link_action_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}