<?php
/**
 * WooCommerce Pre-Orders
 *
 * @package   WC_Pre_Orders/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pre-Orders Admin Settings class.
 */
class WC_Pre_Orders_Admin_Settings {

	/**
	 * Settings page tab ID
	 *
	 * @var string
	 */
	private $settings_tab_id = 'pre_orders';

	/**
	 * Initialize the admin settings actions.
	 */
	public function __construct() {
		// Add 'Pre-Orders' tab to WooCommerce settings.
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 21, 1 );

		// Show settings.
		add_action( 'woocommerce_settings_tabs_' . $this->settings_tab_id, array( $this, 'show_settings' ) );

		// Save settings.
		add_action( 'woocommerce_update_options_' . $this->settings_tab_id, array( $this, 'save_settings' ) );
	}

	/**
	 * Add 'Pre-Orders' tab to WooCommerce Settings tabs
	 *
	 * @param  array $settings_tabs Tabs array sans 'Pre-Orders' tab.
	 *
	 * @return array $settings_tabs Now with 100% more 'Pre-Orders' tab!
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs[ $this->settings_tab_id ] = __( 'Pre-Orders', 'woocommerce-pre-orders' );

		return $settings_tabs;
	}

	/**
	 * Show the 'Pre-Orders' settings page.
	 */
	public function show_settings() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Save the 'Pre-Orders' settings page.
	 */
	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Returns settings array for use by output/save functions.
	 *
	 * @return array Settings.
	 */
	public function get_settings() {
		/**
		 * Filter the settings array.
		 *
		 * @since 1.0.0
		 * @param array $settings Settings.
		 */
		return apply_filters(
			'wc_pre_orders_settings',
			array(
				// Common actions section
				array(
					'title' => __( 'Common actions', 'woocommerce-pre-orders' ),
					'type'  => 'title',
					'desc'  => sprintf(
						'<a href="%s" class="button button-secondary" target="_blank">%s</a>',
						esc_url( admin_url( 'admin.php?page=wc_pre_orders&tab=manage' ) ),
						__( 'View All Pre-Orders', 'woocommerce-pre-orders' )
					),
				),
				array( 'type' => 'sectionend' ),

				// Button text customizations
				array(
					'title' => __( 'Button text customization', 'woocommerce-pre-orders' ),
					'type'  => 'title',
				),
				array(
					'title'    => __( '"Add to cart" button text', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Text displayed on the "Add to cart" button when a product is available for pre-order.', 'woocommerce-pre-orders' ),
					'desc_tip' => true,
					'id'       => 'wc_pre_orders_add_to_cart_button_text',
					'default'  => __( 'Pre-order now', 'woocommerce-pre-orders' ),
					'type'     => 'text',
				),
				array(
					'title'    => __( '"Place order" button text', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Text displayed on the "Place order" button when a customer is checking out with pre-ordered products.', 'woocommerce-pre-orders' ),
					'desc_tip' => true,
					'id'       => 'wc_pre_orders_place_order_button_text',
					'default'  => __( 'Place pre-order now', 'woocommerce-pre-orders' ),
					'type'     => 'text',
				),
				array( 'type' => 'sectionend' ),

				// Product messages
				array(
					'title' => __( 'Messages customization', 'woocommerce-pre-orders' ),
					/* translators: %1$s: Opening code tag %2$s: Closing code tag */
					'desc'  => sprintf( __( 'Use %1$s{availability_date}%2$s and %1$s{availability_time}%2$s to display when products will be available.', 'woocommerce-pre-orders' ), '<code>', '</code>' ),
					'type'  => 'title',
				),
				array(
					'title'    => __( 'Product page message', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Message displayed below the price on product pages. Use {availability_date} to show the release date. Basic HTML is allowed. Leave empty to hide.', 'woocommerce-pre-orders' ),
					'desc_tip' => true,
					'id'       => 'wc_pre_orders_single_product_message',
					/* translators: %s: Availability date placeholder */
					'default'  => sprintf( __( 'This item will be released %s.', 'woocommerce-pre-orders' ), '{availability_date}' ),
					'type'     => 'textarea',
				),
				array(
					'title'    => __( 'Product list message', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Message displayed under products in the product list. Use {availability_date} to show the release date. Basic HTML is allowed. Leave empty to hide.', 'woocommerce-pre-orders' ),
					'desc_tip' => true,
					'id'       => 'wc_pre_orders_shop_loop_product_message',
					/* translators: %s: Availability date placeholder */
					'default'  => sprintf( __( 'Available %s.', 'woocommerce-pre-orders' ), '{availability_date}' ),
					'type'     => 'textarea',
				),
				array( 'type' => 'sectionend' ),

				// Cart and checkout text
				array(
					'title' => __( 'Cart and checkout text', 'woocommerce-pre-orders' ),
					/* translators: %1$s: Opening code tag %2$s: Closing code tag */
					'desc'  => sprintf( __( 'Use %1$s{order_total}%2$s for the order total and %1$s{availability_date}%2$s for the product release date.', 'woocommerce-pre-orders' ), '<code>', '</code>' ),
					'type'  => 'title',
				),
				array(
					'title'    => __( 'Release date label', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Label for the release date in the cart. Leave empty to hide the date.', 'woocommerce-pre-orders' ),
					'desc_tip' => true,
					'id'       => 'wc_pre_orders_availability_date_cart_title_text',
					'default'  => __( 'Available', 'woocommerce-pre-orders' ),
					'type'     => 'text',
				),
				array(
					'title'    => __( 'Charged upon release text', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Text for orders charged on release date (pay later). Use {order_total} and {availability_date} to show details.', 'woocommerce-pre-orders' ),
					'desc_tip' => true,
					'id'       => 'wc_pre_orders_upon_release_order_total_format',
					/* translators: %1$s: Order total placeholder %2$s: Availability date placeholder */
					'default'  => sprintf( __( '%1$s charged %2$s', 'woocommerce-pre-orders' ), '{order_total}', '{availability_date}' ),
					'css'      => 'min-width: 300px;',
					'type'     => 'text',
				),
				array(
					'title'    => __( 'Charged upfront text', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Text for orders charged now (pay now). Use {order_total} to show the price.', 'woocommerce-pre-orders' ),
					'desc_tip' => true,
					'id'       => 'wc_pre_orders_upfront_order_total_format',
					/* translators: %s: Order total placeholder */
					'default'  => sprintf( __( '%s charged upfront', 'woocommerce-pre-orders' ), '{order_total}' ),
					'css'      => 'min-width: 150px;',
					'type'     => 'text',
				),
				array( 'type' => 'sectionend' ),

				// Out-of-stock settings
				array(
					'title' => __( 'Out-of-stock products', 'woocommerce-pre-orders' ),
					'type'  => 'title',
				),
				array(
					'title'    => __( 'Enable pre-orders for out-of-stock products', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Automatically enable pre-orders when a compatible product becomes out of stock', 'woocommerce-pre-orders' ),
					'desc_tip' => __( 'Only product types compatible with Pre-Orders (simple, variable, composite, bundle, booking, mix-and-match and subscription) will be affected. For variable products, all variations must be out of stock. Compatible out-of-stock products will be marked as "in stock" with stock management disabled.', 'woocommerce-pre-orders' ),
					'id'       => 'wc_pre_orders_auto_pre_order_out_of_stock',
					'default'  => 'no',
					'type'     => 'checkbox',
					'class'    => '',
				),
				array( 'type' => 'sectionend' ),

				// Test site settings
				array(
					'title' => __( 'Test site settings', 'woocommerce-pre-orders' ),
					'type'  => 'title',
				),
				array(
					'title'    => __( 'Disable automatic pre-order processing', 'woocommerce-pre-orders' ),
					'desc'     => __( 'Prevent pre-orders from processing automatically (for test sites). Your system will not charge customers or complete orders.', 'woocommerce-pre-orders' ),
					'desc_tip' => false,
					'id'       => 'wc_pre_orders_disable_auto_processing',
					'default'  => 'no',
					'type'     => 'checkbox',
				),
				array( 'type' => 'sectionend' ),
			)
		);
	}
}

new WC_Pre_Orders_Admin_Settings();
