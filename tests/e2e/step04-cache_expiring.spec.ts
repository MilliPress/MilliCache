import { test, expect } from './setup/e2e-wp-test';
import { clearCache } from './utils/tools';
import { FrontendPage } from './pages';
import {
    waitForCachePopulation,
    waitForCacheExpiration,
} from './utils/wait-helpers';

test.describe('Step 4: Cache Expiring', () => {
    test('Cache Expiring', async ({ page }) => {
        const frontend = new FrontendPage(page);

        // Flush the cache
        await clearCache();

        // Go to sample page - first request is a miss
        await frontend.goto('/sample-page');

        // Wait for cache to be populated (polling instead of hardcoded wait)
        const hitResponse = await waitForCachePopulation(page, '/sample-page');
        await expect(hitResponse).toBeCacheHit();

        // Wait for cache to expire (polling instead of hardcoded 5s wait)
        // wp-env is configured with 5s TTL for testing
        const missResponse = await waitForCacheExpiration(page, {
            maxWaitMs: 10000,
            pollIntervalMs: 500,
        });
        await expect(missResponse).toBeCacheMiss();

        // Verify cache is repopulated after expiration
        const rehitResponse = await frontend.reload();
        await expect(rehitResponse).toBeCacheHit();
    });
});