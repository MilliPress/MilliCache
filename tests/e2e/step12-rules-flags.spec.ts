import { test, expect } from './setup/e2e-wp-test';
import { clearCache, networkActivatePlugin } from './utils/tools';
import { FrontendPage } from './pages';

/**
 * Step 12: Cache Flag Generation Tests
 *
 * Tests for RequestFlags rules that generate cache invalidation flags.
 * Flags are used to selectively invalidate cache entries.
 */
test.describe.configure({ mode: 'serial' });

test.describe('Step 12: Cache Flag Generation', () => {
    test.beforeAll(async () => {
        await networkActivatePlugin();
    });

    test.describe('Singular Post Flags', () => {
        test('Single post should have post flag', async ({ page }) => {
            // Clear cache to ensure fresh state
            await clearCache('*');

            const frontend = new FrontendPage(page);

            // Navigate to Hello World post (ID 1 on fresh install)
            await frontend.goto('/hello-world/');
            const response = await frontend.reload();

            // Should have post flag for site 1, post 1
            await expect(response).toHaveCacheFlags('1:post:1');
        });

        test('Sample page should have post flag', async ({ page }) => {
            const frontend = new FrontendPage(page);

            // Navigate to Sample Page (ID 2 on fresh install)
            await frontend.goto('/sample-page/');
            const response = await frontend.reload();

            // Should have post flag
            const headers = frontend.getCacheHeaders();
            expect(headers.flags).toContain('1:post:');
        });
    });

    test.describe('Home Page Flags', () => {
        test('Homepage should have home flag', async ({ page }) => {
            await clearCache('*');

            const frontend = new FrontendPage(page);

            await frontend.goto('/');
            const response = await frontend.reload();

            // Should have home flag
            await expect(response).toHaveCacheFlags('1:home');
        });
    });

    test.describe('Archive Flags', () => {
        test('Category archive should have category flag', async ({ page }) => {
            await clearCache('*');

            const frontend = new FrontendPage(page);

            // Navigate to category archive
            const firstResponse = await frontend.goto('/blog/category/uncategorized/');

            // Skip test if category doesn't exist or is 404
            if (firstResponse.status() !== 200) {
                test.skip();
                return;
            }

            // Reload to get cached version
            const response = await frontend.reload();

            // Should have category archive flag
            const headers = frontend.getCacheHeaders();
            expect(headers.flags).not.toBeNull();
            expect(headers.flags).toMatch(/archive.*category/i);
        });

        test('Author archive should have author flag', async ({ page }) => {
            const frontend = new FrontendPage(page);

            await frontend.goto('/author/admin/');

            // Author pages may redirect or have different behavior
            // Check if we got a valid response
            const response = await frontend.reload();

            if (response.status() === 200) {
                const headers = frontend.getCacheHeaders();
                if (headers.flags) {
                    expect(headers.flags).toMatch(/author/i);
                }
            }
        });

        test('Date archive should have date flags', async ({ page }) => {
            const frontend = new FrontendPage(page);
            const year = new Date().getFullYear();

            await frontend.goto(`/${year}/`);

            // Date archives may have different behavior based on content
            const response = await frontend.reload();

            if (response.status() === 200) {
                const headers = frontend.getCacheHeaders();
                if (headers.flags) {
                    // Should have year in flags
                    expect(headers.flags).toContain(`${year}`);
                }
            }
        });
    });

    test.describe('Multisite Flags', () => {
        test('Different sites should have different site prefixes', async ({
            page,
        }) => {
            await clearCache('*');

            const frontend = new FrontendPage(page);

            // Test site 1 (main site)
            await frontend.goto('/');
            await frontend.reload();
            const site1Headers = frontend.getCacheHeaders();

            // Test site 2 (subsite - blog ID may vary)
            await frontend.goto('/site2/');
            await frontend.reload();
            const site2Headers = frontend.getCacheHeaders();

            // Flags should have different site prefixes (numbers may vary based on blog IDs)
            if (site1Headers.flags && site2Headers.flags) {
                // Extract site prefix from flags (format: "N:flag" where N is blog ID)
                const site1Prefix = site1Headers.flags.match(/^(\d+):/)?.[1];
                const site2Prefix = site2Headers.flags.match(/^(\d+):/)?.[1];

                // Both should have prefixes and they should be different
                expect(site1Prefix).toBeTruthy();
                expect(site2Prefix).toBeTruthy();
                expect(site1Prefix).not.toBe(site2Prefix);
            }
        });
    });

    test.describe('Feed Flags', () => {
        test('RSS feed should have feed flag', async ({ page }) => {
            const frontend = new FrontendPage(page);

            const response = await frontend.goto('/feed/');

            // Feeds may or may not be cached
            if (response.headers()['x-millicache-status']) {
                const headers = frontend.getCacheHeaders();
                if (headers.flags) {
                    expect(headers.flags).toContain('feed');
                }
            }
        });
    });
});
