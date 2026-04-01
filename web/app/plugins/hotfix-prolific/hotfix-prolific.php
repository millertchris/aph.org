<?php

/**
 * Plugin Name:       Hotfix Plugin
 * Plugin URI:        https://prolificdigital.com/
 * Description:       A temporary plugin to apply a critical hotfix.
 * Version:           1.0.1
 * Author:            Prolific Digital
 * Author URI:        https://prolificdigital.com/
 * Text Domain:       my-hotfix-plugin
 * Domain Path:       /languages
 */

// Exit if accessed directly to prevent unauthorized access.
if (! defined('ABSPATH')) {
  exit;
}

/**
 * Hides the credit card payment option for specific user roles on the WooCommerce checkout.
 *
 * @param array $available_gateways An array of available payment gateways.
 * @return array Modified array of available payment gateways.
 */
function pd_hide_credit_card_for_specific_roles($available_gateways)
{
  // Check if WooCommerce is active and if we are on the checkout page.
  if (! class_exists('WooCommerce') || ! is_checkout()) {
    return $available_gateways;
  }

  // Get the current user object.
  $current_user = wp_get_current_user();

  // Define the roles for which the credit card option should be hidden.
  $restricted_roles = array('eot', 'eot-assistant', 'teacher');

  // Check if the current user has any of the restricted roles.
  $has_restricted_role = false;
  foreach ($restricted_roles as $role) {
    if (in_array($role, (array) $current_user->roles)) {
      $has_restricted_role = true;
      break;
    }
  }

  // If the user has a restricted role, unset the credit card gateway.
  // IMPORTANT: Replace 'stripe' with the actual ID of your credit card payment gateway.
  // Common IDs include 'stripe', 'paypal_pro', 'braintree', 'authorize_net_cim', etc.
  if ($has_restricted_role && isset($available_gateways['authorize_net_cim_credit_card'])) {
    unset($available_gateways['authorize_net_cim_credit_card']);
    // You can add error logging here for debugging purposes if needed.
    // error_log( 'Credit card option hidden for user ' . $current_user->user_login . ' with role: ' . implode(', ', (array) $current_user->roles) );
  }

  return $available_gateways;
}
add_filter('woocommerce_available_payment_gateways', 'pd_hide_credit_card_for_specific_roles');


function pad_remove_gforms_quotes($email)
{
  $headers = $email["headers"];
  $from = $headers["From"];
  $headers["From"] = str_replace('"', '', $from);
  $email["headers"] = $headers;
  return $email;
}
add_filter('gform_pre_send_email', 'pad_remove_gforms_quotes', 10, 4);

// Fix document item links that don't work on click
function fix_document_item_links()
{
?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle document-item-link clicks
      document.querySelectorAll('.accordion-wrapper a').forEach(function(link) {
        link.addEventListener('click', function(e) {
          // Prevent any event bubbling that might interfere
          e.stopPropagation();

          // Ensure the link navigates properly
          if (this.href) {
            window.location.href = this.href;
          }
        });
      });
    });
  </script>
  <?php
}
add_action('wp_footer', 'fix_document_item_links');




/**
 * Fixes the payment method dropdown issue in WooCommerce admin order edit.
 * 
 * This solution addresses a critical error that occurs when admins edit orders where
 * the payment method field is blank in the billing section. The issue is that the 
 * payment method dropdown doesn't include all payment methods that were actually used
 * for orders (like Account Funds, specific credit card gateways, etc.), causing the 
 * field to be blank and triggering critical errors when the order is saved.
 * 
 * The fix:
 * 1. Ensures ALL payment methods appear in the dropdown, including disabled ones if they were used
 * 2. Automatically selects the correct payment method that was used for the order
 * 3. Prevents the field from being blank which causes critical errors
 */

/**
 * Fix payment method dropdown in admin order edit page.
 * 
 * This function ensures that the payment method used for an order is always available
 * in the dropdown, even if the gateway is currently disabled. This prevents the field
 * from being blank and causing critical errors.
 * 
 * @param WC_Order $order The order being edited
 */
function pd_fix_payment_method_dropdown($order)
{
  // Get the payment method used for this order
  $order_payment_method = $order->get_payment_method();
  $order_payment_method_title = $order->get_payment_method_title();

  // Only add the script if there's a payment method set
  if (!empty($order_payment_method)) {
  ?>
    <script type="text/javascript">
      jQuery(document).ready(function($) {
        // Get the payment method dropdown
        var paymentSelect = $('#_payment_method');

        if (paymentSelect.length) {
          var currentPaymentMethod = '<?php echo esc_js($order_payment_method); ?>';
          var currentPaymentTitle = '<?php echo esc_js($order_payment_method_title); ?>';

          // Check if the current payment method exists in the dropdown
          if (!paymentSelect.find('option[value="' + currentPaymentMethod + '"]').length) {
            // Payment method not in dropdown, we need to add it

            // Determine the display title
            var displayTitle = currentPaymentTitle || currentPaymentMethod;

            // Special handling for known payment methods
            if (currentPaymentMethod === 'accountfunds') {
              displayTitle = 'Account Funds';
            } else if (currentPaymentMethod === 'authorize_net_cim_credit_card') {
              displayTitle = 'Credit Card';
            }

            // Add the option before "Other" if it exists, otherwise append
            var otherOption = paymentSelect.find('option[value="other"]');
            if (otherOption.length) {
              $('<option value="' + currentPaymentMethod + '">' + displayTitle + '</option>').insertBefore(otherOption);
            } else {
              paymentSelect.append('<option value="' + currentPaymentMethod + '">' + displayTitle + '</option>');
            }
          }

          // Select the correct payment method
          paymentSelect.val(currentPaymentMethod);

          // If still no value selected (shouldn't happen), try to set it again after a delay
          if (!paymentSelect.val() && currentPaymentMethod) {
            setTimeout(function() {
              paymentSelect.val(currentPaymentMethod);
            }, 100);
          }
        }
      });
    </script>
  <?php
  }
}
add_action('woocommerce_admin_order_data_after_billing_address', 'pd_fix_payment_method_dropdown', 20);

/**
 * Ensure payment method is properly saved when orders are created or updated.
 * 
 * This function ensures that the payment method is correctly stored in the order
 * metadata when orders are created through checkout.
 * 
 * @param int $order_id The ID of the order being created
 * @param array $posted_data The posted data from checkout
 */
function pd_ensure_payment_method_saved($order_id, $posted_data)
{
  $order = wc_get_order($order_id);

  if (!$order) {
    return;
  }

  // Get the payment method
  $payment_method = $order->get_payment_method();

  // Ensure payment method title is set if method exists
  if ($payment_method && !$order->get_payment_method_title()) {
    // Try to get the payment gateway to get proper title
    $payment_gateways = WC()->payment_gateways->payment_gateways();

    if (isset($payment_gateways[$payment_method])) {
      $gateway = $payment_gateways[$payment_method];
      $order->set_payment_method_title($gateway->get_title());
    } else {
      // Set a fallback title based on the payment method ID
      $fallback_titles = array(
        'accountfunds' => __('Account Funds', 'woocommerce-account-funds'),
        'authorize_net_cim_credit_card' => __('Credit Card', 'woocommerce'),
        'paypal' => __('PayPal', 'woocommerce'),
        'stripe' => __('Credit Card (Stripe)', 'woocommerce'),
        'bacs' => __('Bank Transfer', 'woocommerce'),
        'cheque' => __('Check Payment', 'woocommerce'),
        'cod' => __('Cash on Delivery', 'woocommerce'),
      );

      $title = isset($fallback_titles[$payment_method])
        ? $fallback_titles[$payment_method]
        : ucwords(str_replace('_', ' ', $payment_method));

      $order->set_payment_method_title($title);
    }

    $order->save();
  }
}
add_action('woocommerce_checkout_update_order_meta', 'pd_ensure_payment_method_saved', 20, 2);

/**
 * Validate and fix payment method on order save in admin.
 * 
 * This prevents critical errors by ensuring a valid payment method is always set
 * when an order is saved from the admin panel.
 * 
 * @param int $order_id The order ID being saved
 */
function pd_validate_payment_method_on_save($order_id)
{
  // Only run in admin
  if (!is_admin()) {
    return;
  }

  // Check if we're saving from the order edit screen
  if (isset($_POST['_payment_method'])) {
    $order = wc_get_order($order_id);

    if (!$order) {
      return;
    }

    $payment_method = sanitize_text_field($_POST['_payment_method']);

    // If payment method is empty or 'other', try to use the existing one
    if (empty($payment_method) || $payment_method === 'other') {
      $existing_method = $order->get_payment_method();

      // If there's an existing method, keep it
      if (!empty($existing_method)) {
        $_POST['_payment_method'] = $existing_method;
      }
    }
  }
}
add_action('woocommerce_process_shop_order_meta', 'pd_validate_payment_method_on_save', 5);

/**
 * Populate the payment method dropdown with ALL registered payment gateways.
 * 
 * This function completely rebuilds the payment method dropdown to include:
 * - All currently enabled payment gateways
 * - All disabled payment gateways 
 * - Historical payment methods that might have been used
 * 
 * This ensures admins can select any payment gateway when creating or editing orders,
 * preventing blank payment method fields that cause critical errors.
 */
function pd_populate_all_payment_methods_dropdown()
{
  // Only run in admin on order edit/create pages
  if (!is_admin()) {
    return;
  }

  global $post, $theorder;

  // Check if we're on an order edit page
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || !in_array($screen->id, array('shop_order', 'woocommerce_page_wc-orders'))) {
    return;
  }

  ?>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      var isPopulating = false;
      var lastPopulateTime = 0;

      // Function to populate the payment methods
      function populatePaymentMethods() {
        // Prevent multiple simultaneous executions
        if (isPopulating) {
          return false;
        }

        // Throttle - don't run more than once per second
        var now = Date.now();
        if (now - lastPopulateTime < 1000) {
          return false;
        }

        var paymentSelect = $('#_payment_method');

        if (!paymentSelect.length || !paymentSelect.is(':visible')) {
          return false;
        }

        // Check if already has Account Funds option (our marker that it's populated)
        if (paymentSelect.find('option[value="accountfunds"]').length > 0) {
          return true;
        }

        isPopulating = true;
        lastPopulateTime = now;

        // Store the currently selected value
        var currentValue = paymentSelect.val();

        // Clear existing options
        paymentSelect.empty();

        // Add options in specific order
        paymentSelect.append('<option value=""><?php echo esc_js(__('N/A', 'woocommerce')); ?></option>');

        <?php
        // Check if Credit Card gateway is enabled
        if (WC()->payment_gateways()) {
          $payment_gateways = WC()->payment_gateways->payment_gateways();
          if (isset($payment_gateways['authorize_net_cim_credit_card']) && $payment_gateways['authorize_net_cim_credit_card']->enabled === 'yes') {
            echo "paymentSelect.append('<option value=\"authorize_net_cim_credit_card\">Credit Card</option>');\n";
          }
        }
        ?>

        // Always add Account Funds
        paymentSelect.append('<option value="accountfunds">Account Funds</option>');

        // Add Other option
        paymentSelect.append('<option value="other"><?php echo esc_js(__('Other', 'woocommerce')); ?></option>');

        <?php
        // If editing existing order, ensure its payment method is available
        if (isset($theorder) && $theorder) {
          $order_payment_method = $theorder->get_payment_method();
          $order_payment_method_title = $theorder->get_payment_method_title();

          if (!empty($order_payment_method) && !in_array($order_payment_method, array('authorize_net_cim_credit_card', 'accountfunds', 'other', ''))) {
            echo "if (!paymentSelect.find('option[value=\"" . esc_js($order_payment_method) . "\"]').length) {\n";
            echo "    paymentSelect.find('option[value=\"other\"]').before('<option value=\"" . esc_js($order_payment_method) . "\">" . esc_js($order_payment_method_title ?: $order_payment_method) . "</option>');\n";
            echo "}\n";
            echo "currentValue = '" . esc_js($order_payment_method) . "';\n";
          }
        }
        ?>

        // Restore the selected value
        if (currentValue) {
          paymentSelect.val(currentValue);
        }

        isPopulating = false;
        return true;
      }

      // Initial population on page load
      setTimeout(function() {
        populatePaymentMethods();
      }, 500);

      // Watch for when billing section is edited
      $(document).on('click', '.edit_address', function() {
        setTimeout(function() {
          populatePaymentMethods();
        }, 200);
      });

      // Only watch for specific WooCommerce AJAX operations
      $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings && settings.data && typeof settings.data === 'string') {
          if (settings.data.indexOf('woocommerce_add_order_item') !== -1 ||
            settings.data.indexOf('woocommerce_remove_order_item') !== -1 ||
            settings.data.indexOf('woocommerce_calc_line_taxes') !== -1) {
            setTimeout(function() {
              populatePaymentMethods();
            }, 500);
          }
        }
      });
    });
  </script>
<?php
}
add_action('admin_footer', 'pd_populate_all_payment_methods_dropdown');

/**
 * Query database for all unique payment methods ever used.
 * 
 * This function helps identify payment methods that might have been used historically
 * but are no longer registered or enabled.
 */
function pd_get_all_used_payment_methods()
{
  global $wpdb;

  // Query for all unique payment methods used in orders
  $payment_methods = $wpdb->get_results("
        SELECT DISTINCT meta_value as payment_method
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_payment_method'
        AND meta_value != ''
        AND meta_value IS NOT NULL
    ");

  $methods = array();
  foreach ($payment_methods as $method) {
    $methods[$method->payment_method] = $method->payment_method;
  }

  return $methods;
}

/**
 * Intercept order creation to ensure payment method is never empty.
 * 
 * This validation hook runs BEFORE the order is processed, intercepting the form submission
 * when an admin clicks "Create" on a new order. If no payment method is selected (N/A or empty),
 * it automatically sets it to "other" to prevent critical errors.
 * 
 * This is the earliest possible intervention point - right when $_POST data is submitted.
 * 
 * @param int $order_id The order ID being created
 */
function pd_intercept_empty_payment_method_on_create($order_id)
{
  // Only run in admin and when we have POST data
  if (!is_admin() || empty($_POST)) {
    return;
  }

  // Check if this is a new order creation (action = 'editpost' with no previous status)
  $is_new_order = isset($_POST['action']) && $_POST['action'] === 'editpost'
    && isset($_POST['post_type']) && $_POST['post_type'] === 'shop_order'
    && isset($_POST['original_post_status']) && $_POST['original_post_status'] === 'auto-draft';

  if (!$is_new_order) {
    return;
  }

  // Check if payment method is empty or explicitly set to empty (N/A option)
  if (!isset($_POST['_payment_method']) || $_POST['_payment_method'] === '' || $_POST['_payment_method'] === null) {
    // Set to 'other' before WooCommerce processes the order
    $_POST['_payment_method'] = 'other';

    // Also ensure the payment method title is set
    if (!isset($_POST['_payment_method_title']) || empty($_POST['_payment_method_title'])) {
      $_POST['_payment_method_title'] = 'Other';
    }

    // Log this intervention for debugging (optional)
    if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('PD Hotfix: Intercepted empty payment method on new order #' . $order_id . ' - set to "other"');
    }
  }
}
// Hook with priority 1 to run VERY early, before WooCommerce processes the data
add_action('woocommerce_process_shop_order_meta', 'pd_intercept_empty_payment_method_on_create', 1);

/**
 * Additional safety: Validate payment method right before order object is saved.
 * 
 * This is a secondary defense that catches any cases where payment method might still be empty.
 * It runs right before the order object is saved to the database.
 * 
 * @param WC_Order $order The order object about to be saved
 */
function pd_validate_payment_method_before_save($order)
{
  // Only in admin context
  if (!is_admin()) {
    return;
  }

  // If payment method is empty, set it to 'other'
  if (!$order->get_payment_method()) {
    $order->set_payment_method('other');
    $order->set_payment_method_title('Other');

    // Log for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
      error_log('PD Hotfix: Fixed empty payment method before save for order #' . $order->get_id());
    }
  }
}
// This hook fires right before any order save operation
add_action('woocommerce_before_order_object_save', 'pd_validate_payment_method_before_save', 10);



// =========================================================================
// FQ ACCOUNT FIELD PERSISTENCE FIX
// WORDPRESS 6.9 UPDATE START - December 2024
// Issue: FQ Account not displaying in orders table after WP 6.9 update with HPOS
// =========================================================================

/**
 * ISSUE: FQ Account Value Not Saved During Order Creation
 * 
 * PROBLEM DESCRIPTION:
 * When creating new WooCommerce orders in admin:
 * 1. Customer is selected correctly ✅
 * 2. FQ Account dropdown populates correctly ✅  
 * 3. Admin selects an FQ Account value ✅
 * 4. Clicks "Create" to save the order ✅
 * 5. After page reload, FQ Account field shows "Not set" ❌
 * 
 * ROOT CAUSE:
 * The FQ Account field value is not being properly saved during the order creation process.
 * The theme's save_order_fq_account() function may not be executing or the POST data
 * is not being captured correctly during order creation.
 * 
 * SOLUTION:
 * Hook into the order save process to ensure FQ Account data is properly captured
 * and saved to order meta data during both order creation and updates.
 * 
 * TECHNICAL IMPLEMENTATION:
 * - Hooks into woocommerce_process_shop_order_meta with high priority (50)
 * - Captures fq_account POST data during form submission
 * - Retrieves FQ account name from taxonomy term
 * - Saves both _fq_account (ID) and _fq_account_name to order meta
 * - Includes debug logging when WP_DEBUG is enabled
 */

/**
 * Ensure FQ Account is properly saved during order creation and updates.
 * 
 * This function hooks into the order save process to capture the FQ Account
 * value from the form submission and save it to order meta data.
 * Enhanced for WordPress 6.9 and HPOS compatibility.
 * 
 * WORDPRESS 6.9 UPDATE - December 2024:
 * - Added support for both order ID and order object parameters
 * - Changed from update_post_meta to order->update_meta_data for HPOS
 * - Added proper meta key prefix (_fq_account_name) for orders table display
 * - Added new HPOS hooks: woocommerce_new_order, woocommerce_update_order
 * - Fixed clearing of FQ Account when "Not Set" is selected
 * 
 * @param int|WC_Order $order_id_or_order The order ID (legacy) or order object (HPOS)
 */
function pd_ensure_fq_account_saved($order_id_or_order)
{
  // Only run in admin
  if (!is_admin()) {
    return;
  }

  // Handle both order ID (legacy) and order object (HPOS)
  if (is_object($order_id_or_order)) {
    // HPOS: Order object passed directly
    $order = $order_id_or_order;
    $order_id = $order->get_id();
  } else {
    // Legacy: Order ID passed
    $order_id = $order_id_or_order;
    $order = wc_get_order($order_id);
  }

  // Check if FQ Account was submitted
  if (!isset($_POST['fq_account'])) {
    return;
  }

  $fq_account_id = sanitize_text_field($_POST['fq_account']);

  // Handle "Not Set" - clear the FQ account data
  if ($fq_account_id === '-1' || empty($fq_account_id)) {
    if ($order) {
      $order->update_meta_data('_fq_account', '');
      $order->update_meta_data('_fq_account_name', '');
      $order->save_meta_data();
    } else {
      update_post_meta($order_id, '_fq_account', '');
      update_post_meta($order_id, '_fq_account_name', '');
    }
    return;
  }

  // Get the FQ account name from taxonomy
  $fq_account_name = '';
  $term = get_term_by('term_taxonomy_id', $fq_account_id);
  if ($term && !is_wp_error($term)) {
    $fq_account_name = $term->name;
  }

  // Save using the order object method (works for both legacy and HPOS)
  if ($order) {
    $order->update_meta_data('_fq_account', $fq_account_id);
    $order->update_meta_data('_fq_account_name', $fq_account_name);
    $order->save_meta_data();
  } else {
    // Fallback to direct meta update
    update_post_meta($order_id, '_fq_account', $fq_account_id);
    update_post_meta($order_id, '_fq_account_name', $fq_account_name);
  }

  // Log the save operation for debugging
  if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('PD Hotfix: Saved FQ Account for order #' . $order_id . ' - ID: ' . $fq_account_id . ', Name: ' . $fq_account_name);
  }
}
// Hook with high priority to ensure it runs after theme functions
// Enhanced with HPOS-compatible hooks for WordPress 6.9
add_action('woocommerce_process_shop_order_meta', 'pd_ensure_fq_account_saved', 50, 2);
add_action('woocommerce_new_order', 'pd_ensure_fq_account_saved', 50);
add_action('woocommerce_update_order', 'pd_ensure_fq_account_saved', 50);
add_action('save_post_shop_order', 'pd_ensure_fq_account_saved', 50);

/**
 * Fix FQ Account field persistence in admin order edit.
 * 
 * This function ensures that when editing existing orders, the FQ Account
 * field shows the correct saved value instead of resetting to "Not set".
 */
function pd_fix_fq_account_persistence()
{
  // Only run in admin on order edit pages
  if (!is_admin()) {
    return;
  }

  global $post, $theorder;

  // Check if we're on an order edit page
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || !in_array($screen->id, array('shop_order', 'woocommerce_page_wc-orders'))) {
    return;
  }

  // Get the order object
  $order = null;
  if (isset($theorder) && $theorder) {
    $order = $theorder;
  } elseif (isset($post) && $post && $post->post_type === 'shop_order') {
    $order = wc_get_order($post->ID);
  }

  if (!$order) {
    return;
  }

  // Get the saved FQ Account values
  $fq_account_id = $order->get_meta('_fq_account');
  $fq_account_name = $order->get_meta('_fq_account_name');
  $customer_id = $order->get_customer_id();

?>
  <script type="text/javascript">
    jQuery(document).ready(function($) {
      // Function to populate FQ Account dropdown for existing orders
      function populateFqAccountForExistingOrder() {
        var fqSelect = $('#fq_account');

        if (!fqSelect.length) {
          // FQ Account field doesn't exist yet, try again later
          setTimeout(populateFqAccountForExistingOrder, 500);
          return;
        }

        <?php if ($customer_id && $fq_account_id && $fq_account_id !== '-1'): ?>
          // We have a customer and a saved FQ Account
          var savedFqAccountId = '<?php echo esc_js($fq_account_id); ?>';
          var savedFqAccountName = '<?php echo esc_js($fq_account_name); ?>';
          var customerId = '<?php echo esc_js($customer_id); ?>';

          // Make AJAX call to get FQ accounts for this customer
          $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
              action: 'pd_get_customer_fq_accounts',
              user_id: customerId,
              security: '<?php echo wp_create_nonce('pd_fq_accounts'); ?>'
            },
            success: function(response) {
              if (response && response.success && response.fq_options) {
                // Clear and repopulate the FQ Account dropdown
                fqSelect.empty();
                fqSelect.append($('<option></option>').attr('value', '-1').text('Not Set'));

                $.each(response.fq_options, function(key, value) {
                  if (key != '-1') {
                    fqSelect.append($('<option></option>').attr('value', key).text(value));
                  }
                });

                // Set the saved value
                if (savedFqAccountId && savedFqAccountId !== '-1') {
                  // Check if the saved option exists
                  if (fqSelect.find('option[value="' + savedFqAccountId + '"]').length === 0) {
                    // Add the saved option if it doesn't exist
                    fqSelect.append($('<option></option>').attr('value', savedFqAccountId).text(savedFqAccountName || 'FQ Account'));
                  }
                  // Select the saved value
                  fqSelect.val(savedFqAccountId);
                }
              }
            },
            error: function() {
              // On error, at least ensure the saved value is available
              if (savedFqAccountId && savedFqAccountId !== '-1') {
                if (fqSelect.find('option[value="' + savedFqAccountId + '"]').length === 0) {
                  fqSelect.append($('<option></option>').attr('value', savedFqAccountId).text(savedFqAccountName || 'FQ Account'));
                }
                fqSelect.val(savedFqAccountId);
              }
            }
          });
        <?php elseif ($fq_account_id && $fq_account_id !== '-1'): ?>
          // No customer but we have a saved FQ Account (shouldn't happen but handle it)
          var savedFqAccountId = '<?php echo esc_js($fq_account_id); ?>';
          var savedFqAccountName = '<?php echo esc_js($fq_account_name); ?>';

          if (fqSelect.find('option[value="' + savedFqAccountId + '"]').length === 0) {
            fqSelect.append($('<option></option>').attr('value', savedFqAccountId).text(savedFqAccountName || 'FQ Account'));
          }
          fqSelect.val(savedFqAccountId);
        <?php endif; ?>
      }

      // Initial population
      setTimeout(populateFqAccountForExistingOrder, 100);

      // Also populate when customer field changes (in case admin changes customer)
      $(document).on('select2:select', ':input.wc-customer-search', function(e) {
        // The existing JavaScript in wc-enhanced-select-override.js handles this
        // But we ensure our saved value is restored if needed
        setTimeout(function() {
          var fqSelect = $('#fq_account');
          <?php if ($fq_account_id && $fq_account_id !== '-1'): ?>
            // If this is the original customer being re-selected, restore the FQ Account
            if (e.params.data.id == '<?php echo esc_js($customer_id); ?>') {
              var savedFqAccountId = '<?php echo esc_js($fq_account_id); ?>';
              if (fqSelect.val() != savedFqAccountId) {
                fqSelect.val(savedFqAccountId);
              }
            }
          <?php endif; ?>
        }, 200);
      });
    });
  </script>
<?php
}
add_action('admin_footer', 'pd_fix_fq_account_persistence');

/**
 * Safe function to get user FQ accounts.
 * 
 * This function safely retrieves FQ accounts for a user without the PHP fatal errors
 * that occur in the theme's version.
 */
function pd_get_user_fq_accounts_fixed($user_id)
{
  $fq_accounts = array();

  if (!$user_id) {
    return $fq_accounts;
  }

  // Get user groups (FQ accounts are stored as user groups)
  $user_groups = wp_get_object_terms($user_id, 'user-group', array('fields' => 'all'));

  if (is_wp_error($user_groups) || empty($user_groups)) {
    return $fq_accounts;
  }

  $x = 0;
  foreach ($user_groups as $term) {
    // CRITICAL FIX: Create the object BEFORE assigning properties
    $fq_accounts[$x] = new stdClass();
    $fq_accounts[$x]->fq_account_id = $term->term_id;
    $fq_accounts[$x]->fq_account_name = $term->name;
    $fq_accounts[$x]->fq_account_slug = $term->slug;
    $x++;
  }

  return $fq_accounts;
}

/**
 * AJAX endpoint for getting customer FQ accounts.
 * 
 * This endpoint is used by the FQ Account persistence fix to fetch
 * available FQ accounts for a specific customer when editing orders.
 */
function pd_ajax_get_customer_fq_accounts()
{
  // Verify nonce for security
  if (!wp_verify_nonce($_POST['security'], 'pd_fq_accounts')) {
    wp_send_json_error('Invalid security token');
  }

  $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

  if (!$user_id) {
    wp_send_json_error('No user ID provided');
  }

  // Use our fixed function to get FQ accounts
  $fq_accounts_objects = pd_get_user_fq_accounts_fixed($user_id);

  // Convert to the format expected by the frontend
  $fq_options = array('-1' => 'Not Set');

  if (!empty($fq_accounts_objects)) {
    foreach ($fq_accounts_objects as $fq_account) {
      if (isset($fq_account->fq_account_id) && isset($fq_account->fq_account_name)) {
        $fq_options[$fq_account->fq_account_id] = $fq_account->fq_account_name;
      }
    }
  }

  wp_send_json(array(
    'fq_options' => $fq_options,
    'success' => true
  ));
}
add_action('wp_ajax_pd_get_customer_fq_accounts', 'pd_ajax_get_customer_fq_accounts');
// =========================================================================
// END WORDPRESS 6.9 UPDATE - FQ ACCOUNT FIELD PERSISTENCE FIX
// =========================================================================


// =========================================================================
// PO NUMBER FIELD PERSISTENCE FIX
// WORDPRESS 6.9 UPDATE START - December 2024
// Issue: PO Number field not saving after WP 6.9 update with HPOS compatibility
// =========================================================================

/**
 * ISSUE: PO Number Value Not Saved During Admin Order Creation
 * 
 * PROBLEM DESCRIPTION:
 * When creating new WooCommerce orders in admin:
 * 1. Admin fills in the PO Number field ✅
 * 2. Clicks "Create" to save the order ✅
 * 3. After page reload, PO Number field is empty ❌
 * 4. The PO Number value is not saved to database ❌
 * 
 * ROOT CAUSE:
 * The PO Number field value is not being properly captured and saved during 
 * the admin order creation process. This only happens in production environment,
 * potentially due to timing issues or conflicts with the FQ Account persistence fix.
 * 
 * SOLUTION:
 * Hook into the order save process with high priority to ensure PO Number data 
 * is properly captured from POST data and saved to order meta during both 
 * order creation and updates in admin.
 * 
 * TECHNICAL IMPLEMENTATION:
 * - Hooks into woocommerce_process_shop_order_meta with priority 60 (after FQ Account fix)
 * - Also hooks into save_post_shop_order as a fallback
 * - Captures po_number POST data during form submission
 * - Saves to 'PO Number' meta key (matching theme's implementation)
 * - Includes debug logging when WP_DEBUG is enabled
 */

/**
 * Ensure PO Number is properly saved during admin order creation and updates.
 * 
 * This function hooks into the order save process to capture the PO Number
 * value from the form submission and save it to order meta data.
 * Enhanced for WordPress 6.9 and HPOS compatibility.
 * 
 * WORDPRESS 6.9 UPDATE - December 2024:
 * - Added support for both order ID and order object parameters
 * - Changed from update_post_meta to order->update_meta_data for HPOS
 * - Added new HPOS hooks: woocommerce_new_order, woocommerce_update_order
 * 
 * @param int|WC_Order $order_id_or_order The order ID (legacy) or order object (HPOS)
 */
function pd_ensure_po_number_saved($order_id_or_order) {
    // Only run in admin
    if (!is_admin()) {
        return;
    }
    
    // Handle both order ID (legacy) and order object (HPOS)
    if (is_object($order_id_or_order)) {
        // HPOS: Order object passed directly
        $order = $order_id_or_order;
        $order_id = $order->get_id();
    } else {
        // Legacy: Order ID passed
        $order_id = $order_id_or_order;
        $order = wc_get_order($order_id);
    }
    
    // Check if PO Number was submitted
    if (!isset($_POST['po_number'])) {
        return;
    }
    
    // Get the PO Number value
    $po_number = sanitize_text_field($_POST['po_number']);
    
    // Save using the order object method (works for both legacy and HPOS)
    if ($order) {
        $order->update_meta_data('PO Number', $po_number);
        $order->save_meta_data();
    } else {
        // Fallback to direct meta update
        update_post_meta($order_id, 'PO Number', $po_number);
    }
    
    // Log the save operation for debugging
    if (defined('WP_DEBUG') && WP_DEBUG && !empty($po_number)) {
        error_log('PD Hotfix: Saved PO Number for order #' . $order_id . ' - Value: ' . $po_number);
    }
}

// Hook with priority 60 to ensure it runs after FQ Account fix (priority 50)
// Enhanced with HPOS-compatible hooks for WordPress 6.9
add_action('woocommerce_process_shop_order_meta', 'pd_ensure_po_number_saved', 60, 2);
add_action('woocommerce_new_order', 'pd_ensure_po_number_saved', 60);
add_action('woocommerce_update_order', 'pd_ensure_po_number_saved', 60);
add_action('save_post_shop_order', 'pd_ensure_po_number_saved', 60);

/**
 * Additional validation to ensure PO Number persists through AJAX operations.
 * 
 * When admin edits orders and various AJAX calls are made (add items, calculate taxes, etc),
 * we need to ensure the PO Number value is maintained.
 * 
 * @param WC_Order $order The order object
 */
function pd_maintain_po_number_on_ajax($order) {
    // Only in admin context
    if (!is_admin()) {
        return;
    }
    
    // Check if this is an AJAX request that might affect the order
    if (defined('DOING_AJAX') && DOING_AJAX) {
        // Get the existing PO Number from the order
        $existing_po_number = $order->get_meta('PO Number');
        
        // If we have a PO Number in POST data, use that; otherwise keep existing
        if (isset($_POST['po_number'])) {
            $new_po_number = sanitize_text_field($_POST['po_number']);
            if ($new_po_number !== $existing_po_number) {
                $order->update_meta_data('PO Number', $new_po_number);
                $order->save();
            }
        }
    }
}
add_action('woocommerce_before_order_object_save', 'pd_maintain_po_number_on_ajax', 20);
// =========================================================================
// END WORDPRESS 6.9 UPDATE - PO NUMBER FIELD PERSISTENCE FIX
// =========================================================================




// =========================================================================
// FIX 10: SYSPRO ORDER NUMBER REST API SAVE FIX (HPOS COMPATIBILITY)
// Added: 2026-01-15
// =========================================================================

/**
 * ISSUE: SYSPRO Order Number Not Being Saved via REST API
 *
 * CONTEXT:
 * After WordPress 6.9 update with HPOS (High-Performance Order Storage) enabled,
 * new orders are not receiving SYSPRO order numbers from the external system.
 *
 * ROOT CAUSE:
 * The theme's REST API update_callback (Order.php:103-106) uses $object->ID
 * to get the order ID. With HPOS, $object is an array, so $object->ID is null.
 * This causes a FATAL ERROR in the APH\Order constructor when it tries to call
 * ->get_id() on null. The fatal error crashes the entire REST API request,
 * preventing SYSPRO numbers from being saved.
 *
 * SOLUTION:
 * 1. Remove the theme's broken REST field registration
 * 2. Re-register with a working HPOS-compatible callback
 *
 * META KEY: syspro_order_number (no underscore prefix)
 */

/**
 * Remove the theme's broken REST API field and re-register with working callback
 *
 * The theme registers the syspro_order_number field with a broken update_callback
 * that causes fatal errors with HPOS. We need to remove it and add a working one.
 */
function pd_fix_syspro_rest_field_registration() {
    global $wp_rest_additional_fields;

    // Remove the theme's broken registration if it exists
    if ( isset( $wp_rest_additional_fields['shop_order']['syspro_order_number'] ) ) {
        unset( $wp_rest_additional_fields['shop_order']['syspro_order_number'] );
    }

    // Re-register with HPOS-compatible callbacks
    register_rest_field( 'shop_order', 'syspro_order_number', array(
        'get_callback' => function( $object ) {
            // $object is an array with order data
            $order_id = isset( $object['id'] ) ? absint( $object['id'] ) : 0;
            if ( ! $order_id ) {
                return '--';
            }

            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                return '--';
            }

            $syspro_id = $order->get_meta( 'syspro_order_number' );
            return ! empty( $syspro_id ) ? $syspro_id : '--';
        },
        'update_callback' => function( $value, $object, $field_name ) {
            // $object can be array or WC_Order depending on context
            $order_id = 0;

            if ( is_array( $object ) && isset( $object['id'] ) ) {
                $order_id = absint( $object['id'] );
            } elseif ( is_object( $object ) ) {
                if ( method_exists( $object, 'get_id' ) ) {
                    $order_id = $object->get_id();
                } elseif ( isset( $object->ID ) ) {
                    $order_id = absint( $object->ID );
                } elseif ( isset( $object->id ) ) {
                    $order_id = absint( $object->id );
                }
            }

            if ( ! $order_id ) {
                error_log( 'PD Hotfix: SYSPRO REST API update failed - could not determine order ID' );
                return false;
            }

            $order = wc_get_order( $order_id );
            if ( ! $order ) {
                error_log( 'PD Hotfix: SYSPRO REST API update failed - order not found: ' . $order_id );
                return false;
            }

            $sanitized_value = sanitize_text_field( $value );
            $order->update_meta_data( 'syspro_order_number', $sanitized_value );
            $order->save();

            error_log( 'PD Hotfix: Saved SYSPRO order number via REST API for order #' . $order_id . ' - Value: ' . $sanitized_value );

            return true;
        },
        'schema' => array(
            'description' => __( 'SYSPRO Order Number' ),
            'type'        => 'string',
            'context'     => array( 'view', 'edit' ),
        ),
    ) );
}
// Run at priority 99 to ensure it runs AFTER the theme's registration
add_action( 'rest_api_init', 'pd_fix_syspro_rest_field_registration', 99 );

// =========================================================================
// END FIX 10: SYSPRO ORDER NUMBER REST API SAVE FIX (HPOS COMPATIBILITY)
// =========================================================================

// =========================================================================
// FIX 11: SYSPRO ORDER NUMBER ADMIN DISPLAY FIX (HPOS META FALLBACK)
// Added: 2026-01-15
// =========================================================================

/**
 * ISSUE: SYSPRO Order Number Shows in Column But Not on Edit Order Page
 *
 * CONTEXT:
 * On production with HPOS enabled, SYSPRO numbers appear in the orders list
 * column but show "--" on the edit order page. This inconsistency occurs
 * because:
 * 1. Some orders have SYSPRO data only in legacy wp_postmeta (pre-HPOS)
 * 2. Some orders have SYSPRO data only in wp_wc_orders_meta (HPOS)
 * 3. The WC_Order::get_meta() method only checks the active data store
 *
 * ROOT CAUSE:
 * When HPOS is enabled, $order->get_meta('syspro_order_number') only queries
 * the wp_wc_orders_meta table. If the data exists in wp_postmeta (legacy)
 * but wasn't migrated, it won't be found. Different contexts (list vs edit)
 * may load order objects differently, causing inconsistent results.
 *
 * SOLUTION:
 * 1. Filter get_meta calls for syspro_order_number
 * 2. If HPOS returns empty, check legacy wp_postmeta as fallback
 * 3. If found in legacy, optionally sync to HPOS for future consistency
 *
 * META KEY: syspro_order_number (no underscore prefix)
 */

/**
 * Add fallback to legacy postmeta for SYSPRO order number retrieval
 *
 * This filter intercepts $order->get_meta('syspro_order_number') calls and
 * provides a fallback to the legacy wp_postmeta table when HPOS is enabled
 * but the data hasn't been migrated.
 *
 * @param mixed  $value    The meta value (empty if not found in HPOS)
 * @param object $order    The WC_Order object
 * @param string $meta_key The meta key being retrieved
 * @param bool   $single   Whether to return a single value
 * @param string $context  The context (view or edit)
 * @return mixed           The meta value with legacy fallback
 */
function pd_syspro_meta_hpos_fallback( $value, $order, $meta_key, $single, $context ) {
    // Only intercept syspro_order_number meta key
    if ( $meta_key !== 'syspro_order_number' ) {
        return $value;
    }

    // If we already have a value, no need for fallback
    if ( ! empty( $value ) ) {
        return $value;
    }

    // Check if HPOS is enabled
    $hpos_enabled = false;
    if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
        $hpos_enabled = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    // Only apply fallback when HPOS is enabled
    if ( ! $hpos_enabled ) {
        return $value;
    }

    // Get order ID
    $order_id = 0;
    if ( method_exists( $order, 'get_id' ) ) {
        $order_id = $order->get_id();
    }

    if ( ! $order_id ) {
        return $value;
    }

    // Check legacy wp_postmeta table directly
    global $wpdb;
    $legacy_value = $wpdb->get_var( $wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = %s LIMIT 1",
        $order_id,
        'syspro_order_number'
    ) );

    if ( ! empty( $legacy_value ) ) {
        // Found in legacy postmeta - sync to HPOS for future consistency
        // Use update_meta_data to write to HPOS tables
        if ( method_exists( $order, 'update_meta_data' ) ) {
            // Prevent infinite loop by removing this filter temporarily
            remove_filter( 'woocommerce_order_get_meta', 'pd_syspro_meta_hpos_fallback', 5 );

            $order->update_meta_data( 'syspro_order_number', $legacy_value );
            $order->save_meta_data();

            // Re-add the filter
            add_filter( 'woocommerce_order_get_meta', 'pd_syspro_meta_hpos_fallback', 5, 5 );

            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'PD Hotfix: Synced SYSPRO from legacy postmeta to HPOS for order #' . $order_id . ' - Value: ' . $legacy_value );
            }
        }

        return $legacy_value;
    }

    // Also check wp_wc_orders_meta directly in case WC's get_meta isn't finding it
    $hpos_value = $wpdb->get_var( $wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}wc_orders_meta WHERE order_id = %d AND meta_key = %s LIMIT 1",
        $order_id,
        'syspro_order_number'
    ) );

    if ( ! empty( $hpos_value ) ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'PD Hotfix: Found SYSPRO in HPOS meta via direct query for order #' . $order_id . ' - Value: ' . $hpos_value );
        }
        return $hpos_value;
    }

    return $value;
}
// Hook at priority 5 to run before most other filters
add_filter( 'woocommerce_order_get_meta', 'pd_syspro_meta_hpos_fallback', 5, 5 );

// =========================================================================
// END FIX 11: SYSPRO ORDER NUMBER ADMIN DISPLAY FIX (HPOS META FALLBACK)
// =========================================================================













// =========================================================================
// SYSPRO ORDER NUMBER PERSISTENCE FIX
// WORDPRESS 6.9 UPDATE START - December 2024
// Issue: SYSPRO Order numbers not showing in orders table after WP 6.9 update
// =========================================================================

/**
 * ISSUE: SYSPRO Order Number Not Displaying After WordPress 6.9 Update
 * 
 * PROBLEM DESCRIPTION:
 * After WordPress 6.9 update with HPOS (High-Performance Order Storage):
 * 1. SYSPRO Order numbers are assigned via REST API ✅
 * 2. Theme's setSysproId() method uses update_post_meta() ❌
 * 3. update_post_meta() doesn't work properly with HPOS ❌
 * 4. SYSPRO Order column shows empty in orders table ❌
 * 
 * ROOT CAUSE:
 * The theme's APH\Order::setSysproId() method uses update_post_meta()
 * which is incompatible with WooCommerce's HPOS system. The data needs
 * to be saved using WooCommerce's order meta methods.
 * 
 * SOLUTION:
 * Hook into order meta updates to ensure SYSPRO order numbers are
 * properly saved using HPOS-compatible methods when they're set
 * via REST API or other means.
 * 
 * TECHNICAL IMPLEMENTATION:
 * - Monitors updates to syspro_order_number meta
 * - Converts update_post_meta calls to HPOS-compatible methods
 * - Ensures data persists in both legacy and HPOS storage
 */

/**
 * Fix SYSPRO order number persistence for HPOS compatibility
 * 
 * This function intercepts attempts to save SYSPRO order numbers
 * and ensures they're saved using HPOS-compatible methods.
 * 
 * WORDPRESS 6.9 UPDATE - December 2024:
 * - Handles legacy update_post_meta calls from theme
 * - Converts to HPOS-compatible order->update_meta_data
 * - Works with REST API updates
 * 
 * @param int $meta_id ID of the meta data
 * @param int $object_id Post/Order ID
 * @param string $meta_key Meta key being updated
 * @param mixed $meta_value Meta value being saved
 */
function pd_fix_syspro_order_number_persistence($meta_id, $object_id, $meta_key, $meta_value) {
    // Only process syspro_order_number meta
    if ($meta_key !== 'syspro_order_number') {
        return;
    }
    
    // Check if this is an order
    $order = wc_get_order($object_id);
    if (!$order) {
        return;
    }
    
    // Use HPOS-compatible method to ensure it's saved properly
    $order->update_meta_data('syspro_order_number', $meta_value);
    $order->save_meta_data();
    
    // Log for debugging
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('PD Hotfix: Fixed SYSPRO order number persistence for order #' . $object_id . ' - Value: ' . $meta_value);
    }
}

// Hook into post meta updates to fix SYSPRO order numbers
// This catches update_post_meta calls from the theme
add_action('added_post_meta', 'pd_fix_syspro_order_number_persistence', 10, 4);
add_action('updated_post_meta', 'pd_fix_syspro_order_number_persistence', 10, 4);

/**
 * Alternative approach: Filter the meta update to use HPOS methods
 * 
 * @param mixed $check Whether to proceed with the meta update
 * @param int $object_id Order ID
 * @param string $meta_key Meta key
 * @param mixed $meta_value Meta value
 * @param mixed $prev_value Previous value
 * @return mixed
 */
function pd_filter_syspro_meta_update($check, $object_id, $meta_key, $meta_value, $prev_value = '') {
    // Only process syspro_order_number meta
    if ($meta_key !== 'syspro_order_number') {
        return $check;
    }
    
    // Check if this is an order and HPOS is enabled
    if (class_exists('\Automattic\WooCommerce\Utilities\OrderUtil') && 
        \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
        
        $order = wc_get_order($object_id);
        if ($order) {
            // Use HPOS-compatible method
            $order->update_meta_data('syspro_order_number', $meta_value);
            $order->save_meta_data();
            
            // Prevent the original update_post_meta from running
            return true;
        }
    }
    
    return $check;
}

// Filter meta updates before they happen
add_filter('update_post_metadata', 'pd_filter_syspro_meta_update', 10, 5);
add_filter('add_post_metadata', 'pd_filter_syspro_meta_update', 10, 5);

// =========================================================================
// END WORDPRESS 6.9 UPDATE - SYSPRO ORDER NUMBER PERSISTENCE FIX
// =========================================================================


// =========================================================================
// FORCE DOCUMENT DOWNLOADS INSTEAD OF BROWSER VIEWING
// =========================================================================

/**
 * Forces all document links to download instead of opening in browser.
 * 
 * PROBLEM DESCRIPTION:
 * When users click on document links (especially PDFs) on the manuals-downloads page,
 * the documents open in the browser instead of downloading. This creates a poor user
 * experience as users expect to download the files for offline use.
 * 
 * AFFECTED PAGES:
 * - /manuals-downloads/ page with FacetWP search functionality
 * - Document links with class "document-item-link"
 * - Particularly PDF files which don't have the download attribute by default
 * 
 * ROOT CAUSE:
 * The theme template only adds the download attribute to non-PDF files, assuming
 * users want to view PDFs in the browser. However, for this site's use case,
 * all documents should download directly. Additionally, the download attribute
 * doesn't work for cross-origin URLs due to browser security, and CORS prevents
 * client-side fetching from media.aph.org.
 * 
 * SOLUTION:
 * - Creates a server-side download proxy endpoint that fetches and serves files
 * - Intercepts clicks on document links and redirects to proxy endpoint
 * - Proxy sets proper headers to force download instead of display
 * - Works around CORS restrictions by fetching files server-side
 * 
 * TECHNICAL IMPLEMENTATION:
 * - Registers custom AJAX endpoint for file downloads
 * - JavaScript intercepts clicks and redirects to proxy URL
 * - PHP fetches file from media.aph.org and streams to browser
 * - Sets Content-Disposition header to force download
 * - Handles both same-origin and cross-origin files
 * 
 * @since 1.0.1
 */

/**
 * AJAX handler for proxying document downloads
 * 
 * This function fetches files from remote URLs (like media.aph.org) and
 * streams them to the browser with proper download headers.
 */
function pd_ajax_download_document() {
    // Verify the request
    if (!isset($_GET['file_url']) || empty($_GET['file_url'])) {
        wp_die('Invalid request');
    }

    $file_url = esc_url_raw($_GET['file_url']);
    
    // Security check: Only allow downloads from trusted domains
    $allowed_domains = array(
        'media.aph.org',
        'aph.org',
        parse_url(home_url(), PHP_URL_HOST)
    );
    
    $file_host = parse_url($file_url, PHP_URL_HOST);
    $is_allowed = false;
    
    foreach ($allowed_domains as $domain) {
        if ($file_host === $domain || strpos($file_host, '.' . $domain) !== false) {
            $is_allowed = true;
            break;
        }
    }
    
    if (!$is_allowed) {
        wp_die('Unauthorized domain');
    }

    // Get filename from URL or parameter
    $filename = isset($_GET['filename']) ? sanitize_file_name($_GET['filename']) : basename(parse_url($file_url, PHP_URL_PATH));
    
    // Use WordPress HTTP API to fetch the file
    $response = wp_remote_get($file_url, array(
        'timeout' => 60,
        'sslverify' => false, // May need this for staging environments
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        )
    ));
    
    if (is_wp_error($response)) {
        wp_die('Failed to fetch file: ' . $response->get_error_message());
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        wp_die('Failed to fetch file: HTTP ' . $response_code);
    }
    
    // Get the content type
    $content_type = wp_remote_retrieve_header($response, 'content-type');
    if (empty($content_type)) {
        // Guess content type based on extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime_types = array(
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
            'csv' => 'text/csv'
        );
        $content_type = isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
    }
    
    // Get the file content
    $file_content = wp_remote_retrieve_body($response);
    $file_size = strlen($file_content);
    
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers to force download
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $file_size);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output the file
    echo $file_content;
    exit;
}
add_action('wp_ajax_pd_download_document', 'pd_ajax_download_document');
add_action('wp_ajax_nopriv_pd_download_document', 'pd_ajax_download_document');

/**
 * Adds JavaScript to intercept document clicks and use proxy
 */
function pd_force_document_downloads() {
    // Only load on pages with document downloads
    // Now includes WooCommerce single product pages for Manuals & Downloads tab
    if (!is_page_template('template/documents.php') && !is_page('manuals-downloads') && !is_singular('product')) {
        return;
    }
?>
    <script>
        (function() {
            'use strict';

            /**
             * Extracts filename from a URL
             * @param {string} url The file URL
             * @return {string} The filename
             */
            function getFilenameFromUrl(url) {
                try {
                    var urlObj = new URL(url);
                    var pathname = urlObj.pathname;
                    var filename = pathname.substring(pathname.lastIndexOf('/') + 1);
                    // Decode URI component to handle special characters
                    return decodeURIComponent(filename) || 'document';
                } catch (e) {
                    // Fallback for invalid URLs
                    return 'document';
                }
            }

            /**
             * Forces download using server-side proxy
             * @param {string} url The file URL
             * @param {string} filename The filename for download
             */
            function forceFileDownload(url, filename) {
                // console.log('PD Hotfix: Initiating download for ' + filename);
                
                // Build proxy URL using WordPress AJAX
                var proxyUrl = '<?php echo admin_url('admin-ajax.php'); ?>?' + 
                    'action=pd_download_document' +
                    '&file_url=' + encodeURIComponent(url) +
                    '&filename=' + encodeURIComponent(filename);
                
                // Use window.location to trigger download through proxy
                // This will cause the browser to download the file
                window.location.href = proxyUrl;
            }

            /**
             * Handles click on document links
             * @param {Event} event The click event
             */
            function handleDocumentLinkClick(event) {
                var link = event.target.closest('.document-item-link');
                
                if (!link) {
                    return;
                }

                var href = link.getAttribute('href');
                if (!href) {
                    return;
                }

                // Check if it's a file URL
                var isFileUrl = href.match(/\.(pdf|doc|docx|xls|xlsx|ppt|pptx|zip|txt|rtf|csv)$/i) ||
                                href.includes('/uploads/') ||
                                href.includes('/media/') ||
                                href.includes('media.aph.org');

                if (!isFileUrl) {
                    return;
                }

                // Prevent default action
                event.preventDefault();
                event.stopPropagation();

                // Get filename
                var filename = getFilenameFromUrl(href);

                // Always use the proxy for consistent behavior
                // This ensures all files download instead of opening in browser
                forceFileDownload(href, filename);

                return false;
            }

            /**
             * Sets up the download functionality
             */
            function setupDownloadHandlers() {
                // Remove any existing listeners first
                document.removeEventListener('click', handleDocumentLinkClick, true);
                
                // Add event listener using capture to ensure we catch the click first
                document.addEventListener('click', handleDocumentLinkClick, true);
                
                // console.log('PD Hotfix: Document download handlers initialized');
            }

            /**
             * Initialize and set up event listeners
             */
            function initialize() {
                // Set up click handlers
                setupDownloadHandlers();

                // Re-setup handlers after FacetWP updates
                document.addEventListener('facetwp-loaded', function() {
                    // console.log('PD Hotfix: FacetWP content updated, re-initializing download handlers');
                    // No need to re-setup as we're using event delegation
                });

                // Also listen for any AJAX complete events (backup)
                if (window.jQuery) {
                    jQuery(document).ajaxComplete(function(event, xhr, settings) {
                        if (settings && settings.url && settings.url.includes('facetwp')) {
                            // console.log('PD Hotfix: AJAX update detected');
                            // No need to re-setup as we're using event delegation
                        }
                    });
                }
            }

            // Start when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initialize);
            } else {
                // DOM already loaded
                initialize();
            }

        })();
    </script>
<?php
}
add_action('wp_footer', 'pd_force_document_downloads', 20);



// =========================================================================
// PO NUMBER EMAIL DISPLAY FIX
// Issue: PO Number stopped appearing in order emails after switching from
// woocommerce_checkout_update_order_meta to woocommerce_checkout_create_order
// =========================================================================

/**
 * ISSUE: PO Number Not Displaying in Order Emails
 *
 * PROBLEM:
 * After switching the PO Number save hook from woocommerce_checkout_update_order_meta
 * (which fires AFTER $order->save()) to woocommerce_checkout_create_order (which fires
 * BEFORE $order->save()), the PO Number stopped appearing in customer order emails.
 *
 * ROOT CAUSE:
 * woocommerce_checkout_create_order fires before the order is persisted to the database.
 * $order->update_meta_data() only queues the meta in memory. When WooCommerce later
 * generates the email, the $order object passed to the email template may be a separately
 * loaded instance that doesn't reflect the queued meta, or HPOS object caching may cause
 * the meta to be invisible at email render time.
 *
 * SOLUTION:
 * Replace the theme's email display function with a more robust version that uses multiple
 * fallback strategies to retrieve the PO Number:
 * 1. Standard $order->get_meta() on the passed order object
 * 2. Fresh order reload from database via wc_get_order()
 * 3. Direct database query against HPOS orders meta table
 * 4. Direct database query against legacy wp_postmeta table
 */

// Remove theme's email hook and replace with our robust version.
// Must run after theme has registered its hook, so we use after_setup_theme.
add_action('after_setup_theme', function () {
    remove_action('woocommerce_email_after_order_number', 'po_number_add_to_email', 10);
    add_action('woocommerce_email_after_order_number', 'pd_po_number_add_to_email', 10, 2);
}, 20);

/**
 * Display PO Number in order emails with robust meta retrieval.
 *
 * Replaces the theme's po_number_add_to_email() function with multiple
 * fallback strategies to ensure the PO Number is always retrieved,
 * regardless of HPOS caching or object timing issues.
 *
 * @param WC_Order $order      The order object.
 * @param bool     $plain_text Whether the email is plain text.
 */
function pd_po_number_add_to_email($order, $plain_text) {
    $po_number = '';
    $order_id = $order->get_id();

    // Strategy 1: Standard get_meta on the passed order object
    if ($order_id) {
        $po_number = $order->get_meta('PO Number');
    }

    // Strategy 2: Reload order fresh from database
    if (empty($po_number) && $order_id) {
        $fresh_order = wc_get_order($order_id);
        if ($fresh_order) {
            $po_number = $fresh_order->get_meta('PO Number');
        }
    }

    // Strategy 3: Direct database query (HPOS meta table, then legacy postmeta)
    if (empty($po_number) && $order_id) {
        global $wpdb;

        // Try HPOS orders meta table
        $hpos_table = $wpdb->prefix . 'wc_orders_meta';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $hpos_table)) === $hpos_table) {
            $po_number = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$hpos_table} WHERE order_id = %d AND meta_key = %s LIMIT 1",
                $order_id,
                'PO Number'
            ));
        }

        // Try legacy postmeta table
        if (empty($po_number)) {
            $po_number = get_post_meta($order_id, 'PO Number', true);
        }
    }

    if (!empty($po_number)) {
        if ($plain_text === false) {
            echo '<h4 style="color: #4a2a4d; text-align: center; margin-top: 0px;">PO Number: ' . esc_html($po_number) . '</h4>';
        } else {
            echo 'PO Number: ' . esc_html($po_number);
        }
    }
}
// =========================================================================
// END PO NUMBER EMAIL DISPLAY FIX
// =========================================================================


// =========================================================================
// CUSTOM FILE TYPE UPLOAD SUPPORT (.VHU & .SWU)
// Issue: WordPress blocks .vhu and .swu firmware update files from being
// uploaded via the Media Library due to unrecognized MIME types
// =========================================================================

/**
 * ISSUE: Unable to Upload .VHU and .SWU Files in WordPress
 *
 * PROBLEM:
 * WordPress restricts file uploads to a whitelist of known MIME types.
 * .vhu (Vispero Hardware Update) and .swu (SWUpdate Archive) firmware
 * files are not in this whitelist, causing uploads to fail with
 * "Sorry, you are not allowed to upload this file type."
 *
 * ROOT CAUSE:
 * WordPress uses wp_check_filetype_and_ext() to validate uploads against
 * its allowed MIME types array. Since .vhu and .swu are not registered,
 * they are rejected at upload time. Additionally, WordPress 4.7.1+
 * performs real MIME type verification using finfo/mime_content_type,
 * which can still block uploads even after registering the extension,
 * because binary firmware files won't match a specific MIME signature.
 *
 * SOLUTION:
 * Two filters work together to allow these uploads:
 * 1. upload_mimes — Registers .vhu and .swu with application/octet-stream
 *    MIME type so WordPress recognizes them as allowed extensions
 * 2. wp_check_filetype_and_ext — Bypasses the real MIME type verification
 *    for these specific extensions, preventing false rejection when PHP's
 *    fileinfo reports a generic or mismatched MIME type for binary firmware
 */

// Register .vhu and .swu MIME types for the upload whitelist.
add_filter('upload_mimes', function ($mimes) {
    $mimes['vhu'] = 'application/octet-stream';
    $mimes['swu'] = 'application/octet-stream';
    return $mimes;
});

/**
 * Bypass real MIME type verification for .vhu and .swu files.
 *
 * WordPress 4.7.1+ uses finfo/mime_content_type to verify that a file's
 * actual content matches its extension. Binary firmware files lack a
 * recognizable MIME signature, so this check incorrectly rejects them.
 * This filter forces the correct ext/type values for our firmware
 * extensions, ensuring the upload proceeds.
 *
 * @param array  $data     Values for the extension, mime type, and filename.
 * @param string $file     Full path to the file.
 * @param string $filename The name of the file.
 * @param array  $mimes    Allowed mime types keyed by extension.
 * @return array Modified file data with correct ext and type.
 */
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if (in_array(strtolower($ext), ['vhu', 'swu'])) {
        $data['ext']  = $ext;
        $data['type'] = 'application/octet-stream';
        $data['proper_filename'] = $filename;
    }

    return $data;
}, 10, 4);
// =========================================================================
// END CUSTOM FILE TYPE UPLOAD SUPPORT
// =========================================================================


/**
 * Add Two-Factor Authentication Custom Styles Inline
 * Add this code to your theme's functions.php file
 * All styles are scoped under .wpdef-2fa-wrap and use !important to override defaults
 */

function add_inline_2fa_custom_styles() {
    // $screen = get_current_screen();

    if ( ! is_account_page() ) {
        return;
    }
    
    // if ( ! is_account_page() ) {
        ?>
        <style type="text/css">
            /* ===========================
               Two-Factor Authentication Custom Styles
               All styles scoped under .wpdef-2fa-wrap
               =========================== */

            /* Base Styles */
            .wpdef-2fa-wrap .form-table#defender-security {
                width: auto !important;
                border-collapse: collapse !important;
                margin: 20px 0 !important;
                background: #fff !important;
            }

            .wpdef-2fa-wrap .form-table#defender-security tbody {
                width: 100% !important;
            }

            .wpdef-2fa-wrap .user-sessions-wrap>td, .wpdef-2fa-wrap .user-sessions-wrap>th {
                padding: 0 !important;
            }

            /* Notification Styles */
            .wpdef-2fa-wrap .def-notification {
                background: #fff3cd !important;
                border-left: 4px solid #ffc107 !important;
                padding: 12px 16px !important;
                margin: 16px 0 !important;
                display: flex !important;
                align-items: center !important;
                border-radius: 4px !important;
            }

            .wpdef-2fa-wrap .def-notification .dashicons-warning {
                color: #ffc107 !important;
                font-size: 20px !important;
                margin-right: 10px !important;
                flex-shrink: 0 !important;
            }

            /* Authentication Methods Table */
            .wpdef-2fa-wrap .auth-methods-table {
                width: auto !important;
                border-collapse: collapse !important;
                margin: 20px 0 !important;
                background: #fff !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                overflow: hidden !important;
            }

            .wpdef-2fa-wrap .auth-methods-table thead {
                background: #f7f7f7 !important;
                border-bottom: 2px solid #ddd !important;
            }

            .wpdef-2fa-wrap .auth-methods-table thead th {
                padding: 12px 16px !important;
                text-align: left !important;
                font-weight: 600 !important;
                color: #23282d !important;
                font-size: 14px !important;
            }

            .wpdef-2fa-wrap .auth-methods-table thead .col-enabled {
                width: 80px !important;
                text-align: center !important;
            }

            .wpdef-2fa-wrap .auth-methods-table thead .col-primary {
                width: auto !important;
            }

            .wpdef-2fa-wrap .auth-methods-table tbody tr {
                border-bottom: 1px solid #e5e5e5 !important;
                transition: background-color 0.2s ease !important;
            }

            .wpdef-2fa-wrap .auth-methods-table tbody tr:last-child {
                /* border-bottom: none !important; */
            }

            .wpdef-2fa-wrap .auth-methods-table tbody tr:hover {
                background-color: #f9f9f9 !important;
            }

            .wpdef-2fa-wrap .auth-methods-table tbody th,
            .wpdef-2fa-wrap .auth-methods-table tbody td {
                padding: 16px !important;
                vertical-align: top !important;
            }

            /* Radio and Toggle Column Styles */
            .wpdef-2fa-wrap .auth-methods-table .radio-button,
            .wpdef-2fa-wrap .auth-methods-table .toggles {
                width: 60px !important;
                text-align: center !important;
                /* vertical-align: middle !important; */
            }

            .wpdef-2fa-wrap .auth-methods-table .radio-button input[type="radio"],
            .wpdef-2fa-wrap .auth-methods-table .toggles input[type="checkbox"] {
                margin: 0 !important;
                cursor: pointer !important;
            }

            .wpdef-2fa-wrap .auth-methods-table .radio-button input[type="radio"]:disabled,
            .wpdef-2fa-wrap .auth-methods-table .toggles input[type="checkbox"]:disabled {
                cursor: not-allowed !important;
                opacity: 0.5 !important;
            }

            /* Toggle Switch Styling — explicit dimensions on checkbox,
               both pseudo-elements absolutely positioned within it */
            .wpdef-2fa-wrap .wpdef-ui-toggle {
                position: relative !important;
                display: inline-block !important;
                appearance: none !important;
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                width: 38px !important;
                height: 20px !important;
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                outline: none !important;
                cursor: pointer !important;
                vertical-align: middle !important;
            }

            .wpdef-2fa-wrap .wpdef-ui-toggle:checked {
                background: transparent !important;
            }

            /* Knob (white circle) */
            .wpdef-2fa-wrap .wpdef-ui-toggle::before {
                content: '' !important;
                position: absolute !important;
                width: 16px !important;
                height: 16px !important;
                border-radius: 50% !important;
                background: #fff !important;
                top: 2px !important;
                left: 2px !important;
                transition: left 0.3s !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.4) !important;
                z-index: 1 !important;
            }

            .wpdef-2fa-wrap .wpdef-ui-toggle:checked::before {
                left: 20px !important;
            }

            .wpdef-2fa-wrap .wpdef-ui-toggle.disabled {
                cursor: not-allowed !important;
                opacity: 0.5 !important;
            }

            /* Content Column */
            .wpdef-2fa-wrap .auth-methods-table tbody td strong {
                display: block !important;
                font-size: 15px !important;
                color: #23282d !important;
                margin-bottom: 8px !important;
            }

            .wpdef-2fa-wrap .auth-methods-table tbody td p {
                color: #000000 !important;
                font-size: 13px !important;
                line-height: 1.5 !important;
                margin: 8px 0 !important;
            }

            /* TOTP Section Styles */
            .wpdef-2fa-wrap #defender-totp {
                margin-top: 20px !important;
                padding: 20px !important;
                background: #f9f9f9 !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
            }

            .wpdef-2fa-wrap #defender-totp.hidden {
                display: none;
            }

            .wpdef-2fa-wrap .card {
                background: white !important;
                padding: 20px !important;
                border-radius: 4px !important;
                margin-bottom: 20px !important;
            }

            .wpdef-2fa-wrap .card strong {
                display: block !important;
                font-size: 15px !important;
                color: #23282d !important;
                margin-bottom: 12px !important;
            }

            .wpdef-2fa-wrap .card p {
                color: #000000 !important;
                font-size: 14px !important;
                line-height: 1.6 !important;
                margin: 12px 0 !important;
            }

            /* App Selection */
            .wpdef-2fa-wrap #auth-app {
                width: 100% !important;
                max-width: 300px !important;
                padding: 8px 12px !important;
                margin: 12px 0 !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                font-size: 14px !important;
            }

            /* Download Links */
            .wpdef-2fa-wrap #ios-app,
            .wpdef-2fa-wrap #android-app {
                display: inline-block !important;
                margin: 12px 12px 12px 0 !important;
                transition: opacity 0.2s !important;
            }

            .wpdef-2fa-wrap #ios-app:hover,
            .wpdef-2fa-wrap #android-app:hover {
                opacity: 0.8 !important;
            }

            .wpdef-2fa-wrap #ios-app img,
            .wpdef-2fa-wrap #android-app img {
                height: 40px !important;
                width: auto !important;
                display: block !important;
            }

            .wpdef-2fa-wrap .line {
                height: 1px !important;
                background: #ddd !important;
                margin: 24px 0 !important;
            }

            /* QR Code */
            .wpdef-2fa-wrap .wd_text_wrap {
                margin: 12px 0 !important;
            }

            .wpdef-2fa-wrap #defender-qr-code {
                margin: 20px 0 !important;
                display: inline-block !important;
                padding: 10px !important;
                background: white !important;
                border-radius: 4px !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
            }

            /* Code Display */
            .wpdef-2fa-wrap .wd_code_wrap {
                background: #f0f0f0 !important;
                padding: 16px !important;
                border-radius: 4px !important;
                margin: 16px 0 !important;
                display: flex !important;
                align-items: center !important;
                flex-wrap: wrap !important;
                gap: 12px !important;
            }

            .wpdef-2fa-wrap .wd_code_wrap code {
                font-family: 'Courier New', monospace !important;
                font-size: 16px !important;
                font-weight: 600 !important;
                color: #23282d !important;
                letter-spacing: 2px !important;
                flex: 1 !important;
                min-width: 200px !important;
            }

            .wpdef-2fa-wrap .wd_code_wrap button {
                flex-shrink: 0 !important;
            }

            /* OTP Input */
            .wpdef-2fa-wrap .well {
                background: white !important;
                padding: 20px !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                margin: 16px 0 !important;
                width: auto !important;
            }

            .wpdef-2fa-wrap .well .error {
                color: #d63638 !important;
                font-size: 13px !important;
                margin-bottom: 12px !important;
                display: none !important;
            }

            .wpdef-2fa-wrap .well .error:not(:empty) {
                display: block !important;
            }

            .wpdef-2fa-wrap #otp-code,
            .wpdef-2fa-wrap .def-small-text {
                width: 100% !important;
                max-width: 200px !important;
                padding: 10px 12px !important;
                font-size: 16px !important;
                border: 1px solid #8c8f94 !important;
                border-radius: 4px !important;
                margin-bottom: 12px !important;
                font-family: 'Courier New', monospace !important;
                letter-spacing: 3px !important;
            }

            .wpdef-2fa-wrap #otp-code:focus,
            .wpdef-2fa-wrap .def-small-text:focus {
                border-color: #000000 !important;
                outline: none !important;
                box-shadow: 0 0 0 1px #000000 !important;
            }

            /* Backup Codes Section */
            .wpdef-2fa-wrap #wpdef-2fa-backup-codes {
                margin: 16px 0 !important;
            }

            .wpdef-2fa-wrap .button-wpdef-2fa-backup-codes-generate {
                margin-right: 12px !important;
            }

            .wpdef-2fa-wrap .wpdef-2fa-backup-codes-count {
                display: inline-block !important;
                color: #000000 !important;
                font-size: 13px !important;
            }

            .wpdef-2fa-wrap .wpdef-2fa-backup-codes-count.hidden {
                display: none !important;
            }

            .wpdef-2fa-wrap .wpdef-2fa-backup-codes-wrapper {
                margin-top: 20px !important;
                padding: 20px !important;
                background: #f9f9f9 !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
            }

            .wpdef-2fa-wrap .wpdef-2fa-backup-codes-wrapper .description {
                color: #000000 !important;
                font-size: 13px !important;
                margin-bottom: 16px !important;
            }

            .wpdef-2fa-wrap .wpdef-2fa-backup-codes-wrapper .download-button {
                margin: 16px 0 !important;
            }

            .wpdef-2fa-wrap .wpdef-2fa-backup-codes-unused-codes {
                display: grid !important;
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
                gap: 12px !important;
                margin-top: 20px !important;
            }

            .wpdef-2fa-wrap .wpdef-2fa-backup-codes-unused-codes p {
                background: white !important;
                padding: 12px !important;
                border: 1px solid #ddd !important;
                border-radius: 4px !important;
                font-family: 'Courier New', monospace !important;
                font-size: 14px !important;
                text-align: center !important;
                margin: 0 !important;
            }

            /* Fallback Email Section */
            .wpdef-2fa-wrap #wpdef-2fa-email {
                margin: 16px 0 !important;
            }

            .wpdef-2fa-wrap #wpdef-2fa-email.hidden {
                display: none;
            }

            /* Track (background behind the knob) — absolutely positioned to fill the checkbox */
            .wpdef-2fa-wrap input[type=checkbox].wpdef-ui-toggle:after {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                display: block !important;
                background-color: #767676 !important;
                border-radius: 10px !important;
                box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1) !important;
                transition: background-color 0.3s !important;
            }

            .wpdef-2fa-wrap input[type=checkbox].wpdef-ui-toggle:checked:after {
                background-color: #000000 !important;
            }

            .wpdef-2fa-wrap #wpdef-2fa-email input[type="text"],
            .wpdef-2fa-wrap #wpdef-2fa-email .regular-text {
                width: 100% !important;
                max-width: 400px !important;
                padding: 10px 12px !important;
                font-size: 14px !important;
                border: 1px solid #8c8f94 !important;
                border-radius: 4px !important;
            }

            .wpdef-2fa-wrap #wpdef-2fa-email input[type="text"]:focus,
            .wpdef-2fa-wrap #wpdef-2fa-email .regular-text:focus {
                border-color: #000000 !important;
                outline: none !important;
                box-shadow: 0 0 0 1px #000000 !important;
            }

            /* Notice Styles */
            .wpdef-2fa-wrap .wpdef-notice {
                padding: 12px 16px !important;
                margin: 16px 0 !important;
                border-left: 4px solid !important;
                border-radius: 4px !important;
                display: flex !important;
                align-items: flex-start !important;
                position: relative !important;
            }

            .wpdef-2fa-wrap .wpdef-notice .dashicons {
                font-size: 20px !important;
                margin-right: 10px !important;
                flex-shrink: 0 !important;
                margin-top: 2px !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.info {
                background: #e5f5fa !important;
                border-left-color: #00a0d2 !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.info .dashicons {
                color: #00a0d2 !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.warning {
                background: #fff3cd !important;
                border-left-color: #ffc107 !important;
                box-shadow: unset !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.warning .dashicons {
                color: #ffc107 !important;
            }

            .wpdef-2fa-wrap .wpdef-notice p {
                margin: 0 !important;
                flex: 1 !important;
            }

            .wpdef-2fa-wrap .wpdef-notice-message {
                font-size: 13px !important;
                line-height: 1.5 !important;
                color: #23282d !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.is-dismissible .notice-dismiss {
                position: absolute !important;
                top: 8px !important;
                right: 8px !important;
                background: none !important;
                border: none !important;
                cursor: pointer !important;
                padding: 8px !important;
                color: #787c82 !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.is-dismissible .notice-dismiss:hover {
                color: #23282d !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.is-dismissible .notice-dismiss .screen-reader-text {
                position: absolute !important;
                left: -9999px !important;
            }

            .wpdef-2fa-wrap .wpdef-notice.additional-2fa-method,
            .wpdef-2fa-wrap .wpdef-notice.user-handle-mismatch {
                display: none !important;
            }

            /* Button Styles */
            .wpdef-2fa-wrap .button {
                display: inline-block !important;
                padding: 8px 16px !important;
                font-size: 13px !important;
                line-height: 1.5 !important;
                border-radius: 4px !important;
                border: 1px solid #7e8993 !important;
                background: #f6f7f7 !important;
                color: #2c3338 !important;
                cursor: pointer !important;
                text-decoration: none !important;
                transition: all 0.2s ease !important;
                font-weight: 500 !important;
            }

            .wpdef-2fa-wrap .button[type=submit] {
                background: #000 !important;
                color: #fff !important;
                border: 2px solid #000 !important;
                font-weight: 800 !important;
                border-radius: 0 !important;
                font-size: 16px !important;
            }


            .wpdef-2fa-wrap .button:hover {
                background: #f0f0f1 !important;
                border-color: #000000 !important;
                color: #2c3338 !important;
            }

            .wpdef-2fa-wrap .button:active {
                transform: translateY(1px) !important;
            }

            .wpdef-2fa-wrap .button:disabled {
                cursor: not-allowed !important;
                opacity: 0.5 !important;
            }

            .wpdef-2fa-wrap .button-primary {
                background: #000000 !important;
                border-color: #000000 !important;
                color: white !important;
            }

            .wpdef-2fa-wrap .button-primary:hover {
                background: #135e96 !important;
                border-color: #135e96 !important;
                color: white !important;
            }

            .wpdef-2fa-wrap .button-secondary {
                background: #f0f0f1 !important;
                border-color: #7e8993 !important;
                color: #2c3338 !important;
            }

            /* Utility Classes */
            .wpdef-2fa-wrap .hidden {
                display: none;
            }

            .wpdef-2fa-wrap .hide-if-no-js {
                /* JavaScript enabled by default */
            }

            .wpdef-2fa-wrap .disabled {
                opacity: 0.5 !important;
                cursor: not-allowed !important;
            }

            /* Responsive Styles - Tablet (768px and below) */
            @media screen and (max-width: 768px) {
                .wpdef-2fa-wrap .auth-methods-table thead {
                    display: none !important;
                }

                #defender-security .radio-button {
                    display: none !important;
                }

                .wpdef-2fa-wrap .auth-methods-table tbody tr {
                    display: block !important;
                    margin-bottom: 16px !important;
                    border: 1px solid #ddd !important;
                    border-radius: 4px !important;
                    padding: 16px !important;
                }

                .wpdef-2fa-wrap .auth-methods-table tbody th,
                .wpdef-2fa-wrap .auth-methods-table tbody td {
                    display: block !important;
                    width: 100% !important;
                    padding: 8px 0 !important;
                    text-align: left !important;
                }

                .wpdef-2fa-wrap .auth-methods-table .radio-button,
                .wpdef-2fa-wrap .auth-methods-table .toggles {
                    width: 100% !important;
                    text-align: left !important;
                    margin-bottom: 12px !important;
                }

                .wpdef-2fa-wrap .auth-methods-table .radio-button::before {
                    content: 'Default: ' !important;
                    font-weight: 600 !important;
                    margin-right: 8px !important;
                }

                .wpdef-2fa-wrap .auth-methods-table .toggles::before {
                    content: 'Enable: ' !important;
                    font-weight: 600 !important;
                    margin-right: 8px !important;
                }

                .wpdef-2fa-wrap .def-notification {
                    padding: 12px !important;
                    font-size: 13px !important;
                }

                .wpdef-2fa-wrap #defender-totp {
                    padding: 16px !important;
                }

                .wpdef-2fa-wrap .card {
                    padding: 16px !important;
                }

                .wpdef-2fa-wrap .wpdef-2fa-backup-codes-unused-codes {
                    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
                }
            }

            /* Responsive Styles - Mobile (480px and below) */
            @media screen and (max-width: 480px) {
                .wpdef-2fa-wrap .form-table#defender-security {
                    margin: 12px 0 !important;
                }

                .wpdef-2fa-wrap .auth-methods-table tbody tr {
                    padding: 12px !important;
                    margin-bottom: 12px !important;
                }

                .wpdef-2fa-wrap .def-notification {
                    flex-direction: column !important;
                    align-items: flex-start !important;
                }

                .wpdef-2fa-wrap .def-notification .dashicons-warning {
                    margin-bottom: 8px !important;
                }

                .wpdef-2fa-wrap #defender-totp {
                    padding: 12px !important;
                }

                .wpdef-2fa-wrap .card {
                    padding: 12px !important;
                }

                .wpdef-2fa-wrap .wd_code_wrap {
                    flex-direction: column !important;
                    align-items: stretch !important;
                }

                .wpdef-2fa-wrap .wd_code_wrap code {
                    min-width: 100% !important;
                    text-align: center !important;
                    font-size: 14px !important;
                }

                .wpdef-2fa-wrap .wd_code_wrap button {
                    width: 100% !important;
                }

                .wpdef-2fa-wrap #ios-app img,
                .wpdef-2fa-wrap #android-app img {
                    height: 35px !important;
                }

                .wpdef-2fa-wrap .wpdef-2fa-backup-codes-unused-codes {
                    grid-template-columns: 1fr !important;
                }

                .wpdef-2fa-wrap .button {
                    width: 100% !important;
                    text-align: center !important;
                    margin-bottom: 8px !important;
                }

                .wpdef-2fa-wrap .well {
                    padding: 16px !important;
                }

                .wpdef-2fa-wrap #otp-code,
                .wpdef-2fa-wrap .def-small-text {
                    max-width: 100% !important;
                    margin-bottom: 12px !important;
                }

                .wpdef-2fa-wrap #verify-otp {
                    width: 100% !important;
                }

                .wpdef-2fa-wrap #wpdef-2fa-email input[type="text"],
                .wpdef-2fa-wrap #wpdef-2fa-email .regular-text {
                    max-width: 100% !important;
                }

                .wpdef-2fa-wrap .wpdef-notice {
                    padding: 10px 12px !important;
                }

                .wpdef-2fa-wrap .wpdef-notice .dashicons {
                    font-size: 18px !important;
                }
            }

            /* Responsive Styles - Extra Small Mobile (360px and below) */
            @media screen and (max-width: 360px) {
                .wpdef-2fa-wrap .auth-methods-table tbody tr {
                    padding: 10px !important;
                }

                .wpdef-2fa-wrap .card strong,
                .wpdef-2fa-wrap .auth-methods-table tbody td strong {
                    font-size: 14px !important;
                }

                .wpdef-2fa-wrap .card p,
                .wpdef-2fa-wrap .auth-methods-table tbody td p {
                    font-size: 12px !important;
                }

                .wpdef-2fa-wrap #defender-qr-code {
                    padding: 8px !important;
                }

                .wpdef-2fa-wrap #defender-qr-code > div {
                    width: 150px !important;
                    height: 150px !important;
                }

                .wpdef-2fa-wrap .wd_code_wrap code {
                    font-size: 12px !important;
                    letter-spacing: 1px !important;
                    word-break: break-all !important;
                }

                .wpdef-2fa-wrap #ios-app img,
                .wpdef-2fa-wrap #android-app img {
                    height: 30px !important;
                }

                .wpdef-2fa-wrap .button {
                    padding: 8px 12px !important;
                    font-size: 12px !important;
                }

                .wpdef-2fa-wrap .wpdef-2fa-backup-codes-unused-codes p {
                    font-size: 12px !important;
                    padding: 10px !important;
                }
            }

            /* Focus states for accessibility */
            .wpdef-2fa-wrap .button:focus,
            .wpdef-2fa-wrap input[type="radio"]:focus,
            .wpdef-2fa-wrap input[type="checkbox"]:focus,
            .wpdef-2fa-wrap input[type="text"]:focus,
            .wpdef-2fa-wrap select:focus {
                outline: 2px solid #000000 !important;
                outline-offset: 2px !important;
            }

            .wpdef-2fa-wrap .wpdef-ui-toggle:focus {
                outline: 3px solid #0073aa !important;
                outline-offset: 2px !important;
                box-shadow: 0 0 0 1px #fff !important;
            }

            /* High contrast mode support */
            @media (prefers-contrast: high) {
                .wpdef-2fa-wrap .auth-methods-table {
                    border: 2px solid #000 !important;
                }

                .wpdef-2fa-wrap .auth-methods-table tbody tr {
                    border: 2px solid #000 !important;
                }

                .wpdef-2fa-wrap .button {
                    border: 2px solid #000 !important;
                }

                .wpdef-2fa-wrap input[type=checkbox].wpdef-ui-toggle:after {
                    box-shadow: inset 0 0 0 3px #000 !important;
                }

                .wpdef-2fa-wrap .wpdef-ui-toggle::before {
                    border: 2px solid #000 !important;
                }
            }

            /* Reduced motion support */
            @media (prefers-reduced-motion: reduce) {
                .wpdef-2fa-wrap *,
                .wpdef-2fa-wrap *::before,
                .wpdef-2fa-wrap *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }
        </style>
        <?php
    // }
}
add_action( 'wp_head', 'add_inline_2fa_custom_styles' );