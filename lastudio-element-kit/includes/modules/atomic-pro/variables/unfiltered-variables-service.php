<?php
namespace LaStudioKitThemeBuilder\Modules\AtomicPro\Variables;

use Elementor\Modules\Variables\Services\Batch_Operations\Batch_Processor;
use Elementor\Modules\Variables\Services\Variables_Service;
use Elementor\Modules\Variables\Storage\Variables_Repository;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Same as core's Variables_Service, but `load()` keeps Size variables
 * (core strips them when Elementor Pro is not installed).
 */
class Unfiltered_Variables_Service extends Variables_Service {

	/** @var Variables_Repository */
	protected $kit_repository;

	/** @var string|null Only keep this variable type when set. */
	protected $only_type = null;

	public function __construct( Variables_Repository $repository, Batch_Processor $batch_processor, $only_type = null ) {
		parent::__construct( $repository, $batch_processor );

		$this->kit_repository = $repository;
		$this->only_type = $only_type;
	}

	public function load() {
		$collection = $this->kit_repository->load()->serialize( true );

		if ( $this->only_type && ! empty( $collection['data'] ) ) {
			$collection['data'] = array_filter(
				$collection['data'],
				fn( $variable ) => ( $variable['type'] ?? '' ) === $this->only_type
			);
		}

		return $collection;
	}
}
