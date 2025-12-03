import { test } from './setup/e2e-wp-test';
import { clearCache, validateHeader } from './utils/tools';

/**
 * Clear cache before running the tests.
 */
test.describe('Step 5: Cache RequestFlags', () => {
    test('Check flags by context', async ({ page }) => {
        // Targets and their expected cache flags
        // Multisite prefix for flags is "{site_id}:".
        const targets = {
            'Hello World!': ['1:post:1'],
            'Sample Page': ['1:post:2'],
        };

        // Flush the cache
        await clearCache('1:*');

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