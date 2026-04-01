<?php
/**
 * Advanced Tab.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'SFL_Advanced_Tab' ) ) {
	return new SFL_Advanced_Tab();
}

/**
 * SFL_Advanced_Tab.
 */
class SFL_Advanced_Tab extends SFL_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'advanced';
		$this->label = esc_html__( 'Advanced', 'save-for-later-for-woocommerce' );

		parent::__construct();
	}

	/**
	 * Output the Advance Tab content.
	 */
	public function advanced_section_array() {

		return array(
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Restriction Settings', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_advanced_section',
			),
			array(
				'title'   => esc_html__( '"Save for Later" link will be displayed for', 'save-for-later-for-woocommerce' ),
				'type'    => 'select',
				'options' => array(
					'1' => esc_html__( 'All Product (s)', 'save-for-later-for-woocommerce' ),
					'2' => esc_html__( 'Include Product(s)', 'save-for-later-for-woocommerce' ),
					'3' => esc_html__( 'Exclude Product(s)', 'save-for-later-for-woocommerce' ),
					'5' => esc_html__( 'Include Categories', 'save-for-later-for-woocommerce' ),
					'6' => esc_html__( 'Exclude Categories', 'save-for-later-for-woocommerce' ),
				),
				'default' => '1',
				'id'      => $this->get_option_key( 'product_category' ),
			),
			array(
				'title'       => esc_html__( 'Include Product(s)', 'save-for-later-for-woocommerce' ),
				'id'          => $this->get_option_key( 'included_product' ),
				'class'       => 'sfl_product_cat_search_fields',
				'action'      => 'sfl_product_search',
				'type'        => 'sfl_custom_fields',
				'list_type'   => 'products',
				'sfl_field'   => 'ajaxmultiselect',
				'desc_tip'    => true,
				'desc'        => esc_html__( 'You can also choose multiple products', 'save-for-later-for-woocommerce' ),
				'placeholder' => esc_html__( 'Select a Product', 'save-for-later-for-woocommerce' ),
				'allow_clear' => true,
			),
			array(
				'title'       => esc_html__( 'Exclude Product(s)', 'save-for-later-for-woocommerce' ),
				'id'          => $this->get_option_key( 'exclude_product' ),
				'class'       => 'sfl_product_cat_search_fields',
				'action'      => 'sfl_product_search',
				'type'        => 'sfl_custom_fields',
				'list_type'   => 'products',
				'sfl_field'   => 'ajaxmultiselect',
				'desc_tip'    => true,
				'desc'        => esc_html__( 'You can also choose multiple products', 'save-for-later-for-woocommerce' ),
				'placeholder' => esc_html__( 'Select a Product', 'save-for-later-for-woocommerce' ),
				'allow_clear' => true,
			),
			array(
				'title'    => esc_html__( 'Include Categories', 'save-for-later-for-woocommerce' ),
				'type'     => 'multiselect',
				'multiple' => false,
				'class'    => 'sfl_select2 sfl_product_cat_search_fields',
				'options'  => sfl_get_wc_categories(),
				'id'       => $this->get_option_key( 'included_category' ),
			),
			array(
				'title'    => esc_html__( 'Exclude Categories', 'save-for-later-for-woocommerce' ),
				'type'     => 'multiselect',
				'multiple' => false,
				'class'    => 'sfl_select2 sfl_product_cat_search_fields',
				'options'  => sfl_get_wc_categories(),
				'id'       => $this->get_option_key( 'exclude_category' ),
			),
			array(
				'title'   => esc_html__( 'Allow "Save for Later" for', 'save-for-later-for-woocommerce' ),
				'type'    => 'select',
				'options' => array(
					'1' => esc_html__( 'All user(s)', 'save-for-later-for-woocommerce' ),
					'2' => esc_html__( 'Included user(s)', 'save-for-later-for-woocommerce' ),
					'3' => esc_html__( 'Excluded user(s)', 'save-for-later-for-woocommerce' ),
					'4' => esc_html__( 'Included User Role(s)', 'save-for-later-for-woocommerce' ),
					'5' => esc_html__( 'Excluded User Role(s)', 'save-for-later-for-woocommerce' ),
				),
				'default' => '1',
				'id'      => $this->get_option_key( 'user_roles_users' ),
			),
			array(
				'title'       => esc_html__( 'Include User(s)', 'save-for-later-for-woocommerce' ),
				'id'          => $this->get_option_key( 'included_user' ),
				'class'       => 'sfl_customers_roles_search',
				'action'      => 'sfl_customers_search',
				'type'        => 'sfl_custom_fields',
				'list_type'   => 'customers',
				'sfl_field'   => 'ajaxmultiselect',
				'placeholder' => esc_html__( 'Select a User', 'save-for-later-for-woocommerce' ),
				'allow_clear' => true,
			),
			array(
				'title'       => esc_html__( 'Exclude User(s)', 'save-for-later-for-woocommerce' ),
				'id'          => $this->get_option_key( 'exclude_user' ),
				'class'       => 'sfl_customers_roles_search',
				'action'      => 'sfl_customers_search',
				'type'        => 'sfl_custom_fields',
				'list_type'   => 'customers',
				'sfl_field'   => 'ajaxmultiselect',
				'placeholder' => esc_html__( 'Select a User', 'save-for-later-for-woocommerce' ),
				'allow_clear' => true,
			),
			array(
				'title'   => esc_html__( 'Include User Roles', 'save-for-later-for-woocommerce' ),
				'type'    => 'multiselect',
				'class'   => 'sfl_select2 sfl_customers_roles_search',
				'options' => sfl_get_user_roles(),
				'id'      => $this->get_option_key( 'included_user_role' ),
			),
			array(
				'title'   => esc_html__( 'Exclude User Roles', 'save-for-later-for-woocommerce' ),
				'type'    => 'multiselect',
				'class'   => 'sfl_select2 sfl_customers_roles_search',
				'options' => sfl_get_user_roles(),
				'id'      => $this->get_option_key( 'excluded_user_role' ),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_advanced_section',
			),
			array(
				'type'  => 'title',
				'title' => esc_html__( 'Style Settings', 'save-for-later-for-woocommerce' ),
				'id'    => 'sfl_advanced_style_section',
			),
			array(
				'title'   => esc_html__( 'Apply Styles from', 'save-for-later-for-woocommerce' ),
				'type'    => 'select',
				'options' => array(
					'1' => esc_html__( 'Plugin', 'save-for-later-for-woocommerce' ),
					'2' => esc_html__( 'Theme', 'save-for-later-for-woocommerce' ),
				),
				'default' => '1',
				'id'      => $this->get_option_key( 'apply_styles_from' ),
			),
			array(
				'title'   => esc_html__( 'Custom CSS', 'save-for-later-for-woocommerce' ),
				'id'      => $this->get_option_key( 'custom_css' ),
				'type'    => 'textarea',
				'default' => '',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'sfl_advanced_style_section',
			),
		);
	}
}

return new SFL_Advanced_Tab();
