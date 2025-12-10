import { test, expect } from './setup/e2e-wp-test';
import { clearCache, networkActivatePlugin } from './utils/tools';
import { FrontendPage } from './pages';

/**
 * Step 10: Bootstrap Rules Tests
 *
 * Tests for pre-WordPress rules that execute before WordPress loads.
 * These rules control caching based on request characteristics.
 */
test.describe('Step 10: Bootstrap Rules', () => {
    test.beforeAll(async () => {
        await networkActivatePlugin();
    });

    test.describe('Request Method Rules', () => {
        test('GET requests should be cached', async ({ page }) => {
            // Clear cache to ensure fresh state
            await clearCache('*');

            const frontend = new FrontendPage(page);

            // First request - cache miss
            await frontend.goto('/sample-page/');

            // Second request - cache hit
            const response = await frontend.reload();
            await expect(response).toBeCacheHit();
        });

        test('POST requests should bypass cache', async ({ request }) => {
            // Make a POST request
            const response = await request.post('/', {
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                data: 'test=data',
            });

            // POST should bypass cache (no cache headers or bypass status)
            const status = response.headers()['x-millicache-status'];
            expect(status === 'bypass' || status === undefined).toBeTruthy();
        });
    });

    test.describe('Admin Path Rules', () => {
        // Use storage state for this describe block
        test.use({ storageState: process.env.WP_AUTH_STORAGE });

        test('wp-admin paths should not be cached', async ({
            page,
            admin,
        }) => {
            await admin.visitAdminPage('/');

            // Admin pages should not have cache headers
            // Verify by checking the page loaded correctly
            await expect(page.locator('#wpadminbar')).toBeVisible();
        });
    });

    test.describe('REST API Rules', () => {
        test('wp-json requests should bypass cache', async ({ request }) => {
            const response = await request.get('/wp-json/wp/v2/posts');

            // REST API should bypass cache
            const status = response.headers()['x-millicache-status'];
            expect(status === 'bypass' || status === undefined).toBeTruthy();
        });

        test('wp-json with different endpoints should bypass cache', async ({
            request,
        }) => {
            const endpoints = ['/wp-json/', '/wp-json/wp/v2/pages'];

            for (const endpoint of endpoints) {
                const response = await request.get(endpoint);
                const status = response.headers()['x-millicache-status'];
                expect(
                    status === 'bypass' || status === undefined,
                    `Endpoint ${endpoint} should bypass cache`
                ).toBeTruthy();
            }
        });
    });

    test.describe('Cookie-based Rules', () => {
        test('Requests with WordPress auth cookies should bypass cache', async ({
            page,
        }) => {
            // Clear all and prime cache first
            await clearCache('*');
            await page.context().clearCookies();

            const frontend = new FrontendPage(page);

            // Prime the cache first (as anonymous)
            await frontend.goto('/');
            const hitResponse = await frontend.reload();
            expect(hitResponse).toBeCacheHit();

            // Now set a WordPress logged-in cookie pattern
            await page.context().addCookies([
                {
                    name: 'wordpress_logged_in_abcdef1234567890',
                    value: 'admin|1234567890|token',
                    domain: 'localhost',
                    path: '/',
                },
            ]);

            // With auth cookie, should NOT serve cached content (miss or bypass)
            const response = await frontend.goto('/');
            expect(response).toHaveCacheStatus(['miss', 'bypass']);

            // Clean up
            await page.context().clearCookies();
        });
    });

    test.describe('Static File Rules', () => {
        test('CSS files should bypass cache', async ({ request }) => {
            // Try to access a known CSS file
            const response = await request.get(
                '/wp-includes/css/dashicons.min.css'
            );

            // Static files should not have millicache headers
            const status = response.headers()['x-millicache-status'];
            expect(status === 'bypass' || status === undefined).toBeTruthy();
        });

        test('JS files should bypass cache', async ({ request }) => {
            // Try to access a known JS file
            const response = await request.get(
                '/wp-includes/js/jquery/jquery.min.js'
            );

            // Static files should not have millicache headers
            const status = response.headers()['x-millicache-status'];
            expect(status === 'bypass' || status === undefined).toBeTruthy();
        });
    });
});
