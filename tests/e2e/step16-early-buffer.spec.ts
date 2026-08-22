import { test, expect } from './setup/e2e-wp-test';
import { clearCache, networkActivatePlugin } from './utils/tools';
import { FrontendPage } from './pages';

/**
 * Step 16: Outermost Output Buffer
 *
 * MilliCache opens its capture buffer in the drop-in phase, making it the
 * outermost output buffer. Post-processors that buffer on init (simulated
 * by the init-ob-postprocessor mu-plugin, mirroring TranslatePress) nest
 * inside it and flush first, so the cache stores the transformed HTML.
 */

const MARKER = '<!-- mc-e2e-init-ob -->';

test.describe('Step 16: Outermost Output Buffer', () => {
    test.beforeAll(async () => {
        await networkActivatePlugin();
        await clearCache('*');
    });

    test.describe('Init-phase post-processor capture', () => {
        test('Post-processed HTML is served on miss and stored in the cache', async ({
            page,
        }) => {
            await clearCache('*');
            await page.context().clearCookies();

            const frontend = new FrontendPage(page);

            // Prime the cache: the visitor sees the post-processed page.
            const missResponse = await frontend.goto('/hello-world/');
            await expect(missResponse).toHaveCacheStatus(['miss', 'bypass']);
            expect(await page.content()).toContain(MARKER);

            // The regression assertion: the cached entry must contain the
            // post-processed HTML, not the raw capture.
            const hitResponse = await frontend.reload();
            await expect(hitResponse).toBeCacheHit();
            expect(await page.content()).toContain(MARKER);
        });
    });

    test.describe('Never-storable paths stay uncached', () => {
        test('Canonical redirects are not cached', async ({ page }) => {
            await clearCache('*');

            // WordPress 301-redirects the missing trailing slash on
            // template_redirect:10 — before the storage sentinel fires.
            for (let i = 0; i < 2; i++) {
                const response = await page.request.get('/hello-world', {
                    maxRedirects: 0,
                });
                expect(response.status()).toBe(301);
                const status = response.headers()['x-millicache-status'];
                expect(status).not.toBe('hit');
            }
        });
    });
});
