<?php
/**
 * Settings functionality REST endpoint.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Endpoints
 */

namespace WPMUDEV_Videos\Core\Endpoints;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Endpoint;

/**
 * Class Settings
 *
 * @package WPMUDEV_Videos\Core\Endpoints
 */
class Settings extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @var string $endpoint
	 *
	 * @since 1.8.0
	 */
	private $endpoint = '/settings';

	/**
	 * Register the routes for handling settings functionality.
	 *
	 * Register routes:
	 * - v1/settings - GET to get settings.
	 * - v1/settings - POST, PUT, PATCH to update settings.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'key' => array(
							'required'    => false,
							'type'        => 'string',
							'description' => __( 'Setting key to get data. If empty, the whole settings will be returned.', 'wpmudev_vids' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'value' => array(
							'required'          => true,
							'description'       => __( 'Value(s) to update.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param, $request ) {
								// Get key.
								$setting_key = $request->get_param( 'key' );
								// If key is empty, we need array data.
								if ( empty( $setting_key ) ) {
									return is_array( $param );
								} else {
									return true;
								}
							},
						),
						'key'   => array(
							'required'    => false,
							'type'        => 'string',
							'description' => __( 'Setting key to update. If empty, the whole settings will be updated.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);
	}

	/**
	 * Get the settings data based on the key.
	 *
	 * If key is not given, we will return the whole settings data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_settings( $request ) {
		// Get key.
		$key = $request->get_param( 'key' );

		// Get the settings value.
		$value = Helpers\Settings::get( $key );

		// Send response.
		return $this->get_response( $value );
	}

	/**
	 * Add or update the settings data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_settings( $request ) {
		// Get key and value.
		$key    = $request->get_param( 'key' );
		$values = $request->get_param( 'value' );

		// Update the settings.
		$updated = Helpers\Settings::set( $key, $values );

		// Get the response data.
		$settings = $updated ? Helpers\Settings::get() : array();

		// Send response.
		return $this->get_response( $settings, $updated );
	}
}