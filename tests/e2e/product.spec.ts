import { test, expect } from '@playwright/test';

test.describe('Product Page', () => {
  // Braille Bridge is a grouped product with multiple variants
  const productURL = '/product/braille-bridge/';

  test('product page loads with title', async ({ page }) => {
    await page.goto(productURL);
    await expect(page).toHaveTitle(/Braille Bridge/);
    await expect(page.locator('h1')).toHaveText('Braille Bridge');
  });

  test('product image loads from S3', async ({ page }) => {
    await page.goto(productURL);

    // Product image should be from S3 (aph-media.s3.amazonaws.com or media.aph.org)
    const productImage = page.locator('figure img').first();
    await expect(productImage).toBeVisible();

    const src = await productImage.getAttribute('src');
    expect(src).toBeTruthy();
    // Image should load successfully (not 404)
    if (src) {
      const response = await page.request.get(src);
      expect(response.status()).toBe(200);
    }
  });

  test('product displays price', async ({ page }) => {
    await page.goto(productURL);

    // Should show price range for grouped product
    const priceArea = page.locator('main');
    await expect(priceArea).toContainText('$');
  });

  test('product has add to cart button', async ({ page }) => {
    await page.goto(productURL);

    const addToCart = page.locator('button[name="add-to-cart"], button.single_add_to_cart_button').first();
    await expect(addToCart).toBeVisible();
  });

  test('grouped product shows quantity inputs for variants', async ({ page }) => {
    await page.goto(productURL);

    // Grouped products have multiple quantity spinbuttons
    const qtyInputs = page.locator('input[type="number"]');
    const count = await qtyInputs.count();
    expect(count).toBeGreaterThanOrEqual(2); // At least 2 variants
  });

  test('product shows category links', async ({ page }) => {
    await page.goto(productURL);

    // Should have category links
    const categories = page.locator('a[href*="product-category"]');
    const count = await categories.count();
    expect(count).toBeGreaterThan(0);
  });

  test('product has description section', async ({ page }) => {
    await page.goto(productURL);

    const description = page.locator('h2:has-text("Product Description")');
    await expect(description).toBeVisible();
  });

  test('product shows related/other products', async ({ page }) => {
    await page.goto(productURL);

    const otherProducts = page.locator('h2:has-text("Other Products")');
    await expect(otherProducts).toBeVisible();
  });

  test('product has breadcrumb navigation', async ({ page }) => {
    await page.goto(productURL);

    const breadcrumb = page.locator('nav:has-text("Breadcrumb"), [aria-label*="Breadcrumb"]');
    await expect(breadcrumb).toBeVisible();
    await expect(breadcrumb).toContainText('Home');
  });

  test('add grouped product to cart', async ({ page }) => {
    await page.goto(productURL);

    // Set quantity of first variant to 1
    const firstQty = page.locator('input[type="number"]').first();
    await firstQty.fill('1');

    // Click add to cart
    await page.locator('button[name="add-to-cart"], button.single_add_to_cart_button').first().click();

    // Should see cart update — either redirect to cart or show success notice
    await page.waitForLoadState('networkidle');

    // Verify we're on the cart page or a success message appeared
    // WooCommerce may redirect to cart or show an inline notice
    const onCartPage = page.url().includes('/cart/');
    const successNotice = page.locator('.woocommerce-message, .wc-block-components-notice-banner');
    const viewCartLink = page.locator('a:has-text("View cart"), a:has-text("View Cart")').first();

    // At least one of these should be true after add-to-cart
    const hasNotice = await successNotice.isVisible().catch(() => false);
    const hasViewCart = await viewCartLink.isVisible().catch(() => false);
    expect(onCartPage || hasNotice || hasViewCart).toBeTruthy();
  });
});
