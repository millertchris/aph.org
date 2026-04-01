<?php
/**
 * Utility class file.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS;

use WC_Countries;
use WC_Shipping_Zones;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

trait Util {

	/**
	 * Helper method to check whether given zone_id has usps method instance.
	 *
	 * @param int $zone_id Zone ID.
	 *
	 * @return bool True if given zone_id has usps method instance.
	 *
	 * @since 4.4.0
	 */
	public function is_zone_has_usps( int $zone_id ): bool {
		global $wpdb;

		// phpcs:ignore --- Need to use WPDB::get_var() to check the existing USPS in the shipping zone
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(instance_id) FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = 'usps' AND zone_id = %d", $zone_id ) ) > 0;
	}

	/**
	 * Is USPS Settings Page.
	 *
	 * @return bool
	 */
	public function is_usps_settings_page() {
		return isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'shipping' === $_GET['tab'] && isset( $_GET['section'] ) && 'usps' === $_GET['section']; // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Is USPS Instance Settings Page.
	 *
	 * @return bool
	 */
	public function is_usps_instance_settings_page() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$is_shipping_settings_page = isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['tab'] ) && 'shipping' === $_GET['tab'];

		if ( $is_shipping_settings_page && isset( $_GET['instance_id'] ) ) {
			$shipping_method = WC_Shipping_Zones::get_shipping_method( absint( $_GET['instance_id'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return isset( $shipping_method ) && 'usps' === $shipping_method->id;
	}

	/**
	 * Helper method to get the number of usps method instances.
	 *
	 * @return int The number of usps method instances
	 */
	public function instance_count(): int {
		global $wpdb;

		// phpcs:ignore --- Need to use WPDB::get_var() to count the existing USPS in the shipping zone
		return absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_shipping_zone_methods WHERE method_id = 'usps'" ) );
	}

	/**
	 * Helper method to check if there are existing usps method instances.
	 *
	 * @return bool
	 */
	public function instances_exist(): bool {
		return $this->instance_count() > 0;
	}

	/**
	 * Determines whether the specified country is defined by WooCommerce as requiring a postcode during checkout.
	 *
	 * @param string $country_code Country code.
	 *
	 * @return bool
	 */
	public function is_postcode_required_for_country( string $country_code ): bool {

		$wc_countries = WC()->countries ?? null;
		if ( ! $wc_countries instanceof WC_Countries ) {
			return true;
		}

		$country_locale_list = $wc_countries->get_country_locale();
		if (
			isset( $country_locale_list[ $country_code ]['postcode']['required'] )
			&& false === $country_locale_list[ $country_code ]['postcode']['required']
		) {
			return false;
		}

		return true;
	}
}
