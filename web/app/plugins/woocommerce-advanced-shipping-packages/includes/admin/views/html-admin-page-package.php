<h2>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=advanced_shipping_packages' ) ); ?>"><?php esc_html_e( 'Advanced Shipping Packages', '' ); ?></a> &gt;
	<?php echo esc_html( $package->post_title ); ?>
</h2>
<input type="hidden" name="advanced_shipping_package" value="<?php echo absint( $package->ID ); ?>" />

<div class="aspwc-title-text-wrap" id="titlediv">
	<legend class="screen-reader-text"><span><?php echo wp_kses_post( __( 'title', 'advanced-shipping-packages-for-woocommerce' ) ); ?></span></legend>
	<input class="input-text regular-input aspwc-title-text"
		   type="text"
		   name="post_title"
		   id="title"
		   value="<?php echo esc_attr( $package->post_title ); ?>"
		   placeholder="<?php echo esc_attr( __( 'Add title', 'advanced-shipping-packages-for-woocommerce' ) ); ?>" />
</div>

<div class="aspwc-meta-box-wrap" style="margin-right: 300px;" id="poststuff">
	<div id="aspwc_conditions" class="postbox ">
		<h2 class="" style="border-bottom: 1px solid #eee;"><span><?php esc_html_e( 'Conditions', 'advanced-shipping-packages-for-woocommerce' ); ?></span></h2>
		<div class="inside"><?php
			require_once plugin_dir_path( __FILE__ ) . 'html-admin-page-package-conditions.php';
		?></div>
	</div>

	<div id="aspwc_settings" class="postbox ">
		<h2 class="" style="border-bottom: 1px solid #eee;"><span><?php esc_html_e( 'Settings', 'advanced-shipping-packages-for-woocommerce' ); ?></span></h2>
		<div class="inside"><?php
			require_once plugin_dir_path( __FILE__ ) . 'html-admin-page-package-settings.php';
		?></div>
	</div>
</div>
