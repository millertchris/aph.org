<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

wc_print_notices();

do_action( 'woocommerce_before_cart' ); ?>


<div class="cart-wrapper">

	<div class="layout list-of-items my-cart list-view">
		<div class="wrapper">
			<h2 class="cart-items-title">Cart items</h2>
			<div class="layout-options">
				<a class="layout-button list-view btn active" href="#" data-view="list">List View</a>
				<a class="layout-button btn" href="#" data-view="grid">Grid View</a>
			</div>
			<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
		<?php do_action( 'woocommerce_before_cart_table' ); ?>

				<?php do_action( 'woocommerce_before_cart_contents' ); ?>


				<div class="line-items shop_table shop_table_responsive cart woocommerce-cart-form__contents line-items-box">

						<?php
						$i = 1;
						$item_array = WC()->cart->get_cart();
						$item_count = count($item_array);
						foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
							$x = $i + 1;
							$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
							$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

							if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
								$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
								?>
								<div id="product-<?php echo $i; ?>" class="item woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
										<div class="item-image"  style="background-image: url('<?php echo get_the_post_thumbnail_url($product_id); ?>')"></div>
										<div class="item-number">
											<span class="label item-name">
												<a class="item-link" href="<?php echo $product_permalink; ?>" alt="">
												<?php
													// SKU
													echo $_product->get_sku() . ' ';
													echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '' );

												?>
												</a>
												<?php
													do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

													// Meta data.
													// echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

													
													// Backorder notification.
													if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
														echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>' ) );
													}
												?>
											</span>
										
										</div>
									<!-- <div class="item-detail product-sku"><p>Catalog No: <?php echo $_product->get_sku(); ?></p></div> -->

									<div class="item-detail item-remove">
										<?php
											// @codingStandardsIgnoreLine
											echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
												'<a href="%s" class="edit-item" aria-label="%s" data-product_id="%s" data-product_sku="%s"><i class="fas fa-times" aria-hidden="true"></i> Delete product</a>',
												esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
												__( 'Delete product #' . $i, 'woocommerce' ),
												esc_attr( $product_id ),
												esc_attr( $_product->get_sku() )
											), $cart_item_key );
										?>
									</div>
									<?php if ($i !== $item_count) : ?>
										<a href="#product-<?php echo $x; ?>" class="skip-to-item"> <span>Skip to next product</span></a>
									<?php endif; ?>
									<div class="item-detail"><p>Price: <?php
										echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
									?></p></div>
									<div class="item-detail product-quantity">
										<?php
											if ( $_product->is_sold_individually() ) {
												$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
											} else {
												$product_quantity = woocommerce_quantity_input( array(
													'input_name'   => "cart[{$cart_item_key}][qty]",
													'input_value'  => $cart_item['quantity'],
													'max_value'    => $_product->get_max_purchase_quantity(),
													'min_value'    => '0',
													'product_name' => $_product->get_name(),
												), $_product, false );
											}
											echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
											?>
									</div>
									<div class="item-detail product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
										<p>Total: <?php
										echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
									?></p>
									</div>
									<div class="item-detail update-item">
										<button type="submit" class="update-cart" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>
									</div>

								</div>

								<?php do_action( 'woocommerce_cart_contents' ); ?>

								<?php
							}
							$i++;
						}
						?>



				</div>

				<div class="cart-actions actions td-button">
					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon">
							<label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>


					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</div>
				<?php do_action( 'woocommerce_after_cart_contents' ); ?>


		<?php do_action( 'woocommerce_after_cart_table' ); ?>
	</form>
		</div>
	</div>

	<div class="cart-collaterals">
		<?php
			/**
			 * Cart collaterals hook.
			 *
			 * @hooked woocommerce_cart_totals
			 * @hooked aph_replacement_parts - 10
			 * @hooked woocommerce_cross_sell_display - 20
			 */
			do_action( 'woocommerce_cart_collaterals' );
		?>
	</div>
</div>


<?php
/**
 * After cart hook.
 *
 * @hooked woocommerce_cross_sell_display
 */
 do_action( 'woocommerce_after_cart' ); ?>
