import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';
import path from 'path';

// Load test environment variables
dotenv.config({ path: path.resolve(__dirname, '.env.test') });

const baseURL = process.env.TEST_URL || 'https://aph.ddev.site';

export default defineConfig({
  testDir: './e2e',
  fullyParallel: false, // WooCommerce cart state is shared — run sequentially
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1, // Single worker to avoid cart/session conflicts
  reporter: process.env.CI ? 'github' : 'html',

  use: {
    baseURL,
    ignoreHTTPSErrors: true, // DDEV uses self-signed certs
    screenshot: 'only-on-failure',
    trace: 'on-first-retry',
    actionTimeout: 15000,
    navigationTimeout: 30000,
  },

  projects: [
    // Setup: create test user if needed
    {
      name: 'setup',
      testMatch: /global-setup\.ts/,
    },
    // Main test suite
    {
      name: 'e-commerce',
      use: {
        ...devices['Desktop Chrome'],
        storageState: './fixtures/auth-state.json',
      },
      dependencies: ['setup'],
    },
    // Mobile viewport
    {
      name: 'mobile',
      use: {
        ...devices['iPhone 14'],
        storageState: './fixtures/auth-state.json',
      },
      dependencies: ['setup'],
    },
  ],
});
