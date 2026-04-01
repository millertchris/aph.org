<?php
/**
 * Database functionality class for the videos.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Modules\Videos\Models
 */

namespace WPMUDEV_Videos\Core\Modules\Videos\Models;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WP_Query;
use WPMUDEV_Videos\Core\Abstracts\Base;
use WPMUDEV_Videos\Core\Modules\Playlists;

/**
 * Class Query
 *
 * You need to manually switch the site before utilizing this
 * class. Otherwise you will not get the results as the video
 * data is in main site of multisite.
 * Models will not use cache.
 *
 * @package WPMUDEV_Videos\Core\Modules\Videos\Models
 */
class Query extends Base {

	/**
	 * Get custom video post items for videos.
	 *
	 * @param array $args Arguments to filter.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return int[] Array of Video IDs.
	 */
	public function videos( $args = array() ) {
		// Video query arguments.
		$args = $this->post_args( $args );

		// Get videos.
		$videos = get_posts( $args );

		/**
		 * Filter to alter videos list.
		 *
		 * @param array $videos Videos list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_get_videos', $videos );
	}

	/**
	 * Get playlist from playlist slug.
	 *
	 * @param string $slug Term slug.
	 *
	 * @since 1.8.0
	 *
	 * @return int
	 */
	public function video_by_slug( $slug ) {
		// Get the video post by slug.
		$post = get_page_by_path( $slug, 'OBJECT', Video::POST_TYPE );

		// Set in cache.
		if ( is_wp_error( $post ) || empty( $post->ID ) ) {
			$post_id = false;
		} else {
			$post_id = $post->ID;
		}

		/**
		 * Filter to alter playlist list.
		 *
		 * @param array         $playlists Playlist list.
		 * @param string        $slug      Slug of the playlist.
		 * @param \WP_Post|bool $post      Post object.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_video_by_slug', $post_id, $slug, $post );
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
	public function videos_count() {
		// Video query arguments.
		$args = $this->post_args();

		// Create new query object.
		$query = new WP_Query( $args );

		$count = $query->found_posts;

		/**
		 * Filter to alter the total no. of videos.
		 *
		 * @param int $count Videos count.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_total_count', $count );
	}

	/**
	 * Get the videos assigned to a playlist.
	 *
	 * Get using get_posts if not found in cache.
	 * WP_Query will be slow, when we have taxonomy query
	 * attached to it. So make sure to cache when you use it.
	 *
	 * @param int          $playlist Playlist ID.
	 * @param array|string $fields   Field or field array.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class, Added fields param.
	 *
	 * @return int[] Array of post ids.
	 */
	public function playlist_videos( $playlist, $fields = 'ids' ) {
		// Add taxonomy query to get videos.
		$args = $this->post_args(
			array(
				'numberposts' => - 1,
				// phpcs:ignore
				'tax_query'   => array(
					array(
						'taxonomy'         => Playlists\Models\Playlist::TAXONOMY,
						'field'            => 'term_id',
						'terms'            => $playlist,
						'include_children' => false,
					),
				),
				'fields'      => $fields,
			)
		);

		// Get videos.
		$videos = get_posts( $args );

		/**
		 * Filter to alter videos of a playlist.
		 *
		 * @param array $videos   Videos list.
		 * @param int   $playlist Playlist ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_playlist_videos', $videos, $playlist );
	}

	/**
	 * Get the videos assigned to a admin page help section.
	 *
	 * Get using get_posts if not found in cache.
	 * WP_Query will be slow, when we have taxonomy query
	 * attached to it. So make sure to cache when you use it.
	 *
	 * @param string $location Location Page ID.
	 *
	 * @since 1.8
	 *
	 * @return array Array of post data.
	 */
	public function location_videos( $location ) {
		$videos = array();

		// Get location playlist IDs.
		$playlists = Playlists\Models\Query::get()->location_playlists( $location );

		// Only when playlists found.
		if ( ! empty( $playlists ) ) {
			// Add taxonomy query to get videos.
			$args = $this->post_args(
				array(
					'numberposts' => - 1,
					// phpcs:ignore
					'tax_query'   => array(
						array(
							'taxonomy'         => Playlists\Models\Playlist::TAXONOMY,
							'field'            => 'term_id',
							'terms'            => $playlists,
							'include_children' => false,
						),
					),
					'fields'      => array( 'ID', 'post_title' ),
				)
			);

			// Get videos.
			$videos = get_posts( $args );
		}

		/**
		 * Filter to alter videos of a location.
		 *
		 * @param array  $videos   Videos list (ID and Title).
		 * @param string $location Location ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_playlist_videos', $videos, $location );
	}

	/**
	 * Get recently created video ID.
	 *
	 * @param string $type Video type (leave empty to check all).
	 *
	 * @since 1.8.0
	 *
	 * @return int[] Array of IDs.
	 */
	public function last_created_video( $type = '' ) {
		// Video query arguments to get recently updated one.
		$args = $this->post_args(
			array(
				'orderby'     => 'date',
				'order'       => 'DESC',
				'numberposts' => 1,
				'video_type'  => $type,
			)
		);

		// Get post ids.
		$videos = get_posts( $args );

		/**
		 * Filter to alter last updated video result.
		 *
		 * @param array $videos Videos list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_last_updated_video', $videos );
	}

	/**
	 * Get recently updated video ID.
	 *
	 * @param string $type Video type (leave empty to check all).
	 *
	 * @since 1.8.0
	 *
	 * @return int[] Array of IDs.
	 */
	public function last_updated_video( $type = '' ) {
		// Video query arguments to get recently updated one.
		$args = $this->post_args(
			array(
				'orderby'     => 'modified',
				'order'       => 'DESC',
				'numberposts' => 1,
				'video_type'  => $type,
			)
		);

		// Get post ids.
		$videos = get_posts( $args );

		/**
		 * Filter to alter last updated video result.
		 *
		 * @param array $videos Videos list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_last_updated_video', $videos );
	}

	/**
	 * Get a single video type name.
	 *
	 * Default type will be `default`.
	 * No need to cache the result as it is already cached by the WP.
	 *
	 * @param int $id Video ID.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public function get_video_type( $id ) {
		// Should be a valid post type first.
		if ( Video::POST_TYPE !== get_post_type( $id ) ) {
			return '';
		}

		// Get the video type.
		$type = get_post_meta( $id, 'video_type', true );

		// If not found in cache.
		if ( empty( $type ) ) {
			$type = apply_filters( 'wpmudev_vids_get_video_type_default', 'default' );
		}

		return $type;
	}

	/**
	 * Get the post query arguments based on the current page params.
	 *
	 * If pagination request or sorting is found, generate WP_Query arguments
	 * based on the parameters.
	 *
	 * @param array $extra_args Arguments to filter.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return array $args
	 */
	private function post_args( $extra_args = array() ) {
		// Setup the default arguments.
		$args = array(
			'post_type'   => Video::POST_TYPE,
			'post_status' => 'publish', // We need only public items.
			'fields'      => 'ids',
			'orderby'     => 'ID',
			'order'       => 'DESC',
		);

		// Parse arguments.
		$args = wp_parse_args( $extra_args, $args );

		// Setup video type filter.
		if ( ! empty( $args['video_type'] ) ) {
			// phpcs:ignore
			$args['meta_key'] = 'video_type';
			// phpcs:ignore
			$args['meta_value'] = $args['video_type'];

			// No need of type arg.
			unset( $args['video_type'] );
		}

		/**
		 * Filter to change the query arguments for custom videos.
		 *
		 * See https://codex.wordpress.org/Function_Reference/WP_Query#Parameters
		 *
		 * @param array $args Query arguments.
		 *
		 * @since 1.7
		 */
		return apply_filters( 'wpmudev_videos_videos_query_args', $args );
	}
}