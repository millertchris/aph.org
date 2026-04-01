<?php
/**
 * This template displays contents inside Save For Later table
 *
 * This template can be overridden by copying it to yourtheme/save-for-later-for-woocommerce/save-for-later-for-woocommerce.php
 *
 * To maintain compatibility, Save For Later for WooCommerce will update the template files and you have to copy the updated files to your theme
 *
 * @package Save for Later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$i = ( 1 < $page_number ) ? ( ( ( $page_number - 1 ) * sfl_pagination_count() ) + 1 ) : 1;

foreach ( $sfl_ids as $each_id ) {
	$sfl_data    = sfl_get_entry( $each_id );
	$product_obj = wc_get_product( $sfl_data->get_product_id() );

	if ( 'publish' !== get_post_status( $sfl_data->get_product_id() ) ) { // is valid product.
		sfl_invalid_delete( $each_id );
		continue;
	}
	?>
	<tr>
		<td>
			<?php printf( '<input class="sfl_entry" type="checkbox" name="sfl_post_id[]" value="%s" />', esc_attr( $each_id ) ); ?>
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
			$product_image = apply_filters( 'sfl_product_image_html', sfl_get_product_thumbnail( $product_obj, false ), $product_obj, $sfl_data );

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
			$product_name = apply_filters( 'sfl_product_name_html', sfl_get_product_data( 'name', $sfl_data->get_product_id(), $product_obj, $sfl_data ), $product_obj, $sfl_data );

			echo wp_kses_post( $product_name );

			// Meta data.
			if ( sfl_check_is_array( $sfl_data->get_cart_item() ) ) {
				if ( isset( $sfl_data->get_cart_item()['data'] ) && is_object( $sfl_data->get_cart_item()['data'] ) && '__PHP_Incomplete_Class' !== get_class( $sfl_data->get_cart_item()['data'] ) ) {
					echo wp_kses_post( wc_get_formatted_cart_item_data( $sfl_data->get_cart_item() ) );
				}
			}
			?>
		</td>
		<td data-title="<?php echo esc_html( get_option( 'sfl_localization_sfl_pro_price' ) ); ?>">
			<?php
			/**
			 * Save for later Product Name html
			 *
			 * @since 3.8.0
			 */
			$product_price = apply_filters( 'sfl_product_price_html', sfl_price( $sfl_data->get_product_price(), false ), $product_obj, $sfl_data );

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
			$product_qty = apply_filters( 'sfl_product_quantity_html', $sfl_data->get_product_qty(), $product_obj, $sfl_data );

			echo wp_kses_post( $product_qty );
			?>
		</td>
		<?php
		sfl_get_template(
			'sfl-list-buttons.php',
			array(
				'sfl_process_id' => $each_id,
				'permalink'      => $permalink,
				'sfl_product_id' => $sfl_data->get_product_id(),
				'sfl_product'    => $product_obj,
			)
		);
		?>
	</tr>
	<?php
	$i++;
}
