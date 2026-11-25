<?php
namespace LaStudioKitThemeBuilder\Modules\DynamicTags\Tags;

use Elementor\Controls_Manager;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Tags\Base\Pro_Tag;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Module;
use LaStudioKitThemeBuilder\Modules\DynamicTags\Classes\Pro_Utils as Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Request_Parameter extends Pro_Tag {
	public function get_name() {
		return 'request-arg';
	}

	public function get_title() {
		return esc_html__( 'Request Parameter', 'lastudio-kit' );
	}

	public function get_group() {
		return Module::SITE_GROUP;
	}

	public function get_categories() {
		return [
			Module::TEXT_CATEGORY,
			Module::POST_META_CATEGORY,
		];
	}

	public function render() {
		$settings = $this->get_settings();
		$request_type = isset( $settings['request_type'] ) ? strtoupper( $settings['request_type'] ) : false;
		$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : false;
		$value = '';

		if ( ! $param_name || ! $request_type ) {
			return '';
		}

		switch ( $request_type ) {
			case 'POST':
				$value = Utils::_unstable_get_super_global_value( $_POST, $param_name ) ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				break;
			case 'GET':
				$value = Utils::_unstable_get_super_global_value( $_GET, $param_name ) ?? ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				break;
			case 'QUERY_VAR':
				$value = get_query_var( $param_name );
				break;
		}
		if ( ! is_scalar( $value ) ) {
			return '';
		}
		$value = sanitize_text_field( wp_unslash( (string) $value ) );
		// Encode shortcode brackets so visitor input is never executed by do_shortcode().
		$value = str_replace( [ '[', ']' ], [ '&#91;', '&#93;' ], esc_html( $value ) );
		echo $value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}

	protected function register_controls() {
		$this->add_control(
			'request_type',
			[
				'label'   => esc_html__( 'Type', 'lastudio-kit' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'get',
				'options' => [
					'get' => 'Get',
					'post' => 'Post',
					'query_var' => 'Query Var',
				],
			]
		);
		$this->add_control(
			'param_name',
			[
				'label'   => esc_html__( 'Parameter Name', 'lastudio-kit' ),
				'type' => Controls_Manager::TEXT,
				'ai' => [
					'active' => false,
				],
			]
		);
	}
}
