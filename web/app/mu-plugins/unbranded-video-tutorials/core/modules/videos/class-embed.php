<?php
/**
 * The videos embed functionality class of the plugin.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Modules\Videos
 */

namespace WPMUDEV_Videos\Core\Modules\Videos;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Embed
 *
 * @package WPMUDEV_Videos\Core\Modules\Videos
 */
class Embed extends Base {

	/**
	 * Default arguments for video iframe.
	 *
	 * @since 1.8.0
	 *
	 * @var array $default_args
	 */
	private $default_args = array(
		'width'    => 840,
		'height'   => 473,
		'autoplay' => false,
	);

	/**
	 * Register our custom video providers to WP.
	 *
	 * WP has a list whitelisted oEmbed providers. We need to add our
	 * custom video hosts to the list inorder to use WP's inbuilt oEmbed
	 * feature. YouTube and Vimeo are already in the list.
	 * See https://wordpress.org/support/article/embeds/
	 *
	 * @since 1.7
	 * @uses  wp_oembed_add_provider()
	 *
	 * @return void
	 */
	public function register_providers() {
		// Get our custom providers.
		$hosts = Helpers\Data::custom_hosts();

		if ( ! empty( $hosts ) ) {
			// oEmbed instance.
			$oembed = $this->wp_oembed();

			foreach ( $hosts as $host => $props ) {
				// No need to register existing ones again.
				if ( ! isset( $props['format'] ) || isset( $oembed->providers[ $props['format'] ] ) ) {
					continue;
				}

				// Register custom provider.
				wp_oembed_add_provider(
					$props['format'],
					$props['provider'],
					$props['regex']
				);
			}
		}

		/**
		 * Action hook fired after registering custom oEmbed providers.
		 *
		 * @since 1.7
		 */
		do_action( 'wpmudev_vids_after_register_oembed_providers' );
	}

	/**
	 * Get embed html content for the video.
	 *
	 * Generate iframe content for the WPMUDEV hosted videos.
	 * If it is custom video, get the embed content using the
	 * oEmbed feature of WordPress.
	 *
	 * @param int   $video_id Video ID.
	 * @param array $args     Arguments.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public function get_video_embed( $video_id, $args = array() ) {
		$data = array(
			'title'            => '',
			'html'             => '',
			'duration'         => '',
			'duration_seconds' => '',
		);

		// Get video object.
		$video = Controller::get()->get_video( $video_id );

		if ( $video->is_valid() && 'default' === $video->video_type ) {
			$data['title']            = $video->video_title;
			$data['duration_seconds'] = $video->video_duration;

			// Set video embed arguments.
			$args = wp_parse_args( $args, $this->default_args );

			// Create ssl url.
			$url = $this->url_base() . rawurlencode( $video->video_slug );

			// Autoplay?.
			if ( ! empty( $args['autoplay'] ) ) {
				$url = add_query_arg( 'autoplay', '1', $url );
			}

			// Get API key.
			$api_key = Helpers\General::api_key();

			// Get the hub site id.
			$site_id = Helpers\General::hub_site_id();

			// Used to sign video embed urls with a 10 minute expiring signature when domain mapping is on.
			$expire    = strtotime( '+10 Minutes' );
			$signature = hash_hmac( 'sha1', $site_id . $expire, $api_key );

			// Make url.
			$url = add_query_arg(
				array(
					'id'        => $site_id,
					'expire'    => $expire,
					'signature' => $signature,
				),
				$url
			);

			// Generate html.
			$data['html'] = '<iframe src="' . $url . '" frameborder="0" width="' . $args['width'] . '" height="' . $args['height'] . '" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
		}

		/**
		 * Filter video embed html for the default video.
		 *
		 * @param array  $data   Embed data.
		 * @param string $video  Video object.
		 * @param int    $width  Width of the embed.
		 * @param int    $height Height of the embed.
		 *
		 * @since 1.5.2
		 * @since 1.7 The `$width` parameter was added.
		 * @since 1.7 The `$height` parameter was added.
		 * @since 1.8 The `$type` parameter was added.
		 */
		return apply_filters( 'wpmudev_vids_oembed_model_get_video_embed', $data, $video_id, $args );
	}

	/**
	 * Get embed html content for a custom video.
	 *
	 * Custom video embeds are loaded using oEmbed class.
	 * So it may take a bit longer than normal video.
	 *
	 * @param int   $video_id Video ID.
	 * @param array $args     Arguments.
	 *
	 * @since 1.7
	 *
	 * @return array
	 */
	public function get_custom_video_embed( $video_id, $args = array() ) {
		$data = array();

		// Get video object.
		$video = Controller::get()->get_video( $video_id );

		if ( $video->is_valid() && 'custom' === $video->video_type ) {
			// Set video embed arguments.
			$args = wp_parse_args( $args, $this->default_args );

			// Don't miss required data.
			$args['host'] = $video->video_host;

			// If custom thumbnail is not set.
			if ( empty( $args['thumbnail'] ) && isset( $video->thumbnail['id'] ) ) {
				$args['thumbnail'] = $video->thumbnail['id'];
			}

			// If start time is enabled.
			if ( ! isset( $args['start_enabled'] ) ) {
				$args['start_enabled'] = $video->video_start;
			}

			// If end time is enabled.
			if ( ! isset( $args['end_enabled'] ) ) {
				$args['end_enabled'] = $video->video_end;
			}

			// If end time is not set.
			if ( empty( $args['end_time'] ) ) {
				$args['end_time'] = $video->video_end_time;
			}

			// If start time is not set.
			if ( empty( $args['start_time'] ) ) {
				$args['start_time'] = $video->video_start_time;
			}

			// If end time is not set.
			if ( empty( $args['end_time'] ) ) {
				$args['end_time'] = $video->video_end_time;
			}

			// If title is not set.
			if ( empty( $args['title'] ) ) {
				$args['title'] = $video->video_title;
			}

			// Get embed html.
			$data = $this->get_embed_data( $video->video_url, $args );

			// Setup custom video player if required.
			$data['html'] = $this->custom_video_player( $data['html'], $args );
		}

		/**
		 * Filter video embed html for the custom video.
		 *
		 * @param string $data     Embed data.
		 * @param string $video_id Video ID.
		 * @param array  $args     Arguments.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_oembed_model_get_custom_video_embed', $data, $video_id, $args );
	}

	/**
	 * Get embed html content for a video from URL.
	 *
	 * Generate iframe content for the WPMUDEV hosted videos.
	 * If it is custom video, get the embed content using the
	 * oEmbed feature of WordPress.
	 *
	 * @param int   $url  Video URL.
	 * @param array $args Arguments.
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	public function get_url_oembed_data( $url, $args = array() ) {
		$data = array();

		if ( $this->is_valid( $url ) ) {
			// Set video embed arguments.
			$args = wp_parse_args( $args, $this->default_args );

			// Get embed html.
			$data = $this->get_embed_data( $url, $args );

			// Set title from oembed.
			if ( ! empty( $data['title'] ) && empty( $args['title'] ) ) {
				$args['title'] = $data['title'];
			}

			// Setup custom video player if required.
			if ( ! empty( $data['html'] ) ) {
				$data['html'] = $this->custom_video_player( $data['html'], $args );
			}
		}

		/**
		 * Filter video embed html for the custom video.
		 *
		 * @param string $html   HTML embed code (since.
		 * @param string $video  Video object.
		 * @param int    $width  Width of the embed.
		 * @param int    $height Height of the embed.
		 *
		 * @since 1.5.2
		 * @since 1.7 The `$width` parameter was added.
		 * @since 1.7 The `$height` parameter was added.
		 * @since 1.8 The `$type` parameter was added.
		 */
		return apply_filters( 'wpmudev_vids_oembed_model_get_custom_video_embed', $data, $args );
	}

	/**
	 * Get custom video embed html content from provider.
	 *
	 * Use WP_oEmbed class to get the embed content from
	 * the video url.
	 *
	 * @param string $url  Video URL.
	 * @param array  $args Embed arguments.
	 *
	 * @since 1.7
	 *
	 * @return array
	 */
	public function get_embed_data( $url, $args = array() ) {
		// Set video embed arguments.
		$args = wp_parse_args( $args, array() );

		// Setup cache args.
		$cache_args = array_merge( array( $url ), $args );

		// Get from cache first.
		$data = Helpers\Cache::get_cache( 'video_embed_data', $cache_args );

		// Get iframe html from oembed.
		if ( empty( $data ) ) {
			// Get WP_oEmbed instance.
			$oembed = $this->wp_oembed();

			// Embed data.
			$data = $oembed->get_data( $url, $args );

			// Only video type is accepted.
			if ( isset( $data->type ) && 'video' === $data->type ) {
				$data = array(
					'html'             => $data->html,
					'title'            => $data->title,
					'duration'         => isset( $data->duration ) ? Helper::seconds_to_time( $data->duration ) : '',
					'duration_seconds' => isset( $data->duration ) ? $data->duration : '',
				);

				// Set to cache.
				Helpers\Cache::set_cache( 'video_embed_data', $data, $cache_args );
			} else {
				$data = array();
			}
		}

		/**
		 * Filter the custom video embed html.
		 *
		 * @param array $data Embed data.
		 *
		 * @since 1.7
		 */
		return apply_filters( 'wpmudev_vids_custom_get_html', $data );
	}

	/**
	 * Get custom video embed html content from provider.
	 *
	 * Use WP_oEmbed class to get the embed content from
	 * the video url.
	 *
	 * @param string $url  Video URL.
	 * @param string $host Host name.
	 *
	 * @since 1.7
	 *
	 * @return false|string
	 */
	public function is_valid( $url, $host = '' ) {
		// Get embed class object.
		$wp_oembed = $this->wp_oembed();

		// Escape unwanted tags.
		$url = esc_url_raw( $url );

		// Get the oEmbed provider.
		$provider = $wp_oembed->get_provider( $url );

		// Invalid url.
		if ( empty( $provider ) ) {
			return false;
		}

		// Custom hosts.
		$hosts = Helpers\Data::custom_hosts();

		// Validate the host name.
		if ( ! empty( $host ) && ( ! isset( $hosts[ $host ]['provider'] ) || $provider !== $hosts[ $host ]['provider'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get custom video player html content.
	 *
	 * If custom thumbnail is set, we need to generate a custom
	 * video player setup to load the thumbnail before video.
	 *
	 * @param string $html Video html.
	 * @param array  $args Custom args.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	private function custom_video_player( $html, $args ) {
		// Get custom properties.
		if ( empty( $args['thumbnail'] ) && empty( $args['start_time'] ) && empty( $args['end_time'] ) ) {
			return $html;
		}

		// Create new DOM instance.
		libxml_use_internal_errors( true );
		$doc = new \DOMDocument();
		$doc->loadHTML( $html );

		// Get the iframe.
		$iframe = $doc->getElementsByTagName( 'iframe' )->item( 0 );

		// Get the existing src.
		$src = $iframe->getAttribute( 'src' );

		// Setup start and end time.
		$src = $this->setup_playback_time( $src, $args );

		// Get thumbnail url.
		$thumbnail_url = empty( $args['thumbnail'] ) ? '' : wp_get_attachment_image_url( $args['thumbnail'], 'full' );

		// Setup custom thumbnail.
		if ( ! empty( $thumbnail_url ) && ! empty( $args['host'] ) ) {
			// Get video player.
			$html = Helpers\General::view(
				'common/player',
				array(
					'thumbnail_url' => $thumbnail_url,
					'host'          => $args['host'],
					'title'         => $args['title'],
					'src'           => add_query_arg( 'autoplay', 1, $src ),
				),
				true
			);
		} else {
			// Clear the src for lazy loading.
			$iframe->setAttribute( 'src', $src );

			// Save html.
			$html = $doc->saveHTML();
		}

		/**
		 * Filter to modify custom video player html.
		 *
		 * @param string $video_html Video html.
		 * @param array  $args       Custom args.
		 *
		 * @since 1.7
		 */
		return apply_filters( 'wpmudev_vids_custom_video_player_html', $html, $args );
	}

	/**
	 * Setup video start and end time into embed url.
	 *
	 * Different video hosts support setting a start and end time for
	 * video playbacks.
	 *
	 * @param string $src  Video src url.
	 * @param array  $args Custom arguments.
	 *
	 * @since 1.7
	 *
	 * @return string
	 */
	private function setup_playback_time( $src, $args ) {
		// We need some data.
		if ( empty( $args['host'] ) && empty( $args['start_time'] ) && empty( $data['end_time'] ) ) {
			return $src;
		}

		// Format the start time into seconds.
		$start = ! empty( $args['start_time'] ) && ! empty( $args['start_enabled'] ) ? Helper::time_to_seconds( $args['start_time'] ) : false;
		$end   = ! empty( $args['end_time'] ) && ! empty( $args['end_enabled'] ) ? Helper::time_to_seconds( $args['end_time'] ) : false;

		switch ( $args['host'] ) {
			// Refer https://stackoverflow.com/a/19507833/3845839.
			case 'youtube':
				if ( ! empty( $start ) ) {
					$src = add_query_arg( 'start', $start, $src );
				}
				if ( ! empty( $end ) ) {
					$src = add_query_arg( 'end', $end, $src );
				}
				break;

			// Refer https://vimeo.zendesk.com/hc/en-us/articles/360000121668-Starting-playback-at-a-specific-timecode.
			case 'vimeo':
				if ( ! empty( $start ) ) {
					$src = $src . '#t=' . $start;
				}
				break;

			// Refer https://wistia.com/support/developers/embed-options#time.
			case 'wistia':
				if ( ! empty( $start ) ) {
					$src = add_query_arg( 'time', $start, $src );
				}
				break;
		}

		/**
		 * Filter hook to modify src url after setting time.
		 *
		 * @param string $src  Video src url.
		 * @param array  $args Custom arguments.
		 *
		 * @since 1.7
		 */
		return apply_filters( 'wpmudev_vids_setup_playback_time', $src, $args );
	}

	/**
	 * Get the WP_oEmbed class instance.
	 *
	 * This function will return the WP_oEmbed class object.
	 * If it is not initialized already, we will do that.
	 *
	 * @since 1.7
	 * @since 1.8 Moved to this class.
	 *
	 * @return \WP_oEmbed
	 */
	private function wp_oembed() {
		global $wp_version;

		// Include the embed functions.
		include_once ABSPATH . WPINC . '/embed.php';

		// Include the WP_oEmbed class.
		if ( version_compare( $wp_version, '5.3' ) >= 0 ) {
			include_once ABSPATH . WPINC . '/class-wp-oembed.php';
		} else {
			// Include the WP_oEmbed class in WP 5.2 or less.
			include_once ABSPATH . WPINC . '/class-oembed.php';
		}

		// Make sure not to create multiple instanc
		// if the _wp_oembed_get_object() function exists.
		if ( function_exists( '_wp_oembed_get_object' ) ) {
			return _wp_oembed_get_object();
		}

		static $wp_oembed = null;

		// If not, create new instance.
		if ( is_null( $wp_oembed ) ) {
			$wp_oembed = new \WP_oEmbed();
		}

		return $wp_oembed;
	}

	/**
	 * Get the base URL for the video server.
	 *
	 * If a staging URL is being used, use it instead of the
	 * production URL.
	 *
	 * @since 1.8.1
	 *
	 * @return string
	 */
	private function url_base() {
		// Production URL.
		$root = 'https://wpmudev.com';

		// If custom API server is being used.
		if ( defined( 'WPMUDEV_CUSTOM_API_SERVER' ) && WPMUDEV_CUSTOM_API_SERVER ) {
			$root = untrailingslashit( WPMUDEV_CUSTOM_API_SERVER );
		}

		// Video base URL.
		$url = $root . '/video/';

		/**
		 * Filter video embed URL base for default videos.
		 *
		 * @param string $url Video base URL.
		 *
		 * @since 1.8.1
		 */
		return apply_filters( 'wpmudev_vids_oembed_url_base', $url );
	}
}