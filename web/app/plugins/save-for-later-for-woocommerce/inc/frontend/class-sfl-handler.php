<?php
/**
 * Save For Later Handling
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Handler' ) ) {
	/**
	 * Main SFL_Handler Class.
	 * */
	class SFL_Handler {
		/**
		 * Class Initialization.
		 *
		 * @since 1.0
		 */
		public static function init() {
			$sfl_list_position = get_option( 'sfl_general_sfl_table_position', 'woocommerce_after_cart' );

			add_filter( 'woocommerce_cart_item_name', array( __CLASS__, 'display_sfl_link' ), 10, 3 );
			add_action( 'woocommerce_before_cart', array( __CLASS__, 'display_sfl_notice' ) );
			add_action( $sfl_list_position, array( __CLASS__, 'display_sfl_list' ) );
			add_action( 'woocommerce_cart_is_empty', array( __CLASS__, 'display_sfl_list' ), 99 );
			add_action( 'sfl_before_product_list_table', array( __CLASS__, 'sfl_list_bulk_action_field' ) );

			// Display SFL Table Under WC Cart Blocks.
			add_action( 'render_block_woocommerce/cart', array( __CLASS__, 'display_sfl_list_block' ), 10, 2 );
		}

		/**
		 * Show the save for later list after the cart block
		 *
		 * @since 3.8.0
		 * @param String $content The content.
		 * @param Array  $parsed_block The block.
		 * @return mixed
		 */
		public static function display_sfl_list_block( $content, $parsed_block ) {
			ob_start();

			if ( is_user_logged_in() ) {
				if ( 'yes' === get_option( 'sfl_general_enable_sfl' ) ) {
					$sfl_ids = sfl_get_users_list( get_current_user_id() );

					if ( sfl_check_is_array( $sfl_ids ) ) {
						self::display_logged_in_users_table();
					}
				}
			} elseif ( 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) ) {
				$sfl_ids = sfl_get_cookie_data( 'sfl_list_entry' );

				if ( sfl_check_is_array( $sfl_ids ) ) {
					self::display_guest_users_table();
				}
			}

			$template = ob_get_clean();

			return $content . $template;
		}

		/**
		 * Display SFL List Bulk Action Field
		 *
		 * @since 3.0
		 */
		public static function sfl_list_bulk_action_field() {
			$args['bulk_actions'] = array(
				''                => esc_html__( 'Bulk actions', 'save-for-later-for-woocommerce' ),
				'sfl_add_to_cart' => esc_html__( 'Move to cart', 'save-for-later-for-woocommerce' ),
				'sfl_remove'      => esc_html__( 'Remove', 'save-for-later-for-woocommerce' ),
			);

			sfl_get_template( 'sfl-list-filter.php', $args );
		}

		/**
		 * Display SFL Notice in cart.
		 *
		 * @since 1.0
		 */
		public static function display_sfl_notice() {
			if ( isset( $_REQUEST['sfl_status'] ) ) {
				if ( 'success' === $_REQUEST['sfl_status'] ) {
					wc_add_notice( get_option( 'sfl_messages_sfl_cart_to_list_msg' ) );
				}

				if ( 'error' === $_REQUEST['sfl_status'] ) {
					wc_add_notice( 'Something Went Wrong', 'error' );
				}
			}
		}

		/**
		 * Display SFL option in cart.
		 *
		 * @since 1.0
		 * @param string $cart_item_name Cart Item Name.
		 * @param Array  $cart_item Cart Item.
		 * @param string $cart_item_key Cart Item Key.
		 */
		public static function display_sfl_link( $cart_item_name, $cart_item, $cart_item_key ) {
			if ( ! is_cart() ) {
				return $cart_item_name;
			}

			if ( ! SFL_Restriction_Handler::is_valid_for_sfl( $cart_item_key ) ) {
				return $cart_item_name;
			}

			if ( ( is_user_logged_in() && 'yes' === get_option( 'sfl_general_enable_sfl' ) ) || ( ! is_user_logged_in() && 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) ) ) {
				$save_for_later_url = sfl_get_args_added_url(
					sfl_get_cart_url(),
					array(
						'sfl_product_id'    => sfl_get_product_variation_id( $cart_item ),
						'sfl_action'        => 'sfl_list_add',
						'sfl_cart_item_key' => $cart_item_key,
						'sfl_nonce'         => wp_create_nonce( 'sfl-list-add' ),
					)
				);

				return $cart_item_name . '<br>' . sprintf( '<a class="sfl_cart_link" href="%s">%s</a>', esc_url( $save_for_later_url ), get_option( 'sfl_messages_sfl_btn_text' ) );
			}

			return $cart_item_name;
		}

		/**
		 * Display SFL table in cart/Shortcode.
		 *
		 * @since 1.0.0
		 */
		public static function display_sfl_list() {
			if ( is_user_logged_in() ) {
				if ( 'yes' === get_option( 'sfl_general_enable_sfl' ) ) {
					self::display_logged_in_users_table();
				}
			} elseif ( 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) ) {
					self::display_guest_users_table();
			}
		}

		/**
		 * Display SFL Guest Users table in cart/Shortcode.
		 *
		 * @since 1.0
		 */
		public static function display_guest_users_table() {
			$sfl_ids = sfl_get_cookie_data( 'sfl_list_entry' );

			if ( ! sfl_check_is_array( $sfl_ids ) ) {
				return;
			}

			$per_page                       = sfl_pagination_count();
			$per_page                       = $per_page ? $per_page : count( $sfl_ids );
			$current_page                   = 1;
			$default_args['posts_per_page'] = $per_page;
			$default_args['offset']         = ( $current_page - 1 ) * $per_page;
			$page_count                     = ceil( count( $sfl_ids ) / $per_page );
			$data_args                      = array(
				'sfl_ids'    => array_slice( $sfl_ids, $default_args['offset'], $per_page ),
				'pagination' => array(
					'page_count'      => $page_count,
					'current_page'    => $current_page,
					'next_page_count' => ( ( $current_page + 1 ) > ( $page_count - 1 ) ) ? ( $current_page ) : ( $current_page + 1 ),
				),
			);

			sfl_get_template( 'sfl-list-guest.php', $data_args );
		}

		/**
		 * Display SFL Logged in Users table in cart/Shortcode.
		 *
		 * @since 1.0
		 */
		public static function display_logged_in_users_table() {
			$sfl_ids = sfl_get_users_list( get_current_user_id() );

			if ( ! $sfl_ids ) {
				return;
			}

			$per_page                       = sfl_pagination_count();
			$per_page                       = $per_page ? $per_page : count( $sfl_ids );
			$current_page                   = 1;
			$default_args['posts_per_page'] = $per_page;
			$default_args['offset']         = ( $current_page - 1 ) * $per_page;
			$page_count                     = ceil( count( $sfl_ids ) / $per_page );
			$data_args                      = array(
				'sfl_ids'    => array_slice( $sfl_ids, $default_args['offset'], $per_page ),
				'pagination' => array(
					'page_count'      => $page_count,
					'current_page'    => $current_page,
					'next_page_count' => ( ( $current_page + 1 ) > ( $page_count - 1 ) ) ? ( $current_page ) : ( $current_page + 1 ),
				),
			);

			sfl_get_template( 'sfl-list.php', $data_args );
		}
	}

	SFL_Handler::init();
}
