<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicLoop\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal replacement for the `ElementorPro\Core\Utils` helpers used by the Loop module.
 */
class Pro_Utils {

	public static function is_script_debug() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;
	}

	public static function is_elementor_tests() {
		return defined( 'ELEMENTOR_TESTS' ) && ELEMENTOR_TESTS;
	}

	public static function get_super_global_value( $super_global, $key ) {
		if ( ! isset( $super_global[ $key ] ) ) {
			return null;
		}

		if ( $_FILES === $super_global ) {
			return isset( $super_global[ $key ]['name'] ) ? map_deep( $super_global[ $key ], 'sanitize_file_name' ) : $super_global[ $key ];
		}

		return wp_kses_post_deep( wp_unslash( $super_global[ $key ] ) );
	}

	public static function get_public_post_types( $args = [] ) {
		$post_type_args = [
			'show_in_nav_menus' => true,
		];

		if ( ! empty( $args['post_type'] ) ) {
			$post_type_args['name'] = $args['post_type'];
			unset( $args['post_type'] );
		}

		$post_type_args = wp_parse_args( $post_type_args, $args );

		$post_types = [];

		foreach ( get_post_types( $post_type_args, 'objects' ) as $post_type => $object ) {
			$post_types[ $post_type ] = $object->label;
		}

		return apply_filters( 'elementor_pro/utils/get_public_post_types', $post_types );
	}
}
