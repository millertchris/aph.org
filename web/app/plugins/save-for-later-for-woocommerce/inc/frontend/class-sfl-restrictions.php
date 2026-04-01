<?php
/**
 * Save For Later Restrictions Handling
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Restriction_Handler' ) ) {

	/**
	 * Main SFL_Restriction_Handler Class.
	 * */
	class SFL_Restriction_Handler {
		/**
		 * Cart Item Key
		 *
		 * @var string
		 */
		public static $sfl_cart_item_key;

		/**
		 * Check is SFL Valid
		 *
		 * @since 1.0
		 * @param String $cart_item_key Cart item key.
		 * @return bool
		 */
		public static function is_valid_for_sfl( $cart_item_key ) {
			self::$sfl_cart_item_key = $cart_item_key;

			if ( ! is_user_logged_in() && 'yes' !== get_option( 'sfl_general_enable_guest_sfl' ) ) {
				return false;
			}

			// User roles.
			if ( is_user_logged_in() && ! self::is_valid_user() ) {
				return false;
			}

			// Product category.
			if ( ! self::check_product_and_category_valid_to_sfl() ) {
				return false;
			}

			$cart_contents = WC()->cart->get_cart();

			if ( ! isset( $cart_contents[ $cart_item_key ] ) ) {
				return false;
			}

			$cart_item   = $cart_contents[ $cart_item_key ];
			$product_id  = $cart_item['product_id'];
			$product_obj = wc_get_product( $product_id );

			if ( ! is_a( $product_obj, 'WC_Product' ) || ! in_array( $product_obj->get_type(), sfl_supported_product_types(), true ) ) {
				return false;
			}

			/**
			 * Check is Valid Cart item.
			 *
			 * @since 3.8.0
			 */
			return apply_filters( 'sfl_check_is_valid_cart_item', true, $cart_item_key, $cart_item );
		}

		/**
		 * Check if ordered user is matched with selected user/user role for refund.
		 *
		 * @since 1.0
		 * @return bool
		 */
		public static function is_valid_user() {
			$user_type = get_option( 'sfl_advanced_user_roles_users' );
			if ( '2' === $user_type ) {
				$user_ids = get_option( 'sfl_advanced_included_user' );
				if ( ! sfl_check_is_array( $user_ids ) ) {
					return true;
				} elseif ( in_array( get_current_user_id(), $user_ids ) ) {
					return true;
				}
			} elseif ( '3' === $user_type ) {
				$user_ids = get_option( 'sfl_advanced_exclude_user' );

				if ( ! sfl_check_is_array( $user_ids ) ) {
					return true;
				} elseif ( ! in_array( get_current_user_id(), $user_ids ) ) {
					return true;
				}
			} elseif ( '4' === $user_type ) {
				$user_roles = get_option( 'sfl_advanced_included_user_role' );

				if ( ! sfl_check_is_array( $user_roles ) ) {
					return true;
				} else {
					return self::is_valid_user_role( get_current_user_id(), $user_roles );
				}
			} elseif ( '5' === $user_type ) {
				$user_roles = get_option( 'sfl_advanced_excluded_user_role' );

				if ( ! sfl_check_is_array( $user_roles ) ) {
					return true;
				} else {
					return self::is_valid_user_role( get_current_user_id(), $user_roles ) ? false : true;
				}
			} else {
				return true;
			}

			return false;
		}

		/**
		 * Check if corresponding user role is selected.
		 *
		 * @since 1.0
		 * @param Integer $user_id User ID.
		 * @param Array   $selected_user_roles User Roles.
		 * @return bool
		 */
		public static function is_valid_user_role( $user_id, $selected_user_roles ) {
			$user_obj = get_userdata( $user_id );

			if ( ! sfl_check_is_array( $user_obj->roles ) ) {
				return false;
			}

			foreach ( $user_obj->roles as $role ) {
				if ( in_array( $role, $selected_user_roles ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check if order has selected product/category.
		 *
		 * @since 1.0
		 * @param Integer $product_id Product ID.
		 * @return bool
		 */
		public static function check_product_and_category_valid_to_sfl( $product_id = '' ) {
			$product_type = get_option( 'sfl_advanced_product_category' );

			if ( '2' === $product_type ) {
				$product_ids = get_option( 'sfl_advanced_included_product' );

				if ( ! sfl_check_is_array( $product_ids ) ) {
					return true;
				} elseif ( $product_id ) {
						return in_array( $product_id, $product_ids );
				} else {
					return self::check_get_items( 'product', $product_ids );
				}
			} elseif ( '3' === $product_type ) {
				$product_ids = get_option( 'sfl_advanced_exclude_product' );

				if ( ! sfl_check_is_array( $product_ids ) ) {
					return true;
				} elseif ( $product_id ) {
						return in_array( $product_id, $product_ids ) ? false : true;
				} else {
					return ( self::check_get_items( 'product', $product_ids ) ) ? false : true;
				}
			} elseif ( '5' === $product_type ) {
				$category = get_option( 'sfl_advanced_included_category' );

				if ( ! sfl_check_is_array( $category ) ) {
					return true;
				} elseif ( $product_id ) {
						return self::is_selected_category( $product_id, $category );
				} else {
					return self::check_get_items( 'category', $category );
				}
			} elseif ( '6' === $product_type ) {
				$category = get_option( 'sfl_advanced_exclude_category' );

				if ( ! sfl_check_is_array( $category ) ) {
					return true;
				} elseif ( $product_id ) {
						return self::is_selected_category( $product_id, $category ) ? false : true;
				} else {
					return ( self::check_get_items( 'category', $category ) ) ? false : true;
				}
			} else {
				return true;
			}

			return false;
		}

		/**
		 * Check if product/category/ is matched with selected value.
		 *
		 * @since 1.0.0
		 * @param String $post_type Post Type.
		 * @param Array  $select_products Products array of products.
		 * @return bool
		 */
		public static function check_get_items( $post_type, $select_products ) {
			$bool          = false;
			$cart_contents = WC()->cart->get_cart();

			if ( ! isset( $cart_contents[ self::$sfl_cart_item_key ] ) ) {
				return;
			}

			$item = $cart_contents[ self::$sfl_cart_item_key ];

			if ( 'product' === $post_type ) {
				$product_id = sfl_get_product_variation_id( $item );
				$bool       = self::is_selected_product( $product_id, $select_products );
			} elseif ( 'category' === $post_type ) {
				$bool = self::is_selected_category( $item['product_id'], $select_products );
			}

			return $bool;
		}

		/**
		 * Check if corresponding product is selected.
		 *
		 * @since 1.0
		 * @param Integer $product_id Product id.
		 * @param Array   $selected_products Products array of products.
		 * @return bool
		 */
		public static function is_selected_product( $product_id, $selected_products ) {
			$selected_products = sfl_check_is_array( $selected_products ) ? $selected_products : explode( ',', $selected_products );

			if ( in_array( $product_id, $selected_products ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Check if corresponding category is selected.
		 *
		 * @since 1.0
		 * @param Integer $product_id Product ID.
		 * @param Array   $selected_category Category.
		 * @return bool
		 */
		public static function is_selected_category( $product_id, $selected_category ) {
			$selected_category = sfl_check_is_array( $selected_category ) ? $selected_category : explode( ',', $selected_category );
			$terms             = get_the_terms( $product_id, 'product_cat' );

			if ( ! sfl_check_is_array( $terms ) ) {
				return false;
			}

			foreach ( $terms as $key => $term ) {
				if ( in_array( $term->term_id, $selected_category ) ) {
					return true;
				}
			}

			return false;
		}
	}

}
