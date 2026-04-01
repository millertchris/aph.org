<?php
/**
 * The assets functionality class for the videos.
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

use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Assets
 *
 * @package WPMUDEV_Videos\Core\Controllers
 */
class Assets extends Base {

	/**
	 * Initialize assets functionality.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'public_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_assets' ) );

		// Include clipboard JS.
		add_filter( 'wpmudev_vids_assets_get_scripts', array( $this, 'register_clipboard' ), 10, 2 );
	}

	/**
	 * Assets for our front end functionality.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function public_assets() {
		$this->register_styles();
		$this->register_scripts();
		$this->register_styles( 'front' );
		$this->register_scripts( 'front' );
	}

	/**
	 * Assets for our admin functionality.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function admin_assets() {
		$this->register_styles();
		$this->register_scripts();
		$this->register_styles( 'admin' );
		$this->register_scripts( 'admin' );
	}

	/**
	 * Assets for our block editor functionality.
	 *
	 * @since 1.8.4
	 *
	 * @return void
	 */
	public function block_assets() {
		$this->register_scripts( 'block' );
	}

	/**
	 * Register available styles.
	 *
	 * We are just registering the assets with WP now.
	 * We will enqueue them when it's really required.
	 *
	 * @param string $context Context.
	 *
	 * @since 1.8.0
	 * @since 1.8.4 Changed param.
	 *
	 * @return void
	 */
	private function register_styles( $context = 'common' ) {
		// Get all the assets.
		$styles = $this->get_styles( $context );

		// Register all styles.
		foreach ( $styles as $handle => $data ) {
			// Register custom videos scripts.
			wp_register_style(
				$handle,
				WPMUDEV_VIDEOS_URL . 'app/assets/css/' . $data['src'],
				empty( $data['deps'] ) ? array() : $data['deps'],
				empty( $data['version'] ) ? WPMUDEV_VIDEOS_VERSION : $data['version'],
				empty( $data['media'] ) ? false : true
			);
		}
	}

	/**
	 * Register available scripts.
	 *
	 * We are just registering the assets with WP now.
	 * We will enqueue them when it's really required.
	 *
	 * @param string $context Context.
	 *
	 * @since 1.8.0
	 * @since 1.8.4 Changed param.
	 *
	 * @return void
	 */
	private function register_scripts( $context = 'common' ) {
		// Get all the assets.
		$scripts = $this->get_scripts( $context );

		// Register all available scripts.
		foreach ( $scripts as $handle => $data ) {
			// Register custom videos scripts.
			wp_register_script(
				$handle,
				WPMUDEV_VIDEOS_URL . 'app/assets/js/' . $data['src'],
				empty( $data['deps'] ) ? array() : $data['deps'],
				empty( $data['version'] ) ? WPMUDEV_VIDEOS_VERSION : $data['version'],
				isset( $data['footer'] ) ? $data['footer'] : true
			);
		}
	}

	/**
	 * Enqueue a style with WordPress.
	 *
	 * This is just an alias function.
	 *
	 * @param string $style Style handle name.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function enqueue_style( $style ) {
		// Only if not enqueued already.
		if ( ! wp_style_is( $style ) ) {
			wp_enqueue_style( $style );
		}
	}

	/**
	 * Enqueue a script with localization.
	 *
	 * Always use this method to enqueue scripts. Then only
	 * we will get the required localized vars.
	 *
	 * @param string $script Script handle name.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function enqueue_script( $script ) {

		// Only if not enqueued already.
		if ( ! wp_script_is( $script ) ) {
			// Extra vars.
			wp_localize_script(
				$script,
				'ivtModuleVars',
				/**
				 * Filter to add/remove vars in script.
				 *
				 * @since 1.8.0
				 */
				apply_filters( "wpmudev_vids_assets_module_vars_{$script}", array() )
			);

			wp_localize_script(
				$script,
				'ivtVars',
				/**
				 * Filter to add/remove vars in script.
				 *
				 * @param array $common_vars Common vars.
				 * @param array $handle      Script handle name.
				 *
				 * @since 1.8.0
				 */
				apply_filters( 'wpmudev_vids_assets_common_vars', array(), $script )
			);

			// Now enqueue.
			wp_enqueue_script( $script );
		}
	}

	/**
	 * Assets for our front end functionality.
	 *
	 * @param string $context Context.
	 *
	 * @since 1.7
	 * @since 1.8.4 changed param.
	 *
	 * @return array
	 */
	private function get_scripts( $context = 'common' ) {
		$scripts = array();

		switch ( $context ) {
			case 'block':
				// Blocks sidebar.
				$scripts['wpmudev-videos-blocks-sidebar'] = array(
					'src'  => 'blocks-sidebar.min.js',
					'deps' => array( 'wp-edit-post', 'wp-plugins', 'wp-element' ),
				);
				break;
			case 'admin':
				$scripts['wpmudev-videos-sui']     = array(
					'src' => 'shared-ui.min.js',
				);
				$scripts['wpmudev-videos-vendors'] = array(
					'src' => 'chunk-vendors.min.js',
				);
				$scripts['wpmudev-videos-common']  = array(
					'src'  => 'chunk-common.min.js',
					'deps' => array( 'wpmudev-videos-vendors' ),
				);

				$scripts['wpmudev-videos-settings']  = array(
					'src'  => 'settings.min.js',
					'deps' => array( 'jquery', 'wpmudev-videos-sui', 'wpmudev-videos-common' ),
				);
				$scripts['wpmudev-videos-videos']    = array(
					'src'  => 'videos.min.js',
					'deps' => array( 'jquery', 'wpmudev-videos-sui', 'wpmudev-videos-common' ),
				);
				$scripts['wpmudev-videos-dashboard'] = array(
					'src'  => 'dashboard.min.js',
					'deps' => array( 'jquery', 'wpmudev-videos-sui', 'wpmudev-videos-common' ),
				);
				$scripts['wpmudev-videos-playlists'] = array(
					'src'  => 'playlists.min.js',
					'deps' => array( 'jquery', 'wpmudev-videos-sui', 'wpmudev-videos-common' ),
				);
				$scripts['wpmudev-videos-tutorials'] = array(
					'src'  => 'tutorials.min.js',
					'deps' => array( 'jquery', 'wpmudev-videos-sui', 'wpmudev-videos-common' ),
				);
				break;

			default:
				// Video player is common.
				$scripts['wpmudev-videos-player'] = array(
					'src'  => 'player-script.min.js',
					'deps' => array( 'jquery' ),
				);
				break;
		}

		/**
		 * Filter to include/exclude new script.
		 *
		 * Modules should use this filter to that common localized
		 * vars will be available.
		 *
		 * @param array  $scripts Scripts list.
		 * @param string $context Context.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_assets_get_scripts', $scripts, $context );
	}

	/**
	 * Assets for our front end functionality.
	 *
	 * @param bool $context Context.
	 *
	 * @since 1.8.0
	 * @since 1.8.4 Changed param.
	 *
	 * @return array
	 */
	private function get_styles( $context = 'common' ) {
		$styles = array();

		switch ( $context ) {
			case 'admin':
				$styles['wpmudev-videos-settings']  = array(
					'src' => 'settings.min.css',
				);
				$styles['wpmudev-videos-videos']    = array(
					'src' => 'videos.min.css',
				);
				$styles['wpmudev-videos-dashboard'] = array(
					'src' => 'dashboard.min.css',
				);
				$styles['wpmudev-videos-playlists'] = array(
					'src' => 'playlists.min.css',
				);
				$styles['wpmudev-videos-tutorials'] = array(
					'src' => 'tutorials.min.css',
				);
				break;
			default:
				// Video player is common.
				$styles['wpmudev-videos-player'] = array(
					'src' => 'player-style.min.css',
				);
				break;
		}

		/**
		 * Filter to include/exclude new style.
		 *
		 * Modules should use this filter to include styles.
		 *
		 * @param array  $styles  Styles list.
		 * @param string $context Context.
		 *
		 * @since 1.8.0
		 */
		return apply_filters( 'wpmudev_vids_assets_get_styles', $styles, $context );
	}

	/**
	 * Add clipboard JS to the scripts list if required.
	 *
	 * @param array  $scripts Scripts list.
	 * @param string $context Context.
	 *
	 * @since 1.8.0
	 * @since 1.8.4 Added $context param.
	 *
	 * @return array
	 */
	public function register_clipboard( $scripts, $context ) {
		global $wp_version;

		// We need to include the lib manually for WP below 5.2.
		if ( 'admin' === $context && version_compare( $wp_version, '5.2', '<' ) ) {
			$scripts['clipboard'] = array(
				'src'  => 'clipboard.min.js',
				'deps' => array( 'jquery' ),
			);
		}

		return $scripts;
	}
}