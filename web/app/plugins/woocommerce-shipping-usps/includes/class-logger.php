<?php
/**
 * Logger class.
 *
 * A class to handle logging for the USPS shipping method.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS;

use WC_Logger;

/**
 * Logger class.
 */
class Logger {
	/**
	 * WC Log context.
	 *
	 * @var string
	 */
	const CONTEXT = 'woocommerce-shipping-usps';

	/**
	 * WC Logger
	 *
	 * @var WC_Logger
	 */
	private $logger;

	/**
	 * Is debug enabled.
	 *
	 * @var bool
	 */
	private bool $is_debug_enabled;

	/**
	 * Constructor.
	 *
	 * @param boolean $is_debug_enabled Flag whether debug enabled or not.
	 */
	public function __construct( bool $is_debug_enabled ) {
		$this->is_debug_enabled = $is_debug_enabled;

		$this->logger = wc_get_logger();
	}

	/**
	 * Check if debug is enabled.
	 *
	 * @return bool
	 */
	private function is_debug_enabled(): bool {
		return $this->is_debug_enabled;
	}

	/**
	 * Add a debug log entry.
	 * Only logs if debug is enabled.
	 *
	 * @param string $message Message to display.
	 * @param array  $data    Additional contextual data to pass.
	 *
	 * @return void
	 */
	public function debug( string $message, array $data = array() ) {
		if ( ! $this->is_debug_enabled() ) {
			return;
		}

		$this->logger->debug( $message, $data );
	}

	/**
	 * Add an error log entry.
	 *
	 * @param string $message Message to display.
	 * @param array  $data    Additional contextual data to pass.
	 *
	 * @return void
	 */
	public function error( string $message, array $data = array() ) {
		$this->logger->error( $message, $data );
	}
}
