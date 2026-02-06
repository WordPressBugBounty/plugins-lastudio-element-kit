<?php
/**
 * Subscribe Form main template
 */

use Elementor\Icons_Manager;

$submit_button_text = $this->get_settings( 'submit_button_text' );
$submit_placeholder = $this->get_settings( 'submit_placeholder' );
$layout             = $this->get_settings( 'layout' );
$button_icon        = $this->get_settings( 'button_icon' );
$use_icon           = $this->get_settings( 'add_button_icon' );

$classes = ['lakit-subscribe-form'];

if( lastudio_kit()->get_theme_support('elementor::newsletter-v2') ){
    $classes[] = 'lakit-subscribe-v2';
}
else{
    $classes[] = esc_attr('lakit-subscribe-form--' . $layout . '-layout');
}

$this->add_render_attribute( 'main-container', 'class', join(' ', $classes) );

$this->add_render_attribute( 'main-container', 'data-settings', $this->generate_setting_json() );

$instance_data = apply_filters( 'lastudio-kit/subscribe-form/input-instance-data', array(), $this );

$this->add_render_attribute( 'form-input',
	array(
		'class'       => array(
			'lakit-subscribe-form__input lakit-subscribe-form__mail-field',
		),
		'type'               => 'email',
		'name'               => 'email',
		'placeholder'        => $submit_placeholder,
		'autocomplete'       => 'email',
		'data-instance-data' => wp_json_encode( $instance_data ),
	)
);

$submit_html = '<a class="lakit-subscribe-form__submit elementor-button elementor-size-md" href="#" aria-label="'.esc_attr__("Subscribe to newsletter", "lastudio-kit").'">';
if(filter_var( $use_icon, FILTER_VALIDATE_BOOLEAN )){
    $submit_html .= '<span class="elementor-icon">';
    $submit_html .= Icons_Manager::try_get_icon_html( $button_icon, [ 'aria-hidden' => 'true' , 'class' => 'lakit-subscribe-form__submit-icon' ] );
    $submit_html .= '</span>';
}
$submit_html .= sprintf('<span class="lakit-subscribe-form__submit-text">%1$s</span>', esc_attr($submit_button_text));
$submit_html .= '</a>';

$enable_checkbox_agreement = $this->get_settings_for_display('enable_checkbox_agreement');

?>
<div <?php $this->print_render_attribute_string( 'main-container' ); ?>>
	<form method="POST" action="#" class="lakit-subscribe-form__form">
		<div class="lakit-subscribe-form__input-group">
			<div class="lakit-subscribe-form__fields">
				<input <?php $this->print_render_attribute_string( 'form-input' ); ?>/><?php $this->generate_additional_fields();
                if( lastudio_kit()->get_theme_support('elementor::newsletter-v2') ){
                    echo $submit_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            ?></div>
            <?php
            if( !lastudio_kit()->get_theme_support('elementor::newsletter-v2') ){
                echo $submit_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
            ?>
		</div>
        <?php
            if(filter_var( $enable_checkbox_agreement, FILTER_VALIDATE_BOOLEAN )){
                $agreement_content = $this->get_settings_for_display('agreement_content');
                echo sprintf(
                '<div class="lakit-subscribe-form__agreements"><label class="lakit-subscribe-form__agreement-checkbox"><input type="checkbox" name="agreement_checkbox" value="yes"/><span class="lakit-subscribe-form__agreement-content">%1$s</span></label></div>',
                wp_kses($agreement_content, [
                    'a' => [
                        'style'    => true,
                        'class'  => true,
                        'href'  => true,
                        'target'  => true,
                    ],
                    'img' => [
                        'style'    => true,
                        'class'  => true,
                        'src'  => true,
                        'alt'  => true,
                        'width' => true,
                        'height' => true,
                        'srcset' => true,
                        'sizes' => true,
                        'loading' => true,
                        'fetchpriority' => true,
                        'decoding' => true,
                    ],
                    'strong' => [
                        'style'    => true,
                        'class'  => true,
                    ],
                    'span' => [
                        'style'    => true,
                        'class'  => true,
                    ],
                    'em' => [
                        'style'    => true,
                        'class'  => true,
                    ],
                    'i' => [
                        'style'    => true,
                        'class'  => true,
                    ],
                ])
                );
            }
        ?>
		<div class="lakit-subscribe-form__message"><div class="lakit-subscribe-form__message-inner"><span></span></div></div>
	</form>
</div>
