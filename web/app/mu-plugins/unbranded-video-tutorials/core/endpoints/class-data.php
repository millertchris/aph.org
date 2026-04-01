<?php
/**
 * Common data functionality REST endpoint.
 *
 * @link    https://wpmudev.com
 * @since   1.8.1
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
use WPMUDEV_Videos\Core\Core;
use WPMUDEV_Videos\Core\Tasks\Import;
use WPMUDEV_Videos\Core\Helpers\General;
use WPMUDEV_Videos\Core\Abstracts\Endpoint;

/**
 * Class Data
 *
 * @package WPMUDEV_Videos\Core\Endpoints
 */
class Data extends Endpoint {

	/**
	 * API endpoint for the current endpoint.
	 *
	 * @since 1.8.0
	 *
	 * @var string $endpoint
	 */
	private $endpoint = '/data/';

	/**
	 * Register the routes for handling imports and exports.
	 *
	 * Register routes:
	 * - v1/data/import - POST to perform import.
	 * - v1/data/import/status - GET to get the status.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	public function register_routes() {
		// Import process.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . 'import',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'run_import' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'selected' => array(
							'required'    => true,
							'type'        => 'array',
							'default'     => array(),
							'items'       => array(
								'type' => 'string',
							),
							'description' => __( 'Items to import.', 'wpmudev_vids' ),
						),
						'thumb'    => array(
							'required'    => false,
							'type'        => 'boolean',
							'default'     => false,
							'description' => __( 'Should import thumbnails.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);

		// Import status.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . 'import/status',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_status' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Perform json file upload.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.1
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function run_import( $request ) {
		// Should import thumbnails.
		$thumb = (bool) $request->get_param( 'thumb' );
		// Get the selected items.
		$selected = (array) $request->get_param( 'selected' );

		// Send error response if nothing is selected.
		if ( empty( $selected ) ) {
			return $this->get_error_response(
				__( 'Nothing to import. Please select something.', 'wpmudev_vids' )
			);
		}

		// Get files.
		$files = $request->get_file_params();

		if ( empty( $files['file']['tmp_name'] ) ) {
			// Send error response.
			return $this->get_error_response(
				__( 'Invalid file. Please try again with a valid export file.', 'wpmudev_vids' )
			);
		}

		// Get the json file content.
		// phpcs:ignore
		$data = file_get_contents( $files['file']['tmp_name'] );

		if ( empty( $data ) ) {
			// Send error response.
			return $this->get_error_response(
				__( 'Invalid file format. Please try again with a valid export file.', 'wpmudev_vids' )
			);
		}

		// Convert to PHP array.
		$data = json_decode( $data, true );

		// Import details.
		$details = array(
			'videos'      => count( $data['videos'] ),
			'playlists'   => count( $data['playlists'] ),
			'display'     => false,
			'permissions' => ! empty( $data['settings']['roles'] ),
			'thumb'       => ! empty( $data['thumb'] ),
		);

		// Do it on main site.
		General::switch_site();

		// First import settings.
		if ( ! empty( $data['settings'] ) ) {
			Import\Settings::get()->import( $data['settings'], $selected );
		}

		// Queue playlists for importing.
		if ( ! empty( $data['playlists'] ) && in_array( 'playlists', $selected, true ) ) {
			foreach ( $data['playlists'] as $playlist ) {
				$playlist['type']         = 'playlist';
				$playlist['import_thumb'] = $thumb;
				Core::get()->import->push_to_queue( $playlist );
			}
		}

		// Queue video for importing.
		// NOTE: DO NOT insert videos before playlists.
		if ( ! empty( $data['videos'] ) && in_array( 'videos', $selected, true ) ) {
			foreach ( $data['videos'] as $video ) {
				$video['type']         = 'video';
				$video['import_thumb'] = $thumb;
				Core::get()->import->push_to_queue( $video );
			}
		}

		// Save and dispatch.
		Core::get()->import->save()->dispatch();

		// Restore site.
		General::restore_site();

		// Send status response.
		return $this->get_response( $details );
	}

	/**
	 * Get the remaining count of the import process.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.1
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_status( $request ) {
		// Do it on main site.
		General::switch_site();

		// Get the remaining count.
		$status = Core::get()->import->get_status();

		// Restore site.
		General::restore_site();

		// Send status response.
		return $this->get_response( $status );
	}
}