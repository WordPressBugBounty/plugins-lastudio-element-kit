<?php
namespace LaStudioKitThemeBuilder\Modules\LoopBuilder;

use Elementor\Controls_Manager;
use Elementor\Core\Documents_Manager;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Core\Base\Document;
use Elementor\Widget_Base;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Plugin;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Preview_Manager;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Utils;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Documents\Loop as LoopDocument;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Skins\Skin_Loop_Post_Taxonomy;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Skins\Skin_Loop_Product;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Skins\Skin_Loop_Product_Taxonomy;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * LA-Studio Kit Loop Builder.
 */
class Module extends \Elementor\Core\Base\Module {

	/**
	 * Elementor template-library taxonomy slug.
	 */
	const TEMPLATE_LIBRARY_TYPE_SLUG = 'lakit-loop-item';
	const LOOP_BASE_SKIN_ID = 'base';
	const LOOP_POST_SKIN_ID = 'post';
	const LOOP_POST_TAXONOMY_SKIN_ID = 'post_taxonomy';
	const LOOP_PRODUCT_SKIN_ID = 'product';
	const LOOP_PRODUCT_TAXONOMY_SKIN_ID = 'product_taxonomy';
	const QUERY_ID = 'query';
	const LOOP_WIDGETS = [
		'lakit-loop-grid',
		'lakit-loop-carousel',
	];

	public static $taxonomies_displayed_ids = [];

	/**
	 * @var Preview_Manager
	 */
	private $preview_manager;

	public static function is_active() {
		return true;
	}

	public static function add_to_taxonomies_avoid_list( $ids ) {
		self::$taxonomies_displayed_ids = array_unique( array_merge( self::$taxonomies_displayed_ids, $ids ) );
	}

	public static function get_taxonomies_avoid_list_ids() {
		return self::$taxonomies_displayed_ids;
	}

	public function get_name() {
		return 'lakit-loop-builder';
	}

	public static function has_woocommerce() {
		return class_exists( 'WooCommerce' ) && class_exists( '\LaStudioKitThemeBuilder\Modules\Woocommerce\Classes\Products_Renderer' );
	}

	/**
	 * @return Preview_Manager
	 */
	public function get_preview_manager() {
		return $this->preview_manager;
	}

	private function get_version() {
		return lastudio_kit()->get_version( true );
	}

	public function register_styles() {
		$direction_suffix = is_rtl() ? '-rtl' : '';
		$has_custom_breakpoints = Plugin::elementor()->breakpoints->has_custom_breakpoints();

		wp_register_style(
			'lakit-loop-common',
			Plugin::get_assets_url( "css/widget-loop-common{$direction_suffix}.min.css" ),
			[ 'elementor-frontend' ],
			$this->get_version()
		);

		wp_register_style(
			'lakit-loop-grid',
			Plugin::get_frontend_file_url( "widget-loop-grid{$direction_suffix}.min.css", $has_custom_breakpoints ),
			[ 'elementor-frontend', 'lakit-loop-common' ],
			$has_custom_breakpoints ? null : $this->get_version()
		);

		wp_register_style(
			'lakit-loop-filter',
			Plugin::get_assets_url( "css/widget-loop-filter{$direction_suffix}.min.css" ),
			[ 'elementor-frontend' ],
			$this->get_version()
		);

		wp_register_style(
			'lakit-loop-carousel',
			Plugin::get_assets_url( "css/widget-loop-carousel{$direction_suffix}.min.css" ),
			[ 'elementor-frontend', 'e-swiper', 'lakit-loop-common' ],
			$this->get_version()
		);
	}

	public function register_frontend_scripts() {
		wp_register_script(
			'lakit-loop-builder',
			Plugin::get_assets_url( 'js/loop-builder-frontend.js' ),
			[ 'elementor-frontend', 'jquery' ],
			$this->get_version(),
			true
		);

		wp_localize_script( 'lakit-loop-builder', 'LaStudioKitLoopBuilderFrontendConfig', [
			'refreshUrl' => Filter\Rest_Api::get_refresh_url(),
			'nonce' => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
		] );
	}

	public function enqueue_editor_scripts() {
		wp_enqueue_script(
			'lakit-loop-builder-editor',
			Plugin::get_assets_url( 'js/loop-builder-editor.js' ),
			[ 'elementor-editor', 'lastudio-kit-ext-editor', 'wp-i18n' ],
			$this->get_version(),
			true
		);

		wp_localize_script( 'lakit-loop-builder-editor', 'LaStudioKitLoopBuilderConfig', [
			'documentType' => LoopDocument::get_type(),
			'widgets' => static::LOOP_WIDGETS,
			'ctaStyleUrl' => Plugin::get_assets_url( 'css/loop-grid-cta.min.css' ),
			'adminUrl' => admin_url(),
			'taxonomiesUrl' => Filter\Rest_Api::get_taxonomies_url(),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
			'mainQueryPostType' => $this->get_main_query_post_type(),
		] );
	}

	private function get_main_query_post_type() {
		$query_vars = isset( $GLOBALS['wp_query'] ) ? $GLOBALS['wp_query']->query_vars : [];
		$query_vars = apply_filters( 'elementor/query/get_query_args/current_query', $query_vars );

		return ! empty( $query_vars['post_type'] ) && is_string( $query_vars['post_type'] ) ? $query_vars['post_type'] : 'post';
	}

	public function enqueue_preview_styles() {
		wp_enqueue_style(
			'lakit-loop-builder-preview',
			Plugin::get_assets_url( 'css/loop-builder-preview.css' ),
			[],
			$this->get_version()
		);
	}

	public function __construct() {
		parent::__construct();

		$this->preview_manager = new Preview_Manager();

		Filter\Loop_Filter::instance();

		// Atomic elements: their CSS is only enqueued for posts declared through `elementor/post/render`.
		add_action( 'elementor/post/render', [ $this, 'declare_loop_templates_rendered' ] );

		add_action( 'elementor/frontend/after_register_styles', [ $this, 'register_styles' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_frontend_scripts' ] );
		add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_preview_styles' ] );

		add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
			$widgets_manager->register( new Widgets\Loop_Grid() );
			$widgets_manager->register( new Widgets\Loop_Carousel() );
			$widgets_manager->register( new Widgets\Taxonomy_Filter() );
		} );

		add_action( 'elementor/documents/register', function ( $documents_manager ) {
			$this->register_documents( $documents_manager );
		}, 11 );

		add_filter( 'elementor/finder/categories', [ $this, 'add_finder_items' ] );
		add_action( 'elementor/template-library/create_new_dialog_fields', [ $this, 'add_posts_type_to_template_popup' ], 10 );
		add_action( 'elementor/template-library/create_new_dialog_fields', [ $this, 'add_taxonomies_type_to_template_popup' ], 12 );
		add_action( 'lastudio-kit/loop-builder/documents/loop/query_settings', [ $this, 'add_taxonomies_type_to_loop_settings_query' ], 12 );

		add_filter( 'elementor/frontend/builder_content_data', [ $this, 'filter_content_data' ], 10, 2 );

		add_action( 'manage_' . Source_Local::CPT . '_posts_columns', function ( $columns ) {
			return $this->manage_posts_columns( $columns );
		} );

		add_action( 'elementor/editor/init', function () {
			Plugin::elementor()->common->add_template( __DIR__ . '/views/cta-template.php' );
		} );

		if ( $this->is_loop_theme_builder() ) {
			add_filter( 'template_include', [ $this, 'filter_template_to_canvas_view' ], 999 );
			add_filter( 'body_class', [ $this, 'filter_body_class' ] );
		}

		// Prevent enqueue default dynamic CSS for loop item templates.
		add_filter( 'elementor/css-file/dynamic/should_enqueue', function ( $should_enqueue, $post_id ) {
			if ( $this->is_loop_item_document_type_meta_key( $post_id ) ) {
				$should_enqueue = false;
			}

			return $should_enqueue;
		}, 10, 2 );

		// Prevent enqueue default Post CSS for loop item templates.
		add_action( 'elementor/css-file/post/enqueue', function ( $css_file ) {
			$post_id = $css_file->get_post_id();
			$file_handle = 'elementor-post-' . $post_id;

			if ( $this->is_loop_item_document_type_meta_key( $post_id ) && wp_style_is( $file_handle, 'enqueued' ) ) {
				wp_dequeue_style( $file_handle );
			}
		} );

		foreach ( static::LOOP_WIDGETS as $widget_type ) {
			add_action( 'elementor/widget/' . $widget_type . '/skins_init', function ( Widget_Base $widget ) {
				$widget->add_skin( new Skin_Loop_Post_Taxonomy( $widget ) );
			}, 12 );
		}

		$this->init_woocommerce();
	}

	/**
	 * WooCommerce integration (Products / Product Taxonomy skins).
	 * Port of the loop related parts of `ElementorPro\Modules\Woocommerce\Module`.
	 */
	private function init_woocommerce() {
		if ( ! static::has_woocommerce() ) {
			return;
		}

		add_action( 'elementor/template-library/create_new_dialog_fields', [ $this, 'add_products_type_to_template_popup' ], 11 );
		add_action( 'lastudio-kit/loop-builder/documents/loop/query_settings', [ $this, 'add_products_type_to_loop_settings_query' ], 11 );
		add_action( 'elementor/template-library/create_new_dialog_fields', [ $this, 'add_products_taxonomy_type_to_template_popup' ], 13 );
		add_action( 'lastudio-kit/loop-builder/documents/loop/query_settings', [ $this, 'add_products_taxonomy_type_to_loop_settings_query' ], 13 );

		foreach ( static::LOOP_WIDGETS as $widget_type ) {
			add_action( 'elementor/widget/' . $widget_type . '/skins_init', function ( Widget_Base $widget ) {
				$widget->add_skin( new Skin_Loop_Product( $widget ) );
			}, 10 );
			add_action( 'elementor/widget/' . $widget_type . '/skins_init', function ( Widget_Base $widget ) {
				$widget->add_skin( new Skin_Loop_Product_Taxonomy( $widget ) );
			}, 13 );
		}

		add_filter( 'elementor/query/query_args', function ( $query_args, $widget ) {
			return $this->loop_query( $query_args, $widget );
		}, 10, 2 );

		add_filter( 'elementor/document/config', [ $this, 'add_loop_recommended_widgets' ], 11, 2 );
	}

	/* ----------------------------------------------------------------------------------------------
	 * Atomic elements styles
	 * -------------------------------------------------------------------------------------------- */

	/**
	 * Post IDs already declared as rendered (loop templates included).
	 *
	 * @var array
	 */
	private static $rendered_post_ids = [];

	/**
	 * When a post is rendered, declare the Loop Item templates used by its loop widgets as rendered too,
	 * so Elementor enqueues their atomic styles (same approach as core Components).
	 */
	public function declare_loop_templates_rendered( $post_id ) {
		$post_id = (int) $post_id;

		if ( ! $post_id || isset( self::$rendered_post_ids[ $post_id ] ) ) {
			return;
		}

		self::$rendered_post_ids[ $post_id ] = true;

		foreach ( $this->get_loop_template_ids_from_post( $post_id ) as $template_id ) {
			do_action( 'elementor/post/render', $template_id );
		}
	}

	/**
	 * Called right before a loop widget renders its template(s). If the template was not declared before
	 * the page styles were enqueued (e.g. widget rendered by a shortcode, a popup, or another late context),
	 * declare it now and enqueue its atomic styles (printed in the footer).
	 *
	 * @param int[] $template_ids
	 */
	public static function ensure_template_styles( array $template_ids ) {
		$late = false;

		foreach ( $template_ids as $template_id ) {
			$template_id = (int) $template_id;

			if ( ! $template_id || isset( self::$rendered_post_ids[ $template_id ] ) ) {
				continue;
			}

			do_action( 'elementor/post/render', $template_id );
			$late = true;
		}

		if ( $late && did_action( 'elementor/frontend/after_enqueue_post_styles' ) ) {
			do_action( 'elementor/frontend/after_enqueue_post_styles' );
		}
	}

	/**
	 * @return int[]
	 */
	private function get_loop_template_ids_from_post( $post_id ) {
		$documents = Plugin::elementor()->documents;
		$document = Plugin::elementor()->preview->is_editor_or_preview()
			? $documents->get_doc_or_auto_save( $post_id, get_current_user_id() )
			: $documents->get( $post_id );

		if ( ! $document ) {
			return [];
		}

		$elements_data = $document->get_elements_data();

		if ( empty( $elements_data ) || ! is_array( $elements_data ) ) {
			return [];
		}

		$template_ids = [];

		Plugin::elementor()->db->iterate_data( $elements_data, function ( $element ) use ( &$template_ids ) {
			if ( empty( $element['widgetType'] ) || ! in_array( $element['widgetType'], static::LOOP_WIDGETS, true ) ) {
				return $element;
			}

			$settings = $element['settings'] ?? [];

			if ( ! empty( $settings['template_id'] ) ) {
				$template_ids[] = (int) $settings['template_id'];
			}

			if ( ! empty( $settings['alternate_templates'] ) && is_array( $settings['alternate_templates'] ) ) {
				foreach ( $settings['alternate_templates'] as $alternate ) {
					if ( ! empty( $alternate['template_id'] ) ) {
						$template_ids[] = (int) $alternate['template_id'];
					}
				}
			}

			return $element;
		} );

		return array_values( array_unique( array_filter( $template_ids ) ) );
	}

	public function filter_template_to_canvas_view() {
		return ELEMENTOR_PATH . 'modules/page-templates/templates/canvas.php';
	}

	private function get_preview_loop_item_id() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = Utils::_unstable_get_super_global_value( $_GET, 'elementor-preview' );

		if ( ! $post_id ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$library_type = Utils::_unstable_get_super_global_value( $_GET, 'elementor_library' );

			if ( 'elementor-' . self::TEMPLATE_LIBRARY_TYPE_SLUG === $library_type ) {
				$post_id = get_the_ID();
			}
		}

		return $post_id;
	}

	public function filter_body_class( $classes ) {
		$classes[] = 'e-loop-template-canvas';

		$post_id = $this->get_preview_loop_item_id();

		if ( $post_id && 'product' === $this->get_source_type_from_post_meta( $post_id ) ) {
			$classes[] = 'woocommerce';
		}

		return $classes;
	}

	/**
	 * Determine whether we are in the Editor and are trying to Edit an empty loop template.
	 * In that case, add some data so the loop document can print placeholder markup.
	 */
	public function filter_content_data( $data, $post_id ) {
		if (
			empty( $data )
			&& LoopDocument::get_type() === get_post_meta( $post_id, Document::TYPE_META_KEY, true )
			&& wp_doing_ajax()
		) {
			$data['empty_loop_template'] = true;
			$data['empty_loop_template_id'] = $post_id;
		}

		return $data;
	}

	public function add_finder_items( array $categories ) {
		$categories['create']['items']['lakit-loop-template'] = [
			'title' => esc_html__( 'Add New Loop Template (LaStudio)', 'lastudio-kit' ),
			'icon' => 'plus-circle-o',
			'url' => $this->get_admin_templates_url() . '#add_new',
			'keywords' => [ 'template', 'theme', 'new', 'create', 'loop', 'dynamic', 'listing', 'archive', 'repeater' ],
		];

		return $categories;
	}

	public function add_posts_type_to_template_popup( $form ) {
		if ( empty( $form ) ) {
			return;
		}

		$form->add_control( '_elementor_source', [
			'type' => Controls_Manager::SELECT,
			'label' => esc_html__( 'Choose source type', 'lastudio-kit' ),
			'options' => $this->get_post_type_options(),
			'section' => 'main',
			'required' => true,
			'conditions' => [
				'template-type' => self::TEMPLATE_LIBRARY_TYPE_SLUG,
			],
		] );
	}

	public function get_source_type_from_post_meta( $post_id ) {
		$source_type = get_post_meta( intval( $post_id ), '_elementor_source', true );

		return empty( $source_type ) ? 'post' : $source_type;
	}

	private function is_editing_existing_loop_item() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$elementor_library = Utils::_unstable_get_super_global_value( $_GET, 'elementor_library' ) ?? '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = Utils::_unstable_get_super_global_value( $_GET, 'elementor-preview' );

		return ! empty( $elementor_library ) && $this->is_loop_item_document_type_meta_key( $post_id );
	}

	private function is_creating_new_loop_item() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = Utils::_unstable_get_super_global_value( $_GET, 'post_type' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id = Utils::_unstable_get_super_global_value( $_GET, 'p' );

		return 'elementor_library' === $post_type && $this->is_loop_item_document_type_meta_key( $post_id );
	}

	private function is_loop_item_document_type_meta_key( $post_id ) {
		if ( ! $post_id ) {
			return false;
		}

		return static::TEMPLATE_LIBRARY_TYPE_SLUG === get_post_meta( $post_id, Document::TYPE_META_KEY, true );
	}

	private function is_loop_theme_builder() {
		return $this->is_editing_existing_loop_item() || $this->is_creating_new_loop_item();
	}

	/**
	 * @param Documents_Manager $documents_manager
	 */
	private function register_documents( $documents_manager ) {
		$documents_manager->register_document_type( LoopDocument::get_type(), LoopDocument::get_class_full_name() );
	}

	private function get_admin_templates_url( $relative = false ) {
		$base_url = Source_Local::ADMIN_MENU_SLUG;

		if ( ! $relative ) {
			$base_url = admin_url( $base_url );
		}

		return add_query_arg(
			[
				'tabs_group' => LoopDocument::ADMIN_LIBRARY_TAB_GROUP,
				'elementor_library_type' => self::TEMPLATE_LIBRARY_TYPE_SLUG,
			],
			$base_url
		);
	}

	private function manage_posts_columns( $columns ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$taxonomy_type_slug = Utils::_unstable_get_super_global_value( $_REQUEST, Source_Local::TAXONOMY_TYPE_SLUG );

		if ( self::TEMPLATE_LIBRARY_TYPE_SLUG === $taxonomy_type_slug ) {
			unset( $columns['instances'] );
		}

		return $columns;
	}

	private function get_post_type_options() {
		return [ self::LOOP_POST_SKIN_ID => esc_html__( 'Posts', 'lastudio-kit' ) ];
	}

	public function add_taxonomies_type_to_template_popup( $form ) {
		$this->add_option_to_control( $form, '_elementor_source', self::LOOP_POST_TAXONOMY_SKIN_ID, esc_html__( 'Post Taxonomy', 'lastudio-kit' ) );
	}

	public function add_taxonomies_type_to_loop_settings_query( $form ) {
		$this->add_option_to_control( $form, 'source', self::LOOP_POST_TAXONOMY_SKIN_ID, esc_html__( 'Post Taxonomy', 'lastudio-kit' ) );
	}

	public function add_products_type_to_template_popup( $form ) {
		$this->add_option_to_control( $form, '_elementor_source', self::LOOP_PRODUCT_SKIN_ID, esc_html__( 'Products', 'lastudio-kit' ) );
	}

	public function add_products_type_to_loop_settings_query( $form ) {
		$this->add_option_to_control( $form, 'source', self::LOOP_PRODUCT_SKIN_ID, esc_html__( 'Products', 'lastudio-kit' ) );
	}

	public function add_products_taxonomy_type_to_template_popup( $form ) {
		$this->add_option_to_control( $form, '_elementor_source', self::LOOP_PRODUCT_TAXONOMY_SKIN_ID, esc_html__( 'Product Taxonomy', 'lastudio-kit' ) );
	}

	public function add_products_taxonomy_type_to_loop_settings_query( $form ) {
		$this->add_option_to_control( $form, 'source', self::LOOP_PRODUCT_TAXONOMY_SKIN_ID, esc_html__( 'Product Taxonomy', 'lastudio-kit' ) );
	}

	/**
	 * Adds an option to a select control of the "Create new template" dialog or of the Loop document.
	 */
	protected function add_option_to_control( $form, $control_name, $option_key, $option_label ) {
		if ( empty( $form ) ) {
			return;
		}

		$controls = $form->get_controls( $control_name );

		if ( ! $controls || ! isset( $controls['options'] ) ) {
			return;
		}

		if ( '_elementor_source' === $control_name && isset( $controls['conditions']['template-type'] ) && self::TEMPLATE_LIBRARY_TYPE_SLUG !== $controls['conditions']['template-type'] ) {
			return;
		}

		$options = $controls['options'];
		$options[ $option_key ] = $option_label;

		$form->update_control( $control_name, [
			'options' => $options,
		] );
	}

	/* ----------------------------------------------------------------------------------------------
	 * WooCommerce
	 * -------------------------------------------------------------------------------------------- */

	public function loop_query( $query_args, $widget ) {
		if ( ! $this->is_product_query( $widget ) ) {
			return $query_args;
		}

		return $this->parse_loop_query_args( $widget, $query_args );
	}

	private function is_product_query( $widget ) {
		if ( ! $widget instanceof Widgets\Base ) {
			return false;
		}

		return self::LOOP_PRODUCT_SKIN_ID === $widget->get_current_skin_id();
	}

	private function parse_loop_query_args( $widget, $query_args ) {
		global $wp_query;

		$settings = $this->adjust_setting_for_product_renderer( $widget );

		// For Products_Renderer.
		if ( ! isset( $GLOBALS['post'] ) ) {
			$GLOBALS['post'] = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		if ( 'current_query' === $settings['query_post_type'] && class_exists( '\LaStudioKitThemeBuilder\Modules\Woocommerce\Classes\Current_Query_Renderer' ) ) {
			$shortcode = new \LaStudioKitThemeBuilder\Modules\Woocommerce\Classes\Current_Query_Renderer( $settings, 'current_query' );
		} else {
			$shortcode = new \LaStudioKitThemeBuilder\Modules\Woocommerce\Classes\Products_Renderer( $settings, 'products' );
		}

		$parsed_query_args = $shortcode->get_query_args();

		if ( empty( $wp_query->include_field_ids_arg ) ) {
			unset( $parsed_query_args['fields'] );
		}

		$override_various_query_args = array_filter( $query_args, function ( $key ) {
			return in_array( $key, [ 'posts_per_page', 'offset', 'paged' ], true );
		}, ARRAY_FILTER_USE_KEY );

		return wp_parse_args( $override_various_query_args, $parsed_query_args );
	}

	private function adjust_setting_for_product_renderer( $widget ) {
		$settings = $widget->get_settings_for_display();

		$query_name = $widget->get_query_name();

		$unique_query_settings = array_filter( $settings, function ( $key ) use ( $query_name ) {
			return 0 === strpos( $key, $query_name );
		}, ARRAY_FILTER_USE_KEY );

		$query_settings = [];

		foreach ( $unique_query_settings as $key => $value ) {
			$query_settings[ 'query' . str_replace( $query_name, '', $key ) ] = $value;
		}

		$settings = array_merge( $settings, $query_settings );

		$settings['columns'] = ! empty( $settings['columns'] ) ? $settings['columns'] : 4;

		if ( isset( $settings['posts_per_page'] ) ) {
			$settings['rows'] = ceil( $settings['posts_per_page'] / max( 1, (int) $settings['columns'] ) );
		} else {
			$settings['rows'] = 1;
		}

		$settings['paginate'] = 'yes';
		$settings['allow_order'] = 'no';
		$settings['show_result_count'] = 'no';
		$settings['query_fields'] = false;

		foreach ( [ 'query_post_type' => 'product', 'query_orderby' => 'date', 'query_order' => 'desc' ] as $key => $default ) {
			if ( empty( $settings[ $key ] ) ) {
				$settings[ $key ] = $default;
			}
		}

		return $settings;
	}

	public function add_loop_recommended_widgets( $config, $post_id ) {
		if ( 'product' !== get_post_meta( $post_id, '_elementor_source', true ) ) {
			return $config;
		}

		if ( LoopDocument::get_type() !== get_post_meta( $post_id, Document::TYPE_META_KEY, true ) ) {
			return $config;
		}

		$recommended = [
			'woocommerce-product-title',
			'woocommerce-product-price',
			'woocommerce-product-add-to-cart',
			'woocommerce-product-rating',
			'woocommerce-product-short-description',
			'lakit-wooproduct-title',
			'lakit-wooproduct-price',
			'lakit-wooproduct-addtocart',
			'lakit-wooproduct-rating',
			'lakit-wooproduct-shortdescription',
		];

		foreach ( $recommended as $widget_name ) {
			$config['panel']['widgets_settings'][ $widget_name ] = [
				'categories' => [ 'recommended' ],
				'show_in_panel' => true,
			];
		}

		$config['panel']['widgets_settings']['woocommerce-product-images'] = [
			'show_in_panel' => false,
		];

		return $config;
	}
}
