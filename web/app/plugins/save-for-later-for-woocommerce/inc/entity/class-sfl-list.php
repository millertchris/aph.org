<?php
/**
 * SFL List
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_List' ) ) {

	/**
	 * SFL_List Class.
	 */
	class SFL_List extends SFL_Post {

		/**
		 * Post Type
		 *
		 * @var String.
		 */
		protected $post_type = SFL_Register_Post_Types::SFL_POSTTYPE;

		/**
		 * Post Status
		 *
		 * @var String.
		 */
		protected $post_status = 'publish';

		/**
		 * SFL User
		 *
		 * @var Object.
		 */
		protected $user;

		/**
		 * Product data
		 *
		 * @var Array.
		 */
		protected $sfl_product_data;

		/**
		 * Saved Count
		 *
		 * @var Integer
		 */
		protected $sfl_saved_count;

		/**
		 * Purchased Count
		 *
		 * @var Integer.
		 */
		protected $sfl_purchased_count;

		/**
		 * Deleted Count
		 *
		 * @var Integer.
		 */
		protected $sfl_deleted_count;

		/**
		 * Saved Products
		 *
		 * @var string.
		 */
		protected $sfl_saved_products;

		/**
		 * Purchased Products
		 *
		 * @var String.
		 */
		protected $sfl_puchased_products;

		/**
		 * Deleted Products
		 *
		 * @var String.
		 */
		protected $sfl_deleted_products;

		/**
		 * Product ID
		 *
		 * @var Integer.
		 */
		protected $sfl_product_id;

		/**
		 * Product Price
		 *
		 * @var String.
		 */
		protected $sfl_product_price;

		/**
		 * Product Quantity
		 *
		 * @var Integer.
		 */
		protected $sfl_product_qty;

		/**
		 * Cart Item key
		 *
		 * @var String.
		 */
		protected $sfl_cart_item_key;

		/**
		 * Cart Item Meta
		 *
		 * @var String.
		 */
		protected $sfl_cart_item;

		/**
		 * Order ID
		 *
		 * @var Integer.
		 */
		protected $sfl_order_id;

		/**
		 * Saved Date
		 *
		 * @var String.
		 */
		protected $sfl_activity_date;

		/**
		 * Parent ID
		 *
		 * @var String.
		 */
		protected $parent_id;

		/**
		 * Meta data keys
		 *
		 * @var Array.
		 */
		protected $meta_data_keys = array(
			'sfl_saved_products'    => '',
			'sfl_puchased_products' => '',
			'sfl_deleted_products'  => '',
			'sfl_product_id'        => '',
			'sfl_product_price'     => '',
			'sfl_product_qty'       => '',
			'sfl_cart_item_key'     => '',
			'sfl_cart_item'         => array(),
			'sfl_order_id'          => '',
			'sfl_activity_date'     => '',
			'sfl_saved_count'       => 0,
			'sfl_purchased_count'   => 0,
			'sfl_deleted_count'     => 0,
		);

		/**
		 * Prepare extra post data
		 *
		 * @since 1.0
		 */
		protected function load_extra_postdata() {
			$this->name        = $this->post->post_title;
			$this->parent_id   = $this->post->post_parent;
			$this->post_author = $this->post->post_author;
		}

		/**
		 * Get User Id.
		 *
		 * @since 1.0
		 * @return Integer
		 */
		public function get_user_id() {
			return $this->post->post_author;
		}

		/**
		 * Get parent Id.
		 *
		 * @since 1.0
		 * @return Integer
		 */
		public function get_parent_id() {
			return $this->parent_id;
		}

		/**
		 * Get User.
		 *
		 * @since 1.0
		 * @return Object
		 */
		public function get_user() {

			if ( $this->user ) {
				return $this->user;
			}

			$this->user = get_userdata( $this->get_user_id() );

			return $this->user;
		}

		/**
		 * Get Product Data.
		 *
		 * @since 1.0
		 * @return Object
		 */
		public function get_product_data() {
			$this->sfl_product_data = wc_get_product( $this->get_product_id() );
			return $this->sfl_product_data;
		}

		/**
		 * Get formatted Activity datetime
		 *
		 * @since 1.0
		 * @return string
		 */
		public function get_formatted_activity_date() {
			return SFL_Date_Time::get_date_object_format_datetime( $this->get_activity_date() );
		}

		/**
		 * Setters and Getters
		 */

		/**
		 * Set Saved Products Count
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_saved_count( $value ) {
			$this->sfl_saved_count = $value;
		}

		/**
		 * Set Purchased Products Count
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_purchased_count( $value ) {
			$this->sfl_purchased_count = $value;
		}

		/**
		 * Set Deleted Products Count
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_deleted_count( $value ) {
			$this->sfl_deleted_count = $value;
		}

		/**
		 * Set Purchased Products
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_puchased_products( $value ) {
			$this->sfl_puchased_products = $value;
		}

		/**
		 * Set Deleted Products
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_deleted_products( $value ) {
			$this->sfl_deleted_products = $value;
		}

		/**
		 * Set Product ID
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_product_id( $value ) {
			$this->sfl_product_id = $value;
		}

		/**
		 * Set Product Price
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_product_price( $value ) {
			$this->sfl_product_price = $value;
		}

		/**
		 * Set Product Quantity
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_product_qty( $value ) {
			$this->sfl_product_qty = $value;
		}

		/**
		 * Set Cart Item Key
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_cart_item_key( $value ) {
			$this->sfl_cart_item_key = $value;
		}

		/**
		 * Set Cart Item Meta
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_cart_item( $value ) {
			$this->sfl_cart_item = $value;
		}

		/**
		 * Set Order ID
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_order_id( $value ) {
			$this->sfl_order_id = $value;
		}

		/**
		 * Set Activity Date
		 *
		 * @since 1.0
		 * @param string $value Value.
		 */
		public function set_activity_date( $value ) {
			$this->sfl_activity_date = $value;
		}

		/**
		 * Get Saved Count
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_saved_count() {
			return (int) $this->sfl_saved_count;
		}

		/**
		 * Get Purchased Count
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_purchased_count() {
			return (int) $this->sfl_purchased_count;
		}

		/**
		 * Get Deleted Count
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_deleted_count() {
			return (int) $this->sfl_deleted_count;
		}

		/**
		 * Get Saved Products
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_total_sfl() {
			return (int) ( $this->get_saved_count() + $this->get_purchased_count() + $this->get_deleted_count() );
		}

		/**
		 * Get Product ID
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_product_id() {
			return $this->sfl_product_id;
		}

		/**
		 * Get Product Name
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_product_name() {
			if ( ! is_object( $this->get_product_data() ) ) {
				return '';
			}

			return '<a href="' . esc_url( get_permalink( $this->get_product_id() ) ) . '">' . $this->get_product_data()->get_name() . '</a>';
		}

		/**
		 * Get Product Image
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_product_image() {

			return $this->get_product_data()->get_image();
		}

		/**
		 * Get Product price
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_product_price() {
			return $this->sfl_product_price;
		}

		/**
		 * Get Product Quantity
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_product_qty() {

			return $this->sfl_product_qty;
		}

		/**
		 * Get Item Total
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_item_total() {
			$sfl_product_qty   = ! empty( $this->sfl_product_qty ) ? $this->sfl_product_qty : 0;
			$sfl_product_price = ! empty( $this->sfl_product_price ) ? $this->sfl_product_price : 0;

			return ( $sfl_product_qty * $sfl_product_price );
		}

		/**
		 * Get Cart Item Key
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_cart_item_key() {
			return $this->sfl_cart_item_key;
		}

		/**
		 * Get Cart Item Meta
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_cart_item() {
			return $this->sfl_cart_item;
		}

		/**
		 * Get Activity Date
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_activity_date() {
			return $this->sfl_activity_date;
		}

		/**
		 * Get Order ID
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_order_id() {
			return $this->sfl_order_id;
		}

		/**
		 * Get Formatted Order Link
		 *
		 * @since 1.0
		 * @return string.
		 */
		public function get_formatted_order_link() {
			return '<a href="' . esc_url( get_edit_post_link( $this->get_order_id() ) ) . '" >#' . $this->get_order_id() . '</a>';
		}
	}

}
