<?php
/**
 * Base class for all endpoint classes.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Abstracts
 */

namespace WPMUDEV_Videos\Core\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_REST_Request;
use WP_REST_Response;
use WPMUDEV_Videos\Core\Controllers\Permission;

/**
 * Class Endpoint
 *
 * @package WPMUDEV_Videos\Core\Abstracts
 */
abstract class Endpoint extends Base {

	/**
	 * API endpoint version.
	 *
	 * @var int $version
	 *
	 * @since 1.8.0
	 */
	protected $version = 1;

	/**
	 * API endpoint namespace.
	 *
	 * @var string $namespace
	 *
	 * @since 1.8.0
	 */
	private $namespace;

	/**
	 * Endpoint constructor.
	 *
	 * We need to register the routes here.
	 *
	 * @since 4.0.0
	 */
	protected function __construct() {
		parent::__construct();

		// Setup namespace of the endpoint.
		$this->namespace = 'wpmudev-videos/v' . $this->version;

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Get namespace of the endpoint.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Get current version of the endpoint.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get formatted response for the current request.
	 *
	 * @param array $data    Response data.
	 * @param bool  $success Is request success.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_REST_Response
	 */
	public function get_response( $data = array(), $success = true ) {
		// Response status.
		$status = $success ? 200 : 400;

		return new WP_REST_Response(
			array(
				'success' => $success,
				'data'    => $data,
			),
			$status
		);
	}

	/**
	 * Get formatted response for the error request.
	 *
	 * @param string $message Error message.
	 * @param string $code    Is Error code.
	 * @param array  $data    Error data.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_REST_Response
	 */
	public function get_error_response( $message = '', $code = 'error', $data = array() ) {
		return new WP_REST_Response(
			array(
				'code'    => $code,
				'message' => $message,
				'data'    => $data,
			),
			400
		);
	}

	/**
	 * Retrieves a parameter from the request.
	 *
	 * This is a wrapper function to get default value if the param
	 * is not found. Also with optional sanitization.
	 *
	 * @param WP_REST_Request $request           Request object.
	 * @param string          $key               Parameter name.
	 * @param mixed           $default_value     Default value.
	 * @param string|bool     $sanitize_callback Sanitization callback.
	 *
	 * @since 1.8.0
	 *
	 * @return mixed
	 */
	public function get_param( WP_REST_Request $request, $key, $default_value = '', $sanitize_callback = false ) {
		$value = $request->get_param( $key );

		$value = ( null === $value ? $default_value : $value );

		if ( $sanitize_callback && is_callable( $sanitize_callback ) ) {
			return call_user_func( $sanitize_callback, $value );
		} else {
			return $value;
		}
	}

	/**
	 * Check if a given request has access to update a setting.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return bool
	 */
	public function permissions_check( $request ) {
		// Check capability.
		$capable = current_user_can( Permission::SETTINGS_CAP );

		/**
		 * Filter to modify the capability check.
		 *
		 * @paran bool $capable Is capable.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_rest_permissions_check', $capable );
	}

	/**
	 * Check if current user is logged in to access API.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function loggedin_check( $request ) {
		// Check if user is logged in.
		$capable = is_user_logged_in();

		/**
		 * Filter to modify the capability check.
		 *
		 * @paran bool $capable Is capable.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_rest_loggedin_check', $capable );
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * This should be defined in extending class.
	 *
	 * @since 1.8.0
	 */
	abstract public function register_routes();
}