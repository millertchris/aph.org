<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mightily.com
 * @since             1.0.1
 * @package           Back_End_Shipping
 *
 * @wordpress-plugin
 * Plugin Name:       Back End Shipping
 * Plugin URI:        https://mightily.com
 * Description:       Extend Woocommerce to support shipping calculations within the Edit Order dashboard.
 * Version:           1.0.2
 * Author:            Mightily
 * Author URI:        https://mightily.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       back-end-shipping
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BACK_END_SHIPPING_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-back-end-shipping-activator.php
 */
function activate_back_end_shipping() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-back-end-shipping-activator.php';
	Back_End_Shipping_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-back-end-shipping-deactivator.php
 */
function deactivate_back_end_shipping() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-back-end-shipping-deactivator.php';
	Back_End_Shipping_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_back_end_shipping' );
register_deactivation_hook( __FILE__, 'deactivate_back_end_shipping' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-back-end-shipping.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_back_end_shipping() {

	$plugin = new Back_End_Shipping();
	$plugin->run();

}
run_back_end_shipping();


// function packages_active($product_names, $package){
// 	return $product_names;
// }
// function packages_meta_setup($product_names, $package){
// 	var_dump($package['contents']);
// 	return $product_names;
// }
// function the_dramatist_fire_on_wp_initialization() {
// 	// if(!is_checkout()){
// 	// 	return false;
// 	// }
// 	$package_active = add_filter( 'woocommerce_shipping_package_details_array', 'packages_active', 10, 2);
// 	if(!empty($package_active)){
// 		$package_meta_setup = add_filter( 'woocommerce_shipping_package_details_array', 'packages_meta_setup', 10, 2);
// 	} else {
// 		// Add meta for current shipping line item
// 	}
// }
// add_action( 'woocommerce_review_order_before_shipping', 'the_dramatist_fire_on_wp_initialization' );

// function testfunc($product_names, $package ){
// 	var_dump($package);
// }
function testdata(){
	$order = wc_get_order(422551);
	//print_r($order);
	$order_item_array = [];
	foreach($order->get_items() as $order_item){
		$order_item_array[$order_item->get_name()] = $order_item->get_product_id();
	}
	//var_dump($order_item_array);
	foreach($order->get_items('shipping') as $shipping_item_object){
		$shipping_item_data = $shipping_item_object->get_data();
		$shipping_items_meta = $shipping_item_object->get_meta('Items');
		if(!is_array($shipping_items_meta)){
			return false;
		}
		$shipping_items_array = explode(' &times; ', $shipping_items_meta);
		$shipping_item_ids = [];
		array_pop($shipping_items_array);
		foreach($shipping_items_array as $key => $shipping_item){
			if($key == 0){
				$shipping_item_ids[] = $order_item_array[$shipping_item];
			} else {
				$comma_pos = strpos($shipping_item, ',');
				if($comma_pos !== false){
					$shipping_item_ids[] = $order_item_array[substr($shipping_item, $comma_pos + 2)];
				}
			}
		}
		$shipping_item_ids = implode(', ', $shipping_item_ids);
		$shipping_item_object->add_meta_data('Ids', $shipping_item_ids);
		$shipping_item_object->save_meta_data();
		$shipping_item_object->save();
	} 
}
//add_action('init', 'testdata');
