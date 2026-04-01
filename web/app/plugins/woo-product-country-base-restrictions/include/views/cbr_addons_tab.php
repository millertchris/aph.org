<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $fzpcr;

$more_plugins = array(
	0 => array(
		'title' => 'Advanced Shipment Tracking',
		'description' => __( 'AST Pro provides powerful features to easily add tracking information to WooCommerce orders, automate fulfillment workflows, and keep your customers happy and informed.', 'woo-product-country-base-restrictions' ),
		'url' => 'https://www.zorem.com/product/woocommerce-advanced-shipment-tracking/?utm_source=wp-admin&utm_medium=cbr-addons&utm_campaign=add-ons',
		'image' => 'ast-45.png',
		'file' => 'ast-pro/ast-pro.php'
	),
	1 => array(
		'title' => 'TrackShip for WooCommerce',
		'description' => __( 'Take control of your post-shipping workflows, reduce time spent on customer service and provide a superior post-purchase experience to your customers. Beyond automatic shipment tracking, TrackShip brings a branded tracking experience into your store.', 'woo-product-country-base-restrictions' ),
		'url' => 'https://wordpress.org/plugins/trackship-for-woocommerce/?utm_source=wp-admin&utm_medium=cbr-addons&utm_campaign=add-ons',
		'image' => 'ts-45.png',
		'file' => 'trackship-for-woocommerce/trackship-for-woocommerce.php'
	),
	2 => array(
		'title' => 'Zorem Local Pickup Pro',
		'description' => __( 'The Advanced Local Pickup (ALP) helps you manage local pickup order workflows more conveniently by extending the WooCommerce Local Pickup shipping method. The Pro version lets you set up multiple pickup locations, split business hours, and apply discounts by pickup location.', 'woo-product-country-base-restrictions' ),
		'url' => 'https://www.zorem.com/product/zorem-local-pickup-pro/?utm_source=wp-admin&utm_medium=cbr-addons&utm_campaign=add-ons',
		'image' => 'alp-45.png',
		'file' => 'advanced-local-pickup-pro/advanced-local-pickup-pro.php'
	),
	3 => array(
		'title' => 'Customer Email Verification',
		'description' => __( 'Customer Email Verification helps WooCommerce store owners reduce registration spam and fraudulent orders by requiring customers to verify their email address when registering an account or before placing an order.', 'woo-product-country-base-restrictions' ),
		'url' => 'https://www.zorem.com/product/customer-email-verification/?utm_source=wp-admin&utm_medium=cbr-addons&utm_campaign=add-ons',
		'image' => 'cev-45.png',
		'file' => 'customer-email-verification/customer-email-verification.php'
	),
	4 => array(
		'title' => 'SMS for WooCommerce',
		'description' => __( 'Keep your customers informed by sending them automated SMS text messages with order and delivery updates. You can send SMS notifications to customers when the order status is updated or when the shipment is out for delivery and more.', 'woo-product-country-base-restrictions' ),
		'url' => 'https://www.zorem.com/product/sms-for-woocommerce/?utm_source=wp-admin&utm_medium=cbr-addons&utm_campaign=add-ons',
		'image' => 'sms-45.png',
		'file' => 'sms-for-woocommerce/sms-for-woocommerce.php'
	),
	5 => array(
		'title' => 'Email Reports for WooCommerce',
		'description' => __( 'Sales Report Email Pro helps you understand how well your store is performing and how your products are selling by sending daily, weekly, or monthly sales reports directly from your WooCommerce store to your email.', 'woo-product-country-base-restrictions' ),
		'url' => 'https://www.zorem.com/product/email-reports-for-woocommerce/?utm_source=wp-admin&utm_medium=cbr-addons&utm_campaign=add-ons',
		'image' => 'sre-45.png',
		'file' => 'sales-report-email-pro/sales-report-email-pro.php'
	),
);

$plugin_url = $fzpcr->plugin_dir_url();
?>
<section id="cbr_content4" class="cbr_tab_section">
	<div class="d_table addons_page_dtable" style="">
		<section id="content_tab_addons" class="
		<?php
		if ( class_exists( 'Country_Based_Restrictions_PRO_Add_on' ) ) {
			?>
			inner_tab_section
			<?php } ?>">

		<!-- CBR Go Pro v2 layout (mirrors AST structure exactly) -->
		<div class="cbr-go-pro-v2">
			<!-- Hero -->
			<div class="gopro-hero">
				<h1><?php esc_html_e( 'Take Your Country Restrictions to the Next Level', 'woo-product-country-base-restrictions' ); ?></h1>
				<p>
				<?php 
				echo wp_kses_post(
					__( 'Stop limiting your store with basic restrictions. Switch from a <strong>basic setup</strong> to a <a href="https://www.zorem.com/product/country-based-restriction-pro/?utm_source=wp-admin&utm_medium=CBRPRO&utm_campaign=go-pro" target="_blank">fully advanced restriction powerhouse</a>.', 'woo-product-country-base-restrictions' )
				);
				?>
				</p>
			</div>

			<!-- Feature comparison -->
			<div class="gopro-comparison">
				<!-- Table header -->
				<div class="gopro-comp-header">
					<div class="gopro-comp-header-label"><?php esc_html_e( 'Feature Comparison', 'woo-product-country-base-restrictions' ); ?></div>
					<div class="gopro-comp-header-col">
						<span class="comp-header-badge badge-current"><?php esc_html_e( 'Current', 'woo-product-country-base-restrictions' ); ?></span>
						<span class="comp-header-title"><?php esc_html_e( 'CBR FREE', 'woo-product-country-base-restrictions' ); ?></span>
					</div>
					<div class="gopro-comp-header-col is-pro">
						<span class="comp-header-badge badge-recommended"><?php esc_html_e( 'Recommended', 'woo-product-country-base-restrictions' ); ?></span>
						<span class="comp-header-title"><?php esc_html_e( 'CBR PRO', 'woo-product-country-base-restrictions' ); ?></span>
					</div>
				</div>
				<?php
				$comp_features = array(
					array(
						'title'     => __( 'GeoLocation Detection', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Detect customer country using WooCommerce geolocation or shipping address.', 'woo-product-country-base-restrictions' ),
						'free'      => 'check',
						'free_label'=> '',
						'pro'       => __( 'Advanced Detection', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Product Restrictions', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Restrict specific products from being purchased in certain countries.', 'woo-product-country-base-restrictions' ),
						'free'      => 'check',
						'free_label'=> '',
						'pro'       => __( 'Full Restrictions', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Catalog Visibility', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Hide or show products in your catalog based on the customer\'s country.', 'woo-product-country-base-restrictions' ),
						'free'      => 'limited',
						'free_label'=> __( 'Basic', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'Full Control', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Catalog Restriction Rules', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Create restriction rules by product categories and apply to multiple countries at once.', 'woo-product-country-base-restrictions' ),
						'free'      => 'cross',
						'free_label'=> __( 'Not Available', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'Category Rules', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Payment Gateway by Country', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Restrict specific payment gateways based on the customer\'s country.', 'woo-product-country-base-restrictions' ),
						'free'      => 'cross',
						'free_label'=> __( 'Not Available', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'Gateway Control', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Country Detection Widget', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Display a country detection widget on the frontend with customizer support.', 'woo-product-country-base-restrictions' ),
						'free'      => 'cross',
						'free_label'=> __( 'Not Available', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'Full Widget', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Debug Mode', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Show geolocation country in the frontend toolbar for easy debugging.', 'woo-product-country-base-restrictions' ),
						'free'      => 'check',
						'free_label'=> '',
						'pro'       => __( 'Advanced Debug', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Restrict on Place Order (Checkout)', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Restrict products at checkout based on the customer\'s billing/shipping country.', 'woo-product-country-base-restrictions' ),
						'free'      => 'cross',
						'free_label'=> __( 'Not Available', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'Checkout Control', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Bypass Restrictions for Users', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Allow specific users or user roles to bypass country-based restrictions.', 'woo-product-country-base-restrictions' ),
						'free'      => 'cross',
						'free_label'=> __( 'Not Available', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'User Bypass', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Compatible with Popular Plugins', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Built-in support for WPML, Polylang, and other popular plugins.', 'woo-product-country-base-restrictions' ),
						'free'      => 'limited',
						'free_label'=> __( 'Limited Support', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'Full Integration', 'woo-product-country-base-restrictions' ),
					),
					array(
						'title'     => __( 'Premium Support', 'woo-product-country-base-restrictions' ),
						'desc'      => __( 'Priority ticket handling and dedicated help center access.', 'woo-product-country-base-restrictions' ),
						'free'      => 'limited',
						'free_label'=> __( 'Standard Only', 'woo-product-country-base-restrictions' ),
						'pro'       => __( 'Priority Support', 'woo-product-country-base-restrictions' ),
					),
				);
				foreach ( $comp_features as $feat ) :
					?>
				<div class="gopro-comp-row">
					<div class="gopro-comp-feature">
						<strong><?php echo esc_html( $feat['title'] ); ?></strong>
						<span><?php echo esc_html( $feat['desc'] ); ?></span>
					</div>
					<div class="gopro-comp-cell">
						<?php if ( 'check' === $feat['free'] ) : ?>
							<span class="comp-icon icon-check">
								<svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
							</span>
						<?php else : ?>
							<span class="comp-icon icon-x">
								<svg fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5"><line x1="16" y1="8" x2="8" y2="16"/><line x1="8" y1="8" x2="16" y2="16"/></svg>
							</span>
						<?php endif; ?>
						<?php if ( ! empty( $feat['free_label'] ) ) : ?>
							<span class="comp-status"><?php echo esc_html( $feat['free_label'] ); ?></span>
						<?php endif; ?>
					</div>
					<div class="gopro-comp-cell is-pro">
						<span class="comp-icon icon-check">
							<svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
						</span>
						<span class="comp-status"><?php echo esc_html( $feat['pro'] ); ?></span>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<!-- CTA -->
			<div class="gopro-cta">
				<a href="https://www.zorem.com/product/country-based-restriction-pro/?utm_source=wp-admin&utm_medium=CBRPRO&utm_campaign=go-pro" class="gopro-cta-btn" target="_blank"><?php esc_html_e( 'GET STARTED WITH PRO', 'woo-product-country-base-restrictions' ); ?></a>
				<p class="gopro-cta-sub"><?php esc_html_e( 'Join store owners optimizing their country restrictions', 'woo-product-country-base-restrictions' ); ?></p>
			</div>

		</div>
		<!-- End .cbr-go-pro-v2 hero + comparison -->

		<!-- Powerful Add-ons -->
		<div class="cbr-go-pro-v2">
			<div class="gopro-addons">
				<div class="gopro-addons-header">
					<div>
						<h2><?php esc_html_e( 'Powerful Add-ons', 'woo-product-country-base-restrictions' ); ?></h2>
						<p><?php esc_html_e( 'Extend your store\'s capabilities with our ecosystem', 'woo-product-country-base-restrictions' ); ?></p>
					</div>
				</div>
				<div class="gopro-addons-track">
					<?php
					$icon_colors = array( '#ecfdf5', '#ede9fe', '#eff6ff', '#fef3c7', '#fce7f3', '#e0f2fe' );
					foreach ( $more_plugins as $index => $addon ) :
						$icon_bg = isset( $icon_colors[ $index ] ) ? $icon_colors[ $index ] : '#f3f4f6';
						?>
						<div class="gopro-addon-card">
							<div class="gopro-addon-card-top">
								<div class="gopro-addon-card-icon" style="background:<?php echo esc_attr( $icon_bg ); ?>">
									<img src="<?php echo esc_url( $plugin_url . 'assets/images/' . $addon['image'] ); ?>" alt="<?php echo esc_attr( $addon['title'] ); ?>">
								</div>
								<h3><?php echo esc_html( $addon['title'] ); ?></h3>
							</div>
							<div class="gopro-addon-card-body">
								<p><?php echo esc_html( $addon['description'] ); ?></p>
							</div>
							<div class="gopro-addon-card-footer">
								<?php if ( is_plugin_active( $addon['file'] ) ) : ?>
									<span class="gopro-addon-card-btn is-active"><?php esc_html_e( 'Active', 'woo-product-country-base-restrictions' ); ?></span>
								<?php else : ?>
									<a href="<?php echo esc_url( $addon['url'] ); ?>" class="gopro-addon-card-btn" target="_blank"><?php esc_html_e( 'Learn More', 'woo-product-country-base-restrictions' ); ?></a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>

		</section>
	</div>
</section>
