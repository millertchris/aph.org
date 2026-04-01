<?php
/**
 * Uninstall process for the plugin.
 *
 * @link    https://wpmudev.com
 * @since   1.8.1
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos
 */

use WPMUDEV_Videos\Core\Helpers\Cache;
use WPMUDEV_Videos\Core\Modules\Videos;
use WPMUDEV_Videos\Core\Helpers\Settings;
use WPMUDEV_Videos\Core\Modules\Playlists;

// If uninstall not called from WordPress exit.
defined( 'WP_UNINSTALL_PLUGIN' ) || exit();

// Auto load classes.
require_once plugin_dir_path( __FILE__ ) . '/core/external/autoloader.php';

// Get the settings.
$keep_data     = (bool) Settings::get( 'keep_data' );
$keep_settings = (bool) Settings::get( 'keep_settings' );

// If not asked to keep.
if ( ! $keep_settings ) {
	// Delete the plugin settings.
	Settings::delete();

	// Delete the version option.
	delete_site_option( 'wpmudev_videos_version' );
}

if ( ! $keep_data ) {
	// We need to register the taxonomies.
	Playlists\Controller::get()->register_taxonomy();

	// Get all available video ids.
	$videos = get_posts(
		array(
			'post_type'   => Videos\Models\Video::POST_TYPE,
			'numberposts' => - 1,
			'fields'      => 'ids',
		)
	);

	// Loop through each video and delete.
	if ( ! empty( $videos ) ) {
		foreach ( $videos as $video_id ) {
			// Force delete post and meta.
			wp_delete_post( $video_id, true );
		}
	}

	// Get all available playlists.
	$playlists = get_terms(
		array(
			'taxonomy'   => Playlists\Models\Playlist::TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	// Loop through each playlist and delete.
	if ( ! empty( $playlists ) ) {
		foreach ( $playlists as $playlist_id ) {
			// Delete term and meta.
			wp_delete_term( $playlist_id, Playlists\Models\Playlist::TAXONOMY );
		}
	}
}

// Refresh all cache.
Cache::refresh_cache();