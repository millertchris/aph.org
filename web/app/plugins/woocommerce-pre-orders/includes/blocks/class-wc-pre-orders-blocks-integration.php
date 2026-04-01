<?php
/**
 * WooCommerce Pre-orders Blocks Integration.
 *
 * A class to represent the block features to be added to the plugin.
 *
 * @package WooCommerce Pre-orders
 */
namespace WooCommerce\Pre_Orders\Blocks;

defined( 'ABSPATH' ) || exit;

use Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry;
use Automattic\WooCommerce\Blocks\Registry\Container;
use Automattic\WooCommerce\Blocks\Assets\Api as AssetApi;
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Domain\Services\ExtendRestApi;

/**
 * This class is responsible for integrating a new payment method when using WooCommerce Blocks.
 */
class WC_Pre_Orders_Blocks_Integration {

	public function __construct() {
		// Include needed files.
		$this->includes();

		// Add woocommerce blocks support.
		$this->add_woocommerce_block_support();

		// Enqueue checkout integration for block checkout
		add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after', array( $this, 'enqueue_checkout_integration' ) );
	}

	/**
	 * Add payment method block support.
	 */
	public function add_woocommerce_block_support() {

		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			// Register payment method integrations.
			add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_payment_method_integrations' ) );
			$this->register_payment_methods();
			$this->blocks_loaded();
		}
	}

	/**
	 * Register payment method.
	 *
	 * @return WC_Pre_Orders_Blocks_Gateway $instance pre-orders gateway instance.
	 */
	protected function register_payment_methods() {
		$container = Package::container();

		$container->register(
			WC_Pre_Orders_Blocks_Gateway::class,
			function( Container $container ) {
				$asset_api = $container->get( AssetApi::class );
				return new WC_Pre_Orders_Blocks_Gateway( $asset_api );
			}
		);
	}

	/**
	 * Register the payment requirements for blocks.
	 */
	public function blocks_loaded() {
		$args = array(
			'data_callback' => array( $this, 'add_pre_order_availability_payment_requirement' ),
		);
		if ( function_exists( 'woocommerce_store_api_register_payment_requirements' ) ) {
			woocommerce_store_api_register_payment_requirements( $args );
		} else {
			$extend = Package::container()->get( ExtendRestApi::class );
			$extend->register_payment_requirements( $args );
		}
		WC_Pre_Orders_Extend_Store_API::init();
	}

	/**
	 * Check if is a pre-order and charged upon release.
	 *
	 * @return bool
	 */
	public static function is_pre_order_and_charged_upon_release() {
		return \WC_Pre_Orders_Cart::cart_contains_pre_order() && \WC_Pre_Orders_Product::product_is_charged_upon_release( \WC_Pre_Orders_Cart::get_pre_order_product() );
	}

	/**
	 * Adds pre_order availability payment requirement for carts that contain a product that requires it.
	 *
	 * @return array
	 */
	public function add_pre_order_availability_payment_requirement() {
		if ( $this->is_pre_order_and_charged_upon_release() ) {
			return array( 'pre-orders' );
		}
		return array();
	}

	/**
	 * Register payment method integration.
	 *
	 * @param PaymentMethodRegistry $payment_method_registry Payment method registry object.
	 */
	public function register_payment_method_integrations( PaymentMethodRegistry $payment_method_registry ) {

		$payment_method_registry->register(
			Package::container()->get( WC_Pre_Orders_Blocks_Gateway::class )
		);
	}

	/**
	 * Include class that represents the gateway.
	 */
	public function includes() {
		require_once __DIR__ . '/class-wc-pre-orders-blocks-gateway.php';
	}

	/**
	 * Enqueue checkout integration for block checkout
	 *
	 * @since 2.3.0
	 */
	public function enqueue_checkout_integration() {
		// Prevent multiple calls
		static $checkout_integration_script_enqueued = false;
		if ( $checkout_integration_script_enqueued ) {
			return;
		}

		// Enqueue the checkout integration script
		$script_path       = WC_PRE_ORDERS_PLUGIN_URL . '/build/block-checkout/integration.js';
		$script_asset_path = WC_PRE_ORDERS_PLUGIN_PATH . '/build/block-checkout/integration.asset.php';
		$script_asset      = file_exists( $script_asset_path ) ? require $script_asset_path : array(
			'dependencies' => array(),
			'version'      => WC_PRE_ORDERS_VERSION,
		);
		wp_enqueue_script(
			'wc-pre-orders-checkout-integration',
			$script_path,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		// Localize the data for the checkout integrationscript
		$localized_data = array(
			'place_order_button_text' => sanitize_text_field(
				get_option(
					'wc_pre_orders_place_order_button_text',
					__( 'Place pre-order now', 'woocommerce-pre-orders' )
				)
			),
			'cart_contains_pre_order' => (bool) \WC_Pre_Orders_Cart::cart_contains_pre_order(),
		);
		wp_localize_script( 'wc-pre-orders-checkout-integration', 'wc_pre_orders_checkout_params', $localized_data );

		$checkout_integration_script_enqueued = true;
	}
}
