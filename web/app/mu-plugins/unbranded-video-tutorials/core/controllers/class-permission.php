<?php
/**
 * The permission functionality class.
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

use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Permission
 *
 * This class will handle setting the capability for the settings
 * and other functionality of the plugin.
 *
 * @package WPMUDEV_Videos\Core\Controllers
 */
class Permission extends Base {

	/**
	 * Settings capability name.
	 *
	 * @var string $settings_cap
	 *
	 * @since 1.8.0
	 */
	const SETTINGS_CAP = 'wpmudev_videos_manage_settings';

	/**
	 * Initialize permission functionality.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Set capabilities.
		add_action( 'wpmudev_vids_after_update_settings', array( $this, 'set_capability' ) );
		add_filter( 'user_has_cap', array( $this, 'filter_user_has_cap' ), 10, 3 );
	}

	/**
	 * Update the role capabilities based on the settings.
	 *
	 * Plugin settings can be managed only by a user with a custom
	 * capability `wpmudev_videos_manage_settings`. When the settings are
	 * saved, get the selected roles and assign the required capability
	 * to the roles.
	 *
	 * @since 1.7
	 *
	 * @global $wp_roles
	 *
	 * @return void
	 */
	public function set_capability() {
		global $wp_roles;

		// Enabled capabilities.
		$enabled_roles = (array) Helpers\Settings::get( 'roles', array() );

		// Make sure admin user has the capability in single installations.
		if ( ! is_multisite() ) {
			$enabled_roles = array_merge( array( 'administrator' ), $enabled_roles );
		}

		// Loop through each roles.
		foreach ( $wp_roles->get_names() as $role => $label ) {
			// Role is enabled in settings, so add capability.
			if ( in_array( $role, $enabled_roles, true ) ) {
				$wp_roles->add_cap( $role, self::SETTINGS_CAP );
			} else {
				// Remove the capability if not enabled.
				$wp_roles->remove_cap( $role, self::SETTINGS_CAP );
			}
		}
	}

	/**
	 * Filter a user's capabilities so they can be altered at runtime.
	 *
	 * This is used to grant  the 'wpmudev_videos_manage_settings' capability
	 * to the user if they have the ability to manage options.
	 * This does not get called for Super Admins because super admin has all capabilities.
	 *
	 * @param bool[]   $user_caps     Array of key/value pairs where keys represent a capability name and boolean values
	 *                                represent whether the user has that capability.
	 * @param string[] $required_caps Required primitive capabilities for the requested capability.
	 * @param array    $args          Arguments that accompany the requested capability check.
	 *
	 * @since 1.7
	 *
	 * @return bool[] Concerned user's capabilities.
	 */
	public function filter_user_has_cap( $user_caps, $required_caps, $args ) {
		// Our custom capability is not being checked.
		if ( self::SETTINGS_CAP !== $args[0] ) {
			return $user_caps;
		}

		// User already has the capability.
		if ( array_key_exists( self::SETTINGS_CAP, $user_caps ) ) {
			return $user_caps;
		}

		// Non-multisite admin should be capable.
		if ( ! is_multisite() && user_can( $args[1], 'manage_options' ) ) {
			$user_caps[ self::SETTINGS_CAP ] = true;
		}

		return $user_caps;
	}
}