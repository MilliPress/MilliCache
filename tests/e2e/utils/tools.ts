import { exec } from 'child_process';
import { expect } from '@playwright/test';

/**
 * Executes a WP-CLI command.
 * @param command The WP-CLI command to execute.
 * @returns A promise that resolves with the command output.
 */
export async function runWpCliCommand(command: string): Promise<string> {
    return new Promise((resolve) => {
        exec(`npm run env:tests-cli wp ${command}`, (error, stdout, stderr) => {
            resolve(stdout);
        });
    });
}

/**
 * Network activates a plugin.
 * @param slug
 */
export async function networkActivatePlugin(slug = 'millicache') {
    // CLI command to list all network-activated plugins
    const listCommand = `plugin list --status=active --field=name --network`;

    // Run the WP-CLI command to get the list of activated plugins
    const activatedPlugins = await runWpCliCommand(listCommand);

    // Check if the plugin is already activated
    if (!activatedPlugins.includes(slug)) {
        // CLI command to activate the plugin
        const activateCommand = `plugin activate ${slug} --network`;

        // Run the WP-CLI command to activate the plugin
        await runWpCliCommand(activateCommand);
    }
}

/**
 * Network deactivates a plugin.
 * @param slug
 */
export async function networkDeactivatePlugin(slug = 'millicache') {
    // CLI command to activate the plugin
    const command = `plugin deactivate ${slug} --network`;

    // Run the WP-CLI command
    await runWpCliCommand(command);
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
