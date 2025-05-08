import { test } from './setup/e2e-wp-test';
import { flushCache, validateHeader } from './utils/tools';

test.describe('Step 4: Cache Expiring', () => {
    test('Cache Expiring', async ({ page }) => {
        // Flush the cache
        await flushCache('1:1:*');

        // Go to the home page
        await page.goto('/sample-page');

        // Wait a second, so the caching is done
        await page.waitForTimeout(1000);

        // Reload the same page to check if the status is hit
        const response = await page.reload();

        // Check if the status is set to hit
        await validateHeader(response, 'status', 'hit');

        // Wait 5 seconds to expire the cache
        await page.waitForTimeout(5000);

        // Reload the same page to check if the status changes to expire
        const response2 = await page.reload();

        // Check if the status is set to miss
        await validateHeader(response2, 'status', 'miss');

        // Reload the same page to check if the status is hit
        const response3 = await page.reload();

        // Check if the status is set to hit
        await validateHeader(response3, 'status', 'hit');
    });
});