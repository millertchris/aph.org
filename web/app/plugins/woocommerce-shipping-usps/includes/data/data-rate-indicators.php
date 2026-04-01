<?php
/**
 * USPS Rate Indicators
 *
 * @package WC_Shipping_USPS
 */

/**
 * Filter to modify the USPS Rate Indicators.
 *
 * @var array List of rate indicators.
 *
 * @since 5.4.3
 */
return apply_filters(
	'wc_usps_rate_indicators',
	array(
		'flat-rate' => array(
			// Priority Mail Express Flat Rate.
			'E4', // Priority Mail Express Flat Rate Envelope - Post Office To Addressee.
			'E6', // Priority Mail Express Legal Flat Rate Envelope.
			'E7', // Priority Mail Express Legal Flat Rate Envelope Sunday / Holiday.
			// Priority Mail Flat Rate.
			'FA', // Legal Flat Rate Envelope.
			'FB', // Medium Flat Rate Box / Large Flat Rate Bag.
			'FE', // Flat Rate Envelope.
			'FP', // Padded Flat Rate Envelope.
			'FS', // Small Flat Rate Box.
			'PL', // Large Flat Rate Box.
			'PM', // Large Flat Rate Box APO/FPO/DPO.
			'SB', // Small Flat Rate Bag.
			// USPS Connect Local Flat Rate.
			'LC', // USPS Connect Local Single Piece.
			'LF', // USPS Connect Local Flat Rate Box.
			'LL', // USPS Connect Local Large Flat Rate Bag.
			'LO', // USPS Connect Local Oversized.
			'LS', // USPS Connect Local Small Flat Rate Bag.
		),
		'cubic'     => array(
			// Generic Cubic Pricing.
			'CP', // Cubic Parcel.
			'C1', // Cubic Pricing Tier 1.
			'C2', // Cubic Pricing Tier 2.
			'C3', // Cubic Pricing Tier 3.
			'C4', // Cubic Pricing Tier 4.
			'C5', // Cubic Pricing Tier 5.
			'P5', // Cubic Soft Pack Tier 1.
			'P6', // Cubic Soft Pack Tier 2.
			'P7', // Cubic Soft Pack Tier 3.
			'P8', // Cubic Soft Pack Tier 4.
			'P9', // Cubic Soft Pack Tier 5.
			'Q6', // Cubic Soft Pack Tier 6.
			'Q7', // Cubic Soft Pack Tier 7.
			'Q8', // Cubic Soft Pack Tier 8.
			'Q9', // Cubic Soft Pack Tier 9.
			'Q0', // Cubic Soft Pack Tier 10.
		),
	)
);
