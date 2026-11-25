<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparator_Provider;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparators_Checker;
use LaStudioKitExtensions\Elementor\Classes\Query_Control as QueryControlModule;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class In_Categories_Condition extends Condition_Base {

	public function get_name() {
		return 'in_categories';
	}

	public function get_label() {
		return esc_html__( 'In Categories', 'lastudio-kit' );
	}

	public function get_group() {
		return 'post';
	}

	public function check( $args ): bool {
		if ( empty( $args['categories'] ) ) {
			return true;
		}

		$post_categories = wp_get_post_categories( get_the_ID(), [ 'fields' => 'ids' ] ) ?? [];

		if ( empty( $post_categories ) ) {
			return ! ( Comparator_Provider::COMPARATOR_IS === $args['comparator'] );
		}

		$category_ids = array_column( $args['categories'], 'id' );

		return Comparators_Checker::check_array_contains( $args['comparator'], $category_ids, $post_categories );
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
			'categories',
			[
				'type' => QueryControlModule::QUERY_CONTROL_ID,
				'autocomplete' => [
					'object' => QueryControlModule::QUERY_OBJECT_TAX,
					'query' => [
						'taxonomy' => 'category',
					],
				],
				'multiple' => true,
				'placeholder' => esc_html__( 'Type to search', 'lastudio-kit' ),
				'required' => true,
			]
		);
	}
}
