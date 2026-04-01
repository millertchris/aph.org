<?php
/**
 * The front functionality class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV_Videos\Core\Controllers
 */

namespace WPMUDEV_Videos\Core\Controllers;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Helpers;
use WPMUDEV_Videos\Core\Abstracts\Base;
use WPMUDEV_Videos\Core\Modules\Videos\Controller as Video_Controller;
use WPMUDEV_Videos\Core\Modules\Playlists\Controller as Playlist_Controller;

/**
 * Class Front
 *
 * @package WPMUDEV_Videos\Core\Views
 */
class Front extends Base {

	/**
	 * Initialize front view.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Register shortcode.
		add_shortcode( 'wpmudev-video', array( $this, 'handle_shortcode' ) );
	}

	/**
	 * Output video shortcode content.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @since 1.4
	 *
	 * @return string
	 */
	public function handle_shortcode( $atts ) {
		// Get shortcode attributes.
		$args = shortcode_atts(
			array(
				'video'      => false,
				'group'      => false,
				'show_title' => true,
				'width'      => 500,
				'height'     => 281,
				'videos'     => array(),
				'playlist'   => array(),
			),
			$atts
		);

		// Calculate the height.
		if ( ! $args['height'] ) {
			$args['height'] = ceil( ( $args['width'] * 9 ) / 16 );
		}

		if ( ! empty( $args['group'] ) ) {
			// Make sure to query main site.
			Helpers\General::switch_site();

			// Get playlist.
			$playlist = Playlist_Controller::get()->get_playlist( $args['group'] );

			// Only if playlist is valid.
			if ( ! empty( $playlist ) && ! $playlist->is_error() ) {
				// Get playlist videos.
				$videos = Video_Controller::get()->get_playlist_videos(
					$playlist->id,
					array( 'ID', 'post_title' )
				);

				// If videos found.
				if ( ! empty( $videos ) ) {
					foreach ( $videos as $data ) {
						// Get embed data.
						$embed = Video_Controller::get()->get_video_embed(
							$data->ID,
							array(
								'width'  => $args['width'],
								'height' => $args['height'],
							)
						);

						// Only when embed found.
						if ( ! empty( $embed['html'] ) ) {
							$args['videos'][] = array(
								'id'    => $data->ID,
								'title' => $data->post_title,
								'embed' => $embed['html'],
							);
						}
					}
				}

				// Set playlist.
				$args['playlist'] = array(
					'id'    => $playlist->id,
					'title' => $playlist->title,
				);
			}

			// Restore old blog.
			Helpers\General::restore_site();
		} elseif ( ! empty( $args['video'] ) ) {
			// Make sure to query main site.
			Helpers\General::switch_site();

			// Get the video object.
			$video = Video_Controller::get()->get_video( $args['video'] );

			// If a valid video.
			if ( ! $video->is_error() ) {
				// Get embed data.
				$embed = Video_Controller::get()->get_video_embed(
					$video->id,
					array(
						'width'  => $args['width'],
						'height' => $args['height'],
					)
				);

				// Only when embed found.
				if ( ! empty( $embed['html'] ) ) {
					$args['videos'][] = array(
						'id'    => $video->id,
						'title' => $video->video_title,
						'embed' => $embed['html'],
					);
				}
			}

			// Restore old blog.
			Helpers\General::restore_site();
		}

		// Enqueue assets.
		Assets::get()->enqueue_style( 'wpmudev-videos-player' );
		Assets::get()->enqueue_script( 'wpmudev-videos-player' );

		// Videos template.
		return Helpers\General::view( 'front/shortcodes/video', $args, true );
	}
}