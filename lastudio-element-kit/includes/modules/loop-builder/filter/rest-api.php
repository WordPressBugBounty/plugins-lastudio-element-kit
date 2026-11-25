<?php
namespace LaStudioKitThemeBuilder\Modules\LoopBuilder\Filter;

use Elementor\Utils as Core_Utils;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Plugin;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Traits\Taxonomy_Filter_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * REST endpoints of the loop filter.
 * Port of `ElementorPro\Modules\LoopFilter\Data\Endpoints\Refresh_Loop` / `Get_Post_Type_Taxonomies`.
 *
 * - POST lastudio-kit/v1/loop-filter/refresh-loop
 * - POST lastudio-kit/v1/loop-filter/get-post-type-taxonomies
 */
class Rest_Api {

	use Taxonomy_Filter_Trait;

	const REST_NAMESPACE = 'lastudio-kit/v1';
	const LOOP_WIDGETS = [ 'lakit-loop-grid' ];

	/**
	 * @var Loop_Filter
	 */
	private $loop_filter;

	private $is_edit_mode;

	public function __construct( Loop_Filter $loop_filter ) {
		$this->loop_filter = $loop_filter;

		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public static function get_refresh_url() {
		return rest_url( self::REST_NAMESPACE . '/loop-filter/refresh-loop' );
	}

	public static function get_taxonomies_url() {
		return rest_url( self::REST_NAMESPACE . '/loop-filter/get-post-type-taxonomies' );
	}

	public function register_routes() {
		register_rest_route( self::REST_NAMESPACE, '/loop-filter/get-post-type-taxonomies', [
			[
				'args' => [
					'post_type' => [
						'type' => 'string',
						'required' => true,
						'validate_callback' => function ( $param ) {
							return ! empty( $param ) && is_string( $param );
						},
					],
				],
				'methods' => \WP_REST_Server::CREATABLE,
				'callback' => function ( \WP_REST_Request $request ) {
					return $this->get_taxonomy_options( [ $request->get_param( 'post_type' ) ] );
				},
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			],
		] );

		register_rest_route( self::REST_NAMESPACE, '/loop-filter/refresh-loop', [
			[
				'args' => [
					'post_id' => [
						'type' => 'integer',
						'required' => true,
						'validate_callback' => function ( $param ) {
							return ! empty( $param ) && is_numeric( $param );
						},
					],
					'widget_id' => [
						'type' => 'string',
						'required' => true,
						'validate_callback' => function ( $param ) {
							return $this->is_widget_id_valid( $param );
						},
					],
					'widget_filters' => [
						'type' => 'object',
						'required' => true,
						'validate_callback' => function ( $param ) {
							return is_array( $param );
						},
					],
					'widget_model' => [
						'type' => 'object',
						'required' => false,
						'validate_callback' => function ( $param, $request ) {
							if ( ! $this->is_edit_mode( $request->get_param( 'post_id' ) ) ) {
								return false;
							}

							return $this->is_widget_model_valid( $param );
						},
					],
					'pagination_base_url' => [
						'required' => false,
						'validate_callback' => function ( $param ) {
							return filter_var( $param, FILTER_VALIDATE_URL );
						},
						'sanitize_callback' => 'esc_url_raw',
					],
				],
				'methods' => \WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'get_updated_loop_widget_markup' ],
				'permission_callback' => [ $this, 'refresh_permission_callback' ],
			],
		] );
	}

	public function get_updated_loop_widget_markup( \WP_REST_Request $request ) {
		$data = $request->get_params();

		$this->loop_filter->register_widget_filter( $data['widget_id'], $data['widget_filters'] );

		if ( $this->is_edit_mode( $data['post_id'] ) && ! empty( $data['widget_model'] ) ) {
			$widget_instance = Plugin::elementor()->elements_manager->create_element_instance( $data['widget_model'] );
		} else {
			$widget_instance = $this->create_widget_instance_from_db( $data['post_id'], $data['widget_id'] );
		}

		if ( ! $widget_instance ) {
			return new \WP_Error( 'lakit_loop_filter_not_found', 'Widget not found.', [ 'status' => 404 ] );
		}

		set_query_var( 'pagination_base_url', $data['pagination_base_url'] ?? null );

		ob_start();

		$widget_instance->render_content();

		$markup = ob_get_clean();

		set_query_var( 'pagination_base_url', null );

		return [
			'data' => $markup,
		];
	}

	public function refresh_permission_callback( \WP_REST_Request $request ) {
		$post_id = $request->get_param( 'post_id' );

		if ( $this->is_edit_mode( $post_id ) ) {
			return true;
		}

		$post = get_post( $post_id );

		if ( ! $post || 'publish' !== $post->post_status ) {
			return false;
		}

		$document = Plugin::elementor()->documents->get( $post_id );

		if ( ! $document ) {
			return false;
		}

		$widget = Core_Utils::find_element_recursive( $document->get_elements_data(), $request->get_param( 'widget_id' ) );

		return ! empty( $widget ) && 'widget' === $widget['elType'] && in_array( $widget['widgetType'], self::LOOP_WIDGETS, true );
	}

	private function is_widget_model_valid( $widget_model ) {
		return is_array( $widget_model )
			&& isset( $widget_model['id'] )
			&& is_string( $widget_model['id'] )
			&& isset( $widget_model['settings'] )
			&& is_array( $widget_model['settings'] )
			&& isset( $widget_model['widgetType'] )
			&& in_array( $widget_model['widgetType'], self::LOOP_WIDGETS, true );
	}

	/**
	 * The widget ID can only be 7 characters long, and contain only letters and numbers.
	 */
	private function is_widget_id_valid( $widget_id ) {
		return preg_match( '/^[a-zA-Z0-9]+$/', $widget_id ) && strlen( $widget_id ) === 7;
	}

	private function create_widget_instance_from_db( $post_id, $widget_id ) {
		$document = Plugin::elementor()->documents->get( $post_id );

		if ( ! $document ) {
			return null;
		}

		$widget_data = Core_Utils::find_element_recursive( $document->get_elements_data(), $widget_id );

		if ( empty( $widget_data ) ) {
			return null;
		}

		Plugin::elementor()->documents->switch_to_document( $document );

		return Plugin::elementor()->elements_manager->create_element_instance( $widget_data );
	}

	private function is_edit_mode( $post_id ) {
		if ( isset( $this->is_edit_mode ) ) {
			return $this->is_edit_mode;
		}

		$document = Plugin::elementor()->documents->get( $post_id );

		$this->is_edit_mode = ! empty( $document ) && $document->is_editable_by_current_user();

		return $this->is_edit_mode;
	}
}
