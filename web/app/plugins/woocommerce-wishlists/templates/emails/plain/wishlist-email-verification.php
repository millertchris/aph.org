<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
$link = add_query_arg( array( 'wcwlemailconfirmationcode' => $email_confirmation_hash ), get_site_url() );
?>

<?php esc_html_e( 'We received your request to add your email to your wishlist. Before we begin using this email address, we want to be certain we have your permission. Confirm by visiting this link in your browser:', 'wc_wishlist' ); ?>

<?php echo esc_url( $link ); ?>

<?php esc_html_e( 'If you did not make this request you can safely ignore this email.', 'wc_wishlist' ); ?>

<?php do_action( 'woocommerce_email_footer', $email_heading, $email ); ?>
