<?php
/**
 * USPS Abstract API class file.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS\API;

use WC_Shipping_USPS;

require_once WC_USPS_API_DIR . 'class-first-class-limits.php';

/**
 * USPS Abstract API class.
 */
abstract class Abstract_API {

	/**
	 * Endpoint for the API.
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * USPS Shipping Method Class.
	 *
	 * @var WC_Shipping_USPS
	 */
	protected $shipping_method;

	/**
	 * Calculate shipping cost.
	 *
	 * @param array $package Package to ship.
	 *
	 * @return void
	 */
	abstract public function calculate_shipping( $package );

	/**
	 * Perform a request to check user ID validness.
	 *
	 * Error notice will displayed if user ID is invalid.
	 */
	abstract public function validate_credentials();

	/**
	 * Check if package dimensions and weight are within FCPIS limits.
	 *
	 * Logs a debug message if the package is outside limits. The USPS API does
	 * not validate FCPIS eligibility, so limits must be enforced client-side.
	 *
	 * @see https://pe.usps.com/text/imm/immc2_022.htm#ep2686853
	 *
	 * @param float $length Package length in inches.
	 * @param float $width  Package width in inches.
	 * @param float $height Package height in inches.
	 * @param float $weight Package weight in pounds.
	 *
	 * @return bool True if the package is within FCPIS limits, false if it should be skipped.
	 */
	protected function is_package_eligible_for_fcpis( float $length, float $width, float $height, float $weight ): bool {
		/**
		 * Filter to bypass FCPIS eligibility checks.
		 *
		 * Returning a non-null value will short-circuit the eligibility check
		 * and use the returned boolean as the result.
		 *
		 * @since 5.5.2
		 *
		 * @param bool|null $eligible Null to run default checks, or a boolean to override.
		 * @param float     $length   Package length in inches.
		 * @param float     $width    Package width in inches.
		 * @param float     $height   Package height in inches.
		 * @param float     $weight   Package weight in pounds.
		 */
		$override = apply_filters( 'woocommerce_shipping_usps_fcpis_eligible', null, $length, $width, $height, $weight );
		if ( null !== $override ) {
			return (bool) $override;
		}

		// Always enforce weight limit, even when dimensions are not set.
		if ( $weight > First_Class_Limits::INTL_MAX_WEIGHT_LB ) {
			$this->shipping_method->debug(
				sprintf(
					'Skipping FCPIS rate — package weight (%slb) exceeds limit (%slb).',
					$weight,
					First_Class_Limits::INTL_MAX_WEIGHT_LB
				)
			);
			return false;
		}

		$combined = $length + $width + $height;
		$sorted   = array( $length, $width, $height );
		rsort( $sorted );

		// Max length check.
		if ( $sorted[0] > First_Class_Limits::INTL_MAX_LENGTH ) {
			$this->shipping_method->debug(
				sprintf(
					'Skipping FCPIS rate — a package length (L=%s) exceeds max allowed length (%s).',
					$length,
					First_Class_Limits::INTL_MAX_LENGTH
				)
			);
			return false;
		}

		// Combined dimensions check.
		if ( $combined > First_Class_Limits::INTL_MAX_COMBINED_DIMENSIONS ) {
			$this->shipping_method->debug(
				sprintf(
					'Skipping FCPIS rate — a package combined dimension (combined=%s) exceeds max allowed (%s).',
					$combined,
					First_Class_Limits::INTL_MAX_COMBINED_DIMENSIONS
				)
			);
			return false;
		}

		// Minimum dimension checks.
		if (
			// Allow to pass when length and height is not set.
			$sorted[0] && $sorted[2]
			&& (
				$sorted[0] < First_Class_Limits::INTL_MIN_LENGTH
				|| $sorted[2] < First_Class_Limits::INTL_MIN_HEIGHT
			)
		) {
			$this->shipping_method->debug(
				sprintf(
					'Skipping FCPIS rate — package dimensions (L=%s, H=%s) below minimum (min length=%s, min height=%s).',
					$sorted[0],
					$sorted[2],
					First_Class_Limits::INTL_MIN_LENGTH,
					First_Class_Limits::INTL_MIN_HEIGHT
				)
			);
			return false;
		}

		return true;
	}
}
