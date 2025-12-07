import { test, expect } from './setup/e2e-wp-test';
import { clearCache, networkActivatePlugin } from './utils/tools';
import { login, logout } from './utils/auth';
import { FrontendPage } from './pages';

/**
 * Step 11: WordPress Rules Tests
 *
 * Tests for rules that execute after WordPress loads.
 * These rules control caching based on WordPress state.
 */
test.describe('Step 11: WordPress Rules', () => {
    test.beforeAll(async () => {
        await networkActivatePlugin();
        await clearCache('*');
    });

    test.describe('Logged-in User Rules', () => {
        test('Logged-out users should receive cached content', async ({
            page,
        }) => {
            // Clear cache and cookies to start fresh
            await clearCache('*');
            await logout(page);
            await page.context().clearCookies();

            const frontend = new FrontendPage(page);

            // Prime the cache with first request
            await frontend.goto('/hello-world/');

            // Second request should be cached
            const response = await frontend.reload();
            await expect(response).toBeCacheHit();
        });

        test('Logged-in users should bypass cache', async ({ page }) => {
            await login(page);

            const frontend = new FrontendPage(page);
            const response = await frontend.goto('/');

            await expect(response).toBeCacheBypassed();

            // Clean up
            await logout(page);
        });

        test('Cache hit after logging out', async ({ page }) => {
            // Start logged in
            await login(page);

            const frontend = new FrontendPage(page);

            // Request as logged-in user (bypass)
            const bypassResponse = await frontend.goto('/');
            await expect(bypassResponse).toBeCacheBypassed();

            // Logout
            await logout(page);
            await page.context().clearCookies();

            // First request as logged-out user primes the cache
            await frontend.goto('/');
            // Second request should be a cache hit
            const hitResponse = await frontend.reload();
            await expect(hitResponse).toBeCacheHit();
        });
    });

    test.describe('Response Code Rules', () => {
        test('404 pages should not be cached', async ({ page }) => {
            const frontend = new FrontendPage(page);

            // Request a non-existent page
            const response = await frontend.goto(
                '/this-page-definitely-does-not-exist-12345/'
            );

            // Verify it's a 404
            expect(response.status()).toBe(404);

            // 404 should not be cached (either no header or miss/bypass)
            const status = response.headers()['x-millicache-status'];
            expect(status !== 'hit').toBeTruthy();
        });

        test('200 OK responses should be cached', async ({ page }) => {
            await clearCache('*');

            const frontend = new FrontendPage(page);

            // Request a valid page
            const response1 = await frontend.goto('/sample-page/');
            expect(response1.status()).toBe(200);

            // Second request should be cached
            const response2 = await frontend.reload();
            await expect(response2).toBeCacheHit();
        });
    });

    test.describe('AJAX and Cron Rules', () => {
        test('admin-ajax.php requests should bypass cache', async ({
            request,
        }) => {
            const response = await request.post('/wp-admin/admin-ajax.php', {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                data: 'action=heartbeat',
            });

            const status = response.headers()['x-millicache-status'];
            expect(status === 'bypass' || status === undefined).toBeTruthy();
        });
    });

    test.describe('Search Results', () => {
        test('Search results pages behavior', async ({ page }) => {
            const frontend = new FrontendPage(page);

            // Search for something
            const response = await frontend.goto('/?s=test');

            // Search results may or may not be cached depending on config
            // Just verify the page loads and has valid response
            expect(response.status()).toBe(200);
        });
    });
});
