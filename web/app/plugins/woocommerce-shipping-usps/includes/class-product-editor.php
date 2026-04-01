<?php
/**
 * Product editor handler class.
 *
 * @package WC_Shipping_USPS
 */

namespace WooCommerce\USPS;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\BlockTemplates\BlockInterface;
use WC_Shipping_USPS_Admin;

/**
 * Product editor handler.
 */
class Product_Editor {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_block_template_area_product-form_after_add_block_product-shipping-dimensions', array( $this, 'add_shipping_blocks' ) );
		add_action( 'woocommerce_block_template_area_product-form_after_add_block_product-variation-shipping-dimensions', array( $this, 'add_shipping_blocks' ) );
	}

	/**
	 * Add custom blocks to the product editor shipping section.
	 *
	 * @param BlockInterface $shipping_dimensions_block The shipping dimensions block.
	 */
	public function add_shipping_blocks( BlockInterface $shipping_dimensions_block ) {
		if ( ! method_exists( $shipping_dimensions_block, 'get_parent' ) ) {
			return;
		}

		$parent = $shipping_dimensions_block->get_parent();

		/**
		 * Add Envelope Checkbox Block.
		 *
		 * @phpstan-ignore-next-line This is a valid call to a method that exists in the ContainerInterface.
		 */
		$parent->add_block(
			array(
				'id'         => 'usps-envelope',
				'blockName'  => 'woocommerce/product-checkbox-field',
				'attributes' => array(
					'title'          => __( 'USPS extra settings', 'woocommerce-shipping-usps' ),
					'label'          => __( 'Envelope', 'woocommerce-shipping-usps' ),
					'property'       => 'meta_data.' . WC_Shipping_USPS_Admin::META_KEY_ENVELOPE,
					'tooltip'        => __( 'Use Envelope rates to ship package', 'woocommerce-shipping-usps' ),
					'checkedValue'   => 'yes',
					'uncheckedValue' => '',
				),
			)
		);

		/**
		 * Add Declared Value Text Block.
		 *
		 * @phpstan-ignore-next-line This is a valid call to a method that exists in the ContainerInterface.
		 */
		$parent->add_block(
			array(
				'id'         => 'usps-declared-value',
				'blockName'  => 'woocommerce/product-text-field',
				'attributes' => array(
					'label'       => __( 'Declared Value', 'woocommerce-shipping-usps' ),
					'property'    => 'meta_data.' . WC_Shipping_USPS_Admin::META_KEY_DECLARED_VALUE,
					'placeholder' => __( "Use Product's Price", 'woocommerce-shipping-usps' ),
					'tooltip'     => __( 'Items value sent with rate request for international shipping.', 'woocommerce-shipping-usps' ),
				),
			)
		);
	}
}

new Product_Editor();
