<?php
namespace LaStudioKitThemeBuilder\Modules\LoopBuilder\Documents;

use Elementor\Controls_Manager;
use Elementor\Core\Base\Document;
use Elementor\Modules\Library\Documents\Library_Document;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Utils as Core_Utils;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Files\Css\Loop_Css_Printer;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Query as QueryModule;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Utils;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Classes\Plugin;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Module as LoopBuilderModule;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Providers\Taxonomy_Loop_Provider;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Traits\Taxonomy_Filter_Trait;
use LaStudioKitThemeBuilder\Modules\LoopBuilder\Traits\Tag_Trait;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Loop Item document.
 *
 */
class Loop extends Library_Document {

	use Taxonomy_Filter_Trait;
	use Tag_Trait;

	const DOCUMENT_TYPE = 'lakit-loop-item';
	const SINGLE_PREFIX = 'single/';
	const ADMIN_LIBRARY_TAB_GROUP = 'theme';

	const RECOMMENDED_POSTS_WIDGET_NAMES = [
		'theme-post-title',
		'theme-post-excerpt',
		'theme-post-featured-image',
		'theme-post-content',
		'post-info',
		'lakit-post-title',
		'lakit-post-excerpt',
		'lakit-post-featured-image',
		'lakit-post-meta',
		'lakit-post-terms',
		'lakit-post-date',
		'lakit-post-author',
	];

	const WIDGETS_TO_HIDE = [
		'loop-grid',
		'loop-carousel',
		'lakit-loop-grid',
		'lakit-loop-carousel',
		'woocommerce-product-data-tabs',
	];

	const PREVIEW_TYPE = 'preview_type';
	const PREVIEW_ID = 'preview_id';

	public static function get_type() {
		return static::DOCUMENT_TYPE;
	}

	public static function get_title() {
		return esc_html__( 'Loop Item (LaStudio)', 'lastudio-kit' );
	}

	public static function get_plural_title() {
		return esc_html__( 'Loop Items (LaStudio)', 'lastudio-kit' );
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['admin_tab_group'] = static::ADMIN_LIBRARY_TAB_GROUP;
		$properties['support_kit'] = true;
		$properties['support_site_editor'] = true;
		$properties['support_conditions'] = false;
		$properties['condition_type'] = 'general';

		return $properties;
	}

	/* ----------------------------------------------------------------------------------------------
	 * Site editor (Theme Builder app)
	 * -------------------------------------------------------------------------------------------- */

	public static function get_site_editor_config() {
		return [
			'type' => static::get_type(),
			'icon' => 'eicon-loop-builder',
			'title' => static::get_title(),
			'page_title' => static::get_title(),
			'page_layout' => 'grid',
			'url' => '/site-editor/templates/' . static::get_type(),
			'urls' => [
				'route' => '/site-editor/templates/' . static::get_type(),
				'create' => static::get_create_url(),
				'thumbnail' => Plugin::get_assets_url( 'images/loop-item.svg' ),
			],
			'tooltip_data' => [
				'title' => esc_html__( 'What is a loop?', 'lastudio-kit' ),
				'content' => esc_html__( 'A Loop is a layout you can customize to display recurring dynamic content - like listings, posts, portfolios, products, etc.', 'lastudio-kit' ),
				'tip' => esc_html__( 'Start by creating a master item. All the other instances in the grid will match this design. Then go back to the widget in the editor panel and assign both a template and a source of content.', 'lastudio-kit' ),
				'docs' => '',
				'video_url' => '',
			],
			'show_instances' => false,
		];
	}

	public function get_name() {
		return static::get_type();
	}

	public static function get_create_url() {
		$base_create_url = Plugin::elementor()->documents->get_create_new_post_url( Source_Local::CPT );

		return add_query_arg( [ 'template_type' => static::get_type() ], $base_create_url );
	}

	public function save( $data ) {
		if ( isset( $data['settings']['source'] ) ) {
			update_post_meta( $this->get_main_id(), '_elementor_source', $data['settings']['source'] );
		}

		return parent::save( $data );
	}

	private function get_data_id() {
		if ( Taxonomy_Loop_Provider::is_loop_taxonomy() ) {
			return $this->get_data_id_from_taxonomy_loop_query();
		}

		return get_the_ID();
	}

	public function get_container_attributes() {
		$attributes = Document::get_container_attributes();
		$post_id = $this->get_data_id();

		$attributes['class'] .= ' e-loop-item lakit-loop-item';
		$attributes['class'] .= ' e-loop-item-' . $post_id;
		$attributes['class'] .= ' ' . esc_attr( implode( ' ', get_post_class( [], $post_id ) ) );

		$attributes['data-custom-edit-handle'] = true;

		return $attributes;
	}

	public function get_initial_config() {
		$config = parent::get_initial_config();
		$loop_builder_module = LoopBuilderModule::instance();
		$source_type = $loop_builder_module->get_source_type_from_post_meta( $this->get_main_id() );

		if ( 'post' === $source_type || 'product' === $source_type ) {
			foreach ( static::RECOMMENDED_POSTS_WIDGET_NAMES as $recommended_posts_widget_name ) {
				$config['panel']['widgets_settings'][ $recommended_posts_widget_name ] = [
					'categories' => [ 'recommended' ],
					'show_in_panel' => true,
				];
			}
		}

		$config['panel']['widgets_settings']['container'] = [
			'categories' => [ 'layout' ],
		];

		foreach ( static::WIDGETS_TO_HIDE as $widget_to_hide ) {
			$config['panel']['widgets_settings'][ $widget_to_hide ] = [
				'show_in_panel' => false,
			];
		}

		$config['container_attributes'] = $this->get_container_attributes();
		$config['lakit_loop_item'] = true;

		return $config;
	}

	public function get_location_label() {
		return '';
	}

	public function get_css_wrapper_selector() {
		return '.e-loop-item-' . $this->get_main_id();
	}

	public static function get_preview_as_default() {
		return '';
	}

	public static function get_preview_as_options() {
		$post_types = Utils::get_public_post_types();

		$post_types_options = [];

		foreach ( $post_types as $post_type => $label ) {
			$post_types_options[ self::SINGLE_PREFIX . $post_type ] = get_post_type_object( $post_type )->labels->singular_name;
		}

		return [
			'single' => [
				'label' => esc_html__( 'Single', 'lastudio-kit' ),
				'options' => $post_types_options,
			],
		];
	}

	/**
	 * Get Edit Url
	 *
	 * Disable the Library modal for non-container (section) users.
	 *
	 * @return string
	 */
	public function get_edit_url() {
		$url = parent::get_edit_url();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['action'] ) && 'elementor_new_post' === $_GET['action'] && Plugin::elementor()->experiments->is_feature_active( 'container' ) ) {
			$url .= '#library';
		}

		return $url;
	}

	protected static function get_editor_panel_categories() {
		$new_categories = [
			'recommended' => [
				'title' => esc_html__( 'Recommended', 'lastudio-kit' ),
			],
		];

		return static::insert_categories_after_favorites( $new_categories );
	}

	protected function register_controls() {
		parent::register_controls();

		$this->register_preview_settings_controls();

		$this->update_preview_control();

		$this->inject_width_control();

		$this->add_query_section();

		Plugin::elementor()->controls_manager->add_custom_css_controls( $this );
	}

	/**
	 * Preview settings (ported from Theme_Document).
	 */
	protected function register_preview_settings_controls() {
		$this->start_controls_section(
			'preview_settings',
			[
				'label' => esc_html__( 'Preview Settings', 'lastudio-kit' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			self::PREVIEW_TYPE,
			[
				'label' => esc_html__( 'Preview Dynamic Content as', 'lastudio-kit' ),
				'label_block' => true,
				'type' => Controls_Manager::SELECT,
				'default' => $this::get_preview_as_default(),
				'groups' => $this::get_preview_as_options(),
				'export' => false,
			]
		);

		$this->add_control(
			self::PREVIEW_ID,
			[
				'type' => QueryModule::QUERY_CONTROL_ID,
				'label_block' => true,
				'autocomplete' => [
					'object' => QueryModule::QUERY_OBJECT_POST,
				],
				'export' => false,
				'condition' => [
					self::PREVIEW_TYPE . '!' => [
						'',
						'search',
					],
				],
			]
		);

		$this->add_control(
			'apply_preview',
			[
				'type' => Controls_Manager::BUTTON,
				'label' => esc_html__( 'Apply & Preview', 'lastudio-kit' ),
				'label_block' => true,
				'show_label' => false,
				'text' => esc_html__( 'Apply & Preview', 'lastudio-kit' ),
				'event' => 'lakitLoopBuilder:ApplyPreview',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Get Wrapper Tags
	 *
	 * The loop item always uses a `div` as wrapper.
	 *
	 * @return false
	 */
	public function get_wrapper_tags() {
		return false;
	}

	/**
	 * Print elements with wrapper.
	 *
	 * Renders custom markup if `$elements_data['empty_loop_template']` is set. This variable is set via the
	 * 'elementor/frontend/builder_content_data' filter in the loop builder module.
	 */
	public function print_elements_with_wrapper( $elements_data = null ) {
		if ( isset( $elements_data['empty_loop_template'] ) ) {
			$this->print_empty_loop_template_markup( $elements_data['empty_loop_template_id'] );

			return;
		}

		if ( ! $elements_data ) {
			$elements_data = $this->get_elements_data();
		}
		?>
		<div <?php Core_Utils::print_html_attributes( $this->get_container_attributes() ); ?>>
			<?php $this->print_elements( $elements_data ); ?>
		</div>
		<?php
	}

	private function enqueue_loop_css() {
		Loop_Css_Printer::print_for_post( $this->post->ID, $this->is_autosave() );
	}

	public function before_get_content() {
		LoopBuilderModule::instance()->get_preview_manager()->switch_to_preview_query();
	}

	public function after_get_content() {
		LoopBuilderModule::instance()->get_preview_manager()->restore_current_query();
	}

	/**
	 * Get content.
	 *
	 * Retrieve the content with CSS in the Editor.
	 */
	public function get_content( $with_css = false ) {
		$edit_mode = Plugin::elementor()->editor->is_edit_mode();

		$document = Plugin::elementor()->documents->get_current();

		$should_switch_document = $document && $document::get_type() !== self::DOCUMENT_TYPE;

		if ( $should_switch_document ) {
			Plugin::elementor()->documents->switch_to_document( $this );
		}

		add_filter( 'elementor/frontend/builder_content/before_print_css', [ $this, 'prevent_inline_css_printing' ] );

		$this->enqueue_loop_css();

		Plugin::elementor()->editor->set_edit_mode( false );

		$this->before_get_content();

		$content = parent::get_content();

		$this->after_get_content();

		remove_filter( 'elementor/frontend/builder_content/before_print_css', [ $this, 'prevent_inline_css_printing' ] );

		Plugin::elementor()->editor->set_edit_mode( $edit_mode );

		if ( $should_switch_document ) {
			Plugin::elementor()->documents->restore_document();
		}

		return $content;
	}

	public function print_content() {
		$plugin = Plugin::elementor();

		if ( $plugin->preview->is_preview_mode( $this->get_main_id() ) ) {
			echo $plugin->preview->builder_wrapper( '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $this->get_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function get_elements_raw_data( $data = null, $with_html_content = false ) {
		$preview_manager = LoopBuilderModule::instance()->get_preview_manager();

		$preview_manager->switch_to_preview_query();

		$editor_data = parent::get_elements_raw_data( $data, $with_html_content );

		$preview_manager->restore_current_query();

		return $editor_data;
	}

	public function render_element( $data ) {
		$preview_manager = LoopBuilderModule::instance()->get_preview_manager();

		$preview_manager->switch_to_preview_query();

		$render_html = parent::render_element( $data );

		$preview_manager->restore_current_query();

		return $render_html;
	}

	/**
	 * Runs on the 'elementor/frontend/builder_content/before_print_css' hook.
	 *
	 * @return false
	 */
	public function prevent_inline_css_printing() {
		return false;
	}

	/**
	 * Markup rendered in the editor when a loop template is empty, so the editor can switch to this document in place.
	 */
	protected function print_empty_loop_template_markup( $post_id ) {
		?>
		<div
			data-elementor-type="<?php echo esc_attr( static::get_type() ); ?>"
			data-elementor-post-type="<?php echo esc_attr( $this->get_post()->post_type ); ?>"
			data-elementor-id="<?php echo esc_attr( $post_id ); ?>"
			class="elementor elementor-<?php echo esc_attr( $post_id ); ?> elementor-edit-area elementor-edit-mode elementor-edit-area-active e-loop-first-edit"
			data-elementor-title="<?php echo esc_attr( static::get_title() ); ?>"
		>
			<div class="elementor-section-wrap ui-sortable"></div>
		</div>
		<?php
	}

	public function get_wp_preview_url() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['initial_document_id'] ) ) {
			return parent::get_wp_preview_url();
		}

		$preview_id = (int) $this->get_settings( self::PREVIEW_ID );
		$post_id = $this->get_main_id();

		list( $preview_category ) = array_pad( explode( '/', (string) $this->get_settings( self::PREVIEW_TYPE ) ), 2, '' );

		$preview_url = '';

		if ( 'single' === $preview_category ) {
			$post = get_post( $preview_id );
			if ( $post ) {
				$preview_url = get_permalink( $post );
			}
		} elseif ( in_array( $preview_category, [ 'post_taxonomy', 'product_taxonomy', 'taxonomy' ], true ) ) {
			$term = get_term( $preview_id );
			if ( $term && ! is_wp_error( $term ) ) {
				$preview_url = get_term_link( $term );
			}
		}

		if ( empty( $preview_url ) ) {
			$preview_url = $this->get_permalink();
		}

		$query_args = [
			'preview' => true,
			'preview_nonce' => wp_create_nonce( 'post_preview_' . $post_id ),
			'theme_template_id' => $post_id,
		];

		$preview_url = set_url_scheme( add_query_arg( $query_args, $preview_url ) );

		return apply_filters( 'elementor/document/wp_preview_url', $preview_url, $this );
	}

	public function get_preview_as_query_args() {
		$preview_id = (int) $this->get_settings( self::PREVIEW_ID );

		list( $preview_category, $preview_object_type ) = array_pad( explode( '/', (string) $this->get_settings( self::PREVIEW_TYPE ) ), 2, '' );

		$query_args = [];

		switch ( $preview_category ) {
			case 'taxonomy':
			case 'post_taxonomy':
			case 'product_taxonomy':
				$term = $this->get_taxonomy_term( $preview_id, $preview_object_type );

				if ( $term && ! is_wp_error( $term ) ) {
					$query_args = [
						'tax_query' => [
							[
								'taxonomy' => $term->taxonomy,
								'terms' => [ $term->term_id ],
								'field' => 'id',
							],
						],
					];
				}
				break;
			case 'single':
				$query_args = [
					'post_type' => $preview_object_type,
					'p' => $preview_id,
				];
				break;
		}

		if ( empty( $query_args ) ) {
			$query_args = [
				'p' => $this->get_main_id(),
				'post_type' => $this->get_main_post()->post_type,
			];
		}

		return $query_args;
	}

	public function after_preview_switch_to_query() {}

	/**
	 * @return \WP_Error|\WP_Term|null|false
	 */
	private function get_taxonomy_term( $preview_id, $preview_object_type ) {
		if ( ! empty( $preview_id ) ) {
			return get_term( $preview_id );
		}

		$terms = get_terms( [
			'taxonomy' => $preview_object_type,
		] );

		return is_array( $terms ) ? reset( $terms ) : false;
	}

	/**
	 * @return void
	 */
	protected function add_query_section() {
		$this->start_controls_section(
			'_section_query',
			[
				'label' => esc_html__( 'Query', 'lastudio-kit' ),
				'tab' => Controls_Manager::TAB_SETTINGS,
			]
		);

		$source_type = LoopBuilderModule::instance()->get_source_type_from_post_meta( $this->get_main_id() );

		$this->add_control(
			'source',
			[
				'label' => esc_html__( 'Source Type', 'lastudio-kit' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'post' => esc_html__( 'Posts', 'lastudio-kit' ),
				],
				'default' => $source_type,
				'prefix_class' => 'elementor-view-',
			]
		);

		do_action( 'lastudio-kit/loop-builder/documents/loop/query_settings', $this );

		$this->add_control(
			'apply_query_source',
			[
				'type' => Controls_Manager::BUTTON,
				'label' => esc_html__( 'Apply', 'lastudio-kit' ),
				'label_block' => true,
				'show_label' => false,
				'text' => esc_html__( 'Apply', 'lastudio-kit' ),
				'event' => 'lakitLoopBuilder:ApplySourceChange',
			]
		);

		$this->add_control(
			'query_source_description',
			[
				'raw' => esc_html__( 'This affects the types of widgets and templates you can use for your master item.', 'lastudio-kit' ),
				'type' => Controls_Manager::RAW_HTML,
				'content_classes' => 'elementor-descriptor',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * @return void
	 */
	protected function inject_width_control() {
		$this->start_injection( [
			'type' => 'section',
			'at' => 'start',
			'of' => 'preview_settings',
		] );

		$this->add_responsive_control(
			'preview_width',
			[
				'label' => esc_html__( 'Width', 'lastudio-kit' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'vw', 'custom' ],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1140,
					],
					'em' => [
						'min' => 20,
						'max' => 100,
					],
					'rem' => [
						'min' => 20,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}}' => '--preview-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_injection();
	}

	/**
	 * @return void
	 */
	protected function update_preview_control() {
		$source_type = LoopBuilderModule::instance()->get_source_type_from_post_meta( $this->get_main_id() );

		if ( Taxonomy_Loop_Provider::is_source_type_taxonomy( $source_type ) ) {
			$this->update_taxonomy_settings_controls( $source_type );
		} else {
			$this->update_post_settings_controls( $source_type );
		}
	}

	/**
	 * @param array $new_categories
	 * @return array
	 */
	private static function insert_categories_after_favorites( array $new_categories ) {
		$existing_categories = parent::get_editor_panel_categories();
		$category_keys = array_keys( $existing_categories );
		$index = array_search( 'favorites', $category_keys, true );

		if ( false === $index ) {
			return $new_categories + $existing_categories;
		}

		$before_and_including_favorites = array_slice( $existing_categories, 0, $index + 1, true );
		$after_favorites = array_slice( $existing_categories, $index + 1, null, true );

		return $before_and_including_favorites + $new_categories + $after_favorites;
	}

	private function update_post_settings_controls( $source_type ) {
		$this->update_control(
			self::PREVIEW_TYPE,
			[
				'default' => self::SINGLE_PREFIX . $source_type,
				'label' => esc_html__( 'Preview a specific post or item', 'lastudio-kit' ),
			]
		);

		$latest_posts = get_posts( [
			'posts_per_page' => 1,
			'post_type' => $source_type,
		] );

		$args = [
			'autocomplete' => [
				'object' => QueryModule::QUERY_OBJECT_POST,
				'query' => [
					'post_type' => [ $source_type ],
				],
			],
		];

		if ( ! empty( $latest_posts ) ) {
			$args['default'] = $latest_posts[0]->ID;
		}

		$this->update_control( self::PREVIEW_ID, $args );
	}

	private function update_taxonomy_settings_controls( $source_type ) {
		$this->update_taxonomy_preview_type_control( $source_type );
		$this->update_taxonomy_preview_id_control( $source_type );
	}

	private function update_taxonomy_preview_type_control( $source_type ) {
		$post_type_slug = $this->extract_taxonomy_type( $source_type );
		$allowed_post_types = Taxonomy_Loop_Provider::get_supported_cpts( $post_type_slug );
		$options_prefix = $source_type . '/';
		$taxonomy_options = $this->get_taxonomy_options( $allowed_post_types, $options_prefix );

		$this->update_control(
			self::PREVIEW_TYPE,
			[
				'default' => Taxonomy_Loop_Provider::get_default_source_type( $source_type, $options_prefix ),
				'groups' => [
					'archive' => [
						'label' => esc_html__( 'Archive', 'lastudio-kit' ),
						'options' => $taxonomy_options,
					],
				],
				'label' => esc_html__( 'Preview a specific post or item', 'lastudio-kit' ),
			]
		);
	}

	private function update_taxonomy_preview_id_control( $source_type ) {
		$term_id = $this->get_settings_for_display( self::PREVIEW_ID ) ?? null;
		$taxonomy = Taxonomy_Loop_Provider::get_default_source_type( $source_type );

		$filter_args = [
			'taxonomy' => $taxonomy,
			'show_empty_items' => 'yes',
			'show_child_taxonomy' => 'yes',
		];

		if ( $term_id ) {
			$filter_args['term_taxonomy_id'] = $term_id;
		}

		$latest_terms = $this->get_filtered_taxonomies( $filter_args, [] );

		$args = [
			'autocomplete' => [
				'object' => QueryModule::QUERY_OBJECT_TAX,
				'query' => [
					'taxonomy' => $taxonomy,
				],
			],
		];

		if ( ! empty( $latest_terms ) ) {
			$term = array_values( $latest_terms )[0];
			$args['default'] = $term->term_id;
		}

		$this->update_control( self::PREVIEW_ID, $args );
	}

	private function extract_taxonomy_type( $source_type ) {
		return str_replace( '_taxonomy', '', $source_type );
	}
}
