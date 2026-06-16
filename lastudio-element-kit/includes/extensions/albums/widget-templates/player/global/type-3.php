<?php
$icon_play = $this->_get_icon('icon_play');
$icon_pause = $this->_get_icon('icon_pause');
$icon_prev = $this->_get_icon('icon_prev');
$icon_next = $this->_get_icon('icon_next');
if(empty($icon_play)){
	$icon_play = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="1024" height="1024" viewBox="0 0 1024 1024" class="lakit-font-icon-svg" data-icon-name="play" data-icon-type="dlicon"><path d="M224 960c6.4 0 12.8-3.2 19.2-6.4l576-416c9.6-6.4 12.8-16 12.8-25.6s-6.4-19.2-12.8-25.6l-576-416c-9.6-6.4-22.4-9.6-35.2-3.2-9.6 6.4-16 16-16 28.8v832c0 12.8 6.4 22.4 16 28.8 6.4 3.2 9.6 3.2 16 3.2zm32-800l489.6 352L256 864V160z" fill="currentColor"></path></svg>';
}
if(empty($icon_pause)){
	$icon_pause = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="1024" height="1024" viewBox="0 0 1024 1024" class="lakit-font-icon-svg" data-icon-name="pause" data-icon-type="dlicon"><path d="M384 928c19.2 0 32-12.8 32-32V128c0-19.2-12.8-32-32-32H160c-19.2 0-32 12.8-32 32v768c0 19.2 12.8 32 32 32h224zM192 160h160v704H192V160zm672 768c19.2 0 32-12.8 32-32V128c0-19.2-12.8-32-32-32H640c-19.2 0-32 12.8-32 32v768c0 19.2 12.8 32 32 32h224zM672 160h160v704H672V160z" fill="currentColor"></path></svg>';
}
if(empty($icon_prev)){
	$icon_prev = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="1024" height="1024" viewBox="0 0 1024 1024" class="lakit-font-icon-svg" data-icon-name="prev" data-icon-type="dlicon"><path d="M864 960c4.96 0 9.984-1.152 14.56-3.488A31.985 31.985 0 0 0 896 928V96c0-12.032-6.72-23.04-17.44-28.512-10.72-5.504-23.584-4.544-33.312 2.56l-576 416C260.928 492.064 256 501.728 256 512s4.928 19.936 13.248 25.952l576 416C850.816 957.984 857.408 960 864 960zm-32-94.592L342.656 512 832 158.592v706.816zM128 64v896c0 17.696 14.304 32 32 32s32-14.304 32-32V64c0-17.696-14.304-32-32-32s-32 14.304-32 32z" fill="currentColor"></path></svg>';
}
if(empty($icon_next)){
	$icon_next = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="1024" height="1024" viewBox="0 0 1024 1024" class="lakit-font-icon-svg" data-icon-name="next" data-icon-type="dlicon"><path d="M145.44 67.488A31.985 31.985 0 0 0 128 96v832c0 12.032 6.72 23.04 17.44 28.512A32.086 32.086 0 0 0 160 960c6.592 0 13.184-2.016 18.752-6.048l576-416C763.072 531.936 768 522.272 768 512s-4.928-19.936-13.248-25.952l-576-416c-9.76-7.04-22.624-8-33.312-2.56zM192 158.592L681.344 512 192 865.408V158.592zM864 992c17.696 0 32-14.304 32-32V64c0-17.696-14.304-32-32-32s-32 14.304-32 32v896c0 17.696 14.304 32 32 32z" fill="currentColor"></path></svg>';
}
?>
<div class="lakitplayer__controls">
	<div class="lakitplayer__control__top">
        <button type="button" class="lakitplayer_btn__prev"><?php echo $icon_prev; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
        <button type="button" class="lakitplayer_btn__playpause">
			<?php echo $icon_play; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo $icon_pause; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </button>
        <button type="button" class="lakitplayer_btn__next"><?php echo $icon_next; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
	</div>
	<div class="lakitplayer__control__bottom">
		<div class="lakitplayer__control_tracker">
			<div class="lakitplayer_rangewrap">
				<input class="lakitplayer_tracker__progress" type="range" min="0" max="1000" value="0"/>
			</div>
			<div class="lakitplayer__control_tracker_buffer"></div>
		</div>
		<div class="lakitplayer__control_timer">
			<span class="lakitplayer_time__start">00:00</span>
			<span class="lakitplayer_time__end">00:00</span>
		</div>
	</div>
</div>