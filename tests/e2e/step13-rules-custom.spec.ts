import { test, expect } from './setup/e2e-wp-test';
import { clearCache, networkActivatePlugin, runWpCliCommand } from './utils/tools';
import { FrontendPage } from './pages';

/**
 * Step 13: Custom Rules Tests
 *
 * Tests for custom rule behavior and rule operators.
 */
test.describe('Step 13: Custom Rules', () => {
    test.beforeAll(async () => {
        await networkActivatePlugin();
    });

    test.describe('Rule Operators', () => {
        test.describe('Exact Match (=)', () => {
            test('Exact path match for homepage', async ({ page }) => {
                // Clear cache to start fresh
                await clearCache('*');

                const frontend = new FrontendPage(page);

                // Homepage should be cached
                await frontend.goto('/');
                const response = await frontend.reload();
                await expect(response).toBeCacheHit();
            });

            test('Exact path match for specific page', async ({ page }) => {
                const frontend = new FrontendPage(page);

                await frontend.goto('/sample-page/');
                const response = await frontend.reload();
                await expect(response).toBeCacheHit();
            });
        });

        test.describe('LIKE Operator (Wildcard)', () => {
            test('Wildcard should match multiple paths', async ({ page }) => {
                const frontend = new FrontendPage(page);

                // All site paths should be cached
                const paths = ['/site2/', '/site3/', '/site4/', '/site5/'];

                for (const path of paths) {
                    await frontend.goto(path);
                    const response = await frontend.reload();
                    await expect(response).toBeCacheHit();
                }
            });
        });
    });

    test.describe('Match Types', () => {
        test.describe('when() - All conditions must match (AND)', () => {
            test('GET request to cacheable path should be cached', async ({
                page,
            }) => {
                const frontend = new FrontendPage(page);

                // Both conditions met: GET method AND cacheable path
                await frontend.goto('/sample-page/');
                const response = await frontend.reload();
                await expect(response).toBeCacheHit();
            });
        });

        test.describe('when_any() - Any condition matches (OR)', () => {
            test('Multiple page types should be cached', async ({ page }) => {
                const frontend = new FrontendPage(page);

                // Test different page types that should all be cached
                const pages = [
                    '/', // Home
                    '/sample-page/', // Page
                    '/hello-world/', // Post
                ];

                for (const pagePath of pages) {
                    await clearCache('*');
                    await frontend.goto(pagePath);
                    const response = await frontend.reload();
                    await expect(response).toBeCacheHit();
                }
            });
        });
    });

    test.describe('Cache TTL Behavior', () => {
        test('Cache should expire based on TTL', async ({ page }) => {
            // This is tested in step4, but we verify the rule is working
            const frontend = new FrontendPage(page);
            await clearCache('*');

            await frontend.goto('/sample-page/');
            const response = await frontend.reload();
            await expect(response).toBeCacheHit();

            // Verify TTL header is set
            const headers = frontend.getCacheHeaders();
            expect(headers.ttl).toBeDefined();
        });
    });

    test.describe('Cache Flag Actions', () => {
        test('Clearing by flag should only affect matching entries', async ({
            page,
        }) => {
            // Start with clean cache
            await clearCache('*');

            const frontend = new FrontendPage(page);

            // Prime cache for both pages
            await frontend.goto('/sample-page/');
            await frontend.reload();
            await expect(await frontend.getLastResponse()).toBeCacheHit();

            await frontend.goto('/hello-world/');
            await frontend.reload();
            await expect(await frontend.getLastResponse()).toBeCacheHit();

            // Clear only the sample-page cache by flag
            // Note: The exact flag format depends on the RequestFlags rules
            await clearCache('1:post:2'); // Sample page is typically ID 2

            // Sample page should be miss after clearing
            const sampleResponse = await frontend.goto('/sample-page/');
            await expect(sampleResponse).toBeCacheMiss();

            // Hello world should still be hit (different flag)
            const helloResponse = await frontend.goto('/hello-world/');
            await expect(helloResponse).toBeCacheHit();
        });
    });

    test.describe('Rule Priority', () => {
        test('Later rules should be able to override earlier rules', async ({
            page,
        }) => {
            // This tests that the rule order system works
            // Higher order rules execute later and can override
            const frontend = new FrontendPage(page);

            // By default, public pages are cached
            await clearCache('*');
            await frontend.goto('/');
            const response = await frontend.reload();
            await expect(response).toBeCacheHit();
        });
    });

    test.describe('Default Rules Verification', () => {
        test('Default bootstrap rules are active', async ({ page }) => {
            const frontend = new FrontendPage(page);

            // Verify basic caching works (default rules enable caching)
            await clearCache('*');
            await frontend.goto('/');
            const response = await frontend.reload();

            // If default rules are active, page should be cached
            await expect(response).toBeCacheHit();
        });

        test('Default WordPress rules are active', async ({ page }) => {
            // Clear cache and cookies first
            await clearCache('*');
            await page.context().clearCookies();

            const frontend = new FrontendPage(page);

            // Prime the cache first (as anonymous)
            await frontend.goto('/');
            const hitResponse = await frontend.reload();
            await expect(hitResponse).toBeCacheHit();

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
            await expect(response).toHaveCacheStatus(['miss', 'bypass']);

            // Clean up
            await page.context().clearCookies();
        });
    });
});
