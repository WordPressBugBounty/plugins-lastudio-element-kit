<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions\Base\Title_Condition_Base;

class Post_Title_Condition extends Title_Condition_Base {

	public function get_name() {
		return 'post_title';
	}

	public function get_group() {
		return 'post';
	}

	public function get_label() {
		return esc_html__( 'Post Title', 'lastudio-kit' );
	}

	protected function get_query() {
		return [
			'post_status' => 'publish',
			'post_type' => 'post',
		];
	}
}
