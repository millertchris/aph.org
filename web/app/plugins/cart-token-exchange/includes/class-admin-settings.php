<?php

if (!defined('ABSPATH')) {
    exit;
}

class CTE_Admin_Settings {

    public static function get_settings() {
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

    public static function add_settings_tab($settings_tabs) {
        $settings_tabs['cart_token'] = __('Cart Token Exchange', 'cart-token-exchange');
        return $settings_tabs;
    }

    public static function settings_tab() {
        woocommerce_admin_fields(self::get_settings());
    }

    public static function update_settings() {
        woocommerce_update_options(self::get_settings());
    }
}