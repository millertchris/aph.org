<?php
/**
 * General helper class for the plugin.
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
 * Class General
 *
 * @package WPMUDEV_Videos\Core\Helpers
 */
class General {

	/**
	 * Flag to check switching sites.
	 *
	 * @var bool $switched
	 *
	 * @since 1.7.2
	 */
	private static $switched = false;

	/**
	 * List of plugin page IDs.
	 *
	 * All possible page IDs should be here with the page type string as
	 * the array item value.
	 *
	 * @var array $pages Page ID as key and type as value.
	 *
	 * @since 1.7.0
	 */
	public static $pages = array(
		'support_page_video-tuts'                         => 'tutorials',
		'toplevel_page_video-tuts'                        => 'tutorials',
		'dashboard_page_video-tuts'                       => 'tutorials',
		'toplevel_page_video-tut'                         => 'tutorials',
		'toplevel_page_wpmudev-videos'                    => 'dashboard',
		'toplevel_page_wpmudev-videos-network'            => 'dashboard',
		'dashboard_page_wpmudev-videos-videos'            => 'videos',
		'dashboard_page_wpmudev-videos-videos-network'    => 'videos',
		'dashboard_page_wpmudev-videos-playlists'         => 'playlists',
		'dashboard_page_wpmudev-videos-playlists-network' => 'playlists',
		'dashboard_page_wpmudev-videos-settings'          => 'settings',
		'dashboard_page_wpmudev-videos-settings-network'  => 'settings',
	);

	/**
	 * Check if current page is one of our plugin page.
	 *
	 * @param string|bool $type Page type.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public static function is_plugin_page( $type = false ) {
		if ( empty( $type ) ) {
			// Get current screen id.
			$current_screen = get_current_screen();

			// Check if current page at least any of our pages.
			$result = isset( $current_screen->id ) && in_array( $current_screen->id, array_keys( self::$pages ), true );
		} elseif ( $type && self::current_page() === $type ) {
			$result = true;
		} else {
			$result = false;
		}

		/**
		 * Check if current page is our plugin page.
		 *
		 * @param bool        $result Result.
		 * @param string|bool $type   Page type.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_is_plugin_page', $result, $type );
	}

	/**
	 * Get admin page url for the videos or settings.
	 *
	 * @param string $type Page type (videos or settings).
	 *
	 * @since 1.7
	 *
	 * @return string|void
	 */
	public static function url( $type = 'dashboard' ) {
		// Video tutorials should can be handled differently.
		if ( 'tutorials' === $type ) {
			if ( 'dashboard' === Settings::get( 'menu_location', 'dashboard' ) ) {
				return admin_url( 'index.php?page=video-tuts' );
			} else {
				return admin_url( 'admin.php?page=video-tuts' );
			}
		}

		switch ( $type ) {
			case 'videos':
				$page = 'admin.php?page=wpmudev-videos-videos';
				break;
			case 'playlists':
				$page = 'admin.php?page=wpmudev-videos-playlists';
				break;
			case 'settings':
				$page = 'admin.php?page=wpmudev-videos-settings';
				break;
			default:
				$page = 'admin.php?page=wpmudev-videos';
				break;
		}

		return is_multisite() ? network_admin_url( $page ) : admin_url( $page );
	}

	/**
	 * Get the current admin page alias.
	 *
	 * @param string $page Default page.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function current_page( $page = '' ) {
		// Get current screen id.
		$current_screen = get_current_screen();

		// Check if current page is our plugin page.
		if ( isset( $current_screen->id ) && in_array( $current_screen->id, array_keys( self::$pages ), true ) ) {
			$page = self::$pages[ $current_screen->id ];
		}

		return $page;
	}

	/**
	 * Check if domain mapping is active.
	 *
	 * Domain mapped url are required for the WPMUDEV hosted videos
	 * to work and register with API.
	 *
	 * @since 1.4
	 *
	 * @return bool
	 */
	public static function is_mapped() {
		// As of WP 4.9 Referrer-Policy:same-origin is sent in admin,
		// so we have to use mapping mode now always.
		if ( is_admin() ) {
			return true;
		}

		// Allow users of other domain mapping plugins to turn on support.
		if ( defined( 'WPMUDEV_VIDS_DOMAIN_MAPPED' ) ) {
			return true;
		}

		// Only when domain mapping is available.
		if ( is_multisite() && class_exists( 'domain_map' ) ) {
			// Get settings.
			$options = get_site_option( 'domain_mapping', array() );
			// If not original.
			if ( 'original' !== $options['map_admindomain'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Wrapper function to get main site ID.
	 *
	 * Function get_main_site_id() is introduced in 4.9.0. So we need
	 * to make sure we get the current id even in old versions.
	 *
	 * @since 1.7
	 *
	 * @return int
	 */
	private static function main_site_id() {
		// Use get_main_site_id if it is available.
		if ( function_exists( 'get_main_site_id' ) ) {
			return get_main_site_id();
		}

		// If not multisite, return current ID.
		if ( ! is_multisite() ) {
			return get_current_blog_id();
		}

		// Get the network.
		$network = get_network();

		return $network ? $network->site_id : 0;
	}

	/**
	 * Render an admin view template.
	 *
	 * Helper function to render a template file with data.
	 *
	 * @param string $view   File name.
	 * @param array  $args   Arguments.
	 * @param bool   $return_content Should return the content as string.
	 *
	 * @since 1.7
	 *
	 * @return void|string
	 */
	public static function view( $view, $args = array(), $return_content = false ) {
		// Default views.
		$file_name = WPMUDEV_VIDEOS_DIR . 'app/templates/' . $view . '.php';

		// If file exist, set all arguments are variables.
		if ( file_exists( $file_name ) && is_readable( $file_name ) ) {
			if ( ! empty( $args ) ) {
				$args = (array) $args;

				// Make all array item available in template.
				foreach ( $args as $key => $value ) {
					$$key = $value;
				}
			}

			if ( ! $return_content ) {
				/* @noinspection PhpIncludeInspection */
				include $file_name;
			} else {
				ob_start();
				/* @noinspection PhpIncludeInspection */
				include $file_name;

				// Return the content.
				return ob_get_clean();
			}
		}
	}

	/**
	 * Switch to main site if required.
	 *
	 * Custom videos are stored in main site as custom post.
	 * We need to switch to main site to query the post data.
	 * We will keep an internal flag so that
	 *
	 * @since 1.7.2
	 *
	 * @return void
	 */
	public static function switch_site() {
		// Already switched, don't overkill, dude.
		if ( self::$switched ) {
			return;
		}

		// Switch if not in main site.
		if ( ! is_main_site() ) {
			// Switch to main site.
			switch_to_blog( self::main_site_id() );

			// Switch flag.
			self::$switched = true;
		}
	}

	/**
	 * Restore to current site if required.
	 *
	 * Custom videos are stored in main site as custom post.
	 * We need to switch back to current site after switching
	 * to main site for query.
	 *
	 * @since 1.7.2
	 *
	 * @return void
	 */
	public static function restore_site() {
		if ( self::$switched ) {
			// Restore to previous blog.
			restore_current_blog();

			// Reset the flag.
			self::$switched = false;
		}
	}

	/**
	 * Returns current user name to be displayed.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function get_user_name() {
		// Get current user.
		$current_user = wp_get_current_user();

		// If first name is ready get that, or get the display name.
		$name = empty( $current_user->first_name ) ? $current_user->display_name : $current_user->first_name;

		// Fallback to unknown name.
		if ( empty( $name ) ) {
			$name = __( 'User', 'wpmudev_vids' );
		}

		return ucfirst( $name );
	}

	/**
	 * Check if currently logged in user is a valid member.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function is_valid_member() {
		if ( class_exists( 'WPMUDEV_Dashboard' ) && method_exists( \WPMUDEV_Dashboard::$upgrader, 'user_can_install' ) ) {
			return \WPMUDEV_Dashboard::$upgrader->user_can_install( 248, true );
		}

		return false;
	}

	/**
	 * Get WPMUDEV API key for the member.
	 *
	 * Members can define the API key using WPMUDEV_APIKEY constant
	 * or install and activate the WPMUDEV Dashboard plugin.
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public static function api_key() {
		$key = false;

		// Dashboard is active.
		if ( class_exists( 'WPMUDEV_Dashboard' ) ) {
			// Get membership type.
			$key = \WPMUDEV_Dashboard::$api->get_key();
		} elseif ( defined( 'WPMUDEV_APIKEY' ) && WPMUDEV_APIKEY ) {
			$key = WPMUDEV_APIKEY;
		}

		return $key;
	}

	/**
	 * Get WPMUDEV Hub site id for the site.
	 *
	 * Get the unique site id from the Hub for the current website.
	 *
	 * @since 1.8.0
	 *
	 * @return int
	 */
	public static function hub_site_id() {
		$id = 0;

		// Dashboard is active.
		if ( class_exists( 'WPMUDEV_Dashboard' ) ) {
			// Get membership type.
			$id = \WPMUDEV_Dashboard::$api->get_site_id();
		}

		return $id;
	}

	/**
	 * Get the current membership status using Dash plugin.
	 *
	 * We will get the status using WPMUDEV Dashboard plugin.
	 *
	 * @since 1.8.0
	 * @since 1.8.2 Removed $project_id param.
	 *
	 * @return bool
	 */
	public static function membership_status() {
		static $status = null;

		// Get the status.
		if ( is_null( $status ) ) {
			// Dashboard is active and required methods are available.
			if (
				class_exists( 'WPMUDEV_Dashboard' )
				&& isset( \WPMUDEV_Dashboard::$api )
				&& method_exists( \WPMUDEV_Dashboard::$api, 'get_membership_status' )
			) {
				// Get membership type.
				$status = \WPMUDEV_Dashboard::$api->get_membership_status();
			} else {
				$status = '';
			}
		}

		/**
		 * Filter to modify WPMUDEV membership status or user.
		 *
		 * @param string $status Status.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_membership_status', $status );
	}
}