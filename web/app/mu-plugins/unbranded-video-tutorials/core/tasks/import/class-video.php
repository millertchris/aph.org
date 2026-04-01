<?php
/**
 * The video item import class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.1
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */

namespace WPMUDEV_Videos\Core\Tasks\Import;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Modules\Videos;
use WPMUDEV_Videos\Core\Helpers\General;
use WPMUDEV_Videos\Core\Modules\Playlists;

/**
 * Class Video
 *
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */
class Video extends Helper {

	/**
	 * Video and playlist link queue name.
	 *
	 * @since 1.8.1
	 */
	const LINK_QUEUE = 'ivt_import_video_playlist_queue';

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
		// Create new video.
		$this->create_video( $item );

		// Restore old blog.
		General::restore_site();
	}

	/**
	 * Remove existing duplicate.
	 *
	 * This is usually applicable only to default videos.
	 * If there is a video exist with same slug, delete it.
	 *
	 * @param array $video Video data to import.
	 *
	 * @since 1.8.1
	 */
	public function remove_existing( $video ) {
		global $wpdb;

		// Get the existing video if any.
		// phpcs:ignore
		$post_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT p1.ID FROM $wpdb->posts p1 JOIN $wpdb->postmeta p2 ON p1.ID = p2.post_id WHERE p1.post_name = %s AND p1.post_type = %s AND p2.meta_value = 'default'",
				$video['video_slug'],
				Videos\Models\Video::POST_TYPE
			)
		);

		if ( ! empty( $post_id ) ) {
			// Get the video object.
			$video = Videos\Controller::get()->get_video( $post_id );

			// Delete default video.
			if ( ! $video->is_error() && 'default' === $video->video_type ) {
				$video->delete();
			}
		}
	}

	/**
	 * Create a new video item.
	 *
	 * @param array $data Video data.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	public function create_video( $data ) {
		// Get a new video object.
		if ( 'default' === $data['video_type'] ) {
			$video_id = $this->default_video( $data );
		} else {
			$video_id = $this->custom_video( $data );
		}

		// Queue playlists.
		$this->link_playlists( $data, $video_id );

		/**
		 * Action hook to fire after default video is imported.
		 *
		 * @param int   $video_id Video ID.
		 * @param array $data     Video data.
		 *
		 * @since 1.8.1
		 */
		do_action( 'wpmudev_vids_after_import_video', $video_id, $data );
	}

	/**
	 * Create a new default video item.
	 *
	 * @param array $data Video data.
	 *
	 * @since 1.8.1
	 *
	 * @return int|bool
	 */
	public function default_video( $data ) {
		// Get a new video object.
		$video = Videos\Models\Video::get();

		// Setup required properties.
		$video->video_type     = 'default';
		$video->author         = get_current_user_id();
		$video->video_title    = $this->get_value( 'video_title', $data );
		$video->video_slug     = $this->get_value( 'video_slug', $data );
		$video->video_duration = $this->get_value( 'video_duration', $data );

		// Save the video.
		return $video->save();
	}

	/**
	 * Create a new custom video item.
	 *
	 * @param array $data Video data.
	 *
	 * @since 1.8.1
	 *
	 * @return int|bool
	 */
	public function custom_video( $data ) {
		// Get a new video object.
		$video = Videos\Models\Custom_Video::get();

		// Setup required properties.
		$video->video_type       = 'custom';
		$video->author           = get_current_user_id();
		$video->video_title      = $this->get_value( 'video_title', $data );
		$video->video_slug       = $this->get_value( 'video_slug', $data );
		$video->video_duration   = $this->get_value( 'video_duration', $data );
		$video->video_host       = $this->get_value( 'video_host', $data, 'youtube' );
		$video->video_url        = $this->get_value( 'video_url', $data );
		$video->video_start      = $this->get_value( 'video_start', $data, false );
		$video->video_end        = $this->get_value( 'video_end', $data, false );
		$video->video_start_time = $this->get_value( 'video_start_time', $data );
		$video->video_end_time   = $this->get_value( 'video_end_time', $data );

		// Create or update video.
		$video_id = $video->save();

		// Send error response.
		if ( $video->is_error() ) {
			return false;
		}

		// Upload thumb.
		if ( ! empty( $data['import_thumb'] ) && ! empty( $data['thumbnail']['url'] ) ) {
			$thumb_id = $this->upload_thumb(
				$data['thumbnail']['url'],
				$this->get_value( 'video_title', $data ),
				$video_id
			);

			if ( ! empty( $thumb_id ) ) {
				// Get the new video object.
				$video = Videos\Controller::get()->get_video( $video_id );

				// Set the thumbnail.
				$video->set_thumbnail( $thumb_id );
			}
		}

		return $video_id;
	}

	/**
	 * Link the videos to imported playlist.
	 *
	 * Please call this after playlists.
	 *
	 * @param array $data     Video data.
	 * @param int   $video_id Video ID.
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	private function link_playlists( $data, $video_id ) {
		// Get existing queues.
		$playlist_queue = get_transient( Playlist::LINK_QUEUE );

		// Make sure required things are available.
		if ( empty( $data['playlists'] ) || empty( $playlist_queue ) || empty( $video_id ) ) {
			return;
		}

		$playlists = array();

		// Loop through each video.
		foreach ( $data['playlists'] as $playlist_id ) {
			if ( isset( $playlist_queue[ $playlist_id ] ) ) {
				$playlists[] = $playlist_queue[ $playlist_id ];
			}
		}

		// Link playlist and videos.
		Playlists\Controller::get()->link_playlists_to_video( $playlists, $video_id );
	}
}