<?php
/**
 * Meta item template
 */


$item_meta = $this->_loop_item( array( 'item_meta' ), '%s' );
if(!empty($item_meta)) {
    echo '<div class="lakit-htimeline-item__meta">'.esc_html($item_meta).'</div>';
}
if( filter_var($this->get_settings_for_display('move_image_to_meta'), FILTER_VALIDATE_BOOLEAN) ) {
    $this->_render_image( $item_settings );
}