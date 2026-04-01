<?php
/**
 * The Data class for the videos.
 *
 * @link    https://wpmudev.com
 * @since   1.7.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Helpers
 */

namespace WPMUDEV_Videos\Core\Helpers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class Data
 *
 * @package WPMUDEV_Videos\Core\Helpers
 */
class Data {

	/**
	 * Get the roles that are currently available.
	 *
	 * Roles are taken from wp_roles(). So any custom roles registered
	 * with WP will also included.
	 *
	 * @param bool $include_admin Should include admin.
	 *
	 * @since 1.8.0
	 *
	 * @return array $roles Roles array.
	 */
	public static function get_roles( $include_admin = true ) {
		// Get all available roles.
		$roles = wp_roles()->get_names();

		// Admins can manage the settings, so he should have all access.
		if ( ! $include_admin ) {
			unset( $roles['administrator'] );
		}

		/**
		 * Filter hook to add/remove roles to settings.
		 *
		 * @param array $roles Roles.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_get_roles', $roles );
	}

	/**
	 * Get list of custom video hosts supported.
	 *
	 * New hosts can be added using wpmudev_vids_custom_hosts filter and
	 * then you need to handle the embed output using wpmudev_vids_embed_html.
	 * YouTube and Vimeo are already registered, so we don't need all those properties.
	 *
	 * @since 1.7
	 *
	 * @return array $hosts Registered hosts.
	 */
	public static function custom_hosts() {
		$hosts = array(
			'youtube' => array(
				'name'     => __( 'YouTube', 'wpmudev_vids' ),
				'provider' => 'https://www.youtube.com/oembed',
				'icon'     => 'sui-icon-social-youtube',
				'end_time' => true,
			),
			'vimeo'   => array(
				'name'     => __( 'Vimeo', 'wpmudev_vids' ),
				'provider' => 'https://vimeo.com/api/oembed.json',
				'icon'     => 'sui-icon-instagram',
				'end_time' => false,
			),
			'wistia'  => array(
				'name'     => __( 'Wistia', 'wpmudev_vids' ),
				'format'   => '#https?:\/\/(.+)?(wistia\.com|wi\.st)\/(medias|embed)\/.*#i',
				'provider' => 'http://fast.wistia.net/oembed',
				'regex'    => true,
				'icon'     => 'sui-icon-social-twitter',
				'end_time' => false,
			),
		);

		/**
		 * Filter to add/remove hosts from custom hosts list.
		 *
		 * New hosts can be added using wpmudev_vids_custom_hosts filter and
		 * then you need to handle the embed output using wpmudev_vids_embed_html.
		 *
		 * @param array $hosts Registered hosts.
		 *
		 * @since 1.7
		 */
		return apply_filters( 'wpmudev_vids_custom_hosts', $hosts );
	}

	/**
	 * Get list of WP pages where contexual videos are added.
	 *
	 * Use the filter to add new page to select from playlist
	 * visibility settings.
	 *
	 * @since 1.8.0
	 *
	 * @return array $videos Registered videos.
	 */
	public static function video_pages() {
		$pages = array(
			'dashboard'          => __( 'Dashboard', 'wpmudev_vids' ),
			'post'               => __( 'Add Post', 'wpmudev_vids' ),
			'edit-post'          => __( 'Edit Post', 'wpmudev_vids' ),
			'page'               => __( 'Add Page', 'wpmudev_vids' ),
			'edit-page'          => __( 'Edit Page', 'wpmudev_vids' ),
			'gutenberg-editor'   => __( 'Gutenberg Editor', 'wpmudev_vids' ),
			'widgets'            => __( 'Widgets', 'wpmudev_vids' ),
			'nav-menus'          => __( 'Menus', 'wpmudev_vids' ),
			'themes'             => __( 'Themes', 'wpmudev_vids' ),
			'edit-post_tag'      => __( 'Tags', 'wpmudev_vids' ),
			'edit-category'      => __( 'Categories', 'wpmudev_vids' ),
			'upload'             => __( 'Media Library', 'wpmudev_vids' ),
			'media'              => __( 'Upload Media', 'wpmudev_vids' ),
			'edit-comments'      => __( 'Comments', 'wpmudev_vids' ),
			'users'              => __( 'Users', 'wpmudev_vids' ),
			'user'               => __( 'Add User', 'wpmudev_vids' ),
			'profile'            => __( 'Profile', 'wpmudev_vids' ),
			'user-edit'          => __( 'Edit User', 'wpmudev_vids' ),
			'tools'              => __( 'Tools', 'wpmudev_vids' ),
			'import'             => __( 'Import', 'wpmudev_vids' ),
			'export'             => __( 'Export', 'wpmudev_vids' ),
			'options-general'    => __( 'General Settings', 'wpmudev_vids' ),
			'options-writing'    => __( 'Writing Settings', 'wpmudev_vids' ),
			'options-reading'    => __( 'Reading Settings', 'wpmudev_vids' ),
			'options-discussion' => __( 'Discussion Settings', 'wpmudev_vids' ),
			'options-media'      => __( 'Media Settings', 'wpmudev_vids' ),
			'options-permalink'  => __( 'Permalink Settings', 'wpmudev_vids' ),
			'update-core'        => __( 'WordPress Updates', 'wpmudev_vids' ),
			'plugin-install'     => __( 'Add Plugins', 'wpmudev_vids' ),
			'theme-install'      => __( 'Add Themes', 'wpmudev_vids' ),
		);

		/**
		 * Filter to add/remove pages from contexual help.
		 *
		 * @param array $pages Registered video pages.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_video_pages', $pages );
	}
}