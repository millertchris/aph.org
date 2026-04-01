<?php
/**
 * The export functionality class.
 *
 * @link       https://wpmudev.com
 * @since      1.8.1
 *
 * @author     Joel James <joel@incsub.com>
 * @package    WPMUDEV_Videos\Core\Tasks
 * @subpackage Export
 */

namespace WPMUDEV_Videos\Core\Tasks;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Abstracts\Base;
use WPMUDEV_Videos\Core\Modules\Videos;
use WPMUDEV_Videos\Core\Helpers\Settings;
use WPMUDEV_Videos\Core\Modules\Playlists;

/**
 * Class Export
 *
 * @package WPMUDEV_Videos\Core\Tasks
 */
class Export extends Base {

	/**
	 * Initialize the class and register hooks.
	 *
	 * @since 1.8.1
	 */
	public function init() {
		// Check if required to export.
		add_action( 'admin_init', array( $this, 'handle_export' ) );
	}

	/**
	 * Handle the export request if required.
	 *
	 * If current request is for exporting
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	public function handle_export() {
		// Get request data.
		$post = filter_input_array( INPUT_POST );

		// Only if export request.
		if ( isset( $post['ivt-action'], $post['export'] ) && 'export' === $post['ivt-action'] && ! empty( $post['ivt-export'] ) ) {
			// Verify the nonce.
			if ( wp_verify_nonce( $post['export-nonce'], 'ivt-export' ) ) {
				// Get the data to export.
				$data = $this->get_data( $post );

				// Download the file.
				$this->download( $data );
			}
		}
	}

	/**
	 * Generate the data to export.
	 *
	 * This is a huge process. If there are 100+ videos
	 * or playlists, it may take some time to export.
	 *
	 * @param array $post Request data.
	 *
	 * @since 1.8.1
	 *
	 * @return array
	 */
	private function get_data( $post ) {
		// Default data.
		$data = array(
			'videos'    => array(),
			'playlists' => array(),
			'settings'  => array(),
			// Should import thumbs.
			'thumb'     => in_array( 'thumb', $post['export'], true ),
		);

		// If videos should be exported.
		if ( in_array( 'videos', $post['export'], true ) ) {
			$data['videos'] = Videos\Controller::get()->get_videos(
				array( 'posts_per_page' => - 1 )
			);
		}

		// If playlists should be exported.
		if ( in_array( 'playlists', $post['export'], true ) ) {
			$data['playlists'] = Playlists\Controller::get()->get_playlists(
				array( 'count' => 0 )
			);
		}

		// If display settings should be exported.
		if ( in_array( 'display', $post['export'], true ) ) {
			$data['settings']['show_menu']       = Settings::get( 'show_menu' );
			$data['settings']['menu_title']      = Settings::get( 'menu_title' );
			$data['settings']['menu_location']   = Settings::get( 'menu_location' );
			$data['settings']['contextual_help'] = Settings::get( 'contextual_help' );
		}

		// If permission settings should be exported.
		if ( in_array( 'permissions', $post['export'], true ) ) {
			$data['settings']['roles'] = Settings::get( 'roles', array() );
		}

		return apply_filters( 'wpmudev_vids_export_data', $data, $post );
	}

	/**
	 * Download the data as a json file.
	 *
	 * @param array  $data Data for export.
	 * @param string $name Name of export file.
	 *
	 * @since 1.8.1
	 */
	private function download( $data, $name = 'ivt-export' ) {
		// Convert data to json.
		$json = wp_json_encode( $data );

		// Make file name unique.
		$name = $name . '-' . time();

		// Force browser to download.
		header( 'Content-Disposition: attachment; filename=' . $name . '.json' );
		header( 'Content-Type: application/json' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		// phpcs:ignore
		echo( $json );
		exit();
	}
}