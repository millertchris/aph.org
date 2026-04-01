<?php
/**
 * Messages Tab.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'SFL_Messages_Tab' ) ) {
	return new SFL_Messages_Tab();
}

/**
 * SFL_Messages_Tab.
 */
class SFL_Messages_Tab extends SFL_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'messages';
		$this->label = esc_html__( 'Messages', 'save-for-later-for-woocommerce' );

		parent::__construct();
	}

	/**
	 * Output the Messages Tab content.
	 */
	public function messages_section_array() {

		return array(
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Save For Later Link Text', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_link_section',
			),
			array(
				'title'   => esc_html__( '"Save For Later" Text to display on Cart Page', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_btn_text' ),
				'type'    => 'text',
				'default' => 'Save for Later',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_link_section',
			),
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Saved For Later Table Messages', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_table_messages',
			),
			array(
				'title'   => esc_html__( 'Message to display on each product when the price has increased', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_price_inc_msg' ),
				'type'    => 'text',
				'default' => 'After you saved this product, the price now has increased by {increased_price}.',
			),
			array(
				'title'   => esc_html__( 'Message to display on each product when the price has decreased', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_price_dec_msg' ),
				'type'    => 'text',
				'default' => 'After you saved this product, the price now has decreased by {decreased_price}.',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_table_messages',
			),
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Stock Message', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_stock_section',
			),
			array(
				'title'   => esc_html__( 'Message to display when a quantity of the saved later product is less than the available quantity while adding the product to cart', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_qty_change_msg' ),
				'type'    => 'text',
				'default' => 'The stock of this product has been reduced now compared to when you saved this product[sfl_reduced_stock].',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_stock_section',
			),
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Cart Message', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_cart_section',
			),

			array(
				'title'   => esc_html__( 'Message to display when a user added the product to Saved for Later Table', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_cart_to_list_msg' ),
				'type'    => 'text',
				'default' => 'You have successfully added the product to Saved for Later Table.',
			),
			array(
				'title'   => esc_html__( 'Message to display when a user moved the product to cart from Saved for Later Table', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_list_to_cart_msg' ),
				'type'    => 'text',
				'default' => 'You have successfully moved the product to cart from your Saved for Later Table.',
			),
			array(
				'title'   => esc_html__( 'Message to display when a user removed the product from Saved for Later Table', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_cart_remove_msg' ),
				'type'    => 'text',
				'default' => 'You have successfully deleted the product from your Saved for Later Table.',
			),
			array(
				'title'   => esc_html__( 'Bulk Action - Products Moving to Cart from Saved for Later Table', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_list_to_cart_msg_bulk' ),
				'type'    => 'text',
				'default' => 'You have successfully moved the products to the cart from your Saved for Later Table.',
			),
			array(
				'title'   => esc_html__( 'Bulk Action - Removing Products from Saved for Later Table', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_cart_remove_msg_bulk' ),
				'type'    => 'text',
				'default' => 'You have successfully deleted the products from your Saved for Later Table.',
			),
			array(
				'title'   => esc_html__( 'Message to display when a user moved the product to cart from Saved for Later Table which is already exist', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_cart_exist_remove_msg' ),
				'type'    => 'text',
				'default' => 'This product already exists in the cart. Hence, the entry from the Saved for Later Table has been removed.',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_cart_section',
			),
		);
	}
}

return new SFL_Messages_Tab();
