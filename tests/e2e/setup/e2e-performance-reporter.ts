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

process.env.WP_ARTIFACTS_PATH ??= join( process.cwd(), 'artifacts' );

class PerformanceReporter implements Reporter {
    private shard?: FullConfig[ 'shard' ];

    allResults: Record<
        string,
        {
            title: string;
            results: Record<string, number[]>[];
        }
    > = {};

    /**
     * Formats performance value based on the key.
     *
     * @param value
     * @param key
     */
    formatValue(value, key) {
        if (key === 'CLS') {
            return value.toFixed(2);
        }

        if (key === 'wpDbQueries') {
            return value.toFixed(0);
        }

        if (key === 'wpMemoryUsage') {
            return `${(value / Math.pow(10, 6)).toFixed(2)} MB`;
        }

        return `${value.toFixed(2)} ms`;
    }

    onBegin(config: FullConfig) {
        if (config.shard) {
            this.shard = config.shard;
        }
    }

    /**
     * Called after a test has been finished in the worker process.
     *
     * Used to add test results to the final summary of all tests.
     *
     * @param test
     * @param result
     */
    onTestEnd(test: TestCase, result: TestResult) {
        const performanceResults = result.attachments.find(
            (attachment) => attachment.name === 'results'
        );

        if (performanceResults?.body) {
            this.allResults[test.location.file] ??= {
                // 0 = empty, 1 = browser, 2 = file name, 3 = test suite name.
                title: test.titlePath()[3],
                results: [],
            };
            this.allResults[test.location.file].results.push(
                JSON.parse(performanceResults.body.toString('utf-8'))
            );
        }
    }

    /**
     * Called after all tests have been run, or testing has been interrupted.
     *
     * Provides a quick summary and writes all raw numbers to a file
     * for further processing, for example to compare with a previous run.
     *
     * @param result
     */
    onEnd(result: FullResult) {
        const summary = [];

        if (Object.keys(this.allResults).length > 0) {
            if (this.shard) {
                console.log(
                    `\nPerformance Test Results ${this.shard.current}/${this.shard.total}`
                );
            } else {
                console.log(`\nPerformance Test Results`);
            }
            console.log(`Status: ${result.status}`);
        }

        for (const [file, {title, results}] of Object.entries(this.allResults)) {
            // console.log(`\n${title}\n`);
            const aggregatedResults = results.reduce((acc, pluginResults) => {
                for (const [plugin, metrics] of Object.entries(pluginResults)) {
                    acc[plugin] ??= {};
                    for (const [metric, values] of Object.entries(metrics)) {
                        acc[plugin][metric] ??= [];
                        acc[plugin][metric].push(...values);
                    }
                }
                return acc;
            }, {} as Record<string, Record<string, number[]>>);

            // Create an array to pass to console.table()
            const tableData = Object.keys(aggregatedResults).map(plugin => {
                const row = { Plugin: plugin };
                for (const [metric, values] of Object.entries(aggregatedResults[plugin])) {
                    row[metric] = this.formatValue(median(values), metric);
                }
                return row;
            });

            // Display the table using console.table
            console.table(tableData);

            summary.push({
                file,
                title,
                results: aggregatedResults,
            });
        }

        if (!this.shard) {
            const artifactsPath = process.env.WP_ARTIFACTS_PATH as string;

            // Ensure the directory exists
            if (!existsSync(artifactsPath)) {
                mkdirSync(artifactsPath, { recursive: true });
            }

            const summaryFilePath = join(artifactsPath, 'performance-results.json');

            try {
                writeFileSync(summaryFilePath, JSON.stringify(summary, null, 2));
                console.log(`Results written to ${summaryFilePath}`);
            } catch (error) {
                console.error(`Error writing results: ${error.message}`);
            }
        }
    }
}

export default PerformanceReporter;