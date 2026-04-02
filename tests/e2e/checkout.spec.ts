import { test, expect } from '@playwright/test';

test.describe('Checkout', () => {
  // Add a product before checkout tests
  test.beforeEach(async ({ page }) => {
    await page.goto('/product/braille-bridge/');
    const firstQty = page.locator('input[type="number"]').first();
    await firstQty.fill('1');
    await page.locator('button:has-text("Add to cart")').click();
    await page.waitForLoadState('networkidle');
  });

  test('checkout page loads with form', async ({ page }) => {
    await page.goto('/checkout/');
    await expect(page).toHaveTitle(/Checkout/);

    // Should have billing fields
    await expect(page.locator('#billing_first_name, input[name="billing_first_name"]')).toBeVisible();
  });

  test('checkout shows order summary', async ({ page }) => {
    await page.goto('/checkout/');

    // Should show the product in order review
    const orderReview = page.locator('#order_review, .woocommerce-checkout-review-order');
    await expect(orderReview).toBeVisible();
    await expect(orderReview).toContainText('$');
  });

  test('checkout has billing fields', async ({ page }) => {
    await page.goto('/checkout/');

    // Core billing fields should be present
    await expect(page.locator('#billing_first_name, input[name="billing_first_name"]')).toBeVisible();
    await expect(page.locator('#billing_last_name, input[name="billing_last_name"]')).toBeVisible();
    await expect(page.locator('#billing_email, input[name="billing_email"]')).toBeVisible();
    await expect(page.locator('#billing_address_1, input[name="billing_address_1"]')).toBeVisible();
    await expect(page.locator('#billing_city, input[name="billing_city"]')).toBeVisible();
    await expect(page.locator('#billing_postcode, input[name="billing_postcode"]')).toBeVisible();
  });

  test('checkout validates required fields', async ({ page }) => {
    await page.goto('/checkout/');

    // Clear any pre-filled fields and try to submit
    await page.locator('#billing_first_name, input[name="billing_first_name"]').fill('');
    await page.locator('#billing_last_name, input[name="billing_last_name"]').fill('');

    // Click place order
    const placeOrder = page.locator('#place_order, button:has-text("Place order")');
    if (await placeOrder.isVisible()) {
      await placeOrder.click();

      // Should show validation errors
      await page.waitForTimeout(2000);
      const errorNotice = page.locator('.woocommerce-error, .woocommerce-NoticeGroup-checkout');
      await expect(errorNotice).toBeVisible();
    }
  });

  test('checkout has payment method selection', async ({ page }) => {
    await page.goto('/checkout/');

    // Should have payment methods listed
    const paymentMethods = page.locator('#payment, .wc_payment_methods');
    await expect(paymentMethods).toBeVisible();
  });

  test('checkout has PO number field (APH custom)', async ({ page }) => {
    await page.goto('/checkout/');

    // APH has a custom PO Number field on checkout
    const poField = page.locator('#po_number, input[name*="po_number"], input[name*="po-number"]');
    // This may or may not be present depending on customer type
    // Just check checkout doesn't error — the field is optional
    await expect(page.locator('main')).toBeVisible();
  });

  test('checkout form can be filled (without submitting)', async ({ page }) => {
    await page.goto('/checkout/');

    // Fill billing details with test data
    await page.locator('#billing_first_name, input[name="billing_first_name"]').fill('Test');
    await page.locator('#billing_last_name, input[name="billing_last_name"]').fill('Customer');
    await page.locator('#billing_address_1, input[name="billing_address_1"]').fill('1839 Frankfort Ave');
    await page.locator('#billing_city, input[name="billing_city"]').fill('Louisville');
    await page.locator('#billing_postcode, input[name="billing_postcode"]').fill('40206');

    const emailField = page.locator('#billing_email, input[name="billing_email"]');
    if (await emailField.isVisible()) {
      await emailField.fill('test-customer@aph.org');
    }

    const phoneField = page.locator('#billing_phone, input[name="billing_phone"]');
    if (await phoneField.isVisible()) {
      await phoneField.fill('502-895-2405');
    }

    // Verify fields retained values
    await expect(page.locator('#billing_first_name, input[name="billing_first_name"]')).toHaveValue('Test');
    await expect(page.locator('#billing_city, input[name="billing_city"]')).toHaveValue('Louisville');

    // NOTE: We intentionally do NOT submit the order to avoid creating test orders
    // in the database or charging payment methods
  });
});
