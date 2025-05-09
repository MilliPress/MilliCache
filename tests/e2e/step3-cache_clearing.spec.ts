import { test, expect } from './setup/e2e-wp-test';
import { login, logout } from './utils/auth';
import { flushCache, validateHeader } from './utils/tools';

test.describe('Step 3: Cache Clearing', () => {
    test('Clear cache', async ({ page, admin }) => {
        // Login to the admin dashboard
        await login(page);

        // Visit the admin dashboard
        await admin.visitAdminPage('/network');

        // Flush Button
        const adminBarFlushButton = page.locator('#wp-admin-bar-millicache');

        // Check if the button is visible and click it
        await expect(adminBarFlushButton).toBeVisible().then(async () => {
            // Click the button
            await adminBarFlushButton.click();

            // Wait for the page to load
            await page.waitForLoadState();
        });

        // Logout
        await logout(page);

        // Reload the page
        await page.reload();

        // Check if the button is not visible
        await expect(adminBarFlushButton).not.toBeVisible();

        // Validate the cache is not set
        const response = await page.goto('/');
        await validateHeader(response, 'status', 'miss');
    });

    test('Page is cached', async ({ page }) => {
        const response = await page.goto('/');
        await validateHeader(response, 'status', 'hit');
    });

    test('Clear cache again', async ({ page }) => {
        await flushCache();
        const response = await page.goto('/');
        await validateHeader(response, 'status', 'miss');
    });
});