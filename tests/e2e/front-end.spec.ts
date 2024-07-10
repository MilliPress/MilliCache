import { test, expect } from './setup/e2e-wp-test';
import { validateHeader } from './utils/validateHeader';
import { flushCache } from './utils/flushCache';

/**
 * Set the test mode to serial.
 */
test.describe.configure({ mode: 'serial' });

/**
 * Activate the plugin before running the tests.
 */
test.beforeAll(async ({ requestUtils }) => {
    // Wait some seconds for the backend test to test the plugin activation
    await new Promise((resolve) => setTimeout(resolve, 5000));

    // Activate the plugin
    await requestUtils.activatePlugin('millicache');
});

/**
 * Test the front-end of the plugin.
 */
test.describe('Visitor', () => {

    /**
     * Smoke Test the MilliCache Status Header.
     * This is a test for the front-end of the plugin and that the Cache is working.
     */
    test('MilliCache Status Header is set to miss|hit', async ({ page }) => {
        // Go to the home page
        const response = await page.goto('/');

        // Check if the status is set to miss or hit
        await validateHeader(response, 'status', ['miss', 'hit']);

        // Reload the same page to check if the status is hit
        const response2 = await page.reload();

        // Check if the status is set to hit
        await validateHeader(response2, 'status', 'hit');
    });

    /**
     * We flush the cache via WP-CLI and check the status again.
     */
    test('Flush Cache', async ({ page, admin, requestUtils }) => {
        // Flush the cache
        await flushCache({ page, admin });

        // Go to the home page
        const response = await page.goto('/');

        // Check if the status is set to miss or hit
        await validateHeader(response, 'status', 'miss');
    });

    /**
     * Test Background Cache Regeneration.
     */
    test('Cache Expiring', async ({ page, requestUtils }) => {
        // Go to the home page
        await page.goto('/');

        // Wait 2 seconds to expire the cache
        await page.waitForTimeout(2000);

        // Reload the same page to check if the status is hit
        const response = await page.reload();

        // Check if the status is set to miss
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

    /**
     * Test navigation through the website.
     */
    test('Cache flagging', async ({ page }) => {
        // Define the targets and their expected cache flags
       const targets = {
            'Hello World!': ['post:1:1', 'site:1:1'],
            'Sample Page': ['post:1:2', 'site:1:1'],
            'admin': ['author:1:1', 'site:1:1'],
        };

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