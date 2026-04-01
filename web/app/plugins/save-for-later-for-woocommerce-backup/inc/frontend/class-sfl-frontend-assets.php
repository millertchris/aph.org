<?php
/**
 * Enqueue Frontend Assets Files.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'SFL_Frontend_Assets' ) ) {

	/**
	 * SFL_Frontend_Assets Class.
	 *
	 * @since 1.0
	 */
	class SFL_Frontend_Assets {

		/**
		 * SFL_Frontend_Assets Class Initialization.
		 *
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'external_js_files' ) );
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'external_css_files' ) );
		}

		/**
		 * Enqueue external JS files.
		 *
		 * @since 1.0
		 */
		public static function external_js_files() {
			wp_enqueue_script( 'sfl_scripts', SFL_PLUGIN_URL . '/assets/js/frontend/frontend.js', array( 'jquery', 'jquery-blockui' ), SFL_VERSION );
			wp_localize_script(
				'sfl_scripts',
				'sfl_frontend_params',
				array(
					'sfl_list_pagination_nonce' => wp_create_nonce( 'sfl-list-pagination' ),
					'ajaxurl'                   => SFL_ADMIN_AJAX_URL,
					'current_user'              => get_current_user_id(),
					'is_logged_in'              => is_user_logged_in() ? true : false,
					'current_page_url'          => get_permalink(),
					'remove_from_sfl_list_msg'  => esc_html__( 'Are you sure you want to remove from the list?', 'save-for-later-for-woocommerce' ),
				)
			);
		}

		/**
		 * Enqueue external css files.
		 *
		 * @since 1.0
		 */
		public static function external_css_files() {
			wp_enqueue_style('dashicons');
			if ( 1 == get_option( 'sfl_advanced_apply_styles_from' ) ) {
				wp_enqueue_style( 'sfl_list_styles', SFL_PLUGIN_URL . '/assets/css/frontend/sfl_list_styles.css', array( 'dashicons' ), SFL_VERSION );
				// Add inline style.
				self::add_inline_style();
			}
		}


		/**
		 * Add Inline style.
		 *
		 * @since 1.0
		 */
		public static function add_inline_style() {
			$contents = get_option( 'sfl_advanced_custom_css', '' );

			if ( ! $contents ) {
				return;
			}

			// Add custom css as inline style.
			wp_add_inline_style( 'sfl_list_styles', $contents );
		}
	}

	SFL_Frontend_Assets::init();
}
