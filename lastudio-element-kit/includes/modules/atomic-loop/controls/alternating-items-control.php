<?php

namespace LaStudioKitThemeBuilder\Modules\AtomicLoop\Controls;

use Elementor\Modules\AtomicWidgets\Controls\Base\Element_Control_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Alternating_Items_Control extends Element_Control_Base {
	public function get_type(): string {
		return 'alternating-items';
	}

	public function get_props(): array {
		return [];
	}
}
