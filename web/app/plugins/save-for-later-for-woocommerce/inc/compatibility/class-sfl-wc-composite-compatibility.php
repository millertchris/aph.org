<?php
/**
 * Woocommerce Composite Compatibility.
 *
 * @package Save for Later/Woocommerce Composite Compatibility.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_WC_Composite_Compatibility' ) ) {

	/**
	 * Class Declaration.
	 * */
	class SFL_WC_Composite_Compatibility extends SFL_Abstract_Compatibility {
		/**
		 * Field name suffix.
		 *
		 * @var Array
		 */
		protected static $suffix = '';

		/**
		 * Class Constructor.
		 *
		 * @since 3.8.0
		 */
		public function __construct() {
			$this->id = 'wc_composite';

			parent::__construct();
		}

		/**
		 * Is plugin enabled?.
		 *
		 * @since 3.8.0
		 * @return Boolean
		 * */
		public function is_plugin_enabled() {
			return class_exists( 'WC_Composite_Products' );
		}

		/**
		 * Admin Hook in methods.
		 *
		 * @since 3.8.0
		 */
		public function admin_action() {
			add_filter( 'sfl_admin_list_product_name_html', array( $this, 'sfl_table_display_name' ), 10, 3 );
			add_filter( 'sfl_admin_list_product_price_html', array( $this, 'sfl_table_display_price' ), 10, 3 );
			add_filter( 'sfl_admin_list_product_quantity_html', array( $this, 'sfl_table_display_quantity' ), 10, 3 );
		}

		/**
		 * Hook in methods.
		 *
		 * @since 3.8.0
		 */
		public function actions() {
			add_filter( 'sfl_supported_product_types', array( $this, 'add_product_type' ) );
			add_filter( 'sfl_check_is_valid_cart_item', array( $this, 'restrict_composite_item' ), 10, 3 );
			add_filter( 'sfl_product_image_html', array( $this, 'sfl_table_display_image' ), 10, 3 );
			add_filter( 'sfl_product_name_html', array( $this, 'sfl_table_display_name' ), 10, 3 );
			add_filter( 'sfl_product_price_html', array( $this, 'sfl_table_display_price' ), 10, 3 );
			add_filter( 'sfl_product_quantity_html', array( $this, 'sfl_table_display_quantity' ), 10, 3 );
			add_filter( 'sfl_list_data_product_price', array( $this, 'composite_product_price' ), 10, 4 );
			add_filter( 'sfl_check_is_product_already_in_list', array( $this, 'check_product_already_in_list' ), 10, 3 );
			add_filter( 'slf_is_valid_to_display_price_diff', array( $this, 'price_diff_display_validation' ), 10, 3 );
			add_filter( 'sfl_check_product_already_in_cart', array( $this, 'check_product_already_in_cart' ), 10, 2 );
		}

		/**
		 * Allow Product Types.
		 *
		 * @since 3.8.0
		 * @param Array $allowed_product_type Allowed Product Types.
		 * @return Array
		 */
		public function add_product_type( $allowed_product_type ) {
			$product_types = array( 'composite' );

			if ( ! sfl_check_is_array( $allowed_product_type ) ) {
				return $product_types;
			}

			return array_merge( $allowed_product_type, $product_types );
		}

		/**
		 * Allow Product Types.
		 *
		 * @since 3.8.0
		 * @param Boolean $bool whether is to display or not.
		 * @param String  $cart_item_key Cart Item Key.
		 * @param Array   $cart_item Cart Item.
		 * @return Boolean
		 */
		public function restrict_composite_item( $bool, $cart_item_key, $cart_item ) {
			if ( ! sfl_check_is_array( $cart_item ) ) {
				return $bool;
			}

			if ( isset( $cart_item['composite_parent'] ) ) {
				return false;
			}

			return $bool;
		}

		/**
		 * Save for Later Table Image
		 *
		 * @since 3.8.0
		 * @param HTML       $html Image html.
		 * @param WC_Product $product_obj Product Object.
		 * @param SFL_List   $sfl_obj Save for Later Object.
		 * @return HTML
		 */
		public function sfl_table_display_image( $html, $product_obj, $sfl_obj ) {
			if ( ! sfl_check_is_array( $sfl_obj->get_cart_item() ) ) {
				return $html;
			}

			if ( ! isset( $sfl_obj->get_cart_item()['composite_data'] ) || ! sfl_check_is_array( $sfl_obj->get_cart_item()['composite_data'] ) ) {
				return $html;
			}

			$html .= '<div class="slf-composite-product-child">';
			$count = 1;

			foreach ( $sfl_obj->get_cart_item()['composite_data'] as $key => $value ) {
				$_product_id = isset( $value['product_id'] ) ? $value['product_id'] : '';

				if ( empty( $_product_id ) ) {
					continue;
				}

				$_product_obj = wc_get_product( $_product_id );

				if ( ! is_a( $_product_obj, 'WC_Product' ) ) {
					continue;
				}

				$html .= '<div class="sfl-component-wrap sfl-component-img-wrap sfl-component-img-' . $count . '" >';
				$html .= sfl_get_product_thumbnail( $_product_obj, false );
				$html .= '</div>';
				$count++;
			}

			$html .= '</div>';

			return $html;
		}

		/**
		 * Save for Later Table Name
		 *
		 * @since 3.8.0
		 * @param HTML       $html Name html.
		 * @param WC_Product $product_obj Product Object.
		 * @param SFL_List   $sfl_obj Save for Later Object.
		 * @return HTML
		 */
		public function sfl_table_display_name( $html, $product_obj, $sfl_obj ) {
			if ( ! sfl_check_is_array( $sfl_obj->get_cart_item() ) ) {
				return $html;
			}

			if ( ! isset( $sfl_obj->get_cart_item()['composite_data'] ) || ! sfl_check_is_array( $sfl_obj->get_cart_item()['composite_data'] ) ) {
				return $html;
			}

			if ( empty( $product_obj ) || false === $product_obj->is_type( 'composite' ) ) {
				return $html;
			}

			$html .= '<div class="slf-composite-product-child">';
			$count = 1;

			foreach ( $sfl_obj->get_cart_item()['composite_data'] as $key => $value ) {
				$_product_id = isset( $value['variation_id'] ) ? $value['variation_id'] : $value['product_id'];

				if ( empty( $_product_id ) ) {
					continue;
				}

				$_product_obj = wc_get_product( $_product_id );

				if ( ! is_a( $_product_obj, 'WC_Product' ) ) {
					continue;
				}

				$component_title = isset( $value['title'] ) ? $value['title'] : '';

				$html .= '<div class="sfl-component-wrap sfl-component-name-wrap sfl-component-name-' . $count . '" >';
				$html .= $component_title . ': <a href="' . esc_url( $_product_obj->get_permalink() ) . '">' . esc_html( $_product_obj->get_name() ) . '</a>';
				$html .= '</div>';
				$count++;
			}

			$html .= '</div>';

			return $html;
		}

		/**
		 * Save for Later Table Price
		 *
		 * @since 3.8.0
		 * @param HTML       $html Price html.
		 * @param WC_Product $product_obj Product Object.
		 * @param SFL_List   $sfl_obj Save for Later Object.
		 * @return HTML
		 */
		public function sfl_table_display_price( $html, $product_obj, $sfl_obj ) {
			if ( ! sfl_check_is_array( $sfl_obj->get_cart_item() ) ) {
				return $html;
			}

			if ( ! isset( $sfl_obj->get_cart_item()['composite_data'] ) || ! sfl_check_is_array( $sfl_obj->get_cart_item()['composite_data'] ) ) {
				return $html;
			}

			if ( empty( $product_obj ) || false === $product_obj->is_type( 'composite' ) ) {
				return $price;
			}

			$composite = new WC_Product_Composite( $product_obj->get_id() );

			$html .= '<div class="slf-composite-product-child">';
			$count = 1;

			foreach ( $sfl_obj->get_cart_item()['composite_data'] as $key => $value ) {
				$_product_id = isset( $value['variation_id'] ) ? $value['variation_id'] : $value['product_id'];

				if ( empty( $_product_id ) ) {
					continue;
				}

				$_product_obj = wc_get_product( $_product_id );

				if ( ! is_a( $_product_obj, 'WC_Product' ) ) {
					continue;
				}

				$component_id = isset( $value['composite_id'] ) ? $value['composite_id'] : '';
				$component    = $composite->get_component( $key );

				if ( ! is_a( $component, 'WC_CP_Component' ) ) {
					continue;
				}

				$component_option = $composite->get_component_option( $key, $_product_id );

				if ( $component_option && $component_option->is_priced_individually() ) {
					$html .= '<div class="sfl-component-wrap sfl-component-price-wrap sfl-component-price-' . $count . '" >';
					$html .= wc_price( $component_option->get_price() );
					$html .= '</div>';
				}

				$count++;
			}

			return $html;
		}

		/**
		 * Save for Later Table Quantity
		 *
		 * @since 3.8.0
		 * @param HTML       $html Quantity html.
		 * @param WC_Product $product_obj Product Object.
		 * @param SFL_List   $sfl_obj Save for Later Object.
		 * @return HTML
		 */
		public function sfl_table_display_quantity( $html, $product_obj, $sfl_obj ) {
			if ( ! sfl_check_is_array( $sfl_obj->get_cart_item() ) ) {
				return $html;
			}

			if ( ! isset( $sfl_obj->get_cart_item()['composite_data'] ) || ! sfl_check_is_array( $sfl_obj->get_cart_item()['composite_data'] ) ) {
				return $html;
			}

			if ( empty( $product_obj ) || false === $product_obj->is_type( 'composite' ) ) {
				return $price;
			}

			$composite = new WC_Product_Composite( $product_obj->get_id() );

			$html .= '<div class="slf-composite-product-child">';
			$count = 1;

			foreach ( $sfl_obj->get_cart_item()['composite_data'] as $key => $value ) {
				$_product_id = isset( $value['product_id'] ) ? $value['product_id'] : '';

				if ( empty( $_product_id ) ) {
					continue;
				}

				$_product_obj = wc_get_product( $_product_id );

				if ( ! is_a( $_product_obj, 'WC_Product' ) ) {
					continue;
				}

				$quantity_min = isset( $value['quantity_min'] ) ? $value['quantity_min'] : 1;
				$quantity_max = isset( $value['quantity_max'] ) ? $value['quantity_max'] : 1;

				if ( $quantity_min !== $quantity_max ) {
					$quantity = isset( $value['quantity'] ) ? $value['quantity'] : 1;
				} else {
					$quantity = $sfl_obj->get_product_qty();
				}

				$component_id = isset( $value['composite_id'] ) ? $value['composite_id'] : '';
				$component    = $composite->get_component( $key );

				if ( ! is_a( $component, 'WC_CP_Component' ) ) {
					continue;
				}

				$html .= '<div class="sfl-component-wrap sfl-component-qty-wrap sfl-component-qty-' . $count . '" >';
				$html .= $quantity;
				$html .= '</div>';
				$count++;
			}

			return $html;
		}

		/**
		 * Save for Later Table Quantity
		 *
		 * @since 3.8.0
		 * @param Price      $price Product Price.
		 * @param WC_Product $product_obj Product Object.
		 * @param Array      $cart_item Cart Item.
		 * @param String     $cart_item_key Cart Item Key.
		 * @return HTML
		 */
		public function composite_product_price( $price, $product_obj, $cart_item, $cart_item_key ) {
			if ( empty( $product_obj ) || false === $product_obj->is_type( 'composite' ) ) {
				return $price;
			}

			$wc_cp_display = new WC_CP_Display();
			$price         = $wc_cp_display->get_container_cart_item_price_amount( $cart_item, 'price' );

			return $price;
		}

		/**
		 * Check Product Already in SFL List
		 *
		 * @since 3.8.0
		 * @param Boolean    $bool whether to exists or not.
		 * @param WC_Product $product_obj Product Object.
		 * @param Integer    $user_id User ID.
		 * @return Boolean
		 */
		public function check_product_already_in_list( $bool, $product_obj, $user_id ) {
			if ( empty( $product_obj ) || false === $product_obj->is_type( 'composite' ) ) {
				return $bool;
			}

			return false;
		}

		/**
		 * Check Product Already in Cart
		 *
		 * @since 3.8.0
		 * @param Boolean    $bool whether to exists or not.
		 * @param WC_Product $product_obj Product Object.
		 * @return Boolean
		 */
		public function check_product_already_in_cart( $bool, $product_obj ) {
			if ( empty( $product_obj ) || false === $product_obj->is_type( 'composite' ) ) {
				return $bool;
			}

			return false;
		}

		/**
		 * Save for Later Table Quantity
		 *
		 * @since 3.8.0
		 * @param Boolean    $bool whether to display.
		 * @param WC_Product $product_obj Product Object.
		 * @return Boolean
		 */
		public function price_diff_display_validation( $bool, $product_obj ) {
			if ( empty( $product_obj ) || false === $product_obj->is_type( 'composite' ) ) {
				return $bool;
			}

			return false;
		}
	}
}
