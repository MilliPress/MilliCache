import type { Page, Locator, Response } from '@playwright/test';

/**
 * Component Object for the WordPress Admin Bar with MilliCache menu.
 *
 * The root button opens the core command palette in wp-admin (WP 7+) and
 * toggles the submenu elsewhere; direct clear actions live in the hover
 * submenu, and the network-wide clear needs a confirming second click.
 */
export class AdminBarComponent {
    readonly page: Page;

    private static readonly SELECTORS = {
        adminBar: '#wpadminbar',
        millicacheMenu: '#wp-admin-bar-millicache',
        rootButton: '#wp-admin-bar-millicache > button.ab-item',
        clearButton: '#wp-admin-bar-millicache-clear button.ab-item',
        clearCurrentButton:
            '#wp-admin-bar-millicache_clear_current button.ab-item',
        commandPalette: '.commands-command-menu',
        commandPaletteInput: '.commands-command-menu input',
        commandPaletteItem: '.commands-command-menu [cmdk-item]',
        cacheStatus: '#wp-admin-bar-millicache .ab-label',
    };

    private static readonly CONFIRM_LABEL = 'Click again to confirm';

    readonly adminBar: Locator;
    readonly millicacheMenu: Locator;
    readonly commandPalette: Locator;

    constructor(page: Page) {
        this.page = page;
        this.adminBar = page.locator(AdminBarComponent.SELECTORS.adminBar);
        this.millicacheMenu = page.locator(
            AdminBarComponent.SELECTORS.millicacheMenu
        );
        this.commandPalette = page.locator(
            AdminBarComponent.SELECTORS.commandPalette
        );
    }

    /**
     * Check if the admin bar is visible.
     */
    async isAdminBarVisible(): Promise<boolean> {
        return this.adminBar.isVisible();
    }

    /**
     * Check if the MilliCache menu is visible in the admin bar.
     */
    async isMillicacheMenuVisible(): Promise<boolean> {
        return this.millicacheMenu.isVisible();
    }

    /**
     * Hover over the MilliCache menu to reveal submenu items.
     */
    async openMenu(): Promise<void> {
        await this.millicacheMenu.waitFor({ state: 'visible', timeout: 5000 });
        await this.millicacheMenu.hover();

        await this.page
            .locator(AdminBarComponent.SELECTORS.clearButton)
            .waitFor({ state: 'visible', timeout: 5000 });
    }

    /**
     * Click the root admin bar button.
     * In wp-admin (WP 7+) this opens the command palette; on the front end
     * it toggles the submenu.
     */
    async clickRoot(): Promise<void> {
        await this.page
            .locator(AdminBarComponent.SELECTORS.rootButton)
            .waitFor({ state: 'visible', timeout: 5000 });
        await this.page.click(AdminBarComponent.SELECTORS.rootButton);
    }

    /**
     * Open the command palette via the root button (wp-admin, WP 7+).
     */
    async openPalette(): Promise<void> {
        await this.clickRoot();
        await this.commandPalette.waitFor({ state: 'visible', timeout: 5000 });
    }

    /**
     * Close the command palette via the Escape key.
     */
    async closePalette(): Promise<void> {
        await this.page.keyboard.press('Escape');
        await this.commandPalette.waitFor({ state: 'hidden', timeout: 5000 });
    }

    /**
     * Check whether the submenu is open (click-toggled on the front end).
     */
    async isSubmenuOpen(): Promise<boolean> {
        const classes = await this.millicacheMenu.getAttribute('class');
        return (classes || '').split(/\s+/).includes('hover');
    }

    /**
     * Wait for the next clear request against the cache endpoint.
     */
    private waitForClearResponse(): Promise<Response> {
        return this.page.waitForResponse(
            (response) =>
                /\/millicache\/v1\/(network\/)?cache/.test(response.url()) &&
                response.request().method() === 'POST',
            { timeout: 10000 }
        );
    }

    /**
     * Clear the site cache via the submenu shortcut (single click).
     */
    async clearSiteCache(): Promise<void> {
        await this.openMenu();

        const responsePromise = this.waitForClearResponse();
        await this.page.click(AdminBarComponent.SELECTORS.clearButton);
        await responsePromise;
    }

    /**
     * Clear the network cache via the submenu shortcut.
     * Requires the confirming second click.
     */
    async clearNetworkCache(): Promise<void> {
        await this.openMenu();

        const clearButton = this.page.locator(
            AdminBarComponent.SELECTORS.clearButton
        );

        await clearButton.click();
        await clearButton
            .filter({ hasText: AdminBarComponent.CONFIRM_LABEL })
            .waitFor({ timeout: 5000 });

        const responsePromise = this.waitForClearResponse();
        await clearButton.click();
        await responsePromise;
    }

    /**
     * Clear the given targets (comma-separated flags/post IDs/URLs)
     * through the command palette's dynamic command.
     */
    async clearTargetsViaPalette(targets: string): Promise<void> {
        await this.openPalette();

        await this.page.fill(
            AdminBarComponent.SELECTORS.commandPaletteInput,
            targets
        );

        const command = this.page
            .locator(AdminBarComponent.SELECTORS.commandPaletteItem)
            .filter({ hasText: 'Clear cache for' });
        await command.waitFor({ state: 'visible', timeout: 5000 });

        const responsePromise = this.waitForClearResponse();
        await command.click();
        await responsePromise;
    }

    /**
     * Get the cache status text from the admin bar (if displayed).
     */
    async getCacheStatusText(): Promise<string | null> {
        const statusElement = this.page.locator(
            AdminBarComponent.SELECTORS.cacheStatus
        );

        if (await statusElement.isVisible()) {
            return statusElement.textContent();
        }

        return null;
    }

    /**
     * Wait for the admin bar to be loaded.
     */
    async waitForAdminBar(): Promise<void> {
        await this.adminBar.waitFor({ state: 'visible', timeout: 10000 });
    }
}
