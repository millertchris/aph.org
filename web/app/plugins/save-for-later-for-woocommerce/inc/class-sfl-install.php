<?php
/**
 * Initialize the Plugin.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Install' ) ) {

	/**
	 * SFL_Install Class.
	 *
	 * @since 1.0
	 * */
	class SFL_Install {

		/**
		 * Hook in methods
		 *
		 * @since 1.0
		 * */
		public static function init() {
			add_action( 'woocommerce_init', array( __CLASS__, 'check_version' ) );
			add_filter( 'plugin_action_links_' . SFL_PLUGIN_SLUG, array( __CLASS__, 'settings_link' ) );
		}

		/**
		 * Check Version.
		 *
		 * @since 1.0
		 * */
		public static function check_version() {
			if ( version_compare( get_option( 'sfl_version', 1.0 ), SFL_VERSION, '>=' ) ) {
				return;
			}

			self::install(); // Set default values.
		}

		/**
		 * Install
		 *
		 * @since 1.0
		 */
		public static function install() {
			self::set_default_values(); // default values.
			self::update_version();
		}

		/**
		 * Settings link.
		 *
		 * @since 1.0
		 * @param string $links Settings URL
		 */
		public static function settings_link( $links ) {
			$setting_page_link = '<a href="' . sfl_get_settings_page_url() . '">' . esc_html__( 'Settings', 'save-for-later-for-woocommerce' ) . '</a>';

			array_unshift( $links, $setting_page_link );

			return $links;
		}

		/**
		 *  Set settings default values
		 *
		 * @since 1.0
		 */
		public static function set_default_values() {
			if ( ! class_exists( 'SFL_Settings' ) ) {
				include_once SFL_PLUGIN_PATH . '/inc/admin/menu/class-sfl-settings.php';
			}

			// default for settings.
			$settings = SFL_Settings::get_settings_pages();

			foreach ( $settings as $setting_key => $value ) {

				$settings_array = $value->get_settings( $setting_key );

				foreach ( $settings_array as $value ) {

					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						if ( get_option( $value['id'] ) === false ) {
							add_option( $value['id'], $value['default'] );
						}
					}
				}
			}

			self::set_custom_option_default_values();
		}

		/**
		 * Set Custom options default values.
		 *
		 * @since 1.0
		 */
		public static function set_custom_option_default_values() {
			// General Tab.
			if ( get_option( 'sfl_general_guest_timeout' ) == false ) {
				add_option( 'sfl_general_guest_timeout', 60 );
			}

			if ( get_option( 'sfl_general_guest_timeout_type' ) == false ) {
				add_option( 'sfl_general_guest_timeout_type', 1 );
			}
		}



		/**
		 * Update current version.
		 *
		 * @since 1.0
		 */
		private static function update_version() {
			update_option( 'sfl_version', SFL_VERSION );
		}
	}

	SFL_Install::init();
}
