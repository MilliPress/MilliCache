import { test, expect } from './setup/e2e-wp-test';
import { networkActivatePlugin } from './utils/tools';
import { login, logout } from './utils/auth';
import { FrontendPage } from './pages';

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
 * Step 6: New Post Caching
 */
test.describe('Step 6: New Post Caching', () => {
    let postId: number | null = null;

    test('Create new page', async ({ admin, editor }) => {
        await admin.createNewPost({
            postType: 'page',
            title: 'MilliCache Test',
            content: 'This is a test post for MilliCache.',
            showWelcomeGuide: false,
        });
        postId = await editor.publishPost();
    });

    test('Validate cache headers for new page (logged-out)', async ({
        page,
    }) => {
        // Logout and clear cookies to test as anonymous user
        await logout(page);
        await page.context().clearCookies();

        const frontend = new FrontendPage(page);

        // First request should be a cache miss
        const response1 = await frontend.goto('/millicache-test/');
        await expect(response1).toBeCacheMiss();

        // Second request should be a cache hit
        const response2 = await frontend.reload();
        await expect(response2).toBeCacheHit();
    });

    test('Validate cache bypass for logged-in users', async ({ page }) => {
        await login(page);

        const frontend = new FrontendPage(page);
        const response = await frontend.goto('/millicache-test/');
        await expect(response).toBeCacheBypassed();
    });

    test('Delete the test page', async ({ requestUtils }) => {
        if (postId) {
            await requestUtils.rest({
                method: 'DELETE',
                path: `/wp/v2/pages/${postId}`,
                params: { force: true },
            });
        }
    });
});