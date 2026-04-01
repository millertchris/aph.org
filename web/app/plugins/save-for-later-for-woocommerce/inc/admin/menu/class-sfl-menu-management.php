<?php
/**
 * Menu Management
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Menu_Management' ) ) {

	include_once 'class-sfl-settings.php';

	/**
	 * Main SFL_Menu_Management Class.
	 * */
	class SFL_Menu_Management {

		/**
		 * Plugin slug.
		 *
		 * @var String
		 */
		protected static $plugin_slug = 'sfl';

		/**
		 * Menu slug.
		 *
		 * @var String
		 */
		protected static $menu_slug = 'sfl_user_list';

		/**
		 * Settings slug.
		 *
		 * @var String
		 */
		protected static $settings_slug = 'sfl_settings';

		/**
		 * Reports slug.
		 *
		 * @var String
		 */
		protected static $reports_slug = 'sfl_reports';

		/**
		 * Class Init Function
		 *
		 * @since 1.0
		 */
		public static function init() {
			// Add Admin Menu Page.
			add_action( 'admin_menu', array( __CLASS__, 'add_menu_pages' ) );
			// Add Custom Screen Ids.
			add_filter( 'woocommerce_screen_ids', array( __CLASS__, 'add_custom_wc_screen_ids' ), 9, 1 );
		}

		/**
		 * Add Custom Screen IDs in WooCommerce.
		 *
		 * @since 1.0
		 * @param Object $wc_screen_ids Woocommerce Screent ID's.
		 */
		public static function add_custom_wc_screen_ids( $wc_screen_ids ) {
			$screen_ids   = sfl_page_screen_ids();
			$newscreenids = get_current_screen();
			$screenid     = str_replace( 'edit-', '', $newscreenids->id );

			// Return if current page is not refund page.
			if ( ! in_array( $screenid, $screen_ids, true ) ) {
				return $wc_screen_ids;
			}

			$wc_screen_ids[] = $screenid;

			return $wc_screen_ids;
		}

		/**
		 * Adding SFL Menu
		 *
		 * @since 1.0
		 */
		public static function add_menu_pages() {
			$dash_icon_url = SFL_PLUGIN_URL . '/assets/images/dash-icon.png';
			add_menu_page( esc_html__( 'Save For Later', 'save-for-later-for-woocommerce' ), esc_html__( 'Save For Later', 'save-for-later-for-woocommerce' ), 'manage_options', self::$menu_slug, '', $dash_icon_url );

			$settings_page = add_submenu_page( self::$menu_slug, esc_html__( 'Settings', 'save-for-later-for-woocommerce' ), esc_html__( 'Settings', 'save-for-later-for-woocommerce' ), 'manage_options', self::$settings_slug, array( __CLASS__, 'settings_page' ) );
			$reports_page  = add_submenu_page( self::$menu_slug, esc_html__( 'Reports', 'save-for-later-for-woocommerce' ), esc_html__( 'Reports', 'save-for-later-for-woocommerce' ), 'manage_options', self::$reports_slug, array( __CLASS__, 'reports_page' ) );

			add_action( sanitize_key( 'load-' . $settings_page ), array( __CLASS__, 'settings_page_init' ) );
		}

		/**
		 * Settings page init.
		 *
		 * @since 1.0
		 */
		public static function settings_page_init() {
			global $current_tab, $current_section, $current_action;

			// Include settings pages.
			$settings        = SFL_Settings::get_settings_pages();
			$tabs            = sfl_get_allowed_setting_tabs();
			$current_tab     = ( ! isset( $_GET['tab'] ) || empty( $_GET['tab'] ) || ! array_key_exists( wc_clean( wp_unslash( $_GET['tab'] ) ), $tabs ) ) ? key( $tabs ) : wc_clean( wp_unslash( $_GET['tab'] ) ); // Get current tab/section.
			$section         = isset( $settings[ $current_tab ] ) ? $settings[ $current_tab ]->get_sections() : array();
			$current_section = empty( $_REQUEST['section'] ) ? key( $section ) : wc_clean( wp_unslash( $_REQUEST['section'] ) );
			$current_section = empty( $current_section ) ? $current_tab : $current_section;
			$current_action  = empty( $_REQUEST['action'] ) ? '' : wc_clean( wp_unslash( $_REQUEST['action'] ) );

			/**
			 * Action hook fired Settings Save.
			 *
			 * @since 1.0
			 */
			do_action( sanitize_key( self::$plugin_slug . '_settings_save_' . $current_tab ), $current_section );

			/**
			 * Action hook fired Settings Reset.
			 *
			 * @since 1.0
			 */
			do_action( sanitize_key( self::$plugin_slug . '_settings_reset_' . $current_tab ), $current_section );

			// Add Custom Field in Settings.
			add_action( 'woocommerce_admin_field_sfl_custom_fields', array( __CLASS__, 'custom_fields_output' ) );
			// Save Custom Field in Settings.
			add_filter( 'woocommerce_admin_settings_sanitize_option_sfl_custom_fields', array( __CLASS__, 'save_custom_fields' ), 10, 3 );
		}

		/**
		 * Settings page output.
		 *
		 * @since 1.0
		 */
		public static function settings_page() {
			SFL_Settings::output();
		}

		/**
		 * Settings page output.
		 *
		 * @since 1.0
		 */
		public static function reports_page() {
			include_once 'reports/reports-view.php';
		}

		/**
		 * Output the custom field settings.
		 *
		 * @since 1.0
		 */
		public static function custom_fields_output( $options ) {
			SFL_Settings::output_fields( $options );
		}

		/**
		 * Save Custom Field settings.
		 *
		 * @since 1.0
		 */
		public static function save_custom_fields( $value, $option, $raw_value ) {
			SFL_Settings::save_fields( $value, $option, $raw_value );
		}
	}

	SFL_Menu_Management::init();
}
