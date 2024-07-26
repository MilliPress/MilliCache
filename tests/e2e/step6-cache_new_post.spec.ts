import { test } from './setup/e2e-wp-test';
import { validateHeader, networkActivatePlugin } from "./utils/tools";
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
test.beforeAll(async () => {
    await networkActivatePlugin();
});

/**
 * Step 2: Dashboard Elements & Functionality
 */
test.describe('Step 2: Dashboard Elements & Functionality', () => {
    let postId = null;

    test('Create new page', async ({ admin, editor }) => {
        await admin.createNewPost({
            postType: 'page',
            title: "MilliCache Test",
            content: "This is a test post for MilliCache.",
            showWelcomeGuide: false
        });
        postId = await editor.publishPost();
    });

    test('Validate cache headers for new page', async ({ page }) => {
        await logout(page);
        await page.context().clearCookies();
        const response1 = await page.goto('/millicache-test/');
        await validateHeader(response1, 'status', 'miss');
        const response2 = await page.reload();
        await validateHeader(response2, 'status', 'hit');
    });

    test('Login and validate "bypass" cache header', async ({ page }) => {
        await login(page);
        const response3 = await page.goto('/millicache-test/');
        await validateHeader(response3, 'status', 'bypass');
    });

    test('Delete the page', async ({ requestUtils }) => {
        await requestUtils.rest({
            method: 'DELETE',
            path: `/wp/v2/pages/${postId}`,
            params: { force: true },
        });
    });
});