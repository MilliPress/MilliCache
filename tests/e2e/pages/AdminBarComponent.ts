import type { Page, Locator } from '@playwright/test';

/**
 * Component Object for the WordPress Admin Bar with MilliCache menu.
 */
export class AdminBarComponent {
    readonly page: Page;

    private static readonly SELECTORS = {
        adminBar: '#wpadminbar',
        millicacheMenu: '#wp-admin-bar-millicache',
        clearAllLink: '#wp-admin-bar-millicache-clear-all a',
        clearSiteLink: '#wp-admin-bar-millicache-clear-site a',
        cacheStatus: '#wp-admin-bar-millicache .ab-label',
    };

    readonly adminBar: Locator;
    readonly millicacheMenu: Locator;

    constructor(page: Page) {
        this.page = page;
        this.adminBar = page.locator(AdminBarComponent.SELECTORS.adminBar);
        this.millicacheMenu = page.locator(
            AdminBarComponent.SELECTORS.millicacheMenu
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
    async hoverMillicacheMenu(): Promise<void> {
        // Wait for menu to be visible first
        await this.millicacheMenu.waitFor({ state: 'visible', timeout: 5000 });

        // Hover to reveal submenu
        await this.millicacheMenu.hover();

        // Wait a bit for CSS transition
        await this.page.waitForTimeout(300);

        // Wait for submenu to appear
        const clearAllLink = this.page.locator(
            AdminBarComponent.SELECTORS.clearAllLink
        );

        // If not visible, try clicking instead of hovering
        if (!(await clearAllLink.isVisible())) {
            await this.millicacheMenu.click();
            await this.page.waitForTimeout(300);
        }

        await clearAllLink.waitFor({ state: 'visible', timeout: 5000 });
    }

    /**
     * Clear all cache across the network.
     * Waits for the operation to complete.
     */
    async clearAllCache(): Promise<void> {
        await this.hoverMillicacheMenu();

        const clearAllLink = this.page.locator(
            AdminBarComponent.SELECTORS.clearAllLink
        );

        // Get the href and navigate directly (more reliable than click + wait)
        const href = await clearAllLink.getAttribute('href');
        if (href) {
            await this.page.goto(href);
            await this.page.waitForLoadState('networkidle');
        } else {
            // Fallback to click
            await clearAllLink.click();
            await this.page.waitForLoadState('networkidle');
        }
    }

    /**
     * Clear cache for the current site only.
     * Waits for the operation to complete.
     */
    async clearSiteCache(): Promise<void> {
        await this.hoverMillicacheMenu();

        const clearSiteLink = this.page.locator(
            AdminBarComponent.SELECTORS.clearSiteLink
        );

        // Click and wait for page to reload/respond
        await Promise.all([
            this.page.waitForLoadState('networkidle'),
            clearSiteLink.click(),
        ]);
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
