<?php
/**
 * Register form template
 */
$username = ! empty( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : '';
$email    = ! empty( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : '';
?>
<form method="post" class="lakit-register">
  <?php if(  'yes' === $this->get_settings_for_display('show_username') ): ?>
	<p class="lakit-register__row">
		<label class="lakit-register__label" for="lakit_username"><?php echo esc_html($settings['label_username']); ?></label>
		<input type="text" class="lakit-register__input" name="username" id="lakit_username" value="<?php echo esc_attr($username); ?>" placeholder="<?php echo esc_attr($settings['placeholder_username']); ?>"/>
	</p>
  <?php endif; ?>
	<p class="lakit-register__row">
		<label  class="lakit-register__label"  for="lakit_email"><?php echo esc_html($settings['label_email']); ?></label>
		<input type="email" class="lakit-register__input" name="email" id="lakit_email" value="<?php echo esc_attr($email); ?>" placeholder="<?php echo esc_attr($settings['placeholder_email']); ?>"/>
	</p>
<?php if(  'yes' === $this->get_settings_for_display('show_password') ): ?>
	<p class="lakit-register__row">
		<label  class="lakit-register__label" for="lakit_password"><?php echo esc_html($settings['label_pass']); ?></label>
		<input type="password" class="lakit-register__input" name="password" id="lakit_password" placeholder="<?php echo esc_attr($settings['placeholder_pass']); ?>"/>
	</p>
	<?php if ( 'yes' === $this->get_settings_for_display('confirm_password') ) : ?>
		<p class="lakit-register__row">
			<label  class="lakit-register__label" for="lakit_password_confirm"><?php echo esc_html($settings['label_pass_confirm']); ?></label>
			<input type="password" class="lakit-register__input" name="password-confirm" id="lakit_password_confirm" placeholder="<?php echo esc_attr($settings['placeholder_pass_confirm']); ?>"/>
			<?php echo '<input type="hidden" name="lakit_confirm_password" value="true">'; ?>
		</p>
	<?php endif; ?>
<?php endif; ?>
    <?php
    if( 'yes' === $this->get_settings_for_display('show_extra_html') ){
        $extra_html_a = $this->get_settings_for_display('extra_html_a');
        echo sprintf('<div class="lakit-register__extra lakit-register__extra_a">%1$s</div>', wp_kses_post($extra_html_a));
    }
    ?>

    <?php do_action( 'lakit_register_form' ); ?>

  <?php
    if(shortcode_exists('Heateor_Social_Login')){
      echo do_shortcode('[Heateor_Social_Login]');
    }
    elseif (shortcode_exists('TheChamp-Login')){
      echo do_shortcode('[TheChamp-Login]');
    }
    else{
      do_action('lastudio-kit/widget/register/after_form');
    }
  ?>

	<p class="lakit-register__row lakit-register-submit">
		<?php
			wp_nonce_field( 'lakit-register', 'lakit-register-nonce' );
			printf( '<input type="hidden" name="lakit_redirect" value="%s">', esc_url($redirect_url) );
      printf('<input type="hidden" name="lakit_field_log" value="%s">', esc_url($this->get_settings_for_display('show_username')));
      printf('<input type="hidden" name="lakit_field_pwd" value="%s">', esc_attr($this->get_settings_for_display('show_password')));
      printf('<input type="hidden" name="lakit_field_cpwd" value="%s">', esc_attr($this->get_settings_for_display('confirm_password')));
      if( lastudio_kit_integration()->is_active_recaptchav3() ){
        echo '<input type="hidden" name="lakit_recaptcha_response" value=""/>';
      }
		?>
		<button type="submit" class="lakit-register__submit button" name="register"><?php
			echo esc_html($settings['label_submit']);
		?></button>
	</p>
</form>
<?php
include $this->_get_global_template( 'messages' );
