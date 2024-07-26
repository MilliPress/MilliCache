import { test } from './setup/e2e-wp-test';
import { validateHeader, getRandomAnchor, networkActivatePlugin } from './utils/tools';
import { flushCache } from './utils/flushCache';

/**
 * Set the test mode to serial.
 */
test.describe.configure({ mode: 'serial' });

/**
 * Activate the plugin before running the tests.
 */
test.beforeAll(async ({ requestUtils }) => {
    // Wait some seconds for the backend test to first run the plugin activation tests
    if (process.env.RUN_ALL_TESTS) {
        await new Promise((resolve) => setTimeout(resolve, 5000));
    }

    // Go sure that the plugin is activated
    await networkActivatePlugin();
});

/**
 * Test Caching of the plugin.
 */
test.describe('Visitor @ WordPress', () => {

    /**
     * We flush the cache via WP-CLI and check the status again.
     */
    test('Flush Cache & Cache Page', async ({ page, admin, requestUtils }) => {
        // Flush the cache
        await flushCache({ page, admin });

        // Go to the home page
        const response = await page.goto('/');

        // Check if the status is set to miss or hit
        await validateHeader(response, 'status', 'miss');

        // Reload the same page to check if the status is hit
        const response2 = await page.reload();

        // Check if the status is set to hit
        await validateHeader(response2, 'status', 'hit');
    });

    /**
     * Test Background Cache Regeneration.
     */
    test('Cache Expiring', async ({ page, admin }) => {
        // Flush the cache
        await flushCache({ page, admin });

        // Go to the home page
        await page.goto('/');

        // Wait a second, so the caching is done
        await page.waitForTimeout(1000);

        // Reload the same page to check if the status is hit
        const response = await page.reload();

        // Check if the status is set to hit
        await validateHeader(response, 'status', 'hit');

        // Wait 6 seconds to expire the cache
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

/**
 * Test Multisite Caching of the plugin.
 */
test.describe('Visitor @ WordPress Multisite', () => {
    const sites = 5;

    test ('Check if the plugin is active', async ({ page }) => {
        for (let i = 1; i <= sites; i++) {
            const response = await page.goto(`/site${i}/`);
            await validateHeader(response, 'status', ['miss', 'hit']);
        }
    });

    test('Network Caching & Flushing', async ({ page, admin }) => {
        let matrix = [];

        // For each Multisite
        for (let i = 1; i <= sites; i++) {
            // Go to the home page
            await page.goto(`/site${i}/`);

            // Get random link href
            const anchor = await getRandomAnchor({page});

            // Get href attribute
            const href = anchor ? await anchor.getAttribute('href') : null;

            if (anchor) {
                // Open the link
                const response = await page.goto(href);

                // Wait for the page to load
                await page.waitForLoadState();

                // Check if the status is set to miss or hit
                await validateHeader(response, 'status', ['miss', 'hit']);

                // Reload the same page to check if the status is hit
                const response2 = await page.reload();

                // Check if the status is set to hit
                await validateHeader(response2, 'status', 'hit');

                // Get the response header "x-millicache-flags"
                const flags = await validateHeader(response2, 'flags', null, false);

                // Add tested site to matrix with tested links
                matrix.push({link: href, flags: flags});
            }
        }

        // Flush the network cache
        await flushCache({ page, admin, network: true });

        // Check pages of the matrix
        for (let i = 0; i < matrix.length; i++) {
            const {link} = matrix[i];
            const response = await page.goto(link);

            // Check if the status is set to miss
            await validateHeader(response, 'status', 'miss');
        }
    });

});