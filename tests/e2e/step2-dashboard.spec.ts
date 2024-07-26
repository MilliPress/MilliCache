import { test, expect } from './setup/e2e-wp-test';
import { networkActivatePlugin } from "./utils/tools";

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
test.beforeAll(async () => {
    await networkActivatePlugin();
});

/**
 * Step 2: Dashboard Elements & Functionality
 */
test.describe('Step 2: Dashboard Elements & Functionality', () => {
    test('"At a Glance"-Widget is available', async ({ page, admin}) => {
        await admin.visitAdminPage('/');
        const element = page.locator('#dashboard_right_now .cache-count');
        await expect(element).toBeVisible();
    });

    test('Adminbar button is available', async ({ page, admin }) => {
        await admin.visitAdminPage('/');
        const element = page.locator('#wp-admin-bar-millicache');
        await expect(element).toBeVisible();
    });
});