# WooCommerce HPOS Compatibility Audit Report

## Plugin Information
- **Plugin Name**: Price based on User Role for WooCommerce
- **Version**: 1.4
- **Author**: Tyche Softwares (Modified by Mightily)
- **Audit Date**: 2025-08-28

## Executive Summary

**Verdict**: `requires_changes`  
**Risk Level**: `medium`

The "Price based on User Role for WooCommerce" plugin requires modifications to be fully compatible with WooCommerce High-Performance Order Storage (HPOS). While the plugin uses some HPOS-safe APIs like `wc_get_order()`, it has several critical issues related to product metadata handling that need to be addressed before HPOS can be safely enabled.

## Detailed Findings

### ✅ HPOS-Compatible Practices Found
- Uses `wc_get_order()` for order retrieval (line 174 in functions file)
- No direct SQL queries on legacy wp_posts/wp_postmeta tables
- No admin UI assumptions about posts tables
- No problematic third-party dependencies

### ❌ HPOS Compatibility Issues

#### 1. Missing HPOS Compatibility Declaration
**Status**: Not Found  
**Impact**: Plugin won't be recognized as HPOS-compatible

#### 2. Legacy Product Metadata Access
**Files Affected**: 
- `/includes/class-alg-wc-price-by-user-role-core.php`
- `/includes/settings/class-alg-wc-price-by-user-role-settings-per-product.php`

**Issues Identified**:
- Direct `get_post_meta()` calls for product data (lines 146, 175, 180, 202, 226)
- `update_post_meta()` calls for saving plugin settings (line 138)
- Custom pricing metadata accessed via legacy post meta functions

## Evidence Details

### Code Issues Found

1. **Direct Price Metadata Access** (Line 146)
   ```php
   $the_price = get_post_meta( $child_id, '_price', true );
   ```
   Should use: `$product->get_price()`

2. **Plugin Settings Metadata** (Line 175)
   ```php
   if ( 'yes' === get_post_meta( alg_get_product_id_or_variation_parent_id( $_product ), '_alg_wc_price_by_user_role_per_product_settings_enabled', true ) )
   ```
   Should use: `$product->get_meta('_alg_wc_price_by_user_role_per_product_settings_enabled')`

3. **Role-Based Pricing Data** (Line 180)
   ```php
   $regular_price_per_product = get_post_meta( $_product_id, '_alg_wc_price_by_user_role_regular_price_' . $current_user_role, true );
   ```
   Should use: `$product->get_meta('_alg_wc_price_by_user_role_regular_price_' . $current_user_role)`

4. **Metadata Updates** (Line 138)
   ```php
   update_post_meta( $the_post_id, $the_meta_name, apply_filters( 'alg_wc_price_by_user_role_save_meta_box_value', $option_value, $option['name'] ) );
   ```
   Should use: `$product->update_meta_data($the_meta_name, $value); $product->save();`

## Compatibility Checklist

| Check | Status | Notes |
|-------|--------|-------|
| HPOS compatibility declared | ❌ | Missing declaration |
| Uses WC Data Stores for orders | ✅ | Uses wc_get_order() |
| Avoids wp_posts/wp_postmeta for orders | ✅ | No direct order post access |
| Avoids WP_Query on shop_order | ✅ | No problematic queries found |
| Uses wc_get_orders/OrderQuery | N/A | Plugin doesn't query orders |
| Order meta via WC API | ✅ | Limited order interaction |
| Handles custom order types | N/A | No custom order types |
| No direct SQL on legacy tables | ✅ | No raw SQL queries |
| No admin UI posts table assumptions | ✅ | No problematic admin hooks |
| No blocking 3rd party dependencies | ✅ | Self-contained plugin |

## Recommended Fixes

### Priority 1: Essential Changes

1. **Add HPOS Compatibility Declaration**
   - **Difficulty**: Easy
   - **Fix**: Add to main plugin file:
   ```php
   use Automattic\WooCommerce\Utilities\FeaturesUtil;
   
   add_action('before_woocommerce_init', function() {
       if (class_exists(FeaturesUtil::class)) {
           FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
       }
   });
   ```

2. **Update Product Metadata Access**
   - **Difficulty**: Medium
   - **Fix**: Replace all `get_post_meta($product_id, $key, true)` with `$product->get_meta($key)`

3. **Update Product Metadata Saving**
   - **Difficulty**: Medium  
   - **Fix**: Replace `update_post_meta($product_id, $key, $value)` with:
   ```php
   $product->update_meta_data($key, $value);
   $product->save();
   ```

4. **Fix Direct Price Access**
   - **Difficulty**: Easy
   - **Fix**: Replace `get_post_meta($child_id, '_price', true)` with `wc_get_product($child_id)->get_price()`

### Priority 2: Best Practices

1. **Product Object Retrieval Optimization**
   - Ensure product objects are properly retrieved before meta access
   - Add null checks for product objects

2. **Backward Compatibility**
   - Consider adding fallback logic for older WooCommerce versions

## Testing Recommendations

### Pre-Deployment Testing
1. **Compatibility Mode Testing**
   - Enable HPOS in compatibility mode
   - Test all pricing features with different user roles
   - Verify product variation pricing works correctly
   - Check grouped product pricing functionality

2. **Data Integrity Verification**
   - Run `wp wc hpos verify_data` command
   - Check for sync errors in WooCommerce logs
   - Verify pricing data is maintained correctly

3. **Functional Testing**
   - Test product price display for all user roles
   - Verify per-product pricing settings save correctly
   - Test global multiplier functionality
   - Check shipping price modifications work properly

## Risk Assessment

**Overall Risk**: Medium

**Reasoning**:
- Plugin primarily handles product pricing, not order data
- Core HPOS order functionality should not be severely impacted
- Product metadata issues could cause pricing discrepancies
- Plugin hasn't been updated since 2019, indicating potential maintenance concerns

## Limitations and Considerations

1. **Plugin Maintenance**: Last updated in 2019, may not receive active support
2. **Testing Environment**: Runtime testing not performed due to lack of staging environment
3. **Order Impact**: Minimal direct impact on order processing, mainly affects product display pricing
4. **Variation Support**: Plugin handles variable products, ensure compatibility is maintained

## Implementation Timeline

1. **Phase 1** (1-2 hours): Add HPOS compatibility declaration
2. **Phase 2** (4-6 hours): Update all product metadata access patterns
3. **Phase 3** (2-3 hours): Testing and validation in compatibility mode
4. **Phase 4** (1-2 hours): Final testing in native HPOS mode

## References

- [WooCommerce HPOS Documentation](https://woocommerce.com/document/high-performance-order-storage/)
- [HPOS Developer Guide](https://developer.woocommerce.com/2022/09/14/high-performance-order-storage-progress-update/)
- [WooCommerce Data Stores Documentation](https://woocommerce.github.io/code-reference/)

---

**Report Generated**: 2025-08-28  
**Audit Tool**: Claude Code HPOS Compatibility Analyzer