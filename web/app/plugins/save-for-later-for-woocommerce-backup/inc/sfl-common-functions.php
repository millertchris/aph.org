<?php
/**
 * Common functions
 *
 * @package Function
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'sfl_check_is_array' ) ) {

	/**
	 * Check if resource is array.
	 *
	 * @since 1.0
	 * @return bool
	 */
	function sfl_check_is_array( $array ) {
		return ( is_array( $array ) && ! empty( $array ) );
	}
}

if ( ! function_exists( 'sfl_pagination_count' ) ) {
		/**
		 * Get the save for later table pagination count.
		 *
		 * @since 3.4.0
		 * @return int
		 */
	function sfl_pagination_count() {
		return get_option( 'sfl_table_pagination' );
	}
}

if ( ! function_exists( 'sfl_page_screen_ids' ) ) {

	/**
	 * Get page screen IDs.
	 *
	 * @return array
	 */
	function sfl_page_screen_ids() {
		/**
		 * Filter for Page Screen ID's
		 *
		 * @since 1.0
		 */
		return apply_filters(
			'sfl_page_screen_ids',
			array(
				'save-for-later_page_sfl_settings',
				'save-for-later_page_sfl_reports',
				SFL_Register_Post_Types::SFL_POSTTYPE,
			)
		);
	}
}

if ( ! function_exists( 'sfl_get_wc_categories' ) ) {

	/**
	 * Get WC Categories.
	 *
	 * @since 1.0
	 * @return array
	 */
	function sfl_get_wc_categories() {
		$categories    = array();
		$wc_categories = get_terms( 'product_cat' );

		if ( ! sfl_check_is_array( $wc_categories ) ) {
			return $categories;
		}

		foreach ( $wc_categories as $category ) {
			$categories[ $category->term_id ] = $category->name;
		}

		return $categories;
	}
}

if ( ! function_exists( 'sfl_get_user_roles' ) ) {

	/**
	 * Get WordPress User Roles.
	 *
	 * @since 1.0
	 * @return array
	 */
	function sfl_get_user_roles() {
		global $wp_roles;
		$user_roles = array();

		if ( ! isset( $wp_roles->roles ) || ! sfl_check_is_array( $wp_roles->roles ) ) {
			return $user_roles;
		}

		foreach ( $wp_roles->roles as $slug => $role ) {
			$user_roles[ $slug ] = $role['name'];
		}

		return $user_roles;
	}
}

if ( ! function_exists( 'sfl_get_settings_page_url' ) ) {

	/**
	 * Function to get event page URL
	 *
	 * @since 1.0
	 * @param Array $args Arguments.
	 * @return array
	 */
	function sfl_get_settings_page_url( $args = array() ) {

		$url = admin_url( 'admin.php?page=sfl_settings' );

		if ( sfl_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}
		return $url;
	}
}

if ( ! function_exists( 'sfl_get_product_variation_id' ) ) {

	/**
	 * Get product/variation id.
	 *
	 * @since 1.0
	 * @param Array $product Product.
	 * @return int
	 */
	function sfl_get_product_variation_id( $product ) {
		$product_id    = $product['product_id'];
		$whole_product = wc_get_product( $product_id );

		if ( is_object( $whole_product ) ) {
			$product_id = $whole_product->is_type( 'variable' ) ? $product['variation_id'] : $product['product_id'];
		}

		return $product_id;
	}
}

if ( ! function_exists( 'sfl_get_args_added_url' ) ) {

	/**
	 * Get product/variation id.
	 *
	 * @since 1.0
	 * @param String $url URL.
	 * @param Array  $args Arguments.
	 * @return int
	 */
	function sfl_get_args_added_url( $url, $args ) {

		return esc_url( add_query_arg( $args, $url ) );
	}
}

if ( ! function_exists( 'sfl_get_cart_url' ) ) {

	/**
	 * Function to get Cart page URL
	 *
	 * @since 1.0
	 * @return array
	 */
	function sfl_get_cart_url() {

		return function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : WC()->cart->get_cart_url();
	}
}

if ( ! function_exists( 'sfl_create_child_entry' ) ) {

	/**
	 * Function to Created Child Entry
	 *
	 * @since 1.0
	 * @param Integer $sfl_current_post_id SFL Post ID.
	 * @param String  $status status.
	 * @param Array   $additional_args SFL Data.
	 * @return bool
	 */
	function sfl_create_child_entry( $sfl_current_post_id, $status, $additional_args = array() ) {
		$sfl_data = sfl_get_entry( $sfl_current_post_id );

		$sfl_args = array(
			'sfl_product_id'    => $sfl_data->get_product_id(),
			'sfl_activity_date' => current_time( 'mysql', true ),
			'sfl_product_price' => $sfl_data->get_product_price(),
			'sfl_product_qty'   => $sfl_data->get_product_qty(),
			'sfl_cart_item'     => $sfl_data->get_cart_item(),
		);

		// Creating Child Post.
		$post_args = array(
			'post_parent' => $sfl_data->get_parent_id(),
			'post_author' => $sfl_data->get_user_id(),
			'post_status' => $status,
		);

		$sfl_args = sfl_check_is_array( $additional_args ) ? array_merge( $sfl_args, $additional_args ) : $sfl_args;

		sfl_create_entry( $sfl_args, $post_args );

		// Updating Count in Parent Post.
		$sfl_parent_data = sfl_get_entry( $sfl_data->get_parent_id() );

		$parent_meta_args = array(
			'sfl_saved_count'   => ( $sfl_parent_data->get_saved_count() - 1 ),
			'sfl_activity_date' => current_time( 'mysql', true ),
		);

		if ( 'sfl_purchased' === $status ) {
			$parent_meta_args['sfl_purchased_count'] = $sfl_parent_data->get_purchased_count() + 1;
		} else {
			$parent_meta_args['sfl_deleted_count'] = $sfl_parent_data->get_deleted_count() + 1;
		}

		sfl_update_entry( $sfl_data->get_parent_id(), $parent_meta_args );

		sfl_create_log_entry( $sfl_args, array( 'post_status' => $status ) );

		$temprory_post_id = get_post_meta( $sfl_current_post_id, 'currently_saved_id', true );

		if ( $temprory_post_id ) {
			sfl_delete_log_entry( $temprory_post_id );
		}

		// Deleting Current Post (Saved Status).
		return sfl_delete_entry( $sfl_current_post_id ) ? true : false;
	}
}

if ( ! function_exists( 'sfl_get_add_to_cart_qty' ) ) {

	/**
	 * Function to get add to cart qty
	 *
	 * @since 1.0
	 * @param Array $sfl_data SFL Data.
	 * @return Integer
	 */
	function sfl_get_add_to_cart_qty( $sfl_data ) {

		if ( sfl_is_stock_managing( $sfl_data->get_product_data() ) ) {
			return $sfl_data->get_product_data()->get_stock_quantity() < $sfl_data->get_product_qty() ? $sfl_data->get_product_data()->get_stock_quantity() : $sfl_data->get_product_qty();
		}
		return $sfl_data->get_product_qty();
	}
}


if ( ! function_exists( 'sfl_get_add_to_cart_qty_guest' ) ) {

	/**
	 * Function to get add to cart qty guest
	 *
	 * @since 1.0
	 * @param Integer $product_id Product Id.
	 * @param Array   $sfl_data SFL Data.
	 * @return Integer
	 */
	function sfl_get_add_to_cart_qty_guest( $product_id, $sfl_data ) {

		$product_data = wc_get_product( $product_id );

		if ( sfl_is_stock_managing( $product_data ) ) {
			return $product_data->get_stock_quantity() < sfl_get_cookie_data_by_key( 'sfl_product_qty', $sfl_data ) ? $product_data->get_stock_quantity() : sfl_get_cookie_data_by_key( 'sfl_product_qty', $sfl_data );
		}
		return sfl_get_cookie_data_by_key( 'sfl_product_qty', $sfl_data );
	}
}

if ( ! function_exists( 'sfl_is_stock_managing' ) ) {

	/**
	 * Function to checks product Managing the stocks
	 *
	 * @since 1.0
	 * @param Object $product_data Woocommerce Product Object.
	 * @return bool
	 */
	function sfl_is_stock_managing( $product_data ) {

		if ( $product_data->managing_stock() && ( $product_data->get_stock_quantity() ) ) {
			return true;
		} else {
			return false;
		}
	}
}


if ( ! function_exists( 'sfl_get_base_url' ) ) {

	/**
	 * Function to get base URL
	 *
	 * @since 1.0
	 * @return array
	 */
	function sfl_get_base_url() {
		global $post_id;

		return add_query_arg(
			array(
				array(
					'post'   => $post_id,
					'action' => 'edit',
				),
			),
			admin_url( 'post.php' )
		);
	}
}

if ( ! function_exists( 'sfl_set_cookie_data' ) ) {

	/**
	 * Function to set cookie data
	 *
	 * @since 1.0
	 * @param String  $name Cookie Name.
	 * @param String  $value Cookie Value.
	 * @param Integer $expiration Expiration.
	 * @return bool
	 */
	function sfl_set_cookie_data( $name, $value, $expiration = '' ) {
		$expiration = ! empty( $expiration ) ? $expiration : time() + sfl_get_cookie_expiration_time();
		$value      = json_encode( stripslashes_deep( $value ) );
		setcookie( $name, $value, $expiration, '/' );
	}
}

if ( ! function_exists( 'sfl_get_cookie_data' ) ) {

	/**
	 * Function to get cookie data
	 *
	 * @since 1.0
	 * @param String $name Cookie Name.
	 * @return array
	 */
	function sfl_get_cookie_data( $name ) {
		$sfl_ids   = array();
		$sfl_datas = array();

		if ( isset( $_COOKIE[ $name ] ) ) {
			$sfl_datas = json_decode( stripslashes( wc_clean( wp_unslash( $_COOKIE[ $name ] ) ) ), true );

			if ( ! sfl_check_is_array( $sfl_datas ) ) {
				return $sfl_ids;
			}

			foreach ( $sfl_datas as $product_id => $cookie_data ) {
				$sfl_data = sfl_get_cookie_data_by_id( $cookie_data['sfl_product_id'], 'sfl_list_entry' );

				if ( WC()->cart->find_product_in_cart( sfl_get_cookie_data_by_key( 'sfl_cart_item_key', $cookie_data ) ) ) {
					continue;
				}

				$sfl_ids[ $product_id ] = $cookie_data;
			}
		}

		return $sfl_ids;
	}
}

if ( ! function_exists( 'sfl_get_cookie_data_by_id' ) ) {

	/**
	 * Function to get cookie data
	 *
	 * @since 1.0
	 * @param Integer $id Cookie ID.
	 * @param String  $name Cookie Name.
	 * @return array
	 */
	function sfl_get_cookie_data_by_id( $id, $name ) {

		if ( isset( $_COOKIE[ $name ] ) ) {
			$cookie_data = json_decode( stripslashes( wc_clean( wp_unslash( $_COOKIE[ $name ] ) ) ), true );
			return isset( $cookie_data[ $id ] ) ? $cookie_data[ $id ] : '';
		}

		return '';
	}
}


if ( ! function_exists( 'sfl_unset_cookie_data' ) ) {

	/**
	 * Function to unset cookie data
	 *
	 * @since 1.0
	 * @param String $name Cookie Name.
	 * @return bool
	 */
	function sfl_unset_cookie_data( $name ) {
		return sfl_set_cookie_data( $name, array(), time() - 3600 );
	}
}

if ( ! function_exists( 'sfl_get_product_data' ) ) {

	/**
	 * Function to get product data
	 *
	 * @since 1.0
	 * @param String  $key Cookie Key.
	 * @param Integer $product_id Product ID.
	 * @param Array   $product_data Product Data.
	 * @param Array   $data Data.
	 * @return string/array
	 */
	function sfl_get_product_data( $key, $product_id, $product_data, $data ) {

		switch ( $key ) {
			case 'image':
				return sfl_render_product_image( $product_data );

			case 'name':
				return sfl_get_product_col_data( $product_data, $product_id, $data );

		}
	}
}


if ( ! function_exists( 'sfl_get_cookie_data_by_key' ) ) {

	/**
	 * Function to get product data by key
	 *
	 * @since 1.0
	 * @param String $key Cookie Key.
	 * @param Array  $cookie_data Cookie Data.
	 * @return string/integer
	 */
	function sfl_get_cookie_data_by_key( $key, $cookie_data ) {
		if ( ! sfl_check_is_array( $cookie_data ) || empty( $key ) ) {
			return;
		}

		return isset( $cookie_data[ $key ] ) ? $cookie_data[ $key ] : '';
	}
}

if ( ! function_exists( 'sfl_remove_cookie_entry_by_id' ) ) {

	/**
	 * Function to removed entry from cookie data
	 *
	 * @since 1.0
	 * @param Integer $product_id Product ID.
	 * @return string/integer
	 */
	function sfl_remove_cookie_entry_by_id( $product_id ) {
		$sfl_list = sfl_get_cookie_data( 'sfl_list_entry' );
		unset( $sfl_list[ $product_id ] );
		sfl_set_cookie_data( 'sfl_list_entry', $sfl_list );
		return true;
	}
}

if ( ! function_exists( 'sfl_get_cookie_expiration_time' ) ) {

	/**
	 * Function to get cookie expiration time
	 *
	 * @since 1.0
	 * @return string/integer
	 */
	function sfl_get_cookie_expiration_time() {
		$expire_input = get_option( 'sfl_general_guest_timeout' );
		$type         = get_option( 'sfl_general_guest_timeout_type', '1' );
		$expire_time  = '';
		if ( '1' == $type ) {
			$expire_time = $expire_input * 60;
		} elseif ( '2' == $type ) {
			$expire_time = $expire_input * 3600;
		} elseif ( '3' == $type ) {
			$expire_time = $expire_input * 86400;
		}

		return $expire_time;
	}
}

if ( ! function_exists( 'sfl_get_allowed_setting_tabs' ) ) {

	/**
	 * Get setting tabs
	 *
	 * @since 1.0
	 * @return array
	 */
	function sfl_get_allowed_setting_tabs() {
		/**
		 * Filter Settings Tabs
		 *
		 * @since 1.0
		 */
		return apply_filters( 'sfl_settings_tabs_array', array() );
	}
}

if ( ! function_exists( 'sfl_get_tax_based_price' ) ) {

	/**
	 * Get tax based price
	 *
	 * @since 1.0
	 * @param Integer $product_id Product ID.
	 * @return int/HTML
	 */
	function sfl_get_tax_based_price( $product_id ) {

		$product = wc_get_product( $product_id );

		if ( 'incl' == get_option( 'woocommerce_tax_display_cart' ) ) {
			$product_price = wc_get_price_including_tax( $product );
		} else {
			$product_price = wc_get_price_excluding_tax( $product );
		}

		/**
		 * Filter for Tax Price
		 *
		 * @since 1.0.0
		 */
		return $product_price;
	}
}

if ( ! function_exists( 'sfl_invalid_delete' ) ) {

	/**
	 * Invalid delete.
	 *
	 * @since 1.0
	 * @param Integer $item_id Item ID.
	 */
	function sfl_invalid_delete( $item_id ) {
		$item_data        = sfl_get_entry( $item_id );
		$parent_data      = sfl_get_entry( $item_data->get_parent_id() );
		$parent_meta_args = array(
			'sfl_saved_count'   => ( $parent_data->get_saved_count() - 1 ),
			'sfl_activity_date' => current_time( 'mysql', true ),
		);
		sfl_update_entry( $item_data->get_parent_id(), $parent_meta_args );
		sfl_delete_entry( $item_id );
	}
}

if ( ! function_exists( 'sfl_invalid_delete_guest' ) ) {

	/**
	 * Invalid delete Guest.
	 *
	 * @since 1.0
	 * @param Integer $sfl_product_id Product ID.
	 */
	function sfl_invalid_delete_guest( $sfl_product_id ) {
		$get_cookie_ids = sfl_get_cookie_data( 'sfl_list_entry' );
		unset( $get_cookie_ids[ $sfl_product_id ] );

		$cookie_val = array();

		foreach ( $get_cookie_ids as $get_cookie_id => $val ) {
			$cookie_val[ $get_cookie_id ] = $val;
		}

		sfl_set_cookie_data( 'sfl_list_entry', $cookie_val );
	}
}

if ( ! function_exists( 'sfl_get_filter_options' ) ) {

	/**
	 * Get Filter Options.
	 *
	 * @since 1.0
	 */
	function sfl_get_filter_options() {
		return array(
			'all_time'      => esc_html( 'All Time', 'save-for-later-for-woocommerce' ),
			'sfl_today'     => esc_html( 'Today', 'save-for-later-for-woocommerce' ),
			'sfl_yesterday' => esc_html( 'Yesterday', 'save-for-later-for-woocommerce' ),
			'this_week'     => esc_html( 'This Week', 'save-for-later-for-woocommerce' ),
			'last_week'     => esc_html( 'Last Week', 'save-for-later-for-woocommerce' ),
			'this_month'    => esc_html( 'This Month', 'save-for-later-for-woocommerce' ),
			'last_month'    => esc_html( 'Last Month', 'save-for-later-for-woocommerce' ),
			'this_year'     => esc_html( 'This Year', 'save-for-later-for-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'sfl_get_report_date_range' ) ) {

	/**
	 * Get Report Date Range.
	 *
	 * @since 1.0
	 * @param Array $filter_arg Report Arguments.
	 */
	function sfl_get_report_date_range( $filter_arg ) {

		switch ( $filter_arg ) {
			case 'all_time':
				return array(
					'from' => 'all_time',
					'to'   => 'all_time',
				);
				break;
			case 'sfl_today':
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( 'today', current_time( 'timestamp' ) ) ) . ' 00:00:00',
					'to'   => gmdate( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) ) . ' 23:59:59',
				);
				break;
			case 'sfl_yesterday':
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( '-1 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ) . ' 00:00:00',
					'to'   => gmdate( 'Y-m-d', strtotime( '-1 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ) . ' 23:59:59',
				);
				break;

			case 'this_week':
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ),
					'to'   => gmdate( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) ),
				);
				break;

			case 'this_week':
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ),
					'to'   => gmdate( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) ),
				);
				break;
			case 'last_week':
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( '-14 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ),
					'to'   => gmdate( 'Y-m-d', strtotime( '-6 days', strtotime( 'midnight', current_time( 'timestamp' ) ) ) ),
				);

				break;
			case 'this_month':
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) ) ),
					'to'   => gmdate( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) ),
				);
				break;
			case 'last_month':
				$first_day_current_month = strtotime( gmdate( 'Y-m-01', current_time( 'timestamp' ) ) );
				return array(
					'from' => gmdate( 'Y-m-01', strtotime( '-1 DAY', $first_day_current_month ) ),
					'to'   => gmdate( 'Y-m-t', strtotime( '-1 DAY', $first_day_current_month ) ),
				);
				break;
			case 'this_year':
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( gmdate( 'Y-01-01', current_time( 'timestamp' ) ) ) ),
					'to'   => gmdate( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) ),
				);
				break;
			default:
				return array(
					'from' => gmdate( 'Y-m-d', strtotime( gmdate( 'Y-01-01', current_time( 'timestamp' ) ) ) ),
					'to'   => gmdate( 'Y-m-d', strtotime( 'midnight', current_time( 'timestamp' ) ) ),
				);
				break;
		}
	}
}

if ( ! function_exists( 'sfl_get_product_thumbnail' ) ) {
	/**
	 * Get Product Thumbnail
	 *
	 * @since 2.8
	 * @param Object $product_obj Product Object.
	 * @return String.
	 */
	function sfl_get_product_thumbnail( $product_obj, $echo = true ) {
		$product_permalink = $product_obj->is_visible() ? $product_obj->get_permalink() : '';
		$thumbnail         = $product_obj->get_image();

		if ( empty( $product_permalink ) ) {
			$data = wp_kses_post( $thumbnail ); // PHPCS: XSS ok.
		} else {
			/* translators: %s Product URL, %s Image */
			$data = sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $thumbnail ) ); // PHPCS: XSS ok.
		}

		if ( ! $echo ) {
			return $data;
		}

		echo wp_kses_post( $data );
	}
}

if ( ! function_exists( 'sfl_get_valid_cart_items' ) ) {
	/**
	 * Get Valid Cart Items
	 *
	 * @since 3.7.0
	 */
	function sfl_get_valid_cart_items() {
		$valid_cart_items = array();

		if ( is_admin() ) {
			return $valid_cart_items;
		}

		if ( ! has_block( 'woocommerce/cart' ) && ! is_cart() ) {
			return $valid_cart_items;
		}

		if ( WC()->cart->is_empty() ) {
			return $valid_cart_items;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( SFL_Restriction_Handler::is_valid_for_sfl( $cart_item_key ) ) {
				$valid_cart_items[] = $cart_item_key;
			}
		}

		return $valid_cart_items;
	}
}

if ( ! function_exists( 'sfl_supported_product_types' ) ) {
	/**
	 * Get Supported Product Type.
	 *
	 * @since 3.8.0
	 * @return Array
	 * */
	function sfl_supported_product_types() {
		/**
		 * Supported Product Type
		 *
		 * @since 3.8.0
		 * @return Array.
		 */
		return apply_filters( 'sfl_supported_product_types', array( 'simple', 'variable' ) );
	}
}
