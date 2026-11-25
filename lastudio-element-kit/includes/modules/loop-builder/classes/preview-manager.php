<?php
namespace LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes;

use LaStudioKitThemeBuilder\Modules\LoopBuilder\Documents\Loop;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Switches the global query to the "Preview as" post/term while a Loop Item template is edited.
 * Port of `ElementorPro\Modules\ThemeBuilder\Classes\Preview_Manager`, limited to Loop documents.
 */
class Preview_Manager {

	public function __construct() {
		add_action( 'elementor/template-library/before_get_source_data', [ $this, 'switch_to_preview_query' ] );
		add_action( 'elementor/template-library/after_get_source_data', [ $this, 'restore_current_query' ] );
		add_action( 'elementor/dynamic_tags/before_render', [ $this, 'switch_to_preview_query' ] );
		add_action( 'elementor/dynamic_tags/after_render', [ $this, 'restore_current_query' ] );
	}

	/**
	 * Stack of switches made by this manager, so we only restore what we switched.
	 *
	 * @var bool[]
	 */
	private $switched = [];

	public function switch_to_preview_query() {
		$document = Plugin::elementor()->documents->get_doc_or_auto_save( get_the_ID() );

		if ( ! $document instanceof Loop ) {
			$this->switched[] = false;

			return;
		}

		Plugin::elementor()->db->switch_to_query( $document->get_preview_as_query_args(), true );

		$document->after_preview_switch_to_query();

		$this->switched[] = true;
	}

	public function restore_current_query() {
		$did_switch = array_pop( $this->switched );

		if ( $did_switch ) {
			Plugin::elementor()->db->restore_current_query();
		}
	}
}
