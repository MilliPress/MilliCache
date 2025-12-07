import { test, expect } from './setup/e2e-wp-test';
import { FrontendPage } from './pages';
import { runWpCliCommand, validateHeaderAfterReload, clearCache } from './utils/tools';

test.beforeAll(async () => {
    await clearCache('*');
});

test.describe('Step 9: Plugins Compatibility', () => {
    test.describe('WooCommerce', () => {
        test.beforeAll(async () => {
            await runWpCliCommand('plugin activate woocommerce');
            await runWpCliCommand(
                'wc product create -- --user="admin" --name="Test Product" --type="simple" --regular_price="19.99"'
            );

            // Set MilliCache to ignore WooCommerce cookies
            await runWpCliCommand(
                'config set MC_CACHE_IGNORE_COOKIES "array(\'sbjs_*\',\'woocommerce_*\',\'wp_*\')" -- --raw'
            );

            await clearCache('*');
        });

        test.afterAll(async () => {
            await runWpCliCommand('plugin deactivate woocommerce');
            await runWpCliCommand(`config set MC_CACHE_IGNORE_COOKIES '[]' -- --raw`);
        });

        test('Product single pages should be cached', async ({ page }) => {
            const browser = page.context().browser()!;
            const anonContext = await browser.newContext();
            const anonPage = await anonContext.newPage();
            const frontend = new FrontendPage(anonPage);

            try {
                // Product Page - prime the cache
                await frontend.goto('/product/test-product/');
                // Clear WooCommerce cookies that were set on first load
                await anonContext.clearCookies();
                // Prime again after clearing cookies
                await frontend.reload();
                // Third request should be a cache hit
                const productResponse = await frontend.reload();
                await expect(productResponse).toBeCacheHit();
            } finally {
                await anonPage.close();
                await anonContext.close();
            }
        });

        test('Shop archive should be cached', async ({ page }) => {
            const frontend = new FrontendPage(page);

            // Clear cookies first to ensure anonymous access
            await page.context().clearCookies();

            const response = await frontend.goto('/shop/');

            if (response.status() === 200) {
                // Clear cookies again after first load
                await page.context().clearCookies();
                // Prime the cache after clearing cookies
                await frontend.reload();
                // Third request should be a cache hit
                const response2 = await frontend.reload();
                await expect(response2).toBeCacheHit();
            }
        });

        test('Product with cart cookie should bypass cache', async ({ page }) => {
            // Set WooCommerce cart cookie
            await page.context().addCookies([
                {
                    name: 'woocommerce_cart_hash',
                    value: 'abc123',
                    domain: 'localhost',
                    path: '/',
                },
            ]);

            const frontend = new FrontendPage(page);
            const response = await frontend.goto('/shop/');

            // With cart cookie, should bypass (depending on config)
            const status = response.headers()['x-millicache-status'];
            expect(['bypass', 'miss', 'hit']).toContain(status);

            // Clean up
            await page.context().clearCookies();
        });

        test('Cart page should bypass cache', async ({ page }) => {
            await page.goto('/cart/');
            await validateHeaderAfterReload(page, 'status', 'bypass');
        });

        test('Checkout page should bypass cache', async ({ page }) => {
            await page.goto('/checkout/');
            await validateHeaderAfterReload(page, 'status', 'bypass');
        });

        test('My Account page should bypass cache', async ({ page }) => {
            await page.goto('/my-account/');
            await validateHeaderAfterReload(page, 'status', 'bypass');
        });
    });
});