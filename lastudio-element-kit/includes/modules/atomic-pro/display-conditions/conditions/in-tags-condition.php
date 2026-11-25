<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparator_Provider;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparators_Checker;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Condition_Base;
use LaStudioKitExtensions\Elementor\Classes\Query_Control as QueryControlModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class In_Tags_Condition extends Condition_Base {

	public function get_name() {
		return 'in_tags';
	}

	public function get_label() {
		return esc_html__( 'In Tags', 'lastudio-kit' );
	}

	public function get_group() {
		return 'post';
	}

	public function check( $args ) : bool {
		if ( empty( $args['tags'] ) ) {
			return true;
		}

		$post_tags = wp_get_post_tags( get_the_ID(), [ 'fields' => 'ids' ] ) ?? [];

		$tag_ids = array_column( $args['tags'], 'id' );

		return Comparators_Checker::check_array_contains( $args['comparator'], $tag_ids, $post_tags );
	}

	public function get_options() {
		$comparators = Comparator_Provider::get_comparators(
			[
				Comparator_Provider::COMPARATOR_IS,
				Comparator_Provider::COMPARATOR_IS_NOT,
			]
		);

		$this->add_control(
			'comparator',
			[
				'type' => Controls_Manager::SELECT,
				'options' => $comparators,
				'default' => Comparator_Provider::COMPARATOR_IS,
			]
		);

		$this->add_control(
			'tags',
			[
				'type' => QueryControlModule::QUERY_CONTROL_ID,
				'autocomplete' => [
					'object' => QueryControlModule::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => 'post_tag',
					],
				],
				'multiple' => true,
				'placeholder' => esc_html__( 'Type to search', 'lastudio-kit' ),
				'required' => true,
			]
		);
	}
}
