<?php
/**
 * Localization Tab.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'SFL_Localization_Tab' ) ) {
	return new SFL_Localization_Tab();
}

/**
 * SFL_Localization_Tab.
 */
class SFL_Localization_Tab extends SFL_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'localization';
		$this->label = esc_html__( 'Localization', 'save-for-later-for-woocommerce' );

		parent::__construct();
	}

	/**
	 * Output the Localization Tab content.
	 */
	public function localization_section_array() {

		return array(
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Saved For Later Label Customization', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_table_customization',
			),
			array(
				'title'   => esc_html__( 'Saved For Later Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_table' ),
				'type'    => 'text',
				'default' => 'Saved For Later',
			),
			array(
				'title'   => esc_html__( 'S.No', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_s_no' ),
				'type'    => 'text',
				'default' => 'S.No',
			),
			array(
				'title'   => esc_html__( 'Image Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_image' ),
				'type'    => 'text',
				'default' => 'Image',
			),
			array(
				'title'   => esc_html__( 'Product Name Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_pro_name' ),
				'type'    => 'text',
				'default' => 'Product Name',
			),
			array(
				'title'   => esc_html__( 'Price Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_pro_price' ),
				'type'    => 'text',
				'default' => 'Price',
			),
			array(
				'title'   => esc_html__( 'Quantity Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_pro_qty' ),
				'type'    => 'text',
				'default' => 'Quantity',
			),
			array(
				'title'   => esc_html__( 'Actions Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'sfl_pro_actions' ),
				'type'    => 'text',
				'default' => 'Actions',
			),
			array(
				'title'   => esc_html__( 'Move to Cart Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'move_to_cart' ),
				'type'    => 'text',
				'default' => 'Move to cart',
			),
			array(
				'title'   => esc_html__( 'Remove Label', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'remove' ),
				'type'    => 'text',
				'default' => 'Remove',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_table_customization',
			),
		);
	}
}

return new SFL_Localization_Tab();
