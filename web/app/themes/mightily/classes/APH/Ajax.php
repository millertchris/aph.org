<?php

/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-08-22
 * Time: 09:56
 */

namespace APH;

/**
 * Manage helpers for the wc order fields.
 *
 * Class Fields
 * @package APH
 */
class Ajax {

    static $allowed_roles = [Roles::EOT, Roles::OOA, Roles::TVI];

    /**
     * Give a user_id, return all possible EOT's this user can interact with.
     * Used as an example as to how to use the sytem.
     *
     * @param $user_id
     *
     * @return array
     */

    // Add PO Number to checkout field
    static function validate_po() {

        check_ajax_referer('po-nonce', 'security');

        // The $_REQUEST contains all the data sent via ajax
        if (isset($_REQUEST)) {
            $customer_id = '';
            if ($_REQUEST['customer_id'] && $_REQUEST['customer_id'] != '') {
                $customer_id = $_REQUEST['customer_id'];
            } else {
                $customer_id = get_current_user_id();
            }
            $args = array(
                'limit' => -1,
                'customer_id'  => $customer_id,
                'meta_key'     => 'PO Number', // The postmeta key field
                'meta_value'   => $_REQUEST['po_number'],
                'meta_compare' => '=', // The comparison argument
                'return' => 'ids',
            );

            $orders = wc_get_orders($args);

            if ($orders) {
                echo 'TRUE';
            } else {
                echo 'FALSE';
            }

            // If you're debugging, it might be useful to see what was sent in the $_REQUEST
            // print_r($_REQUEST);

        }

        // Always die in functions echoing ajax content
        die();
    }

    static function validate_po_ajax_enqueue() {

        if (!is_user_logged_in()) {
            return false;
        }

        // Enqueue javascript on the frontend.
        wp_enqueue_script(
            'validate-po-ajax-script',
            get_template_directory_uri() . '/app/assets/js/validatePo.js',
            array('jquery')
        );

        // The wp_localize_script allows us to output the ajax_url path for our script to use.
        wp_localize_script(
            'validate-po-ajax-script',
            'ajax_obj',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'po_ajax_nonce' => wp_create_nonce('po-nonce')
            )
        );
    }

    public function generate_shopper_invite($email_address, $group_id, $user_exists) {


        $email_address = \APH\Encrypter::encryptString($email_address);

        $group_id = \APH\Encrypter::encryptString($group_id);

        // Get all the email class instances
        $emails = wc()->mailer()->emails;

        // Access the default subject for new order email notifications
        $invite = new $emails['Teacher_Invite_Email'];

        // Trigger the invite email
        $invite->trigger(null, $group_id, $email_address, $user_exists);
    }

    static function invite_shopper() {

        //check_ajax_referer( 'invite-shopper-nonce', 'security' );

        $current_user = wp_get_current_user();

        if (isset($_REQUEST) && $_REQUEST['method'] == 'check_shopper') {

            // The $_REQUEST contains all the data sent via ajax
            if (isset($_REQUEST) && isset($current_user->roles) && is_array($current_user->roles) && in_array('eot', $current_user->roles)) {
                $email_address = ($_REQUEST['email_address']) ? $_REQUEST['email_address'] : '';
                $group_id = ($_REQUEST['group_id']) ? $_REQUEST['group_id'] : '';
                if (get_user_by('email', $email_address)) {
                    echo 'TRUE';
                } else {
                    echo 'FALSE';
                }
            }
        }

        if (isset($_REQUEST) && $_REQUEST['method'] == 'invite_shopper') {

            // The $_REQUEST contains all the data sent via ajax
            if (isset($_REQUEST) && isset($current_user->roles) && is_array($current_user->roles) && in_array('eot', $current_user->roles)) {
                $email_address = ($_REQUEST['email_address']) ? $_REQUEST['email_address'] : '';
                $group_id = ($_REQUEST['group_id']) ? $_REQUEST['group_id'] : '';
                if (get_user_by('email', $email_address)) {
                    $user_exists = true;
                } else {
                    $user_exists = false;
                }
                // FIX: Instantiate the class before calling a non-static method
                $ajax_instance = new self(); // Create an instance of the current class (APH\Ajax)
                $ajax_instance->generate_shopper_invite($email_address, $group_id, $user_exists); // Call the non-static method on the instance
            }
        }

        // Always die in functions echoing ajax content
        die();
    }

    static function invite_shopper_ajax_enqueue() {

        if (!is_user_logged_in()) {
            return false;
        }

        // Enqueue javascript on the frontend.
        wp_enqueue_script(
            'invite-shopper-ajax-script',
            get_template_directory_uri() . '/app/assets/js/inviteShopper.js',
            array('jquery')
        );

        // The wp_localize_script allows us to output the ajax_url path for our script to use.
        wp_localize_script(
            'invite-shopper-ajax-script',
            'invite_shopper_ajax_obj',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'invite_shopper_ajax_nonce' => wp_create_nonce('invite-shopper-nonce')
            )
        );
    }

    static function append_customer_data_ajax_search($found_customers) {
        $found_customers_appended = [];

        foreach ($found_customers as $customer_id => $text) {
            // Ensure customer data is valid before proceeding
            $customer_data = get_userdata($customer_id);
            $customer_user_obj = get_user_by('id', $customer_id);

            // Check if user data was successfully retrieved for both objects
            if ($customer_data && $customer_user_obj) {
                $fq_options = [];
                $fq_options['-1'] = 'Not Set';
                $customer_roles = $customer_data->roles; // Line 184: This should now be safe

                // Get syspro data
                if (get_user_meta($customer_id, 'sysproCustomer', true)) {
                    $text .= ' SYS #' . get_user_meta($customer_id, 'sysproCustomer', true);
                } else {
                    $text .= ' SYS #--';
                }

                // Get fq accounts
                // Line 193: This should now be safe as $customer_user_obj is checked
                foreach (wp_get_terms_for_user($customer_user_obj, 'user-group') as $group) {
                    $fq_options[$group->term_id] = __($group->name, 'textdomain');
                    //$group->term_id;
                    //$group->name;
                }

                // Build new array
                $found_customers_appended[$customer_id] = [
                    'text' => $text,
                    'roles' => $customer_roles,
                    'fq_options' => $fq_options
                ];
            } else {
                // Optionally, handle cases where user data is not found.
                // For example, you might log an error, skip this customer,
                // or add a placeholder entry to $found_customers_appended.
                // For now, we'll just skip the invalid customer.
                error_log("APH/Ajax.php: User data not found for customer ID: " . $customer_id);
            }
        }
        return $found_customers_appended;
    }

    static function check_order_status() {

        check_ajax_referer('order-status-nonce', 'security');

        // The $_REQUEST contains all the data sent via ajax
        if (isset($_REQUEST)) {
            if (isset($_REQUEST['email_address']) && isset($_REQUEST['order_number'])) {
                // If you're debugging, it might be useful to see what was sent in the $_REQUEST
                $email_address = strtolower(strip_tags($_REQUEST['email_address']));
                $order_number = strip_tags($_REQUEST['order_number']);
                // // Query order by id
                $order = wc_get_order($order_number);
                if (!$order) {
                    echo 'NO_ORDER';
                    die();
                }
                $order_billing_email_address = strtolower($order->get_billing_email());
                $customer_data = $order->get_user();
                if ($customer_data) {
                    $order_customer_email_address = strtolower($customer_data->user_email);
                } else {
                    $order_customer_email_address = '';
                }
                if ($email_address != $order_billing_email_address && $email_address != $order_customer_email_address) {
                    echo 'NO_MATCH';
                    die();
                } else {
                    $order_items = $order->get_items();
                    $product_details = [];
                    foreach ($order_items as $product) {
                        $product_details[] = $product['name'] . " x " . $product['qty'];
                    }
                    echo json_encode(array(
                        'order_number'   => $order_number,
                        'order_status'   => $order->get_status(),
                        'order_total'    => $order->get_formatted_order_total(),
                        'order_po'       => get_post_meta($order_number, 'PO Number', true),
                        'order_products' => $product_details,
                    ));
                }
            }
        }

        // Always die in functions echoing ajax content
        die();
    }

    static function check_order_status_ajax_enqueue() {

        if (!is_user_logged_in()) {
            return false;
        }

        // Enqueue javascript on the frontend.
        wp_enqueue_script(
            'order-status-ajax-script',
            get_template_directory_uri() . '/app/assets/js/orderStatus.js',
            array('jquery')
        );

        // The wp_localize_script allows us to output the ajax_url path for our script to use.
        wp_localize_script(
            'order-status-ajax-script',
            'order_status_obj',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'order_status_ajax_nonce' => wp_create_nonce('order-status-nonce')
            )
        );
    }

    // FQ Balance Lookup API
    static function check_fq_balance() {

        check_ajax_referer('fq-nonce', 'security');

        // The $_REQUEST contains all the data sent via ajax
        if (isset($_REQUEST)) {
            $fq_accounts = [];
            foreach (wp_get_terms_for_user(get_current_user_id(), 'user-group') as $group) {
                // Trimming the first three chars from group name "FQ ", formatting for api
                $fq_accounts[] = str_pad(substr($group->name, 3), 15, '0', STR_PAD_LEFT);
            }
            if (count($fq_accounts) > 0) {
                if (show_env_banner()) {
                    $curl_url = FQ_URL_STG;
                    $secret_key = FQ_KEY_STG;
                } else {
                    $curl_url = FQ_URL_PRD;
                    $secret_key = FQ_KEY_PRD;
                }
                $fq_request_body = [
                    'ids' => $fq_accounts,
                    'secretKeyWC' => $secret_key
                ];
                $ch = curl_init($curl_url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fq_request_body));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json'
                ));
                # Return response instead of printing.
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                # Send request.
                $result = curl_exec($ch);
                curl_close($ch);
                echo $result;
            } else {
                echo 'FALSE';
            }
        }

        // Always die in functions echoing ajax content
        die();
    }

    static function check_fq_balance_ajax_enqueue() {

        if (!is_user_logged_in()) {
            return false;
        }

        // Enqueue javascript on the frontend.
        wp_enqueue_script(
            'fq-balance-ajax-script',
            get_template_directory_uri() . '/app/assets/js/checkFqBalance.js',
            array('jquery')
        );

        // The wp_localize_script allows us to output the ajax_url path for our script to use.
        wp_localize_script(
            'fq-balance-ajax-script',
            'fq_obj',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'fq_ajax_nonce' => wp_create_nonce('fq-nonce')
            )
        );
    }

    // Net Balance Lookup API
    static function check_net_balance() {

        check_ajax_referer('net-nonce', 'security');

        // The $_REQUEST contains all the data sent via ajax
        if (isset($_REQUEST)) {
            $net_accounts = [];
            foreach (wp_get_terms_for_user(get_current_user_id(), 'user-group') as $group) {
                // Trimming the first three chars from group name "net ", formatting for api
                $net_accounts[] = str_pad(substr($group->name, 3), 15, '0', STR_PAD_LEFT);
            }
            if (count($net_accounts) > 0) {
                if (show_env_banner()) {
                    $curl_url = NT_URL_STG;
                    $curl_usr = NT_USR_STG;
                    $curl_psw = NT_PSW_STG;
                } else {
                    $curl_url = NT_URL_PRD;
                    $curl_usr = NT_USR_PRD;
                    $curl_psw = NT_PSW_PRD;
                }
                $net_request_body = [
                    'UserId' => $net_accounts,
                ];

                $ch = curl_init($curl_url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($net_request_body));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "$curl_usr:$curl_psw");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Accept: application/json'
                ));
                # Return response instead of printing.
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                # Send request.
                $result = curl_exec($ch);
                curl_close($ch);
                echo $result;
            } else {
                echo 'FALSE';
            }
        }

        // Always die in functions echoing ajax content
        die();
    }

    static function check_net_balance_ajax_enqueue() {

        if (!is_user_logged_in()) {
            return false;
        }

        // Enqueue javascript on the frontend.
        wp_enqueue_script(
            'net-balance-ajax-script',
            get_template_directory_uri() . '/app/assets/js/checkNetBalance.js',
            array('jquery')
        );

        // The wp_localize_script allows us to output the ajax_url path for our script to use.
        wp_localize_script(
            'net-balance-ajax-script',
            'net_obj',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'net_ajax_nonce' => wp_create_nonce('net-nonce')
            )
        );
    }
}
