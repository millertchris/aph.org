<?php 
/**
 * Plugin Name: IgniteWoo Updater
 * Plugin URI: https://ignitewoo.com/
 * Description: Helps you manage your IgniteWoo software licenses and receive important updates for your IgniteWoo products.
 * Version: 3.0.3
 * Author: IgniteWoo.com
 * Author URI: https://ignitewoo.com/
 * Network: true
 * Requires at least: 3.8.1
 * Tested up to: 5.2.1
 *
 * Text Domain: ignition-updater
 */
/*
    Copyright 2012 - 2020 IgniteWoo.com
    Copyright 2012 - WooThemes

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    
*/


/* TODO:


	Implement the package signing for WP 5.2.x when the WP developers integrate that for plugins. 
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 // Always load.
if ( is_admin() ) {
	add_action( 'plugins_loaded', '__ignition_updater' );
}

/* DEPRECATED: 
if ( is_admin() && ( isset( $_POST['action'] ) && 'ignition_activate_license_keys' == $_POST['action'] ) ) {
	add_action( 'plugins_loaded', '__ignition_updater' );
}
	else if ( is_admin() && !defined( 'DOING_AJAX' ) ) {
	add_action( 'plugins_loaded', '__ignition_updater' );
}
*/

function __ignition_updater () {
	global $ignition_updater_token, $ignition_updater;
	
	$ignition_updater_token = 'ignitewoo-updater'; 

	// Load the version from the plugin header in this file. This way we 
	// don't need to remember to change it anywhere else.
	$version = get_file_data( __FILE__, array( 'Version' ), '' );

	if ( is_array( $version ) ) {
		$version = array_pop( $version );
	}

	require_once( 'classes/class-ignition-updater.php' );

	$ignition_updater = new IgniteWoo_Updater( __FILE__, $version ); // ENSURE THE VERSION IS CORRECT AND MATCHES THIS PLUGIN VERSION

	//$ignition_updater->version = '3.0'; // Must be a string, not a float.

	@$ignition_updater->admin->product_id = 'Updater';
	@$ignition_updater->admin->licence_hash = '6471fb9bec3ef8e9dcafe3ba5bd994c8';
	@$ignition_updater->admin->slug = plugin_basename( __FILE__ );
	@$ignition_updater->admin->dir = dirname( __FILE__ );
}
