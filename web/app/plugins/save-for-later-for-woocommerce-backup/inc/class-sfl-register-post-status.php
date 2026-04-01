<?php
/**
 * Register Custom Post Status.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Register_Post_Status' ) ) {

	/**
	 * SFL_Register_Post_Status Class.
	 */
	class SFL_Register_Post_Status {

		/**
		 * Class initialization.
		 * 
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'register_custom_post_status' ) );
		}

		/**
		 * Register Custom Post Status.
		 * 
		 * @since 1.0
		 */
		public static function register_custom_post_status() {
			$custom_post_statuses = array(
				// for log difference
				'sfl_current_saved' => array( 'SFL_Register_Post_Status', 'log_current_saved_post_status_args' ),
				'sfl_saved'         => array( 'SFL_Register_Post_Status', 'saved_post_status_args' ),
				'sfl_purchased'     => array( 'SFL_Register_Post_Status', 'purchased_post_status_args' ),
				'sfl_deleted'       => array( 'SFL_Register_Post_Status', 'deleted_post_status_args' ),
			);

			/**
			 * Filter for SFL custom post status.
			 *
			 * @since 1.0
			 * */
			$custom_post_statuses = apply_filters( 'sfl_add_custom_post_status', $custom_post_statuses );

			// return if no post status to register.
			if ( ! sfl_check_is_array( $custom_post_statuses ) ) {
				return;
			}

			foreach ( $custom_post_statuses as $post_status => $args_function ) {
				$args = array();

				if ( $args_function ) {
					$args = call_user_func_array( $args_function, array() );
				}

				// Register post status.
				register_post_status( $post_status, $args );
			}
		}

		/**
		 * Saved Products Custom Log Current Saved Post Status arguments.
		 * 
		 * @since 1.0
		 */
		public static function log_current_saved_post_status_args() {
			/**
			 * Filter SFL current saved post status.
			 *
			 * @since 1.0
			 * */
			$args = apply_filters(
				'sfl_current_saved_post_status_args',
				array(
					'label'                     => esc_html_x( 'Log Currently Saved', 'save-for-later-for-woocommerce' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => false,
				)
			);

			return $args;
		}

		/**
		 * Saved Products Custom Post Status arguments.
		 * 
		 * @since 1.0
		 */
		public static function saved_post_status_args() {
			/**
			 * Filter SFL Saved post status.
			 *
			 * @since 1.0
			 * */
			$args = apply_filters(
				'sfl_saved_post_status_args',
				array(
					'label'                     => esc_html_x( 'Saved', 'save-for-later-for-woocommerce' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => false,
				)
			);

			return $args;
		}

		/**
		 * Purchased Products Custom Post Status arguments.
		 * 
		 * @since 1.0
		 */
		public static function purchased_post_status_args() {
			/**
			 * Filter SFL Purchased post status.
			 *
			 * @since 1.0
			 * */
			$args = apply_filters(
				'sfl_purchased_post_status_args',
				array(
					'label'                     => esc_html_x( 'Purchased', 'save-for-later-for-woocommerce' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => false,
				)
			);

			return $args;
		}

		/**
		 * Saved Deleted Custom Post Status arguments.
		 * 
		 * @since 1.0
		 */
		public static function deleted_post_status_args() {
			/**
			 * Filter SFL post delete.
			 *
			 * @since 1.0
			 * */
			$args = apply_filters(
				'sfl_deleted_post_status_args',
				array(
					'label'                     => esc_html_x( 'Deleted', 'save-for-later-for-woocommerce' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => false,
					'show_in_admin_status_list' => false,
				)
			);

			return $args;
		}
	}

	SFL_Register_Post_Status::init();
}
