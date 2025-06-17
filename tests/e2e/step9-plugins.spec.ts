import { test } from './setup/e2e-wp-test';
import {runWpCliCommand, validateHeaderAfterReload, flushCache} from "./utils/tools";

test.beforeEach(async () => {
    await flushCache('*');
});

test.describe('Step 9: Plugins Compatibility', () => {
    test('WooCommerce', async ({ page }) => {
        await runWpCliCommand('plugin activate woocommerce');
        await runWpCliCommand('wc product create -- --user="admin" --name="Test Product" --type="simple" --regular_price="19.99"');

        // Set MilliCache to ignore WooCommerce cookies
        await runWpCliCommand('config set MC_CACHE_IGNORE_COOKIES "array(\'sbjs\',\'woocommerce_\',\'wp_\')" -- --raw');

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

        // Reset ignore cookies to the original value
        await runWpCliCommand(`config set MC_CACHE_IGNORE_COOKIES '[]' -- --raw`);
    });
});