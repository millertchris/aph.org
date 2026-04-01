<?php
/**
 * USPS First-Class service dimensional and weight limits.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS\API;

/**
 * First-Class service limits shared by REST and Legacy API implementations.
 */
final class First_Class_Limits {

	/**
	 * First Class Mail max weight in ounces.
	 */
	public const MAX_WEIGHT_OZ = 13;

	/**
	 * First Class Mail max length in inches.
	 */
	public const MAX_LENGTH = 15;

	/**
	 * First Class Mail max width in inches.
	 */
	public const MAX_WIDTH = 12;

	/**
	 * First Class Mail max height in inches.
	 */
	public const MAX_HEIGHT = 0.75;

	/**
	 * First-Class Package International Service max weight in pounds (4 lbs).
	 *
	 * @see https://pe.usps.com/text/imm/immc2_022.htm#ep2686853
	 */
	public const INTL_MAX_WEIGHT_LB = 4;

	/**
	 * First-Class Package International Service max length in inches (non-roll).
	 *
	 * @see https://pe.usps.com/text/imm/immc2_022.htm#ep2686853
	 */
	public const INTL_MAX_LENGTH = 24;

	/**
	 * First-Class Package International Service max combined dimensions (L+W+H) in inches (non-roll).
	 *
	 * @see https://pe.usps.com/text/imm/immc2_022.htm#ep2686853
	 */
	public const INTL_MAX_COMBINED_DIMENSIONS = 36;

	/**
	 * First-Class Package International Service minimum length in inches (non-roll).
	 *
	 * @see https://pe.usps.com/text/imm/immc2_022.htm#ep2686853
	 */
	public const INTL_MIN_LENGTH = 6;

	/**
	 * First-Class Package International Service minimum height in inches (non-roll).
	 *
	 * @see https://pe.usps.com/text/imm/immc2_022.htm#ep2686853
	 */
	public const INTL_MIN_HEIGHT = 4;
}
