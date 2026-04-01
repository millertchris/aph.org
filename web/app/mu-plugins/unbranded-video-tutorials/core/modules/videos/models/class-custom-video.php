<?php
/**
 * Custom video modal class.
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

use WPMUDEV_Videos\Core\Modules\Videos;

/**
 * Class Custom_Video
 *
 * You need to manually switch the site before utilizing this
 * class. Otherwise you will not get the results as the video
 * data is in main site of multisite.
 *
 * @package WPMUDEV_Videos\Core\Modules\Videos\Models
 */
class Custom_Video extends Video {

	/**
	 * The video's type.
	 *
	 * @var string
	 * @since 1.8.0
	 */
	public $video_type = 'custom';

	/**
	 * Video host.
	 *
	 * @var string
	 *
	 * @since 1.8.0
	 */
	public $video_host = '';

	/**
	 * The video url.
	 *
	 * @var string
	 *
	 * @since 1.8.0
	 */
	public $video_url = '';

	/**
	 * Flag to enable video start time.
	 *
	 * @var int
	 *
	 * @since 1.8.0
	 */
	public $video_start = 0;

	/**
	 * Flag to enable video end time.
	 *
	 * @var int
	 *
	 * @since 1.8.0
	 */
	public $video_end = 0;

	/**
	 * The video start time (if applicable).
	 *
	 * @var int
	 *
	 * @since 1.8.0
	 */
	public $video_start_time = '';

	/**
	 * The video end time (if applicable).
	 *
	 * @var int
	 *
	 * @since 1.8.0
	 */
	public $video_end_time = '';

	/**
	 * Custom thumbnail data.
	 *
	 * @var array $thumbnail
	 *
	 * @since 1.8.0
	 */
	public $thumbnail = array();

	/**
	 * Setup the custom video model object.
	 *
	 * If current video is not custom video, set the error object.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	protected function setup() {
		// Setup basic things.
		parent::setup();

		// Setup custom fields.
		if ( 'custom' === $this->video_type ) {
			$this->setup_properties();
		} else {
			$this->validate_result(
				new \WP_Error( 202, __( 'Trying to use custom video object on a non-custom video.', 'wpmudev_vids' ) )
			);
		}
	}

	/**
	 * Set custom thumbnail for a video.
	 *
	 * We don't check if it's a custom video because,
	 * even if we set thumbnail for default video, it won't
	 * be used at all.
	 *
	 * @param int $thumb Thumbnail ID.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function set_thumbnail( $thumb ) {
		// Set video post thumbnail.
		$success = set_post_thumbnail( $this->id, $thumb );

		// Re-init the thumbnail.
		if ( $success ) {
			$this->setup_thumbnail();
		}

		/**
		 * Action hook to fire after video thumbnail is updated.
		 *
		 * @param int $thumb Thumbnail ID.
		 * @param int $id    Video ID.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_custom_video_model_after_thumbnail_update', $thumb, $this->id );

		// Delete cache.
		$this->refresh_cache();

		return $success;
	}

	/**
	 * Delete the custom thumbnail from a video.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function delete_thumbnail() {
		// Remove the thumbnail.
		$success = delete_post_thumbnail( $this->id );

		// Delete the thumbnail.
		if ( $success ) {
			$this->thumbnail = array();
		}

		/**
		 * Action hook to fire after video thumbnail is deleted.
		 *
		 * @param int $id Video ID.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_custom_videos_model_after_thumbnail_delete', $this->id );

		// Delete cache.
		$this->refresh_cache();

		return $success;
	}

	/**
	 * Get custom properties available in video.
	 *
	 * @param bool $parent_value Should include meta?.
	 *
	 * @since 1.8.0
	 *
	 * @return array
	 */
	protected function get_meta_properties( $parent_value = true ) {
		// Custom properties as meta.
		$properties = array(
			'video_host',
			'video_url',
			'video_start',
			'video_end',
			'video_start_time',
			'video_end_time',
		);

		// Merge properties.
		if ( $parent_value ) {
			$properties = array_merge( $properties, parent::get_meta_properties() );
		}

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
	 * Setup extra fields property for the custom video.
	 *
	 * All custom video data is stored in one meta. We need
	 * to set each items as the video object property.
	 *
	 * @since 1.8.0
	 */
	private function setup_properties() {
		if ( $this->is_existing() ) {
			foreach ( $this->get_meta_properties( false ) as $property ) {
				$meta = get_post_meta( $this->id, $property, true );
				if ( ! empty( $meta ) ) {
					$this->$property = $meta;
				}
			}

			// Setup thumbnail.
			$this->setup_thumbnail();
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
	 * Setup custom attachment data for the video.
	 *
	 * If a custom thumbnail is set for the video, the image
	 * will be set as post thumbnail. Get those details.
	 * Note: Default video type will not have custom thumbnail.
	 *
	 * @since 1.7.0
	 * @since 1.8.0 Renamed and moved to this class.
	 *
	 * @return void
	 */
	private function setup_thumbnail() {
		// Thumbnail ID.
		$thumbnail_id = get_post_thumbnail_id( $this->id );

		// Only when thumbnail data is found.
		if ( ! empty( $thumbnail_id ) ) {
			// Attachment file.
			$attachment = get_attached_file( $thumbnail_id );

			// Attachment data.
			$this->thumbnail = array(
				'id'   => $thumbnail_id,
				'file' => wp_basename( $attachment ),
				'url'  => wp_get_attachment_image_url( $thumbnail_id, 'full' ),
			);
		}
	}

	/**
	 * Handle validation of the video form.
	 *
	 * Make sure URL is valid and provider exist using oembed
	 * API. Also make sure the thumbnail and playlists are valid.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	protected function validate() {
		// Invalid embed url or host.
		if ( ! Videos\Embed::get()->is_valid( $this->video_url, $this->video_host ) ) {
			$this->set_error(
				'invalid_url',
				__( 'The URL you have attached is invalid. Try again by copying the URL from your browser and placing below.', 'wpmudev_vids' )
			);
		}

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