import { test } from './setup/e2e-wp-test';
import { flushCache, validateHeaderAfterReload } from './utils/tools';

test.describe('Step 4: Cache Expiring', () => {
    test('Cache Expiring', async ({ page }) => {
        // Flush the cache
        await flushCache();

        // Go to the home page
        await page.goto('/sample-page');

        // Wait a second, so the caching is done
        await page.waitForTimeout(1000);

        // Reload and validate header status is 'hit'
        await validateHeaderAfterReload(page, 'status', 'hit');

        // Wait 5 seconds to expire the cache
        await page.waitForTimeout(5000);

        // Reload and validate header status is 'miss'
        await validateHeaderAfterReload(page, 'status', 'miss');

        // Reload and validate header status is 'hit'
        await validateHeaderAfterReload(page, 'status', 'hit');
    });
});