<?php
namespace LaStudioKitThemeBuilder\Modules\Woocommerce\Tags;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Data_Tag;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Module;
use LaStudioKitExtensions\Elementor\Controls\Control_Query as QueryModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Woocommerce_Add_To_Cart extends Data_Tag {

	public function get_name() {
		return 'woocommerce-add-to-cart';
	}

	public function get_title() {
		return esc_html__( 'Add To Cart', 'lastudio-kit' );
	}

	public function get_group() {
		return Module::WOOCOMMERCE_GROUP;
	}

	public function get_categories() {
		return [ Module::URL_CATEGORY ];
	}

	protected function register_controls() {
		$this->add_control(
			'product_id',
			[
				'label' => esc_html__( 'Product', 'lastudio-kit' ),
				'type' => \LaStudioKitThemeBuilder\Modules\DynamicTags\Module::get_query_control_type(),
				'options' => [],
				'label_block' => true,
				'autocomplete' => [
					'object' => QueryModule::QUERY_OBJECT_POST,
					'query' => [
						'post_type' => [ 'product' ],
						'tax_query' => [
							[
								'taxonomy' => 'product_type',
								'field'    => 'slug',
								'terms'    => 'simple',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'quantity',
			[
				'label' => esc_html__( 'Quantity', 'lastudio-kit' ),
				'type' => Controls_Manager::TEXT,
				'ai' => [
					'active' => false,
				],
				// Must be a string: the V4 (atomic) schema of a TEXT control is a string prop,
				// an integer default fails validation ("link: invalid_value").
				'default' => '1',
			]
		);
	}

	public function get_value( array $options = [] ) {
		$settings = $this->get_settings_for_display();

		if ( ! $settings['product_id'] ) {
			global $product;

			$product = wc_get_product();

			if ( empty( $product ) ) {
				return;
			}

			$product_id = $product->get_id();
		} else {
			$product_id = absint( $settings['product_id'] );
		}

		$quantity = absint( $settings['quantity'] );

		$url = 'yes' === get_option( 'woocommerce_cart_redirect_after_add' )
			? wc_get_cart_url()
			: get_permalink( $product_id );

		return home_url() . '?add-to-cart=' . $product_id . '&quantity=' . $quantity . '&e-redirect=' . $url;
	}
}
