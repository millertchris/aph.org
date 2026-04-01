<?php
/**
 * Helper class to handle the import tasks.
 *
 * Extend this class if you need the helper methods.
 *
 * @link    https://wpmudev.com
 * @since   1.8.1
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */

namespace WPMUDEV_Videos\Core\Tasks\Import;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Helper
 *
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */
class Helper extends Base {

	/**
	 * Get the value of a field.
	 *
	 * @param string $name          Name of the field.
	 * @param array  $data          Data.
	 * @param mixed  $default_value Default value.
	 *
	 * @since 1.8.1
	 *
	 * @return mixed
	 */
	public function get_value( $name, $data = array(), $default_value = '' ) {
		return isset( $data[ $name ] ) ? $data[ $name ] : $default_value;
	}

	/**
	 * Upload the custom thumbnail for the video or playlist.
	 *
	 * If custom video thumbnail is found, try to download
	 * it and upload it.
	 *
	 * @param string $url     URL of the image.
	 * @param string $title   Title (optional).
	 * @param int    $post_id Post ID (optional).
	 *
	 * @since 1.8.1
	 *
	 * @return int|bool Attachment ID or false.
	 */
	protected function upload_thumb( $url, $title = '', $post_id = 0 ) {
		// Only if thumbnail url is set.
		if ( empty( $url ) ) {
			return false;
		}

		// Make sure the required function is there.
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		// Upload the image from url.
		$id = media_sideload_image( $url, $post_id, $title, 'id' );

		// Get the attachment ID.
		$id = is_wp_error( $id ) || empty( $id ) ? false : $id;

		/**
		 * Filter to modify attachment ID after importing.
		 *
		 * @param int|bool $id      Attachment ID.
		 * @param string   $url     URL of the image.
		 * @param string   $title   Title.
		 * @param int      $post_id Post ID.
		 *
		 * @since 1.8.1
		 */
		return apply_filters( 'wpmudev_vids_import_uploaded_thumb', $id, $url, $title, $post_id );
	}
}