<?php
/**
 * Video management functionality REST endpoint.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Modules\Videos
 */

namespace WPMUDEV_Videos\Core\Modules\Videos;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WPMUDEV_Videos\Core\Helpers\Data;
use WPMUDEV_Videos\Core\Helpers\General;
use WPMUDEV_Videos\Core\Abstracts\Endpoint;

/**
 * Class Endpoints
 *
 * @package WPMUDEV_Videos\Core\Modules\Videos
 */
class Endpoints extends Endpoint {

	/**
	 * API endpoint for the video management.
	 *
	 * @since 1.8.0
	 *
	 * @var string $endpoint
	 */
	private $endpoint = '/videos/';

	/**
	 * Register the routes for handling videos functionality.
	 *
	 * Register routes:
	 * - v1/videos - GET to get videos list.
	 * - v1/videos/{id} - GET to get details of a single video.
	 * - v1/videos/{id} - POST, PUT, PATCH to update a video.
	 * - v1/videos/{id} - DELETE to delete a video.
	 * - v1/videos/embed/{id} - GET to get the video embed.
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
						'search' => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Text to search videos. Only titles will be searched.', 'wpmudev_vids' ),
						),
						'type'   => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video type to get. By default all types will be included.', 'wpmudev_vids' ),
						),
						'count'  => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'No. of videos required.', 'wpmudev_vids' ),
						),
						'page'   => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'Current page number (required only if count is set).', 'wpmudev_vids' ),
						),
						'field'  => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Fields to return (ids or object).', 'wpmudev_vids' ),
							'enum'        => array(
								'ids',
								'object',
							),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_video' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'title'         => array(
							'type'              => 'string',
							'required'          => true,
							'description'       => __( 'Video title.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								return ! empty( $param );
							},
						),
						'host'          => array(
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Video host type. Only supported hosts will be allowed.', 'wpmudev_vids' ),
							'enum'        => array_keys( Data::custom_hosts() ),
						),
						'url'           => array(
							'type'              => 'string',
							'required'          => true,
							'description'       => __( 'Video URL. Only oEmbed supported URLs should be used.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid url.
								return ! empty( $param ) && filter_var( $param, FILTER_VALIDATE_URL );
							},
						),
						'playlists'     => array(
							'type'        => 'array',
							'required'    => false,
							'default'     => array(),
							'description' => __( 'Video playlist ID. Should be a valid term ID.', 'wpmudev_vids' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
						'thumbnail'     => array(
							'type'              => 'integer',
							'required'          => false,
							'description'       => __( 'Video custom thumbnail ID. Should be a valid attachment ID.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid image attachment.
								return empty( $param ) || wp_attachment_is_image( $param );
							},
						),
						'start_enabled' => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video start time enabled.', 'wpmudev_vids' ),
						),
						'end_enabled'   => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video end time enabled.', 'wpmudev_vids' ),
						),
						'start_time'    => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video start time.', 'wpmudev_vids' ),
						),
						'end_time'      => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video end time.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);

		// Route to manage single video.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . '(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_video' ),
					'permission_callback' => array( $this, 'loggedin_check' ),
					'args'                => array(
						'id' => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Video ID to get details.', 'wpmudev_vids' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_video' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id'            => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Video ID to update.', 'wpmudev_vids' ),
						),
						'title'         => array(
							'type'              => 'string',
							'required'          => false,
							'description'       => __( 'Video title.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								return ! empty( $param );
							},
						),
						'host'          => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video host type. Only supported hosts will be allowed.', 'wpmudev_vids' ),
							'enum'        => array_keys( Data::custom_hosts() ),
						),
						'url'           => array(
							'type'              => 'string',
							'required'          => false,
							'description'       => __( 'Video URL. Only oEmbed supported URLs should be used.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid url.
								return ! empty( $param ) && filter_var( $param, FILTER_VALIDATE_URL );
							},
						),
						'playlists'     => array(
							'type'        => 'array',
							'required'    => false,
							'default'     => array(),
							'description' => __( 'Video playlist ID. Should be a valid term ID.', 'wpmudev_vids' ),
							'items'       => array(
								'type' => 'integer',
							),
						),
						'thumbnail'     => array(
							'type'              => 'integer',
							'required'          => false,
							'description'       => __( 'Video custom thumbnail ID. Should be a valid attachment ID.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid image attachment.
								return empty( $param ) || wp_attachment_is_image( $param );
							},
						),
						'start_enabled' => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video start time enabled.', 'wpmudev_vids' ),
						),
						'end_enabled'   => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video end time enabled.', 'wpmudev_vids' ),
						),
						'start_time'    => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video start time.', 'wpmudev_vids' ),
						),
						'end_time'      => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video end time.', 'wpmudev_vids' ),
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_video' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'id' => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Video ID to delete.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);

		// Route to get the video embed.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . '(?P<id>\d+)/embed',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_embed' ),
					'permission_callback' => array( $this, 'loggedin_check' ),
					'args'                => array(
						'id'            => array(
							'type'        => 'integer',
							'required'    => true,
							'description' => __( 'Video ID to get embed.', 'wpmudev_vids' ),
						),
						'width'         => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'The embed width.', 'wpmudev_vids' ),
						),
						'height'        => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'The embed height.', 'wpmudev_vids' ),
						),
						'autoplay'      => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Should autoplay the embed video.', 'wpmudev_vids' ),
						),
						'thumbnail'     => array(
							'type'              => 'integer',
							'required'          => false,
							'description'       => __( 'Video custom thumbnail ID. Should be a valid attachment ID.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid image attachment.
								return empty( $param ) || wp_attachment_is_image( $param );
							},
						),
						'start_enabled' => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video start time enabled.', 'wpmudev_vids' ),
						),
						'end_enabled'   => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video end time enabled.', 'wpmudev_vids' ),
						),
						'start_time'    => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video start time.', 'wpmudev_vids' ),
						),
						'end_time'      => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video end time.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);

		// Route to get the video embed.
		register_rest_route(
			$this->get_namespace(),
			$this->endpoint . 'embed',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_url_embed' ),
					'permission_callback' => array( $this, 'permissions_check' ),
					'args'                => array(
						'url'           => array(
							'type'              => 'string',
							'required'          => true,
							'description'       => __( 'Video URL. Only oEmbed supported URLs should be used.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid url.
								return ! empty( $param ) && filter_var( $param, FILTER_VALIDATE_URL );
							},
						),
						'host'          => array(
							'type'        => 'string',
							'required'    => true,
							'description' => __( 'Video host type. Only supported hosts will be allowed.', 'wpmudev_vids' ),
							'enum'        => array_keys( Data::custom_hosts() ),
						),
						'width'         => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'The embed width.', 'wpmudev_vids' ),
						),
						'height'        => array(
							'type'        => 'integer',
							'required'    => false,
							'description' => __( 'The embed height.', 'wpmudev_vids' ),
						),
						'autoplay'      => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Should autoplay the embed video.', 'wpmudev_vids' ),
						),
						'thumbnail'     => array(
							'type'              => 'integer',
							'required'          => false,
							'description'       => __( 'Video custom thumbnail ID. Should be a valid attachment ID.', 'wpmudev_vids' ),
							'validate_callback' => function ( $param ) {
								// Should be a valid image attachment.
								return empty( $param ) || wp_attachment_is_image( $param );
							},
						),
						'start_enabled' => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video start time enabled.', 'wpmudev_vids' ),
						),
						'end_enabled'   => array(
							'type'        => 'boolean',
							'required'    => false,
							'description' => __( 'Video end time enabled.', 'wpmudev_vids' ),
						),
						'start_time'    => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video start time.', 'wpmudev_vids' ),
						),
						'end_time'      => array(
							'type'        => 'string',
							'required'    => false,
							'description' => __( 'Video end time.', 'wpmudev_vids' ),
						),
					),
				),
			)
		);

		// Route to get the recent video.
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
	}

	/**
	 * Get a single video video data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_video( $request ) {
		// Get the video ID.
		$video_id = $request->get_param( 'id' );

		General::switch_site();

		// Get the video object.
		$video = Controller::get()->get_video( $video_id );

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $video->is_error() ) {
			return $this->get_error_response(
				$video->get_error()->get_error_message(),
				$video->get_error()->get_error_code()
			);
		} else {
			return $this->get_response( $video->to_array() );
		}
	}

	/**
	 * Create a new video post.
	 *
	 * Only custom video types can be created.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_video( $request ) {
		// Make sure to query main site.
		General::switch_site();

		// Get a new video object.
		$video = Models\Custom_Video::get();

		// Setup required properties.
		$video->video_title = $request->get_param( 'title' );
		$video->video_host  = $request->get_param( 'host' );
		$video->video_url   = $request->get_param( 'url' );

		// Flag for enabling start time.
		if ( ! empty( $request['start_enabled'] ) ) {
			$video->video_start = true;
		}

		// Flag for enabling end time.
		if ( ! empty( $request['end_enabled'] ) ) {
			$video->video_end = true;
		}

		// Set start time if enabled.
		if ( ! empty( $request['start_time'] ) ) {
			$video->video_start_time = $request['start_time'];
		}

		// Set end time if enabled.
		if ( ! empty( $request['end_time'] ) ) {
			$video->video_end_time = $request['end_time'];
		}

		// Create or update video.
		$result = $video->save();

		// Send error response.
		if ( $video->is_error() ) {
			// Restore old blog.
			General::restore_site();

			return $this->get_error_response(
				$video->get_error()->get_error_message(),
				$video->get_error()->get_error_code()
			);
		}

		// Get the new video object.
		$video = Controller::get()->get_video( $result );

		// Add playlist.
		if ( $result && ! empty( $request['playlists'] ) ) {
			// Add new playlists.
			$video->add_playlist( $request['playlists'] );
		}

		// Add thumbnail.
		if ( $result && ! empty( $request['thumbnail'] ) ) {
			// Add new thumbnail.
			$video->set_thumbnail( $request['thumbnail'] );
		}

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $result ) {
			return $this->get_response( $video->to_array() );
		} else {
			return $this->get_error_response(
				__( 'Could not create the video. Please try again.', 'wpmudev_vids' ),
				'create_failed'
			);
		}
	}

	/**
	 * Update a video data.
	 *
	 * Only title and playlist can be updated.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_video( $request ) {
		// Get the video ID.
		$video_id = $request->get_param( 'id' );

		// Make sure to query main site.
		General::switch_site();

		// Get the video object.
		$video = Controller::get()->get_video( $video_id );

		// Send error response.
		if ( $video->is_error() ) {
			// Restore old blog.
			General::restore_site();

			return $this->get_error_response(
				$video->get_error()->get_error_message(),
				$video->get_error()->get_error_code()
			);
		}

		// Update title.
		if ( ! empty( $request['title'] ) ) {
			$video->video_title = sanitize_text_field( $request['title'] );

		}

		if ( 'custom' === $video->video_type ) {
			// Update host.
			if ( ! empty( $request['host'] ) ) {
				$video->video_host = $request['host'];
			}

			// Update url.
			if ( ! empty( $request['url'] ) ) {
				$video->video_url = $request['url'];
			}

			// Update url.
			if ( ! empty( $request['url'] ) ) {
				$video->video_url = $request['url'];
			}

			// Flag for enabling start time.
			if ( $request->offsetExists( 'start_enabled' ) ) {
				$video->video_start = (bool) $request['start_enabled'];
			}

			// Flag for enabling end time.
			if ( $request->offsetExists( 'end_enabled' ) ) {
				$video->video_end = (bool) $request['end_enabled'];
			}

			// Set start time if enabled.
			if ( ! empty( $request['start_time'] ) ) {
				$video->video_start_time = $request['start_time'];
			}

			// Set end time if enabled.
			if ( ! empty( $request['end_time'] ) ) {
				$video->video_end_time = $request['end_time'];
			}

			// Add thumbnail.
			if ( ! empty( $request['thumbnail'] ) ) {
				// Set thumbnail.
				$video->set_thumbnail( $request['thumbnail'] );
			} elseif ( $request->offsetExists( 'thumbnail' ) && empty( $request['thumbnail'] ) ) {
				// Delete the thumbnail.
				$video->delete_thumbnail();
			}
		}

		// Add playlist.
		if ( ! empty( $request['playlist'] ) ) {
			// Remove existing playlists.
			$video->remove_playlist( $video->playlists );

			// Add new playlists.
			$video->add_playlist( $request['playlist'] );
		}

		// Create or update video.
		$result = $video->save();

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $result ) {
			// Get updated video.
			$video = Controller::get()->get_video( $result );

			return $this->get_response( $video );
		} else {
			return $this->get_error_response(
				__( 'Could not update the video. Please try again.', 'wpmudev_vids' ),
				'update_failed'
			);
		}
	}

	/**
	 * Delete a video custom post and it's meta.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_video( $request ) {
		// Video ID.
		$video_id = $request->get_param( 'id' );

		// Make sure to query main site.
		General::switch_site();

		// Get the video object.
		$video = Controller::get()->get_video( $video_id );

		// If error, return early.
		if ( $video->is_error() ) {
			return $this->get_error_response(
				$video->get_error()->get_error_message(),
				$video->get_error()->get_error_code()
			);
		}

		// Only custom videos can be deleted.
		if ( 'custom' === $video->video_type ) {
			$deleted = $video->delete();
		} else {
			return $this->get_error_response(
				__( 'Default videos can not be deleted.', 'wpmudev_vids' ),
				'delete_failed'
			);
		}

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( $deleted ) {
			return $this->get_response(
				array(
					'message' => __( 'Video has been deleted.', 'wpmudev_vids' ),
				)
			);
		} else {
			return $this->get_error_response(
				__( 'Could not delete the video. Please try again.', 'wpmudev_vids' ),
				'delete_failed'
			);
		}
	}

	/**
	 * Get the list of available videos.
	 *
	 * This includes both default and custom videos.
	 * You can also pass a search param, to search through
	 * the video titles.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_list( $request ) {
		// Pagination params.
		$args = array(
			'posts_per_page' => $this->get_param( $request, 'count', -1 ),
			'paged'          => $this->get_param( $request, 'page', 1 ),
		);

		// Set search query.
		if ( ! empty( $request['search'] ) ) {
			$args['s'] = $request['search'];
		}

		// Set video type if found.
		if ( ! empty( $request['type'] ) ) {
			$args['video_type'] = $request['type'];
		}

		// Only ids required.
		if ( ! empty( $request['field'] ) && 'ids' === $request['field'] ) {
			$args['field'] = 'ids';
		}

		// Make sure to query main site.
		General::switch_site();

		// Get videos.
		$videos = Controller::get()->get_videos( $args );

		// Restore old blog.
		General::restore_site();

		// Send response.
		return $this->get_response( $videos );
	}

	/**
	 * Get a single video embed data.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_embed( $request ) {
		$args = array();

		// Get the video ID.
		$video_id = $request->get_param( 'id' );

		// Embed width.
		if ( ! empty( $request['width'] ) ) {
			$args['width'] = (int) $request['width'];
		}

		// Embed height.
		if ( ! empty( $request['height'] ) ) {
			$args['height'] = (int) $request['height'];
		}

		// Should autoplay.
		if ( ! empty( $request['autoplay'] ) ) {
			$args['autoplay'] = (int) $request['autoplay'];
		}

		// Custom thumbnail.
		if ( ! empty( $request['thumbnail'] ) ) {
			$args['thumbnail'] = (int) $request['thumbnail'];
		}

		// Start time.
		if ( ! empty( $request['start_time'] ) && ! empty( $request['start_enabled'] ) ) {
			$args['start_time'] = $request['start_time'];
		}

		// End time.
		if ( ! empty( $request['end_time'] ) && ! empty( $request['end_enabled'] ) ) {
			$args['end_time'] = $request['end_time'];
		}

		// Make sure to query main site.
		General::switch_site();

		// Get the video object.
		$embed = Controller::get()->get_video_embed( $video_id, $args );

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( empty( $embed ) ) {
			return $this->get_error_response(
				__( 'Couldn\'t find the embed for the given video.', 'wpmudev_vids' ),
				'empty_embed'
			);
		} else {
			return $this->get_response( $embed );
		}
	}

	/**
	 * Get embed from the given url and video options.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_url_embed( $request ) {
		// Get the video ID.
		$video_url = $request->get_param( 'url' );

		$args = array(
			'host' => $request->get_param( 'host' ),
		);

		// Embed width.
		if ( ! empty( $request['width'] ) ) {
			$args['width'] = (int) $request['width'];
		}

		// Embed height.
		if ( ! empty( $request['height'] ) ) {
			$args['height'] = (int) $request['height'];
		}

		// Custom thumbnail.
		if ( ! empty( $request['thumbnail'] ) ) {
			$args['thumbnail'] = (int) $request['thumbnail'];
		}

		// Start time.
		if ( ! empty( $request['start_time'] ) && ! empty( $request['start_enabled'] ) ) {
			$args['start_time'] = $request['start_time'];
		}

		// End time.
		if ( ! empty( $request['end_time'] ) && ! empty( $request['end_enabled'] ) ) {
			$args['end_time'] = $request['end_time'];
		}

		// Make sure to query main site.
		General::switch_site();

		// Invalid host.
		if ( ! Embed::get()->is_valid( $video_url, $args['host'] ) ) {
			return $this->get_error_response(
				__( 'Invalid host. Please try again with valid URL and host.', 'wpmudev_vids' ),
				'invalid_host'
			);
		}

		$data = Controller::get()->get_url_data( $video_url, $args );

		// Restore old blog.
		General::restore_site();

		// Send response.
		if ( empty( $data ) ) {
			return $this->get_error_response(
				__( 'There was an error processing the video URL. Please try again.', 'wpmudev_vids' ),
				'invalid_url'
			);
		} else {
			return $this->get_response( $data );
		}
	}

	/**
	 * Get the last updated video.
	 *
	 * @since 1.8.0
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function recent() {
		// Make sure to query main site.
		General::switch_site();

		// Get recently updated video.
		$video = Controller::get()->get_last_updated_video();

		// Restore old blog.
		General::restore_site();

		// Send response.
		return $this->get_response( array( 'video' => $video ) );
	}
}