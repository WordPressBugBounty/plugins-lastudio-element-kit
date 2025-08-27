<?php

$form_id_from_settings = $this->get_settings_for_display('form_id');

$form_id = !empty($form_id_from_settings) ? $form_id_from_settings : get_the_ID();


$form = new Give_Donate_Form($form_id);

$goal_option = give_get_meta($form->ID, '_give_goal_option', true);
$goal_progress_stats = give_goal_progress_stats($form);
$goal_format = $goal_progress_stats['format'];
$show_goal = $this->get_settings_for_display('show_text');
$show_progress_bar = $this->get_settings_for_display('show_progress_bar');

$income = $form->get_earnings();
$goal = $goal_progress_stats['raw_goal'];

switch ($goal_format) {
    case 'donation':
        $progress = $goal ? round(($form->get_sales() / $goal) * 100, 2) : 0;
        $progress_bar_value = $form->get_sales() >= $goal ? 100 : $progress;
        break;

    case 'donors':
        $progress = $goal ? round((give_get_form_donor_count($form->ID) / $goal) * 100, 2) : 0;
        $progress_bar_value = give_get_form_donor_count($form->ID) >= $goal ? 100 : $progress;
        break;

    case 'percentage':
        $progress = $goal ? round(($income / $goal) * 100, 2) : 0;
        $progress_bar_value = $income >= $goal ? 100 : $progress;
        break;

    default:
        $progress = $goal ? round(($income / $goal) * 100, 2) : 0;
        $progress_bar_value = $income >= $goal ? 100 : $progress;
        break;
}
?>
<div class="lakit-goal-progress">
    <?php
    if($show_progress_bar === 'yes'){
        echo sprintf('<div class="progress-percent">%1$s&#37;</div>', esc_html(round($progress)));
        echo sprintf('<div class="give-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%1$s"><span style="width:%2$s;"></span></div>', esc_attr($progress_bar_value), esc_attr($progress_bar_value . '%'));
    }
    if($show_goal === 'yes'){
    ?>
    <div class="raised">
        <?php
        if ('amount' === $goal_format) :

            $form_currency = apply_filters(
                'give_goal_form_currency',
                give_get_currency($form_id),
                $form_id
            );

            $income_format_args = apply_filters(
                'give_goal_income_format_args',
                [
                    'sanitize' => false,
                    'currency' => $form_currency,
                    'decimal' => false,
                ],
                $form_id
            );
            $goal_format_args = apply_filters(
                'give_goal_amount_format_args',
                [
                    'sanitize' => false,
                    'currency' => $form_currency,
                    'decimal' => false,
                ],
                $form_id
            );

            $goal_amounts = apply_filters(
                'give_goal_amounts',
                [
                    $form_currency => $goal,
                ],
                $form_id
            );
            $income_amounts = apply_filters(
                'give_goal_raised_amounts',
                [
                    $form_currency => $income,
                ],
                $form_id
            );

            // Get human readable donation amount.
            $income = give_human_format_large_amount( give_format_amount($income, $income_format_args), ['currency' => $form_currency] );
            $goal = give_human_format_large_amount( give_format_amount($goal, $goal_format_args), ['currency' => $form_currency] );

            // Format the human readable donation amount.
            $formatted_income = give_currency_filter( $income, [ 'form_id' => $form_id ] );
            $formatted_goal = give_currency_filter( $goal, [ 'form_id' => $form_id ] );

	        echo sprintf(
		        '<span class="amount">%1$s</span><span class="t-of">%3$s</span><span class="goal">%2$s</span>',
		        esc_attr($formatted_income),
		        esc_attr($formatted_goal),
		        esc_html__('of', 'lastudio-kit')
	        );

        elseif ('percentage' === $goal_format) :
	        echo sprintf(
		        '<span class="amount">%1$s&#37;</span><span class="t-of">%2$s</span><span class="goal">100&#37;</span>',
		        esc_html(round($progress)),
		        esc_html__('of', 'lastudio-kit')
	        );
        elseif ('donation' === $goal_format) :?>
            <span class="amount"><?php echo give_format_amount($form->get_sales(), ['decimal' => false]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <span class="goal"><?php echo sprintf( /* translators: %s is replaced with "string" */ _n('of %s donation', 'of %s donations', $goal, 'lastudio-kit'), give_format_amount($goal, ['decimal' => false]) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        <?php
        elseif ('donors' === $goal_format) : ?>
            <span class="amount"><?php echo give_get_form_donor_count($form->ID); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <span class="goal"><?php echo sprintf( /* translators: %s is replaced with "string" */ _n('of %s donor', 'of %s donors', $goal, 'lastudio-kit'), give_format_amount($goal, ['decimal' => false]) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        <?php
        endif ?>
    </div>
    <?php
    }
    ?>
</div>