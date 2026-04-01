<?php
/**
 * SFL Post Table
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Post_Table' ) ) {

	/**
	 * SFL_Post_Table Class.
	 *
	 * @since 1.0
	 */
	class SFL_Post_Table {

		/**
		 * Object
		 *
		 * @var Object
		 */
		private static $object;

		/**
		 * Post type
		 *
		 * @var String
		 */
		private static $post_type = SFL_Register_Post_Types::SFL_POSTTYPE;

		/**
		 * Plugin Slug
		 *
		 * @var Object
		 */
		private static $plugin_slug = 'sfl';

		/**
		 * Class initialization.
		 *
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'admin_init', array( __CLASS__, 'remove_editor_and_title' ) );
			add_filter( 'admin_body_class', array( __CLASS__, 'custom_body_class' ), 10, 1 ); // Body class.
			add_filter( 'post_row_actions', array( __CLASS__, 'handle_post_row_actions' ), 10, 2 );
			add_filter( 'disable_months_dropdown', array( __CLASS__, 'remove_month_dropdown' ), 10, 2 );
			add_action( 'views_edit-' . self::$post_type, array( __CLASS__, 'remove_views' ) );
			add_filter( 'bulk_actions-edit-' . self::$post_type, array( __CLASS__, 'handle_bulk_actions' ), 10, 1 );
			add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ), 10, 2 ); // Adding Meta Boxes
			add_filter( 'posts_search', array( __CLASS__, 'search_action' ) ); // Add Search Action Query.
			add_action( 'posts_join', array( __CLASS__, 'table_join_query' ), 10, 2 ); // Table Join.
			add_action( 'posts_distinct', array( __CLASS__, 'distinct_post' ), 10, 2 ); // Display result column value in unique.
			add_filter( 'manage_' . self::$post_type . '_posts_columns', array( __CLASS__, 'define_columns' ) ); // define column header.
			add_action( 'manage_' . self::$post_type . '_posts_custom_column', array( __CLASS__, 'render_columns' ), 10, 2 ); // display column value.
			add_filter( 'manage_edit-' . self::$post_type . '_sortable_columns', array( __CLASS__, 'sortable_columns' ) ); // define sortable column.
		}

		/**
		 * Remove Editor and Title Meta boxes.
		 *
		 * @since 1.0
		 */
		public static function remove_editor_and_title() {
			$remove_fields = array( 'editor', 'title' );

			foreach ( $remove_fields as $remove_field ) {
				remove_post_type_support( self::$post_type, $remove_field ); // Remove Supports for Request Post Type.
			}
		}

		/**
		 * Adding SFL meta box
		 *
		 * @since 1.0
		 * @param String $post_type Post Type.
		 * @param Object $post Object.
		 */
		public static function add_meta_boxes( $post_type, $post ) {
			if ( self::$post_type != $post_type ) {
				return;
			}
			// Remove post submit metabox
			remove_meta_box( 'submitdiv', self::$post_type, 'side' );

			add_meta_box( 'sfl_data_table', esc_html__( 'Users List', 'save-for-later-for-woocommerce' ), array( __CLASS__, 'sfl_data_table' ), self::$post_type, 'normal' );
		}

		/**
		 * Display Save/Delete Entries in list table.
		 *
		 * @since 1.0
		 */
		public static function display_save_delete_table() {
			if ( ! class_exists( 'SFL_Save_Delete_List_Table' ) ) {
				require_once SFL_PLUGIN_PATH . '/inc/admin/menu/wp-list-table/class-sfl-save-delete-list-table.php';
			}

			$post_table = new SFL_Save_Delete_List_Table();
			$post_table->prepare_items();

			echo '<div class="sfl_save_delete_table_wrap">';
			$post_table->views();
			$post_table->display();
			echo '</div>';
		}

		/**
		 * Display Purchased Entries in list table.
		 *
		 * @since 1.0
		 */
		public static function display_purchased_table() {
			if ( ! class_exists( 'SFL_Purchased_List_Table' ) ) {
				require_once SFL_PLUGIN_PATH . '/inc/admin/menu/wp-list-table/class-sfl-purchased-list-table.php';
			}

			$post_table = new SFL_Purchased_List_Table();
			$post_table->prepare_items();
			echo '<div class="sfl_purchased_table_wrap">';
			$post_table->views();
			$post_table->display();
			echo '</div>';
		}

		/**
		 * Display Saved/deleted/purchased Table With Menu and grand total.
		 *
		 * @since 1.0
		 */
		public static function sfl_data_table() {
			sfl_table_menus_layout( sfl_get_table_menus() );

			if ( isset( $_REQUEST['status'] ) ) {
				if ( 'sfl_purchased' == $_REQUEST['status'] ) {
					self::display_purchased_table();
				} else {
					self::display_save_delete_table();
				}

				self::display_table_total_data();
			}
		}

		/**
		 * Display Grand Total Data.
		 *
		 * @since 1.0
		 */
		public static function display_table_total_data() {
			global $post_id;

			if ( isset( $_REQUEST['status'] ) ) {
				$child_posts = sfl_get_child_posts( $post_id, sanitize_title( wp_unslash( $_REQUEST['status'] ) ) );

				if ( ! sfl_check_is_array( $child_posts ) ) {
					return;
				}

				$sft_data_total = 0;
				foreach ( $child_posts as $each_data ) {
					$sfl_data        = sfl_get_entry( $each_data );
					$sft_data_total += $sfl_data->get_item_total();
				}

				$allowed_html = array(
					'b'    => array(),
					'span' => array(
						'class' => array(),
					),
				);

				echo wp_kses( sprintf( '<b>Total Price of Products: %s</b>', sfl_price( $sft_data_total, false ) ), $allowed_html );
			}
		}

		/**
		 * Add custom class in body.
		 *
		 * @since 1.0
		 * @param String $class Class.
		 */
		public static function custom_body_class( $class ) {
			global $post;

			if ( ! is_object( $post ) ) {
				return $class;
			}

			if ( $post->post_type == self::$post_type ) {
				return $class . ' sfl_body_content';
			}
			return $class;
		}

		/*
		 * Handle Row Actions
		 */

		public static function handle_post_row_actions( $actions, $post ) {

			if ( $post->post_type == self::$post_type ) {
				return array();
			}

			return $actions;
		}

		/*
		 * Remove views
		 */

		public static function remove_views( $views ) {
			return array();
		}


		/**
		 * Remove month dropdown
		 */
		public static function remove_month_dropdown( $bool, $post_type ) {
			return $post_type == self::$post_type ? true : $bool;
		}

		/*
		 * Handle Bulk Actions
		 */

		public static function handle_bulk_actions( $actions ) {
			global $post;
			if ( $post->post_type == self::$post_type ) {
				return array();
			}

			return $actions;
		}

		/**
		 * Define custom columns
		 */
		public static function define_columns( $columns ) {

			if ( ! sfl_check_is_array( $columns ) ) {
				$columns = array();
			}

			$columns = array(
				'sfl_user_details' => esc_html__( 'User Details', 'save-for-later-for-woocommerce' ),
				'sfl_total'        => esc_html__( 'Total Number of Saved Later Products', 'save-for-later-for-woocommerce' ),
				'sfl_current'      => esc_html__( 'Total Number of Current Saved Later Products', 'save-for-later-for-woocommerce' ),
				'sfl_purchased'    => esc_html__( 'Total Number of Purchased Products', 'save-for-later-for-woocommerce' ),
				'sfl_deleted'      => esc_html__( 'Total Number of Deleted Products', 'save-for-later-for-woocommerce' ),
				'sfl_lst_activity' => esc_html__( 'Last Activity Date', 'save-for-later-for-woocommerce' ),
				'sfl_actions'      => esc_html__( 'Action', 'save-for-later-for-woocommerce' ),
			);

			return $columns;
		}

		/*
		 * Remove views
		 */

		public static function prepare_row_data( $postid ) {

			if ( empty( self::$object ) || self::$object->get_id() != $postid ) {
				self::$object = sfl_get_entry( $postid );
			}

			return self::$object;
		}

		/**
		 * Render each column
		 */
		public static function render_columns( $column, $postid ) {
			self::prepare_row_data( $postid );
			$function = 'render_' . $column . '_cloumn';

			if ( method_exists( __CLASS__, $function ) ) {
				self::$function();
			}
		}

		/**
		 * Render User Details column
		 */
		public static function render_sfl_user_details_cloumn() {
			echo esc_html( self::$object->get_user()->display_name ) . ' <br> ( ' . esc_html( self::$object->get_user()->user_email ) . ' )';
		}

		/**
		 * Render Total SFL column
		 */
		public static function render_sfl_total_cloumn() {
			echo esc_html( self::$object->get_total_sfl() );
		}

		/**
		 * Render Current SFL column
		 */
		public static function render_sfl_current_cloumn() {
			echo esc_html( self::$object->get_saved_count() );
		}

		/**
		 * Render Purchased SFL column
		 */
		public static function render_sfl_purchased_cloumn() {
			echo esc_html( self::$object->get_purchased_count() );
		}

		/**
		 * Render Deleted SFL column
		 */
		public static function render_sfl_deleted_cloumn() {
			echo esc_html( self::$object->get_deleted_count() );
		}

		/**
		 * Render Last Activity column
		 */
		public static function render_sfl_lst_activity_cloumn() {
			echo esc_html( self::$object->get_formatted_activity_date() );
		}

		/**
		 * Render Actions column
		 */
		public static function render_sfl_actions_cloumn() {
			$allowed_html = array(
				'a' => array(
					'href'  => array(),
					'class' => array(),
				),
			);

			echo wp_kses(
				sprintf(
					'<a href="%s" class="%s">%s</a>',
					esc_url( add_query_arg( array( 'status' => 'sfl_saved' ), get_edit_post_link( self::$object->get_id() ) ) ),
					esc_attr( 'edit_view' ),
					esc_html( 'View', 'save-for-later-for-woocommerce' )
				),
				$allowed_html
			);
		}

		/**
		 * Sortable columns
		 */
		public static function search_action( $where ) {
			global $wpdb, $wp_query;

			if ( ! is_search() || ! isset( $_REQUEST['s'] ) || $wp_query->query_vars['post_type'] != self::$post_type ) {
				return $where;
			}

			$search_ids = array();
			$terms      = explode( ',', sanitize_title( wp_unslash( $_REQUEST['s'] ) ) );

			foreach ( $terms as $term ) {
				$term       = $wpdb->esc_like( wc_clean( $term ) );
				$post_query = new SFL_Query( $wpdb->posts, 'p' );
				$post_query->select( 'DISTINCT `p`.post_author' )
						->where( '`p`.post_type', self::$post_type )
						->where( '`p`.post_status', 'publish' );

				$search_ids = $post_query->fetchCol( 'post_author' );

				$post_query = new SFL_Query( $wpdb->posts, 'p' );
				$post_query->select( '`um`.user_id' )
						->leftJoin( $wpdb->users, 'u', '`u`.ID = `p`.ID' )
						->leftJoin( $wpdb->usermeta, 'um', '`p`.ID = `um`.user_id' )
						->whereIn( '`u`.ID', $search_ids )
						->whereIn( '`um`.meta_key', array( 'first_name', 'last_name', 'billing_email', 'nickname' ) )
						->wherelike( '`um`.meta_value', '%' . $term . '%' )
						->orderBy( '`p`.ID' );

				$search_ids = $post_query->fetchCol( 'user_id' );
			}
			$search_ids = array_filter( array_unique( $search_ids ) );

			if ( count( $search_ids ) > 0 ) {
				$where = str_replace( 'AND (((', "AND ( ({$wpdb->posts}.post_author IN (" . implode( ',', $search_ids ) . ')) OR ((', $where );
			}

			return $where;
		}

		/**
		 * Join query
		 */
		public static function table_join_query( $join ) {
			global $wp_query;

			if ( is_admin() && ! isset( $_GET['post'] ) && isset( $wp_query->query_vars['post_type'] ) && $wp_query->query_vars['post_type'] == self::$post_type ) {
				if ( isset( $_REQUEST['s'] ) && isset( $_REQUEST['post_type'] ) && sanitize_title( wp_unslash( $_REQUEST['post_type'] ) ) == self::$post_type ) {
					global $wpdb;
					$join .= " INNER JOIN $wpdb->usermeta ON ($wpdb->posts.post_author = $wpdb->usermeta.user_id)";
				}
			}

			return $join;
		}

		/**
		 * Sortable columns
		 */
		public static function sortable_columns( $columns ) {

			$columns = array(
				'sfl_total'        => 'sfl_total',
				'sfl_current'      => 'sfl_current',
				'sfl_purchased'    => 'sfl_purchased',
				'sfl_deleted'      => 'sfl_deleted',
				'sfl_lst_activity' => 'sfl_lst_activity',
			);

			return $columns;
		}

		/**
		 * Sortable columns
		 */
		public static function orderby_columns( $order_by, $wp_query ) {
			global $wpdb;
			if ( isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == self::$post_type ) {
				if ( ! isset( $_REQUEST['order'] ) && ! isset( $_REQUEST['orderby'] ) ) {
					$order_by = "{$wpdb->posts}.ID " . 'DESC';
				} else {
					$decimal_column = array( 'hrw_available_balance', 'hrw_total_balance' );

					if ( in_array( sanitize_title( wp_unslash( $_REQUEST['orderby'] ) ), $decimal_column ) ) {
						$order_by = "CAST({$wpdb->postmeta}.meta_value AS DECIMAL) " . sanitize_title( wp_unslash( $_REQUEST['order'] ) );
					} elseif ( sanitize_title( wp_unslash( $_REQUEST['orderby'] ) ) == 'post_status' ) {
						$order_by = "{$wpdb->posts}.post_status " . sanitize_title( wp_unslash( $_REQUEST['order'] ) );
					} else {
						$order_by = "{$wpdb->postmeta}.meta_value " . sanitize_title( wp_unslash( $_REQUEST['order'] ) );
					}
				}
			}

			return $order_by;
		}

		/**
		 * Sorting Functionality
		 *
		 * @since 1.0.0
		 */
		public static function orderby_filter_query( $query ) {
			if ( isset( $_REQUEST['post_type'] ) && sanitize_title( wp_unslash( $_REQUEST['post_type'] ) ) == self::$post_type && self::$post_type == $query->query['post_type'] ) {
				if ( isset( $_GET['orderby'] ) ) {
					$excerpt_array = array( 'ID', 'post_status' );
					if ( ! in_array( sanitize_title( wp_unslash( $_GET['orderby'] ) ), $excerpt_array ) ) {
						$query->query_vars['meta_key'] = sanitize_title( wp_unslash( $_GET['orderby'] ) );
					}
				}
			}
		}

		/**
		 * Distinct Functionality
		 *
		 * @since 1.0.0
		 */
		public static function distinct_post( $distinct ) {
			if ( ( isset( $_REQUEST['s'] ) || isset( $_REQUEST['orderby'] ) ) && ( isset( $_REQUEST['post_type'] ) && sanitize_title( wp_unslash( $_REQUEST['post_type'] ) ) ) == self::$post_type ) {
				$distinct .= empty( $distinct ) ? 'DISTINCT' : $distinct;
			}

			return $distinct;
		}
	}

	SFL_Post_Table::init();
}
