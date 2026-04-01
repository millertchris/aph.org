<?php
/**
 * The main plugin class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core
 */

namespace WPMUDEV_Videos\Core;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class WPMUDEV_Videos
 *
 * @package WPMUDEV_Videos\Core
 */
final class WPMUDEV_Videos extends Base {

	/**
	 * Initialize functionality of the plugin.
	 *
	 * This is where we kick-start the plugin by defining
	 * everything required and register all hooks.
	 *
	 * @since  3.2.0
	 * @access protected
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->define();
		$this->run();
	}

	/**
	 * Register all of the actions and filters.
	 *
	 * @since  1.8.0
	 * @access private
	 *
	 * @return void
	 */
	private function run() {
		// Run free version.
		Core::get()->setup();
	}

	/**
	 * Check if current version is Pro.
	 *
	 * Currently only Pro version is available.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public function is_pro() {
		return true;
	}

	/**
	 * Define all the constants required for the plugin.
	 *
	 * We define only the required items at the main plugin file so that
	 * we can handle the Pro/Free conflicts easily.
	 *
	 * @since 1.8.0
	 */
	private function define() {
		// Shared UI version.
		if ( ! defined( 'WPMUDEV_VIDEOS_SUI_VERSION' ) ) {
			define( 'WPMUDEV_VIDEOS_SUI_VERSION', '2.12.23' );
		}

		// Plugin directory.
		if ( ! defined( 'WPMUDEV_VIDEOS_DIR' ) ) {
			define( 'WPMUDEV_VIDEOS_DIR', plugin_dir_path( WPMUDEV_VIDEOS_FILE ) );
		}

		// Plugin url.
		if ( ! defined( 'WPMUDEV_VIDEOS_URL' ) ) {
			define( 'WPMUDEV_VIDEOS_URL', plugin_dir_url( WPMUDEV_VIDEOS_FILE ) );
		}
	}
}