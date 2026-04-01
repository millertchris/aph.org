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

$add_to_cart_url = sfl_get_args_added_url(
	$permalink,
	array(
		'sfl_post_id' => $sfl_process_id,
		'sfl_action'  => 'sfl_add_to_cart',
	)
);
$remove_url      = sfl_get_args_added_url(
	$permalink,
	array(
		'sfl_post_id' => $sfl_process_id,
		'sfl_action'  => 'sfl_remove',
	)
);
?>
<td data-title="<?php echo esc_html( get_option( 'sfl_localization_sfl_pro_actions' ) ); ?>">
	<?php if ( $sfl_product->is_in_stock() ) { ?>
		<a class="button sfl-add" href="<?php echo esc_url( $add_to_cart_url ); ?>"><?php echo esc_html( get_option( 'sfl_localization_move_to_cart' ) ); ?></a>
	<?php } ?>
	<a class="button sfl-remove" href="<?php echo esc_url( $remove_url ); ?>"><?php echo esc_html( get_option( 'sfl_localization_remove' ) ); ?></a>
</td>
