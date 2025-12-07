import type { Page, Locator } from '@playwright/test';
import type { Admin } from '@wordpress/e2e-test-utils-playwright';

/**
 * Page Object for the MilliCache Settings page.
 */
export class SettingsPage {
    readonly page: Page;
    readonly admin: Admin;

    private static readonly SELECTORS = {
        settingsPanel: '#millicache-settings .components-panel',
        settingsTab: '#tab-panel-0-settings',
        gracePeriodLabel: 'span:has-text("Grace Period")',
        ttlLabel: 'span:has-text("Cache TTL")',
        saveButton: 'button:has-text("Save Settings")',
        successNotice: '.components-notice.is-success',
        // Input relative to label
        inputRelative:
            'xpath=ancestor::div[contains(@class, "components-unit-control-wrapper")]//input',
    };

    readonly settingsPanel: Locator;
    readonly settingsTab: Locator;
    readonly saveButton: Locator;

    constructor(page: Page, admin: Admin) {
        this.page = page;
        this.admin = admin;
        this.settingsPanel = page.locator(SettingsPage.SELECTORS.settingsPanel);
        this.settingsTab = page.locator(SettingsPage.SELECTORS.settingsTab);
        this.saveButton = page.locator(SettingsPage.SELECTORS.saveButton);
    }

    /**
     * Navigate to the MilliCache settings page.
     */
    async goto(): Promise<void> {
        await this.admin.visitAdminPage('/options-general.php?page=millicache');
        await this.waitForSettingsLoaded();
    }

    /**
     * Wait for the settings panel to be loaded.
     */
    async waitForSettingsLoaded(): Promise<void> {
        await this.settingsPanel.waitFor({ state: 'visible', timeout: 10000 });
    }

    /**
     * Open the Settings tab.
     */
    async openSettingsTab(): Promise<void> {
        await this.settingsTab.click();
        await this.page.waitForLoadState('networkidle');
    }

    /**
     * Get the Grace Period input locator.
     */
    private getGracePeriodInput(): Locator {
        return this.page
            .locator(SettingsPage.SELECTORS.gracePeriodLabel)
            .locator(SettingsPage.SELECTORS.inputRelative);
    }

    /**
     * Get the TTL input locator.
     */
    private getTtlInput(): Locator {
        return this.page
            .locator(SettingsPage.SELECTORS.ttlLabel)
            .locator(SettingsPage.SELECTORS.inputRelative);
    }

    /**
     * Set the grace period value.
     * @param value - Grace period in seconds
     */
    async setGracePeriod(value: string): Promise<void> {
        const input = this.getGracePeriodInput();
        await input.click();
        await input.fill(value);
        // Trigger blur to ensure React state updates
        await input.blur();
    }

    /**
     * Get the current grace period value.
     * @returns Grace period value as string
     */
    async getGracePeriod(): Promise<string> {
        const input = this.getGracePeriodInput();
        return input.inputValue();
    }

    /**
     * Set the cache TTL value.
     * @param value - TTL in seconds
     */
    async setTtl(value: string): Promise<void> {
        const input = this.getTtlInput();
        await input.clear();
        await input.fill(value);
    }

    /**
     * Get the current TTL value.
     * @returns TTL value as string
     */
    async getTtl(): Promise<string> {
        const input = this.getTtlInput();
        return input.inputValue();
    }

    /**
     * Save settings and wait for the save to complete.
     */
    async saveSettings(): Promise<void> {
        // Wait for button to be visible
        await this.saveButton.waitFor({ state: 'visible' });

        // Wait for button to be enabled using Playwright's built-in method
        await this.saveButton.waitFor({ state: 'attached' });

        // Poll until button is enabled
        const maxWait = 5000;
        const pollInterval = 100;
        const startTime = Date.now();

        while (Date.now() - startTime < maxWait) {
            const isDisabled = await this.saveButton.isDisabled();
            if (!isDisabled) {
                break;
            }
            await this.page.waitForTimeout(pollInterval);
        }

        // Click save and wait for API response
        await Promise.all([
            this.page.waitForResponse(
                (response) =>
                    response.url().includes('wp-json') && response.status() === 200
            ),
            this.saveButton.click(),
        ]);
    }

    /**
     * Check if the success notice is visible.
     */
    async isSuccessNoticeVisible(): Promise<boolean> {
        const notice = this.page.locator(SettingsPage.SELECTORS.successNotice);
        return notice.isVisible();
    }
}
