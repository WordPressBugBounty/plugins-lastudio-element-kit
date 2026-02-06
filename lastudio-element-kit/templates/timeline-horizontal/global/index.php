<?php
/**
 * Timeline main template
 */

$nav_type = $this->get_settings_for_display('navigation_type');
$vertical_layout = $this->get_settings_for_display('vertical_layout');
$horizontal_alignment = $this->get_settings_for_display('horizontal_alignment');

$this->add_render_attribute( 'wrapper', 'class',
	array(
		'lakit-htimeline',
		'lakit-htimeline--layout-' . esc_attr( $vertical_layout ),
		'lakit-htimeline--align-' . esc_attr( $horizontal_alignment ),
		'lakit-htimeline--' . esc_attr( $nav_type ),
	)
);

$desktop_columns = $this->get_settings_for_display('columns');
if(empty($desktop_columns)){
    $desktop_columns = 3;
}
$tablet_columns = $this->get_settings_for_display('columns_tablet');
if(empty($tablet_columns)){
    $tablet_columns = $desktop_columns;
}
$mobile_columns = $this->get_settings_for_display('columns_mobile');
if(empty($mobile_columns)){
    $mobile_columns = $tablet_columns;
}

$data_columns = array(
	'desktop' => $desktop_columns,
	'tablet'  => $tablet_columns,
	'mobile'  => $mobile_columns
);

$this->add_render_attribute( 'wrapper', 'data-columns', wp_json_encode( $data_columns ) );
?>

<div <?php $this->print_render_attribute_string( 'wrapper' ) ?>>
	<div class="lakit-htimeline-inner">
		<div class="lakit-htimeline-track">
            <?php
            $this->_get_global_looped_template( 'list-top', 'cards_list' );
            if( 'full-timeline' !== $nav_type ){
                $this->_get_global_looped_template( 'list-middle', 'cards_list' );
                $this->_get_global_looped_template( 'list-bottom', 'cards_list' );
            }
            ?>
		</div>
        <?php
        if( 'full-timeline' === $nav_type ){
            $this->_get_global_looped_template( 'list-timeline', 'cards_list' );
        }
        ?>
	</div>
	<?php
		if ( 'arrows-nav' === $nav_type ) {
            $selected_prev_arrow_icon = $this->get_settings_for_display('selected_prev_arrow_icon');
            $selected_next_arrow_icon = $this->get_settings_for_display('selected_next_arrow_icon');
		    echo $this->_get_icon_setting( $selected_prev_arrow_icon, '<button class="lakit-arrow prev-arrow arrow-disabled">%s</button>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $this->_get_icon_setting( $selected_next_arrow_icon, '<button class="lakit-arrow next-arrow">%s</button>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	?>
</div>