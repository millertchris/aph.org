<?php
/**
 * Video modal class for the videos module.
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

use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Model;
use WPMUDEV_Videos\Core\Modules\Playlists;
use WPMUDEV_Videos\Core\Modules\Videos\Helper;

/**
 * Class Video
 *
 * You need to manually switch the site before utilizing this
 * class. Otherwise you will not get the results as the video
 * data is in main site of multisite.
 *
 * @package WPMUDEV_Videos\Core\Modules\Videos\Models
 */
class Video extends Model {

	/**
	 * Video ID.
	 *
	 * @since 1.8.0
	 * @var int
	 */
	public $id;

	/**
	 * The video's title.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $video_title = '';

	/**
	 * The video's slug/name.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $video_slug = '';

	/**
	 * The video's type.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $video_type = 'default';

	/**
	 * The videos' total length.
	 *
	 * This may not be available for few custom videos.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $video_duration = '';

	/**
	 * ID of video author.
	 *
	 * A numeric string, for compatibility reasons.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	public $author = 0;

	/**
	 * Playlist IDs of the video.
	 *
	 * @since 1.8.0
	 *
	 * @var array $playlists
	 */
	public $playlists = array();

	/**
	 * The link to view the video in videos page.
	 *
	 * @since 1.8.0
	 *
	 * @var string $view_link
	 */
	public $view_link = '';

	/**
	 * The video's local publication time.
	 *
	 * @since 1.8.0
	 * @var string
	 */
	public $date = '0000-00-00 00:00:00';

	/**
	 * Custom post type name.
	 *
	 * @since 1.7
	 *
	 * @var string $post_type
	 */
	const POST_TYPE = 'wpmudev_custom_video';

	/**
	 * Publish status string.
	 *
	 * @since 1.8.0
	 */
	const PUBLISH_STATUS = 'publish';

	/**
	 * Setup the video model object.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return void
	 */
	protected function setup() {
		if ( $this->is_valid_video() ) {
			// Setup video object.
			$this->setup_video();
			// Setup video type.
			$this->setup_custom_properties();
			// Setup playlist.
			$this->setup_playlists();
			// Setup links.
			$this->setup_links();
		} else {
			$this->set_error(
				'invalid_id',
				__( 'Video ID is invalid. Please try again with a valid ID.', 'wpmudev_vids' )
			);
		}

		/**
		 * Action to hook to run after video object is setup.
		 *
		 * @param Video $this Video object.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_video_model_after_video_setup', $this );
	}

	/**
	 * Get all properties of video as array.
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
	 * Save a video data to the database.
	 *
	 * All custom fields of the video are stored in meta
	 * fields. So, we will be preparing them to include in
	 * the post data to update/create.
	 *
	 * @since 1.8.0
	 *
	 * @return bool|int
	 */
	public function save() {
		// Setup data in required format.
		$post = $this->get_prepared_data();

		// Validate data before saving it.
		$this->validate();

		// Validation error.
		if ( $this->is_error() ) {
			return false;
		}

		// Insert or update video post.
		$video = empty( $this->id ) ? wp_insert_post( $post ) : wp_update_post( $post );

		// Validate result.
		$this->validate_result( $video );

		// Execute post video update action.
		if ( ! $this->is_error() ) {
			if ( isset( $post['ID'] ) ) {
				/**
				 * Action to hook to run after a new video is created.
				 *
				 * @param int   $video Video ID.
				 * @param array $data  Original data.
				 * @param array $post  Prepared post data.
				 *
				 * @since 1.8.0
				 */
				do_action( 'wpmudev_vids_video_model_after_video_create', $video, $post );
			} else {
				// Set last updated video cache.
				Helpers\Cache::set_cache( 'last_updated_video', $this );

				/**
				 * Action to hook to run after video is updated.
				 *
				 * @param int   $video Video ID.
				 * @param array $data  Original data.
				 * @param array $post  Prepared post data.
				 *
				 * @since 1.8.0
				 */
				do_action( 'wpmudev_vids_videos_model_after_video_update', $video, $post );
			}

			// Delete cache.
			$this->refresh_cache();

			return $video;
		}

		return false;
	}

	/**
	 * Delete a custom video post.
	 *
	 * Force delete the video post so that the meta
	 * and other data will be cleared.
	 *
	 * @param bool $force Should force delete?.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function delete( $force = true ) {
		// Force delete.
		$video = wp_delete_post( $this->id, $force );

		if ( $video ) {
			/**
			 * Action to hook to run after video is deleted.
			 *
			 * @param int $id Video ID.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpmudev_vids_video_model_after_video_delete', $this->id );

			// Delete cache.
			$this->refresh_cache();
		}

		return $video ? true : false;
	}

	/**
	 * Add playlist(s) to the current video.
	 *
	 * Multiple playlist can be set to a video at a time.
	 *
	 * @param array|int $playlist Playlist(s) ID.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function add_playlist( $playlist ) {
		// Set playlist(s).
		$ids = wp_set_object_terms(
			$this->id,
			(array) $playlist,
			Playlists\Models\Playlist::TAXONOMY,
			true
		);

		// Check if the operation was success.
		$success = ! is_wp_error( $ids );

		if ( $success ) {
			// Update the playlist property.
			$this->setup_playlists();

			/**
			 * Action hook to fire after playlists are added to a video.
			 *
			 * @param int $playlists Playlist ID.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpmudev_vids_video_model_after_video_playlist_add', $this->playlists );

			// Delete cache.
			$this->refresh_cache();
			$this->refresh_playlist_cache( $playlist );

			// Setup links.
			$this->setup_links();
		}

		return $success;
	}

	/**
	 * Remove playlist(s) from the current video.
	 *
	 * Provide playlist slug or ID.
	 *
	 * @param array|int|string $playlist Playlist(s) ID or slug (can be array or single).
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function remove_playlist( $playlist ) {
		// Remove playlist(s).
		$success = wp_remove_object_terms(
			$this->id,
			$playlist,
			Playlists\Models\Playlist::TAXONOMY
		);

		if ( $success ) {
			// Update the playlist property.
			$this->setup_playlists();

			/**
			 * Action hook to fire after playlists are removed from a video.
			 *
			 * @param int|string|array $playlists Playlist ID or slug.
			 *
			 * @since 1.8.0
			 */
			do_action( 'wpmudev_vids_video_model_after_video_playlist_remove', $this->playlists );

			// Delete cache.
			$this->refresh_cache();
			$this->refresh_playlist_cache( $playlist );

			// Setup links.
			$this->setup_links();
		}

		return $success;
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

		/**
		 * Filter hook to alter the video meta before updating in db.
		 *
		 * @param array $meta Video meta.
		 * @param Video $this Video object.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_videos_model_prepare_video_meta', $meta, $this );
	}

	/**
	 * Get custom properties available in video.
	 *
	 * @since 1.8.0
	 *
	 * @return mixed
	 */
	protected function get_meta_properties() {
		// Custom properties as meta.
		$properties = array(
			'video_type',
			'video_duration',
		);

		/**
		 * Filter to modify list of available custom properties.
		 *
		 * @param mixed  $value Field value.
		 * @param string $key   Field key.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_video_model_meta_properties', $properties );
	}

	/**
	 * Prepare post data in required format to pass to WP.
	 *
	 * We need to include the required meta data into post array
	 * so WP will update the meta along with post data update.
	 * We will sanitize all fields before giving it to WP.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	private function get_prepared_data() {
		// Video ID.
		$this->id = $this->sanitize( $this->id, 'id' );

		// Default post fields.
		$video = array(
			'ID'          => $this->id,
			'post_status' => self::PUBLISH_STATUS,
			'post_title'  => $this->sanitize( $this->video_title ),
			'post_type'   => self::POST_TYPE,
			'post_name'   => $this->sanitize( $this->video_slug ),
			'post_author' => empty( $this->author ) ? get_current_user_id() : $this->author,
			'meta_input'  => $this->get_prepared_meta(),
		);

		/**
		 * Filter hook to alter the post data.
		 *
		 * @param array $video Video post data.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_video_model_prepare_video_data', $video );
	}

	/**
	 * Setup video object from post data.
	 *
	 * Custom fields are not set here.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private function setup_video() {
		// Get the video post.
		$video = $this->id > 0 ? get_post( $this->id ) : array();

		// Return early if invalid video.
		if ( empty( $video ) ) {
			return;
		}

		// Set post fields.
		$this->id          = $video->ID;
		$this->date        = $video->post_date;
		$this->author      = (int) $video->post_author;
		$this->video_slug  = $video->post_name;
		$this->video_title = $video->post_title;
	}

	/**
	 * Setup video type of the current model.
	 *
	 * Check the video meta for the video type.
	 *
	 * @since 1.8.0
	 * @uses  get_post_meta
	 *
	 * @return void
	 */
	private function setup_custom_properties() {
		if ( $this->id > 0 ) {
			// Get video type.
			$type = get_post_meta( $this->id, 'video_type', true );
			// Get video duration.
			$duration = get_post_meta( $this->id, 'video_duration', true );

			if ( ! empty( $type ) ) {
				$this->video_type = $type;
			}

			if ( ! empty( $duration ) ) {
				$this->video_duration = Helper::seconds_to_time( $duration );
			}
		}
	}

	/**
	 * Setup playlists of a video.
	 *
	 * Playlists are the taxonomy terms. So set the
	 * term ids to the video object.
	 *
	 * @since 1.8.0
	 * @uses  wp_get_post_terms
	 *
	 * @return void
	 */
	private function setup_playlists() {
		if ( $this->id > 0 ) {
			$playlists = wp_get_post_terms(
				$this->id,
				Playlists\Models\Playlist::TAXONOMY,
				array( 'fields' => 'ids' )
			);

			/**
			 * Filter to alter the list of playlist ids of a video.
			 *
			 * @param array $playlists Playlist IDs.
			 * @param int   $id        Video ID.
			 *
			 * @since 1.8.0
			 */
			$this->playlists = apply_filters( 'wpmudev_vids_video_model_setup_playlists', $playlists, $this->id );
		}
	}

	/**
	 * Setup view/edit links of the video.
	 *
	 * @since 1.8.0
	 * @uses  wp_get_post_terms
	 *
	 * @return void
	 */
	private function setup_links() {
		if ( $this->id > 0 && ! empty( $this->playlists ) ) {
			// Get the first playlist id.
			$playlist = empty( $this->playlists[0] ) ? 0 : $this->playlists[0];

			// View link.
			$view_link = Helpers\General::url( 'tutorials' ) . '#/view/' . $playlist . '/' . $this->id;

			/**
			 * Filter to alter the video view link.
			 *
			 * @param string $view_link View link.
			 * @param int    $id        Video ID.
			 *
			 * @since 1.8.0
			 */
			$this->view_link = apply_filters( 'wpmudev_vids_video_model_view_link', $view_link, $this->id );
		}
	}

	/**
	 * Check if current model is valid.
	 *
	 * Only video type posts should be valid.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	private function is_valid_video() {
		$valid = true;

		// Should be a video post type.
		if ( $this->id > 0 && self::POST_TYPE !== get_post_type( $this->id ) ) {
			$valid = false;
		}

		/**
		 * Filter to alter the validation check of current model.
		 *
		 * @param array $valid Is valid.
		 * @param int   $id    Video ID.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_video_model_is_valid_video', $valid, $this->id );
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
	 * Clear the a playlist object cache.
	 *
	 * @param int $playlist Playlist ID.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private function refresh_playlist_cache( $playlist ) {
		// Get from cache first.
		if ( $playlist > 0 ) {
			// Delete the cache.
			Helpers\Cache::delete_cache( 'playlist', array( 'playlist' => $playlist ) );
		}
	}

	/**
	 * Handle validation of the video form.
	 *
	 * By default no validation is required. Make sure to
	 * override this in extending class to validate custom
	 * fields.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	protected function validate() {
		/**
		 * Action hook to run after validation.
		 *
		 * @param Video|Custom_Video $this Video object.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_validate_form', $this );
	}
}