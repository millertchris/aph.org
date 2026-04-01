<?php

if ( ! function_exists( 'sfl_is_user_having_list' ) ) {

	/**
	 * Function to check user having list
	 *
	 * @return bool/integer
	 */
	function sfl_is_user_having_list( $user_id ) {

		$get_sfl_data = get_posts(
			array(
				'author'         => $user_id,
				'post_status'    => 'publish',
				'post_type'      => SFL_Register_Post_Types::SFL_POSTTYPE,
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		);

		return ( sfl_check_is_array( $get_sfl_data ) ) ? $get_sfl_data[0] : false;
	}
}

if ( ! function_exists( 'sfl_is_product_already_in_list' ) ) {

	/**
	 * Function to check product already in cart
	 *
	 * @since 1.0.0
	 * @return bool/integer
	 */
	function sfl_is_product_already_in_list( $user_id, $product_id ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		$product_obj = wc_get_product( $product_id );

		if ( empty( $product_obj ) ) {
			return false;
		}

		$get_sfl_data = get_posts(
			array(
				'author'         => $user_id,
				'post_status'    => 'sfl_saved',
				'meta_key'       => 'sfl_product_id',
				'meta_value'     => $product_id,
				'post_type'      => SFL_Register_Post_Types::SFL_POSTTYPE,
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		);

		$bool = ( sfl_check_is_array( $get_sfl_data ) ) ? $get_sfl_data[0] : false;

		/**
		 * Check product already exists in List.
		 *
		 * @since 3.8.0
		 */
		return apply_filters( 'sfl_check_is_product_already_in_list', $bool, $product_obj, $user_id );
	}
}

if ( ! function_exists( 'sfl_get_users_list' ) ) {

	/**
	 * Function to get sfl users list
	 *
	 * @return bool/array
	 */
	function sfl_get_users_list( $user_id ) {
		$sfl_ids      = array();
		$get_sfl_data = get_posts(
			array(
				'author'         => $user_id,
				'post_status'    => 'sfl_saved',
				'post_type'      => SFL_Register_Post_Types::SFL_POSTTYPE,
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		);

		if ( ! sfl_check_is_array( $get_sfl_data ) ) {
			return $sfl_ids;
		}

		foreach ( $get_sfl_data as $each_id ) {
			$sfl_data = sfl_get_entry( $each_id );

			if ( WC()->cart->find_product_in_cart( $sfl_data->get_cart_item_key() ) ) {
				continue;
			}

			$sfl_ids[] = $each_id;
		}

		return $sfl_ids;
	}
}

if ( ! function_exists( 'sfl_get_child_posts' ) ) {

	/**
	 * Function to get sfl child posts
	 *
	 * @return bool/array
	 */
	function sfl_get_child_posts( $parent_id, $status ) {

		$get_sfl_data = get_posts(
			array(
				'post_parent'    => $parent_id,
				'post_status'    => $status,
				'post_type'      => SFL_Register_Post_Types::SFL_POSTTYPE,
				'fields'         => 'ids',
				'posts_per_page' => -1,
			)
		);

		return $get_sfl_data;
	}
}

if ( ! function_exists( 'sfl_get_child_posts_reports' ) ) {

	/**
	 * Function to get sfl child posts Reports.
	 *
	 * @since 1.0
	 * @param Array  $filter_arg Post Arguments.
	 * @param String $status Post Status.
	 * @return Integer|Boolean.
	 */
	function sfl_get_child_posts_reports( $filter_arg, $status = '' ) {

		$date_ranges = sfl_get_report_date_range( $filter_arg );

		if ( ! isset( $date_ranges['from'] ) || ! isset( $date_ranges['to'] ) ) {
			return 0;
		}

		$status       = ( 'all' == $status ) ? array( 'sfl_current_saved', 'sfl_saved', 'sfl_purchased', 'sfl_deleted' ) : $status;
		$prepare_args = array(
			'post_status'    => $status,
			'post_type'      => SFL_Register_Post_Types::SFL_MASTERLOG,
			'fields'         => 'ids',
			'posts_per_page' => -1,
		);

		if ( 'all_time' != $date_ranges['from'] ) {
			$prepare_args['date_query'] = array(
				array(
					'after'     => $date_ranges['from'],
					'before'    => $date_ranges['to'],
					'inclusive' => true,
				),
			);
		}

		$get_sfl_data = get_posts( $prepare_args );

		return ( sfl_check_is_array( $get_sfl_data ) ) ? count( $get_sfl_data ) : 0;
	}
}

if ( ! function_exists( 'sfl_get_child_posts_reports_amount' ) ) {

	/**
	 * Function to get sfl child posts Reports
	 *
	 * @since 1.0
	 * @param Array $filter_arg Post Arguments.
	 * @return Integer|Boolean.
	 */
	function sfl_get_child_posts_reports_amount( $filter_arg ) {

		$date_ranges = sfl_get_report_date_range( $filter_arg );

		if ( ! isset( $date_ranges['from'] ) || ! isset( $date_ranges['to'] ) ) {
			return 0;
		}
		$prepare_args = array(
			'post_status'    => 'sfl_purchased',
			'post_type'      => SFL_Register_Post_Types::SFL_MASTERLOG,
			'fields'         => 'ids',
			'posts_per_page' => -1,
		);

		if ( 'all_time' != $date_ranges['from'] ) {
			$prepare_args['date_query'] = array(
				array(
					'after'     => $date_ranges['from'],
					'before'    => $date_ranges['to'],
					'inclusive' => true,
				),
			);
		}

		$get_sfl_data = get_posts( $prepare_args );

		if ( sfl_check_is_array( $get_sfl_data ) ) {
			$sft_data_total = 0;

			foreach ( $get_sfl_data as $each_data ) {
				$sfl_data        = sfl_get_entry( $each_data );
				$sft_data_total += $sfl_data->get_item_total();
			}

			return $sft_data_total;
		}

		return 0;
	}
}



if ( ! function_exists( 'sfl_get_table_menus' ) ) {

	/**
	 * Function to get sfl table menus Saved/Purchased/Deleted
	 *
	 * @return array
	 */
	function sfl_get_table_menus() {
		return array(
			'sfl_saved'     => esc_html__( 'Saved', 'save-for-later-for-woocommerce' ),
			'sfl_purchased' => esc_html__( 'Purchased', 'save-for-later-for-woocommerce' ),
			'sfl_deleted'   => esc_html__( 'Deleted', 'save-for-later-for-woocommerce' ),
		);
	}
}
