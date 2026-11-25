<?php
namespace LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Port of the `LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Utils` helpers used by the Loop Builder.
 */
class Utils {

	public static function get_public_post_types( $args = [] ) {
		$post_type_args = [
			'show_in_nav_menus' => true,
		];

		if ( ! empty( $args['post_type'] ) ) {
			$post_type_args['name'] = $args['post_type'];
			unset( $args['post_type'] );
		}

		$post_type_args = wp_parse_args( $post_type_args, $args );

		$_post_types = get_post_types( $post_type_args, 'objects' );

		$post_types = [];

		foreach ( $_post_types as $post_type => $object ) {
			$post_types[ $post_type ] = $object->label;
		}

		return apply_filters( 'lastudio-kit/loop-builder/get_public_post_types', $post_types );
	}

	public static function get_current_post_id() {
		if ( isset( Plugin::elementor()->documents ) && Plugin::elementor()->documents->get_current() ) {
			return Plugin::elementor()->documents->get_current()->get_main_id();
		}

		return get_the_ID();
	}

	public static function trim_words( $text, $length ) {
		if ( ! $length ) {
			return $text;
		}

		$words = preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY );

		if ( count( $words ) > $length ) {
			$words = array_slice( $words, 0, $length );
		}

		return implode( ' ', $words );
	}

	public static function _unstable_get_super_global_value( $super_global, $key ) {
		if ( ! isset( $super_global[ $key ] ) ) {
			return null;
		}

		return wp_kses_post_deep( wp_unslash( $super_global[ $key ] ) );
	}

	public static function format_control_condition( $name, $operator, $value ) {
		return compact( 'name', 'operator', 'value' );
	}
}
