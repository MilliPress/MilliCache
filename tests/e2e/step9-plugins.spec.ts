import { test } from './setup/e2e-wp-test';
import {runWpCliCommand, validateHeaderAfterReload, clearCache} from "./utils/tools";

test.beforeEach(async ({ page }) => {
    await clearCache('*');
});

test.describe('Step 9: Plugins Compatibility', () => {
    test('WooCommerce', async ({ page }) => {
        await runWpCliCommand('plugin activate woocommerce');
        await runWpCliCommand('wc product create -- --user="admin" --name="Test Product" --type="simple" --regular_price="19.99"');

        // Set MilliCache to ignore WooCommerce cookies
        await runWpCliCommand('config set MC_CACHE_IGNORE_COOKIES "array(\'sbjs_*\',\'woocommerce_*\',\'wp_*\')" -- --raw');

        // Product Page
        await page.goto('/product/test-product/');
        await validateHeaderAfterReload(page, 'status', 'hit');

        // Cart
        await page.goto('/cart/');
        await validateHeaderAfterReload(page, 'status', 'bypass');

        // Checkout
        await page.goto('/checkout/');
        await validateHeaderAfterReload(page, 'status', 'bypass');

        // My Account
        await page.goto('/my-account/');
        await validateHeaderAfterReload(page, 'status', 'bypass');

        await runWpCliCommand('plugin deactivate woocommerce');

        // Reset ignore cookies
        await runWpCliCommand(`config set MC_CACHE_IGNORE_COOKIES '[]' -- --raw`);
    });

    test('BuddyPress', async ({ page }) => {
        await runWpCliCommand('plugin activate buddypress');

        // Check if the user exists first before trying to create them
        const userExistsCheck = await runWpCliCommand('user list -- --network --field=user_login --format=csv')
            .then(output => output.includes('buddytest'));

        if (! userExistsCheck) {
            await runWpCliCommand('user create buddytest buddytest@example.com -- --role=subscriber --user_pass=password123');
        }

        // Flush rewrite rules
        await runWpCliCommand('rewrite flush');

        // Members Directory
        await page.goto('/members/');
        await validateHeaderAfterReload(page, 'status', 'hit');

        // Activity Stream
        await page.goto('/activity/');
        await validateHeaderAfterReload(page, 'status', 'hit');

        // Member Profile
        await page.goto('/members/buddytest/');
        await validateHeaderAfterReload(page, 'status', 'hit');

        await runWpCliCommand('plugin deactivate buddypress');

    });
});