<?php
/**
 * Playlist management functionality REST endpoint.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Modules\Playlists
 */

namespace WPMUDEV_Videos\Core\Modules\Playlists;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WPMUDEV_Videos\Core\Helpers\Data;
use WPMUDEV_Videos\Core\Helpers\Cache;
use WPMUDEV_Videos\Core\Modules\Videos;
use WPMUDEV_Videos\Core\Helpers\General;
use WPMUDEV_Videos\Core\Abstracts\Endpoint;

/**
 * Class Endpoints
 *
 * @package WPMUDEV_Videos\Core\Modules\Playlists
 */
class Endpoints extends Endpoint {

	/**
	 * API endpoint for the playlist management.
	 *
	 * @since 1.8.0
	 *
	 * @var string $endpoint
	 */
	private $endpoint = '/playlists/';

	/**
	 * Register the routes for handling settings functionality.
	 *
	 * Register routes:
	 * - v1/playlists - GET to get playlist list.
	 * - v1/playlists - POST to create new playlist.
	 * - v1/playlists/{id} - GET to get details of a single playlist.
	 * - v1/playlists/{id} - POST, PUT, PATCH to update a playlist.
	 * - v1/playlists/{id} - DELETE to delete a playlist.
	 * - v1/playlists/thumbnail/{id} - POST, PUT, PATCH to update the playlist thumbnail.
	 * - v1/playlists/thumbnail/{id} - DELETE to delete the playlist thumbnail.
	 *
	 * @since 1.8.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_list' ),
					'permission_callback' => array( $this, 'loggedin_check' ),
					'args'                => array(
						'search'   => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Text to search playlist. Only titles will be searched.', 'wpmudev_vids' ),
						),
						'count'    => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'No. of playlists required.', 'wpmudev_vids' ),
						),
						'page'     => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'Current page number (required only if count is set).', 'wpmudev_vids' ),
						),
						'show_all' => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Should skip permission check and get all playlists.', 'wpmudev_vids' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_playlist' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'title'       => array(
							'type'              => 'string',
							'required'          => true,
							'description'       => __( 'Playlist title.', 'wpmudev_vids' ),
							'validate_callback' => array( $this, 'validate_name' ),
						),
						'videos'      => array(
							'required'          => true,
							'default'           => array(),
							'description'       => __( 'Video IDs to add to the playlist.', 'wpmudev_vids' ),
							'validate_callback' => array( $this, 'validate_videos' ),
							'items'             => array(
								'type' => 'integer',
							),
						),
						'locations'   => array(
							'required'          => false,
							'default'           => array(),
							'description'       => __( 'Locations where these playlist videos are available in contextual help.', 'wpmudev_vids' ),
							'validate_callback' => array( $this, 'validate_locations' ),
							'items'             => array(
								'type' => 'integer',
							),
						),
						'roles'       => array(
							'required'    => false,
							'default'     => array(),
							'description' => __( 'User roles who can access this playlist and it\'s videos', 'wpmudev_vids' ),
							'items'       => array(
								'type' => 'string',
							),
						),
						'description' => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Playlist description.', 'wpmudev_vids' ),
						),
						'thumbnail'   => array(
							'type'              => 'integer',
							'required'          => false,
							'description'       => __( 'Playlist thumbnail ID.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid image attachment.
								return empty( $param ) || wp_attachment_is_image( $param );
							},
						),
					),
				),
			)
		);

		// Route to manage single playlist.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . '(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_playlist' ),
					'permission_callback' => array( $this, 'loggedin_check' ),
					'args'                => array(
						'id' => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Playlist ID to get details.', 'wpmudev_vids' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_playlist' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id'          => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Playlist ID to update.', 'wpmudev_vids' ),
						),
						'title'       => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Playlist title.', 'wpmudev_vids' ),
						),
						'description' => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Playlist description.', 'wpmudev_vids' ),
						),
						'videos'      => array(
							'required'          => false,
							'description'       => __( 'Video IDs of the playlist (Be careful, you need to provide all playlists here. Otherwise existing videos will be removed).', 'wpmudev_vids' ),
							'validate_callback' => array( $this, 'validate_videos' ),
							'items'             => array(
								'type' => 'integer',
							),
						),
						'locations'   => array(
							'required'          => false,
							'description'       => __( 'Locations where these playlist videos are available in contextual help.', 'wpmudev_vids' ),
							'validate_callback' => array( $this, 'validate_locations' ),
							'items'             => array(
								'type' => 'integer',
							),
						),
						'roles'       => array(
							'required'    => false,
							'description' => __( 'User roles who can access this playlist and it\'s videos', 'wpmudev_vids' ),
							'items'       => array(
								'type' => 'string',
							),
						),
						'thumbnail'   => array(
							'type'              => 'integer',
							'required'          => false,
							'description'       => __( 'Playlist thumbnail ID.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid image attachment.
								return empty( $param ) || wp_attachment_is_image( $param );
							},
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_playlist' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id' => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Playlist ID to delete.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);

		// Route to manage single playlist videos.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . '(?P<id>\d+)/videos',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'link_videos' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id'     => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Playlist ID.', 'wpmudev_vids' ),
						),
						'videos' => array(
							'required'    => true,
							'default'     => array(),
							'description' => __( 'Video IDs to add to the playlist.', 'wpmudev_vids' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'unlink_videos' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id'     => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Playlist ID.', 'wpmudev_vids' ),
						),
						'videos' => array(
							'required'          => true,
							'default'           => array(),
							'description'       => __( 'Video IDs to remove from the playlist.', 'wpmudev_vids' ),
							'validate_callback' => array( $this, 'validate_videos' ),
							'items'             => array(
								'type' => 'integer',
							),
						),
					),
				),
			)
		);

		// Route to get the recent playlist.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . 'recent',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'recent' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		// Route to bulk delete playlists and videos.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . 'bulk-actions',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'bulk_actions' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'action'    => array(
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Bulk action to perform with playlist.', 'wpmudev_vids' ),
						),
						'videos'    => array(
							'required'    => false,
							'default'     => array(),
							'description' => __( 'Video IDs to bulk process.', 'wpmudev_vids' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
						'playlists' => array(
							'required'    => false,
							'default'     => array(),
							'description' => __( 'Playlist IDs to bulk process.', 'wpmudev_vids' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
					),
				),
			)
		);

		// Route to reorder playlist.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . 'reorder',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'reorder' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'playlist' => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Playlist ID to reorder.', 'wpmudev_vids' ),
						),
						'from'     => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Previous order of the playlist.', 'wpmudev_vids' ),
						),
						'to'       => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'New order of the playlist.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);

		// Route to playlist order repair.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . 'repair-orders',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'repair' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Get a single playlist data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_playlist( $request ) {
		// Get the playlist ID.
		$id = $request->get_param( 'id' );

		// Make sure to query main site.
		General::switch_site();

		// Get playlist.
		$playlist = Controller::get()->get_playlist( $id );

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $playlist->is_error() ) {
			return $this->get_error_response(
				$playlist->get_error()->get_error_message(),
				$playlist->get_error()->get_error_code()
			);
		} else {
			return $this->get_response( $playlist->to_array() );
		}
	}

	/**
	 * Create a new playlist term.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_playlist( $request ) {
		// Make sure to query main site.
		General::switch_site();

		// Get a new playlist object.
		$playlist = Models\Playlist::get( 0 );

		// Setup required properties.
		$playlist->title  = $request->get_param( 'title' );
		$playlist->videos = $this->get_param( $request, 'videos', array() );

		// Set description.
		if ( ! empty( $request['description'] ) ) {
			$playlist->description = $request['description'];
		}

		// Set thumbnail.
		if ( ! empty( $request['thumbnail'] ) ) {
			$playlist->playlist_thumbnail = $request['thumbnail'];
		}

		// Set locations.
		if ( $request->offsetExists( 'locations' ) ) {
			$playlist->playlist_locations = (array) $request['locations'];
		}

		// Set roles.
		if ( $request->offsetExists( 'roles' ) ) {
			$playlist->playlist_roles = (array) $request['roles'];
		}

		// All new playlists are custom.
		$playlist->playlist_type = 'custom';

		// Get last order.
		$last_order = (int) Models\Query::get()->get_last_order();

		// Set order.
		$playlist->playlist_order = $last_order + 1;

		// Create or update video.
		$result = $playlist->save();

		if ( $result ) {
			$playlist = Controller::get()->get_playlist( $result, true );
		}

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $result ) {
			return $this->get_response( $playlist );
		} else {
			return $this->get_error_response(
				__( 'Could not create the playlist. Please try again.', 'wpmudev_vids' ),
				'create_failed'
			);
		}
	}

	/**
	 * Update a single playlist data.
	 *
	 * NOTE: We won't link videos in update. You need to use the
	 * playlist link/unlink endpoints for that.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_playlist( $request ) {
		// Get the playlist ID.
		$id = $request->get_param( 'id' );

		// Make sure to query main site.
		General::switch_site();

		// Get the playlist object.
		$playlist = Controller::get()->get_playlist( $id );

		// Send error response.
		if ( $playlist->is_error() ) {
			// Restore old blog.
			General::restore_site();

			return $this->get_error_response(
				$playlist->get_error()->get_error_message(),
				$playlist->get_error()->get_error_code()
			);
		}

		// Set the title.
		if ( ! empty( $request['title'] ) ) {
			$playlist->title = $request['title'];
		}

		// Set description.
		if ( ! empty( $request['description'] ) ) {
			$playlist->description = $request['description'];
		}

		// Set thumbnail.
		if ( ! empty( $request['thumbnail'] ) ) {
			$playlist->playlist_thumbnail = $request['thumbnail'];
		} elseif ( $request->offsetExists( 'thumbnail' ) && empty( $request['thumbnail'] ) ) {
			// Delete the thumbnail.
			$playlist->playlist_thumbnail = 0;
		}

		// Set locations.
		if ( $request->offsetExists( 'locations' ) ) {
			$playlist->playlist_locations = (array) $request['locations'];
		}

		// Set roles.
		if ( $request->offsetExists( 'roles' ) ) {
			$playlist->playlist_roles = (array) $request['roles'];
		}

		// Add playlist.
		if ( $request->offsetExists( 'videos' ) ) {
			// Remove existing videos.
			if ( ! empty( $playlist->videos ) ) {
				foreach ( $playlist->videos as $video ) {
					$playlist->remove_video( $video );
				}
			}

			// Add new videos.
			$playlist->videos = (array) $request['videos'];
		}

		// Create or update video.
		$result = $playlist->save();

		if ( $result ) {
			$playlist = Controller::get()->get_playlist( $id, true );
		}

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $result ) {
			return $this->get_response( $playlist );
		} else {
			return $this->get_error_response(
				__( 'Could not update the playlist. Please try again.', 'wpmudev_vids' ),
				'update_failed'
			);
		}
	}

	/**
	 * Delete a playlist from the database.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_playlist( $request ) {
		// Get the playlist ID.
		$id = $request->get_param( 'id' );

		// Make sure to query main site.
		General::switch_site();

		// Get the playlist object.
		$playlist = Controller::get()->get_playlist( $id );

		// If error, return early.
		if ( $playlist->is_error() ) {
			return $this->get_response(
				$playlist->get_error()->get_error_message(),
				$playlist->get_error()->get_error_code()
			);
		}

		$deleted = $playlist->delete();

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $deleted ) {
			return $this->get_response(
				array(
					'message' => __( 'Playlist has been deleted.', 'wpmudev_vids' ),
				)
			);
		} else {
			return $this->get_error_response(
				__( 'Could not delete the playlist. Please try again.', 'wpmudev_vids' ),
				'delete_failed'
			);
		}
	}

	/**
	 * Process the bulk actions for the playlist.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function bulk_actions( $request ) {
		// Get the playlist IDs.
		$items = $request->get_param( 'items' );

		// Get the action.
		$action = $request->get_param( 'action' );

		switch ( $action ) {
			case 'delete':
				// Bulk delete.
				return $this->bulk_delete( $items );
		}

		// Send response.
		return $this->get_error_response(
			__( 'Could not process the bulk action. Please try again.', 'wpmudev_vids' ),
			'bulk_action_failed'
		);
	}

	/**
	 * Get the list of playlist.
	 *
	 * This includes both default and custom playlist.
	 * You can also pass a search param, to search through
	 * the playlist titles.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_list( $request ) {
		// Get the video search term.
		$search = $request->get_param( 'search' );

		// Pagination params.
		$args = array(
			'count'    => $this->get_param( $request, 'count', 0 ),
			'page'     => $this->get_param( $request, 'page', 1 ),
			'show_all' => $this->get_param( $request, 'show_all', false ),
		);

		// Add search query.
		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}

		// Make sure to query main site.
		General::switch_site();

		// Get videos.
		$playlists = Controller::get()->get_playlists( $args );

		// Restore old blog.
		General::restore_site();

		// Send response.
		return $this->get_response( $playlists );
	}

	/**
	 * Link videos to the playlist
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function link_videos( $request ) {
		// Get the playlist ID.
		$id = $request->get_param( 'id' );
		// Get the video IDs.
		$videos = (array) $request->get_param( 'videos' );

		if ( ! empty( $videos ) ) {
			// Make sure to query main site.
			General::switch_site();

			// Link each videos.
			foreach ( $videos as $video ) {
				Controller::get()->link_playlists_to_video( $id, $video );
			}

			// Restore old blog.
			General::restore_site();

			// Send response.
			return $this->get_response(
				array(
					'message' => __( 'Videos has been linked to the playlist.', 'wpmudev_vids' ),
				)
			);
		}

		// Send error response.
		return $this->get_error_response(
			__( 'Could not link the videos to playlist. Please try again.', 'wpmudev_vids' ),
			'link_failed'
		);
	}

	/**
	 * Unlink videos from the playlist
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function unlink_videos( $request ) {
		// Get the playlist ID.
		$id = $request->get_param( 'id' );

		// Get the video IDs.
		$videos = (array) $request->get_param( 'videos' );

		if ( ! empty( $videos ) ) {
			// Make sure to query main site.
			General::switch_site();

			// Link each videos.
			foreach ( $videos as $video ) {
				Controller::get()->unlink_playlists_from_video( $id, $video );
			}

			// Refresh cache.
			Cache::refresh_cache();

			// Restore old blog.
			General::restore_site();

			// Send response.
			return $this->get_response(
				array(
					'message' => __( 'Videos has been unlinked from the playlist.', 'wpmudev_vids' ),
				)
			);
		}

		// Send error response.
		return $this->get_error_response(
			__( 'Could not unlink the videos from playlist. Please try again.', 'wpmudev_vids' ),
			'unlink_failed'
		);
	}

	/**
	 * Get the recently updated playlist.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function recent() {
		// Make sure to query main site.
		General::switch_site();

		// Get recently updated video.
		$playlist = Controller::get()->get_last_updated_playlist();

		// Restore old blog.
		General::restore_site();

		// Send response.
		return $this->get_response( $playlist );
	}

	/**
	 * Validate the playlist video IDs.
	 *
	 * @param array $params Video IDs.
	 *
	 * @since 1.8.0
	 *
	 * @return bool|WP_Error
	 */
	public function validate_videos( $params ) {
		// Should be array.
		if ( ! is_array( $params ) ) {
			return false;
		}

		// Loop through each ids.
		foreach ( $params as $param ) {
			// Make sure the video exist.
			if ( false === get_post_status( $param ) ) {
				return new WP_Error(
					'rest_invalid_video',
					/* translators: %d: Invalid video ID. */
					sprintf( __( 'Invalid video ID: %d', 'wpmudev_vids' ), $param )
				);
			}
		}

		return true;
	}

	/**
	 * Validate the playlist locations.
	 *
	 * @param array $params Locations.
	 *
	 * @since 1.8.0
	 *
	 * @return bool|WP_Error
	 */
	public function validate_locations( $params ) {
		// Should be array.
		if ( ! is_array( $params ) ) {
			return false;
		}

		// Allowed locations.
		$allowed = array_keys( Data::video_pages() );

		// Get invalid items.
		$invalid = array_diff( $params, $allowed );

		// Make sure the video exist.
		if ( count( $invalid ) > 0 ) {
			return new WP_Error(
				'rest_invalid_locations',
				/* translators: %d: Invalid locations. */
				sprintf( __( 'Invalid locations: %s', 'wpmudev_vids' ), implode( ', ', $invalid ) )
			);
		}

		return true;
	}

	/**
	 * Validate the playlist name.
	 *
	 * Check if the playlist already exist.
	 *
	 * @param string $param Name param.
	 *
	 * @since 1.8.4
	 *
	 * @return bool|WP_Error
	 */
	public function validate_name( $param ) {
		// Should be array.
		if ( empty( $param ) ) {
			return false;
		}

		// Make sure the video exist.
		if ( term_exists( $param, Models\Playlist::TAXONOMY ) ) {
			return new WP_Error(
				'rest_invalid_name',
				__( 'Playlist name already in use. Please try another name.', 'wpmudev_vids' )
			);
		}

		return true;
	}

	/**
	 * Bulk delete playlists and videos.
	 *
	 * @param array $playlists Playlist IDs and videos ids.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_REST_Response
	 */
	private function bulk_delete( $playlists ) {
		// Make sure to query main site.
		General::switch_site();

		$videos            = array();
		$deleted_videos    = array();
		$deleted_playlists = array();

		if ( ! empty( $playlists ) ) {
			foreach ( $playlists as $id => $data ) {
				if ( ! empty( $data['selected'] ) ) {
					// Get the playlist object.
					$playlist = Controller::get()->get_playlist( $id );

					// If error or if it is default playlist, return early.
					if ( ! empty( $playlist ) && ! $playlist->is_error() && 'custom' === $playlist->playlist_type ) {
						// Attempt to delete the playlist.
						if ( $playlist->delete() ) {
							$deleted_playlists[] = $id;
						}
					}
				}

				// Now remove the videos if the playlist is not deleted.
				if ( ! in_array( $id, $deleted_playlists, true ) && ! empty( $data['videos'] ) ) {
					foreach ( $data['videos'] as $video ) {
						if ( isset( $videos[ $video ] ) ) {
							$videos[ $video ][] = $id;
						} else {
							$videos[ $video ] = array( $id );
						}
					}
				}
			}
		}

		if ( ! empty( $videos ) ) {
			foreach ( $videos as $video => $playlist_ids ) {
				// If playlist is already delete, skip.
				$playlist_ids = array_unique( $playlist_ids );

				if ( ! empty( $playlist_ids ) ) {
					// Get the video object.
					$video = Videos\Controller::get()->get_video( $video );

					// If error, return early.
					if ( empty( $video ) || $video->is_error() ) {
						continue;
					}

					// Remove playlists.
					if ( $video->remove_playlist( $playlist_ids ) ) {
						$deleted_videos[ $video->id ] = $playlist_ids;
					}
				}
			}
		}

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( count( $deleted_playlists ) > 0 || count( $deleted_videos ) > 0 ) {
			return $this->get_response(
				array(
					'message' => __( 'Playlist and videos has been deleted.', 'wpmudev_vids' ),
					'deleted' => array(
						'playlists' => $deleted_playlists,
						'videos'    => $deleted_videos,
					),
				)
			);
		} else {
			return $this->get_error_response(
				__( 'Could not delete the playlists and videos. Please try again.', 'wpmudev_vids' ),
				'delete_failed'
			);
		}
	}

	/**
	 * Reorder the playlist to new order.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.4
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function reorder( $request ) {
		// Get the playlist ID.
		$playlist = (int) $request->get_param( 'playlist' );
		// Get the playlist ID.
		$from = (int) $request->get_param( 'from' );
		// Get the playlist ID.
		$to = (int) $request->get_param( 'to' );

		// Make sure to query main site.
		General::switch_site();

		// Get recently updated video.
		Controller::get()->reorder_playlists( $playlist, $from, $to );

		// Restore old blog.
		General::restore_site();

		// Send response.
		return $this->get_response();
	}

	/**
	 * Repair the playlist to orders if required.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.4
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function repair( $request ) {
		// Make sure to query main site.
		General::switch_site();

		// Repair playlist orders.
		Controller::get()->repair_playlist_order();

		// Restore old blog.
		General::restore_site();

		// Send response.
		return $this->get_response();
	}
}