<?php
namespace LaStudioKitThemeBuilder\Modules\Woocommerce\Documents;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\ThemeBuilder\Documents\Single_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Product extends Single_Base {

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location'] = 'single';
		$properties['condition_type'] = 'product';

		return $properties;
	}

	public static function get_type() {
		return 'product';
	}

	public static function get_title() {
		return __( 'Single Product', 'lastudio-kit' );
	}

	public static function get_plural_title() {
		return __( 'Single Products', 'lastudio-kit' );
	}

	protected static function get_site_editor_icon() {
		return 'eicon-single-product';
	}

	protected static function get_site_editor_tooltip_data() {
		return [
			'title' => __( 'What is a Single Product Template?', 'lastudio-kit' ),
			'content' => __( 'A single product template allows you to easily design the layout and style of WooCommerce single product pages, and apply that template to various conditions that you assign.', 'lastudio-kit' ),
			'tip' => __( 'You can create multiple single product templates, and assign each to different types of products, enabling a custom design for each group of similar products.', 'lastudio-kit' ),
			'docs' => 'https://la-studioweb.com/go/elementor/app-theme-builder-product',
			'video_url' => 'https://www.youtube.com/embed/PjhoB1RWkBM',
		];
	}

	public static function get_editor_panel_config() {
		$config = parent::get_editor_panel_config();
		$config['widgets_settings']['woocommerce-product-content'] = [
			'show_in_panel' => true,
		];

		return $config;
	}

	public function get_depended_widget() {
		return lastudio_kit()->elementor()->widgets_manager->get_widget_types( 'lakit-wooproduct-datatabs' );
	}

	public function get_container_attributes() {
		$attributes = parent::get_container_attributes();

		$attributes['class'] .= ' product';

		return apply_filters('lastudio-kit/document/wrapper_attributes', $attributes);
	}

	public function filter_body_classes( $body_classes ) {
		$body_classes = parent::filter_body_classes( $body_classes );

		if ( get_the_ID() === $this->get_main_id() || lastudio_kit()->elementor()->preview->is_preview_mode( $this->get_main_id() ) ) {
			$body_classes[] = 'woocommerce';
		}

		return $body_classes;
	}

	public function before_get_content() {
		parent::before_get_content();

		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		do_action( 'woocommerce_before_single_product' );
	}

	public function after_get_content() {
		parent::after_get_content();

		do_action( 'woocommerce_after_single_product' );
	}

	public function print_content() {
		if ( post_password_required() ) {
			echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		parent::print_content();
	}

	protected static function get_editor_panel_categories() {
		$categories = [
			// Move to top as active.
			'lastudiokit-woo-product' => [
				'title' => __( 'LaStudio Kit Product', 'lastudio-kit' ),
				'active' => true,
			],
		];

		$categories += parent::get_editor_panel_categories();

		unset( $categories['theme-elements-single'] );

		return $categories;
	}

	protected function register_controls() {
		parent::register_controls();

		$this->update_control(
			'preview_type',
			[
				'type' => Controls_Manager::HIDDEN,
				'default' => 'single/product',
			]
		);

		$latest_posts = get_posts( [
			'posts_per_page' => 1,
			'post_type' => 'product',
		] );

		if ( ! empty( $latest_posts ) ) {
			$this->update_control(
				'preview_id',
				[
					'default' => $latest_posts[0]->ID,
				]
			);
		}
	}

	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['category'] = 'single product';

		return $config;
	}
}
