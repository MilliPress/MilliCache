#!/usr/bin/env node

/**
 * External dependencies
 */
const { existsSync, readFileSync, writeFileSync } = require('node:fs');
const { join, resolve } = require('node:path');

process.env.WP_ARTIFACTS_PATH ??= join(process.cwd(), 'artifacts');

const args = process.argv.slice(2);

const beforeFile = args[1];
const afterFile = args[0] || resolve('./artifacts/performance-results.json');

if (!existsSync(afterFile)) {
    console.error(`File not found: ${afterFile}`);
    process.exit(1);
}

if (beforeFile && !existsSync(beforeFile)) {
    console.error(`File not found: ${beforeFile}`);
    process.exit(1);
}

/**
 * Formats performance value based on the key.
 *
 * @param value
 * @param key
 */
function formatValue(value, key) {
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

/**
 * @param {unknown} v
 * @return {string} Formatted value.
 */
function formatTableValue(v) {
    if (v === true || v === 'true') {
        return '✅';
    }
    if (!v || v === 'false') {
        return '';
    }
    return v?.toString() || String(v);
}

/**
 * Simple way to format an array of objects as a Markdown table.
 *
 * @param {Array<Object>} rows Table rows.
 * @param {Record<string, string>} fastestMap A map of the fastest values to bold them.
 * @return {string} Markdown table content.
 */
function formatAsMarkdownTable(rows, fastestMap) {
    let result = '';
    const headers = Object.keys(rows[0]);
    for (const header of headers) {
        result += `| ${header} `;
    }
    result += '|\n';
    for (const header of headers) {
        const dashes = '-'.repeat(header.length);
        result += `| ${dashes} `;
    }
    result += '|\n';

    for (const row of rows) {
        for (const [key, value] of Object.entries(row)) {
            const formattedValue = formatTableValue(value);
            // Check if the value should be highlighted (bold in Markdown)
            const finalValue = fastestMap[key] === formattedValue && key === 'timeToFirstByte' ? `**${formattedValue}**` : formattedValue;
            result += `| ${finalValue.padStart(key.length, ' ')} `;
        }
        result += '|\n';
    }

    return result;
}

/**
 * Computes the median number from an array numbers.
 *
 * @param {number[]} array List of numbers.
 * @return {number} Median.
 */
function median(array) {
    const mid = Math.floor(array.length / 2);
    const numbers = [...array].sort((a, b) => a - b);
    const result =
        array.length % 2 !== 0 ? numbers[mid] : (numbers[mid - 1] + numbers[mid]) / 2;

    return Number(result.toFixed(2));
}

/**
 * Identify the fastest values for each metric.
 *
 * @param {Array<Object>} results Results of plugins.
 * @return {Record<string, string>} Map of fastest values.
 */
function identifyFastestValues(results) {
    const fastestMap = {};
    const metrics = Object.keys(results[0]).filter((key) => key !== 'Plugin');

    metrics.forEach((metric) => {
        let fastestValue = Infinity;
        results.forEach((result) => {
            const value = parseFloat(result[metric]);
            if (!isNaN(value) && value < fastestValue) {
                fastestValue = value;
            }
        });

        results.forEach((result) => {
            if (parseFloat(result[metric]) === fastestValue) {
                fastestMap[metric] = result[metric];
            }
        });
    });

    return fastestMap;
}

/**
 * @type {Array<{file: string, title: string, results: Record<string, number[]>[]}>}
 */
let beforeStats = [];

/**
 * @type {Array<{file: string, title: string, results: Record<string, number[]>[]}>}
 */
let afterStats;

if (beforeFile) {
    try {
        beforeStats = JSON.parse(readFileSync(beforeFile, { encoding: 'utf-8' }));
    } catch {}
}

try {
    afterStats = JSON.parse(readFileSync(afterFile, { encoding: 'utf-8' }));
} catch {
    console.error(`Could not read file: ${afterFile}`);
    process.exit(1);
}

let summaryMarkdown = `**Performance Test Results**\n\n`;

if (process.env.GITHUB_SHA) {
    summaryMarkdown += `Performance test results for ${process.env.GITHUB_SHA} are in 🛎️!\n\n`;
} else {
    summaryMarkdown += `Performance test results are in 🛎️!\n\n`;
}

if (beforeFile) {
    summaryMarkdown += `Note: the numbers in parentheses show the difference to the previous (baseline) test run.\n\n`;
}

console.log('Performance Test Results\n');

if (beforeFile) {
    console.log(
        'Note: the numbers in parentheses show the difference to the previous (baseline) test run.\n'
    );
}

const DELTA_VARIANCE = 500;  // 500 ms variance
const PERCENTAGE_VARIANCE = 5;  // 5% variance

for (const { file, title, results } of afterStats) {
    const prevStat = beforeStats.find((s) => s.file === file);

    const diffResults = [];

    for (const i in results) {
        const newResult = results[i];

        const diffResult = {
            Plugin: i,
        };

        for (const [key, values] of Object.entries(newResult)) {
            const prevValues =
                prevStat?.results.length === results.length
                    ? prevStat?.results[i].key
                    : null;

            const value = median(values);
            const prevValue = prevValues ? median(prevValues) : 0;
            const delta = value - prevValue;
            const percentage = (delta / value) * 100;

            // Apply comparison logic ONLY for 'millicache'
            if (file.includes('millicache')) {
                if (
                    !prevValues ||
                    !percentage ||
                    Math.abs(percentage) <= PERCENTAGE_VARIANCE ||
                    !delta ||
                    Math.abs(delta) <= DELTA_VARIANCE
                ) {
                    diffResult[key] = formatValue(value, key);
                    continue;
                }

                const prefix = delta > 0 ? '+' : '';

                diffResult[key] = `${formatValue(value, key)} (${prefix}${formatValue(
                    delta,
                    key
                )} / ${prefix}${percentage}%)`;
            } else {
                // For other plugins, just show the current value
                diffResult[key] = formatValue(value, key);
            }
        }

        diffResults.push(diffResult);
    }

    console.log(title);
    console.table(diffResults);

    // Identify the fastest values for each metric
    const fastestMap = identifyFastestValues(diffResults);

    summaryMarkdown += `**${title}**\n\n`;
    summaryMarkdown += `${formatAsMarkdownTable(diffResults, fastestMap)}\n`;
}

writeFileSync(join(process.env.WP_ARTIFACTS_PATH, '/performance-results.md'), summaryMarkdown);
