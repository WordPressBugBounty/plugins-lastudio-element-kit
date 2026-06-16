<?php
/**
 * Login messages
 */

$message = wp_cache_get( 'lakit-login-messages', 'lastudio-kit' );

if ( ! $message ) {
	return;
}

?>
<div class="lakit-login-message"><?php
	echo esc_html($message);
?></div>