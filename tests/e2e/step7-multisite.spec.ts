import { test } from './setup/e2e-wp-test';
import { flushCache, validateHeader, getRandomAnchor } from './utils/tools';

test.describe('Step 7: Network Caching & Flushing', () => {
    const sites = 5;
    let matrix = [];

    test ('Check network for active plugin', async ({ page }) => {
        for (let i = 1; i <= sites; i++) {
            const response = await page.goto(`/site${i}/`);
            await validateHeader(response, 'status', ['miss', 'hit']);
        }
    });

    test('Network Caching & Flushing', async ({ page, admin }) => {
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
    });

    test('Flush network cache & validate sites', async ({ page, admin }) => {
        // Flush the network cache
        await flushCache({ page, admin, network: true });

        // Validate all sites of the matrix
        for (let i = 1; i <= sites; i++) {
            const response = await page.goto(`/site${i}/`);
            await validateHeader(response, 'status', 'miss');
        }
    });
});