<?php
/**
 * license.php
 *
 * @author    Justin Greer <justin@justin-greer.com
 * @copyright Justin Greer Interactive, LLC
 * @date      5/8/17
 * @package   WP-Nightly
 *
 * @todo Add Addon filter and run the grid from that instead of being hardcoded
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$options = get_option( 'wo_license_information' );
add_thickbox();
?>

<div class="section group product-license-grid">

	<div class="col span_6_of_6">
		<div class="product">
			<p>
				A valid license will ensure you install always has access to the latest updates.
				Enter your license in the field below. You can view your license key but visiting your account at
				<a href="https://wp-oauth.com/my-account/product-licenses/" target="_blank">https://wp-oauth.com/my-account/product-licenses/</a>.
			</p>
			<?php
			$license_key = wo_license_key();
			if ( ! empty( $options['license'] ) && ! empty( $license_key ) && $options['license'] != 'invalid' ) :
				?>
				<table style="width: 100%; margin: 1em 0;">
					<tr>
						<th style="text-align: left; width: 10%;">License Status:</th>
						<td><?php echo ucfirst( $options['license'] ); ?></td>
					</tr>

				<?php if ( ! empty( $options['customer_name'] ) ) : ?>
						<tr>
							<th style="text-align: left; width:10%;">Customer Name:</th>
							<td><?php echo $options['customer_name']; ?></td>
						</tr>
				<?php endif; ?>
					<tr>
						<th style="text-align: left; width:10%;">Expires:</th>
						<td><?php echo $options['expires'] !== 'lifetime' ? date( 'F jS, Y', strtotime( $options['expires'] ) ) : '<strong>Lifetime</strong>'; ?></td>
					</tr>
				</table>

				<?php if ( $options['license'] == 'invalid' ) : ?>
					<p>
						<span style="color:red;">INVALID</span>
					</p>
				<?php endif; ?>

			<?php elseif ( ! empty( $options['license'] ) && ! empty( $license_key ) && $options['license'] == 'invalid' ) : ?>
				<p>
					<span style="color:red;">INVALID LICENSE - Visit <a
								href="https://wp-oauth.com">https://wp-oauth.com</a> to get a valid license.</span>
				</p>
			<?php endif; ?>
			<form class="license_form" action="" method="post" style="display: inline-block; float: left;">
				<input type="text" name="wo_license_key" value="<?php echo wo_license_key(); ?>"
					   placeholder="Enter License Key" style="width:300px;"/>
				<input type="hidden" name="activate_license" value="true"/>
				<input class="button" type="submit" value="Activate / Reactivate"/>

				<div class="clearboth"></div>

				<?php
				global $license_error;
				if ( ! is_null( $license_error ) ) :
					?>
					<p style="color:red;"><?php echo $license_error; ?></p>
				<?php endif; ?>
			</form>

			<?php if ( empty( $license_key ) ) : ?>
				<!--<a href="<?php echo admin_url( 'admin.php?page=wo_server_status&tab=license&oauth_license_check' ); ?>">
					OAuth License Check
				</a>-->
			<?php endif; ?>

			<?php if ( ! empty( $license_key ) ) : ?>
				<form action="" method="post" style="display: inline-block; float: left; margin-left: 0.5em;">
					<input type="hidden" name="deactivate_license" value="true"/>
					<input class="button" type="submit" value="Deactivate License"/>
				</form>
			<?php endif; ?>
		</div>
	</div>

</div>
