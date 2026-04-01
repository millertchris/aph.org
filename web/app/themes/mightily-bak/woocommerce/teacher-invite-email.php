<?php
/**
 *
 * Teacher invite template
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

<p><?php _e( 'Use the link below to register and be a part of your EOT’s shopper group and start shopping today.', 'woocommerce' ); ?></p>

<p><strong><?php __( 'Registration URL: ', 'woocommerce' ) ?></strong> <a href="<?php _e(get_home_url().'/register?group='.$group_id ); ?>" target="_blank">Register Now</a></p>

<?php
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );