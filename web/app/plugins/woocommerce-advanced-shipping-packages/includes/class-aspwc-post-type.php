<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class ASPWC_Post_Type.
 *
 * Initialize the ASPWC custom post type.
 *
 * @class       ASPWC_Post_Type
 * @author     	Jeroen Sormani
 * @package		WooCommerce Shipping Packages
 * @version		1.0.0
 */
class ASPWC_Post_Type {


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Register post type
		add_action( 'init', array( $this, 'register_post_type' ) );
	}


	/**
	 * Post type.
	 *
	 * Register and setup the custom post type for this plugin.
	 *
	 * @since 1.0.0
	 */
	public function register_post_type() {

		$labels = array(
			'name'               => __( 'Shipping Package', 'advanced-shipping-packages-for-woocommerce' ),
			'singular_name'      => __( 'Shipping Package', 'advanced-shipping-packages-for-woocommerce' ),
			'add_new'            => __( 'Add New', 'advanced-shipping-packages-for-woocommerce' ),
			'add_new_item'       => __( 'Add New Shipping Package', 'advanced-shipping-packages-for-woocommerce' ),
			'edit_item'          => __( 'Edit Shipping Package', 'advanced-shipping-packages-for-woocommerce' ),
			'new_item'           => __( 'New Shipping Package', 'advanced-shipping-packages-for-woocommerce' ),
			'view_item'          => __( 'View Shipping Package', 'advanced-shipping-packages-for-woocommerce' ),
			'search_items'       => __( 'Search Shipping Packages', 'advanced-shipping-packages-for-woocommerce' ),
			'not_found'          => __( 'No Shipping Packages', 'advanced-shipping-packages-for-woocommerce' ),
			'not_found_in_trash' => __( 'No Shipping Packages found in Trash', 'advanced-shipping-packages-for-woocommerce' ),
		);

		register_post_type( 'shipping_package', array(
			'label'              => 'shipping_package',
			'show_ui'            => true,
			'show_in_menu'       => false,
			'public'             => false,
			'publicly_queryable' => false,
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
			'rewrite'            => false,
			'_builtin'           => false,
			'query_var'          => true,
			'supports'           => array( 'title' ),
			'labels'             => $labels,
		) );

	}
}
