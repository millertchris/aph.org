<?php
    // =========================================================================
    // ADD MESSAGE ABOVE REGISTRATION FORM
    // =========================================================================
    function login_message() {
        if (get_option('woocommerce_enable_myaccount_registration') == 'yes') {
            ?>
    		<div class="woocommerce-info">
                <p><?php _e('The APH online shopping site cannot be used by customers outside of the United States, the United States Territories and Canada. If you need to have an order shipped to an international address, please order by calling 1-502-895-2405, Monday-Friday, 8:00 a.m. – 8:00 p.m. Eastern Standard Time.'); ?></p>                
    		</div>
    	<?php
        }
    }


    function remove_my_account_links($menu_links) {
        unset($menu_links['account-funds']); // Addresses


        //unset( $menu_links['dashboard'] ); // Remove Dashboard
        //unset( $menu_links['payment-methods'] ); // Remove Payment Methods
        //unset( $menu_links['orders'] ); // Remove Orders
        //unset( $menu_links['downloads'] ); // Disable Downloads
        //unset( $menu_links['edit-account'] ); // Remove Account details tab
        //unset( $menu_links['customer-logout'] ); // Remove Logout link

        return $menu_links;
    }

    /**
     * APH-466
     * define the woocommerce_default_address_fields callback
     *
     * @param array $fields
     *
     * @return array
     * 
     * UPDATE 11/11/2019: REMOVED THIS FILTER BECAUSE THIS FIELD IS NO LONGER NEEDED
     */
    // function filter_woocommerce_default_address_fields( $fields ) {

    //     // print_r($fields);

    //     $fields['company_2'] = [
    //         'label' => 'Company 2 (ex. Attn: xxxx)',
    //         'class' => ['form-row-wide'],
    //         'autocomplete' => 'organization',
    //         'priority' => 31,
    //         'required' => false
    //     ];

    //     return $fields;
    // };

