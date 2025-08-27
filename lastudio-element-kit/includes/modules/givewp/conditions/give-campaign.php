<?php
namespace LaStudioKitThemeBuilder\Modules\Givewp\Conditions;

use LaStudioKitThemeBuilder\Modules\ThemeBuilder\Conditions\Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class Give_Campaign extends Condition_Base {

	public function get_name() {
		return 'give_campaign_page';
	}

	public function get_label() {
		return __( 'GiveWP Campaign', 'lastudio-kit' );
	}

	public function check( $args ) {
		if ( ! is_singular( 'page' ) ) {
			return false;
		}

		$post_id     = get_the_ID();
		$campaign_id = get_post_meta( $post_id, 'give_campaign_id', true );

		return intval( $campaign_id ) !== 0;
	}
}
