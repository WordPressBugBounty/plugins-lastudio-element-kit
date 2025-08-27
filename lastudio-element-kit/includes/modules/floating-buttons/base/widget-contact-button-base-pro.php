<?php

namespace LaStudioKitThemeBuilder\Modules\FloatingButtons\Base;

use Elementor\Modules\FloatingButtons\Base\Widget_Contact_Button_Base;

abstract class Widget_Contact_Button_Base_Pro extends Widget_Contact_Button_Base {

	public function has_widget_inner_wrapper(): bool {
		return ! lastudio_kit()->elementor()->experiments->is_feature_active( 'e_optimized_markup' );
	}

	public function show_in_panel(): bool {
		return true;
	}

	public function hide_on_search(): bool {
		return false;
	}
}
