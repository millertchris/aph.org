<?php

/**
 * Plugin Name: Cart Token Exchange
 * Plugin URI: https://prolificdigital.com
 * Description: Handles cart token restoration from Louis search application
 * Version: 1.0.0
 * Author: Prolific Digital
 * Author URI: https://prolificdigital.com
 * Text Domain: cart-token-exchange
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

if (!defined('CTE_PLUGIN_FILE')) {
    define('CTE_PLUGIN_FILE', __FILE__);
}

if (!defined('CTE_PLUGIN_PATH')) {
    define('CTE_PLUGIN_PATH', plugin_dir_path(__FILE__));
}

if (!defined('CTE_PLUGIN_URL')) {
    define('CTE_PLUGIN_URL', plugin_dir_url(__FILE__));
}

class CartTokenExchange {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        $this->load_textdomain();
        $this->includes();
        $this->init_hooks();
    }

    public function load_textdomain() {
        load_plugin_textdomain('cart-token-exchange', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function includes() {
        // All functionality is contained in the main plugin file
    }

    public function init_hooks() {
        // Try multiple hooks to catch cart token before it gets stripped
        add_action('wp', array($this, 'handle_cart_token_restoration'), 5);
        add_action('wp_loaded', array($this, 'handle_cart_token_restoration'), 5);
        add_action('template_redirect', array($this, 'handle_cart_token_restoration'));

        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_settings_tabs_cart_token', array($this, 'settings_tab'));
        add_action('woocommerce_update_options_cart_token', array($this, 'update_settings'));

        // Cart modification hooks for bidirectional sync
        add_action('woocommerce_add_to_cart', array($this, 'sync_cart_to_store_api'), 10, 6);
        add_action('woocommerce_cart_item_removed', array($this, 'sync_cart_to_store_api_simple'), 10, 2);
        add_action('woocommerce_after_cart_item_quantity_update', array($this, 'sync_cart_to_store_api_simple'), 10, 4);
        add_action('woocommerce_cart_emptied', array($this, 'sync_cart_to_store_api_simple'));
    }

    public function handle_cart_token_restoration() {
        // Prevent running multiple times per request
        static $restoration_attempted = false;
        if ($restoration_attempted) {
            return;
        }

        // Only log if we actually have a cart token in the URL to reduce noise
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $has_token_in_url = strpos($request_uri, 'cart_token=') !== false;

        // Log restoration attempts only when debug logging is enabled
        if ($has_token_in_url && get_option('cte_debug_logging', 'no') === 'yes') {
            error_log('[Cart Token Debug] Starting cart restoration process on hook: ' . current_filter());
        }

        if (!$this->should_handle_restoration()) {
            return;
        }

        $cart_token = $this->detect_cart_token();

        if (!$cart_token) {
            return;
        }

        // Check if we've already restored this specific token
        $last_restored_token = WC()->session ? WC()->session->get('louis_last_restored_token') : null;
        if ($last_restored_token === $cart_token) {
            return;
        }

        // Mark as attempted to prevent duplicate runs
        $restoration_attempted = true;

        if (!$this->check_rate_limit()) {
            $this->log_event('warning', 'Rate limit exceeded for cart token restoration', array('ip' => $this->get_user_ip()));
            return;
        }

        $payload = $this->validate_cart_token($cart_token);

        if (!$payload) {
            $this->log_event('warning', 'Invalid cart token received', array('token_preview' => substr($cart_token, 0, 20) . '...'));
            return;
        }

        $success = $this->restore_cart_from_token($cart_token);

        if ($success) {
            $this->log_event('info', 'Cart successfully restored from token');

            if (isset($_GET['cart_token'])) {
                wp_redirect(remove_query_arg('cart_token'));
                exit;
            }
        } else {
            $this->log_event('error', 'Failed to restore cart from token');
            wc_add_notice(__('Unable to restore your cart from Louis. Please add items again.', 'cart-token-exchange'), 'notice');
        }
    }

    private function should_handle_restoration() {
        $restoration_enabled = get_option('cte_enable_cart_restoration', 'yes');

        if ($restoration_enabled !== 'yes') {
            return false;
        }

        // Check if we have a cart token - if so, always allow restoration
        $get_token = isset($_GET['cart_token']);
        $post_token = isset($_POST['cart_token']);
        $session_token = (WC()->session && WC()->session->get('louis_cart_token'));

        $has_cart_token = $get_token || $post_token || $session_token;

        if ($has_cart_token) {
            return true;
        }

        $is_cart = is_cart();
        $is_checkout = is_checkout();
        $is_shop = is_shop();
        $is_woocommerce = is_woocommerce();

        return $is_cart || $is_checkout || $is_shop || $is_woocommerce;
    }

    private function detect_cart_token() {
        // Also check raw request URI
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $has_token_in_url = strpos($request_uri, 'cart_token=') !== false;

        // Debug logging only when enabled
        if ($has_token_in_url && get_option('cte_debug_logging', 'no') === 'yes') {
            error_log('[Cart Token Debug] $_GET contents: ' . print_r($_GET, true));
            error_log('[Cart Token Debug] $_POST contents: ' . print_r($_POST, true));
            error_log('[Cart Token Debug] $_REQUEST contents: ' . print_r($_REQUEST, true));
            error_log('[Cart Token Debug] Raw REQUEST_URI: ' . $request_uri);
        }

        // Try to parse cart_token directly from URL first
        if (preg_match('/[?&]cart_token=([^&]+)/', $request_uri, $matches)) {
            $cart_token = urldecode($matches[1]);
            return sanitize_text_field($cart_token);
        }

        if (isset($_GET['cart_token'])) {
            return sanitize_text_field($_GET['cart_token']);
        }

        if (isset($_POST['cart_token'])) {
            return sanitize_text_field($_POST['cart_token']);
        }

        // Only use session token if there's no URL parameter (to prevent repeated restoration)
        if (!$has_token_in_url && WC()->session && WC()->session->get('louis_cart_token')) {
            return WC()->session->get('louis_cart_token');
        }
        return null;
    }

    private function validate_cart_token($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }

            $payload = json_decode(base64_decode($parts[1]), true);

            if (!$payload) {
                return false;
            }

            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return false;
            }

            if (!isset($payload['iss']) || $payload['iss'] !== 'store-api') {
                return false;
            }

            return $payload;
        } catch (Exception $e) {
            $this->log_event('error', 'Token validation error: ' . $e->getMessage());
            return false;
        }
    }

    private function restore_cart_from_token($cart_token) {
        try {
            $site_url = home_url();
            $api_url = $site_url . '/wp-json/wc/store/v1/cart';

            $response = wp_remote_get($api_url, array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Cart-Token' => $cart_token,
                ),
                'timeout' => 30,
                'redirection' => 2,
                'httpversion' => '1.1',
                'blocking' => true,
            ));

            if (is_wp_error($response)) {
                $this->log_event('error', 'API request failed: ' . $response->get_error_message());
                return false;
            }

            $body = wp_remote_retrieve_body($response);
            $cart_data = json_decode($body, true);

            if (!$cart_data) {
                $this->log_event('warning', 'Failed to decode API response');
                return false;
            }

            if (empty($cart_data['items'])) {
                $this->log_event('warning', 'Empty or invalid cart data received');
                return false;
            }

            WC()->cart->empty_cart();

            $restored_items = 0;
            foreach ($cart_data['items'] as $item) {
                $product_id = isset($item['id']) ? intval($item['id']) : 0;
                $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                $variation_id = isset($item['variation_id']) ? intval($item['variation_id']) : 0;
                $variation = isset($item['variation']) && is_array($item['variation']) ? $item['variation'] : array();

                if ($product_id > 0) {
                    $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation);
                    if ($added) {
                        $restored_items++;
                    }
                }
            }

            if ($restored_items > 0) {
                WC()->cart->calculate_totals();
                if (WC()->session) {
                    WC()->session->set('louis_cart_token', $cart_token);
                    WC()->session->set('louis_cart_restored_time', time());
                    WC()->session->set('louis_last_restored_token', $cart_token);
                }

                $this->log_event('info', "Successfully restored {$restored_items} items from cart token");
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->log_event('error', 'Cart restoration failed: ' . $e->getMessage());
            return false;
        }
    }

    private function check_rate_limit() {
        $user_ip = $this->get_user_ip();
        $transient_key = 'cte_cart_attempts_' . md5($user_ip);
        $attempts = get_transient($transient_key) ?: 0;

        // Allow 50 attempts per hour for testing, can be reduced later
        if ($attempts >= 50) {
            return false;
        }

        set_transient($transient_key, $attempts + 1, HOUR_IN_SECONDS);
        return true;
    }

    private function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }

    private function log_event($level, $message, $context = array()) {
        if (get_option('cte_debug_logging', 'no') === 'yes') {
            $log_entry = array(
                'timestamp' => current_time('mysql'),
                'level' => $level,
                'message' => $message,
                'context' => $context,
                'user_ip' => $this->get_user_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            );

            error_log('[Cart Token Exchange] ' . json_encode($log_entry));
        }
    }

    public function sync_cart_to_store_api($cart_item_key = null, $product_id = null, $quantity = null, $variation_id = null, $variation = null, $cart_item_data = null) {
        $this->perform_cart_sync();
    }

    public function sync_cart_to_store_api_simple() {
        $this->perform_cart_sync();
    }

    private function perform_cart_sync() {
        if (!WC()->session) {
            return;
        }

        $cart_token = WC()->session->get('louis_cart_token');

        if (!$cart_token) {
            return;
        }

        if (get_option('cte_enable_cart_restoration', 'yes') !== 'yes') {
            return;
        }

        // Don't sync if we're in the middle of a restoration process
        if (doing_action('template_redirect')) {
            return;
        }

        // Don't sync if restoration just happened (within 5 seconds)
        $restoration_time = WC()->session->get('louis_cart_restored_time');
        if ($restoration_time && (time() - $restoration_time) < 5) {
            return;
        }

        // Don't sync if there's a cart token in the URL (incoming restoration)
        if (isset($_GET['cart_token'])) {
            return;
        }

        try {
            $cart_contents = $this->get_current_cart_for_api();
            $success = $this->update_store_api_cart($cart_token, $cart_contents);

            if ($success) {
                $this->log_event('info', 'Successfully synced cart changes to Store API', array('items_count' => count($cart_contents)));
            } else {
                $this->log_event('warning', 'Failed to sync cart changes to Store API');
            }
        } catch (Exception $e) {
            $this->log_event('error', 'Cart sync error: ' . $e->getMessage());
        }
    }

    private function get_current_cart_for_api() {
        $cart_items = array();

        if (!WC()->cart || WC()->cart->is_empty()) {
            return $cart_items;
        }

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_items[] = array(
                'key' => $cart_item_key,
                'id' => $cart_item['product_id'],
                'quantity' => $cart_item['quantity'],
                'variation_id' => isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0,
                'variation' => isset($cart_item['variation']) ? $cart_item['variation'] : array()
            );
        }

        return $cart_items;
    }

    private function update_store_api_cart($cart_token, $cart_items) {
        // Check if WordPress HTTP functions are available
        if (!function_exists('wp_remote_post') || !function_exists('wp_remote_request')) {
            $this->log_event('error', 'WordPress HTTP functions not available');
            return false;
        }

        try {
            $site_url = home_url();

            // First, clear the Store API cart using wp_remote_request with DELETE method
            $clear_response = wp_remote_request($site_url . '/wp-json/wc/store/v1/cart/items', array(
                'method' => 'DELETE',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Cart-Token' => $cart_token,
                ),
                'timeout' => 30,
            ));

            if (is_wp_error($clear_response)) {
                $this->log_event('error', 'Failed to clear Store API cart: ' . $clear_response->get_error_message());
                return false;
            }

            // If cart is empty, we're done
            if (empty($cart_items)) {
                return true;
            }

            // Add each item to the Store API cart
            $success_count = 0;
            foreach ($cart_items as $item) {
                $add_data = array(
                    'id' => $item['id'],
                    'quantity' => $item['quantity']
                );

                if (!empty($item['variation_id'])) {
                    $add_data['variation'] = $item['variation'];
                }

                $add_response = wp_remote_post($site_url . '/wp-json/wc/store/v1/cart/add-item', array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                        'Cart-Token' => $cart_token,
                    ),
                    'body' => json_encode($add_data),
                    'timeout' => 30,
                ));

                if (!is_wp_error($add_response)) {
                    $response_code = wp_remote_retrieve_response_code($add_response);
                    if ($response_code >= 200 && $response_code < 300) {
                        $success_count++;
                    }
                }
            }

            return $success_count === count($cart_items);
        } catch (Exception $e) {
            $this->log_event('error', 'Store API cart update failed: ' . $e->getMessage());
            return false;
        }
    }

    public function add_settings_tab($settings_tabs) {
        $settings_tabs['cart_token'] = __('Cart Token Exchange', 'cart-token-exchange');
        return $settings_tabs;
    }

    public function settings_tab() {
        woocommerce_admin_fields($this->get_settings());
    }

    public function update_settings() {
        woocommerce_update_options($this->get_settings());
    }

    public function get_settings() {
        return array(
            'section_title' => array(
                'name' => __('Cart Token Exchange Settings', 'cart-token-exchange'),
                'type' => 'title',
                'desc' => __('Configure cart token handling from Louis application', 'cart-token-exchange'),
                'id' => 'cte_section'
            ),
            'enable_cart_restoration' => array(
                'name' => __('Enable Cart Restoration', 'cart-token-exchange'),
                'type' => 'checkbox',
                'desc' => __('Allow cart restoration from Louis application tokens', 'cart-token-exchange'),
                'id' => 'cte_enable_cart_restoration',
                'default' => 'yes'
            ),
            'token_expiry_hours' => array(
                'name' => __('Token Expiry (Hours)', 'cart-token-exchange'),
                'type' => 'number',
                'desc' => __('Maximum age for cart tokens (default: 48)', 'cart-token-exchange'),
                'id' => 'cte_token_expiry_hours',
                'default' => 48,
                'css' => 'width: 100px;'
            ),
            'debug_logging' => array(
                'name' => __('Debug Logging', 'cart-token-exchange'),
                'type' => 'checkbox',
                'desc' => __('Log cart restoration attempts for debugging', 'cart-token-exchange'),
                'id' => 'cte_debug_logging',
                'default' => 'no'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'cte_section'
            )
        );
    }

    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' . sprintf(esc_html__('Cart Token Exchange requires WooCommerce to be installed and active. You can download %s here.', 'cart-token-exchange'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
    }

    public function activate() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('Cart Token Exchange requires WooCommerce to be installed and active.', 'cart-token-exchange'));
        }

        add_option('cte_enable_cart_restoration', 'yes');
        add_option('cte_token_expiry_hours', 48);
        add_option('cte_debug_logging', 'no');
    }

    public function deactivate() {
        wp_cache_flush();
    }
}

CartTokenExchange::get_instance();
