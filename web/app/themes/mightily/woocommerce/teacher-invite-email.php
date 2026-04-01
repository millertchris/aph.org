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

<?php if($user_exists) : ?>

	<p><?php _e( 'Use the link below to accept your invitation to be a part of your EOT’s shopper group and start shopping today.', 'woocommerce' ); ?></p>
	<p><a href="<?php _e(get_home_url().'/accept-eot-invitation?group='.$group_id ); ?>" target="_blank">Accept Invitation</a></p>

<?php else : ?>

	<p><?php _e( 'Use the link below to register and be a part of your EOT’s shopper group and start shopping today.', 'woocommerce' ); ?></p>
	<p><a href="<?php _e(get_home_url().'/register?group='.$group_id ); ?>" target="_blank">Register Now</a></p>

<?php endif; ?>


<?php
/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );