<?php
/*
Plugin Name: HumanWare Order Trigger
Description: Calls HumanWare API when orders contain flagged products (based on ACF).
Version: 1.1
Author: Vivek Bansal
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Hook into WooCommerce order processing
add_action('woocommerce_checkout_order_processed', 'hw_check_order_for_humanware_products', 20, 1);

function hw_check_order_for_humanware_products($order_id) {
    // Validate order ID
    $order_id = absint($order_id);
    if (!$order_id) {
        return;
    }
    
    $logger = wc_get_logger();
    $order = wc_get_order($order_id);
    
    if (!$order) {
        return;
    }
    
    // Sanitize email
    $email = sanitize_email($order->get_billing_email());
    
    if (!is_email($email)) {
        $logger->error("Invalid email for order {$order_id}", array('source' => 'humanware-order-trigger'));
        return;
    }

    foreach ($order->get_items() as $item) {
        $product_id = absint($item->get_product_id());
        $qty        = absint($item->get_quantity());
        $product    = wc_get_product($product_id);
        
        if (!$product) {
            continue;
        }

        // ACF field on product: 'trigger_humanware'
        $should_trigger = get_field('trigger_humanware', 'post_' . $product_id);

        if ($should_trigger) {
            $sku = sanitize_text_field($product->get_sku());
            
            if (empty($sku)) {
                $logger->error("Empty SKU for product {$product_id}", array('source' => 'humanware-order-trigger'));
                continue;
            }

            // Call HumanWare API with sanitized data
            $order_number = sanitize_text_field($order->get_order_number());
            $response = invoke_tv_api_e_order($order_number, $sku, $qty, $email);

            // Log API response
            if ($response !== false) {
                $logger->info("HumanWare API called for order {$order_id}, product {$sku}", array('source' => 'humanware-order-trigger'));
            } else {
                $logger->error("HumanWare API call failed for order {$order_id}, product {$sku}", array('source' => 'humanware-order-trigger'));
            }
        }
    }
}

/**
 * Get HumanWare API credentials securely
 */
function hw_get_api_credentials() {
    // First, check for WordPress constants (most secure)
    if (defined('HUMANWARE_API_SECRET')) {
        return HUMANWARE_API_SECRET;
    }
    
    // Fallback to encrypted database option
    $secret = get_option('humanware_api_secret');
    if ($secret) {
        return $secret;
    }
    
    // Log error if no credentials found
    $logger = wc_get_logger();
    $logger->error('HumanWare API secret not configured', array('source' => 'humanware-order-trigger'));
    return false;
}

/**
 * Call HumanWare API with order information
 * 
 * @param string $order_id Order number
 * @param string $sku Product SKU
 * @param int $number_of_products Quantity
 * @param string $email Customer email
 * @return string|false API response or false on failure
 */
function invoke_tv_api_e_order($order_id, $sku, $number_of_products, $email) {
    // Validate and sanitize all inputs
    $order_id = sanitize_text_field($order_id);
    $sku = sanitize_text_field($sku);
    $number_of_products = absint($number_of_products);
    $email = sanitize_email($email);
    
    // Validate email format
    if (!is_email($email)) {
        return false;
    }
    
    // Get secret key securely
    $secretKey = hw_get_api_credentials();
    if (!$secretKey) {
        return false;
    }
    
    $date = date("Y-m-d");
    $time = date("H:i");
    $timeZone = 0; // Consider making this configurable
    $client = "APH";

    // Generate secure hash
    $key = $secretKey . $date . $time . $timeZone . $email . $sku . $number_of_products;
    $hash = md5($key);

    // Build API URL with proper encoding
    $api_params = array(
        'date'    => $date,
        'time'    => $time,
        'tz'      => $timeZone,
        'email'   => $email,
        'sku'     => $sku,
        'amount'  => $number_of_products,
        'h'       => $hash,
        'orderId' => $order_id,
        'client'  => $client,
    );
    
    $url = 'https://www.tactileview.com/include/shoppurchase.asp?' . http_build_query($api_params);

    // Use WordPress HTTP API instead of cURL for better security
    $args = array(
        'timeout'     => 30,
        'redirection' => 5,
        'httpversion' => '1.1',
        'sslverify'   => true, // Enforce SSL certificate verification
        'headers'     => array(
            'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url'),
        ),
    );
    
    $response = wp_remote_get($url, $args);
    
    // Check for errors
    if (is_wp_error($response)) {
        $logger = wc_get_logger();
        $logger->error('HumanWare API error: ' . $response->get_error_message(), array('source' => 'humanware-order-trigger'));
		hw_send_error_email($order_id, $sku, "API response was '{$body}'");
        return false;
    }
    
    // Check HTTP status code
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        $logger = wc_get_logger();
        $logger->error('HumanWare API returned status code: ' . $response_code, array('source' => 'humanware-order-trigger'));
		hw_send_error_email($order_id, $sku, "API response was '{$body}'");
        return false;
    }
    
    // Return the response body
    //return wp_remote_retrieve_body($response);
	
	$body = trim(wp_remote_retrieve_body($response));

	if (strtolower($body) === 'yes') {
		$logger = wc_get_logger();
        $logger->error('HumanWare API returned yes with status code: ' . $response_code, array('source' => 'humanware-order-trigger'));
        return $body;
    }

    // Unexpected response or "no"
	$logger = wc_get_logger();
    $logger->error("HumanWare API returned: {$body}", ['source' => 'humanware-order-trigger']);
    hw_send_error_email($order_id, $sku, "API response was '{$body}'");

    return false;
}

/**
 * Add admin notice if API credentials are not configured
 */
add_action('admin_notices', 'hw_check_api_credentials_notice');

function hw_check_api_credentials_notice() {
    // Only show on WooCommerce settings pages, but not on the HumanWare settings page itself
    if (!isset($_GET['page']) || (strpos($_GET['page'], 'wc-') !== 0 && $_GET['page'] !== 'humanware-api-settings')) {
        return;
    }
    
    // Skip notice on the HumanWare settings page itself
    if (isset($_GET['page']) && $_GET['page'] === 'humanware-api-settings') {
        return;
    }
    
    // Check if credentials are configured
    if (!defined('HUMANWARE_API_SECRET') && !get_option('humanware_api_secret')) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>HumanWare Order Trigger:</strong> API credentials are not configured. ';
        echo '<a href="' . admin_url('admin.php?page=humanware-api-settings') . '">Configure API Settings</a></p>';
        echo '</div>';
    }
}

/**
 * Add settings page for API configuration (optional fallback)
 */
add_action('admin_menu', 'hw_add_settings_page');

function hw_add_settings_page() {
    add_submenu_page(
        'woocommerce',
        'HumanWare API Settings',
        'HumanWare API',
        'manage_woocommerce',
        'humanware-api-settings',
        'hw_settings_page_content'
    );
}

function hw_send_error_email($order_id, $sku, $reason) {
    $to = defined('HUMANWARE_ERROR_EMAIL') ? HUMANWARE_ERROR_EMAIL : get_option('humanware_error_email');
	if (empty($to)) {
        return; // no target email configured
    }

     $subject = "⚠ HumanWare API Error - Order {$order_id}";

    $message  = "Automated Notification:\n\n";
    $message .= "Order {$order_id} failed to connect with the HumanWare API. ";
    $message .= "Because of this, the customer has not received their software download email from HumanWare.\n\n";
    $message .= "Reason: {$reason}\n\n";
    $message .= "Please take the following action:\n";
    $message .= "• Manually resubmit the order in order to trigger the HumanWare API call.\n";
    $message .= "• Confirm that the resubmission is successful and customer receives their download email.\n";

    wp_mail($to, $subject, $message);
}

function hw_settings_page_content() {
    // Check if constant is defined
    $is_constant_defined = defined('HUMANWARE_API_SECRET');
    
    // Handle form submission only if constant is not defined
    if (!$is_constant_defined && isset($_POST['submit']) && check_admin_referer('humanware_api_settings')) {
        $secret = sanitize_text_field($_POST['humanware_api_secret']);
        update_option('humanware_api_secret', $secret);
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }
    
    $current_secret = get_option('humanware_api_secret', '');
    ?>
    <div class="wrap">
        <h1>HumanWare API Settings</h1>
        
        <?php if ($is_constant_defined): ?>
            <div class="notice notice-success" style="margin-top: 20px;">
                <p><strong>✓ API Secret is configured in wp-config.php</strong></p>
                <p>The HumanWare API secret key is securely defined as a constant in your wp-config.php file. This is the recommended configuration method.</p>
            </div>
            
            <h2>Current Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Configuration Method</th>
                    <td><strong>wp-config.php constant</strong> (Most Secure)</td>
                </tr>
                <tr>
                    <th scope="row">API Secret Status</th>
                    <td>
                        <span style="color: green; font-weight: bold;">✓ Configured</span>
                        <p class="description">The API secret is securely stored and cannot be modified from this interface.</p>
                    </td>
                </tr>
            </table>
            
            <?php if ($current_secret): ?>
                <div class="notice notice-warning" style="margin-top: 20px;">
                    <p><strong>Note:</strong> A database-stored secret was found but will be ignored since the wp-config.php constant takes precedence.</p>
                    <p>You may want to clear the database option for security:</p>
                    <form method="post" action="" style="margin-top: 10px;">
                        <?php wp_nonce_field('humanware_clear_db_secret'); ?>
                        <input type="hidden" name="clear_db_secret" value="1" />
                        <input type="submit" name="submit" class="button" value="Clear Database Secret" />
                    </form>
                </div>
                <?php
                // Handle clearing database secret
                if (isset($_POST['clear_db_secret']) && check_admin_referer('humanware_clear_db_secret')) {
                    delete_option('humanware_api_secret');
                    echo '<script>window.location.href = window.location.href;</script>';
                }
                ?>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="notice notice-warning" style="margin-top: 20px;">
                <p><strong>Recommended:</strong> For better security, define the API secret in wp-config.php instead of storing it in the database:</p>
                <pre>define('HUMANWARE_API_SECRET', 'your-secret-key');</pre>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('humanware_api_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="humanware_api_secret">API Secret Key</label></th>
                        <td>
                            <input type="password" id="humanware_api_secret" name="humanware_api_secret" 
                                   value="<?php echo esc_attr($current_secret); ?>" class="regular-text" />
                            <p class="description">Enter the HumanWare API secret key</p>
                            <?php if (empty($current_secret)): ?>
                                <p class="description" style="color: #d63638;">⚠ No API secret is currently configured</p>
                            <?php else: ?>
                                <p class="description" style="color: #00a32a;">✓ API secret is configured in database</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        <?php endif; ?>
        
        <hr style="margin-top: 40px;">
        
        <h2>API Connection Test</h2>
        <p>Use this to verify that your API credentials are working correctly.</p>
        <form method="post" action="">
            <?php wp_nonce_field('humanware_test_connection'); ?>
            <input type="hidden" name="test_connection" value="1" />
            <input type="submit" name="submit" class="button button-secondary" value="Test API Connection" />
        </form>
        
        <?php
        // Handle connection test
        if (isset($_POST['test_connection']) && check_admin_referer('humanware_test_connection')) {
            $secret = hw_get_api_credentials();
            if ($secret) {
                echo '<div class="notice notice-success" style="margin-top: 10px;"><p>✓ API credentials are configured and accessible.</p></div>';
            } else {
                echo '<div class="notice notice-error" style="margin-top: 10px;"><p>✗ No API credentials found. Please configure the API secret.</p></div>';
            }
        }
        ?>
    </div>
    <?php
}