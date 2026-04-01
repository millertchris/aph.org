<?php
/**
 * Main plugin header.
 *
 * @package WPMUDEV Videos
 *
 * Plugin Name: WPMU DEV Videos
 * Description: A simple way to integrate WPMU DEV's over 40 unbranded support videos into your websites.
 * Version:     1.8.15
 * Plugin URI:  https://wpmudev.com/project/unbranded-video-tutorials/
 * Author:      WPMU DEV
 * Author URI:  https://wpmudev.com/
 * Text Domain: wpmudev_vids
 * Domain Path: languages
 * Network:     true
 * WDP ID:      248
 *
 * Copyright 2007-2019 Incsub (http://incsub.com).
 *
 * Author - Aaron Edwards
 * Contributors - Jeffri, Joshua Dailey, Joel James
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

// Define WPMUDEV_VIDEOS_FILE.
define( 'WPMUDEV_VIDEOS_FILE', __FILE__ );

// Plugin version.
define( 'WPMUDEV_VIDEOS_VERSION', '1.8.15' );

// Auto load classes.
require_once plugin_dir_path( __FILE__ ) . '/core/external/autoloader.php';

// Run plugin activation hook to setup plugin.
register_activation_hook( __FILE__, array( \WPMUDEV_Videos\Core\Controllers\Installer::get(), 'activate' ) );

// Run plugin deactivation hook.
register_deactivation_hook( __FILE__, array( \WPMUDEV_Videos\Core\Controllers\Installer::get(), 'deactivate' ) );

/**
 * Load main instance of plugin.
 *
 * Returns the main instance of WPMUDEV_Videos\Core\Videos
 * to maintain a single copy of the plugin object.
 * By the way we need at least PHP version 5.4.
 *
 * @since 1.7.0
 */
if ( version_compare( PHP_VERSION, '5.4.0' ) >= 0 ) {
	// Init the plugin and load the plugin instance for the first time.
	add_action( 'plugins_loaded', array( 'WPMUDEV_Videos\Core\WPMUDEV_Videos', 'get' ) );
}