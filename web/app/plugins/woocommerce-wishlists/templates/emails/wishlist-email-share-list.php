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

    <p>
        <?php echo esc_html( $name ); ?>
        <?php esc_html_e( 'has a wishlist to share on', 'wc_wishlist' ); ?>
        <a href="<?php echo esc_url( get_site_url() ); ?>"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></a>
    </p>

<?php
$wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist->id );

if ( count( $wishlist_items ) > 0 ) :
    ?>

    <ul>
        <?php
        foreach ( $wishlist_items as $wishlist_item_key => $item ) :
            $_product     = wc_get_product( $item['data'] );
            $product_url  = is_array( $item['variation'] ) ? add_query_arg( $item['variation'], $_product->get_permalink() ) : $_product->get_permalink();
            $product_link = sprintf( '<a href="%s">%s</a>', esc_url( $product_url ), esc_html( $_product->get_name() ) );
            $product_name = apply_filters( 'woocommerce_cart_item_name', $product_link, $item, $wishlist_item_key );
            $price        = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $item, $wishlist_item_key );
            $price        = apply_filters( 'woocommerce_wishlist_list_item_price', $price, $item, $wishlist );
            $quantity     = apply_filters( 'woocommerce_wishlist_list_item_quantity_value', absint( $item['quantity'] ), $item, $wishlist );
            ?>

            <li>
                <?php echo wp_kses_post( $product_name ); ?>
                <?php echo ', ' . esc_html__( 'Price:', 'wc_wishlist' ) . ' ' . wp_kses_post( $price ); ?>
                <?php echo ' ' . esc_html__( 'Quantity:', 'wc_wishlist' ) . ' ' . esc_html( $quantity ); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ( $additional_content ) : ?>
    <?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?>
<?php endif; ?>

    <p>
        <?php esc_html_e( 'You can view this list by clicking on the link or copy and pasting it into your browser.', 'wc_wishlist' ); ?>
        <br/>
        <?php esc_html_e( 'View List:', 'wc_wishlist' ); ?>
        <a href="<?php echo esc_url( $wishlist->get_the_url_view( $wishlist->id, true ) ); ?>">
            <?php echo esc_html( $wishlist->get_the_url_view( $wishlist->id, true ) ); ?>
        </a>
    </p>

<?php do_action( 'woocommerce_email_footer', $email_heading, $email ); ?>
