import { test, expect } from './setup/e2e-wp-test';
import { login, logout } from './utils/auth';
import { clearCache } from './utils/tools';
import { AdminBarComponent, FrontendPage } from './pages';

test.describe('Step 3: Cache Clearing', () => {
    test('Clear network cache via admin bar submenu', async ({
        page,
        admin,
    }) => {
        // First, prime the cache
        await clearCache('*');
        await page.context().clearCookies();

        const frontend = new FrontendPage(page);
        await frontend.goto('/');
        await frontend.reload();

        // Now login and visit the network admin
        await login(page);
        await admin.visitAdminPage('/network');

        const adminBar = new AdminBarComponent(page);
        await adminBar.waitForAdminBar();
        await expect(adminBar.millicacheMenu).toBeVisible();

        // The root click must open the command palette, never clear directly.
        let clearRequestFired = false;
        page.on('request', (request) => {
            if (
                /\/millicache\/v1\/(network\/)?cache/.test(request.url()) &&
                request.method() === 'POST'
            ) {
                clearRequestFired = true;
            }
        });

        await adminBar.openPalette();
        expect(clearRequestFired).toBe(false);

        // MilliCache commands are pre-loaded (root context, empty search).
        await expect(
            page
                .locator('.commands-command-menu [cmdk-item]')
                .filter({ hasText: 'Clear network cache' })
        ).toBeVisible();

        // Our trigger swaps in the clear-cache placeholder.
        await expect(
            page.locator('.commands-command-menu input')
        ).toHaveAttribute('placeholder', /flag patterns/);

        await adminBar.closePalette();

        // A regular (non-button) open must not list our commands.
        await page.evaluate(() =>
            (
                window as unknown as {
                    wp: {
                        data: {
                            dispatch: (store: string) => { open: () => void };
                        };
                    };
                }
            ).wp.data.dispatch('core/commands').open()
        );
        await expect(adminBar.commandPalette).toBeVisible();
        await expect(
            page
                .locator('.commands-command-menu [cmdk-item]')
                .filter({ hasText: 'Clear network cache' })
        ).toBeHidden();
        await adminBar.closePalette();

        // Network clear requires the confirming second click.
        await adminBar.clearNetworkCache();

        // Logout
        await logout(page);
        await page.context().clearCookies();

        // Validate the cache was cleared (should be miss)
        const response = await frontend.goto('/');
        await expect(response).toBeCacheMiss();
    });

    test('Root button toggles the submenu on the front end', async ({
        page,
    }) => {
        await login(page);
        await page.goto('/');

        const adminBar = new AdminBarComponent(page);
        await adminBar.waitForAdminBar();

        // No palette on the front end; root click toggles the submenu.
        let clearRequestFired = false;
        page.on('request', (request) => {
            if (
                /\/millicache\/v1\/(network\/)?cache/.test(request.url()) &&
                request.method() === 'POST'
            ) {
                clearRequestFired = true;
            }
        });

        await adminBar.clickRoot();
        expect(await adminBar.isSubmenuOpen()).toBe(true);
        expect(clearRequestFired).toBe(false);

        await adminBar.clickRoot();
        expect(await adminBar.isSubmenuOpen()).toBe(false);

        await logout(page);
        await page.context().clearCookies();
    });

    test('Clear a single target via the command palette', async ({
        page,
        admin,
    }) => {
        // Prime two pages
        await clearCache('*');
        await page.context().clearCookies();

        const frontend = new FrontendPage(page);
        await frontend.goto('/hello-world/');
        const helloWorldUrl = page.url();
        await frontend.goto('/sample-page/');
        await frontend.reload();

        // Clear only the hello-world URL via the palette's dynamic command
        await login(page);
        await admin.visitAdminPage('/');

        const adminBar = new AdminBarComponent(page);
        await adminBar.waitForAdminBar();
        await adminBar.clearTargetsViaPalette(helloWorldUrl);

        await logout(page);
        await page.context().clearCookies();

        // The target misses, the untouched page still hits
        const missResponse = await frontend.goto('/hello-world/');
        await expect(missResponse).toBeCacheMiss();

        const hitResponse = await frontend.goto('/sample-page/');
        await expect(hitResponse).toBeCacheHit();
    });

    test('Clear a network-wide flag pattern via the command palette', async ({
        page,
        admin,
    }) => {
        // Prime the main site's home page (carries the "1:home" flag)
        await clearCache('*');
        await page.context().clearCookies();

        const frontend = new FrontendPage(page);
        await frontend.goto('/');
        await frontend.goto('/sample-page/');
        await frontend.reload();

        // Must route through /network/cache; the site endpoint would
        // prefix the pattern to "1:*:home" and match nothing.
        await login(page);
        await admin.visitAdminPage('/network');

        const adminBar = new AdminBarComponent(page);
        await adminBar.waitForAdminBar();
        await adminBar.clearTargetsViaPalette('*:home');

        await logout(page);
        await page.context().clearCookies();

        // The home page misses, the untouched page still hits
        const missResponse = await frontend.goto('/');
        await expect(missResponse).toBeCacheMiss();

        const hitResponse = await frontend.goto('/sample-page/');
        await expect(hitResponse).toBeCacheHit();
    });

    test('Page is cached after first request', async ({ page }) => {
        // Clear cache to ensure isolated test
        await clearCache('*');
        await page.context().clearCookies();

        const frontend = new FrontendPage(page);

        // First request - prime the cache
        await frontend.goto('/sample-page/');

        // Second request - should be cached
        const response = await frontend.reload();
        await expect(response).toBeCacheHit();
    });

    test('Clear cache via WP-CLI', async ({ page }) => {
        // Create a new browser context to ensure clean state
        const context = await page.context().browser()!.newContext();
        const newPage = await context.newPage();

        try {
            const frontend = new FrontendPage(newPage);

            // Clear first and prime
            await clearCache('*');
            await frontend.goto('/hello-world/');

            // Verify it gets cached
            const hitResponse = await frontend.reload();
            await expect(hitResponse).toBeCacheHit();

            // Now clear via CLI
            await clearCache('*');

            // Open new page in same context to avoid any page-level cache
            const checkPage = await context.newPage();
            const checkFrontend = new FrontendPage(checkPage);

            // Should be miss after clearing
            const response = await checkFrontend.goto('/hello-world/');
            await expect(response).toBeCacheMiss();

            await checkPage.close();
        } finally {
            await newPage.close();
            await context.close();
        }
    });
});