#!/usr/bin/env node

const { existsSync, readFileSync, writeFileSync } = require('node:fs');
const { join, resolve } = require('node:path');

// Define a default path for WP artifacts
const WP_ARTIFACTS_PATH = process.env.WP_ARTIFACTS_PATH ?? join(process.cwd(), 'artifacts');

// Parse command-line arguments
const args = process.argv.slice(2);
const beforeFile = args[1];
const afterFile = args[0] || resolve('./artifacts/performance-results.json');

// Check for missing files
function checkFileExistence(file, description)
{
    if (!existsSync(file)) {
        console.error(`File not found: ${file} (${description})`);
        process.exit(1);
    }
}
checkFileExistence(afterFile, "after results");

if (beforeFile) {
    checkFileExistence(beforeFile, "before results");
}

/**
 * Format values based on the metric key.
 * @param {number} value - The metric value.
 * @param {string} key - The metric name.
 * @return {string} - The formatted value.
 */
function formatValue(value, key)
{
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

/**
 * Format a table value.
 * @param {any} v - Value to be formatted.
 * @return {string} - The formatted string for the table.
 */
function formatTableValue(v)
{
    if (v === true || v === 'true') {
        return 'Yes';
    }
    if (!v || v === 'false') {
        return '';
    }
    return v?.toString() || String(v);
}

/**
 * Create a Markdown table from the results.
 * @param {Array<Object>} rows - Table rows.
 * @param {Record<string, string>} fastestMap - Map of fastest values for highlighting.
 * @return {string} - Markdown table as a string.
 */
function formatAsMarkdownTable(rows, fastestMap)
{
    let result = '';
    const headers = Object.keys(rows[0]);

    // Add headers
    result += headers.map(header => `| ${header} `).join('') + '|\n';
    result += headers.map(header => `| ${'-'.repeat(header.length)} `).join('') + '|\n';

    // Add rows
    for (const row of rows) {
        for (const [key, value] of Object.entries(row)) {
            const formattedValue = formatTableValue(value);
            const finalValue = fastestMap[key] === formattedValue && key === 'timeToFirstByte'
                ? `**${formattedValue}**`
                : formattedValue;
            result += `| ${finalValue.padStart(key.length, ' ')} `;
        }
        result += '|\n';
    }
    return result;
}

/**
 * Calculate the median of an array.
 * @param {number[]} array - Array of numbers.
 * @return {number} - Median value.
 */
function median(array)
{
    const sorted = [...array].sort((a, b) => a - b);
    const mid = Math.floor(sorted.length / 2);
    return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2;
}

/**
 * Identify the fastest values for each metric.
 * @param {Array<Object>} results - Array of result objects.
 * @return {Record<string, string>} - Map of the fastest values.
 */
function identifyFastestValues(results)
{
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

// Load before and after stats
let beforeStats = [];
let afterStats;

if (beforeFile) {
    try {
        beforeStats = JSON.parse(readFileSync(beforeFile, 'utf-8'));
    } catch {
        console.error(`Could not read or parse file: ${beforeFile}`);
        process.exit(1);
    }
}

try {
    afterStats = JSON.parse(readFileSync(afterFile, 'utf-8'));
} catch {
    console.error(`Could not read or parse file: ${afterFile}`);
    process.exit(1);
}

// Create the Markdown summary
let summaryMarkdown = `**Performance Test Results**\n\n`;

if (process.env.GITHUB_SHA) {
    summaryMarkdown += `Performance test results for commit ${
        process.env.GITHUB_SHA} are available.\n\n`;
}

if (beforeFile) {
    summaryMarkdown += `The numbers in parentheses represent the differences from the previous baseline test.\n\n`;
}

const DELTA_VARIANCE = 500; // 500 ms variance
const PERCENTAGE_VARIANCE = 5; // 5% variance

for (const { file, title, results } of afterStats) {
    const prevStat = beforeStats.find((s) => s.file === file);
    const diffResults = [];

    for (const i in results) {
        const newResult = results[i];
        const diffResult = { Plugin: i };

        for (const [key, values] of Object.entries(newResult)) {
            const prevValues = prevStat?.results.length === results.length
            ? prevStat?.results[i]?.[key]
            : null;

            const value = median(values);
            const prevValue = prevValues ? median(prevValues) : 0;
            const delta = value - prevValue;
            const percentage = (delta / value) * 100;

            // Only show comparison for 'millicache'
            if (file.includes('millicache')) {
                if (Math.abs(percentage) <= PERCENTAGE_VARIANCE && Math.abs(delta) <= DELTA_VARIANCE) {
                    diffResult[key] = formatValue(value, key);
                } else {
                    const prefix = delta > 0 ? '+' : '';
                    diffResult[key] = `${formatValue(value, key)} (${prefix}${formatValue(delta, key)} / ${prefix}${percentage.toFixed(2)}%)`;
                }
            } else {
                diffResult[key] = formatValue(value, key);
            }
        }
        diffResults.push(diffResult);
    }

    console.log(`Results for ${title}`);
    console.table(diffResults);

    const fastestMap = identifyFastestValues(diffResults);
    summaryMarkdown += `**${title}**\n\n`;
    summaryMarkdown += `${formatAsMarkdownTable(diffResults, fastestMap)}\n`;
}

// Write the Markdown summary to a file
const summaryFilePath = join(WP_ARTIFACTS_PATH, '/performance-results.md');
writeFileSync(summaryFilePath, summaryMarkdown);
console.log(`Summary written to ${summaryFilePath}`);
