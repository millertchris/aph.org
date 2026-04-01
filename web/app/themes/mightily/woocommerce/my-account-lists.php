<?php do_action( 'woocommerce_wishlists_before_wrapper' ); ?>
<div id="wl-wrapper" class="woocommerce">
    <h2><?php echo apply_filters( 'woocommerce_my_account_my_wishlists_title', __( 'Wishlists', 'wc_wishlist' ) ); ?></h2>
    <table class="shop_table cart wl-table wl-manage" cellspacing="0">
        <thead>
        <tr>
            <th class="product-name"><?php _e( 'List Name', 'wc_wishlist' ); ?></th>
            <th class="wl-date-added"><?php _e( 'Date Added', 'wc_wishlist' ); ?></th>
            <th class="wl-privacy-col"><?php _e( 'Privacy Settings', 'wc_wishlist' ); ?></th>
            <th class="wl-delete-col"><?php _e( 'Delete', 'wc_wishlist' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php $lists = WC_Wishlists_User::get_wishlists(); ?>
		<?php if ( $lists && count( $lists ) ) : ?>
			<?php foreach ( $lists as $list ) : ?>
				<?php $sharing = $list->get_wishlist_sharing(); ?>
                <tr class="cart_table_item">
                    <td class="product-name">
                      <div class="wishlist-title-box">
                            <p class="wishlist-title-p">
                                <span class="list-title td-titles desktop-hide"><?php _e( 'List Name', 'wc_wishlist' ); ?></span>
                                <a href="<?php $list->the_url_edit(); ?>"><?php $list->the_title(); ?></a>
                            </p>
                            <?php $wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $list->id, true ); ?>
                            <?php $wishlist_count = count($wishlist_items); ?>
                            <?php $items_label = ($wishlist_count == 1) ? 'item' : 'items'; ?>
                            <span class="list-count"><?php echo $wishlist_count . ' ' . $items_label . ' in list'; ?></span>
                      </div>
                      <div class="row-actions"></div>
          						<?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
          							<?php //woocommerce_wishlists_get_template( 'wishlist-sharing-menu.php', array( 'id' => $list->id ) ); ?>
          						<?php endif; ?>
                    </td>
                    <td class="wl-date-added"><span class="date-title td-titles desktop-hide"><?php _e( 'Date Added', 'wc_wishlist' ); ?></span><?php echo date_i18n( get_option( 'date_format' ), strtotime( $list->post->post_date ) ); ?></td>
                    <td class="wl-privacy-col">
                        <span class="privacy-title td-titles desktop-hide"><?php _e( 'Privacy Settings', 'wc_wishlist' ); ?></span>
                        <?php
                            $public_query = array(
                                'url' => '/my-account/account-wishlists/',
                                'wlaction' => 'edit-lists',
                                'sharing' => array(
                                    $list->id => 'Public'
                                ),
                                'update_wishlists' => 'Save Changes',
                                '_n' => wp_create_nonce( 'wc-wishlists-edit-lists'),
                                '_wp_http_referer' => wp_get_referer()
                            );
                            $shared_query = array(
                                'url' => '/my-account/account-wishlists/',
                                'wlaction' => 'edit-lists',
                                'sharing' => array(
                                    $list->id => 'Shared'
                                ),
                                'update_wishlists' => 'Save Changes',
                                '_n' => wp_create_nonce( 'wc-wishlists-edit-lists'),
                                '_wp_http_referer' => wp_get_referer()
                            );
                            $private_query = array(
                                'url' => '/my-account/account-wishlists/',
                                'wlaction' => 'edit-lists',
                                'sharing' => array(
                                    $list->id => 'Private'
                                ),
                                'update_wishlists' => 'Save Changes',
                                '_n' => wp_create_nonce( 'wc-wishlists-edit-lists'),
                                '_wp_http_referer' => wp_get_referer()
                            );                                                                                        
                        ?>
                        <div class="select-url-wrapper select-privacy-wrapper">
                            <img class="select-privacy-loader" src="<?php echo get_bloginfo('template_directory'); ?>/app/assets/img/loader.gif" alt="Loading Icon"/>
                            <select class="select-url select-styled select-privacy">
                                <option <?php selected( $sharing, 'Public' ); ?> value='<?php echo json_encode($public_query); ?>'><?php _e( 'Public', 'wc_wishlist' ); ?></option>
                                <option <?php selected( $sharing, 'Shared' ); ?> value='<?php echo json_encode($shared_query); ?>'><?php _e( 'Shared', 'wc_wishlist' ); ?></option>
                                <option <?php selected( $sharing, 'Private' ); ?> value='<?php echo json_encode($private_query); ?>'><?php _e( 'Private', 'wc_wishlist' ); ?></option>
                            </select>
                        </div>
			            <?php // echo $list->get_wishlist_sharing( true ); ?>
                    </td>
                    <td class="wl-delete-col">
                        <?php
                            $delete_query = array(
                                'wlid' => $list->id,
                                'wlaction' => 'delete-list',
                                '_n' => wp_create_nonce( 'wc-wishlists-delete-list'),
                            );
                        ?>
                        <a role="button" class="btn wlconfirm" data-message="<?php _e( 'Are you sure you want to delete this list?', 'wc_wishlist' ); ?>" href="?<?php echo http_build_query($delete_query); ?>"><?php _e( 'Delete List', 'wc_wishlist' ); ?></a>
                    </td>
                </tr>

				<?php
				//Registers the email form modal to be printed in the footer.
				woocommerce_wishlists_get_template( 'wishlist-email-form.php', array( 'wishlist' => $list ) );
				?>
			<?php endforeach; ?>
            <tr>

            </tr>
		<?php endif; ?>
        </tbody>
    </table>
</div><!-- /wishlist-wrapper -->
<?php do_action( 'woocommerce_wishlists_after_wrapper' ); ?>
