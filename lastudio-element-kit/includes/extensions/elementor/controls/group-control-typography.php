<?php

namespace LaStudioKitExtensions\Elementor\Controls;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Group_Control_Typography as Base;

class Group_Control_Typography extends Base {

    protected static $fields;

    public static function get_type() {
        return 'lakit-typography';
    }
    protected function init_fields()
    {
        $fields = parent::init_fields();
        if(isset($fields['text_transform'])){
            $fields['text_transform']['responsive'] = true;
        }
        return $fields;
    }
}