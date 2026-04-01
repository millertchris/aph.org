<?php
/* Admin HTML Settings */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$report_filter = isset( $_REQUEST['sfl_report_filter'] ) ? wc_clean( wp_unslash( $_REQUEST['sfl_report_filter'] ) ) : 'all_time';

?>
<div class = "wrap <?php echo esc_attr( self::$plugin_slug ); ?>_reports_wrapper_cover woocommerce">

	<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_header">
		<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_title"><h2><?php esc_html_e( 'Save For Later - Reports', 'save-for-later-for-woocommerce' ); ?></h2></div>
		<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_logo"></div>
	</div>

	<form method = "post"  enctype = "multipart/form-data">
		<p>
			<label>
			<?php esc_html_e( 'Filters', 'save-for-later-for-woocommerce' ); ?>
			</label>

			<select name="sfl_report_filter" class="sfl_report_filter" id="sfl_report_filter" >
				<?php foreach ( sfl_get_filter_options() as $key => $label ) { ?>
					<option <?php selected( $report_filter, $key ); ?> value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
				<?php } ?>
			</select>

			<input type="submit" name="sfl_report_submit"  class="sfl_report_submit button-primary" id="sfl_report_submit" value="<?php esc_html_e( 'Submit', 'save-for-later-for-woocommerce' ); ?>">
				<?php	wp_nonce_field( 'sfl_report_nonce', false, true ); ?>
		</p>
	</form>

	<div class="sfl_report_table">
		<?php
		$over_all      = sfl_get_child_posts_reports( $report_filter, 'sfl_saved' ) ? sfl_get_child_posts_reports( $report_filter, 'sfl_saved' ) : 0;
		$current_saved = sfl_get_child_posts_reports( $report_filter, 'sfl_current_saved' ) ? sfl_get_child_posts_reports( $report_filter, 'sfl_current_saved' ) : 0;
		$purchased     = sfl_get_child_posts_reports( $report_filter, 'sfl_purchased' ) ? sfl_get_child_posts_reports( $report_filter, 'sfl_purchased' ) : 0;
		$deleted       = sfl_get_child_posts_reports( $report_filter, 'sfl_deleted' ) ? sfl_get_child_posts_reports( $report_filter, 'sfl_deleted' ) : 0;
		$price         = sfl_get_child_posts_reports_amount( $report_filter ) ? sfl_get_child_posts_reports_amount( $report_filter ) : 0;
		?>
		<table>
			<tr>
				<td>
					<label>
						<?php esc_html_e( 'Total Number of Saved Later Products', 'save-for-later-for-woocommerce' ); ?>
					</label>
				</td>
				<td>
					<span><?php echo esc_html( $over_all ); ?></span>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<?php esc_html_e( 'Total Number of Current Saved Later Products', 'save-for-later-for-woocommerce' ); ?>
					</label>
				</td>
				<td>
					<span><?php echo esc_html( $current_saved ); ?></span>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<?php esc_html_e( 'Total Number of Purchased Products', 'save-for-later-for-woocommerce' ); ?>
					</label>
				</td>
				<td>
					<span><?php echo esc_html( $purchased ); ?></span>
				</td>
			</tr>
			<tr>
				<td>
					<label>
						<?php esc_html_e( 'Total Number of Deleted Products', 'save-for-later-for-woocommerce' ); ?>
					</label>
				</td>
				<td>
					<span><?php echo esc_html( $deleted ); ?></span>
				</td>
			<tr>   
				<td>
					<label>
						<?php esc_html_e( 'Total Amount Received Through Save for Later', 'save-for-later-for-woocommerce' ); ?>
					</label>
				</td>
				<td>
					<span><?php sfl_price( $price ); ?></span>
				</td>
			</tr>
		</table>
	</div>
</div>
<?php
