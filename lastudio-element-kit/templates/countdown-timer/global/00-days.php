<div class="lakit-countdown-timer__item item-days">
	<div class="lakit-countdown-timer__item-value" data-value="days"><?php echo $this->date_placeholder(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php $this->_html( 'label_days', '<div class="lakit-countdown-timer__item-label">%s</div>' ); ?>
</div><?php echo $this->blocks_separator(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>