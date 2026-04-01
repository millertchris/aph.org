<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Conditions table.
 *
 * Display table with all the user configured shipping packages.
 *
 * @author		Jeroen Sormani
 * @package 	Advanced Shipping Packages for WooCommerce
 * @version		1.0.0
 */

$shipping_packages = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'shipping_package', 'post_status' => array( 'draft', 'publish' ), 'orderby' => 'menu_order', 'order' => 'ASC' ) );
$shipping_method_condition = wpc_get_condition( 'shipping_method' );
$field_args = $shipping_method_condition->get_value_field_args();
$method_names = array_reduce( $field_args['options'], 'array_replace', [] );

?><tr valign="top">
	<th scope="row" class="titledesc"><?php
		esc_html_e( 'Shipping packages', 'advanced-shipping-packages-for-woocommerce' ); ?>:<br />
	</th>
	<td class="forminp" id="advanced-shipping-packages-table">

		<p>
			<a href="javascript:void(0);" class="aspwc-quickhelp"><?php esc_html_e( 'Quick tips', 'advanced-shipping-packages-for-woocommerce' ); ?> <span class="dashicons dashicons-arrow-right-alt2"></span></a>
		</p>
		<div class="description hidden">
			<ul><em>
				<li>- <?php esc_html_e( 'Each package you setup below will attempt to split the cart when it matches the setup conditions', 'advanced-shipping-packages-for-woocommerce' ); ?></li>
				<li>- <?php esc_html_e( 'Products are only split from the original package, not from other split packages', 'advanced-shipping-packages-for-woocommerce' ); ?></li>
				<li>- <?php esc_html_e( 'All products that match the \'Product conditions\' will be split and grouped into a package', 'advanced-shipping-packages-for-woocommerce' ); ?></li>
				<li>- <?php esc_html_e( 'Each package will be shown separately in the cart/checkout with its own shipping rates/cost', 'advanced-shipping-packages-for-woocommerce' ); ?></li>
				<li>- <?php esc_html_e( 'Packages are processed in sorted order', 'advanced-shipping-packages-for-woocommerce' ); ?></li>
			</em></ul>
		</div>

		<table class='wpc-conditions-post-table wpc-sortable-post-table widefat striped'>
			<thead>
				<tr>
					<th></th>
					<th class="column-primary"><?php esc_html_e( 'Title', 'advanced-shipping-packages-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Enabled', 'advanced-shipping-packages-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Package name', 'advanced-shipping-packages-for-woocommerce' ); ?></th>
					<th class="column-excluded-whitelisted"><?php esc_html_e( 'Excluded/whitelisted', 'advanced-shipping-packages-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody><?php

				foreach ( $shipping_packages as $shipping_package ) :
					$exclude    = get_post_meta( $shipping_package->ID, '_exclude_shipping', true );
					$include    = get_post_meta( $shipping_package->ID, '_include_shipping', true );
					$name       = get_post_meta( $shipping_package->ID, '_name', true );
					$rate_names = array_map( function ( $v ) use ( $method_names ) {
						return $method_names[ $v ] ?? $v;
					}, (array) ( $include ?: $exclude ) );

					?><tr data-id="<?php echo absint( $shipping_package->ID ); ?>">

						<td class='sort' width="1%">
							<input type='hidden' name='sort[]' value='<?php echo absint( $shipping_package->ID ); ?>' />
						</td>
						<td class="column-primary">
							<a href='<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_packages&id=' . $shipping_package->ID ) ); ?>' class='row-title' title='<?php esc_html_e( 'Edit shipping option', 'advanced-shipping-packages-for-woocommerce' ); ?>'>
								<strong><?php echo esc_html( $shipping_package->post_title ) ?: __( 'Untitled', 'advanced-shipping-packages-for-woocommerce' ); ?></strong>
							</a>
							<div class='row-actions'>
								<span class='edit'>
									<a href='<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_packages&id=' . $shipping_package->ID ) ); ?>' title='<?php esc_html_e( 'Edit shipping option', 'advanced-shipping-packages-for-woocommerce' ); ?>'>
										<?php esc_html_e( 'Edit', 'advanced-shipping-packages-for-woocommerce' ); ?>
									</a>
									|
								</span>
								<span class='trash'>
									<a href='<?php echo esc_url( get_delete_post_link( $shipping_package->ID ) ); ?>' title='<?php esc_html_e( 'Delete Extra shipping option', 'advanced-shipping-packages-for-woocommerce' ); ?>' onclick="return window.confirm('<?php esc_html_e( 'Are you sure you want to delete this package?', 'advanced-shipping-packages-for-woocommerce' ); ?>');">
										<?php esc_html_e( 'Delete', 'advanced-shipping-packages-for-woocommerce' ); ?>
									</a>
								</span>
							</div>
						</td>
						<td class="enabled" width="1%">
							<a class="wpc-toggle-enabled" onclick="return false;"><?php
								if ( $shipping_package->post_status == 'publish' ) {
									echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--enabled" aria-label="' . esc_html__( 'Enabled', 'woocommerce' ) . '">' . esc_attr__( 'Yes', 'woocommerce' ) . '</span>';
								} else {
									echo '<span class="woocommerce-input-toggle woocommerce-input-toggle--disabled" aria-label="' . esc_html__( 'Disabled', 'woocommerce' ) . '">' . esc_attr__( 'No', 'woocommerce' ) . '</span>';
								}
							?></a>
						</td>
						<td><?php echo wp_kses_post( $name ); ?></td>
						<td class="column-excluded-whitelisted">
							<div class="excluded-whitelisted">
								<strong><?php echo $include ? '+ ' : ($exclude ? '- ' : ''); ?></strong><?php
								echo esc_html( implode( ', ', $rate_names ) ); ?>
							</div>
						</td>
					</tr><?php

				endforeach;

				if ( empty( $shipping_packages ) ) :

					?><tr>
						<td colspan='4' style="display: table-cell;"><?php esc_html_e( 'There are no Advanced Shipping Packages. Yet...', 'advanced-shipping-packages-for-woocommerce' ); ?></td>
					</tr><?php

				endif;

			?></tbody>
			<tfoot>
				<tr>
					<th colspan='5' style='padding-left: 10px; display: table-cell;'>
						<a href='<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_packages&id=new' ) ); ?>' class='add button'><?php esc_html_e( 'Add shipping package', 'advanced-shipping-packages-for-woocommerce' ); ?></a>
					</th>
				</tr>
			</tfoot>
		</table>
	</td>
</tr>
