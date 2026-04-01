<?php
/**
 * USPS Legacy API class file.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS\API;

use WC_Product;
use WC_Shipping_USPS;
use WC_Shipping_USPS_Admin;
use WooCommerce\BoxPacker\Abstract_Packer;
use WooCommerce\BoxPacker\WC_Boxpack;

require_once WC_USPS_API_DIR . '/class-abstract-api.php';
require_once WC_USPS_API_DIR . '/class-first-class-limits.php';

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase --- USPS API provides an object with camelCase properties and method

/**
 * USPS REST API class.
 */
class REST_API extends Abstract_API {

	/**
	 * Standard rate requests.
	 *
	 * @var array
	 */
	private array $standard_rate_requests = array();

	/**
	 * First Class rate requests.
	 *
	 * @var array
	 */
	private array $first_class_rate_requests = array();

	/**
	 * Priority flat rate requests.
	 *
	 * @var array
	 */
	private array $priority_flat_rate_requests = array();

	/**
	 * Express flat rate requests.
	 *
	 * @var array
	 */
	private array $express_flat_rate_requests = array();

	/**
	 * Current package being processed.
	 *
	 * @var array
	 */
	private array $package = array();

	/**
	 * Whether the current shipment is domestic.
	 *
	 * @var bool
	 */
	private bool $is_domestic_shipment = true;

	/**
	 * Rate indicators.
	 *
	 * @var array
	 */
	private array $rate_indicators = array();

	/**
	 * Class constructor.
	 *
	 * @param WC_Shipping_USPS $shipping_method USPS shipping method object.
	 */
	public function __construct( WC_Shipping_USPS $shipping_method ) {
		$this->shipping_method = $shipping_method;
		$this->rate_indicators = include WC_USPS_ABSPATH . 'includes/data/data-rate-indicators.php';
	}

	/**
	 * Calculate shipping cost.
	 *
	 * This method processes each package individually, sending a separate API request
	 * for each package regardless of its size.
	 *
	 * @since   4.4.7
	 * @version 4.4.7
	 *
	 * @param array $package Package to ship.
	 *
	 * @return void
	 */
	public function calculate_shipping( $package ) {

		// Destination country is always required.
		if ( empty( $package['destination']['country'] ) ) {
			$this->shipping_method->debug( __( 'No destination country provided. USPS rates cannot be calculated.', 'woocommerce-shipping-usps' ) );

			return;
		}

		// Destination postcode is required for certain countries.
		if (
			$this->shipping_method->is_postcode_required_for_country( $package['destination']['country'] )
			&& empty( $package['destination']['postcode'] )
		) {
			$this->shipping_method->debug( __( 'No destination postcode provided. USPS rates cannot be calculated.', 'woocommerce-shipping-usps' ) );

			return;
		}

		$this->run_pre_calculation_setup( $package );

		$this->shipping_method->debug( '------------------Start Shipping Calculation------------------' );

		// Calculate rates for each rate type.
		$this->maybe_calculate_standard_rates();
		$this->maybe_calculate_priority_flat_rates();
		$this->maybe_calculate_express_flat_rates();
		$this->maybe_calculate_first_class_mail_rates();

		// Store the raw found rates, so we can pass to the filter later.
		$this->shipping_method->raw_found_rates = $this->shipping_method->found_rates;

		// Validate the found rates.
		$this->validate_found_rates();

		// Add rates.
		if ( ! empty( $this->shipping_method->found_rates ) ) {
			$this->check_found_rates();
		} elseif ( $this->shipping_method->fallback ) {
			$this->shipping_method->add_rate(
				array(
					'id'    => $this->shipping_method->get_rate_id() . '_fallback',
					'label' => $this->shipping_method->title,
					'cost'  => $this->shipping_method->fallback,
					'sort'  => 0,
				)
			);
		} else {
			$this->shipping_method->debug( __( 'Warning: The fallback amount is not set.', 'woocommerce-shipping-usps' ) );
		}

		$this->shipping_method->debug( '------------------End Shipping Calculation------------------' );
	}

	/**
	 * Perform a request to check REST API credentials.
	 */
	public function validate_credentials() {
		return $this->shipping_method->oauth->is_authenticated();
	}

	/**
	 * Get Standard Rate API requests.
	 *
	 * @return array
	 */
	private function get_standard_rate_api_requests(): array {
		if ( $this->shipping_method->is_package_overweight( $this->package, 70 ) ) {
			return array();
		}

		// Use the selected packing method from the instance settings.
		switch ( $this->shipping_method->packing_method ) {
			case 'box_packing':
				$requests = $this->box_shipping();
				break;
			case 'weight_based':
				$requests = $this->weight_based_shipping();
				break;
			case 'per_item':
			default:
				$requests = $this->per_item_shipping();
				break;
		}

		return $requests;
	}

	/**
	 * Get First Class Mail Rate API requests.
	 *
	 * @return array
	 */
	private function get_first_class_rate_api_requests(): array {
		// Use the selected packing method from the instance settings.
		switch ( $this->shipping_method->packing_method ) {
			case 'box_packing':
				$box_constraints = array(
					'outer_length' => First_Class_Limits::MAX_LENGTH,
					'outer_width'  => First_Class_Limits::MAX_WIDTH,
					'outer_height' => First_Class_Limits::MAX_HEIGHT,
					'max_weight'   => First_Class_Limits::MAX_WEIGHT_OZ / 16,
					'is_letter'    => true,
				);
				$requests        = $this->box_shipping( 'letter-rates', $box_constraints );
				break;
			case 'weight_based':
				$max_package_weight = First_Class_Limits::MAX_WEIGHT_OZ / 16;
				$requests           = $this->weight_based_shipping( 'letter-rates', $max_package_weight );
				break;
			case 'per_item':
			default:
				$requests = $this->per_item_shipping( 'letter-rates' );
				break;
		}

		return $requests;
	}

	/**
	 * Process API requests and update found rates.
	 *
	 * @param array  $requests Requests to process.
	 * @param string $endpoint API Endpoint.
	 *
	 * @return void
	 */
	private function process_api_requests_and_update_rates( array $requests, string $endpoint ): void {
		if ( empty( $requests ) ) {
			return;
		}

		// Get API responses for all requests.
		$api_responses = $this->get_api_responses( $requests, $endpoint );
		if ( empty( $api_responses ) ) {
			return;
		}

		// Parse the rates from the API responses.
		$rates = $this->parse_rates_from_api_responses( $api_responses );

		// Merge with existing rates.
		$this->shipping_method->found_rates = array_merge( $this->shipping_method->found_rates, $rates );
	}

	/**
	 * Maybe calculate standard rates for the package.
	 *
	 * @return void
	 */
	private function maybe_calculate_standard_rates(): void {
		if ( ! $this->shipping_method->enable_standard_services ) {
			return;
		}

		// Store the requests in the class property for later validation.
		$this->standard_rate_requests = $this->get_standard_rate_api_requests();

		// Process the requests and update rates.
		$this->process_api_requests_and_update_rates( $this->standard_rate_requests, 'options' );
	}

	/**
	 * Maybe calculate priority flat rates for the package.
	 *
	 * @return void
	 */
	private function maybe_calculate_priority_flat_rates(): void {
		if ( 'yes' !== $this->shipping_method->enable_flat_rate_boxes && 'priority' !== $this->shipping_method->enable_flat_rate_boxes ) {
			return;
		}

		// Store the requests in the class property for later validation.
		$this->priority_flat_rate_requests = $this->get_flat_rate_api_requests( 'priority' );

		// Process the requests and update rates.
		$this->process_api_requests_and_update_rates( $this->priority_flat_rate_requests, 'options' );
	}

	/**
	 * Maybe calculate express flat rates for the package.
	 *
	 * @return void
	 */
	private function maybe_calculate_express_flat_rates(): void {
		if ( 'yes' !== $this->shipping_method->enable_flat_rate_boxes && 'express' !== $this->shipping_method->enable_flat_rate_boxes ) {
			return;
		}

		// Store the requests in the class property for later validation.
		$this->express_flat_rate_requests = $this->get_flat_rate_api_requests( 'express' );

		// Process the requests and update rates.
		$this->process_api_requests_and_update_rates( $this->express_flat_rate_requests, 'options' );
	}

	/**
	 * Maybe calculate express flat rates for the package.
	 *
	 * @return void
	 */
	private function maybe_calculate_first_class_mail_rates(): void {
		if ( ! $this->shipping_method->has_enabled_first_class_service( $this->is_domestic_shipment ) ) {
			return;
		}

		if ( ! $this->is_package_eligible_for_first_class() ) {
			return;
		}

		// Store the requests in the class property for later validation.
		$this->first_class_rate_requests = $this->get_first_class_rate_api_requests();

		// Process the requests and update rates.
		$this->process_api_requests_and_update_rates( $this->first_class_rate_requests, 'letter-rates' );
	}

	/**
	 * Check if package is eligible for first class service.
	 *
	 * @return bool
	 */
	private function is_package_eligible_for_first_class(): bool {

		$packages = $this->package['contents'] ?? null;
		if ( ! is_array( $packages ) ) {
			return false;
		}

		foreach ( $packages as $package ) {
			$product = $package['data'] ?? null;
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			if ( ! $this->is_domestic_shipment && 0 < $package['line_total'] ) {
				/**
				 * Filter to allow First-Class Mail International rates for packages with declared value.
				 *
				 * By default, FCMI (codes 13, 14) is blocked for merchandise (items with value > $0)
				 * as per USPS rules. Return true to allow FCMI rates regardless of package value.
				 *
				 * @since 5.5.2
				 *
				 * @param bool $allow Whether to allow FCMI for merchandise. Default false.
				 */
				if ( ! apply_filters( 'woocommerce_shipping_usps_allow_fcmi_for_merchandise', false ) ) {
					return false;
				}
			}

			$weight_lbs = $this->shipping_method->get_product_weight( $product );
			$weight_oz  = wc_get_weight( $weight_lbs, 'oz', 'lbs' );
			if ( First_Class_Limits::MAX_WEIGHT_OZ < $weight_oz ) {
				return false;
			}

			$dimensions = $this->shipping_method->get_product_dimensions( $product );
			if ( First_Class_Limits::MAX_LENGTH < $dimensions[0] || First_Class_Limits::MAX_WIDTH < $dimensions[1] || First_Class_Limits::MAX_HEIGHT < $dimensions[2] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Validate found rates to ensure they have enough packages.
	 * Also adds unpacked item costs to rates if needed.
	 *
	 * @return void
	 */
	private function validate_found_rates(): void {
		if ( ! $this->shipping_method->found_rates ) {
			return;
		}

		$rate_id_prefix = $this->shipping_method->get_rate_id() . ':';

		foreach ( $this->shipping_method->found_rates as $key => $value ) {
			// Skip package count validation for flat rates.
			// Flat rates are already validated during packing (if items don't fit, no requests are made).
			// Cost comparison in check_found_rates() will determine which option is cheaper.
			// Note: After grouping in parse_rates_from_api_responses(), flat rate keys are "{rate_id}:flatrate:priority" or "{rate_id}:flatrate:express" (no trailing colon or box ID).
			$is_flat_rate = ( $key === $rate_id_prefix . 'flatrate:express' || $key === $rate_id_prefix . 'flatrate:priority' );

			if ( ! $is_flat_rate && isset( $this->standard_rate_requests ) ) {
				// Standard (non-flat-rate) rates must match the number of standard requests.
				if ( $value['packages'] < count( $this->standard_rate_requests ) ) {
					$this->shipping_method->debug( "Unsetting {$key} - too few packages." );
					unset( $this->shipping_method->found_rates[ $key ] );
				}
			}

			if ( $this->shipping_method->unpacked_item_costs && ! empty( $this->shipping_method->found_rates[ $key ] ) ) {
				// translators: %s is a USPS rate key.
				$this->shipping_method->debug( sprintf( __( 'Adding unpacked item costs to rate %s', 'woocommerce-shipping-usps' ), $key ) );
				$this->shipping_method->found_rates[ $key ]['cost'] += $this->shipping_method->unpacked_item_costs;
			}
		}
	}

	/**
	 * Generate XML requests using box packing method.
	 *
	 * @version 5.4.0
	 *
	 * @param string $endpoint API endpoint name.
	 * @param array  $constraints Filter packing boxes by given constraints.
	 *
	 * @return array Array of JSON requests.
	 */
	private function box_shipping( $endpoint = 'options', $constraints = array() ): array {

		$requests  = array();
		$boxes     = $this->get_boxes( $constraints );
		$boxpacker = ( new WC_Boxpack( 'in', 'lbs', $this->shipping_method->box_packer_library ) )->get_packer();

		// Define boxes.
		foreach ( $boxes as $key => $box ) {
			$newbox = $boxpacker->add_box( $box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight'] );
			$newbox->set_id( isset( $box['name'] ) ? $box['name'] : $key );
			$newbox->set_inner_dimensions( $box['inner_length'], $box['inner_width'], $box['inner_height'] );
			if ( $box['max_weight'] ) {
				$newbox->set_max_weight( $box['max_weight'] );
			}
			if ( $box['is_letter'] ) {
				$newbox->set_type( 'envelope' );
			}
		}

		if ( ! is_array( $this->package['contents'] ) ) {
			return $requests;
		}

		// Add items.
		foreach ( $this->package['contents'] as $values ) {
			$product = $values['data'];

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			if ( ! $product->needs_shipping() ) {
				// translators: %d is a product ID.
				$this->shipping_method->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'woocommerce-shipping-usps' ), $product->get_id() ) );
				continue;
			}

			$weight            = $this->shipping_method->get_product_weight( $product );
			$quantity          = (float) $values['quantity'];
			$quantity_floored  = (int) floor( $quantity );
			$quantity_fraction = $quantity - $quantity_floored;

			$declared_value = $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE );
			$declared_value = '' === $declared_value ? $product->get_price() : $declared_value;
			if ( 1 <= $quantity_floored ) {
				$dimensions = $this->shipping_method->get_product_dimensions( $product );
				$boxpacker->add_item(
					$dimensions[0],
					$dimensions[1],
					$dimensions[2],
					$weight,
					$declared_value,
					array(),
					$quantity_floored
				);
			}
			if ( 0 < $quantity_fraction ) {
				$dimensions = $this->shipping_method->get_product_dimensions( $product, $quantity_fraction );
				$boxpacker->add_item(
					$dimensions[0],
					$dimensions[1],
					$dimensions[2],
					$weight * $quantity_fraction,
					$declared_value * $quantity_fraction,
					array(),
					1
				);
			}
		}
		/**
		 * Allow boxpack to be overriden by devs.
		 *
		 * @see   https://github.com/woocommerce/woocommerce-shipping-usps/issues/155
		 *
		 * @var Abstract_Packer $boxpacker Boxpacker object.
		 *
		 * @since 4.4.12
		 */
		$boxpacker = apply_filters( 'woocommerce_shipping_usps_boxpack_before_pack', $boxpacker );

		// Pack it.
		$boxpacker->pack();

		// Get packages.
		$box_packages = $boxpacker->get_packages();
		foreach ( $box_packages as $key => $box_package ) {
			if ( true === $box_package->unpacked ) {
				$this->shipping_method->debug( 'Unpacked Item' );

				switch ( $this->shipping_method->unpacked_item_handling ) {
					case 'fallback':
						// No request, just a fallback, if the fallback amount is set.
						if ( $this->shipping_method->fallback ) {
							$this->shipping_method->unpacked_item_costs += (float) $this->shipping_method->fallback;
						} else {
							$this->shipping_method->debug( __( 'Warning: The fallback amount is not set.', 'woocommerce-shipping-usps' ) );
						}
						continue 2;
					case 'ignore':
						// No request.
						continue 2;
					case 'abort':
						// No requests!
						return array();
				}
			} else {
				$this->shipping_method->debug( 'Packed ' . $box_package->id );
			}

			$weight         = $box_package->weight;
			$box_dimensions = array( $box_package->length, $box_package->width, $box_package->height );
			rsort( $box_dimensions, SORT_NUMERIC );

			$package_id = $this->generate_package_id( $key, 1, $box_dimensions, $weight, 'api' );

			$request = $this->build_api_request( $this->package['destination'], $box_dimensions, $weight, $box_package->value, 'ALL', $endpoint );

			$requests[ $package_id ] = $request;
		}

		return $requests;
	}

	/**
	 * Generate shipping request for weights only.
	 *
	 * @param string     $endpoint           The endpoint to use for the request. Default 'options'.
	 * @param float|null $max_package_weight Maximum package weight in pounds. Default null.
	 *
	 * @return array
	 */
	private function weight_based_shipping( $endpoint = 'options', $max_package_weight = null ): array {
		$country  = $this->package['destination']['country'] ?? '';
		$domestic = in_array( $country, $this->shipping_method->domestic, true );
		$items    = array(
			'regular' => array(),
			'large'   => array(),
		);

		// Phase 1: Build deterministic item list — split fractional quantities and categorize by size upfront.
		foreach ( $this->package['contents'] as $item_id => $values ) {
			$product = $values['data'];

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			if ( ! $product->needs_shipping() ) {
				// translators: %d is a product ID.
				$this->shipping_method->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'woocommerce-shipping-usps' ), $product->get_id() ) );
				continue;
			}

			$weight         = $this->shipping_method->get_product_weight( $product );
			$quantity       = (float) $values['quantity'];
			$quantity_floor = (int) floor( $quantity );
			$fractional_qty = $quantity - $quantity_floor;

			$declared_value = $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE );
			$declared_value = '' === $declared_value ? $product->get_price() : $declared_value;

			// Whole units (floor of qty).
			if ( $quantity_floor >= 1 ) {
				$dimensions = $this->shipping_method->get_product_dimensions( $product );
				$category   = max( $dimensions ) > 12 ? 'large' : 'regular';

				$items[ $category ][ $item_id ] = array(
					'dimensions'     => $dimensions,
					'weight'         => $weight,
					'declared_value' => (float) $declared_value,
					'quantity'       => $quantity_floor,
				);
			}

			// Fractional remainder with pre-adjusted weight, value, and dimensions.
			if ( 0 < $fractional_qty ) {
				$frac_dimensions = $this->shipping_method->get_product_dimensions( $product, $fractional_qty );
				$category        = max( $frac_dimensions ) > 12 ? 'large' : 'regular';

				$items[ $category ][ $item_id . '-frac' ] = array(
					'dimensions'     => $frac_dimensions,
					'weight'         => $weight * $fractional_qty,
					'declared_value' => (float) $declared_value * $fractional_qty,
					'quantity'       => 1,
				);
			}
		}

		$requests = array();

		// Phase 2a: Large items — one request per item.
		foreach ( $items['large'] as $item_id => $item ) {
			$package_id              = $this->generate_package_id( 'large-item-' . $item_id, $item['quantity'], $item['dimensions'], $item['weight'], 'api' );
			$request                 = $this->build_api_request( $this->package['destination'], $item['dimensions'], $item['weight'], $item['declared_value'], 'ALL', $endpoint );
			$requests[ $package_id ] = $request;
		}

		// Phase 2b: Regular items — pack by weight into shipping packages.
		if ( ! empty( $items['regular'] ) ) {
			$max_weight        = $max_package_weight ?? ( ( $domestic || 'MX' === $country ) ? 70 : 44 );
			$shipping_packages = array();
			$remaining_weight  = $max_weight;
			$pkg_dimensions    = array( 0, 0, 0 );
			$items_value       = 0;
			$items_weight      = 0;
			$index             = 0;

			foreach ( $items['regular'] as $item_id => $item ) {
				for ( $i = 0; $i < $item['quantity']; $i++ ) {
					// If this unit doesn't fit, finalize current package first.
					if ( $items_weight > 0 && $remaining_weight < $item['weight'] ) {
						$shipping_packages[ $index ] = array(
							'dimensions' => $pkg_dimensions,
							'weight'     => $items_weight,
							'value'      => round( $items_value, wc_get_price_decimals() ),
						);

						$remaining_weight = $max_weight;
						$pkg_dimensions   = array( 0, 0, 0 );
						$items_value      = 0;
						$items_weight     = 0;
						++$index;
					}

					$pkg_dimensions[0] = max( $pkg_dimensions[0], $item['dimensions'][0] );
					$pkg_dimensions[1] = max( $pkg_dimensions[1], $item['dimensions'][1] );
					$pkg_dimensions[2] = max( $pkg_dimensions[2], $item['dimensions'][2] );
					$remaining_weight -= $item['weight'];
					$items_value      += $item['declared_value'];
					$items_weight     += $item['weight'];
				}
			}

			// Finalize the last package with any remaining items.
			if ( $items_weight > 0 ) {
				$shipping_packages[ $index ] = array(
					'dimensions' => $pkg_dimensions,
					'weight'     => $items_weight,
					'value'      => round( $items_value, wc_get_price_decimals() ),
				);
			}

			// Deduplicate identical packages — one request with qty > 1 instead of N requests.
			$unique_packages = array();
			foreach ( $shipping_packages as $pkg ) {
				$dedup_key = implode( '_', array( $pkg['dimensions'][0], $pkg['dimensions'][1], $pkg['dimensions'][2], $pkg['weight'], $pkg['value'] ) );
				if ( isset( $unique_packages[ $dedup_key ] ) ) {
					++$unique_packages[ $dedup_key ]['qty'];
				} else {
					$unique_packages[ $dedup_key ] = array_merge( $pkg, array( 'qty' => 1 ) );
				}
			}

			$idx = 0;
			foreach ( $unique_packages as $pkg ) {
				$package_id              = $this->generate_package_id( 'weight-package-' . $idx, $pkg['qty'], $pkg['dimensions'], $pkg['weight'], 'api' );
				$request                 = $this->build_api_request( $this->package['destination'], $pkg['dimensions'], $pkg['weight'], $pkg['value'], 'ALL', $endpoint );
				$requests[ $package_id ] = $request;
				++$idx;
			}
		}

		return $requests;
	}

	/**
	 * Per item shipping.
	 *
	 * @param string $endpoint The endpoint to use for the request. Default 'options'.
	 *
	 * @return array
	 */
	private function per_item_shipping( $endpoint = 'options' ): array {
		$requests = array();

		if ( ! is_array( $this->package['contents'] ) ) {
			return $requests;
		}

		// Get weight of order.
		foreach ( $this->package['contents'] as $item_id => $values ) {
			$product = $values['data'];

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			if ( ! $product->needs_shipping() ) {
				// translators: %d is a product ID.
				$this->shipping_method->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'woocommerce-shipping-usps' ), $product->get_id() ) );
				continue;
			}

			$weight           = $this->shipping_method->get_product_weight( $product );
			$quantity         = (float) $values['quantity'];
			$quantity_floored = (int) floor( $quantity );
			$fractional_qty   = $quantity - $quantity_floored;

			$declared_value = $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE );
			$declared_value = (float) ( '' === $declared_value ? $product->get_price() : $declared_value );

			if ( 1 <= $quantity_floored ) {
				$dimensions = $this->shipping_method->get_product_dimensions( $product );
				$package_id = $this->generate_package_id( $item_id, $quantity_floored, $dimensions, $weight, 'api' );
				$request    = $this->build_api_request( $this->package['destination'], $dimensions, $weight, $declared_value, 'ALL', $endpoint );

				$requests[ $package_id ] = $request;
			}

			if ( 0 < $fractional_qty ) {
				$dimensions = $this->shipping_method->get_product_dimensions( $product, $fractional_qty );
				$package_id = $this->generate_package_id( $item_id, 1, $dimensions, $weight * $fractional_qty, 'api' );
				$request    = $this->build_api_request( $this->package['destination'], $dimensions, $weight * $fractional_qty, $declared_value * $fractional_qty, 'ALL', $endpoint );

				$requests[ $package_id ] = $request;
			}
		}

		return $requests;
	}

	/**
	 * Filter boxes by given constraints.
	 *
	 * @param  array $constraints Constraints array.
	 *
	 * @return array
	 */
	private function get_boxes( $constraints ) {
		$boxes = $this->shipping_method->boxes;
		if ( empty( $constraints ) ) {
			return $boxes;
		}

		$filtered_boxes = array_filter(
			$boxes,
			function ( $box ) use ( $constraints ) {
				foreach ( $constraints as $constraint_key => $constraint_value ) {
					if (
						! isset( $box[ $constraint_key ] )
						|| ( is_numeric( $constraint_value ) && $box[ $constraint_key ] > $constraint_value )
						|| ( is_bool( $constraint_value ) && $box[ $constraint_key ] !== $constraint_value )
					) {
						return false;
					}
				}

				return true;
			}
		);

		return $filtered_boxes;
	}

	/**
	 * Build a USPS API request array for the package.
	 *
	 * @param array  $destination    Destination of the package.
	 * @param array  $dimensions     Package dimensions.
	 * @param float  $weight         Package weight.
	 * @param float  $declared_value Package Value.
	 * @param string $mail_class     Mail class.
	 * @param string $endpoint       The endpoint to use for the request. Default 'options'.
	 *
	 * @return array
	 */
	public function build_api_request( array $destination, array $dimensions, float $weight, float $declared_value, string $mail_class = 'ALL', $endpoint = 'options' ): array {
		switch ( $endpoint ) {
			case 'letter-rates':
				if (
					$this->shipping_method->is_card( $dimensions[0], $dimensions[1], $dimensions[2] )
					&& 3.5 >= wc_get_weight( $weight, 'oz', 'lbs' )
				) {
					return $this->build_letters_api_request( $destination, $dimensions, $weight, $declared_value, 'CARDS' );
				} elseif (
					$this->shipping_method->is_letter( $dimensions[0], $dimensions[1], $dimensions[2] )
					&& 3.5 >= wc_get_weight( $weight, 'oz', 'lbs' )
				) {
					return $this->build_letters_api_request( $destination, $dimensions, $weight, $declared_value, 'LETTERS' );
				} elseif (
					$this->shipping_method->is_large_envelope( $dimensions[0], $dimensions[1], $dimensions[2] )
					&& First_Class_Limits::MAX_WEIGHT_OZ >= wc_get_weight( $weight, 'oz', 'lbs' )
				) {
					return $this->build_letters_api_request( $destination, $dimensions, $weight, $declared_value, 'FLATS' );
				} else {
					return array();
				}
			default:
				return $this->build_options_api_request( $destination, $dimensions, $weight, $declared_value, $mail_class );
		}
	}

	/**
	 * Build a USPS API request array for the package.
	 *
	 * @param array  $destination    Destination of the package.
	 * @param array  $dimensions     Package dimensions.
	 * @param float  $weight         Package weight.
	 * @param float  $declared_value Package Value.
	 * @param string $mail_class     Mail class.
	 *
	 * @return array
	 */
	public function build_options_api_request( array $destination, array $dimensions, float $weight, float $declared_value, string $mail_class ): array {
		// Sort dimensions so that the largest dimension is key 0 and smallest is key 2.
		rsort( $dimensions, SORT_NUMERIC );

		$request = array(
			'pricingOptions'     => array(
				array(
					'priceType' => 'ONLINE' === $this->shipping_method->shippingrates ? 'COMMERCIAL' : 'RETAIL',
				),
			),
			'originZIPCode'      => $this->shipping_method->origin,
			'packageDescription' => array(
				'weight'       => $weight,
				'length'       => (float) $dimensions[0],
				'width'        => (float) $dimensions[1],
				'height'       => (float) $dimensions[2],
				'girth'        => (float) max( $this->shipping_method->get_girth( $dimensions ), 1 ),
				'packageValue' => $declared_value,
				'mailClass'    => $mail_class,
				'mailingDate'  => wp_date( 'Y-m-d', strtotime( 'tomorrow' ) ),
			),
		);

		// Depending on the destination, we need to add different fields.
		if ( $this->is_domestic_shipment ) {
			$request['destinationZIPCode'] = $destination['postcode'];
		} else {
			$request['foreignPostalCode']      = $destination['postcode'];
			$request['destinationCountryCode'] = $destination['country'];
		}

		return $request;
	}

	/**
	 * Build a USPS API request array for the package.
	 *
	 * @param array  $destination    Destination of the package.
	 * @param array  $dimensions     Package dimensions.
	 * @param float  $weight         Package weight.
	 * @param float  $declared_value Package Value.
	 * @param string $mail_class     Mail class.
	 *
	 * @return array
	 */
	public function build_letters_api_request( array $destination, array $dimensions, float $weight, float $declared_value, string $mail_class ): array {
		// Sort dimensions so that the largest dimension is key 0 and smallest is key 2.
		rsort( $dimensions, SORT_NUMERIC );

		$request = array(
			'weight'             => wc_get_weight( $weight, 'oz', 'lbs' ),
			'length'             => (float) $dimensions[0],
			'height'             => (float) $dimensions[1],
			'thickness'          => (float) $dimensions[2],
			'processingCategory' => $mail_class,
			'itemValue'          => $declared_value,
			'mailingDate'        => wp_date( 'Y-m-d', strtotime( 'tomorrow' ) ),
		);

		if ( ! $this->is_domestic_shipment && ! empty( $destination['country'] ) ) {
			$request['destinationCountryCode'] = $destination['country'];
		}

		return $request;
	}

	/**
	 * Check if a service code represents a domestic flat rate service.
	 *
	 * @param string $service_code The service code to check.
	 *
	 * @return bool True if the service is domestic, false otherwise.
	 */
	private function is_domestic_flat_rate_service( string $service_code ): bool {
		return 'd' === substr( $service_code, 0, 1 );
	}

	/**
	 * Check if a service ID represents a domestic service.
	 *
	 * @param string $service_id The service ID to check.
	 *
	 * @return bool True if the service is domestic, false if it's international.
	 */
	private function is_domestic_service( string $service_id ): bool {
		return 0 === strpos( $service_id, 'D_' );
	}

	/**
	 * Map USPS REST 4-character SKU prefixes to legacy numeric service IDs.
	 *
	 * This keeps backward compatibility with merchants’ saved settings, which
	 * are keyed by legacy numeric IDs from data-services.php.
	 *
	 * @param string $sku_prefix The first 4 characters of the REST SKU.
	 * @return string Legacy numeric service ID (as string) or the original code if unmapped.
	 */
	private function map_rest_sku_to_legacy_code( string $sku_prefix ): string {
		$map = array(
			// Domestic.
			'DFX'  => '0', // First Class Flats.
			'DFL'  => '78', // First Class Letter Metered.
			'DUX'  => '1058', // Ground Advantage.
			'DEX'  => '3',    // Priority Mail Express.
			'DEF'  => '3',    // Priority Mail Express.
			'DMX'  => '6',    // Media Mail.
			'DLX'  => '7',    // Library Mail.
			'DPX'  => '1',    // Priority Mail.
			'DPU'  => '1',    // Priority Mail.
			'DOX'  => '1',    // Priority Mail.
			// 'DVX' => '4',    // Parcel Select.
			// 'DBP' => '5',    // Bound Printed Matter.
			// International.
			'IEX'  => '1',    // Priority Mail Express International.
			'IEF'  => '1',    // Priority Mail Express International.
			'IPX'  => '2',    // Priority Mail International.
			'IPF'  => '2',    // Priority Mail International.
			'IFXL' => '13',   // First-Class Mail International Letters.
			'IFXF' => '14',   // First-Class Mail International Flats.
			'IFXP' => '15',   // First-Class Package International Service.
			'IFXC' => '21',   // First-Class Mail International Postcards.
		);

		// Check 4 letter SKU first, if not present try 3 letter SKU.
		$legacy_code = $map[ $sku_prefix ] ?? $map[ substr( $sku_prefix, 0, 3 ) ] ?? $sku_prefix;

		return $legacy_code;
	}

	/**
	 * Get the flat rate box name from the service code.
	 *
	 * @param string $service_code The service code to get the box name for.
	 *
	 * @return string The flat rate box name or empty string if not found.
	 */
	public function get_flat_rate_box_name_from_service_code( string $service_code ): string {
		if ( empty( $this->shipping_method->flat_rate_boxes ) || ! isset( $this->shipping_method->flat_rate_boxes[ $service_code ] ) ) {
			return '';
		}

		return $this->shipping_method->flat_rate_boxes[ $service_code ]['name'];
	}

	/**
	 * Get the expected USPS rateIndicator for a flat rate box service ID.
	 *
	 * We prefer using mailClass + rateIndicator to match rates for flat rate boxes.
	 * If a service ID is not mapped, return an empty string.
	 *
	 * @param string $service_id Flat rate box service ID (e.g., d16, d29, d44, d17, d17b, d28, d22, d13, d30, d63).
	 * @return string Expected rateIndicator or empty string if unknown.
	 */
	private function get_rate_indicator_for_box( string $service_id ): string {
		if (
			! empty( $this->shipping_method->flat_rate_boxes )
			&& isset( $this->shipping_method->flat_rate_boxes[ $service_id ] )
			&& isset( $this->shipping_method->flat_rate_boxes[ $service_id ]['rate_indicator'] )
			&& '' !== (string) $this->shipping_method->flat_rate_boxes[ $service_id ]['rate_indicator']
		) {
			return strtoupper( (string) $this->shipping_method->flat_rate_boxes[ $service_id ]['rate_indicator'] );
		}

		return '';
	}

	/**
	 * Add flat rate boxes to the boxpacker object.
	 *
	 * @param Abstract_Packer $boxpacker         BoxPacker object.
	 * @param string          $flat_rate_service 'priority' or 'express'.
	 *
	 * @return void
	 */
	private function add_flat_rate_boxes_to_boxpacker( Abstract_Packer $boxpacker, string $flat_rate_service ): void {

		if ( empty( $this->shipping_method->flat_rate_boxes ) ) {
			return;
		}

		$added_boxes = array();

		// Define boxes.
		foreach ( $this->shipping_method->flat_rate_boxes as $service_code => $box ) {

			if ( $box['service'] !== $flat_rate_service ) {
				continue;
			}

			// Only add flat rate boxes that work for the intended destination country.
			if ( $this->is_domestic_shipment !== $this->is_domestic_flat_rate_service( $service_code ) ) {
				continue;
			}

			$newbox = $boxpacker->add_box(
				$box['length'],
				$box['width'],
				$box['height'],
				$this->shipping_method->get_empty_box_weight( $service_code, $box['weight'] ),
				$box['max_weight']
			);

			$newbox->set_id( $service_code );

			if ( isset( $box['volume'] ) && method_exists( $newbox, 'set_volume' ) ) {
				$newbox->set_volume( $box['volume'] );
			}

			if ( isset( $box['type'] ) && method_exists( $newbox, 'set_type' ) ) {
				$newbox->set_type( $box['type'] );
			}

			$added_boxes[] = $service_code . ' - ' . $box['name'] . ' (' . $box['length'] . 'x' . $box['width'] . 'x' . $box['height'] . ')';
		}

		// Add custom flat rate boxes.
		$added_boxes = array_merge( $added_boxes, $this->add_custom_flat_rate_boxes_to_boxpacker( $boxpacker, $flat_rate_service ) );

		$this->shipping_method->debug( 'Calculating USPS Flat Rate with boxes: ' . implode( ', ', $added_boxes ) );
	}

	/**
	 * Add custom flat rate boxes to the boxpacker object.
	 *
	 * @param Abstract_Packer $boxpacker         BoxPacker object.
	 * @param string          $flat_rate_service 'priority' or 'express'.
	 *
	 * @return array List of added box descriptions for debug logging.
	 */
	private function add_custom_flat_rate_boxes_to_boxpacker( Abstract_Packer $boxpacker, string $flat_rate_service ): array {

		if ( empty( $this->shipping_method->enable_custom_flat_rate_boxes ) || empty( $this->shipping_method->custom_flat_rate_boxes ) ) {
			return array();
		}

		$added_boxes = array();

		foreach ( $this->shipping_method->custom_flat_rate_boxes as $box ) {
			$flat_rate_type = $box['flat_rate_type'] ?? '';

			if ( ! isset( $this->shipping_method->flat_rate_boxes[ $flat_rate_type ] ) ) {
				continue;
			}

			$predefined_box = $this->shipping_method->flat_rate_boxes[ $flat_rate_type ];

			if ( $predefined_box['service'] !== $flat_rate_service ) {
				continue;
			}

			if ( $this->is_domestic_shipment !== $this->is_domestic_flat_rate_service( $flat_rate_type ) ) {
				continue;
			}

			$newbox = $boxpacker->add_box(
				$box['length'],
				$box['width'],
				$box['height'],
				$this->shipping_method->get_empty_box_weight( $flat_rate_type, floatval( $box['box_weight'] ) ),
				$box['max_weight']
			);

			$newbox->set_id( $flat_rate_type );

			if ( isset( $predefined_box['type'] ) && method_exists( $newbox, 'set_type' ) ) {
				$newbox->set_type( $predefined_box['type'] );
			}

			$added_boxes[] = $flat_rate_type . ' (custom) - ' . $box['name'] . ' (' . $box['length'] . 'x' . $box['width'] . 'x' . $box['height'] . ')';
		}

		return $added_boxes;
	}

	/**
	 * Generate request xml for flat rate packages.
	 *
	 * @param string $flat_rate_service 'priority' or 'express'.
	 *
	 * @return array
	 */
	private function get_flat_rate_api_requests( string $flat_rate_service ): array {

		$boxpacker = ( new WC_Boxpack( 'in', 'lbs', $this->shipping_method->box_packer_library ) )->get_packer();

		$requests = array();

		$this->add_flat_rate_boxes_to_boxpacker( $boxpacker, $flat_rate_service );

		// Add items.
		foreach ( $this->package['contents'] as $values ) {

			$product = $values['data'];

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			if ( ! $product->needs_shipping() ) {
				// translators: %d is a product ID.
				$this->shipping_method->debug( sprintf( __( 'Product #%d is virtual. Skipping.', 'woocommerce-shipping-usps' ), $product->get_id() ) );
				continue;
			}

			$dimensions = $this->shipping_method->get_product_dimensions( $product );
			$weight     = $this->shipping_method->get_product_weight( $product );

			$boxpacker->add_item(
				$dimensions[0],
				$dimensions[1],
				$dimensions[2],
				$weight,
				$product->get_price(),
				array(),
				(int) ceil( (float) $values['quantity'] )
			);
		}

		// Pack it.
		$boxpacker->pack();

		// Get packages.
		$packages = $boxpacker->get_packages();

		foreach ( $packages as $key => $package ) {

			if ( true === $package->unpacked ) {
				$this->shipping_method->debug( 'Unpacked Item, can\'t fit in any ' . $flat_rate_service . ' flat rate boxes. Disabling flat rate services.' );

				return array();
			}

			$this->shipping_method->debug( 'Packed ' . $this->get_flat_rate_box_name_from_service_code( $package->id ) );

			$dimensions = array(
				$package->length,
				$package->width,
				$package->height,
			);

			rsort( $dimensions, SORT_NUMERIC );

			$weight = $package->weight;

			$mail_class = 'express' === $flat_rate_service ? 'PRIORITY_MAIL_EXPRESS' : 'PRIORITY_MAIL';

			if ( ! $this->is_domestic_shipment ) {
				$mail_class = 'express' === $flat_rate_service ? 'PRIORITY_MAIL_EXPRESS_INTERNATIONAL' : 'PRIORITY_MAIL_INTERNATIONAL';
			}

			$request = $this->build_api_request(
				$this->package['destination'],
				$dimensions,
				$weight,
				$package->value,
				$mail_class
			);

			$package_id = $this->generate_package_id( $key, 1, $dimensions, $weight, 'flatrate', $flat_rate_service, $package->id );

			$requests[ $package_id ] = $request;
		}

		return $requests;
	}

	/**
	 * Parse responses from USPS API.
	 *
	 * @since 4.4.40
	 *
	 * @param array $api_responses Array of API responses.
	 *
	 * @return array
	 */
	private function parse_rates_from_api_responses( array $api_responses ): array {

		$rates_to_prepare = array();
		foreach ( $api_responses as $request_id => $api_response ) {
			if ( ! $api_response || ! is_string( $api_response ) ) {
				continue;
			}

			// Extract rate options from the API response.
			$rate_options = $this->parse_rate_options_from_api_response( $api_response );
			if ( empty( $rate_options ) ) {
				$this->shipping_method->debug( 'No rate options found for request ID ' . $request_id );

				continue;
			}

			// Extract rates from rate options.
			$rates_to_prepare = array_merge( $rates_to_prepare, $this->extract_rates_from_rate_options( $rate_options, $request_id ) );
		}

		$rates = array();
		foreach ( $rates_to_prepare as $rate ) {
			$rate_code = $rate['code'];
			$rate_id   = $rate['id'];
			$rate_cost = $rate['cost'];
			$meta_data = $rate['meta_data'];
			$rate_name = $rate['label'];
			$sort      = $rate['sort'];

			/**
			 * If $rate_id contains 'flatrate', remove the last segment (after the final colon)
			 * so that all Flat Rate shipping methods (e.g. priority, express) are grouped together.
			 */
			if ( false !== strpos( $rate_id, 'flatrate' ) ) {
				$rate_id = implode( ':', array_slice( explode( ':', $rate_id ), 0, -1 ) );
			}

			// Name adjustment.
			if ( ! empty( $this->shipping_method->custom_services[ $rate_code ]['name'] ) ) {
				$rate_name = $this->shipping_method->custom_services[ $rate_code ]['name'];
			}

			// Merging.
			if ( isset( $rates[ $rate_id ] ) ) {
				$rate_cost = $rate_cost + $rates[ $rate_id ]['cost'];
				$packages  = 1 + $rates[ $rate_id ]['packages'];
			} else {
				$packages = 1;
			}

			// Package metadata.
			$meta_data_value = array();
			if ( $meta_data ) {
				// translators: %s is number of rates found.
				$meta_key = sprintf( __( 'Package %s', 'woocommerce-shipping-usps' ), $packages );

				if ( isset( $rates[ $rate_id ] ) && array_key_exists( 'meta_data', $rates[ $rate_id ] ) ) {
					$meta_data_value = $rates[ $rate_id ]['meta_data'];
				}

				$meta_data_value[ $meta_key ] = $meta_data['package_description'] ?? '';

				foreach ( array( 'length', 'width', 'height', 'weight' ) as $detail ) {
					// If no value, don't save anything.
					if ( empty( $meta_data[ 'package_' . $detail ] ) ) {
						continue;
					}

					// The new value to add to the JSON string.
					$new_value = $meta_data[ 'package_' . $detail ];

					// If this rate already has metadata, decode it and add the new value to the array.
					if ( ! empty( $meta_data_value[ '_package_' . $detail ] ) ) {
						$value                                    = json_decode( $meta_data_value[ '_package_' . $detail ], true );
						$value[ $meta_key ]                       = $new_value;
						$meta_data_value[ '_package_' . $detail ] = wp_json_encode( $value );
						continue;
					}

					$meta_data_value[ '_package_' . $detail ] = wp_json_encode( array( $meta_key => $new_value ) );
				}
			}

			// Add rate description and mail class from the API response.
			if ( ! empty( $meta_data['rate_description'] ) ) {
				$meta_data_value['rate_description'] = $meta_data['rate_description'];
			}
			if ( ! empty( $meta_data['rate_mail_class'] ) ) {
				$meta_data_value['rate_mail_class'] = $meta_data['rate_mail_class'];
			}

			// Add packing method type information.
			$meta_data_value = array( 'Packing method' => $this->shipping_method->get_packing_method_label() ) + $meta_data_value;

			// Sort.
			if ( isset( $this->shipping_method->custom_services[ $rate_code ]['order'] ) && is_numeric( $this->shipping_method->custom_services[ $rate_code ]['order'] ) ) {
				$sort = $this->shipping_method->custom_services[ $rate_code ]['order'];
			}

			$rates[ $rate_id ] = array(
				'id'        => $rate_id,
				'label'     => $rate_name,
				'cost'      => $rate_cost,
				'sort'      => $sort,
				'packages'  => $packages,
				'meta_data' => $meta_data_value,
			);
		}

		return $rates;
	}

	/**
	 * Parse rate options from API response.
	 *
	 * @param string $api_response API response (body) JSON string.
	 *
	 * @return array Rate options.
	 *
	 * @since   4.4.7
	 * @version 4.4.8
	 */
	private function parse_rate_options_from_api_response( string $api_response ): array {

		if ( empty( $api_response ) ) {
			return array();
		}

		$results      = array();
		$response_obj = json_decode( $api_response );

		if (
			isset( $response_obj->pricingOptions[0]->shippingOptions )
			&& is_array( $response_obj->pricingOptions[0]->shippingOptions )
		) {
			$shipping_options = $response_obj->pricingOptions[0]->shippingOptions;

			// No shipping options, return.
			if ( empty( $shipping_options ) ) {
				$this->shipping_method->debug( 'Invalid request; no rates returned' );

				return array();
			}

			$results = array();
			foreach ( $shipping_options as $shipping_option ) {
				$rate_options = (
					isset( $shipping_option->rateOptions ) &&
					is_array( $shipping_option->rateOptions )
				) ? $shipping_option->rateOptions : array();

				if ( empty( $rate_options ) ) {
					continue;
				}

				foreach ( $rate_options as $rate_option ) {
					$rates = (
						isset( $rate_option->rates ) &&
						is_array( $rate_option->rates )
					) ? $rate_option->rates : array();

					if ( empty( $rates ) ) {
						continue;
					}

					$results[] = $rate_option;
				}
			}
		} elseif ( isset( $response_obj->rates ) && is_array( $response_obj->rates ) ) {
			$results[] = $response_obj;
		}

		return $results;
	}

	/**
	 * Set up necessary properties before starting the rates calculations.
	 *
	 * @param array $package The shipment.
	 *
	 * @return void
	 */
	public function run_pre_calculation_setup( array $package ): void {
		// Set the package property.
		$this->package = $package;

		// Set the is_domestic_shipment property.
		$this->is_domestic_shipment = $this->shipping_method->is_domestic( $this->package['destination']['country'] );

		$this->shipping_method->unpacked_item_costs = 0;

		// Initialize found_rates as an empty array.
		$this->shipping_method->found_rates = array();
	}

	/**
	 * Prepare rate.
	 *
	 * @param mixed  $rate_code Rate code.
	 * @param mixed  $rate_id   Rate ID.
	 * @param mixed  $rate_name Rate name.
	 * @param mixed  $rate_cost Cost.
	 * @param string $meta_data Rate meta data.
	 * @param int    $sort      Sort order.
	 *
	 * @return void
	 */
	private function prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost, $meta_data = '', $sort = 999 ) {
		// Name adjustment.
		if ( ! empty( $this->shipping_method->custom_services[ $rate_code ]['name'] ) ) {
			$rate_name = $this->shipping_method->custom_services[ $rate_code ]['name'];
		}

		// Merging.
		if ( isset( $this->shipping_method->found_rates[ $rate_id ] ) ) {
			$rate_cost = $rate_cost + $this->shipping_method->found_rates[ $rate_id ]['cost'];
			$packages  = 1 + $this->shipping_method->found_rates[ $rate_id ]['packages'];
		} else {
			$packages = 1;
		}

		// Package metadata.
		$meta_data_value = array();
		if ( $meta_data ) {
			// translators: %s is number of rates found.
			$meta_key = sprintf( __( 'Package %s', 'woocommerce-shipping-usps' ), $packages );

			if ( isset( $this->shipping_method->found_rates[ $rate_id ] ) && array_key_exists( 'meta_data', $this->shipping_method->found_rates[ $rate_id ] ) ) {
				$meta_data_value = $this->shipping_method->found_rates[ $rate_id ]['meta_data'];
			}

			$meta_data_value[ $meta_key ] = $meta_data['package_description'] ?? '';

			foreach ( array( 'length', 'width', 'height', 'weight' ) as $detail ) {
				// If no value, don't save anything.
				if ( empty( $meta_data[ 'package_' . $detail ] ) ) {
					continue;
				}

				// The new value to add to the JSON string.
				$new_value = $meta_data[ 'package_' . $detail ];

				// If this rate already has metadata, decode it and add the new value to the array.
				if ( ! empty( $meta_data_value[ '_package_' . $detail ] ) ) {
					$value                                    = json_decode( $meta_data_value[ '_package_' . $detail ], true );
					$value[ $meta_key ]                       = $new_value;
					$meta_data_value[ '_package_' . $detail ] = wp_json_encode( $value );
					continue;
				}

				$meta_data_value[ '_package_' . $detail ] = wp_json_encode( array( $meta_key => $new_value ) );
			}
		}

		// Add rate description and mail class from the API response.
		if ( ! empty( $meta_data['rate_description'] ) ) {
			$meta_data_value['rate_description'] = $meta_data['rate_description'];
		}
		if ( ! empty( $meta_data['rate_mail_class'] ) ) {
			$meta_data_value['rate_mail_class'] = $meta_data['rate_mail_class'];
		}

		// Add packing method type information.
		$meta_data_value = array( 'Packing method' => $this->shipping_method->get_packing_method_label() ) + $meta_data_value;

		// Sort.
		if ( isset( $this->shipping_method->custom_services[ $rate_code ]['order'] ) ) {
			$sort = $this->shipping_method->custom_services[ $rate_code ]['order'];
		}

		$this->shipping_method->found_rates[ $rate_id ] = array(
			'id'        => $rate_id,
			'label'     => $rate_name,
			'cost'      => $rate_cost,
			'sort'      => $sort,
			'packages'  => $packages,
			'meta_data' => $meta_data_value,
		);
	}

	/**
	 * Check found rates.
	 *
	 * @version 4.4.7
	 */
	private function check_found_rates() {
		// Only offer one Priority Mail rate (standard vs flat rate).
		$found_rates           = $this->shipping_method->found_rates;
		$priority_standard_key = '';
		$priority_flat_keys    = array();
		$express_standard_key  = '';
		$express_flat_keys     = array();

		if ( isset( $found_rates[ $this->shipping_method->get_rate_id() . ':D_PRIORITY_MAIL' ] ) ) {
			$priority_standard_key = $this->shipping_method->get_rate_id() . ':D_PRIORITY_MAIL';
		} elseif ( isset( $found_rates[ $this->shipping_method->get_rate_id() . ':I_PRIORITY_MAIL' ] ) ) {
			$priority_standard_key = $this->shipping_method->get_rate_id() . ':I_PRIORITY_MAIL';
		}

		foreach ( $found_rates as $key => $_rate ) {
			if ( 0 === strpos( $key, $this->shipping_method->get_rate_id() . ':flatrate:priority' ) ) {
				$priority_flat_keys[] = $key;
			}
		}

		if ( $priority_standard_key && ! empty( $priority_flat_keys ) ) {
			$min_flat_key  = '';
			$min_flat_cost = null;
			foreach ( $priority_flat_keys as $key ) {
				$cost = isset( $found_rates[ $key ] ) ? $found_rates[ $key ]['cost'] : null;
				if ( null === $min_flat_cost || ( null !== $cost && $cost < $min_flat_cost ) ) {
					$min_flat_cost = $cost;
					$min_flat_key  = $key;
				}
			}

			if ( null !== $min_flat_cost && $min_flat_cost < $found_rates[ $priority_standard_key ]['cost'] ) {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL api rate - flat rate box is cheaper.' );
				unset( $this->shipping_method->found_rates[ $priority_standard_key ] );
				// Keep only the cheapest flat-rate option.
				foreach ( $priority_flat_keys as $key ) {
					if ( $key !== $min_flat_key ) {
						unset( $this->shipping_method->found_rates[ $key ] );
					}
				}
			} else {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL flat rate - api rate is cheaper.' );
				foreach ( $priority_flat_keys as $key ) {
					unset( $this->shipping_method->found_rates[ $key ] );
				}
			}
		}

		// Only offer one Priority Mail Express rate (standard vs flat rate).
		if ( isset( $found_rates[ $this->shipping_method->get_rate_id() . ':D_EXPRESS_MAIL' ] ) ) {
			$express_standard_key = $this->shipping_method->get_rate_id() . ':D_EXPRESS_MAIL';
		} elseif ( isset( $found_rates[ $this->shipping_method->get_rate_id() . ':I_EXPRESS_MAIL' ] ) ) {
			$express_standard_key = $this->shipping_method->get_rate_id() . ':I_EXPRESS_MAIL';
		}

		foreach ( $found_rates as $key => $_rate ) {
			if ( 0 === strpos( $key, $this->shipping_method->get_rate_id() . ':flatrate:express' ) ) {
				$express_flat_keys[] = $key;
			}
		}

		if ( $express_standard_key && ! empty( $express_flat_keys ) ) {
			$min_flat_key  = '';
			$min_flat_cost = null;
			foreach ( $express_flat_keys as $key ) {
				$cost = isset( $found_rates[ $key ] ) ? $found_rates[ $key ]['cost'] : null;
				if ( null === $min_flat_cost || ( null !== $cost && $cost < $min_flat_cost ) ) {
					$min_flat_cost = $cost;
					$min_flat_key  = $key;
				}
			}

			if ( null !== $min_flat_cost && $min_flat_cost < $found_rates[ $express_standard_key ]['cost'] ) {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL EXPRESS api rate - flat rate box is cheaper.' );
				unset( $this->shipping_method->found_rates[ $express_standard_key ] );
				// Keep only the cheapest express flat-rate option.
				foreach ( $express_flat_keys as $key ) {
					if ( $key !== $min_flat_key ) {
						unset( $this->shipping_method->found_rates[ $key ] );
					}
				}
			} else {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL EXPRESS flat rate - api rate is cheaper.' );
				foreach ( $express_flat_keys as $key ) {
					unset( $this->shipping_method->found_rates[ $key ] );
				}
			}
		}

		/**
		 * Filter to modify the found rates.
		 *
		 * @param array $found_rates List of found rates.
		 * @param array $raw_found_rates List of found rates before being processed.
		 * @param string $offer_rates Rates to offer. Valid values are "all" and "cheapest".
		 *
		 * @since 4.4.64
		 */
		$this->shipping_method->found_rates = apply_filters( 'woocommerce_shipping_usps_found_rates', $this->shipping_method->found_rates, $this->shipping_method->raw_found_rates, $this->shipping_method->offer_rates );

		if ( 'all' === $this->shipping_method->offer_rates ) {
			uasort( $this->shipping_method->found_rates, array( $this->shipping_method, 'sort_rates' ) );

			foreach ( $this->shipping_method->found_rates as $key => $rate ) {
				$this->shipping_method->add_rate( $rate );
			}
		} else {
			$cheapest_rate = '';

			foreach ( $this->shipping_method->found_rates as $key => $rate ) {
				if ( ! $cheapest_rate || $cheapest_rate['cost'] > $rate['cost'] ) {
					$cheapest_rate = $rate;

					/*
					 * Maybe get the custom label for the cheapest rate,
					 * otherwise use the specific rate label with (USPS) appended.
					 */
					$split_key = explode( ':', $key );
					if ( ! empty( $split_key[1] ) && array_key_exists( $split_key[1], $this->shipping_method->custom_services ) && ! empty( $this->shipping_method->custom_services[ $split_key[1] ]['name'] ) ) {
						$cheapest_rate['label'] = $this->shipping_method->custom_services[ $split_key[1] ]['name'];
					} else {
						// translators: %1$s is Label rate, %2$s is the shipping method title.
						$cheapest_rate['label'] = sprintf( __( '%1$s (%2$s)', 'woocommerce-shipping-usps' ), $cheapest_rate['label'], $this->shipping_method->title );
					}
				}
			}

			$this->shipping_method->add_rate( $cheapest_rate );
		}
	}

	/**
	 * Sends an API request to USPS.
	 *
	 * @since 4.4.7
	 *
	 * @param array  $request             The API request to send.
	 * @param string $endpoint            The API endpoint.
	 * @param bool   $force_token_refresh Optional. Whether to force retrieval of a new access token. Default false.
	 *
	 * @return string|bool The response body on success, or false on failure.
	 */
	private function send_api_request( array $request, string $endpoint, bool $force_token_refresh = false ) {

		// Log the request.
		$this->shipping_method->debug( 'USPS Rate REST Request:', $request );
		$token = $this->shipping_method->oauth->get_access_token( $force_token_refresh );

		if ( empty( $token ) ) {
			return false;
		}

		$headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => 'Bearer ' . $token,
		);

		/**
		 * Filter to modify the USPS REST API request.
		 *
		 * @param array $request The api request.
		 * @param array $package The package to ship.
		 *
		 * @since 5.5.1
		 */
		$body = wp_json_encode( apply_filters( 'woocommerce_shipping_usps_rest_api_request', $request, $this->package ) );

		switch ( $endpoint ) {
			case 'letter-rates':
				$endpoint_uri = $this->is_domestic_shipment
					? '/prices/v3/letter-rates/search'
					: '/international-prices/v3/letter-rates/search';
				break;
			case 'options':
			default:
				$endpoint_uri = '/shipments/v3/options/search';
				break;
		}

		$this->shipping_method->debug( 'Endpoint: ' . $endpoint_uri );

		$response = wp_remote_post(
			$this->shipping_method::API_URL . $endpoint_uri,
			array(
				'headers' => $headers,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			// phpcs:ignore --- print_r() only being used when on debug mode.
			$error_messages = array();
			if ( is_array( $response->get_error_messages() ) ) {
				foreach ( $response->get_error_messages() as $error_message ) {
					$error_messages[] = json_decode( $error_message, true );
				}
			}

			$this->shipping_method->debug( 'USPS REQUEST FAILED. Error message(s):', $error_messages );

			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		$response_msg  = wp_remote_retrieve_response_message( $response );

		$this->shipping_method->debug(
			'USPS Rate REST Response:',
			array(
				'CODE'    => $response_code,
				'MESSAGE' => $response_msg,
				'BODY'    => $this->maybe_simplify_response_body( $response_body ),
			)
		);

		if ( 200 !== $response_code ) {
			// If authentication failed (401) and we haven't retried yet, refresh the access token and try once more.
			if ( 401 === $response_code && false === $force_token_refresh ) {
				return $this->send_api_request( $request, $endpoint, true );
			}

			return false;
		}

		return $response_body;
	}

	/**
	 * Maybe simplify the response body.
	 *
	 * @param string $response_body The response body.
	 *
	 * @return mixed
	 */
	private function maybe_simplify_response_body( string $response_body ) {
		$response_body = json_decode( $response_body, true );

		if ( ! isset( $response_body['rateOptions'] ) || ! is_array( $response_body['rateOptions'] ) ) {
			return $response_body;
		}

		foreach ( $response_body['rateOptions'] as $key => $rate ) {
			if ( ! isset( $rate['extraServices'] ) ) {
				continue;
			}
			unset( $response_body['rateOptions'][ $key ]['extraServices'] );
		}

		return $response_body;
	}

	/**
	 * Generate a package ID for the request.
	 *
	 * Contains qty and dimension info so we can look at it again later when it
	 * comes back from USPS if needed.
	 *
	 * @param string  $id           Package ID.
	 * @param int     $qty          Quantity.
	 * @param float[] $dimensions   Array[L, W, H].
	 * @param float   $weight       Weight.
	 * @param string  $request_type "flatrate" or "api".
	 * @param string  $service      "express" or "priority".
	 * @param string  $service_id   Used by international flat rate requests to define which box to use.
	 *
	 * @return string
	 */
	public function generate_package_id( $id, $qty, $dimensions, $weight, $request_type = '', $service = '', $service_id = '' ) {
		$l = $dimensions[0] ?? 0;
		$w = $dimensions[1] ?? 0;
		$h = $dimensions[2] ?? 0;
		return implode( ':', array( $id, $qty, $l, $w, $h, $weight, $request_type, $service, $service_id ) );
	}

	/**
	 * Get the USPS API responses.
	 *
	 * @param array  $api_requests API requests.
	 * @param string $endpoint API endpoint.
	 *
	 * @return array
	 */
	private function get_api_responses( array $api_requests, string $endpoint ): array {
		$api_responses = array();

		foreach ( $api_requests as $request_id => $api_request ) {
			if ( empty( $api_request ) ) {
				continue;
			}

			$transient_key   = 'usps_quote_' . md5( wp_json_encode( $api_request ) );
			$cached_response = get_transient( $transient_key );

			/**
			 * Filter to enable or disable API response caching.
			 *
			 * @param bool  $enable_caching Whether to enable API response caching. Default true.
			 * @param array $api_request    The API request being processed.
			 * @param array $package        The package being shipped.
			 *
			 * @since 4.4.10
			 */
			$enable_caching = apply_filters( 'woocommerce_shipping_usps_enable_api_response_caching', true, $api_request, $this->package );

			// If caching is disabled, force a new API request.
			if ( ! $enable_caching ) {
				$cached_response = false;
			}

			// If there's a cached response, use it.
			if ( false !== $cached_response ) {
				$this->shipping_method->debug(
					'USPS Rate REST Response (Cached)',
					array( json_decode( $cached_response ) )
				);

				$api_responses[ $request_id ] = $cached_response;

				continue;
			}

			$response = $this->send_api_request( $api_request, $endpoint );
			if ( ! $response ) {
				$this->shipping_method->debug(
					'USPS Rate REST Response (Failed)',
					array(
						'request' => $api_request,
					)
				);

				continue;
			}

			/**
			 * Cache the response for one week if response contains rates.
			 *
			 * @var int $transient_expiration Transient expiration in seconds.
			 *
			 * @since 4.4.9
			 */
			$transient_expiration = apply_filters( 'woocommerce_shipping_usps_transient_expiration', DAY_IN_SECONDS * 7 );
			set_transient( $transient_key, $response, $transient_expiration );

			$api_responses[ $request_id ] = $response;
		}

		return $api_responses;
	}

	/**
	 * Parse rates from rate options.
	 *
	 * @param array  $rate_options Rate options.
	 * @param string $request_id   Request ID.
	 *
	 * @return array
	 */
	private function extract_rates_from_rate_options( array $rate_options, string $request_id ): array {
		$rates = array();

		// Get request ID parts.
		$request_id_parts = explode( ':', $request_id );
		if ( count( $request_id_parts ) < 6 ) {
			return array();
		}

		list( $package_item_id, $cart_item_qty, $package_length, $package_width, $package_height, $package_weight, $request_type, $service_type, $service_id ) = $request_id_parts;

		$cart_item_qty = (int) ceil( $cart_item_qty );

		// Use this array to pass metadata to the order item.
		$meta_data                   = array();
		$meta_data['package_length'] = $package_length;
		$meta_data['package_width']  = $package_width;
		$meta_data['package_height'] = $package_height;
		$meta_data['package_weight'] = $package_weight;

		if ( 'flatrate' === $request_type ) {

			$box_rate_indicator = $this->get_rate_indicator_for_box( $service_id );

			foreach ( $rate_options as $rate_option ) {
				$rate                = $rate_option->rates[0] ?? null;
				$rate_indicator      = $rate->rateIndicator ?? null;
				$processing_category = $rate->processingCategory ?? null;
				$rate_description    = isset( $rate->description ) ? sanitize_text_field( $rate->description ) : null;
				$rate_mail_class     = isset( $rate->mailClass ) ? sanitize_text_field( $rate->mailClass ) : null;
				// If the rate indicator doesn't match, skip this rate.
				if (
					null === $rate
					|| $box_rate_indicator !== (string) $rate_indicator
					|| ! in_array( $processing_category, array( 'FLATS', 'MACHINABLE', 'NONSTANDARD' ), true ) // TODO: Look into handling rates with processingCategory=NONSTANDARD.
				) {
					continue;
				}

				$rate_id = implode(
					':',
					array(
						$this->shipping_method->get_rate_id(),
						$request_type,
						$service_type,
						$service_id,
					)
				);

				if ( 'express' === $service_type ) {
					$label = $this->shipping_method->get_option( 'flat_rate_express_title', ( $this->is_domestic_shipment ? '' : 'International ' ) . 'Priority Mail Express Flat Rate&#0174;' );
					$sort  = - 1;
				} else {
					$label = $this->shipping_method->get_option( 'flat_rate_priority_title', ( $this->is_domestic_shipment ? '' : 'International ' ) . 'Priority Mail Flat Rate&#0174;' );
					$sort  = - 2;
				}

				$rate_cost = (float) $rate_option->totalBasePrice;

				// Fees.
				if ( ! empty( $this->shipping_method->flat_rate_fee ) ) {
					$sym = substr( $this->shipping_method->flat_rate_fee, 0, 1 );
					$fee = '-' === $sym ? substr( $this->shipping_method->flat_rate_fee, 1 ) : $this->shipping_method->flat_rate_fee;
					if ( strstr( $fee, '%' ) ) {
						$fee = str_replace( '%', '', $fee );
						if ( '-' === $sym ) {
							$rate_cost = $rate_cost - ( $rate_cost * ( floatval( $fee ) / 100 ) );
						} else {
							$rate_cost = $rate_cost + ( $rate_cost * ( floatval( $fee ) / 100 ) );
						}
					} else {
						$rate_cost = ( '-' === $sym ) ? ( $rate_cost - floatval( $fee ) ) : ( $rate_cost + floatval( $fee ) );
					}

					if ( $rate_cost < 0 ) {
						$rate_cost = 0;
					}
				}

				$meta_data['package_description'] = wp_strip_all_tags( htmlspecialchars_decode( (string) $rate->description, ENT_COMPAT ) );
				$meta_data['rate_description']    = $rate_description;
				$meta_data['rate_mail_class']     = $rate_mail_class;

				$rates[] = array(
					'code'      => $rate->rateIndicator,
					'id'        => $rate_id,
					'label'     => $label,
					'cost'      => $rate_cost,
					'meta_data' => $meta_data,
					'sort'      => $sort,
				);
			}
		} else {
			// Loop defined services.
			foreach ( $this->shipping_method->services as $service_id => $service ) {

				if ( $this->is_domestic_shipment !== $this->is_domestic_service( $service_id ) ) {
					continue;
				}

				$service_name        = trim( str_replace( 'USPS', '', $service['name'] ?? '' ) );
				$rate_code           = (string) $service_id;
				$rate_id             = $this->shipping_method->get_rate_id() . ':' . $rate_code;
				$rate_name           = $service_name . " ({$this->shipping_method->title})";
				$rate_description    = null;
				$rate_mail_class     = null;
				$rate_cost           = null;
				$svc_commitment      = null;
				$quoted_package_name = null;

				// Enforce FCPIS eligibility.
				if (
					! $this->is_domestic_shipment
					&& 'I_FIRST_CLASS_P' === $rate_code
					&& ! $this->is_package_eligible_for_fcpis(
						(float) $package_length,
						(float) $package_width,
						(float) $package_height,
						(float) $package_weight
					)
				) {
					continue;
				}

				// Loop through rate quotes returned from USPS.
				foreach ( $rate_options as $rate_option ) {
					$rate                = $rate_option->rates[0] ?? null;
					$processing_category = $rate->processingCategory ?? null;
					$facility_type       = $rate->destinationEntryFacilityType ?? 'NONE';
					$rate_description    = isset( $rate->description ) ? sanitize_text_field( $rate->description ) : null;
					$rate_mail_class     = isset( $rate->mailClass ) ? sanitize_text_field( $rate->mailClass ) : null;

					/*
					1. Skip when no rate.
					or
					2. Skip when processing category is Open & Distribute rate for standard single-piece shipments.
					These rates require special bulk mail arrangements (PMOD program) and are not applicable to typical e-commerce shipments.
					or
					3. Skip the rate if this is a domestic shipment and the facility type is not "NONE" based on https://pe.usps.com/BusinessMail101?ViewName=DestinationEntry.
					*/
					if (
						null === $rate
						|| 'OPEN_AND_DISTRIBUTE' === $processing_category
						|| ( $this->is_domestic_shipment && 'NONE' !== $facility_type )
					) {
						continue;
					}

					$quoted_service_name = sanitize_title( wp_strip_all_tags( htmlspecialchars_decode( (string) $rate->description, ENT_COMPAT ) ) );
					$rate_indicator      = $rate->rateIndicator ?? null;

					/*
					Skip flat rate services

					they are handled separately via dedicated flat rate request methods:
					- maybe_calculate_priority_flat_rates()
					- maybe_calculate_express_flat_rates()
					We filter out flat rate services by their rateIndicator
					since the REST API returns all rates when using mailClass=ALL.
					*/
					if (
						$this->is_rate_indicator( 'flat-rate', $rate_indicator )
						|| false !== stripos( $quoted_service_name, 'flat-rate' )
					) {
						continue;
					}

					/*
					Skip Cubic pricing services.

					Cubic pricing is available for high-volume shippers on small, heavy parcels.
					Rates are based on parcel size and shipping distance, not weight.
					@see https://developers.usps.com/domesticpricesv3 USPS Domestic Prices API documentation.
					*/
					if ( $this->is_rate_indicator( 'cubic', $rate_indicator ) ) {
						continue;
					}

					// Services data compatibility.
					if ( 'first-class-flats' === $quoted_service_name ) {
						$quoted_service_name = 'first-class-mail-large-envelope';
					}

					$code          = substr( (string) $rate->SKU, 0, 4 );
					$code          = $this->map_rest_sku_to_legacy_code( $code );
					$service_codes = array_map( 'strval', array_keys( $service['services'] ) );

					if ( '' === $code || ! in_array( $code, $service_codes, true ) ) {
						continue;
					}

					$cost = (float) $rate_option->totalBasePrice * $cart_item_qty;

					// Process sub sub services.
					if ( '0' === $code ) {
						if ( array_key_exists( $quoted_service_name, $this->shipping_method->custom_services[ $rate_code ][ $code ] ) ) {
							// Enabled check.
							if ( ! empty( $this->shipping_method->custom_services[ $rate_code ][ $code ][ $quoted_service_name ] ) && ( true !== $this->shipping_method->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['enabled'] || empty( $this->shipping_method->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['enabled'] ) ) ) {
								continue;
							}

							// Cost adjustment %.
							if ( ! empty( $this->shipping_method->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment_percent'] ) ) {
								$cost = round( $cost + ( $cost * ( floatval( $this->shipping_method->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment_percent'] ) / 100 ) ), wc_get_price_decimals() );
							}

							// Cost adjustment.
							if ( ! empty( $this->shipping_method->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment'] ) ) {
								$cost = round( $cost + floatval( $this->shipping_method->custom_services[ $rate_code ][ $code ][ $quoted_service_name ]['adjustment'] ), wc_get_price_decimals() );
							}
						}
					} else {
						// Enabled check.
						if ( ! empty( $this->shipping_method->custom_services[ $rate_code ][ $code ] ) && ( true !== $this->shipping_method->custom_services[ $rate_code ][ $code ]['enabled'] || empty( $this->shipping_method->custom_services[ $rate_code ][ $code ]['enabled'] ) ) ) {

							continue;
						}

						// Cost adjustment %.
						if ( ! empty( $this->shipping_method->custom_services[ $rate_code ][ $code ]['adjustment_percent'] ) ) {
							$cost = round( $cost + ( $cost * ( floatval( $this->shipping_method->custom_services[ $rate_code ][ $code ]['adjustment_percent'] ) / 100 ) ), wc_get_price_decimals() );
						}

						// Cost adjustment.
						if ( ! empty( $this->shipping_method->custom_services[ $rate_code ][ $code ]['adjustment'] ) ) {
							$cost = round( $cost + floatval( $this->shipping_method->custom_services[ $rate_code ][ $code ]['adjustment'] ), wc_get_price_decimals() );
						}
					}

					if ( $this->is_domestic_shipment ) {
						switch ( $code ) {
							// Handle first class - there are multiple d0 rates and we need to handle size retrictions because the API doesn't do this for us!
							case '0':
								$service_name = wp_strip_all_tags( htmlspecialchars_decode( (string) $rate->description, ENT_COMPAT ) );

								/**
								 * Filter to disable the first-class rate.
								 *
								 * @param bool $should_disable_first_class Whether to disable the first-class rate.
								 *
								 * @since 3.7.3
								 */
								if ( apply_filters( 'usps_disable_first_class_rate_' . sanitize_title( $service_name ), false ) ) {
									continue 2;
								}
								break;
							// Media mail has restrictions - check here.
							case '6':
								if ( ! empty( $this->shipping_method->mediamail_restriction ) && is_array( $this->shipping_method->mediamail_restriction ) ) {
									$invalid = false;

									foreach ( $this->package['contents'] as $package_item ) {
										if ( ! in_array( $package_item['data']->get_shipping_class_id(), array_map( 'intval', $this->shipping_method->mediamail_restriction ), true ) ) {
											$invalid = true;
										}
									}

									if ( $invalid ) {
										$this->shipping_method->debug( 'Skipping media mail' );
										continue 2;
									}
								}
								break;
						}
					}

					if ( $this->is_domestic_shipment && $package_length && $package_width && $package_height ) {

						$girth = $this->shipping_method->get_girth(
							array(
								$package_length,
								$package_width,
								$package_height,
							)
						);

						switch ( $code ) {
							case '58':
							case 'DUXP':
								if ( $package_length > 14.75 || $package_width > 11.75 || $package_height > 11.5 ) {
									continue 2;
								} else {
									// Valid.
									break;
								}
								break;
							// Handle first class - there are multiple d0 rates and we need to handle size restrictions because the API doesn't do this for us!
							// Apply the same checks for the rate: 78 - First-Class Mail® Metered Letter.
							//
							// See https://www.usps.com/ship/preparing-domestic-shipments.htm.
							case '0':
							case '78':
								$service_name = wp_strip_all_tags( htmlspecialchars_decode( (string) $rate->description, ENT_COMPAT ) );

								if ( strstr( $service_name, 'Postcards' ) ) {

									if ( $package_length > 6 || $package_length < 5 ) {
										continue 2;
									}
									if ( $package_width > 4.25 || $package_width < 3.5 ) {
										continue 2;
									}
									if ( $package_height > 0.016 || $package_height < 0.007 ) {
										continue 2;
									}
								} elseif ( strstr( $service_name, 'Large Envelope' ) ) {
									if ( ! $this->shipping_method->is_large_envelope( $package_length, $package_width, $package_height ) ) {
										continue 2;
									}
								} elseif ( strstr( $service_name, 'Letter' ) ) {
									if ( ! $this->shipping_method->is_letter( $package_length, $package_width, $package_height ) ) {
										continue 2;
									}
								} elseif ( strstr( $service_name, 'Parcel' ) ) {
									if ( ( $girth + (float) $package_length ) > 108 ) {
										continue 2;
									}
								} elseif ( strstr( $service_name, 'Package' ) ) {
									if ( ( $girth + (float) $package_length ) > 108 ) {
										continue 2;
									}
								}
								break;
						}
					}

					/**
					 * Check for USPS Non-Standard fees incorrectly applied to
					 * USPS medium/small tubes and subtract from the total rate.
					 *
					 * Background:
					 * USPS has begun implementing fees for packages that have
					 * lengths/volumes exceeding what they deem standard dimensions.
					 *
					 * @see   https://www.usps.com/business/web-tools-apis/2022-web-tools-release-notes.pdf section 2.3.1
					 *
					 * These new USPS Non-Standard fees are automatically applied to all
					 * non-standard packages and returned in the total postage rate in the
					 * API response.
					 *
					 * These fees are not supposed to be applied to USPS provided boxes/tubes,
					 * but because we don't have a way to indicate that we are using USPS
					 * packaging in the API request, the fees are currently (and wrongly)
					 * being applied in cases where merchants are using USPS small/medium
					 * tubes. These tubes qualify as non-standard because the lengths are
					 * over 22".
					 *
					 * Hopefully USPS will provide some way to indicate a USPS provided
					 * package in the API request at some point. But until then, in order to
					 * provide a temporary fix, we are checking if package dimensions
					 * match USPS tube dimensions and removing any corresponding fees.
					 *
					 * @see   https://github.com/woocommerce/woocommerce-shipping-usps/issues/350
					 *
					 * @since 4.5.0
					 */

					$remove_non_standard_fee = apply_filters_deprecated( 'woocommmerce_shipping_usps_tubes_remove_non_standard_fees', array( true ), '5.2.8', 'woocommerce_shipping_usps_tubes_remove_non_standard_fees', 'This filter is deprecated because of typo.' );

					/**
					 * Filter to remove non standard fee for the tubes.
					 *
					 * @param bool $remove_non_standard_fee Whether to remove non standard fee or not.
					 *
					 * @since 4.5.0
					 */
					if ( ! empty( $rate_option->{'Fees'} ) && $package_length && $package_width && $package_height && apply_filters( 'woocommerce_shipping_usps_tubes_remove_non_standard_fees', $remove_non_standard_fee ) ) {
						if ( $this->shipping_method->package_has_usps_tube_dimensions( $package_length, $package_width, $package_height ) ) {

							$total_non_standard_fees = 0;
							foreach ( $rate_option->{'Fees'} as $non_standard_fee ) {
								if ( empty( $non_standard_fee->{'Fee'} ) || empty( $non_standard_fee->{'Fee'}->{'FeePrice'} ) ) {
									continue;
								}

								foreach ( $non_standard_fee->{'Fee'}->{'FeePrice'} as $fee_price ) {
									$total_non_standard_fees += (float) $fee_price;
								}
							}

							$cost -= $total_non_standard_fees;
						}
					}

					if ( is_null( $rate_cost ) || $cost < $rate_cost ) {
						$rate_cost           = $cost;
						$svc_commitment      = $rate_option->commitment->name ?? null;
						$quoted_package_name = wp_strip_all_tags( htmlspecialchars_decode( (string) $rate->description, ENT_COMPAT ) );
					}

					$show_delivery_time_setting = 'yes' === $this->shipping_method->get_option( 'show_delivery_time', 'yes' );

					/**
					 * Allow merchants to show/hide service commitment information in the rate label.
					 *
					 * @param bool   $should_show_service_commitment Should the service commitment be displayed?
					 * @param object $rate_option                    The current rate option we're processing.
					 *
					 * @return bool
					 *
					 * @since 5.3.0
					 */
					$should_show_service_commitment = apply_filters( 'woocommerce_shipping_usps_should_show_service_commitment', $show_delivery_time_setting, $rate_option );

					if ( ! $should_show_service_commitment ) {
						$svc_commitment = null;
					}
				}

				if ( ! is_null( $rate_cost ) ) {

					if ( ! empty( $svc_commitment ) && stristr( $svc_commitment, 'days' ) ) {
						$svc_commitment = strtolower( $svc_commitment );
						$rate_name     .= ' (' . $svc_commitment . ')';
					}

					$meta_data['package_description'] = $this->shipping_method->get_rate_package_description(
						array(
							'length' => $package_length,
							'width'  => $package_width,
							'height' => $package_height,
							'weight' => $package_weight,
							'qty'    => in_array( $this->shipping_method->packing_method, array( 'per_item', 'weight_based' ), true ) ? $cart_item_qty : 0,
							'name'   => $quoted_package_name,
						)
					);
					$meta_data['rate_description']    = $rate_description;
					$meta_data['rate_mail_class']     = $rate_mail_class;

					/**
					 * Deprecated filter to modify the rate name.
					 *
					 * @param string $rate_name Rate name.
					 * @param string $rate_id   The rate ID.
					 *
					 * @since 4.4.48
					 */
					$rate_name = apply_filters_deprecated( 'woocommmerce_shipping_usps_custom_service_rate_name', array( $rate_name, $rate_id ), '5.2.8', 'woocommerce_shipping_usps_custom_service_rate_name', 'This filter is deprecated because of typo.' );

					/**
					 * Filter to modify the rate name.
					 *
					 * @param string $rate_name Rate name.
					 * @param string $rate_id The rate ID.
					 *
					 * @since 5.2.8
					 */
					$rate_name = apply_filters( 'woocommerce_shipping_usps_custom_service_rate_name', $rate_name, $rate_id );

					$rates[] = array(
						'code'      => $rate_code,
						'id'        => $rate_id,
						'label'     => $rate_name,
						'cost'      => $rate_cost,
						'meta_data' => $meta_data,
						'sort'      => 999,
					);
				}
			}
		}

		return $rates;
	}

	/**
	 * Check if given indicator belongs to the given rate.
	 *
	 * @param string      $rate_name      The rate to check against.
	 * @param string|null $rate_indicator The rate indicator to check.
	 *
	 * @return bool True if the rate indicator belongs to the given rate, false otherwise.
	 */
	private function is_rate_indicator( string $rate_name, ?string $rate_indicator ): bool {
		// $this->rate_indicators comes from ./data/data-rate-indicators.php
		// They can be filtered so we have to cast to array as we shouldn't trust filtered data.
		$indicators = (array) ( $this->rate_indicators[ $rate_name ] ?? array() );

		return in_array( $rate_indicator, $indicators, true );
	}
}
