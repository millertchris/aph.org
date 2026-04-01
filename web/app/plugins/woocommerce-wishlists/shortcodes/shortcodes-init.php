<?php

/**
 * Shortcodes init
 *
 * Init main shortcodes, and add a few others such as recent products.
 *
 * @author      Lucas Stark
 * @category    Shortcodes
 * @package     WooCommerce_Wishlists/Shortcodes
 * @version     1.0.0
 */
function shortcode_wc_wishlists_create( $atts ) {
	ob_start();
	$guest_setting = WC_Wishlists_Settings::get_setting( 'wc_wishlist_guest_enabled', 'enabled' );
	if ( is_user_logged_in() || ( $guest_setting == 'enabled' ) ) {
		woocommerce_wishlists_get_template( 'create-a-list.php' );
	} else {
		if ( $guest_setting == 'registration_required' ) {
			$message = apply_filters( 'woocommerce_my_account_message', '' );

			if ( ! empty( $message ) ) {
				wc_add_notice( $message );
			}

			add_action( 'woocommerce_login_form', '_woocommerce_wishlist_insert_return_url' );
			add_action( 'woocommerce_register_form', '_woocommerce_wishlist_insert_return_url' );

			echo '<div class="woocommerce woocommerce-page">';
			woocommerce_wishlists_get_template( 'guest-disabled.php' );
			wc_get_template( 'myaccount/form-login.php' );
			echo '</div>';

			remove_action( 'woocommerce_logon_form', '_woocommerce_wishlist_insert_return_url' );
			remove_action( 'woocommerce_register_form', '_woocommerce_wishlist_insert_return_url' );

		} else {
			woocommerce_wishlists_get_template( 'guest-disabled.php' );
		}
	}

	return ob_get_clean();
}

function shortcode_wc_wishlists_search( $atts ) {
	global $paged;

	// Sanitize input at the top
	$search_query = '';
	if ( isset( $_GET['f-list'] ) ) {
		$search_query = sanitize_text_field( wp_unslash( $_GET['f-list'] ) );
	}

	if ( empty( $paged ) ) {
		$paged = 1;
	}

	ob_start();

	$args = array(
		'paged'          => $paged,
		'posts_per_page' => apply_filters( 'woocommerce_wishlist_posts_per_page', 10 ),
		'order'          => apply_filters( 'woocommerce_wishlist_posts_order', 'ASC' ),
		'orderby'        => apply_filters( 'woocommerce_wishlist_posts_orderby', 'post_title' ),
		'post_type'      => 'wishlist',
		//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'   => '_wishlist_sharing',
				'value' => 'public'
			)
		)
	);

	if ( $search_query ) {

		$search_args = array(
			'post_type'   => 'wishlist',
			'post_status' => 'publish',
			's'           => $search_query,
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query'  => array(
				array(
					'key'   => '_wishlist_sharing',
					'value' => 'public'
				)
			)
		);

		$email_args = array(
			'post_type'  => 'wishlist',
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => '_wishlist_sharing',
					'value' => 'public'
				),
				array(
					'key'     => '_wishlist_email',
					'value'   => $search_query,
					'compare' => '='
				)
			)
		);

		$name      = explode( ' ', $search_query );
		$name_args = array(
			'post_type'  => 'wishlist',
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'   => '_wishlist_sharing',
					'value' => 'public'
				),
				array(
					'key'     => '_wishlist_first_name',
					'value'   => $name[0],
					'compare' => 'LIKE'
				)
			)
		);

		if ( count( $name ) > 1 ) {
			$name_args['meta_query'][] = array(
				'key'     => '_wishlist_last_name',
				'value'   => $name[1],
				'compare' => 'LIKE'
			);
		}

		$email_ids  = get_posts( $email_args );
		$name_ids   = get_posts( $name_args );
		$search_ids = get_posts( $search_args );

		if ( count( $name ) == 1 ) {
			$name_args = array(
				'post_type'  => 'wishlist',
				//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'   => '_wishlist_sharing',
						'value' => 'public'
					),
					array(
						'key'     => '_wishlist_last_name',
						'value'   => $name[0],
						'compare' => '='
					)
				)
			);

			$last_ids = get_posts( $name_args );
			if ( $last_ids ) {
				$name_ids = array_merge( $name_ids, $last_ids );
			}
		}

		$found_ids = array();
		if ( $email_ids ) {
			foreach ( $email_ids as $post ) {
				$found_ids[] = $post->ID;
			}
		}

		if ( $name_ids ) {
			foreach ( $name_ids as $post ) {
				$found_ids[] = $post->ID;
			}
		}

		if ( $search_ids ) {
			foreach ( $search_ids as $post ) {
				if ( ! in_array( $post->ID, $found_ids ) ) {
					$found_ids[] = $post->ID;
				}
			}
		}

		if ( count( $found_ids ) ) {
			$args['post__in'] = $found_ids;
		} else {
			$args['post__in'] = array( 0 );
		}
	}

	query_posts( $args );
	woocommerce_wishlists_get_template( 'find-a-list.php' );
	wp_reset_query();

	return ob_get_clean();
}

function shortcode_wc_wishlists_single( $atts ) {
	// Sanitize input at the top
	$wishlist_id = 0;
	if ( isset( $_REQUEST['wlid'] ) && ! empty( $_REQUEST['wlid'] ) ) {
		$wishlist_id = absint( $_REQUEST['wlid'] );
	}

	ob_start();

	if ( $wishlist_id ) {
		global $post;
		$post = get_post( $wishlist_id );
		if ( $post && $post->post_type == 'wishlist' ) {
			setup_postdata( $post );
			woocommerce_wishlists_get_template( 'view-a-list.php' );
		}
	}

	wp_reset_postdata();

	return ob_get_clean();
}

function shortcode_wc_wishlists_my_archive( $atts ) {
	ob_start();
	if ( is_user_logged_in() || ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_guest_enabled', 'enabled' ) == 'enabled' ) ) {
		woocommerce_wishlists_get_template( 'my-lists.php' );
	} else {
		woocommerce_wishlists_get_template( 'guest-disabled.php' );
	}

	return ob_get_clean();
}

function shortcode_wc_wishlists_edit( $atts ) {
	// Sanitize input at the top
	$wishlist_id = 0;
	if ( isset( $_REQUEST['wlid'] ) && ! empty( $_REQUEST['wlid'] ) ) {
		$wishlist_id = absint( $_REQUEST['wlid'] );
	}

	ob_start();

	if ( is_user_logged_in() || ( WC_Wishlists_Settings::get_setting( 'wc_wishlist_guest_enabled', 'enabled' ) == 'enabled' ) ) {

		if ( $wishlist_id ) {
			global $post;
			$post = get_post( $wishlist_id );
			if ( $post && $post->post_type == 'wishlist' ) {
				$key      = WC_Wishlists_Wishlist::get_the_wishlist_owner( $post->ID );
				$user_key = WC_Wishlists_User::get_wishlist_key();

				if ( $key == $user_key || current_user_can( 'manage_woocommerce' ) ) {
					setup_postdata( $post );
					woocommerce_wishlists_get_template( 'edit-my-list.php' );
				} else {
					// Access denied - could add error handling here
				}
			}
		}

		wp_reset_postdata();
	} else {
		woocommerce_wishlists_get_template( 'guest-disabled.php' );
	}

	return ob_get_clean();
}

function shortcode_wc_wishlists_button( $atts = '' ) {
	global $wishlists;

	ob_start();
	$wishlists->add_to_wishlist_button();

	return ob_get_clean();
}

function shortcode_wc_wishlists_add_link( $atts ): string {
	// Sanitize shortcode attributes
	$args = shortcode_atts( array(
		'product_id'  => 0,
		'wishlist_id' => 'default',
		'title'       => esc_html__( 'Add to Wishlist', 'wc_wishlists' ),
	), $atts );

	$product_id  = absint( $args['product_id'] ) ?: absint( get_the_ID() );
	$wishlist_id = sanitize_text_field( $args['wishlist_id'] );
	$title       = sanitize_text_field( $args['title'] );

	if ( $title == 'product_title' ) {
		$product = wc_get_product( $product_id );
		if ( $product ) {
			$title = sprintf( esc_html__( 'Add %s to Wishlist', 'wc_wishlists' ), $product->get_title() );
		}
	}

	return '<a href="' . esc_url( add_query_arg( array(
			'add-to-wishlist-itemid' => $product_id,
			'wlid'                   => $wishlist_id
		) ) ) . '" rel="nofollow" data-product-id="' . esc_attr( $product_id ) . '" data-wishlist-id="' . esc_attr( $wishlist_id ) . '" data-product-type="simple" class="add_to_wishlist">' . esc_html( $title ) . '</a>';
}

add_shortcode( 'wc_wishlists_create', 'shortcode_wc_wishlists_create' );
add_shortcode( 'wc_wishlists_search', 'shortcode_wc_wishlists_search' );
add_shortcode( 'wc_wishlists_single', 'shortcode_wc_wishlists_single' );
add_shortcode( 'wc_wishlists_my_archive', 'shortcode_wc_wishlists_my_archive' );
add_shortcode( 'wc_wishlists_edit', 'shortcode_wc_wishlists_edit' );
add_shortcode( 'wc_wishlists_button', 'shortcode_wc_wishlists_button' );
add_shortcode( 'wc_wishlists_add_link', 'shortcode_wc_wishlists_add_link' );
