<?php
/**
 * Registration messages
 */

$message = wp_cache_get( 'lakit-register-messages', 'lastudio-kit' );

if ( ! $message ) {
	return;
}

?>
<div class="lakit-register-message"><?php
	echo esc_html($message);
?></div>