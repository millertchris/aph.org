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
 * @package           Woocommerce_Louis
 *
 * @wordpress-plugin
 * Plugin Name:       Woocommerce Louis
 * Plugin URI:        https://mightily.com
 * Description:       Extend Woocommerce to support adding Louis products to orders from admin or csr dashboard.
 * Version:           1.0.2
 * Author:            Mightily
 * Author URI:        https://mightily.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woocommerce-louis
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
define( 'WOOCOMMERCE_LOUIS_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-louis-activator.php
 */
function activate_woocommerce_louis() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-louis-activator.php';
	Woocommerce_Louis_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-louis-deactivator.php
 */
function deactivate_woocommerce_louis() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-louis-deactivator.php';
	Woocommerce_Louis_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woocommerce_louis' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_louis' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-louis.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woocommerce_louis() {

	$plugin = new Woocommerce_Louis();
	$plugin->run();

}
run_woocommerce_louis();
