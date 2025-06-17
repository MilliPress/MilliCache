import { exec } from 'child_process';
import { expect } from '@playwright/test';

/**
 * Clear the MilliCache.
 *
 * @param flags The cache flags to clear.
 */
export async function flushCache(flags = '') {
    const stdout = await runWpCliCommand(`millicache clear -- --flags="${flags ? flags : '*'}"`);
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
 * @param slug
 */
export async function networkActivatePlugin(slug = 'millicache') {
    if (slug === 'nocache') {
        return;
    }

    // Run the WP-CLI command to get the list of activated plugins
    const activatedPlugins = await runWpCliCommand('plugin list -- --status=active-network -- --field=name');

    // Check if the plugin is already activated
    if (!activatedPlugins.split('\n').includes(slug)) {
        // Run the WP-CLI command to activate the plugin
        try {
            await runWpCliCommand(`plugin activate ${slug} -- --network`);
        } catch (error) {
            console.error(`Failed to activate plugin ${slug}:`, error);
        }
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
 * Validate a header value.
 *
 * @param response
 * @param name
 * @param expectedValue
 * @param strict
 */
export async function validateHeader(response: { headers: () => any; }, name: string, expectedValue: string | string[] = null, strict: boolean = true ) {
    // Get all headers
    const headers = response.headers();

    // Get the specific header
    const headerValue = headers['x-millicache-' + name];

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

    // Log the response headers for debugging
    const allHeaders = await response.allHeaders();
    const millicacheHeaders = Object.keys(allHeaders)
        .filter(key => key.toLowerCase().startsWith('x-millicache-'))
        .reduce((obj, key) => {
            obj[key] = allHeaders[key];
            return obj;
        }, {});

    console.log('MilliCache Headers (expecting status: ' + expectedValue + '):', millicacheHeaders);

    // Validate the header
    const headerValue = response.headers()['x-millicache-' + headerName.toLowerCase()];
    if (headerValue !== expectedValue) {
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


