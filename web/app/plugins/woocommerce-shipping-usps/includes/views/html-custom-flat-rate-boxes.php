<?php
/**
 * Custom flat rate boxes table file.
 *
 * @var WC_Shipping_USPS $shipping_method USPS Shipping Method.
 *
 * @package WC_Shipping_USPS
 */

// Build grouped options for the flat rate type select.
$flat_rate_groups = array();
foreach ( $shipping_method->flat_rate_boxes as $code => $box ) {
	$is_domestic = 'd' === substr( $code, 0, 1 );
	$service     = $box['service'];

	if ( $is_domestic && 'priority' === $service ) {
		$group = __( 'Domestic Priority Mail', 'woocommerce-shipping-usps' );
	} elseif ( $is_domestic && 'express' === $service ) {
		$group = __( 'Domestic Priority Mail Express', 'woocommerce-shipping-usps' );
	} elseif ( ! $is_domestic && 'priority' === $service ) {
		$group = __( 'International Priority Mail', 'woocommerce-shipping-usps' );
	} else {
		$group = __( 'International Priority Mail Express', 'woocommerce-shipping-usps' );
	}

	if ( ! isset( $flat_rate_groups[ $group ] ) ) {
		$flat_rate_groups[ $group ] = array();
	}
	$flat_rate_groups[ $group ][ $code ] = $box['name'] . ( $is_domestic ? '' : ' (intl.)' );
}

?>
<tr valign="top" id="custom_flat_rate_box_options">
	<th scope="row" class="titledesc">
		<?php esc_html_e( 'Custom Flat Rate Boxes', 'woocommerce-shipping-usps' ); ?>

		<?php echo wc_help_tip( __( 'Define custom flat rate box or envelope dimensions to account for bulging or non-standard packing. Each entry is mapped to a USPS flat rate product and will be used alongside the predefined flat rate boxes in the packing algorithm.', 'woocommerce-shipping-usps' ) ); ?>
	</th>
	<td class="forminp">
		<table class="usps_custom_flat_rate_boxes usps_boxes widefat">
			<thead>
				<tr>
					<th class="check-column"><input type="checkbox" /></th>
					<th><?php esc_html_e( 'Name', 'woocommerce-shipping-usps' ); ?></th>
					<th><?php esc_html_e( 'L', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'W', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'H', 'woocommerce-shipping-usps' ); ?> (in)</th>
					<th><?php esc_html_e( 'Box weight', 'woocommerce-shipping-usps' ); ?> (lbs)</th>
					<th><?php esc_html_e( 'Max weight', 'woocommerce-shipping-usps' ); ?> (lbs)</th>
					<th><?php esc_html_e( 'Flat Rate packaging', 'woocommerce-shipping-usps' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th colspan="8">
						<a href="#" class="button plus insert"><?php esc_html_e( 'Add custom Flat Rate box', 'woocommerce-shipping-usps' ); ?></a>
						<a href="#" class="button minus remove"><?php esc_html_e( 'Remove selected box(es)', 'woocommerce-shipping-usps' ); ?></a>
					</th>
				</tr>
			</tfoot>
			<tbody id="custom_flat_rate_rates">
				<?php
				if ( $shipping_method->custom_flat_rate_boxes ) {
					foreach ( $shipping_method->custom_flat_rate_boxes as $key => $box ) {
						?>
						<tr>
							<td class="check-column"><input type="checkbox" /></td>
							<td><input type="text" size="10" maxlength="150" name="<?php echo esc_attr( "custom_flat_rate_boxes_name[$key]" ); ?>" value="<?php echo isset( $box['name'] ) ? esc_attr( stripslashes( $box['name'] ) ) : ''; ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "custom_flat_rate_boxes_length[$key]" ); ?>" value="<?php echo esc_attr( $box['length'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "custom_flat_rate_boxes_width[$key]" ); ?>" value="<?php echo esc_attr( $box['width'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "custom_flat_rate_boxes_height[$key]" ); ?>" value="<?php echo esc_attr( $box['height'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "custom_flat_rate_boxes_box_weight[$key]" ); ?>" value="<?php echo esc_attr( $box['box_weight'] ); ?>" /></td>
							<td><input type="number" size="5" max="10000000" min="0" step="0.0001" lang="en" name="<?php echo esc_attr( "custom_flat_rate_boxes_max_weight[$key]" ); ?>" value="<?php echo esc_attr( $box['max_weight'] ); ?>" /></td>
							<td>
								<select name="<?php echo esc_attr( "custom_flat_rate_boxes_flat_rate_type[$key]" ); ?>" class="custom-flat-rate-type-select">
									<?php foreach ( $flat_rate_groups as $group_label => $options ) : ?>
										<optgroup label="<?php echo esc_attr( $group_label ); ?>">
											<?php foreach ( $options as $code => $name ) : ?>
												<option value="<?php echo esc_attr( $code ); ?>" <?php selected( isset( $box['flat_rate_type'] ) ? $box['flat_rate_type'] : '', $code ); ?>><?php echo esc_html( $name ); ?></option>
											<?php endforeach; ?>
										</optgroup>
									<?php endforeach; ?>
								</select>
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
