import { test as base, expect } from '@playwright/test';

import {
    Admin,
    Editor,
    PageUtils,
    RequestUtils,
} from '@wordpress/e2e-test-utils-playwright';

/**
 * Extends the base test environment with additional WordPress utilities.
 */
const test = base.extend<{
    admin: Admin;
    editor: Editor;
    pageUtils: PageUtils;
    requestUtils: RequestUtils;
}>({
    async admin({ page, pageUtils, editor }, use) {
        await use(new Admin({ page, pageUtils, editor }));
    },
    async editor({ page }, use) {
        await use(new Editor({ page }));
    },
    async pageUtils({ page }, use) {
        await use(new PageUtils({ page }));
    },
    async requestUtils({}, use) {
        // We want to make all REST API calls as authenticated users.
        const requestUtils = await RequestUtils.setup({
            baseURL: process.env.WP_BASE_URL,
            user: {
                username: process.env.WP_USERNAME,
                password: process.env.WP_PASSWORD,
            },
        });

        await use(requestUtils);
    },
});

export { test, expect };