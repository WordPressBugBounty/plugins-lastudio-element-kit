<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicLoop;

use Elementor\Core\Base\Document;
use Elementor\Modules\AtomicWidgets\Elements\Base\Render_Context;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop\Collection_Loop;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Documents\Loop;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Module as LoopBuilder_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Makes sure a LaStudio Loop Item template embedded inside an atomic Loop gets its CSS.
 */
class Embedded_Loop_Item_Css {

	public function register_hooks(): void {
		add_action( 'elementor/frontend/before_get_builder_content', [ $this, 'enqueue_for_embedded_template' ], 10, 2 );
	}

	public function enqueue_for_embedded_template( Document $document, bool $is_excerpt ): void {
		if ( $is_excerpt || empty( Render_Context::get( Collection_Loop::LOOP_CONTEXT_KEY ) ) ) {
			return;
		}

		if ( ! class_exists( Loop::class ) || ! $document instanceof Loop ) {
			return;
		}

		LoopBuilder_Module::ensure_template_styles( [ $document->get_main_id() ] );
	}
}
