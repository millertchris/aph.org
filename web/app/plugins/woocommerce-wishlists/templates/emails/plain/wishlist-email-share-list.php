<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php
$name = $name ? $name : get_post_meta( $wishlist->id, '_wishlist_first_name', true ) . ' ' . get_post_meta( $wishlist->id, '_wishlist_last_name', true );
$name = $name ? $name : __( 'Someone', 'wc_wishlist' );

// phpcs:disable WordPress.Security.NonceVerification.Missing
if ( isset( $_POST['wishlist_email_from'] ) ) {
    $name = sanitize_text_field( wp_unslash( $_POST['wishlist_email_from'] ) );
}
// phpcs:enable WordPress.Security.NonceVerification.Missing
?>

<?php echo esc_html( $name ); ?> <?php esc_html_e( 'has a wishlist to share on', 'wc_wishlist' ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?> (<?php echo esc_url( get_site_url() ); ?>)

<?php
// Rest of your template...
