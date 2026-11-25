<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions;

use Elementor\Modules\AtomicWidgets\Controls\Section;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Classes\Or_Condition;
use LaStudioKitThemeBuilder\Modules\AtomicPro\PropTypes\Display_Conditions_Prop_Type;
use LaStudioKitThemeBuilder\Modules\AtomicPro\Transformers\Display_Conditions as Display_Conditions_Transformer;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display Conditions for Atomic Elements (General tab).
 *
 * Port of `ElementorPro\Modules\DisplayConditions\Module`, limited to atomic elements.
 */
class Display_Conditions extends \Elementor\Core\Base\Module {

	private $hidden_elements_ids = [];

	private static $booted = false;

	public function get_name() {
		return 'lakit-display-conditions';
	}

	/**
	 * Start Display Conditions once.
	 *
	 * Building the conditions list reads the dynamic tags config, which initializes the controls
	 * manager, so it waits until everything hooked on `elementor/init` (e.g. Elementor Pro) has run.
	 */
	public static function boot() {
		if ( self::$booted ) {
			return;
		}

		self::$booted = true;

		$start = function () {
			new self();
		};

		if ( doing_action( 'elementor/init' ) || ! did_action( 'elementor/init' ) ) {
			add_action( 'elementor/init', $start, 20 );
		} else {
			$start();
		}
	}

	public function __construct() {
		parent::__construct();

		$this->add_component( 'conditions', new Classes\Conditions_Manager( $this ) );
		$this->add_component( 'cache_notice', new Classes\Cache_Notice() );

		add_filter(
			'elementor/atomic-widgets/controls',
			fn( $element_controls, $atomic_element ) => $this->inject_display_conditions_control( $element_controls, $atomic_element ),
			100,
			2
		);

		// Classic (v3) elements: control in the Advanced tab.
		$this->add_advanced_tab_actions();

		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_main_script' ] );
		add_filter( 'elementor/editor/localize_settings', [ $this, 'localize_settings' ] );

		add_action( 'elementor/frontend/before_render', [ $this, 'before_element_render' ] );
		add_action( 'elementor/frontend/after_render', [ $this, 'after_element_render' ] );
		add_action( 'elementor/ajax/register_actions', [ $this, 'register_ajax_actions' ] );
		add_filter( 'elementor/element/is_dynamic_content', [ $this, 'filter_element_caching_is_dynamic_content' ], 10, 3 );
	}


	private function inject_display_conditions_control( $element_controls, $atomic_element ) {
		$schema = $atomic_element::get_props_schema();

		if ( ! array_key_exists( Display_Conditions_Prop_Type::get_key(), $schema ) ) {
			return $element_controls;
		}

		foreach ( $element_controls as $item ) {
			if ( $item instanceof Section && $item->get_id() === 'settings' ) {
				$control = Display_Conditions_Control::bind_to( Display_Conditions_Prop_Type::get_key() )
					->set_label( __( 'Display Conditions', 'lastudio-kit' ) )
					->set_meta( [
						'topDivider' => true,
					] );

				$item->add_item( $control );
				break;
			}
		}

		return $element_controls;
	}

	private function add_advanced_tab_actions() {
		$hooks = [
			'elementor/element/section/section_advanced/after_section_end' => 'css_classes', // Sections
			'elementor/element/column/section_advanced/after_section_end' => 'css_classes', // Columns
			'elementor/element/common/_section_style/after_section_end' => '_css_classes', // Widgets
			'elementor/element/container/section_layout/after_section_end' => 'css_classes', // Containers
		];

		foreach ( $hooks as $hook => $injection_position ) {
			add_action(
				$hook,
				function( $element, $args ) use ( $injection_position ) {
					$this->add_control_to_advanced_tab( $element, $injection_position );
				},
				10,
				2
			);
		}
	}

	/**
	 * @param \Elementor\Controls_Stack $element
	 * @param string $injection_position
	 */
	private function add_control_to_advanced_tab( $element, $injection_position ) {
		// Replace core's "Display Conditions" Pro promotion.
		if ( $element->get_controls( 'display_conditions_pro' ) ) {
			$element->remove_control( 'display_conditions_pro' );
		}

		if ( $element->get_controls( 'e_display_conditions' ) ) {
			// Already added (e.g. by Elementor Pro without a license, showing a locked promotion): unlock it.
			if ( $element->get_controls( 'e_display_conditions_trigger' ) ) {
				$element->update_control( 'e_display_conditions_trigger', [
					'raw' => $this->get_display_conditions_control_template(),
				] );
			}
			return;
		}

		$element->start_injection( [ 'of' => $injection_position ] );

		$element->add_control(
			'e_display_conditions_trigger',
			[
				'type'      => \Elementor\Controls_Manager::RAW_HTML,
				'separator' => 'before',
				'raw'       => $this->get_display_conditions_control_template(),
			]
		);

		$element->add_control(
			'e_display_conditions',
			[
				'type' => \Elementor\Controls_Manager::HIDDEN,
			]
		);

		$element->end_injection();
	}

	private function get_display_conditions_control_template() {
		ob_start();
		?>
		<div class="e-control-display-conditions__wrapper">
			<span class="e-control-display-conditions__desc">
				<?php echo esc_html__( 'Display Conditions', 'lastudio-kit' ); ?>
			</span>
			<i class="eicon-flow e-control-display-conditions"></i>
		</div>
		<?php
		return ob_get_clean();
	}

	public function localize_settings( $settings ) {
		$settings['displayConditions'] = $this->get_conditions_manager()->get_conditions_config();

		return $settings;
	}

	public function enqueue_main_script() {
		wp_enqueue_script(
			'e-display-conditions',
			lastudio_kit()->plugin_url( 'includes/modules/atomic-pro/assets/js/display-conditions.js' ),
			[
				'react',
				'react-dom',
				'backbone-marionette',
				'elementor-web-cli',
				'wp-date',
				'elementor-common',
				'elementor-editor-modules',
				'elementor-editor-document',
				'elementor-v2-ui',
				'elementor-v2-editor-ui',
				'elementor-v2-icons',
			],
			lastudio_kit()->get_version( true ),
			true
		);

		wp_add_inline_script(
			'e-display-conditions',
			'window.ElementorProDisplayConditions = ' . wp_json_encode( [ 'isLicenseExpired' => false ] ) . ';',
			'before'
		);

		wp_set_script_translations( 'e-display-conditions', 'lastudio-kit' );

		wp_enqueue_style(
			'lakit-display-conditions',
			lastudio_kit()->plugin_url( 'includes/modules/atomic-pro/assets/css/display-conditions.min.css' ),
			[],
			lastudio_kit()->get_version( true )
		);
	}

	protected function get_saved_conditions( $settings ) {
		if ( isset( $settings[ Display_Conditions_Prop_Type::get_key() ] ) ) {
			$settings['e_display_conditions'] = Display_Conditions_Transformer::extract_from_settings( $settings );
		}

		$conditions_json = ! empty( $settings['e_display_conditions'] ) ? $settings['e_display_conditions'] : '[]';

		if ( is_array( $conditions_json ) ) {
			$conditions_json = $conditions_json[0] ?? '[]';
		}

		$conditions = is_string( $conditions_json ) ? json_decode( $conditions_json, true ) : [];

		return is_array( $conditions ) ? $conditions : [];
	}

	public function before_element_render( $element ) {
		$is_atomic = array_key_exists( Display_Conditions_Prop_Type::get_key(), $this->get_element_schema( $element ) );

		if ( ! $is_atomic ) {
			// Classic (v3) element: read the raw setting to avoid parsing every element's settings.
			$raw = $element->get_settings( 'e_display_conditions' );
			if ( empty( $raw ) ) {
				return;
			}
		}

		$settings = $element->get_settings_for_display();

		$saved_conditions = $this->get_saved_conditions( $settings );

		if ( Plugin::elementor()->editor->is_edit_mode() || empty( $saved_conditions ) ) {
			return;
		}

		$saved_conditions = $this->get_converted_conditions( $saved_conditions );
		$saved_conditions = new Or_Condition( $this->get_conditions_manager(), $saved_conditions );

		if ( ! $saved_conditions->check() ) {
			add_filter( 'elementor/element/get_child_type', '__return_false' ); // Prevent getting content of inner elements.
			add_filter( 'elementor/frontend/' . $element->get_type() . '/should_render', '__return_false' );
			$this->hidden_elements_ids[] = $element->get_id();
		}
	}

	private function get_element_schema( $element ): array {
		if ( ! method_exists( $element, 'get_props_schema' ) ) {
			return [];
		}

		$schema = $element::get_props_schema();

		return is_array( $schema ) ? $schema : [];
	}

	public function after_element_render( $element ) {
		if ( ! in_array( $element->get_id(), $this->hidden_elements_ids, true ) ) {
			return;
		}

		remove_filter( 'elementor/element/get_child_type', '__return_false' );
		remove_filter( 'elementor/frontend/' . $element->get_type() . '/should_render', '__return_false' );
	}

	/**
	 * @return Classes\Conditions_Manager
	 */
	public function get_conditions_manager() {
		return $this->get_component( 'conditions' );
	}

	public function register_ajax_actions( $ajax_manager ) {
		$ajax_manager->register_ajax_action( 'display_conditions_set_cache_notice_status', [ $this->get_component( 'cache_notice' ), 'set_notice_status' ] );
	}

	public function filter_element_caching_is_dynamic_content( $is_dynamic_content, $element_raw_data, $element_instance ) {
		if ( ! empty( $element_raw_data['settings'][ Display_Conditions_Prop_Type::get_key() ] ) || ! empty( $element_raw_data['settings']['e_display_conditions'] ) ) {
			$is_dynamic_content = true;
		}

		return $is_dynamic_content;
	}

	private function get_converted_conditions( $conditions ) {
		foreach ( $conditions as $condition ) {
			if ( ! isset( $condition['condition'] ) ) {
				return $conditions;
			}
		}

		return count( $conditions ) ? [ $conditions ] : [];
	}
}
