<?php

namespace LaStudioKitExtensions\Elementor\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;
use Elementor\Group_Control_Base;

/**
 * Taxonomy query group control (Loop Grid / Loop Carousel taxonomy skins).
 *
 * Built on top of the kit `Group_Control_Query`, it only keeps the source + include/exclude tabs wrapper.
 * The terms controls themselves are added by the skin (see Taxonomy_Loop_Provider).
 */
class Group_Control_Taxonomy extends Group_Control_Query {

	protected static $presets;
	protected static $fields;

	public static function get_type() {
		return 'lastudiokit-taxonomy-query';
	}

	protected function init_fields_by_name( $name ) {
		$tabs_wrapper = $name . '_query_args';
		$include_wrapper = $name . '_query_include';

		return [
			'post_type' => [
				'label' => esc_html__( 'Source', 'lastudio-kit' ),
				'type' => Controls_Manager::SELECT,
				'options' => [],
			],
			'query_args' => [
				'type' => Controls_Manager::TABS,
			],
			'query_include' => [
				'type' => Controls_Manager::TAB,
				'label' => esc_html__( 'Include', 'lastudio-kit' ),
				'tabs_wrapper' => $tabs_wrapper,
			],
			'filter_by' => [
				'label' => esc_html__( 'Filter By', 'lastudio-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'show_all',
				'options' => [
					'show_all' => esc_html__( 'Show All', 'lastudio-kit' ),
					'manual_selection' => esc_html__( 'Manual Selection', 'lastudio-kit' ),
				],
				'tabs_wrapper' => $tabs_wrapper,
				'inner_tab' => $include_wrapper,
			],
			'query_exclude' => [
				'type' => Controls_Manager::TAB,
				'label' => esc_html__( 'Exclude', 'lastudio-kit' ),
				'tabs_wrapper' => $tabs_wrapper,
			],
		];
	}

	protected function prepare_fields( $fields ) {
		// Source options are taxonomies provided through `fields_options`, not post types.
		return Group_Control_Base::prepare_fields( $fields );
	}
}
