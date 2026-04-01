<?php
/**
 * Database functionality class for the playlist.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Modules\Playlists\Models
 */

namespace WPMUDEV_Videos\Core\Modules\Playlists\Models;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Query
 *
 * You need to manually switch the site before utilizing this
 * class. Otherwise you will not get the results as the video
 * data is in main site of multisite.
 *
 * @package WPMUDEV_Videos\Core\Modules\Playlists\Models
 */
class Query extends Base {

	/**
	 * Get playlist ids from taxonomy terms.
	 *
	 * @param array $args Arguments to filter.
	 *
	 * @since 1.8.0
	 *
	 * @return int[] Array of terms.
	 */
	public function playlists( $args = array() ) {
		// Restrict playlists with permissions.
		if ( empty( $args['show_all'] ) ) {
			$args = array_merge(
				$args,
				array(
					// phpcs:ignore
					'meta_query' => array(
						// Permission filter.
						$this->playlist_roles_meta_args(),
					),
				)
			);
		}

		// Playlist term query arguments.
		$args = $this->term_args( $args );

		// Get term ids.
		$playlists = $this->get_terms( $args );

		/**
		 * Filter to alter playlist list.
		 *
		 * @param array $playlists Playlist list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_playlists', $playlists );
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
	public function playlist_by_slug( $slug ) {
		// Get the term object by slug.
		$term = get_term_by( 'slug', $slug, Playlist::TAXONOMY );

		// Set in cache.
		if ( is_wp_error( $term ) || empty( $term ) ) {
			$term_id = false;
		} else {
			$term_id = $term->term_id;
		}

		/**
		 * Filter to alter playlist list.
		 *
		 * @param array       $playlists Playlist list.
		 * @param string      $slug      Slug of the playlist.
		 * @param object|bool $term      Term object.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_playlist_by_slug', $term_id, $slug, $term );
	}

	/**
	 * Get total no. of playlists available.
	 *
	 * We are getting only the count, not terms.
	 *
	 * @since 1.8.0
	 *
	 * @return int Number of playlists.
	 */
	public function playlists_count() {
		// Playlist term query arguments.
		$args = $this->term_args();

		// Get the count.
		$count = wp_count_terms( $args );

		/**
		 * Filter to alter the total no. of videos.
		 *
		 * @param int $count Videos count.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_total_count', $count );
	}

	/**
	 * Get the playlist IDs assigned to a location.
	 *
	 * Get using get_posts if not found in cache.
	 * WP_Query will be slow, when we have taxonomy query
	 * attached to it. So make sure to cache when you use it.
	 *
	 * @param string $location Location Page ID.
	 *
	 * @since 1.8
	 *
	 * @return int[] Array of term ids.
	 */
	public function location_playlists( $location ) {
		// Playlist query arguments to get location based items.
		$args = $this->term_args(
			array(
				// phpcs:ignore
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'compare' => 'LIKE',
						'key'     => 'playlist_locations',
						// phpcs:ignore
						'value'   => serialize( $location ),
					),
					// Permission filter.
					$this->playlist_roles_meta_args(),
				),
			)
		);

		/**
		 * Filter to alter playlist arguments of a location.
		 *
		 * @param array  $args     Playlist arguments.
		 * @param string $location Location ID.
		 *
		 * @since 1.8.0
		 */
		$args = apply_filters( 'wpmudev_vids_playlist_model_location_playlists_args', $args, $location );

		// Get terms.
		$playlists = $this->get_terms( $args );

		/**
		 * Filter to alter playlist IDs of a location.
		 *
		 * @param array  $playlists Playlist IDs.
		 * @param string $location  Location ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_location_playlists', $playlists, $location );
	}

	/**
	 * Get term query meta arguments to filter by roles.
	 *
	 * We need to make sure only allowed roles are seeing the videos.
	 *
	 * @since 1.8.0
	 *
	 * @return array $args
	 */
	private function playlist_roles_meta_args() {
		$args = array();

		// Get current user object.
		$user = wp_get_current_user();

		// Get current roles.
		$roles = (array) $user->roles;

		// If admin, no need to filter.
		if ( ! in_array( 'administrator', $roles, true ) && ! current_user_can( 'setup_network' ) ) {
			// Relation should be "OR".
			$args = array(
				'relation' => 'OR',
			);

			foreach ( $roles as $role ) {
				$args[] = array(
					'compare' => 'LIKE',
					'key'     => 'playlist_roles',
					'value'   => serialize( $role ), // phpcs:ignore
				);
			}
		}

		/**
		 * Filter to alter playlist query arguments for roles.
		 *
		 * @param array $args Arguments.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_roles_meta_args', $args );
	}

	/**
	 * Get recently updated playlist term object.
	 *
	 * Only one term id will be returned.
	 *
	 * @since 1.8.0
	 *
	 * @return int[] Array of term ids.
	 */
	public function last_updated_playlist() {
		// Playlist query arguments to get recently updated one.
		$args = $this->term_args(
			array(
				'orderby'  => 'meta_value',
				'number'   => 1,
				'order'    => 'DESC',
				// phpcs:ignore
				'meta_key' => 'playlist_updated_time',
			)
		);

		$playlists = $this->get_terms( $args );

		/**
		 * Filter to alter last updated playlist result.
		 *
		 * @param array $playlists Playlist list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_last_updated', $playlists );
	}

	/**
	 * Get recently created playlist term object.
	 *
	 * Only one term id will be returned.
	 *
	 * @since 1.8.0
	 *
	 * @return int[] Array of term ids.
	 */
	public function last_created_playlist() {
		// Playlist query arguments to get recently updated one.
		$args = $this->term_args(
			array(
				'orderby' => 'ID',
				'order'   => 'DESC',
				'number'  => 1,
			)
		);

		$playlists = $this->get_terms( $args );

		/**
		 * Filter to alter last updated playlist result.
		 *
		 * @param array $playlists Playlist list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_last_created', $playlists );
	}

	/**
	 * Link playlist(s) to given video.
	 *
	 * Multiple playlists can be set to a video at a time.
	 *
	 * @param array $playlists Array of playlist ids.
	 * @param int   $video     Video ID.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function link_playlists_to_video( $playlists, $video ) {
		$success = false;

		// Link playlist terms.
		$ids = wp_set_object_terms(
			(int) $video,
			(array) $playlists,
			Playlist::TAXONOMY,
			true
		);

		if ( ! empty( $ids ) ) {
			$success = true;

			/**
			 * Action hook to fire after playlists are set to video.
			 *
			 * @param int|array $id    Playlist ID(s).
			 * @param int       $video Video ID.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpmudev_vids_playlist_model_after_video_playlist_set', $ids, $video );
		}

		return $success;
	}

	/**
	 * Remove video from a given playlist(s).
	 *
	 * Multiple playlists can be removed from a video at a time.
	 *
	 * @param array $playlists Array of playlist ids.
	 * @param int   $video_id  Video ID.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function unlink_playlists_from_video( $playlists, $video_id ) {
		$success = wp_remove_object_terms(
			(int) $video_id,
			(array) $playlists,
			Playlist::TAXONOMY
		);

		// Make sure the response is correct.
		if ( is_wp_error( $success ) || empty( $success ) ) {
			$success = false;
		}

		if ( $success ) {
			/**
			 * Action hook to fire after playlists are removed from a video.
			 *
			 * @param int|array $id       Playlist ID(s).
			 * @param int       $video_id Video ID.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpmudev_vids_playlist_model_after_playlist_video_remove', $playlists, $video_id );
		}

		return $success;
	}

	/**
	 * Parse the query arguments and include default ones.
	 *
	 * Make sure to use this method if you don't pass the
	 * taxonomy name and other required items common for playlist
	 * terms.
	 *
	 * @param array $extra_args Arguments to filter.
	 *
	 * @since 1.8.0
	 *
	 * @return array $args
	 */
	private function term_args( $extra_args = array() ) {
		// Setup the default arguments.
		$args = array(
			'taxonomy'   => Playlist::TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'ids',
			'orderby'    => 'meta_value_num',
			// phpcs:ignore
			'meta_key'   => 'playlist_order',
			'order'      => 'ASC',
		);

		// Parse arguments.
		$args = wp_parse_args( $extra_args, $args );

		// Include pagination if required.
		$args = wp_parse_args(
			$args,
			$this->get_pagination_args( $extra_args )
		);

		/**
		 * Filter to change the query arguments for custom videos.
		 *
		 * See https://developer.wordpress.org/reference/classes/WP_Term_Query/__construct/
		 *
		 * @param array $args Term Query arguments.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_term_args', $args );
	}

	/**
	 * Parse the query arguments and get pagination args.
	 *
	 * WP_Meta_Query will not setup pagination automatically.
	 * We need to calculate the offset based on the page number
	 * and no. of items required.
	 *
	 * @param array $args Arguments.
	 *
	 * @since 1.8.0
	 *
	 * @return array $args
	 */
	private function get_pagination_args( $args = array() ) {
		// Setup pagination args.
		if ( isset( $args['count'], $args['page'] ) ) {
			$number = (int) $args['count'];
			$page   = (int) $args['page'];

			return array(
				'number' => $number,
				'offset' => ( $page - 1 ) * $number,
			);
		}

		return array();
	}

	/**
	 * Make new query to get the playlists from db.
	 *
	 * Custom video items are stored as custom post. We need to
	 * get the post items using get_terms.
	 * Custom post is registered in main site only if it's a multisite.
	 * NOTE: We don't use cache here.
	 *
	 * @param array $args Term Query arguments.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	private function get_terms( $args ) {
		// Get terms.
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) ) {
			$terms = array();
		}

		/**
		 * Filter to modify terms result after returned from db.
		 *
		 * See https://developer.wordpress.org/reference/functions/get_terms/#parameters
		 *
		 * @param array $terms Terms array.
		 * @param array $args  Query arguments.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_get_terms', $terms, $args );
	}

	/**
	 * Get playlist and order to reorder.
	 *
	 * Get the details of playlists between the from
	 * and to playlist IDs.
	 *
	 * @param int $from From position.
	 * @param int $to   To position.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	public function get_playlists_to_order( $from, $to ) {
		global $wpdb;

		// Setup query.
		$query = $wpdb->prepare(
			"
				SELECT t1.term_id, t2.meta_value as playlist_order FROM $wpdb->term_taxonomy t1
				JOIN $wpdb->termmeta t2 ON t1.term_id = t2.term_id
				WHERE t1.taxonomy = %s
					AND t2.meta_key = 'playlist_order'
					AND t2.meta_value BETWEEN %d AND %d
				",
			Playlist::TAXONOMY,
			$from,
			$to
		);

		// Get playlist ID and order.
		// phpcs:ignore
		$playlists = $wpdb->get_results( $query, OBJECT_K );

		/**
		 * Filter to modify terms and order result.
		 *
		 * @param array $playlists Array of objects.
		 * @param int   $from      From position.
		 * @param int   $to        To position.
		 *
		 * @since 1.8.4
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_get_playlists_to_order', $playlists, $from, $to );
	}

	/**
	 * Get last playlist order.
	 *
	 * Get the largest order of the existing playlist.
	 *
	 * @since 1.8.4
	 *
	 * @return array
	 */
	public function get_last_order() {
		global $wpdb;

		// Setup query.
		$query = $wpdb->prepare(
			"
				SELECT t1.meta_value as playlist_order FROM $wpdb->termmeta t1
				JOIN $wpdb->term_taxonomy t2 ON t1.term_id = t2.term_id
				WHERE t2.taxonomy = %s
					AND t1.meta_key = 'playlist_order'
				ORDER BY ABS(t1.meta_value) DESC
				LIMIT 1
				",
			Playlist::TAXONOMY
		);

		// Get playlist order.
		// phpcs:ignore
		$last_order = $wpdb->get_var( $query );

		/**
		 * Filter to modify last playlist order.
		 *
		 * @param int $last_order Last order.
		 *
		 * @since 1.8.4
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_get_next_order', $last_order );
	}

	/**
	 * Get the current playlist orders.
	 *
	 * Get the playlist id and order in order.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	public function get_current_orders() {
		global $wpdb;

		// Setup query.
		$query = $wpdb->prepare(
			"
				SELECT t1.meta_value as playlist_order, t1.term_id as playlist_id FROM $wpdb->termmeta t1
				JOIN $wpdb->term_taxonomy t2 ON t1.term_id = t2.term_id
				WHERE t2.taxonomy = %s
					AND t1.meta_key = 'playlist_order'
				ORDER BY ABS(t1.meta_value) ASC
				",
			Playlist::TAXONOMY
		);

		// Get playlist orders.
		// phpcs:ignore
		$orders = $wpdb->get_results( $query, ARRAY_A );

		/**
		 * Filter to modify the playlist orders result.
		 *
		 * @param array $orders Latest orders.
		 *
		 * @since 1.8.6
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_get_current_orders', $orders );
	}
}