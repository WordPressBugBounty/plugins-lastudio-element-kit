<?php
/**
 * Timeline list item template
 */
$nav_type      = $this->get_settings_for_display('navigation_type');
$layout        = $this->get_settings_for_display('vertical_layout');
$item_settings = $this->_processed_item;

$this->add_render_attribute(
	'item_top_' . $item_settings['_id'],
	array(
		'class' => array(
			'lakit-htimeline-item',
			'elementor-repeater-item-' . esc_attr( $item_settings['_id'] )
		),
		'data-item-id' => esc_attr( $item_settings['_id'] )
	)
);

if ( filter_var( $item_settings['is_item_active'], FILTER_VALIDATE_BOOLEAN ) ) {
	$this->add_render_attribute( 'item_top_' . $item_settings['_id'], 'class', 'is-active' );
}

?>

<div <?php $this->print_render_attribute_string( 'item_top_' . $item_settings['_id'] ) ?>>
	<?php

    if( 'full-timeline' === $nav_type){
        include $this->_get_global_template( 'card' );
    }
    else{
        switch ( $layout ) {
            case 'top':

                include $this->_get_global_template( 'card' );

                break;

            case 'chess':

                if ( $this->_processed_index % 2 ) {
                    include $this->_get_global_template( 'meta' );
                } else {
                    include $this->_get_global_template( 'card' );
                }

                break;

            case 'bottom':

                include $this->_get_global_template( 'meta' );

                break;
        }
    }
	?>
</div>
