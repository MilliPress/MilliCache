import { test } from './setup/e2e-wp-test';
import { clearCache, validateHeader } from './utils/tools';

/**
 * Test cache flag generation for different content types.
 */
test.describe('Step 5: Cache RequestFlags', () => {
    test('Check flags by context', async ({ page }) => {
        // URLs and their expected cache flags
        // Multisite prefix for flags is "{site_id}:".
        const targets = [
            { url: '/hello-world/', expectedFlags: ['1:post:'] },
            { url: '/sample-page/', expectedFlags: ['1:post:'] },
        ];

        // Flush the cache
        await clearCache('1:*');

        // Check pages for cache flags
        for (const { url, expectedFlags } of targets) {
            let response = await page.goto(url);
            await validateHeader(response, 'status', 'miss');

            response = await page.reload();
            await validateHeader(response, 'status', 'hit');
            await validateHeader(response, 'flags', expectedFlags, false);
        }
    });
});