<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicLoop;

use Elementor\Modules\AtomicWidgets\Module as AtomicWidgetsModule;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Data\Controller as Collection_Loop_Data_Controller;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop\Collection_Loop;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop_Empty_State\Collection_Loop_Empty_State;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop_Item\Collection_Loop_Item;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop_Layout\Collection_Loop_Layout;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop_Pagination\Collection_Loop_Pagination;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop_Pagination_Next\Collection_Loop_Pagination_Next;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Elements\Collection_Loop_Pagination_Prev\Collection_Loop_Pagination_Prev;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Import\Loop_Query_Import_Remap;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Query\Loop_Query_Prop_Type;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Query\Loop_Query_Transformer;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Query\TemplateTypes\Post_Taxonomy_Template_Type;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Query\TemplateTypes\Template_Type_Registry;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Woocommerce\Product_Taxonomy_Template_Type;
use LaStudioKitThemeBuilder\Modules\AtomicLoop\Woocommerce\Product_Template_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Atomic (V4) "Loop" element.
 */
class Module extends \Elementor\Core\Base\Module {

	const MODULE_NAME = 'lakit-atomic-loop';
	const PACKAGES = [
		'editor-canvas-extended',
		'editor-collection-loop',
	];
	const MIN_ELEMENTOR_VERSION = '4.2';
	const PAGINATION_SCRIPT_HANDLE = 'lakit-collection-loop-pagination-handler';

	public static function is_active() {
		return defined( 'ELEMENTOR_VERSION' )
			&& version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '>=' )
			&& class_exists( AtomicWidgetsModule::class )
			&& \Elementor\Plugin::$instance->experiments->is_feature_active( AtomicWidgetsModule::EXPERIMENT_NAME );
	}

	public function get_name() {
		return self::MODULE_NAME;
	}

	public function __construct() {
		parent::__construct();

		add_action( 'elementor/atomic-widgets/import/id-remap-handlers/register', [ Loop_Query_Import_Remap::class, 'register' ] );

		add_action( 'init', fn() => $this->init_template_type_registry() );

		// Run after core registered its "Loop" promotion element, so the real element replaces it.
		add_action(
			'elementor/elements/elements_registered',
			fn ( $elements_manager ) => $this->register_elements( $elements_manager ),
			20
		);

		add_action( 'elementor/editor/v2/scripts/enqueue', [ $this, 'enqueue_editor_packages' ], 20 );

		// Remove core's "Upgrade" card for the Loop element.
		add_filter( 'elementor/editor/localize_settings', [ $this, 'remove_promotion_data' ], 999 );

		add_action(
			'elementor/atomic-widgets/settings/transformers/register',
			fn ( $transformers ) => $transformers->register(
				Loop_Query_Prop_Type::get_key(),
				new Loop_Query_Transformer()
			)
		);

		new Collection_Loop_Data_Controller();

		( new Embedded_Loop_Item_Css() )->register_hooks();
	}

	private function init_template_type_registry(): void {
		$registry = Template_Type_Registry::instance();

		$registry->register( new Post_Taxonomy_Template_Type() );

		if ( class_exists( 'WooCommerce' ) ) {
			$registry->register( new Product_Template_Type() );
			$registry->register( new Product_Taxonomy_Template_Type() );
		}

		do_action( Template_Type_Registry::REGISTER_ACTION, $registry );
	}

	private function register_elements( $elements_manager ) {
		$elements_manager->register_element_type( new Collection_Loop() );
		$elements_manager->register_element_type( new Collection_Loop_Layout() );
		$elements_manager->register_element_type( new Collection_Loop_Item() );
		$elements_manager->register_element_type( new Collection_Loop_Pagination() );
		$elements_manager->register_element_type( new Collection_Loop_Pagination_Prev() );
		$elements_manager->register_element_type( new Collection_Loop_Pagination_Next() );
		$elements_manager->register_element_type( new Collection_Loop_Empty_State() );
	}

	public function remove_promotion_data( $settings ) {
		if ( ! empty( $settings['atomicWidgetPromotions'] ) && is_array( $settings['atomicWidgetPromotions'] ) ) {
			$settings['atomicWidgetPromotions'] = array_values( array_filter(
				$settings['atomicWidgetPromotions'],
				fn( $promotion ) => ( $promotion['type'] ?? '' ) !== 'collection-loop'
			) );
		}

		return $settings;
	}

	private function asset_path( $path ) {
		return lastudio_kit()->plugin_path( 'includes/modules/atomic-loop/assets/' . $path );
	}

	private function asset_url( $path ) {
		return lastudio_kit()->plugin_url( 'includes/modules/atomic-loop/assets/' . $path );
	}

	private function asset_version( $path ) {
		$file = $this->asset_path( $path );

		return is_readable( $file ) ? lastudio_kit()->get_version( true ) . '.' . filemtime( $file ) : lastudio_kit()->get_version( true );
	}

	/**
	 * Enqueue the editor packages. Runs after the atomic-pro module so the license-api shim
	 * and the other `*-extended` packages are already registered.
	 */
	public function enqueue_editor_packages() {
		foreach ( static::PACKAGES as $package ) {
			$config_file = $this->asset_path( "js/packages/{$package}/{$package}.asset.php" );

			if ( ! is_readable( $config_file ) ) {
				continue;
			}

			$config = require $config_file;

			$deps = array_values( array_filter( $config['deps'], fn( $dep ) => wp_script_is( $dep, 'registered' ) ) );

			wp_enqueue_script(
				$config['handle'],
				$this->asset_url( "js/packages/{$package}/{$package}.js" ),
				$deps,
				$this->asset_version( "js/packages/{$package}/{$package}.js" ),
				true
			);

			wp_set_script_translations( $config['handle'], 'lastudio-kit' );
		}
	}
}
