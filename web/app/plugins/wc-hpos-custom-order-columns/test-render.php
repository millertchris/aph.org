<?php
/**
 * Test actual rendering context
 * Run with: wp eval-file /sites/aph-staging.prolificdigital.io/files/web/app/plugins/wc-hpos-custom-order-columns/test-render.php --skip-themes
 */

// Load WooCommerce admin
if (!class_exists('WC_Admin')) {
    require_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/class-wc-admin.php';
}

echo "=== Testing Order List Table ===\n\n";

// Check if the Orders list table class exists
if (class_exists('\Automattic\WooCommerce\Internal\Admin\Orders\ListTable')) {
    echo "✓ HPOS ListTable class exists\n";
    
    // Create an instance to test
    set_current_screen('woocommerce_page_wc-orders');
    
    try {
        // Create the list table instance
        $list_table = new \Automattic\WooCommerce\Internal\Admin\Orders\ListTable();
        
        // Get columns
        $columns = $list_table->get_columns();
        echo "\nColumns from ListTable::get_columns():\n";
        foreach ($columns as $key => $label) {
            echo "  - $key: $label\n";
        }
        
        // Check sortable columns
        $sortable = $list_table->get_sortable_columns();
        echo "\nSortable columns:\n";
        foreach ($sortable as $key => $data) {
            echo "  - $key\n";
        }
        
    } catch (Exception $e) {
        echo "Error creating ListTable: " . $e->getMessage() . "\n";
    }
} else {
    echo "✗ HPOS ListTable class not found\n";
}

// Check if we're dealing with the right table class
echo "\n=== Checking Table Class Loading ===\n";

// Check which order storage is being used
if (class_exists('Automattic\WooCommerce\Utilities\OrderUtil')) {
    $using_cot = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    echo "Custom Order Tables (COT/HPOS): " . ($using_cot ? 'ENABLED' : 'DISABLED') . "\n";
    
    $data_sync = \Automattic\WooCommerce\Utilities\OrderUtil::is_custom_order_tables_in_sync();
    echo "Data Sync Enabled: " . ($data_sync ? 'YES' : 'NO') . "\n";
}

// Check if there's a caching issue
echo "\n=== Checking for Caching Issues ===\n";
$transient_key = 'wc_hpos_custom_columns_test_' . get_current_user_id();
set_transient($transient_key, 'test', 60);
$test = get_transient($transient_key);
if ($test === 'test') {
    echo "Transients working correctly\n";
    delete_transient($transient_key);
} else {
    echo "⚠️  Transient test failed - possible caching issue\n";
}

// Check user meta for hidden columns specific to HPOS
echo "\n=== Checking User Meta for Hidden Columns ===\n";
$user_id = 1; // Admin user
$hidden_columns = get_user_meta($user_id, 'managewoocommerce_page_wc-orderscolumnshidden', true);
if ($hidden_columns) {
    echo "Hidden columns for user $user_id:\n";
    print_r($hidden_columns);
} else {
    echo "No hidden columns meta found for user $user_id\n";
}

// Check screen options
$per_page = get_user_meta($user_id, 'woocommerce_page_wc-orders_per_page', true);
echo "\nPer page setting: " . ($per_page ?: 'not set') . "\n";