<?php
/**
 * Video helper functionality class.
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

use DateTime;
use WPMUDEV_Videos\Core\Helpers\Data;
use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Helper
 *
 * @package WPMUDEV_Videos\Core\Modules\Videos
 */
class Helper extends Base {

	/**
	 * Get list of registered video pages.
	 *
	 * The custom pages, added using wpmudev_vids_pages filter will
	 * also be there in this list.
	 *
	 * @since 1.7
	 *
	 * @return array $videos Registered videos.
	 */
	public static function videos_pages() {
		$pages = array(
			'dashboard'          => array(
				'dashboard',
				'admin-bar',
				'quickpress',
				'change-password',
			),
			'post'               => array(
				'add-new-post',
				'the-toolbar',
				'edit-text',
				'add-paragraph',
				'add-heading',
				'hyperlinks',
				'lists',
				'oEmbed',
				'playlists',
				'excerpt',
				'add-image-from-pc',
				'add-image-from-media-library',
				'add-image-from-url',
				'image-gallery',
				'edit-image',
				'replace-image',
				'delete-image',
				'image-editor',
				'featured-image',
				'revisions',
			),
			'edit-post'          => array(
				'gutenberg-add-post',
				'trash-post',
				'restore-post',
				'pages-v-posts',
			),
			'page'               => array(
				'add-new-page',
				'the-toolbar',
				'edit-text',
				'add-paragraph',
				'add-heading',
				'hyperlinks',
				'lists',
				'oEmbed',
				'playlists',
				'add-image-from-pc',
				'add-image-from-media-library',
				'add-image-from-url',
				'image-gallery',
				'edit-image',
				'replace-image',
				'delete-image',
				'image-editor',
				'revisions',
			),
			'edit-page'          => array(
				'gutenberg-add-page',
				'trash-post',
				'restore-page',
				'pages-v-posts',
			),
			'gutenberg-editor'   => array(
				'gutenberg-editor-overview',
				'gutenberg-reusable-blocks',
			),
			'widgets'            => array( 'widgets' ),
			'nav-menus'          => array( 'menus' ),
			'themes'             => array( 'change-theme', 'customize' ),
			'edit-post_tag'      => array( 'tags' ),
			'edit-category'      => array( 'categories' ),
			'upload'             => array( 'media-library', 'image-editor' ),
			'media'              => array( 'add-media' ),
			'edit-comments'      => array( 'comments' ),
			'users'              => array( 'create-edit-user' ),
			'user'               => array(
				'create-edit-user',
				'change-password',
			),
			'profile'            => array(
				'create-edit-user',
				'change-password',
			),
			'user-edit'          => array( 'create-edit-user' ),
			'tools'              => array( 'tools' ),
			'import'             => array( 'tools' ),
			'export'             => array( 'tools' ),
			'options-general'    => array( 'settings' ),
			'options-writing'    => array( 'settings' ),
			'options-reading'    => array( 'settings' ),
			'options-discussion' => array( 'settings' ),
			'options-media'      => array( 'settings' ),
			'options-permalink'  => array( 'settings' ),
			'update-core'        => array( 'running-updates' ),
			'plugin-install'     => array( 'install-plugin' ),
			'theme-install'      => array( 'install-themes' ),
		);

		// If Classic editor is installed show old add new page/post videos.
		if ( class_exists( '\Classic_Editor' ) ) {
			$pages['edit-post'] = array( 'add-new-post', 'trash-post', 'restore-post', 'pages-v-posts' );
			$pages['edit-page'] = array( 'add-new-page', 'trash-post', 'restore-page', 'pages-v-posts' );
		}

		/**
		 * Filter to add/remove pages from videos.
		 *
		 * @param array $pages Registered video pages.
		 *
		 * @since 1.7
		 */
		return apply_filters( 'wpmudev_vids_pages', $pages );
	}

	/**
	 * Get the host display name using key.
	 *
	 * @param string $host Video host.
	 * @param mixed  $name Default name.
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public function host_name( $host = 'youtube', $name = '' ) {
		// Get available hosts.
		$hosts = Data::custom_hosts();

		if ( isset( $hosts[ $host ]['name'] ) ) {
			$name = $hosts[ $host ]['name'];
		}

		/**
		 * Filter to modify host name.
		 *
		 * @param string $name Host name.
		 * @param string $host Host key.
		 *
		 * @since 1.7
		 */
		return apply_filters( 'wpmudev_vids_custom_host_name', $name, $host );
	}

	/**
	 * Convert time string from settings to seconds.
	 *
	 * @param string $time Time in hh:mm:ss format.
	 *
	 * @since 1.7
	 *
	 * @return bool|float|int
	 */
	public static function time_to_seconds( $time ) {
		if ( empty( $time ) ) {
			return false;
		}
		$seconds = 0;
		$time    = str_replace( '.', ':', $time );
		$pattern = '/^([0-9]{1,3})(:([0-9]{1,3})(:([0-9]{1,3}))?)?$/';
		if ( preg_match( $pattern, $time ) ) {
			// Replace dots with colon.
			$time = array_reverse( explode( ':', $time ) );

			foreach ( $time as $key => $value ) {
				if ( $key > 2 ) {
					break;
				}
				$seconds += pow( 60, $key ) * $value;
			}
		}

		/**
		 * Filter hook to modify the time to seconds string.
		 *
		 * @param int $seconds Duration seconds.
		 * @param string $time Time formatted string.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_time_to_seconds', $seconds, $time );
	}

	/**
	 * Convert seconds to time format.
	 *
	 * @param int|string $seconds Convert seconds to hh:mm:ss format.
	 *
	 * @since 1.8.0
	 *
	 * @return int|string
	 */
	public static function seconds_to_time( $seconds ) {
		if ( empty( $seconds ) || ! is_numeric( $seconds ) ) {
			return 0;
		}

		try {
			// Convert to time format.
			$time = gmdate( 'H:i:s', intval( $seconds ) );
		} catch ( \Exception $e ) {
			$time = 0;
		}

		/**
		 * Filter hook to modify src url after setting time.
		 *
		 * @param string $time    Time formatted string.
		 * @param int    $seconds Duration seconds.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_seconds_to_time', $time, $seconds );
	}
}