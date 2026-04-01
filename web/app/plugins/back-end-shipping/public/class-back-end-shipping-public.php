<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mightily.com
 * @since      1.0.0
 *
 * @package    Back_End_Shipping
 * @subpackage Back_End_Shipping/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Back_End_Shipping
 * @subpackage Back_End_Shipping/public
 * @author     Mightily <sos@mightily.com>
 */
class Back_End_Shipping_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Back_End_Shipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Back_End_Shipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/back-end-shipping-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Back_End_Shipping_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Back_End_Shipping_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/back-end-shipping-public.js', array( 'jquery' ), $this->version, false );

	}

	public function get_shipping_options($data){
        $json_data = $data->get_body();
        //$json_array = json_decode('[{"name":"John", "age":31, "city":"New York"}]');
        $shipping_data = json_decode($json_data);
        //$shipping_data->cartContents;
        //$shipping_data->shipTo->city;
        // Get some data from this order
        $country     = $shipping_data->shipTo->country;
        $state       = $shipping_data->shipTo->state;
        $postcode    = $shipping_data->shipTo->postCode;
        $city        = $shipping_data->shipTo->city;
        $order_items = $shipping_data->cartContents;
        $customer_role = $shipping_data->customerRole;
        if($customer_role == 'guest' || $customer_role == 'administrator'){
            $customer_role = 'customer';
        }
        // Bring in some front end classes
        WC()->frontend_includes();
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
        WC()->customer = new WC_Customer();
        // Set customer role based on chosen customers role on edit order screen
        WC()->customer->set_role($customer_role);
        // Set user role based on chosen customers role on edit order screen
        $current_user = wp_get_current_user();
        $current_user->set_role($customer_role);
        WC()->cart = new WC_Cart();

        // Reset shipping
        WC()->shipping()->reset_shipping();

        // Set correct temporary location based off of order details, not customer
        if ( $country != '' ) {
            WC()->customer->set_billing_location( $country, $state, $postcode, $city );
            WC()->customer->set_shipping_location( $country, $state, $postcode, $city );
        } else {
            WC()->customer->set_billing_address_to_base();
            WC()->customer->set_shipping_address_to_base();
        }

        // Remove all current items from cart
        if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
            WC()->cart->empty_cart();
        }

        // Add all order items to cart. You can only add products that are in stock!
        $errors = [];
        $shipped = [];
        // Added session variable to make preorders purchasable when using this endpoint
        $_SESSION['tmp_usr_get_cart'] = true;        
        foreach ($order_items as $order_item) {
            if(!WC()->cart->add_to_cart($order_item->productId, $order_item->qty)){
                $product = wc_get_product($order_item->productId);
                $errors[] = $product->get_sku();
            } else {
                $shipped[] = $order_item->productId;
            }
        }

        // Calculate shipping using cart techniques
        $packages = WC()->cart->get_shipping_packages();
        $shipping = WC()->shipping->calculate_shipping($packages);
        $packages = WC()->shipping->get_packages();
        $packages_formatted = ['packages' => array(), 'errors' => $errors, 'shipped' => $shipped];
        $i = 0;
        foreach($packages as $package){
            $packages_formatted['packages'][$i] = [];
            $packages_formatted['packages'][$i]['package_label'] = 'Package ' . ($i + 1);
            $packages_formatted['packages'][$i]['product_names'] = [];
            $packages_formatted['packages'][$i]['product_ids'] = [];
            $packages_formatted['packages'][$i]['rates'] = [];

            // Loop through contents and put together list of product names in the package.
            $product_names = [];
            $product_ids = [];
            foreach ($package['contents'] as $item_id => $values) {
				$product_names[$item_id] = $values['data']->get_name() . ' &times; ' . $values['quantity'];
                $product_ids[$item_id] = $values['product_id'];
			}
			$product_names = apply_filters( 'bes_woocommerce_shipping_package_details_array', $product_names, $package );
            $packages_formatted['packages'][$i]['product_names'] = implode(', ', $product_names);
            $packages_formatted['packages'][$i]['product_ids'] = implode(', ', $product_ids);

            // Loop through rates and add get label and cost of each rate.
            foreach($package['rates'] as $shipping_rate){
                $rate_meta_array = $shipping_rate->get_meta_data();
                // $shipping_items = '';
                // foreach($rate_meta_array as $rate_meta_key => $rate_meta_value){
                //     if($rate_meta_key == 'Items'){
                //         $shipping_items = $rate_meta_value;
                //     }
                // }
                // print_r($shipping_rate->get_instance_id());
                $packages_formatted['packages'][$i]['rates'][] = array(
                    'shipping_id' => $shipping_rate->get_method_id(),
                    'shipping_instance_id' => $shipping_rate->get_instance_id(),
                    'shipping_label' => $shipping_rate->get_label(),
                    'shipping_cost' => $shipping_rate->get_cost(),
                    //'shipping_items' => $shipping_items
                );
                //var_dump($shipping_rate);
                //echo $shipping_rate->get_label() . ': $' . $shipping_rate->get_cost() . ' <a href="#" class="add-shipping-line modal-close" data-shipping-label="'.$shipping_rate->get_label().'" data-shipping-cost="'.$shipping_rate->get_cost().'" data-shipping-items="">Add Shipping</a><br />';
            }
            $i++;
        }

        return $packages_formatted;

	}

	public function register_bes_rest() {
		register_rest_route( 'backendshipping/v1', 'options', array(
			'methods' => 'POST',
			'callback' => array($this, 'get_shipping_options'),
			'accept_json' => true
		));
    }
    
    public function add_line_item_meta($order, $data){
        $order_item_array = [];
        // Add some meta to the order items
        foreach($order->get_items() as $order_item){
            $order_item_array[$order_item->get_name()] = $order_item->get_product_id();
            $order_item->add_meta_data('_shipped', '1');
            //$order_item->save_meta_data();
            //$order_item->save();
        } 
        // Add some meta to the shipping items
        foreach($order->get_items('shipping') as $shipping_item_object){
            $shipping_item_data = $shipping_item_object->get_data();
            $shipping_items_meta = $shipping_item_object->get_meta('Items');
            if(!$shipping_items_meta){
                return false;
            }
            $shipping_items_array = explode(' &times; ', $shipping_items_meta);
            $shipping_item_ids = [];
            array_pop($shipping_items_array);
            foreach($shipping_items_array as $key => $shipping_item){
                if($key == 0){
                    $shipping_item_ids[] = $order_item_array[$shipping_item];
                } else {
                    $comma_pos = strpos($shipping_item, ',');
                    if($comma_pos !== false){
                        $shipping_item_ids[] = $order_item_array[substr($shipping_item, $comma_pos + 2)];
                    }
                }
            }
            $shipping_item_ids = implode(', ', $shipping_item_ids);
            $shipping_item_object->add_meta_data('Ids', $shipping_item_ids);
            //$shipping_item_object->save_meta_data();
            //$shipping_item_object->save();
        }           
    }

}
