<?php
/**
 * Common actions functionality REST endpoint.
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
use WPMUDEV_Videos\Core\Modules;
use WPMUDEV_Videos\Core\Controllers;
use WPMUDEV_Videos\Core\Abstracts\Endpoint;

/**
 * Class Actions
 *
 * @package WPMUDEV_Videos\Core\Endpoints
 */
class Actions extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @since 1.8.0
	 *
	 * @var string $endpoint
	 */
	private $endpoint = '/actions/';

	/**
	 * Register the routes for handling actions.
	 *
	 * Register routes:
	 * - v1/actions - GET to perform actions.
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
					'callback'            => array( $this, 'run_action' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'action' => array(
							'required'    => true,
							'type'        => 'string',
							'description' => __( 'Action name to execute.', 'wpmudev_vids' ),
							'enum'        => array(
								'dismiss_dash',
								'dismiss_welcome',
								'refresh_membership',
								'reset_settings',
								'reset_data',
							),
						),
					),
				),
			)
		);
	}

	/**
	 * Perform the action.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function run_action( $request ) {
		// Get the action name.
		$action = $request->get_param( 'action' );

		switch ( $action ) {
			case 'dismiss_dash':
				return $this->dismiss_dash_notice();
			case 'dismiss_welcome':
				return $this->dismiss_welcome();
			case 'refresh_membership':
				return $this->refresh_membership();
			case 'reset_settings':
				return $this->reset_settings();
			case 'reset_data':
				return $this->reset_data();
		}

		// Send error response.
		return $this->get_error_response(
			__( 'Invalid action. Please try again with a valid action name.', 'wpmudev_vids' )
		);
	}

	/**
	 * Dismiss the onboarding modal.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function dismiss_dash_notice() {
		// Update the flag.
		$updated = Helpers\Settings::set( 'dismiss_dash_notice', 1 );

		// Send response.
		return $this->get_response( array(), $updated );
	}

	/**
	 * Dismiss the welcome modal.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function dismiss_welcome() {
		// Update the flag.
		$updated = Helpers\Settings::set( 'dismiss_welcome_notice', 1 );

		// Send response.
		return $this->get_response( array(), $updated );
	}

	/**
	 * Refresh the current logged in membership.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function refresh_membership() {
		// Dashboard is active.
		if ( class_exists( 'WPMUDEV_Dashboard' ) ) {
			// Forcefully call api to update status.
			\WPMUDEV_Dashboard::$api->hub_sync( false, true );

			// Send response.
			return $this->get_response(
				array(
					'message' => __( 'Membership has been successfully refreshed.', 'wpmudev_vids' ),
				),
				true
			);
		} else {
			// Send error response.
			return $this->get_error_response(
				__( 'WPMUDEV Dashboard is not active.', 'wpmudev_vids' )
			);
		}
	}

	/**
	 * Clean all data from WP.
	 *
	 * This is a dangerous action which should not be used
	 * in any case unless you are a developer.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function reset_settings() {
		// Delete settings.
		Helpers\Settings::delete();

		// Setup default settings.
		Controllers\Installer::get()->setup_default_settings();

		// Send response.
		return $this->get_response(
			array(
				'message' => __( 'Plugin settings reset succesfully.', 'wpmudev_vids' ),
			),
			true
		);
	}

	/**
	 * Clean videos and playlists and create again.
	 *
	 * This should be used only if you know what you are
	 * doing. This will delete all your custom videos and
	 * playlists and create only default videos and playlists.
	 *
	 * @since 1.8.1
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function reset_data() {
		// Make sure to query main site.
		Helpers\General::switch_site();

		// Clean all.
		Modules\Content::get()->clean_all();

		// Setup videos and playlists again.
		Modules\Content::get()->setup_videos();

		// Restore old blog.
		Helpers\General::restore_site();

		// Send response.
		return $this->get_response(
			array(
				'message' => __( 'Plugin data reset succesfully.', 'wpmudev_vids' ),
			),
			true
		);
	}
}