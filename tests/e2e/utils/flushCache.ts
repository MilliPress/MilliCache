import { expect } from '@playwright/test';
import { login, logout } from './auth';

export async function flushCache({ page, admin }) {
    // Login to the admin dashboard
    await login(page);

    // Flush Button
    const adminBarFlushButton = page.locator('#wp-admin-bar-millicache');

    // Check if the button is visible & click it
    await expect(adminBarFlushButton).toBeVisible().then(async () => {
        await adminBarFlushButton.click();
        console.log('Cache flushed');
    });

    // Logout
    await logout(page);

    // Check if the button is not visible
    await expect(adminBarFlushButton).not.toBeVisible();
}
