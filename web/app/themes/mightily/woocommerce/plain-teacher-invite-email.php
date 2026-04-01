<?php
/**
 *
 * Welcome email content template
 *
 * The file is prone to modifications after plugin upgrade or alike; customizations are advised via hooks/filters
 *
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php _e( 'Sign up at APH to utilize your EOT funds and start making purchases today. Use the link below to sign up and be a part of your EOT&rsquo;s purchasing group.', 'woocommerce' ); ?></p>

<p><?php _e( 'Use the link below to get started', 'woocommerce' ); ?></p>

<p>
	<strong><?php __( 'Registration URL: ', 'woocommerce' ) ?></strong> <a href="<?php _e(get_home_url().'/register?group='.$group_id ); ?>" title="Register at APH" target="_blank"><?php _e(get_home_url().'/register?group='.$group_id ); ?></a>
</p>

<?php
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );