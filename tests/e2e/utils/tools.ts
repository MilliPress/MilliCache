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
    return new Promise((resolve) => {
        exec(`npm run env:tests-cli wp ${command}`, (error, stdout, stderr) => {
            if (error) {
                console.error(`exec error: ${error}`);
                return;
            }
            resolve(stdout);
        });
    });
}

/**
 * Network activates a plugin.
 * @param slug
 */
export async function networkActivatePlugin(slug = 'millicache') {
    // Run the WP-CLI command to get the list of activated plugins
    const activatedPlugins = await runWpCliCommand('plugin list -- --status=active-network -- --field=name');

    // Check if the plugin is already activated
    if (!activatedPlugins.split('\n').includes(slug)) {
        // Run the WP-CLI command to activate the plugin
        await runWpCliCommand(`plugin activate ${slug} --network`);
    }
}

/**
 * Network deactivates a plugin.
 * @param slug
 */
export async function networkDeactivatePlugin(slug = 'millicache') {
    // Run the WP-CLI command to activate the plugin
    await runWpCliCommand(`plugin deactivate ${slug} -- --network`);
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
