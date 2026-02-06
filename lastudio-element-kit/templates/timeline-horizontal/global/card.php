<?php
/**
 * Card item template
 */
$nav_type = $this->get_settings_for_display('navigation_type');
$show_arrow = filter_var( $this->get_settings_for_display('show_card_arrows'), FILTER_VALIDATE_BOOLEAN );
$move_meta2_content = filter_var( $this->get_settings_for_display('move_meta2_content'), FILTER_VALIDATE_BOOLEAN );;
$move_image_to_meta = filter_var( $this->get_settings_for_display('move_image_to_meta'), FILTER_VALIDATE_BOOLEAN );;
$title_tag  = lastudio_kit_helper()->validate_html_tag($this->get_settings_for_display('item_title_size'));
$item_title = $this->_loop_item( array( 'item_title' ), '%s' );
$item_desc  = $this->_loop_item( array( 'item_desc' ), '%s' );
?>

<div class="lakit-htimeline-item__card">
	<div class="lakit-htimeline-item__card-inner">
		<?php

        if($move_meta2_content){
            $item_meta = $this->_loop_item( array( 'item_meta' ), '%s' );
            if(!empty($item_meta)) {
                echo '<div class="lakit-htimeline-item__meta">'.esc_html($item_meta).'</div>';
            }
            echo '<div class="card-inner2">';
        }

        if( ! $move_image_to_meta ) {
            $this->_render_image( $item_settings );
        }
        if(!empty($item_title)) {
            echo sprintf(
                '<%1$s class="lakit-htimeline-item__card-title">%2$s</%1$s>',
                esc_attr($title_tag),
                wp_kses($item_title, LaStudio_Kit_Helper::kses_allowed_tags()),
            );
        }
        if(!empty($item_desc)) {
            echo sprintf(
                '<div class="lakit-htimeline-item__card-desc">%1$s</div>',
                wp_kses($item_desc, LaStudio_Kit_Helper::kses_allowed_tags()),
            );
        }
        if($move_meta2_content){
            echo '</div>';
        }
		?>
	</div>
	<?php
    if ( $show_arrow && 'full-timeline' !== $nav_type ) {
        echo '<div class="lakit-htimeline-item__card-arrow"></div>';
    }
    ?>
</div>
