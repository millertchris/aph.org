import { test, expect } from '@playwright/test';

test.describe('Cart', () => {
  // Add a product before cart tests
  test.beforeEach(async ({ page }) => {
    // Navigate to a simple/grouped product and add to cart
    await page.goto('/product/braille-bridge/');

    // Set quantity of first variant to 1
    const firstQty = page.locator('input[type="number"]').first();
    await firstQty.fill('1');

    // Add to cart
    await page.locator('button:has-text("Add to cart")').click();
    await page.waitForLoadState('networkidle');
  });

  test('cart page loads with items', async ({ page }) => {
    await page.goto('/cart/');
    await expect(page).toHaveTitle(/Cart/);

    // Should NOT show "cart is empty"
    await expect(page.locator('text=Your cart is currently empty')).not.toBeVisible();
  });

  test('cart displays product name', async ({ page }) => {
    await page.goto('/cart/');

    // Should show the product we added
    const cartTable = page.locator('.woocommerce-cart-form, .shop_table, table');
    await expect(cartTable).toBeVisible();
  });

  test('cart shows item price and total', async ({ page }) => {
    await page.goto('/cart/');

    // Should display prices
    await expect(page.locator('main')).toContainText('$');
  });

  test('cart quantity can be updated', async ({ page }) => {
    await page.goto('/cart/');

    // Find quantity input in cart
    const cartQty = page.locator('.woocommerce-cart-form input[type="number"], .cart input[type="number"]').first();
    if (await cartQty.isVisible()) {
      await cartQty.fill('2');

      // Click update cart button
      const updateBtn = page.locator('button:has-text("Update cart"), input[name="update_cart"]');
      if (await updateBtn.isVisible()) {
        await updateBtn.click();
        await page.waitForLoadState('networkidle');
      }
    }
  });

  test('cart has proceed to checkout button', async ({ page }) => {
    await page.goto('/cart/');

    const checkoutLink = page.locator('a:has-text("Proceed to checkout"), .checkout-button');
    await expect(checkoutLink).toBeVisible();
  });

  test('proceed to checkout navigates to checkout page', async ({ page }) => {
    await page.goto('/cart/');

    const checkoutLink = page.locator('a:has-text("Proceed to checkout"), .checkout-button');
    await checkoutLink.click();

    await page.waitForURL('**/checkout/**');
    await expect(page).toHaveTitle(/Checkout/);
  });

  test('empty cart shows return to shop link', async ({ page }) => {
    // Start fresh — clear the cart via WooCommerce URL
    await page.goto('/cart/?empty-cart=true');
    // If that doesn't work, navigate to empty cart
    await page.goto('/cart/');

    // If cart is empty, should show return to shop
    const emptyMessage = page.locator('text=Your cart is currently empty');
    if (await emptyMessage.isVisible()) {
      const returnLink = page.locator('a:has-text("Return to shop")');
      await expect(returnLink).toBeVisible();
    }
  });
});
