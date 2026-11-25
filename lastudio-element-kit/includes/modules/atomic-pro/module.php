<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro;

use Elementor\Modules\AtomicWidgets\Module as AtomicWidgetsModule;
use Elementor\Modules\AtomicWidgets\PropTypes\Attributes_Prop_Type;
use Elementor\Modules\AtomicWidgets\Controls\Section;
use Elementor\Modules\AtomicWidgets\PropsResolver\Transformers_Registry;
use Elementor\Modules\Variables\PropTypes\Size_Variable_Prop_Type;
use LaStudioKitThemeBuilder\Modules\AtomicPro\Attributes\Custom_Attributes_Compat;
use LaStudioKitThemeBuilder\Modules\AtomicPro\Attributes\Pro_Attributes_Transformer;
use LaStudioKitThemeBuilder\Modules\AtomicPro\Attributes\Repeatable_Attributes_Control;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Display_Conditions;
use LaStudioKitThemeBuilder\Modules\AtomicPro\PropTypes\Condition_Group_Prop_Type;
use LaStudioKitThemeBuilder\Modules\AtomicPro\PropTypes\Display_Conditions_Prop_Type;
use LaStudioKitThemeBuilder\Modules\AtomicPro\Transformers\Condition_Group as Condition_Group_Transformer;
use LaStudioKitThemeBuilder\Modules\AtomicPro\Transformers\Display_Conditions as Display_Conditions_Transformer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Atomic Elements (Editor V4) features for sites
 *
 * Ported from Elementor:
 *  - Style tab > Custom CSS
 *  - Interactions > Details > Repeat
 *  - General tab > Attributes
 *  - General tab > Display Conditions
 *  - Design system > Variables > Size
 *
 */
class Module extends \Elementor\Core\Base\Module {

	const PACKAGES = [
		'license-api',
		'core-adapter-utils',
		'editor-controls-extended',
		'editor-editing-panel-extended',
		'editor-variables-extended',
		'editor-interactions-extended',
	];

	const SIZE_VARIABLES_LIMIT = 100000;

	public static function is_active() {
		return class_exists( AtomicWidgetsModule::class ) && \Elementor\Plugin::$instance->experiments->is_feature_active( AtomicWidgetsModule::EXPERIMENT_NAME );
	}

	public function get_name() {
		return 'lakit-atomic-pro';
	}

	private function asset_url( $path ) {
		return lastudio_kit()->plugin_url( 'includes/modules/atomic-pro/assets/' . $path );
	}

	public function asset_version( $path ) {
		$file = $this->asset_path( $path );

		return is_readable( $file ) ? lastudio_kit()->get_version( true ) . '.' . filemtime( $file ) : lastudio_kit()->get_version( true );
	}

	private function asset_path( $path ) {
		return lastudio_kit()->plugin_path( 'includes/modules/atomic-pro/assets/' . $path );
	}

	public function __construct() {
		parent::__construct();

		// Editor V2 packages.
		add_action( 'elementor/editor/v2/scripts/enqueue', [ $this, 'enqueue_editor_packages' ] );

		// 1. Custom CSS: keep `custom_css` of atomic styles in the editor data (core removes it without Pro).
		add_filter(
			'elementor/atomic_widgets/editor_data/element_styles',
			fn( $styles_without_custom_css, $styles ) => $styles,
			10,
			2
		);

		// 3. Attributes.
		$this->init_attributes();

		// 4. Display conditions.
		$this->init_display_conditions();

		// 5. Size variables (backend is in core, only the quota + editor package are needed).
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_size_variables_quota' ] );
		Variables\Size_Variables_Compat::register_hooks();

		// 2. Interactions (Repeat): Pro runtime supports `repeat` / `times`.
		$this->init_interactions();

		// Remove the core "Pro" promotion controls replaced by the real controls above.
		add_filter( 'elementor/atomic-widgets/controls', [ $this, 'remove_core_promotion_controls' ], 999 );
	}

	public function remove_core_promotion_controls( $element_controls ) {
		return static::remove_promotion_controls( $element_controls, [ 'attributes', 'display-conditions' ] );
	}

	/**
	 * @param array    $element_controls
	 * @param string[] $types Promotion control types to remove.
	 */
	public static function remove_promotion_controls( $element_controls, array $types ) {
		$promotion_class = 'Elementor\\Modules\\Promotions\\Controls\\Atomic_Promotion_Control';

		foreach ( $element_controls as $item ) {
			if ( ! $item instanceof Section ) {
				continue;
			}

			$items = $item->get_items();
			$filtered = array_values( array_filter( $items, function ( $control ) use ( $promotion_class, $types ) {
				return ! ( $control instanceof $promotion_class && in_array( $control->get_type(), $types, true ) );
			} ) );

			if ( count( $filtered ) !== count( $items ) ) {
				$item->set_items( $filtered );
			}
		}

		return $element_controls;
	}

	/* ----------------------------------------------------------------------------------------------
	 * Editor packages
	 * -------------------------------------------------------------------------------------------- */

	public function enqueue_editor_packages() {
		$version = lastudio_kit()->get_version( true );

		foreach ( static::PACKAGES as $package ) {
			$config_file = $this->asset_path( "js/packages/{$package}/{$package}.asset.php" );

			if ( ! is_readable( $config_file ) ) {
				continue;
			}

			$config = require $config_file;

			// Keep only registered dependencies (some core packages are version gated).
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


	/* ----------------------------------------------------------------------------------------------
	 * Attributes
	 * -------------------------------------------------------------------------------------------- */

	private function init_attributes() {
		Custom_Attributes_Compat::register_hooks();

		add_action(
			'elementor/atomic-widgets/settings/transformers/register',
			function ( $transformers ) {
				$transformers->register( Attributes_Prop_Type::get_key(), new Pro_Attributes_Transformer() );
			},
			20
		);

		add_filter(
			'elementor/atomic-widgets/controls',
			fn( $element_controls, $atomic_element ) => static::inject_attributes_control( $element_controls, $atomic_element ),
			10,
			2
		);
	}

	public static function inject_attributes_control( $element_controls, $atomic_element ) {
		$schema = $atomic_element::get_props_schema();

		if ( ! array_key_exists( 'attributes', $schema ) ) {
			return $element_controls;
		}

		foreach ( $element_controls as $item ) {
			if ( $item instanceof Section && $item->get_id() === 'settings' ) {
				$is_empty_controls = empty( $item->get_items() );

				$control = Repeatable_Attributes_Control::bind_to( 'attributes' )
					->set_meta( [ 'topDivider' => ! $is_empty_controls ] )
					->set_repeaterLabel( __( 'Attributes', 'lastudio-kit' ) )
					->set_initialValues( [
						'key' => [
							'$$type' => 'string',
							'value' => '',
						],
						'value' => [
							'$$type' => 'string',
							'value' => '',
						],
					] )
					->set_child_control_props( (object) [] )
					->set_patternLabel( '${value.key.value}="${value.value.value}"' )
					->set_placeholder( 'Empty attribute' )
					->set_child_control_type( 'attributes' )
					->hide_duplicate()
					->hide_toggle();

				if ( method_exists( $control, 'set_prop_key' ) ) {
					$control->set_prop_key( 'attributes' );
				}

				$item->add_item( $control );
				break;
			}
		}

		return $element_controls;
	}

	/* ----------------------------------------------------------------------------------------------
	 * Display conditions
	 * -------------------------------------------------------------------------------------------- */

	private function init_display_conditions() {
		add_filter( 'elementor/atomic-widgets/props-schema', function ( $schema ) {
			$prop_type = Display_Conditions_Prop_Type::make();
			$overridable_prop_type = 'Elementor\\Modules\\Components\\PropTypes\\Overridable_Prop_Type';

			if ( class_exists( $overridable_prop_type ) ) {
				$prop_type->meta( $overridable_prop_type::ignore() );
			}

			$schema[ Display_Conditions_Prop_Type::get_key() ] = $prop_type;

			return $schema;
		} );

		add_action( 'elementor/atomic-widgets/settings/transformers/register', function ( Transformers_Registry $transformers ) {
			$transformers->register( Display_Conditions_Prop_Type::get_key(), new Display_Conditions_Transformer() );
			$transformers->register( Condition_Group_Prop_Type::get_key(), new Condition_Group_Transformer() );

			return $transformers;
		} );

		Display_Conditions::boot();
	}

	/* ----------------------------------------------------------------------------------------------
	 * Size variables
	 * -------------------------------------------------------------------------------------------- */

	public function enqueue_size_variables_quota() {
		if ( ! class_exists( Size_Variable_Prop_Type::class ) ) {
			return;
		}

		wp_add_inline_script(
			'elementor-common',
			'window.ElementorVariablesQuotaConfigExtended = ' . wp_json_encode( [
				Size_Variable_Prop_Type::get_key() => self::SIZE_VARIABLES_LIMIT,
			] ) . ';',
			'before'
		);
	}

	/* ----------------------------------------------------------------------------------------------
	 * Interactions (Repeat)
	 * -------------------------------------------------------------------------------------------- */

	private function init_interactions() {
		if ( ! class_exists( 'Elementor\\Modules\\Interactions\\Module' ) ) {
			return;
		}

		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_interactions_scripts' ], 20 );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'register_interactions_scripts' ], 1 );

		// Run after core's print_interactions_data (priority 1): if core enqueued its runtime, swap it.
		add_action( 'wp_footer', [ $this, 'replace_frontend_interactions' ], 2 );

		add_action( 'elementor/preview/enqueue_scripts', function () {
			$core_module = \Elementor\Plugin::$instance->modules_manager->get_modules( \Elementor\Modules\Interactions\Module::MODULE_NAME );

			if ( $core_module && method_exists( $core_module, 'enqueue_preview_scripts' ) ) {
				remove_action( 'elementor/preview/enqueue_scripts', [ $core_module, 'enqueue_preview_scripts' ] );
			}
		}, 1 );

		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'replace_preview_interactions' ], 20 );
	}

	private function get_interactions_deps() {
		return [
			\Elementor\Modules\Interactions\Module::HANDLE_MOTION_JS,
			\Elementor\Modules\Interactions\Module::HANDLE_SHARED_UTILS,
		];
	}

	public function register_interactions_scripts() {
		$version = lastudio_kit()->get_version( true );

		if ( ! wp_script_is( 'lakit-interactions', 'registered' ) ) {
			wp_register_script( 'lakit-interactions', $this->asset_url( 'js/interactions-pro.js' ), $this->get_interactions_deps(), $this->asset_version( 'js/interactions-pro.js' ), true );
		}

		if ( ! wp_script_is( 'lakit-editor-interactions', 'registered' ) ) {
			wp_register_script( 'lakit-editor-interactions', $this->asset_url( 'js/editor-interactions-pro.js' ), $this->get_interactions_deps(), $this->asset_version( 'js/editor-interactions-pro.js' ), true );
		}
	}

	private function get_interactions_config() {
		$module = \Elementor\Modules\Interactions\Module::instance();

		return $module && is_callable( [ $module, 'get_config' ] ) ? $module->get_config() : [];
	}

	public function replace_frontend_interactions() {
		$handle = \Elementor\Modules\Interactions\Module::HANDLE_FRONTEND;

		if ( ! wp_script_is( $handle, 'enqueued' ) ) {
			return;
		}

		$this->register_interactions_scripts();

		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
		wp_enqueue_script( 'lakit-interactions' );
		wp_localize_script( 'lakit-interactions', \Elementor\Modules\Interactions\Module::JS_CONFIG_OBJECT, $this->get_interactions_config() );
	}

	public function replace_preview_interactions() {
		$handle = \Elementor\Modules\Interactions\Module::HANDLE_EDITOR;

		$this->register_interactions_scripts();

		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
		wp_enqueue_script( \Elementor\Modules\Interactions\Module::HANDLE_SHARED_UTILS );
		wp_enqueue_script( \Elementor\Modules\Interactions\Module::HANDLE_MOTION_JS );
		wp_enqueue_script( 'lakit-editor-interactions' );
		wp_localize_script( 'lakit-editor-interactions', \Elementor\Modules\Interactions\Module::JS_CONFIG_OBJECT, $this->get_interactions_config() );
	}
}
