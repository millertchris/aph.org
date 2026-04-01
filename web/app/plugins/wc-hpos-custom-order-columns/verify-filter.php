<?php
/**
 * Verify the correct filter name
 * Run with: wp eval-file /sites/aph-staging.prolificdigital.io/files/web/app/plugins/wc-hpos-custom-order-columns/verify-filter.php --skip-themes
 */

// Create the list table instance to check order_type
set_current_screen('woocommerce_page_wc-orders');

if (class_exists('\Automattic\WooCommerce\Internal\Admin\Orders\ListTable')) {
    $list_table = new \Automattic\WooCommerce\Internal\Admin\Orders\ListTable();
    
    // Use reflection to get the order_type property
    $reflection = new ReflectionClass($list_table);
    $property = $reflection->getProperty('order_type');
    $property->setAccessible(true);
    $order_type = $property->getValue($list_table);
    
    echo "Order type: $order_type\n";
    echo "Expected filter name: woocommerce_{$order_type}_list_table_columns\n";
    echo "Actual filter name: woocommerce_shop_order_list_table_columns\n\n";
    
    // Test both filter names
    $test_columns = ['test' => 'Test'];
    
    echo "Testing filter: woocommerce_shop_order_list_table_columns\n";
    add_filter('woocommerce_shop_order_list_table_columns', function($cols) {
        $cols['custom1'] = 'Custom 1';
        return $cols;
    });
    $result1 = apply_filters('woocommerce_shop_order_list_table_columns', $test_columns);
    echo "Result: " . (isset($result1['custom1']) ? '✓ Filter works' : '✗ Filter not working') . "\n\n";
    
    echo "Testing filter: woocommerce_shop_order_list_table_columns (with shop_order type)\n";
    add_filter('woocommerce_shop_order_list_table_columns', function($cols) {
        $cols['custom2'] = 'Custom 2';
        return $cols;
    });
    $result2 = apply_filters('woocommerce_shop_order_list_table_columns', $test_columns);
    echo "Result: " . (isset($result2['custom2']) ? '✓ Filter works' : '✗ Filter not working') . "\n\n";
    
    // Now test the actual get_columns method
    echo "Testing ListTable::get_columns() method:\n";
    $columns = $list_table->get_columns();
    echo "Columns returned:\n";
    foreach ($columns as $key => $label) {
        echo "  - $key: $label\n";
    }
}