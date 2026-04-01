<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section id="cbr_content6" class="cbr_tab_section">
	<div class="cbr_tab_inner_container">
		<div class="cbr-pro-tab-content">
			<div class="cbr-pro-tab-overlay">
				<h3><?php esc_html_e( 'Payment Restrictions', 'woo-product-country-base-restrictions' ); ?> <span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span> <span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span></h3>
				<p><?php esc_html_e( 'Restrict payment methods based on the customer billing or shipping country. Create rules to include or exclude specific payment gateways for specific countries. Upgrade to PRO to unlock this feature.', 'woo-product-country-base-restrictions' ); ?></p>
				<a href="https://www.zorem.com/product/country-based-restriction-pro/?utm_source=wp-admin&utm_medium=CBRPRO&utm_campaign=add-ons" class="button-primary" target="_blank"><?php esc_html_e( 'Upgrade To PRO', 'woo-product-country-base-restrictions' ); ?></a>
			</div>
			<!-- Locked preview of the feature -->
			<div style="opacity: 0.4; pointer-events: none; margin-top: 20px;">
				<table class="form-table" style="border: 1px solid #e0e0e0; background: #fff;border-radius: 8px;border-collapse: unset;">
					<tbody>
						<tr valign="top">
							<td class="bulk-data-table" style="padding: 0;">
								<div style="padding: 15px;">
									<!-- Sample checkout restriction rule preview -->
									<div class="accordion" style="background: #fafafa; border: 1px solid #e0e0e0; border-radius: 3px; padding: 12px 15px; margin-bottom: 10px;">
										<span style="display: flex; align-items: center; justify-content: space-between;">
											<span style="display: flex; align-items: center; gap: 10px;">
												<span style="font-weight: 600; color: #3c4758;"><?php esc_html_e( 'Payment Restriction Rule 1', 'woo-product-country-base-restrictions' ); ?></span>
											</span>
											<span style="display: flex; align-items: center; gap: 8px;">
												<span class="dashicons dashicons-menu" style="color: #999;"></span>
												<span class="dashicons dashicons-no-alt" style="color: #999;"></span>
												<span class="dashicons dashicons-admin-page" style="color: #999;"></span>
											</span>
										</span>
									</div>
									<div class="accordion" style="background: #fafafa; border: 1px solid #e0e0e0; border-radius: 3px; padding: 12px 15px; margin-bottom: 10px;">
										<span style="display: flex; align-items: center; justify-content: space-between;">
											<span style="display: flex; align-items: center; gap: 10px;">
												<span style="font-weight: 600; color: #3c4758;"><?php esc_html_e( 'Payment Restriction Rule 2', 'woo-product-country-base-restrictions' ); ?></span>
											</span>
											<span style="display: flex; align-items: center; gap: 8px;">
												<span class="dashicons dashicons-menu" style="color: #999;"></span>
												<span class="dashicons dashicons-no-alt" style="color: #999;"></span>
												<span class="dashicons dashicons-admin-page" style="color: #999;"></span>
											</span>
										</span>
									</div>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="submit cbr-btn" style="margin-top: 15px;">
					<button type="button" class="button button-primary" disabled><?php esc_html_e( 'Add New Restriction Rule', 'woo-product-country-base-restrictions' ); ?> <span class="dashicons dashicons-plus"></span></button>
				</div>
			</div>
		</div>
	</div>
</section>
