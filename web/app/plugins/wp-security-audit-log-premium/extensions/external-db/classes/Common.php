<?php
/**
 * Class: Utility Class
 *
 * Utility class for common function.
 *
 * @since      1.0.0
 * @package    wsal
 * @subpackage external-db
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WSAL_Ext_Common
 *
 * Utility class, used for all the common functions used in the plugin.
 *
 * @package    wsal
 * @subpackage external-db
 */
class WSAL_Ext_Common {

	/**
	 * Holds the extension base URL.
	 *
	 * @var string
	 *
	 * @since 4.4.3.2
	 */
	private static $extension_base_url = null;

	/**
	 * Monolog helper.
	 *
	 * @var WSAL_Ext_MonologHelper
	 *
	 * @since 5.0.0
	 */
	private static $monolog_helper;

	/**
	 * Returns the extension base URL directory.
	 *
	 * @return string
	 *
	 * @since 4.4.3.2
	 */
	public static function get_extension_base_url(): string {
		if ( null === self::$extension_base_url ) {
			self::$extension_base_url = trailingslashit( WSAL_BASE_URL ) . 'extensions/external-db/';
		}

		return self::$extension_base_url;
	}

	/**
	 * Gets the Monolog helper instance.
	 *
	 * @return WSAL_Ext_MonologHelper Monolog helper instance.
	 *
	 * @since 4.3.0
	 */
	public static function get_monolog_helper() {
		if ( ! isset( self::$monolog_helper ) ) {
			global $wsal_class;
			self::$monolog_helper = new \WSAL_Ext_MonologHelper( $wsal_class );
		}

		return self::$monolog_helper;
	}
}
