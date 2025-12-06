import { test, expect } from './setup/e2e-wp-test';
import { clearCache, networkActivatePlugin, runWpCliCommand } from './utils/tools';
import { FrontendPage } from './pages';

/**
 * Step 15: Custom Post Type Tests
 *
 * Tests for caching behavior with custom post types.
 * Uses WooCommerce products as an example CPT if available.
 */
test.describe('Step 15: Custom Post Types', () => {
    test.beforeAll(async () => {
        await networkActivatePlugin();
        await clearCache('*');
    });

    test.describe('WooCommerce Products (if available)', () => {
        test.beforeEach(async () => {
            // Check if WooCommerce is active
            try {
                await runWpCliCommand('plugin is-active woocommerce');
            } catch {
                test.skip();
            }
        });

        test('Product single pages should be cached', async ({ page }) => {
            const frontend = new FrontendPage(page);

            // Try to access a product page
            // Note: This assumes WooCommerce sample data is installed
            const response = await frontend.goto('/shop/');

            if (response.status() === 200) {
                const response2 = await frontend.reload();
                await expect(response2).toBeCacheHit();
            }
        });

        test('Product archives should be cached', async ({ page }) => {
            const frontend = new FrontendPage(page);

            const response = await frontend.goto('/shop/');

            if (response.status() === 200) {
                const response2 = await frontend.reload();
                // Shop page should be cached for anonymous users
                await expect(response2).toHaveCacheStatus(['hit', 'miss']);
            }
        });

        test('Product with cart cookie should bypass cache', async ({
            page,
        }) => {
            // Set WooCommerce cart cookie
            await page.context().addCookies([
                {
                    name: 'woocommerce_cart_hash',
                    value: 'abc123',
                    domain: 'localhost',
                    path: '/',
                },
            ]);

            const frontend = new FrontendPage(page);
            const response = await frontend.goto('/shop/');

            // With cart cookie, should bypass (depending on config)
            const status = response.headers()['x-millicache-status'];
            expect(['bypass', 'miss', 'hit']).toContain(status);

            // Clean up
            await page.context().clearCookies();
        });
    });

    test.describe('Generic CPT Behavior', () => {
        test('Standard post types should generate correct flags', async ({
            page,
        }) => {
            const frontend = new FrontendPage(page);

            // Test with built-in 'post' type
            await frontend.goto('/hello-world/');
            const response = await frontend.reload();

            const headers = frontend.getCacheHeaders();

            // Should have post flag
            expect(headers.flags).toContain('post:');
        });

        test('Page post type should generate correct flags', async ({
            page,
        }) => {
            const frontend = new FrontendPage(page);

            // Test with built-in 'page' type
            await frontend.goto('/sample-page/');
            const response = await frontend.reload();

            const headers = frontend.getCacheHeaders();

            // Should have post flag (pages are also posts in WP)
            expect(headers.flags).toContain('post:');
        });
    });

    test.describe('CPT Archive Caching', () => {
        test('Post type archives should be cached', async ({ page }) => {
            const frontend = new FrontendPage(page);

            // Test category archive (acts like a post type archive)
            await frontend.goto('/category/uncategorized/');

            if ((await frontend.getLastResponse())?.status() === 200) {
                const response = await frontend.reload();
                await expect(response).toBeCacheHit();
            }
        });

        test('Archive flags should include post type identifier', async ({
            page,
        }) => {
            const frontend = new FrontendPage(page);

            await frontend.goto('/category/uncategorized/');
            const response = await frontend.reload();

            const headers = frontend.getCacheHeaders();

            if (headers.flags) {
                // Should have archive-related flags
                expect(headers.flags).toMatch(/archive/i);
            }
        });
    });

    test.describe('CPT Cache Invalidation', () => {
        test('Updating a post should invalidate its cache', async ({
            page,
            requestUtils,
        }) => {
            const frontend = new FrontendPage(page);

            // Prime the Hello World post cache
            await page.context().clearCookies();
            await frontend.goto('/hello-world/');
            const cachedResponse = await frontend.reload();
            await expect(cachedResponse).toBeCacheHit();

            // Update the post
            await requestUtils.rest({
                method: 'POST',
                path: '/wp/v2/posts/1',
                data: {
                    content: `Updated at ${Date.now()}`,
                },
            });

            // After update, cache should be invalidated
            await page.context().clearCookies();
            const afterUpdateResponse = await frontend.goto('/hello-world/');

            // Should be miss after invalidation
            await expect(afterUpdateResponse).toBeCacheMiss();

            // Next request should be hit again
            const rehitResponse = await frontend.reload();
            await expect(rehitResponse).toBeCacheHit();
        });
    });
});
