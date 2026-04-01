<?php
/**
 * The Contexual Help class for the videos.
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
use WPMUDEV_Videos\Core\Modules\Videos;

/**
 * Class Contextual
 *
 * @package WPMUDEV_Videos\Core\Controllers
 */
class Contextual extends Base {

	/**
	 * Initialize contexual help.
	 *
	 * @since 1.8.0
	 */
	public function init() {
		// Only when membership is active.
		if ( Helpers\General::is_valid_member() ) {
			// Setup block editor hooks.
			add_action( 'init', array( $this, 'register_block_server_render' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_scripts' ) );

			// Add contextual help.
			add_action( 'current_screen', array( $this, 'help' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		}
	}

	/**
	 * Enqueue block specific scripts.
	 *
	 * @since  1.8.4
	 *
	 * @return void
	 */
	public function enqueue_block_scripts() {
		// Enqueue the scripts.
		Assets::get()->enqueue_script( 'wpmudev-videos-blocks-sidebar' );
	}

	/**
	 * Register server side rendering for block.
	 *
	 * We render the videos sidebar using server side
	 * rendering component.
	 *
	 * @since 1.8.4
	 *
	 * @return void
	 */
	public function register_block_server_render() {
		// Register block.
		register_block_type(
			'ivt/videos',
			array(
				'apiVersion'      => 1,
				'attributes'      => array(),
				'render_callback' => array( $this, 'block_render_callback' ),
			)
		);
	}

	/**
	 * Add new help item to contextual help screen.
	 *
	 * Get the list of videos from the playlist in which the current
	 * admin page is assigned.
	 *
	 * @param \WP_Screen $current_screen Current screen object.
	 *
	 * @since 1.7
	 *
	 * @return void
	 */
	public function help( $current_screen ) {
		// Do not continue if not enabled.
		if ( ! $this->is_enabled() || $this->is_block_editor() ) {
			return;
		}

		// Videos.
		$list = array();

		// Make sure to query main site.
		Helpers\General::switch_site();

		// Videos of current page.
		$videos = Videos\Controller::get()->get_location_videos( $current_screen->id );

		// Only when current screen is one of them.
		if ( ! empty( $videos ) ) {
			// Loop through each screen.
			foreach ( $videos as $video ) {
				// Embed data.
				$embed = Videos\Controller::get()->get_video_embed(
					$video->ID,
					array(
						'width'  => 500,
						'height' => 281,
					)
				);

				// Only if embed is found.
				if ( ! empty( $embed['html'] ) ) {
					$list[] = array(
						'id'      => $video->ID,
						'title'   => $video->post_title,
						'content' => $embed['html'],
					);
				}
			}

			// Get the view content.
			$contextual_help = Helpers\General::view(
				'admin/contextual',
				array( 'videos' => $list ),
				true
			);

			// Get the title.
			$menu_title = Helpers\Settings::get( 'menu_title' );

			// Make sure menu title is not empty.
			if ( empty( $menu_title ) ) {
				$menu_title = __( 'Video Tutorials', 'wpmudev_vids' );
			}

			// Add contextual help item.
			$current_screen->add_help_tab(
				array(
					'id'      => 'wpmudev_vids',
					'title'   => $menu_title,
					'content' => $contextual_help,
				)
			);
		}

		// Make sure to restore the site.
		Helpers\General::restore_site();
	}

	/**
	 * Contextual help scripts for the page.
	 *
	 * If videos are found for the current page, enqueue
	 * the styles and scripts.
	 *
	 * @since 1.7
	 * @since 1.8.0 Added page check.
	 *
	 * @return void
	 */
	public function scripts() {
		// Do not continue if not enabled.
		if ( ! $this->is_enabled() ) {
			return;
		}

		global $current_screen;

		// Only if we can determine the page id.
		if ( empty( $current_screen->id ) ) {
			return;
		}

		// Enqueue if block editor.
		$enqueue = $this->is_block_editor();

		if ( $enqueue ) {
			// Make sure to query main site.
			Helpers\General::switch_site();

			// Videos of current page.
			$videos = Videos\Controller::get()->get_location_videos( $current_screen->id );

			// Enqueue if videos found.
			$enqueue = ! empty( $videos );

			// Make sure to restore the site.
			Helpers\General::restore_site();
		}

		// Enqueue if videos are not empty.
		if ( $enqueue ) {
			Assets::get()->enqueue_style( 'wpmudev-videos-player' );
			Assets::get()->enqueue_script( 'wpmudev-videos-player' );
		}
	}

	/**
	 * Render the content for the block editor sidebar.
	 *
	 * @since 1.8.4
	 *
	 * @return string
	 */
	public function block_render_callback() {
		$content = '';

		// Make sure to query main site.
		Helpers\General::switch_site();

		// Videos of gutenberg editor.
		$videos = Videos\Controller::get()->get_location_videos( 'gutenberg-editor' );

		// We need videos, bro.
		if ( ! empty( $videos ) ) {
			foreach ( $videos as $video ) {
				// Embed data.
				$embed = Videos\Controller::get()->get_video_embed(
					$video->ID,
					array(
						'width'  => 250,
						'height' => 140,
					)
				);

				// Help content.
				if ( ! empty( $embed['html'] ) ) {
					$content .= '<h4>' . esc_attr( $video->post_title ) . '</h4>';
					$content .= $embed['html'];
				}
			}
		}

		// Make sure to restore the site.
		Helpers\General::restore_site();

		/**
		 * Filter hook to modify block render content.
		 *
		 * @param string $content Content.
		 * @param array  $videos  Video objects.
		 *
		 * @since 1.8.4
		 */
		return apply_filters( 'wpmudev_vids_block_render_callback', $content, $videos );
	}

	/**
	 * Check if contextual help is enabled in settings.
	 *
	 * @since 1.7.2
	 *
	 * @return bool
	 */
	private function is_enabled() {
		// Get the settings.
		$enabled = Helpers\Settings::get( 'contextual_help' );

		/**
		 * Filter to enabled/disable contextual help.
		 *
		 * @param bool $enabled Is enabled?.
		 *
		 * @since 1.7.2
		 */
		return apply_filters( 'wpmudev_vids_enable_contextual_help', $enabled );
	}

	/**
	 * Check if current page is block editor page.
	 *
	 * @since 1.8.4
	 *
	 * @return bool
	 */
	private function is_block_editor() {
		// Current screen.
		$current_screen = get_current_screen();

		// Only when Gutenberg block editor is available.
		$is_block_editor = method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor();

		/**
		 * Filter to modify editor page check.
		 *
		 * @param bool $is_block_editor Is block editor page.
		 *
		 * @since 1.8.4
		 */
		return apply_filters( 'wpmudev_vids_is_block_editor', $is_block_editor );
	}
}