<?php
/**
 * This template displays contents inside Save For Later table
 *
 * This template can be overridden by copying it to yourtheme/save-for-later-for-woocommerce/save-for-later-for-woocommerce.php
 *
 * To maintain compatibility, Save For Later for WooCommerce will update the template files and you have to copy the updated files to your theme
 *
 * @package Save for later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="sfl_table_list_filter_container">
	<div class="sfl_bulk_actions">
		<select name="sfl_action">
				<?php foreach ( $bulk_actions as $key => $label ) { ?>
					<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php } ?>
		</select>

		<input type="submit" name="sfl_filter_sbt" class="sfl_button sfl_action_btn" value="<?php esc_html_e( 'Apply', 'save-for-later-for-woocommerce' ); ?>">
		<?php wp_nonce_field( 'sfl_product_list_table_filter', false, true ); ?>
	</div>
	<br class="clear">
</div>
