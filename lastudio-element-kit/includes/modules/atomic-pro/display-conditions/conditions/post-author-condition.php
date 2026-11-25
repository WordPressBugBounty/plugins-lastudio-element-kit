<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Conditions;

class Post_Author_Condition extends Page_Author_Condition {

	public function get_name() {
		return 'post_author';
	}

	public function get_group() {
		return 'post';
	}
}
