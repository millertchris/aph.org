<?php
/**
 * Auto-loader.
 *
 * @package Save for Later/Class
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SFL_Autoload' ) ) {
	/**
	 * Auto-loader.
	 *
	 * @class SFL_Autoload
	 */
	class SFL_Autoload {

		/**
		 * Path to the includes directory.
		 *
		 * @var String
		 */
		private $include_path = '';

		/**
		 * Construct SFL_Autoload
		 *
		 * @since 3.8.0
		 */
		public function __construct() {
			$this->include_path = SFL_ABSPATH . 'inc/';

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Auto-load our classes on demand to reduce memory consumption.
		 *
		 * @since 3.8.0
		 * @param String $class Class name.
		 */
		public function autoload( $class ) {
			$class = strtolower( $class );

			// Make sure our classes are going to load.
			if ( 0 !== strpos( $class, 'sfl_' ) ) {
				return;
			}

			$file = 'class-' . str_replace( '_', '-', $class ) . '.php'; // Retrieve file name from class name.
			$path = $this->include_path . $file;

			if ( false !== strpos( $class, '_data_store' ) ) {
				$path = $this->include_path . 'data-stores/' . $file;
			} elseif ( false !== strpos( $class, 'meta_box_' ) ) {
				$path = $this->include_path . 'admin/meta-boxes/' . $file;
			} elseif ( false !== strpos( $class, 'compatible_' ) ) {
				$path = $this->include_path . 'compatibles/' . $file;
			} elseif ( false !== strpos( $class, '_shortcode_' ) ) {
				$path = $this->include_path . 'shortcodes/' . $file;
			}

			// Include a class file.
			if ( $path && is_readable( $path ) ) {
				include_once $path;
			}
		}
	}

	new SFL_Autoload();
}
