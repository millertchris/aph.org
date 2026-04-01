<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Get Shipping packages group posts.
 *
 * Get a list of the posts (IDs).
 *
 * @since 1.0.0
 *
 * @param  array $args List of WP_Query arguments.
 * @return array List of published 'shipping_package' post IDs.
 */
function aspwc_get_shipping_package_posts( $args = array() ) {

	$query = new WP_Query( wp_parse_args( $args, array(
		'posts_per_page'         => 1000,
		'post_type'              => 'shipping_package',
		'post_status'            => 'publish',
		'orderby'                => 'menu_order',
		'order'                  => 'ASC',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	) ) );

	return apply_filters( 'advanced_shipping_packages_get_post_ids', $query->posts );

}


/**
 * Get posts that match.
 *
 * Get a list of the post IDs that are matching their conditions.
 *
 * @since 1.0.0
 *
 * @return array
 */
function aspwc_get_posts_matching_conditions() {

	$posts        = aspwc_get_shipping_package_posts();
	$matching_ids = array();

	// Get the total shipping package
	remove_filter( 'woocommerce_cart_shipping_packages', 'aspwc_split_cart_shipping_packages' );
	$shipping_packages = WC()->cart->get_shipping_packages();
	$first_package = reset( $shipping_packages );
	add_filter( 'woocommerce_cart_shipping_packages', 'aspwc_split_cart_shipping_packages' );

	// Packages can be empty in WC Subscriptions for example when up-/down-grading
	// This check prevents it from checking conditions which may fail because of packages without contents
	if ( empty( $shipping_packages ) ) {
		return array();
	}

	foreach ( $posts as $post ) {
		$condition_groups = get_post_meta( $post->ID, '_conditions', true );
		if ( wpc_match_conditions( $condition_groups, array( 'context' => 'aspwc', 'package' => $first_package ) ) == true ) {
			$matching_ids[] = $post->ID;
		}
	}

	return $matching_ids;

}


/**
 * Product matches split conditions?
 *
 * Check if the given item matches the conditions to split it from
 * the main package.
 *
 * @since 1.0.0
 *
 * @param  array $item    List of cart item data.
 * @param  int   $post_id ID of the shipping package post ID
 * @return bool           True when the products matches the conditions, false otherwise.
 */
function aspwc_product_matches_package_split_conditions( $item, $post_id ) {

	// Format as package so matching functions are recognising it.
	$package['contents'][] = $item;

	$condition_groups = get_post_meta( $post_id, '_product_conditions', true );
	return wpc_match_conditions( $condition_groups, array( 'context' => 'aspwc', 'package' => $package ) );

}


/**
 * Get the split package.
 *
 * Get the full split package array with all the details, including the
 * products that should be split.
 *
 * @since 1.0.0
 *
 * @param  array         $default_package Default package.
 * @param  int           $post_id         ID of the post to base the package splitting on.
 * @return array|boolean                  The split package with all its data when valid. False when invalid.
 */
function aspwc_get_split_package( $default_package, $post_id ) {

	$split_package                  = $default_package;
	$split_package['contents']      = array();
	$split_package['contents_cost'] = 0;
	$split_package['id']            = $post_id;
	$split_package['asp_id']        = $post_id; // New primary identifier

	foreach ( $default_package['contents'] as $key => $item ) {
		if ( ! $item['data']->needs_shipping() ) continue;

		if ( aspwc_product_matches_package_split_conditions( $item, $post_id ) == true ) {
			$split_package['contents'][ $key ] = $item;
		}
	}

	if ( empty( $split_package['contents'] ) ) {
		return false;
	}

	// Set package contents cost
	foreach ( $split_package['contents'] as $item ) {
		if ( $item['data']->needs_shipping() ) {
			if ( isset( $item['line_total'] ) ) {
				$split_package['contents_cost'] += $item['line_total'];
			}
		}
	}

	return $split_package;

}


/**
 * THE splitting of packages.
 *
 * This is THE function that splits the order into
 * multiple packages.
 *
 * @since 1.0.0
 *
 * @param  array $packages List of existing packages.
 * @return array           List of modified packages.
 */
function aspwc_split_cart_shipping_packages( $packages ) {

	if ( get_option( 'enable_woocommerce_advanced_shipping_packages', 'yes' ) != 'yes' ) {
		return $packages;
	}

	$origin_packages = $packages;
	$packages        = array();
	$matching_posts  = aspwc_get_posts_matching_conditions();
	$default_package = reset( $origin_packages );

	if ( ! $matching_posts ) {
		return $origin_packages; // Return original package(s) if no rules apply.
	}

	// Get the split packages
	foreach ( $matching_posts as $post_id ) {

		if ( ! $split_package = aspwc_get_split_package( $default_package, $post_id ) ) {
			continue;  // Skip invalid packages
		}

		// Set package
		if ( isset( $default_package['recurring_cart_key'] ) ) { // WC Subscriptions
			$packages[ $default_package['recurring_cart_key'] ] = $split_package;
		} else {
			$packages[] = $split_package;
		}

		// Unset the split items from the default package items
		$unset_keys = array_intersect( array_keys( $default_package['contents'] ), array_keys( $split_package['contents'] ) );
		foreach ( $unset_keys as $k ) {
			if ( isset( $default_package['contents'][ $k ] ) ) {
				unset( $default_package['contents'][ $k ] );
			}
		}

	}

	// Default package calculation
	$default_package['id'] = $default_package['asp_id'] = 'default';
	$default_package['content_cost'] = 0;

	foreach ( $default_package['contents'] as $item ) {
		if ( $item['data']->needs_shipping() ) {
			if ( isset( $item['line_total'] ) ) {
				$default_package['contents_cost'] += $item['line_total'];
			}
		}
	}

	if ( ! empty( $default_package['contents'] ) ) {
		$packages[] = $default_package;
	}

	return $packages;

}
add_filter( 'woocommerce_cart_shipping_packages', 'aspwc_split_cart_shipping_packages' );


/**
 * Name shipping packages.
 *
 * Set the shipping package name accordingly.
 *
 * @since 1.0.0
 *
 * @param  string $name    original shipping package name.
 * @param  int    $i       Shipping package index.
 * @param  array  $package Package list.
 * @return string          Modified shipping package name.
 */
function aspwc_shipping_package_name( $name, $i, $package ) {

	$title = get_option( 'advanced_shipping_packages_default_package_name', '' );

	if ( ( ! isset( $package['asp_id'] ) || ( $package['asp_id'] === 'default' ) ) && ! empty( $title ) ) { // Default package
		$name = $title;
	} elseif ( isset( $package['asp_id'] ) && is_numeric( $package['asp_id'] ) ) {
		$package_name = get_post_meta( absint( $package['asp_id'] ), '_name', true );
		if ( ! empty( $package_name ) ) {
			$name = $package_name;
		}
	}

	return $name;

}
add_filter( 'woocommerce_shipping_package_name', 'aspwc_shipping_package_name', 10, 3 );


/**
 * Exclude shipping rates.
 *
 * Exclude any shipping rates that may have been set to be excluded.
 *
 * @since 1.0.0
 *
 * @param  array $rates   List of available shipping options.
 * @param  array $package List of shipping package data.
 * @return array          List of modified shipping options.
 */
function aspwc_exclude_shipping_rates( $rates, $package ) {

	if ( ! isset( $package['asp_id'], $package['id'] ) || $package['id'] == 'default' ) {
		return $rates;
	}
	$excluded_rates = (array) get_post_meta( $package['asp_id'], '_exclude_shipping', true );

	foreach ( $rates as $k => $rate ) {
		// For BC add a ID formatted as we're saving it; the default way of writing a rate ID (method_id:instance_id).
		// Carrier plugins tend to (rightfully) override this rate ID in a different way.
		$id = $rate->method_id . ':' . $rate->instance_id;
		if ( array_intersect( array( $rate->id, $rate->method_id, $rate->instance_id, $id ), $excluded_rates ) ) {
			unset( $rates[ $k ] );
		}
	}

	return $rates;

}
add_filter( 'woocommerce_package_rates', 'aspwc_exclude_shipping_rates', 10, 2 );


/**
 * Whitelist shipping rates.
 *
 * Filter shipping rates based on the set whitelist of rate.
 *
 * @since 1.1.6
 *
 * @param  array $rates   List of available shipping options.
 * @param  array $package List of shipping package data.
 * @return array          List of modified shipping options.
 */
function aspwc_whitelist_shipping_rates( $rates, $package ) {
	if ( ! isset( $package['asp_id'], $package['id'] ) || $package['id'] == 'default' ) {
		return $rates;
	}
	$whitelisted_rates = array_filter( (array) get_post_meta( $package['asp_id'], '_include_shipping', true ) );

	if ( empty( $whitelisted_rates ) ) {
		return $rates;
	}

	$new_rates = array();
	foreach ( $rates as $k => $rate ) {
		// For BC add a ID formatted as we're saving it; the default way of writing a rate ID (method_id:instance_id).
		// Carrier plugins tend to (rightfully) override this rate ID in a different way.
		$id = $rate->method_id . ':' . $rate->instance_id;
		if ( array_intersect( array( $rate->id, $rate->method_id, $rate->instance_id, $id ), array_filter( $whitelisted_rates ) ) ) {
			$new_rates[ $k ] = $rate;
		}
	}

	return $new_rates;
}
add_filter( 'woocommerce_package_rates', 'aspwc_whitelist_shipping_rates', 20, 2 );


/**
 * Store package name.
 *
 * Store the package name with the shipping line item.
 *
 * @since 1.1.9
 *
 * @param WC_Order_Item_Shipping $item        Shipping order line item.
 * @param int                    $package_key Package index.
 * @param array                  $package     Package data.
 * @param WC_Order               $order       Related order.
 */
function aspwc_checkout_store_package_name( $item, $package_key, $package, $order ) {
	$asp_id = isset( $package['asp_id'] ) && 'shipping_package' == get_post_type( $package['asp_id'] ) ? $package['asp_id'] : null;
	$name   = str_replace( ':', '', __( 'Shipping:', 'woocommerce' ) ); // Use WC Core original translation as default
	if ( $asp_id ) {
		$name = get_post_meta( $asp_id, '_name', true );
	}

	// Default package name
	if ( ( $asp_id === 'default' || is_null( $asp_id ) ) && $title = get_option( 'advanced_shipping_packages_default_package_name', '' ) ) {
		$name = $title;
	}
	$item->add_meta_data( 'package_name', $name );
}
add_action( 'woocommerce_checkout_create_order_shipping_item', 'aspwc_checkout_store_package_name', 10, 4 );


/**
 * Split package total rows.
 *
 * Split the total rows to have separate rows for each package instead of a single 'Shipping' row.
 * Shows on the checkout/thank you page and emails.
 *
 * @since 1.1.9
 *
 * @param  array    $total_rows  Original list of order totals.
 * @param  WC_Order $order       Order object.
 * @param  string   $tax_display Whether to show tax 'incl' or 'excl'.
 * @return mixed                 Modified list of order totals.
 */
function aspwc_split_package_total_rows( $total_rows, $order, $tax_display ) {
	if ( get_option( 'advanced_shipping_packages_display_separately', 'no' ) != 'yes' ) {
		return $total_rows;
	}

	/** @var WC_Order $order */
	$shipping_items    = $order->get_items( 'shipping' );
	$key_position      = array_search( 'shipping', array_keys( $total_rows ) );
	$new_shipping_rows = array();

	foreach ( $shipping_items as $item ) {
		$package_name = $item->get_meta( 'package_name' ) ?: str_replace( ':', '', __( 'Shipping:', 'woocommerce' ) );

		$new_shipping_rows[ 'shipping_' . $item->get_id() ] = array(
			'label' => $package_name . ':', // Package name
			'value' => '<strong>' . $item->get_name() . ':</strong> ' . aspwc_get_shipping_to_display( $item, $order, $tax_display ) . '<br/><small>' . $item->get_meta( 'Items' ) . '</small>',
		);
	}

	// Remove original shipping line - Only when new rows are set (which should only happen when package names are stored)
	if ( ! empty( $new_shipping_rows ) ) {
		unset( $total_rows['shipping'] );
	}

	// Add package line(s)
	array_splice( $total_rows, $key_position, 0, $new_shipping_rows ); // splice in at position 3

	return $total_rows;
}
add_filter( 'woocommerce_get_order_item_totals', 'aspwc_split_package_total_rows', 10, 3 );


/**
 * Get shipping line to display.
 *
 * Get the shipping text do display for use in the totals table. This is the shipping rate title and amount/taxes.
 *
 * Inspired by WC_Order::get_shipping_to_display()
 *
 * @since 1.1.9
 *
 * @param  WC_Order_Item_Shipping $item
 * @param string $tax_display
 * @return mixed
 */
function aspwc_get_shipping_to_display( $item, $order, $tax_display = '' ) {
	$tax_display = $tax_display ? $tax_display : get_option( 'woocommerce_tax_display_cart' );

	$total = (float) $item->get_total();
	if ( 0 < abs( (float) $total ) ) {

		if ( 'excl' === $tax_display ) { // Show shipping excluding tax.

			$shipping = wc_price( $total, array( 'currency' => $order->get_currency() ) );
			if ( (float) $item->get_total_tax() > 0 && $order->get_prices_include_tax() ) {
				$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>', $order, $tax_display );
			}
		} else { // Show shipping including tax.

			$shipping = wc_price( $total + (float) $item->get_total_tax(), array( 'currency' => $order->get_currency() ) );
			if ( (float) $order->get_shipping_tax() > 0 && ! $order->get_prices_include_tax() ) {
				$shipping .= apply_filters( 'woocommerce_order_shipping_to_display_tax_label', '&nbsp;<small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>', $order, $tax_display );
			}
		}

	} else {
		$shipping = __( 'Free!', 'woocommerce' );
	}

	return $shipping;
}

/**
 * Debug function.
 *
 * Internal package debug function.
 *
 * @param $packages
 * @param $comment
 * @return void
 */
function _aspwc_debug_packages( $packages, $comment = '' ) {
	foreach ( $packages as $index => $package ) {
		unset( $package['user'] );
		unset( $package['destination'] );
		unset( $package['applied_coupons'] );

		$product_names = array();
		foreach ( $package['contents'] as $item_id => $values ) {
			$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
		}

		unset( $package['contents'] );
		$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
		$package['product_names'] = $product_names;
		error_log( $index . ' - ' . $comment . ' : ' . print_r( $package, 1 ) );
	}
}

/**************************************************************
 * Backwards compatibility for WP Conditions
 *************************************************************/

/**
 * Add the filters required for backwards-compatibility for the matching functionality.
 *
 * @since 1.1.0
 */
function aspwc_add_bc_filter_condition_match( $match, $condition, $operator, $value, $args = array() ) {

	if ( ! isset( $args['context'] ) || $args['context'] != 'aspwc' ) {
		return $match;
	}

	if ( has_filter( 'advanced_shipping_packages_for_woocommerce_match_condition_' . $condition ) ) {
		$package = $args['package'] ?? array();
		$match = apply_filters( 'advanced_shipping_packages_for_woocommerce_match_condition_' . $condition, $match = false, $operator, $value, $package );
	}

	return $match;

}
add_action( 'wp-conditions\condition\match', 'aspwc_add_bc_filter_condition_match', 10, 5 );


/**
 * Add condition descriptions of custom conditions.
 *
 * @since 1.1.0
 */
function aspwc_add_bc_filter_condition_descriptions( $descriptions ) {
	return apply_filters( 'advanced_shipping_packages_descriptions', $descriptions );
}
add_filter( 'wp-conditions\condition_descriptions', 'aspwc_add_bc_filter_condition_descriptions' );


/**
 * Add custom field BC.
 *
 * @since 1.1.0
 */
function aspwc_add_bc_action_custom_fields( $type, $args ) {

	if ( has_action( 'wp_condition_html_field_type_' . $type ) ) {
		do_action( 'wp_condition_html_field_type_' . $args['type'], $args );
	}

}
add_action( 'wp-conditions\html_field_hook', 'aspwc_add_bc_action_custom_fields', 10, 2 );
