<?php
/**
 * Save for Later Main Class
 *
 * @package Save for Later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'SFL_Save_Later' ) ) {

	/**
	 * Main SFL_Save_Later Class.
	 * */
	final class SFL_Save_Later {

		/**
		 * Version.
		 *
		 * @var string
		 * */
		private $version = '3.9.0';

		/**
		 * WordPress Requires.
		 *
		 * @var String
		 * */
		public static $wp_requires = '4.6';

		/**
		 * WooCommerce Requires.
		 *
		 * @var String
		 * */
		public static $wc_requires = '3.5';

		/**
		 * Compatibility.
		 *
		 * @var array
		 * */
		protected $compatibilities;

		/**
		 * The single instance of the class.
		 *
		 * @var string
		 * */
		protected static $instance = null;

		/**
		 * Load SFL_Save_Later Class in Single Instance.
		 *
		 * @since 1.0
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning has been forbidden
		 *
		 * @since 1.0
		 * */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', '1.0' );
		}

		/**
		 * Unserialize the class data has been forbidden.
		 *
		 * @since 1.0
		 * */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, 'You are not allowed to perform this action!!!', '1.0' );
		}

		/**
		 * Constructor.
		 *
		 * @since 1.0
		 * */
		public function __construct() {
			$this->define_constants();
			$this->include_files();
			$this->init_hooks();
		}

		/**
		 * Prepare the constants value array.
		 *
		 * @since 1.0
		 * */
		private function define_constants() {

			$constant_array = array(
				'SFL_VERSION'        => $this->version,
				'SFL_LOCALE'         => 'save-for-later-for-woocommerce',
				'SFL_FOLDER_NAME'    => 'save-for-later-for-woocommerce',
				'SFL_ABSPATH'        => dirname( SFL_PLUGIN_FILE ) . '/',
				'SFL_ADMIN_URL'      => admin_url( 'admin.php' ),
				'SFL_ADMIN_AJAX_URL' => admin_url( 'admin-ajax.php' ),
				'SFL_PLUGIN_SLUG'    => plugin_basename( SFL_PLUGIN_FILE ),
				'SFL_PLUGIN_PATH'    => untrailingslashit( plugin_dir_path( SFL_PLUGIN_FILE ) ),
				'SFL_PLUGIN_URL'     => untrailingslashit( plugins_url( '/', SFL_PLUGIN_FILE ) ),
			);

			/**
			 * Filter SFL Constants.
			 *
			 * @since 1.0
			 * */
			$constant_array = apply_filters( 'sfl_define_constants', $constant_array );

			if ( is_array( $constant_array ) && ! empty( $constant_array ) ) {
				foreach ( $constant_array as $name => $value ) {
					$this->define_constant( $name, $value );
				}
			}
		}

		/**
		 * Define the Constants value.
		 *
		 * @since 1.0
		 * @param String $name Constant Name.
		 * @param String $value Constant Value.
		 * */
		private function define_constant( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Load plugin the translate files.
		 *
		 * @since 1.0
		 * */
		private function load_plugin_textdomain() {
			if ( function_exists( 'determine_locale' ) ) {
				$locale = determine_locale();
			} else {
				$locale = is_admin() ? get_user_locale() : get_locale(); // @todo Remove when start supporting WP 5.0 or later.
			}

			/**
			 * Filter SFL Locale.
			 *
			 * @since 1.0
			 * */
			$locale = apply_filters( 'plugin_locale', $locale, 'save-for-later-for-woocommerce' );

			unload_textdomain( 'save-for-later-for-woocommerce' );
			load_textdomain( 'save-for-later-for-woocommerce', WP_LANG_DIR . '/save-for-later-for-woocommerce/save-for-later-for-woocommerce-' . $locale . '.mo' );
			load_textdomain( 'save-for-later-for-woocommerce', WP_LANG_DIR . '/plugins/save-for-later-for-woocommerce-' . $locale . '.mo' );
			load_plugin_textdomain( 'save-for-later-for-woocommerce', false, dirname( plugin_basename( SFL_PLUGIN_FILE ) ) . '/languages' );
		}

		/**
		 * Include the files.
		 *
		 * @since 1.0
		 * */
		private function include_files() {
			include_once SFL_ABSPATH . 'inc/class-sfl-autoload.php';
			include_once SFL_ABSPATH . 'inc/class-sfl-install.php';
			include_once SFL_ABSPATH . 'inc/class-sfl-date-time.php';
			include_once SFL_ABSPATH . 'inc/privacy/class-sfl-privacy.php';
			include_once SFL_ABSPATH . 'inc/class-sfl-query.php';
			include_once SFL_ABSPATH . 'inc/class-sfl-register-post-types.php';
			include_once SFL_ABSPATH . 'inc/class-sfl-register-post-status.php';
			include_once SFL_ABSPATH . 'inc/abstracts/class-sfl-settings-page.php';
			include_once SFL_ABSPATH . 'inc/abstracts/class-sfl-post.php';
			include_once SFL_ABSPATH . 'inc/entity/class-sfl-list.php';
			include_once SFL_ABSPATH . 'inc/entity/class-sfl-log-list.php';
			include_once SFL_ABSPATH . 'inc/sfl-layout-functions.php';
			include_once SFL_ABSPATH . 'inc/sfl-common-functions.php';
			include_once SFL_ABSPATH . 'inc/sfl-post-functions.php';
			include_once SFL_ABSPATH . 'inc/sfl-post-common-functions.php';
			include_once SFL_ABSPATH . 'inc/class-sfl-order-handler.php';
			include_once SFL_ABSPATH . 'inc/class-sfl-blocks-compatibility.php';

			if ( is_admin() ) {
				$this->include_admin_files();
			}

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->include_frontend_files();
			}

			// Compatibility classes.
			include_once SFL_ABSPATH . 'inc/compatibility/class-sfl-compatibility-instances.php';
		}

		/**
		 * Include required files.
		 *
		 * @since 1.0
		 * */
		private function include_admin_files() {
			include_once SFL_ABSPATH . 'inc/admin/class-sfl-admin-assets.php';
			include_once SFL_ABSPATH . 'inc/admin/class-sfl-admin-ajax.php';
			include_once SFL_ABSPATH . 'inc/admin/menu/class-sfl-menu-management.php';
			include_once SFL_ABSPATH . 'inc/admin/class-sfl-post-table.php';
		}

		/**
		 * Include admin files.
		 *
		 * @since 1.0
		 * */
		private function include_frontend_files() {
			include_once SFL_ABSPATH . 'inc/frontend/class-sfl-frontend-assets.php';
			include_once SFL_ABSPATH . 'inc/frontend/class-sfl-handler.php';
			include_once SFL_ABSPATH . 'inc/frontend/class-sfl-actions-handler.php';
			include_once SFL_ABSPATH . 'inc/frontend/class-sfl-restrictions.php';
		}

		/**
		 * Define the hooks.
		 *
		 * @since 1.0
		 * */
		private function init_hooks() {
			add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_compatibility_with_wc_hpos' ) );
			add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );

			register_activation_hook( SFL_PLUGIN_FILE, array( 'SFL_Install', 'install' ) ); // Register the plugin.
		}

		/**
		 * Init.
		 *
		 * @since 1.0
		 * */
		public function plugins_loaded() {
			/**
			 * Action hook to Load file before plugin loaded.
			 *
			 * @since 1.0
			 */
			do_action( 'sfl_before_plugin_loaded' );

			$this->load_plugin_textdomain();

			SFL_Compatibility_Instances::instance();

			/**
			 * Action hook to Load file After plugin loaded.
			 *
			 * @since 1.0
			 */
			do_action( 'sfl_after_plugin_loaded' );
		}

		/**
		 * Declare compatibility with HPOS Plugin.
		 *
		 * @since 3.4.0
		 *
		 * @return void
		 * */
		public static function declare_compatibility_with_wc_hpos() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', SFL_PLUGIN_FILE, true );
			}
		}

		/**
		 * Templates.
		 *
		 * @since 1.0
		 * */
		public function templates() {
			return SFL_PLUGIN_PATH . '/templates/';
		}

		/**
		 * Get the plugin url.
		 *
		 * @return String
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', SFL_PLUGIN_FILE ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return String
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( SFL_PLUGIN_FILE ) );
		}

		/**
		 * Compatibilities instances.
		 *
		 * @since 3.8.0
		 * */
		public function compatibilities() {
			return $this->compatibilities;
		}
	}

}
