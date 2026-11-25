<?php
namespace LaStudioKitThemeBuilder\Modules\Woocommerce\Tags;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Product_Terms extends Base_Tag {
	public function get_name() {
		return 'woocommerce-product-terms-tag';
	}

	public function get_title() {
		return esc_html__( 'Product Terms', 'lastudio-kit' );
	}

	protected function register_advanced_section() {
		parent::register_advanced_section();

		$this->update_control(
			'before',
			[
				'default' => esc_html__( 'Categories', 'lastudio-kit' ) . ': ',
			]
		);
	}

	protected function register_controls() {
		$taxonomy_filter_args = [
			'show_in_nav_menus' => true,
			'object_type' => [ 'product' ],
		];

		$taxonomies = get_taxonomies( $taxonomy_filter_args, 'objects' );

		$options = [];

		foreach ( $taxonomies as $taxonomy => $object ) {
			$options[ $taxonomy ] = $object->label;
		}

		// The controls may be registered before WooCommerce registers its taxonomies (init, priority 5).
		if ( empty( $options ) ) {
			$options = [
				'product_cat' => esc_html__( 'Product categories', 'lastudio-kit' ),
				'product_tag' => esc_html__( 'Product tags', 'lastudio-kit' ),
			];

			if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
				foreach ( wc_get_attribute_taxonomies() as $attribute ) {
					$options[ wc_attribute_taxonomy_name( $attribute->attribute_name ) ] = $attribute->attribute_label;
				}
			}
		}

		$this->add_control(
			'taxonomy',
			[
				'label' => esc_html__( 'Taxonomy', 'lastudio-kit' ),
				'type' => Controls_Manager::SELECT,
				'options' => $options,
				'default' => 'product_cat',
			]
		);

		$this->add_control(
			'separator',
			[
				'label' => esc_html__( 'Separator', 'lastudio-kit' ),
				'type' => Controls_Manager::TEXT,
				'default' => ', ',
			]
		);

		$this->add_product_id_control();
	}

	public function render() {
		$settings = $this->get_settings_for_display();

		$product = $this->get_product( $settings['product_id'] );

		if ( ! $product ) {
			return;
		}

		$value = get_the_term_list( $product->get_id(), $settings['taxonomy'], '', $settings['separator'] );

		echo wp_kses_post( $value );
	}
}
