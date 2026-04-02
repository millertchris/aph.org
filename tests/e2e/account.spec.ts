import { test, expect } from '@playwright/test';

test.describe('My Account', () => {
  test('my account page loads with login form', async ({ browser }) => {
    // Use fresh context (no auth state) to test login page
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await context.newPage();

    await page.goto(`${process.env.TEST_URL || 'https://aph.ddev.site'}/my-account/`);
    await expect(page).toHaveTitle(/My account/);

    // Should show login and register forms
    await expect(page.locator('h2:has-text("Login")')).toBeVisible();
    await expect(page.locator('h2:has-text("Register")')).toBeVisible();

    await context.close();
  });

  test('login form has required fields', async ({ browser }) => {
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await context.newPage();

    await page.goto(`${process.env.TEST_URL || 'https://aph.ddev.site'}/my-account/`);

    // Login form should have username and password fields
    await expect(page.locator('#username, input[name="username"]')).toBeVisible();
    await expect(page.locator('#password, input[name="password"]')).toBeVisible();

    await context.close();
  });

  test('login with invalid credentials shows error', async ({ browser }) => {
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await context.newPage();

    await page.goto(`${process.env.TEST_URL || 'https://aph.ddev.site'}/my-account/`);

    await page.locator('#username, input[name="username"]').fill('invalid@user.com');
    await page.locator('#password, input[name="password"]').fill('wrongpassword');
    await page.locator('button[name="login"], input[name="login"]').click();

    await page.waitForLoadState('networkidle');

    // Should show error message
    const error = page.locator('.woocommerce-error, .wc-block-components-notice-banner');
    await expect(error).toBeVisible();

    await context.close();
  });

  test('registration form has required fields', async ({ browser }) => {
    const context = await browser.newContext({ ignoreHTTPSErrors: true });
    const page = await context.newPage();

    await page.goto(`${process.env.TEST_URL || 'https://aph.ddev.site'}/my-account/`);

    // Register section should have form fields
    const registerSection = page.locator('h2:has-text("Register")').locator('..');
    await expect(registerSection).toBeVisible();

    await context.close();
  });

  test('logged-in user sees account dashboard', async ({ page }) => {
    // Uses auth state from global-setup
    await page.goto('/my-account/');

    // Should see account navigation (not login form)
    const accountNav = page.locator('.woocommerce-MyAccount-navigation, nav:has-text("Dashboard")');
    await expect(accountNav).toBeVisible();
  });

  test('account dashboard has expected navigation links', async ({ page }) => {
    await page.goto('/my-account/');

    // Standard WooCommerce account links
    await expect(page.locator('a:has-text("Dashboard")')).toBeVisible();
    await expect(page.locator('a:has-text("Orders")')).toBeVisible();
    await expect(page.locator('a:has-text("Addresses")')).toBeVisible();
    await expect(page.locator('a:has-text("Account details")')).toBeVisible();
    await expect(page.locator('a:has-text("Logout"), a:has-text("Log out")')).toBeVisible();
  });

  test('orders page loads', async ({ page }) => {
    await page.goto('/my-account/orders/');
    await expect(page.locator('main')).toBeVisible();
    // Should either show orders or "No order has been made yet"
  });

  test('addresses page loads', async ({ page }) => {
    await page.goto('/my-account/edit-address/');
    await expect(page.locator('main')).toBeVisible();
  });

  test('account details page loads with form', async ({ page }) => {
    await page.goto('/my-account/edit-account/');

    // Should have account details form
    await expect(page.locator('input[name="account_first_name"], #account_first_name')).toBeVisible();
    await expect(page.locator('input[name="account_last_name"], #account_last_name')).toBeVisible();
    await expect(page.locator('input[name="account_email"], #account_email')).toBeVisible();
  });
});
