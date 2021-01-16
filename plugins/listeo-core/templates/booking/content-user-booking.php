<?php 
if(isset($data)) :

 
endif;
if($data->comment == 'owner reservations'){
	return;
} 
$class = array();
$tag = array();
$show_approve = false;
$show_reject = false;
switch ($data->status) {
	case 'waiting' :
		$class[] = 'waiting-booking';
		$tag[] = '<span class="booking-status pending">'.esc_html__('Waiting for owner confirmation', 'listeo_core').'</span>';
		$show_approve = true;
		$show_reject = true;
	break;

	case 'confirmed' :
		$class[] = 'approved-booking';
		$tag[] = '<span  class="booking-status">'.esc_html__('Approved', 'listeo_core').'</span>';
		if($data->price>0){
			$tag[] = '<span class="booking-status unpaid">'.esc_html__('Unpaid', 'listeo_core').'</span>';	
		}
		$show_approve = false;
		$show_reject = true;
	break;

	case 'paid' :

		$class[] = 'approved-booking';
		$tag[] = '<span class="booking-status">'.esc_html__('Approved', 'listeo_core').'</span>';
		if($data->price>0){
			$tag[] = '<span class="booking-status paid">'.esc_html__('Paid', 'listeo_core').'</span>';
		}
		$show_approve = false;
		$show_reject = false;
	break;

	case 'cancelled' :

		$class[] = 'canceled-booking';
		$tag[] = '<span class="booking-status">'.esc_html__('Canceled', 'listeo_core').'</span>';
		$show_approve = false;
		$show_reject = false;
		$show_delete = true;
	break;
	
	default:
		# code...
		break;
}
//$payment_url = $order->get_checkout_payment_url();


//get order data
if(isset($data->order_id) && !empty($data->order_id) && $data->status == 'confirmed'){
	$order = wc_get_order( $data->order_id );
	if($order) {
		$payment_url = $order->get_checkout_payment_url();
	
		$order_data = $order->get_data();

		$order_status = $order_data['status'];
	}
	if (new DateTime() > new DateTime($data->expiring) ) {
   	 $payment_url = false;
   	 $class[] = 'expired-booking';
   	 unset($tag[1]);
   	 $tag[] = '<span class="booking-status">'.esc_html__('Expired', 'listeo_core').'</span>';
   	 $show_delete = true;
	}
}

?>
<li class="user-booking <?php echo implode(' ',$class); ?>" id="booking-list-<?php echo esc_attr($data->ID);?>">
	<div class="list-box-listing bookings">
		<div class="list-box-listing-img"><?php echo get_avatar($data->bookings_author, '70') ?></div>
		<div class="list-box-listing-content">
			<div class="inner">
				<h3 id="title"><a href="<?php echo get_permalink($data->listing_id); ?>"><?php echo get_the_title($data->listing_id); ?></a> <?php echo implode(' ',$tag); ?></h3>

				<div class="inner-booking-list">
					<h5><?php esc_html_e('Booking Date:', 'listeo_core'); ?></h5>
					<ul class="booking-list">
						<?php 
						//get post type to show proper date
						$listing_type = get_post_meta($data->listing_id,'_listing_type', true);

						if($listing_type == 'rental') { ?>
							<li class="highlighted" id="date"><?php echo date_i18n(get_option( 'date_format' ), strtotime($data->date_start)); ?> - <?php echo date_i18n(get_option( 'date_format' ), strtotime($data->date_end)); ?></li>
						
						<?php } 
							else if($listing_type == 'service') { 
						?>
							<li class="highlighted" id="date">
								<?php echo date_i18n(get_option( 'date_format' ), strtotime($data->date_start)); ?> <?php esc_html_e('at','listeo_core'); ?> 
								<?php 
									$time_start = date_i18n(get_option( 'time_format' ), strtotime($data->date_start));
									$time_end = date_i18n(get_option( 'time_format' ), strtotime($data->date_end));?>

								<?php echo $time_start ?> <?php if($time_start != $time_end) echo '- '.$time_end; ?></li>
						
						<?php } else { 
							//event ?>
							<li class="highlighted" id="date">
							<?php 
							$meta_value = get_post_meta($data->listing_id,'_event_date',true);
							$meta_value_date = explode(' ', $meta_value,2); 

							$meta_value_date[0] = str_replace('/','-',$meta_value_date[0]);
							$meta_value = date_i18n(get_option( 'date_format' ), strtotime($meta_value_date[0])); 
							
						
							//echo strtotime(end($meta_value_date));
							//echo date( get_option( 'time_format' ), strtotime(end($meta_value_date)));
							if( isset($meta_value_date[1]) ) { 
								$time = str_replace('-','',$meta_value_date[1]);
								$meta_value .= esc_html__(' at ','listeo_core'); 
								$meta_value .= date_i18n(get_option( 'time_format' ), strtotime($time));

							} echo $meta_value;

							$meta_value = get_post_meta($data->listing_id,'_event_date_end',true);
							if(isset($meta_value) && !empty($meta_value))  : 
							
							$meta_value_date = explode(' ', $meta_value,2); 

							$meta_value_date[0] = str_replace('/','-',$meta_value_date[0]);
							$meta_value = date_i18n(get_option( 'date_format' ), strtotime($meta_value_date[0])); 
							
						
							//echo strtotime(end($meta_value_date));
							//echo date( get_option( 'time_format' ), strtotime(end($meta_value_date)));
							if( isset($meta_value_date[1]) ) { 
								$time = str_replace('-','',$meta_value_date[1]);
								$meta_value .= esc_html__(' at ','listeo_core'); 
								$meta_value .= date_i18n(get_option( 'time_format' ), strtotime($time));

							} echo ' - '.$meta_value; ?>
							<?php endif; ?>
							</li>
						<?php }
						 ?>

					</ul>
				</div>
				<?php $details = json_decode($data->comment); 
				if (
				 	(isset($details->childrens) && $details->childrens > 0)
				 	||
				 	(isset($details->adults) && $details->adults > 0)
				 	||
				 	(isset($details->tickets) && $details->tickets > 0)
				) { ?>			
				<div class="inner-booking-list">
					<h5><?php esc_html_e('Booking Details:', 'listeo_core'); ?></h5>
					<ul class="booking-list">
						<li class="highlighted" id="details">
						<?php if( isset($details->childrens) && $details->childrens > 0) : ?>
							<?php printf( _n( '%d Child', '%s Children', $details->childrens, 'listeo_core' ), $details->childrens ) ?>
						<?php endif; ?>
						<?php if( isset($details->adults)  && $details->adults > 0) : ?>
							<?php printf( _n( '%d Guest', '%s Guests', $details->adults, 'listeo_core' ), $details->adults ) ?>
						<?php endif; ?>
						<?php if( isset($details->tickets)  && $details->tickets > 0) : ?>
							<?php printf( _n( '%d Ticket', '%s Tickets', $details->tickets, 'listeo_core' ), $details->tickets ) ?>
						<?php endif; ?>
						</li>
					</ul>
				</div>	
				<?php } ?>	
				
				<?php $address = get_post_meta( $data->listing_id, '_address', true ); 
				if($address) { ?>
				<div class="inner-booking-list">
					<h5><?php esc_html_e('Booking Location:', 'listeo_core'); ?></h5>
					<ul class="booking-list">
						<?php echo $address; ?>
						
					</ul>
				</div>
				<?php 
				}
				
				$currency_abbr = get_option( 'listeo_currency' );
				$currency_postion = get_option( 'listeo_currency_postion' );
				$currency_symbol = Listeo_Core_Listing::get_currency_symbol($currency_abbr);
				$decimals = get_option('listeo_number_decimals',2);

				if($data->price): ?>
				<div class="inner-booking-list">
					<h5><?php esc_html_e('Price:', 'listeo_core'); ?></h5>
					<ul class="booking-list">
						<li class="highlighted" id="price">
							<?php if($currency_postion == 'before') { echo $currency_symbol.' '; }  ?>
							<?php 
							if(is_numeric($data->price)){
							 	echo number_format_i18n($data->price,$decimals);
							} else {
								echo $data->price;
							}; 
							
							 ?>
							<?php if($currency_postion == 'after') { echo ' '.$currency_symbol; }  ?></li>
					</ul>
				</div>	
				<?php endif; ?>	
				
				<div class="inner-booking-list">
					
					<h5><?php esc_html_e('Client:', 'listeo_core'); ?></h5>
					<ul class="booking-list" id="client">
						<?php if( isset($details->first_name) || isset($details->last_name) ) : ?>
						<li id="name"><?php if(isset($details->first_name)) echo stripslashes($details->first_name); ?> <?php if(isset($details->last_name)) echo stripslashes($details->last_name); ?></li>
						<?php endif; ?>
						<?php if( isset($details->email)) : ?><li id="email"><a href="mailto:<?php echo esc_attr($details->email) ?>"><?php echo $details->email; ?></a></li>
						<?php endif; ?>
						<?php if( isset($details->phone)) : ?><li id="phone"><a href="tel:<?php echo esc_attr($details->phone) ?>"><?php echo $details->phone; ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
				<?php if( isset($details->billing_address_1) ) : ?>
				<div class="inner-booking-list">
					
					<h5><?php esc_html_e('Address:', 'listeo_core'); ?></h5>
					<ul class="booking-list" id="client">
		
						<?php if( isset($details->billing_address_1) ) : ?>
							<li id="billing_address_1"><?php echo stripslashes($details->billing_address_1); ?> </li>
						<?php endif; ?>
						<?php if( isset($details->billing_address_1) ) : ?>
							<li id="billing_postcode"><?php echo stripslashes($details->billing_postcode); ?> </li>
						<?php endif; ?>	
						<?php if( isset($details->billing_city) ) : ?>
							<li id="billing_city"><?php echo stripslashes($details->billing_city); ?> </li>
						<?php endif; ?>
						<?php if( isset($details->billing_country) ) : ?>
							<li id="billing_country"><?php echo stripslashes($details->billing_country); ?> </li>
						<?php endif; ?>
						
					</ul>
				</div>
				<?php endif; ?>
				<?php if( isset($details->service) && !empty($details->service)) : ?>
					<div class="inner-booking-list">
						<h5><?php esc_html_e('Extra Services:', 'listeo_core'); ?></h5>
						<?php echo listeo_get_extra_services_html($details->service); //echo wpautop( $details->service); ?>
					</div>	
				<?php endif; ?>
				<?php if( isset($details->message) && !empty($details->message)) : ?>
					<div class="inner-booking-list">
						<h5><?php esc_html_e('Message:', 'listeo_core'); ?></h5>
						<?php echo wpautop( stripslashes($details->message)); ?>
					</div>	
				<?php endif; ?>

				<div class="inner-booking-list">
					<h5><?php esc_html_e('Booking requested on:', 'listeo_core'); ?></h5>
					<ul class="booking-list">
						<li class="highlighted" id="price">
							<?php echo date_i18n(get_option( 'date_format' ), strtotime($data->created)); ?>
							<?php 
								$date_created = explode(' ', $data->created); 
									if( isset($date_created[1]) ) { ?>
									<?php esc_html_e('at','listeo_core'); ?>
									
							<?php echo date_i18n(get_option( 'time_format' ), strtotime($date_created[1])); } ?>
						</li>
					</ul>
				</div>	

				<a href="#small-dialog" data-recipient="<?php echo esc_attr($data->owner_id); ?>" data-booking_id="booking_<?php echo esc_attr($data->ID); ?>" class="booking-message rate-review popup-with-zoom-anim"><i class="sl sl-icon-envelope-open"></i> <?php esc_attr_e('Send Message','listeo_core') ?></a>

				

			</div>
		</div>
	</div>
	<div class="buttons-to-right">
		<?php 

		if(isset($payment_url) && !empty($payment_url) && !get_option('listeo_disable_payments') ) :
			if($order_status != 'completed') : ?>
			<a href="<?php echo esc_url($payment_url) ?>" class="button green pay"><i class="sl sl-icon-check"></i> <?php esc_html_e('Pay', 'listeo_core'); ?></a>
		<?php endif; 
		endif; ?>
		<?php if(isset($show_delete) && $show_delete == true) : ?>
			<a href="#" class="button gray delete" data-booking_id="<?php echo esc_attr($data->ID); ?>"><i class="sl sl-icon-trash"></i> <?php esc_html_e('Delete', 'listeo_core'); ?></a>
		<?php endif; ?>
		<?php if($show_reject) : ?>
		<a href="#" class="button gray reject" data-booking_id="<?php echo esc_attr($data->ID); ?>"><i class="sl sl-icon-close"></i> <?php esc_html_e('Cancel', 'listeo_core'); ?></a>
		<?php endif; ?>
		
	</div>
</li>