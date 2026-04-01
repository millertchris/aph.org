<?php
/**
 * SFL List Table
 *
 * @package list table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'SFL_Purchased_List_Table' ) ) {

	/**
	 * SFL_Purchased_List_Table Class.
	 * */
	class SFL_Purchased_List_Table extends WP_List_Table {

		/**
		 * Total Count of Table
		 *
		 * @var Integer
		 * */
		private $total_items;

		/**
		 * Per page count
		 *
		 * @var Integer
		 * */
		private $perpage;

		/**
		 * Database
		 *
		 * @var Object
		 * */
		private $database;

		/**
		 * Offset
		 *
		 * @var Integer
		 * */
		private $offset;

		/**
		 * Order BY
		 *
		 * @var String
		 * */
		private $orderby = 'ORDER BY menu_order ASC';

		/**
		 * Table Slug
		 *
		 * @var String
		 * */
		private $table_slug = 'sfl';

		/**
		 * Post type
		 *
		 * @var String
		 * */
		private $post_type = SFL_Register_Post_Types::SFL_POSTTYPE;

		/**
		 * Base URL
		 *
		 * @var String
		 * */
		private $base_url;

		/**
		 * Current URL
		 *
		 * @var String
		 * */
		private $current_url;

		/**
		 * Prepare the table Data to display table based on pagination.
		 *
		 * @since 1.0
		 * */
		public function prepare_items() {
			global $wpdb;
			$this->database = $wpdb;

			$this->base_url = sfl_get_base_url();

			add_filter( sanitize_key( $this->table_slug . '_query_orderby' ), array( $this, 'query_orderby' ) );

			$this->prepare_current_url();
			$this->get_perpage_count();
			$this->get_current_pagenum();
			$this->get_current_page_items();
			$this->prepare_pagination_args();
			$this->prepare_column_headers();
		}

		/**
		 * Get per page count
		 * */
		private function get_perpage_count() {

			$this->perpage = 10;
		}

		/**
		 * Prepare pagination
		 * */
		private function prepare_pagination_args() {

			$this->set_pagination_args(
				array(
					'total_items' => $this->total_items,
					'per_page'    => $this->perpage,
				)
			);
		}

		/**
		 * Get current page number
		 * */
		private function get_current_pagenum() {

			$this->offset = 10 * ( $this->get_pagenum() - 1 );
		}

		/**
		 * Prepare header columns
		 * */
		private function prepare_column_headers() {
			$columns               = $this->get_columns();
			$hidden                = $this->get_hidden_columns();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );
		}

		/**
		 * Initialize the columns
		 * */
		public function get_columns() {
			$columns = array(
				'product_name'  => esc_html__( 'Product Name', 'save-for-later-for-woocommerce' ),
				'price'         => esc_html__( 'Price', 'save-for-later-for-woocommerce' ),
				'quantity'      => esc_html__( 'Quantity', 'save-for-later-for-woocommerce' ),
				'total'         => esc_html__( 'Total', 'save-for-later-for-woocommerce' ),
				'order_id'      => esc_html__( 'Order ID', 'save-for-later-for-woocommerce' ),
				'activity_date' => esc_html__( 'Activity Date', 'save-for-later-for-woocommerce' ),
			);

			return $columns;
		}

		/**
		 * Initialize the hidden columns
		 * */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Initialize the sortable columns
		 * */
		public function get_sortable_columns() {

			return array(
				'price'         => array( 'price', false ),
				'quantity'      => array( 'quantity', false ),
				'total'         => array( 'total', false ),
				'activity_date' => array( 'activity_date', false ),
			);
		}

		/**
		 * Get current url
		 * */
		private function prepare_current_url() {
			$pagenum       = $this->get_pagenum();
			$args['paged'] = $pagenum;
			$url           = add_query_arg( $args, $this->base_url );

			$this->current_url = $url;
		}

		/**
		 * Prepare each column data
		 *
		 * @since 1.0
		 * @param Object $item Post Object.
		 * @param String $column_name Table Column Name.
		 * */
		protected function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'product_name':
					/**
					 * Save for later Product Name html
					 *
					 * @since 3.8.0
					 */
					$product_name = apply_filters( 'sfl_admin_list_product_name_html', $item->get_product_name(), $item->get_product_data(), $item );

					return $product_name;

				case 'price':
					/**
					 * Save for later Product Name html
					 *
					 * @since 3.8.0
					 */
					$product_price = apply_filters( 'sfl_admin_list_product_price_html', sfl_price( $item->get_product_price(), false ), $item->get_product_data(), $item );

					return $product_price;

				case 'quantity':
					/**
					 * Save for later Product Name html
					 *
					 * @since 3.8.0
					 */
					$product_qty = apply_filters( 'sfl_admin_list_product_quantity_html', $item->get_product_qty(), $item->get_product_data(), $item );

					return $product_qty;

				case 'total':
					return sfl_price( $item->get_item_total(), false );

				case 'order_id':
					return $item->get_formatted_order_link( $item->get_order_id() );

				case 'activity_date':
					return $item->get_formatted_activity_date();

			}
		}

		/**
		 * Initialize the columns
		 *
		 * @since 1.0
		 * */
		private function get_current_page_items() {
			global $post_id;

			$get_parent_data = sfl_get_entry( $post_id );

			$status = ' IN("sfl_purchased")';

			if ( ! empty( $_REQUEST[ 's' ] ) || ! empty( $_REQUEST[ 'orderby' ] ) ) { // @codingStandardsIgnoreLine.
				$where = ' INNER JOIN ' . $this->database->postmeta . " pm ON ( pm.post_id = p.ID ) where post_type='" . $this->post_type . "' and post_status " . $status . " and post_author='$get_parent_data->post_author'";
			} else {
				$where = " where post_type='" . $this->post_type . "' and post_status" . $status . " and post_author='$get_parent_data->post_author'";
			}

			/**
			 * Filter for Post Where
			 *
			 * @since 1.0
			 * @param String $where
			 */
			$where = apply_filters( $this->table_slug . '_query_where', $where );

			/**
			 * Filter for Post Limit
			 *
			 * @since 1.0
			 */
			$limit = apply_filters( $this->table_slug . '_query_limit', $this->perpage );

			/**
			 * Filter for Post Offset
			 *
			 * @since 1.0
			 */
			$offset = apply_filters( $this->table_slug . '_query_offset', $this->offset );

			/**
			 * Filter for Post Order by
			 *
			 * @since 1.0
			 */
			$orderby = apply_filters( $this->table_slug . '_query_orderby', $this->orderby );

			$count_items       = $this->database->get_results( 'SELECT DISTINCT ID FROM ' . $this->database->posts . " AS p $where $orderby" );
			$this->total_items = count( $count_items );

			$prepare_query = $this->database->prepare( 'SELECT DISTINCT ID FROM ' . $this->database->posts . " AS p $where $orderby LIMIT %d,%d", $offset, $limit );

			$items = $this->database->get_results( $prepare_query, ARRAY_A );

			$this->prepare_item_object( $items );
		}

		/**
		 * Prepare item Object
		 *
		 * @since 1.0
		 * @param Array $items Post Value.
		 * */
		private function prepare_item_object( $items ) {
			$prepare_items = array();
			if ( sfl_check_is_array( $items ) ) {
				foreach ( $items as $item ) {
					$item_data = sfl_get_entry( $item['ID'] );
					if ( 'publish' == get_post_status( $item_data->get_product_id() ) ) { // valid product
						$prepare_items[] = $item_data;
					} else {
						sfl_invalid_delete( $item['ID'] );
					}
				}
			}

			$this->items = $prepare_items;
		}

		/**
		 * Sort
		 *
		 * @since 1.0
		 * @param String $orderby Table Display Order.
		 * */
		public function query_orderby( $orderby ) {

			if ( empty( $_REQUEST[ 'orderby' ] ) ) { // @codingStandardsIgnoreLine.
				return $orderby;
			}

			$order = 'DESC';
			if ( ! empty( $_REQUEST[ 'order' ] ) && is_string( $_REQUEST[ 'order' ] ) ) { // @codingStandardsIgnoreLine.
				if ( 'ASC' === strtoupper( wc_clean( wp_unslash( $_REQUEST[ 'order' ] ) ) ) ) { // @codingStandardsIgnoreLine.
					$order = 'ASC';
				}
			}

			switch ( wc_clean( wp_unslash( $_REQUEST[ 'orderby' ] ) ) ) { // @codingStandardsIgnoreLine.
				case 'rule_name':
					$orderby = ' ORDER BY p.post_title ' . $order;
					break;
				case 'status':
					$orderby = ' ORDER BY p.post_status ' . $order;
					break;
				case 'created':
					$orderby = ' ORDER BY p.post_date ' . $order;
					break;
				case 'modified':
					$orderby = ' ORDER BY p.post_modified ' . $order;
					break;
			}

			return $orderby;
		}
	}

}
