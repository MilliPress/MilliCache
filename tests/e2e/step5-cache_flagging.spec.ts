import { test } from './setup/e2e-wp-test';
import { flushCache, validateHeader } from './utils/tools';

/**
 * Clear cache before running the tests.
 */
test.describe('Step 5: Cache Flagging', () => {
    test('Cache flagging', async ({ page }) => {
        // Targets and their expected cache flags
        const targets = {
            'Hello World!': ['post:1:1', 'site:1:1'],
            'Sample Page': ['post:1:2', 'site:1:1'],
            'admin': ['author:1:1', 'site:1:1'],
        };

        // Flush the cache
        await flushCache('site:1:1');

        // Check pages for cache flags
        for (const [linkTitle, expectedFlags] of Object.entries(targets)) {
            await page.goto('/');

            const href = await page.getByText(linkTitle).getAttribute('href');

            let response = await page.goto(href);
            await validateHeader(response, 'status', 'miss');

            response = await page.reload();
            await validateHeader(response, 'status', 'hit');
            await validateHeader(response, 'flags', expectedFlags, false);
        }
    });
});