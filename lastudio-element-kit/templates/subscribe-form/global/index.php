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

?>
<div <?php $this->print_render_attribute_string( 'main-container' ); ?>>
	<form method="POST" action="#" class="lakit-subscribe-form__form">
		<div class="lakit-subscribe-form__input-group">
			<div class="lakit-subscribe-form__fields">
				<input <?php $this->print_render_attribute_string( 'form-input' ); ?>/><?php $this->generate_additional_fields();
                if( lastudio_kit()->get_theme_support('elementor::newsletter-v2') ){
                    ?><a class="lakit-subscribe-form__submit elementor-button elementor-size-md" href="#"><?php if ( filter_var( $use_icon, FILTER_VALIDATE_BOOLEAN ) ) { echo '<span class="elementor-icon">'; Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' , 'class' => 'lakit-subscribe-form__submit-icon' ] ); echo '</span>'; } ?><span class="lakit-subscribe-form__submit-text"><?php echo esc_html($submit_button_text) ?></span></a><?php
                }
            ?></div>
            <?php
            if( !lastudio_kit()->get_theme_support('elementor::newsletter-v2') ){
                ?><a class="lakit-subscribe-form__submit elementor-button elementor-size-md" href="#"><?php if ( filter_var( $use_icon, FILTER_VALIDATE_BOOLEAN ) ) { echo '<span class="elementor-icon">'; Icons_Manager::render_icon( $button_icon, [ 'aria-hidden' => 'true' , 'class' => 'lakit-subscribe-form__submit-icon' ] ); echo '</span>'; } ?><span class="lakit-subscribe-form__submit-text"><?php echo esc_html($submit_button_text) ?></span></a><?php
            }
            ?>
		</div>
		<div class="lakit-subscribe-form__message"><div class="lakit-subscribe-form__message-inner"><span></span></div></div>
	</form>
</div>
