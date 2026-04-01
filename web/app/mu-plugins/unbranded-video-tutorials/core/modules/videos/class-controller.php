<?php
/**
 * The videos functionality class of the plugin.
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

use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Controller
 *
 * @package WPMUDEV_Videos\Core\Modules\Videos
 */
class Controller extends Base {

	/**
	 * Initialize the custom post class.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Register videos CPT.
		add_action( 'init', array( $this, 'register_post' ) );

		// Register oembed providers.
		add_action( 'init', array( Embed::get(), 'register_providers' ) );

		// Setup missing custom video data before save.
		add_filter( 'wpmudev_vids_video_model_prepare_video_data', array( $this, 'setup_embed_data' ) );

		// Handle some special cases.
		add_action( 'wpmudev_vids_video_model_after_video_setup', array( $this, 'handle_special_cases' ) );

		// Setup endpoints.
		Endpoints::get();
	}

	/**
	 * Register custom post type.
	 *
	 * We are taking advantage of custom post type to store all
	 * video items so we can query them easily.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return void
	 */
	public function register_post() {
		// Make it is registered in main site only in multisite.
		if ( is_main_site() ) {
			$args = array(
				'labels'    => array(
					'name'          => __( 'WPMUDEV Videos', 'wpmudev_vids' ),
					'singular_name' => __( 'WPMUDEV Video', 'wpmudev_vids' ),
				),
				'public'    => false,
				'query_var' => false,
				'supports'  => array( 'title', 'thumbnail' ),
			);

			register_post_type( Models\Video::POST_TYPE, $args );
		}
	}

	/**
	 * Get a single video model object.
	 *
	 * Get from cache first, if empty, load directly.
	 * We don't load the model yet. We will use
	 *
	 * @param int|string $id Video ID (or slug for backward compat).
	 *
	 * @since 1.8.0
	 *
	 * @return Models\Video|Models\Custom_Video
	 */
	public function get_video( $id ) {
		// Get from cache first.
		$video = Helpers\Cache::get_cache(
			'video',
			array(
				'video' => $id,
			)
		);

		$type = '';

		// If not found in cache.
		if ( empty( $video ) ) {
			if ( is_numeric( $id ) ) {
				// Video type.
				$type = Models\Query::get()->get_video_type( $id );
			} elseif ( is_string( $id ) ) {
				// Get video id by slug.
				$id = Models\Query::get()->video_by_slug( $id );
				// Get the modal object.
				if ( ! empty( $id ) ) {
					// Video type.
					$type = Models\Query::get()->get_video_type( $id );
				}
			}

			switch ( $type ) {
				case 'custom':
					$video = Models\Custom_Video::get( $id );
					break;
				default:
					$video = Models\Video::get( $id );
					break;
			}

			// Set to cache.
			if ( ! empty( $video ) ) {
				Helpers\Cache::set_cache(
					'video',
					$video,
					array(
						'video' => $id,
					)
				);
			}
		}

		/**
		 * Filter hook to modify the video model object.
		 *
		 * @param object $video Video object.
		 * @param string $type  Video type.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_video', $video, $type );
	}

	/**
	 * Get custom video post items for custom videos.
	 *
	 * @param array $args Arguments to filter.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return Models\Video|Models\Custom_Video[] Array of videos with video id as key.
	 */
	public function get_videos( $args = array() ) {
		// Get from cache first.
		$videos = Helpers\Cache::get_cache( 'videos', $args, $found );

		// Get custom video posts.
		if ( empty( $found ) ) {
			// Get videos.
			$videos = Models\Query::get()->videos( $args );

			// Get the video objects.
			if ( ! empty( $videos ) && ( ! isset( $args['field'] ) || 'ids' !== $args['field'] ) ) {
				// Setup each video objects.
				foreach ( $videos as $key => $id ) {
					$videos[ $key ] = $this->get_video( $id );
				}
			}

			// Store data to cache.
			Helpers\Cache::set_cache( 'videos', $videos, $args );
		}

		/**
		 * Filter to alter videos list.
		 *
		 * @param array $videos Videos list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_videos', $videos );
	}

	/**
	 * Get total no. of videos available.
	 *
	 * We are getting only the count, not posts.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return int Number of videos.
	 */
	public function get_videos_count() {
		// Get from cache first.
		$count = Helpers\Cache::get_cache( 'videos_count' );

		// If not found in cache (do not check for empty).
		if ( empty( $count ) ) {
			// Get count.
			$count = Models\Query::get()->videos_count();

			// Set to cache.
			Helpers\Cache::set_cache( 'videos_count', $count );
		}

		/**
		 * Filter to alter the total no. of videos.
		 *
		 * @param int $count Videos count.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_videos_count', $count );
	}

	/**
	 * Get the videos assigned to a playlist.
	 *
	 * Get using get_posts if not found in cache.
	 * WP_Query will be slow, when we have taxonomy query
	 * attached to it. So make sure to cache.
	 *
	 * @param int          $playlist Playlist ID.
	 * @param array|string $fields   Field or field array.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return Models\Video|Models\Custom_Video[] Array of video objects.
	 */
	public function get_playlist_videos( $playlist, $fields = 'ids' ) {
		// Get from cache first.
		$videos = Helpers\Cache::get_cache(
			'playlist_videos',
			array(
				'playlist' => $playlist,
				'fields'   => $fields,
			),
			$found
		);

		// Get custom video posts.
		if ( empty( $found ) ) {
			// Get videos of playlist.
			$videos = Models\Query::get()->playlist_videos( $playlist, $fields );

			// Store data to cache.
			Helpers\Cache::set_cache(
				'playlist_videos',
				$videos,
				array(
					'playlist' => $playlist,
					'fields'   => $fields,
				)
			);
		}

		// Make sure it is array.
		$videos = empty( $videos ) ? array() : $videos;

		/**
		 * Filter to alter videos of a playlist.
		 *
		 * @param array $videos   Videos list.
		 * @param int   $playlist Playlist ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_playlist_videos', $videos, $playlist );
	}

	/**
	 * Get the videos assigned to a location.
	 *
	 * Get using get_posts if not found in cache.
	 * WP_Query will be slow, when we have taxonomy query
	 * attached to it. So make sure to cache.
	 *
	 * @param string $location Location Page ID.
	 *
	 * @since 1.8
	 *
	 * @return Models\Video|Models\Custom_Video[] Array of video objects.
	 */
	public function get_location_videos( $location ) {
		// Get from cache first.
		$videos = Helpers\Cache::get_cache(
			'location_videos',
			array( 'location' => $location ),
			$found
		);

		// Get custom video posts.
		if ( empty( $found ) ) {
			// Get videos of location.
			$videos = Models\Query::get()->location_videos( $location );

			// Store data to cache.
			Helpers\Cache::set_cache(
				'location_videos',
				$videos,
				array(
					'location' => $location,
				)
			);
		}

		/**
		 * Filter to alter videos of a location.
		 *
		 * @param array  $videos   Videos list.
		 * @param string $location Location ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_location_videos', $videos, $location );
	}

	/**
	 * Get recently updated video object.
	 *
	 * Get using get_posts if not found in cache.
	 *
	 * @param string $type Video type (leave empty to check all).
	 *
	 * @since 1.8.0
	 *
	 * @return Models\Video|Models\Custom_Video
	 */
	public function get_last_updated_video( $type = '' ) {
		// Get from cache first.
		$video = Helpers\Cache::get_cache( 'last_updated_video', array( 'type' => $type ) );

		// Get custom video posts.
		if ( empty( $video ) ) {
			// Get last updated video.
			$videos = Models\Query::get()->last_updated_video( $type );

			if ( ! empty( $videos ) ) {
				$video = $this->get_video( $videos[0] );

				// Store data to cache.
				Helpers\Cache::set_cache( 'last_updated_video', $video );
			}
		}

		/**
		 * Filter to alter last updated video result.
		 *
		 * @param Models\Video|Models\Custom_Video $videos Video object.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_last_updated_video', $video, array( 'type' => $type ) );
	}

	/**
	 * Get recently created video object.
	 *
	 * Get using get_posts if not found in cache.
	 *
	 * @param string $type Video type (leave empty to check all).
	 *
	 * @since 1.8.0
	 *
	 * @return Models\Video|Models\Custom_Video
	 */
	public function get_last_created_video( $type = '' ) {
		// Get from cache first.
		$video = Helpers\Cache::get_cache( 'last_created_video', array( 'type' => $type ) );

		// Get custom video posts.
		if ( empty( $video ) ) {
			// Get last updated video.
			$videos = Models\Query::get()->last_updated_video( $type );

			if ( ! empty( $videos ) ) {
				$video = $this->get_video( $videos[0] );

				// Store data to cache.
				Helpers\Cache::set_cache( 'last_created_video', $video, array( 'type' => $type ) );
			}
		}

		/**
		 * Filter to alter last created video result.
		 *
		 * @param Models\Video|Models\Custom_Video $videos Video object.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_last_created_video', $video );
	}

	/**
	 * Get a video embed html from video url and host.
	 *
	 * Make sure you include host in args, so that we can
	 * validate the video.
	 *
	 * @param string $url  Video URL.
	 * @param array  $args Arguments.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public function get_url_data( $url, $args = array() ) {
		// Get embed from url.
		$embed = Embed::get()->get_url_oembed_data( $url, $args );

		/**
		 * Filter hook to modify the video model object.
		 *
		 * @param object $video Video object.
		 * @param string $type  Video type.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_url_embed', $embed, $url );
	}

	/**
	 * Get a video embed html from video ID.
	 *
	 * @param int   $id   Video ID.
	 * @param array $args Arguments.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function get_video_embed( $id, $args = array() ) {
		// Get from cache first.
		$embed = Helpers\Cache::get_cache( 'video_embed_' . $id, $args );

		// If not found in cache.
		if ( empty( $embed ) ) {
			// Get video object.
			$video = self::get()->get_video( $id );

			if ( $video->is_valid() ) {
				if ( 'custom' === $video->video_type ) {
					$embed = Embed::get()->get_custom_video_embed( $id, $args );

					// Save to cache.
					Helpers\Cache::set_cache( 'video_embed_' . $id, $embed, $args );
				} else {
					$embed = Embed::get()->get_video_embed( $id, $args );
				}
			} else {
				$embed = array();
			}
		}

		/**
		 * Filter hook to modify the video embed data.
		 *
		 * @param array $embed Embed data.
		 * @param int   $id    Video ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_video_embed', $embed, $id );
	}

	/**
	 * Add missing custom video data to video object.
	 *
	 * Before saving custom video, make sure we have title and other
	 * possible data is set.
	 *
	 * @param array $data Video post data.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	public function setup_embed_data( $data ) {
		// If only data is missing.
		if ( empty( $video['post_title'] ) || empty( $data['meta_input']['video_duration'] ) ) {
			// We need url to perform this.
			if ( ! empty( $data['meta_input']['video_url'] ) ) {
				// Get the embed data using oembed.
				$embed_data = Embed::get()->get_embed_data( $data['meta_input']['video_url'] );

				// Set the title if required.
				if ( empty( $video['post_title'] ) && ! empty( $embed_data['title'] ) ) {
					$video['post_title'] = $embed_data['title'];
				}

				// Set the duration if required.
				if ( empty( $data['meta_input']['video_duration'] ) && ! empty( $embed_data['duration_seconds'] ) ) {
					$data['meta_input']['video_duration'] = $embed_data['duration_seconds'];
				}
			}
		}

		// If duration set.
		if ( ! empty( $data['meta_input']['video_duration'] ) ) {
			// Convert to seconds.
			$seconds = Helper::time_to_seconds( $data['meta_input']['video_duration'] );
			// Only if not empty.
			if ( ! empty( $seconds ) ) {
				// Set the value.
				$data['meta_input']['video_duration'] = $seconds;
			}
		}

		return $data;
	}

	/**
	 * Handle if any video object needs special attention.
	 *
	 * Oembed video slug should be case sensitive. In WP slugs will be
	 * automatically converted to lower case. So, make it camel case.
	 *
	 * @param Models\Video $video Video object.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function handle_special_cases( $video ) {
		// oEmbed should be case sensitive.
		if ( 'oembed' === $video->video_slug ) {
			$video->video_slug = 'oEmbed';
		}
	}
}