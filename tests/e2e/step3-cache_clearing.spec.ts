import { test } from './setup/e2e-wp-test';
import { flushCache } from './utils/flushCache';
import { validateHeader } from './utils/tools';

test.describe('Step 3: Cache Clearing', () => {
    test('Clear cache', async ({ page, admin }) => {
        await flushCache({ page, admin });
        const response = await page.goto('/');
        await validateHeader(response, 'status', 'miss');
    });

    test('Page is cached', async ({ page }) => {
        const response = await page.goto('/');
        await validateHeader(response, 'status', 'hit');
    });

    test('Clear cache again', async ({ page, admin }) => {
        await flushCache({ page, admin });
        const response = await page.goto('/');
        await validateHeader(response, 'status', 'miss');
    });
});