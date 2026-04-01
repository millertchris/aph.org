<?php
/**
 * Box size table file.
 *
 * @var WC_Shipping_USPS $shipping_method USPS Shipping Method.
 *
 * @package WC_Shipping_USPS
 */

?>
<tr valign="top" id="packing_options">
	<th scope="row" class="titledesc">
		<?php esc_html_e( 'Box Sizes', 'woocommerce-shipping-usps' ); ?>

		<img class="help_tip" data-tip="<?php esc_attr_e( 'Items will be packed into these boxes based on item dimensions and volume. Outer dimensions will be passed to USPS, whereas inner dimensions will be used for packing. Items not fitting into boxes will be packed individually.', 'woocommerce-shipping-usps' ); ?>" src="<?php echo esc_url( WC()->plugin_url() ); ?>/assets/images/help.png" height="16" width="16" />
	</th>
	<td class="forminp">
		<table class="usps_boxes widefat">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox" /></th>
					<th><?php esc_html_e( 'Name', 'woocommerce-shipping-usps' ); ?></th>
					<th><?php esc_html_e( 'L', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'W', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'H', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'Inner L', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'Inner W', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'Inner H', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'Weight of Box', 'woocommerce-shipping-usps' ); ?> (lbs)</th>
					<th><?php esc_html_e( 'Max Weight', 'woocommerce-shipping-usps' ); ?> (lbs)</th>
					<th><?php esc_html_e( 'Letter', 'woocommerce-shipping-usps' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="11">
						<a href="#" class="button plus insert"><?php esc_html_e( 'Add Box', 'woocommerce-shipping-usps' ); ?></a>
						<a href="#" class="button minus remove"><?php esc_html_e( 'Remove selected box(es)', 'woocommerce-shipping-usps' ); ?></a>
					</th>
				</tr>
			</tfoot>
			<tbody id="rates">
				<?php
				if ( $shipping_method->boxes ) {
					foreach ( $shipping_method->boxes as $key => $box ) {
						?>
						<tr>
							<td class="check-column"><input type="checkbox" /></td>
							<td><input type="text" size="10" maxlength="150" name="<?php echo esc_attr( "boxes_name[$key]" ); ?>" value="<?php echo isset( $box['name'] ) ? esc_attr( stripslashes( $box['name'] ) ) : ''; ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_outer_length[$key]" ); ?>" value="<?php echo esc_attr( $box['outer_length'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_outer_width[$key]" ); ?>" value="<?php echo esc_attr( $box['outer_width'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_outer_height[$key]" ); ?>" value="<?php echo esc_attr( $box['outer_height'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_inner_length[$key]" ); ?>" value="<?php echo esc_attr( $box['inner_length'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_inner_width[$key]" ); ?>" value="<?php echo esc_attr( $box['inner_width'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_inner_height[$key]" ); ?>" value="<?php echo esc_attr( $box['inner_height'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_box_weight[$key]" ); ?>" value="<?php echo esc_attr( $box['box_weight'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "boxes_max_weight[$key]" ); ?>" value="<?php echo esc_attr( $box['max_weight'] ); ?>" /></td>
							<td><input type="checkbox" name="<?php echo esc_attr( "boxes_is_letter[$key]" ); ?>" <?php checked( isset( $box['is_letter'] ) && true === $box['is_letter'], true ); ?> /></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</td>
</tr>
