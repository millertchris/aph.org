<?php do_action( 'woocommerce_wishlists_before_wrapper' ); ?>
<?php $lists = WC_Wishlists_User::get_wishlists(); ?>
<div id="wl-wrapper" class="woocommerce">

	<?php if ( function_exists( 'wc_print_messages' ) ) : ?>
		<?php wc_print_messages(); ?>
	<?php else : ?>
		<?php WC_Wishlist_Compatibility::wc_print_notices(); ?>
	<?php endif; ?>

	<?php $max_list_count = apply_filters( 'wc_wishlists_max_user_list_count', '*' ); ?>
	<?php if ( $max_list_count === '*' || ( empty( $lists ) || count( $lists ) < $max_list_count ) ) : ?>
		<div class="wl-row">
			<a href="<?php esc_attr_e( WC_Wishlists_Pages::get_url_for( 'create-a-list' ) ); ?>" class="button alt wl-create-new"><?php esc_html_e( 'Create a New List', 'wc_wishlist' ); ?></a>
		</div>
	<?php endif; ?>

	<?php if ( $lists && count( $lists ) ) : ?>
		<form method="post">

			<?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo WC_Wishlists_Plugin::nonce_field( 'edit-lists' );
			?>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo WC_Wishlists_Plugin::action_field( 'edit-lists' );
			?>


			<table class="shop_table cart wl-table wl-manage" cellspacing="0">
				<thead>
				<tr>
					<th class="product-name"><?php esc_html_e( 'List Name', 'wc_wishlist' ); ?></th>
					<th class="wl-date-added"><?php esc_html_e( 'Date Added', 'wc_wishlist' ); ?></th>
					<th class="wl-privacy-col"><?php esc_html_e( 'Privacy Settings', 'wc_wishlist' ); ?></th>
				</tr>
				</thead>
				<tbody>

				<?php foreach ( $lists as $list ) : ?>
					<?php
					$sharing = $list->get_wishlist_sharing();
					?>

					<tr class="cart_table_item">
						<td class="product-name">
							<strong><a href="<?php echo esc_attr( $list->get_the_url_edit( $list->id ) ); ?>"><?php $list->the_title(); ?></a></strong>
							<div class="row-actions">
									<span class="edit">
										<small><a rel="nofollow" href="<?php $list->the_url_edit(); ?>"><?php esc_html_e( 'Manage this list', 'wc_wishlist' ); ?></a></small>
									</span>
								|
								<span class="trash">
										<small><a rel="nofollow" class="ico-delete wlconfirm" data-message="<?php esc_attr_e( 'Are you sure you want to delete this list?', 'wc_wishlist' ); ?>" href="<?php $list->the_url_delete(); ?>"><?php esc_html_e( 'Delete', 'wc_wishlist' ); ?></a></small>
									</span>
								<?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
									|
									<span class="view">
											<small><a rel="nofollow" href="<?php $list->the_url_view(); ?>&preview=true"><?php esc_html_e( 'Preview', 'wc_wishlist' ); ?></a></small>
										</span>
								<?php endif; ?>
							</div>
							<?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
								<?php woocommerce_wishlists_get_template( 'wishlist-sharing-menu.php', array( 'id' => $list->id ) ); ?>
							<?php endif; ?>
						</td>
						<td class="wl-date-added"><?php esc_html( date_i18n( get_option( 'date_format' ), strtotime( $list->post->post_date ) ) ); ?></td>
						<td class="wl-privacy-col">
							<select class="wl-priv-sel" name="sharing[<?php echo absint($list->id); ?>]">
								<option <?php selected( $sharing, 'Public' ); ?> value="Public"><?php esc_html_e( 'Public', 'wc_wishlist' ); ?></option>
								<option <?php selected( $sharing, 'Shared' ); ?> value="Shared"><?php esc_html_e( 'Shared', 'wc_wishlist' ); ?></option>
								<option <?php selected( $sharing, 'Private' ); ?> value="Private"><?php esc_html_e( 'Private', 'wc_wishlist' ); ?></option>
							</select>

						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<td colspan="2">&nbsp;</td>
					<td class="actions">
						<input type="submit" class="button wl-but" name="update_wishlists" value="<?php esc_html_e( 'Save Changes', 'wc_wishlist' ); ?>"/>
					</td>
				</tr>

				</tbody>
			</table>
		</form>
	<?php else : ?>
		<?php $shop_url = get_permalink( WC_Wishlist_Compatibility::wc_get_page_id( 'shop' ) ); ?>
		<?php esc_html_e( 'You have not created any lists yet.', 'wc_wishlist' ); ?>
		<a href="<?php echo esc_attr( $shop_url ); ?>"><?php esc_html_e( 'Go shopping to create one.', 'wc_wishlist' ); ?></a>
	<?php endif; ?>

	<?php
	if ( $lists && count( $lists ) ) :
		foreach ( $lists as $list ) :
			$sharing = $list->get_wishlist_sharing();
			if ( 'Public' === $sharing || 'Shared' === $sharing ) :
				woocommerce_wishlists_get_template( 'wishlist-email-form.php', array( 'wishlist' => $list ) );
			endif;
		endforeach;
	endif;
	?>
</div><!-- /wishlist-wrapper -->
<?php
/** After wishlist wrapper hook. */
do_action( 'woocommerce_wishlists_after_wrapper' ); ?>
