<?php
// =========================================================================
// HIDING BULK EDIT OPTIONS FROM ALL BUT ADMIN ROLES
// =========================================================================
function hide_bulk_edit() {
    if (is_user_role('customer_service') || is_user_role('eot') || is_user_role('eot-assistant')) {
        ?>
<style>
.bulkactions {
    display: none;
}
</style>
<?php
    }
}
//======================================================================
// ADDING WOOCOMMERCE SUPPORT TO THE THEME
//======================================================================
function add_woocommerce_support() {
    add_theme_support('woocommerce');
    // add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}

//======================================================================
// WOOCOMMERCE BREADRUMBS
//======================================================================
function aph_woocommerce_breadcrumbs( $defaults ) {
    // Change the breadcrumb home text from 'Home' to 'Apartment'
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="Breadcrumb Navigation">';
	return $defaults;
}

//======================================================================
// CUSTOMIZING THE CHECKOUT PAGE
//======================================================================
// WooCommerce Rename/reorder Checkout Fields
function new_order($checkout_fields) {
    $checkout_fields['billing']['billing_country']['priority'] = 95;
    // $checkout_fields['billing']['billing_phone']['priority'] = 35;
    // $checkout_fields['billing']['billing_email']['priority'] = 36;
    $checkout_fields['shipping']['shipping_country']['priority'] = 95;
    $checkout_fields['billing']['billing_company']['label'] = 'Customer/Company name';
    $checkout_fields['shipping']['shipping_company']['label'] = 'Customer/Company name';
    return $checkout_fields;
}

// Changing the text in the Order Notes for User Role: Teacher
function custom_override_checkout_fields($fields) {
    if (is_user_role('teacher')) {
        $fields['order']['order_comments']['placeholder'] = 'Notes about your request, e.g. special notes for delivery. Please do not include student personal identifiable information.';
        $fields['order']['order_comments']['label'] = 'Request notes';
        return $fields;
    } else {
        $fields['order']['order_comments']['placeholder'] = 'Notes about your order, e.g. special notes for delivery. Please do not include student personal identifiable information.';
        $fields['order']['order_comments']['label'] = 'Order notes';
        return $fields;
    }
}

// Add "Continue Shopping" button to Cart/Checkout Pages
function woo_add_continue_shopping_button_to_cart() {
    // $shop_page_url = get_page_uri( 1317 );
    // $link = get_permalink(woocommerce_get_page_id('shop'));

    echo '<div class="wc-continue-shopping">';
    echo '<a href="/shop" class="continue-shopping-button button">Continue Shopping</a>';
    echo '</div>';
}

// Checking if billing email is already registered
function after_checkout_validation( $posted ) {
  // checking to see if the user is logged in
  if (!is_user_logged_in()) {
    // if not, checking to see if the email is already registered
    if(email_exists( $posted['billing_email'] )) {
      wc_add_notice( __( "This email is already registered! Please log in, or use a different email.", 'woocommerce' ), 'error' );
    }
  }

}


//======================================================================
// ADMIN ORDER PAGE
//======================================================================
// Making fields required on new order/edit order screens
function validate_fields_admin_screen($order_id) {
    if(isset($_POST['action']) && $_POST['action'] == 'editpost'){
        if (is_user_role('eot') || is_user_role('eot-assistant') || is_user_role('customer_service') || is_user_role('administrator')) {
            // Making the Billing Email a required field
            if (empty($_POST['_billing_email'])) {
                // Add an error here
                wp_die('Billing Email required. <a href="javascript:history.back()">Go back to order.</a>');
            }
            if (stripos($_POST['_billing_email'], '@' ) === false) {
                // Add an error here
                wp_die('Valid Billing Email is required. <a href="javascript:history.back()">Go back to order.</a>');
            }

            if (!empty($_POST['_payment_method']) && $_POST['_payment_method'] == 'accountfunds') {
                // Making the Customer field required when Payment Method is set to "Account Funds"
                if(empty($_POST['customer_user'])) {
                    wp_die('To use Account Funds, a Customer must be set. <a href="javascript:history.back()">Go back to order.</a>');
                } 
                // Making the FQ Account a required field when Payment Method is set to "Account Funds"
                if(isset($_POST['fq_account']) && $_POST['fq_account'] == -1) {
                    wp_die('You must select an FQ Account. <a href="javascript:history.back()">Go back to order.</a>');
                }
            }
        }
    }
}



//======================================================================
// CART PAGE
//======================================================================
function aph_replacement_parts() {
  $products = WC()->cart->get_cart();
  $product_parts = [];
   $product_data = []; ?>
<?php foreach ( $products as $product_item_key => $product_item) : ?>
<?php if(get_field('replacement_parts', $product_item['product_id'])) :
//var_dump(get_field('replacement_parts', $product_item['product_id']));
       $product_parts_array = get_field('replacement_parts', $product_item['product_id']);
       $product_parts_published_array = [];
       foreach($product_parts_array as $product_part_single){
           if($product_part_single->post_status == 'publish'){
                $product_parts_published_array[] = $product_part_single;
           }
       }
       $product_parts[$product_item['product_id']] = $product_parts_published_array;
       $product_count = count($product_parts);
       ?>
<?php endif; ?>
<?php endforeach; ?>
<?php if ($product_parts) : ?>
<div class="replacement-parts">
    <h1 class="h3 title"><?php esc_html_e( 'Replacement Parts', 'woocommerce' ); ?></h1>
    <?php foreach ($product_parts as $key => $parts) : ?>
    <div class="product-links">
        <h2 class="h6 product-parent-title"><?php echo get_the_title($key); ?></h2>
        <?php foreach ($parts as $part) : ?>
        <a href="<?php echo get_the_permalink($part->ID); ?>"><?php echo get_the_title($part->ID); ?></a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif;
}

// Ensure cart contents update when products are added to the cart via AJAX
function wc_add_to_cart_preview_content_fragment($fragments) {
    ob_start();
    APH\Templates::cart_preview_content();
    $fragments['ul.ajax-sub-menu'] = ob_get_clean();
    return $fragments;
}

function wc_add_to_cart_preview_link_fragment($fragments) {
    ob_start();
    APH\Templates::cart_preview_link();
    $fragments['a.cart-preview-link'] = ob_get_clean();
    return $fragments;
  }

//======================================================================
// Adding sidebar support for WooCommerce
//======================================================================
function wc_sidebar() {
    get_sidebar('sidebar-widgets');
}

//======================================================================
// MY ACCOUNT PAGE
//======================================================================
// show all orders on Orders page, without pagination
add_filter( 'woocommerce_my_account_my_orders_query', 'custom_my_account_orders_query', 20, 1 );
function custom_my_account_orders_query( $args ) {
    $args['limit'] = -1;

    return $args;
}
 // Change the name of the endpoints in the Dashboard Menu that appear in My Account Page - WooCommerce 2.6
function my_account_endpoint_names($items) {
    // var_dump($items);
    if (is_user_role('teacher')) {
        $items['orders'] = __('Requests', 'woocommerce');
    }
    unset($items['view-license-keys']);
    return $items;    
}

// Change the names of the Orders column
function new_orders_columns($columns = []) {
    if (is_user_role('teacher')) {
        $columns = [
            'order-number'  => __('Request', 'woocommerce'),
            'order-date'    => __('Date', 'woocommerce'),
            'order-status'  => __('Status', 'woocommerce'),
            'order-total'   => __('Total', 'woocommerce'),
            'order-actions' => __('Actions', 'woocommerce'),
        ];
        return $columns;
    } else {
        $columns = [
            'order-number'  => __('Order', 'woocommerce'),
            'order-date'    => __('Date', 'woocommerce'),
            'order-status'  => __('Status', 'woocommerce'),
            'order-total'   => __('Total', 'woocommerce'),
            'order-actions' => __('Actions', 'woocommerce'),
        ];
        return $columns;
    }
}

// Change the name of the Orders page for teachers
function change_my_account_orders_title($title) {
    if (is_user_role('teacher')) {
        $title = __('Requests', 'woocommerce');
        return $title;
    } else {
        $title = __('Orders', 'woocommerce');
        return $title;
    }
}

// Change the name of the Downloads
function change_my_account_downloads_title($title) {
  $title = __('My Downloads', 'woocommerce');
  return $title;
}

// preventing duplicate emails/usernames
// function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
//
//     if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
//
//          $validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );
//
//   }
//
//   if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
//
//          $validation_errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'woocommerce' ) );
//
//   }
//      return $validation_errors;
// }

//======================================================================
// SHOP/ARCHIVE LOOP
//======================================================================
function wc_main_wrapper_start() {
    echo '<div class="wrapper">';
}
function wc_main_wrapper_end() {
    echo '</div>';
}
// Adding a wrapper to the WooCommerce Shop Loop templates
function wc_product_loop_wrap_start() {
  ?>
<div class="main-shop-loop">

    <div class="layout search-results">
        <div class="wrapper">
            <div class="intro style-2">
                <div class="wrapper">
                    <h1 class="h6"><?php echo (single_term_title()) ? single_term_title() : '' ; ?> Products</h1>
                </div>
                <div class="before-loop">
                    <div class="notices">
                        <?php woocommerce_output_all_notices(); ?>
                    </div>
                    <div class="ordering">
                        <?php
                  woocommerce_result_count();
                  woocommerce_catalog_ordering();
                  ?>
                    </div>
                </div>
            </div>
            <?php
}

// Changing markup for product thumbnails
function aph_get_the_product_thumbnail_url() {
  global $post;
  $image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );
  return get_the_post_thumbnail_url( $post->ID, $image_size );
}
function aph_item_thumb() {
  ?>
            <a href="<?php the_permalink(); ?>">
                <div class="item-image"
                    style="background-image: url('<?php echo aph_get_the_product_thumbnail_url(); ?>')">
                </div>
                <div class="item-number">
                    <span class="label item-name"><?php echo get_the_title(); ?></span>
                </div>
            </a>
            <?php
}

// changing the markup of the product titles in the product loop
function aph_template_loop_product_title() {
  ?>
            <div class="item-number">
                <span class="label item-name"><a
                        href="<?php the_permalink(); ?>"><?php echo get_the_title(); ?></a></span>
            </div>
            <?php
}

// Adding product stock amount
function aph_template_loop_stock() {
  wc_get_template( 'loop/item-stock.php' );
}

function wc_product_loop_wrap_end() {
    echo '</div></div></div>';
}

//======================================================================
// PRODUCT CATEGORY PAGE
//======================================================================
// Function to pull in the Category Thumbnail image
function woocommerce_category_image() {
    global $wp_query;
    $cat = $wp_query->get_queried_object();
    $thumbnail_id = get_woocommerce_term_meta($cat->term_id, 'thumbnail_id', true);
    $image = wp_get_attachment_url($thumbnail_id);
    if ($image) {
        $thumb = $image;
    } else {
        $thumb = the_post_thumbnail_url();
    }
    return $thumb;
}

//======================================================================
// LIMIT CHECKOUT FIELDS CHARACTER LENGTH
//======================================================================
function validate_checkout_field_length($fields, $errors){
    // $all_methods = WC()->shipping->get_shipping_methods();
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $method_names = [];
    $shipping = false;
    foreach($chosen_methods as $key => $value) {
        array_push($method_names, $value);
    }
    foreach($method_names as $name) {
        $ups = stripos($name, 'ups');
        if($ups !== false) {
            $shipping = true ;
            break;
        } 
    }
	$fields_to_check = array(
		'billing_first_name' => 'Billing First Name',
		'billing_last_name' => 'Billing Last Name',
		'billing_company' => 'Billing Company',
		'billing_address_1' => 'Billing Address 1',
		'billing_address_2' => 'Billing Address 2',
		'billing_city' => 'Billing City',
		'billing_state' => 'Billing State',
		'billing_postcode' => 'Billing Postcode',
		'billing_country' => 'Billing Country',
		'billing_phone' => 'Phone Number',
		'billing_email' => 'Email Address',
		'shipping_first_name' => 'Shipping First Name',
		'shipping_last_name' => 'Shipping Last Name',
		'shipping_company' => 'Shipping Company',
		'shipping_address_1' => 'Shipping Address 1',
		'shipping_address_2' => 'Shipping Address 1',
		'shipping_city' => 'Shipping City',
		'shipping_state' => 'Shipping State',
		'shipping_postcode' => 'Shipping Postcode',
		'shipping_country' => 'Shipping Country',
        'shipping_phone' => 'Shipping Phone',
		'po_number' => 'PO Number'
    );
    // if((strlen(utf8_decode($fields['billing_first_name'])) + strlen(utf8_decode($fields['billing_last_name']))) > 50){
    //     $errors->add('validation', '<strong>Your Billing Name</strong> is too long. Please use 50 characters or less for Billing First and Last Name combined.');
    // }
    // if((strlen(utf8_decode($fields['shipping_first_name'])) + strlen(utf8_decode($fields['shipping_last_name']))) > 50){
    //     $errors->add('validation', '<strong>Your Shipping Name</strong> is too long. Please use 50 characters or less for Shipping First and Last Name combined.');
    // }    
	foreach($fields_to_check as $field_name => $field_label){
        if($field_name == 'po_number'){
            $character_limit = 30;
        } elseif($field_name == 'billing_postcode' || $field_name == 'shipping_postcode'){
            $character_limit = 10;
        } elseif($field_name == 'billing_phone' || $field_name == 'shipping_phone'){
            $character_limit = 20;
        } elseif($field_name == 'billing_first_name' || $field_name == 'shipping_first_name' || $field_name == 'billing_last_name' || $field_name == 'shipping_last_name'){
            $character_limit = 20;
        } else {
            $character_limit = 40;
        }
		if(isset($fields[$field_name]) && strlen(utf8_decode($fields[$field_name])) > $character_limit){
			$errors->add('validation', '<strong>' . $field_label . '</strong> is too long. Please use ' . $character_limit . ' characters or less.');
        }
        if($field_name == 'shipping_address_1' && $shipping ){
            $has_po_box = false;
            $pobox_strings = array(
                'po box',
                'p.o. box',
                'pobox',
                'p.o.box',
                'po. box',
                'po box.',
                'po.box.',
                'post office box',
                'postoffice box'
            );
            foreach($pobox_strings as $pobox_string){
                if (stripos($fields[$field_name], $pobox_string) !== false) {
                    $has_po_box = true;
                }                
            }
            if($has_po_box){
                $errors->add('validation', 'UPS will only accept shipments to a valid street address. UPS does not deliver to P.O. Boxes.');
            }
        }
    }


}
function checkout_field_disallow_chars() {
    // Only on checkout page
    //if( ! ( is_checkout() && ! is_wc_endpoint_url() ) ) return;
    ?>
            <script>
            jQuery(document).ready(function($){
                var sanitize_postcode_fields = '#billing_postcode, #shipping_postcode';
                var sanitize_checkout_fields =
                    '#billing_first_name, #billing_last_name, #billing_company, #billing_address_1, #billing_address_2, #billing_city, #shipping_first_name, #shipping_last_name, #shipping_company, #shipping_address_1, #shipping_address_2, #shipping_city, #po_number';
                var sanitize_admin_postcode_fields = '#_billing_postcode, #_shipping_postcode';
                var sanitize_admin_checkout_fields =
                    '#_billing_first_name, #_billing_last_name, #_billing_company, #_billing_address_1, #_billing_address_2, #_billing_city, #_shipping_first_name, #_shipping_last_name, #_shipping_company, #_shipping_address_1, #_shipping_address_2, #_shipping_city, #po_number';
                var sanitize_po_fields =  '#po_number';
                $(sanitize_po_fields).bind('keyup blur', function() {
                    $(this).val($(this).val().replace(/[\\\//>/</'\]\[/}/{/+/_/)/(/^ %@;:?!&$*#~`|,.]+/,
                        ''));
                });
            });
            </script>
            <?php
}

//======================================================================
// LIMIT ADMIN ORDER FIELDS LENGTH
//======================================================================
function validate_field_length_admin_screen($order_id){
	$fields_to_check = array(
		'_billing_first_name' => 'Billing First Name',
		'_billing_last_name' => 'Billing Last Name',
		'_billing_company' => 'Billing Company',
		'_billing_address_1' => 'Billing Address 1',
		'_billing_address_2' => 'Billing Address 2',
		'_billing_city' => 'Billing City',
		'_billing_state' => 'Billing State',
		'_billing_postcode' => 'Billing Postcode',
		'_billing_country' => 'Billing Country',
		'_billing_phone' => 'Phone Number',
		'_billing_email' => 'Email Address',
		'_shipping_first_name' => 'Shipping First Name',
		'_shipping_last_name' => 'Shipping Last Name',
		'_shipping_company' => 'Shipping Company',
		'_shipping_address_1' => 'Shipping Address 1',
		'_shipping_address_2' => 'Shipping Address 1',
		'_shipping_city' => 'Shipping City',
		'_shipping_state' => 'Shipping State',
		'_shipping_postcode' => 'Shipping Postcode',
		'_shipping_country' => 'Shipping Country',
        '_shipping_phone' => 'Shipping Phone',
		'po_number' => 'PO Number'
    );
    // if((strlen(utf8_decode($_POST['_billing_first_name'])) + strlen(utf8_decode($_POST['_billing_last_name']))) > 50){
    //     wp_die('<strong>Billing Name</strong> is too long. Please use 50 characters or less for Billing First and Last Name combined. <a href="javascript:history.back()">Go back to order.</a>');
    // }
    // if((strlen(utf8_decode($_POST['_shipping_first_name'])) + strlen(utf8_decode($_POST['_shipping_last_name']))) > 50){
    //     wp_die('<strong>Shipping Name</strong> is too long. Please use 50 characters or less for Shipping First and Last Name combined. <a href="javascript:history.back()">Go back to order.</a>');
    // }    
	foreach($fields_to_check as $field_name => $field_label){
        if($field_name == 'po_number'){
            $character_limit = 30;
        } elseif($field_name == '_billing_postcode' || $field_name == '_shipping_postcode'){
            $character_limit = 10;
        } elseif($field_name == '_billing_phone' || $field_name == '_shipping_phone'){
            $character_limit = 20;            
        } elseif($field_name == '_billing_first_name' || $field_name == '_shipping_first_name' || $field_name == '_billing_last_name' || $field_name == '_shipping_last_name'){
            $character_limit = 20;
        } else {
            $character_limit = 40;
        }        
		if(isset($_POST[$field_name]) && strlen(utf8_decode($_POST[$field_name])) > $character_limit){
			wp_die($field_label . ' is too long. The maximum length for this field is '.$character_limit.' characters. <a href="javascript:history.back()">Go back to order.</a>');
		}
	}
}

//======================================================================
// WOOCOMMERCE EMAILS
//======================================================================
// ADD CUSTOM EMAILS TO WC
function custom_woocommerce_emails($email_classes) {
    include(locate_template('emails/class-teacher-invite-email.php'));
    include(locate_template('emails/class-eot-gateway-email.php'));
    include(locate_template('emails/class-teacher-request-email.php'));
    include(locate_template('emails/class-teacher-processing-email.php'));
    include(locate_template('emails/class-weekly-order-review-email.php'));
    include(locate_template('emails/class-secondary-download-email.php'));

    $email_classes['Teacher_Invite_Email'] = new Teacher_Invite_Email(); // add to the list of email classes that WooCommerce loads
    $email_classes['EOT_Gateway_Email'] = new EOT_Gateway_Email(); // add to the list of email classes that WooCommerce loads
    $email_classes['Teacher_Request_Email'] = new Teacher_Request_Email();
    $email_classes['Teacher_Processing_Email'] = new Teacher_Processing_Email();
    $email_classes['Weekly_Order_Review_Email'] = new Weekly_Order_Review_Email(); // add to the list of email classes that WooCommerce loads
    $email_classes['Secondary_Download_Email'] = new Secondary_Download_Email();

    return $email_classes;
}

function fq_number_add_to_email($order, $sent_to_admin, $plain_text, $email) {
	$account = get_post_meta( $order->get_order_number(), '_fq_account_name', true );
	if($account) {
		if ( $plain_text === false ) { 
			echo '<h2 style="text-align: center; margin-bottom: 10px;">Quota Account: ' . $account . '</h2>';
		} else {
			echo "Quota Account: $account";	
		}
	}
}

function po_number_add_to_email($order, $plain_text) {
	$account = get_post_meta( $order->get_order_number(), 'PO Number', true );
	if($account) {
		if ( $plain_text === false ) { 
			echo '<h4 style="color: #4a2a4d; text-align: center; margin-top: 0px;">PO Number: ' . $account . '</h4>';
		} else {
			echo "PO Number: $account";	
		}
	}
}

function wc_enhanced_select_override() {
    // De-register default woocommerce script
    wp_deregister_script('wc-enhanced-select');
    // Register our version of the script
    wp_register_script('wc-enhanced-select', get_stylesheet_directory_uri() . '/app/assets/js/wc-enhanced-select-override.js', array( 'jquery', 'selectWoo'), '1.0.9' );
    // We need to get the role of the customer if applicable and pass as global param
    $screen = get_current_screen();
    $fq_options = [];
    $fq_options['-1'] = 'Not Set';
    if(isset($screen->post_type) && $screen->post_type == 'shop_order' && isset($_GET['action']) && $_GET['action'] == 'edit'){
        global $post;
        if(isset($post->ID)){
            if(get_post_meta($post->ID, '_customer_user', true)){
                // Get customer role
                $user_id = get_post_meta($post->ID, '_customer_user', true);
                $user_meta = get_userdata($user_id);
                $user_roles = $user_meta->roles;
                $user_role = $user_roles[0];

                // Get FQ Accounts that this EOT has access to

                $user_obj = get_user_by('id', $user_id);
                //print_r(wp_get_terms_for_user($user_obj, 'user-group'));
                foreach(wp_get_terms_for_user($user_obj, 'user-group') as $group){
                    $fq_options[$group->term_id] = __($group->name, 'textdomain');
                    //$group->term_id;
                    //$group->name;
                }               
            }

        }
    }
    if(isset($user_role)){
        $customer_role = $user_role;
    } else {
        $customer_role = 'guest';
    }  
    // Localization array copied from woocommerce/includes/admin/class-wc-admin-assets.php line 119
    wp_localize_script(
        'wc-enhanced-select',
        'wc_enhanced_select_params',
        array(
            'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'woocommerce' ),
            'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'woocommerce' ),
            'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'woocommerce' ),
            'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'woocommerce' ),
            'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'woocommerce' ),
            'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'woocommerce' ),
            'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'woocommerce' ),
            'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'woocommerce' ),
            'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'woocommerce' ),
            'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'woocommerce' ),
            'ajax_url'                  => admin_url( 'admin-ajax.php' ),
            'search_products_nonce'     => wp_create_nonce( 'search-products' ),
            'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
            'search_categories_nonce'   => wp_create_nonce( 'search-categories' ),
            // Appended to native woocommerce array
            'current_customer_role'     => $customer_role,
            'fq_options'                => $fq_options
        )
    );    
}

function wc_meta_boxes_override() {
    $screen       = get_current_screen();
    $screen_id    = $screen ? $screen->id : '';
    $wc_screen_id = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
    $suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';    
    if ( in_array( str_replace( 'edit-', '', $screen_id ), wc_get_order_types( 'order-meta-boxes' ) ) ) {
        $default_location = wc_get_customer_default_location();
        wp_deregister_script('wc-admin-order-meta-boxes');
        wp_enqueue_script( 'wc-admin-order-meta-boxes', get_stylesheet_directory_uri() . '/app/assets/js/meta-boxes-order-override.js', array( 'wc-admin-meta-boxes', 'wc-backbone-modal', 'selectWoo', 'wc-clipboard' ), WC_VERSION );
        wp_localize_script(
            'wc-admin-order-meta-boxes',
            'woocommerce_admin_meta_boxes_order',
            array(
                'countries'              => wp_json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
                'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ),
                'default_country'        => isset( $default_location['country'] ) ? $default_location['country'] : '',
                'default_state'          => isset( $default_location['state'] ) ? $default_location['state'] : '',
                'placeholder_name'       => esc_attr__( 'Name (required)', 'woocommerce' ),
                'placeholder_value'      => esc_attr__( 'Value (required)', 'woocommerce' ),
            )
        );
    } 
}

//======================================================================
// VALIDATE LOUIS PRODUCTS IN ORDER AT CHECKOUT
//======================================================================
function validate_order_products($fields, $errors){
    // Restrict to US if any Louis product is present
    // Restrict to US and EOT community if Louis and sku ends in *-LF
    // var_dump(WC());
    // Loop over $cart items
    $louis_order = false;
    $louis_restricted_product = false;
    $digital_products_only = true;
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = $cart_item['data'];
        if($product->get_status() == 'louis'){
            $louis_order = true;
            $sku_string = $product->get_sku();
            $sku_restricted_ending = "LF";
            $sku_restricted_length = strlen($sku_restricted_ending);
            if ( substr_compare($sku_string, $sku_restricted_ending, -$sku_restricted_length) === 0 ) {
                $louis_restricted_product = true;
            }
        }
        if(!$product->is_downloadable()){
            $digital_products_only = false;
        }
    }
    if($louis_order && $fields['billing_country'] != 'US'){
        $errors->add('validation', 'We were not able to complete your purchase. Louis products are not available outside the U.S. Please remove Louis items from your cart.');
    }
    if($louis_restricted_product && !APH\Roles::userHas([APH\Roles::EOT, APH\Roles::OOA, APH\Roles::TVI], wp_get_current_user())){
        $errors->add('validation', 'Large print PDFs are purchasable only by Ex Officio Trustees of the Federal Quota Program.');
    }
    if(!$digital_products_only && $fields['billing_country'] == 'AU'){
        $errors->add('validation', 'Physical products are not available in your region. Please remove them from your cart and try again.');
    }
}