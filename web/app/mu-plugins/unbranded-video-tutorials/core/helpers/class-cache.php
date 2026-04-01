<?php
/**
 * Cache helper class for the videos.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Helpers
 */

namespace WPMUDEV_Videos\Core\Helpers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

/**
 * Class Cache
 *
 * @package WPMUDEV_Videos\Core\Helpers
 */
class Cache {

	/**
	 * Wrapper for wp_cache_get function in WPMUDEV Videos.
	 *
	 * Use this to get the cache values set using set_cache method.
	 *
	 * @param int|string $key       The key under which the cache contents are stored.
	 * @param array      $args      Additional arguments.
	 * @param bool       $found     Optional. Whether the key was found in the cache (passed by reference).
	 *                              Disambiguate a return of false, a storable value. Default null.
	 * @param string     $group     Optional. Where the cache contents are grouped.
	 * @param bool       $force     Optional. Whether to force an update of the local
	 *                              cache from the persistent cache. Default false.
	 *
	 * @since 1.7
	 * @since 1.8 Changed param position to move $found param to third.
	 *
	 * @return bool|mixed False on failure to retrieve contents or the cache
	 *                      contents on success
	 */
	public static function get_cache( $key, $args = array(), &$found = null, $group = 'wpmudev_videos', $force = false ) {
		// Check if caching disabled.
		if ( ! self::can_cache() ) {
			return false;
		}

		// Get the current version.
		$version = wp_cache_get( 'wpmudev_videos_cache_version' );

		// Do not continue if version is not set.
		if ( ! empty( $version ) ) {
			// Get the cache value.
			$data = wp_cache_get( self::cache_key( $key, $args ), $group, $force, $found );

			// Return only data.
			if ( isset( $data['version'] ) && $version === $data['version'] && ! empty( $data['data'] ) ) {
				return $data['data'];
			} elseif ( isset( $data['version'] ) && $version !== $data['version'] ) {
				$found = false;
			}
		}

		return false;
	}

	/**
	 * Wrapper for wp_cache_set in WPMUDEV Videos.
	 *
	 * Set cache using this method so that we can delete them without
	 * flushing the object cache as whole. This cache can be deleted
	 * using normal wp_cache_delete.
	 *
	 * @param int|string $key       The cache key to use for retrieval later.
	 * @param mixed      $data      The contents to store in the cache.
	 * @param array      $args      Additional arguments.
	 * @param string     $group     Optional. Where to group the cache contents.
	 *                              Enables the same key to be used across groups.
	 * @param int        $expire    Optional. When to expire the cache contents, in seconds.
	 *                              Default 0 (no expiration).
	 *
	 * @since 1.7
	 *
	 * @return bool False on failure, true on success.
	 */
	public static function set_cache( $key, $data, $args = array(), $group = 'wpmudev_videos', $expire = 0 ) {
		// Check if caching disabled.
		if ( ! self::can_cache() ) {
			return false;
		}

		// Get the current version.
		$version = wp_cache_get( 'wpmudev_videos_cache_version' );

		// In case version is not set, set now.
		if ( empty( $version ) ) {
			// In case version is not set, use default 1.
			$version = 1;

			// Set cache version.
			wp_cache_set( 'wpmudev_videos_cache_version', $version );
		}

		// Add to cache array with version.
		$data = array(
			'data'    => $data,
			'version' => $version,
		);

		// Set to WP cache.
		return wp_cache_set( self::cache_key( $key, $args ), $data, $group, $expire );
	}

	/**
	 * Wrapper for wp_cache_delete in WPMUDEV Videos.
	 *
	 * Using this wrapper so it will take care of creating the proper
	 * cache key from the arguments.
	 *
	 * @param int|string $key       The cache key to use for retrieval later.
	 * @param array      $args      Additional arguments.
	 * @param string     $group     Optional. Where to group the cache contents.
	 *                              Enables the same key to be used across groups.
	 *
	 * @since 1.8.0
	 *
	 * @return bool False on failure, true on success.
	 */
	public static function delete_cache( $key, $args = array(), $group = 'wpmudev_videos' ) {
		// Delete the cache.
		return wp_cache_delete( self::cache_key( $key, $args ), $group );
	}

	/**
	 * Generate hashed cache key from arguments.
	 *
	 * This can be useful when you run custom queries with custom arguments.
	 *
	 * @param string $key  Base key.
	 * @param array  $args Arguments.
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	private static function cache_key( $key, $args = array() ) {
		// Add base key to the array.
		$args[] = $key;

		return md5( wp_json_encode( $args ) );
	}

	/**
	 * Refresh the whole WPMUDEV Videos cache.
	 *
	 * We can not delete the cache by group. So use
	 * this method to refresh the cache using version.
	 *
	 * @since 1.7
	 *
	 * @return bool
	 */
	public static function refresh_cache() {
		// Check if caching disabled.
		if ( ! self::can_cache() ) {
			return false;
		}

		// Increment the version.
		return wp_cache_incr( 'wpmudev_videos_cache_version' );
	}

	/**
	 * Check if we can cache the objects.
	 *
	 * Object caching can be disabled by returning false to
	 * wpmudev_videos_enable_cache filter.
	 *
	 * @since 1.8.3
	 *
	 * @return bool $enable
	 */
	private static function can_cache() {
		/**
		 * Make caching controllable.
		 *
		 * By default we can cache.
		 *
		 * @param bool $enable_cache Should cache?.
		 *
		 * @since 1.8.3
		 */
		return apply_filters( 'wpmudev_videos_enable_cache', true );
	}
}