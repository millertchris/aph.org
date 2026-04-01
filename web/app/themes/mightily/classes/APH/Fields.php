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
class Fields {

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
    static function checkout_field_po_number($fields) {
        if (is_user_role('eot') || is_user_role('eot-assistant')) {
            // The PO number should only be required for EOT and EOT Assistants
            $fields['order']['po_number'] = [
                'type'          => 'text',
                'label'         => __('PO Number', 'woocommerce'),
                'required'      => true,
                'class'         => ['form-row-wide'],
                'clear'         => true,
                'placeholder'   => 'No special characters or spaces allowed',
                'autocomplete'  => 'off',
                'priority'      => 999
            ];
        } else {
            $fields['order']['po_number'] = [
                'type'          => 'text',
                'label'         => __('PO Number', 'woocommerce'),
                'required'      => false,
                'class'         => ['form-row-wide'],
                'clear'         => true,
                'placeholder'   => 'No special characters or spaces allowed',
                'autocomplete'  => 'off',
                'priority'      => 999
            ];
        }

        return $fields;
    }

    // Save PO Number on checkout
    static function checkout_field_po_number_update_order_meta($order_id) {
        if (!empty($_POST['po_number'])) {
            update_post_meta($order_id, 'PO Number', sanitize_text_field($_POST['po_number']));
        }
    }

    // Maybe require PO Number on admin screen
    static function validate_po_number_admin_screen($post_id, $post) {
        if (is_user_role('eot') || is_user_role('eot-assistant') || is_user_role('copy_of_csr') || is_user_role('administrator')) {
            if (isset($_POST['customer_user'])) {
                $user = get_user_by('id', $_POST['customer_user']);
                if (in_array('eot', (array) $user->roles) || in_array('eot-assistant', (array) $user->roles)) {
                    if (empty($_POST['po_number'])) {
                        // Add an error here
                        wp_die('PO Number required. <a href="javascript:history.back()">Go back to order.</a>');
                    }
                }
            }
        }
    }

    // Output PO Number field in order data on order edit screen in woocommerce
    static function checkout_field_po_number_add_to_admin_screen($order) {
        woocommerce_wp_text_input([
            'id'            => 'po_number',
            'label'         => __('PO Number:', 'woocommerce'),
            'placeholder' => 'No special characters or spaces allowed.',
            'value'         => $order->get_meta('PO Number'),
            'wrapper_class' => 'form-field-wide',
        ]);
    }

    // Save the PO Number field value as order meta data and update order item meta data
    static function save_order_po_number($post_id, $post) {
        if (isset($_POST['po_number'])) {
            // Save "po number" as order meta data
            update_post_meta($post_id, 'PO Number', sanitize_text_field($_POST['po_number']));
        }
    }

    // Add Shipping Phone to checkout field
    static function checkout_field_shipping_phone($fields) {
        $fields['shipping']['shipping_phone'] = [
            'type'        => 'text',
            'label'       => __('Shipping Phone', 'woocommerce'),
            'required'    => false,
            'class'       => ['form-row-wide'],
            'clear'       => true,
            'priority'    => 999
        ];
        return $fields;
    }

    // Save Shipping Phone on checkout
    static function checkout_field_shipping_phone_update_order_meta($order_id) {
        if (!empty($_POST['shipping_phone'])) {
            update_post_meta($order_id, 'Shipping Phone', sanitize_text_field($_POST['shipping_phone']));
        }
    }

    // Maybe require Shipping Phone on admin screen
    static function validate_shipping_phone_admin_screen($post_id, $post) {
        // if (is_user_role('eot') || is_user_role('eot-assistant') || is_user_role('copy_of_csr') || is_user_role('administrator')) {
        //     if (isset($_POST['customer_user'])) {
        //         $user = get_user_by('id', $_POST['customer_user']);
        //         if (in_array('eot', (array) $user->roles) || in_array('eot-assistant', (array) $user->roles)) {
        //             if (empty($_POST['po_number'])) {
        //                 // Add an error here
        //                 wp_die('PO Number required. <a href="javascript:history.back()">Go back to order.</a>');
        //             }
        //         }
        //     }
        // }
    }

    // Output Shipping Phone field in order data on order edit screen in woocommerce
    static function checkout_field_shipping_phone_add_to_admin_screen($order) {
        $shipping_phone = $order->get_meta('Shipping Phone');

?>
        <div class="address">
            <p<?php if (empty($shipping_phone)) echo ' class="none_set"' ?>>
                <strong>Shipping Phone:</strong>
                <?php echo (!empty($shipping_phone)) ? $shipping_phone : '-' ?>
                </p>
        </div>
        <div class="edit_address"><?php
                                    woocommerce_wp_text_input(array(
                                        'id' => 'shipping_phone',
                                        'label' => __('Shipping Phone', 'woocommerce'),
                                        'wrapper_class' => 'form-field-wide',
                                        'value' => $shipping_phone
                                    ));
                                    ?></div><?php
            }

            // Save the Shipping Phone field value as order meta data and update order item meta data
            static function save_order_shipping_phone($post_id, $post) {
                if (isset($_POST['shipping_phone'])) {
                    // Save "po number" as order meta data
                    update_post_meta($post_id, 'Shipping Phone', sanitize_text_field($_POST['shipping_phone']));
                }
            }

            // Require Shipping Phone on checkout if shipping address is not US
            static function validate_shipping_phone_checkout($fields, $errors) {
                //var_dump($fields);
            }

            // Output Shipping Email field in order data on order edit screen in woocommerce
            static function checkout_field_shipping_email_add_to_admin_screen($order) {
                $shipping_email = $order->get_meta('Shipping Email');

                ?>
        <div class="address">
            <p<?php if (empty($shipping_email)) echo ' class="none_set"' ?>>
                <strong>Shipping Email:</strong>
                <?php echo (!empty($shipping_email)) ? $shipping_email : '-' ?>
                </p>
        </div>
        <div class="edit_address"><?php
                                    woocommerce_wp_text_input(array(
                                        'id' => 'shipping_email',
                                        'label' => __('Shipping Email', 'woocommerce'),
                                        'wrapper_class' => 'form-field-wide',
                                        'value' => $shipping_email
                                    ));
                                    ?></div><?php
            }

            static function save_order_shipping_email($post_id, $post) {
                if (isset($_POST['shipping_email'])) {
                    // Save "po number" as order meta data
                    update_post_meta($post_id, 'Shipping Email', sanitize_text_field($_POST['shipping_email']));
                }
            }

            // Add additional email for digital download keys
            static function checkout_field_digital_download_email($fields) {
                // $licensed = get_post_meta($post->ID, 'lmfwc_licensed_product', true);
                if (is_user_role('eot') || is_user_role('eot-assistant')) {
                    $show_download_email_field = false;
                    foreach (WC()->cart->get_cart() as $cart_item) {
                        $product = $cart_item['data'];
                        if ($product->is_downloadable() && get_post_meta($product->get_id(), 'lmfwc_licensed_product', true) == 1) {
                            $show_download_email_field = true;
                            break;
                        }
                    }

                    if ($show_download_email_field) {
                        $fields['order']['digital_download_email'] = [
                            'type'        => 'email',
                            'label'       => __('Send software license keys to another email address', 'woocommerce'),
                            'required'    => false,
                            'class'       => ['form-row-wide'],
                            'clear'       => true,
                            'priority'    => 999
                        ];
                    }
                }
                return $fields;
            }

            // Save secondary email for digital download keys
            static function checkout_field_digital_download_email_update_order_meta($order_id) {
                if (!empty($_POST['digital_download_email'])) {
                    update_post_meta($order_id, '_digital_download_email', sanitize_text_field($_POST['digital_download_email']));
                }
            }

            // Output secondary email for digital download field in order data on order edit screen in woocommerce
            static function checkout_field_digital_download_email_add_to_admin_screen($order) {
                $digital_download_email = $order->get_meta('_digital_download_email');

                ?>
        <div class="address">
            <p<?php if (empty($digital_download_email)) echo ' class="none_set"' ?>>
                <strong>Send License Key to Email:</strong>
                <?php echo (!empty($digital_download_email)) ? $digital_download_email : '-' ?>
                </p>
        </div>
        <div class="edit_address"><?php
                                    woocommerce_wp_text_input(array(
                                        'id' => 'digital_download_email',
                                        'label' => __('Send License Key to Email', 'woocommerce'),
                                        'wrapper_class' => 'form-field-wide',
                                        'value' => $digital_download_email
                                    ));
                                    ?></div><?php
            }

            // Save the secondary email digital download keys field value as order meta data and update order meta data
            static function save_order_digital_download_email($post_id, $post) {
                if (isset($_POST['digital_download_email'])) {
                    // Save "po number" as order meta data
                    update_post_meta($post_id, '_digital_download_email', sanitize_text_field($_POST['digital_download_email']));
                }
            }

            static function meta_box_field_order_status() {
                global $post;
                // Get the WC_Order Object
                $order = wc_get_order($post->ID);
                echo '<select id="pseduo-order-status-select" style="margin-top: 2px; width: 100%;"></select>';
                if ($order->get_status() == 'quote') {
                    $now = time(); // or your date as well
                    $created_date = strtotime($order->order_date);
                    $datediff = $now - $created_date;
                    $rounded_datediff = round($datediff / (60 * 60 * 24));
                    if ($rounded_datediff == 1) {
                        $day_string = 'Day';
                    } else {
                        $day_string = 'Days';
                    }
                    echo '<p class="woocommerce-order-quote-age" style="font-weight: bold;">Quote Age: ' . $rounded_datediff . ' ' . $day_string;
                }
            }

            static function meta_box_add_field_order_status() {
                add_meta_box('pseudo-order-status', __('Order Status', 'woocommerce'), array(new Fields(), 'meta_box_field_order_status'), 'shop_order', 'side', 'core');
            }

            static function remove_wc_countries($countries) {
                //unset($countries["PR"]);
                if (isset($countries['PR'])) {
                    unset($countries['PR']);
                }
                if (isset($countries['VI'])) {
                    unset($countries['VI']);
                }
                if (isset($countries['AS'])) {
                    unset($countries['AS']);
                }
                if (isset($countries['GU'])) {
                    unset($countries['GU']);
                }
                if (isset($countries['MP'])) {
                    unset($countries['MP']);
                }
                return $countries;
            }

            static function add_wc_states($states) {
                $states['US'] = array(
                    'AL' => __('Alabama', 'woocommerce'),
                    'AK' => __('Alaska', 'woocommerce'),
                    'AS' => __('American Samoa', 'woocommerce'),
                    'AZ' => __('Arizona', 'woocommerce'),
                    'AR' => __('Arkansas', 'woocommerce'),
                    'CA' => __('California', 'woocommerce'),
                    'CO' => __('Colorado', 'woocommerce'),
                    'CT' => __('Connecticut', 'woocommerce'),
                    'DE' => __('Delaware', 'woocommerce'),
                    'DC' => __('District Of Columbia', 'woocommerce'),
                    'FL' => __('Florida', 'woocommerce'),
                    'GA' => _x('Georgia', 'US state of Georgia', 'woocommerce'),
                    'GU' => __('Guam', 'woocommerce'),
                    'HI' => __('Hawaii', 'woocommerce'),
                    'ID' => __('Idaho', 'woocommerce'),
                    'IL' => __('Illinois', 'woocommerce'),
                    'IN' => __('Indiana', 'woocommerce'),
                    'IA' => __('Iowa', 'woocommerce'),
                    'KS' => __('Kansas', 'woocommerce'),
                    'KY' => __('Kentucky', 'woocommerce'),
                    'LA' => __('Louisiana', 'woocommerce'),
                    'ME' => __('Maine', 'woocommerce'),
                    'MD' => __('Maryland', 'woocommerce'),
                    'MA' => __('Massachusetts', 'woocommerce'),
                    'MI' => __('Michigan', 'woocommerce'),
                    'MN' => __('Minnesota', 'woocommerce'),
                    'MS' => __('Mississippi', 'woocommerce'),
                    'MO' => __('Missouri', 'woocommerce'),
                    'MT' => __('Montana', 'woocommerce'),
                    'NE' => __('Nebraska', 'woocommerce'),
                    'NV' => __('Nevada', 'woocommerce'),
                    'NH' => __('New Hampshire', 'woocommerce'),
                    'NJ' => __('New Jersey', 'woocommerce'),
                    'NM' => __('New Mexico', 'woocommerce'),
                    'NY' => __('New York', 'woocommerce'),
                    'NC' => __('North Carolina', 'woocommerce'),
                    'ND' => __('North Dakota', 'woocommerce'),
                    'MP' => __('Northern Mariana Islands', 'woocommerce'),
                    'OH' => __('Ohio', 'woocommerce'),
                    'OK' => __('Oklahoma', 'woocommerce'),
                    'OR' => __('Oregon', 'woocommerce'),
                    'PA' => __('Pennsylvania', 'woocommerce'),
                    'PR' => __('Puerto Rico', 'woocommerce'),
                    'RI' => __('Rhode Island', 'woocommerce'),
                    'SC' => __('South Carolina', 'woocommerce'),
                    'SD' => __('South Dakota', 'woocommerce'),
                    'TN' => __('Tennessee', 'woocommerce'),
                    'TX' => __('Texas', 'woocommerce'),
                    'UT' => __('Utah', 'woocommerce'),
                    'VT' => __('Vermont', 'woocommerce'),
                    'VA' => __('Virginia', 'woocommerce'),
                    'VI' => __('Virgin Islands', 'woocommerce'),
                    'WA' => __('Washington', 'woocommerce'),
                    'WV' => __('West Virginia', 'woocommerce'),
                    'WI' => __('Wisconsin', 'woocommerce'),
                    'WY' => __('Wyoming', 'woocommerce')
                );
                return $states;
            }

            static function save_account_weekly_synopsis($user_id) {

                if (!current_user_can('edit_user', $user_id)) {
                    return false;
                }
                update_user_meta($user_id, 'account_weekly_synopsis', $_POST['account_weekly_synopsis']);
            }

            static function get_teacher_list($current_user) {
                $teacher_users[$current_user->ID] = 'Please Select';

                foreach (wp_get_terms_for_user($current_user, 'user-group') as $group) {

                    // Loop through the users of this group and append them to the child array
                    foreach (get_objects_in_term($group->term_id, 'user-group') as $child_user_id) {
                        $child_user_object = get_userdata($child_user_id);

                        if (in_array('teacher', $child_user_object->roles)) {
                            $teacher_users[$child_user_id] = $child_user_object->user_firstname . ' ' . $child_user_object->user_lastname . ' (' . $child_user_object->user_email . ')';
                        }
                    }
                }

                return $teacher_users;
            }

            static function get_fq_user_list($current_user) {
                $fq_users[$current_user->ID] = 'Please Select';

                foreach (wp_get_terms_for_user($current_user, 'user-group') as $group) {

                    // Loop through the users of this group and append them to the child array
                    foreach (get_objects_in_term($group->term_id, 'user-group') as $child_user_id) {
                        $child_user_object = get_userdata($child_user_id);

                        // IMPORTANT FIX: Cast $child_user_object->roles to an array
                        if ($child_user_id != $current_user->ID && (in_array('teacher', (array) $child_user_object->roles) || in_array('customer', (array) $child_user_object->roles) || in_array('eot-assistant', (array) $child_user_object->roles))) {
                            $fq_users[$child_user_id] = $child_user_object->user_firstname . ' ' . $child_user_object->user_lastname . ' (' . $child_user_object->user_email . ')';
                        }
                    }
                }

                return $fq_users;
            }

            static function checkout_field_create_order_for($fields) {
                $current_user = wp_get_current_user();

                // If user is EOT only will we show this option. Does not apply to EOT Assistant. Roles::TVI, removed on 23 may 2023.
                if (Roles::userHas([Roles::EOT, Roles::OOA], $current_user)) {
                    $fields['order']['create_order_for'] = [
                        'type'        => 'select',
                        'label'       => __('Assign this order to', 'woocommerce'),
                        'required'    => false,
                        'class'       => ['form-row-wide'],
                        'clear'       => true,
                        'priority'    => 5
                    ];

                    // Get all teachers in all groups. user_id => user_name
                    $fq_user_list = self::get_fq_user_list($current_user);
                    // Add this user account to beginning of the list of teachers so orders can be assigned to their own account.
                    $fq_user_list = array($current_user->ID => 'Please Select') + $fq_user_list;

                    $fields['order']['create_order_for']['options'] = $fq_user_list;
                }

                return $fields;
            }

            static function change_order_user_id($order, $data) {
                // Check if form has create_order_for field set. If yes then get the value and use it as the new order user id
                // var_dump($_POST['create_order_for']);
                if (!empty($_POST['create_order_for'])) {
                    $order_for_user_id = sanitize_text_field($_POST['create_order_for']);

                    $order->set_customer_id($order_for_user_id);

                    //file_put_contents('teacher-id.txt', print_r($_POST['create_order_for'], true));
                    //file_put_contents('order-object.txt', print_r($order, true));
                }
            }

            static function create_order_for_update_order_meta($order_id) {
                if (! empty($_POST['create_order_for'])) {
                    update_post_meta($order_id, 'Order Created For', sanitize_text_field($_POST['create_order_for']));
                }
            }

            // Add additional email for digital download keys
            static function checkout_field_louis_download_email($fields) {
                // $licensed = get_post_meta($post->ID, 'lmfwc_licensed_product', true);

                $show_louis_download_email_field = false;
                foreach (WC()->cart->get_cart() as $cart_item) {
                    $product = $cart_item['data'];
                    if ($product->is_downloadable() && $product->get_status() == 'louis') {
                        $show_louis_download_email_field = true;
                        break;
                    }
                }

                if ($show_louis_download_email_field) {
                    $fields['order']['louis_download_email'] = [
                        'type'        => 'email',
                        'label'       => __('Send Louis download to a second email address', 'woocommerce'),
                        'required'    => false,
                        'class'       => ['form-row-wide'],
                        'clear'       => true,
                        'priority'    => 999
                    ];
                }

                return $fields;
            }

            // Save secondary email for digital download keys
            static function checkout_field_louis_download_email_update_order_meta($order_id) {
                if (!empty($_POST['louis_download_email'])) {
                    update_post_meta($order_id, '_louis_download_email', sanitize_text_field($_POST['louis_download_email']));
                }
            }

            static function checkout_field_louis_download_email_add_to_admin_screen($order) {
                $louis_download_email = $order->get_meta('_louis_download_email');

                ?>
        <div class="address">
            <p<?php if (empty($louis_download_email)) echo ' class="none_set"' ?>>
                <strong>Send Louis Download to Email:</strong>
                <?php echo (!empty($louis_download_email)) ? $louis_download_email : '-' ?>
                </p>
        </div>
        <div class="edit_address"><?php
                                    woocommerce_wp_text_input(array(
                                        'id' => 'louis_download_email',
                                        'label' => __('Send Louis Download to Email', 'woocommerce'),
                                        'wrapper_class' => 'form-field-wide',
                                        'value' => $louis_download_email
                                    ));
                                    ?></div><?php
            }

            static function save_order_louis_download_email($post_id, $post) {
                if (isset($_POST['louis_download_email'])) {
                    // Save "po number" as order meta data
                    update_post_meta($post_id, '_louis_download_email', sanitize_text_field($_POST['louis_download_email']));
                }
            }
        }
