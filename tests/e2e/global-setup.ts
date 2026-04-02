import { test as setup, expect } from '@playwright/test';

const baseURL = process.env.TEST_URL || 'https://aph.ddev.site';
const customerEmail = process.env.TEST_CUSTOMER_EMAIL || 'test-customer@aph.org';
const customerPassword = process.env.TEST_CUSTOMER_PASSWORD || 'testpassword123';

/**
 * Global setup: Log in as the test customer and save auth state.
 *
 * Prerequisites:
 *   The test customer must exist in WordPress. Create via WP-CLI:
 *   wp user create test-customer test-customer@aph.org --role=customer --user_pass=testpassword123
 */
setup('authenticate as test customer', async ({ page }) => {
  // Navigate to My Account (login page)
  await page.goto(`${baseURL}/my-account/`);

  // Fill login form
  await page.locator('#username').fill(customerEmail);
  await page.locator('#password').fill(customerPassword);
  await page.locator('button[name="login"], input[name="login"]').click();

  // Wait for redirect to account dashboard
  await page.waitForURL('**/my-account/**');

  // Verify we're logged in — dashboard should show account navigation
  await expect(page.locator('.woocommerce-MyAccount-navigation')).toBeVisible();

  // Save auth state for other tests to reuse
  await page.context().storageState({ path: './fixtures/auth-state.json' });
});
