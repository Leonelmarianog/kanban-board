import { test, expect } from '@playwright/test';

// See here how to get started:
// https://playwright.dev/docs/intro
test('visits the app root url', async ({ page }) => {
  // Navigate to the app first to set localStorage in the correct origin
  await page.goto('/');
  // Set auth token to simulate authenticated user
  await page.evaluate(() => {
    localStorage.setItem('authToken', 'test-token');
  });
  // Reload to apply the auth state
  await page.reload();
  await expect(page.locator('h1')).toHaveText('My board');
});
