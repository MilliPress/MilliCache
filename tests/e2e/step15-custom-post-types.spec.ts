import { test, expect } from './setup/e2e-wp-test';
import { clearCache, getCategoryUrl, networkActivatePlugin } from './utils/tools';
import { FrontendPage } from './pages';

/**
 * Step 15: Custom Post Type Tests
 *
 * Tests for caching behavior with custom post types.
 * Plugin-specific CPT tests (e.g., WooCommerce products) are in step9-plugins.spec.ts
 */
test.describe('Step 15: Custom Post Types', () => {
    test.beforeAll(async () => {
        await networkActivatePlugin();
        await clearCache('*');
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

            // Get the category URL dynamically (handles /category/uncategorized/ vs /blog/category/uncategorized/)
            const categoryUrl = await getCategoryUrl('uncategorized');

            // Test category archive (acts like a post type archive)
            await frontend.goto(categoryUrl);

            if ((await frontend.getLastResponse())?.status() === 200) {
                const response = await frontend.reload();
                await expect(response).toBeCacheHit();
            }
        });

        test('Archive flags should include post type identifier', async ({
            page,
        }) => {
            const frontend = new FrontendPage(page);

            // Get the category URL dynamically (handles /category/uncategorized/ vs /blog/category/uncategorized/)
            const categoryUrl = await getCategoryUrl('uncategorized');

            await frontend.goto(categoryUrl);
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
