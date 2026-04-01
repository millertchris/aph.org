<?php
/**
 * Playlist import task class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.1
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */

namespace WPMUDEV_Videos\Core\Tasks\Import;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Helpers\General;
use WPMUDEV_Videos\Core\Modules\Playlists;

/**
 * Class Playlist
 *
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */
class Playlist extends Helper {

	/**
	 * Video and playlist link queue name.
	 *
	 * @since 1.8.1
	 */
	const LINK_QUEUE = 'ivt_import_playlist_video_queue';

	/**
	 * Import a video post.
	 *
	 * @param array $item Data to import.
	 *
	 * @since 1.8.1
	 */
	public function import( $item ) {
		// Make sure to query main site.
		General::switch_site();

		// Remove duplicate.
		$this->remove_existing( $item );
		// Create new playlist.
		$this->create_playlist( $item );

		// Restore old blog.
		General::restore_site();
	}

	/**
	 * Remove existing duplicate.
	 *
	 * This is usually applicable only to default playlists.
	 * If there is a playlist exist with same slug, delete it.
	 *
	 * @param array $playlist Playlist data to import.
	 *
	 * @since 1.8.1
	 */
	public function remove_existing( $playlist ) {
		global $wpdb;

		// Get the existing video if any.
		// phpcs:ignore
		$term_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT t1.term_id FROM $wpdb->terms t1 JOIN $wpdb->term_taxonomy t2 ON t2.term_id = t1.term_id WHERE t1.slug = %s AND t2.taxonomy = %s",
				$playlist['slug'],
				Playlists\Models\Playlist::TAXONOMY
			)
		);

		if ( ! empty( $term_id ) ) {
			// Get the video object.
			$playlist = Playlists\Controller::get()->get_playlist( $term_id );

			// Delete playlist.
			if ( ! $playlist->is_error() ) {
				$playlist->delete();
			}
		}
	}

	/**
	 * Create a new playlist post.
	 *
	 * @param array $data Video data.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	public function create_playlist( $data ) {
		// Get a new playlist object.
		$playlist = Playlists\Models\Playlist::get();

		// Setup required properties.
		$playlist->title              = $this->get_value( 'title', $data );
		$playlist->slug               = $this->get_value( 'slug', $data );
		$playlist->description        = $this->get_value( 'description', $data );
		$playlist->playlist_locations = $this->get_value( 'playlist_locations', $data, array() );
		$playlist->playlist_roles     = $this->get_value( 'playlist_roles', $data, array() );
		$playlist->playlist_type      = $this->get_value( 'playlist_type', $data, 'custom' );

		// Upload thumb.
		if ( ! empty( $data['import_thumb'] ) && ! empty( $data['thumbnail']['url'] ) ) {
			// Get the thumbnail id.
			$thumb_id = $this->upload_thumb( $data['thumbnail']['url'], $this->get_value( 'title', $data ) );

			if ( ! empty( $thumb_id ) ) {
				// Set the thumbnail.
				$playlist->playlist_thumbnail = $thumb_id;
			}
		}

		// Create or update video.
		$playlist_id = $playlist->save();

		// No need to continue if error.
		if ( $playlist->is_error() ) {
			return;
		}

		// Queue playlists.
		$this->queue_playlists( $data, $playlist_id );

		/**
		 * Action hook to fire after default video is imported.
		 *
		 * @param int   $video_id Video ID.
		 * @param array $data     Video data.
		 *
		 * @since 1.8.1
		 */
		do_action( 'wpmudev_vids_after_import_playlist', $playlist_id, $data );
	}

	/**
	 * Queue the playlists for linking with videos.
	 *
	 * If playlists are being imported with videos,
	 * we need to link them after importing.
	 *
	 * @param array $data        Playlist data.
	 * @param int   $playlist_id Playlist ID.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	private function queue_playlists( $data, $playlist_id ) {
		// We need playlist id and videos.
		if ( empty( $playlist_id ) || empty( $data['id'] ) ) {
			return;
		}

		// Get existing queue.
		$queue = get_transient( self::LINK_QUEUE );

		// Make sure it's array.
		$queue = empty( $queue ) ? array() : (array) $queue;

		// Set to queue.
		$queue[ $data['id'] ] = $playlist_id;

		// Set transient.
		set_transient( self::LINK_QUEUE, $queue );
	}
}