import { expect } from '@playwright/test';
import { login, logout } from './auth';

export async function flushCache({ page, admin, network = false}) {
    // Login to the admin dashboard
    await login(page);

    // Visit the admin dashboard
    await admin.visitAdminPage(network ? '/network' : '/');

    // Flush Button
    const adminBarFlushButton = page.locator('#wp-admin-bar-millicache');

    // Check if the button is visible & click it
    await expect(adminBarFlushButton).toBeVisible().then(async () => {
        await adminBarFlushButton.click();
    });

    // Logout
    await logout(page);

    // Reload the page
    await page.reload();

    // Check if the button is not visible
    await expect(adminBarFlushButton).not.toBeVisible();
}
