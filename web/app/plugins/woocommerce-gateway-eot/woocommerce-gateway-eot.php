<?php
/**
 * Plugin Name: WooCommerce EOT Gateway
 * Plugin URI: https://mightily.com
 * Description: Allows Teachers to use EOT funds to place orders. Requires approval from EOT.
 * Author: Mightily
 * Author URI: https://mightily.com
 * Version: 1.0
 * Text Domain: wc-gateway-eot
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2015-2016 Mightily, Inc. (curiosity@Mightily.com) and WooCommerce
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Gateway-eot
 * @author    Mightily
 * @category  Admin
 * @copyright Copyright (c) 2015-2016, Mightily, Inc. and WooCommerce
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 *
 * This offline gateway forks the WooCommerce core "Cheque" payment gateway to create another EOT Payment method.
 */
 
defined( 'ABSPATH' ) or exit;


// Make sure WooCommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	return;
}


/**
 * Add the gateway to WC Available Gateways
 * 
 * @since 1.0.0
 * @param array $gateways all available WC gateways
 * @return array $gateways all WC gateways + offline gateway
 */
function wc_eot_add_to_gateways( $gateways ) {
	$gateways[] = 'WC_Gateway_EOT';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'wc_eot_add_to_gateways' );


/**
 * Adds plugin page links
 * 
 * @since 1.0.0
 * @param array $links all plugin links
 * @return array $links all plugin links + our custom links (i.e., "Settings")
 */
function wc_eot_gateway_plugin_links( $links ) {

	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=eot_gateway' ) . '">' . __( 'Configure', 'wc-gateway-eot' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_eot_gateway_plugin_links' );


/**
 * Offline Payment Gateway
 *
 * Provides an Offline Payment Gateway; mainly for testing purposes.
 * We load it later to ensure WC is loaded first since we're extending it.
 *
 * @class 		WC_Gateway_EOT
 * @extends		WC_Payment_Gateway
 * @version		1.0.0
 * @package		WooCommerce/Classes/Payment
 * @author 		Mightily
 */
add_action( 'plugins_loaded', 'wc_eot_gateway_init', 11 );

function wc_eot_gateway_init() {

	class WC_Gateway_EOT extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 */
		public function __construct() {
	  
			$this->id                 = 'eot_gateway';
			$this->icon               = apply_filters('woocommerce_offline_icon', '');
			$this->has_fields         = false;
			$this->method_title       = __( 'EOT', 'wc-gateway-eot' );
			$this->method_description = __( 'Allows teachers and fqr accounts to choose their EOT as a payment option. Requires manual approval from EOT.', 'wc-gateway-eot' );
		  
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
		  
			// Define user set variables
			$this->title        = $this->get_option( 'title' );
			$this->description  = $this->get_option( 'description' );
			$this->instructions = $this->get_option( 'instructions', $this->description );
		  
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );
		  
			// Customer Emails
			add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
		}
	
	
		/**
		 * Initialize Gateway Settings Form Fields
		 */
		public function init_form_fields() {
	  
			$this->form_fields = apply_filters( 'wc_eot_form_fields', array(
		  
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wc-gateway-eot' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable EOT Payment', 'wc-gateway-eot' ),
					'default' => 'yes'
				),
				
				'title' => array(
					'title'       => __( 'Title', 'wc-gateway-eot' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'wc-gateway-eot' ),
					'default'     => __( 'EOT Payment', 'wc-gateway-eot' ),
					'desc_tip'    => true,
				),
				
				'description' => array(
					'title'       => __( 'Description', 'wc-gateway-eot' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'wc-gateway-eot' ),
					'default'     => __( 'Your order will be sent to your EOT for approval.', 'wc-gateway-eot' ),
					'desc_tip'    => true,
				),
				
				'instructions' => array(
					'title'       => __( 'Instructions', 'wc-gateway-eot' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wc-gateway-eot' ),
					'default'     => '',
					'desc_tip'    => true,
				),
			) );
		}
	
	
		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) );
			}
		}
	
	
		/**
		 * Add content to the WC emails.
		 *
		 * @access public
		 * @param WC_Order $order
		 * @param bool $sent_to_admin
		 * @param bool $plain_text
		 */
		public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
		
			if ( $this->instructions && ! $sent_to_admin && $this->id === $order->payment_method && $order->has_status( 'pending' ) ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
		}
	
	
		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
	
			$order = wc_get_order( $order_id );
			
			// Mark as pending (we're awaiting the payment)
			$order->update_status( 'pending', __( 'Marked as Pending Payment, Awaiting EOT Approval', 'wc-gateway-eot' ) );
			
			// Reduce stock levels
			$order->reduce_order_stock();
			
			// If teacher, add their EOTs Id as meta data for future reference
			// Also add their group id (fq account) as meta data for future reference
			
			if (is_user_role('teacher')) {

				// Get the EOT from the group this teacher is in.
				// Use the user id to get their group id
				$user_group_id = 0;

				foreach (wp_get_terms_for_user(get_current_user_id(), 'user-group') as $group) {
					$user_group_id = $group->term_id;

					break;
				}

				// Get user ids in group.
				$term = get_term_by('id', $user_group_id, 'user-group');

				$user_ids = get_objects_in_term($term->term_id, 'user-group');

				// Find the first EOT in the group
				$group_eot_id = 0;

				foreach ($user_ids as $group_member_id) {
					if (is_user_role('eot', $group_member_id)) {
						$group_eot_id = $group_member_id;

						break;
					}
				}
				
				// Add eot id as meta data
				update_post_meta($order->ID, '_eot_id', $group_eot_id);
				
				// Add fq account as meta data
				update_post_meta($order->ID, '_fq_account', $user_group_id);
				
				// Add fq account name as meta data
				update_post_meta($order->ID, '_fq_account', $user_group_id);
				// Add account name as meta data
				$fq_account_name = get_term_by('term_taxonomy_id', $user_group_id);
				$fq_account_name = $fq_account_name->name;
				update_post_meta($order->ID, '_fq_account_name', $fq_account_name);				

			}
			
			// Remove cart
			WC()->cart->empty_cart();
			
			
			// Return thankyou redirect
			return array(
				'result' 	=> 'success',
				'redirect'	=> $this->get_return_url( $order )
			);
		}
	
  } // end \WC_Gateway_EOT class
}