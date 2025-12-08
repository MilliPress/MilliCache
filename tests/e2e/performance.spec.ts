import { test } from '@wordpress/e2e-test-utils-playwright';
import { networkActivatePlugin, networkDeactivatePlugin, removeAdvancedCacheDropIn } from './utils/tools';

/**
 * MilliCache Performance Test
 *
 * Compares WordPress performance with and without MilliCache to measure
 * the caching effectiveness. Uses warm-up runs and multiple iterations
 * for statistical reliability.
 */

interface TestScenario {
    name: string;
    plugin: string | null;
}

interface MetricResults {
    timeToFirstByte: number[];
    largestContentfulPaint: number[];
    lcpMinusTtfb: number[];
    [key: string]: number[];
}

interface Results {
    [scenario: string]: MetricResults;
}

const results: Results = {};

// Configuration - more iterations since we only test 2 scenarios now
const warmUpRuns = Number(process.env.WARMUP_RUNS) || 3;
const iterations = Number(process.env.TEST_ITERATIONS) || 15;

// Test scenarios: baseline (no cache) vs MilliCache
const scenarios: TestScenario[] = [
    { name: 'nocache', plugin: null },
    { name: 'millicache', plugin: 'millicache' },
];

test.describe('MilliCache Performance Tests', () => {
    test.use({
        // @ts-ignore
        storageState: {}, // User will be logged out.
    });

    // Run once before all tests to set up the environment.
    test.beforeAll(async ({ requestUtils }) => {
        // Deactivate MilliCache first.
        await networkDeactivatePlugin('millicache');

        // Remove the advanced-cache.php drop-in to ensure clean nocache baseline.
        await removeAdvancedCacheDropIn();

        // Enable performance mode (longer TTL) and reset all caches.
        await requestUtils.request.get(
            `${requestUtils.baseURL}/?perf_mode=1&reset_helper`
        );
    });

    // After all tests, disable perf mode and attach results.
    test.afterAll(async ({ requestUtils }, testInfo) => {
        // Disable performance mode.
        await requestUtils.request.get(
            `${requestUtils.baseURL}/?perf_mode=0`
        );

        // Attach results in the format the reporter expects.
        // The reporter will aggregate these and add metadata.
        await testInfo.attach('results', {
            body: JSON.stringify(results, null, 2),
            contentType: 'application/json',
        });
    });

    // Run tests for each scenario.
    for (const scenario of scenarios) {
        // Setup for this scenario: activate/deactivate plugin and reset caches.
        test(`Setup ${scenario.name}`, async ({ requestUtils }) => {
            if (scenario.plugin) {
                // Activate the caching plugin.
                await networkActivatePlugin(scenario.plugin);
            } else {
                // Ensure no caching: deactivate millicache and remove drop-in.
                await networkDeactivatePlugin('millicache');
                await removeAdvancedCacheDropIn();
            }

            // Reset all caches before this scenario's measurements.
            await requestUtils.request.get(
                `${requestUtils.baseURL}/?reset_helper`
            );
        });

        // Warm-up runs (discarded, not recorded).
        for (let i = 1; i <= warmUpRuns; i++) {
            test(`Warm-up ${scenario.name} (${i} of ${warmUpRuns})`, async ({ page }) => {
                await page.goto('/sample-page/');
                // Just load the page, don't record metrics.
            });
        }

        // Actual measurement runs.
        for (let i = 1; i <= iterations; i++) {
            test(`Measure ${scenario.name} (${i} of ${iterations})`, async ({
                page,
                metrics,
            }) => {
                await page.goto('/sample-page/');

                const serverTiming = await metrics.getServerTiming();

                // Initialize result object for this scenario.
                results[scenario.name] ??= {
                    timeToFirstByte: [],
                    largestContentfulPaint: [],
                    lcpMinusTtfb: [],
                };

                const ttfb = await metrics.getTimeToFirstByte();
                const lcp = await metrics.getLargestContentfulPaint();

                results[scenario.name].timeToFirstByte.push(ttfb);
                results[scenario.name].largestContentfulPaint.push(lcp);
                results[scenario.name].lcpMinusTtfb.push(lcp - ttfb);

                // Record server timing data on first iteration only.
                if (i === 1) {
                    for (const [key, value] of Object.entries(serverTiming)) {
                        const camelKey = key.replace(/-([a-z])/g, (_, c) => c.toUpperCase());
                        results[scenario.name][camelKey] ??= [];
                        results[scenario.name][camelKey].push(value);
                    }
                }
            });
        }
    }
});
