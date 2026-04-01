<?php
/**
 * The admin menu functionality class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Controllers
 */

namespace WPMUDEV_Videos\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Views;
use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Menu
 *
 * @package WPMUDEV_Videos\Core\Controllers
 */
class Menu extends Base {

	/**
	 * Initialize menu functionality.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Setup menu page.
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		// Setup videos page.
		add_action( 'admin_menu', array( $this, 'videos_menu' ) );
	}

	/**
	 * Register admin settings page menu.
	 *
	 * You can hide menu using WPMUDEV_VIDS_HIDE_SETTINGS constant defining true,
	 * but we strongly recommend NOT doing so.
	 * Plugin can be only managed in network admin in multisite.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		// Define this in wp-config to hide the setting menu.
		if ( defined( 'WPMUDEV_VIDS_HIDE_SETTINGS' ) && WPMUDEV_VIDS_HIDE_SETTINGS ) {
			return;
		}

		// Setup sub menus.
		$this->dashboard();
		$this->videos();
		$this->playlists();
		$this->settings();

		// Rename menu.
		$this->rename_dashboard();
	}

	/**
	 * Setup dashboard main admin menu.
	 *
	 * We will rewrite the name to match the plugin name later.
	 *
	 * @since 1.8.0
	 */
	public function dashboard() {
		add_menu_page(
			__( 'Dashboard', 'wpmudev_vids' ),
			__( 'Dashboard', 'wpmudev_vids' ),
			Permission::SETTINGS_CAP,
			'wpmudev-videos',
			array( Views\Admin::get(), 'dashboard' ),
			Views\Admin::get()->get_menu_icon()
		);
	}

	/**
	 * Setup videos management page submenu.
	 *
	 * This page will be used to manage the videos.
	 *
	 * @since 1.8.0
	 */
	public function videos() {
		add_submenu_page(
			'wpmudev-videos',
			__( 'Videos', 'wpmudev_vids' ),
			__( 'Videos', 'wpmudev_vids' ),
			Permission::SETTINGS_CAP,
			'wpmudev-videos-videos',
			array( Views\Admin::get(), 'videos' )
		);
	}

	/**
	 * Setup playlist management page submenu.
	 *
	 * @since 1.8.0
	 */
	public function playlists() {
		add_submenu_page(
			'wpmudev-videos',
			__( 'Playlists', 'wpmudev_vids' ),
			__( 'Playlists', 'wpmudev_vids' ),
			Permission::SETTINGS_CAP,
			'wpmudev-videos-playlists',
			array( Views\Admin::get(), 'playlists' )
		);
	}

	/**
	 * Setup plugin settings page submenu.
	 *
	 * This page will handle the settings page view.
	 *
	 * @since 1.8.0
	 */
	public function settings() {
		add_submenu_page(
			'wpmudev-videos',
			__( 'Settings', 'wpmudev_vids' ),
			__( 'Settings', 'wpmudev_vids' ),
			Permission::SETTINGS_CAP,
			'wpmudev-videos-settings',
			array( Views\Admin::get(), 'settings' )
		);
	}

	/**
	 * Rename the main label of the menu to WPMUDEV Videos.
	 *
	 * This should be run after setting all menu items.
	 *
	 * @since 1.8.0
	 */
	public function rename_dashboard() {
		global $menu;

		foreach ( $menu as $position => $data ) {
			// Only when it's WPMUDEV Videos menu.
			if ( isset( $data[2] ) && 'wpmudev-videos' === $data[2] ) {
				// Rename the plugin main menu title to Video Tutorials.
				// phpcs:ignore
				$menu[ $position ][0] = __( 'Video Settings', 'wpmudev_vids' );
			}
		}
	}

	/**
	 * Video tutorials menu in admin area.
	 *
	 * Video list page is required only within subsites if network.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function videos_menu() {
		// We need video pages inside single site admin only.
		if ( is_network_admin() ) {
			return;
		}

		// Check if menu is enabled.
		$enable_menu = Helpers\Settings::get( 'show_menu' );

		if ( empty( $enable_menu ) ) {
			return;
		}

		// Menu location settings.
		$menu_location = Helpers\Settings::get(
			'menu_location',
			'dashboard'
		);

		// Menu title.
		$menu_title = $this->videos_menu_title();

		// If menu enabled, make sure menu location is set.
		if ( empty( $menu_location ) ) {
			$menu_location = 'dashboard';
		}

		switch ( $menu_location ) {
			case 'top':
				// Add new top level menu.
				add_menu_page(
					esc_attr( $menu_title ),
					esc_attr( $menu_title ),
					'read',
					'video-tut',
					array( Views\Admin::get(), 'video_tutorials' ),
					'dashicons-format-video',
					57.24
				);
				break;
			case 'dashboard':
			case 'support_system':
				// Support system menu.
				$parent_slug = 'support_system' === $menu_location ? 'ticket-manager' : 'index.php';

				// Add submenu.
				add_submenu_page(
					$parent_slug,
					esc_attr( $menu_title ),
					esc_attr( $menu_title ),
					'read',
					'video-tuts',
					array( Views\Admin::get(), 'video_tutorials' )
				);
				break;
		}
	}

	/**
	 * Video tutorials menu title.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	public function videos_menu_title() {
		// Menu title.
		$menu_title = Helpers\Settings::get(
			'menu_title',
			__( 'Video Tutorials', 'wpmudev_vids' )
		);

		// Make sure menu title is not empty.
		if ( empty( $menu_title ) ) {
			$menu_title = __( 'Video Tutorials', 'wpmudev_vids' );
		}

		/**
		 * Filter hook to modify videos menu title.
		 *
		 * @param string $menu_title Menu title.
		 *
		 * @since 1.8.4
		 */
		return apply_filters( 'wpmudev_vids_videos_menu_title', $menu_title );
	}
}