<?php
/**
 * Blocks Compatibility.
 *
 * @package Save for later/Class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SFL_Blocks_Compatibility' ) ) {
	/**
	 * Blocks Compatibility.
	 *
	 * @class SFL_Blocks_Compatibility
	 * @package Class
	 */
	class SFL_Blocks_Compatibility {

		/**
		 * Min required plugin versions to check.
		 *
		 * @var Array
		 */
		private static $required = array(
			'blocks' => '7.0.0',
		);

		/**
		 * Initialize.
		 *
		 * @since 3.8.0
		 */
		public static function init() {
			// When WooCommerceBlocks is loaded, set up the Integration class.
			add_action( 'woocommerce_blocks_loaded', __CLASS__ . '::setup_blocks_integration' );
		}

		/**
		 * Sets up the Blocks integration class.
		 *
		 * @since 3.8.0
		 */
		public static function setup_blocks_integration() {
			if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Package' ) || version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), self::$required['blocks'] ) <= 0 ) {
				return;
			}

			/**
			 * Filter the compatible blocks.
			 *
			 * @since 3.8.0
			 */
			$compatible_blocks = apply_filters( 'sfl_compatible_blocks', array( 'cart', 'checkout', 'mini-cart' ) );

			foreach ( $compatible_blocks as $block_name ) {
				add_action(
					"woocommerce_blocks_{$block_name}_block_registration",
					function ( $registry ) {
						$registry->register( SFL_Blocks_Integration::instance() );
					}
				);
			}
		}
	}

	SFL_Blocks_Compatibility::init();
}
