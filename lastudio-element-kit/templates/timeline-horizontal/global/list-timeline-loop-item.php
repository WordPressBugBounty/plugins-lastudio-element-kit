<?php
/**
 * Timeline list item template
 */
$item_settings = $this->_processed_item;
$item_meta = $this->_loop_item( array( 'item_meta' ), '%s' );
$show_arrow = filter_var( $this->get_settings_for_display('show_card_arrows'), FILTER_VALIDATE_BOOLEAN );
$this->add_render_attribute(
    'timeline_bar_' . $item_settings['_id'],
    array(
        'class' => array(
            'lakit-htimeline-timelinebar',
            'elementor-repeater-item-' . esc_attr( $item_settings['_id'] )
        ),
        'data-item-id' => esc_attr( $item_settings['_id'] )
    )
);

if(empty($item_meta)){
    return;
}
?>
<div <?php $this->print_render_attribute_string( 'timeline_bar_' . $item_settings['_id'] ) ?>>
	<?php
        echo '<div>'.esc_html($item_meta).'</div>';
        if($show_arrow){
            echo '<div class="lakit-htimeline-item__card-arrow"></div>';
        }
	?>
</div>
