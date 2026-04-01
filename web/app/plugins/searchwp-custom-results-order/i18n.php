<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Internationalization handler
 *
 * @since 1.0
 */
class SearchWP_CRO_I18n {

	/**
	 * Strings used throughout
	 *
	 * @var array $strings
	 */
	public static $strings;

	/**
	 * Defines strings used in the UI that require i18n
	 *
	 * @since 1.0
	 */
	private function __construct() {}

	/**
	 * Initializer
	 *
	 * @since 1.0
	 */
	public static function init() {

		// Load translations from the plugin's languages directory.
		load_plugin_textdomain( 'searchwp-custom-results-order', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Load translations string.
		self::load_translations();
	}

	/**
	 * Loads translations.
	 *
	 * @since 1.3.10
	 */
	public static function load_translations() {

		self::$strings = [
			'activate' => __( 'Activate', 'searchwp-custom-results-order' ),
		];
	}
}
