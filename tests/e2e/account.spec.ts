import { test, expect } from '@playwright/test';

const baseURL = process.env.TEST_URL || 'https://aph.ddev.site';

test.describe('My Account — Unauthenticated', () => {
  // These tests use a fresh browser context (no saved auth)
  test.use({ storageState: { cookies: [], origins: [] } });

  test('my account page loads with login form', async ({ page }) => {
    await page.goto(`${baseURL}/my-account/`);
    await expect(page).toHaveTitle(/My account/);

    // Should show login and register headings
    await expect(page.locator('h2:has-text("Login")')).toBeVisible();
    await expect(page.locator('h2:has-text("Register")')).toBeVisible();
  });

  test('login form has username and password fields', async ({ page }) => {
    await page.goto(`${baseURL}/my-account/`);

    await expect(page.locator('#username')).toBeVisible();
    await expect(page.locator('#password')).toBeVisible();
  });

  test('login with invalid credentials shows error', async ({ page }) => {
    await page.goto(`${baseURL}/my-account/`);

    await page.locator('#username').fill('invalid@user.com');
    await page.locator('#password').fill('wrongpassword');
    await page.locator('button[name="login"], input[name="login"]').click();

    await page.waitForLoadState('networkidle');

    const error = page.locator('.woocommerce-error, .wc-block-components-notice-banner');
    await expect(error).toBeVisible();
  });

  test('registration form is visible', async ({ page }) => {
    await page.goto(`${baseURL}/my-account/`);

    await expect(page.locator('h2:has-text("Register")')).toBeVisible();
  });
});

test.describe('My Account — Authenticated', () => {
  // These tests use saved auth state from global-setup

  test('logged-in user sees account dashboard', async ({ page }) => {
    await page.goto('/my-account/');

    // Should NOT show login form — should show logged-in content
    await expect(page.locator('h2:has-text("Login")')).not.toBeVisible();
    // Should show user email or account content
    await expect(page.locator('main')).toBeVisible();
  });

  test('account dashboard does not show login form', async ({ page }) => {
    await page.goto('/my-account/');

    // Logged-in users should not see the login/register forms
    await expect(page.locator('h2:has-text("Register")')).not.toBeVisible();
  });

  test('orders page loads', async ({ page }) => {
    await page.goto('/my-account/orders/');
    await expect(page.locator('main')).toBeVisible();
  });

  test('edit address page loads', async ({ page }) => {
    await page.goto('/my-account/edit-address/');
    await expect(page.locator('main')).toBeVisible();
  });

  test('account details page loads', async ({ page }) => {
    await page.goto('/my-account/edit-account/');
    await expect(page.locator('main')).toBeVisible();
  });
});
