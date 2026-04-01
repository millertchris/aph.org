<?php
/**
 * Save For Later Actions Handling
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Actions_Handler' ) ) {

	/**
	 * Main SFL_Actions_Handler Class.
	 *
	 * @since 1.0
	 * */
	class SFL_Actions_Handler {

		/**
		 * Get the cached Product ID which is in progress.
		 *
		 * @var int
		 */
		protected static $sfl_in_progress;

		/**
		 * Class Init Function
		 *
		 * @since 1.0.0
		 */
		public static function init() {
			add_action( 'template_redirect', array( __CLASS__, 'process_sfl_link' ) );
			add_action( 'template_redirect', array( __CLASS__, 'process_sfl_table_actions' ) );
			add_action( 'woocommerce_remove_cart_item', array( __CLASS__, 'remove_from_sfl_list_cart_item_remove' ), 10, 1 );

			$order_statuses = array( 'processing', 'completed' );

			foreach ( $order_statuses as $order_status ) {
				add_action( 'woocommerce_order_status_' . $order_status, array( __CLASS__, 'process_sfl_contain_order' ), 10, 1 );
			}
		}

		/**
		 * Removing Entry from sfl list while cart item remove.
		 *
		 * @since 1.0
		 * @param string $cart_item_key Cart Item Key.
		 */
		public static function remove_from_sfl_list_cart_item_remove( $cart_item_key ) {
			$cart_contents = WC()->cart->get_cart();

			if ( ! isset( $cart_contents[ $cart_item_key ] ) ) {
				return;
			}

			$cart_content_data = $cart_contents[ $cart_item_key ];

			if ( ! isset( $cart_content_data['sfl_product'] ) ) {
				return;
			}

			$sfl_data = $cart_content_data['sfl_product'];

			if ( is_user_logged_in() ) {
				self::remove_from_sfl_list( $sfl_data['sfl_post_id'] );
			} elseif ( ! is_user_logged_in() && 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) ) {
				sfl_remove_cookie_entry_by_id( $sfl_data['sfl_post_id'] );
			}

			wp_safe_redirect( get_permalink() );
		}

		/**
		 * Process SFL Contain Order.
		 *
		 * @since 3.5.0
		 * @param integer $order_id Order ID.
		 */
		public static function process_sfl_contain_order( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! is_object( $order ) || $order->get_meta( 'sfl_processed_already', true ) ) {
				return;
			}

			$items = $order->get_items();

			foreach ( $items as $item ) {
				if ( ! $item->meta_exists( '_sfl_post_id' ) ) {
					continue;
				}

				$post_id    = $item->get_meta( '_sfl_post_id' );
				$product_id = $item->get_meta( '_sfl_product_id' );

				sfl_create_child_entry( $post_id, 'sfl_purchased', array( 'sfl_order_id' => $order_id ) );

				if ( ! $order->get_customer_id() ) {
					$saved_count = get_post_meta( $product_id, 'sfl_saved', true ) ? get_post_meta( $product_id, 'sfl_saved', true ) : 0;

					update_post_meta( $product_id, 'sfl_saved', $saved_count - 1 );

					// Increase Deleted Count.
					$deleted_count = get_post_meta( $product_id, 'sfl_purchased', true ) ? get_post_meta( $product_id, 'sfl_purchased', true ) : 0;
					update_post_meta( $product_id, 'sfl_deleted', $deleted_count + 1 );
				}
			}

			$order->update_meta_data( 'sfl_processed_already', 'yes' );
			$order->save();
		}

		/**
		 * Process SFL Link Actions.
		 *
		 * @since 1.0.0
		 */
		public static function process_sfl_link() {
			$nonce_value = isset( $_REQUEST['sfl_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sfl_nonce'] ) ) : '';

			if ( ! wp_verify_nonce( $nonce_value, 'sfl-list-add' ) ) {
				return;
			}

			$product_id = isset( $_REQUEST['sfl_product_id'] ) ? wc_clean( wp_unslash( $_REQUEST['sfl_product_id'] ) ) : '';

			if ( empty( $product_id ) || self::$sfl_in_progress === $product_id ) {
				return;
			}

			$sfl_cart_item_key = isset( $_REQUEST['sfl_cart_item_key'] ) ? wc_clean( wp_unslash( $_REQUEST['sfl_cart_item_key'] ) ) : '';

			if ( empty( $sfl_cart_item_key ) ) {
				return;
			}

			self::$sfl_in_progress = $product_id;
			$action                = isset( $_REQUEST['sfl_action'] ) ? wc_clean( wp_unslash( $_REQUEST['sfl_action'] ) ) : '';

			if ( empty( $action ) || 'sfl_list_add' !== $action ) {
				return;
			}

			// Preparing Basic Data.
			$sfl_args = array(
				'sfl_product_id'    => $product_id,
				'sfl_activity_date' => current_time( 'mysql', true ),
			);

			// Preparing Cart Data.
			$cart_contents = WC()->cart->get_cart();

			if ( ! sfl_check_is_array( $cart_contents ) ) {
				return;
			}

			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				if ( $sfl_cart_item_key !== $cart_item_key ) {
					continue;
				}

				$product_obj      = wc_get_product( $product_id );
				$cart_schema_keys = array( 'key', 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax' );
				/**
				 * List Data Product Price Hook
				 *
				 * @since 3.8.0
				 */
				$sfl_args['sfl_product_price'] = apply_filters( 'sfl_list_data_product_price', sfl_get_tax_based_price( $product_id ), $product_obj, $cart_item, $cart_item_key );
				$sfl_args['sfl_product_qty']   = $cart_item['quantity'];
				$sfl_args['sfl_cart_item']     = $cart_item;

				remove_action( 'woocommerce_remove_cart_item', array( 'SFL_Actions_Handler', 'remove_from_sfl_list_cart_item_remove' ), 10, 1 );

				WC()->cart->remove_cart_item( $cart_item_key );
			}

			// Perform SFL Data Entry.
			$status = 'error';

			if ( is_user_logged_in() ) {
				self::sfl_perform_create_entry( $sfl_args, $product_id );

				$status = 'success';
			} elseif ( ! is_user_logged_in() && 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) ) {
				self::sfl_perform_create_entry_guest( $sfl_args, $product_id );

				$status = 'success';
			}

			self::$sfl_in_progress = false;

			$prepare_url = add_query_arg( array( 'sfl_status' => $status ), get_permalink() );

			wp_safe_redirect( $prepare_url );
			exit();
		}

		/**
		 * Creating session for entry.
		 *
		 * @since 1.0
		 * @param Array   $sfl_args SFL Arguments.
		 * @param Integer $product_id Product ID.
		 */
		public static function sfl_perform_create_entry_guest( $sfl_args, $product_id ) {
			$sfl_list                = sfl_get_cookie_data( 'sfl_list_entry' );
			$sfl_list[ $product_id ] = $sfl_args;

			sfl_set_cookie_data( 'sfl_list_entry', $sfl_list );

			$count_data = get_post_meta( $product_id, 'sfl_saved', true ) ? get_post_meta( $product_id, 'sfl_saved', true ) : 0;

			update_post_meta( $product_id, 'sfl_saved', $count_data + 1 );
		}

		/**
		 * Creating Child Entry of SFL Link Actions.
		 *
		 * @since 1.0
		 * @param Array   $sfl_args SFL Arguments.
		 * @param Integer $product_id Product ID.
		 */
		public static function sfl_perform_create_entry( $sfl_args, $product_id ) {
			// Performing Data Insert.
			$bool          = false;
			$sfl_parent_id = sfl_is_user_having_list( get_current_user_id() );
			$saved_id      = '';

			if ( $sfl_parent_id ) {
				// is product already in SFL cart.
				$sfl_existed_id = sfl_is_product_already_in_list( get_current_user_id(), $product_id );

				if ( ! $sfl_existed_id ) {
					// Child Post Entry.
					$saved_id = self::create_sfl_child_post_entry( $sfl_parent_id, $sfl_args );
				} else {
					sfl_update_entry( $sfl_existed_id, $sfl_args );
				}
			} else {
				// Parent Post Entry.
				$parent_post_args = array(
					'post_author' => get_current_user_id(),
					'post_status' => 'publish',
				);

				$sfl_parent_id = sfl_create_entry( array(), $parent_post_args );
				// Child Post Entry.
				$saved_id = self::create_sfl_child_post_entry( $sfl_parent_id, $sfl_args );
			}

			// Update Parent Post Count.
			$parent_meta_args                      = array();
			$sfl_data                              = sfl_get_entry( $sfl_parent_id );
			$parent_meta_args['sfl_activity_date'] = current_time( 'mysql', true );

			if ( $saved_id ) {
				$parent_meta_args['sfl_saved_count'] = $sfl_data->get_saved_count() + 1;
			}

			sfl_update_entry( $sfl_parent_id, $parent_meta_args );

			return $saved_id;
		}

		/**
		 * Create Temporary Record.
		 *
		 * @since 1.0
		 * @param Integer $sfl_parent_id SFL Parent ID.
		 * @param Array   $sfl_args SFL Arguments.
		 */
		public static function create_sfl_child_post_entry( $sfl_parent_id, $sfl_args ) {
			$post_args = array(
				'post_parent' => $sfl_parent_id,
				'post_author' => get_current_user_id(),
				'post_status' => 'sfl_saved',
			);

			$saved_id = sfl_create_entry( $sfl_args, $post_args );

			// For Permanent Record.
			sfl_create_log_entry( $sfl_args, $post_args );

			// For Temporary Record.
			$saved_post_args = array(
				'post_parent' => $sfl_parent_id,
				'post_author' => get_current_user_id(),
				'post_status' => 'sfl_current_saved',
			);

			$currently_saved_id = sfl_create_log_entry( $sfl_args, $saved_post_args );

			update_post_meta( $saved_id, 'currently_saved_id', $currently_saved_id );

			return $saved_id;
		}

		/**
		 * Process SFL Table Actions.
		 *
		 * @since 1.0
		 */
		public static function process_sfl_table_actions() {
			$ids = isset( $_REQUEST['sfl_post_id'] ) ? wc_clean( wp_unslash( $_REQUEST['sfl_post_id'] ) ) : array();
			$ids = ! is_array( $ids ) ? explode( ',', $ids ) : $ids;

			if ( ! sfl_check_is_array( $ids ) ) {
				return;
			}

			$action = isset( $_REQUEST['sfl_action'] ) ? wc_clean( wp_unslash( $_REQUEST['sfl_action'] ) ) : '';

			if ( empty( $action ) ) {
				return;
			}

			$type        = count( $ids ) > 1 ? 'bulk' : 'single';
			$return      = false;
			$success_ids = array();

			foreach ( $ids as $id ) {
				switch ( $action ) {
					case 'sfl_add_to_cart':
						$return = self::add_to_cart_from_sfl_list( $id, $type );
						break;
					case 'sfl_remove':
						$return = self::remove_from_sfl_list( $id, $type );
						break;
				}

				if ( true === $return ) {
					$success_ids[] = $id;
				}
			}

			if ( 'bulk' === $type ) {
				if ( ! is_user_logged_in() && 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) ) {
					$get_cookie_ids = sfl_get_cookie_data( 'sfl_list_entry' );

					foreach ( $success_ids as $id ) {
						if ( isset( $get_cookie_ids[ $id ] ) ) {
							unset( $get_cookie_ids[ $id ] );
						}
					}

					sfl_set_cookie_data( 'sfl_list_entry', $get_cookie_ids );
				}

				if ( 'sfl_add_to_cart' === $action ) {
					wc_add_notice( get_option( 'sfl_messages_sfl_list_to_cart_msg_bulk', 'You have successfully moved the products to the cart from your Saved for Later list.' ), 'success' );
				}

				if ( 'sfl_remove' === $action ) {
					wc_add_notice( get_option( 'sfl_messages_sfl_cart_remove_msg_bulk', 'You have successfully deleted the products from your Saved Later list.' ), 'success' );
				}
			}

			wp_safe_redirect( get_permalink() );
			exit();
		}

		/**
		 * Process Add to cart to SFL list Actions.
		 *
		 * @since 1.0
		 * @param Integer $sfl_post_id SFL Post ID.
		 * @param String  $type Action Type.
		 */
		public static function add_to_cart_from_sfl_list( $sfl_post_id, $type ) {
			// cart exist check.
			$cart_contents = WC()->cart->get_cart();

			if ( is_user_logged_in() ) {
				$sfl_data       = sfl_get_entry( $sfl_post_id );
				$sfl_product_id = $sfl_data->get_product_id();
			} else {
				$sfl_data       = sfl_get_cookie_data_by_id( $sfl_post_id, 'sfl_list_entry' );
				$sfl_product_id = isset( $sfl_data['sfl_product_id'] ) ? $sfl_data['sfl_product_id'] : '';
			}

			if ( empty( $sfl_product_id ) ) {
				return;
			}

			if ( sfl_check_is_array( $cart_contents ) ) {
				foreach ( $cart_contents as $cart_item ) {
					$product_id  = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
					$product_obj = wc_get_product( $product_id );
					/**
					 * Check Product already exists
					 *
					 * @since 3.8.0
					 */
					if ( (int) $product_id === (int) $sfl_product_id && apply_filters( 'sfl_check_product_already_in_cart', true, $product_obj ) ) {
						if ( self::remove_from_sfl_list_handle( $sfl_post_id ) ) {
							wc_add_notice( get_option( 'sfl_messages_sfl_cart_exist_remove_msg' ), 'error' );
						}
						return;
					}
				}
			}

			if ( is_user_logged_in() ) {
				return self::add_to_cart_user( $sfl_post_id, $type );
			} elseif ( 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) ) {
				return self::add_to_cart_guest( $sfl_post_id, $type );
			}
		}

		/**
		 * Process Add to cart to SFL list Actions to user.
		 *
		 * @since 1.0
		 * @param Integer $sfl_post_id SFL Post ID.
		 * @param String  $type Action Type.
		 */
		public static function add_to_cart_user( $sfl_post_id, $type ) {
			$sfl_data       = sfl_get_entry( $sfl_post_id );
			$sfl_product_id = $sfl_data->get_product_id();
			$cart_item_data = array();

			if ( sfl_check_is_array( $sfl_data->get_cart_item() ) ) {
				$cart_schema_keys = array( 'key', 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax' );
				$cart_item_data  += array_diff_key( $sfl_data->get_cart_item(), array_flip( $cart_schema_keys ) );
			}

			$cart_item_data['sfl_product'] = array(
				'product_id'   => $sfl_product_id,
				'sfl_post_id'  => $sfl_post_id,
				'sfl_purchase' => 'yes',
			);

			$add_to_cart_qty = sfl_get_add_to_cart_qty( $sfl_data ); // get add to cart qty.
			$cart_item_key   = WC()->cart->add_to_cart( $sfl_product_id, $add_to_cart_qty, 0, array(), $cart_item_data ); // Move to cart from SFL List.

			if ( $cart_item_key ) {
				sfl_update_entry( $sfl_post_id, array( 'sfl_cart_item_key' => $cart_item_key ) );
			}

			if ( 'single' === $type ) {
				if ( $cart_item_key ) {
					if ( sfl_is_stock_managing( $sfl_data->get_product_data() ) && $sfl_data->get_product_data()->get_stock_quantity() < $sfl_data->get_product_qty() ) {
						$calculated_qty = $sfl_data->get_product_qty() - $sfl_data->get_product_data()->get_stock_quantity();

						wc_add_notice( sprintf( str_replace( '[sfl_reduced_stock]', $calculated_qty, get_option( 'sfl_messages_sfl_qty_change_msg' ) ) ) );
						return;
					}

					wc_add_notice( get_option( 'sfl_messages_sfl_list_to_cart_msg' ), 'success' );
				} else {
					wc_add_notice( esc_html__( 'Something Went Wrong', 'save-for-later-for-woocommerce' ), 'error' );
					return false;
				}
			}

			return true;
		}

		/**
		 * Process Add to cart to SFL list Actions to guest.
		 *
		 * @since 1.0
		 * @param Integer $sfl_product_id SFL Product ID.
		 * @param String  $type Action Type.
		 */
		public static function add_to_cart_guest( $sfl_product_id, $type ) {
			$sfl_data       = sfl_get_cookie_data_by_id( $sfl_product_id, 'sfl_list_entry' );
			$cart_item_data = array(
				'sfl_product' => array(
					'product_id'   => $sfl_product_id,
					'sfl_purchase' => 'yes',
				),
			);

			if ( isset( $sfl_data['sfl_cart_item'] ) && sfl_check_is_array( $sfl_data['sfl_cart_item'] ) ) {
				$cart_schema_keys = array( 'key', 'product_id', 'variation_id', 'variation', 'quantity', 'data', 'data_hash', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax' );
				$cart_item_data   = array_merge( $cart_item_data, array_diff_key( $sfl_data['sfl_cart_item'], array_flip( $cart_schema_keys ) ) );
			}

			$add_to_cart_qty = sfl_get_add_to_cart_qty_guest( $sfl_product_id, $sfl_data ); // get add to cart qty.
			$cart_item_key   = WC()->cart->add_to_cart( $sfl_product_id, $add_to_cart_qty, 0, array(), $cart_item_data ); // Move to cart from SFL List.

			if ( $cart_item_key ) {
				// Cookie handling.
				$sfl_data ['sfl_cart_item_key'] = $cart_item_key;
				$get_cookie_ids                 = sfl_get_cookie_data( 'sfl_list_entry' );
				unset( $get_cookie_ids[ $sfl_product_id ] );

				$cookie_val = array();

				foreach ( $get_cookie_ids as $get_cookie_id => $val ) {
					$cookie_val[ $get_cookie_id ] = $val;
				}

				$cookie_val[ $sfl_product_id ] = $sfl_data;
				sfl_set_cookie_data( 'sfl_list_entry', $cookie_val );

				$product_data = wc_get_product( $sfl_product_id );
			}

			if ( 'single' === $type ) {
				if ( $cart_item_key ) {
					if ( sfl_is_stock_managing( $product_data ) && $product_data->get_stock_quantity() < sfl_get_cookie_data_by_key( 'sfl_product_qty', $cookie_data ) ) {
						wc_add_notice( get_option( 'sfl_messages_sfl_qty_change_msg' ), 'success' );
						return;
					}

					wc_add_notice( get_option( 'sfl_messages_sfl_list_to_cart_msg' ), 'success' ); // Success Notice.
				} else {
					wc_add_notice( esc_html__( 'Somthing Went Wrong', 'save-for-later-for-woocommerce' ), 'error' ); // Failure Notice.
					return false;
				}
			}

			return true;
		}

		/**
		 * Remove Entry From SFL list.
		 *
		 * @since 1.0
		 * @param Integer $sfl_post_id SFL Post ID.
		 * @param String  $type Action Type.
		 */
		public static function remove_from_sfl_list( $sfl_post_id, $type = 'single' ) {
			$return = self::remove_from_sfl_list_handle( $sfl_post_id );

			if ( 'single' === $type ) {
				if ( $return ) {
					wc_add_notice( get_option( 'sfl_messages_sfl_cart_remove_msg' ), 'success' ); // Success Notice.
				} else {
					wc_add_notice( esc_html__( 'Something Went Wrong', 'save-for-later-for-woocommerce' ), 'error' ); // Failure Notice.
				}
			}
		}

		/**
		 * Handle removing action for user/guest.
		 *
		 * @since 1.0
		 * @param Integer $sfl_post_id SFL Post ID.
		 */
		public static function remove_from_sfl_list_handle( $sfl_post_id ) {
			if ( is_user_logged_in() && sfl_create_child_entry( $sfl_post_id, 'sfl_deleted' ) ) {
				return true;
			}

			if ( ! is_user_logged_in() && 'yes' === get_option( 'sfl_general_enable_guest_sfl' ) && sfl_remove_cookie_entry_by_id( $sfl_post_id ) ) {
				// Reduce saved Count.
				$saved_count = get_post_meta( $sfl_post_id, 'sfl_saved', true ) ? get_post_meta( $sfl_post_id, 'sfl_saved', true ) : 0;

				update_post_meta( $sfl_post_id, 'sfl_saved', $saved_count - 1 );

				// Increase Deleted Count.
				$deleted_count = get_post_meta( $sfl_post_id, 'sfl_deleted', true ) ? get_post_meta( $sfl_post_id, 'sfl_deleted', true ) : 0;

				update_post_meta( $sfl_post_id, 'sfl_deleted', $deleted_count + 1 );

				return true;
			}

			return false;
		}
	}

	SFL_Actions_Handler::init();
}
