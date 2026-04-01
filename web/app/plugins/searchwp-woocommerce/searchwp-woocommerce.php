<?php
/*
Plugin Name: SearchWP WooCommerce Integration
Plugin URI: https://searchwp.com/extensions/woocommerce-integration/
Description: Integrate SearchWP with WooCommerce searches and Layered Navigation
Version: 1.4.0
Requires PHP: 5.6
Author: SearchWP
Author URI: https://searchwp.com/

Copyright 2014-2025 SearchWP, LLC

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SEARCHWP_WOOCOMMERCE_VERSION' ) ) {
	define( 'SEARCHWP_WOOCOMMERCE_VERSION', '1.4.0' );
}

/**
 * Implement updater.
 *
 * @since 1.1.0
 *
 * @return bool|SWP_WooCommerce_Updater
 */
function searchwp_woocommerce_update_check() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}

	// Environment check.
	if ( ! defined( 'SEARCHWP_PREFIX' ) ) {
		return false;
	}

	if ( ! defined( 'SEARCHWP_EDD_STORE_URL' ) ) {
		return false;
	}

	if ( ! class_exists( 'SWP_WooCommerce_Updater' ) ) {
		// Load our custom updater.
		include_once __DIR__ . '/vendor/updater.php';
	}

	$license_key = \SearchWP\License::get_key();

	// Instantiate the updater to prep the environment.
	$searchwp_woocommerce_updater = new SWP_WooCommerce_Updater(
		SEARCHWP_EDD_STORE_URL,
		__FILE__,
		[
			'item_id'   => 33339,
			'version'   => SEARCHWP_WOOCOMMERCE_VERSION,
			'license'   => $license_key,
			'item_name' => 'WooCommerce Integration',
			'author'    => 'SearchWP',
			'url'       => site_url(),
		]
	);

	return $searchwp_woocommerce_updater;
}

add_action( 'admin_init', 'searchwp_woocommerce_update_check' );

/**
 * Class SearchWP_WooCommerce_Integration.
 *
 * @since 1.1.0
 */
class SearchWP_WooCommerce_Integration { // phpcs:ignore

	/**
	 * Stores the results of the SearchWP query.
	 *
	 * @since 1.1.0
	 *
	 * @var array
	 */
	private $results = [];

	/**
	 * Stores the ordering of the WooCommerce query.
	 *
	 * @since 1.1.0
	 *
	 * @var array
	 */
	private $ordering = [];

	/**
	 * Stores the original search query.
	 *
	 * @since 1.1.0
	 *
	 * @var array
	 */
	private $original_query = '';

	/**
	 * Stores the filtered posts.
	 *
	 * @since 1.1.0
	 *
	 * @var array
	 */
	private $filtered_posts = [];

	/**
	 * SearchWP_WooCommerce_Integration constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {

		add_filter( 'searchwp\source\attribute\label', [ $this, 'comments_label' ], 10, 2 );

		add_filter( 'woocommerce_json_search_found_products', [ $this, 'json_search_products' ] );

		$query                = isset( $_GET['s'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->original_query = $query;

		add_filter( 'searchwp\native\short_circuit', [ $this, 'maybe_cancel_native_searchwp' ], 999, 1 );
		add_filter( 'searchwp\native\force', [ $this, 'maybe_force_admin_search' ] );
		add_filter( 'searchwp\query\mods', [ $this, 'implement_mods' ] );

		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Customize the label used for Comments in the SearchWP engine configuration.
	 *
	 * @since 1.3.0
	 *
	 * @param string $label The default label.
	 * @param array  $args  The arguments passed to the filter.
	 */
	public function comments_label( $label, $args = null ) {

		$custom = __( 'Reviews', 'searchwp-woocommerce' );

		if ( $args['source'] === 'post.product' && $args['attribute'] === 'comments' ) {
			return $custom;
		} else {
			return $label;
		}
	}

	/**
	 * Initializer.
	 *
	 * @since 1.1.0
	 */
	public function init() {

		global $wp_query;

		/**
		 * Filter to force SearchWP to handle WooCommerce searches.
		 *
		 * @since 1.1.11
		 *
		 * @param bool $forced Whether to force SearchWP to handle WooCommerce searches.
		 */
		$forced = apply_filters( 'searchwp_woocommerce_forced', false );

		if ( ( empty( $_GET['post_type'] ) || $_GET['post_type'] !== 'product' ) && empty( $forced ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Short circuit if we're in the admin but admin searches are not enabled in SearchWP
		// e.g. Because WooCommerce does support SKU searches out of the box.
		/**
		 * Filter to short-circuit SearchWP handling of WooCommerce searches in the admin.
		 *
		 * @since 1.3.1
		 *
		 * @param bool     $in_admin Whether to short-circuit SearchWP handling of WooCommerce searches in the admin.
		 * @param WP_Query $wp_query The WP_Query object.
		 */
		$in_admin = apply_filters( 'searchwp\native\admin\short_circuit', false, $wp_query ); // $query questionable.
		if ( empty( $in_admin ) && is_admin() ) {
			return;
		}

		/**
		 * Filter to short-circuit SearchWP Native searches.
		 *
		 * @since 1.3.1
		 *
		 * @param bool     $cancel   Whether to short-circuit SearchWP Native searches.
		 * @param WP_Query $wp_query The WP_Query object.
		 */
		$searchwp_core_short_circuit = apply_filters( 'searchwp\native\short_circuit', false, $wp_query );

		/**
		 * Filter to short-circuit SearchWP handling of WooCommerce searches.
		 *
		 * @since 1.1.0
		 *
		 * @param bool $short_circuit Whether to short-circuit SearchWP handling of WooCommerce searches.
		 */
		$short_circuit = apply_filters( 'searchwp_woocommerce_short_circuit', $searchwp_core_short_circuit );

		if ( ( ! empty( $short_circuit ) || empty( $_GET['s'] ) ) && empty( $forced ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// WooCommerce hooks.
		add_action( 'loop_shop_post_in', [ $this, 'post_in' ], 9999 );
		add_action( 'woocommerce_product_query', [ $this, 'product_query' ], 10, 1 );
		add_filter( 'woocommerce_get_filtered_term_product_counts_query', [ $this, 'get_filtered_term_product_counts_query' ] );
		add_filter( 'woocommerce_product_query_meta_query', [ $this, 'woocommerce_product_query_meta_query' ] );
		add_filter( 'woocommerce_price_filter_sql', [ $this, 'price_filter_sql' ] );

		$this->get_woocommerce_ordering();

		// WordPress hooks.
		add_action( 'wp', [ $this, 'hijack_query_vars' ], 1 );
		add_action( 'wp', [ $this, 'replace_original_search_query' ], 3 );
	}

	/**
	 * Force an admin search when applicable.
	 *
	 * @since 1.1.20
	 *
	 * @param bool $args Whether to force an admin search.
	 *
	 * @return bool
	 */
	public function maybe_force_admin_search( $args ) {

		if ( ! is_admin() || ! $this->is_woocommerce_search() ) {
			return false;
		}

		// If this is an admin search and there is an admin engine with Products, force it to happen.
		// We have to do this because $query->is_search() is false at runtime.
		$admin_engine = \SearchWP\Settings::get_admin_engine();

		if ( empty( $admin_engine ) ) {
			return $args;
		}

		$engine_model = new \SearchWP\Engine( $admin_engine );
		$sources      = $engine_model->get_sources();

		if ( ! array_key_exists( 'post' . SEARCHWP_SEPARATOR . 'product', $sources ) ) {
			return $args;
		}

		add_filter( 'searchwp\native\args', [ $this, 'set_admin_search_args' ] );

		return true;
	}

	/**
	 * Set the admin search args.
	 *
	 * @since 1.3.1
	 *
	 * @param array $args The admin search args.
	 *
	 * @return mixed
	 */
	public function set_admin_search_args( $args ) {

		remove_filter( 'searchwp\native\args', [ $this, 'set_admin_search_args' ] );

		if ( array_key_exists( 'product_search', $args ) && $args['product_search'] ) {
			$args['post__in'] = [];
		}

		return $args;
	}

	/**
	 * Determine whether to cancel a native SearchWP search.
	 *
	 * @since 1.1.20
	 *
	 * @param bool $cancel Whether to cancel the native search.
	 *
	 * @return bool
	 */
	public function maybe_cancel_native_searchwp( $cancel ) {

		return is_admin() ? false : $this->is_woocommerce_search( $cancel );
	}

	/**
	 * Implement SearchWP mods.
	 *
	 * @since 1.3.1
	 *
	 * @param array $mods The mods.
	 *
	 * @return mixed
	 */
	public function implement_mods( $mods ) {

		global $wpdb;

		$mod = new \SearchWP\Mod();
		$mod->raw_join_sql(
			function ( $runtime ) use ( $wpdb ) {
				return "LEFT JOIN {$wpdb->posts} AS swpwcposts ON swpwcposts.ID = {$runtime->get_foreign_alias()}.id";
			}
		);
		$main_join_sql = $this->query_main_join( '' );
		$main_join_sql = str_replace( "{$wpdb->posts}.", 'swpwcposts.', $main_join_sql );
		$mod->raw_join_sql( $main_join_sql );

		$column_as = $this->searchwp_query_inject();
		if ( ! empty( $column_as ) ) {
			$mod->column_as(
				str_ireplace( 'AS average_rating', '', $column_as ),
				'average_rating'
			);
		}

		$orderbys = $this->query_orderby();

		foreach ( $orderbys as $orderby ) {
			$mod->order_by( $orderby['column'], $orderby['direction'], 5 );
		}

		$where = $this->searchwp_query_where();

		if ( $where ) {
			$mod->raw_where_sql( ' 1=1 ' . $this->searchwp_query_where() );
		}

		$mods[] = $mod;

		// Exclude hidden products?
		$excluded = $this->exclude_hidden_products( [] );
		if ( ! empty( $excluded ) ) {
			$source = \SearchWP\Utils::get_post_type_source_name( 'product' );
			$mod    = new \SearchWP\Mod( $source );
			$mod->set_where(
				[
					[
						'column'  => 'id',
						'value'   => $excluded,
						'compare' => 'NOT IN',
						'type'    => 'NUMERIC',
					],
				]
			);

			$mods[] = $mod;
		}

		// Hide out of stock items?
		$out_of_stock = $this->maybe_exclude_out_of_stock_products( [] );

		if ( ! empty( $out_of_stock ) ) {
			$source = \SearchWP\Utils::get_post_type_source_name( 'product' );
			$mod    = new \SearchWP\Mod( $source );
			$mod->set_where(
				[
					[
						'column'  => 'id',
						'value'   => $out_of_stock,
						'compare' => 'NOT IN',
						'type'    => 'NUMERIC',
					],
				]
			);

			$mods[] = $mod;
		}

		return $mods;
	}

	/**
	 * Maybe hijack the WooCommerce json search results.
	 *
	 * @since 1.2.1
	 *
	 * @param array $core_wc_products The core WooCommerce products.
	 *
	 * @return array|mixed
	 */
	public function json_search_products( $core_wc_products ) {

		if ( ! class_exists( 'SWP_Query' ) ) {
			return $core_wc_products;
		}

		/**
		 * Filter to determine whether to hijack the WooCommerce JSON search results.
		 *
		 * @since 1.2.1
		 *
		 * @param bool $proceed Whether to proceed with hijacking the WooCommerce JSON search results.
		 */
		$proceed = apply_filters( 'searchwp_woocommerce_hijack_json_search', false );

		if ( empty( $proceed ) ) {
			return $core_wc_products;
		}

		$args = [
			's'              => isset( $_REQUEST['term'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['term'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'post_type'      => [ 'product', 'product_variation' ],
			'engine'         => 'default',
			'page'           => 1,
			'fields'         => 'ids',
			'posts_per_page' => -1,
		];

		/**
		 * Filter the arguments used to retrieve WooCommerce products for the JSON search.
		 *
		 * @since 1.2.1
		 *
		 * @param array $args The arguments used to retrieve WooCommerce products for the JSON search.
		 */
		$results = new SWP_Query( apply_filters( 'searchwp_woocommerce_json_search_products_args', $args ) );

		if ( empty( $results->posts ) ) {
			return $core_wc_products;
		}

		// See WC_AJAX@json_search_products().
		$product_objects = array_filter( array_map( 'wc_get_product', $results->posts ), 'wc_products_array_filter_readable' );
		$products        = [];

		foreach ( $product_objects as $product_object ) {
			$formatted_name = $product_object->get_formatted_name();
			$managing_stock = $product_object->managing_stock();

			if ( $managing_stock && ! empty( $_GET['display_stock'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$formatted_name .= ' &ndash; ' . wc_format_stock_for_display( $product_object );
			}

			$products[ $product_object->get_id() ] = rawurldecode( $formatted_name );
		}

		return $products;
	}

	/**
	 * Limit the WooCommerce query for get_filtered_price() to SearchWP results.
	 *
	 * @since 1.4.0
	 *
	 * @param string $sql The SQL query.
	 *
	 * @return string
	 */
	public function price_filter_sql( $sql ) {

		$sql .= ' AND product_id IN (' . implode( ',', array_map( 'absint', $this->results ) ) . ')';

		return $sql;
	}

	/**
	 * We need to customize WooCommerce's visibility meta query because we're doing our own.
	 *
	 * @since 1.1.0
	 *
	 * @param array $meta_query The meta query.
	 *
	 * @return mixed
	 */
	public function woocommerce_product_query_meta_query( $meta_query ) {

		/**
		 * Filter to determine whether to consider WooCommerce visibility in the meta query.
		 *
		 * @since 1.1.10
		 *
		 * @param bool $proceed Whether to consider WooCommerce visibility in the meta query.
		 */
		$proceed = apply_filters( 'searchwp_woocommerce_consider_visibility', true );

		if ( empty( $proceed ) ) {
			return $meta_query;
		}

		if ( isset( $meta_query['visibility'] ) && $this->is_woocommerce_search() ) {
			unset( $meta_query['visibility'] );
		}

		return $meta_query;
	}

	/**
	 * Even if it's not a WooCommerce search, we should exclude hidden WooCommerce product IDs.
	 *
	 * @since 1.1.3
	 *
	 * @param array $ids The IDs of the hidden products.
	 *
	 * @return array
	 */
	public function exclude_hidden_products( $ids ) {

		/**
		 * Filter to determine whether to consider WooCommerce visibility in the tax query.
		 *
		 * @since 1.1.10
		 *
		 * @param bool $proceed Whether to consider WooCommerce visibility in the tax query.
		 */
		$proceed = apply_filters( 'searchwp_woocommerce_consider_visibility', true );

		if ( empty( $proceed ) ) {
			return $ids;
		}

		$args = [
			'post_type' => 'product',
			'nopaging'  => true,
			'fields'    => 'ids',
		];

		$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			[
				'taxonomy' => 'product_visibility',
				'field'    => 'slug',
				'terms'    => 'exclude-from-search',
			],
		];

		$hidden = get_posts( $args );

		if ( ! empty( $hidden ) ) {
			$ids = array_merge( $ids, $hidden );
		}

		return $ids;
	}

	/**
	 * If out of stock options should be hidden from search, exclude them from search.
	 *
	 * @since 1.1.8
	 *
	 * @param array $ids The IDs of the out-of-stock products.
	 *
	 * @return array
	 */
	public function maybe_exclude_out_of_stock_products( $ids ) {

		if ( 'yes' !== get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			return $ids;
		}

		$args = [
			'post_type' => 'product',
			'nopaging'  => true,
			'fields'    => 'ids',
			'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'outofstock',
					'operator' => 'IN',
				],
			],
		];

		$out_of_stock = get_posts( $args );

		if ( ! empty( $out_of_stock ) ) {
			$ids = array_merge( $ids, $out_of_stock );
		}

		return $ids;
	}

	/**
	 * Retrieves the filtered post IDs.
	 *
	 * @since 1.1.0
	 *
	 * @param array $post__in The post IDs to include.
	 *
	 * @return array
	 */
	public function include_filtered_posts( $post__in ) {

		$post__in = array_merge( (array) $post__in, $this->filtered_posts );

		return array_unique( $post__in );
	}

	/**
	 * Piggyback WooCommerce's Layered Navigation and inject SearchWP results where applicable.
	 *
	 * @since 1.1.9
	 *
	 * @param array $filtered_posts The filtered post IDs.
	 *
	 * @return array
	 */
	public function post_in( $filtered_posts ) {

		if ( ! class_exists( 'SWP_Query' ) ) {
			return $filtered_posts;
		}

		$search_query = stripslashes( get_search_query() );

		if ( $this->is_woocommerce_search() && $search_query === $this->original_query ) {

			if ( ! empty( $this->results ) ) {
				return $this->results;
			}

			$searchwp_engine = 'default';

			$swppg = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

			// Force SearchWP to only consider the filtered posts.
			if ( ! empty( $filtered_posts ) ) {
				$this->filtered_posts = $filtered_posts;
				add_filter( 'searchwp\post__in', [ $this, 'include_filtered_posts' ] );
			}

			/**
			 * Action hook to allow for custom handling before the SearchWP WooCommerce search is run.
			 *
			 * @since 1.1.10
			 *
			 * @param SearchWP_WooCommerce_Integration $this The SearchWP WooCommerce Integration object.
			 */
			do_action( 'searchwp_woocommerce_before_search', $this );

			/**
			 * Filter to determine whether to log WooCommerce searches.
			 *
			 * @since 1.1.0
			 *
			 * @param bool $log Whether to log WooCommerce searches.
			 */
			if ( ! apply_filters( 'searchwp_woocommerce_log_searches', true ) ) {
				add_filter( 'searchwp\statistics\log', '__return_false' );
			}

			$wc_query = new WC_Query();

			$args = [
				's'              => $this->original_query,
				'engine'         => $searchwp_engine,
				'page'           => $swppg,
				'fields'         => 'ids',
				'posts_per_page' => -1,
				'tax_query'      => $wc_query->get_tax_query( [] , true ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				'meta_query'     => $wc_query->get_meta_query( [] , true ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			];

			/**
			 * Filter to modify the SearchWP query arguments for WooCommerce searches.
			 *
			 * @since 1.1.10
			 *
			 * @param array $args The SearchWP query arguments.
			 */
			$args = apply_filters( 'searchwp_woocommerce_query_args', $args );

			$results = new SWP_Query( $args );

			$this->results = $results->posts;

			// Force 'no results' if the results are empty.
			if ( empty( $this->results ) ) {
				$this->results = [ 0 ];
			}

			// Once our search has run we don't want to interfere any subsequent queries.
			add_filter( 'searchwp\native\short_circuit', '__return_true' );

			return $this->results;
		} elseif ( ! empty( $this->results ) ) {
			return $this->results;
		}

		return (array) $filtered_posts;
	}

	/**
	 * WooCommerce stores products in view as a transient based on $wp_query but that falls apart
	 * with search terms that rely on SearchWP, WP_Query's s param returns nothing, and that gets used by WC.
	 *
	 * @since 1.1.0
	 */
	public function hijack_query_vars() {

		global $wp_query;

		if ( $this->is_woocommerce_search()
			&& ( function_exists( 'SWP' ) || defined( 'SEARCHWP_VERSION' ) )
			&& ! isset( $_GET['orderby'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& $this->original_query ) {

			$wp_query->set( 'post__in', [] );
			$wp_query->set( 's', '' );

			if ( isset( $wp_query->query['s'] ) ) {
				unset( $wp_query->query['s'] );
			}
		}
	}

	/**
	 * Put back the search query once we've hijacked it to get around WooCommerce's products in view storage.
	 *
	 * @since 1.1.0
	 */
	public function replace_original_search_query() {

		global $wp_query;

		if ( ! empty( $this->original_query ) ) {
			$wp_query->set( 's', $this->original_query );
		}
	}

	/**
	 * Determine whether a WooCommerce search is taking place.
	 *
	 * @since 1.1.0
	 *
	 * @param bool $result Whether a WooCommerce search is taking place.
	 *
	 * @return bool
	 */
	public function is_woocommerce_search( $result = false ) {

		/**
		 * Filter to force SearchWP to handle WooCommerce searches.
		 *
		 * @since 1.1.11
		 *
		 * @param bool $forced Whether to force SearchWP to handle WooCommerce searches.
		 */
		$woocommerce_search = apply_filters( 'searchwp_woocommerce_forced', $result );

		if (
			( is_search()
				|| (
					is_archive()
					&& isset( $_GET['s'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					&& ! empty( $_GET['s'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				)
			)
			&& isset( $_GET['post_type'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& $_GET['post_type'] === 'product' // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		) {
			$woocommerce_search = true;
		}

		return $woocommerce_search;
	}

	/**
	 * Utilize WooCommerce's WC_Query object to retrieve information about any ordering that's going on.
	 *
	 * @since 1.1.0
	 */
	public function get_woocommerce_ordering() {

		if ( ! $this->is_woocommerce_search() ) {
			return;
		}

		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		if ( ! isset( WC()->query ) && ! is_object( WC()->query ) ) {
			return;
		}

		if ( ! method_exists( WC()->query, 'get_catalog_ordering_args' ) ) {
			return;
		}

		$this->ordering               = WC()->query->get_catalog_ordering_args();
		$this->ordering['wc_orderby'] = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}


	/**
	 * Set our environment variables once a WooCommerce query is in progress.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Query $q The WP_Query object.
	 */
	public function product_query( $q ) {

		global $wp_query;

		if ( $this->is_woocommerce_search() ) {
			$q->set( 's', '' );
		}

		/**
		 * Filter to determine whether to log WooCommerce searches.
		 *
		 * @since 1.1.0
		 *
		 * @param bool $log Whether to log WooCommerce searches.
		 */
		$force_weight_sort = apply_filters( 'searchwp_woocommerce_force_weight_sort', true );

		// if SearchWP found search results we want the order of results to be returned by SearchWP weight in descending order.
		if ( $this->is_woocommerce_search() && $force_weight_sort ) {
			$wp_query->set( 'order', 'DESC' );
			$wp_query->set( 'orderby', 'post__in' );

			// If it's not the main Search page, it's the WooCommerce Shop page.
			if ( ! is_search() && wc_get_page_id( 'shop' ) === get_queried_object_id() ) {
				$wp_query->set( 's', '' );
			}
		}
	}

	/**
	 * WooCommerce Layered Nav Widgets fire a query to get term counts on each load, when SearchWP is in play
	 * these counts can be incorrect when searching for taxonomy terms that match the Layered Nav filters,
	 * so we need to hijack this query entirely, run our own, and generate new SQL for WooCommerce to fire.
	 *
	 * @since 1.1.6
	 *
	 * @param WP_Query $query The WP_Query object.
	 *
	 * @return mixed
	 */
	public function get_filtered_term_product_counts_query( $query ) {

		global $wpdb;

		if ( empty( $this->results ) ) {
			return $query;
		}

		// If we've found results and there are supposed to be zero results, we need to force that here.
		if ( count( $this->results ) === 1 && isset( $this->results[0] ) && $this->results[0] === 0 ) {
			$query['where'] .= ' AND 1 = 0';
		} else {
			// Modify the WHERE clause to also include SearchWP-provided results.
			$query['where'] .= " AND {$wpdb->posts}.ID IN (" . implode( ',', array_map( 'absint', $this->results ) ) . ')';
		}

		return $query;
	}

	/**
	 * Depending on the sorting taking place we may need a custom JOIN in the main SearchWP query.
	 *
	 * @since 1.1.0
	 *
	 * @param string $sql The current JOIN clause.
	 *
	 * @return string
	 */
	public function query_main_join( $sql ) {

		global $wpdb;

		if ( ! $this->is_woocommerce_search() ) {
			return $sql;
		}

		// If WooCommerce is sorting results we need to tell SearchWP to return them in that order.

		if ( ! isset( $this->ordering['wc_orderby'] ) ) {
			$this->get_woocommerce_ordering();
		}

		// Depending on the sorting we need to do different things.
		if ( isset( $this->ordering['wc_orderby'] ) ) {

			switch ( $this->ordering['wc_orderby'] ) {
				case 'price':
				case 'price-desc':
				case 'popularity':
					$meta_key = $this->ordering['wc_orderby'] === 'price' ? '_price' : 'total_sales';
					$sql      = $sql . $wpdb->prepare( " LEFT JOIN {$wpdb->postmeta} AS swpwc ON {$wpdb->posts}.ID = swpwc.post_id AND swpwc.meta_key = %s", $meta_key );
					break;

				case 'rating':
					$sql = $sql . " LEFT OUTER JOIN {$wpdb->comments} swpwpcom ON({$wpdb->posts}.ID = swpwpcom.comment_post_ID) LEFT JOIN {$wpdb->commentmeta} swpwpcommeta ON(swpwpcom.comment_ID = swpwpcommeta.comment_id) ";
					break;
			}
		}

		return $sql;
	}

	/**
	 * Handle the various sorting capabilities offered by WooCommerce by making sure SearchWP respects them since
	 * we are always ordering by post__in based on SearchWP's retrieved results.
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	public function query_orderby() {

		if ( ! $this->is_woocommerce_search() || empty( $this->ordering ) ) {
			return [];
		}

		if ( ! isset( $this->ordering['wc_orderby'] ) ) {
			$this->get_woocommerce_ordering();
		}

		if ( ! isset( $this->ordering['wc_orderby'] ) ) {
			return [];
		}

		// Depending on the sorting we need to do different things.
		$order = isset( $this->ordering['order'] ) ? $this->ordering['order'] : 'ASC';

		return $this->build_orderby_array( $this->ordering['wc_orderby'], $order );
	}

	/**
	 * Build the ORDER BY array based on the WooCommerce ordering.
	 *
	 * @since 1.4.0
	 *
	 * @param string $wc_orderby The WooCommerce orderby parameter.
	 * @param string $order      The order parameter.
	 *
	 * @return array
	 */
	private function build_orderby_array( $wc_orderby, $order ) {

		$array_return = [];

		switch ( $wc_orderby ) {
			case 'price':
			case 'price-desc':
			case 'popularity':
				$order          = in_array( $wc_orderby, [ 'popularity', 'price-desc' ], true ) ? 'DESC' : $order;
				$array_return[] = [
					'column'    => 'swpwc.meta_value+0',
					'direction' => $order,
				];
				break;

			case 'date':
				$array_return[] = [
					'column'    => 'swpwcposts.post_date',
					'direction' => 'DESC',
				];
				break;

			case 'rating':
				$array_return[] = [
					'column'    => 'average_rating',
					'direction' => 'DESC',
				];
				$array_return[] = [
					'column'    => 'swpwcposts.post_date',
					'direction' => 'DESC',
				];
				break;

			case 'name':
				$array_return[] = [
					'column'    => 'post_title',
					'direction' => 'ASC',
				];
				break;
		}

		return $array_return;
	}

	/**
	 * Callback for SearchWP's main query that facilitates integrating WooCommerce ratings.
	 *
	 * @since 1.1.6
	 *
	 * @return string
	 */
	public function searchwp_query_inject() {

		$sql = '';

		if ( ! isset( $this->ordering['wc_orderby'] ) ) {
			$this->get_woocommerce_ordering();
		}

		if ( $this->is_woocommerce_search() && ! empty( $this->ordering ) ) {
			// Ratings need more SQL.
			if ( $this->ordering['wc_orderby'] === 'rating' ) {
				$sql = ' AVG( swpwpcommeta.meta_value ) as average_rating ';
			}
		}

		return $sql;
	}

	/**
	 * Callback for SearchWP's main query that facilitates sorting by WooCommerce rating.
	 *
	 * @since 1.1.6
	 *
	 * @return string
	 */
	public function searchwp_query_where() {

		$sql = '';

		if ( ! isset( $this->ordering['wc_orderby'] ) ) {
			$this->get_woocommerce_ordering();
		}

		if ( $this->is_woocommerce_search() && ! empty( $this->ordering ) ) {
			// Ratings need extra SQL.
			if ( $this->ordering['wc_orderby'] === 'rating' ) {
				$sql = " AND ( swpwpcommeta.meta_key = 'rating' OR swpwpcommeta.meta_key IS null ) ";
			}
		}

		return $sql;
	}
}

new SearchWP_WooCommerce_Integration();
