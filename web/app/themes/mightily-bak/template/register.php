<?php

// Template name: Register
get_header();

?>


<?php
//$group_object = get_term('214', 'user-group');
//$group_slug = $group_object->slug;
//print_r($group_object->slug);
//wp_set_terms_for_user(33, 'user-group', array('fq-244782'));

?>
<div class="interior-page">

	<div class="registration-form woocommerce">
		<div class="wrapper">

			<?php wc_print_notices(); ?>

			<h1 class="h2"><?php _e( 'Register', 'woocommerce-simple-registration' ); ?></h1>

			<form method="post" class="register">

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="reg_username"><?php _e( 'Username', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
					</p>

				<?php endif; ?>

				<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
					<label for="reg_email"><?php _e( 'Email address', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
					<span></span>
				</p>



				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

					<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
						<label for="reg_password"><?php _e( 'Password', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
						<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" />
					</p>

				<?php endif; ?>

				<!-- Spam Trap -->
				<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'woocommerce-simple-registration' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" autocomplete="off" /></div>

				<?php do_action( 'woocommerce_register_form' ); ?>
				<?php do_action( 'woocommerce_simple_registration_form' ); ?>

				<p class="woocomerce-FormRow form-row">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<?php 
						$the_redirect = $_GET['redirect_to'];
						if($the_redirect == '/checkout') : ?>
							<input type="hidden" name="redirect_to" value="<?php echo home_url($the_redirect); ?>" />
					<?php endif; ?>
					<input type="submit" class="woocommerce-Button button" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce-simple-registration' ); ?>" />
				</p>

				<p class="woocommerce-simple-registration-login-link">
					<a href="<?php echo esc_url( home_url('my-account') ); ?>"><?php esc_html_e( 'Log in', 'woocommerce-simple-registration' ); ?></a>

				</p>

				<?php do_action( 'woocommerce_register_form_end' ); ?>

			</form>
		</div>

	</div>

</div>

<?php get_footer(); ?>
