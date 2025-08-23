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

    test('Adminbar menu is available', async ({ page, admin }) => {
        await admin.visitAdminPage('/');
        const element = page.locator('#wp-admin-bar-millicache');
        await expect(element).toBeVisible();
    });

    test('Settings page is available', async ({ page, admin }) => {
        await admin.visitAdminPage('/options-general.php?page=millicache');

        // React component is rendered.
        const element = page.locator('#millicache-settings .components-panel');
        await expect(element).toBeVisible();

        // Open Settings Tab.
        const settingsTab = page.locator('#tab-panel-0-settings');
        await expect(settingsTab).toBeVisible();
        await settingsTab.click();

        // Get the element that has the text "Max TTL".
        const maxTTL = page.locator('span:has-text("Max TTL")');
        await expect(maxTTL).toBeVisible();

        // Get the maxTTL input field.
        const maxTTLInput = maxTTL
            .locator('xpath=ancestor::div[contains(@class, "components-unit-control-wrapper")]//input');
        await expect(maxTTLInput).toBeVisible();

        // Change the value of maxTTLInput.
        await maxTTLInput.fill('2');
        await expect(maxTTLInput).toHaveValue('2');

        // Click the button that has the text "Save Changes".
        const saveButton = page.locator('button:has-text("Save Settings")');
        await expect(saveButton).toBeVisible();
        await saveButton.click();

        // Check if the value of maxTTLInput is still 2
        await expect(maxTTLInput).toHaveValue('2');
    });
});