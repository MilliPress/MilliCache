import { test, expect } from './setup/e2e-wp-test';
import { runWpCliCommand, validateHeader, flushCache } from './utils/tools';

test.describe('Step 8: WP-CLI Commands', () => {
    test('WP-CLI: Check MilliCache is active', async () => {
        await runWpCliCommand('plugin is-active millicache -- --network');
    });

    test('WP-CLI: Clear MilliCache', async ({ page }) => {
        // Go to the home page
        await page.goto('/');

        // Clear the cache
        await flushCache();

        // Go to the home page
        const response = await page.goto('/');

        // Validate the cache is empty
        await validateHeader(response, 'status', 'miss');

        // Reload the page
        const response2 = await page.reload();

        // Validate the cache is set to hit
        await validateHeader(response2, 'status', 'hit');

        // Clear cache by flag, this tests other commands as well as they all use the same final function
        await flushCache('1:home');

        // Go to the home page
        const response3 = await page.goto('/');

        // Validate the cache is empty
        await validateHeader(response3, 'status', 'miss');
    });

    test('WP-CLI: MilliCache Stats', async () => {
        // General cache stats
        const stdout = await runWpCliCommand('millicache stats');

        // If the output contains "Empty", the cache is empty. Otherwise, it is not empty.
        expect(stdout).not.toContain('Empty');

        // Clear cache of another site
        await flushCache('2:*');

        // Get stats by flag of site 1
        const stdout3 = await runWpCliCommand('millicache stats -- --flag=1:home');

        // Validate network 1 cache is still available
        expect(stdout3).not.toContain('Empty');

        // Clear cache of network 1
        await flushCache('1:*');

        // Stats by flag of network 1
        const stdout5 = await runWpCliCommand('millicache stats -- --flag=1:*');

        // Validate network 1 cache is empty
        expect(stdout5).toContain('Empty');
    });
});