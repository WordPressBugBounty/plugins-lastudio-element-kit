<?php

namespace LaStudioKitExtensions\Elementor\Controls;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Registers the `query` control type  so the ported
 * Display Conditions can declare their autocomplete controls without Pro.
 * The modal renders these controls itself; this class only makes the type known to Elementor.
 */
class Control_Query_Compat extends Control_Query {

    const COMPAT_CONTROL_ID = 'query';

    public function get_type() {
        return self::COMPAT_CONTROL_ID;
    }

    public static function get_control_type() {
        return self::COMPAT_CONTROL_ID;
    }
}
