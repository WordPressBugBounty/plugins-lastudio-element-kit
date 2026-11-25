<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicLoop\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Controller_Base {

	private $endpoints = [];

	abstract public function get_name();

	public function get_namespace() {
		return 'lastudio-kit/v1';
	}

	abstract protected function register_endpoints();

	protected function register_endpoint( $endpoint ) {
		$endpoint_instance = new $endpoint( $this );

		$this->endpoints[ $endpoint_instance->get_name() ] = $endpoint_instance;
	}

	public function __construct() {
		add_action( 'rest_api_init', function () {
			$this->register_endpoints();
		} );
	}
}
