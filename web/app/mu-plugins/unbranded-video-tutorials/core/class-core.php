<?php
/**
 * The core plugin class.
 *
 * @link    https://wpmudev.com
 * @since   1.8.0
 *
 * @author  Joel James <joel@incsub.com>
 * @package WPMUDEV\Core
 */

namespace WPMUDEV_Videos\Core;

// If this file is called directly, abort.
defined( 'WPINC' ) || die;

use WPMUDEV_Videos\Core\Abstracts\Base;

/**
 * Class Core.
 *
 * @package WPMUDEV_Videos\Core
 */
class Core extends Base {

	/**
	 * Import class instance.
	 *
	 * @since 1.8.1
	 *
	 * @var Tasks\Import
	 */
	public $import;

	/**
	 * Setup the plugin and register all hooks.
	 *
	 * Pro version features and not initialized yet, so do not
	 * execute something on this hooks if you are checking for
	 * Pro version.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	public function setup() {
		// Register all actions and filters.
		$this->define();

		/**
		 * Important: Do not change the priority.
		 *
		 * We need to initialize the modules as early as possible
		 * but using `init` hook. Then only other hooks will work.
		 */
		add_action( 'init', array( $this, 'init_modules' ), -1 );

		// Initialize dash notification.
		add_action( 'init', array( $this, 'init_dash' ), 1 );

		/**
		 * Action hook to trigger after initializing all core actions.
		 *
		 * You still need to check if it Pro version or Free.
		 *
		 * @since 1.8.0
		 */
		do_action( 'wpmudev_vids_after_core_init' );
	}

	/**
	 * Register all the actions and filters for the plugin free features.
	 *
	 * Note: Module features are registered within the module class.
	 *
	 * @since 1.8.0
	 *
	 * @return void
	 */
	private function define() {
		// Initialize classes.
		Controllers\I18n::get();
		Controllers\Permission::get();
		Controllers\Compatibility::get();
		Controllers\Menu::get();
		Controllers\Assets::get();
		Controllers\Front::get();

		// Installer.
		Controllers\Installer::get();

		// Rest API.
		Endpoints\Settings::get();
		Endpoints\Actions::get();
		Endpoints\Summary::get();
		Endpoints\Data::get();

		// Setup views.
		Views\Admin::get();

		// Videos on screens.
		Controllers\Contextual::get();

		// Videos on screens.
		Tasks\Export::get();

		// Setup import instance.
		$this->import = new Tasks\Import();
	}

	/**
	 * Initialize modules for the core plugin.
	 *
	 * Note: Hooks that execute after init hook with priority 1 or higher
	 * will only work from this method. You need to handle the earlier hooks separately.
	 * Hook into `beehive_after_core_modules_init` to add new
	 * module.
	 *
	 * @since 3.2.0
	 */
	public function init_modules() {
		// Custom videos.
		Modules\Videos\Controller::get();
		Modules\Playlists\Controller::get();

		// Only after modules initialized.
		Modules\Content::get();

		/**
		 * Action hook to execute after free modules initialization.
		 *
		 * @since 3.2.0
		 */
		do_action( 'beehive_after_core_modules_init' );
	}

	/**
	 * Register WPMUDEV Dashboard for this plugin.
	 *
	 * @since 1.7
	 * @since 1.8.0 moved to core class.
	 *
	 * @return void
	 */
	public function init_dash() {
		// WPMUDEV Dashboard plugin.
		$file = WPMUDEV_VIDEOS_DIR . '/core/external/dash-notice/wpmudev-dash-notification.php';

		// If file exist, setup dash notice.
		if ( file_exists( $file ) ) {
			// Dash notices global.
			global $wpmudev_notices;

			// Add this plugin to the list.
			$wpmudev_notices[] = array(
				'id'      => 248,
				'name'    => 'WPMU DEV Videos',
				'screens' => array(
					'dashboard_page_wpmudev-videos-settings',
					'dashboard_page_wpmudev-videos-settings-network',
					'toplevel_page_video-tuts',
					'dashboard_page_video-tuts',
				),
			);

			// Include file.
			include_once $file;
		}
	}
}