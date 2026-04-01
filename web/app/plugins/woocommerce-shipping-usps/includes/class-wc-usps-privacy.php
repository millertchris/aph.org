<?php
/**
 * WC_USPS_Privacy class file.
 *
 * @package WC_Shipping_USPS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

/**
 * WC_USPS_Privacy class.
 */
class WC_Usps_Privacy extends WC_Abstract_Privacy {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( __( 'USPS', 'woocommerce-shipping-usps' ) );
	}

	/**
	 * Gets the message of the privacy to display.
	 */
	public function get_privacy_message() {
		// translators: %s is a link to WC USPS plugin documentation page.
		return wpautop( sprintf( __( 'By using this extension, you may be storing personal data or sharing data with an external service. <a href="%s" target="_blank">Learn more about how this works, including what you may want to include in your privacy policy.</a>', 'woocommerce-shipping-usps' ), 'https://docs.woocommerce.com/document/privacy-shipping/#woocommerce-shipping-usps' ) );
	}
}

new WC_Usps_Privacy();
