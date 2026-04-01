<?php
/**
 * The compatibility functionality class.
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

use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Compatibility
 *
 * @package WPMUDEV_Videos\Core\Controllers
 */
class Compatibility extends Base {

	/**
	 * Initialize compatibility.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Compatibility with Members.
		add_action( 'members_register_caps', array( $this, 'register_members_caps' ) );
		add_action( 'members_register_cap_groups', array( $this, 'register_members_groups' ) );

		// Compatibility with User Role Editor.
		add_filter( 'ure_built_in_wp_caps', array( $this, 'filter_ure_caps' ) );
		add_filter( 'ure_capabilities_groups_tree', array( $this, 'filter_ure_groups' ) );
	}

	/**
	 * Registers the custom capability for the Members plugin.
	 *
	 * @link  https://wordpress.org/plugins/members/
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function register_members_caps() {
		members_register_cap(
			Permission::SETTINGS_CAP,
			array(
				'label' => __( 'Access Settings', 'wpmudev_vids' ),
				'group' => 'wpmudev_videos',
			)
		);
	}

	/**
	 * Registers the custom capability group for the Members plugin.
	 *
	 * @link  https://wordpress.org/plugins/members/
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function register_members_groups() {
		members_register_cap_group(
			'wpmudev_videos',
			array(
				'label' => __( 'WPMUDEV Videos', 'wpmudev_vids' ),
				'caps'  => array( Permission::SETTINGS_CAP ),
				'icon'  => 'dashicons-format-video',
			)
		);
	}

	/**
	 * Registers the custom capability for the User Role Editor plugin.
	 *
	 * @link https://wordpress.org/plugins/user-role-editor/
	 *
	 * @param array[] $caps Array of existing capabilities.
	 *
	 * @return array[] Updated array of capabilities.
	 */
	public function filter_ure_caps( $caps ) {
		$caps[ Permission::SETTINGS_CAP ] = array(
			'custom',
			'wpmudev_videos',
		);

		return $caps;
	}

	/**
	 * Registers the custom capability group for the User Role Editor plugin.
	 *
	 * @link https://wordpress.org/plugins/user-role-editor/
	 *
	 * @param array[] $groups Array of existing groups.
	 *
	 * @return array[] Updated array of groups.
	 */
	public function filter_ure_groups( $groups ) {
		$groups['wpmudev_videos'] = array(
			'caption' => esc_html__( 'WPMUDEV Videos', 'wpmudev_vids' ),
			'parent'  => 'custom',
			'level'   => 2,
		);

		return $groups;
	}
}