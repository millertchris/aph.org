<?php do_action( 'woocommerce_email_header', $email_heading, null ); ?>
	<p><?php esc_html_e( sprintf( 'Price drop alert from %s. Items below have been reduced in price:', get_option( 'blogname' ) ), 'wc_wishlist' ); ?></p>
<?php foreach ( $changes as $change ) : ?>
	<h2><a href="<?php echo esc_attr($change['url']); ?>"><?php esc_html_e( sprintf( 'New Low Price For: %s', $change['title'] ), 'wc_wishlist' ); ?></h2>
<?php endforeach; ?>
<?php do_action( 'woocommerce_email_footer', null ); ?>
