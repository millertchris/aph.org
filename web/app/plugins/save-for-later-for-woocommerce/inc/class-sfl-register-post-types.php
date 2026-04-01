<?php
/**
 * Custom Post Type.
 * 
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Register_Post_Types' ) ) {

	/**
	 * SFL_Register_Post_Types Class.
	 */
	class SFL_Register_Post_Types {
		/*
		 * Save For Later List Post Type
		 */
		const SFL_POSTTYPE = 'sfl-list';

		/*
		 * Save For Later Master Log Post Type
		 */
		const SFL_MASTERLOG = 'sfl-masterlog';

		/**
		 * SFL_Register_Post_Types Class initialization.
		 * 
		 * @since 1.0
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'register_custom_post_types' ) );
		}

		/**
		 * Register Custom Post types.
		 * 
		 * @since 1.0
		 */
		public static function register_custom_post_types() {
			if ( ! is_blog_installed() ) {
				return;
			}

			$custom_post_types = array(
				self::SFL_POSTTYPE  => array( 'SFL_Register_Post_Types', 'sfl_post_type_args' ),
				self::SFL_MASTERLOG => array( 'SFL_Register_Post_Types', 'sfl_masterlog_post_type_args' ),

			);
			
			/**
			 * Filter SFL Custom Post
			 *
			 * @since 1.0
			 * */
			$custom_post_types = apply_filters( 'sfl_add_custom_post_types', $custom_post_types );

			// return if no post type to register.
			if ( ! sfl_check_is_array( $custom_post_types ) ) {
				return;
			}

			foreach ( $custom_post_types as $post_type => $args_function ) {

				$args = array();
				if ( $args_function ) {
					$args = call_user_func_array( $args_function, $args );
				}

				// Register custom post type.
				register_post_type( $post_type, $args );
			}
		}

		/**
		 * Prepare SFL Post type arguments.
		 * 
		 * @since 1.0
		 */
		public static function sfl_post_type_args() {
			/**
			 * Filter SFL Rule Post
			 *
			 * @since 1.0
			 * */
			return apply_filters(
				'sfl_rules_post_type_args',
				array(
					'labels'              => array(
						'name'               => esc_html__( 'Product List', 'save-for-later-for-woocommerce' ),
						'singular_name'      => esc_html__( 'Product List', 'save-for-later-for-woocommerce' ),
						'menu_name'          => esc_html__( 'Product List', 'save-for-later-for-woocommerce' ),
						'add_new'            => esc_html__( 'Add New Product List', 'save-for-later-for-woocommerce' ),
						'add_new_item'       => esc_html__( 'Add New Product List', 'save-for-later-for-woocommerce' ),
						'edit'               => esc_html__( 'Edit Product List', 'save-for-later-for-woocommerce' ),
						'edit_item'          => esc_html__( 'View Product List', 'save-for-later-for-woocommerce' ),
						'new_item'           => esc_html__( 'New Product List', 'save-for-later-for-woocommerce' ),
						'view'               => esc_html__( 'View Product List', 'save-for-later-for-woocommerce' ),
						'view_item'          => esc_html__( 'View Product List', 'save-for-later-for-woocommerce' ),
						'search_items'       => esc_html__( 'Search Users', 'save-for-later-for-woocommerce' ),
						'not_found'          => esc_html__( 'No Users found', 'save-for-later-for-woocommerce' ),
						'not_found_in_trash' => esc_html__( 'No Users found in trash', 'save-for-later-for-woocommerce' ),
					),
					'description'         => esc_html__( 'Here you can able to see list of Users', 'save-for-later-for-woocommerce' ),
					'public'              => true,
					'show_ui'             => true,
					'capability_type'     => 'post',
					'show_in_menu'        => 'sfl_user_list',
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
					'show_in_nav_menus'   => false,
					'capabilities'        => array(
						'publish_posts'       => 'publish_posts',
						'edit_posts'          => 'edit_posts',
						'edit_others_posts'   => 'edit_others_posts',
						'delete_posts'        => 'delete_posts',
						'delete_others_posts' => 'delete_others_posts',
						'read_private_posts'  => 'read_private_posts',
						'edit_post'           => 'edit_post',
						'delete_post'         => 'delete_post',
						'read_post'           => 'read_post',
						'create_posts'        => 'do_not_allow',
					),
					'map_meta_cap'        => true,
				)
			);
		}

		/**
		 * Prepare CRF Post type arguments.
		 * 
		 * @since 1.0
		 */
		public static function sfl_masterlog_post_type_args() {
			/**
			 * Filter SFL Master Log Post
			 *
			 * @since 1.0
			 * */
			return apply_filters(
				'sfl_masterlog_post_type_args',
				array(
					'label'           => esc_html__( 'Master Log', 'crowdfunding-pro-for-woocommerce' ),
					'public'          => false,
					'hierarchical'    => false,
					'supports'        => false,
					'capability_type' => 'post',
					'rewrite'         => false,
				)
			);
		}
	}

	SFL_Register_Post_Types::init();
}
