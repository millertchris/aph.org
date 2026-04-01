<?php
/**
 * Admin Ajax.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Admin_Ajax' ) ) {

	/**
	 * SFL_Admin_Ajax Class.
	 */
	class SFL_Admin_Ajax {

		/**
		 * SFL_Admin_Ajax Class initialization.
		 *
		 * @since 1.0
		 */
		public static function init() {

			$actions = array(
				'product_search'   => false,
				'customers_search' => false,
				'list_pagination'  => true,
			);

			foreach ( $actions as $action => $nopriv ) {
				add_action( 'wp_ajax_sfl_' . $action, array( __CLASS__, $action ) );

				if ( $nopriv ) {
					add_action( 'wp_ajax_nopriv_sfl_' . $action, array( __CLASS__, $action ) );
				}
			}
		}

		/**
		 * Product search.
		 *
		 * @since 1.0
		 */
		public static function product_search() {
			check_ajax_referer( 'sfl-search-nonce', 'sfl_security' );

			try {
				$term = isset( $_GET['term'] ) ? (string) wc_clean( wp_unslash( $_GET['term'] ) ) : '';

				if ( empty( $term ) ) {
					throw new exception( esc_html__( 'No Product(s) found', 'save-for-later-for-woocommerce' ) );
				}

				$data_store = WC_Data_Store::load( 'product' );
				$ids        = $data_store->search_products( $term, '', false );

				$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
				$products        = array();

				foreach ( $product_objects as $product_object ) {
					if ( $product_object->is_type( 'simple' ) ) {
						$products[ $product_object->get_id() ] = rawurldecode( $product_object->get_formatted_name() );
					}
				}
				wp_send_json( $products );
			} catch ( Exception $ex ) {
				wp_die();
			}
		}

		/**
		 * Customer search.
		 *
		 * @since 1.0
		 */
		public static function customers_search() {
			check_ajax_referer( 'sfl-search-nonce', 'sfl_security' );

			try {
				$term = isset( $_GET['term'] ) ? (string) wc_clean( wp_unslash( $_GET['term'] ) ) : '';

				if ( empty( $term ) ) {
					throw new exception( esc_html__( 'No Customer(s) found', 'save-for-later-for-woocommerce' ) );
				}

				$exclude         = isset( $_GET['exclude'] ) ? (string) wc_clean( wp_unslash( $_GET['exclude'] ) ) : '';
				$exclude         = ! empty( $exclude ) ? array_map( 'intval', explode( ',', $exclude ) ) : array();
				$found_customers = array();
				$customers_query = new WP_User_Query(
					array(
						'fields'         => 'all',
						'orderby'        => 'display_name',
						'search'         => '*' . $term . '*',
						'search_columns' => array( 'ID', 'user_login', 'user_email', 'user_nicename' ),
					)
				);
				$customers       = $customers_query->get_results();

				if ( sfl_check_is_array( $customers ) ) {
					foreach ( $customers as $customer ) {
						if ( ! in_array( $customer->ID, $exclude ) ) {
							$found_customers[ $customer->ID ] = $customer->display_name . ' (#' . $customer->ID . ' &ndash; ' . sanitize_email( $customer->user_email ) . ')';
						}
					}
				}

				wp_send_json( $found_customers );
			} catch ( Exception $ex ) {
				wp_die();
			}
		}

		/**
		 * Display SFL list based on pagination.
		 *
		 * @since 1.0
		 */
		public static function list_pagination() {
			check_ajax_referer( 'sfl-list-pagination', 'sfl_security' );

			try {
				if ( ! isset ( $_POST ) || ! isset ( $_POST[ 'page_number' ] ) ) { // @codingStandardsIgnoreLine.
					throw new exception( esc_html__( 'Invalid Request', 'save-for-later-for-woocommerce' ) );
				}

				$current_page    = ! empty ( $_POST[ 'page_number' ] ) ? absint ( $_POST[ 'page_number' ] ) : 0 ; // @codingStandardsIgnoreLine.
				$page_url        = ! empty ( $_POST[ 'page_url' ] ) ? wc_clean ( wp_unslash ( $_POST[ 'page_url' ] ) ) : '' ; // @codingStandardsIgnoreLine.
				$per_page     = sfl_pagination_count();
				$offset       = ( $current_page - 1 ) * $per_page;
				$user_id      = isset( $_REQUEST['current_user'] ) ? wc_clean( wp_unslash( $_REQUEST['current_user'] ) ) : '';

				// Get gift products based on per page count.
				if ( ! empty( $user_id ) ) {
					$sfl_ids = sfl_get_users_list( $user_id );
					$sfl_ids = array_slice( $sfl_ids, $offset, $per_page );

					// Get gift products table body content.
					$html = sfl_get_template_html(
						'sfl-list-data.php',
						array(
							'sfl_ids'     => $sfl_ids,
							'permalink'   => esc_url( $page_url ),
							'page_number' => $current_page,
						)
					);
				} else {
					$sfl_ids = sfl_get_cookie_data( 'sfl_list_entry' );
					$sfl_ids = array_slice( $sfl_ids, $offset, $per_page );
					$html    = sfl_get_template_html(
						'sfl-list-data-guest.php',
						array(
							'sfl_ids'     => $sfl_ids,
							'permalink'   => esc_url( $page_url ),
							'page_number' => $current_page,
						)
					);
				}

				wp_send_json_success( array( 'html' => $html ) );
			} catch ( Exception $ex ) {
				wp_send_json_error( array( 'error' => $ex->getMessage() ) );
			}
		}
	}

	SFL_Admin_Ajax::init();
}
