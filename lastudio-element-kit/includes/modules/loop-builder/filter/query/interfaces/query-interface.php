<?php

namespace LaStudioKitThemeBuilder\Modules\LoopBuilder\Filter\Query\Interfaces;

use LaStudioKitThemeBuilder\Modules\LoopBuilder\Filter\Query\Taxonomy_Manager;

interface Query_Interface {
	public function __construct( $filter_terms, Taxonomy_Manager $taxonomy_manager );
	public function get_query();
}
