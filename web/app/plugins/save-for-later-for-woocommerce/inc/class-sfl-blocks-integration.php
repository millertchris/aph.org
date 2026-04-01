<?php
/**
 * Blocks integration class.
 *
 * @package Save for Later/Class
 */

/**
 * A class for integrating with WooCommerce Blocks scripts.
 */
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if ( ! class_exists( 'SFL_Blocks_Integration' ) ) {
	/**
	 * Blocks integration class.
	 *
	 * @class  SFL_Blocks_Integration
	 * @implements   IntegrationInterface
	 * @package Class
	 */
	class SFL_Blocks_Integration implements IntegrationInterface {

		/**
		 * The single instance of the class.
		 *
		 * @var SFL_Blocks_Integration
		 */
		protected static $instance = null;

		/**
		 * Main SFL_Blocks_Integration instance. Ensures only one instance of SFL_Blocks_Integration is loaded or can be loaded.
		 *
		 * @since 3.8.0
		 * @return SFL_Blocks_Integration
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * The name of the integration.
		 *
		 * @since 3.8.0
		 * @return String
		 */
		public function get_name() {
			return 'save-for-later-for-woocommerce';
		}

		/**
		 * When called invokes any initialization/setup for the integration.
		 *
		 * @since 3.8.0
		 */
		public function initialize() {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
			add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
		}

		/**
		 * Returns an array of script handles to enqueue in the frontend context.
		 *
		 * @since 3.8.0
		 * @return Array
		 */
		public function get_script_handles() {
			return array( 'sfl-blocks-integration' );
		}

		/**
		 * Returns an array of script handles to enqueue in the editor context.
		 *
		 * @since 3.8.0
		 * @return Array
		 */
		public function get_editor_script_handles() {
			return array( 'sfl-blocks-integration' );
		}

		/**
		 * An array of key, value pairs of data made available to the block on the client side.
		 *
		 * @since 3.8.0
		 * @return Array
		 */
		public function get_script_data() {
			return array( 'sfl-blocks-integration' => 'active' );
		}

		/**
		 * Get the file modified time as a cache buster if we're in dev mode.
		 *
		 * @since 3.8.0
		 * @param String $file Local path to the file.
		 * @return String The cache buster value to use for the given file.
		 */
		protected function get_file_version( $file ) {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
				return filemtime( $file );
			}

			return SFL_VERSION;
		}

		/**
		 * Enqueue block assets for the editor.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_block_editor_assets() {
			$script_path       = 'blocks/admin/index.js';
			$script_url        = sfl()->plugin_url() . "/assets/{$script_path}";
			$script_asset_path = SFL_ABSPATH . 'assets/blocks/admin/index.asset.php';
			$script_asset      = file_exists( $script_asset_path ) ? require $script_asset_path : array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

			wp_register_script( 'sfl-admin-blocks-integration', $script_url, $script_asset['dependencies'], $script_asset['version'], true );
			wp_enqueue_script( 'sfl-admin-blocks-integration' );
		}

		/**
		 * Enqueue block assets for both editor and front-end.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_block_assets() {
			$script_path       = 'blocks/frontend/index.js';
			$style_path        = 'blocks/frontend/index.css';
			$script_url        = sfl()->plugin_url() . "/assets/{$script_path}";
			$style_url         = sfl()->plugin_url() . "/assets/{$style_path}";
			$script_asset_path = SFL_ABSPATH . 'assets/blocks/frontend/index.asset.php';
			$style_asset_path  = SFL_ABSPATH . 'assets/blocks/frontend/index.css';
			$script_asset      = file_exists( $script_asset_path ) ? require $script_asset_path : array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

			wp_enqueue_style( 'sfl-blocks-integration', $style_url, '', $this->get_file_version( $style_asset_path ), 'all' );
			wp_register_script( 'sfl-blocks-integration', $script_url, $script_asset['dependencies'], $script_asset['version'], true );
			wp_localize_script(
				'sfl-blocks-integration',
				'sfl_blocks_params',
				array(
					'sfl_valid_cart_items' => sfl_get_valid_cart_items(),
					'add_sfl_cart_url'     => sfl_get_args_added_url(
						sfl_get_cart_url(),
						array(
							'sfl_action' => 'sfl_list_add',
							'sfl_nonce'  => wp_create_nonce( 'sfl-list-add' ),
						)
					),
				)
			);
			wp_set_script_translations( 'sfl-blocks-integration', 'save-for-later-for-woocommerce', SFL_ABSPATH . 'languages/' );
		}
	}
}
