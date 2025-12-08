#!/usr/bin/env node

const { existsSync, readFileSync, writeFileSync } = require('node:fs');
const { join, resolve } = require('node:path');

// Define paths
const WP_ARTIFACTS_PATH = process.env.WP_ARTIFACTS_PATH ?? join(process.cwd(), 'artifacts');

// Parse command-line arguments
const args = process.argv.slice(2);
const afterFile = args[0] || resolve('./artifacts/performance-raw.json');
const beforeFile = args[1];

// Regression threshold (10%)
const REGRESSION_THRESHOLD = 0.10;

/**
 * Check file existence and exit if not found.
 */
function checkFileExistence(file, description) {
    if (!existsSync(file)) {
        console.error(`File not found: ${file} (${description})`);
        process.exit(1);
    }
}

/**
 * Calculate median of an array.
 */
function median(array) {
    if (!array || array.length === 0) return 0;
    const sorted = [...array].sort((a, b) => a - b);
    const mid = Math.floor(sorted.length / 2);
    return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2;
}

/**
 * Calculate standard deviation.
 */
function standardDeviation(array) {
    if (!array || array.length < 2) return 0;
    const avg = array.reduce((a, b) => a + b, 0) / array.length;
    const squareDiffs = array.map(value => Math.pow(value - avg, 2));
    return Math.sqrt(squareDiffs.reduce((a, b) => a + b, 0) / array.length);
}

/**
 * Remove outliers using IQR method.
 */
function removeOutliers(array) {
    if (!array || array.length < 4) return { cleaned: array, removed: 0 };

    const sorted = [...array].sort((a, b) => a - b);
    const q1 = sorted[Math.floor(sorted.length * 0.25)];
    const q3 = sorted[Math.floor(sorted.length * 0.75)];
    const iqr = q3 - q1;
    const lowerBound = q1 - 1.5 * iqr;
    const upperBound = q3 + 1.5 * iqr;

    const cleaned = array.filter(v => v >= lowerBound && v <= upperBound);
    return { cleaned, removed: array.length - cleaned.length };
}

/**
 * Format milliseconds nicely.
 */
function formatMs(value) {
    if (value >= 1000) {
        return `${(value / 1000).toFixed(2)}s`;
    }
    return `${value.toFixed(0)}ms`;
}

/**
 * Calculate speedup ratio.
 */
function calculateSpeedup(baseline, cached) {
    if (cached === 0) return 0;
    return baseline / cached;
}

/**
 * Calculate percentage improvement.
 */
function calculateImprovement(baseline, cached) {
    if (baseline === 0) return 0;
    return ((baseline - cached) / baseline) * 100;
}

// Check required files
checkFileExistence(afterFile, 'current results');

// Load results
let afterStats;
try {
    afterStats = JSON.parse(readFileSync(afterFile, 'utf-8'));
} catch (e) {
    console.error(`Could not parse: ${afterFile}`);
    process.exit(1);
}

let beforeStats = null;
if (beforeFile) {
    checkFileExistence(beforeFile, 'baseline results');
    try {
        beforeStats = JSON.parse(readFileSync(beforeFile, 'utf-8'));
    } catch (e) {
        console.error(`Could not parse: ${beforeFile}`);
        process.exit(1);
    }
}

// Process results - handle both old and new format
let currentData;
if (Array.isArray(afterStats)) {
    // Old format: array of test results
    const perfResult = afterStats.find(r => r.file && r.file.includes('performance'));
    currentData = perfResult ? { scenarios: perfResult.results, metadata: {} } : { scenarios: {}, metadata: {} };
} else if (afterStats.scenarios) {
    // New format: { metadata, scenarios }
    currentData = afterStats;
} else {
    // Direct scenarios object
    currentData = { scenarios: afterStats, metadata: {} };
}

const { scenarios, metadata } = currentData;

// Extract metrics
const nocacheMetrics = scenarios.nocache || {};
const millicacheMetrics = scenarios.millicache || {};

// Calculate medians with outlier removal
const nocacheTtfbData = removeOutliers(nocacheMetrics.timeToFirstByte || []);
const millicacheTtfbData = removeOutliers(millicacheMetrics.timeToFirstByte || []);
const nocacheLcpData = removeOutliers(nocacheMetrics.largestContentfulPaint || []);
const millicacheLcpData = removeOutliers(millicacheMetrics.largestContentfulPaint || []);

const nocacheTtfb = median(nocacheTtfbData.cleaned);
const millicacheTtfb = median(millicacheTtfbData.cleaned);
const nocacheLcp = median(nocacheLcpData.cleaned);
const millicacheLcp = median(millicacheLcpData.cleaned);

// Calculate improvements
const ttfbSpeedup = calculateSpeedup(nocacheTtfb, millicacheTtfb);
const lcpSpeedup = calculateSpeedup(nocacheLcp, millicacheLcp);
const ttfbImprovement = calculateImprovement(nocacheTtfb, millicacheTtfb);
const lcpImprovement = calculateImprovement(nocacheLcp, millicacheLcp);

// Calculate standard deviations
const ttfbStdDev = standardDeviation(millicacheTtfbData.cleaned);
const lcpStdDev = standardDeviation(millicacheLcpData.cleaned);

// Regression detection
let regressionStatus = 'PASS';
let regressionMessage = '';
let previousSpeedup = null;

if (beforeStats) {
    let prevData;
    if (Array.isArray(beforeStats)) {
        const perfResult = beforeStats.find(r => r.file && r.file.includes('performance'));
        prevData = perfResult ? { scenarios: perfResult.results } : { scenarios: {} };
    } else if (beforeStats.scenarios) {
        prevData = beforeStats;
    } else {
        prevData = { scenarios: beforeStats };
    }

    const prevNocache = prevData.scenarios.nocache || {};
    const prevMillicache = prevData.scenarios.millicache || {};

    const prevNocacheTtfb = median(prevNocache.timeToFirstByte || []);
    const prevMillicacheTtfb = median(prevMillicache.timeToFirstByte || []);

    if (prevMillicacheTtfb > 0) {
        previousSpeedup = prevNocacheTtfb / prevMillicacheTtfb;
        const speedupChange = (ttfbSpeedup - previousSpeedup) / previousSpeedup;

        if (speedupChange < -REGRESSION_THRESHOLD) {
            regressionStatus = 'FAIL';
            regressionMessage = `Performance regression detected: speedup dropped from ${previousSpeedup.toFixed(2)}x to ${ttfbSpeedup.toFixed(2)}x (${(speedupChange * 100).toFixed(1)}%)`;
        } else if (speedupChange > 0) {
            regressionMessage = `Performance improved: ${previousSpeedup.toFixed(2)}x -> ${ttfbSpeedup.toFixed(2)}x (+${(speedupChange * 100).toFixed(1)}%)`;
        } else {
            regressionMessage = `Performance stable: ${previousSpeedup.toFixed(2)}x -> ${ttfbSpeedup.toFixed(2)}x (${(speedupChange * 100).toFixed(1)}%)`;
        }
    }
}

// Generate Markdown output
let markdown = `## MilliCache Performance Results\n\n`;

if (ttfbSpeedup > 1) {
    markdown += `Loading **${ttfbSpeedup.toFixed(1)}x faster** with MilliCache!\n\n`;
} else {
    markdown += `Performance comparison complete.\n\n`;
}

markdown += `| Metric | Without Cache | With MilliCache | Improvement |\n`;
markdown += `|--------|---------------|-----------------|-------------|\n`;
markdown += `| Server Response (TTFB) | ${formatMs(nocacheTtfb)} | ${formatMs(millicacheTtfb)} | **${ttfbImprovement.toFixed(0)}% faster** |\n`;
markdown += `| Page Load (LCP) | ${formatMs(nocacheLcp)} | ${formatMs(millicacheLcp)} | **${lcpImprovement.toFixed(0)}% faster** |\n`;
markdown += `\n`;
markdown += `> **Server Response** is the primary metric for page caching. It measures how fast the server delivers the HTML. MilliCache focuses on serving cached pages instantly, eliminating database queries and PHP processing.\n\n`;
markdown += `> **Page Load** includes frontend rendering time, which depends on themes, scripts, and assets. Content optimization is outside MilliCache's current scope.\n\n`;

// Technical details in collapsible section
markdown += `<details>\n`;
markdown += `<summary>Technical Details</summary>\n\n`;

const totalIterations = (nocacheMetrics.timeToFirstByte || []).length;
const outliersRemoved = nocacheTtfbData.removed + millicacheTtfbData.removed;

// Get DB queries from raw data
const nocacheDbQueries = nocacheMetrics.wpDbQueries?.[0] || 0;
const millicacheDbQueries = millicacheMetrics.wpDbQueries?.[0] || 0;

// Calculate response time ranges
const nocacheTtfbMin = Math.min(...(nocacheMetrics.timeToFirstByte || [0]));
const nocacheTtfbMax = Math.max(...(nocacheMetrics.timeToFirstByte || [0]));
const millicacheTtfbMin = Math.min(...(millicacheMetrics.timeToFirstByte || [0]));
const millicacheTtfbMax = Math.max(...(millicacheMetrics.timeToFirstByte || [0]));

// Get WordPress timing metrics (from first/uncached request)
const wpTotal = nocacheMetrics.wpTotal?.[0] || 0;
const wpBeforeTemplate = nocacheMetrics.wpBeforeTemplate?.[0] || 0;
const wpTemplate = nocacheMetrics.wpTemplate?.[0] || 0;

markdown += `### Cache Efficiency\n`;
markdown += `- Database queries: ${nocacheDbQueries} → ${millicacheDbQueries}`;
if (nocacheDbQueries > 0 && millicacheDbQueries === 0) {
    markdown += ` (100% reduction)`;
}
markdown += `\n`;
markdown += `- Response time range: ${formatMs(millicacheTtfbMin)}-${formatMs(millicacheTtfbMax)} (cached) vs ${formatMs(nocacheTtfbMin)}-${formatMs(nocacheTtfbMax)} (uncached)\n`;
markdown += `\n`;

if (wpTotal > 0) {
    markdown += `### Cache Generation (First Request)\n`;
    markdown += `- Total processing: ${wpTotal.toFixed(2)}ms\n`;
    if (wpBeforeTemplate > 0) {
        markdown += `- Before template: ${wpBeforeTemplate.toFixed(2)}ms\n`;
    }
    if (wpTemplate > 0) {
        markdown += `- Template rendering: ${wpTemplate.toFixed(2)}ms\n`;
    }
    markdown += `\n`;
}

markdown += `### Test Configuration\n`;
markdown += `- Iterations: ${totalIterations}`;
if (metadata.warmUpRuns) {
    markdown += ` (${metadata.warmUpRuns} warm-up runs discarded)`;
}
markdown += `\n`;
markdown += `- Outliers removed: ${outliersRemoved}\n`;

if (metadata.timestamp) {
    markdown += `- Timestamp: ${metadata.timestamp}\n`;
}
if (metadata.commitSha) {
    markdown += `- Commit: ${metadata.commitSha.substring(0, 7)}\n`;
}

if (beforeStats) {
    markdown += `\n### Regression Analysis\n`;
    markdown += `- Status: **${regressionStatus}**\n`;
    markdown += `- ${regressionMessage}\n`;
    markdown += `- Threshold: ${(REGRESSION_THRESHOLD * 100).toFixed(0)}% slowdown triggers failure\n`;
}

markdown += `\n</details>\n`;

// Console output
console.log('\n=== MilliCache Performance Results ===\n');
console.log(`TTFB: ${formatMs(nocacheTtfb)} -> ${formatMs(millicacheTtfb)} (${ttfbSpeedup.toFixed(2)}x faster)`);
console.log(`LCP:  ${formatMs(nocacheLcp)} -> ${formatMs(millicacheLcp)} (${lcpSpeedup.toFixed(2)}x faster)`);

if (beforeStats) {
    console.log(`\nRegression: ${regressionStatus}`);
    console.log(regressionMessage);
}

// Write markdown file
const markdownPath = join(WP_ARTIFACTS_PATH, 'performance-results.md');
writeFileSync(markdownPath, markdown);
console.log(`\nMarkdown written to: ${markdownPath}`);

// Write JSON for trending/artifacts
const trendData = {
    timestamp: metadata.timestamp || new Date().toISOString(),
    commitSha: metadata.commitSha || process.env.GITHUB_SHA,
    metrics: {
        ttfb: {
            nocache: nocacheTtfb,
            millicache: millicacheTtfb,
            speedup: ttfbSpeedup,
            improvement: ttfbImprovement,
        },
        lcp: {
            nocache: nocacheLcp,
            millicache: millicacheLcp,
            speedup: lcpSpeedup,
            improvement: lcpImprovement,
        },
    },
    regression: {
        status: regressionStatus,
        previousSpeedup,
        currentSpeedup: ttfbSpeedup,
        threshold: REGRESSION_THRESHOLD,
    },
    raw: scenarios,
};

const jsonPath = join(WP_ARTIFACTS_PATH, 'performance-results.json');
writeFileSync(jsonPath, JSON.stringify(trendData, null, 2));
console.log(`JSON written to: ${jsonPath}`);

// Exit with error if regression detected
if (regressionStatus === 'FAIL') {
    console.error('\nExiting with error due to performance regression.');
    process.exit(1);
}
