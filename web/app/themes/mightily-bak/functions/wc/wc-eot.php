<?php

// =========================================================================
// THIS FILE EXTENDS WOOCOMMERCE TO SUPPORT EOT, EOT ASS, AND TEACHERS
// =========================================================================

// =========================================================================
// Style properties for EOTs and OOAs on Edit Order admin screen
// =========================================================================
function eot_styles() {
    if (is_user_role('eot') || is_user_role('eot-assistant')) {
        ?>
<style>
/* Hide Delete Note from edit order screen */
.delete_note {
    display: none;
}
/* Hide Add Shipping button from edit order screen */
button.add-order-shipping {
    display: none;
}
/* Hide fields to edit line item meta data */
#order_line_items td.name div.edit {
    display: none !important;
}
/* Hide private notes field in right column */
#spnotes {
    display: none;
}
</style>
<?php
    }
}

// =========================================================================
// REMOVING REFUND CAPABILITIES FOR EOT AND EOT ASSISTANTS
// =========================================================================
function hide_wc_refund_button() {
    echo '<script>';
    echo 'jQuery(function () {';
    echo 'jQuery(".refund-items").hide();';
    echo 'jQuery(document).ajaxComplete(function() {';
    echo 'jQuery(".refund-items").css("display","none");})';
    echo '});';
    echo 'jQuery(".order_actions option[value=send_email_customer_refunded_order]").remove();';
    echo 'if (jQuery("#original_post_status").val()=="wc-refunded") {';
    echo 'jQuery("#s2id_order_status").html("Refunded");';
    echo '} else {';
    echo 'jQuery("#order_status option[value=wc-refunded]").remove();';
    echo '}';
    echo '</script>';
}

// =========================================================================
// REMOVING ALL PAYMENT OPTIONS FOR EOT AND EOT ASS EXCEPT ACCOUNTFUNDS
// =========================================================================
function hide_wc_payment_methods() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        // Check if user role is TVI
        if (in_array('eot', $roles) || in_array('eot-assistant', $roles)) : ?>
<script>
jQuery(function() {
    jQuery('#_payment_method option').each(function() {
        if (jQuery(this).attr('value') != 'accountfunds') {
            jQuery(this).remove();
        }
    });
});
</script>
<?php endif;
    }
}

// =========================================================================
// REMOVING ORDER STATUS OF COMPLETED FOR EOT AND EOT ASS
// =========================================================================
function hide_wc_order_statuses() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        // Check if user role is TVI
        if (in_array('eot', $roles) || in_array('eot-assistant', $roles)) : ?>
<script>
jQuery(function() {
    jQuery('#order_status option').each(function() {
        if (jQuery(this).attr('value') == 'wc-completed') {
            jQuery(this).remove();
        }
    });
});
</script>
<?php endif;
    }
}

// =========================================================================
// REMOVING ABILITY FOR EOT OR EOT ASS TO EDIT LINE ITEMS ON AN ORDER
// =========================================================================
function hide_wc_edit_line_items() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        // Check if user role is TVI
        if (in_array('eot', $roles) || in_array('eot-assistant', $roles)) : ?>
<script>
jQuery(document).ready(function($){
    $('.wc-order-edit-line-item-actions').remove();
    $('.wc-order-bulk-actions .add-items').after(
        '<p style="color:red;text-align:left;"><strong>*If you change the TVI order request (quantity/product addition/product removal), you will need to cancel the original request and retype the updated order for accurate shipping class changes. Thank you for your patience during this transition and know that we are working on adding this information to the site for future orders!</strong></p>'
    );
    // $('.wc-order-bulk-actions .add-items').remove();
});
</script>
<?php endif;
    }
}

// =========================================================================
// ADDING GLOBAL STYLESHEET URI
// =========================================================================
function add_global_stylesheet_uri() {
    if (is_user_logged_in()) : ?>
<script>
var stylesheet_directory_uri = "<?php echo get_stylesheet_directory_uri(); ?>";
</script>
<?php endif;
}

// =========================================================================
// DISABLING ORDER STATUS OPTIONS FOR EOT AND EOT ASSISTANTS
// =========================================================================

function remove_processing_status($statuses) {
    if (is_user_logged_in()) {
        // unset( $statuses['wc-pending'] );
        unset($statuses['wc-refunded']);
        //unset($statuses['wc-failed']);
        unset($statuses['wc-on-hold']);
    }

    return $statuses;
}

// =========================================================================
// CHANGING PLACE ORDER TEXT FOR TVI ACCOUNTS
// =========================================================================
function custom_order_button_text() {
    $button_text = 'Place Order';

    if (is_user_logged_in()) {
        $user = wp_get_current_user();

        $roles = $user->roles;

        // Check if user role is TVI
        if (in_array('teacher', $roles) || in_array('fqr', $roles)) {
            $button_text = 'Submit Request';
        }
    }

    return __($button_text, 'woocommerce');
}

// =========================================================================
// HIDE ACCOUNT FUNDS IF NOT AN EOT OR EOT ASSISTANT
// =========================================================================
function hide_account_funds_payment_gateway($available_gateways) {
    if (isset($available_gateways['accountfunds'])) {

        // Store the account funds property
        $account_funds = $available_gateways['accountfunds'];

        // Remove account funds
        unset($available_gateways['accountfunds']);

        // Add account funds back if logged in and EOT or EOT Assistant
        if (is_user_logged_in()) {
            $user = wp_get_current_user();

            $roles = $user->roles;

            // Check if user roles contains EOT. If it doesn't then unset the accountfunds property.
            if (in_array('eot', $roles) || in_array('eot-assistant', $roles)) {
                $available_gateways['accountfunds'] = $account_funds;
            }
        }
    }

    return $available_gateways;
}

// =========================================================================
// HIDE EOT PAYMENT OPTION IF NOT A TEACHER OR FEDERAL QUOTA REQUESTOR
// =========================================================================
function hide_eot_payment_gateway($available_gateways) {
    if (isset($available_gateways['eot_gateway'])) {

        // Store the eot gateway property
        $eot = $available_gateways['eot_gateway'];

        // Remove eot gateway
        unset($available_gateways['eot_gateway']);

        // Add eot gateway back if logged in and teacher or fqr
        if (is_user_logged_in()) {
            $user = wp_get_current_user();

            $roles = $user->roles;

            // Check if user roles contains EOT. If it doesn't then unset the accountfunds property.
            if (in_array('teacher', $roles) || in_array('fqr', $roles)) {
                $available_gateways['eot_gateway'] = $eot;
            }
        }
    }

    return $available_gateways;
}


// =========================================================================
// HIDE CREDIT CARDS PAYMENT IF NOT A GUEST OR STANDARD CUSTOMER
// =========================================================================
function hide_credit_card_payment_gateway($available_gateways) {
    // Hide authorize gateway if eot, ooa, teacher
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        // Check if user roles contains the roles below and unset the credit card gateway if they match
        if (in_array('eot', $roles) || in_array('eot-assistant', $roles) || in_array('teacher', $roles)) {
            // Remove credit card
            if(isset($available_gateways['authorize_net_aim'])){
                unset($available_gateways['authorize_net_aim']);   
            }
            if(isset($available_gateways['card_connect'])){
                unset($available_gateways['card_connect']);   
            }            
        }
    }  
    return $available_gateways;
}


// =========================================================================
// ALLOW EOT AND EOT ASS TO SEE OTHER ORDERS - TO DO: ALLOW ORDERS ONLY IN GROUP
// =========================================================================
function add_view_order_capability() {
    $eot_role = get_role('eot');
    $eot_role->add_cap('view_order', true);

    $eot_ass_role = get_role('eot-assistant');
    $eot_ass_role->add_cap('view_order', true);
}


// =========================================================================
// ORDER UPDATE CUSTOM ENPOINT
// =========================================================================
// Add some query vars we can use in our functions
function add_query_vars_filter($vars) {
    $vars[] = 'wc_order_method';

    $vars[] = 'wc_order';

    return $vars;
}


function order_update($current_user) {

    // Get the post var todo - use native wp get vars
    $order_id = null;

    $method = null;

    if (isset($_GET['wc_order_id'])) {
        $order_id = $_GET['wc_order_id'];
    }

    if (isset($_GET['wc_order_method'])) {
        $method = $_GET['wc_order_method'];
    }

    // Do the approve order method
    if ($method == 'approve') {

        // Get the order object
        $order_id = base64_decode($order_id);
        $order = wc_get_order($order_id);

        // Set the order status to processed
        $order->update_status('processing', 'Approved by EOT. ');

        // Deduct from this users account
        WC_Account_Funds::remove_funds($current_user->ID, $order->get_total());

        // Redirect back to profile
        wp_redirect('/profile');

        exit;
    }

    // Do the approve order method
    if ($method == 'deny') {

        // Get the order object
        $order_id = base64_decode($order_id);
        $order = wc_get_order($order_id);

        // Set the order status to cancelled
        $order->update_status('cancelled', 'Denied by EOT. ');

        // Redirect back to profile
        wp_redirect('/profile');

        exit;
    }
}

function order_update_init() {
    if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/order-update') {
        $current_user = wp_get_current_user();

        // is there a user to check
        if (isset($current_user->roles) && is_array($current_user->roles)) {

            // is this user an eot or assistant
            if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles)) {

                //do some logic to update the order status
                order_update($current_user);
            } else {

                // Not an eot or eot assistant
                wp_redirect(home_url());

                exit;
            }
        } else {

            // Not logged in
            wp_redirect(home_url());

            exit;
        }
    }
}

// =========================================================================
// WEEKLY ORDER REVIEW EMAIL - PULL IN DATA AND TRIGGER EMAIL
// =========================================================================
function generate_weekly_order_review_email() {
    // Only run if we are on the production environment.
    if(show_env_banner()){
        return false;
    }
    $user_id = '';
    $email_address = '';
    // Get the EOT/OOA objects/ids (right now just using hardcoded ids for testing)
    $args = array(
        'role__in'  => array('eot', 'eot-assistant'),
        'meta_key' => 'account_weekly_synopsis',
        'meta_value' => '1',
        'meta_compare' => '=' // exact match only        
    );
    $relevant_users = get_users($args);
    // Get all the email class instances
    $emails = wc()->mailer()->emails;
    // Access the default subject for new order email notifications
    $invite = new $emails['Weekly_Order_Review_Email'];
    // loop through users and get ID and Email
    foreach($relevant_users as $single_user) {
        $user = $single_user;
        $email_address = $single_user->user_email;
        // Trigger the invite email
        $invite->trigger($user, $email_address);
    }
}

// =========================================================================
// WEEKLY ORDER REVIEW EMAIL TEST - PULL IN DATA AND TRIGGER EMAIL
// =========================================================================
function generate_weekly_order_review_email_test() {
    // Only run if we are on the production environment.
    if(!(isset($_GET['send_test']) && $_GET['send_test'] == 'yes')){
        return false;
    }
    $user_id = '';
    $email_address = '';
    // Get the EOT/OOA objects/ids (right now just using hardcoded ids for testing)
    $args = array(
        'include'  => array(38),       
    );
    $relevant_users = get_users($args);
    // Get all the email class instances
    $emails = wc()->mailer()->emails;
    // Access the default subject for new order email notifications
    $invite = new $emails['Weekly_Order_Review_Email'];
    // loop through users and get ID and Email
    foreach($relevant_users as $single_user) {
        $user = $single_user;
        $email_address = $single_user->user_email;
        // Trigger the invite email
        $invite->trigger($user, $email_address);
    }
}

// =========================================================================
// OPT OUT CUSTOM ENDPOINT
// =========================================================================
function opt_out_user($current_user) {
    if (isset($_GET['opt_out_email_address'])) {
        $opt_out_email_address = $_GET['opt_out_email_address'];

        // check that the email address posted is the same as the email address of the current user
        if ($current_user->user_email == $opt_out_email_address) {
            $current_user->remove_role('eot');

            $current_user->remove_role('eot-assistant');

            $current_user->add_role('customer');
        }
    }

    // Not an eot or eot assistant
    wp_redirect(home_url() . '/profile');

    exit;
}

function opt_out_init() {
    if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/opt-out') {
        $current_user = wp_get_current_user();

        // is there a user to check
        if (isset($current_user->roles) && is_array($current_user->roles)) {

            // is this user an eot or assistant
            if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles)) {

                //process the form and opt the user out
                opt_out_user($current_user);
            } else {

                // Not an eot or eot assistant
                wp_redirect(home_url());

                exit;
            }
        } else {

            // Not logged in
            wp_redirect(home_url());

            exit;
        }
    }
}

// Saving the EOT ID as meta to check when the CSR creates a new order
function save_order_eot_id($post_id, $post) {
    //var_dump($_POST);
    //die();
    if (isset($_POST['customer_user'])) {
        // $eot_id = get_post_meta( $post_id, '_eot_id');
        $user = get_user_by('id', $_POST['customer_user']);
        if (in_array('eot', (array) $user->roles) || in_array('eot-assistant', (array) $user->roles) || in_array('net_30', (array) $user->roles)) {
            //The user has the "eot, eot-assistant" role
            update_post_meta( $post_id, '_eot_id', $_POST['customer_user'] );
            // update_post_meta( $post_id, '_fq_account', '-1' );
        } elseif(in_array('teacher', (array) $user->roles)){
            // This customer is a teacher. We need to get their eot id.
            $user_group_id = 0;
            foreach (wp_get_terms_for_user($_POST['customer_user'], 'user-group') as $group) {
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
            if($group_eot_id != 0){
                delete_post_meta( $post_id, '_eot_id');
                update_post_meta( $post_id, '_eot_id', $group_eot_id);
            } else {
                delete_post_meta( $post_id, '_eot_id');
            }
        } else {
            delete_post_meta( $post_id, '_eot_id');
            delete_post_meta( $post_id, '_fq_account');
        }
    } else {
        delete_post_meta( $post_id, '_eot_id');
        delete_post_meta( $post_id, '_fq_account');
    }
}

// Save the custom editable field value as order meta data and update order item meta data
function save_order_fq_account($post_id, $post) {
    if (isset($_POST['fq_account'])) {
		$fq_account = sanitize_text_field($_POST['fq_account']);
		$fq_account_name = get_term_by('term_taxonomy_id', $fq_account);
		$fq_account_name = $fq_account_name->name;
        // Save "fq_account" as order meta data
        update_post_meta($post_id, '_fq_account_name', $fq_account_name);
        update_post_meta($post_id, '_fq_account', sanitize_text_field($_POST['fq_account']));
    }
}

// =========================================================================
// ADDS "CUSTOM FIELDS" TO USER OBJECT WHEN NEW USER IS CREATED OR EDITED
// =========================================================================

// This adds the field during user registration or account creation in the backend
function add_custom_fields_create_user($user_id) {
    // Defaults to user_id but will be changed by Syspro via api call
    update_user_meta($user_id, 'customer_number', $user_id);

    // Adding additional fields to house FQ fund information
    update_user_meta($user_id, 'fq_outstanding_balance', 1);
    update_user_meta($user_id, 'fq_available_funding', 1);
    update_user_meta($user_id, 'fq_overspent', 1);
}

// Adds the field to existing user accounts if the field does not exist
function add_custom_fields_edit_user($user_id) {
    // Check if customer_number exists. If not, add the meta field
    $customer_number = get_user_meta($user_id, 'customer_number', true);

    $fq_outstanding_balance = get_user_meta($user_id, 'fq_outstanding_balance', true);
    $fq_available_funding = get_user_meta($user_id, 'fq_available_funding', true);
    $fq_overspent = get_user_meta($user_id, 'fq_overspent', true);

    if (!$customer_number || !$fq_outstanding_balance) {
        update_user_meta($user_id, 'customer_number', $user_id);

        // Adding additional fields to house FQ fund information
        update_user_meta($user_id, 'fq_outstanding_balance', 1);
        update_user_meta($user_id, 'fq_available_funding', 1);
        update_user_meta($user_id, 'fq_overspent', 1);
    }
}

// Displays the field on the profile screen
function show_customer_number_field($user) {
    $customer_number = get_user_meta($user->ID, 'customer_number', true);
    $syspro_customer_number = get_user_meta($user->ID, 'sysproCustomer', true);
    $fq_outstanding_balance = get_user_meta($user->ID, 'fq_outstanding_balance', true);
    $fq_available_funding = get_user_meta($user->ID, 'fq_available_funding', true);
    $fq_overspent = get_user_meta($user->ID, 'fq_overspent', true); ?>


<h3><?php esc_html_e('Customer Information', 'syspro'); ?></h3>

<table class="form-table">
    <tr>
        <th><?php esc_html_e('WC Customer #', 'syspro'); ?></th>
        <td>
            <?php echo esc_attr($customer_number); ?>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('SYSPRO Customer #', 'syspro'); ?></th>
        <td>
            <?php echo esc_attr($syspro_customer_number); ?>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('FQ Outstanding Balance', 'syspro'); ?></th>
        <td>
            <?php echo esc_attr($fq_outstanding_balance); ?>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('FQ Available Funding', 'syspro'); ?></th>
        <td>
            <?php echo esc_attr($fq_available_funding); ?>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e('FQ Overspent', 'syspro'); ?></th>
        <td>
            <?php echo esc_attr($fq_overspent); ?>
        </td>
    </tr>
</table>

<?php
}

// Both hooks are responsible for displaying the customer number but not editing it

// =========================================================================
// ADD "ORDER NOTES" TO FRONTEND
// =========================================================================
function get_private_order_notes($order_id) {
    global $wpdb;

    $table_perfixed = $wpdb->prefix . 'comments';

    $results = $wpdb->get_results("
        SELECT *
        FROM $table_perfixed
        WHERE  `comment_post_ID` = $order_id
        AND  `comment_type` LIKE  'order_note'
    ");

    foreach ($results as $note) {
        $order_note[]  = [
            'note_id'      => $note->comment_ID,
            'note_date'    => $note->comment_date,
            'note_author'  => $note->comment_author,
            'note_content' => $note->comment_content,
        ];
    }

    return $order_note;
}

function add_comments_to_order_details($order) {
    // Changing the text for User Role: Teacher
    $orderText = '';
    if (is_user_role('teacher')) {
        $orderText = 'Request';
    } else {
        $orderText = 'Order';
    } ?>

<h2 class="woocommerce-order-details__title"><?php echo $orderText; ?> Notes</h2>

<?php
    $order_id = $order->get_id();

    $order_notes = get_private_order_notes($order_id);

    foreach ($order_notes as $note) {
        $note_id = $note['note_id'];

        $note_date = $note['note_date'];

        $note_author = $note['note_author'];

        $note_content = $note['note_content'];

        // Outputting each note content for the order
        echo '<p>' . $note_content . '</p>';
    }
}


// =========================================================================
// ALLOW EOT, EOTA, TEACHER TO BACKORDER FQ PRODUCTS // TODO CHECK AJAX CSR CALLS
// =========================================================================
function allow_backorder_override($product) {
    // Check product has federal quota funds attribute and is equal to 'available'

    if ($product->get_attribute('federal-quota-funds') && $product->get_attribute('federal-quota-funds') == 'Available') {

        // Check user is logged in before we continue
        $current_user = wp_get_current_user();
        // Check user there is a user to check
        if (isset($current_user->roles) && is_array($current_user->roles)) {
            // Dont override if this is an ajax call
            if(defined('DOING_AJAX') && DOING_AJAX && in_array('customer_service', $current_user->roles)){
                return false;
            }
            // Check user is eot, eota, teacher or csr
            if (in_array('eot', $current_user->roles) || in_array('eot-assistant', $current_user->roles) || in_array('teacher', $current_user->roles) || in_array('customer_service', $current_user->roles)) {
                // Allow product to be backordered
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function filter_products_backorders_allowed($backorder_allowed, $product_id, $product) {
    if (allow_backorder_override($product)) {
        $backorder_allowed = true;
    }

    return $backorder_allowed;
}


function filter_product_backorders_require_notification($notify, $product) {
    if (allow_backorder_override($product)) {
        $notify = true;
    }

    return $notify;
}


function product_is_purchasable($is_purchasable, $product) {
    if (allow_backorder_override($product)) {
        $is_purchasable = true;
    }

    return $is_purchasable;
}


function filter_product_is_in_stock($is_in_stock, $product) {
    if (allow_backorder_override($product)) {
        $is_in_stock = true;
    }

    return $is_in_stock;
}


// =========================================================================
// PREVENT EDITING BILLING INFO AT CHECKOUT IF EOT OR EOTA
// =========================================================================
function disable_billing_fields($billing_fields) {
    if (is_user_role('eot') || is_user_role('eot-assistant') || is_user_role('teacher')) {
        $custom_attributes = [
            'readonly' => 'readonly',
            // 'disabled' => 'disabled'
        ];

        foreach ($billing_fields as $key => $value) {
            $billing_fields[$key]['custom_attributes'] = $custom_attributes;
        }
    }

    return $billing_fields;
}

// =========================================================================
// UPDATE PLACEHOLDER FOR SHIPPING ADDRESS 1 - MUST DO IT THIS WAY
// =========================================================================
function remove_address_one_placeholders( $fields ) {
    // Remove labels for "address 2" shipping fields
    unset($fields['address_1']['placeholder']);
    return $fields;
}
function add_address_one_placeholders( $fields ) {
    // Add custom billing "address 2" label
    $fields['billing']['billing_address_1']['placeholder'] = __( 'House number and street name', 'woocommerce' );
    // Put back shipping "address 2" label
    $fields['shipping']['shipping_address_1']['placeholder'] = __( 'Must be a valid street address. No PO boxes allowed.', 'woocommerce' );
    return $fields;
}
// =========================================================================
// IF CHECKING OUT AS TEACHER, PRE POPULATE BILLING FROM EOT
// =========================================================================

function replace_billing_for_teacher($fields = []) {

    // If teacher, update billing fields
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


        $_POST['billing_first_name'] = get_user_meta($group_eot_id, 'billing_first_name', true);
        $_POST['billing_last_name'] = get_user_meta($group_eot_id, 'billing_last_name', true);
        $_POST['billing_company'] = get_user_meta($group_eot_id, 'billing_company', true);
        $_POST['billing_address_1'] = get_user_meta($group_eot_id, 'billing_address_1', true);
        $_POST['billing_address_2'] = get_user_meta($group_eot_id, 'billing_address_2', true);
        $_POST['billing_city'] = get_user_meta($group_eot_id, 'billing_city', true);
        $_POST['billing_state'] = get_user_meta($group_eot_id, 'billing_state', true);
        $_POST['billing_country'] = get_user_meta($group_eot_id, 'billing_country', true);
        $_POST['billing_postcode'] = get_user_meta($group_eot_id, 'billing_postcode', true);
        $_POST['billing_phone'] = get_user_meta($group_eot_id, 'billing_phone', true);
        $_POST['billing_email'] = get_user_meta($group_eot_id, 'billing_email', true);

    }

    return $fields;
}

// =========================================================================
// REMOVE TAX FOR EOTS, EOTA, TVI, AND TAX-EXEMPT ROLES
// =========================================================================
function wc_diff_rate_for_user() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        // Check if user roles contains EOT. If it doesn't then unset the accountfunds property.
        if (in_array('eot', $roles) || in_array('eot-assistant', $roles) || in_array('teacher', $roles) || in_array('tax-exempt', $roles)) {
            wc()->customer->set_is_vat_exempt(true);
        }
    }
}

function wc_get_variation_prices_hash_filter( $hash, $item, $display ) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        // Check if user roles contains EOT. If it doesn't then unset the accountfunds property.
        if (in_array('eot', $roles) || in_array('eot-assistant', $roles) || in_array('teacher', $roles) || in_array('tax-exempt', $roles)) {
            $hash['2'] = array();
        }
    }
	// return the hash
	return $hash;
}

function wc_get_price_suffix_filter( $price_display_suffix, $item ) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = $user->roles;
        // Check if user roles contains EOT. If it doesn't then unset the accountfunds property.
        if (in_array('eot', $roles) || in_array('eot-assistant', $roles) || in_array('teacher', $roles) || in_array('tax-exempt', $roles)) {
            return '';
        }
    }
	// return if unmatched
	return $price_display_suffix;
}

/**
 * Add the EOT email information to the order details. Meant to be used in
 * a hook such as
 * add_action('woocommerce_order_details_before_order_table_items', 'aph_add_eot_to_order_details');
 * add_action('woocommerce_order_details_before_order_table', 'aph_add_eot_to_order_details');
 *
 * @param WC_Order $order
 */

function aph_add_eot_to_order_details(WC_Order $order) {

    // this is the user that created the order.
    $user_id = $order->get_user_id();

    if (is_user_role('teacher', $user_id)) {

        // returns an array of user ids that are eots for accounts in the same group as the user
        $eot_ids = APH\FQ::getUsersEOTs($user_id);

        // Ensure we have exactly 1 EOT
        if (count($eot_ids) < 1 ) return; // No EOT to display
        $eot_id = array_pop($eot_ids);
        
        $eot = get_userdata($eot_id); // display_name, user_email

        echo "Questions? Please contact your EOT {$eot->display_name} at {$eot->user_email}.";
    }

}

// =========================================================================
// QUOTE REMINDER EMAIL
// =========================================================================
function generate_quote_reminder() {
    $twenty_days_ago = date('Y-m-d', strtotime('-20 days'));
    // Get orders that are 20 days old, send the reminder
    $orders = wc_get_orders([
        'limit' => -1,
        'status' => 'quote',
        'date_created' => $twenty_days_ago,
    ]);
    if(is_array($orders) && count($orders) > 0){
        // Get all the email class instances
        $emails = wc()->mailer()->emails;
        $email = new $emails['WC_Email_Customer_Invoice'];
        foreach($orders as $order){
            // Setting a bit of meta data that is used to change the heading of the invoice email
            $order->update_meta_data('reminder_email_sent', '1');
            $email->trigger($order->get_id(), $order);
        }
    }
}
function generate_quote_reminder_test() {
    // Only run if a get parameter is set
    if(!(isset($_GET['send_reminder']) && $_GET['send_reminder'] == 'yes')){
        return false;
    }
    $twenty_days_ago = date('Y-m-d', strtotime('-20 days'));
    // Get orders that are 20 days old, send the reminder
    $orders = wc_get_orders([
        'limit' => -1,
        'status' => 'quote',
        'date_created' => $twenty_days_ago,
    ]);
    if(is_array($orders) && count($orders) > 0){
        // Get all the email class instances
        $emails = wc()->mailer()->emails;
        $email = new $emails['WC_Email_Customer_Invoice'];
        foreach($orders as $order){
            // Setting a bit of meta data that is used to change the heading of the invoice email
            $order->update_meta_data('reminder_email_sent', '1');
            $email->trigger($order->get_id(), $order);
        }
    }
}