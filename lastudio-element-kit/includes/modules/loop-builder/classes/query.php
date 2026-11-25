<?php
namespace LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes;

use LaStudioKitExtensions\Elementor\Controls\Control_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Port of the query helpers from `LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Query`.
 *
 * The editor controls are provided by the LA-Studio Kit query controls
 * (`lastudiokit-query`, `lastudiokit-query-group`, `lastudiokit-related-query`).
 */
class Query {

	const QUERY_CONTROL_ID = Control_Query::QUERY_CONTROL_ID;

	const QUERY_OBJECT_POST = 'post';
	const QUERY_OBJECT_TAX = 'tax';
	const QUERY_OBJECT_AUTHOR = 'author';
	const QUERY_OBJECT_USER = 'user';
	const QUERY_OBJECT_LIBRARY_TEMPLATE = 'library_template';
	const QUERY_OBJECT_ATTACHMENT = 'attachment';
	const QUERY_OBJECT_CPT_TAX = 'cpt_tax';
	const QUERY_OBJECT_JS = 'js';

	public static $displayed_ids = [];

	private static $ignore_avoid_list = false;

	private static $instance = null;

	/**
	 * @return static
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function add_to_avoid_list( $ids ) {
		if ( ! self::$ignore_avoid_list ) {
			self::$displayed_ids = array_unique( array_merge( self::$displayed_ids, $ids ) );
		}
	}

	public static function get_avoid_list_ids() {
		return self::$displayed_ids;
	}

	public function get_query_ignoring_avoid_list( $loop_widget, $query_name, $query_args ) {
		$original_ignore = self::$ignore_avoid_list;
		self::$ignore_avoid_list = true;

		try {
			return $this->get_query( $loop_widget, $query_name, $query_args );
		} finally {
			self::$ignore_avoid_list = $original_ignore;
		}
	}

	/**
	 * @param \Elementor\Widget_Base $widget
	 * @param string $name
	 * @param array $query_args
	 * @param array $fallback_args
	 *
	 * @return \WP_Query
	 */
	public function get_query( $widget, $name, $query_args = [], $fallback_args = [] ) {
		$prefix = $name . '_';
		$post_type = $widget->get_settings( $prefix . 'post_type' );

		if ( 'related' === $post_type ) {
			$query = new Related_Query( $widget, $name, $query_args, $fallback_args );
		} else {
			$query = new Post_Query( $widget, $name, $query_args );
		}

		return $query->get_query();
	}
}
