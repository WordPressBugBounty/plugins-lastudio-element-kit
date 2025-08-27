<?php
namespace LaStudioKitExtensions\GiveWP\DynamicTags;

use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Tag as DynamicTagsTag;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Module as DynamicTagsModule;
use LaStudioKitExtensions\Elementor\Controls\Control_Query as QueryControlModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Tag extends DynamicTagsTag {

	public function get_name() {
		return 'givewp_form';
	}

	public function get_title() {
		return esc_html__( 'Give Donation Form', 'lastudio-kit' );
	}

	public function get_group() {
		return DynamicTagsModule::ACTION_GROUP;
	}

	public function get_categories() {
		return [ DynamicTagsModule::URL_CATEGORY ];
	}

	public static function on_import_replace_dynamic_content( $config, $map_old_new_post_ids ) {
		if ( isset( $config['settings']['givewp_form'] ) ) {
			$config['settings']['givewp_form'] = $map_old_new_post_ids[ $config['settings']['givewp_form'] ];
		}

		return $config;
	}

	public function register_controls() {
		$this->add_control(
			'givewp_form',
			[
				'label' => esc_html__( 'Donation Form', 'lastudio-kit' ),
				'type' => QueryControlModule::QUERY_CONTROL_ID,
				'autocomplete' => [
					'object' => 'post',
					'query' => [
						'posts_per_page' => 20,
						'post_status' => [ 'publish' ],
						'post_type' => [ 'give_forms' ]
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
		if ( empty($settings['givewp_form']) ) {
			return '';
		}

		$form_id = absint( $settings['givewp_form'] );

		if ('give_forms' !== get_post_type($form_id)) {
			return '';
		}

		$link_action_url = lastudio_kit()->elementor()->frontend->create_action_hash( 'givewp_form:open', [
			'id'    => $form_id
		] );
		// PHPCS - `create_action_hash` is safe.
		echo $link_action_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}