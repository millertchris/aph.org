<?php
/**
 * Plugin Name: WooCommerce USPS Shipping
 * Plugin URI: https://woocommerce.com/products/usps-shipping-method/
 * Description: Obtain shipping rates dynamically via the USPS Shipping API for your orders.
 * Version: 5.5.3
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.8
 * Tested up to: 6.9
 * WC requires at least: 10.4
 * WC tested up to: 10.6
 * Text Domain: woocommerce-shipping-usps
 * Copyright: © 2026 WooCommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Woo: 18657:83d1524e8f5f1913e58889f83d442c32
 *
 * https://www.usps.com/webtools/htm/Rate-Calculators-v1-5.htm
 *
 * @package woocommerce-shipping-usps
 */

/**
 * Please read the Terms and Conditions of Use, indicate your acceptance or non-acceptance and then click the SUBMIT button at the end to register for  * the APIs.
 *
 * TERMS AND CONDITIONS OF USE
 * USPS Web Tools APPLICATION PROGRAM INTERFACES (APIs)
 *
 * ACKNOWLEDGEMENT AND ACCEPTANCE. Read this carefully before registering to use the USPS Web Tools Application Program Interface (API) servers. By *  utilizing the APIs, you hereby ACCEPT ALL of the terms and conditions of this agreement.
 *
 * LICENSE GRANT. The United States Postal Service (USPS or Postal Service) grants to the Business User, including customers of Developer, and  Developer (* jointly referred to as "User"), a worldwide, nonexclusive, nontransferable, royalty-free license to interface with USPS Web Tool (API)  servers to use * the trademarks/logos and USPS data received via the interfaces in accordance with this agreement, the USPS Web Tool User Guides, and  the Software * Distributor Policy Guide.
 *
 * INTELLECTUAL PROPERTY RIGHTS. The sample code, the documentation, and the trademarks/logos provided on this site and in hardcopy form are the *  intellectual property of USPS protected under U.S. laws. The information and images presented may not be reproduced, republished, adopted, used, or *  modified under any circumstances.
 *
 * USE REQUIREMENTS FOR BUSINESS USER AND DEVELOPER.
 * * User agrees to use the USPS Web site in accordance with any additional requirements that may be posted on the Web site screens, emails, provided  in * the USPS Web Tool Kit User Guides, or in the Software Distributors Policy Guide.
 * * User agrees to use the USPS Web site, APIs and USPS data to facilitate USPS shipping transactions only.
 * * The trademarks/logos and USPS data received via the interfaces may not be used in any way that implies endorsement or sponsorship by USPS of the *  User or any of the User's products, goods or services.
 * * User may not use the interface in any way that adversely affects the performance or function of the USPS Web Tools (API) servers.
 * * User may not post or transmit information or materials that would violate rights of any third party or which contains a virus or other harmful *  component.
 * * User agrees to provide and keep updated, complete and accurate information about User upon registration for the APIs.
 *
 * ADDITIONAL USE REQUIREMENTS FOR BUSINESS USER.
 * * Business User is responsible to maintain the confidentiality of its password and ID as specified in the registration process.
 * * Business User may not package software which interfaces with any or all USPS APIs with password and ID for resale or distribution to others.
 * * Business User may reuse and distribute the API documentation and sample code in order to provide API access to customers and affiliates.
 *
 * ADDITIONAL USE REQUIREMENTS FOR DEVELOPER
 * * Developer may package software which interfaces with any or all USPS APIs with password and ID for resale or distribution to others only after *  registering with USPS as a Developer and agreeing to these Terms and Conditions of Use.
 * * Developers shall distribute these USPS Terms and Conditions of Use with its software to its customers and any other Business User.
 *
 * DISCLAIMER OF WARRANTIES. THE MATERIALS IN THE WEB TOOLS DOCMENTATION SITE (WWW.USPS.COM/WEBTOOLS), THE SOFTWARE DESCRIBED ON AND DISTRIBUTED FROM *  SAID SITE, AND THE APPLICATION PROGRAM INTERFACES DESCRIBED ON SAID SITE ARE PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND EITHER EXPRESS OR *  IMPLIED. TO THE FULLEST EXTENT PERMISSIBLE PURSUANT TO APPLICABLE LAW, USPS DISCLAIMS ALL WARRANTIES, EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED *  TO, IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE POSTAL SERVICE DOES NOT WARRANT OR REPRESENT THAT THE INFORMATION  * IS ACCURATE OR RELIABLE OR THAT THE SITE OR INTERFACES WILL BE FREE OF ERRORS OR VIRUSES.
 *
 * LIMITATION OF LIABILITY. UNDER NO CIRCUMSTANCES, INCLUDING BUT NOT LIMITED TO NEGLIGENCE, WILL USPS BE LIABLE FOR DIRECT, SPECIAL OR CONSEQUENTIAL *  DAMAGES THAT RESULT FROM THE USE OR INABILITY TO USE THE MATERIALS IN THE WEB TOOLS DOCUMENTATION SITE (WWW.USPS.COM/WEBTOOLS) OR THE APPLICATION *  PROGRAM INTERFACES REFERENCED AND DESCRIBED IN SAID SITE. IN NO EVENT SHALL USPS BE LIABLE TO A USER FOR ANY LOSS, DAMAGE OR CLAIM.
 *
 * TERMINATION. This agreement is effective until terminated by either party. Upon termination of this agreement, User shall immediately cease to use *  USPS APIs, all associated documentation, and trademarks/logos, and shall destroy all copies thereof in the control or possession of User.
 *
 * INDEMNIFICATION. User will indemnify and hold USPS harmless from all claims, damages, costs and expenses related to the operation of the User's Web  * site in conjunction with USPS Web site interface. User shall permit USPS to participate in any defense and shall seek USPS's written consent prior  to * entering into any settlement.
 *
 * MAILER RESPONSIBILITY. A mailer must comply with all applicable postal standards, including those in the Domestic Mail Manual and the International  * Mail Manual. In the event of a conflict between USPS Web site information and Mail Manual information, the USPS Mail Manuals will control.
 *
 * APPLICABLE LAW. This agreement shall be governed by United States Federal law.
 *
 * PRIVACY ACT STATEMENT. Collection of this information is authorized by 39 U.S.C. 401 and 404. This information will be used to provide User with *  Postal Service Web Tools information. The Postal Service may disclose this information to a government agency for law enforcement purposes; in a legal  * proceeding to which the USPS is a party or has an interest; to a government agency when relevant to a decision concerning employment, security *  clearances, contracts, licenses, grants or other benefits; to a person under contract with the USPS to fulfill an agency function; to an independent *  certified public accountant during an official audit of USPS finances, and for any other use authorized by law. Providing the information is *  voluntary; however, without the information, we cannot respond to your expressed interest in receiving, using or accessing the USPS Web Tools.
 *
 * I acknowledge, I have read, and understand the above terms and conditions and I am authorized to accept this agreement on behalf of stated User *  company.
 */

define( 'WC_USPS_VERSION', '5.5.3' ); // WRCS: DEFINED_VERSION.
define( 'WC_USPS_FILE', __FILE__ );
define( 'WC_USPS_ABSPATH', trailingslashit( __DIR__ ) );
define( 'WC_USPS_API_DIR', WC_USPS_ABSPATH . 'includes/api/' );

// Plugin init hook.
add_action( 'plugins_loaded', 'wc_shipping_usps_init' );

// Subscribe to automated translations.
add_filter( 'woocommerce_translations_updates_for_' . basename( WC_USPS_FILE, '.php' ), '__return_true' );
/**
 * Initialize plugin.
 */
function wc_shipping_usps_init() {

	require_once 'vendor/autoload_packages.php';

	if ( ! class_exists( 'WooCommerce' ) ) {
		wc_shipping_usps_show_woocommerce_deactivated_notice();

		return;
	}

	require_once WC_USPS_ABSPATH . 'includes/class-wc-usps.php';

	new WC_USPS();
}

/**
 * Show WooCommerce Deactivated Notice.
 */
function wc_shipping_usps_show_woocommerce_deactivated_notice() {
	/* translators: %s: WooCommerce link */
	echo '<div class="error"><p>' .
		sprintf(
		// translators: %1$s: WooCommerce link, %2$s: Add plugins link.
			esc_html__( 'WooCommerce USPS Shipping requires %1$s to be installed and active. Install and activate it %2$s.', 'woocommerce-shipping-usps' ),
			'<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>',
			'<a href="' . esc_url( admin_url( '/plugin-install.php?s=woocommerce&tab=search&type=term' ) ) . '" target="_blank">here</a>'
		)
		. '</p></div>';
}

register_activation_hook( WC_USPS_FILE, 'wc_shipping_usps_activation_check' );

/**
 * Check plugin can run
 */
function wc_shipping_usps_activation_check() {
	if ( ! function_exists( 'simplexml_load_string' ) ) {
		deactivate_plugins( basename( WC_USPS_FILE ) );
		wp_die( 'Sorry, but you cannot run this plugin, it requires the SimpleXML library installed on your server/hosting to function.' );
	}
}
