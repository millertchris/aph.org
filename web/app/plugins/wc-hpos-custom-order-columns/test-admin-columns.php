<?php
/**
 * Test Admin Columns Pro integration
 * Run with: wp eval-file /sites/aph-staging.prolificdigital.io/files/web/app/plugins/wc-hpos-custom-order-columns/test-admin-columns.php --skip-themes
 */

// Check if Admin Columns Pro is managing the orders screen
echo "=== Admin Columns Pro Check ===\n\n";

// Check if ACP is active
if (class_exists('AC\ListScreenRepository\Storage')) {
    echo "✓ Admin Columns Pro is active\n";
    
    // Check if there's a custom column set for orders
    if (class_exists('ACP\ListScreenRepository\Database')) {
        echo "✓ Admin Columns Pro Database class exists\n";
    }
} else {
    echo "✗ Admin Columns Pro classes not found\n";
}

// Check if Admin Columns is managing WooCommerce orders
if (class_exists('AC\ListScreenRepository\Storage\ListScreenRepository')) {
    echo "\nChecking for WooCommerce Orders screen configuration...\n";
}

// Test the actual filter chain
echo "\n=== Testing Filter Chain ===\n";

// Get all callbacks for the column filter
global $wp_filter;
if (isset($wp_filter['woocommerce_shop_order_list_table_columns'])) {
    echo "\nAll callbacks for 'woocommerce_shop_order_list_table_columns':\n";
    foreach ($wp_filter['woocommerce_shop_order_list_table_columns'] as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            echo "Priority $priority: ";
            if (is_array($callback['function'])) {
                if (is_object($callback['function'][0])) {
                    echo get_class($callback['function'][0]) . '::' . $callback['function'][1];
                } else {
                    echo $callback['function'][0] . '::' . $callback['function'][1];
                }
            } elseif (is_string($callback['function'])) {
                echo $callback['function'];
            } else {
                echo "Closure/Anonymous";
            }
            echo "\n";
        }
    }
}

// Check screen registration
echo "\n=== Screen Registration Check ===\n";

// Simulate being on the orders page
set_current_screen('woocommerce_page_wc-orders');
$screen = get_current_screen();
if ($screen) {
    echo "Screen ID: " . $screen->id . "\n";
    echo "Screen Base: " . $screen->base . "\n";
    echo "Post Type: " . $screen->post_type . "\n";
    
    // Check if columns are registered for this screen
    $columns = get_column_headers($screen);
    if ($columns) {
        echo "\nColumns registered for this screen:\n";
        foreach ($columns as $key => $label) {
            echo "  - $key: $label\n";
        }
    } else {
        echo "\nNo columns registered via get_column_headers()\n";
    }
}

// Test applying filters with the correct screen context
echo "\n=== Testing with Screen Context ===\n";
$_GET['page'] = 'wc-orders';
$test_columns = ['order_number' => 'Order'];
$result = apply_filters('woocommerce_shop_order_list_table_columns', $test_columns);
echo "Columns after filter:\n";
foreach ($result as $key => $label) {
    echo "  - $key: $label\n";
}

// Check if Admin Columns is completely overriding
echo "\n=== Checking Admin Columns Override ===\n";
if (function_exists('ac_get_list_screen')) {
    echo "Admin Columns function 'ac_get_list_screen' exists\n";
    // Try to get the list screen for WooCommerce orders
    $list_screen = ac_get_list_screen('woocommerce_page_wc-orders');
    if ($list_screen) {
        echo "Admin Columns is managing WooCommerce Orders screen\n";
        echo "List Screen Key: " . $list_screen->get_key() . "\n";
    } else {
        echo "Admin Columns not managing this screen\n";
    }
}