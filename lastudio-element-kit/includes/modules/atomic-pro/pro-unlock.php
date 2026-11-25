<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro;

use Elementor\Modules\AtomicWidgets\PropTypes\Attributes_Prop_Type;
use LaStudioKitThemeBuilder\Modules\AtomicPro\Attributes\Pro_Attributes_Transformer;
use LaStudioKitThemeBuilder\Modules\AtomicPro\DisplayConditions\Display_Conditions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Atomic features unlocked while Elementor Pro is active.
 *
 * When Elementor Pro is active the kit's `atomic-pro` module is not loaded, and Pro only
 * enables these features for licenses that include them. This class enables, for the
 * features the current Pro license does not cover:
 *  - Style tab > Custom CSS
 *  - General tab > Attributes
 *  - Interactions > Repeat (Pro interactions runtime + editor package)
 *  - General tab > Display Conditions (atomic) / Advanced tab > Display Conditions (v3)
 * and replaces the Pro "Post Terms" / "Product Terms" dynamic tags with fixed versions.
 *
 * Everything already enabled by Pro is left untouched, so nothing is registered twice.
 */
class Pro_Unlock {

	const CUSTOM_CSS_FEATURE = 'atomic-custom-css';
	const ATTRIBUTES_FEATURE = 'atomic-custom-attributes';
	const INTERACTIONS_FEATURE = 'pro-interactions';

	private static $instance = null;

	/**
	 * Core "Pro" promotion controls to remove from atomic elements.
	 *
	 * @var string[]
	 */
	private $promotions_to_remove = [];

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		if ( ! class_exists( '\ElementorPro\License\API' ) ) {
			return;
		}

		$this->replace_term_tags();

		if ( Module::is_active() ) {
			$this->unlock_custom_css();

			if ( ! $this->pro_handles_attributes() ) {
				$this->unlock_attributes();
			}

			if ( ! $this->pro_handles_interactions() ) {
				$this->unlock_interactions();
			}
		}

		if ( ! $this->pro_handles_display_conditions() ) {
			$this->unlock_display_conditions();
		}

		if ( $this->promotions_to_remove ) {
			// Core adds locked promotions when the Pro license is inactive: the real controls replace them.
			add_filter(
				'elementor/atomic-widgets/controls',
				fn( $element_controls ) => Module::remove_promotion_controls( $element_controls, $this->promotions_to_remove ),
				999
			);
		}
	}

	/* ----------------------------------------------------------------------------------------------
	 * License checks (mirror the checks done by the Elementor Pro modules)
	 * -------------------------------------------------------------------------------------------- */

	private function pro_has_feature( $feature ) {
		return \ElementorPro\License\API::is_licence_has_feature( $feature );
	}

	private function pro_handles_attributes() {
		return $this->pro_has_feature( self::ATTRIBUTES_FEATURE ) || \ElementorPro\License\API::is_license_expired();
	}

	private function pro_handles_interactions() {
		if ( ! class_exists( '\ElementorPro\License\Admin' ) || empty( \ElementorPro\License\Admin::get_license_key() ) ) {
			return false;
		}

		if ( ! \ElementorPro\License\API::is_license_active() ) {
			return false;
		}

		return \ElementorPro\License\API::is_licence_has_feature( self::INTERACTIONS_FEATURE, \ElementorPro\License\API::BC_VALIDATION_CALLBACK );
	}

	private function pro_handles_display_conditions() {
		$module = '\ElementorPro\Modules\DisplayConditions\Module';

		return class_exists( $module ) && $module::can_use_display_conditions();
	}

	/* ----------------------------------------------------------------------------------------------
	 * Custom CSS
	 * -------------------------------------------------------------------------------------------- */

	private function unlock_custom_css() {
		// Keep `custom_css` in the editor data (Pro strips it when the license lacks the feature).
		add_filter(
			'elementor/atomic_widgets/editor_data/element_styles',
			fn( $styles_without_custom_css, $styles ) => $styles,
			20,
			2
		);

		// The editor panel asks `license/tier-features` whether the Custom CSS section is available.
		add_filter( 'rest_request_after_callbacks', [ $this, 'add_tier_features' ], 10, 3 );
	}

	/**
	 * @param \WP_REST_Response|\WP_Error|mixed $response
	 * @param array                             $handler
	 * @param \WP_REST_Request                  $request
	 */
	public function add_tier_features( $response, $handler, $request ) {
		if ( ! $response instanceof \WP_REST_Response || ! $request instanceof \WP_REST_Request ) {
			return $response;
		}

		if ( '/elementor-pro/v1/license/tier-features' !== untrailingslashit( $request->get_route() ) ) {
			return $response;
		}

		$data = $response->get_data();
		$features = is_array( $data ) && ! empty( $data['features'] ) && is_array( $data['features'] ) ? $data['features'] : [];

		$features[] = self::CUSTOM_CSS_FEATURE;

		if ( ! $this->pro_handles_attributes() ) {
			$features[] = self::ATTRIBUTES_FEATURE;
		}

		$data = is_array( $data ) ? $data : [];
		$data['features'] = array_values( array_unique( $features ) );
		$response->set_data( $data );

		return $response;
	}

	/* ----------------------------------------------------------------------------------------------
	 * Attributes
	 * -------------------------------------------------------------------------------------------- */

	private function unlock_attributes() {
		// `Custom_Attributes_Compat` hooks are already registered by Elementor Pro.
		add_action(
			'elementor/atomic-widgets/settings/transformers/register',
			function ( $transformers ) {
				$transformers->register( Attributes_Prop_Type::get_key(), new Pro_Attributes_Transformer() );
			},
			20
		);

		add_filter(
			'elementor/atomic-widgets/controls',
			fn( $element_controls, $atomic_element ) => Module::inject_attributes_control( $element_controls, $atomic_element ),
			10,
			2
		);

		$this->promotions_to_remove[] = 'attributes';
	}

	/* ----------------------------------------------------------------------------------------------
	 * Interactions (Repeat)
	 * -------------------------------------------------------------------------------------------- */

	private function unlock_interactions() {
		if ( ! class_exists( 'Elementor\\Modules\\Interactions\\Module' ) || ! class_exists( '\ElementorPro\Modules\Interactions\Hooks' ) ) {
			return;
		}

		// Same hooks Elementor Pro registers for licenses with interactions: adds the
		// `editor-interactions-extended` package and swaps in the Pro interactions runtime.
		( new \ElementorPro\Modules\Interactions\Hooks() )->register();
	}

	/* ----------------------------------------------------------------------------------------------
	 * Display conditions
	 * -------------------------------------------------------------------------------------------- */

	private function unlock_display_conditions() {
		// Elementor Pro already registers the `display-conditions` prop type and its transformers
		// (atomic-widgets module), so only the controls, editor config and frontend checks are added.
		// Works for classic (v3) elements too, even when atomic elements are disabled.
		Display_Conditions::boot();

		$this->promotions_to_remove[] = 'display-conditions';
	}

	/* ----------------------------------------------------------------------------------------------
	 * Dynamic tags
	 * -------------------------------------------------------------------------------------------- */

	/**
	 * Elementor Pro's "Post Terms" / "Product Terms" build their taxonomy list before the post type or
	 * the WooCommerce taxonomies are available, so the list is empty and the tags are dropped from
	 * atomic elements (and show an empty select in v3). Replace them with the kit's fixed versions:
	 * same names and controls, so saved settings keep working.
	 */
	private function replace_term_tags() {
		add_action( 'elementor/dynamic_tags/register', function ( $dynamic_tags ) {
			$dynamic_tags->register( new \LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Post_Terms() );

			if ( class_exists( 'WooCommerce' ) ) {
				$dynamic_tags->register( new \LaStudioKitThemeBuilder\Modules\Woocommerce\Tags\Product_Terms() );
			}
		}, 999 );
	}
}
