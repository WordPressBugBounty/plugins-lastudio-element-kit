<?php
/**
 * Loop item template
 */


$target     = $this->get_settings_for_display('banner_link_target');
$rel        = $this->get_settings_for_display('banner_link_rel');
$banner_link = $this->get_settings_for_display('banner_link');
$banner_title = $this->get_settings_for_display('banner_title');
$banner_text = $this->get_settings_for_display('banner_text');
$css_classes = [
    'lakit-banner',
    'lakit-ef-' . esc_attr( $this->get_settings_for_display('animation_effect') ),
];

$kses_allows = \LaStudio_Kit_Helper::kses_allowed_tags();

?>
<figure class="<?php echo join(' ', $css_classes); ?>"><?php
    if(!empty($banner_link)){
        echo sprintf( '<a href="%1$s" class="lakit-banner__link" target="%2$s" rel="%3$s">', esc_url($banner_link), esc_attr($target), esc_attr($rel) );
    }
		echo '<div class="lakit-banner__overlay"></div>';
		$this->print_var($this->_get_banner_image());
		echo '<div class="lakit-banner__content">';
			echo '<div class="lakit-banner__content-wrap">';
				$title_tag = lastudio_kit_helper()->validate_html_tag( $this->_get_html( 'banner_title_html_tag', '%s' ) );
                if(!empty($banner_title)){
                    echo sprintf('<%1$s class="lakit-banner__title">%2$s</%1$s>', $title_tag, wp_kses($banner_title, $kses_allows));
                }
                if(!empty($banner_text)){
                    echo sprintf('<div class="lakit-banner__text">%1$s</div>', wp_kses($banner_text, $kses_allows));
                }
			echo '</div>';
		echo '</div>';
    if(!empty($banner_link)){
        echo '</a>';
    }
?></figure>
