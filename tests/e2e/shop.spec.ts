import { test, expect } from '@playwright/test';

test.describe('Shop & Browse', () => {
  test('shop page loads', async ({ page }) => {
    await page.goto('/shop/');
    await expect(page).toHaveTitle(/Shop/);
    await expect(page.locator('main')).toBeVisible();
  });

  test('shop has link to browse products', async ({ page }) => {
    await page.goto('/shop/');
    const browseLink = page.locator('a:has-text("Browse the Shop")');
    await expect(browseLink).toBeVisible();
  });

  test('product search results page loads with products', async ({ page }) => {
    await page.goto('/search-results/?fwp_content_types=product');
    await expect(page).toHaveTitle(/Search Results/);

    // Should show result count
    const resultsHeading = page.locator('h2:has-text("results found")');
    await expect(resultsHeading).toBeVisible();

    // Extract count and verify products exist
    const text = await resultsHeading.textContent();
    const count = parseInt(text?.match(/(\d+)/)?.[1] || '0');
    expect(count).toBeGreaterThan(0);
  });

  test('product search returns relevant results', async ({ page }) => {
    await page.goto('/search-results/?fwp_content_types=product');

    // Use the search box
    const searchInput = page.locator('input[placeholder*="keywords"], input[type="text"]').first();
    await searchInput.fill('braille');
    await page.locator('button:has-text("Submit")').click();

    // Wait for results to update (FacetWP uses AJAX)
    await page.waitForTimeout(2000);

    // Should still have results
    const resultsHeading = page.locator('h2:has-text("results found")');
    await expect(resultsHeading).toBeVisible();
  });

  test('pagination exists on search results', async ({ page }) => {
    await page.goto('/search-results/?fwp_content_types=product');

    // Should have pagination navigation
    const pagination = page.locator('.facetwp-pager').first();
    await expect(pagination).toBeVisible();
  });

  test('product categories link to filtered results', async ({ page }) => {
    await page.goto('/shop/');

    // Navigate to a product category
    await page.goto('/product-category/expanded-core-curriculum/');
    await expect(page.locator('main')).toBeVisible();
  });
});
