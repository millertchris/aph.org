<?php
/**
 * Summary functionality REST endpoint.
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
use WPMUDEV_Videos\Core\Modules;
use WPMUDEV_Videos\Core\Helpers\General;
use WPMUDEV_Videos\Core\Abstracts\Endpoint;

/**
 * Class Summary
 *
 * @package WPMUDEV_Videos\Core\Endpoints
 */
class Summary extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @var string $endpoint
	 *
	 * @since 1.8.0
	 */
	private $endpoint = '/summary';

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
					'callback'            => array( $this, 'summary' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get all summary data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function summary( $request ) {
		// Make sure to query main site.
		General::switch_site();

		// Get recent custom video.
		$recent_custom_video = Modules\Videos\Controller::get()->get_last_created_video( 'custom' );

		// Make sure it's empty.
		if ( empty( $recent_custom_video ) ) {
			$recent_custom_video = array();
		}

		$data = array(
			'recent' => array(
				'updated_video'        => Modules\Videos\Controller::get()->get_last_updated_video(),
				'updated_playlist'     => Modules\Playlists\Controller::get()->get_last_updated_playlist(),
				'created_custom_video' => $recent_custom_video,
				'created_playlist'     => Modules\Playlists\Controller::get()->get_last_created_playlist(),
			),
			'count'  => array(
				'videos'    => Modules\Videos\Controller::get()->get_videos_count(),
				'playlists' => Modules\Playlists\Controller::get()->get_playlists_count(),
			),
		);

		// Restore old blog.
		General::restore_site();

		// Send response.
		return $this->get_response( $data );
	}
}