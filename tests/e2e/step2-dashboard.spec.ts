import { test, expect } from './setup/e2e-wp-test';
import { networkActivatePlugin } from './utils/tools';
import { AdminBarComponent, SettingsPage } from './pages';

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
    test('"At a Glance"-Widget is available', async ({ page, admin }) => {
        await admin.visitAdminPage('/');
        const element = page.locator('#dashboard_right_now .cache-count');
        await expect(element).toBeVisible();
    });

    test('Adminbar menu is available', async ({ page, admin }) => {
        await admin.visitAdminPage('/');
        const adminBar = new AdminBarComponent(page);
        await expect(adminBar.millicacheMenu).toBeVisible();
    });

    test('Settings page is available', async ({ page, admin }) => {
        const settings = new SettingsPage(page, admin);

        // Navigate to settings page
        await settings.goto();

        // Open Settings Tab
        await settings.openSettingsTab();

        // Get current value and set to a different value to trigger change detection
        const currentGrace = await settings.getGracePeriod();
        const newValue = currentGrace === '2' ? '3' : '2';

        // Set the new value
        await settings.setGracePeriod(newValue);

        // Save settings
        await settings.saveSettings();

        // Verify the value persisted
        const savedGrace = await settings.getGracePeriod();
        expect(savedGrace).toBe(newValue);
    });
});