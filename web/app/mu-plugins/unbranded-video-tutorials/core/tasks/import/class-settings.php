<?php
/**
 * The settings import functionality class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.1
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */

namespace WPMUDEV_Videos\Core\Tasks\Import;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Abstracts\Base;
use WPMUDEV_Videos\Core\Helpers\Settings as Options;

/**
 * Class Settings
 *
 * @package WPMUDEV_Videos\Core\Tasks\Import
 */
class Settings extends Base {

	/**
	 * Display settings fields.
	 *
	 * @since 1.8.3
	 *
	 * @var array $display
	 */
	private $display = array(
		'show_menu',
		'menu_title',
		'menu_location',
		'contextual_help',
	);

	/**
	 * Permissions settings fields.
	 *
	 * @since 1.8.3
	 *
	 * @var array $display
	 */
	private $permissions = array(
		'roles',
	);

	/**
	 * Import the settings from json.
	 *
	 * @param array $settings Settings data to import.
	 * @param array $selected Selected items.
	 *
	 * @since 1.8.1
	 */
	public function import( $settings, $selected = array() ) {
		// If empty, no need to continue.
		if ( empty( $settings ) ) {
			return;
		}

		// Get existing settings.
		$options = Options::get();

		// Groups to import.
		$groups = array( 'display', 'permissions' );

		foreach ( $groups as $group ) {
			// If selected.
			if ( in_array( $group, $selected, true ) ) {
				foreach ( $this->{$group} as $key ) {
					// Replace existing options.
					if ( isset( $settings[ $key ] ) ) {
						$options[ $key ] = $settings[ $key ];
					}
				}
			}
		}

		// Update settings.
		Options::set( false, $options );
	}
}