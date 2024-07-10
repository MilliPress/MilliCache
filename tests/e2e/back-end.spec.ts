import { test, expect } from './setup/e2e-wp-test';
import { validateHeader } from "./utils/validateHeader";
import { login, logout } from "./utils/auth";

/**
 * Set the test mode to serial.
 */
test.describe.configure({ mode: 'serial' });

/**
 * Login via our authentication storage.
 */
test.use({ storageState: process.env.WP_AUTH_STORAGE });

/**
 * Activate the plugin before running the tests.
 */
test.beforeAll(async ({ requestUtils }) => {
    await requestUtils.activatePlugin('millicache');
});

/**
 * Check if the plugin is active.
 */
test.describe('Admin', () => {
    test('Deactivate & Activate Plugin', async ({ requestUtils }) => {
        await requestUtils.deactivatePlugin('millicache');
        await requestUtils.activatePlugin('millicache');
    });

    test('"At a Glance"-Widget Displays Cache Size', async ({ requestUtils, page, admin}) => {
        await admin.visitAdminPage('/');

        const adminBar = page.locator('#dashboard_right_now .cache-count');

        await expect(adminBar).toBeVisible();
    });

    test('Validate cache for new page', async ({ requestUtils, page, admin, editor }) => {
        // Create a new post
        await admin.createNewPost({
            postType: 'page',
            title: "MilliCache Test",
            content: "This is a test post for MilliCache.",
            showWelcomeGuide:false
        })

        // Publish the post
        const id = await editor.publishPost();

        // Logout
        await logout(page);

        // Set storage state to null
        await page.context().clearCookies();

        // Go to the home page
        const response1 = await page.goto('/millicache-test/');

        // Check if the status is set to miss or hit
        await validateHeader(response1, 'status', 'miss');

        // Reload the same page to check if the status is hit
        const response2 = await page.reload();

        // Check if the status is set to miss
        await validateHeader(response2, 'status', 'hit');

        // Login
        await login(page);

        // Reload the same page to check if the status is bypass
        const response3 = await page.reload();

        // Check if the status is set to miss
        await validateHeader(response3, 'status', 'bypass');

        // Delete the page
        await requestUtils.rest( {
            method: 'DELETE',
            path: `/wp/v2/pages/${ id }`,
            params: {
                force: true,
            },
        } );
    });
});
