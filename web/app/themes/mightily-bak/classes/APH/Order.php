<?php

namespace APH;

/**
 * Class Order
 * @package APH
 *
 * A model of a wc_order wih additional logic
 */
class Order {

    /** @var  \WC_Order */
    var $order = null;
    var $order_id = null;

	const SysproOrderNumber = 'syspro_order_number';

	function __construct($order)
	{
		if (is_integer($order)) {
			$this->order_id = $order;
			$this->order = wc_get_order($this->order_id);
		} else {
			$this->order = $order;
			$this->order_id = $this->order->get_id();
		}
	}

	function getFQAName() {
        return get_post_meta($this->order_id, '_fq_account_name', true);
	}

    function getEOT() {
        $eot_id = get_post_meta($this->order_id, '_eot_id', true);
        if ($eot_id) {
        	return get_userdata($eot_id);
        } else {
        	return null;
        }
    }

	function setEOT($eot_id) {
		update_post_meta($this->order_id, '_eot_id', $eot_id);
	}

    function getFQAccountId() {
        return get_post_meta($this->order_id, '_fq_account', true);
    }

	function setFQAaccountId($value) {
		update_post_meta($this->order_id, '_fq_account', $value);

		// Set the Name for the account.
		$fq_account = get_term_by('term_taxonomy_id', $value);
		update_post_meta($this->order_id, '_fq_account_name', $fq_account->name);
	}

	/* APH-447 Syspro Order Number - keeping it all in one place within the order */
	function getSysproId() {
		$sysproId = \get_post_meta($this->order_id, self::SysproOrderNumber, true);
		if($sysproId){
			return $sysproId;
		} else {
			return '--';
		}
	}

	function setSysproId($value) {
		\update_post_meta($this->order_id, self::SysproOrderNumber, sanitize_text_field($value));
		return $value;
	}

	function getSysproCustomerId() {
		$customerId = $this->order->get_customer_id();
		$sysproCustomer = get_user_meta($customerId, 'sysproCustomer', true);
		if($sysproCustomer){
			return $sysproCustomer;
		} else {
			return '--';
		}		
	}	

	static function actionSysproOrderNumber_RestApiInit() {
		\register_rest_field('shop_order', self::SysproOrderNumber, [
			'get_callback' => function ($params) {
				$order = new \APH\Order($params['id']);
				return $order->getSysproId();
			},
			'update_callback' => function ($value, $object, $fieldName) {
					$order = new \APH\Order($object->ID);
					$order->setSysproId($value);
			}
		]);
	}

	static function syspro_order_number_add_to_admin_screen($order) {

		$objOrder = new \APH\Order($order);
		$sysproId = $objOrder->getSysproId();
	
		echo '<p class="form-field syspro_id_field form-field-wide">
		<label for="eot_id">Syspro Order #:</label><span id="sysproOrderId">' . $sysproId . '</span></p>';
	}

	static function syspro_customer_number_add_to_admin_screen($order) {

		$objOrder = new \APH\Order($order);
		$customerId = $objOrder->getSysproCustomerId();
	
		echo '<p class="form-field syspro_id_field form-field-wide">
		<label for="eot_id">Syspro Customer #:</label><span id="sysproCustomerId">' . $customerId . '</span></p>'; ?>
		<script>
			jQuery(document).ready(function(){
				if(jQuery('#select2-customer_user-container').length > 0){
					jQuery('#select2-customer_user-container').append(' SYS #' + jQuery('#sysproCustomerId').text());
				}
			});
		</script><?php
	}	

	static function eot_id_add_to_admin_screen($order){
		if (get_post_meta($order->get_id(), '_eot_id')) {
			$eot_id_array = get_post_meta($order->get_id(), '_eot_id');
			$eot_id = $eot_id_array[0];
			$eot_info = get_userdata($eot_id);
	
			echo '<p class="form-field eot_id_field form-field-wide">
			<label for="eot_id">EOT:</label>'.$eot_info->first_name.' '.$eot_info->last_name.' (WC Customer #: '.$eot_id.')</p>';
		}
	}

	// static function fq_account_add_to_admin_screen($order){
	// 	// If $order is an int, we want to get the order object
	// 	if(is_int($order)){
	// 		$order = wc_get_order($order);
	// 	}
	// 	if (get_post_meta($order->get_id(), '_eot_id')) {
	// 		$eot_id_array = get_post_meta($order->get_id(), '_eot_id');
	// 		$eot_id = $eot_id_array[0];
	// 		$eot_info = get_userdata($eot_id);

	// 		// Get FQ Accounts that this EOT has access to
	// 		$eot_user_obj = get_user_by('id', $eot_id);
	// 		$fq_options = [];
	// 		$fq_options['-1'] = 'Not Set';
	// 		//print_r(wp_get_terms_for_user($eot_user_obj, 'user-group'));
	// 		foreach(wp_get_terms_for_user($eot_user_obj, 'user-group') as $group){
	// 			$fq_options[$group->term_id] = __($group->name, 'textdomain');
	// 			//$group->term_id;
	// 			//$group->name;
	// 		}
	// 		$default_value = (get_post_meta($order->get_id(), '_fq_account')) ? get_post_meta($order->get_id(), '_fq_account') : '';
	// 		if(isset($default_value[0])){
	// 			$default_value = $default_value[0];
	// 		} else {
	// 			$default_value = '-1';
	// 		}

	// 		$payment_type = $order->get_payment_method();
	// 		if($payment_type == 'accountfunds'){
	// 			woocommerce_wp_select([
	// 				'id'            => 'fq_account',
	// 				'label'         => __('FQ Account:', 'woocommerce'),
	// 				'value'         => $default_value,
	// 				'options'       => $fq_options,
	// 				'wrapper_class' => 'form-field-wide',
	// 			]);

	// 		}
	// 	}
	// }

	static function fq_account_add_to_admin_screen($order){
		// If $order is an int, we want to get the order object
		$fq_options = [];
		$fq_options['-1'] = 'Not Set';		
		if(is_int($order)){
			$order = wc_get_order($order);
		}
		$default_value = (get_post_meta($order->get_id(), '_fq_account')) ? get_post_meta($order->get_id(), '_fq_account') : '';
		if(isset($default_value[0])){
			$default_value = $default_value[0];
		} else {
			$default_value = '-1';
		}		
		if (get_post_meta($order->get_id(), '_eot_id')) {
			$eot_id_array = get_post_meta($order->get_id(), '_eot_id');
			$eot_id = $eot_id_array[0];
			$eot_info = get_userdata($eot_id);

			// Get FQ Accounts that this EOT has access to
			$eot_user_obj = get_user_by('id', $eot_id);
			//print_r(wp_get_terms_for_user($eot_user_obj, 'user-group'));
			foreach(wp_get_terms_for_user($eot_user_obj, 'user-group') as $group){
				$fq_options[$group->term_id] = __($group->name, 'textdomain');
				//$group->term_id;
				//$group->name;
			}
		}

		woocommerce_wp_select([
			'id'            => 'fq_account',
			'label'         => __('FQ Account:', 'woocommerce'),
			'value'         => $default_value,
			'options'       => $fq_options,
			'wrapper_class' => 'form-field-wide',
		]);

	}	

	static function created_by_add_to_admin_screen($order){
		if (get_post_meta($order->get_id(), '_created_by')) {
			$created_by_array = get_post_meta($order->get_id(), '_created_by');
			$created_by = $created_by_array[0];
			echo '<p class="form-field created_by_field form-field-wide">
			<label for="created_by">Created By:</label>'.$created_by.'</p>';
		}
	}	

	static function forcePaymentifEot($needs_payment, $cart){
		// If user is eot, eota or teacher we need to force needs payment to true
		if(is_user_logged_in()){
			$user = wp_get_current_user();
			$roles = $user->roles;
			$roles = array_reverse($roles);
			$primary_role = array_pop($roles);
			if(in_array($primary_role, FQ::$allowed_roles)){
				$needs_payment = true;
			}
		}
		return $needs_payment;
	}	

	static function woocommerce_shop_order_search($search_fields){
        $search_fields[] = 'syspro_order_number';
        return $search_fields;		
	}

	static function maybe_add_created_by_meta($order_id){
		if(is_admin()){
			$user = wp_get_current_user();
			update_post_meta($order_id, '_created_by', $user->user_email);
		}
	}

	static function force_order_item_processing($needs_procesing, $product, $order_id){
        // Check if the product is virtual and downloadable
        $is_virtual_downloadable_item = $product->is_downloadable() && $product->is_virtual();
    
        // If the product is virtual and downloadable, decide if it needs processing, meaning the order will first be set to Processing
        if ( $is_virtual_downloadable_item ) {
			if($product->get_price() != 0){
				$needs_procesing = true;
			}
        }
    
        return $needs_procesing;
	}

	static function maybe_add_shipping_line_meta($item, $package_key, $package, $order){
        if(!$item->get_meta('Ids')){
            $product_ids = [];
            $product_ids_string = false;
            if(isset($package['contents']) && is_array($package['contents'])){
                foreach($package['contents'] as $product){
                    $product_ids[] = $product['product_id'];
                }
                // Convert array to comma string;
                $product_ids_string = implode(', ', $product_ids);
            }
            // Add Ids meta data to this shipping item
            if($product_ids_string){
                $item->add_meta_data('Ids', $product_ids_string, true);
            }
        }
	}

	static function csr_auto_close_window($order_id){
		// Close the order complete window when the csr successfully enters payment for a customer
		if(is_user_role('customer_service')){ ?>
			<script>
				window.close();
			</script>
		<?php }
	}
	
	static function add_quote_post_status(){
		register_post_status('wc-quote', array(
			'label'                     => 'Quote',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Quote (%s)', 'Quote (%s)' )
		));
	}

	static function add_quote_order_status($order_statuses){
		$new_order_statuses = array();
		// add new order status after processing
		foreach ($order_statuses as $key => $status){
			$new_order_statuses[ $key ] = $status;
			if ('wc-pending' === $key) {
				$new_order_statuses['wc-quote'] = 'Quote';
			}			
		}
		return $new_order_statuses;
	}

	static function quote_order_status_editable($editable, $order){
        if($order->get_status() == 'quote'){
            $editable = true;
        }
        return $editable;		
	}

	static function assign_fs_license($fs_data, $order){
		// Credential setup, production and staging
		// {"PartnerId":"428576F1-AA4B-4F45-9380-E40E5D3418BF","ApiKey":"Q+IOtBkdpqk47YXG9j8TZmVPUwiwkbuMwEq6v6Udjos="}
		if(show_env_banner()){
			$curl_url = 'https://portalbeta.freedomscientific.com/api/partner/1/allocate';
			$base64_api_key = 'j5aY+3JPlfI3rnM0EUrK/ubBJlxGsXafyxmyXwsczfs=';
			$x_fs_partner_id = 'c9159cab-b448-439f-86ab-fe863d94e872';
		} else {
			$curl_url = 'https://portal.freedomscientific.com/api/partner/1/allocate';
			$base64_api_key = 'Q+IOtBkdpqk47YXG9j8TZmVPUwiwkbuMwEq6v6Udjos=';
			$x_fs_partner_id = '428576F1-AA4B-4F45-9380-E40E5D3418BF';
		}

        $fs_data_json = json_encode($fs_data); 

        // Format the api key to binary
        $binary_api_key = base64_decode($base64_api_key);

        $x_fs_hmac_signature = hash_hmac('sha256', $fs_data_json, $binary_api_key);
        $x_fs_hmac_signature = hex2bin($x_fs_hmac_signature);
        $x_fs_hmac_signature = base64_encode($x_fs_hmac_signature);

        $ch = curl_init($curl_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fs_data_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'x-fs-partner-id: '.$x_fs_partner_id,
            'x-fs-hmac-signature: '.$x_fs_hmac_signature
        ));
        # Return response instead of printing.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        # Send request.
        $result = curl_exec($ch);
        // Check for price in results. If there is a price, we need to encode a string to purchase on aph website
        // echo $result;
        // $decoded = json_decode($result);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpcode == '200'){
            // Success, body is empty
            $order_meta = $fs_data['count'] . ' license(s) issued for Freedom Scientific Product ID ' . $fs_data['productId'];
        } else {
            // Error, body will contain error data
            $order_meta = 'Error delegating license from Freedom Scientific. Error message received: '. $result;
        }
        $existing_order_meta = get_post_meta($fs_data['orderNumber'], '_fs_license_requested', true);
        $new_order_meta = $existing_order_meta . ', ' . $order_meta;
        add_post_meta($fs_data['orderNumber'], '_fs_license_requested', $new_order_meta, true);
        $order->add_order_note(__($order_meta));		
	}

    static function init_fs($order_id) {
        // If the license hasnt been requested yet, continue
        if(get_post_meta($order_id, '_fs_license_requested', true) == ''){
            // Get the order items to see if this order contains fs products
            $order = wc_get_order($order_id);
            foreach($order->get_items() as $item_id => $item){
                $product_id = $item->get_product_id();
                if(get_field('fs_product_id', $product_id) && get_field('fs_product_id', $product_id) != ''){
                    $fs_data = [
                        "allocateTo" => [
                            "email" => $order->get_billing_email(),
                            "name"=>  $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()                            
                        ],
                        "productId" => get_field('fs_product_id', $product_id),
                        "count" => $item->get_quantity(),
                        "orderNumber" => $order_id,
                        // "emailCC" => "eot@mightily.com"                        
                    ];
                    \APH\Order::assign_fs_license($fs_data, $order);
                }
            }
        }
    }	

}

