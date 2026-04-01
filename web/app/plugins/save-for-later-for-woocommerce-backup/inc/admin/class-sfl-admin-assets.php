<?php
/**
 * Enqueue Admin Assets Files.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Admin_Assets' ) ) {

	/**
	 * SFL_Admin_Assets Class.
	 */
	class SFL_Admin_Assets {

		/**
		 * SFL_Admin_Assets Class Initialization.
		 *
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'external_js_files' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'external_css_files' ) );
		}

		/**
		 * Enqueue external css files.
		 *
		 * @since 1.0
		 */
		public static function external_css_files() {
			$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$screen_ids   = sfl_page_screen_ids();
			$newscreenids = get_current_screen();
			$screenid     = str_replace( 'edit-', '', $newscreenids->id );

			if ( ! in_array( $screenid, $screen_ids ) ) {
				return;
			}

			wp_enqueue_style( 'sfl_admin_styles', SFL_PLUGIN_URL . '/assets/css/admin/admin.css', array(), SFL_VERSION );
		}

		/**
		 * Enqueue Admin end required JS files.
		 *
		 * @since 1.0
		 */
		public static function external_js_files() {
			$suffix        = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$screen_ids    = sfl_page_screen_ids();
			$newscreenids  = get_current_screen();
			$screenid      = str_replace( 'edit-', '', $newscreenids->id );
			$enqueue_array = array(
				'sfl-select2'  => array(
					'callable' => array( 'SFL_Admin_Assets', 'select2' ),
					'restrict' => in_array( $screenid, $screen_ids ),
				),
				'sfl-settings' => array(
					'callable' => array( 'SFL_Admin_Assets', 'settings_scripts' ),
					'restrict' => in_array( $screenid, $screen_ids ),
				),
			);

			/**
			 * Filter for Admin Enqueue Scripts
			 *
			 * @since 1.0
			 * @param Array $enqueue_array Enqueue Arguments.
			 */
			$enqueue_array = apply_filters( 'sfl_admin_enqueue_scripts', $enqueue_array );

			if ( ! sfl_check_is_array( $enqueue_array ) ) {
				return;
			}

			foreach ( $enqueue_array as $key => $enqueue ) {
				if ( ! sfl_check_is_array( $enqueue ) ) {
					continue;
				}

				if ( $enqueue['restrict'] ) {
					call_user_func_array( $enqueue['callable'], array( $suffix ) );
				}
			}

			/**
			 * Action hook fired Admin JS Enqueue.
			 *
			 * @since 1.0
			 */
			do_action( 'sfl_admin_after_enqueue_js' );
		}

		/**
		 * Enqueue select2 scripts.
		 *
		 * @since 1.0
		 */
		public static function select2( $suffix ) {
			wp_enqueue_script( 'sfl-enhanced', SFL_PLUGIN_URL . '/assets/js/sfl-enhanced.js', array( 'jquery', 'select2', 'jquery-ui-datepicker' ), SFL_VERSION );
			wp_localize_script(
				'sfl-enhanced',
				'sfl_enhanced_select_params',
				array(
					'search_nonce' => wp_create_nonce( 'sfl-search-nonce' ),
					'ajaxurl'      => SFL_ADMIN_AJAX_URL,
				)
			);
		}

		/**
		 * Enqueue Admin Settings scripts.
		 *
		 * @since 1.0
		 */
		public static function settings_scripts() {
			wp_enqueue_script( 'sfl-settings', SFL_PLUGIN_URL . '/assets/js/admin/sfl-settings.js', array( 'jquery' ), SFL_VERSION );
		}
	}

	SFL_Admin_Assets::init();
}
