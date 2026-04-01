<?php
/**
 * Flat rate box row file.
 *
 * @package WC_Shipping_USPS
 */

/**
 * Filter to modify the flat rate boxes.
 *
 * @var array List of flat rate boxes.
 *
 * @since 4.4.61
 */
$flat_rate_box_sizes = apply_filters(
	'wc_usps_flat_rate_box_weight_table_rows',
	array(
		'd28'  => array(
			'keys'  => array( 'i16' ),
			'label' => __( 'Small Box', 'woocommerce-shipping-usps' ),
		),
		'd17b' => array(
			'keys'  => array( 'i9b' ),
			'label' => __( 'Medium Box - 1', 'woocommerce-shipping-usps' ),
		),
		'd17'  => array(
			'keys'  => array( 'i9' ),
			'label' => __( 'Medium Box - 2', 'woocommerce-shipping-usps' ),
		),
		'd22'  => array(
			'keys'  => array( 'i11' ),
			'label' => __( 'Large Box', 'woocommerce-shipping-usps' ),
		),
		'd13'  => array(
			'keys'  => array( 'd16', 'i13', 'i8' ),
			'label' => __( 'Envelope', 'woocommerce-shipping-usps' ),
		),
		'd30'  => array(
			'keys'  => array( 'd44', 'i30' ),
			'label' => __( 'Legal Envelope', 'woocommerce-shipping-usps' ),
		),
		'd63'  => array(
			'keys'  => array( 'd29', 'i63', 'i29' ),
			'label' => __( 'Padded Envelope', 'woocommerce-shipping-usps' ),
		),
		'd38'  => array(
			'keys'  => array(),
			'label' => __( 'Gift Card Envelope', 'woocommerce-shipping-usps' ),
		),
		'd40'  => array(
			'keys'  => array(),
			'label' => __( 'Window Envelope', 'woocommerce-shipping-usps' ),
		),
		'd42'  => array(
			'keys'  => array(),
			'label' => __( 'Small Envelope', 'woocommerce-shipping-usps' ),
		),
	)
);

// Create array of duplicate box/envelope sizes to output as hidden inputs.
$duplicate_sizes = call_user_func_array( 'array_merge', array_column( $flat_rate_box_sizes, 'keys' ) );
?>
<tr verticle-align="top" id="flat_rate_box_weights">
	<th scope="row" class="titledesc">
		<?php esc_html_e( 'Flat Rate Box Weights', 'woocommerce-shipping-usps' ); ?>
		<img class="help_tip" data-tip="<?php esc_attr_e( 'Use this table to adjust the empty box/envelope weights as needed.', 'woocommerce-shipping-usps' ); ?>" src="<?php echo esc_url( WC()->plugin_url() . '/assets/images/help.png' ); ?>" height="16" width="16"/>
	</th>
	<td class="forminp">
		<table class="flat_rate_box_weights widefat">
			<thead>
			<tr>
				<th><?php esc_html_e( 'Name', 'woocommerce-shipping-usps' ); ?></th>
				<th><?php esc_html_e( 'Empty Weight', 'woocommerce-shipping-usps' ); ?></th>
				<th><?php esc_html_e( 'Length', 'woocommerce-shipping-usps' ); ?></th>
				<th><?php esc_html_e( 'Width', 'woocommerce-shipping-usps' ); ?></th>
				<th><?php esc_html_e( 'Height', 'woocommerce-shipping-usps' ); ?></th>
			</tr>
			</thead>
			<tbody id="rates">
			<?php
			// @var WC_Shipping_USPS $shipping_method USPS Shipping Method.
			if ( ! empty( $shipping_method->flat_rate_boxes ) ) {
				// Loop through the unique box sizes and build our table.
				foreach ( $flat_rate_box_sizes as $key => $data ) {

					// Make sure the box exists in the main array before proceeding.
					if ( empty( $shipping_method->flat_rate_boxes[ $key ] ) ) {
						continue;
					}

					// Get the box details.
					$box = $shipping_method->flat_rate_boxes[ $key ];

					// Get the saved value if it exists, otherwise leave empty.
					$value = ! empty( $shipping_method->flat_rate_box_weights[ $key ] ) ? $shipping_method->flat_rate_box_weights[ $key ] : '';
					?>
					<tr>
						<td>
							<?php
								// translators: %s is a label rate.
								echo esc_html( sprintf( __( 'Flat Rate %s', 'woocommerce_shipping_usps' ), $data['label'] ) );
							?>
						</td>
						<td><input class="input-text wc_input_decimal empty-weight" type="text" size="5" data-duplicate_sizes="<?php echo esc_attr( implode( '|', $data['keys'] ) ); ?>" name="flat_rate_box_weights[<?php echo esc_attr( $key ); ?>]" placeholder="<?php echo esc_attr( $box['weight'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />lbs</td>
						<td><input type="text" size="5" readonly value="<?php echo esc_attr( $box['length'] ); ?>" />in</td>
						<td><input type="text" size="5" readonly value="<?php echo esc_attr( $box['width'] ); ?>" />in</td>
						<td><input type="text" size="5" readonly value="<?php echo esc_attr( $box['height'] ); ?>" />in</td>
					</tr>
					<?php

				}

				// Create hidden inputs for duplicated sizes so we can dynamically fill via JavaScript.
				foreach ( $duplicate_sizes as $key ) {
					?>
					<input type="hidden" name="flat_rate_box_weights[<?php echo esc_attr( $key ); ?>]" value="" />
					<?php
				}
			}
			?>
			</tbody>
		</table>
	</td>
</tr>
