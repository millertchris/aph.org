<?php

// Sanitize GET parameters at the top
$wlid     = isset( $_GET['wlid'] ) ? sanitize_key( $_GET['wlid'] ) : '';
$wishlist = WC_Wishlists_Wishlist::get_wishlist( $wlid );
$current_owner_key = WC_Wishlists_User::get_wishlist_key();
$sharing           = $wishlist->get_wishlist_sharing();
$sharing_key       = $wishlist->get_wishlist_sharing_key();
$wl_owner          = $wishlist->get_wishlist_owner();

$notifications = get_post_meta( $wishlist->id, '_wishlist_owner_notifications', true );
if ( empty( $notifications ) ) {
	$notifications = 'yes';
}

$wishlist_items = WC_Wishlists_Wishlist_Item_Collection::get_items( $wishlist->id, true );
// only include the items where the product is published
$wishlist_items = array_filter(
	$wishlist_items,
	function ( $item ) {
		$product = wc_get_product( $item['product_id'] );
		if ( $product ) {
			return $product->get_status() == 'publish';
		}

		return false;
	}
);

$treat_as_registry = false;
?>

<?php
if ( $wl_owner != WC_Wishlists_User::get_wishlist_key() && ! current_user_can( 'manage_woocommerce' ) ) :
	die();
endif;
?>

<?php do_action( 'woocommerce_wishlists_before_wrapper' ); ?>
<div id="wl-wrapper" class="product woocommerce"> <!-- product class so woocommerce stuff gets applied in tabs -->

		<?php if ( function_exists( 'wc_print_messages' ) ) : ?>
			<?php wc_print_messages(); ?>
		<?php else : ?>
			<?php WC_Wishlist_Compatibility::wc_print_notices(); ?>
		<?php endif; ?>

        <div class="wl-intro">
            <h2 class="entry-title"><?php $wishlist->the_title(); ?></h2>
            <div class="wl-intro-desc">
				<?php $wishlist->the_content(); ?>
            </div>
			<?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
                <div class="wl-share-url">
                    <strong><?php esc_html_e( 'Wishlist URL:', 'wc_wishlist' ); ?> </strong><?php echo esc_url( WC_Wishlists_Wishlist::get_the_url_view( $wishlist->id, $sharing === 'Shared' ) ); ?>
                </div>
			<?php endif; ?>
			<?php if ( $sharing == 'Public' || $sharing == 'Shared' ) : ?>
				<?php if ( $wishlist_items && count( $wishlist_items ) ) : ?>
                    <div class="wl-meta-share">
						<?php woocommerce_wishlists_get_template( 'wishlist-sharing-menu.php', [ 'id' => $wishlist->id ] ); ?>
                    </div>
				<?php endif; ?>
			<?php endif; ?>

            <p>
                <a class="wlconfirm"
                   data-message="<?php esc_attr_e( 'Are you sure you want to delete this list?', 'wc_wishlist' ); ?>"
                   href="<?php $wishlist->the_url_delete(); ?>"><?php esc_html_e( 'Delete list', 'wc_wishlist' ); ?></a>
				<?php if ( ( $sharing == 'Public' || $sharing == 'Shared' ) && count( $wishlist_items ) ) : ?>
                    |
                    <a rel="nofollow"
                       href="<?php $wishlist->the_url_view(); ?>&preview=true"><?php esc_html_e( 'Preview List', 'wc_wishlist' ); ?></a>
				<?php endif; ?>
            </p>
        </div>

        <div class="wl-tab-wrap woocommerce-tabs">

            <ul class="wl-tabs tabs">
                <li class="wl-items-tab"><a href="#tab-wl-items"><?php esc_html_e( 'List Items', 'wc_wishlist' ); ?></a>
                </li>
                <li class="wl-settings-tab"><a
                            href="#tab-wl-settings"><?php esc_html_e( 'Settings', 'wc_wishlist' ); ?></a>
                </li>
            </ul>

        <div class="wl-panel panel" id="tab-wl-items">
			<?php if ( sizeof( $wishlist_items ) > 0 ) : ?>
                <form action="<?php $wishlist->the_url_edit(); ?>" method="post" class="wl-form" id="wl-items-form">
                    <input type="hidden" name="wlid" value="<?php echo esc_attr($wishlist->id); ?>"/>
					<?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo WC_Wishlists_Plugin::nonce_field( 'manage-list' ); ?>

					<?php
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo WC_Wishlists_Plugin::action_field( 'manage-list' ); ?>
                    <input type="hidden" name="wlmovetarget" id="wlmovetarget" value="0"/>

                        <div class="wl-row">
                            <table width="100%" cellpadding="0" cellspacing="0" class="wl-actions-table">
                                <tbody>
                                <tr>
                                    <td>
                                        <select class="wl-sel move-list-sel" name="wlupdateaction" id="wleditaction1">
                                            <option selected="selected"><?php esc_html_e( 'Actions', 'wc_wishlist' ); ?></option>
                                            <option value="quantity"><?php esc_html_e( 'Update Quantities', 'wc_wishlist' ); ?></option>
											<?php if ( ! class_exists( 'WC_Catalog_Visibility_Options' ) ) : ?>
                                                <option value="add-to-cart"><?php esc_html_e( 'Add to Cart', 'wc_wishlist' ); ?></option>
                                                <option value="quantity-add-to-cart"><?php esc_html_e( 'Update Quantities and Add to Cart', 'wc_wishlist' ); ?></option>
											<?php endif; ?>
                                            <option value="remove"><?php esc_html_e( 'Remove from List', 'wc_wishlist' ); ?></option>
                                            <optgroup
                                                    label="<?php esc_attr_e( 'Move to another List', 'wc_wishlist' ); ?>">
												<?php $lists = WC_Wishlists_User::get_wishlists(); ?>
												<?php if ( $lists && count( $lists ) ) : ?>
													<?php foreach ( $lists as $list ) : ?>
														<?php if ( $list->id != $wishlist->id ) : ?>
                                                            <option value="<?php echo esc_attr( $list->id ); ?>"><?php $list->the_title(); ?>
                                                                ( <?php echo esc_html( $wishlist->get_wishlist_sharing( true ) ); ?>
                                                                )
                                                            </option>
														<?php endif; ?>
													<?php endforeach; ?>
												<?php endif; ?>
                                                <option value="create"><?php esc_html_e( '+ Create A New List', 'wc_wishlist' ); ?></option>
                                            </optgroup>
                                        </select>
                                    <td>
                                        <button class="button small wl-but wl-add-to btn-apply"><?php esc_html_e( 'Apply Action', 'wc_wishlist' ); ?></button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div><!-- wl-row wl-clear -->

                        <table class="cart wl-table manage  shop_table shop_table_responsive" cellspacing="0">
                            <thead>
                            <tr>
                                <th class="check-column"><input type="checkbox" name="" value=""/></th>
                                <th class="product-remove">&nbsp;</th>
                                <th class="product-thumbnail">&nbsp;</th>
                                <th class="product-name"><?php esc_html_e( 'Product', 'wc_wishlist' ); ?></th>
                                <th class="product-price"><?php esc_html_e( 'Price', 'wc_wishlist' ); ?></th>
                                <th class="product-quantity ctr"><?php esc_html_e( 'Qty', 'wc_wishlist' ); ?></th>
								<?php if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_show_previously_ordered', 'no' ) == 'yes' ) : ?>
                                    <th class="product-quantity ctr"><?php echo esc_html( apply_filters( 'wc_wishlist_show_previously_ordered_column_heading', WC_Wishlists_Settings::get_setting( 'wc_wishlist_show_previously_ordered_column_heading', __( 'Ordered', 'wc_wishlist' ) ) ) ); ?></th>
								<?php endif; ?>
								<?php if ( ( apply_filters( 'woocommerce_wishlist_purchases_enabled', true, $wishlist ) ) ) : ?>
                                    <th></th>
								<?php endif; ?>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							foreach ( $wishlist_items as $wishlist_item_key => $item ) {

								//$_product   = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $wishlist_item_key );
								$product_id = apply_filters( 'woocommerce_cart_item_product_id', $item['product_id'], $item, $wishlist_item_key );
								$_product   = wc_get_product( $item['data'] );
								if ( $_product->exists() && $item['quantity'] > 0 ) {
									$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $item ) : '', $item, $wishlist_item_key );

									?>
                                    <tr class="cart_table_item">
                                        <td class="check-column">
                                            <label aria-label="">
                                                <input type="checkbox" name="wlitem[]"
                                                       value="<?php echo esc_attr( $wishlist_item_key ); ?>"/>
                                            </label>
                                        </td>
                                        <td class="product-remove">
                                            <a rel="nofollow"
                                               href="<?php echo esc_url( woocommerce_wishlist_url_item_remove( $wishlist->id, $wishlist_item_key ) ); ?>"
                                               class="remove wlconfirm"
                                               title="<?php esc_attr_e( 'Remove this item from your wishlist', 'wc_wishlist' ); ?>"
                                               data-message="<?php esc_attr_e( 'Are you sure you would like to remove this item from your list?', 'wc_wishlist' ); ?>">&times;</a>
                                        </td>

                                        <!-- The thumbnail -->
                                        <td class="product-thumbnail">
											<?php
											$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $item, $wishlist_item_key );
											if ( ! $product_permalink ) {
												echo wp_kses_post( $thumbnail );
											} else {
												?>
                                                <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo wp_kses_post( $thumbnail ); ?></a>
												<?php
											}
											?>
                                        </td>

                                        <td class="product-name"
                                            data-title="<?php esc_attr_e( 'Product', 'wc_wishlist' ); ?>">
											<?php
											if ( ! $product_permalink ) {
												echo esc_html( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $item, $wishlist_item_key ) ) . '&nbsp;';
											} else {
												echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $item, $wishlist_item_key ) );
											}

											// Meta data
											if ( function_exists( 'wc_get_formatted_cart_item_data' ) ) {
												echo wp_kses_post( wc_get_formatted_cart_item_data( $item ) );
											} else {
												echo wp_kses_post( WC()->cart->get_item_data( $item ) );
											}


											// Availability
											$availability = $_product->get_availability();

											if ( $availability && $availability['availability'] ) :
												echo wp_kses_post( apply_filters( 'woocommerce_stock_html', '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>', $availability['availability'] ) );
											endif;
											?>

											<?php do_action( 'woocommerce_wishlist_after_list_item_name', $item, $wishlist ); ?>
                                        </td>

                                        <!-- Product price -->
                                        <td class="product-price"
                                            data-title="<?php esc_attr_e( 'Price', 'wc_wishlist' ); ?>">
											<?php
											$price = WC()->cart->get_product_price( $item['data'] );
											$price = apply_filters( 'woocommerce_cart_item_price', $price, $item, $wishlist_item_key );
											?>

											<?php echo wp_kses_post( apply_filters( 'woocommerce_wishlist_list_item_price', $price, $item, $wishlist ) ); ?>
                                        </td>

                                        <!-- Quantity inputs -->
                                        <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'wc_wishlist' ); ?>">
                                            <?php
                                            if ( $_product->is_sold_individually() ) {
                                                ?>
                                                1 <input type="hidden"
                                                         name="cart[<?php echo esc_attr( $wishlist_item_key ); ?>][qty]"
                                                         class="input-text qty text"
                                                         value="1" />
                                                <?php
                                            } else {
                                                $product_quantity_value = apply_filters( 'woocommerce_wishlist_list_item_quantity_value', $item['quantity'], $item, $wishlist );

                                                $step = apply_filters( 'woocommerce_quantity_input_step', '1', $_product );
                                                $min  = apply_filters( 'woocommerce_quantity_input_min', '', $_product );
                                                $max  = apply_filters( 'woocommerce_quantity_input_max', $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(), $_product );
                                                ?>
                                                <div class="quantity">
                                                    <input type="text"
                                                           name="cart[<?php echo esc_attr( $wishlist_item_key ); ?>][qty]"
                                                           step="<?php echo esc_attr( $step ); ?>"
                                                           min="<?php echo esc_attr( $min ); ?>"
                                                           max="<?php echo esc_attr( $max ); ?>"
                                                           value="<?php echo esc_attr( $product_quantity_value ); ?>"
                                                           size="4"
                                                           title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
                                                           class="input-text qty text"
                                                           maxlength="12" />
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </td>

										<?php if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_show_previously_ordered', 'no' ) == 'yes' ) : ?>
                                            <td class="product-quantity"
                                                data-title="<?php echo esc_attr( apply_filters( 'wc_wishlist_show_previously_ordered_column_heading', WC_Wishlists_Settings::get_setting( 'wc_wishlist_show_previously_ordered_column_heading', __( 'Ordered', 'wc_wishlist' ) ) ) ); ?>">
                                                <?php
                                                $ordered_qty = isset( $item['ordered_total'] ) ? intval( $item['ordered_total'] ) : 0;
                                                ?>
                                                <div class="quantity">
                                                    <input class="input-text qty text"
                                                           maxlength="12"
                                                           type="text"
                                                           name="cart[<?php echo esc_attr( $wishlist_item_key ); ?>][ordered_qty]"
                                                           value="<?php echo esc_attr( $ordered_qty ); ?>" />
                                                </div>
                                            </td>
										<?php endif; ?>

                                        <td class="product-purchase">
											<?php if ( apply_filters( 'woocommerce_wishlist_user_can_purchase', true, $_product ) ) : ?>
												<?php if ( $_product->get_type() != 'external' && $_product->is_in_stock() ) : ?>
                                                    <a rel="nofollow"
                                                       href="<?php echo esc_url( woocommerce_wishlist_url_item_add_to_cart( $wishlist->id, $wishlist_item_key, $wishlist->get_wishlist_sharing() == 'Shared' ? $wishlist->get_wishlist_sharing_key() : false, 'edit' ) ); ?>"
                                                       class="wishlist-add-to-cart-button button alt"><?php esc_html_e( 'Add to Cart', 'wc_wishlist' ); ?></a>
												<?php elseif ( $_product->get_type() == 'external' ) : ?>
                                                    <a rel="nofollow"
                                                       href="<?php echo esc_url( $_product->add_to_cart_url() ); ?>"
                                                       rel="nofollow"
                                                       class="single_add_to_cart_button button alt"><?php echo esc_html( $_product->single_add_to_cart_text() ); ?></a>
												<?php endif; ?>
											<?php endif; ?>
                                        </td>
                                    </tr>
									<?php
								}
							}
							?>

                            <tr>
                                <td class="check-column"></td>
                                <td class="product-remove">&nbsp;</td>
                                <td class="product-thumbnail">&nbsp;</td>
                                <td class="product-name"></td>
                                <td class="product-price"></td>
                                <td class="product-quantity ctr"></td>
								<?php if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_show_previously_ordered', 'no' ) == 'yes' ) : ?>
                                    <td class="product-quantity ctr"></td>
								<?php endif; ?>
								<?php if ( ( apply_filters( 'woocommerce_wishlist_purchases_enabled', true, $wishlist ) ) ) : ?>
                                    <td class="product-purchase">
                                        <!-- Add all to cart, since 2.2.11 -->
                                        <input type="submit" class="button alt wl-add-all"
                                               name="wladdall"
                                               value="<?php esc_attr_e( 'Add All To Cart', 'wc_wishlist' ); ?>"/>
                                        <input type="hidden" name="wladdall-screen" value="edit"/>
                                    </td>
								<?php endif; ?>
                            </tr>

                            </tbody>
                        </table>
                        <div class="wl-row">
                            <table width="100%" cellpadding="0" cellspacing="0" class="wl-actions-table">
                                <tbody>
                                <tr>
                                    <td>
                                        <select class="wl-sel move-list-sel" name="wleditaction2" id="wleditaction2">
                                            <option selected="selected"><?php esc_html_e( 'Actions', 'wc_wishlist' ); ?></option>
                                            <option value="quantity"><?php esc_html_e( 'Update Quantities', 'wc_wishlist' ); ?></option>
											<?php if ( ! class_exists( 'WC_Catalog_Visibility_Options' ) ) : ?>
                                                <option value="add-to-cart"><?php esc_html_e( 'Add to Cart', 'wc_wishlist' ); ?></option>
                                                <option value="quantity-add-to-cart"><?php esc_html_e( 'Update Quantities and Add to Cart', 'wc_wishlist' ); ?></option>
											<?php endif; ?>
                                            <option value="remove"><?php esc_html_e( 'Remove from List', 'wc_wishlist' ); ?></option>
                                            <optgroup
                                                    label="<?php esc_attr_e( 'Move to another list', 'wc_wishlist' ); ?>">
												<?php $lists = WC_Wishlists_User::get_wishlists(); ?>
												<?php if ( $lists && count( $lists ) ) : ?>
													<?php foreach ( $lists as $list ) : ?>
														<?php if ( $list->id != $wishlist->id ) : ?>
                                                            <option value="<?php echo esc_attr( $list->id ); ?>"><?php $list->the_title(); ?>
                                                                ( <?php echo esc_html( $wishlist->get_wishlist_sharing( true ) ); ?>)
                                                            </option>
														<?php endif; ?>
													<?php endforeach; ?>
												<?php endif; ?>
                                                <option value="create"><?php esc_html_e( '+ Create A New List', 'wc_wishlist' ); ?></option>
                                            </optgroup>
                                        </select>
                                    </td>
                                    <td>
                                        <button class="button small wl-but wl-add-to btn-apply"><?php esc_html_e( 'Apply Action', 'wc_wishlist' ); ?></button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <div class="wl-clear"></div>
                        </div><!-- wl-row wl-clear -->
                    </form>

				<?php else : ?>
					<?php $shop_url = get_permalink( wc_get_page_id( 'shop' ) ); ?>
					<?php esc_html_e( 'You do not have anything in this list.', 'wc_wishlist' ); ?>
                    <a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Go Shopping', 'wc_wishlist' ); ?></a>.

				<?php endif; ?>


            </div><!-- /tab-wl-items -->

            <div class="wl-panel panel" id="tab-wl-settings">
                <div class="wl-form">
                    <form action="" enctype="multipart/form-data" method="post">
                        <input type="hidden" name="wlid" value="<?php echo esc_attr( $wishlist->id ); ?>"/>
                        <input type="hidden" name="wlaction" value="edit-list" />
						<?php wp_nonce_field( 'wc-wishlists-edit-list', '_n', true, true ); ?>
                        <p class="form-row form-row-wide">
                            <label for="wishlist_title"><?php esc_html_e( 'Name your list', 'wc_wishlist' ); ?>
                                <abbr class="required" title="required">*</abbr></label>
                            <input required type="text" name="wishlist_title" id="wishlist_title" class="input-text"
                                   value="<?php echo esc_attr( $wishlist->post->post_title ); ?>"/>
                        </p>
                        <p class="form-row form-row-wide">
                            <label for="wishlist_description"><?php esc_html_e( 'Describe your list', 'wc_wishlist' ); ?></label>
                            <textarea name="wishlist_description"
                                      id="wishlist_description"><?php echo esc_textarea( $wishlist->post->post_content ); ?></textarea>
                        </p>
                        <hr/>
                        <div class="form-row">
                            <strong><?php esc_html_e( 'Privacy Settings', 'wc_wishlist' ); ?>
                                <abbr class="required" title="required">*</abbr></strong>
                            <table class="wl-rad-table">
								<?php if ( apply_filters( 'wc_wishlist_allow_public_lists', true ) ) : ?>
                                    <tr>
                                        <td>
                                            <input type="radio" name="wishlist_sharing" id="rad_pub"
                                                   value="Public" <?php checked( 'Public', $sharing ); ?>>
                                        </td>
                                        <td><label for="rad_pub"><?php esc_html_e( 'Public', 'wc_wishlist' ); ?>
                                                <span class="wl-small">- <?php esc_html_e( 'Anyone can search for and see this list. You can also share using a link.', 'wc_wishlist' ); ?></span></label>
                                        </td>
                                    </tr>
								<?php endif; ?>
								<?php if ( apply_filters( 'wc_wishlist_allow_shared_lists', true ) ) : ?>
                                    <tr>
                                        <td>
                                            <input type="radio" name="wishlist_sharing" id="rad_shared"
                                                   value="Shared" <?php checked( 'Shared', $sharing ); ?>>
                                        </td>
                                        <td><label for="rad_shared"><?php esc_html_e( 'Shared', 'wc_wishlist' ); ?>
                                                <span class="wl-small">- <?php esc_html_e( 'Only people with the link can see this list. It will not appear in public search results.', 'wc_wishlist' ); ?></span></label>
                                        </td>
                                    </tr>
								<?php endif; ?>
								<?php if ( apply_filters( 'wc_wishlist_allow_private_lists', true ) ) : ?>
                                    <tr>
                                        <td>
                                            <input type="radio" name="wishlist_sharing" id="rad_priv"
                                                   value="Private" <?php checked( 'Private', $sharing ); ?>>
                                        </td>
                                        <td><label for="rad_priv"><?php esc_html_e( 'Private', 'wc_wishlist' ); ?>
                                                <span class="wl-small">- <?php esc_html_e( 'Only you can see this list.', 'wc_wishlist' ); ?></span></label>
                                        </td>
                                    </tr>
								<?php endif; ?>
                            </table>
                        </div>
                        <p class="form-row"><?php esc_html_e( 'Enter a name you would like associated with this list.  If your list is public, users can find it by searching for this name.', 'wc_wishlist' ); ?></p>

                        <p class="form-row form-row-first">
                            <label for="wishlist_first_name"><?php esc_html_e( 'First Name', 'wc_wishlist' ); ?></label>
                            <input type="text" name="wishlist_first_name" id="wishlist_first_name"
                                   value="<?php echo esc_attr( get_post_meta( $wishlist->id, '_wishlist_first_name', true ) ); ?>"
                                   class="input-text"/>
                        </p>

                        <p class="form-row form-row-last">
                            <label for="wishlist_last_name"><?php esc_html_e( 'Last Name', 'wc_wishlist' ); ?></label>
                            <input type="text" name="wishlist_last_name" id="wishlist_last_name"
                                   value="<?php echo esc_attr( get_post_meta( $wishlist->id, '_wishlist_last_name', true ) ); ?>"
                                   class="input-text"/>
                        </p>

                        <div class="wl-clear"></div>
                        <p class="form-row">
                            <label for="wishlist_owner_email"><?php esc_html_e( 'Email Associated with the List', 'wc_wishlist' ); ?></label>
                            <input type="text" name="wishlist_owner_email" id="wishlist_owner_email"
                                   value="<?php echo esc_attr( get_post_meta( $wishlist->id, '_wishlist_email', true ) ); ?>"
                                   class="input-text"/>
                        </p>

						<?php if ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_notifications_enabled', 'disabled' ) == 'enabled' ) : ?>
                            <div class="wl-clear"></div>
                            <p class="form-row"><?php esc_html_e( 'Email Notifications', 'wc_wishlist' ); ?></p>
                            <div class="form-row">
                                <table class="wl-rad-table">
                                    <tr>
                                        <td>
                                            <input type="radio" id="rad_notification_yes"
                                                   name="wishlist_owner_notifications"
                                                   value="yes" <?php checked( 'yes', $notifications ); ?>>
                                        </td>
                                        <td>
                                            <label for="rad_notification_yes"><?php esc_html_e( 'Yes', 'wc_wishlist' ); ?>
                                                <span class="wl-small">- <?php esc_html_e( 'Send me an email if a price reduction occurs.', 'wc_wishlist' ); ?></span></label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type="radio" id="rad_notification_no"
                                                   name="wishlist_owner_notifications"
                                                   value="no" <?php checked( 'no', $notifications ); ?>>
                                        </td>
                                        <td><label for="rad_notification_no"><?php esc_html_e( 'No', 'wc_wishlist' ); ?>
                                                <span class="wl-small">- <?php esc_html_e( 'Do not send me an email if a price reduction occurs.', 'wc_wishlist' ); ?></span></label>
                                        </td>
                                    </tr>
                                </table>
                            </div>
						<?php endif; ?>

                        <div class="wl-clear"></div>

                        <p class="form-row">
                            <input type="submit" class="button alt" name="update_wishlist"
                                   value="<?php esc_attr_e( 'Save Changes', 'wc_wishlist' ); ?>">
                        </p>
                    </form>
                    <div class="wl-clear"></div>
                </div><!-- /wl form -->

            </div><!-- /tab-wl-settings panel -->
        </div><!-- /wishlist-wrapper -->

		<?php woocommerce_wishlists_get_template( 'wishlist-email-form.php', [ 'wishlist' => $wishlist ] ); ?>
    </div>
<?php do_action( 'woocommerce_wishlists_after_wrapper' ); ?>
