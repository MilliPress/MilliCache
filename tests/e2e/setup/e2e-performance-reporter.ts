import { join } from 'node:path';
import { existsSync, mkdirSync, writeFileSync } from 'node:fs';
import type {
    FullConfig,
    FullResult,
    Reporter,
    TestCase,
    TestResult,
} from '@playwright/test/reporter';
import { median } from '../utils/tools';

// Set the default WP artifacts path if not provided in the environment
const WP_ARTIFACTS_PATH = process.env.WP_ARTIFACTS_PATH ?? join(process.cwd(), 'artifacts');

class PerformanceReporter implements Reporter {
    private shard?: FullConfig['shard'];
    private allResults: Record<string, { title: string; results: Record<string, number[]>[] }> = {};

    /**
     * Formats performance values based on the metric key.
     *
     * @param value - The performance value.
     * @param key - The metric name.
     * @returns A formatted string representing the metric value.
     */
    private formatValue(value: number, key: string): string {
        switch (key) {
            case 'CLS':
                return value.toFixed(2);
            case 'wpDbQueries':
                return value.toFixed(0);
            case 'wpMemoryUsage':
                return `${(value / 1e6).toFixed(2)} MB`; // Convert to MB
            default:
                return `${value.toFixed(2)} ms`;
        }
    }

    /**
     * This method is called when the test run begins.
     * It sets up configuration-related data, such as the shard information.
     *
     * @param config - The full configuration of the test run.
     */
    onBegin(config: FullConfig): void {
        this.shard = config.shard;
    }

    /**
     * This method is called at the end of each test case.
     * It captures and stores performance results in the allResults object.
     *
     * @param test - The current test case.
     * @param result - The result of the test case.
     */
    onTestEnd(test: TestCase, result: TestResult): void {
        const performanceResults = result.attachments.find(
            (attachment) => attachment.name === 'results'
        );

        if (performanceResults?.body) {
            const file = test.location.file;
            const title = test.titlePath()[3];

            this.allResults[file] ??= { title, results: [] };
            this.allResults[file].results.push(JSON.parse(performanceResults.body.toString('utf-8')));
        }
    }

    /**
     * This method is called at the end of all tests.
     * It aggregates results and writes them to a JSON file for further analysis.
     *
     * @param result - The final result of the test run.
     */
    onEnd(result: FullResult): void {
        if (!Object.keys(this.allResults).length) return;

        // Display shard info if available
        if (this.shard) {
            console.log(`\nPerformance Test Results ${this.shard.current}/${this.shard.total}`);
        } else {
            console.log('\nPerformance Test Results');
        }
        console.log(`Status: ${result.status}`);

        const summary: Array<{ file: string; title: string; results: Record<string, number[]> }> = [];

        for (const [file, { title, results }] of Object.entries(this.allResults)) {
            const aggregatedResults = this.aggregateResults(results);

            // Create data for console.table
            // @ts-ignore
            const tableData = this.formatTableData(aggregatedResults);
            console.table(tableData);

            // @ts-ignore
            summary.push({ file, title, results: aggregatedResults });
        }

        this.writeResultsToFile(summary);
    }

    /**
     * Aggregates the results by plugin and metric.
     *
     * @param results - The raw test results.
     * @returns Aggregated results.
     */
    private aggregateResults(results: Record<string, number[]>[]): Record<string, Record<string, number[]>> {
        return results.reduce((acc, pluginResults) => {
            for (const [plugin, metrics] of Object.entries(pluginResults)) {
                acc[plugin] ??= {};

                // Ensure metrics is either an array or an object
                for (const [metricKey, metricValues] of Object.entries(metrics)) {
                    acc[plugin][metricKey] ??= [];

                    if (Array.isArray(metricValues)) {
                        // Spread array of values
                        acc[plugin][metricKey].push(...metricValues);
                    } else if (typeof metricValues === 'number') {
                        // If the metric value is a single number, push it directly
                        acc[plugin][metricKey].push(metricValues);
                    }
                }
            }
            return acc;
        }, {} as Record<string, Record<string, number[]>>);
    }

    /**
     * Formats the results into a structure suitable for displaying using console.table.
     *
     * @param aggregatedResults - The aggregated results by plugin and metric.
     * @returns A list of rows formatted for console.table.
     */
    private formatTableData(aggregatedResults: Record<string, Record<string, number[]>>): Array<Record<string, string>> {
        return Object.keys(aggregatedResults).map((plugin) => {
            const row: Record<string, string> = { Plugin: plugin };
            for (const [metric, values] of Object.entries(aggregatedResults[plugin])) {
                row[metric] = this.formatValue(median(values), metric);
            }
            return row;
        });
    }

    /**
     * Writes the summary results to a JSON file in the WP artifacts path.
     *
     * @param summary - The aggregated summary data to be written to the file.
     */
    private writeResultsToFile(summary: Array<{ file: string; title: string; results: Record<string, number[]> }>): void {
        const artifactsPath = WP_ARTIFACTS_PATH;

        // Ensure the directory exists
        if (!existsSync(artifactsPath)) {
            mkdirSync(artifactsPath, { recursive: true });
        }

        const summaryFilePath = join(artifactsPath, 'performance-results.json');
        try {
            writeFileSync(summaryFilePath, JSON.stringify(summary, null, 2));
            console.log(`Results written to ${summaryFilePath}`);
        } catch (error) {
            console.error(`Error writing results: ${(error as Error).message}`);
        }
    }
}

export default PerformanceReporter;
