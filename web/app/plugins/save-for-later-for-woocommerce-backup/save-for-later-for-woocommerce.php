<?php
/**
 * Plugin Name: Save for Later for WooCommerce
 * Description: Offer the flexibility of moving products from the cart to a Save for Later list for your customers.
 * Version: 3.9.0
 * Author: Flintop
 * Author URI: https://flintop.com
 * Text Domain: save-for-later-for-woocommerce
 * Domain Path: /languages
 * Woo: 5079998:e39549cfa50b4f0b6f526cdad0dcd633
 * Tested up to: 6.7.1
 * WC tested up to: 9.6.0
 * WC requires at least: 3.5
 * Copyright: © 2019 Flintop
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Save For Later.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Include once will help to avoid fatal error by load the files when you call init hook
 * */
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Include main class file.
 */
if ( ! class_exists( 'SFL_Save_Later' ) ) {
	include_once 'inc/class-save-for-later.php';
}

if ( ! function_exists( 'sfl_is_valid_wp' ) ) {

	/**
	 * Is valid WordPress version?
	 *
	 * @return bool
	 */
	function sfl_is_valid_wp() {
		return ( version_compare( get_bloginfo( 'version' ), SFL_Save_Later::$wp_requires, '<' ) ) ? false : true;
	}
}

if ( ! function_exists( 'sfl_is_valid_wc' ) ) {

	/**
	 * Is valid WooCommerce version?
	 *
	 * @return bool
	 */
	function sfl_is_valid_wc() {
		return ( version_compare( get_option( 'woocommerce_version' ), SFL_Save_Later::$wc_requires, '<' ) ) ? false : true;
	}
}

if ( ! function_exists( 'sfl_is_wc_active' ) ) {

	/**
	 * Function to check whether WooCommerce is active or not.
	 *
	 * @return bool
	 */
	function sfl_is_wc_active() {
		// This condition is for multi site installation.
		if ( is_multisite() && ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return false;
			// This condition is for single site installation.
		} elseif ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'sfl_is_plugin_active' ) ) {

	/**
	 * Is plugin active?
	 *
	 * @return bool
	 */
	function sfl_is_plugin_active() {
		if ( sfl_is_valid_wp() && sfl_is_wc_active() && sfl_is_valid_wc() ) {
			return true;
		}

		add_action(
			'admin_notices',
			function () {
					$notice = '';

				if ( ! sfl_is_valid_wp() ) {
					$notice = sprintf( 'This version of Save for Later for WooCommerce requires WordPress %1s or newer.', SFL_Save_Later::$wp_requires );
				} elseif ( ! sfl_is_wc_active() ) {
					$notice = 'Save for Later for WooCommerce Plugin will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin.';
				} elseif ( ! sfl_is_valid_wc() ) {
					$notice = sprintf( 'This version of Save for Later for WooCommerce requires WooCommerce %1s or newer.', SFL_Save_Later::$wc_requires );
				}

				if ( $notice ) {
					echo '<div class="error">';
					echo '<p>' . wp_kses_post( $notice ) . '</p>';
					echo '</div>';
				}
			}
		);

		return false;
	}
}

// Return if the plugin is not active.
if ( ! sfl_is_plugin_active() ) {
	return;
}

// Define constant.
if ( ! defined( 'SFL_PLUGIN_FILE' ) ) {
	define( 'SFL_PLUGIN_FILE', __FILE__ );
}


if ( ! function_exists( 'sfl' ) ) {

	/**
	 * Save For Later.
	 */
	function sfl() {
		return SFL_Save_Later::instance();
	}
}

/**
 * Initialize the plugin.
 * */
sfl();
