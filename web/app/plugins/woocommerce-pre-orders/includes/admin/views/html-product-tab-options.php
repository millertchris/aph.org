<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

// Availability date
$availability_timestamp = WC_Pre_Orders_Product::get_localized_availability_datetime_timestamp( $post->ID );
$availability_date      = ( 0 === $availability_timestamp ) ? '' : date_i18n( 'Y-m-d H:i', $availability_timestamp );

// Exception conditions that prevent pre-order settings changes
$has_active_pre_orders = WC_Pre_Orders_Product::product_has_active_pre_orders( $post->ID );
$has_synced_subs       = WC_Pre_Orders_Compat_Subscriptions::product_has_synced_subs();
$has_trial_period      = WC_Pre_Orders_Compat_Subscriptions::product_has_trial_period();

// Linter is getting confused with HTML mixed with PHP, so ignoring this rule
// phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExactIndent,Generic.WhiteSpace.ScopeIndent.Incorrect,Generic.WhiteSpace.DisallowSpaceIndent,Generic.WhiteSpace.ScopeIndent.IncorrectExact
?>

<div id="wc_pre_orders_data" class="panel woocommerce_options_panel">

	<div class="options_group">

		<?php if ( $has_active_pre_orders || $has_synced_subs || $has_trial_period ) : ?>

			<div class="notice notice-warning inline">

				<?php if ( $has_active_pre_orders ) : ?>
					<p>
						<?php esc_html_e( "This product has active pre-orders, so settings can't be changed while they're in progress. To make changes, cancel or complete the active pre-orders.", 'woocommerce-pre-orders' ); ?>
					</p>
					<p>
						<a href="
						<?php
							echo esc_url(
								add_query_arg(
									array( '_product_id' => $post->ID ),
									admin_url( 'admin.php?page=wc_pre_orders' )
								)
							);
						?>
						" class="button">
						<?php
							esc_html_e( 'View Pre-Orders', 'woocommerce-pre-orders' );
						?>
						</a>&nbsp;&nbsp;
						<a href="
							<?php
							echo esc_url(
								add_query_arg(
									array(
										'tab'     => 'actions',
										'section' => 'complete',
										'action_default_product' => $post->ID,
									),
									admin_url( 'admin.php?page=wc_pre_orders' )
								)
							);
							?>
						" class="button"><?php esc_html_e( 'Complete Pre-Orders', 'woocommerce-pre-orders' ); ?></a>&nbsp;&nbsp;
						<a href="
							<?php
							echo esc_url(
								add_query_arg(
									array(
										'tab'     => 'actions',
										'section' => 'cancel',
										'action_default_product' => $post->ID,
									),
									admin_url( 'admin.php?page=wc_pre_orders' )
								)
							);
							?>
						" class="button"><?php esc_html_e( 'Cancel Pre-Orders', 'woocommerce-pre-orders' ); ?></a>
					</p>
				<?php endif; ?>

				<?php if ( $has_synced_subs ) : ?>
					<p>
						<?php esc_html_e( "This product has synced subscriptions, so settings can't be changed while they're in progress. To make changes, cancel or complete the synced subscriptions.", 'woocommerce-pre-orders' ); ?>
					</p>
				<?php endif; ?>

				<?php if ( $has_trial_period ) : ?>
					<p>
						<?php esc_html_e( "This product has a trial period, so settings can't be changed while the trial is in progress. To make changes, cancel or complete the trial.", 'woocommerce-pre-orders' ); ?>
					</p>
				<?php endif; ?>

			</div>

		<?php
			// Don't render code instead of just hiding/disabling fields to prevent client-side tampering with pre-order settings
			else :
		?>

			<?php
				/**
				 * Action hook to add custom content to the start of the pre-order product options
				 *
				 * @since 1.0.0
				 *
				 * @param int $post_id The ID of the post being edited
				 */
				do_action( 'wc_pre_orders_product_options_start', $post->ID );

				// Enable pre-orders checkbox.
				woocommerce_wp_checkbox(
					array(
						'id'          => '_wc_pre_orders_enabled',
						'label'       => __( 'Enable pre-orders', 'woocommerce-pre-orders' ),
						'description' => __( 'Allow customers to place pre-orders for this product', 'woocommerce-pre-orders' ),
						'desc_tip'    => false,
					)
				);
			?>

			<div class="wc-pre-orders-product-tab-fields-container">

				<p class="form-field _wc_pre_orders_availability_datetime_field">
					<label for="_wc_pre_orders_availability_datetime"><?php esc_html_e( 'Release date (optional)', 'woocommerce-pre-orders' ); ?></label>
					<input
						type="text"
						class="short"
						name="_wc_pre_orders_availability_datetime"
						id="_wc_pre_orders_availability_datetime"
						value="<?php echo esc_attr( $availability_date ); ?>"
						placeholder="YYYY-MM-DD HH:MM"
					/>
					<span class="woocommerce-help-tip" tabindex="0" data-tip="<?php esc_attr_e( '(Optional) Specify when the product will be available. If set, customers will see this release date at checkout.', 'woocommerce-pre-orders' ); ?>" aria-label="<?php esc_attr_e( '(Optional) Specify when the product will be available. If set, customers will see this release date at checkout.', 'woocommerce-pre-orders' ); ?>"></span>
				</p>

				<?php

					// Pre-order fee
					woocommerce_wp_text_input(
						array(
							'id'          => '_wc_pre_orders_fee',
							'class'       => 'short wc_input_price',
							/* translators: %s: currency symbol */
							'label'       => sprintf( __( 'Pre-order fee (%s - optional)', 'woocommerce-pre-orders' ), get_woocommerce_currency_symbol() ),
							'description' => __( '(Optional) Add an extra charge for pre-orders. Leave blank (or zero) if no additional fee is required.', 'woocommerce-pre-orders' ),
							'desc_tip'    => true,
							'value'       => wc_format_localized_decimal( get_post_meta( $post->ID, '_wc_pre_orders_fee', true ) ),
							'placeholder' => '0' . wc_get_price_decimal_separator() . '00',
						)
					);

					// Pre-Order Payment Timing section

					woocommerce_wp_radio(
						array(
							'id'          => '_wc_pre_orders_when_to_charge',
							'label'       => __( 'Customers will be charged', 'woocommerce-pre-orders' ),
							'description' => '',
							'options'     => array(
								'upfront'      => __( 'Upfront (pay now)', 'woocommerce-pre-orders' ),
								'upon_release' => __( 'Upon release (pay later)', 'woocommerce-pre-orders' ),
							),
							'default'     => 'upon_release',
						)
					);

					/**
					 * Action hook to add custom content to the end of the pre-order product options
					 *
					 * @since 1.0.0
					 *
					 * @param int $post_id The ID of the post being edited
					 */
					do_action( 'wc_pre_orders_product_options_end' );

				?>

				<p class="wc-pre-orders-payment-timing-help">
					<strong><?php esc_html_e( 'Upfront (pay now):', 'woocommerce-pre-orders' ); ?></strong>
					<?php esc_html_e( 'Charge customers during checkout. Once payment is confirmed, the order stays in "Pre-ordered" until release, then switches to "Completed" for virtual/downloadable items or "Processing" for physical items.', 'woocommerce-pre-orders' ); ?>
					<br>
					<strong><?php esc_html_e( 'Upon release (pay later):', 'woocommerce-pre-orders' ); ?></strong>
					<?php esc_html_e( 'No charge is taken at checkout. The order remains "Pre-ordered" until release, when it moves to "Pending", then auto-charges if there is a saved payment method, or emails the customer a payment link, switching to "Completed" (virtual/downloadable) or "Processing" (physical) when the payment is confirmed.', 'woocommerce-pre-orders' ); ?>
				</p>

			</div>

		<?php endif; ?>
	</div>
</div>

<?php // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExactIndent,Generic.WhiteSpace.ScopeIndent.Incorrect,Generic.WhiteSpace.DisallowSpaceIndent,Generic.WhiteSpace.ScopeIndent.IncorrectExact ?>
