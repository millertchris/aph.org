# WooCommerce HPOS Compatibility Report

**Plugin:** Woocommerce EOT Account Funds  
**Version:** 1.0  
**Audit Date:** 2025-08-28  

## Executive Summary

**Verdict:** INCOMPATIBLE  
**Risk Level:** HIGH  

The WooCommerce Account Funds plugin is **incompatible** with HPOS due to extensive use of legacy WordPress post/postmeta functions for order data access. The plugin requires significant refactoring to work with HPOS native mode, particularly in the order management and reporting components.

## Detailed Analysis

### HPOS Compatibility Checks

| Check | Status | Details |
|-------|--------|---------|
| Declares HPOS compatibility | ❌ | No FeaturesUtil::declare_compatibility found |
| Uses WC Data Stores for orders | ❌ | Uses legacy post meta functions |
| Avoids wp_posts/wp_postmeta for orders | ❌ | Multiple instances of direct post meta access |
| Avoids WP_Query on shop_order | ✅ | No WP_Query usage found |
| Uses wc_get_orders or OrderQuery | ❌ | Uses get_posts instead |
| Order meta via WC API | ❌ | Uses get_post_meta/update_post_meta |
| Handles custom order types correctly | N/A | Not applicable |
| No direct SQL on legacy tables | ✅ | No direct SQL queries found |
| No admin UI post table assumptions | ✅ | No post list screen dependencies |
| No blocking 3rd party dependencies | ✅ | No problematic dependencies |

### Critical Issues Found

#### 1. Legacy Order Meta Access
**File:** `includes/class-wc-account-funds-order-manager.php:51`
```php
$_funds_used = get_post_meta( version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id(), '_funds_used', true );
```
**Impact:** In HPOS native mode, orders are not stored in wp_posts/wp_postmeta tables, so get_post_meta will fail to retrieve order metadata.

#### 2. Order Meta Updates
**File:** `includes/class-wc-gateway-account-funds.php:162`
```php
update_post_meta( $order_id, '_funds_used', $order->get_total() );
```
**Impact:** Order metadata updates will not be stored properly in HPOS native mode.

#### 3. Reports Query Issues
**File:** `includes/class-wc-account-funds-deposits-by-date.php:37-58`
```php
$args = array(
    'post_type' => 'shop_order',
    // ... other args
);
$orders = get_posts( $args );
```
**Impact:** In HPOS native mode, orders are not stored as posts, so this query will return no results, breaking all reporting functionality.

### Complete Evidence List

1. **Order Manager Meta Access** - `class-wc-account-funds-order-manager.php:51, 115, 116, 144, 184, 211, 235, 346`
2. **Gateway Meta Updates** - `class-wc-gateway-account-funds.php:162-165, 170, 300, 312`
3. **Reports Post Queries** - `class-wc-account-funds-deposits-by-date.php:37, 58, 61`
4. **Legacy Meta Functions** - `class-wc-account-funds-order-manager.php:120, 132, 133, 202`
5. **Updater Meta Access** - `updates/class-wc-account-funds-updater-2.0.9.php:16, 18, 21, 25, 65, 67`

## Recommended Fixes

### High Priority (Required for HPOS Compatibility)

#### 1. Replace Legacy Meta Functions (Medium Difficulty)
```php
// Current (BROKEN in HPOS):
$funds_used = get_post_meta($order_id, '_funds_used', true);
update_post_meta($order_id, '_funds_used', $amount);

// Fix (HPOS Compatible):
$order = wc_get_order($order_id);
$funds_used = $order->get_meta('_funds_used');
$order->update_meta_data('_funds_used', $amount);
$order->save();
```

#### 2. Replace Reports Queries (Hard Difficulty)
```php
// Current (BROKEN in HPOS):
$orders = get_posts(array(
    'post_type' => 'shop_order',
    'meta_query' => array(/* ... */)
));

// Fix (HPOS Compatible):
$orders = wc_get_orders(array(
    'meta_query' => array(/* ... */),
    'date_created' => $date_range
));
```

#### 3. Fix Order Instantiation (Easy Difficulty)
```php
// Current:
$order = new WC_Order($order_id);

// Fix:
$order = wc_get_order($order_id);
```

#### 4. Add HPOS Declaration (Easy Difficulty)
Add to main plugin file after fixes are complete:
```php
if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
    \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
        'custom_order_tables',
        __FILE__,
        true
    );
}
```

## Limitations and Edge Cases

- Reports functionality will completely break in HPOS native mode
- Multiple order metadata fields require migration: `_funds_used`, `_funds_removed`, `_funds_deposited`, `_eot_id`, `_fq_account`, `_fq_account_name`
- No runtime testing performed due to lack of staging environment
- Plugin appears to be customized version (EOT branding, custom account balance fields)

## Testing Recommendations

1. **Before fixes:** Enable HPOS compatibility mode and test all plugin functions
2. **After fixes:** Test in HPOS native mode with data synchronization disabled
3. **Critical test areas:**
   - Account funds checkout process
   - Order completion and fund deduction
   - Account balance display and management
   - Reports and analytics
   - Order cancellation and fund restoration

## Conclusion

This plugin requires substantial development work to achieve HPOS compatibility. The fixes are technically feasible but require careful implementation to maintain data integrity. **Do not enable HPOS native mode** until all recommended fixes are implemented and thoroughly tested.

## References

- [WooCommerce HPOS Upgrade Recipe Book](https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book)
- [High Performance Order Storage Documentation](https://woocommerce.com/document/high-performance-order-storage/)