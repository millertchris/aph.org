<?php
/**
 * Price by User Role for WooCommerce - Core Class
 *
 * @package PriceByUserRole
 * @version 1.1.0
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Price_By_User_Role_Core' ) ) :

	/**
	 * Alg_WC_Price_By_User_Role_Core Class
	 *
	 * @class   Alg_WC_Price_By_User_Role_Core
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	class Alg_WC_Price_By_User_Role_Core {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			if ( 'yes' === get_option( 'alg_wc_price_by_user_role_enabled', 'yes' ) ) {
				if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
					if ( 'no' === get_option( 'alg_wc_price_by_user_role_for_bots_disabled', 'no' ) || ! alg_is_bot() ) {
						$this->add_hooks();
					}
				}
			}
		}

		/**
		 * Add_hooks.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		public function add_hooks() {
			$price_hooks = array();
			// Prices.
			if ( version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' ) ) {
				$price_hooks = array_merge(
					$price_hooks,
					array(
						'woocommerce_get_price',
						'woocommerce_get_sale_price',
						'woocommerce_get_regular_price',
					)
				);
			} else {
				$price_hooks = array_merge(
					$price_hooks,
					array(
						'woocommerce_product_get_price',
						'woocommerce_product_get_sale_price',
						'woocommerce_product_get_regular_price',
					)
				);
			}
			// Variations.
			$price_hooks = array_merge(
				$price_hooks,
				array(
					'woocommerce_variation_prices_price',
					'woocommerce_variation_prices_regular_price',
					'woocommerce_variation_prices_sale_price',
				)
			);
			if ( version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '>=' ) ) {
				$price_hooks = array_merge(
					$price_hooks,
					array(
						'woocommerce_product_variation_get_price',
						'woocommerce_product_variation_get_regular_price',
						'woocommerce_product_variation_get_sale_price',
					)
				);
			}
			// Hooking...
			foreach ( $price_hooks as $price_hook ) {
				add_filter( $price_hook, array( $this, 'change_price_by_role' ), PHP_INT_MAX, 2 );
			}
			// Variations Hash.
			add_filter( 'woocommerce_get_variation_prices_hash', array( $this, 'get_variation_prices_hash' ), PHP_INT_MAX, 3 );
			// Shipping.
			add_filter( 'woocommerce_package_rates', array( $this, 'change_price_by_role_shipping' ), PHP_INT_MAX, 2 );
			// Grouped products.
			add_filter( 'woocommerce_get_price_including_tax', array( $this, 'change_price_by_role_grouped' ), PHP_INT_MAX, 3 );
			add_filter( 'woocommerce_get_price_excluding_tax', array( $this, 'change_price_by_role_grouped' ), PHP_INT_MAX, 3 );
		}

		/**
		 * Change_price_by_role_shipping.
		 *
		 * @param array  $package_rates Package of Rates.
		 * @param string $package Package.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function change_price_by_role_shipping( $package_rates, $package ) {
			if ( 'yes' === get_option( 'alg_wc_price_by_user_role_shipping_enabled', 'no' ) ) {
				$current_user_role      = alg_get_current_user_first_role();
				$koef                   = get_option( 'alg_wc_price_by_user_role_' . $current_user_role, 1 );
				$modified_package_rates = array();
				foreach ( $package_rates as $id => $package_rate ) {
					if ( 1 !== $koef && isset( $package_rate->cost ) ) {
						$package_rate->cost = $package_rate->cost * $koef;
						if ( isset( $package_rate->taxes ) && ! empty( $package_rate->taxes ) ) {
							foreach ( $package_rate->taxes as $tax_id => $tax ) {
								$package_rate->taxes[ $tax_id ] = $package_rate->taxes[ $tax_id ] * $koef;
							}
						}
					}
					$modified_package_rates[ $id ] = $package_rate;
				}
				return $modified_package_rates;
			}
			return $package_rates;
		}

		/**
		 * Change_price_by_role_grouped.
		 *
		 * @param string $price Price.
		 * @param int    $qty Quantity.
		 * @param obj    $_product Object of Product.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function change_price_by_role_grouped( $price, $qty, $_product ) {
			if ( $_product->is_type( 'grouped' ) ) {
				if ( 'yes' === get_option( 'alg_wc_price_by_user_role_per_product_enabled', 'yes' ) ) {
					foreach ( $_product->get_children() as $child_id ) {
						$the_product = wc_get_product( $child_id );
						$the_price   = $the_product ? $the_product->get_price() : '';
						$the_price   = alg_get_product_display_price( $the_product, $the_price );
						if ( $the_price === $price ) {
							return $this->change_price_by_role( $price, $the_product );
						}
					}
				} elseif ( 'yes' === get_option( 'alg_wc_price_by_user_role_multipliers_enabled', 'yes' ) ) {
					return $this->change_price_by_role( $price, null );
				}
			}
			return $price;
		}

		/**
		 * Change_price_by_role.
		 *
		 * @param string $price Price.
		 * @param obj    $_product Object of Product.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		public function change_price_by_role( $price, $_product ) {

			$current_user_role = alg_get_current_user_first_role();

			// Per product.
			if ( 'yes' === get_option( 'alg_wc_price_by_user_role_per_product_enabled', 'yes' ) ) {
				$parent_product = $_product;
				if ( $_product && $_product->is_type( 'variation' ) ) {
					$parent_product = wc_get_product( $_product->get_parent_id() );
				}
				if ( $parent_product && 'yes' === $parent_product->get_meta( '_alg_wc_price_by_user_role_per_product_settings_enabled', true ) ) {
					if ( $_product && 'yes' === $_product->get_meta( '_alg_wc_price_by_user_role_empty_price_' . $current_user_role, true ) ) {
						return '';
					}
					$regular_price_per_product = $_product ? $_product->get_meta( '_alg_wc_price_by_user_role_regular_price_' . $current_user_role, true ) : '';
					if ( '' !== $regular_price_per_product ) {
						$_current_filter = current_filter();
						if ( in_array(
							$_current_filter,
							array(
								'woocommerce_get_price_including_tax',
								'woocommerce_get_price_excluding_tax',
							),
							true
						) ) {
							return alg_get_product_display_price( $_product );
						} elseif ( in_array(
							$_current_filter,
							array(
								'woocommerce_get_price',
								'woocommerce_variation_prices_price',
								'woocommerce_product_get_price',
								'woocommerce_product_variation_get_price',
							),
							true
						) ) {
							$sale_price_per_product = $_product ? $_product->get_meta( '_alg_wc_price_by_user_role_sale_price_' . $current_user_role, true ) : '';
							return ( '' !== $sale_price_per_product && $sale_price_per_product < $regular_price_per_product ) ?
								$sale_price_per_product : $regular_price_per_product;
						} elseif ( in_array(
							$_current_filter,
							array(
								'woocommerce_get_regular_price',
								'woocommerce_variation_prices_regular_price',
								'woocommerce_product_get_regular_price',
								'woocommerce_product_variation_get_regular_price',
							),
							true
						) ) {
							return $regular_price_per_product;
						} elseif ( in_array(
							$_current_filter,
							array(
								'woocommerce_get_sale_price',
								'woocommerce_variation_prices_sale_price',
								'woocommerce_product_get_sale_price',
								'woocommerce_product_variation_get_sale_price',
							),
							true
						) ) {
							$sale_price_per_product = $_product ? $_product->get_meta( '_alg_wc_price_by_user_role_sale_price_' . $current_user_role, true ) : '';
							return ( '' !== $sale_price_per_product ) ?
								$sale_price_per_product : $price;
						}
					}
				}
			}

			// Global.
			if ( 'yes' === get_option( 'alg_wc_price_by_user_role_multipliers_enabled', 'yes' ) ) {
				if ( 'yes' === get_option( 'alg_wc_price_by_user_role_empty_price_' . $current_user_role, 'no' ) ) {
					return '';
				}
				$koef = get_option( 'alg_wc_price_by_user_role_' . $current_user_role, 1 );
				if ( 1 !== $koef ) {
					return ( '' === $price ) ? $price : $price * $koef;
				}
			}

			// No changes.
			return $price;
		}

		/**
		 * Get_variation_prices_hash.
		 *
		 * @param array  $price_hash Price.
		 * @param obj    $_product Object of Product.
		 * @param string $display Display.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function get_variation_prices_hash( $price_hash, $_product, $display ) {
			$user_role                   = alg_get_current_user_first_role();
			$koef                        = get_option( 'alg_wc_price_by_user_role_' . $user_role, 1 );
			$is_empty                    = get_option( 'alg_wc_price_by_user_role_empty_price_' . $user_role, 'no' );
			$price_hash['alg_user_role'] = array(
				$user_role,
				$koef,
				$is_empty,
				get_option( 'alg_wc_price_by_user_role_per_product_enabled', 'yes' ),
				get_option( 'alg_wc_price_by_user_role_multipliers_enabled', 'yes' ),
			);
			return $price_hash;
		}

	}

endif;

return new Alg_WC_Price_By_User_Role_Core();
