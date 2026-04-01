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

/**
 * Action hook fired to Before Save for later Product List
 *
 * @since 1.0
 */
do_action( 'sfl_before_product_list', $sfl_ids );

$pro_check = 0;

foreach ( $sfl_ids as $each_id ) {
	$sfl_data = sfl_get_entry( $each_id );

	$cart_contents = WC()->cart->get_cart();

	if ( ! WC()->cart->find_product_in_cart( $sfl_data->get_cart_item_key() ) ) {
		$pro_check++;
		break;
	}
}

if ( 0 == $pro_check ) {
	return;
}

$columns = array(
	'cb'                => '',
	'sfl_s_no'          => get_option( 'sfl_localization_sfl_s_no' ),
	'sfl_product_image' => get_option( 'sfl_localization_sfl_image' ),
	'sfl_product_name'  => get_option( 'sfl_localization_sfl_pro_name' ),
	'sfl_product_price' => get_option( 'sfl_localization_sfl_pro_price' ),
	'sfl_product_qty'   => get_option( 'sfl_localization_sfl_pro_qty' ),
	'sfl_actions'       => get_option( 'sfl_localization_sfl_pro_actions' ),
);

$woo_class = ( '2' === get_option( 'sfl_advanced_apply_styles_from' ) ) ? 'shop_table cart' : '';
?>
<div class="sfl-wrapper">
<h2><?php echo esc_html( get_option( 'sfl_localization_sfl_table' ) ); ?></h2>

<?php
	/**
	 * Action hook fired to Before Save for later Product List Table
	 *
	 * @since 1.0
	 */
	do_action( 'sfl_before_product_list_table', $sfl_ids );
?>

<table class="sfl-list-table <?php echo esc_attr( $woo_class ); ?>" cellspacing="0">
	<thead>
		<tr> 
			<?php foreach ( $columns as $key => $each_columns ) : ?>
				<th>
				<?php
				if ( 'cb' === $key ) :
					echo '<input type="checkbox" class="sfl_cb sfl_select_all">';
				else :
					echo esc_attr( $each_columns );
				endif;
				?>
				</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php
		sfl_get_template(
			'sfl-list-data.php',
			array(
				'sfl_ids'     => $sfl_ids,
				'permalink'   => get_permalink(),
				'page_number' => 1,
			)
		);
		?>
	</tbody>
	<?php if ( $pagination['page_count'] > 1 ) : ?>
		<tfoot>
			<tr>
				<td colspan="<?php echo esc_attr( count( $columns ) ); ?>" class="footable-visible">
					<?php sfl_get_template( 'pagination.php', $pagination ); ?>
				</td>
			</tr>
		</tfoot>
	<?php endif; ?>
</table>

<?php
/**
 * Action hook fired to After Save for later Product List Table
 *
 * @since 1.0
 */
do_action( 'sfl_after_product_list_table', $sfl_ids );
?>
</div>
<?php
/**
 * Action hook fired to After Save for later Product List
 *
 * @since 1.0
 */
do_action( 'sfl_after_product_list', $sfl_ids );
