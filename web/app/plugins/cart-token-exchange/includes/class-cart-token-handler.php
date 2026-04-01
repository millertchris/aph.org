<?php

if (!defined('ABSPATH')) {
    exit;
}

class CTE_Cart_Token_Handler {

    public static function detect_cart_token() {
        if (isset($_GET['cart_token'])) {
            return sanitize_text_field($_GET['cart_token']);
        }
        
        if (isset($_POST['cart_token'])) {
            return sanitize_text_field($_POST['cart_token']);
        }
        
        if (WC()->session && WC()->session->get('louis_cart_token')) {
            return WC()->session->get('louis_cart_token');
        }
        
        return null;
    }

    public static function validate_cart_token($token) {
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
            error_log('[Cart Token Exchange] Token validation error: ' . $e->getMessage());
            return false;
        }
    }

    public static function restore_cart_from_token($cart_token) {
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
                error_log('[Cart Token Exchange] API request failed: ' . $response->get_error_message());
                return false;
            }
            
            $body = wp_remote_retrieve_body($response);
            $cart_data = json_decode($body, true);
            
            if (!$cart_data || empty($cart_data['items'])) {
                error_log('[Cart Token Exchange] Empty or invalid cart data received');
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
                }
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('[Cart Token Exchange] Cart restoration failed: ' . $e->getMessage());
            return false;
        }
    }
}