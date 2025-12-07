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
    private allResults: Record<string, { title: string; results: Record<string, Record<string, number[]>>[] }> = {};

    /**
     * Formats performance values based on the metric key.
     */
    private formatValue(value: number, key: string): string {
        switch (key) {
            case 'CLS':
                return value.toFixed(2);
            case 'wpDbQueries':
                return value.toFixed(0);
            case 'wpMemoryUsage':
                return `${(value / 1e6).toFixed(2)} MB`;
            default:
                return `${value.toFixed(2)} ms`;
        }
    }

    onBegin(config: FullConfig): void {
        this.shard = config.shard;
    }

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

    onEnd(result: FullResult): void {
        if (!Object.keys(this.allResults).length) return;

        // Display header
        if (this.shard) {
            console.log(`\nMilliCache Performance Results ${this.shard.current}/${this.shard.total}`);
        } else {
            console.log('\nMilliCache Performance Results');
        }
        console.log(`Status: ${result.status}`);

        const summary: Array<{ file: string; title: string; results: Record<string, Record<string, number[]>> }> = [];

        for (const [file, { title, results }] of Object.entries(this.allResults)) {
            const aggregatedResults = this.aggregateResults(results);

            // Display results
            const tableData = this.formatTableData(aggregatedResults);
            if (tableData.length > 0) {
                console.table(tableData);

                // Show speedup summary
                this.showSpeedupSummary(aggregatedResults);
            }

            summary.push({ file, title, results: aggregatedResults });
        }

        this.writeResultsToFile(summary);
    }

    /**
     * Show a quick speedup summary in the console.
     */
    private showSpeedupSummary(results: Record<string, Record<string, number[]>>): void {
        const nocache = results['nocache'];
        const millicache = results['millicache'];

        if (nocache?.timeToFirstByte && millicache?.timeToFirstByte) {
            const nocacheTtfb = median(nocache.timeToFirstByte);
            const millicacheTtfb = median(millicache.timeToFirstByte);

            if (millicacheTtfb > 0) {
                const speedup = nocacheTtfb / millicacheTtfb;
                console.log(`\n  TTFB Speedup: ${speedup.toFixed(2)}x faster with MilliCache`);
            }
        }
    }

    /**
     * Aggregates the results by scenario and metric.
     */
    private aggregateResults(results: Record<string, Record<string, number[]>>[]): Record<string, Record<string, number[]>> {
        return results.reduce((acc, scenarioResults) => {
            for (const [scenario, metrics] of Object.entries(scenarioResults)) {
                acc[scenario] ??= {};

                for (const [metricKey, metricValues] of Object.entries(metrics)) {
                    acc[scenario][metricKey] ??= [];

                    if (Array.isArray(metricValues)) {
                        acc[scenario][metricKey].push(...metricValues);
                    } else if (typeof metricValues === 'number') {
                        acc[scenario][metricKey].push(metricValues);
                    }
                }
            }
            return acc;
        }, {} as Record<string, Record<string, number[]>>);
    }

    /**
     * Formats the results for console.table display.
     */
    private formatTableData(aggregatedResults: Record<string, Record<string, number[]>>): Array<Record<string, string>> {
        return Object.keys(aggregatedResults).map((scenario) => {
            const row: Record<string, string> = { Scenario: scenario };
            for (const [metric, values] of Object.entries(aggregatedResults[scenario])) {
                row[metric] = this.formatValue(median(values), metric);
            }
            return row;
        });
    }

    /**
     * Writes the results to a JSON file.
     */
    private writeResultsToFile(summary: Array<{ file: string; title: string; results: Record<string, Record<string, number[]>> }>): void {
        const artifactsPath = WP_ARTIFACTS_PATH;

        if (!existsSync(artifactsPath)) {
            mkdirSync(artifactsPath, { recursive: true });
        }

        // Output format compatible with results.js
        const output = {
            metadata: {
                warmUpRuns: Number(process.env.WARMUP_RUNS) || 3,
                iterations: Number(process.env.TEST_ITERATIONS) || 15,
                timestamp: new Date().toISOString(),
                commitSha: process.env.GITHUB_SHA,
            },
            scenarios: summary.length > 0 ? summary[0].results : {},
        };

        const summaryFilePath = join(artifactsPath, 'performance-raw.json');
        try {
            writeFileSync(summaryFilePath, JSON.stringify(output, null, 2));
            console.log(`\nResults written to ${summaryFilePath}`);
        } catch (error) {
            console.error(`Error writing results: ${(error as Error).message}`);
        }
    }
}

export default PerformanceReporter;
