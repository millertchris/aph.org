<?php
global $wp_query;

// Sanitize all inputs at the top
$search_query = '';
if ( isset( $_GET['f-list'] ) ) {
	$search_query = sanitize_text_field( wp_unslash( $_GET['f-list'] ) );
}
?>

<?php do_action( 'woocommerce_wishlists_before_wrapper' ); ?>
    <div id="wl-wrapper" class="woocommerce">

		<?php if ( function_exists( 'wc_print_messages' ) ) : ?>
			<?php wc_print_messages(); ?>
		<?php else : ?>
			<?php WC_Wishlist_Compatibility::wc_print_notices(); ?>
		<?php endif; ?>

        <form class="wl-search-form" method="get">
            <label for="f-list"><?php esc_html_e( "Find Someone's List", 'wc_wishlist' ); ?></label>
            <input type="text" name="f-list" id="f-list" class="find-input" value="<?php echo esc_attr( $search_query ); ?>" placeholder="<?php esc_attr_e( 'Enter name or email', 'wc_wishlist' ); ?>"/>
            <input type="submit" class="button" value="<?php esc_html_e( 'Search', 'wc_wishlist' ); ?>"/>
        </form>
        <hr/>

		<?php if ( have_posts() ) : ?>
			<?php if ( $search_query ) : ?>
                <p class="wl-results-msg"><?php echo wp_kses_post(sprintf(__( "We've found %1\$s lists matching <strong>%2\$s</strong>", 'wc_wishlist' ), intval( $wp_query->found_posts ), esc_html( $search_query ) )); ?></p> <?php echo wp_kses_post( sprintf( __( '<a class="wl-clear-results" href="%s">Clear Results</a>', 'wc_wishlist' ), esc_url( WC_Wishlists_Pages::get_url_for( 'find-a-list' ) ) ) ); ?>
			<?php endif; ?>
            <table class="shop_table cart wl-table wl-manage wl-find-table" cellspacing="0">
                <thead>
                <tr>
                    <th class="product-name"><?php esc_html_e( 'List Name', 'wc_wishlist' ); ?></th>
                    <th class="wl-pers-name"><?php esc_html_e( 'Name', 'wc_wishlist' ); ?></th>
                    <th class="wl-date-added"><?php esc_html_e( 'Date Added', 'wc_wishlist' ); ?></th>
                </tr>
                </thead>

				<?php
				while ( have_posts() ) :
					the_post();
					$list = new WC_Wishlists_Wishlist( get_the_ID() );

					// Sanitize the list data
					$list_title = $list->get_title();
					$list_url = $list->get_url_view();
					$first_name = get_post_meta( $list->id, '_wishlist_first_name', true );
					$last_name = get_post_meta( $list->id, '_wishlist_last_name', true );
					$post_date = $list->post->post_date;
					?>
                    <tr>
                        <td><a href="<?php echo esc_url( $list_url ); ?>"><?php echo esc_html( $list_title ); ?></a></td>
                        <td><?php echo esc_html( $first_name ) . ' ' . esc_html( $last_name ); ?></td>
                        <td class="wl-date-added"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $post_date ) ) ); ?></td>
                    </tr>
				<?php endwhile; ?>
            </table>

			<?php woocommerce_wishlists_nav( 'nav-below' ); ?>

		<?php elseif ( $search_query ) : ?>
            <!-- results go down here -->
            <p><?php esc_html_e( "We're sorry, we couldn't find a list for that name. Please double check your entry and try again.", 'wc_wishlist' ); ?></p>
            <h2 class="wl-search-result"><?php esc_html_e( 'We found 0 matching lists', 'wc_wishlist' ); ?></h2>
		<?php endif; ?>
    </div>
<?php do_action( 'woocommerce_wishlists_after_wrapper' ); ?>
