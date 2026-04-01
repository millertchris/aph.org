<?php
/**
 * This template displays contents inside Save For Later table
 *
 * This template can be overridden by copying it to yourtheme/save-for-later-for-woocommerce/sfl-list-guest.php
 *
 * To maintain compatibility, Save For Later for WooCommerce will update the template files and you have to copy the updated files to your theme
 *
 * @package Save for Later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$i = ( 1 < $page_number ) ? ( ( ( $page_number - 1 ) * 5 ) + 1 ) : 1;

foreach ( $sfl_ids as $cookie_data ) {
	$product_obj = wc_get_product( $cookie_data['sfl_product_id'] );

	if ( 'publish' !== get_post_status( $cookie_data['sfl_product_id'] ) ) { // is valid product.
		SFL_Actions_Handler::remove_from_sfl_list_handle( $cookie_data['sfl_product_id'] );
		continue;
	}
	?>
	<tr>
		<td>
			<?php printf( '<input class="sfl_entry" type="checkbox" name="sfl_post_id[]" value="%s" />', esc_attr( $cookie_data['sfl_product_id'] ) ); ?>
		</td>
		<td data-title="<?php echo esc_html( get_option( 'sfl_localization_sfl_s_no' ) ); ?>">
			<?php echo esc_attr( $i ); ?>
		</td>
		<td data-title="<?php echo esc_html( get_option( 'sfl_localization_sfl_image' ) ); ?>">
			<?php
			/**
			 * Save for later Product image html
			 *
			 * @since 3.8.0
			 */
			$product_image = apply_filters( 'sfl_product_image_html', sfl_get_product_thumbnail( $product_obj, false ), $product_obj, $cookie_data );

			echo wp_kses_post( $product_image );
			?>
		</td>
		<td data-title="<?php echo esc_html( get_option( 'sfl_localization_sfl_pro_name' ) ); ?>">
			<?php
			/**
			 * Save for later Product Name html
			 *
			 * @since 3.8.0
			 */
			$product_name = apply_filters( 'sfl_product_name_html', sfl_get_product_data( 'name', $cookie_data['sfl_product_id'], $product_obj, $cookie_data ), $product_obj, $cookie_data );

			echo wp_kses_post( $product_name );
			?>
		</td>
		<td data-title="<?php echo esc_html( get_option( 'sfl_localization_sfl_pro_price' ) ); ?>">
			<?php
				/**
				 * Save for later Product Name html
				 *
				 * @since 3.8.0
				 */
				$product_price = apply_filters( 'sfl_product_price_html', sfl_price( sfl_get_tax_based_price( $cookie_data['sfl_product_id'] ), false ), $product_obj, $cookie_data );

				echo wp_kses_post( $product_price );
			?>
		</td>
		<td data-title="<?php echo esc_html( get_option( 'sfl_localization_sfl_pro_qty' ) ); ?>">
			<?php
			/**
			 * Save for later Product Quantity html
			 *
			 * @since 3.8.0
			 */
			$product_qty = apply_filters( 'sfl_product_quantity_html', sfl_get_cookie_data_by_key( 'sfl_product_qty', $cookie_data ), $product_obj, $cookie_data );

			echo wp_kses_post( $product_qty );
			?>
		</td>
		<?php
		sfl_get_template(
			'sfl-list-buttons.php',
			array(
				'sfl_process_id' => $cookie_data['sfl_product_id'],
				'permalink'      => $permalink,
				'sfl_product_id' => $cookie_data['sfl_product_id'],
				'sfl_product'    => $product_obj,
			)
		);
		?>
	</tr>
	<?php
	$i++;
}
