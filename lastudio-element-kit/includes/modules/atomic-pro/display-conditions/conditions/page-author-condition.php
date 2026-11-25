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

class Page_Author_Condition extends Condition_Base {

	public function get_name() {
		return 'page_author';
	}

	public function get_label() {
		return esc_html__( 'Author', 'lastudio-kit' );
	}

	public function get_group() {
		return 'page';
	}

	public function check( $args ) : bool {
		if ( empty( $args['authors'] ) ) {
			return true;
		}

		$current_post = get_post();

		if ( ! $current_post ) {
			return parent::check( $args );
		}

		$author_ids = array_column( $args['authors'], 'id' );

		return Comparators_Checker::check_array_contains( $args['comparator'], [ $current_post->post_author ], $author_ids );

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
			'authors',
			[
				'label' => esc_html__( 'Author', 'lastudio-kit' ),
				'type' => QueryControlModule::QUERY_CONTROL_ID,
				'autocomplete' => [
					'object' => QueryControlModule::QUERY_OBJECT_AUTHOR,
				],
				'multiple' => true,
				'placeholder' => esc_html__( 'Type to search', 'lastudio-kit' ),
				'required' => true,
			]
		);
	}
}
