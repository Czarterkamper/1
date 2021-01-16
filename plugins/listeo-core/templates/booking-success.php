<?php
if(isset($data->order_id) && $data->order_id ) {
	
	$order = wc_get_order( $data->order_id );
	
	if($order){
		$payment_url = $order->get_checkout_payment_url();	
		$total_value = 	$order->get_total();
	}
	
}

//echo '<div class="notification closeable success">' . $data->message . '</div>';
if(isset($data->error) && $data->error == true){  ?>
	<div class="booking-confirmation-page booking-confrimation-error">
	<i class="fa fa-exclamation-circle"></i>
	<h2 class="margin-top-30"><?php esc_html_e('Oops, we have some problem.','listeo_core'); ?></h2>
	<p><?php echo  $data->message  ?></p>
</div>

<?php } else { ?>
<div class="booking-confirmation-page">
	<i class="fa fa-check-circle"></i>
	<h2 class="margin-top-30"><?php esc_html_e('Thank you for your booking!','listeo_core'); ?></h2>
	<p><?php echo  $data->message  ?></p>

	<?php 
	if(isset($payment_url) && $total_value > 0) { 
		if(!get_option('listeo_disable_payments')){?>
		<a href="<?php echo esc_url($payment_url); ?>" class="button color"><?php esc_html_e('Pay now','listeo_core'); ?></a>
	<?php } 
	}?>

	<?php $user_bookings_page = get_option('listeo_user_bookings_page');  
	if( $user_bookings_page ) : ?>
	<a href="<?php echo esc_url(get_permalink($user_bookings_page)); ?>" class="button"><?php esc_html_e('Go to My Bookings','listeo_core'); ?></a>
	<?php endif; ?>
</div>
<?php } ?>

