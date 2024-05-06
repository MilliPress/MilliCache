import { test, expect } from './setup/e2e-wp-test';

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
 * Deactivate the plugin after running the tests.
 */
test.afterAll(async ({ requestUtils }) => {
    // await requestUtils.deactivatePlugin('millicache');
});

/**
 * Check if the plugin is active.
 */
test.describe('Admin', () => {
    test('"At a Glance"-Widget Displays Cache Size', async ({ requestUtils, page, admin}) => {
        await admin.visitAdminPage('/');

        const adminBar = page.locator('#dashboard_right_now .cache-count');

        await expect(adminBar).toBeVisible();
    });
});
