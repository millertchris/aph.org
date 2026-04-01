<?php
/**
 * Service table file.
 *
 * @var WC_Shipping_USPS $shipping_method USPS Shipping Method.
 *
 * @package WC_Shipping_USPS
 */

$sort             = 0;
$ordered_services = array();

foreach ( $shipping_method->services as $code => $values ) {
	if ( isset( $shipping_method->custom_services[ $code ]['order'] ) ) {
		$sort = (int) $shipping_method->custom_services[ $code ]['order'];
	}

	while ( isset( $ordered_services[ $sort ] ) ) {
		++$sort;
	}

	$ordered_services[ $sort ] = array( $code, $values );
	++$sort;
}

?>
<tr valign="top" id="service_options">
	<th scope="row" class="titledesc"><?php esc_html_e( 'Services', 'woocommerce-shipping-usps' ); ?></th>
	<td class="forminp">
		<table class="usps_services widefat">
			<thead>
				<th class="sort">&nbsp;</th>
				<th><?php esc_html_e( 'Name', 'woocommerce-shipping-usps' ); ?></th>
				<th><?php esc_html_e( 'Service(s)', 'woocommerce-shipping-usps' ); ?></th>
				<th style="width:15%;">
					<?php
					// translators: %s is a currency symbol.
					echo esc_html( sprintf( __( 'Price Adjustment (%s)', 'woocommerce-shipping-usps' ), get_woocommerce_currency_symbol() ) );
					?>
				</th>
				<th style="width:15%;"><?php esc_html_e( 'Price Adjustment (%)', 'woocommerce-shipping-usps' ); ?></th>
			</thead>
			<tbody>
				<?php
				ksort( $ordered_services );

				foreach ( $ordered_services as $value ) {
					$code   = $value[0];
					$values = $value[1];
					if ( ! isset( $shipping_method->custom_services[ $code ] ) ) {
						$shipping_method->custom_services[ $code ] = array();
					}
					?>
						<tr data-service_code="<?php echo esc_attr( $code ); ?>">
							<td class="sort">
								<input type="hidden" class="order" name="usps_service[<?php echo esc_attr( $code ); ?>][order]" value="<?php echo isset( $shipping_method->custom_services[ $code ]['order'] ) ? esc_attr( $shipping_method->custom_services[ $code ]['order'] ) : ''; ?>" />
							</td>
							<td>
								<input type="text" name="<?php echo esc_attr( "usps_service[$code][name]" ); ?>" placeholder="<?php echo esc_attr( $values['name'] . " ({$shipping_method->title})" ); ?>" value="<?php echo isset( $shipping_method->custom_services[ $code ]['name'] ) ? esc_attr( $shipping_method->custom_services[ $code ]['name'] ) : ''; ?>" size="35" maxlength="150" />
							</td>
							<td>
								<ul class="sub_services">
									<?php
									foreach ( $values['services'] as $key => $name ) :
										if ( 0 === $key ) {
											foreach ( $name as $subsub_service_key => $subsub_service ) {
												?>
												<li>
													<label>
														<input type="checkbox" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $subsub_service_key ); ?>][enabled]" <?php checked( ( ! isset( $shipping_method->custom_services[ $code ][ $key ][ $subsub_service_key ]['enabled'] ) || ! empty( $shipping_method->custom_services[ $code ][ $key ][ $subsub_service_key ]['enabled'] ) ), true ); ?> />
														<?php echo esc_html( $subsub_service ); ?>
													</label>
												</li>
												<?php
											}
										} else {
											$classes = ! empty( $values['commercial'] ) && in_array( $key, array_map( 'intval', $values['commercial'] ), true ) ? 'commercial' : '';
											?>
											<li class="<?php echo esc_attr( $classes ); ?>">
												<label>
													<input type="checkbox" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][enabled]" <?php checked( ( ! isset( $shipping_method->custom_services[ $code ][ $key ]['enabled'] ) || ! empty( $shipping_method->custom_services[ $code ][ $key ]['enabled'] ) ), true ); ?> />
													<?php echo esc_html( $name ); ?>
												</label>
											</li>
											<?php
										}
									endforeach;
									?>
								</ul>
							</td>
							<td>
								<ul class="sub_services">
									<?php
									foreach ( $values['services'] as $key => $name ) :
										if ( 0 === $key ) {
											foreach ( $name as $subsub_service_key => $subsub_service ) {
												$service_value = isset( $shipping_method->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment'] ) ? wc_format_localized_price( $shipping_method->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment'] ) : '';
												?>
												<li>
													<?php echo esc_html( get_woocommerce_currency_symbol() ); ?><input type="text" name="<?php echo esc_attr( "usps_service[$code][$key][$subsub_service_key][adjustment]" ); ?>" placeholder="N/A" value="<?php echo esc_attr( $service_value ); ?>" class="wc_input_price" size="4" />
												</li>
												<?php
											}
										} else {
											$classes       = ! empty( $values['commercial'] ) && in_array( $key, array_map( 'intval', $values['commercial'] ), true ) ? 'commercial' : '';
											$service_value = isset( $shipping_method->custom_services[ $code ][ $key ]['adjustment'] ) ? wc_format_localized_price( $shipping_method->custom_services[ $code ][ $key ]['adjustment'] ) : '';
											?>
											<li class="<?php echo esc_attr( $classes ); ?>">
												<?php echo esc_html( get_woocommerce_currency_symbol() ); ?><input type="text" name="<?php echo esc_attr( "usps_service[$code][$key][adjustment]" ); ?>" placeholder="N/A" value="<?php echo esc_attr( $service_value ); ?>" class="wc_input_price" size="4" />
											</li>
											<?php
										}
									endforeach;
									?>
								</ul>
							</td>
							<td>
								<ul class="sub_services">
									<?php
									foreach ( $values['services'] as $key => $name ) :
										if ( 0 === $key ) {
											foreach ( $name as $subsub_service_key => $subsub_service ) {
												?>
												<li>
													<input type="number" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $subsub_service_key ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $shipping_method->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment_percent'] ) ? esc_attr( $shipping_method->custom_services[ $code ][ $key ][ $subsub_service_key ]['adjustment_percent'] ) : ''; ?>" max="100" min="-100" step="0.0001" lang="en" size="4" />%
												</li>
												<?php
											}
										} else {
											$classes = ! empty( $values['commercial'] ) && in_array( $key, array_map( 'intval', $values['commercial'] ), true ) ? 'commercial' : '';
											?>
											<li class="<?php echo esc_attr( $classes ); ?>">
												<input type="number" name="usps_service[<?php echo esc_attr( $code ); ?>][<?php echo esc_attr( $key ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $shipping_method->custom_services[ $code ][ $key ]['adjustment_percent'] ) ? esc_attr( $shipping_method->custom_services[ $code ][ $key ]['adjustment_percent'] ) : ''; ?>" max="100" min="-100" step="0.0001" lang="en" size="4" />%
											</li>
											<?php
										}
									endforeach;
									?>
								</ul>
							</td>
						</tr>
					<?php
					if ( 'D_MEDIA_MAIL' === esc_attr( $code ) ) {
						?>
						<tr id="media_mail_notice" style="display: none;">
							<td style="width: 16px;"></td>
							<td colspan="4">
								<div class="media-mail-notice">
									<?php
									printf(
										// translators: %1$s Media Mail anchor tag start, %3$s <b> tag start, %4$s </b> tag end, %5$s Shipping class document anchor tag start and ( %2$s, %6$s, %8$s ) is anchor tags end.
										esc_html__( 'To enable %1$sMedia Mail®%2$s, you must select the ‘Retrieve Standard Service rates from the USPS API’ option. You then have two options: %3$sa)%4$s You can do nothing, which allows any item to use Media Mail®; or %3$sb)%4$s if only some of your products are eligible for Media Mail®, you need to give each product a %5$sshipping class%6$s and %7$srestrict Media Mail®%8$s to use only that class or classes.', 'woocommerce-shipping-usps' ),
										'<a href=" ' . esc_url( 'https://www.usps.com/ship/mail-shipping-services.htm#mediamail' ) . '" target="_blank">',
										'</a>',
										'<b>',
										'</b>',
										'<a href=" ' . esc_url( 'https://woocommerce.com/document/product-shipping-classes/' ) . '" target="_blank">',
										'</a>',
										'<a href="#woocommerce_usps_mediamail_restriction">',
										'</a>'
									);
									?>
								</div>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</td>
</tr>
