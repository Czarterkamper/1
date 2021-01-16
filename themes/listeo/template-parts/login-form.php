<ul class="tabs-nav">
	<li class=""><a href="#tab1"><?php esc_html_e('Log In','listeo'); ?></a></li>
	<li><a href="#tab2"><?php esc_html_e('Register','listeo'); ?></a></li>
</ul>

<div class="tabs-container alt">

	<!-- Login -->
	<div class="tab-content" id="tab1" style="display: none;">
			<?php

			/*WPEngine compatibility*/
			if (defined('PWP_NAME')) { ?>
				<form method="post" id="login" class="login" action="<?php echo wp_login_url().'?wpe-login=';echo PWP_NAME;?>">
			<?php } else { ?>
				<form method="post" id="login"  class="login" action="<?php echo wp_login_url(); ?>">
			<?php } ?>
				<div class="notification error closeable" style="display: none">
					<p></p>	
				</div>
				<?php do_action( 'listeo_before_login_form' ); ?>
				<p class="form-row form-row-wide">
					<label for="user_login">
						<i class="im im-icon-Male"></i>
						<input placeholder="<?php esc_attr_e( 'Username/Email', 'listeo' ); ?>" type="text" class="input-text" name="log" id="user_login" value="" />
					</label>
				</p>
				

				<p class="form-row form-row-wide">
					<label for="user_pass">
						<i class="im im-icon-Lock-2"></i>
						<input placeholder="<?php esc_attr_e( 'Password', 'listeo' ); ?>" class="input-text" type="password" name="pwd" id="user_pass"/>
					</label>
					<span class="lost_password">
						<a href="<?php echo wp_lostpassword_url(); ?>" ><?php esc_html_e( 'Lost Your Password?', 'listeo' ); ?></a>
					</span>
				</p>

				<div class="form-row">
					<?php wp_nonce_field( 'listeo-ajax-login-nonce', 'login_security' ); ?>
					<input type="submit" class="button border margin-top-5" name="login" value="Login" />
					<div class="checkboxes margin-top-10">
						<input name="rememberme" type="checkbox" id="remember-me" value="forever" /> 
						<label for="remember-me"><?php esc_html_e( 'Remember Me', 'listeo' ); ?></label>

					</div>
				</div>
			</form>
	</div>

	<!-- Register -->
	<div class="tab-content" id="tab2" style="display: none;">	
		<?php
		if ( !get_option('users_can_register') ) : ?>
				<div class="notification error closeable" style="display: block">
					<p><?php esc_html_e( 'Registration is disabled', 'listeo' ) ?></p>	
				</div>
		<?php else :
			/*WPEngine compatibility*/
			if (defined('PWP_NAME')) { ?>
				<form class="register" id="register" action="<?php echo wp_registration_url().'&wpe-login=';echo PWP_NAME; ?>" method="post">
			<?php } else { ?>
				<form class="register" id="register" action="<?php echo wp_registration_url(); ?>" method="post">
			<?php } ?>	
				<div class="notification error closeable" style="display: none">
							<p></p>	
				</div>
			
				<div class="account-type">
					<div>
						<input type="radio" name="user_role" id="freelancer-radio" value="guest" class="account-type-radio" checked/>
						<label for="freelancer-radio"><i class="sl sl-icon-user"></i> <?php esc_html_e('Guest','listeo') ?></label>
					</div>

					<div>
						<input type="radio" name="user_role" id="employer-radio" value="owner" class="account-type-radio"/>
						<label for="employer-radio" ><i class="sl sl-icon-briefcase"></i> <?php esc_html_e('Owner','listeo') ?></label>
					</div>
				</div>
				<div class="clearfix"></div>
				<?php if(!get_option('listeo_registration_hide_username')) : ?>
					<p class="form-row form-row-wide">
						<label for="username2">
							<i class="im im-icon-Male"></i>
							<input placeholder="<?php esc_html_e( 'Username', 'listeo' ); ?>" type="text" class="input-text" name="username" id="username2" value="" />
						</label>
					</p>
				<?php endif; ?>
				
				<?php if(get_option('listeo_display_password_field')) : ?>
				<p class="form-row form-row-wide">
					<label for="password1">
						<i class="im im-icon-Lock-2"></i>
						<input placeholder="<?php esc_html_e( 'Password', 'listeo' ); ?>" class="input-text" type="password" name="password" id="password1"/>
					</label>
				</p>
				<?php endif; ?>

				<?php if(get_option('listeo_display_first_last_name')) : ?>
				<p class="form-row form-row-wide">
					<label for="first-name">
					<i class="im im-icon-Pen"></i>
		            <input placeholder="<?php esc_html_e( 'First Name', 'listeo' ); ?>" type="text" name="first_name" id="first-name"></label>
		        </p>
		 
		        <p class="form-row form-row-wide">
		        	<label for="last-name">
		        	<i class="im im-icon-Pen"></i>
		            <input placeholder="<?php esc_html_e( 'Last Name', 'listeo' ); ?>" type="text" name="last_name" id="last-name">
		        	</label>
		        </p>	
		        <?php endif; ?>

				<p class="form-row form-row-wide">
					<label for="email">
						<i class="im im-icon-Mail"></i>
						<input type="text" placeholder="<?php esc_html_e( 'Email Address', 'listeo' ); ?>" class="input-text" name="email" id="email" value="" />
					</label>
				</p>
				
				<?php if(!get_option('listeo_display_password_field')) : ?>
				<p class="form-row form-row-wide margin-top-30 margin-bottom-30"><?php esc_html_e( 'Note: Your password will be generated automatically and sent to your email address.', 'listeo' ); ?>
		        </p>
		        <?php endif; ?>
				<?php $recaptcha_status = get_option('listeo_recaptcha');
	            	if($recaptcha_status) { ?>
			        <div class="form-row captcha_wrapper">
						<div class="g-recaptcha" data-sitekey="<?php echo get_option('listeo_recaptcha_sitekey'); ?>"></div>
					</div>
		 		<?php } ?>	
				<?php 
			 		 $privacy_policy_status = get_option('listeo_privacy_policy');

	 				if ( $privacy_policy_status && function_exists( 'the_privacy_policy_link' ) ) { ?>
						<p class="form-row margin-top-10 margin-bottom-10">
				            <label for="privacy_policy"><input type="checkbox" id="privacy_policy" name="privacy_policy"><?php esc_html_e( 'I agree to the', 'listeo' ); ?> <a href="<?php echo get_privacy_policy_url(); ?>"><?php esc_html_e( 'Privacy Policy', 'listeo' ); ?></a>    </label>
				        
				        </p>
						        
				<?php } ?>
				<?php wp_nonce_field( 'listeo-ajax-login-nonce', 'register_security' ); ?>
				<input type="submit" class="button border fw margin-top-10" name="register" value="<?php esc_html_e( 'Register', 'listeo' ); ?>" />

			</form>
		<?php endif; ?>
	</div>

</div>
				