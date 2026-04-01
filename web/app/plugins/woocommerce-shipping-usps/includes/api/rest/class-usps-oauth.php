<?php
/**
 * USPS OAuth class file.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS\API;

use WC_Shipping_USPS;

/**
 * Class USPS_OAuth
 *
 * Handles the OAuth authentication for the USPS REST API.
 *
 * @package WooCommerce\USPS
 */
class USPS_OAuth {

	/**
	 * The name of the transient used to store the access token.
	 *
	 * @var WC_Shipping_USPS
	 */
	private WC_Shipping_USPS $shipping_method;

	/**
	 * The name of the transient used to store the access token.
	 *
	 * @var string
	 */
	private string $access_token_transient_name;

	/**
	 * Constructor.
	 *
	 * @param WC_Shipping_USPS $shipping_method The USPS shipping method object.
	 */
	public function __construct( WC_Shipping_USPS $shipping_method ) {
		$this->shipping_method             = $shipping_method;
		$this->access_token_transient_name = 'woocommerce_usps_oauth_access_token_' . md5( $this->shipping_method->client_id . $this->shipping_method->client_secret );
	}

	/**
	 * Check if we've successfully authenticated.
	 *
	 * @return bool
	 */
	public function is_authenticated(): bool {
		return (bool) $this->get_access_token();
	}

	/**
	 * Retrieves an access token.
	 *
	 * @param bool $force_token_refresh Optional. Whether to force retrieval of a new token from the API instead of using the cached token. Default false.
	 *
	 * @return string|false
	 */
	public function get_access_token( bool $force_token_refresh = false ) {

		if ( 'rest' !== $this->shipping_method->api_type ) {
			return false;
		}

		if ( $force_token_refresh ) {
			delete_transient( $this->access_token_transient_name );
		} else {
			$access_token = get_transient( $this->access_token_transient_name );
			if ( ! empty( $access_token ) ) {
				return $access_token;
			}
		}

		$token_data = $this->get_token_data( $force_token_refresh );
		if ( ! $token_data ) {
			return false;
		}

		set_transient( $this->access_token_transient_name, $token_data->access_token, $token_data->expires_in - 60 );

		return $token_data->access_token;
	}

	/**
	 * Retrieves an access token from the USPS OAuth API.
	 *
	 * @param bool $bypass_static_cache Optional. Whether to bypass the static cache and request a new token. Default false.
	 *
	 * @return object|false The token response object on success, or false if the request fails.
	 */
	private function get_token_data( bool $bypass_static_cache = false ) {
		static $token_data;

		if ( null !== $token_data && false === $bypass_static_cache ) {
			return $token_data;
		}

		if ( ! $this->shipping_method->client_id || ! $this->shipping_method->client_secret ) {
			$token_data = false;

			return false;
		}

		$headers = array(
			'Content-Type' => 'application/x-www-form-urlencoded',
		);

		$body = array(
			'grant_type'    => 'client_credentials',
			'client_id'     => $this->shipping_method->client_id,
			'client_secret' => $this->shipping_method->client_secret,
		);

		$response = wp_remote_post(
			$this->shipping_method::API_URL . '/oauth2/v3/token',
			array(
				'headers' => $headers,
				'body'    => $body,
			)
		);

		$token_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( is_wp_error( $response ) || empty( $token_data->access_token ) || empty( $token_data->expires_in ) ) {
			$error_data =
				is_wp_error( $response )
					? array( $response->get_error_message() )
					: (array) $token_data;

			$this->shipping_method->logger->error( 'USPS_OAuth::request_access_token: The USPS OAuth endpoint returned an error', $error_data );

			return false;
		}

		return $token_data;
	}
}
