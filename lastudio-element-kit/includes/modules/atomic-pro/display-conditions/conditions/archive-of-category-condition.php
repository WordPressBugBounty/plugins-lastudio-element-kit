<?php

namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Comparator_Provider;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Archive_Condition_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Archive_Of_Category_Condition extends Archive_Condition_Base {
	public function __construct() {
		parent::__construct( 'categories' );
	}

	public function get_name() {
		return 'archive_of_categories';
	}

	public function get_label(): string {
		return esc_html__( 'Of Categories', 'lastudio-kit' );
	}

	public function check( $args ): bool {
		return parent::check_is_of_taxonomy( $args );
	}

	protected function is_of_taxonomy( $args ): bool {
		return is_category( array_column( $args['categories'], 'id' ) );
	}

	protected function get_taxonomy() {
		return 'category';
	}
}
