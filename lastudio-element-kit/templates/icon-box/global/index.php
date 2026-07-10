<?php
/**
 * Icon Box template
 */
$settings = $this->get_settings_for_display();
$icons_hover_animation = $this->get_settings_for_display('icons_hover_animation');
$icon_position = $this->get_settings_for_display('icon_position');
$show_global_link = $this->get_settings_for_display('show_global_link');
$global_link = $this->get_settings_for_display('global_link');
$enable_btn = $this->get_settings_for_display('enable_btn');
$btn_link = $this->get_settings_for_display('btn_url');
$enable_hover_btn = $this->get_settings_for_display('enable_hover_btn');
$icon_align = $this->get_settings_for_display('icon_align');
$show_overlay = $this->get_settings_for_display('show_overlay');
$show_image_overlay = $this->get_settings_for_display('show_image_overlay');
$info_box_hover_animation = $this->get_settings_for_display('info_box_hover_animation');
$section_bg_hover_color_direction = $this->get_settings_for_display('section_bg_hover_color_direction');

$enable_header_icon = $this->get_settings_for_display('enable_header_icon');
$enable_tooltip = $this->get_settings_for_display('enable_tooltip');
$tooltip_position = $this->get_settings_for_display('tooltip_position');

$box_classes = ['lakit-iconbox'];
$box_classes[] = 'lakit-iconbox__icon-align-' . $icon_position;
if(!empty($info_box_hover_animation)){
    $box_classes[] = 'elementor-animation-' . $info_box_hover_animation;
}
if ($show_overlay == 'yes') {
    $box_classes[] = 'gradient-active';
}
if ($show_image_overlay == 'yes') {
    $box_classes[] = 'image-active';
}
$box_classes[] = $section_bg_hover_color_direction;


$this->add_render_attribute('infobox_wrapper', 'class', $box_classes);

$title_tag = lastudio_kit_helper()->validate_html_tag( $this->get_settings_for_display('title_size') );

$box_title = '';
$move_divider_to_header = false;
$move_subtitle_to_header = false;
if($icon_position == 'lefttitle' || $icon_position == 'righttitle'){
    $tmp_title = $this->_get_html( 'title_text', '<' . $title_tag  . ' class="lakit-iconbox__title">%s</' . $title_tag  . '>' );
    $move_divider_to_header = filter_var( $this->get_settings_for_display('move_divider_to_header'), FILTER_VALIDATE_BOOLEAN);
    $move_subtitle_to_header = filter_var( $this->get_settings_for_display('move_subtitle_to_header'), FILTER_VALIDATE_BOOLEAN);
    if($move_divider_to_header || $move_subtitle_to_header){
        if($move_subtitle_to_header){
            $tmp_title .= $this->_get_html( 'subtitle_text', '<div class="lakit-iconbox__subtitle">%s</div>' );
        }
        if($move_divider_to_header){
            $tmp_title .= '<div class="lakit-iconbox__divider"></div>';
        }
        $box_title = sprintf('<div class="lakit-iconbox__title_wrap">%1$s</div>', $tmp_title);
    }
    else{
        $box_title = $tmp_title;
    }
}

$badge_in_header = filter_var( $this->get_settings_for_display('badge_in_header'), FILTER_VALIDATE_BOOLEAN);
$badge_header_html = '';
if($badge_in_header){
	$badge_header_html = $this->get_badge();
}

if(filter_var($show_global_link, FILTER_VALIDATE_BOOLEAN)){
    $global_link_instance = 'global_link';
    $this->add_link_attributes( $global_link_instance, $global_link );
    $this->add_render_attribute( $global_link_instance, 'class', array(
        'lakit-iconbox__global_link'
    ));
    $this->add_render_attribute( 'infobox_wrapper', [
        'style' => 'cursor: pointer'
    ]);
}

$this->print_var(sprintf('<div %1$s>', $this->get_render_attribute_string('infobox_wrapper') ));

if(filter_var($show_global_link, FILTER_VALIDATE_BOOLEAN)){
    echo sprintf(
        '<a %1$s><div class="screen-reader-text">%2$s</div></a>',
        $this->get_render_attribute_string($global_link_instance),
        esc_html(strip_tags($this->get_settings_for_display('title_text')))
    );
}

    $this->print_var($this->get_overlay_image('<figure class="lakit-iconbox__image-hover">%s</figure>'));

    if( $enable_header_icon !== 'none' ){
        echo '<div class="lakit-iconbox__box_header'. ( !empty($icons_hover_animation) ? ' elementor-animation-' . esc_attr($icons_hover_animation) : '' ) .'">';
        echo '<div class="lakit-iconbox__box_icon icon_pos_' . esc_attr($icon_position) . '">';
        if($enable_header_icon === 'icon'){
            $this->print_var($this->get_main_icon('%s'));
        }
        if($enable_header_icon === 'image'){
            $this->print_var($this->get_main_image('%s'));
        }
        echo '</div>';
        $this->print_var($box_title);
        $this->print_var($badge_header_html);
        echo '</div>';
    }

    echo '<div class="lakit-iconbox__box_body">';

    if(!$move_subtitle_to_header){
        $this->_html( 'subtitle_text', '<div class="lakit-iconbox__subtitle">%s</div>' );
    }

    if($icon_position != 'lefttitle' && $icon_position != 'righttitle'){
        $this->_html( 'title_text', '<' . $title_tag  . ' class="lakit-iconbox__title">%s</' . $title_tag  . '>' );
    }

    if( !$move_divider_to_header && 'yes' == $this->get_settings_for_display('enable_divider') ){
        echo '<div class="lakit-iconbox__divider"></div>';
    }

    $this->_html( 'description_text', '<div class="lakit-iconbox__desc">%s</div>' );

    if(filter_var($enable_btn, FILTER_VALIDATE_BOOLEAN)){
        echo '<div class="lakit-iconbox__button_wrapper '. (filter_var($enable_hover_btn, FILTER_VALIDATE_BOOLEAN) ? 'enable_hover_btn' : 'disable_hover_button') .'">';
        $this->add_link_attributes('button', $btn_link );
        $this->add_render_attribute('button', 'class', 'elementor-button-link elementor-button elementor-btn-align-icon-'. $icon_align );

        if ( !empty($settings['button_hover_animation']) ) {
            $this->add_render_attribute( 'button', 'class', 'elementor-animation-' . $settings['button_hover_animation'] );
        }

        $btn_text = $this->get_button_icon('<span class="elementor-button-icon">%s</span>');
        $btn_text .= sprintf('<span class="elementor-button-text">%s</span>', $this->get_settings_for_display('btn_text'));
        echo sprintf('<a %1$s><span class="elementor-button-content-wrapper">%2$s</span></a>', $this->get_render_attribute_string('button'), $btn_text);
        echo '</div>';
    }
    echo '</div>';
    $this->print_var($this->get_water_icon('<div class="lakit-iconbox__icon-hover">%s</div>'));
    if(!$badge_in_header){
    	$this->print_var($this->get_badge());
    }

    if($enable_tooltip === 'yes'){
        echo '<span class="lakit--hint lakit--hint-parent" data-tip-pos="'. esc_attr($tooltip_position) .'" data-tip-class="lakit--hint-tip-follow-cursor eid-'. esc_attr($this->get_id()) .'"></span>';
        echo '<div class="lakit--hint_content hidden">';
        if($enable_header_icon === 'icon'){
            $this->print_var($this->get_main_icon('<div class="lakit-tip__icon">%s</div>'));
        }
        if($enable_header_icon === 'image'){
            $this->print_var($this->get_main_image('<div class="lakit-tip__image">%s</div>'));
        }
        if( $this->get_settings_for_display('tooltip_show_title') === 'yes' ){
            $this->_html( 'title_text', '<div class="lakit-tip__title">%s</div>' );
        }
        echo '</div>';
    }


echo '</div>';