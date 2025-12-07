import { exec } from 'child_process';
import { expect } from '@playwright/test';

/**
 * Clear the MilliCache.
 *
 * @param flags The cache flags to clear. Use '*' to clear all.
 */
export async function clearCache(flags = '') {
    const stdout = await runWpCliCommand(
        flags ? `millicache clear -- --flags="${flags}"` : 'millicache clear'
    );
    expect(stdout).toContain('Success');
}

/**
 * Executes a WP-CLI command.
 * @param command The WP-CLI command to execute.
 * @returns A promise that resolves with the command output.
 */
export async function runWpCliCommand(command: string): Promise<string> {
    return new Promise((resolve, reject) => {
        exec(`npm run env:tests-cli wp ${command}`, (error, stdout, stderr) => {
            if (error) {
                console.error(`Error executing command: ${stderr}`);
                reject(error);
            } else {
                resolve(stdout);
            }
        });
    });
}

/**
 * Network activates a plugin.
 * Plugin activation is idempotent - activating an already-active plugin succeeds silently.
 * @param slug
 */
export async function networkActivatePlugin(slug = 'millicache') {
    if (slug === 'nocache') {
        return;
    }

    try {
        await runWpCliCommand(`plugin activate ${slug} -- --network`);
    } catch (error) {
        console.error(`Failed to activate plugin ${slug}:`, error);
    }
}

/**
 * Network deactivates a plugin.
 * @param slug
 */
export async function networkDeactivatePlugin(slug = 'millicache') {
    if (slug === 'nocache') {
        return;
    }

    try {
        // Run the WP-CLI command to deactivate the plugin
        await runWpCliCommand(`plugin deactivate ${slug} -- --network`);
    } catch (error) {
        console.error(`Failed to deactivate plugin ${slug}:`, error);
    }
}

/**
 * Removes the advanced-cache.php drop-in to ensure no page caching.
 * This is important for accurate "nocache" baseline measurements.
 */
export async function removeAdvancedCacheDropIn() {
    try {
        // Remove the advanced-cache.php drop-in via shell command
        await runWpCliCommand(`eval "
            \\$file = WP_CONTENT_DIR . '/advanced-cache.php';
            if (file_exists(\\$file)) {
                unlink(\\$file);
                echo 'Removed advanced-cache.php';
            } else {
                echo 'No advanced-cache.php found';
            }
        "`);
    } catch (error) {
        console.error('Failed to remove advanced-cache.php:', error);
    }
}

/**
 * Validate a header value.
 *
 * @param response
 * @param name
 * @param expectedValue
 * @param strict
 */
export async function validateHeader(response: { headers: () => any; url?: () => string }, name: string, expectedValue: string | string[] = null, strict: boolean = true ) {
    // Get all headers
    const headers = response.headers();

    // Get the specific header
    const headerValue = headers['x-millicache-' + name];

    try {
        if (expectedValue !== null) {
            if (Array.isArray(expectedValue)) {
                // Check the header matches one of the expected values
                if (strict) {
                    expect(expectedValue).toContain(headerValue);
                } else {
                    for (const value of expectedValue) {
                        expect(headerValue).toContain(value);
                    }
                }
            } else {
                // Check the header matches the expected value
                expect(headerValue).toBe(expectedValue);
            }
        } else {
            // Check if the header is defined
            expect(headerValue).toBeDefined();
        }
    } catch (error) {
        // Log the URL where the validation failed
        const url = response.url ? response.url() : 'URL not available';
        console.error(`Header validation failed at URL: ${url}`);
        console.error(`Expected header "${name}" ${expectedValue !== null ?
            `to be ${Array.isArray(expectedValue) ? JSON.stringify(expectedValue) : `"${expectedValue}"`}` :
            'to be defined'}, but got "${headerValue}"`);
        throw error;
    }

    return headerValue;
}

/**
 * Reloads the page and validates that a specific header has the expected value
 * @param page - The Playwright page object
 * @param headerName - Name of the header to validate
 * @param expectedValue - Expected value of the header
 */
export async function validateHeaderAfterReload(
    page,
    headerName: string,
    expectedValue: string
) {
    // Wait for any response when reloading the page
    const responsePromise = page.waitForResponse(response => {
        // We want the main page response, which typically contains the final '/' path
        return response.url().includes(page.url());
    });

    await page.reload();
    const response = await responsePromise;

    // Validate the header
    const headerValue = response.headers()['x-millicache-' + headerName.toLowerCase()];
    if (headerValue !== expectedValue) {
        // Log the response headers for debugging
        const allHeaders = await response.allHeaders();
        const millicacheHeaders = Object.keys(allHeaders)
            .filter(key => key.toLowerCase().startsWith('x-millicache-'))
            .reduce((obj, key) => {
                obj[key] = allHeaders[key];
                return obj;
            }, {});

        // Log the URL where validation failed
        console.log(`Header validation failed at URL: ${response.url()}`);
        console.log(`Expected header "${headerName}" to be "${expectedValue}"`, millicacheHeaders);

        // Throw an error if the header value does not match the expected value
        throw new Error(
            `Expected header "${headerName}" to be "${expectedValue}", but got "${headerValue}"`
        );
    }

    return response;
}

/**
 * Get a random link on the page.
 *
 * @param page
 */
export async function getRandomAnchor({ page }) {
    // Get all the links on the page
    const linkHandles = await page.locator('a[href*=\"localhost\"]').elementHandles();

    // Ensure there are links to click on
    if (linkHandles.length > 0) {
        // Randomly click on a link
        const randomIndex = Math.floor(Math.random() * linkHandles.length);
        return linkHandles[randomIndex];
    }

    return null;
}

/**
 * Helper function to camel case the letter after dashes, removing the dashes.
 *
 * @param str
 */
export function camelCaseDashes(str: string) {
    return str.replace(/-([a-z])/g, (_, char) => char.toUpperCase());
}

/**
 * Computes the median number from an array numbers.
 *
 * @param array List of numbers.
 * @return Median.
 */
export function median(array: number[]) {
    const sorted = array.slice().sort((a, b) => a - b);
    const mid = Math.floor(sorted.length / 2);

    if (sorted.length % 2 !== 0) {
        return sorted[mid];
    } else {
        return (sorted[mid - 1] + sorted[mid]) / 2;
    }
}


