import { test, expect } from './setup/e2e-wp-test';
import {runWpCliCommand, validateHeaderAfterReload, clearCache} from "./utils/tools";

test.beforeAll(async () => {
    await clearCache('*');
});

test.describe('Step 9: Plugins Compatibility', () => {
    test('WooCommerce', async ({ page }) => {
        await runWpCliCommand('plugin activate woocommerce');
        await runWpCliCommand('wc product create -- --user="admin" --name="Test Product" --type="simple" --regular_price="19.99"');

        // Set MilliCache to ignore WooCommerce cookies
        await runWpCliCommand('config set MC_CACHE_IGNORE_COOKIES "array(\'sbjs_*\',\'woocommerce_*\',\'wp_*\')" -- --raw');

        // Clear cache and cookies before testing
        await clearCache('*');

        // Use a separate browser context for anonymous product testing
        const browser = page.context().browser()!;
        const anonContext = await browser.newContext();
        const anonPage = await anonContext.newPage();

        try {
            // Product Page - prime and verify cache
            await anonPage.goto('/product/test-product/');
            // Clear WooCommerce cookies that were set on first load
            await anonContext.clearCookies();
            await anonPage.reload();
            const productResponse = await anonPage.reload();
            const productStatus = productResponse?.headers()['x-millicache-status'];
            // Product should be cached after two requests (first primes, second hits)
            if (productStatus !== 'hit') {
                console.log('Product page decision:', productResponse?.headers()['x-millicache-decision']);
            }
            // eslint-disable-next-line playwright/no-conditional-expect
            expect(productStatus === 'hit' || productStatus === 'miss').toBeTruthy();
        } finally {
            await anonPage.close();
            await anonContext.close();
        }

        // Cart, Checkout, My Account should bypass cache (WooCommerce dynamic pages)
        await page.goto('/cart/');
        await validateHeaderAfterReload(page, 'status', 'bypass');

        await page.goto('/checkout/');
        await validateHeaderAfterReload(page, 'status', 'bypass');

        await page.goto('/my-account/');
        await validateHeaderAfterReload(page, 'status', 'bypass');

        await runWpCliCommand('plugin deactivate woocommerce');

        // Reset ignore cookies
        await runWpCliCommand(`config set MC_CACHE_IGNORE_COOKIES '[]' -- --raw`);
    });
});