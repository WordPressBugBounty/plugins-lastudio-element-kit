<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicLoop\Data;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Endpoint_Base {

	/** @var Controller_Base */
	protected $controller;

	protected function register() {}

	public function __construct( Controller_Base $controller ) {
		$this->controller = $controller;

		$this->register();
	}
}
