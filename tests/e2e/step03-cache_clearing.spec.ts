import { test, expect } from './setup/e2e-wp-test';
import { login, logout } from './utils/auth';
import { clearCache } from './utils/tools';
import { FrontendPage } from './pages';

test.describe('Step 3: Cache Clearing', () => {
    test('Clear cache via admin bar', async ({ page, admin }) => {
        // First, prime the cache
        await clearCache('*');
        await page.context().clearCookies();

        const frontend = new FrontendPage(page);
        await frontend.goto('/');
        await frontend.reload();

        // Now login and visit admin
        await login(page);
        await admin.visitAdminPage('/network');

        // Find and click the flush button directly
        const adminBarFlushButton = page.locator('#wp-admin-bar-millicache');
        await expect(adminBarFlushButton).toBeVisible();

        // Click the main menu (this triggers the flush on some setups)
        // or hover and click the submenu
        await adminBarFlushButton.click();
        await page.waitForLoadState('networkidle');

        // Logout
        await logout(page);
        await page.context().clearCookies();

        // Validate the cache was cleared (should be miss)
        const response = await frontend.goto('/');
        await expect(response).toBeCacheMiss();
    });

    test('Page is cached after first request', async ({ page }) => {
        // Clear cache to ensure isolated test
        await clearCache('*');
        await page.context().clearCookies();

        const frontend = new FrontendPage(page);

        // First request - prime the cache
        await frontend.goto('/sample-page/');

        // Second request - should be cached
        const response = await frontend.reload();
        await expect(response).toBeCacheHit();
    });

    test('Clear cache via WP-CLI', async ({ page }) => {
        // Create a new browser context to ensure clean state
        const context = await page.context().browser()!.newContext();
        const newPage = await context.newPage();

        try {
            const frontend = new FrontendPage(newPage);

            // Clear first and prime
            await clearCache('*');
            await frontend.goto('/hello-world/');

            // Verify it gets cached
            const hitResponse = await frontend.reload();
            await expect(hitResponse).toBeCacheHit();

            // Now clear via CLI
            await clearCache('*');

            // Open new page in same context to avoid any page-level cache
            const checkPage = await context.newPage();
            const checkFrontend = new FrontendPage(checkPage);

            // Should be miss after clearing
            const response = await checkFrontend.goto('/hello-world/');
            await expect(response).toBeCacheMiss();

            await checkPage.close();
        } finally {
            await newPage.close();
            await context.close();
        }
    });
});