<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $fzpcr;

$product_visibility = get_option('product_visibility');
if ( empty( $product_visibility ) ) {
	$product_visibility = 'hide_completely';
}
?>
<section id="cbr_content1" class="cbr_tab_section">
	<div class="cbr_tab_inner_container">
		<form method="post" id="cbr_setting_tab_form">
			<div class="accordion heading">
				<label>
					<?php esc_html_e( 'Catalog Visibility', 'woo-product-country-base-restrictions' ); ?>
				</label>
			</div>
			<div class="panel">
				<div class="main-panel">
					<table class="form-table catelog_visibility">
						<tbody>
							<tr valign="top">
								<th>
									<label><input name="product_visibility" value="hide_completely" type="radio" class="product_visibility" <?php checked( 'hide_completely', $product_visibility ); ?>/> <?php esc_html_e( 'Hide Completely', 'woo-product-country-base-restrictions' ); ?></label>
									<p class="desc"><?php esc_html_e( 'Completely hide restricted products from your store.', 'woo-product-country-base-restrictions' ); ?></p>
								</th>
							</tr>
						</tbody>
					</table>
					<div class="inside <?php echo ( 'hide_completely' == $product_visibility ) ? 'active' : ''; ?>">
						<?php $this->get_html_visibility_setting( $this->get_hide_completely_settings() ); ?>
					</div>
				</div>
				<div class="main-panel">
					<table class="form-table catelog_visibility">
						<tbody>
							<tr valign="top">
								<th>
									<label><input name="product_visibility" value="hide_catalog_visibility" type="radio" class="product_visibility"
									<?php checked( 'hide_catalog_visibility', $product_visibility ); ?>
									/> <?php esc_html_e( 'Hide catalog visibility', 'woo-product-country-base-restrictions' ); ?></label>
									<p class="desc"><?php esc_html_e( 'Hide restricted products from your shop and search results. Products remain accessible via direct links.', 'woo-product-country-base-restrictions' ); ?></p>
								</th>
							</tr>
						</tbody>
					</table>
					<div class="inside <?php echo ( 'hide_catalog_visibility' == $product_visibility ) ? 'active' : ''; ?>">
						<?php $this->get_html_visibility_setting( $this->get_product_settings() ); ?>
						<!-- PRO: Hide Restricted Product Price -->
						<table class="form-table">
							<tbody>
								<tr valign="top" class="border_1 cbr-pro-feature-row">
									<td>
										<input type="hidden" value="0"/>
										<input class="checkobox-input pro_feature" type="checkbox" disabled value="1"/>
										<label class="checkbox-label pro_feature"><?php esc_html_e( 'Hide Restricted Product Price', 'woo-product-country-base-restrictions' ); ?></label>
										<span class="woocommerce-help-tip tipTip pro_feature" title="<?php esc_attr_e( 'Enable this option to hide restricted product price.', 'woo-product-country-base-restrictions' ); ?>"></span>
										<span class="cbr-pro-row-actions">
											<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
											<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
										</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="main-panel" style="">
					<table class="form-table catelog_visibility">
						<tbody>
							<tr valign="top">
								<th>
									<label><input name="product_visibility" value="show_catalog_visibility" type="radio" class="product_visibility"
									<?php checked( 'show_catalog_visibility', $product_visibility ); ?>
									/> <?php esc_html_e( 'Catalog Visible (non purchasable)', 'woo-product-country-base-restrictions' ); ?></label>
									<p class="desc"><?php esc_html_e( 'Display restricted products in your catalog but make them non-purchasable.', 'woo-product-country-base-restrictions' ); ?></p>
								</th>
							</tr>
						</tbody>
					</table>
					<div class="inside <?php echo ( 'show_catalog_visibility' == $product_visibility ) ? 'active' : ''; ?>">
						<?php $this->get_html_visibility_setting( $this->get_product_catelog_settings() ); ?>
						<!-- PRO: Hide Restricted Product Price -->
						<table class="form-table">
							<tbody>
								<tr valign="top" class="border_1 cbr-pro-feature-row">
									<td>
										<input type="hidden" value="0"/>
										<input class="checkobox-input pro_feature" type="checkbox" disabled value="1"/>
										<label class="checkbox-label pro_feature"><?php esc_html_e( 'Hide Restricted Product Price', 'woo-product-country-base-restrictions' ); ?></label>
										<span class="woocommerce-help-tip tipTip pro_feature" title="<?php esc_attr_e( 'Enable this option to hide prices for restricted products.', 'woo-product-country-base-restrictions' ); ?>"></span>
										<span class="cbr-pro-row-actions">
											<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
											<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
										</span>
									</td>
								</tr>
								<!-- PRO: Allow Add To Cart -->
								<tr valign="top" class="border_1 cbr-pro-feature-row">
									<td>
										<input type="hidden" value="0"/>
										<input class="checkobox-input pro_feature" type="checkbox" disabled value="1"/>
										<label class="checkbox-label pro_feature"><?php esc_html_e( 'Allow Add To Cart', 'woo-product-country-base-restrictions' ); ?></label>
										<span class="woocommerce-help-tip tipTip pro_feature" title="<?php esc_attr_e( "Enable this option to allow add to cart the restricted products but the customer can't process to checkout.", 'woo-product-country-base-restrictions' ); ?>"></span>
										<span class="cbr-pro-row-actions">
											<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
											<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
										</span>
									</td>
								</tr>
								<!-- PRO: Sort products by availability -->
								<tr valign="top" class="border_1 cbr-pro-feature-row">
									<td>
										<input type="hidden" value="0"/>
										<input class="checkobox-input pro_feature" type="checkbox" disabled value="1"/>
										<label class="checkbox-label pro_feature"><?php esc_html_e( 'Sort products by availability (Available → Restricted)', 'woo-product-country-base-restrictions' ); ?></label>
										<span class="woocommerce-help-tip tipTip pro_feature" title="<?php esc_attr_e( 'Move restricted products to the end of product listings.', 'woo-product-country-base-restrictions' ); ?>"></span>
										<span class="cbr-pro-row-actions">
											<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
											<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
										</span>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<!-- PRO: Restrict product only on Place Order (Checkout) -->
				<div class="main-panel cbr-pro-feature-row">
					<table class="form-table catelog_visibility">
						<tbody>
							<tr valign="top">
								<th>
									<label class="pro_feature"><input name="" value="" type="radio" class="product_visibility"> <?php esc_html_e( 'Restrict product only on Place Order (Checkout)', 'woo-product-country-base-restrictions' ); ?></label>
									<span class="cbr-pro-row-actions">
										<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
										<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
									</span>
									<p class="desc pro_feature"><?php esc_html_e( 'Enable this option to apply country-based restriction only when the customer clicks the Place Order button on the checkout page.', 'woo-product-country-base-restrictions' ); ?></p>
								</th>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="accordion heading">
				<label>
					<?php esc_html_e( 'Restriction Settings', 'woo-product-country-base-restrictions' ); ?>
				</label>
			</div>
			<div class="panel">
				<table class="form-table general">
					<tbody>
						<?php $this->get_html_general_setting( $this->get_general_settings() ); ?>
						<!-- PRO: Bypass Restriction for Specific Users -->
						<tr valign="top" class="border_1 cbr-pro-feature-row">
							<th scope="row" class="titledesc" colspan="2">
								<label for="cbr_bypass_users_pro">
									<?php esc_html_e( 'Bypass Restriction for Specific Users', 'woo-product-country-base-restrictions' ); ?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_attr_e( 'Select individual users who should be exempt from all country-based restrictions. These users will see all products and can purchase without restriction regardless of their detected country.', 'woo-product-country-base-restrictions' ); ?>"></span>
								</label>
								<span class="cbr-pro-row-actions">
									<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
									<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
								</span>
								<fieldset>
									<select id="cbr_bypass_users_pro" class="select" disabled style="width: 100%; max-width: 500px; opacity: 0.5;">
										<option value=""><?php esc_html_e( 'Search for users...', 'woo-product-country-base-restrictions' ); ?></option>
									</select>
								</fieldset>
							</th>
						</tr>
					</tbody>
				</table>
				<table class="form-table visibility-message">
					<tbody>
						<?php $this->get_html_general_setting( $this->get_visibility_message_settings() ); ?>
						<!-- PRO: Cart restriction message -->
						<tr valign="top" class="border_1 cbr-pro-feature-row">
							<th scope="row" class="titledesc" colspan="2">
								<label for="wpcbr_cart_message_pro">
									<?php esc_html_e( 'Cart restriction message', 'woo-product-country-base-restrictions' ); ?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_attr_e( 'Displayed on the cart page when a product is removed due to country restrictions.', 'woo-product-country-base-restrictions' ); ?>"></span>
								</label>
								<span class="cbr-pro-row-actions">
									<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
									<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
								</span>
								<fieldset>
									<textarea rows="3" cols="20" class="input-text regular-input" disabled style="opacity: 0.5;" placeholder="<?php esc_attr_e( '{Product_Name} has been removed from your cart since it is not available for purchase to your Country.', 'woo-product-country-base-restrictions' ); ?>"></textarea>
								</fieldset>
								<p class="description"><?php esc_html_e( 'Available variable: {Product_Name}, {Product_name_with_link}', 'woo-product-country-base-restrictions' ); ?></p>
							</th>
						</tr>
						<!-- PRO: Category restriction message -->
						<tr valign="top" class="border_1 cbr-pro-feature-row">
							<th scope="row" class="titledesc" colspan="2">
								<label for="cbr_cat_default_message_pro">
									<?php esc_html_e( 'Category restriction message', 'woo-product-country-base-restrictions' ); ?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_attr_e( "Displayed on category/tag pages when restricted. Default: 'Sorry, products from this category are not available in your country.'", 'woo-product-country-base-restrictions' ); ?>"></span>
								</label>
								<span class="cbr-pro-row-actions">
									<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
									<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
								</span>
								<fieldset>
									<textarea rows="3" cols="20" class="input-text regular-input" disabled style="opacity: 0.5;" placeholder="<?php esc_attr_e( 'Sorry, products from this category are not available to purchase in your country.', 'woo-product-country-base-restrictions' ); ?>"></textarea>
								</fieldset>
								<p class="description"><?php esc_html_e( 'You can use shortcode [cbr_category_message] in your category template.', 'woo-product-country-base-restrictions' ); ?></p>
							</th>
						</tr>
						<!-- PRO: Product Listing Restriction Message -->
						<tr valign="top" class="border_1 cbr-pro-feature-row">
							<th scope="row" class="titledesc" colspan="2">
								<label for="cbr_catalog_default_message_pro">
									<?php esc_html_e( 'Product Listing Restriction Message', 'woo-product-country-base-restrictions' ); ?>
									<span class="woocommerce-help-tip tipTip" title="<?php esc_attr_e( "Display a custom message on product listing pages (shop, category, tag, or search results) when all products are restricted for the customer's detected country.", 'woo-product-country-base-restrictions' ); ?>"></span>
								</label>
								<span class="cbr-pro-row-actions">
									<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
									<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
								</span>
								<fieldset>
									<textarea rows="3" cols="20" class="input-text regular-input" disabled style="opacity: 0.5;" placeholder="<?php esc_attr_e( 'Sorry, products are not available in your country.', 'woo-product-country-base-restrictions' ); ?>"></textarea>
								</fieldset>
								<p class="description"><?php esc_html_e( 'Shown when no products are available due to country-based restrictions.', 'woo-product-country-base-restrictions' ); ?></p>
							</th>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- PRO: Country Detection Widget section -->
			<div class="cbr-pro-locked-section">
				<div class="accordion heading">
					<label>
						<?php esc_html_e( 'Country Detection Widget', 'woo-product-country-base-restrictions' ); ?>
						<span class="cbr-pro-row-actions">
							<span class="cbr-pro-badge"><?php esc_html_e( 'PRO', 'woo-product-country-base-restrictions' ); ?></span>
							<span class="cbr-pro-lock"><span class="dashicons dashicons-lock"></span></span>
						</span>
					</label>
				</div>
				<div class="panel">
					<table class="form-table country_detection_widget">
						<tbody>
							<tr valign="top" class="border_1">
								<td>
									<div class="row header-label" style="width: 100%;">
										<p><?php esc_html_e( 'Customize the country detection widget', 'woo-product-country-base-restrictions' ); ?></p>
										<p class="description"><?php esc_html_e( 'Show the detected country and allow customers to change their shipping country on any page.', 'woo-product-country-base-restrictions' ); ?></p>
									</div>
									<span class="row cbr-btn">
										<a href="https://www.zorem.com/product/country-based-restriction-pro/?utm_source=wp-admin&utm_medium=CBRPRO&utm_campaign=add-ons" class="button-primary" target="_blank"><?php esc_html_e( 'Upgrade to PRO', 'woo-product-country-base-restrictions' ); ?></a>
									</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="submit cbr-btn">
				<button name="save" class="cbr-save button-primary woocommerce-save-button" type="submit" value="Save changes"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
				<div class="spinner workflow_spinner" style="float:none"></div>
				<?php wp_nonce_field( 'cbr_setting_form_action', 'cbr_setting_form_nonce_field' ); ?>
				<input type="hidden" name="action" value="cbr_setting_form_update">
			</div>
		</form>
	</div>
</section>
