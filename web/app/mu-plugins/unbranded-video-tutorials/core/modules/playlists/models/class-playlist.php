<?php
/**
 * Playlist model class.
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

use WP_Term;
use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Modules\Videos;
use WPMUDEV_Videos\Core\Abstracts\Model;

/**
 * Class Playlist
 *
 * You need to manually switch the site before utilizing this
 * class. Otherwise you will not get the results as the video
 * data is in main site of multisite.
 *
 * @package WPMUDEV_Videos\Core\Modules\Playlists\Models
 */
class Playlist extends Model {

	/**
	 * The playlist's title.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Playlist description.
	 *
	 * @since 3.5.0
	 *
	 * @var string
	 */
	public $description = '';

	/**
	 * IDs of videos attached to the playlist.
	 *
	 * @since 1.8.0
	 *
	 * @var array
	 */
	public $videos = array();

	/**
	 * The playlist's type.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	public $playlist_type = 'default';

	/**
	 * The playlist's pages to add contexual help.
	 *
	 * @since 1.8.0
	 *
	 * @var array
	 */
	public $playlist_locations = array();

	/**
	 * The roles allowed to access the playlist.
	 *
	 * @since 1.8.0
	 *
	 * @var array
	 */
	public $playlist_roles = array();

	/**
	 * The playlist slug.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	public $slug = '';

	/**
	 * The playlist's thumbnail ID.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	public $playlist_thumbnail = '';

	/**
	 * Playlist updated time.
	 *
	 * @since 1.8.0
	 *
	 * @var string
	 */
	public $playlist_updated_time = '0000-00-00 00:00:00';

	/**
	 * Playlist order.
	 *
	 * Always use the order in descending order.
	 *
	 * @since 1.8.4
	 *
	 * @var int
	 */
	public $playlist_order = 0;

	/**
	 * The playlist's thumbnail data.
	 *
	 * @since 1.8.0
	 *
	 * @var array $thumbnail
	 */
	public $thumbnail = array();

	/**
	 * Playlist taxonomy name.
	 *
	 * @since 1.8.0
	 *
	 * @var string $post_type
	 */
	const TAXONOMY = 'playlist';

	/**
	 * Setup the playlist model object.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	protected function setup() {
		if ( $this->is_valid_playlist() ) {
			// Setup video object.
			$this->setup_playlist();
		} else {
			$this->set_error(
				'invalid_id',
				__( 'Playlist ID is invalid. Please try again with a valid ID.', 'wpmudev_vids' )
			);
		}

		/**
		 * Action to hook to run after playlist object is setup.
		 *
		 * No need to switch the site as we are already switched.
		 *
		 * @param Playlist $this Playlist object.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_video_model_after_playlist_setup', $this );
	}

	/**
	 * Get all properties of playlist as array.
	 *
	 * All class properties should be included.
	 *
	 * @since 1.8.0
	 *
	 * @return array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Save a playlist data to the database.
	 *
	 * All custom fields of the playlist are stored in meta
	 * fields. So, we will be preparing them to include in
	 * the post data to update/create.
	 *
	 * @since 1.8.0
	 *
	 * @return bool|int False on fail and term id on success.
	 */
	public function save() {
		// Playlist ID.
		$this->id = $this->sanitize( $this->id, 'id' );

		// Prepare arguments.
		$args = $this->get_prepared_args();

		if ( empty( $this->id ) ) {
			// Insert term.
			$result = wp_insert_term( $this->title, self::TAXONOMY, $args );
		} else {
			// Update term.
			$result = wp_update_term( $this->id, self::TAXONOMY, $args );
		}

		// Validate result.
		$this->validate_result( $result );

		// Only when success.
		if ( ! is_wp_error( $result ) && isset( $result['term_id'] ) ) {
			// Get meta data to update.
			$meta = $this->get_prepared_meta();

			// Update each meta items.
			foreach ( $meta as $meta_key => $meta_value ) {
				update_term_meta( $result['term_id'], $meta_key, $meta_value );
			}

			// Resync videos.
			foreach ( $this->videos as $video ) {
				$this->add_video( $video, true, $result['term_id'] );
			}

			if ( $this->is_new() ) {
				/**
				 * Action to hook to run after a new playlist is created.
				 *
				 * @param Playlist $this Current playlist model.
				 *
				 * @since 1.8.0
				 */
				do_action( 'wpmudev_vids_playlist_model_after_playlist_create', $this );
			} else {
				/**
				 * Action to hook to run after playlist is updated.
				 *
				 * @param Playlist $this Current playlist model.
				 *
				 * @since 1.8.0
				 */
				do_action( 'wpmudev_vids_playlist_model_after_playlist_update', $this );
			}

			// Delete cache.
			$this->refresh_cache();

			return $result['term_id'];
		}

		return false;
	}

	/**
	 * Delete a playlist term.
	 *
	 * All the meta attached to the playlist will also be deleted.
	 * Attached videos will be unlinked.
	 *
	 * @since 1.8.0
	 * @uses  wp_delete_term
	 *
	 * @return bool|\WP_Error
	 */
	public function delete() {
		$deleted = false;

		if ( $this->is_existing() ) {
			// Force delete.
			$deleted = wp_delete_term( $this->id, self::TAXONOMY );

			if ( $deleted ) {
				/**
				 * Action hook to fire after playlist is deleted.
				 *
				 * @param bool|int $deleted Success?.
				 * @param int      $id      Video ID.
				 *
				 * @since 1.7
				 */
				do_action( 'wpmudev_vids_playlist_model_after_playlist_deleted', $deleted, $this );

				// Delete cache.
				$this->refresh_cache();
			}
		}

		return $deleted;
	}

	/**
	 * Add playlist(s) to the current video.
	 *
	 * Multiple playlist can be set to a video at a time.
	 *
	 * @param int  $video         Video ID.
	 * @param bool $keep_existing Should keep existing videos.
	 * @param int  $term          Term ID (Only needed when playlist is new).
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function add_video( $video, $keep_existing = true, $term = 0 ) {
		$success = false;

		// Get the term ID.
		$playlist = empty( $term ) ? $this->id : $term;

		// Link playlist terms.
		$id = wp_set_object_terms(
			(int) $video,
			$playlist,
			self::TAXONOMY,
			$keep_existing
		);

		if ( ! empty( $id ) ) {
			$success = true;

			/**
			 * Action hook to video is set to a playlist.
			 *
			 * @param int|array $id    Playlist ID(s).
			 * @param int       $video Video ID.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpmudev_vids_playlist_model_after_video_playlist_set', $id, $video );

			// Delete cache.
			$this->refresh_cache();
			$this->refresh_video_cache( $video );
		}

		return $success;
	}

	/**
	 * Remove playlist(s) from the current video.
	 *
	 * Provide playlist slug or ID.
	 *
	 * @param int $video Video ID.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function remove_video( $video ) {
		$success = wp_remove_object_terms(
			(int) $video,
			$this->id,
			self::TAXONOMY
		);

		if ( $success ) {
			/**
			 * Action hook to fire after a video is removed from a playlist.
			 *
			 * @param int $id    Playlist ID.
			 * @param int $video Video ID.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpmudev_vids_playlist_model_after_playlist_video_remove', $this->id, $video );

			// Delete cache.
			$this->refresh_cache();
			$this->refresh_video_cache( $video );
		}

		return $success;
	}

	/**
	 * Get corresponding taxonomy term object of the playlist.
	 *
	 * This will be empty if the id given is wrong.
	 *
	 * @since 1.8.0
	 *
	 * @return \WP_Error|WP_Term
	 */
	public function get_term() {
		// New object will not have a term.
		if ( ! empty( $this->id ) ) {
			// Get the term object.
			$term = get_term( $this->id, self::TAXONOMY );

			// Set in cache.
			if ( is_wp_error( $term ) || empty( $term ) ) {
				$term = false;
			}
		} else {
			$term = false;
		}

		/**
		 * Filter the term object of playlist.
		 *
		 * @param WP_Term $term Term object.
		 * @param int     $id   Playlist ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_get_term', $term, $this->id );
	}

	/**
	 * Prepare term arguments to pass to WP.
	 *
	 * We need to make sure only allowed items are updated or
	 * set during term update/create.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	private function get_prepared_args() {
		// Default post fields.
		$args = array(
			'slug'        => $this->sanitize( $this->slug ),
			'description' => $this->sanitize( $this->description ),
		);

		// For existing ones, can update title also.
		if ( ! empty( $this->id ) ) {
			$args['name'] = $this->sanitize( $this->title );
		}

		/**
		 * Filter hook to alter the post data.
		 *
		 * @param array $post Post data.
		 * @param int   $id   Post ID.
		 * @param array $data Custom data.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_prepare_args', $args, $this->id );
	}

	/**
	 * Prepare video meta data in required format to pass to WP.
	 *
	 * Merge all meta values together so we can update the meta
	 * data within the video post update function.
	 * Make sure the meta fields are set in the
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	protected function get_prepared_meta() {
		$meta = array();

		// Replace updated items.
		foreach ( $this->get_meta_properties() as $property ) {
			if ( isset( $this->$property ) ) {
				$meta[ $property ] = $this->sanitize( $this->$property, $property );
			} else {
				$meta[ $property ] = '';
			}
		}

		// Always set the updated time.
		$meta['playlist_updated_time'] = current_time( 'mysql' );

		/**
		 * Filter hook to alter the video meta before updating in db.
		 *
		 * @param array $meta Video meta.
		 * @param int   $id   Video ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_prepare_video_meta', $meta, $this->id );
	}

	/**
	 * Setup thumbnail data of the playlist.
	 *
	 * If a thumbnail is set for the playlist, get the url, file name
	 * and id of the thumbnail attachment.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private function setup_thumbnail() {
		$thumbnail = array();

		if ( $this->is_existing() ) {
			// Get video meta data.
			$thumbnail_id = get_term_meta( $this->id, 'playlist_thumbnail', true );

			// Only when thumbnail data is found.
			if ( ! empty( $thumbnail_id ) ) {
				// Make sure the ID is int.
				$thumbnail_id = (int) $thumbnail_id;

				// Attachment file.
				$attachment = get_attached_file( $thumbnail_id );

				// Attachment data.
				$thumbnail = array(
					'id'   => $thumbnail_id,
					'file' => wp_basename( $attachment ),
					'url'  => wp_get_attachment_image_url( $thumbnail_id, 'full' ),
				);

				$this->playlist_thumbnail = $thumbnail_id;
			}

			/**
			 * Filter to modify playlist thumbnail data.
			 *
			 * @param array $thumbnail Thumbnail data.
			 * @param int   $id        Playlist ID.
			 *
			 * @since 1.8.0
			 */
			$this->thumbnail = apply_filters( 'wpmudev_vids_playlist_model_set_thumbnail', $thumbnail, $this->id );
		}
	}

	/**
	 * Setup extra fields property for the custom video.
	 *
	 * All custom video data is stored in one meta. We need
	 * to set each items as the video object property.
	 *
	 * @since 1.8.0
	 */
	private function setup_properties() {
		// Include custom fields.
		if ( $this->is_existing() ) {
			// Get all meta.
			$meta = get_term_meta( $this->id );

			foreach ( $this->get_meta_properties() as $property ) {
				if ( isset( $meta[ $property ][0] ) ) {
					$this->$property = maybe_unserialize( $meta[ $property ][0] );
				}
			}

			// Setup thumbnail.
			$this->setup_thumbnail();
			// Setup videos.
			$this->setup_videos();
		}

		/**
		 * Action hook to execute after custom video properties are set.
		 *
		 * @param mixed  $value Field value.
		 * @param string $key   Field key.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_video_model_after_setup_properties' );
	}

	/**
	 * Setup playlist object from term data.
	 *
	 * Custom fields are not set here.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private function setup_playlist() {
		// Get the term.
		if ( $this->is_existing() ) {
			$term = $this->get_term();

			if ( ! empty( $term ) ) {
				$this->id          = $term->term_id;
				$this->title       = $term->name;
				$this->slug        = $term->slug;
				$this->description = $term->description;
			}

			// Setup custom properties.
			$this->setup_properties();
		}
	}

	/**
	 * Setup playlist object from term data.
	 *
	 * Custom fields are not set here.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private function setup_videos() {
		// Get the term.
		if ( $this->is_existing() ) {
			$this->videos = Videos\Controller::get()->get_playlist_videos( $this->id );
		}
	}

	/**
	 * Check if current model is valid.
	 *
	 * Only video type posts should be valid.
	 *
	 * @since 1.8.0
	 * @uses  wp_get_post_terms
	 *
	 * @return bool
	 */
	private function is_valid_playlist() {
		$valid = true;

		// Should be a video post type.
		if ( $this->is_existing() ) {
			// Get term.
			$term = $this->get_term();

			// Should be valid only if taxonomy is valid.
			$valid = isset( $term->taxonomy ) && self::TAXONOMY === $term->taxonomy;
		}

		/**
		 * Filter to alter the validation check of current model.
		 *
		 * @param array $valid Is valid.
		 * @param int   $id    Playlist ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_is_valid_playlist', $valid, $this->id );
	}

	/**
	 * Sanitize the playlist data values.
	 *
	 * By default all values will be sanitized for text input
	 * unless if specified.
	 *
	 * @param mixed  $value Meta data.
	 * @param string $key   Field key.
	 *
	 * @since 1.8.0
	 *
	 * @return mixed
	 */
	public function sanitize( $value, $key = '' ) {
		// Don't miss the parent sanitization.
		$final_value = parent::sanitize( $value, $key );

		switch ( $key ) {
			case 'playlist_type':
				$final_value = in_array( $value, array( 'custom', 'default' ), true ) ? $value : 'custom';
				break;
			case 'playlist_thumbnail':
				$final_value = wp_attachment_is_image( $value ) ? (int) $value : '';
				break;
			case 'playlist_locations':
			case 'playlist_roles':
				$final_value = (array) $value;
				break;
			case 'playlist_order':
				$final_value = (int) $value;
				break;
		}

		/**
		 * Filter hook to perform additional sanitization.
		 *
		 * @param mixed  $final_value Field value.
		 * @param string $key         Field key.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_sanitize', $final_value, $key );
	}

	/**
	 * Get custom properties available in playlist.
	 *
	 * @since 1.8.0
	 *
	 * @return mixed
	 */
	private function get_meta_properties() {
		// Custom properties as meta.
		$properties = array(
			'playlist_thumbnail',
			'playlist_type',
			'playlist_locations',
			'playlist_roles',
			'playlist_updated_time',
			'playlist_order',
		);

		/**
		 * Filter to modify list of available custom properties.
		 *
		 * @param mixed  $value Field value.
		 * @param string $key   Field key.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_playlist_model_meta_properties', $properties );
	}

	/**
	 * Clear the current video object cache.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	protected function refresh_cache() {
		// Clear the whole cache, otherwise calculation may break.
		Helpers\Cache::refresh_cache();
	}

	/**
	 * Clear the a video object cache.
	 *
	 * @param int $video Video ID.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private function refresh_video_cache( $video ) {
		// Get from cache first.
		if ( $video > 0 ) {
			// Delete the cache.
			Helpers\Cache::delete_cache( 'video', array( 'video' => $video ) );
		}
	}
}