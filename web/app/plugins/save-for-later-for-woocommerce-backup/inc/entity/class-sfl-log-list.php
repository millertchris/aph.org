<?php

/**
 * SFL List
 *
 * @package Class
 */
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('SFL_Log_List')) {

	/**
	 * SFL_Log_List Class.
	 */
	class SFL_Log_List extends SFL_Post {

		/**
		 * Post Type
		 *
		 * @var String.
		 */
		protected $post_type = SFL_Register_Post_Types::SFL_MASTERLOG;

		/**
		 * Post Status
		 *
		 * @var String.
		 */
		protected $post_status = 'publish';

		/**
		 * SFL User
		 *
		 * @var String.
		 */
		protected $user;

		/**
		 * Product data
		 *
		 * @var String.
		 */
		protected $sfl_product_data;

		/**
		 * Product ID
		 *
		 * @var String.
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
		 * @var String.
		 */
		protected $sfl_product_qty;

		/**
		 * Cart Item key
		 *
		 * @var String.
		 */
		protected $sfl_cart_item_key;

		/**
		 * Order ID
		 *
		 * @var String.
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
		 * @since 1.0
		 * @var Array
		 */
		protected $meta_data_keys = array(
			'sfl_product_id' => '',
			'sfl_product_price' => '',
			'sfl_product_qty' => '',
			'sfl_cart_item_key' => '',
			'sfl_order_id' => '',
			'sfl_activity_date' => '',
				);

		/**
		 * Prepare extra post data
		 *
		 * @since 1.0
		 */
		protected function load_extra_postdata() {
			$this->name = $this->post->post_title;
			$this->parent_id = $this->post->post_parent;
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
			if ($this->user) {
				return $this->user;
			}

			$this->user = get_userdata($this->get_user_id());

			return $this->user;
		}

		/**
		 * Get Product Data.
		 *
		 * @since 1.0
		 * @return Object
		 */
		public function get_product_data() {
			$this->sfl_product_data = wc_get_product($this->get_product_id());

			return $this->sfl_product_data;
		}

		/**
		 * Get formatted Activity datetime
		 *
		 * @since 1.0
		 * @return string
		 */
		public function get_formatted_activity_date() {
			return SFL_Date_Time::get_date_object_format_datetime($this->get_activity_date());
		}

		/**
		 * Setters and Getters
		 */

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
		 * Get Saved Products
		 *
		 * @since 1.0
		 * @return Integer.
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
			if (!is_object($this->get_product_data())) {
				return '';
			}
			
			return '<a href="' . esc_url(get_permalink($this->get_product_id())) . '">' . $this->get_product_data()->get_name() . '</a>';
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
			$sfl_product_qty = !empty($this->sfl_product_qty) ? $this->sfl_product_qty : 0;
			$sfl_product_price = !empty($this->sfl_product_price) ? $this->sfl_product_price : 0;

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
			return '<a href="' . esc_url(get_edit_post_link($this->get_order_id())) . '" >' . $this->get_order_id() . '</a>';
		}
	}

}
