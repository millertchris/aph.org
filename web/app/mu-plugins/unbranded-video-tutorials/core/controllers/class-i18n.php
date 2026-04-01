<?php
/**
 * The internationalization class of the plugin.
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
 * Class I18n
 *
 * @package WPMUDEV_Videos\Core\Controllers
 */
class I18n extends Base {

	/**
	 * Initialize the class by registering hooks.
	 *
	 * @since 3.2.4
	 *
	 * @return void
	 */
	public function init() {
		// Set text domain.
		add_action( 'init', array( $this, 'setup_locale' ) );
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.7.0
	 * @since  1.8.0 Moved to own class.
	 * @access public
	 *
	 * @return void
	 */
	public function setup_locale() {
		// Localize the plugin.
		load_plugin_textdomain(
			'wpmudev_vids',
			false,
			WPMUDEV_VIDEOS_DIR . '/languages/'
		);
	}
}