<?php
/**
 * ASPWC meta box settings.
 *
 * Display the shipping settings in the meta box.
 *
 * @author		Jeroen Sormani
 * @package		Advanced Shipping Packages for WooCommerce
 * @version		1.1.0
 * @var WP_Post $package
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wp_nonce_field( 'aspwc_meta_box', 'aspwc_meta_box_nonce' );

$name               = get_post_meta( $package->ID, '_name', true );
$condition_groups   = get_post_meta( $package->ID, '_product_conditions', true );
$excluded_shipping  = array_filter( (array) get_post_meta( $package->ID, '_exclude_shipping', true ) );
$included_shipping  = array_filter( (array) get_post_meta( $package->ID, '_include_shipping', true ) );
$shipping_methods   = array();

// Get shipping methods from the 'shipping method' condition
$shipping_method_condition = wpc_get_condition( 'shipping_method' );
$field_args = $shipping_method_condition->get_value_field_args();
$shipping_methods = $field_args['options'];
$shipping_option_type = empty( $included_shipping ) ? 'exclude' : 'include';

?><div class='aspwc-meta-box aspwc-meta-box-settings'>

	<div class='aspwc-shipping-package'>

		<?php if ( $package->post_status !== 'publish' ) : ?>
			<div class="wpc-card" style="margin-left: 0; margin-right: 0;">
				<span class="wpc-warning-icon" alt="Warning Icon"></span>
				<div>
					<div class="card-body-text">
						<p><?php esc_html_e( 'This package is currently not enabled and will not be used until enabled.', 'advanced-shipping-packages-for-woocommerce' ); ?></p>
					</div>
					<div class="card-body-actions">
						<button type="button" class="card-button button-primary wpc-toggle-enabled" data-id="<?php echo absint( $package->ID ); ?>"><?php esc_html_e( 'Enable now', 'advanced-shipping-packages-for-woocommerce' ); ?></button>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<p class="aspwc-option">
			<label>
				<span class="aspwc-option-name"><?php esc_html_e( 'Package name', 'advanced-shipping-packages-for-woocommerce' ); ?></span>
				<input type="text" class="aspwc-input" name="_name" value="<?php echo wp_kses_post( $name ); ?>">
			</label><?php
			echo wc_help_tip( __( 'This is the name that will be displayed at the cart/checkout', 'advanced-shipping-packages-for-woocommerce' ) );
		?></p>

		<p class="aspwc-option <?php echo 'aspwc-shipping-option-' . esc_attr( $shipping_option_type ); ?>">
			<input type="hidden" name="_exclude_shipping[]">
			<input type="hidden" name="_include_shipping[]">

			<span class="include-exclude-shipping">
				<label>
					<span class="aspwc-option-name show-if-exclude">
						<?php esc_html_e( 'Exclude shipping methods', 'advanced-shipping-packages-for-woocommerce' ); ?>
						<i class="dashicons dashicons-update switch-include-exclude-shipping" style="line-height: 30px;"></i>
					</span>
					<span class="aspwc-option-name show-if-include">
						<?php esc_html_e( 'Whitelist shipping methods', 'advanced-shipping-packages-for-woocommerce' ); ?>
						<i class="dashicons dashicons-update switch-include-exclude-shipping" style="line-height: 30px;"></i>
					</span>

					<select class="aspwc-input wc-enhanced-select" name="<?php echo $shipping_option_type === 'exclude' ? '_exclude_shipping[]' : '_include_shipping[]'; ?>" multiple="multiple" placeholder="<?php esc_html_e( 'Exclude shipping options', 'advanced-shipping-packages-for-woocommerce' ); ?>"><?php
						foreach ( $shipping_methods as $optgroup => $methods ) :
							?><optgroup label='<?php echo esc_attr( $optgroup ); ?>'><?php
							foreach ( $methods as $k => $v ) :
								?><option value="<?php echo esc_attr( $k ); ?>" <?php selected( in_array( $k, ($excluded_shipping + $included_shipping) ) ); ?>><?php echo esc_html( $v ); ?></option><?php
							endforeach;
						endforeach;
					?></select>
				</label><?php
				echo wc_help_tip( __( 'Exclude or whitelist shipping methods for this package. Leave empty to allow all available methods.', 'advanced-shipping-packages-for-woocommerce' ) ); ?>
			</span>

		</p>

		<?php do_action( 'aspwc/after_package_settings', $package ); ?>

	</div>

	<br/>
	<hr/>

	<div class='wpc-conditions wpc-conditions-meta-box'>
		<div class='wpc-condition-groups'>

			<p>
				<strong><?php esc_html_e( 'Add the products that match one of the following rule groups to the package', 'advanced-shipping-packages-for-woocommerce' ); ?></strong><?php
			?></p><?php

			if ( ! empty( $condition_groups ) ) :

				foreach ( $condition_groups as $condition_group => $conditions ) :
					include 'html-admin-page-package-product-conditions-group.php';
				endforeach;

			else :

				$condition_group = '0';
				include 'html-admin-page-package-product-conditions-group.php';

			endif;

		?></div>

		<div class='wpc-condition-group-template hidden' style='display: none'><?php
			$condition_group = '9999';
			$conditions      = array();
			include 'html-admin-page-package-product-conditions-group.php';
		?></div>
		<a class='button wpc-condition-group-add' href='javascript:void(0);'><?php esc_html_e( 'Add \'Or\' group', 'woocommerce-advanced-messages' ); ?></a>

	</div>

	<?php do_action( 'aspwc/after_package_product_conditions', $package ); ?>

</div>
