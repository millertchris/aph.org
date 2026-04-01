<?php
/**
 * Singleton class for all classes.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Abstracts
 */

namespace WPMUDEV_Videos\Core\Abstracts;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class Base
 *
 * @package WPMUDEV_Videos\Core\Abstracts
 */
abstract class Base {

	/**
	 * Singleton constructor.
	 *
	 * Protect the class from being initiated multiple times.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	protected function __construct() {
		// Protect class from initiated multiple times.
	}

	/**
	 * Instance obtaining method.
	 *
	 * @since 1.7
	 *
	 * @return static Called class instance.
	 */
	public static function get() {
		static $instances = array();

		// @codingStandardsIgnoreLine Plugin-backported
		$called_class_name = get_called_class();

		if ( ! isset( $instances[ $called_class_name ] ) ) {
			$instances[ $called_class_name ] = new $called_class_name();

			// Optionally initialize the class.
			if ( method_exists( $instances[ $called_class_name ], 'init' ) ) {
				$instances[ $called_class_name ]->init();
			}
		}

		return $instances[ $called_class_name ];
	}
}