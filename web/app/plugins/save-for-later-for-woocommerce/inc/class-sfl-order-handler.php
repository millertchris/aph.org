<?php

/**
 * SFL Order Handler
 *
 * @package Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Order_Handler' ) ) {

	/**
	 * Main SFL_Order_Handler Class.
	 * */
	class SFL_Order_Handler {

		/**
		 * Class Initialize function.
		 *
		 * @since 1.0
		 * */
		public static function init() {

			// Create the save for later product order line item.
			add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'create_save_for_later_order_line_item' ), 10, 4 );

			// Hide the order item meta keys.
			add_action( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'hide_order_item_meta_key' ), 10, 2 );

			// Update order meta.
			add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'remove_cookie_val' ) );

			$order_statuses = array( 'cancelled', 'refunded', 'failed' );

			foreach ( $order_statuses as $order_status ) {
				add_action( 'woocommerce_order_status_' . $order_status, array( __CLASS__, 'process_order_fail_action' ), 10, 1 );
			}
		}

		/**
		 * Create the save for later line order item.
		 *
		 * @since 3.5.0
		 * @param Array  $item Order Item.
		 * @param String $cart_item_key Cart Item Key.
		 * @param Array  $values Order Item Values.
		 * @param Object $order Order Object.
		 */
		public static function create_save_for_later_order_line_item( $item, $cart_item_key, $values, $order ) {
			if ( ! isset( $values['sfl_product'] ) ) {
				return;
			}

			$save_for = $values['sfl_product'];

			if ( ! sfl_check_is_array( $save_for ) ) {
				return;
			}

			$save_for = array(
				'_sfl_product_id' => $save_for['product_id'],
				'_sfl_post_id'    => $save_for['sfl_post_id'],
				'_sflproduct'     => 'Yes',
			);

			/**
			 * This hook is used to alter the save for later order item data.
			 *
			 * @since 3.5.0
			 * @param Array $save_for
			 * @param String $cart_item_key
			 * @param Array $item
			 * @param Object $order
			 * @param Array $values
			 */
			$order_item_data = apply_filters( 'sfl_save_for_later_order_item_data', $save_for, $item, $cart_item_key, $values, $order );

			if ( sfl_check_is_array( $order_item_data ) ) {
				foreach ( $order_item_data as $key => $value ) {
					// Update the order item meta.
					$item->add_meta_data( $key, $value );
				}
			}
		}

		/**
		 * Hidden the custom order item meta.
		 *
		 * @since 3.5.0
		 * @param Array $hidden_order_itemmeta
		 * @return Array
		 */
		public static function hide_order_item_meta_key( $hidden_order_itemmeta ) {
			return array_merge( $hidden_order_itemmeta, array( '_sfl_product_id', '_sfl_post_id', '_sflproduct' ) );
		}


		/**
		 * Remove SFL cart item data when complete the checkout.
		 *
		 * @since 1.0
		 * @param Integer $order_id Order ID.
		 */
		public static function remove_cookie_val( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! is_object( $order ) ) {
				return;
			}

			$get_cookie_ids = sfl_get_cookie_data( 'sfl_list_entry' );

			if ( ! sfl_check_is_array( $get_cookie_ids ) ) {
				return;
			}

			foreach ( $order->get_items() as $item_id => $value ) {
				$product_id  = ( isset( $value['variation_id'] ) && ! empty( $value['variation_id'] ) ) ? $value['variation_id'] : $value['product_id'];
				$cookie_data = sfl_get_cookie_data_by_id( $product_id, 'sfl_list_entry' );

				if ( isset( $get_cookie_ids[ $product_id ] ) && ! empty( sfl_get_cookie_data_by_key( 'sfl_cart_item_key', $cookie_data ) ) ) {
					unset( $get_cookie_ids[ $product_id ] );
				}
			}

			sfl_set_cookie_data( 'sfl_list_entry', $get_cookie_ids );
		}

		/**
		 * Process Failed Order.
		 *
		 * @since 1.0
		 * @param Integer $order_id Order ID.
		 */
		public static function process_order_fail_action( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( $order->get_meta( 'sfl_order_fail_action' ) ) {
				return;
			}

			$get_sfl_data = get_posts(
				array(
					'post_status' => 'sfl_purchased',
					'meta_key'    => 'sfl_order_id',
					'meta_value'  => $order_id,
					'post_type'   => SFL_Register_Post_Types::SFL_POSTTYPE,
					'fields'      => -1,
				)
			);

			if ( sfl_check_is_array( $get_sfl_data ) ) {
				return;
			}

			foreach ( $get_sfl_data as $slf_id ) {
				// Handle Cancelled count reduce from purchase.
				$parent_meta_args                        = array();
				$sfl_data                                = sfl_get_entry( $slf_id );
				$sfl_parent_data                         = sfl_get_entry( $sfl_data->post_parent );
				$parent_meta_args['sfl_purchased_count'] = $sfl_parent_data->get_purchased_count() - 1;

				// Update Purchased entry.
				sfl_update_entry( $sfl_data->post_parent, $parent_meta_args );
				// Removed Purchased entry.
				sfl_delete_entry( $slf_id );
			}

			$order->update_meta_data( 'sfl_order_fail_action', true );
			$order->save();
		}
	}

	SFL_Order_Handler::init();
}
