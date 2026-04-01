<?php
/**
 * Playlist functionality class.
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

use WPMUDEV_Videos\Core\Helpers\Cache;
use WPMUDEV_Videos\Core\Abstracts\Base;
use WPMUDEV_Videos\Core\Modules\Videos;

/**
 * Class Controller
 *
 * @package WPMUDEV_Videos\Core\Modules\Playlists
 */
class Controller extends Base {

	/**
	 * Initialize the playlist class.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Register playlist taxonomy.
		add_action( 'init', array( $this, 'register_taxonomy' ) );

		// Schedule repair task.
		add_action( 'init', array( $this, 'schedule_repair' ) );

		// Unschedule repair task.
		add_action( 'wpmudev_vids_before_deactivate', array( $this, 'unschedule_repair' ) );

		// Execute the playlist order repair.
		add_action( 'wpmudev_videos_repair_playlist_order', array( $this, 'repair_playlist_order' ) );

		// Setup endpoints.
		Endpoints::get();
	}

	/**
	 * Register playlist taxonomy for the videos cpt.
	 *
	 * Do not check for main site to register taxonomy. We need the
	 * taxonomy registered in all sites of multisite to access it
	 * using switch_to_blog function.
	 *
	 * @see   https://core.trac.wordpress.org/ticket/20541
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		// Register taxonomy.
		register_taxonomy(
			Models\Playlist::TAXONOMY,
			Videos\Models\Video::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'Playlist', 'wpmudev_vids' ),
					'singular_name' => __( 'Playlist', 'wpmudev_vids' ),
					'search_items'  => __( 'Search Playlist', 'wpmudev_vids' ),
					'all_items'     => __( 'All Playlist', 'wpmudev_vids' ),
					'edit_item'     => __( 'Edit Playlist', 'wpmudev_vids' ),
					'update_item'   => __( 'Update Playlist', 'wpmudev_vids' ),
					'add_new_item'  => __( 'Add New Playlist', 'wpmudev_vids' ),
					'new_item_name' => __( 'New Playlist Name', 'wpmudev_vids' ),
					'menu_name'     => __( 'Playlist', 'wpmudev_vids' ),
				),
				'public'       => false,
				'hierarchical' => false,
				'show_in_rest' => false,
				'meta_box_cb'  => false,
			)
		);
	}

	/**
	 * Schedule repair playlist order task.
	 *
	 * Sometimes due to not properly handling the playlist
	 * delete, the order may get broken. Check if we can fix
	 * anything every 24 hours.
	 *
	 * @since 1.8.6
	 *
	 * @return void
	 */
	public function schedule_repair() {
		if ( ! wp_next_scheduled( 'wpmudev_videos_repair_playlist_order' ) ) {
			wp_schedule_event(
				time(),
				'twicedaily',
				'wpmudev_videos_repair_playlist_order'
			);
		}
	}

	/**
	 * Unschedule repair playlist order task.
	 *
	 * Make sure to run this during plugin deactivation.
	 *
	 * @since 1.8.6
	 *
	 * @return void
	 */
	public function unschedule_repair() {
		// Next scheduled time.
		$timestamp = wp_next_scheduled( 'wpmudev_videos_repair_playlist_order' );

		// Unschedule it.
		wp_unschedule_event( $timestamp, 'wpmudev_videos_repair_playlist_order' );
	}

	/**
	 * Get playlist list from taxonomy terms.
	 *
	 * Get from db only if not found in cache.
	 *
	 * @param array $args  Arguments to filter.
	 * @param bool  $force Force skip cache.
	 *
	 * @since 1.8.0
	 *
	 * @return Models\Playlist[] Array of terms.
	 */
	public function get_playlists( $args = array(), $force = false ) {
		// Get from cache first if not skipped.
		if ( $force ) {
			$found = false;
		} else {
			$cache_args = array_merge( $args, array( '_ivt_current_user_id' => get_current_user_id() ) );
			$playlists  = Cache::get_cache( 'playlists', $cache_args, $found );
		}

		// Get custom video posts.
		if ( empty( $found ) ) {
			// Get playlist ids.
			$playlists = Models\Query::get()->playlists( $args );

			// Get the playlist objects.
			if ( ! empty( $playlists ) ) {
				foreach ( $playlists as $key => $id ) {
					// Setup object.
					$playlists[ $key ] = $this->get_playlist( $id, $force );
				}

				// Store data to cache.
				Cache::set_cache( 'playlists', $playlists, $args );
			}
		}

		/**
		 * Filter to alter playlist list array.
		 *
		 * @param array $playlists Playlist list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_playlists', $playlists );
	}

	/**
	 * Get a single playlist model object.
	 *
	 * Get from cache first, if empty, load directly.
	 * We don't load the model yet. We will use
	 *
	 * @param int|string $id    Playlist ID (or slug for backword compat).
	 * @param bool       $force Force skip cache.
	 *
	 * @since 1.8.0
	 *
	 * @return Models\Playlist
	 */
	public function get_playlist( $id, $force = false ) {
		// Get from cache first.
		if ( ! $force ) {
			$playlist = Cache::get_cache(
				'playlist',
				array(
					'playlist' => $id,
				)
			);
		} else {
			$playlist = false;
		}

		// If not found in cache.
		if ( empty( $playlist ) ) {
			if ( is_numeric( $id ) ) {
				$playlist = Models\Playlist::get( $id, $force );
			} elseif ( is_string( $id ) ) {
				// Get playlist id by slug.
				$id = Models\Query::get()->playlist_by_slug( $id );
				// Get the modal object.
				if ( ! empty( $id ) ) {
					$playlist = Models\Playlist::get( $id, $force );
				}
			}

			// Set to cache.
			if ( ! empty( $playlist ) ) {
				Cache::set_cache(
					'playlist',
					$playlist,
					array(
						'playlist' => $id,
					)
				);
			}
		}

		/**
		 * Filter hook to modify the playlist model object.
		 *
		 * @param Models\Playlist $playlist Playlist object.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_playlist', $playlist );
	}

	/**
	 * Get recently updated playlist model object.
	 *
	 * Get from db only if not found in cache.
	 *
	 * @since 1.8.0
	 *
	 * @return Models\Playlist
	 */
	public function get_last_updated_playlist() {
		// Get from cache first.
		$playlist = Cache::get_cache( 'last_updated_playlist' );

		// Get playlists.
		if ( empty( $playlist ) ) {
			// Get playlist ids.
			$playlists = Models\Query::get()->last_updated_playlist();

			// Get the playlist object.
			if ( ! empty( $playlists ) ) {
				$playlist = $this->get_playlist( $playlists[0] );

				// Store data to cache.
				Cache::set_cache( 'last_updated_playlist', $playlist );
			}
		}

		/**
		 * Filter to alter last updated playlist object.
		 *
		 * @param array $playlist Playlist list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_last_updated_playlist', $playlist );
	}

	/**
	 * Get recently created playlist model object.
	 *
	 * Get from db only if not found in cache.
	 *
	 * @since 1.8.0
	 *
	 * @return Models\Playlist
	 */
	public function get_last_created_playlist() {
		// Get from cache first.
		$playlist = Cache::get_cache( 'last_created_playlist' );

		// Get playlists.
		if ( empty( $playlist ) ) {
			// Get playlist ids.
			$playlists = Models\Query::get()->last_created_playlist();

			// Get the playlist object.
			if ( ! empty( $playlists ) ) {
				$playlist = $this->get_playlist( $playlists[0] );

				// Store data to cache.
				Cache::set_cache( 'last_created_playlist', $playlist );
			}
		}

		/**
		 * Filter to alter last created playlist object.
		 *
		 * @param array $playlist Playlist list.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_last_created_playlist', $playlist );
	}

	/**
	 * Link playlist(s) to given video.
	 *
	 * Multiple playlists can be set to a video at a time by passing array of ids.
	 *
	 * @param array $ids   Playlist ID or array of playlist ids.
	 * @param int   $video Video ID.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function link_playlists_to_video( $ids, $video ) {
		$ids = (array) $ids;

		if ( Models\Query::get()->link_playlists_to_video( $ids, $video ) ) {
			// Delete video cache.
			Cache::delete_cache( 'video', array( 'video' => $video ) );

			// Delete playlist cache.
			foreach ( $ids as $id ) {
				Cache::delete_cache( 'playlist', array( 'playlist' => $id ) );
				Cache::delete_cache( 'playlist_videos', array( 'playlist' => $id ) );
			}

			return true;
		}

		return false;
	}

	/**
	 * Remove playlist(s) from given videos.
	 *
	 * Multiple playlists can be removed from a video at a time.
	 *
	 * @param array $ids   Playlist ID or array of playlist ids.
	 * @param int   $video Video ID.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function unlink_playlists_from_video( $ids, $video ) {
		$ids = (array) $ids;

		if ( Models\Query::get()->unlink_playlists_from_video( $ids, $video ) ) {
			// Delete video cache.
			Cache::delete_cache( 'video', array( 'video' => $video ) );

			// Delete playlist cache.
			foreach ( $ids as $id ) {
				Cache::delete_cache( 'playlist', array( 'playlist' => $id ) );
			}

			return true;
		}

		return false;
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
	public function get_playlists_count() {
		// Get from cache first.
		$count = Cache::get_cache( 'playlists_count' );

		// If not found in cache (do not check for empty).
		if ( empty( $count ) ) {
			// Get count.
			$count = Models\Query::get()->playlists_count();

			Cache::set_cache( 'playlists_count', $count );
		}

		/**
		 * Filter to alter the total no. of playlists.
		 *
		 * @param int $count Playlist count.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_playlists_count', $count );
	}

	/**
	 * Process the playlist reordering request.
	 *
	 * First get the playlists in between the from and to
	 * playlist IDs, then assign them the new order.
	 *
	 * @param int $playlist Playlist ID.
	 * @param int $from     From position.
	 * @param int $to       To position.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	public function reorder_playlists( $playlist, $from, $to ) {
		// Basic validations.
		if ( empty( $playlist ) || $to === $from ) {
			return false;
		}

		// Set the order from and to positions.
		$order_from = $from > $to ? $to : $from + 1;
		$order_to   = $from > $to ? $from - 1 : $to;

		// Get playlist ID and order.
		$playlists = Models\Query::get()->get_playlists_to_order( $order_from, $order_to );

		// Update new order of changed playlist.
		update_term_meta( $playlist, 'playlist_order', $to );

		// Loop through each playlists.
		foreach ( $playlists as $item ) {
			// Check if already updated.
			if ( $playlist === (int) $item->term_id ) {
				continue;
			}

			// Get new order based on the dragged direction.
			$new_oder = $from > $to ? (int) $item->playlist_order + 1 : (int) $item->playlist_order - 1;

			// Update new order.
			update_term_meta( $item->term_id, 'playlist_order', $new_oder );
		}

		// Clear cache.
		Cache::refresh_cache();

		/**
		 * Action hook to execute after reordering a playlist position.
		 *
		 * @param int   $playlist  Playlist ID.
		 * @param int   $from      From position.
		 * @param int   $to        To position.
		 * @param array $playlists Playlists IDs and Order.
		 *
		 * @since 1.8.4
		 */
		do_action( 'wpmudev_vids_after_playlist_reorder', $playlist, $from, $to, $playlists );

		return true;
	}

	/**
	 * Process the playlist reordering request.
	 *
	 * First get the playlists in between the from and to
	 * playlist IDs, then assign them the new order.
	 *
	 * @since 1.8.6
	 *
	 * @return void
	 */
	public function repair_playlist_order() {
		// Get orders.
		$orders = Models\Query::get()->get_current_orders();

		// Start with 0.
		$i = 0;

		if ( ! empty( $orders ) ) {
			foreach ( $orders as $order ) {
				// If no change, no need to update.
				if ( isset( $order['playlist_order'], $order['playlist_id'] ) && $i !== (int) $order['playlist_order'] ) {
					// Update new order.
					update_term_meta( $order['playlist_id'], 'playlist_order', $i );
				}

				// Increase order.
				++$i;
			}

			// Clear cache.
			Cache::refresh_cache();
		}

		/**
		 * Action hook to execute after reordering a playlist position.
		 *
		 * @since 1.8.6
		 */
		do_action( 'wpmudev_vids_after_repair_playlist_order' );
	}
}