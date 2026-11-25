<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\Variables;

use Elementor\Core\Files\CSS\Post as Post_CSS;
use Elementor\Modules\Variables\Classes\CSS_Renderer;
use Elementor\Modules\Variables\Classes\Variables;
use Elementor\Modules\Variables\PropTypes\Size_Variable_Prop_Type;
use Elementor\Modules\Variables\Services\Batch_Operations\Batch_Processor;
use Elementor\Modules\Variables\Storage\Variables_Repository;
use Elementor\Modules\Variables\Transformers\Global_Variable_Transformer;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Elementor core hides Size variables when Elementor Pro is not installed
 * (`Variables_Service::load()`), which removes them from:
 *  - the editor list (REST `elementor/v1/variables/list`),
 *  - the kit CSS (`:root { --name: value }`),
 *  - the style transformers (lookup + `global-size-variable` transformer).
 * This class puts them back.
 */
class Size_Variables_Compat {

	const LIST_ROUTE = '/elementor/v1/variables/list';

	public static function register_hooks() {
		if ( ! class_exists( Size_Variable_Prop_Type::class ) || ! class_exists( Variables_Repository::class ) ) {
			return;
		}

		$self = new self();

		// Editor list.
		add_filter( 'rest_request_after_callbacks', [ $self, 'filter_list_response' ], 10, 3 );

		// Style transformers lookup (core initialises it at the default priority).
		add_action( 'elementor/atomic-widgets/styles/transformers/register', [ $self, 'init_lookup' ], 20 );

		// Kit CSS (core prints the other variables at the default priority).
		add_action( 'elementor/css-file/post/parse', [ $self, 'add_size_variables_css' ], 20 );
	}

	private function service( $only_type = null ) {
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();

		return new Unfiltered_Variables_Service( new Variables_Repository( $kit ), new Batch_Processor(), $only_type );
	}

	/**
	 * @param \WP_REST_Response|\WP_HTTP_Response|\WP_Error|mixed $response
	 * @param array $handler
	 * @param \WP_REST_Request $request
	 */
	public function filter_list_response( $response, $handler, $request ) {
		if ( ! $request instanceof \WP_REST_Request || self::LIST_ROUTE !== $request->get_route() ) {
			return $response;
		}

		if ( ! $response instanceof \WP_REST_Response || $response->is_error() ) {
			return $response;
		}

		$data = $response->get_data();

		if ( empty( $data['success'] ) || ! isset( $data['data']['variables'] ) ) {
			return $response;
		}

		try {
			$collection = $this->service()->load();
		} catch ( \Throwable $e ) {
			return $response;
		}

		$variables = $collection['data'] ?? [];

		$data['data']['variables'] = $variables;
		$data['data']['total'] = count( $variables );

		$response->set_data( $data );

		return $response;
	}

	/**
	 * @param \Elementor\Modules\AtomicWidgets\PropsResolver\Transformers_Registry $registry
	 */
	public function init_lookup( $registry = null ) {
		// Core only registers the transformer for color/font variables; Pro adds the size one.
		if ( $registry && method_exists( $registry, 'register' ) ) {
			$registry->register( Size_Variable_Prop_Type::get_key(), new Global_Variable_Transformer() );
		}

		try {
			Variables::init( $this->service() );
		} catch ( \Throwable $e ) {
			// Keep core's lookup.
		}
	}

	public function add_size_variables_css( $post_css ) {
		if ( ! $post_css instanceof Post_CSS || ! \Elementor\Plugin::$instance->kits_manager->is_kit( $post_css->get_post_id() ) ) {
			return;
		}

		try {
			$css = ( new CSS_Renderer( $this->service( Size_Variable_Prop_Type::get_key() ) ) )->raw_css();
		} catch ( \Throwable $e ) {
			return;
		}

		if ( $css ) {
			$post_css->get_stylesheet()->add_raw_css( $css );
		}
	}
}
