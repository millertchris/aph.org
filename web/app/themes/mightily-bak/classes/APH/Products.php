<?php
/**
 * Created by PhpStorm.
 * User: ntemple
 * Date: 2019-08-27
 * Time: 13:32
 */

namespace APH;


class Products {

    /**
     * APH-446
     *
     * Core function: WC_AJAX::json_search_products_and_variations
     *
     * Extends json_search_products (the woocommerce_json_search_products endpoint) to add
     * price and FQA eligibility.
     *
     * Side Effects: all calls to this AJAX endpoint now return extended data.
     * The objects are gathered and iterated over twice (so not efficient)
     *
     * A more efficient solution would be to copy & modify the woocommerce
     * WC_AJAX::json_search_products_and_variations method to create an additional endpoint for
     * the extended data. That could break if woocommerce is upgraded.
     *
     * @param $products
     *
     * @return array
     */

    static function jsonSearchProductsFilter( $products ) {

        $ids             = array_keys( $products );
        $product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
        $products        = array();

        // This comment is here to allow the IDE to introspect the product_object
        /** @var  \WC_Product_Simple $product_object */
        foreach ( $product_objects as $product_object ) {

            $price    = $product_object->get_price();
            $eligible = $product_object->get_attribute( 'federal-quota-funds' );
            $status   = $product_object->get_status();

            $formatted_name = $product_object->get_formatted_name();
            $managing_stock = $product_object->managing_stock();
            $backorders_allowed = $product_object->backorders_allowed();

            if ( $managing_stock && ! empty( $_GET['display_stock'] ) ) {
                $stock_amount = $product_object->get_stock_quantity();
                /* Translators: %d stock amount */
                $formatted_name .= ' &ndash; ' . sprintf( __( 'Stock: %d', 'woocommerce' ), wc_format_stock_quantity_for_display( $stock_amount, $product_object ) );
                $formatted_name .= " Price: $" . wc_format_decimal($price, 2);
                if ( $eligible == 'Available' ) {
                    $formatted_name .= " (FQ Eligible)";
                } else {
                    $formatted_name .= " (Not FQ Eligible)";
                }
            } else {
                $stock_amount = '-';
            }
            // Only show non-private and non-discontinued products in search results
            if($status != 'private' && !get_field('discontinued', $product_object->get_id())){
                $products[ $product_object->get_id() ] = [
                    'text' => rawurldecode( $formatted_name ),
                    'stock' => $stock_amount,
                    'backorders_allowed' => $backorders_allowed
                ];
            }

        }

        // aph_write_log($products, 'AJAX: jsonSearchProductsFilter');

        return $products;
    }

    /**
     *
     * APH-485 - ensure that price is rounded before being displayed.
     *
     * Uses Hook:
     * 'woocommerce_order_amount_item_total', $total, $this, $item, $inc_tax, $round );
     *
     * SEE: class-wc-rest-orders-controller-v1.php, prepare_item_for_response
     *
     * @param $price
     *
     * @return string
     */
    static function formatPriceAPH485($price) {

        // For testing
        // $price = $price + 1.23223232323231231232132132343243243242343242;

        // Force this to 2 decimal places.
        $rounded_price  = wc_format_decimal($price, 2, false);

        if (strlen($price > 10)) {
            // Log Notification we found an issue here.
            syslog(LOG_WARNING, "APH-485: $price -> $rounded_price");
        }

        return $rounded_price;
    }

    /**
     * APH-485
     * Woocommerce returns the item price as a number, without rounding.
     * This is "as designed" with WC, however causes Syspro to blow chunks
     * when the number is too long.
     *
     * We iterate over the order line items, and round the price to something
     * else before sending out through the API.  See nick if you want to understand
     * where this is happening - for now, I'm happy just to provide a fix
     *
     * @param $results
     *
     * @return array
     */
    static function mungeShopOrder($response, $object, $request) {

//        aph_write_log($response, 'XX1 shop_order data');
//        aph_write_log($object, 'XX2 shop_order object');
//        aph_write_log($request, 'XX3 shop_order request');

        $data = &$response->data;
        $items = &$data['line_items'];

        foreach ($items as &$item) {
            if (isset($item['price'])) {
                $item['price'] = self::formatPriceAPH485($item['price']);
            }
        }
        aph_write_log($response, 'MUNGED RESPONSE');
        return $response;
    }

    static function removeIneligibleItemsFromCart($show_msg = false) {

        global $woocommerce;
        $items_removed = [];

        $user = wp_get_current_user();

        // Only run on users with FQA priviliges
        if (! Roles::isFQAUser($user)) return $items_removed;
        if (empty($woocommerce)) return $items_removed;

        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {

            $product =  wc_get_product( $cart_item['data']);

            // Is this the best way to do this?
            $eligible = $product->get_attribute('federal-quota-funds');

            if ($eligible != 'Available') {
                //remove single product
                $woocommerce->cart->remove_cart_item($cart_item_key);
                $items_removed = $cart_item;
            }
        }

        if (! empty($items_removed) && $show_msg) {
            wc_add_notice( 'Note: Ineligible items have been removed from your cart.', 'success' );
        }

        return $items_removed;

    }

    static function replaceDownloadablePrice($price, $product) {

        if ($product->is_downloadable() && ('' === $product->get_price() || 0 == $product->get_price())) {
            $price = '<span class="woocommerce-Price-amount amount">Get Software</span>';
        }  
        return $price;

    }
    
    static function add_product_to_cart_by_sku() {
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/add-to-cart-cn') {
            // URL format /add-to-cart-cn?cn=1-07421-00,5-09651-01,5-09653-00
            if(isset($_GET['cn']) && isset($_GET['cn'])){
                // Explode query string by comma
                $product_skus = explode(',', $_GET['cn']);
                if(isset($_GET['qty']) && isset($_GET['qty'])){
                    $product_qtys = explode(',', $_GET['qty']);
                } else {
                    $product_qtys = false;
                }
                foreach($product_skus as $key => $product_sku){
                    $product_id = wc_get_product_id_by_sku($product_sku);
                    if($product_qtys){
                        $product_qty = $product_qtys[$key];
                    } else {
                        $product_qty = 1;
                    }
                    WC()->cart->add_to_cart($product_id, $product_qty);
                }
                wp_redirect('/cart');
                exit;
            }
        }
    }
    
    static function allowed_preorder($product){
        $allowed_preorder = false;
        if (class_exists('WC_Pre_Orders_Product') && \WC_Pre_Orders_Product::product_can_be_pre_ordered($product)){
            return 'this is a preorder';
        }
        return $allowed_preorder;
    }

    static function is_purchasable($is_purchasable, $product){
        if (class_exists('WC_Pre_Orders_Product') && \WC_Pre_Orders_Product::product_can_be_pre_ordered($product)){
            if (is_user_logged_in() || isset($_SESSION['tmp_usr_get_cart'])) {
                $user = wp_get_current_user();
                $roles = $user->roles;
                if (isset($_SESSION['tmp_usr_get_cart']) || in_array('customer_service', $roles) || in_array('eot', $roles) || in_array('eot-assistant', $roles) || in_array('teacher', $roles) || in_array('administrator', $roles)) {
                    $is_purchasable = true;
                } else {
                    $is_purchasable = false;
                }
            } else {
                $is_purchasable = false;
            }
        }
        return $is_purchasable;
    }

    static function replace_preorder_price($price, $product) {
        if (class_exists('WC_Pre_Orders_Product') && \WC_Pre_Orders_Product::product_can_be_pre_ordered($product)){
            if (is_user_logged_in() || isset($_SESSION['tmp_usr_get_cart'])) {
                $user = wp_get_current_user();
                $roles = $user->roles;
                if (isset($_SESSION['tmp_usr_get_cart']) || in_array('customer_service', $roles) || in_array('eot', $roles) || in_array('eot-assistant', $roles) || in_array('teacher', $roles) || in_array('administrator', $roles)) {
                    //$is_purchasable = true;
                } else {
                    $price = 'Coming soon.';
                }
            } else {
                $price = 'Coming soon.';
            }
        } 
        return $price;
    }

    static function get_product_installation_instructions($product_id){
        if(get_field('installation_instructions', $product_id)){
            $installation_instructions = get_field('installation_instructions', $product_id);
            return '<a href="'.$installation_instructions['url'].'" target="_blank">How to Install</a><br />';
        }
    }
    
    static function update_related_products_args($args){
        $args['posts_per_page'] = 3;
        $args['meta_query'] = array(
            array(
                'key' => 'discontinued',
                'value' => 'true',
                'compare' => 'NOT LIKE',
            )
        );
        return $args;
    }

    static function set_discontinued_product($value, $product){
        if(get_field('discontinued', $product->get_id())){
            $value = false;
        }
        return $value;        
    }

    static function save_wc_custom_attributes($post_id, $custom_attributes) {
        $i = 0;
        // Loop through the attributes array
        foreach ($custom_attributes as $name => $value) {
            // Relate post to a custom attribute, add term if it does not exist
            wp_set_object_terms($post_id, $value, $name, true);
            // Create product attributes array
            $product_attributes[$i] = array(
                'name' => $name, // set attribute name
                'value' => $value, // set attribute value
                'is_visible' => 1,
                'is_variation' => 0,
                'is_taxonomy' => 1
            );
            $i++;
        }
        // Now update the post with its new attributes
        update_post_meta($post_id, '_product_attributes', $product_attributes);
    }

    static function add_product_to_cart_from_louis() {
        // if (isset($_GET['add-to-cart-from-louis'])) {
        if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/add-to-cart-from-louis') {
            // URL format /add-to-cart-cn?cn=1-07421-00,5-09651-01,5-09653-00
            if(isset($_GET['p'])){
                $encrypted_string = rawurldecode($_GET['p']);
                $decrypted_string = \APH\Encrypter::decryptString($encrypted_string);
                $item_data = explode('||', $decrypted_string);
                // var_dump($item_data);
                // die();
                // Explode query string by comma
                $price = strval($item_data[0]);
                $sku = $item_data[1];
                $title = $item_data[2];
                $efile = $item_data[3];
                $weight = $item_data[4];

                $post_id = wp_insert_post(array(
                    'post_author' => 1,
                    'post_title' => '(Louis) ' . $title,
                    'post_content' => '',
                    'post_status' => 'louis',
                    'post_type' => "product",
                ));

                wp_set_object_terms( $post_id, 'simple', 'product_type' );
                wp_set_object_terms( $post_id, 'louis', 'product_tag' );
                update_post_meta( $post_id, '_visibility', 'visible' );
                update_post_meta( $post_id, '_stock_status', 'instock');
                // update_post_meta( $post_id, 'total_sales', '0' );
                // update_post_meta( $post_id, '_regular_price', $price );
                // update_post_meta( $post_id, '_sale_price', '' );
                // update_post_meta( $post_id, '_purchase_note', '' );
                update_post_meta( $post_id, '_featured', 'no' );
                update_post_meta( $post_id, '_weight', $weight );
                // update_post_meta( $post_id, '_length', '' );
                // update_post_meta( $post_id, '_width', '' );
                // update_post_meta( $post_id, '_height', '' );
                update_post_meta( $post_id, '_sku', $sku );
                // update_post_meta( $post_id, '_sale_price_dates_from', '' );
                // update_post_meta( $post_id, '_sale_price_dates_to', '' );
                update_post_meta( $post_id, '_price', $price );
                update_post_meta( $post_id, '_regular_price', $price );
                //update_post_meta( $post_id, '_sold_individually', '' );
                update_post_meta( $post_id, '_manage_stock', 'no' );
                //update_post_meta( $post_id, '_backorders', 'no' );
                //update_post_meta( $post_id, '_stock', '' ); 
                if($efile == 1){
                    update_post_meta( $post_id, '_downloadable', 'yes' );
                    update_post_meta( $post_id, '_virtual', 'yes' );
                    update_post_meta( $post_id, '_download_expiry', '365');
                    // Build the encoded downloadable file meta data
                    // NEW FILE: Setting the name, getting the url and and Md5 hash number
                    $file_data = [];
                    $file_name = 'Download E-File';
                    $file_url  = 'https://aph.nyc3.digitaloceanspaces.com/resources/EFiles/restricted/'.$sku.'.zip';
                    // if($price === '0'){
                    //     $file_url  = 'https://aph.nyc3.digitaloceanspaces.com/resources/EFiles/free/'.$sku.'.zip';
                    // } else {
                    //     $file_url  = 'https://aph.nyc3.digitaloceanspaces.com/resources/EFiles/restricted/'.$sku.'.zip';
                    // }
                    
                    $md5_num = md5($file_url);

                    // Inserting new file in the exiting array of downloadable files
                    $file_data[0][$md5_num] = array(
                        'name'   =>  $file_name,
                        'file'   =>  $file_url
                    );
                    // Updating database with the new array
                    update_post_meta( $post_id, '_downloadable_files', $file_data[0]);
                } else {
                    update_post_meta( $post_id, '_downloadable', 'no' );
                    update_post_meta( $post_id, '_virtual', 'no' );
                }

                // Use custom method for setting the product attributes
                $custom_attributes = array('pa_federal-quota-funds' => 'available');
                \APH\products::save_wc_custom_attributes($post_id, $custom_attributes);

                // Set the shipping class for the product
                wp_set_post_terms($post_id, array(47), 'product_shipping_class');
                // var_dump($post_id);
                // die();
                // sleep(5);
                // wp_redirect('/?add-to-cart='.$post_id);
                echo '<!DOCTYPE html>
                <html lang="en">
                  <head>
                    <meta charset="utf-8">
                    <title>Redirecting to your cart...</title>
                  </head>
                  <body>
                    <script>window.location.href = "/cart/?add-to-cart='.$post_id.'";</script>
                  </body>
                </html>';

                // exit;

            }
        }       
    }
    
    static function maybe_change_availability_text($availability, $product){
        $made_to_order = $product->get_attribute('make-to-order');
        if($made_to_order != '' && $made_to_order != 'No'){
            $availability = 'Made to Order';
        }
        return $availability;
    }

}