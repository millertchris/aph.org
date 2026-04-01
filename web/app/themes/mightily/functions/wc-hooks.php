<?php

// Changing the order of the information displayed at the top of a single wc_product_class

// * @hooked woocommerce_template_single_title - 5
// * @hooked woocommerce_template_single_rating - 10
// * @hooked woocommerce_template_single_price - 10
// * @hooked woocommerce_template_single_excerpt - 20
// * @hooked woocommerce_template_single_add_to_cart - 30
// * @hooked woocommerce_template_single_meta - 40
// * @hooked woocommerce_template_single_sharing - 50
// * @hooked WC_Structured_Data::generate_product_data() - 60

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);


remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
remove_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10);
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
remove_action('woocommerce_before_shop_loop', 'woocommerce_output_all_notices');
remove_action('woocommerce_before_shop_loop', 'wc_print_notices', 10);
remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);



// add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 5);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
add_action('woocommerce_single_product_summary', 'conditional_add_to_cart', 20);
add_action('woocommerce_single_product_summary', 'aph_product_fq_eligible', 25);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 30);
add_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 40);
add_action('woocommerce_after_single_product_summary', 'product_details', 5);
add_action('woocommerce_after_single_product_summary', 'product_videos', 6);
add_action('woocommerce_after_single_product', 'woocommerce_upsell_display', 15);
add_action('woocommerce_after_single_product', 'woocommerce_output_related_products', 20);



add_action('after_setup_theme', 'add_woocommerce_support');
add_action('woocommerce_after_cart_totals', 'woo_add_continue_shopping_button_to_cart');
add_action('woocommerce_review_order_after_submit', 'woo_add_continue_shopping_button_to_cart');
add_action('woocommerce_cart_collaterals', 'woocommerce_cart_totals');
add_action('woocommerce_cart_collaterals', 'aph_replacement_parts', 10);
add_action('woocommerce_after_cart', 'woocommerce_cross_sell_display', 20);
add_action('woocommerce_sidebar', 'wc_sidebar');
add_action('woocommerce_before_shop_loop', 'wc_product_loop_wrap_start', 10);
add_action('woocommerce_after_shop_loop', 'wc_product_loop_wrap_end');
add_action('woocommerce_before_main_content', 'wc_main_wrapper_start');
add_action('woocommerce_after_main_content', 'wc_main_wrapper_end');
add_action('woocommerce_before_shop_loop_item_title', 'aph_item_thumb', 10);
// add_action('woocommerce_shop_loop_item_title', 'aph_template_loop_product_title', 10);
add_action('woocommerce_checkout_create_order', 'update_shipment_package_data', 10, 1);
add_action('woocommerce_after_checkout_validation', 'after_checkout_validation');


add_action('admin_head', 'hide_wc_refund_button');
add_action('admin_head', 'hide_wc_payment_methods');
add_action('admin_head', 'hide_wc_order_statuses');
// APH 475
//add_action('admin_head', 'hide_wc_edit_line_items');
add_action('admin_head', 'add_global_stylesheet_uri');

add_action('woocommerce_checkout_create_order', function ($order, $data) {
    \APH\Fields::change_order_user_id($order, $data);
}, 10, 2);

add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    \APH\Fields::create_order_for_update_order_meta($order_id);
}, 10, 1);

add_action('init', 'opt_out_init');

// PO Number
add_filter('woocommerce_checkout_fields', function ($fields) {
    return APH\Fields::checkout_field_po_number($fields);
}, 10, 1);
add_action('woocommerce_checkout_create_order', function ($order, $data) {
    if (!empty($_POST['po_number'])) {
        $order->update_meta_data('PO Number', sanitize_text_field($_POST['po_number']));
    }
}, 10, 2);
// add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
//     \APH\Fields::checkout_field_po_number_update_order_meta($order_id);
// }, 10, 1);
add_action('woocommerce_admin_order_data_after_order_details', function ($order) {
    \APH\Fields::checkout_field_po_number_add_to_admin_screen($order);
}, 10, 1);
add_action('woocommerce_process_shop_order_meta', function ($post_id, $post) {
    \APH\Fields::save_order_po_number($post_id, $post);
}, 12, 2);
add_action('woocommerce_process_shop_order_meta', function ($post_id, $post) {
    \APH\Fields::validate_po_number_admin_screen($post_id, $post);
}, 12, 2);

// Shipping Phone
add_filter('woocommerce_checkout_fields', function ($fields) {
    return APH\Fields::checkout_field_shipping_phone($fields);
}, 10, 1);
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    \APH\Fields::checkout_field_shipping_phone_update_order_meta($order_id);
}, 10, 1);
add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    \APH\Fields::checkout_field_shipping_phone_add_to_admin_screen($order);
}, 10, 1);
add_action('woocommerce_process_shop_order_meta', function ($post_id, $post) {
    \APH\Fields::save_order_shipping_phone($post_id, $post);
}, 12, 2);
add_action('woocommerce_process_shop_order_meta', function ($post_id, $post) {
    \APH\Fields::validate_shipping_phone_admin_screen($post_id, $post);
}, 12, 2);
add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    \APH\Fields::checkout_field_shipping_email_add_to_admin_screen($order);
}, 10, 1);
add_action('woocommerce_process_shop_order_meta', function ($post_id, $post) {
    \APH\Fields::save_order_shipping_email($post_id, $post);
}, 12, 2);
add_action('woocommerce_admin_order_data_after_order_details', function ($order) {
    \APH\Order::syspro_order_number_add_to_admin_screen($order);
}, 10, 1);
add_action('woocommerce_admin_order_data_after_order_details', function ($order) {
    \APH\Order::syspro_customer_number_add_to_admin_screen($order);
}, 10, 1);
add_action('woocommerce_admin_order_data_after_order_details', function ($order) {
    \APH\Order::eot_id_add_to_admin_screen($order);
}, 10, 1);
add_action('woocommerce_admin_order_data_after_order_details', function ($order) {
    \APH\Order::fq_account_add_to_admin_screen($order);
}, 10, 1);
// Digital Download Email Address
add_filter('woocommerce_checkout_fields', function ($fields) {
    return APH\Fields::checkout_field_digital_download_email($fields);
}, 10, 1);
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    \APH\Fields::checkout_field_digital_download_email_update_order_meta($order_id);
}, 10, 1);
add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    \APH\Fields::checkout_field_digital_download_email_add_to_admin_screen($order);
}, 10, 1);
add_action('woocommerce_process_shop_order_meta', function ($post_id, $post) {
    \APH\Fields::save_order_digital_download_email($post_id, $post);
}, 12, 2);

add_action('woocommerce_process_shop_order_meta', 'save_order_eot_id', 12, 2);
add_action('woocommerce_process_shop_order_meta', 'save_order_fq_account', 12, 2);
// APH-445 Making certain Admin Order/Edit fields required
add_action('woocommerce_before_save_order_items', 'validate_fields_admin_screen', 20, 1);




add_action('user_register', 'add_custom_fields_create_user', 10, 1);
add_action('profile_update', 'add_custom_fields_edit_user', 10, 1);
add_action('show_user_profile', 'show_customer_number_field');
add_action('edit_user_profile', 'show_customer_number_field');

add_action('admin_enqueue_scripts', 'wc_enhanced_select_override', 20);
add_action('admin_enqueue_scripts', 'wc_meta_boxes_override', 20);

add_action('admin_head', 'csr_hide_delete_note');
add_action('admin_head', 'csr_hide_move_to_trash');
add_action('admin_head', 'csr_hide_line_items_before_creation');
add_action('admin_footer', 'csr_update_customer_payment_link');
add_action('admin_footer', 'csr_hide_private_note_option');
add_action('admin_footer', 'csr_update_pseudo_order_status');
add_action('admin_footer', 'csr_update_quote_status');
add_action('admin_footer', 'csr_accessibility_enhancements');
add_action('admin_footer', 'csr_order_validation');
add_action('admin_footer', 'csr_order_note_templates');
add_action('admin_head', 'eot_styles');
add_action('admin_bar_menu', 'csr_add_logout', 100);
// hiding the 'bulk edit' dropdown from all roles accept admin
add_action('admin_head', 'hide_bulk_edit');


add_action('woocommerce_before_customer_login_form', 'login_message');
add_action('template_redirect', 'wc_diff_rate_for_user', 1);

add_filter('woocommerce_checkout_fields', 'new_order');
add_filter('woocommerce_checkout_fields', 'custom_override_checkout_fields');

// These two filters cannot use anonymous functions or it will break the functionality
add_filter('woocommerce_add_to_cart_fragments', 'wc_add_to_cart_preview_content_fragment');
add_filter('woocommerce_add_to_cart_fragments', 'wc_add_to_cart_preview_link_fragment');

add_filter('woocommerce_account_menu_items', 'my_account_endpoint_names', 15, 1);
add_filter('woocommerce_account_orders_columns', 'new_orders_columns');
add_filter('woocommerce_endpoint_orders_title', 'change_my_account_orders_title');
add_filter('woocommerce_endpoint_downloads_title', 'change_my_account_downloads_title');
add_filter('woocommerce_product_tabs', 'woo_new_product_tab');

add_filter('wc_order_statuses', 'remove_processing_status');
add_filter('wc_order_statuses', 'csr_remove_processing_status');

add_filter('woocommerce_order_button_text', 'custom_order_button_text');
add_filter('woocommerce_available_payment_gateways', 'hide_account_funds_payment_gateway');
add_filter('woocommerce_available_payment_gateways', 'hide_eot_payment_gateway');
add_filter('woocommerce_available_payment_gateways', 'hide_credit_card_payment_gateway');

add_filter('query_vars', 'add_query_vars_filter');
add_filter('woocommerce_email_classes', 'custom_woocommerce_emails');
add_filter('woocommerce_checkout_fields', function ($fields) {
    return APH\Fields::checkout_field_create_order_for($fields);
}, 10, 1);

// add_filter('woocommerce_email_order_meta_keys', 'checkout_field_po_number_add_to_email');
// add_filter('woocommerce_order_details_after_order_table', 'add_comments_to_order_details');
add_filter('woocommerce_product_backorders_allowed', 'filter_products_backorders_allowed', 10, 3);
add_filter('woocommerce_product_backorders_require_notification', 'filter_product_backorders_require_notification', 10, 2);
add_filter('woocommerce_is_purchasable', 'product_is_purchasable', 10, 2);
add_filter('woocommerce_product_is_in_stock', 'filter_product_is_in_stock', 10, 2);
add_filter('woocommerce_billing_fields', 'disable_billing_fields');
add_filter('woocommerce_default_address_fields', 'remove_address_one_placeholders');
add_filter('woocommerce_checkout_fields', 'add_address_one_placeholders');
add_filter('woocommerce_checkout_fields', 'replace_billing_for_teacher', 10, 2);

add_filter('woocommerce_account_menu_items', 'remove_my_account_links');
add_filter('woocommerce_breadcrumb_defaults', 'aph_woocommerce_breadcrumbs');
add_filter('woocommerce_get_variation_prices_hash', 'wc_get_variation_prices_hash_filter', 1, 3);
add_filter('woocommerce_get_price_suffix', 'wc_get_price_suffix_filter', 10, 2);

add_filter('woocommerce_login_redirect', 'wc_login_redirect', 10, 2);
add_action('wp_logout', 'all_logout_redirect');

add_action('woocommerce_order_details_before_order_table', 'aph_add_eot_to_order_details', 10, 1);

// Extend the json data with price and FQA eligibility (Class is only loaded if needed)
add_filter('woocommerce_json_search_found_products', function ($products) {
    return APH\Products::jsonSearchProductsFilter($products);
});

// APH-485 Force rounding on price so that JSON rest responses don't get an unrounded price. APH-485
// Note - this works only for v1 of the API.
add_filter('woocommerce_order_amount_item_total', function ($price, $order_controller, $item, $inc_tax, $round) {
    return APH\Products::formatPriceAPH485($price /*, $order_controller, $item, $inc_tax, $round*/);
}, 10, 5);

// APH-485, api v2 & v3 - we have to munge the entire shop order to fix the price
add_filter('woocommerce_rest_prepare_shop_order_object', function ($response, $object, $request) {
    return APH\Products::mungeShopOrder($response, $object, $request);
}, 10, 3);

// APH-466: modify default address fields in woocommerce, see functions/wc/wc-account.php
// UPDATE 11/11/2019: REMOVED THIS FILTER BECAUSE THIS FIELD IS NO LONGER NEEDED
// add_filter( 'woocommerce_default_address_fields', 'filter_woocommerce_default_address_fields', 10, 1 );

// APH-419, Limit field length and remove special characters from order fields. Partially disabled to allow special characters.
add_action('wp_footer', 'checkout_field_disallow_chars');
add_action('in_admin_footer', 'checkout_field_disallow_chars');
add_action('woocommerce_before_save_order_items', 'validate_field_length_admin_screen', 10, 1);
add_action('woocommerce_after_checkout_validation', 'validate_checkout_field_length', 10, 2);
add_action('woocommerce_after_checkout_validation', 'validate_order_products', 15, 2);

// APH-561: add SKU (as Catalog No. to the Order Details Page)
add_action('woocommerce_after_checkout_validation', function ($fields, $errors) {
    \APH\Fields::validate_shipping_phone_checkout($fields, $errors);
}, 10, 2);

// APH-444: Adding FQ Account number to specific pages and emails:
add_action('woocommerce_email_before_order_table', 'fq_number_add_to_email', 10, 4); // Adding FQ Account to Order Emails.
add_action('woocommerce_email_after_order_number', 'po_number_add_to_email', 10, 2); // Adding PO Number to Order Emails.
// APH-447 - Add Syspro Order Number as meta data
add_action('rest_api_init', function () {
    \APH\Order::actionSysproOrderNumber_RestApiInit();
});

// APH-493 removes ineligible items from users cart before checkout
add_action('woocommerce_before_checkout_form', function () {
    \APH\Products::removeIneligibleItemsFromCart(true);
});

// APH-493 Also run it on the cart page for consistency, and let people know we are removing items.
add_action('woocommerce_before_cart', function () {
    \APH\Products::removeIneligibleItemsFromCart(true);
});

// APH-493 Redirect users who login at the checkout to the cart
// function filter_woocommerce_login_redirect( $redirect, $user ) { 
//     // make filter magic happen here...
//     $redirect = home_url() . '/cart/';
//     return $redirect; 
// }; 
// APH-493 Redirect users who login at the checkout to the cart
add_filter('woocommerce_login_redirect', 'filter_woocommerce_login_redirect', 10, 2);

// add the filter 
add_filter('woocommerce_login_redirect', 'filter_woocommerce_login_redirect', 10, 2);

add_filter('excerpt_length', 'aph_custom_excerpt_length', 999);

// PO Validation Hooks
add_action('wp_ajax_validate_po', function () {
    \APH\Ajax::validate_po();
});

add_action('wp_enqueue_scripts', function () {
    \APH\Ajax::validate_po_ajax_enqueue();
});

add_action('admin_enqueue_scripts', function () {
    \APH\Ajax::validate_po_ajax_enqueue();
});

// Invite Shopper Hooks
add_action('wp_ajax_invite_shopper', function () {
    \APH\Ajax::invite_shopper();
});

add_action('wp_enqueue_scripts', function () {
    \APH\Ajax::invite_shopper_ajax_enqueue();
});

// APH-425 Downloadable Products
add_filter('woocommerce_get_price_html', function ($price, $product) {
    return APH\Products::replaceDownloadablePrice($price, $product);
}, 10, 2);
// APH-425 Force payment at checkout
add_filter('woocommerce_cart_needs_payment', function ($needs_payment, $cart) {
    return APH\Order::forcePaymentifEot($needs_payment, $cart);
}, 10, 2);

// APH-537: Weekly Order Review Email for EOTs/OOAs
add_action('weekly_order_review_email_hook', 'generate_weekly_order_review_email', 10, 2);
add_action('wp_loaded', 'generate_weekly_order_review_email_test', 10, 2);

// APH-576 Manage Addresses
add_action('wp_ajax_addresses', function () {
    \APH\Addresses::addresses();
});
add_action('wp_enqueue_scripts', function () {
    \APH\Addresses::addresses_ajax_enqueue();
});
add_action('woocommerce_before_checkout_shipping_form', function () {
    \APH\Addresses::add_address_combobox();
});

// APH-689
add_filter('woocommerce_json_search_found_customers', function ($found_customers) {
    return APH\Ajax::append_customer_data_ajax_search($found_customers);
}, 10, 1);

// APH 693
add_action('wp_loaded', function () {
    \APH\Products::add_product_to_cart_by_sku();
});

// APH 705
add_filter('woocommerce_is_purchasable', function ($is_purchasable, $product) {
    return APH\Products::is_purchasable($is_purchasable, $product);
}, 10, 2);
add_filter('woocommerce_get_price_html', function ($price, $product) {
    return APH\Products::replace_preorder_price($price, $product);
}, 15, 2);

// APH 688
add_filter('woocommerce_shop_order_search_fields', function ($search_fields) {
    return APH\Order::woocommerce_shop_order_search($search_fields);
});

// APH 584
add_action('woocommerce_order_status_pending_to_processing', function ($order_id) {
    \APH\Emails::maybe_send_teacher_processing_email($order_id);
}, 10, 1);
add_action('woocommerce_new_order', function ($order_id) {
    \APH\Emails::maybe_send_new_request_emails($order_id);
}, 10, 1);

// APH 604
add_action('woocommerce_new_order', function ($order_id) {
    \APH\Order::maybe_add_created_by_meta($order_id);
}, 10, 1);
add_action('woocommerce_admin_order_data_after_order_details', function ($order) {
    \APH\Order::created_by_add_to_admin_screen($order);
}, 10, 1);

// Order Status Hooks
add_action('wp_ajax_check_order_status', function () {
    \APH\Ajax::check_order_status();
});

add_action('wp_enqueue_scripts', function () {
    \APH\Ajax::check_order_status_ajax_enqueue();
});

add_filter('woocommerce_ship_to_different_address_checked', '__return_true');

add_filter('woocommerce_order_item_needs_processing', function ($needs_procesing, $product, $order_id) {
    return \APH\Order::force_order_item_processing($needs_procesing, $product, $order_id);
}, 10, 3);

// Adding meta box admin shop_order pages
add_action('add_meta_boxes', function () {
    \APH\Fields::meta_box_add_field_order_status();
}, 1);

// Remove WC countries that will be added to US state list
add_filter('woocommerce_countries', function ($countries) {
    return \APH\Fields::remove_wc_countries($countries);
}, 10, 1);

// Add new US states that were previously listed as countries
add_filter('woocommerce_states', function ($states) {
    return \APH\Fields::add_wc_states($states);
}, 10, 1);

// Save new user meta field for weekly email 
add_action('woocommerce_created_customer', function ($user_id) {
    \APH\Fields::save_account_weekly_synopsis($user_id);
}, 10, 1);
add_action('personal_options_update', function ($user_id) {
    \APH\Fields::save_account_weekly_synopsis($user_id);
}, 10, 1);
add_action('edit_user_profile_update', function ($user_id) {
    \APH\Fields::save_account_weekly_synopsis($user_id);
}, 10, 1);
add_action('woocommerce_save_account_details', function ($user_id) {
    \APH\Fields::save_account_weekly_synopsis($user_id);
}, 10, 1);
add_action('woocommerce_before_wrapper_start', function () {
    \APH\Templates::product_categories_hero();
}, 9);
add_action('woocommerce_before_main_content', function () {
    \APH\Templates::product_categories_list();
}, 10);

// Send secondary license key email when sending primary license key email
add_action('woocommerce_order_action_lmfwc_send_license_keys', function () {
    \APH\Emails::maybe_send_secondary_license_email_manual();
});

// Send secondary license key email when order goes to processing
add_action('woocommerce_order_status_processing', function ($order_id) {
    \APH\Emails::maybe_send_secondary_license_email_automatic($order_id);
}, 15, 1);

// Send secondary license key email when order goes to processing
add_action('woocommerce_order_status_completed', function ($order_id) {
    \APH\Emails::maybe_send_secondary_license_email_automatic($order_id);
}, 15, 1);

// Add shipping line item meta if not present when order is placed
add_action('woocommerce_checkout_create_order_shipping_item', function ($item, $package_key, $package, $order) {
    \APH\Order::maybe_add_shipping_line_meta($item, $package_key, $package, $order);
}, 10, 4);

// Remove shipping calculator and price from total on cart page.
add_filter('woocommerce_cart_ready_to_calc_shipping', function ($show_shipping) {
    return \APH\Templates::disable_shipping_calc_on_cart($show_shipping);
}, 99);

add_filter('woocommerce_output_related_products_args', function ($args) {
    return \APH\Products::update_related_products_args($args);
}, 20, 1);

add_filter('woocommerce_is_purchasable', function ($value, $product) {
    return \APH\Products::set_discontinued_product($value, $product);
}, 10, 2);

add_action('woocommerce_thankyou', function ($order_id) {
    \APH\Order::csr_auto_close_window($order_id);
}, 10, 2);

add_action('init', function () {
    \APH\Order::add_quote_post_status();
});

add_filter('wc_order_statuses', function ($order_statuses) {
    return \APH\Order::add_quote_order_status($order_statuses);
}, 10, 1);

add_filter('wc_order_is_editable', function ($editable, $order) {
    return \APH\Order::quote_order_status_editable($editable, $order);
}, 10, 2);

add_action('woocommerce_review_order_before_submit', function () {
    \APH\Templates::add_captcha_message_to_guest_checkout();
}, 5);

add_action('wp_loaded', function () {
    \APH\Products::add_product_to_cart_from_louis();
});

function woocommerce_custom_price_to_cart_item($cart_object)
{
    if (!WC()->session->__isset("reload_checkout")) {
        foreach ($cart_object->cart_contents as $key => $value) {
            if (isset($value["custom_price"])) {
                //for woocommerce version lower than 3
                //$value['data']->price = $value["custom_price"];
                //for woocommerce version +3
                $value['data']->set_price($value["custom_price"]);
            }
            if (isset($value["custom_name"])) {
                //for woocommerce version lower than 3
                //$value['data']->price = $value["custom_price"];
                //for woocommerce version +3  
                $value['data']->set_name($value["custom_name"]);
            }
            if (isset($value["custom_sku"])) {
                //for woocommerce version lower than 3
                //$value['data']->price = $value["custom_price"];
                //for woocommerce version +3  
                $value['data']->set_sku($value["custom_sku"]);
            }
        }
    }
}
// add_action( 'woocommerce_before_calculate_totals', 'woocommerce_custom_price_to_cart_item', 99 );

add_filter('woocommerce_email_heading_customer_invoice', function ($heading, $order) {
    return \APH\Emails::maybe_change_email_heading($heading, $order);
}, 10, 2);

add_action('woocommerce_email_order_details', function ($order, $sent_to_admin, $plain_text, $email) {
    \APH\Emails::maybe_add_quote_content($order, $sent_to_admin, $plain_text, $email);
}, 10, 4);

//add_action('quote_reminder_email_hook', 'generate_quote_reminder', 10, 2);
//add_action('wp_loaded', 'generate_quote_reminder_test', 10, 2);

// Set tags to child product skus when product is grouped type
add_action('woocommerce_update_product', function ($product_id) {
    $product = wc_get_product($product_id);
    // Check if it is a grouped product
    if ($product->is_type('grouped') && !get_field('grouped_product_override', $product_id)) {
        // Get the child skus that belong to this grouped product
        $child_skus = [];
        foreach ($product->get_children() as $child_id) {
            $child_product = wc_get_product($child_id);
            $child_skus[] = $child_product->get_sku();
        }
        // Get the existing tags, if we dont do this we will overwrite them
        $terms = get_the_terms($product->get_id(), 'product_tag');
        $term_slugs = array();
        if (!empty($terms) && ! is_wp_error($terms)) {
            foreach ($terms as $term) {
                $term_slugs[] = $term->slug;
            }
        }
        // Set the new tags, a combination of existing tags and child skus
        wp_set_object_terms($product->get_id(), array_merge($term_slugs, $child_skus), 'product_tag');
    }
}, 10, 1);

function jc_custom_post_status()
{
    register_post_status('louis', array(
        'label'                     => _x('Louis', 'product'),
        // 'public'                    => !is_admin(),
        // 'internal'                  => true,
        'exclude_from_search'       => false,
        // 'show_in_admin_all_list'    => false,
        // 'show_in_admin_status_list' => false,
        // 'label_count'               => _n_noop('Louis <span class="count">(%s)</span>', 'Louis <span class="count">(%s)</span>')
    ));
}
add_action('init', 'jc_custom_post_status');

add_filter('facetwp_filtered_post_ids', 'exclude_louis_products_from_results', 10, 2);
function exclude_louis_products_from_results($post_ids, $class)
{
    // Check if we're dealing with product content type
    if (isset($_GET['fwp_content_types']) && $_GET['fwp_content_types'] === 'product') {

        // Get all product IDs that have the 'louis' tag
        $louis_products = get_posts(array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'slug',
                    'terms'    => 'louis',
                    'operator' => 'IN'
                )
            )
        ));

        // Remove louis products from the results
        $post_ids = array_diff($post_ids, $louis_products);

        // Re-index the array to avoid gaps
        $post_ids = array_values($post_ids);
    }

    return $post_ids;
}

function jc_append_post_status_list()
{
    global $post;
    $complete = '';
    $label = '';
    if ($post->post_type == 'product') {
        if ($post->post_status == 'louis') {
            $complete = ' selected="selected"';
            $label = '<span id="post-status-display"> Louis</span>';
        }
?>
        <script>
            jQuery(document).ready(function($) {
                $("select#post_status").append('<option value="louis" <?php echo $complete; ?>>Louis</option>');
                $("span#post-status-display").append('<?php echo $label; ?>');
            });
        </script>
<?php
    }
}
add_action('admin_footer-post.php', 'jc_append_post_status_list');

add_filter('woocommerce_is_purchasable', function ($is_purchasable, $product) {
    if (get_post_status($product->get_id()) == 'louis') {
        $is_purchasable = true;
    }
    return $is_purchasable;
}, 20, 2);

add_filter('woocommerce_is_purchasable', function ($is_purchasable, $product) {
    if (get_field('remove_add_to_cart_override', $product->get_id())) {
        $is_purchasable = false;
    }
    return $is_purchasable;
}, 999, 2);

add_action('woocommerce_login_form', function () {
    \APH\Templates::maybe_add_eua();
}, 10);

add_filter('woocommerce_get_availability_text', function ($availability, $product) {
    return \APH\Products::maybe_change_availability_text($availability, $product);
}, 10, 2);

add_action('wp_loaded', function () {
    if (isset($_GET['empty_cart']) && 'yes' === esc_html($_GET['empty_cart'])) {
        WC()->cart->empty_cart();
        $referer  = wp_get_referer() ? esc_url(remove_query_arg('empty_cart')) : wc_get_cart_url();
        wp_safe_redirect($referer);
    }
}, 20);

add_action('woocommerce_order_status_processing', function ($order_id) {
    \APH\Order::init_fs($order_id);
});
add_action('woocommerce_order_status_completed', function ($order_id) {
    \APH\Order::init_fs($order_id);
});

// FQ Balance Lookup Hooks
add_action('wp_ajax_check_fq_balance', function () {
    \APH\Ajax::check_fq_balance();
});

add_action('wp_enqueue_scripts', function () {
    \APH\Ajax::check_fq_balance_ajax_enqueue();
});

// Net Balance Lookup Hooks
add_action('wp_ajax_check_net_balance', function () {
    \APH\Ajax::check_net_balance();
});

add_action('wp_enqueue_scripts', function () {
    \APH\Ajax::check_net_balance_ajax_enqueue();
});

add_action('wp_loaded', function () {
    \APH\Roles::accept_eot_invitation();
});

// Louis Download Email Address
add_filter('woocommerce_checkout_fields', function ($fields) {
    return APH\Fields::checkout_field_louis_download_email($fields);
}, 10, 1);

add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    \APH\Fields::checkout_field_louis_download_email_update_order_meta($order_id);
}, 10, 1);

add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
    \APH\Fields::checkout_field_louis_download_email_add_to_admin_screen($order);
}, 10, 1);

add_action('woocommerce_process_shop_order_meta', function ($post_id, $post) {
    \APH\Fields::save_order_louis_download_email($post_id, $post);
}, 12, 2);

// Send secondary license key email when order goes to processing
add_action('woocommerce_order_status_processing', function ($order_id) {
    \APH\Emails::maybe_send_louis_download_email_automatic($order_id);
}, 15, 1);

// Send secondary license key email when order goes to processing
add_action('woocommerce_order_status_completed', function ($order_id) {
    \APH\Emails::maybe_send_louis_download_email_automatic($order_id);
}, 15, 1);

add_filter('woocommerce_rest_prepare_shop_order_object', function ($response, $post, $request) {
    return APH\Order::created_by_add_to_rest($response, $post, $request);
}, 10, 3);

add_action('wp_loaded', function () {
    \APH\Products::get_cart_count();
});

add_action('wp_loaded', function () {
    \APH\Products::add_to_cart_by_id();
});

add_action('wp_loaded', function () {
    \APH\Products::add_to_cart_by_catalog_number();
});
