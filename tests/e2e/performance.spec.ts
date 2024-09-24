import { test } from '@wordpress/e2e-test-utils-playwright';
import { camelCaseDashes, networkActivatePlugin, networkDeactivatePlugin } from './utils/tools';

const results: Record<string, Record<string, number[]>> = {};
const iterations = Number(process.env.TEST_ITERATIONS);
const plugins = [
    'nocache',
    'millicache',
    'breeze',
    'cachify',
    'cache-enabler',
    'wp-fastest-cache',
    'wp-super-cache',
];

test.describe('Various Cache Plugins Performance Tests & Validate MilliCache Results', () => {
    test.use({
        // @ts-ignore
        storageState: {}, // User will be logged out.
    });

    // Run *once* before *all* iterations.
    // Ideal for setting up the site for this particular test.
    test.beforeAll(async ({ requestUtils }) => {
        // Deactivate all plugins.
        for (const plugin of plugins) {
            await networkDeactivatePlugin(plugin);
        }

        // Reset the caches.
        await requestUtils.request.get(
            `${requestUtils.baseURL}/?reset_helper`
        );
    });

    // After all results are processed, attach results for further processing.
    // For easier handling, only one attachment per file.
    test.afterAll(async ({}, testInfo) => {
        const combinedResults: Record<string, Record<string, number[]>> = {};

        for (const plugin of plugins) {
            if (results[plugin]) {
                combinedResults[plugin] = results[plugin];
            }
        }

        await testInfo.attach('results', {
            body: JSON.stringify(combinedResults, null, 2),
            contentType: 'application/json',
        });
    });

    // Run the test for each plugin.
    for (const plugin of plugins) {
        test(`Activate ${plugin}`, async () => {
            try {
                await networkActivatePlugin(plugin);
            } catch (error) {
                test.skip();  // Skip further tests for this plugin
            }
        });

        /**
         * Run the test multiple times to get an average.
         */
        for (let i = 1; i <= iterations; i++) {
            test(`Measure load time metrics of ${plugin} (${i} of ${iterations})`, async ({
                  page,
                  metrics,
              }) => {
                await page.goto(`/sample-page/`);

                const serverTiming = await metrics.getServerTiming();

                // Ensure the result object is properly initialized
                results[plugin] ??= {};

                const ttfb = await metrics.getTimeToFirstByte();
                const lcp = await metrics.getLargestContentfulPaint();

                // console.log(`Iteration ${i}: TTFB = ${ttfb.toFixed(2)}, LCP = ${lcp.toFixed(2)}`);

                results[plugin].timeToFirstByte ??= [];
                results[plugin].timeToFirstByte.push(ttfb);
                results[plugin].largestContentfulPaint ??= [];
                results[plugin].largestContentfulPaint.push(lcp);
                results[plugin].lcpMinusTtfb ??= [];
                results[plugin].lcpMinusTtfb.push(lcp - ttfb);

                if (i === 1) {
                    // Get first iteration server timing data, as cache is generated here.
                    for (const [key, value] of Object.entries(serverTiming)) {
                        results[plugin][camelCaseDashes(key)] ??= [];
                        results[plugin][camelCaseDashes(key)].push(value);
                    }
                }
            });
        }

        test(`Deactivate ${plugin}`, async () => {
            await networkDeactivatePlugin(plugin);
        });
    }
});