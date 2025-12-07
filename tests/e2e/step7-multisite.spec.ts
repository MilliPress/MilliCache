import { test, expect } from './setup/e2e-wp-test';
import { clearCache } from './utils/tools';
import { FrontendPage } from './pages';

test.describe('Step 7: Network Caching & Flushing', () => {
    const sites = 5;

    // Deterministic test paths per site (no more random selection)
    const siteTestPaths: Record<number, string[]> = {
        1: ['/', '/sample-page/', '/hello-world/'],
        2: ['/site2/', '/site2/sample-page/', '/site2/hello-world/'],
        3: ['/site3/', '/site3/sample-page/', '/site3/hello-world/'],
        4: ['/site4/', '/site4/sample-page/', '/site4/hello-world/'],
        5: ['/site5/', '/site5/sample-page/', '/site5/hello-world/'],
    };

    // Track tested paths for validation
    const testedPaths: Array<{ path: string; flags: string }> = [];

    test('Check network for active plugin', async ({ page }) => {
        const frontend = new FrontendPage(page);

        for (let i = 1; i <= sites; i++) {
            const path = i === 1 ? '/' : `/site${i}/`;
            // First request primes the cache
            await frontend.goto(path);
            // Second request should be a cache hit
            const response = await frontend.reload();
            await expect(response).toBeCacheHit();
        }
    });

    test('Network Caching with deterministic paths', async ({ page }) => {
        const frontend = new FrontendPage(page);

        // For each Multisite, test specific paths deterministically
        for (let siteId = 1; siteId <= sites; siteId++) {
            const paths = siteTestPaths[siteId];

            // Test one path per site (first path after home)
            const testPath = paths[1] || paths[0];

            // First request primes the cache
            await frontend.goto(testPath);

            // Reload to verify caching (should be a hit)
            const response2 = await frontend.reload();
            await expect(response2).toBeCacheHit();

            // Record the tested path and flags
            const headers = frontend.getCacheHeaders();
            testedPaths.push({
                path: testPath,
                flags: headers.flags || '',
            });
        }

        // Ensure we tested all sites
        expect(testedPaths.length).toBe(sites);
    });

    test('Flush network cache & validate sites', async ({ page }) => {
        const frontend = new FrontendPage(page);

        // Flush the network cache
        await clearCache();

        // Validate all sites show cache miss after flush
        for (let i = 1; i <= sites; i++) {
            const path = i === 1 ? '/' : `/site${i}/`;
            const response = await frontend.goto(path);
            await expect(response).toBeCacheMiss();
        }
    });
});