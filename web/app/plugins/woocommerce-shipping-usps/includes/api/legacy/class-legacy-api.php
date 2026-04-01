<?php
/**
 * USPS Legacy API class file.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS\API;

use Exception;
use SimpleXMLElement;
use WC_Product;
use WC_Shipping_USPS;
use WC_Shipping_USPS_Admin;
use WooCommerce\BoxPacker\Abstract_Packer;
use WooCommerce\BoxPacker\WC_Boxpack;

require_once WC_USPS_API_DIR . 'class-abstract-api.php';

// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase --- USPS API provides an object with camelCase properties and method

/**
 * USPS XML API class.
 */
class Legacy_API extends Abstract_API {

	/**
	 * API endpoint.
	 *
	 * @var string
	 */
	public $endpoint = 'https://secure.shippingapis.com/ShippingAPI.dll';

	/**
	 * Class constructor.
	 *
	 * @param WC_Shipping_USPS $shipping_method USPS shipping method object.
	 */
	public function __construct( $shipping_method ) {
		$this->shipping_method = $shipping_method;
	}

	/**
	 * Calculate shipping cost.
	 *
	 * @since   1.0.0
	 * @version 4.4.7
	 *
	 * @param array $package Package to ship.
	 *
	 * @return void
	 */
	public function calculate_shipping( $package ) {
		$this->shipping_method->unpacked_item_costs = 0;
		$domestic                                   = in_array( $package['destination']['country'], $this->shipping_method->domestic, true );

		$package_requests            = array();
		$express_flat_rate_requests  = array(
			'large'   => array(),
			'regular' => array(),
		);
		$priority_flat_rate_requests = array(
			'large'   => array(),
			'regular' => array(),
		);
		if ( $this->shipping_method->enable_standard_services ) {
			$standard_services_requests = $this->get_standard_package_requests( $package );
			$package_requests           = array_merge_recursive( $package_requests, $standard_services_requests );
		}

		// Flat Rate boxes quote.
		if ( 'yes' === $this->shipping_method->enable_flat_rate_boxes || 'priority' === $this->shipping_method->enable_flat_rate_boxes ) {
			// Priority.
			$priority_flat_rate_requests = $this->get_flat_rate_package_requests( $package, 'priority' );
			$package_requests            = array_merge_recursive( $package_requests, $priority_flat_rate_requests );
		}

		if ( 'yes' === $this->shipping_method->enable_flat_rate_boxes || 'express' === $this->shipping_method->enable_flat_rate_boxes ) {
			// Express.
			$express_flat_rate_requests = $this->get_flat_rate_package_requests( $package, 'express' );
			$package_requests           = array_merge_recursive( $package_requests, $express_flat_rate_requests );
		}

		$packages = array();

		// We are doing separate requests for regular and large items. It seems that if
		// we combine them we don't get rates returned (which is probably a limitation of the USPS API).
		foreach ( $package_requests as $package_type => $package_request ) {
			if ( empty( $package_request ) ) {
				continue;
			}

			$packages = array_merge_recursive( $packages, $this->batch_request_usps_api( $package, $package_request, $domestic ) );
		}

		if ( ! empty( $packages ) ) {
			// Parse the rates from all the combined packages.
			$this->parse_rates_from_usps_packages( $packages, $domestic, $package );
		}

		// Store the found rates, so we can pass to the filter later.
		$this->shipping_method->raw_found_rates = $this->shipping_method->found_rates;

		// Ensure rates were found for all packages.
		if ( $this->shipping_method->found_rates ) {

			foreach ( $this->shipping_method->found_rates as $key => $value ) {
				if ( $this->shipping_method->get_rate_id() . ':flat_rate_box_express' === $key ) {
					if ( $value['packages'] < count( $express_flat_rate_requests['large'] ) + count( $express_flat_rate_requests['regular'] ) ) {
						$this->shipping_method->debug( "Unsetting {$key} - too few packages." );
						unset( $this->shipping_method->found_rates[ $key ] );
					}
				} elseif ( $this->shipping_method->get_rate_id() . ':flat_rate_box_priority' === $key ) {
					if ( $value['packages'] < count( $priority_flat_rate_requests['large'] ) + count( $priority_flat_rate_requests['regular'] ) ) {
						$this->shipping_method->debug( "Unsetting {$key} - too few packages." );
						unset( $this->shipping_method->found_rates[ $key ] );
					}
				} elseif ( isset( $standard_services_requests ) ) {
					if ( $value['packages'] < count( $standard_services_requests['large'] ) + count( $standard_services_requests['regular'] ) ) {
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

		// Add rates.
		if ( $this->shipping_method->found_rates ) {
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
	}

	/**
	 * Perform a request to check credentials validness.
	 */
	public function validate_credentials() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing --- Nonce verification already handled in WC
		if ( empty( $_POST['woocommerce_usps_user_id'] ) ) {
			return;
		}

		// Ignoring the warning because esc_attr() is already escaping special chars for XML.
		$example_xml = '<RateV4Request USERID="' . esc_attr( wp_unslash( $_POST['woocommerce_usps_user_id'] ) ) . '">'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		$example_xml .= '<Revision>2</Revision>';
		$example_xml .= '<Package ID="1">';
		$example_xml .= '<Service>PRIORITY</Service>';
		$example_xml .= '<ZipOrigination>97201</ZipOrigination>';
		$example_xml .= '<ZipDestination>44101</ZipDestination>';
		$example_xml .= '<Pounds>1</Pounds>';
		$example_xml .= '<Ounces>0</Ounces>';
		$example_xml .= '<Container />';
		$example_xml .= '</Package>';
		$example_xml .= '</RateV4Request>';

		$response = wp_remote_post(
			$this->endpoint,
			array(
				'body' => 'API=RateV4&XML=' . $example_xml,
			)
		);

		if ( is_wp_error( $response ) ) {
			return;
		}

		try {
			$xml = $this->get_parsed_xml( $response['body'] );
		} catch ( Exception $e ) {
			echo '<div class="error"><p>' . esc_html( $e->getMessage() ) . '</p></div>';

			return;
		}

		// Abort the process if XML cannot be created.
		if ( false === $xml ) {
			return;
		}
		if ( ! is_object( $xml ) && ! is_a( $xml, 'SimpleXMLElement' ) ) {
			return;
		}

		// 80040B1A is an Authorization failure
		if ( '80040B1A' !== $xml->Number->__toString() ) { // phpcs:ignore --- Need to ignore this because the camelCase is from 3rd party library
			return;
		}

		echo '<div class="error">
			<p>' . wp_kses_post(
			sprintf(
				/* translators: %s: USPS API link */
				__( 'The USPS User ID you entered is invalid. Please make sure you entered a valid ID (<a href="%s" target="_blank">which can be obtained here</a>).', 'woocommerce-shipping-usps' ),
				'https://www.usps.com/business/web-tools-apis/welcome.htm'
			)
		) . '</p>
		</div>';

		$_POST['woocommerce_usps_user_id'] = '';
	}

	/**
	 * Get Parsed XML response.
	 *
	 * @param string $xml XML string.
	 *
	 * @return SimpleXMLElement|bool
	 *
	 * @throws Exception When the debug is on.
	 */
	public function get_parsed_xml( string $xml ) {
		libxml_use_internal_errors( true );

		// Validate that no DOCTYPE is present in the XML.
		if ( false !== strpos( $xml, '<!DOCTYPE' ) ) {
			if ( $this->shipping_method->debug ) {
				throw new Exception( 'Unsafe DOCTYPE detected in XML response.' );
			}

			return false;
		}

		// Use SimpleXML with the LIBXML_NONET flag to prevent external entities.
		$xml_object = simplexml_load_string( $xml, 'SimpleXMLElement', LIBXML_NONET );
		if ( false === $xml_object ) {
			if ( $this->shipping_method->debug ) {
				throw new Exception( 'Error loading XML response string' );
			}

			return false;
		}

		return $xml_object;
	}

	/**
	 * Check found rates.
	 *
	 * @version 4.4.7
	 */
	private function check_found_rates() {

		$rate_id = $this->shipping_method->get_rate_id();

		$rate_ids = array(
			'api'  => array(
				'd_priority' => $rate_id . ':D_PRIORITY_MAIL',
				'd_express'  => $rate_id . ':D_EXPRESS_MAIL',
				'i_priority' => $rate_id . ':I_PRIORITY_MAIL',
				'i_express'  => $rate_id . ':I_EXPRESS_MAIL',
			),
			'flat' => array(
				'priority' => $rate_id . ':flat_rate_box_priority',
				'express'  => $rate_id . ':flat_rate_box_express',
			),
		);

		// Only offer one priority rate.
		if ( isset( $this->shipping_method->found_rates[ $rate_ids['api']['d_priority'] ] ) && isset( $this->shipping_method->found_rates[ $rate_ids['flat']['priority'] ] ) ) {
			if ( $this->shipping_method->found_rates[ $rate_ids['flat']['priority'] ]['cost'] < $this->shipping_method->found_rates[ $rate_ids['api']['d_priority'] ]['cost'] ) {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL api rate - flat rate box is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['api']['d_priority'] ] );
			} else {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL flat rate - api rate is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['flat']['priority'] ] );
			}
		}

		if ( isset( $this->shipping_method->found_rates[ $rate_ids['api']['d_express'] ] ) && isset( $this->shipping_method->found_rates[ $rate_ids['flat']['express'] ] ) ) {
			if ( $this->shipping_method->found_rates[ $rate_ids['flat']['express'] ]['cost'] < $this->shipping_method->found_rates[ $rate_ids['api']['d_express'] ]['cost'] ) {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL EXPRESS api rate - flat rate box is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['api']['d_express'] ] );
			} else {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL EXPRESS flat rate - api rate is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['flat']['express'] ] );
			}
		}

		if ( isset( $this->shipping_method->found_rates[ $rate_ids['api']['i_priority'] ] ) && isset( $this->shipping_method->found_rates[ $rate_ids['flat']['priority'] ] ) ) {
			if ( $this->shipping_method->found_rates[ $rate_ids['flat']['priority'] ]['cost'] < $this->shipping_method->found_rates[ $rate_ids['api']['i_priority'] ]['cost'] ) {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL api rate - flat rate box is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['api']['i_priority'] ] );
			} else {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL flat rate - api rate is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['flat']['priority'] ] );
			}
		}

		if ( isset( $this->shipping_method->found_rates[ $rate_ids['api']['i_express'] ] ) && isset( $this->shipping_method->found_rates[ $rate_ids['flat']['express'] ] ) ) {
			if ( $this->shipping_method->found_rates[ $rate_ids['flat']['express'] ]['cost'] < $this->shipping_method->found_rates[ $rate_ids['api']['i_express'] ]['cost'] ) {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL EXPRESS api rate - flat rate box is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['api']['i_express'] ] );
			} else {
				$this->shipping_method->debug( 'Unsetting PRIORITY MAIL EXPRESS flat rate - api rate is cheaper.' );
				unset( $this->shipping_method->found_rates[ $rate_ids['flat']['express'] ] );
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
	 * Get package request.
	 *
	 * @param array $package Package to ship.
	 *
	 * @return array
	 */
	private function get_standard_package_requests( $package ) {
		if ( $this->shipping_method->is_package_overweight( $package, 70 ) ) {
			return array();
		}

		// Choose selected packing.
		switch ( $this->shipping_method->packing_method ) {
			case 'box_packing':
				$requests = $this->box_shipping( $package );
				break;
			case 'weight_based':
				$requests = $this->weight_based_shipping( $package );
				break;
			case 'per_item':
			default:
				$requests = $this->per_item_shipping( $package );
				break;
		}

		return $requests;
	}

	/**
	 * Generate request xml for flat rate packages.
	 *
	 * @param array  $package           Package with items to pack.
	 * @param string $flat_rate_service 'priority' or 'express.
	 *
	 * @return array
	 */
	private function get_flat_rate_package_requests( $package, $flat_rate_service ) {

		$boxpack  = ( new WC_Boxpack( 'in', 'lbs', $this->shipping_method->box_packer_library ) )->get_packer();
		$domestic = in_array( $package['destination']['country'], $this->shipping_method->domestic, true );
		$added    = array();
		$requests = array(
			'large'   => array(),
			'regular' => array(),
		);

		// Define boxes.
		foreach ( $this->shipping_method->flat_rate_boxes as $service_code => $box ) {

			if ( $box['service'] !== $flat_rate_service ) {
				continue;
			}

			$domestic_service = 'd' === substr( $service_code, 0, 1 );

			if ( ( $domestic && $domestic_service ) || ( ! $domestic && ! $domestic_service ) ) {
				$newbox = $boxpack->add_box( $box['length'], $box['width'], $box['height'], $this->shipping_method->get_empty_box_weight( $service_code, $box['weight'] ), $box['max_weight'] );

				$newbox->set_id( $box['id'] );

				if ( isset( $box['volume'] ) && method_exists( $newbox, 'set_volume' ) ) {
					$newbox->set_volume( $box['volume'] );
				}

				if ( isset( $box['type'] ) && method_exists( $newbox, 'set_type' ) ) {
					$newbox->set_type( $box['type'] );
				}

				$added[] = $service_code . ' - ' . $box['name'] . ' (' . $box['length'] . 'x' . $box['width'] . 'x' . $box['height'] . ')';
			}
		}

		$this->shipping_method->debug( 'Calculating USPS Flat Rate with boxes:', $added );

		// Add items.
		foreach ( $package['contents'] as $item_id => $values ) {

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

			$boxpack->add_item(
				$dimensions[0],
				$dimensions[1],
				$dimensions[2],
				$weight,
				$product->get_price(),
				array(),
				(int) ceil( $values['quantity'] )
			);
		}

		// Pack it.
		$boxpack->pack();

		// Get packages.
		$box_packages = $boxpack->get_packages();

		foreach ( $box_packages as $key => $box_package ) {

			if ( true === $box_package->unpacked ) {
				$this->shipping_method->debug( 'Unpacked Item, can\'t fit in any ' . $flat_rate_service . ' flat rate boxes. Disabling flat rate services.' );

				return array();
			} else {
				$this->shipping_method->debug( 'Packed ' . $box_package->id );
			}

			$weight = $box_package->weight;
			$size   = 'REGULAR';

			$dimensions = array(
				$box_package->length,
				$box_package->width,
				$box_package->height,
			);

			rsort( $dimensions, SORT_NUMERIC );

			$girth = $this->shipping_method->get_girth( $dimensions );

			if ( $domestic ) {

				$request = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[0], $dimensions[1], $dimensions[2], $weight, 'flatrate', $flat_rate_service ) . '">' . "\n";
				if ( 'ONLINE' === $this->shipping_method->shippingrates ) {
					if ( 'express' === $flat_rate_service ) {
						$request .= '<Service>PRIORITY MAIL EXPRESS COMMERCIAL</Service>';
					} else {
						$request .= '<Service>PRIORITY COMMERCIAL</Service>';
					}
				} else {
					$request = ( 'express' === $flat_rate_service ) ? $request . '<Service>PRIORITY MAIL EXPRESS</Service>' : $request . '<Service>PRIORITY</Service>';
				}

				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";

				$request .= '	<Container>' . $box_package->id . '</Container>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<ShipDate>' . wp_date( 'Y-m-d', ( wp_date( 'U' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
				$request .= '</Package>' . "\n";

			} else {

				$request  = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[0], $dimensions[1], $dimensions[2], $weight, 'flatrate', $flat_rate_service, $box_package->id ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>FLATRATE</MailType>';
				$request .= '	<ValueOfContents>' . number_format( $box_package->value, 2, '.', '' ) . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->shipping_method->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";

				$request .= '	<Container>RECTANGULAR</Container>' . "\n";

				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shipping_method->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";
			}

			$requests['regular'][] = $request;
		}

		return $requests;
	}

	/**
	 * Parse response from USPS standard service request.
	 *
	 * @since 4.4.40
	 *
	 * @param array<SimpleXMLElement> $usps_packages List of packages returned from the API.
	 * @param bool                    $domestic      Whether domestic or not.
	 * @param array                   $package       Package to ship.
	 */
	private function parse_rates_from_usps_packages( $usps_packages, $domestic, $package ) {

		foreach ( $usps_packages as $usps_package ) {
			if ( ! $usps_package || ! is_object( $usps_package ) ) {
				continue;
			}

			if ( empty( $usps_package->attributes()->ID ) ) {
				continue;
			}

			// Get package data.
			$data_parts = explode( ':', $usps_package->attributes()->ID );
			if ( count( $data_parts ) < 6 ) {
				continue;
			}

			list( $package_item_id, $cart_item_qty, $package_length, $package_width, $package_height, $package_weight, $request_type, $service_type, $service_id ) = $data_parts;

			$girth = $this->shipping_method->get_girth( array( $package_length, $package_width, $package_height ) );

			// Ensure $cart_item_qty is an integer.
			$cart_item_qty = (int) ceil( $cart_item_qty );

			// Use this array to pass metadata to the order item.
			$meta_data                   = array();
			$meta_data['package_length'] = $package_length;
			$meta_data['package_width']  = $package_width;
			$meta_data['package_height'] = $package_height;
			$meta_data['package_weight'] = $package_weight;

			if ( $domestic ) {
				$quotes = $usps_package->xpath( 'Postage' );
			} else {
				// Response xml for international is much different.
				$quotes = $usps_package->xpath( 'Service' );
			}

			// Display quotes nicely in debug notice.
			$this->debug_usps_standard_service_quotes( $quotes, $domestic );

			if ( 'flatrate' === $request_type ) {

				foreach ( $quotes as $quote ) {
					if ( 'express' === $service_type ) {
						$rate_id = $this->shipping_method->get_rate_id() . ':flat_rate_box_express';
						$label   = $this->shipping_method->get_option( 'flat_rate_express_title', ( $domestic ? '' : 'International ' ) . 'Priority Mail Express Flat Rate&#0174;' );
						$sort    = - 1;
					} else {
						$rate_id = $this->shipping_method->get_rate_id() . ':flat_rate_box_priority';
						$label   = $this->shipping_method->get_option( 'flat_rate_priority_title', ( $domestic ? '' : 'International ' ) . 'Priority Mail Flat Rate&#0174;' );
						$sort    = - 2;
					}

					if ( $domestic ) {
						$rate_cost = (float) $quote->{'Rate'} * $cart_item_qty;

						if ( ! empty( $quote->{'CommercialRate'} ) ) {
							$rate_cost = (float) $quote->{'CommercialRate'} * $cart_item_qty;
						}
					} else {
						// International API returns rates for all types of boxes so we have to see if we have the right one.
						if ( $service_id !== (string) $quote->attributes()->ID ) {
							continue;
						}

						$rate_cost = (float) $quote->{'Postage'} * $cart_item_qty;

						if ( ! empty( $quote->{'CommercialPostage'} ) ) {
							$rate_cost = (float) $quote->{'CommercialPostage'} * $cart_item_qty;
						}
					}

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
							$rate_cost = ( '-' === $sym ) ? ( $rate_cost - (float) $fee ) : ( $rate_cost + (float) $fee );
						}

						if ( $rate_cost < 0 ) {
							$rate_cost = 0;
						}
					}

					$meta_data['package_description'] = wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'MailService'}, ENT_COMPAT ) );

					$this->prepare_rate( 'none', $rate_id, $label, $rate_cost, $meta_data, $sort );
				}
			} else {
				// Loop defined services.
				foreach ( $this->shipping_method->services as $service => $values ) {

					if ( ( $domestic && strpos( $service, 'D_' ) !== 0 ) || ( ! $domestic && strpos( $service, 'I_' ) !== 0 ) ) {
						continue;
					}
					$rate_code           = (string) $service;
					$rate_id             = $this->shipping_method->get_rate_id() . ':' . $rate_code;
					$rate_name           = (string) $values['name'] . " ({$this->shipping_method->title})";
					$rate_cost           = null;
					$svc_commitment      = null;
					$quoted_package_name = null;
					$quote               = null;

					// Enforce FCPIS eligibility.
					if (
						! $domestic
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
					foreach ( $quotes as $quote ) {
						$quoted_service_name = sanitize_title( wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'MailService'}, ENT_COMPAT ) ) );

						if ( false !== stripos( $quoted_service_name, 'flat-rate' ) ) {
							continue; // skip all flat rate, handled above.
						}

						$code = strval( $quote->attributes()->CLASSID );
						$cost = null;

						if ( ! $domestic ) {
							$code = strval( $quote->attributes()->ID );
						}

						$service_codes = array_map( 'strval', array_keys( $values['services'] ) );

						if ( '' !== $code && in_array( $code, $service_codes, true ) ) {
							$cost = (float) $quote->{'Rate'} * $cart_item_qty;

							if ( ! empty( $quote->{'CommercialRate'} ) ) {
								$cost = (float) $quote->{'CommercialRate'} * $cart_item_qty;
							}

							if ( ! $domestic ) {
								$cost = (float) $quote->{'Postage'} * $cart_item_qty;

								if ( ! empty( $quote->{'CommercialPostage'} ) ) {
									$cost = (float) $quote->{'CommercialPostage'} * $cart_item_qty;
								}
							}

							// Process sub sub services.
							if ( '0' === $code ) {
								if ( ! isset( $this->shipping_method->custom_services[ $rate_code ] ) ) {
									continue;
								}

								// Do not parse unregistered service.
								if ( ! array_key_exists( $quoted_service_name, $this->shipping_method->custom_services[ $rate_code ][ $code ] ) ) {
									continue;
								}

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

							if ( $domestic ) {
								switch ( $code ) {
									// Handle first class - there are multiple d0 rates and we need to handle size retrictions because the API doesn't do this for us!
									case '0':
										$service_name = wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'MailService'}, ENT_COMPAT ) );

										/**
										 * Filter to disable the first class rate.
										 *
										 * @param boolean $disable_rate `false` mean disabling the first class rate and `true` means enabling it.
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

											foreach ( $package['contents'] as $package_item ) {
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

							if ( $domestic && $package_length && $package_width && $package_height ) {
								switch ( $code ) {
									case '58':
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
										$service_name = wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'MailService'}, ENT_COMPAT ) );

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
											if ( $girth + (float) $package_length > 108 ) {
												continue 2;
											}
										} elseif ( strstr( $service_name, 'Package' ) ) {
											if ( $girth + (float) $package_length > 108 ) {
												continue 2;
											}
										} else {
											continue 2;
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
							if ( ! empty( $quote->{'Fees'} ) && $package_length && $package_width && $package_height && apply_filters( 'woocommerce_shipping_usps_tubes_remove_non_standard_fees', $remove_non_standard_fee ) ) {
								if ( $this->shipping_method->package_has_usps_tube_dimensions( $package_length, $package_width, $package_height ) ) {

									$total_non_standard_fees = 0;
									foreach ( $quote->{'Fees'} as $non_standard_fee ) {
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

							if ( is_null( $rate_cost ) ) {
								$rate_cost           = $cost;
								$svc_commitment      = $quote->{'SvcCommitments'};
								$quoted_package_name = wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'MailService'}, ENT_COMPAT ) );
							} elseif ( $cost < $rate_cost ) {
								$rate_cost           = $cost;
								$svc_commitment      = $quote->{'SvcCommitments'};
								$quoted_package_name = wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'MailService'}, ENT_COMPAT ) );
							}
						}
					}

					if ( ! is_null( $rate_cost ) ) {
						if ( ! empty( $svc_commitment ) && strstr( $svc_commitment, 'days' ) ) {
							$rate_name .= ' (' . current( explode( 'days', $svc_commitment ) ) . ' days)';
						}

						$meta_data['package_description'] = $this->shipping_method->get_rate_package_description(
							array(
								'length' => $package_length,
								'width'  => $package_width,
								'height' => $package_height,
								'weight' => $package_weight,
								'qty'    => 'per_item' === $this->shipping_method->packing_method ? $cart_item_qty : 0,
								'name'   => $quoted_package_name,
							)
						);

						/**
						 * Deprecated filter to modify the rate name.
						 *
						 * @param string      $rate_name Rate name.
						 * @param object|null $quote     The quote object or null.
						 *
						 * @since 4.4.48
						 */
						$rate_name = apply_filters_deprecated( 'woocommmerce_shipping_usps_custom_service_rate_name', array( $rate_name, $quote ), '5.2.8', 'woocommerce_shipping_usps_custom_service_rate_name', 'This filter is deprecated because of typo.' );

						/**
						 * Filter to modify the rate name.
						 *
						 * @param string      $rate_name Rate name.
						 * @param object|null $quote     The quote object or null.
						 *
						 * @since 5.2.8
						 */
						$rate_name = apply_filters( 'woocommerce_shipping_usps_custom_service_rate_name', $rate_name, $quote );

						$this->prepare_rate( $rate_code, $rate_id, $rate_name, $rate_cost, $meta_data );
					}
				}
			}
		}
	}

	/**
	 * Parse response from USPS standard service request.
	 *
	 * @since   4.4.7
	 * @version 4.4.8
	 *
	 * @param mixed $response Body from WP HTTP API.
	 *
	 * @return array<SimpleXMLElement>|false
	 */
	private function parse_packages_from_usps_api( $response ) {
		try {
			$usps_packages = $this->get_parsed_xml( $response );
		} catch ( Exception $e ) {
			$this->shipping_method->debug( $e->getMessage() );

			return false;
		}

		if ( ! $usps_packages instanceof SimpleXMLElement ) {
			$this->shipping_method->debug( 'Invalid XML response format' );

			return false;
		}

		// No rates, return.
		if ( empty( $usps_packages ) ) {
			$this->shipping_method->debug( 'Invalid request; no rates returned' );

			return false;
		}

		return $usps_packages;
	}

	/**
	 * Debug found quotes in USPS standard service.
	 *
	 * @param array $quotes   Found quotes.
	 * @param bool  $domestic Whether domestic or not.
	 */
	private function debug_usps_standard_service_quotes( $quotes, $domestic ) {
		$found_quotes = array();

		foreach ( $quotes as $quote ) {
			if ( $domestic ) {
				$code = strval( $quote->attributes()->CLASSID );
				$name = wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'MailService'}, ENT_COMPAT ) );
			} else {
				$code = strval( $quote->attributes()->ID );
				$name = wp_strip_all_tags( htmlspecialchars_decode( (string) $quote->{'SvcDescription'}, ENT_COMPAT ) );
			}

			if ( $name && $code ) {
				$found_quotes[ $code ] = $name;
			} elseif ( $name ) {
				// @todo Remove $code here? Because if reached here it's empty or evaluate to `false`.
				$found_quotes[ $code . '-' . sanitize_title( $name ) ] = $name;
			}
		}

		if ( empty( $found_quotes ) ) {
			$this->shipping_method->debug( 'No quotes were returned by USPS.' );

			return;
		}

		ksort( $found_quotes );

		$this->shipping_method->debug( 'The following quotes were returned by USPS. If any of these do not display, they may not be enabled in USPS settings (or exceed size restrictions).', $found_quotes );
	}

	/**
	 * Split up USPS requests into batches when the requests exceed more then 25.
	 *
	 * @since 4.4.40
	 *
	 * @param array $shipping_package Raw shipping package.
	 * @param array $package_requests Package params for the request.
	 * @param bool  $domestic         Whether domestic or not.
	 *
	 * @return array<SimpleXMLElement> Packages.
	 */
	private function batch_request_usps_api( $shipping_package, $package_requests, $domestic ) {
		$packages = array();

		if ( empty( $package_requests ) ) {
			return $packages;
		}

		$offset         = 0;
		$packages_count = count( $package_requests );
		$batch_size     = 25;
		while ( $offset < $packages_count ) {
			$current_batch = array_slice( $package_requests, $offset, $batch_size );
			$offset        = $offset + $batch_size;

			// Send the request for the current batch of packages.
			$response = $this->request_usps_api( $shipping_package, $current_batch, $domestic );
			if ( ! $response ) {
				continue;
			}

			$parsed_packages = $this->parse_packages_from_usps_api( $response );
			if ( empty( $parsed_packages ) ) {
				continue;
			}

			// Include all returned packages from this request.
			foreach ( $parsed_packages as $package ) {
				$packages[] = $package;
			}

			/**
			 * Cache the response for one week if response contains rates.
			 *
			 * @var int Transient expiration in seconds.
			 *
			 * @since 4.4.9
			 */
			$transient_expiration = apply_filters( 'woocommerce_shipping_usps_transient_expiration', DAY_IN_SECONDS * 7 );
			set_transient( $this->shipping_method->request_transient, $response, $transient_expiration );
		}

		return $packages;
	}

	/**
	 * Request standard service through USPS API.
	 *
	 * @since 4.4.7
	 *
	 * @param array $package          Raw package.
	 * @param array $package_requests Package params for the request.
	 * @param bool  $domestic         Whether domestic or not.
	 *
	 * @return string|bool
	 */
	private function request_usps_api( $package, $package_requests, $domestic ) {
		$api     = $domestic ? 'RateV4' : 'IntlRateV2';
		$request = $this->build_usps_standard_service_request( $package_requests, $domestic );

		if ( $this->shipping_method->debug ) {
			// Log the request.
			$this->shipping_method->debug( 'USPS Rate Request:', $this->convert_request_string_to_array( $request ) );
		}

		// Need to save the transient's name because `set_transient` is called
		// from another method.
		$this->shipping_method->request_transient = 'usps_quote_' . md5( $request );
		$cached_response                          = get_transient( $this->shipping_method->request_transient );

		// If there's a cached response, return it.
		if ( false !== $cached_response ) {

			if ( $this->shipping_method->debug ) {
				// Log the cached response.
				$this->shipping_method->debug( 'USPS Rate Response (cached):', $this->convert_response_string_to_array( $cached_response ) );
			}

			return $cached_response;
		}

		// Request to USPS.
		$response = wp_remote_post(
			$this->endpoint,
			array(
				'timeout' => 70,

				/**
				 * Filter to modify the API request.
				 *
				 * @var string XML request.
				 * @var string API type.
				 * @var array  Package requests.
				 * @var array  Package data.
				 *
				 * @since 4.4.1
				 */
				'body'    => apply_filters(
					'woocommerce_shipping_usps_request',
					$request,
					$api,
					$package_requests,
					$package
				),
			)
		);

		if ( is_wp_error( $response ) ) {

			if ( $this->shipping_method->debug ) {
				// Log the error.
				$this->shipping_method->debug( 'USPS Rate Request Failed With Error Message(s):', $response->get_error_messages() );
			}

			return false;
		}

		$response = $response['body'];

		if ( $this->shipping_method->debug ) {
			// Log the response.
			$this->shipping_method->debug( 'USPS Rate Response:', $this->convert_response_string_to_array( $response ) );
		}

		return $response;
	}

	/**
	 * Convert request string to array.
	 *
	 * @param string $request Request string.
	 *
	 * @return array
	 */
	public function convert_request_string_to_array( string $request ): array {
		parse_str( $request, $parsed_request );

		if ( empty( $parsed_request['XML'] ) ) {
			return array();
		}

		$request_xml = simplexml_load_string( $parsed_request['XML'] );
		if ( ! $request_xml ) {
			return array();
		}

		$request_array = json_decode( wp_json_encode( $request_xml ), true );

		return is_array( $request_array ) ? $request_array : array();
	}

	/**
	 * Convert response string to array.
	 *
	 * @param string $response Response string.
	 *
	 * @return array
	 */
	public function convert_response_string_to_array( string $response ): array {
		$decoded_response = str_replace( array( "\n", '<br>' ), '', $response );

		$response_xml = simplexml_load_string( $decoded_response );
		if ( ! $response_xml ) {
			return array();
		}

		$this->clean_response_xml( $response_xml );

		$response_array = json_decode( wp_json_encode( $response_xml ), true );

		return is_array( $response_array ) ? $response_array : array();
	}

	/**
	 * Clean up the response XML.
	 *
	 * @param SimpleXMLElement $response_xml Response XML object.
	 *
	 * @return void
	 */
	public function clean_response_xml( SimpleXMLElement &$response_xml ) {

		// Remove unnecessary keys.
		$keys_to_remove = array(
			'Prohibitions',
			'Restrictions',
			'Observations',
			'CustomsForms',
			'ExpressMail',
			'AreasServed',
			'AdditionalRestrictions',
		);

		foreach ( $keys_to_remove as $key ) {
			if ( isset( $response_xml->Package->{$key} ) ) {
				unset( $response_xml->Package->{$key} );
			}
		}

		// Remove extra wrapper element if it exists.
		if ( isset( $response_xml->Package ) ) {
			$response_xml = $response_xml->Package;
		}

		// If the Service array is present, loop through and clean up sub elements.
		if ( ! empty( $response_xml->Service ) ) {
			foreach ( $response_xml->Service as $service ) {

				// We just need to clean the MaxDimensions element value for now.
				if ( empty( $service->MaxDimensions ) ) {
					continue;
				}

				// Replace MaxDimensions value double quotes with "in" because they break the JSON encoding.
				$service->MaxDimensions = str_replace( '"', 'in', $service->MaxDimensions );
			}
		}
	}

	/**
	 * Build XML request for USPS standard service.
	 *
	 * @since 4.4.7
	 *
	 * @param array $package_requests Package params for the request.
	 * @param bool  $domestic         Whether domestic or not.
	 *
	 * @return string
	 */
	private function build_usps_standard_service_request( $package_requests, $domestic ) {
		$api = $domestic ? 'RateV4' : 'IntlRateV2';

		$request  = '<' . $api . 'Request USERID="' . $this->shipping_method->user_id . '">' . "\n";
		$request .= '<Revision>2</Revision>' . "\n";

		foreach ( $package_requests as $key => $package_request ) {
			$request .= $package_request;
		}

		$request .= '</' . $api . 'Request>' . "\n";
		$request  = 'API=' . $api . '&XML=' . str_replace( array( "\n", "\r" ), '', $request );

		return $request;
	}

	/**
	 * Generate shipping request for weights only.
	 *
	 * @param array $package Package to ship.
	 *
	 * @return array
	 */
	private function weight_based_shipping( $package ) {
		$requests      = array(
			'large'   => array(),
			'regular' => array(),
		);
		$domestic      = in_array( $package['destination']['country'], $this->shipping_method->domestic, true );
		$regular_items = array();
		// Add requests for larger items.
		foreach ( $package['contents'] as $item_id => $values ) {

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
			$quantity_rounded = (int) ceil( $quantity );
			$quantity_floored = (int) floor( $quantity );
			$fractional_qty   = $quantity - floor( $quantity );

			$declared_value = $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE );
			$declared_value = is_numeric( $declared_value ) ? (float) $declared_value : (float) $product->get_price();

			// If we only have fractional quantity (quantity below 1).
			// Allow dimensions adjustments by quantity, before checking is regular item.
			if ( $quantity > 0 && $quantity < 1 ) {
				$dimensions = $this->shipping_method->get_product_dimensions( $product, $fractional_qty );
			} else {
				$dimensions = $this->shipping_method->get_product_dimensions( $product );
			}

			$girth = $this->shipping_method->get_girth( $dimensions );

			if ( max( $dimensions ) <= 12 ) {
				$regular_items[ $item_id ] = array(
					'data'           => $values,
					'dimensions'     => $dimensions,
					'weight'         => $weight,
					'declared_value' => $declared_value,
					'is_envelope'    => $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_ENVELOPE ),
					'quantity'       => $quantity_rounded,
					'fractional_qty' => $fractional_qty,
				);
				continue;
			}

			// Package for whole units (full weight, qty = floored quantity).
			if ( $quantity_floored >= 1 ) {
				if ( $domestic ) {
					$request  = '<Package ID="' . $this->generate_package_id( $item_id, $quantity_floored, $dimensions[0], $dimensions[1], $dimensions[2], $weight ) . '">' . "\n";
					$request .= '	<Service>' . $this->shipping_method->shippingrates . '</Service>' . "\n";
					$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</ZipOrigination>' . "\n";
					$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
					$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
					$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
					$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
					$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<ShipDate>' . wp_date( 'Y-m-d', ( wp_date( 'U' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
					$request .= '</Package>' . "\n";
				} else {
					$request  = '<Package ID="' . $this->generate_package_id( $item_id, $quantity_floored, $dimensions[0], $dimensions[1], $dimensions[2], $weight ) . '">' . "\n";
					$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<MailType>Package</MailType>' . "\n";
					$request .= '	<ValueOfContents>' . $declared_value . '</ValueOfContents>' . "\n";
					$request .= '	<Country>' . $this->shipping_method->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
					$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
					$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
					$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
					$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
					$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</OriginZip>' . "\n";
					$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shipping_method->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
					$request .= '</Package>' . "\n";
				}

				$requests['large'][] = $request;
			}

			// Separate package for the fractional remainder (fractional weight, qty = 1).
			if ( 0 < $fractional_qty ) {
				$fractional_weight     = $weight * $fractional_qty;
				$fractional_value      = $declared_value * $fractional_qty;
				$fractional_dimensions = $this->shipping_method->get_product_dimensions( $product, $fractional_qty );
				$fractional_girth      = $this->shipping_method->get_girth( $fractional_dimensions );

				if ( $domestic ) {
					$request  = '<Package ID="' . $this->generate_package_id( $item_id . '-frac', 1, $fractional_dimensions[0], $fractional_dimensions[1], $fractional_dimensions[2], $fractional_weight ) . '">' . "\n";
					$request .= '	<Service>' . $this->shipping_method->shippingrates . '</Service>' . "\n";
					$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</ZipOrigination>' . "\n";
					$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
					$request .= '	<Pounds>' . floor( $fractional_weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $fractional_weight - floor( $fractional_weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
					$request .= '	<Width>' . $fractional_dimensions[1] . '</Width>' . "\n";
					$request .= '	<Length>' . $fractional_dimensions[0] . '</Length>' . "\n";
					$request .= '	<Height>' . $fractional_dimensions[2] . '</Height>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<ShipDate>' . wp_date( 'Y-m-d', ( wp_date( 'U' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
					$request .= '</Package>' . "\n";
				} else {
					$request  = '<Package ID="' . $this->generate_package_id( $item_id . '-frac', 1, $fractional_dimensions[0], $fractional_dimensions[1], $fractional_dimensions[2], $fractional_weight ) . '">' . "\n";
					$request .= '	<Pounds>' . floor( $fractional_weight ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $fractional_weight - floor( $fractional_weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<MailType>Package</MailType>' . "\n";
					$request .= '	<ValueOfContents>' . $fractional_value . '</ValueOfContents>' . "\n";
					$request .= '	<Country>' . $this->shipping_method->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
					$request .= '	<Width>' . $fractional_dimensions[1] . '</Width>' . "\n";
					$request .= '	<Length>' . $fractional_dimensions[0] . '</Length>' . "\n";
					$request .= '	<Height>' . $fractional_dimensions[2] . '</Height>' . "\n";
					$request .= '	<Girth>' . round( $fractional_girth ) . '</Girth>' . "\n";
					$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</OriginZip>' . "\n";
					$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shipping_method->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
					$request .= '</Package>' . "\n";
				}

				$requests['large'][] = $request;
			}
		}

		// Regular package.
		if ( ! empty( $regular_items ) ) {
			$max_package_weight = ( $domestic || 'MX' === $package['destination']['country'] ) ? 70 : 44;
			$shipping_packages  = array();
			$remaining_weight   = $max_package_weight;
			$items_value        = 0;
			$items_weight       = 0;
			$index              = 0;
			foreach ( $regular_items as $item_id => $item ) {
				for ( $i = 1; $i <= $item['quantity']; $i++ ) {
					$is_last_fractional = $i === (int) $item['quantity'] && 0 < $item['fractional_qty'];
					$effective_weight   = $is_last_fractional ? $item['weight'] * $item['fractional_qty'] : $item['weight'];
					$effective_value    = $is_last_fractional ? $item['declared_value'] * $item['fractional_qty'] : $item['declared_value'];

					if ( $items_weight > 0 && $remaining_weight < $effective_weight ) {
						$shipping_packages[ $index ] = array(
							'dimensions'  => $item['dimensions'],
							'weight'      => $items_weight,
							'value'       => $items_value,
							'is_envelope' => $item['is_envelope'],
						);

						$remaining_weight = $max_package_weight;
						$items_value      = 0;
						$items_weight     = 0;
						++$index;
					}

					$remaining_weight -= $effective_weight;
					$items_value      += $effective_value;
					$items_weight     += $effective_weight;
				}
			}

			if ( $items_weight > 0 ) {
				$shipping_packages[ $index ] = array(
					'dimensions'  => $item['dimensions'],
					'weight'      => $items_weight,
					'value'       => $items_value,
					'is_envelope' => $item['is_envelope'],
				);
			}

			foreach ( $shipping_packages as $key => $shipping_package ) {
				if ( $domestic ) {
					$request  = '<Package ID="' . $this->generate_package_id( 'regular_' . $key, 1, 0, 0, 0, 0 ) . '">' . "\n";
					$request .= '	<Service>' . $this->shipping_method->shippingrates . '</Service>' . "\n";
					$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</ZipOrigination>' . "\n";
					$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
					$request .= '	<Pounds>' . floor( $shipping_package['weight'] ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $shipping_package['weight'] - floor( $shipping_package['weight'] ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '   <Container />' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<ShipDate>' . wp_date( 'Y-m-d', ( wp_date( 'U' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
					$request .= '</Package>' . "\n";
				} else {
					$mail_type = 'yes' === $shipping_package['is_envelope'] ? 'ENVELOPE' : 'PACKAGE';

					$request  = '<Package ID="' . $this->generate_package_id( 'regular_' . $key, 1, 0, 0, 0, 0 ) . '">' . "\n";
					$request .= '	<Pounds>' . floor( $shipping_package['weight'] ) . '</Pounds>' . "\n";
					$request .= '	<Ounces>' . number_format( ( $shipping_package['weight'] - floor( $shipping_package['weight'] ) ) * 16, 2 ) . '</Ounces>' . "\n";
					$request .= '	<Machinable>true</Machinable> ' . "\n";
					$request .= '	<MailType>' . $this->get_mailtype( $mail_type, $shipping_package['value'], $shipping_package['dimensions'][0], $shipping_package['dimensions'][1], $shipping_package['dimensions'][2] ) . '</MailType>' . "\n";
					$request .= '	<ValueOfContents>' . $shipping_package['value'] . '</ValueOfContents>' . "\n";
					$request .= '	<Country>' . $this->shipping_method->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
					$request .= '   <Container />' . "\n";
					$request .= '	<Width />' . "\n";
					$request .= '	<Length />' . "\n";
					$request .= '	<Height />' . "\n";
					$request .= '	<Girth />' . "\n";
					$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</OriginZip>' . "\n";
					$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shipping_method->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
					$request .= '</Package>' . "\n";
				}

				$requests['regular'][] = $request;
			}
		}

		return $requests;
	}

	/**
	 * Generate XML requests using box packing method.
	 *
	 * @version 4.4.7
	 *
	 * @param array $package Package to ship.
	 *
	 * @return array Array of XML requests.
	 */
	private function box_shipping( $package ) {

		$requests = array(
			'large'   => array(),
			'regular' => array(),
		);

		$domestic = in_array( $package['destination']['country'], $this->shipping_method->domestic, true );

		/**
		 * Create a new boxpacker instance.
		 *
		 * @var Abstract_Packer $boxpack Boxpacker object.
		 */
		$boxpack = ( new WC_Boxpack( 'in', 'lbs', $this->shipping_method->box_packer_library ) )->get_packer();

		// Define boxes.
		foreach ( $this->shipping_method->boxes as $key => $box ) {
			$newbox = $boxpack->add_box( $box['outer_length'], $box['outer_width'], $box['outer_height'], $box['box_weight'] );
			$newbox->set_id( isset( $box['name'] ) ? $box['name'] : $key );
			$newbox->set_inner_dimensions( $box['inner_length'], $box['inner_width'], $box['inner_height'] );
			if ( $box['max_weight'] ) {
				$newbox->set_max_weight( $box['max_weight'] );
			}
			if ( $box['is_letter'] ) {
				$newbox->set_type( 'envelope' );
			}
		}

		// Add items.
		foreach ( $package['contents'] as $item_id => $values ) {

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

			$declared_value = $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE );
			$declared_value = '' === $declared_value ? $product->get_price() : $declared_value;

			$boxpack->add_item(
				$dimensions[0],
				$dimensions[1],
				$dimensions[2],
				$weight,
				$declared_value,
				array(),
				(int) ceil( $values['quantity'] )
			);
		}
		/**
		 * Allow to override the boxpack before packing.
		 *
		 * @see   https://github.com/woocommerce/woocommerce-shipping-usps/issues/155
		 *
		 * @param Abstract_Packer $boxpack Boxpacker object.
		 *
		 * @since 4.4.12
		 */
		$boxpack = apply_filters( 'woocommerce_shipping_usps_boxpack_before_pack', $boxpack );
		// Pack it.
		$boxpack->pack();
		// Get packages.
		$box_packages = $boxpack->get_packages();
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
			$weight      = $box_package->weight;
			$size        = 'REGULAR';
			$dimensions  = array( $box_package->length, $box_package->width, $box_package->height );
			$goods_value = number_format( $box_package->value, 2, '.', '' );
			rsort( $dimensions, SORT_NUMERIC );
			if ( max( $dimensions ) > 12 ) {
				$size = 'LARGE';
			}
			$girth = $this->shipping_method->get_girth( $dimensions );
			if ( $dimensions[0] <= 27 && $dimensions[1] <= 17 && $dimensions[2] <= 17 && $weight <= 35 ) {
				// From USPS website
				// Machinable parcels must measure:
				// No more than 27 inches long x 17 inches width x 17 inches high.
				// No more than 25 pounds (35 pounds for Parcel Select and Parcel Return Service, except books and other printed matter which cannot exceed 25 pounds).
				$machinable = 'true';
			} else {
				$machinable = 'false';
			}
			if ( $domestic ) {
				$service = $this->shipping_method->shippingrates;

				$request  = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[0], $dimensions[1], $dimensions[2], $weight ) . '">' . "\n";
				$request .= '	<Service>' . $service . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";

				if ( 'LARGE' === $size ) {
					$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				} else {
					$request .= '	<Container />' . "\n";
				}

				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
				if ( 'LARGE' !== $size ) {
					$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				}

				if ( 'envelope' === $box_package->type && $this->shipping_method->is_letter( $dimensions[0], $dimensions[1], $dimensions[2] ) ) {
					$request .= '	<SortBy>LETTER</SortBy>' . "\n";
				} elseif ( 'envelope' === $box_package->type && $this->shipping_method->is_large_envelope( $dimensions[0], $dimensions[1], $dimensions[2] ) ) {
					$request .= '	<SortBy>LARGEENVELOPE</SortBy>' . "\n";
				}

				$request .= '	<Machinable>' . $machinable . '</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . wp_date( 'Y-m-d', ( wp_date( 'U' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
				$request .= '	<ReturnFees>1</ReturnFees> ' . "\n";
				$request .= '</Package>' . "\n";
			} else {
				$request  = '<Package ID="' . $this->generate_package_id( $key, 1, $dimensions[0], $dimensions[1], $dimensions[2], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>' . $machinable . '</Machinable> ' . "\n";
				$request .= '	<MailType>' . $this->get_mailtype( $box_package->type, $goods_value, $dimensions[0], $dimensions[1], $dimensions[2] ) . '</MailType>' . "\n";
				$request .= '	<GXG><POBoxFlag>N</POBoxFlag><GiftFlag>N</GiftFlag></GXG>' . "\n";
				$request .= '	<ValueOfContents>' . $goods_value . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->shipping_method->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";
				$request .= '	<Container>RECTANGULAR</Container>' . "\n";
				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shipping_method->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";
			}

			$requests[ 'LARGE' === $size ? 'large' : 'regular' ][] = $request;
		}

		return $requests;
	}

	/**
	 * Per item shipping.
	 *
	 * @param mixed $package Package to ship.
	 *
	 * @return array
	 */
	private function per_item_shipping( $package ) {
		$domestic = in_array( $package['destination']['country'], $this->shipping_method->domestic, true );
		$requests = array(
			'large'   => array(),
			'regular' => array(),
		);

		// Get total value of all line items.
		$total_declared_value = 0;
		foreach ( $package['contents'] as $item_id => $values ) {
			$product = $values['data'];

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$declared_value = $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE );
			$declared_value = (float) ( '' === $declared_value ? $product->get_price() : $declared_value );

			$total_declared_value += $declared_value;
		}

		// Get weight of order.
		foreach ( $package['contents'] as $item_id => $values ) {

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
			$girth      = $this->shipping_method->get_girth( $dimensions );
			$quantity   = (int) ceil( $values['quantity'] );
			$size       = max( $dimensions ) > 12 ? 'LARGE' : 'REGULAR';
			$mail_type  = 'yes' === $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_ENVELOPE ) && 0 >= $total_declared_value
				? 'ENVELOPE'
				: 'PACKAGE';

			if ( $domestic ) {
				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $quantity, $dimensions[0], $dimensions[1], $dimensions[2], $weight ) . '">' . "\n";
				$request .= '	<Service>' . $this->shipping_method->shippingrates . '</Service>' . "\n";
				$request .= '	<ZipOrigination>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</ZipOrigination>' . "\n";
				$request .= '	<ZipDestination>' . strtoupper( substr( $package['destination']['postcode'], 0, 5 ) ) . '</ZipDestination>' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";

				if ( 'LARGE' === $size ) {
					$request .= '   <Container>RECTANGULAR</Container>' . "\n";
				} else {
					$request .= '   <Container />' . "\n";
				}

				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
				if ( 'LARGE' !== $size ) {
					$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				}

				if ( 'ENVELOPE' === $mail_type && $this->shipping_method->is_letter( $dimensions[0], $dimensions[1], $dimensions[2] ) ) {
					$request .= '	<SortBy>LETTER</SortBy>' . "\n";
				} elseif ( 'ENVELOPE' === $mail_type && $this->shipping_method->is_large_envelope( $dimensions[0], $dimensions[1], $dimensions[2] ) ) {
					$request .= '	<SortBy>LARGEENVELOPE</SortBy>' . "\n";
				}

				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<ShipDate>' . wp_date( 'Y-m-d', ( wp_date( 'U' ) + ( 60 * 60 * 24 ) ) ) . '</ShipDate>' . "\n";
				$request .= '</Package>' . "\n";

			} else {

				$declared_value = $product->get_meta( WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE );
				$declared_value = (float) ( '' === $declared_value ? $product->get_price() : $declared_value );

				$request  = '<Package ID="' . $this->generate_package_id( $item_id, $quantity, $dimensions[0], $dimensions[1], $dimensions[2], $weight ) . '">' . "\n";
				$request .= '	<Pounds>' . floor( $weight ) . '</Pounds>' . "\n";
				$request .= '	<Ounces>' . number_format( ( $weight - floor( $weight ) ) * 16, 2 ) . '</Ounces>' . "\n";
				$request .= '	<Machinable>true</Machinable> ' . "\n";
				$request .= '	<MailType>' . $this->get_mailtype( $mail_type, $declared_value, $dimensions[0], $dimensions[1], $dimensions[2] ) . '</MailType>' . "\n";
				$request .= '	<ValueOfContents>' . $declared_value . '</ValueOfContents>' . "\n";
				$request .= '	<Country>' . $this->shipping_method->get_country_name( $package['destination']['country'] ) . '</Country>' . "\n";

				$request .= '	<Container>RECTANGULAR</Container>' . "\n";

				$request .= '	<Width>' . $dimensions[1] . '</Width>' . "\n";
				$request .= '	<Length>' . $dimensions[0] . '</Length>' . "\n";
				$request .= '	<Height>' . $dimensions[2] . '</Height>' . "\n";
				$request .= '	<Girth>' . round( $girth ) . '</Girth>' . "\n";
				$request .= '	<OriginZip>' . str_replace( ' ', '', strtoupper( $this->shipping_method->origin ) ) . '</OriginZip>' . "\n";
				$request .= '	<CommercialFlag>' . ( 'ONLINE' === $this->shipping_method->shippingrates ? 'Y' : 'N' ) . '</CommercialFlag>' . "\n";
				$request .= '</Package>' . "\n";

			}

			$requests[ 'LARGE' === $size ? 'large' : 'regular' ][] = $request;
		}

		return $requests;
	}

	/**
	 * Generate a package ID for the request.
	 *
	 * Contains qty and dimension info so we can look at it again later when it
	 * comes back from USPS if needed.
	 *
	 * @param string $id           Package ID.
	 * @param int    $qty          Quantity.
	 * @param float  $l            Length.
	 * @param float  $w            Width.
	 * @param float  $h            Height.
	 * @param float  $weight       Weight.
	 * @param string $request_type 'flatrate' or 'api'.
	 * @param string $service      'express' or 'priority'.
	 * @param string $service_id   Used by international flat rate requests to define which box to use.
	 *
	 * @return string
	 */
	public function generate_package_id( $id, $qty, $l, $w, $h, $weight, $request_type = '', $service = '', $service_id = '' ) {
		return implode( ':', array( $id, $qty, $l, $w, $h, $weight, $request_type, $service, $service_id ) );
	}

	/**
	 * Return the necessary <MailType> value based on package type and
	 * dimensions
	 *
	 * @param string $package_type   Type.
	 * @param float  $value          Value of goods.
	 * @param float  $package_length Length.
	 * @param float  $package_width  Width.
	 * @param float  $package_height Height.
	 *
	 * @return string The necessary <MailType> value.
	 */
	private function get_mailtype( $package_type, $value, $package_length, $package_width, $package_height ) {
		$is_envelope                     = 'envelope' === strtolower( $package_type );
		$is_letter_size                  = $this->shipping_method->is_letter( $package_length, $package_width, $package_height );
		$is_letter_service_enabled       =
			! empty( $this->shipping_method->services['I_FIRST_CLASS_M']['services']['13'] )
			&& ! empty( $this->shipping_method->custom_services['I_FIRST_CLASS_M']['13']['enabled'] );
		$is_large_envelope_size          = $this->shipping_method->is_large_envelope( $package_length, $package_width, $package_height );
		$is_large_letter_service_enabled =
			! empty( $this->shipping_method->services['I_FIRST_CLASS_M']['services']['14'] )
			&& ! empty( $this->shipping_method->custom_services['I_FIRST_CLASS_M']['14']['enabled'] );

		/**
		 * This filter is documented in includes/api/rest/class-rest-api.php
		 *
		 * @since 5.5.2
		 */
		$allow_merchandise = apply_filters( 'woocommerce_shipping_usps_allow_fcmi_for_merchandise', false );
		$value_ok          = 0 >= $value || $allow_merchandise;

		if ( $value_ok && $is_envelope && $is_letter_size && $is_letter_service_enabled ) {
			return 'ENVELOPE';
		} elseif ( $value_ok && $is_envelope && $is_large_envelope_size && $is_large_letter_service_enabled ) {
			return 'LARGEENVELOPE';
		} else {
			return 'PACKAGE';
		}
	}
}
