<?php

$floating_date = $this->get_settings_for_display('floating_date');

if(! lastudio_kit_helper()::string_to_bool($floating_date) ){
	return;
}

$floating_date_style = $this->get_settings_for_display('floating_date_style');
$floating_date_field = $this->get_settings_for_display('floating_date_field');

$date_value = get_post_timestamp();

switch($floating_date_field){
	case 'start':
		$tmp_date = get_post_meta( get_the_ID(), 'event_start_date', true );
		break;
	case 'end':
		$tmp_date = get_post_meta( get_the_ID(), 'event_end_date', true );
		break;
	default:
		$tmp_date = null;
}

if( lastudio_kit_helper()->validate_date($tmp_date, 'Y-m-d') ){
	$date_value = strtotime($tmp_date);
}

$date_html = '';
switch($floating_date_style){
	case 'type1':
	case 'type2':
		$date_html = sprintf('<span class="m-d">%1$s</span><span class="m-my">%2$s</span>', date('j', $date_value), date('M Y', $date_value));
    break;

	default:
		$date_html = date(get_option( 'date_format', 'M j, Y'), $date_value);
}
?>
<div class="lakit-posts__floating_date lakit_fdt--<?php echo esc_attr($floating_date_style);?>">
	<div class="lakit-posts__floating_date-inner"><?php echo $date_html;?></div>
</div>