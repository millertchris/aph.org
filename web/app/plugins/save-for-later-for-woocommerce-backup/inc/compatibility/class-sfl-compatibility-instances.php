<?php
/**
 * Compatibility Instances Class.
 *
 * @package Composite Product/Compatibility Instances
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Compatibility_Instances' ) ) {

	/**
	 * Main Class
	 */
	class SFL_Compatibility_Instances {

		/**
		 * Compatibilities.
		 *
		 * @var Array
		 * */
		private static $compatibilities;

		/**
		 * Get Compatibilities.
		 *
		 * @var Array
		 */
		public static function instance() {
			if ( is_null( self::$compatibilities ) ) {
				self::$compatibilities = self::load_compatibilities();
			}

			return self::$compatibilities;
		}

		/**
		 * Load all Compatibilities.
		 *
		 * @since 3.8.0
		 */
		public static function load_compatibilities() {
			if ( ! class_exists( 'SFL_Abstract_Compatibility' ) ) {
				include SFL_PLUGIN_PATH . '/inc/abstracts/class-sfl-abstract-compatibility.php';
			}

			$default_compatibility_classes = array(
				'sfl-wc-composite-compatibility' => 'SFL_WC_Composite_Compatibility',
			);

			if ( sfl_check_is_array( $default_compatibility_classes ) ) {
				foreach ( $default_compatibility_classes as $file_name => $compatibility_class ) {

					// Include file.
					include 'class-' . $file_name . '.php';

					// Add compatibility.
					self::add_compatibility( new $compatibility_class() );
				}
			}
		}

		/**
		 * Add a Compatibility.
		 *
		 * @since 3.8.0
		 * @param Object $compatibility Compatibility object.
		 * @return Object
		 */
		public static function add_compatibility( $compatibility ) {
			self::$compatibilities[ $compatibility->get_id() ] = $compatibility;

			return new self();
		}

		/**
		 * Get compatibility by id.
		 *
		 * @since 3.8.0
		 * @param String $compatibility_id ID of the compatibility.
		 * @return String|Boolean
		 */
		public static function get_compatibility_by_id( $compatibility_id ) {
			$compatibilities = self::instance();

			return isset( $compatibilities[ $compatibility_id ] ) ? $compatibilities[ $compatibility_id ] : false;
		}

		/**
		 * Reset.
		 *
		 * @since 3.8.0
		 * */
		public static function reset() {
			self::$compatibilities = null;
		}
	}
}
